@extends('layouts.app')

@section('title', 'My Account Profile & Security')

@section('content')
<!-- Section 1: Solid Espresso Profile Header -->
<section class="sec-espresso">
    <div class="bt-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:14px;">
                <div style="width:52px;height:52px;background:var(--bt-amber);color:var(--bt-ink);font-weight:800;font-size:20px;display:flex;align-items:center;justify-content:center;border:2px solid #fff;flex-shrink:0;">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="eyebrow">USER PROFILE &amp; ACCOUNT SECURITY · {{ strtoupper($user->role) }} ACCOUNT</div>
                    <h1 style="font-size:28px;color:#fff;">{{ $user->name }}</h1>
                    <div style="font-size:12.5px;color:#d6cdc2;margin-top:2px;">
                        {{ $user->job_title ?: ucfirst($user->role) }} · {{ $user->email }}
                        @if($user->phone) · {{ $user->phone }} @endif
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <span class="tag tag-amber" style="padding:5px 10px;font-size:11px;">ROLE: {{ strtoupper($user->role) }}</span>
                @if($user->isDeveloper())
                    <a href="{{ route('developer.console') }}" class="btn btn-amber btn-sm">Open Developer Console →</a>
                @elseif($user->isAdmin())
                    @if(\App\Models\PlatformSetting::visible('privilege.admin_console_access', true))
                        <a href="{{ route('admin.console') }}" class="btn btn-amber btn-sm">Open Admin Console →</a>
                    @else
                        <a href="{{ route('consultant.dashboard') }}" class="btn btn-amber btn-sm">Open Consultant Desk →</a>
                    @endif
                @elseif($user->isConsultant())
                    <a href="{{ route('consultant.dashboard') }}" class="btn btn-amber btn-sm">Open Consultant Desk →</a>
                @else
                    <a href="{{ route('portal.index') }}" class="btn btn-amber btn-sm">Open My Client Workspace →</a>
                @endif
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid Cream Band — Edit Personal Details & Change Password -->
<section class="sec-cream">
    <div class="bt-container">
        <div class="flat-grid-2">
            <!-- Update Profile Information -->
            <div class="flat-box">
                <div class="eyebrow">01 · PERSONAL &amp; PROFESSIONAL IDENTITY</div>
                <h3 style="font-size:17px;margin-bottom:10px;">Update Your Profile Details</h3>
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="field">
                        <label>Full Name *</label>
                        <input type="text" name="name" required value="{{ old('name', $user->name) }}">
                    </div>
                    <div class="field">
                        <label>Email Address *</label>
                        <input type="email" name="email" required value="{{ old('email', $user->email) }}">
                    </div>
                    <div class="flat-grid-2">
                        <div class="field">
                            <label>Phone / WhatsApp</label>
                            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+233 ...">
                        </div>
                        <div class="field">
                            <label>Job Title / Designation</label>
                            <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" placeholder="e.g. Founder & CEO">
                        </div>
                    </div>
                    <div class="field">
                        <label>Assigned Platform Role (Managed by Administrator / Developer)</label>
                        <input type="text" value="{{ strtoupper($user->role) }}" disabled style="background:#eee8e0;font-weight:700;color:#555;">
                    </div>
                    <button type="submit" class="btn btn-amber" style="width:100%;">Save Profile Changes →</button>
                </form>
            </div>

            <!-- Update Password with Show/Hide Toggles -->
            <div class="flat-box">
                <div class="eyebrow">02 · CREDENTIALS &amp; PASSWORD SECURITY</div>
                <h3 style="font-size:17px;margin-bottom:10px;">Change Your Password</h3>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="field">
                        <label>Current Password *</label>
                        <input type="password" name="current_password" required placeholder="Enter current password">
                    </div>
                    <div class="field">
                        <label>New Password (minimum 8 characters) *</label>
                        <input type="password" name="password" required placeholder="Enter new password">
                    </div>
                    <div class="field">
                        <label>Confirm New Password *</label>
                        <input type="password" name="password_confirmation" required placeholder="Re-enter new password">
                    </div>
                    <button type="submit" class="btn" style="width:100%;">Update Password →</button>
                </form>

                <div style="margin-top:12px;padding:10px 12px;background:var(--bt-slate);border:1px solid var(--bt-border);font-size:11.5px;">
                    <div style="font-weight:800;text-transform:uppercase;font-size:10px;color:var(--bt-umber);margin-bottom:3px;">Account Metadata</div>
                    <div><strong>User ID:</strong> #{{ $user->id }} · <strong>Member Since:</strong> {{ $user->created_at?->format('d M Y') }}</div>
                    <div><strong>Last Updated:</strong> {{ $user->updated_at?->format('d M Y, H:i') }}</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Solid White Band — Role-Specific Portfolio Summary & Recent Activity -->
<section class="sec-white">
    <div class="bt-container">
        <div class="flat-grid-2">
            <!-- Linked Companies / Assigned Portfolio -->
            <div class="flat-box">
                <div class="eyebrow">03 · LINKED ENTERPRISES &amp; WORKSPACE ACCESS</div>
                @if($user->role === 'client')
                    <h3 style="font-size:16px;margin-bottom:8px;">Companies Under Your Account ({{ $user->ownedCompanies->count() }})</h3>
                    <table class="bt-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Sector &amp; Stage</th>
                                <th>Latest Health Index</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->ownedCompanies as $comp)
                                @php $latest = $comp->assessments->first(); @endphp
                                <tr>
                                    <td style="font-weight:700;">{{ $comp->name }}</td>
                                    <td>{{ $fw->sectors()[$comp->sector] ?? $comp->sector }} · {{ $comp->stage }}</td>
                                    <td><strong>{{ $latest?->health_index ?? '—' }}/100</strong></td>
                                    <td><a href="{{ route('portal.index', ['company' => $comp->ulid]) }}" class="btn btn-sm">Open</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4">No companies registered under your account yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <h3 style="font-size:16px;margin-bottom:8px;">Assigned Consulting Portfolio ({{ $user->assignedCompanies->count() }})</h3>
                    <table class="bt-table">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Contact</th>
                                <th>Diagnostic Runs</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($user->assignedCompanies as $comp)
                                <tr>
                                    <td style="font-weight:700;">{{ $comp->name }}</td>
                                    <td>{{ $comp->contact_name }} ({{ $comp->contact_email }})</td>
                                    <td>{{ $comp->assessments->count() }} run(s)</td>
                                    <td><a href="{{ route('portal.index', ['company' => $comp->ulid]) }}" class="btn btn-sm">Inspect</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4">You have full {{ strtoupper($user->role) }} workspace access across the platform.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            <!-- Personal Security & Activity Audit Log -->
            <div class="flat-box">
                <div class="eyebrow">04 · YOUR RECENT ACCOUNT ACTIVITY LOG</div>
                <h3 style="font-size:16px;margin-bottom:8px;">Recent Actions &amp; Security Events</h3>
                <table class="bt-table">
                    <thead>
                        <tr>
                            <th>Date / Time</th>
                            <th>Event</th>
                            <th>IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentLogs as $log)
                            <tr>
                                <td style="font-size:11px;">{{ $log->created_at->format('d M Y, H:i') }}</td>
                                <td><code style="font-weight:700;color:var(--bt-amber-dark);">{{ $log->event }}</code></td>
                                <td style="font-size:11px;">{{ $log->ip_address }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No recent activity logged yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
