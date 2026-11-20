@extends('layouts.app')

@section('title', 'Consultant Desk & Client Engagement Pipeline')

@section('content')
<!-- Section 1: Solid Espresso Consultant Desk Header & KPI Strip -->
<section class="sec-espresso">
    <div class="bt-container">
        <div style="margin-bottom:18px;">
            <div class="eyebrow">BRIDGE-TAILOR CONSULTANT DESK · CLIENT MANAGEMENT &amp; REVIEW QUEUE</div>
            <h1 style="font-size:28px;color:#fff;">Diagnostic Assessments &amp; CRM Pipeline</h1>
        </div>

        <div class="bt-stats-grid bt-stats-grid-4">
            <div class="bt-stat-card dark">
                <div class="bt-stat-val" style="color:var(--bt-amber);">{{ $stats['total_assessments'] }}</div>
                <div class="bt-stat-label">Total Diagnostic Runs</div>
            </div>
            <div class="bt-stat-card dark">
                <div class="bt-stat-val">{{ $stats['avg_health_index'] }}<span style="font-size:16px;color:#a8a096;font-weight:600;">/100</span></div>
                <div class="bt-stat-label">Cohort Mean Health Index</div>
            </div>
            <div class="bt-stat-card dark">
                <div class="bt-stat-val" style="color:var(--bt-amber);">{{ $stats['pending_reviews'] }}</div>
                <div class="bt-stat-label">Reports Awaiting Consultant Sign-Off</div>
            </div>
            <div class="bt-stat-card dark">
                <div class="bt-stat-val" style="color:#7fd4a6;">{{ $stats['active_leads'] }}</div>
                <div class="bt-stat-label">Active Consulting Engagement Leads</div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid Cream Band — Client Diagnostic Queue & Consultant Review Mode Launcher -->
<section class="sec-cream">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">CONSULTANT REVIEW MODE QUEUE</div>
                <h2 style="font-size:23px;">Client Diagnostic Assessments &amp; Report Approval Status</h2>
            </div>
        </div>

        <table class="bt-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Company &amp; Contact</th>
                    <th>Sector / Stage / Revenue</th>
                    <th>Health Index</th>
                    <th>Primary Recommended Programme (HubSpot Deal)</th>
                    <th>Review Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessments as $ass)
                    @php
                        $scored = $ass->score_snapshot ?? [];
                        $firstProgKey = $scored['sequence'][0]['programme'] ?? 'performance';
                        $prog = $fw->programme($firstProgKey);
                        $rev = $ass->report?->review_status ?? 'ai_draft';
                    @endphp
                    <tr>
                        <td style="font-weight:700;">{{ $ass->created_at->format('d M Y') }}</td>
                        <td>
                            <div style="font-weight:800;">{{ $ass->company->name }}</div>
                            <div style="font-size:11px;color:#555;">{{ $ass->company->contact_name }} · {{ $ass->company->contact_email }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ $fw->sectors()[$ass->company->sector] ?? $ass->company->sector }}</strong></div>
                            <div>{{ $fw->stages()[$ass->company->stage] ?? $ass->company->stage }} · {{ $fw->get("revenue_bands.{$ass->company->revenue_band}", $ass->company->revenue_band) }}</div>
                        </td>
                        <td>
                            <div style="font-size:16px;font-weight:800;">{{ $ass->health_index }}/100</div>
                            <div style="font-size:10.5px;font-weight:700;color:var(--bt-amber-dark);">{{ $ass->health_band }}</div>
                        </td>
                        <td>
                            @if($prog)
                                <div style="font-weight:700;">{{ $prog['entry'] }}</div>
                                <div style="font-size:11px;color:var(--bt-umber);">{{ $prog['line'] }} · <strong>{{ $prog['entry_price'] }}</strong></div>
                            @endif
                        </td>
                        <td>
                            <span class="tag {{ in_array($rev, ['approved', 'published']) ? 'tag-ok' : 'tag-amber' }}">
                                {{ strtoupper($rev) }}
                            </span>
                            @if($ass->report?->reviewer)
                                <div style="font-size:10px;color:#555;margin-top:2px;">by {{ $ass->report->reviewer->name }}</div>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:4px;flex-wrap:wrap;">
                                <a href="{{ route('consultant.review', $ass->ulid) }}" class="btn btn-amber btn-sm">Review &amp; Amend</a>
                                <a href="{{ route('diagnostic.report', $ass->ulid) }}" class="btn btn-outline btn-sm">Client View</a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- Section 3: Solid White Band — Consulting Engagement Pipeline (Prospects -> Discovery -> Proposal -> Active) -->
<section class="sec-white">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">CLIENT MANAGEMENT &amp; CONSULTING CONVERSION PIPELINE</div>
                <h2 style="font-size:23px;">Consultation Requests &amp; HubSpot Deal Routing</h2>
            </div>
        </div>

        <table class="bt-table">
            <thead>
                <tr>
                    <th>Company &amp; Contact</th>
                    <th>Recommended Programme &amp; Value (GHS)</th>
                    <th>Channel &amp; Window</th>
                    <th>Client Message</th>
                    <th>Pipeline Status &amp; Consultant Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($consultations as $cr)
                    <tr>
                        <td>
                            <div style="font-weight:800;">{{ $cr->company->name }}</div>
                            <div style="font-size:11px;">{{ $cr->contact_name }} · {{ $cr->contact_email }}</div>
                            <div style="font-size:11px;color:#666;">{{ $cr->contact_phone }}</div>
                        </td>
                        <td>
                            <span class="tag tag-amber">{{ $cr->business_line }}</span>
                            <div style="font-weight:800;margin-top:2px;">{{ $cr->recommended_programme }}</div>
                            <div style="font-size:11.5px;color:var(--bt-amber-dark);font-weight:700;">{{ $cr->estimated_value_ghs }}</div>
                        </td>
                        <td style="font-size:11.5px;">
                            <div><strong>{{ strtoupper(str_replace('_', ' ', $cr->preferred_channel)) }}</strong></div>
                            <div>{{ $cr->preferred_window ?: 'Flexible' }}</div>
                        </td>
                        <td style="font-size:11.5px;">{{ $cr->client_message ?: '—' }}</td>
                        <td>
                            <form action="{{ route('consultant.consultations.status', $cr->id) }}" method="POST" style="display:flex;flex-direction:column;gap:5px;">
                                @csrf
                                <div style="display:flex;gap:5px;">
                                    <select name="status" style="border:1px solid #b8aea2;padding:4px 6px;font-size:11.5px;font-family:inherit;flex:1;">
                                        @foreach(['new' => 'New Lead', 'contacted' => 'Contacted', 'discovery_booked' => 'Discovery Booked', 'proposal_sent' => 'Proposal Sent', 'active_engagement' => 'Active Engagement', 'closed' => 'Closed'] as $stK => $stL)
                                            <option value="{{ $stK }}" {{ $cr->status === $stK ? 'selected' : '' }}>{{ $stL }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm">Save</button>
                                </div>
                                <input type="text" name="internal_notes" value="{{ $cr->internal_notes }}" placeholder="Internal consultant notes..." style="border:1px solid #b8aea2;padding:4px 6px;font-size:11px;font-family:inherit;">
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5">No consultation requests logged yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
