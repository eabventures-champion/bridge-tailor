@extends('layouts.app')

@section('title', 'Multi-Company Client Account & Action Tracker')

@section('content')
<!-- Section 1: Solid Espresso Header & Scalable Multi-Company Portfolio Switcher -->
<section class="sec-espresso">
    <div class="bt-container">
        <div class="portal-hero-row">
            <div class="portal-hero-content">
                <div class="eyebrow portal-eyebrow">MULTI-COMPANY ACCOUNT · HISTORICAL TRACKING · APPLY &amp; TRACK</div>
                <h1 class="portal-title">Enterprise Portfolio &amp; Implementation Workspace</h1>
                <div class="portal-subtitle">
                    Manage multiple businesses under one consulting account, compare historical diagnostic runs, and log implementation progress (-1 to +2 delta).
                </div>
            </div>

            <!-- Scalable Enterprise Selector (Works for 3 or 300+ Businesses) -->
            <div class="portal-switcher-controls">
                <div class="portal-active-select-box">
                    <span class="portal-active-select-label">Active Business:</span>
                    <select onchange="if(this.value) window.location.href=this.value;"
                            aria-label="Select active business workspace"
                            class="portal-active-select">
                        @foreach($companies as $comp)
                            @php
                                $latestScore = $comp->assessments->first()?->health_index;
                            @endphp
                            <option value="{{ route('portal.index', ['company' => $comp->ulid]) }}"
                                    {{ $activeCompany && $activeCompany->id === $comp->id ? 'selected' : '' }}>
                                {{ $comp->name }} ({{ $comp->assessments->count() }} {{ Str::plural('run', $comp->assessments->count()) }}{{ $latestScore !== null ? " · {$latestScore}/100" : '' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="button"
                        class="btn btn-amber btn-sm portal-search-btn"
                        onclick="const d=document.getElementById('portfolio-directory-drawer'); d.style.display = d.style.display === 'none' ? 'block' : 'none';">
                    ⇄ Search / Browse All ({{ $companies->count() }})
                </button>
            </div>
        </div>

        <!-- Collapsible Searchable Enterprise Directory Drawer (Handles Any Number of Businesses Cleanly) -->
        <div id="portfolio-directory-drawer" style="display:none;margin-top:14px;border-top:1px solid #443e3a;padding-top:14px;">
            <div class="portal-dir-filter-grid">
                <input type="text"
                       id="portfolio-search-input"
                       oninput="filterPortfolioDirectory()"
                       placeholder="Search businesses by company name, contact person, email, or city..."
                       style="width:100%;background:#141210;color:#fff;border:1px solid #5c544f;padding:7px 11px;font-family:inherit;font-size:12.5px;">
                <select id="portfolio-sector-filter"
                        onchange="filterPortfolioDirectory()"
                        style="width:100%;background:#141210;color:#fff;border:1px solid #5c544f;padding:7px 10px;font-family:inherit;font-size:12.5px;">
                    <option value="">All Sectors ({{ $companies->count() }})</option>
                    @foreach($fw->sectors() as $sKey => $sLabel)
                        <option value="{{ strtolower($sKey) }}">{{ $sLabel }}</option>
                    @endforeach
                </select>
                <div style="font-size:11px;color:#cac4c4;font-weight:700;padding:0 6px;">
                    Showing <span id="portfolio-visible-count">{{ $companies->count() }}</span> of {{ $companies->count() }}
                </div>
            </div>

            <div style="max-height:290px;overflow-y:auto;overflow-x:auto;border:1px solid #443e3a;">
                <table class="bt-table" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Sector &amp; Stage</th>
                            <th>Primary Contact</th>
                            <th>Diagnostic Runs</th>
                            <th>Latest Health Score</th>
                            <th>Workspace</th>
                        </tr>
                    </thead>
                    <tbody id="portfolio-directory-tbody">
                        @foreach($companies as $comp)
                            @php
                                $latestRun = $comp->assessments->first();
                                $isCurrent = $activeCompany && $activeCompany->id === $comp->id;
                                $searchBlob = strtolower($comp->name.' '.$comp->contact_name.' '.$comp->contact_email.' '.$comp->city.' '.$comp->country.' '.($fw->sectors()[$comp->sector] ?? $comp->sector));
                            @endphp
                            <tr class="portfolio-dir-row"
                                data-search="{{ $searchBlob }}"
                                data-sector="{{ strtolower($comp->sector) }}"
                                style="{{ $isCurrent ? 'outline:2px solid var(--bt-amber);' : '' }}">
                                <td style="font-weight:800;color:var(--bt-ink);">
                                    {{ $comp->name }}
                                    @if($isCurrent)
                                        <span class="tag tag-amber" style="margin-left:4px;">ACTIVE</span>
                                    @endif
                                </td>
                                <td style="font-size:11.5px;color:#333;">
                                    <strong>{{ $fw->sectors()[$comp->sector] ?? $comp->sector }}</strong> · {{ $comp->stage }}
                                </td>
                                <td style="font-size:11.5px;color:#333;">
                                    {{ $comp->contact_name }} ({{ $comp->contact_email }})
                                </td>
                                <td style="font-size:11.5px;font-weight:700;color:var(--bt-ink);">
                                    {{ $comp->assessments->count() }} {{ Str::plural('run', $comp->assessments->count()) }}
                                </td>
                                <td>
                                    @if($latestRun && $latestRun->health_index !== null)
                                        <span class="tag {{ $latestRun->health_index >= 70 ? 'tag-ok' : ($latestRun->health_index >= 50 ? 'tag-amber' : 'tag-high') }}">
                                            {{ $latestRun->health_index }}/100 · {{ $latestRun->health_band }}
                                        </span>
                                    @else
                                        <span class="tag tag-medium">No runs yet</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('portal.index', ['company' => $comp->ulid]) }}"
                                       class="btn btn-sm {{ $isCurrent ? 'btn-amber' : '' }}" style="width:100%;">
                                        {{ $isCurrent ? 'Selected' : 'Switch →' }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<script>
    function filterPortfolioDirectory() {
        const q = (document.getElementById('portfolio-search-input')?.value || '').trim().toLowerCase();
        const sec = (document.getElementById('portfolio-sector-filter')?.value || '').trim().toLowerCase();
        let visible = 0;

        document.querySelectorAll('.portfolio-dir-row').forEach(function (row) {
            const hay = row.getAttribute('data-search') || '';
            const rowSec = row.getAttribute('data-sector') || '';
            const matchQ = !q || hay.indexOf(q) !== -1;
            const matchSec = !sec || rowSec === sec;

            if (matchQ && matchSec) {
                row.style.display = '';
                visible++;
            } else {
                row.style.display = 'none';
            }
        });

        const countEl = document.getElementById('portfolio-visible-count');
        if (countEl) countEl.textContent = visible;
    }
</script>

@if($activeCompany)
    <!-- Section 2: Solid Cream Band — Active Company Profile & Historical Diagnostic Trajectory -->
    <section class="sec-cream">
        <div class="bt-container">
            <div class="sec-header">
                <div>
                    <div class="eyebrow">ACTIVE COMPANY WORKSPACE · {{ strtoupper($activeCompany->country) }}</div>
                    <h2 style="font-size:24px;">{{ $activeCompany->name }}</h2>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <a href="{{ route('diagnostic.wizard', ['company' => $activeCompany->ulid]) }}" class="btn btn-amber btn-sm">
                        + Run New Diagnostic for {{ $activeCompany->name }} →
                    </a>
                </div>
            </div>

            <div class="flat-grid-3" style="margin-bottom:14px;">
                <div class="flat-box">
                    <div class="eyebrow">COMPANY PROFILE &amp; RETENTION POLICY</div>
                    <div style="font-size:12.5px;line-height:1.6;">
                        <div><strong>Sector:</strong> {{ $fw->sectors()[$activeCompany->sector] ?? $activeCompany->sector }}</div>
                        <div><strong>Stage:</strong> {{ $fw->stages()[$activeCompany->stage] ?? $activeCompany->stage }}</div>
                        <div><strong>Size / Revenue:</strong> {{ $fw->get("size_bands.{$activeCompany->size_band}", $activeCompany->size_band) }} · {{ $fw->get("revenue_bands.{$activeCompany->revenue_band}", $activeCompany->revenue_band) }}</div>
                        <div><strong>Primary Contact:</strong> {{ $activeCompany->contact_name }} ({{ $activeCompany->contact_email }})</div>
                        <div><strong>Retention Policy:</strong> <span class="tag tag-solid">{{ strtoupper(str_replace('_', ' ', $activeCompany->retention_policy)) }}</span></div>
                    </div>
                </div>

                <div class="flat-box">
                    <div class="eyebrow">STATED OBJECTIVES &amp; CHALLENGES</div>
                    <div style="font-size:12px;margin-bottom:6px;">
                        <strong>Objectives:</strong> {{ $activeCompany->objectives ?: 'Not specified' }}
                    </div>
                    <div style="font-size:12px;">
                        <strong>Challenges:</strong> {{ $activeCompany->challenges ?: 'Not specified' }}
                    </div>
                </div>

                <div class="flat-box">
                    <div class="eyebrow">HISTORICAL HEALTH INDEX IMPROVEMENT</div>
                    @php
                        $orderedRuns = $activeCompany->assessments->sortBy('created_at')->values();
                        $firstRun = $orderedRuns->first();
                        $latestRun = $orderedRuns->last();
                        $lift = ($firstRun && $latestRun && $firstRun->id !== $latestRun->id)
                            ? ($latestRun->health_index - $firstRun->health_index)
                            : null;
                    @endphp
                    @if($latestRun)
                        <div style="display:flex;justify-content:space-between;align-items:baseline;">
                            <div>
                                <div style="font-size:34px;font-weight:800;line-height:1;">{{ $latestRun->health_index }}/100</div>
                                <div style="font-size:11px;font-weight:700;color:var(--bt-amber-dark);">{{ $latestRun->health_band }}</div>
                            </div>
                            @if($lift !== null)
                                <div style="text-align:right;">
                                    <span class="tag {{ $lift >= 0 ? 'tag-ok' : 'tag-high' }}" style="font-size:12px;">
                                        {{ $lift >= 0 ? "+{$lift} PTS LIFT" : "{$lift} PTS" }} SINCE BASELINE
                                    </span>
                                    <div style="font-size:10.5px;color:#555;margin-top:3px;">Baseline: {{ $firstRun->health_index }}/100 ({{ $firstRun->created_at->format('M Y') }})</div>
                                </div>
                            @endif
                        </div>
                    @else
                        <div style="font-size:12px;color:#666;">No completed diagnostic runs yet.</div>
                    @endif
                </div>
            </div>

            <!-- Historical Assessments Table -->
            <table class="bt-table">
                <thead>
                    <tr>
                        <th>Run Date</th>
                        <th>Mode</th>
                        <th>Business Health Index</th>
                        <th>Functional Module Scores (SG · FH · OE · MG · HC)</th>
                        <th>Review Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activeCompany->assessments as $ass)
                        @php $mods = $ass->score_snapshot['modules'] ?? []; @endphp
                        <tr>
                            <td style="font-weight:700;">{{ $ass->created_at->format('d M Y, H:i') }}</td>
                            <td><span class="tag tag-medium">{{ strtoupper($ass->mode) }}</span></td>
                            <td>
                                <strong style="font-size:14px;">{{ $ass->health_index ?? '—' }}/100</strong>
                                <span style="font-size:11px;color:var(--bt-amber-dark);">({{ $ass->health_band }})</span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px;flex-wrap:wrap;font-size:11.5px;font-weight:700;">
                                    @foreach(['SG', 'FH', 'OE', 'MG', 'HC'] as $mk)
                                        <span style="border:1px solid var(--bt-border);background:#fff;padding:2px 6px;">
                                            {{ $mk }}: {{ $mods[$mk]['score'] ?? '—' }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td>
                                <span class="tag {{ in_array($ass->report?->review_status, ['approved', 'published']) ? 'tag-ok' : 'tag-amber' }}">
                                    {{ strtoupper($ass->report?->review_status ?? $ass->status) }}
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:4px;">
                                    <a href="{{ route('diagnostic.report', $ass->ulid) }}" class="btn btn-sm">Open Report</a>
                                    <a href="{{ route('diagnostic.print', $ass->ulid) }}" target="_blank" class="btn btn-outline btn-sm">PDF</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No diagnostic assessments run for {{ $activeCompany->name }} yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- Section 3: Solid White Band — Apply & Track (Action Implementation Tracker from Methodology) -->
    <section class="sec-white">
        <div class="bt-container">
            <div class="sec-header">
                <div>
                    <div class="eyebrow">APPLY → IMPACT · ACTION IMPLEMENTATION TRACKER</div>
                    <h2 style="font-size:23px;">Track Recommendation Implementation &amp; Net Movement</h2>
                </div>
                <div style="font-size:11.5px;font-weight:700;color:var(--bt-umber);">
                    DELTA SCALE: -1 (SLIPPED BACK) · 0 (NO CHANGE) · +1 (SMALL PROGRESS) · +2 (STRONG PROGRESS)
                </div>
            </div>

            @forelse($activeCompany->actionItems as $item)
                <div class="flat-box" style="margin-bottom:10px;background:var(--bt-slate);">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap;margin-bottom:8px;">
                        <div>
                            <div style="display:flex;gap:6px;align-items:center;margin-bottom:3px;">
                                <span class="tag {{ $item->priority === 'Critical' ? 'tag-critical' : 'tag-amber' }}">{{ $item->priority }}</span>
                                <span class="tag tag-medium">MODULE {{ $item->module_key }}</span>
                                <span class="tag tag-solid">{{ $item->horizon }}</span>
                                <span class="tag {{ $item->status === 'completed' ? 'tag-ok' : 'tag-medium' }}">STATUS: {{ strtoupper($item->status) }}</span>
                            </div>
                            <h4 style="font-size:14.5px;">{{ $item->title }}</h4>
                            <div style="font-size:12px;color:#333;margin-top:2px;">{{ $item->intervention }}</div>
                            <div style="font-size:11px;color:var(--bt-umber);margin-top:3px;">
                                <strong>Owner:</strong> {{ $item->owner_name ?? 'Unassigned' }} ·
                                <strong>Target KPI:</strong> {{ $item->target_metric }}
                            </div>
                        </div>
                        <div style="text-align:right;background:var(--bt-espresso);color:#fff;padding:7px 12px;">
                            <div style="font-size:10px;color:var(--bt-amber);font-weight:800;text-transform:uppercase;">Net Movement</div>
                            <div style="font-size:20px;font-weight:800;">{{ $item->net_delta >= 0 ? "+{$item->net_delta}" : $item->net_delta }}</div>
                        </div>
                    </div>

                    <!-- Progress Log Entries -->
                    @if($item->logs->count())
                        <div style="margin-bottom:8px;">
                            @foreach($item->logs as $log)
                                <div style="display:flex;gap:8px;align-items:center;background:#fff;border:1px solid var(--bt-border);padding:5px 9px;margin-bottom:4px;font-size:12px;">
                                    <span class="tag {{ $log->delta > 0 ? 'tag-ok' : ($log->delta < 0 ? 'tag-high' : 'tag-medium') }}">
                                        {{ $log->delta > 0 ? "+{$log->delta}" : $log->delta }}
                                    </span>
                                    <span style="flex:1;font-weight:600;">{{ $log->note }}</span>
                                    <span style="font-size:10.5px;color:#777;">{{ $log->created_at->format('d M Y H:i') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Log New Update Form -->
                    <form action="{{ route('portal.actions.log', $item->id) }}" method="POST" class="bt-action-log-form">
                        @csrf
                        <select name="delta" style="border:1px solid #b8aea2;padding:6px 8px;font-size:12px;font-family:inherit;background:#fff;">
                            <option value="2">+2 · Strong progress</option>
                            <option value="1" selected>+1 · Small progress</option>
                            <option value="0">0 · No change</option>
                            <option value="-1">-1 · Slipped back</option>
                        </select>
                        <select name="status" style="border:1px solid #b8aea2;padding:6px 8px;font-size:12px;font-family:inherit;background:#fff;">
                            <option value="open" {{ $item->status === 'open' ? 'selected' : '' }}>Status: Open</option>
                            <option value="in_progress" {{ $item->status === 'in_progress' ? 'selected' : '' }}>Status: In Progress</option>
                            <option value="completed" {{ $item->status === 'completed' ? 'selected' : '' }}>Status: Completed</option>
                            <option value="blocked" {{ $item->status === 'blocked' ? 'selected' : '' }}>Status: Blocked</option>
                        </select>
                        <input type="text" name="note" required placeholder="Log what changed or milestone achieved..." style="border:1px solid #b8aea2;padding:6px 9px;font-size:12px;font-family:inherit;background:#fff;">
                        <button type="submit" class="btn btn-sm">Log Movement</button>
                    </form>
                </div>
            @empty
                <div class="flat-box">No action items registered yet. Run an assessment to populate recommendations.</div>
            @endforelse
        </div>
    </section>

    <!-- Section 4: Solid Slate Band — Document Retention Controls & Add Another Company -->
    <section class="sec-slate">
        <div class="bt-container">
            <div class="flat-grid-2">
                <!-- Document Vault & Retention Purge Control -->
                <div class="flat-box">
                    <div class="eyebrow">CONFIDENTIAL DOCUMENT VAULT &amp; RETENTION DELETION CONTROLS</div>
                    <h4 style="font-size:14px;margin-bottom:8px;">Uploaded Supporting Documents ({{ $activeCompany->documents->count() }})</h4>
                    <table class="bt-table">
                        <thead>
                            <tr>
                                <th>File &amp; Category</th>
                                <th>SHA-256 &amp; Retention Expiry</th>
                                <th>Status / Purge</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeCompany->documents as $doc)
                                <tr>
                                    <td>
                                        <strong>{{ $doc->original_name }}</strong>
                                        <div style="font-size:10.5px;color:#555;">{{ $doc->category }} · {{ number_format($doc->size_bytes / 1024, 1) }} KB</div>
                                    </td>
                                    <td style="font-size:11px;">
                                        <div><code>{{ substr($doc->sha256, 0, 14) }}…</code></div>
                                        <div>Expires: {{ $doc->retention_expires_at?->format('d M Y') ?? '—' }}</div>
                                    </td>
                                    <td>
                                        @if($doc->extraction_status === 'purged')
                                            <span class="tag">PURGED</span>
                                        @else
                                            <form action="{{ route('portal.documents.purge', $doc->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline btn-sm" style="color:var(--bt-danger);border-color:var(--bt-danger);">
                                                    Purge Now
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3">No documents uploaded for this company yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Add Another Company to Multi-Company Account -->
                <div class="flat-box">
                    <div class="eyebrow">MULTI-COMPANY ACCOUNT CAPABILITY</div>
                    <h4 style="font-size:14px;margin-bottom:8px;">Register Another Business Under This Account</h4>
                    <form action="{{ route('portal.companies.store') }}" method="POST">
                        @csrf
                        <div class="flat-grid-2">
                            <div class="field">
                                <label>Company Name *</label>
                                <input type="text" name="name" required placeholder="e.g. Kumasi Packaging & Logistics Ltd">
                            </div>
                            <div class="field">
                                <label>Industry Sector *</label>
                                <select name="sector">
                                    @foreach($fw->sectors() as $k => $label)
                                        <option value="{{ $k }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>Business Stage *</label>
                                <select name="stage">
                                    @foreach($fw->stages() as $k => $label)
                                        <option value="{{ $k }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>Revenue Band (GHS)</label>
                                <select name="revenue_band">
                                    @foreach($fw->get('revenue_bands', []) as $k => $label)
                                        <option value="{{ $k }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field">
                                <label>Contact Person *</label>
                                <input type="text" name="contact_name" required value="{{ $activeCompany->contact_name }}">
                            </div>
                            <div class="field">
                                <label>Contact Email *</label>
                                <input type="email" name="contact_email" required value="{{ $activeCompany->contact_email }}">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-amber btn-sm" style="width:100%;">
                            + Add Company to Portfolio
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endif
@endsection
