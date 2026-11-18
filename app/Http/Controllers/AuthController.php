<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            AuditLog::record('auth.login', null, null, ['role' => $user->role]);

            $home = match ($user->role) {
                'developer' => route('developer.console'),
                'admin' => route('admin.console'),
                'consultant' => route('consultant.dashboard'),
                default => route('portal.index'),
            };

            return redirect()->intended($home)
                ->with('status', "Signed in as {$user->name} ({$user->role}).");
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:180|unique:users,email',
            'phone' => 'nullable|string|max:64',
            'job_title' => 'nullable|string|max:120',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => 'client',
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? 'Business Owner',
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        AuditLog::record('auth.registered', null, null, ['email' => $user->email]);

        return redirect()->route('portal.index')->with('status', 'Client account created. You can now manage multiple businesses and track diagnostic progress.');
    }

    /**
     * Quick role switch for testing the Consultant Review Mode, Admin Framework Editor,
     * and Multi-Company Client Account seamlessly.
     */
    public function quickSwitch(Request $request)
    {
        $role = $request->input('role', 'consultant');
        $email = match ($role) {
            'developer' => 'dev@bridge-tailor.com',
            'admin' => 'admin@bridge-tailor.com',
            'client' => 'kwame@voltaagro.com.gh',
            default => 'consult@bridge-tailor.com',
        };

        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user);
            $request->session()->regenerate();
            AuditLog::record('auth.role_switch', null, null, ['switched_to' => $user->email]);

            return redirect($user->isConsultant() ? route('consultant.dashboard') : route('portal.index'))
                ->with('status', "Switched active session to {$user->name} ({$user->role}).");
        }

        return back();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('diagnostic.landing')->with('status', 'Signed out.');
    }
}
