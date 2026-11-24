<?php

namespace Tests\Feature;

use App\Mail\DeveloperTwoFactorMail;
use App\Mail\SecurityAccessCodeRequestMail;
use App\Models\PlatformSecurityCode;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SecurityAccessGateTest extends TestCase
{
    use DatabaseTransactions;
    protected function setUp(): void
    {
        parent::setUp();
        PlatformSetting::flushCache();
    }

    public function test_guest_is_redirected_to_security_access_gate_when_gate_is_enabled(): void
    {
        PlatformSetting::updateOrCreate(
            ['key' => 'security.gate_enabled'],
            ['group' => 'system', 'type' => 'boolean', 'label' => 'Gate', 'value' => '1']
        );
        PlatformSetting::flushCache();

        $response = $this->get('/');
        $response->assertRedirect(route('security.gate'));
    }

    public function test_security_access_gate_page_loads_successfully(): void
    {
        $response = $this->get(route('security.gate'));
        $response->assertStatus(200);
        $response->assertSee('Platform Access Clearance');
        $response->assertSee('Enter Access Code');
        $response->assertSee('Request Access Code');
        $response->assertSee('eabventures@gmail.com');
    }

    public function test_submitting_access_request_generates_code_and_sends_email_to_admin(): void
    {
        Mail::fake();

        $response = $this->post(route('security.request'), [
            'email' => 'client@acme.com',
            'name' => 'Acme Founder',
            'organization' => 'Acme Holdings Ltd',
            'role_requested' => 'client',
            'message' => 'Need access to assess our agribusiness diagnostics.',
        ]);

        $response->assertSessionHas('request_success', true);
        $this->assertDatabaseHas('platform_security_codes', [
            'email' => 'client@acme.com',
            'name' => 'Acme Founder',
            'delivered_to' => 'eabventures@gmail.com',
            'status' => 'active',
        ]);

        $record = PlatformSecurityCode::where('email', 'client@acme.com')->first();
        $this->assertNotNull($record);
        $this->assertStringStartsWith('BT-SEC-', $record->access_code);

        Mail::assertSent(SecurityAccessCodeRequestMail::class, function ($mail) {
            return $mail->hasTo('eabventures@gmail.com');
        });
    }

    public function test_verifying_invalid_code_fails(): void
    {
        $response = $this->post(route('security.verify'), [
            'code' => 'INVALID-CODE-999',
        ]);

        $response->assertSessionHasErrors(['code']);
        $this->assertFalse(session('platform_security_cleared', false));
    }

    public function test_verifying_valid_code_grants_clearance_and_allows_platform_access(): void
    {
        $testCode = 'BT-SEC-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
        $sec = PlatformSecurityCode::create([
            'access_code' => $testCode,
            'email' => 'partner@consulting.com',
            'name' => 'Partner Reviewer',
            'status' => 'active',
            'times_used' => 0,
            'max_uses' => 10,
            'delivered_to' => 'eabventures@gmail.com',
            'expires_at' => now()->addDays(2),
        ]);

        $response = $this->post(route('security.verify'), [
            'code' => $testCode,
        ]);

        $response->assertSessionHas('platform_security_cleared', true);
        $this->assertEquals(1, $sec->fresh()->times_used);

        // Now guest with cleared session can visit the landing page
        $landingResponse = $this->get('/');
        $landingResponse->assertStatus(200);
    }

    public function test_developer_is_strictly_exempt_from_security_gate(): void
    {
        PlatformSetting::updateOrCreate(
            ['key' => 'security.gate_enabled'],
            ['group' => 'system', 'type' => 'boolean', 'label' => 'Gate', 'value' => '1']
        );
        PlatformSetting::flushCache();

        $dev = User::where('role', 'developer')->first();
        if (! $dev) {
            $dev = User::factory()->create(['role' => 'developer', 'email' => 'dev@bridge-tailor.com']);
        }

        // Developer accessing landing page without prior security code in session
        $response = $this->actingAs($dev)->get('/');
        $response->assertStatus(200);

        // Developer accessing developer console
        $consoleResponse = $this->actingAs($dev)->get('/developer');
        $consoleResponse->assertStatus(200);
    }

    public function test_developer_terminal_login_triggers_2fa_and_sends_code_to_eabventures(): void
    {
        Mail::fake();

        $dev = User::where('role', 'developer')->first();
        if (! $dev) {
            $dev = User::create([
                'name' => 'Lead Platform Developer',
                'email' => 'dev@bridge-tailor.com',
                'role' => 'developer',
                'password' => bcrypt('Developer2026!'),
            ]);
        }

        $response = $this->post(route('security.dev_login'), [
            'email' => $dev->email,
            'password' => 'Developer2026!',
        ]);

        $response->assertSessionHas('dev_2fa_pending', true);
        $this->assertFalse(auth()->check(), 'Developer should not be authenticated before completing 2FA.');

        Mail::assertSent(DeveloperTwoFactorMail::class, function ($mail) {
            return $mail->hasTo('eabventures@gmail.com');
        });

        // Step 2: Verify wrong 2FA code fails
        $wrongResponse = $this->post(route('security.dev_verify_2fa'), [
            'two_factor_code' => '000000',
        ]);
        $wrongResponse->assertSessionHasErrors(['dev_2fa_error']);
        $this->assertFalse(auth()->check());

        // Step 3: Verify valid 2FA code succeeds
        $otp = session('dev_2fa_code');
        $this->assertNotEmpty($otp);

        $verifyResponse = $this->post(route('security.dev_verify_2fa'), [
            'two_factor_code' => $otp,
        ]);

        $verifyResponse->assertRedirect(route('developer.console'));
        $this->assertAuthenticatedAs($dev);
        $this->assertTrue(session('platform_security_cleared', false));
    }
}
