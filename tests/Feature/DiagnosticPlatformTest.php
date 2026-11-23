<?php

namespace Tests\Feature;

use App\Diagnostics\Framework;
use App\Diagnostics\ScoringEngine;
use App\Models\Assessment;
use App\Models\Company;
use App\Models\FrameworkVersion;
use App\Models\NavigationItem;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DiagnosticPlatformTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_framework_definition_passes_structural_validation(): void
    {
        $fw = FrameworkVersion::active();
        $errors = Framework::validate($fw->definition);

        $this->assertEmpty($errors, 'Framework definition had validation errors: '.implode(', ', $errors));
        $this->assertEquals(['SG', 'FH', 'OE', 'MG', 'HC'], $fw->moduleKeys());
    }

    public function test_deterministic_scoring_engine_computes_consistent_scores_and_gates_finance_before_capital(): void
    {
        $fw = FrameworkVersion::active();
        $scorer = new ScoringEngine($fw);

        $symptoms = ['s5' => 5, 's8' => 5, 's9' => 4, 's11' => 5];
        $answers = [
            // Weak financial control (FH1, FH2, FH3) + weak investment readiness (FH4, FH5)
            'fc1' => 2, 'fh_1' => 2, 'fc2' => 2,
            'fc4' => 2, 'fh_2' => 2, 'fh_f1' => 2,
            'fc3' => 2, 'fh_3' => 2,
            'ir2' => 1, 'ir_f1' => 1, 'ir4' => 1,
            'ir1' => 2, 'ir3' => 2,
        ];

        $result1 = $scorer->score(['stage' => 'growth', 'sector' => 'agritech'], ['FH'], $symptoms, $answers);
        $result2 = $scorer->score(['stage' => 'growth', 'sector' => 'agritech'], ['FH'], $symptoms, $answers);

        // Repeatability guarantee
        $this->assertSame($result1['health_index'], $result2['health_index']);
        $this->assertSame($result1['modules']['FH']['score'], $result2['modules']['FH']['score']);

        // Sequencing gate: Business Performance ('performance') must come before Finance Access ('finance_access')
        $seqProgs = array_column($result1['sequence'], 'programme');
        $perfPos = array_search('performance', $seqProgs, true);
        $capPos = array_search('finance_access', $seqProgs, true);
        $this->assertNotFalse($perfPos);
        $this->assertNotFalse($capPos);
        $this->assertLessThan($capPos, $perfPos, 'Financial Control / Performance must gate Capital Raising');
    }

    public function test_end_to_end_diagnostic_submission_and_consultant_review_workflow(): void
    {
        $this->get('/')->assertStatus(200)->assertSee('Bridge Tailor');
        $this->get('/assess')->assertStatus(200)->assertSee('Business Profile');

        $response = $this->post('/assess', [
            'contact_name' => 'Kofi Mensah',
            'company_name' => 'Tema Precision Engineering Ltd',
            'contact_email' => 'kofi@temaprecision.com.gh',
            'contact_phone' => '+233 24 123 4567',
            'country' => 'Ghana',
            'city' => 'Tema',
            'stage' => 'growth',
            'sector' => 'manufacturing',
            'size_band' => '21-50',
            'revenue_band' => '3to6',
            'objectives' => 'Scale industrial spares production and raise working capital.',
            'challenges' => 'Manual spreadsheets and founder bottleneck.',
            'mode' => 'comprehensive',
            'modules_in_scope' => ['SG', 'FH', 'OE', 'MG', 'HC'],
            'symptoms' => ['s2' => 4, 's5' => 4, 's19' => 4],
            'answers' => [
                'sg_1' => 1, 'fd1' => 2, 'sg_4' => 0,
                'fc1' => 2, 'fc4' => 2, 'ir1' => 2,
                'oe_1' => 2, 'oe_5' => 1, 'sec_mf' => 1,
                'mg_1' => 0, 'rm4' => 1,
                'hc_1' => 1, 'hc_2' => 2,
            ],
            'custom_issue' => 'Need to professionalise reporting before meeting lenders.',
            'ai_consent' => 1,
        ]);

        $assessment = Assessment::whereHas('company', fn ($q) => $q->where('name', 'Tema Precision Engineering Ltd'))->first();
        $this->assertNotNull($assessment);
        $this->assertNotNull($assessment->health_index);
        $this->assertNotNull($assessment->report);
        $this->assertNotNull($assessment->company->assigned_consultant_id, 'New companies should be auto-assigned to a consultant when Auto-Assignment is enabled');
        $response->assertRedirect();

        // View report & print view
        $this->get("/report/{$assessment->ulid}?token={$assessment->access_token}")
            ->assertStatus(200)
            ->assertSee('Tema Precision Engineering Ltd')
            ->assertSee('EPISTEMIC INTEGRITY');

        $this->get("/report/{$assessment->ulid}/print?token={$assessment->access_token}")
            ->assertStatus(200)
            ->assertSee('EXECUTIVE DIAGNOSTIC DOSSIER');

        // Unauthenticated or client access to Consultant Review Mode is blocked by RBAC
        $this->get('/consultant')->assertRedirect('/login');
        $clientUser = \App\Models\User::where('email', 'kwame@voltaagro.com.gh')->first();
        $this->actingAs($clientUser)->get('/consultant')->assertStatus(403);

        // Authenticate as Consultant for Consultant Review Mode amendment & approval
        $consultantUser = \App\Models\User::where('email', 'consult@bridge-tailor.com')->first();
        $this->actingAs($consultantUser)->post("/consultant/review/{$assessment->ulid}", [
            'executive_summary' => 'Consultant-verified executive diagnostic summary for Tema Precision Engineering Ltd.',
            'consultant_notes' => 'Approved for Phase 1 Performance System Setup.',
            'review_status' => 'published',
        ])->assertRedirect();

        $this->assertEquals('published', $assessment->fresh()->report->review_status);
        $this->assertEquals('published', $assessment->fresh()->status);

        // Client portal & Consultant dashboard render cleanly under proper roles
        $this->actingAs($clientUser)->get('/portal')->assertStatus(200)->assertSee('Volta Agro-Processing Ltd');
        $this->actingAs($consultantUser)->get('/consultant')->assertStatus(200)->assertSee('Tema Precision Engineering Ltd');
        $this->actingAs($consultantUser)->get('/consultant/framework')->assertStatus(403);

        $adminUser = \App\Models\User::where('email', 'admin@bridge-tailor.com')->first();
        $this->actingAs($adminUser)->get('/consultant/framework')->assertStatus(200)->assertSee('Framework Definition JSON');

        // Admin Console (/admin) has access to console features, but cannot see Developer accounts or the Developer role option
        $this->actingAs($adminUser)->get('/admin')
            ->assertStatus(200)
            ->assertSee('BRIDGE-TAILOR ADMIN CONSOLE')
            ->assertDontSee('dev@bridge-tailor.com')
            ->assertDontSee('Developer (Full Master Console)');

        // Developer Master Account has unrestricted access to Developer Console, Dynamic Text, Visibility Toggles & User Directory (including Developer accounts)
        $devUser = \App\Models\User::where('email', 'dev@bridge-tailor.com')->first();
        $this->assertNotNull($devUser);
        $this->actingAs($devUser)->get('/developer')
            ->assertStatus(200)
            ->assertSee('DEVELOPER MASTER CONSOLE')
            ->assertSee('dev@bridge-tailor.com')
            ->assertSee('Developer (Full Master Console)');
        $this->actingAs($devUser)->post('/developer/text', [
            'texts' => ['text.hero_title' => 'Custom Developer Headline'],
        ])->assertRedirect();
        $this->get('/')->assertStatus(200)->assertSee('Custom Developer Headline');

        $this->actingAs($devUser)->post('/developer/visibility', [
            'vis' => ['vis.top_utility_bar' => '1'], // hide vis.landing_wp_integration_kit by omitting it
        ])->assertRedirect();
        $this->get('/')->assertStatus(200)->assertDontSee('WordPress Integration Kit for www.bridge-tailor.com');

        // User Profile feature works for all roles (Client, Consultant, Admin, Developer)
        $this->actingAs($clientUser)->get('/profile')->assertStatus(200)->assertSee('Kwame Osei-Tutu');
        $this->actingAs($clientUser)->post('/profile/details', [
            'name' => 'Kwame Osei-Tutu',
            'email' => 'kwame@voltaagro.com.gh',
            'phone' => '+233 20 889 4411',
            'job_title' => 'Executive Chairman',
        ])->assertRedirect();
        $this->assertEquals('Executive Chairman', $clientUser->fresh()->job_title);

        // Framework Versioning: Minor Edit in-place vs. New Version (which logs & archives previous version)
        $currentDef = FrameworkVersion::active()->definition;
        $this->actingAs($adminUser)->post('/consultant/framework', [
            'save_mode' => 'minor_edit',
            'version' => '1.0.0',
            'name' => 'Bridge-Tailor Business Diagnostic Framework',
            'change_notes' => 'Typo fix in FH1 question wording.',
            'definition_json' => json_encode($currentDef),
        ])->assertRedirect();
        $this->assertEquals(1, FrameworkVersion::count());

        $this->actingAs($adminUser)->post('/consultant/framework', [
            'save_mode' => 'new_version',
            'version' => '1.1.0',
            'name' => 'Bridge-Tailor Business Diagnostic Framework (Q4 Edition)',
            'change_notes' => 'Added Q4 regulatory checks.',
            'definition_json' => json_encode($currentDef),
        ])->assertRedirect();

        $this->assertEquals(2, FrameworkVersion::count());
        $v10 = FrameworkVersion::where('version', '1.0.0')->first();
        $v11 = FrameworkVersion::where('version', '1.1.0')->first();
        $this->assertFalse($v10->is_active, 'Previous version v1.0.0 must be archived and logged for reference');
        $this->assertTrue($v11->is_active, 'New version v1.1.0 must be active');
    }

    public function test_admin_can_hide_show_overview_and_manage_dynamic_menu(): void
    {
        NavigationItem::resetToDefaults();

        $admin = User::firstOrCreate(
            ['email' => 'admin@bridge-tailor.com'],
            ['name' => 'Samuel Bridge-Tailor', 'role' => 'admin', 'password' => bcrypt('password')]
        );

        // Verify Overview initially visible in guest header
        $overview = NavigationItem::where('key', 'guest_overview')->first();
        $this->assertNotNull($overview);
        $this->assertTrue($overview->is_visible);
        $this->assertTrue($overview->is_active);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Overview');

        // Admin hides Overview
        $this->actingAs($admin)
            ->post("/admin/menu/{$overview->id}/toggle-visibility")
            ->assertRedirect();

        $overview->refresh();
        $this->assertFalse($overview->is_visible);

        // Guest visits landing page — Overview nav link must NOT be visible!
        auth()->logout();
        $response = $this->get('/');
        $response->assertStatus(200);
        $this->assertStringNotContainsString('class=" active">Overview</a>', $response->getContent());

        // Admin enables Overview to show again
        $this->actingAs($admin)
            ->post("/admin/menu/{$overview->id}/toggle-visibility")
            ->assertRedirect();

        $overview->refresh();
        $this->assertTrue($overview->is_visible);

        // Guest sees Overview again
        auth()->logout();
        $response = $this->get('/');
        $response->assertSee('Overview');

        // Admin creates a dynamic menu item
        $this->actingAs($admin)
            ->post('/admin/menu', [
                'label' => 'Advisory Insights',
                'url' => 'https://www.bridge-tailor.com/insights',
                'audience' => 'guest',
                'target' => '_blank',
                'is_active' => '1',
                'is_visible' => '1',
                'sort_order' => 25,
            ])
            ->assertRedirect();

        $customItem = NavigationItem::where('label', 'Advisory Insights')->first();
        $this->assertNotNull($customItem);
        $this->assertTrue($customItem->is_active);
        $this->assertTrue($customItem->is_visible);

        auth()->logout();
        $response = $this->get('/');
        $response->assertSee('Advisory Insights');
        $response->assertSee('https://www.bridge-tailor.com/insights');

        // Admin deactivates the dynamic menu
        $this->actingAs($admin)
            ->post("/admin/menu/{$customItem->id}/toggle-active")
            ->assertRedirect();

        $customItem->refresh();
        $this->assertFalse($customItem->is_active);

        // It must no longer appear in the navigation when inactive
        auth()->logout();
        $response = $this->get('/');
        $this->assertStringNotContainsString('href="https://www.bridge-tailor.com/insights"', $response->getContent());

        // Total removal of dynamic item
        $this->actingAs($admin)
            ->post("/admin/menu/{$customItem->id}/delete")
            ->assertRedirect();

        $this->assertDatabaseMissing('navigation_items', ['id' => $customItem->id]);
    }

    public function test_admin_can_execute_total_removal_and_reset_menu_defaults(): void
    {
        NavigationItem::resetToDefaults();

        $admin = User::firstOrCreate(
            ['email' => 'admin@bridge-tailor.com'],
            ['name' => 'Samuel Bridge-Tailor', 'role' => 'admin', 'password' => bcrypt('password')]
        );

        $overview = NavigationItem::where('key', 'guest_overview')->first();
        $this->assertNotNull($overview);

        // Execute Total Removal on Overview
        $this->actingAs($admin)
            ->post("/admin/menu/{$overview->id}/delete")
            ->assertRedirect();

        $this->assertDatabaseMissing('navigation_items', ['key' => 'guest_overview']);

        // Verify Overview is completely gone for guest
        auth()->logout();
        $response = $this->get('/');
        $this->assertStringNotContainsString('Overview</a>', $response->getContent());

        // Reset menu defaults
        $this->actingAs($admin)
            ->post('/admin/menu-reset')
            ->assertRedirect();

        $this->assertDatabaseHas('navigation_items', ['key' => 'guest_overview']);
        auth()->logout();
        $response = $this->get('/');
        $response->assertSee('Overview');
    }

    public function test_admin_can_dynamically_customize_all_footer_sections(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@bridge-tailor.com'],
            ['name' => 'Samuel Bridge-Tailor', 'role' => 'admin', 'password' => bcrypt('password')]
        );

        $this->actingAs($admin)
            ->post('/admin/text', [
                'texts' => [
                    'text.footer_col1_title' => 'BT GLOBAL ADVISORY · ACCRA & LONDON',
                    'text.footer_col2_title' => 'Core Assessment Frameworks',
                    'text.footer_col3_title' => 'Strategic Interventions',
                    'text.footer_col4_title' => 'Compliance & Enterprise Security',
                    'text.footer_col2_items' => "01 · Governance & Board\n02 · Growth Capital",
                ],
            ])
            ->assertRedirect();

        auth()->logout();
        $response = $this->get('/');
        $response->assertSee('BT GLOBAL ADVISORY · ACCRA & LONDON');
        $response->assertSee('Core Assessment Frameworks');
        $response->assertSee('Compliance & Enterprise Security');
        $response->assertSee('01 · Governance & Board');
        $response->assertSee('02 · Growth Capital');
        // By default, Column 3 (Practice Lines) is hidden
        $response->assertDontSee('Practice Lines');

        // Admin can toggle Column 3 visibility on
        $this->actingAs($admin)
            ->post('/admin/visibility', [
                'vis' => [
                    'vis.footer_section' => '1',
                    'vis.footer_practice_lines' => '1',
                ],
            ])
            ->assertRedirect();

        auth()->logout();
        $response = $this->get('/');
        $response->assertSee('Strategic Interventions');
    }

    public function test_admin_can_dynamically_change_hero_slider_images(): void
    {
        $admin = User::where('role', 'admin')->first();

        // Admin updates slide 1 and slide 2 images dynamically
        $this->actingAs($admin)
            ->post('/admin/text', [
                'texts' => [
                    'text.hero_slide_1_image' => 'https://images.unsplash.com/custom-boardroom-exec.jpg',
                    'text.hero_slide_2_image' => 'images/slider/custom-local-slide.jpg',
                ],
            ])
            ->assertRedirect();

        auth()->logout();
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('https://images.unsplash.com/custom-boardroom-exec.jpg');
        $response->assertSee('images/slider/custom-local-slide.jpg');
    }

    public function test_console_sections_are_collapsed_by_default(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin)->get('/admin');
        $response->assertStatus(200);

        // All 5 major module sections should have collapsed content containers by default
        $response->assertSee('id="dev-visibility-content" style="display:none;', false);
        $response->assertSee('id="dev-navigation-menu-content" style="display:none;', false);
        $response->assertSee('id="dev-text-content" style="display:none;', false);
        $response->assertSee('id="dev-users-content" style="display:none;', false);
        $response->assertSee('id="dev-enterprises-content" style="display:none;', false);

        // All badges and expand buttons should be rendered
        $response->assertSee('+ Expand Section ▼');
        $response->assertSee('+ Expand All Sections');
        $response->assertSee('− Collapse All Sections');
    }

    public function test_developer_can_gate_admin_privileges_for_console_and_framework(): void
    {
        $dev = User::where('role', 'developer')->first();
        $admin = User::where('role', 'admin')->first();

        // 1. Developer toggles off Admin Console access and Framework access
        $response = $this->actingAs($dev)->post(route('developer.privileges.update'), [
            'priv' => [
                'privilege.admin_console_access' => '0',
                'privilege.admin_framework_access' => '0',
            ],
        ]);
        $response->assertRedirect();

        // 2. Admin should now be forbidden (403) from accessing /admin and /consultant/framework
        $adminConsoleRes = $this->actingAs($admin)->get('/admin');
        $adminConsoleRes->assertStatus(403);

        $adminFrameworkRes = $this->actingAs($admin)->get('/consultant/framework');
        $adminFrameworkRes->assertStatus(403);

        // 3. Navigation items for admin should omit admin_console and admin_framework
        $this->actingAs($admin);
        $navItems = NavigationItem::getForCurrentRequest();
        $keys = $navItems->pluck('key')->all();
        $this->assertNotContains('admin_console', $keys);
        $this->assertNotContains('admin_framework', $keys);

        // 4. Developer always retains access regardless of toggles
        $devConsoleRes = $this->actingAs($dev)->get('/admin');
        $devConsoleRes->assertStatus(200);

        $devFrameworkRes = $this->actingAs($dev)->get('/consultant/framework');
        $devFrameworkRes->assertStatus(200);

        // 5. Restore toggles
        $this->actingAs($dev)->post(route('developer.privileges.update'), [
            'priv' => [
                'privilege.admin_console_access' => '1',
                'privilege.admin_framework_access' => '1',
            ],
        ]);

        $this->actingAs($admin)->get('/admin')->assertStatus(200);
        $this->actingAs($admin)->get('/consultant/framework')->assertStatus(200);
    }

    public function test_consultant_dashboard_does_not_render_audit_trail_or_removed_buttons(): void
    {
        $consultant = User::where('role', 'consultant')->first();
        $admin = User::where('role', 'admin')->first();
        $developer = User::where('role', 'developer')->first();

        // 1. Consultant view: No audit trail, no removed buttons
        $response = $this->actingAs($consultant)->get('/consultant');
        $response->assertStatus(200);

        // The 2 action buttons must be absent
        $response->assertDontSee('+ Run Consultant-Assisted Diagnostic');
        $response->assertDontSee('Configure Diagnostic Framework &amp; Questions', false);

        // The Audit Trail section and table must be absent for standard consultants
        $response->assertDontSee('SECURITY &amp; GOVERNANCE AUDIT TRAIL', false);
        $response->assertDontSee('Immutable Audit Log of Report Generation');

        // Modern stats card classes should be present
        $response->assertSee('bt-stats-grid bt-stats-grid-4', false);
        $response->assertSee('bt-stat-card dark', false);

        // 2. Admin & Developer view: Audit trail IS visible
        $adminRes = $this->actingAs($admin)->get('/consultant');
        $adminRes->assertStatus(200);
        $adminRes->assertSee('SECURITY &amp; GOVERNANCE AUDIT TRAIL', false);
        $adminRes->assertSee('Immutable Audit Log of Report Generation');

        $devRes = $this->actingAs($developer)->get('/consultant');
        $devRes->assertStatus(200);
        $devRes->assertSee('SECURITY &amp; GOVERNANCE AUDIT TRAIL', false);
        $devRes->assertSee('Immutable Audit Log of Report Generation');
    }

    public function test_all_user_roles_can_access_their_profile(): void
    {
        $roles = ['client', 'consultant', 'admin', 'developer'];

        foreach ($roles as $role) {
            $user = User::where('role', $role)->first();
            $this->assertNotNull($user, "No user with role '{$role}' found.");

            $this->actingAs($user);
            $items = NavigationItem::getForCurrentRequest();
            $labels = $items->pluck('label')->all();

            // The main navbar should stay clean and focused (not cluttered with My Profile)
            $this->assertNotContains('My Profile', $labels, "Role '{$role}' unexpectedly has 'My Profile' in main navbar.");

            // Profile page remains accessible directly and via utility bar
            $res = $this->get(route('profile.show'));
            $res->assertStatus(200);
            $res->assertSee($user->name);
        }
    }

    public function test_guest_does_not_see_client_staff_sign_in_in_utility_bar(): void
    {
        auth()->logout();

        $response = $this->get('/');
        $response->assertStatus(200);

        // The top utility bar button should NOT be rendered for guests
        $response->assertDontSee('Client / Staff Sign In');

        // The primary navigation bar should still have Sign In
        $response->assertSee('Sign In');
    }

    public function test_unauthenticated_user_cannot_access_portal_and_is_redirected_to_login(): void
    {
        auth()->logout();

        $this->get('/portal')->assertRedirect('/login');
        $this->post('/portal/companies', ['name' => 'Unauthorized Corp'])->assertRedirect('/login');
        $this->post('/portal/actions/1/log', ['delta' => 1, 'note' => 'Test'])->assertRedirect('/login');

        // Authenticated client can access portal
        $clientUser = User::where('role', 'client')->first();
        $this->actingAs($clientUser)->get('/portal')->assertStatus(200);

        // Authenticated consultant can access portal
        $consultantUser = User::where('role', 'consultant')->first();
        $this->actingAs($consultantUser)->get('/portal')->assertStatus(200);
    }

    public function test_top_utility_bar_does_not_render_office_info_and_footer_renders_it_below_first_column(): void
    {
        $clientUser = User::where('role', 'client')->first();

        $response = $this->actingAs($clientUser)->get('/');
        $response->assertStatus(200);

        // Top utility bar has user status but not the office text inside .bt-utility
        $content = $response->getContent();
        preg_match('/<div class="bt-utility">.*?<\/div>\s*<\/div>/s', $content, $utilityMatches);
        $this->assertNotEmpty($utilityMatches);
        $this->assertStringNotContainsString('East Legon, Accra, Ghana', $utilityMatches[0]);
        $this->assertStringNotContainsString('https://bridge-tailor.com', $utilityMatches[0]);
        $this->assertStringContainsString('Signed in as', $utilityMatches[0]);

        // Footer column 1 renders the office and website info
        $this->assertStringContainsString('East Legon, Accra, Ghana', $content);
        $this->assertStringContainsString('https://bridge-tailor.com', $content);
    }

    public function test_mobile_navigation_renders_user_profile_signout_and_role_badge_next_to_hamburger(): void
    {
        $clientUser = User::where('role', 'client')->first();

        $response = $this->actingAs($clientUser)->get('/');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Role badge rendered next to hamburger button in .bt-header-actions
        $this->assertStringContainsString('bt-mobile-role-tag', $content);
        $this->assertStringContainsString('CLIENT', $content);

        // Hamburger drawer contains mobile user status, profile link and logout
        $this->assertStringContainsString('bt-mobile-user-card', $content);
        $this->assertStringContainsString($clientUser->name, $content);
        $this->assertStringContainsString(route('profile.show'), $content);
        $this->assertStringContainsString('bt-mobile-logout-btn', $content);
    }
}
