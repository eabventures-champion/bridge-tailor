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
}
