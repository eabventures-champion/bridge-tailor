<?php

namespace App\Http\Middleware;

use App\Models\PlatformSecurityCode;
use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PlatformSecurityGate
{
    /**
     * Handle an incoming request.
     * Enforces high-end security access for all guests, clients, consultants, and admins.
     * The developer super-account is strictly exempted.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Check if the security gate is enabled in platform settings
        if (! PlatformSetting::visible('security.gate_enabled', true)) {
            return $next($request);
        }

        // 2. The developer role is strictly exempt from the platform gate
        if (Auth::check() && Auth::user()?->isDeveloper()) {
            return $next($request);
        }

        // 3. Exempt specific infrastructure and security authentication routes
        if ($this->isExemptRoute($request)) {
            return $next($request);
        }

        // 4. Verify if the current session has granted security clearance
        if ($request->session()->get('platform_security_cleared') === true) {
            $codeStr = $request->session()->get('platform_security_code');
            if ($codeStr) {
                $record = PlatformSecurityCode::where('access_code', $codeStr)->first();
                // If revoked or expired, revoke the session clearance
                if ($record && ! $record->isValid()) {
                    $request->session()->forget(['platform_security_cleared', 'platform_security_code', 'platform_security_email']);

                    return redirect()->route('security.gate')->withErrors([
                        'code' => 'Your security clearance token has expired or was revoked. Please request a new access code.',
                    ]);
                }
            }

            return $next($request);
        }

        // 5. Access denied: user must pass through the security clearance gate
        if ($request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json([
                'status' => 'error',
                'message' => 'High-end security clearance required to access the Bridge-Tailor platform.',
                'redirect' => route('security.gate'),
            ], 403);
        }

        // Save intended destination for seamless redirect after entering valid access code
        if ($request->isMethod('GET') && ! $request->is('security-access*')) {
            $request->session()->put('url.intended', $request->fullUrl());
        }

        return redirect()->route('security.gate');
    }

    /**
     * Determine if the current request is for an exempt route.
     */
    protected function isExemptRoute(Request $request): bool
    {
        // Security gate routes
        if ($request->is('security-access*')) {
            return true;
        }

        // Named security routes
        $currentRoute = $request->route()?->getName();
        if ($currentRoute && str_starts_with($currentRoute, 'security.')) {
            return true;
        }

        // Static files & system health checks
        if ($request->is('up', 'livewire/*', '_debugbar/*', 'images/*', 'css/*', 'js/*', 'favicon.ico')) {
            return true;
        }

        return false;
    }
}
