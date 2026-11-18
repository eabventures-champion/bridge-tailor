<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login')
                ->withErrors(['role' => 'Please sign in as a Bridge Tailor Consultant, Administrator, or Developer to access that workspace.']);
        }

        // Developer super-account has unrestricted access to every workspace and route
        if ($user->role === 'developer') {
            return $next($request);
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'Insufficient role privileges. Required role: '.implode(' or ', $roles).'.');
        }

        return $next($request);
    }
}
