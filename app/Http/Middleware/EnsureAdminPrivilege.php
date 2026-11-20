<?php

namespace App\Http\Middleware;

use App\Models\PlatformSetting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPrivilege
{
    /**
     * Handle incoming request for Developer-governed Administrator features.
     * Developers always have permanent, unrestricted access.
     * Administrators are subject to the privilege toggles set by the Developer.
     *
     * @param string $privilege 'console' or 'framework'
     */
    public function handle(Request $request, Closure $next, string $privilege): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Developer super-account always has unrestricted access
        if ($user->isDeveloper()) {
            return $next($request);
        }

        // Check developer-governed privilege toggles for standard Admin users
        if ($user->role === 'admin') {
            if ($privilege === 'console') {
                $allowed = PlatformSetting::visible('privilege.admin_console_access', true);
                if (! $allowed) {
                    abort(403, 'Access to the Admin Console has been restricted by the Platform Developer.');
                }
            } elseif ($privilege === 'framework') {
                $allowed = PlatformSetting::visible('privilege.admin_framework_access', true);
                if (! $allowed) {
                    abort(403, 'Access to the Diagnostic Framework & Questions Configuration has been restricted by the Platform Developer.');
                }
            }
        }

        return $next($request);
    }
}
