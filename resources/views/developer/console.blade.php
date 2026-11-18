@extends('layouts.app')

@section('title', ($isDeveloper ? 'Developer Master Console' : 'Admin Console') . ' — Dynamic Text, Visibility & User Directory')

@section('content')
<!-- Section 1: Solid Espresso Console Header & Master KPI Strip -->
<section class="sec-espresso">
    <div class="bt-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin-bottom:14px;">
            <div>
                <div class="eyebrow">{{ $isDeveloper ? 'DEVELOPER MASTER CONSOLE · UNRESTRICTED PLATFORM CONTROL' : 'BRIDGE-TAILOR ADMIN CONSOLE · PLATFORM & USER MANAGEMENT' }}</div>
                <h1 style="font-size:28px;color:#fff;">Dynamic UI Text, Component Visibility &amp; User Directory</h1>
                <div style="font-size:12.5px;color:#d6cdc2;margin-top:4px;">
                    Edit any platform text dynamically, show or hide sections/cards in real time, and inspect or manage {{ $isDeveloper ? 'every Client, Consultant, Admin, and Developer' : 'Client, Consultant, and Admin' }} account.
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <form action="{{ route('developer.reset') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="background:#141210;color:#fff;border-color:#786e67;">
                        ↺ Reset All Text &amp; Visibility to Defaults
                    </button>
                </form>
                <a href="{{ route('consultant.framework') }}" class="btn btn-amber btn-sm">Framework JSON Engine →</a>
            </div>
        </div>

        <div class="flat-grid-5" style="border-color:#443e3a;background:#1c1917;">
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:var(--bt-amber);">{{ $stats['users_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">
                    Users ({{ $stats['clients_count'] }} Client · {{ $stats['consultants_count'] }} Consult · {{ $stats['admins_count'] }} {{ $isDeveloper ? 'Admin/Dev' : 'Admin' }})
                </div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:#fff;">{{ $stats['companies_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">Registered Client Companies</div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:#fff;">{{ $stats['assessments_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">Total Diagnostic Assessments</div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid #443e3a;">
                <div style="font-size:22px;font-weight:800;color:#7fd4a6;">{{ $stats['visible_components'] }} / {{ $stats['total_components'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">UI Components Currently Visible</div>
            </div>
            <div style="padding:10px 14px;">
                <div style="font-size:22px;font-weight:800;color:var(--bt-amber);">{{ $stats['consultations_total'] }} · {{ $stats['actions_total'] }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:#cac4c4;">CRM Leads &amp; Tracked Actions</div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid Cream Band — Show / Hide Platform Components & Cards -->
<section class="sec-cream" id="dev-visibility">
    <div class="bt-container">
        <form action="{{ route('developer.visibility.update') }}" method="POST">
            @csrf
            <div class="sec-header">
                <div>
                    <div class="eyebrow">01 · DYNAMIC COMPONENT &amp; CARD VISIBILITY CONTROLLER</div>
                    <h2 style="font-size:23px;">Show or Hide Sections, Cards &amp; UI Modules Across the Platform</h2>
                </div>
                <button type="submit" class="btn btn-amber">Save Component Visibility Toggles →</button>
            </div>

            <div class="flat-grid-2">
                @foreach($visibilitySettings as $key => $item)
                    @php
                        $isVisible = in_array((string) $item['current'], ['1', 'true', 'yes', 'on'], true);
                    @endphp
                    <label class="flat-box" style="display:flex;justify-content:space-between;align-items:center;gap:12px;cursor:pointer;background:{{ $isVisible ? 'var(--bt-white)' : '#efe8df' }};border-left:4px solid {{ $isVisible ? 'var(--bt-teal)' : '#9E2A1C' }};">
                        <div>
                            <div style="font-weight:700;font-size:13px;">{{ $item['label'] }}</div>
                            <div style="font-size:10.5px;color:#666;margin-top:2px;">Key: <code>{{ $key }}</code></div>
                        </div>
                        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
                            <span class="tag {{ $isVisible ? 'tag-ok' : 'tag-high' }}">{{ $isVisible ? 'VISIBLE' : 'HIDDEN' }}</span>
                            <input type="checkbox" name="vis[{{ $key }}]" value="1" {{ $isVisible ? 'checked' : '' }} style="width:18px;height:18px;cursor:pointer;">
                        </div>
                    </label>
                @endforeach
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                <button type="submit" class="btn btn-amber">Apply Show / Hide Settings →</button>
            </div>
        </form>
    </div>
</section>

<!-- Section 3: Solid White Band — Dynamic Text & Copy CMS Editor -->
<section class="sec-white" id="dev-text">
    <div class="bt-container">
        <form action="{{ route('developer.text.update') }}" method="POST">
            @csrf
            <div class="sec-header">
                <div>
                    <div class="eyebrow">02 · DYNAMIC PLATFORM TEXT &amp; HEADLINE CMS</div>
                    <h2 style="font-size:23px;">Dynamically Edit Platform Headlines, Labels, Callouts &amp; Copy</h2>
                </div>
                <button type="submit" class="btn btn-amber">Save All Text Overrides →</button>
            </div>

            <div class="flat-grid-2">
                @foreach($textSettings as $key => $item)
                    <div class="flat-box" style="background:var(--bt-slate);">
                        <div class="field" style="margin-bottom:0;">
                            <label>{{ $item['label'] }} <span style="font-weight:400;opacity:0.7;">({{ $key }})</span></label>
                            @if(strlen((string) $item['current']) > 110)
                                <textarea name="texts[{{ $key }}]" rows="3">{{ $item['current'] }}</textarea>
                            @else
                                <input type="text" name="texts[{{ $key }}]" value="{{ $item['current'] }}">
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div style="display:flex;justify-content:flex-end;margin-top:12px;">
                <button type="submit" class="btn btn-amber">Publish Dynamic Text Changes →</button>
            </div>
        </form>
    </div>
</section>

<!-- Section 4: Solid Slate Band — User Directory -->
<section class="sec-slate" id="dev-users">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">03 · USER DIRECTORY — {{ $isDeveloper ? 'CLIENTS, CONSULTANTS, ADMINS & DEVELOPERS' : 'CLIENTS, CONSULTANTS & ADMINS' }}</div>
                <h2 style="font-size:23px;">Inspect &amp; Edit Every User Account, Role, Contact Detail &amp; Password</h2>
            </div>
        </div>

        <table class="bt-table" style="margin-bottom:16px;">
            <thead>
                <tr>
                    <th style="width:5%;">ID</th>
                    <th style="width:12%;">Role</th>
                    <th style="width:28%;">User Details (Editable Inline)</th>
                    <th style="width:24%;">Associated Companies &amp; Activity</th>
                    <th style="width:31%;">Role, Title &amp; Password Reset</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $u)
                    <tr>
                        <td style="font-weight:800;">#{{ $u->id }}</td>
                        <td>
                            <span class="tag {{ $u->role === 'developer' ? 'tag-amber' : ($u->role === 'admin' ? 'tag-critical' : ($u->role === 'consultant' ? 'tag-solid' : 'tag-ok')) }}">
                                {{ strtoupper($u->role) }}
                            </span>
                            <div style="font-size:10px;color:#555;margin-top:4px;">Created {{ $u->created_at?->format('d M Y') }}</div>
                        </td>
                        <td colspan="3">
                            <form action="{{ route('developer.users.update', $u->id) }}" method="POST" class="bt-dev-user-form">
                                @csrf
                                <div>
                                    <div class="field" style="margin-bottom:6px;">
                                        <label>Full Name</label>
                                        <input type="text" name="name" value="{{ $u->name }}" required>
                                    </div>
                                    <div class="field" style="margin-bottom:6px;">
                                        <label>Email Address</label>
                                        <input type="email" name="email" value="{{ $u->email }}" required>
                                    </div>
                                    <div class="field" style="margin-bottom:0;">
                                        <label>Phone</label>
                                        <input type="text" name="phone" value="{{ $u->phone }}" placeholder="+233 ...">
                                    </div>
                                </div>

                                <div style="font-size:11.5px;background:#fff;border:1px solid var(--bt-border);padding:8px 10px;">
                                    <div style="font-weight:800;text-transform:uppercase;font-size:10px;color:var(--bt-umber);margin-bottom:4px;">Linked Enterprises</div>
                                    @if($u->role === 'client')
                                        @forelse($u->ownedCompanies as $comp)
                                            <div style="margin-bottom:3px;">
                                                ■ <a href="{{ route('portal.index', ['company' => $comp->ulid]) }}" style="text-decoration:underline;font-weight:700;">{{ $comp->name }}</a>
                                                ({{ $comp->sector }} · {{ $comp->stage }})
                                            </div>
                                        @empty
                                            <div style="color:#777;">No owned companies yet.</div>
                                        @endforelse
                                    @elseif($u->isConsultant())
                                        <div>Assigned Companies: <strong>{{ $u->assignedCompanies->count() }}</strong></div>
                                        @foreach($u->assignedCompanies as $ac)
                                            <div style="margin-top:2px;">■ {{ $ac->name }}</div>
                                        @endforeach
                                    @endif
                                </div>

                                <div>
                                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;margin-bottom:6px;">
                                        <div class="field" style="margin-bottom:0;">
                                            <label>Role</label>
                                            <select name="role">
                                                @foreach($availableRoles as $rK => $rL)
                                                    <option value="{{ $rK }}" {{ $u->role === $rK ? 'selected' : '' }}>{{ $rL }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="field" style="margin-bottom:0;">
                                            <label>Job Title</label>
                                            <input type="text" name="job_title" value="{{ $u->job_title }}">
                                        </div>
                                    </div>
                                    <div class="field" style="margin-bottom:6px;">
                                        <label>Set New Password (leave blank to keep current)</label>
                                        <input type="password" name="new_password" placeholder="New password...">
                                    </div>
                                    <button type="submit" class="btn btn-sm" style="width:100%;">Update {{ $u->name }} →</button>
                                </div>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Create New User -->
        <div class="flat-box">
            <div class="eyebrow">CREATE NEW PLATFORM USER ({{ $isDeveloper ? 'CLIENT, CONSULTANT, ADMIN, OR DEVELOPER' : 'CLIENT, CONSULTANT, OR ADMIN' }})</div>
            <form action="{{ route('developer.users.store') }}" method="POST">
                @csrf
                <div class="flat-grid-3">
                    <div class="field">
                        <label>Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Kofi Boateng">
                    </div>
                    <div class="field">
                        <label>Email Address *</label>
                        <input type="email" name="email" required placeholder="kofi@bridge-tailor.com">
                    </div>
                    <div class="field">
                        <label>Assign Role *</label>
                        <select name="role">
                            <option value="client">Client (Multi-Company Account)</option>
                            <option value="consultant">Consultant (Review Desk &amp; CRM)</option>
                            <option value="admin">Admin (Admin Console + Framework Config)</option>
                            @if($isDeveloper)
                                <option value="developer">Developer (Full Master Console)</option>
                            @endif
                        </select>
                    </div>
                    <div class="field">
                        <label>Job Title</label>
                        <input type="text" name="job_title" placeholder="e.g. Senior Consultant">
                    </div>
                    <div class="field">
                        <label>Phone</label>
                        <input type="text" name="phone" placeholder="+233 ...">
                    </div>
                    <div class="field">
                        <label>Initial Password *</label>
                        <input type="password" name="password" required placeholder="Minimum 6 characters">
                    </div>
                </div>
                <button type="submit" class="btn btn-amber btn-sm">+ Create Platform User Account</button>
            </form>
        </div>
    </div>
</section>

<!-- Section 5: Solid Cream Band — All Registered Companies, Manual Consultant Assignment & Automated Routing Engine -->
<section class="sec-cream" id="dev-enterprises">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">04 · ALL ENTERPRISES, CONSULTANT ASSIGNMENT &amp; AUTOMATED ROUTING</div>
                <h2 style="font-size:22px;">Complete Enterprise Directory, Manual Assignment &amp; Auto-Routing Engine</h2>
            </div>
            @if($unassignedCompaniesCount > 0)
                <form action="{{ route('developer.companies.auto_assign_all') }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-amber btn-sm">
                        ⚡ Auto-Assign {{ $unassignedCompaniesCount }} Unassigned {{ Str::plural('Company', $unassignedCompaniesCount) }} Now →
                    </button>
                </form>
            @endif
        </div>

        <!-- Automated Consultant Routing Engine Control Box -->
        <div class="flat-box" style="margin-bottom:14px;background:var(--bt-white);border-left:5px solid {{ $autoAssignEnabled ? 'var(--bt-teal)' : 'var(--bt-amber)' }};">
            <form action="{{ route('developer.assignment.settings') }}" method="POST">
                @csrf
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:14px;flex-wrap:wrap;margin-bottom:10px;">
                    <div>
                        <div class="eyebrow">AUTOMATED CONSULTANT ROUTING ENGINE (WHEN ADMIN IS UNAVAILABLE)</div>
                        <h3 style="font-size:16px;">Automatic Client-to-Consultant Assignment Configuration</h3>
                        <p style="font-size:12px;color:#443e3a;margin:3px 0 0;">
                            When enabled, every new business that completes a diagnostic assessment or books a consultation is automatically assigned to a consultant immediately, even when an Administrator is offline.
                        </p>
                    </div>
                    <span class="tag {{ $autoAssignEnabled ? 'tag-ok' : 'tag-medium' }}" style="font-size:11px;padding:4px 9px;">
                        {{ $autoAssignEnabled ? '● AUTO-ASSIGNMENT ACTIVE' : '○ MANUAL ASSIGNMENT ONLY' }}
                    </span>
                </div>

                <div class="flat-grid-3" style="align-items:end;">
                    <div class="field" style="margin-bottom:0;">
                        <label>Automation Mode</label>
                        <select name="auto_assign_enabled">
                            <option value="1" {{ $autoAssignEnabled ? 'selected' : '' }}>Enabled — Automatically assign incoming businesses</option>
                            <option value="0" {{ !$autoAssignEnabled ? 'selected' : '' }}>Disabled — Wait for Admin to assign manually</option>
                        </select>
                    </div>
                    <div class="field" style="margin-bottom:0;">
                        <label>Routing Algorithm</label>
                        <select name="auto_assign_strategy">
                            <option value="least_loaded" {{ $autoAssignStrategy === 'least_loaded' ? 'selected' : '' }}>Workload-Balanced (Assign to consultant with fewest clients)</option>
                            <option value="round_robin" {{ $autoAssignStrategy === 'round_robin' ? 'selected' : '' }}>Round-Robin Rotation (Rotate evenly in sequence)</option>
                        </select>
                    </div>
                    <div style="display:flex;flex-direction:column;gap:6px;">
                        @if($unassignedCompaniesCount > 0)
                            <label style="font-size:11.5px;font-weight:700;display:inline-flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="assign_existing_unassigned" value="1" checked>
                                Also auto-assign {{ $unassignedCompaniesCount }} currently unassigned {{ Str::plural('company', $unassignedCompaniesCount) }}
                            </label>
                        @endif
                        <button type="submit" class="btn btn-sm" style="width:100%;">Save Auto-Assignment Rules →</button>
                    </div>
                </div>
            </form>
        </div>

        <table class="bt-table">
            <thead>
                <tr>
                    <th style="width:20%;">Company Name</th>
                    <th style="width:18%;">Sector / Stage / Revenue</th>
                    <th style="width:20%;">Client Owner &amp; Contact</th>
                    <th style="width:20%;">Assigned Consultant (Admin Assign)</th>
                    <th style="width:10%;">Activity</th>
                    <th style="width:12%;">Workspace Links</th>
                </tr>
            </thead>
            <tbody>
                @foreach($companies as $comp)
                    @php $latestAss = $comp->assessments()->latest('id')->first(); @endphp
                    <tr>
                        <td style="font-weight:800;">{{ $comp->name }}</td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ $fw->sectors()[$comp->sector] ?? $comp->sector }}</strong></div>
                            <div>{{ $fw->stages()[$comp->stage] ?? $comp->stage }} · {{ $fw->get("revenue_bands.{$comp->revenue_band}", $comp->revenue_band) }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ $comp->contact_name }}</strong> ({{ $comp->contact_email }})</div>
                            <div>{{ $comp->contact_phone }} · Owner: {{ $comp->owner?->name ?? 'Unassigned' }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <form action="{{ route('developer.companies.assign', $comp->id) }}" method="POST" style="display:flex;gap:4px;align-items:center;margin:0;">
                                @csrf
                                <select name="assigned_consultant_id" style="border:1px solid #b8aea2;padding:5px 7px;font-size:11.5px;font-family:inherit;background:#fff;flex:1;font-weight:700;">
                                    <option value="">— Unassigned —</option>
                                    @foreach($assignableConsultants as $cons)
                                        <option value="{{ $cons->id }}" {{ (int)$comp->assigned_consultant_id === $cons->id ? 'selected' : '' }}>
                                            {{ $cons->name }} ({{ ucfirst($cons->role) }} · {{ $cons->assigned_companies_count }} clients)
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm">Assign</button>
                            </form>
                            @if($comp->consultant)
                                <div style="margin-top:3px;font-size:10.5px;color:var(--bt-success);font-weight:700;">
                                    ■ Assigned to {{ $comp->consultant->name }}
                                </div>
                            @else
                                <div style="margin-top:3px;font-size:10.5px;color:var(--bt-danger);font-weight:700;">
                                    ▲ Unassigned — select above or click Auto-Assign
                                </div>
                            @endif
                        </td>
                        <td style="font-size:11.5px;font-weight:700;">
                            {{ $comp->assessments_count }} runs · {{ $comp->documents_count }} docs · {{ $comp->action_items_count }} actions
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                <a href="{{ route('portal.index', ['company' => $comp->ulid]) }}" class="btn btn-sm">Client Portal</a>
                                @if($latestAss)
                                    <a href="{{ route('diagnostic.report', $latestAss->ulid) }}" class="btn btn-outline btn-sm">Report ({{ $latestAss->health_index }}/100)</a>
                                    <a href="{{ route('consultant.review', $latestAss->ulid) }}" class="btn btn-amber btn-sm">Review Mode</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
