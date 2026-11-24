<?php

namespace App\Http\Controllers;

use App\Mail\DeveloperTwoFactorMail;
use App\Mail\SecurityAccessCodeRequestMail;
use App\Models\AuditLog;
use App\Models\PlatformSecurityCode;
use App\Models\PlatformSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SecurityAccessController extends Controller
{
    /**
     * Display the High-End Security Access Gate page.
     */
    public function showGate(Request $request)
    {
        // If developer is logged in, they are exempt and can proceed directly
        if (Auth::check() && Auth::user()->isDeveloper()) {
            return redirect()->route('developer.console')
                ->with('status', 'Developer bypass active. You have full unrestricted access to the platform.');
        }

        $adminEmail = PlatformSetting::text('security.admin_email', 'eabventures@gmail.com');
        $gateEnabled = PlatformSetting::visible('security.gate_enabled', true);
        $isCleared = $request->session()->get('platform_security_cleared') === true;

        $dev2faPending = $request->session()->get('dev_2fa_pending', false);
        $dev2faEmail = $request->session()->get('dev_2fa_target_email', $adminEmail);
        $dev2faCodePreview = app()->isLocal() ? $request->session()->get('dev_2fa_code') : null;

        // In local development, fetch the most recent active code for convenient testing
        $latestDemoCode = null;
        if (app()->isLocal()) {
            $latestDemoCode = PlatformSecurityCode::where('status', 'active')
                ->latest('id')
                ->first();
        }

        return view('security.gate', [
            'adminEmail' => $adminEmail,
            'gateEnabled' => $gateEnabled,
            'isCleared' => $isCleared,
            'clearedEmail' => $request->session()->get('platform_security_email'),
            'clearedCode' => $request->session()->get('platform_security_code'),
            'latestDemoCode' => $latestDemoCode,
            'dev2faPending' => $dev2faPending,
            'dev2faEmail' => $dev2faEmail,
            'dev2faCodePreview' => $dev2faCodePreview,
        ]);
    }

    /**
     * Verify a submitted access code and grant platform security clearance.
     */
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|string|min:4|max:64',
        ]);

        $rawCode = trim((string) $request->input('code'));
        $cleanCode = strtoupper($rawCode);

        // Allow formatted code like BT-SEC-XXXXXX or without prefix if user types just the letters
        $record = PlatformSecurityCode::where('access_code', $cleanCode)
            ->orWhere('access_code', 'BT-SEC-' . $cleanCode)
            ->first();

        if (! $record || ! $record->isValid()) {
            $errMsg = 'Invalid, expired, or depleted access clearance code. Please double-check your code or submit a request to eabventures@gmail.com.';

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => false,
                    'message' => $errMsg,
                ], 422);
            }

            return back()->withInput()->withErrors(['code' => $errMsg]);
        }

        // Mark code as used
        $record->markUsed($request->ip(), $request->userAgent());

        // Grant clearance to session
        $request->session()->put('platform_security_cleared', true);
        $request->session()->put('platform_security_code', $record->access_code);
        $request->session()->put('platform_security_email', $record->email);
        $request->session()->put('platform_security_name', $record->name);
        $request->session()->put('platform_security_cleared_at', now()->toIso8601String());

        AuditLog::record('security.clearance_granted', null, null, [
            'access_code' => $record->access_code,
            'email' => $record->email,
            'role_requested' => $record->role_requested,
            'ip' => $request->ip(),
        ]);

        $intended = $request->session()->pull('url.intended', route('diagnostic.landing'));

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'message' => 'Security clearance verified. Unlocking Bridge-Tailor platform...',
                'redirect' => $intended,
            ]);
        }

        return redirect($intended)->with('status', 'Security clearance verified. Welcome to the Bridge-Tailor platform.');
    }

    /**
     * Handle incoming request for an access code.
     * Generates a unique code and sends an email to eabventures@gmail.com.
     */
    public function requestAccess(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|max:180',
            'name' => 'required|string|max:160',
            'organization' => 'nullable|string|max:180',
            'role_requested' => 'nullable|string|in:guest,client,consultant,admin',
            'message' => 'required|string|min:5|max:2000',
        ]);

        $adminEmail = PlatformSetting::text('security.admin_email', 'eabventures@gmail.com');
        $expiryHours = (int) PlatformSetting::text('security.code_expiry_hours', '72');
        if ($expiryHours <= 0) {
            $expiryHours = 72;
        }

        // Generate unique high-entropy code
        $code = PlatformSecurityCode::generateUniqueCode();

        $securityCode = PlatformSecurityCode::create([
            'access_code' => $code,
            'email' => strtolower(trim($data['email'])),
            'name' => trim($data['name']),
            'organization' => trim($data['organization'] ?? ''),
            'role_requested' => $data['role_requested'] ?? 'guest',
            'request_message' => trim($data['message']),
            'delivered_to' => $adminEmail,
            'status' => 'active',
            'times_used' => 0,
            'max_uses' => 25,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addHours($expiryHours),
        ]);

        // Dispatch email notification to eabventures@gmail.com
        try {
            Mail::to($adminEmail)->send(new SecurityAccessCodeRequestMail($securityCode));
        } catch (\Throwable $e) {
            Log::error('Security Access Code email delivery error: ' . $e->getMessage(), [
                'recipient' => $adminEmail,
                'code' => $code,
                'requester' => $securityCode->email,
            ]);
        }

        AuditLog::record('security.request_submitted', null, null, [
            'requester_email' => $securityCode->email,
            'requester_name' => $securityCode->name,
            'generated_code' => $securityCode->access_code,
            'dispatched_to' => $adminEmail,
        ]);

        $successMsg = "Your access request has been securely transmitted to {$adminEmail}. Platform administration has received your generated access code and will share it with you for entry.";

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'message' => $successMsg,
                'email' => $securityCode->email,
                'code_preview' => app()->isLocal() ? $securityCode->access_code : null,
            ]);
        }

        return back()->with('status', $successMsg)->with('request_success', true);
    }

    /**
     * Dedicated Developer Master Access Terminal Login (Step 1: Credential Verification & 2FA Dispatch).
     * Strictly reserved for the Developer role. Dispatches 2FA code to eabventures@gmail.com.
     */
    public function developerLogin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::validate($credentials)) {
            $user = User::where('email', $credentials['email'])->first();

            if (! $user || ! $user->isDeveloper()) {
                return back()->withErrors([
                    'dev_error' => 'This terminal is strictly reserved for the Platform Developer. Clients, consultants, and admins must enter their verified Access Code.',
                ]);
            }

            // Generate high-entropy 6-digit OTP code
            $otp = sprintf('%06d', random_int(100000, 999999));
            $targetEmail = PlatformSetting::text('security.admin_email', 'eabventures@gmail.com');

            // Store 2FA challenge state in session
            $request->session()->put('dev_2fa_pending', true);
            $request->session()->put('dev_2fa_user_id', $user->id);
            $request->session()->put('dev_2fa_code', $otp);
            $request->session()->put('dev_2fa_expires_at', now()->addMinutes(15)->timestamp);
            $request->session()->put('dev_2fa_target_email', $targetEmail);
            $request->session()->put('dev_2fa_remember', $request->boolean('remember'));

            // Dispatch 2FA code email directly to eabventures@gmail.com
            try {
                Mail::to($targetEmail)->send(new DeveloperTwoFactorMail($otp, $user, $request->ip(), $request->userAgent()));
            } catch (\Throwable $e) {
                Log::error('Developer 2FA email delivery error: ' . $e->getMessage(), [
                    'recipient' => $targetEmail,
                    'user' => $user->email,
                ]);
            }

            AuditLog::record('security.developer_2fa_dispatched', null, null, [
                'user_id' => $user->id,
                'email' => $user->email,
                'recipient' => $targetEmail,
            ]);

            $msg = "Credentials verified. Two-Factor Authentication code dispatched to {$targetEmail}. Enter the 6-digit verification code to complete bypass.";

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json([
                    'success' => true,
                    'step' => '2fa',
                    'message' => $msg,
                    'target_email' => $targetEmail,
                    'code_preview' => app()->isLocal() ? $otp : null,
                ]);
            }

            return back()->with('dev_2fa_step', true)
                ->with('status', $msg);
        }

        return back()->withErrors([
            'dev_error' => 'Invalid Developer credentials.',
        ]);
    }

    /**
     * Dedicated Developer Master Access Terminal Login (Step 2: 2FA Verification).
     * Validates the 6-digit code delivered to eabventures@gmail.com.
     */
    public function developerVerify2fa(Request $request)
    {
        $request->validate([
            'two_factor_code' => 'required|string|min:4|max:12',
        ]);

        if (! $request->session()->get('dev_2fa_pending') || ! $request->session()->get('dev_2fa_user_id')) {
            return back()->withErrors([
                'dev_error' => 'No active 2FA verification session found. Please re-enter your developer credentials.',
            ]);
        }

        $expiresAt = $request->session()->get('dev_2fa_expires_at');
        if ($expiresAt && now()->timestamp > $expiresAt) {
            $request->session()->forget(['dev_2fa_pending', 'dev_2fa_user_id', 'dev_2fa_code', 'dev_2fa_expires_at']);

            return back()->withErrors([
                'dev_error' => 'Your 2FA verification code has expired (15 minute validity window). Please re-authenticate.',
            ]);
        }

        $expectedCode = (string) $request->session()->get('dev_2fa_code');
        $rawInput = trim((string) $request->input('two_factor_code'));
        $inputCode = preg_replace('/\D/', '', $rawInput);

        if ($inputCode !== $expectedCode) {
            $errMsg = 'Invalid Two-Factor Verification Code. Please check the 6-digit code delivered to eabventures@gmail.com.';

            if ($request->expectsJson() || $request->isXmlHttpRequest()) {
                return response()->json(['success' => false, 'message' => $errMsg], 422);
            }

            return back()->with('dev_2fa_step', true)->withErrors(['dev_2fa_error' => $errMsg]);
        }

        $userId = $request->session()->get('dev_2fa_user_id');
        $remember = $request->session()->get('dev_2fa_remember', false);
        $user = User::find($userId);

        if (! $user || ! $user->isDeveloper()) {
            return back()->withErrors(['dev_error' => 'Unauthorized account.']);
        }

        // Authenticate Developer super-account session
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget([
            'dev_2fa_pending',
            'dev_2fa_user_id',
            'dev_2fa_code',
            'dev_2fa_expires_at',
            'dev_2fa_target_email',
            'dev_2fa_remember',
        ]);

        $request->session()->put('platform_security_cleared', true);
        $request->session()->put('platform_security_code', 'DEV-2FA-VERIFIED');
        $request->session()->put('platform_security_email', $user->email);
        $request->session()->put('platform_security_cleared_at', now()->toIso8601String());

        AuditLog::record('security.developer_2fa_verified', null, null, [
            'user_id' => $user->id,
            'email' => $user->email,
            'ip' => $request->ip(),
        ]);

        $intended = $request->session()->pull('url.intended', route('developer.console'));

        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'success' => true,
                'message' => 'Developer 2FA verified. Entering Developer Master Console...',
                'redirect' => $intended,
            ]);
        }

        return redirect($intended)
            ->with('status', 'Developer Two-Factor Authentication verified. Platform security bypass active.');
    }

    /**
     * Resend fresh 2FA verification code to eabventures@gmail.com.
     */
    public function developerResend2fa(Request $request)
    {
        if (! $request->session()->get('dev_2fa_pending') || ! $request->session()->get('dev_2fa_user_id')) {
            return back()->withErrors(['dev_error' => 'Session expired. Please sign in again.']);
        }

        $userId = $request->session()->get('dev_2fa_user_id');
        $user = User::find($userId);
        if (! $user) {
            return back()->withErrors(['dev_error' => 'Developer account not found.']);
        }

        $targetEmail = PlatformSetting::text('security.admin_email', 'eabventures@gmail.com');
        $otp = sprintf('%06d', random_int(100000, 999999));

        $request->session()->put('dev_2fa_code', $otp);
        $request->session()->put('dev_2fa_expires_at', now()->addMinutes(15)->timestamp);

        try {
            Mail::to($targetEmail)->send(new DeveloperTwoFactorMail($otp, $user, $request->ip(), $request->userAgent()));
        } catch (\Throwable $e) {
            Log::error('Developer 2FA resend error: ' . $e->getMessage());
        }

        AuditLog::record('security.developer_2fa_resent', null, null, [
            'user_id' => $user->id,
            'recipient' => $targetEmail,
        ]);

        return back()->with('dev_2fa_step', true)
            ->with('status', "A fresh 6-digit 2FA verification code was sent to {$targetEmail}.");
    }

    /**
     * Cancel 2FA challenge and return to developer credentials form.
     */
    public function developerCancel2fa(Request $request)
    {
        $request->session()->forget([
            'dev_2fa_pending',
            'dev_2fa_user_id',
            'dev_2fa_code',
            'dev_2fa_expires_at',
            'dev_2fa_target_email',
            'dev_2fa_remember',
        ]);

        return redirect()->route('security.gate')->with('open_dev_modal', true);
    }

    /**
     * Lock the platform terminal and clear session clearance.
     */
    public function lock(Request $request)
    {
        $request->session()->forget([
            'platform_security_cleared',
            'platform_security_code',
            'platform_security_email',
            'platform_security_name',
            'platform_security_cleared_at',
        ]);

        AuditLog::record('security.platform_locked');

        return redirect()->route('security.gate')
            ->with('status', 'Platform session locked. High-end security gate reactivated.');
    }
}
