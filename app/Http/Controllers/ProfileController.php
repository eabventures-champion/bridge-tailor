<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\FrameworkVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $user->load(['ownedCompanies.assessments', 'companies', 'assignedCompanies.assessments']);

        $recentLogs = AuditLog::where('user_id', $user->id)
            ->latest('id')
            ->limit(15)
            ->get();

        return view('profile.show', [
            'user' => $user,
            'fw' => FrameworkVersion::active(),
            'recentLogs' => $recentLogs,
        ]);
    }

    public function updateDetails(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:180|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:64',
            'job_title' => 'nullable|string|max:120',
        ]);

        $user->update($data);

        AuditLog::record('profile.details_updated', null, null, [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return back()->with('status', 'Your profile details have been updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'The current password you entered is incorrect.']);
        }

        $user->update([
            'password' => Hash::make($data['password']),
        ]);

        AuditLog::record('profile.password_changed', null, null, ['role' => $user->role]);

        return back()->with('status', 'Your password has been updated securely.');
    }
}
