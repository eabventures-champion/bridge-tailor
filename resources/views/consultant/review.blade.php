@extends('layouts.app')

@section('title', "Consultant Review Mode — {$company->name}")

@section('content')
<!-- Section 1: Solid Espresso Header -->
<section class="sec-espresso">
    <div class="bt-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;">
            <div>
                <div class="eyebrow">CONSULTANT REVIEW MODE · HUMAN-IN-THE-LOOP QUALITY GATE</div>
                <h1 style="font-size:28px;color:#fff;">Review, Amend &amp; Approve Report: {{ $company->name }}</h1>
                <div style="font-size:12.5px;color:#d6cdc2;margin-top:4px;">
                    Health Index: <strong style="color:var(--bt-amber);">{{ $assessment->health_index }}/100 ({{ $assessment->health_band }})</strong> ·
                    AI Provider: <strong>{{ strtoupper($report?->ai_provider ?? 'framework') }} ({{ $report?->ai_model ?? 'bt-deterministic-v1' }})</strong> ·
                    Current Status: <span class="tag tag-amber">{{ strtoupper($report?->review_status ?? 'ai_draft') }}</span>
                </div>
            </div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
                <form action="{{ route('consultant.review.regenerate', $assessment->ulid) }}" method="POST" style="margin:0;">
                    @csrf
                    <button type="submit" class="btn btn-outline btn-sm" style="background:#141210;color:#fff;border-color:#666;">
                        ↻ Re-Run AI Synthesis
                    </button>
                </form>
                <a href="{{ route('diagnostic.report', $assessment->ulid) }}" class="btn btn-amber btn-sm">
                    Preview Client-Facing Report ↗
                </a>
                <a href="{{ route('consultant.dashboard') }}" class="btn btn-outline btn-sm" style="background:#2c2927;color:#fff;border-color:#666;">
                    ← Back to Desk
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid Cream Band — Internal HubSpot Deal Parameters & Evidence Verification -->
<section class="sec-cream">
    <div class="bt-container">
        <div class="flat-grid-2">
            <!-- HubSpot Deal Box (from Bridge-Tailor methodology) -->
            <div class="flat-box">
                <div class="eyebrow">LOG THIS DEAL IN HUBSPOT (INTERNAL CONSULTANT PRICING VIEW)</div>
                @php
                    $firstProgKey = $scored['sequence'][0]['programme'] ?? 'performance';
                    $firstProg = $fw->programme($firstProgKey);
                @endphp
                @if($firstProg)
                    <table class="bt-table" style="margin-top:6px;">
                        <tr>
                            <td style="font-weight:700;width:38%;">Business Line</td>
                            <td style="font-weight:800;">{{ $firstProg['line'] }} ({{ $firstProg['stage'] }})</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Entry Programme Name</td>
                            <td style="font-weight:800;color:var(--bt-amber-dark);">{{ $firstProg['entry'] }} ({{ $firstProg['entry_duration'] }})</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Programme Value (GHS)</td>
                            <td style="font-weight:800;">{{ $firstProg['entry_price'] }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Follow-On / Expansion</td>
                            <td>{{ $firstProg['next'] }} ({{ $firstProg['next_price'] }}) · {{ $firstProg['revenue_type'] }}</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;">Impact Metric Tracked</td>
                            <td>{{ $firstProg['metric'] }}</td>
                        </tr>
                    </table>
                    <div style="font-size:11px;color:#555;">Set Delivery Start Date on Closed Won in HubSpot to fire the onboarding workflow.</div>
                @endif
            </div>

            <!-- What the Client Flagged & Evidence Gaps -->
            <div class="flat-box">
                <div class="eyebrow">RAW CLIENT SYMPTOMS &amp; LOWEST-SCORING EVIDENCE ANSWERS</div>
                <div style="max-height:195px;overflow-y:auto;font-size:11.5px;">
                    @if($assessment->custom_issue)
                        <div style="background:var(--bt-amber-pale);border-left:4px solid var(--bt-ink);padding:6px 9px;margin-bottom:8px;font-style:italic;">
                            " {{ $assessment->custom_issue }} "
                        </div>
                    @endif
                    @foreach($scored['symptoms'] ?? [] as $s)
                        <div style="padding:3px 0;border-bottom:1px solid #ece4da;">
                            <span class="tag tag-amber">{{ $s['pain'] }}/5</span>
                            <strong>{{ $s['label'] }}</strong>
                        </div>
                    @endforeach
                    @foreach($scored['evidence'] ?? [] as $ev)
                        <div style="padding:4px 0;border-bottom:1px solid #ece4da;">
                            <span class="tag tag-high">{{ $ev['module'] }}</span>
                            {{ $ev['question'] }} → <strong style="color:var(--bt-amber-dark);">"{{ $ev['answer'] }}"</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Solid White Band — Consultant Amendment Form (Executive Summary, Recommendations, Priorities & Approval Gate) -->
<section class="sec-white">
    <div class="bt-container">
        <form action="{{ route('consultant.review.update', $assessment->ulid) }}" method="POST">
            @csrf
            <div class="sec-header">
                <div>
                    <div class="eyebrow">AMEND, APPROVE &amp; PUBLISH</div>
                    <h2 style="font-size:23px;">Consultant Amendments &amp; Final Sign-Off</h2>
                </div>
                <div style="display:flex;gap:8px;align-items:center;">
                    <select name="review_status" style="border:2px solid var(--bt-ink);padding:7px 12px;font-size:12px;font-weight:700;font-family:inherit;background:var(--bt-amber-pale);">
                        <option value="ai_draft" {{ $report?->review_status === 'ai_draft' ? 'selected' : '' }}>Status: AI Draft</option>
                        <option value="under_review" {{ $report?->review_status === 'under_review' ? 'selected' : '' }}>Status: Under Consultant Review</option>
                        <option value="approved" {{ $report?->review_status === 'approved' ? 'selected' : '' }}>Status: Approved by Consultant</option>
                        <option value="published" {{ $report?->review_status === 'published' ? 'selected' : '' }}>Status: Published to Client</option>
                    </select>
                    <button type="submit" class="btn btn-amber">Save Amendments &amp; Update Status →</button>
                </div>
            </div>

            <div class="flat-grid-2" style="margin-bottom:14px;">
                <div class="flat-box">
                    <div class="field" style="margin-bottom:0;">
                        <label>Executive Diagnostic Summary (Editable by Consultant)</label>
                        <textarea name="executive_summary" rows="5" required>{{ old('executive_summary', $report?->executive_summary ?? ($analysis['executive_summary'] ?? '')) }}</textarea>
                    </div>
                </div>
                <div class="flat-box">
                    <div class="field" style="margin-bottom:0;">
                        <label>Signed Consultant Commentary &amp; Engagement Notes (Displayed in Highlighted Callout on Report)</label>
                        <textarea name="consultant_notes" rows="5" placeholder="Add your senior consultant observations, fee/tier guidance, or discovery agenda...">{{ old('consultant_notes', $report?->consultant_notes ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Editable Recommendations Table -->
            <div class="eyebrow" style="margin-bottom:6px;">AMEND AI RECOMMENDATIONS BEFORE PUBLISHING</div>
            <table class="bt-table" style="margin-bottom:16px;">
                <thead>
                    <tr>
                        <th style="width:10%;">Priority</th>
                        <th style="width:8%;">Module</th>
                        <th style="width:14%;">Programme</th>
                        <th style="width:20%;">Recommendation Title</th>
                        <th style="width:28%;">Strategic Intervention</th>
                        <th style="width:20%;">Target KPI</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis['recommendations'] ?? [] as $idx => $rec)
                        <tr>
                            <td>
                                <select name="recommendations[{{ $idx }}][priority]" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                                    @foreach(['Critical', 'High', 'Medium'] as $pr)
                                        <option value="{{ $pr }}" {{ ($rec['priority'] ?? 'High') === $pr ? 'selected' : '' }}>{{ $pr }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="recommendations[{{ $idx }}][module_key]" value="{{ $rec['module_key'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;font-weight:700;">
                            </td>
                            <td>
                                <input type="text" name="recommendations[{{ $idx }}][programme_key]" value="{{ $rec['programme_key'] ?? 'performance' }}" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                            </td>
                            <td>
                                <input type="text" name="recommendations[{{ $idx }}][title]" value="{{ $rec['title'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;font-weight:700;">
                            </td>
                            <td>
                                <textarea name="recommendations[{{ $idx }}][intervention]" rows="2" style="width:100%;border:1px solid #b8aea2;padding:4px;font-family:inherit;font-size:11.5px;">{{ $rec['intervention'] }}</textarea>
                                <input type="hidden" name="recommendations[{{ $idx }}][expected_outcome]" value="{{ $rec['expected_outcome'] ?? '' }}">
                            </td>
                            <td>
                                <input type="text" name="recommendations[{{ $idx }}][target_kpi]" value="{{ $rec['target_kpi'] ?? '' }}" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Editable Consulting Priorities -->
            <div class="eyebrow" style="margin-bottom:6px;">AMEND SEQUENCED BRIDGE-TAILOR CONSULTING PRIORITIES</div>
            <table class="bt-table" style="margin-bottom:14px;">
                <thead>
                    <tr>
                        <th style="width:7%;">Rank</th>
                        <th style="width:16%;">Practice Line</th>
                        <th style="width:20%;">Entry Programme</th>
                        <th style="width:18%;">Follow-On Programme</th>
                        <th style="width:16%;">Delivery Mode</th>
                        <th style="width:23%;">Sequencing Rationale</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($analysis['consulting_priorities'] ?? [] as $idx => $cp)
                        <tr>
                            <td>
                                <input type="number" name="consulting_priorities[{{ $idx }}][rank]" value="{{ $cp['rank'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;font-weight:700;">
                            </td>
                            <td>
                                <input type="text" name="consulting_priorities[{{ $idx }}][business_line]" value="{{ $cp['business_line'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                            </td>
                            <td>
                                <input type="text" name="consulting_priorities[{{ $idx }}][entry_programme]" value="{{ $cp['entry_programme'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;font-weight:700;">
                            </td>
                            <td>
                                <input type="text" name="consulting_priorities[{{ $idx }}][expansion_programme]" value="{{ $cp['expansion_programme'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                            </td>
                            <td>
                                <input type="text" name="consulting_priorities[{{ $idx }}][delivery_mode]" value="{{ $cp['delivery_mode'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                                <input type="hidden" name="consulting_priorities[{{ $idx }}][duration]" value="{{ $cp['duration'] }}">
                            </td>
                            <td>
                                <input type="text" name="consulting_priorities[{{ $idx }}][rationale]" value="{{ $cp['rationale'] }}" style="width:100%;border:1px solid #b8aea2;padding:4px;">
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="display:flex;justify-content:flex-end;">
                <button type="submit" class="btn btn-amber">Save Consultant Review &amp; Publish Report →</button>
            </div>
        </form>
    </div>
</section>

<!-- Section 4: Solid Slate Band — Push New Action Item to Client Tracker & Assessment Audit Log -->
<section class="sec-slate">
    <div class="bt-container">
        <div class="flat-grid-2">
            <div class="flat-box">
                <div class="eyebrow">ASSIGN ACTION ITEM TO CLIENT IMPLEMENTATION TRACKER</div>
                <form action="{{ route('consultant.review.actions.add', $assessment->ulid) }}" method="POST">
                    @csrf
                    <div class="flat-grid-3">
                        <div class="field">
                            <label>Module *</label>
                            <select name="module_key">
                                @foreach($fw->modules() as $mk => $mod)
                                    <option value="{{ $mk }}">{{ $mk }} · {{ $mod['short'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Priority *</label>
                            <select name="priority">
                                <option value="Critical">Critical</option>
                                <option value="High" selected>High</option>
                                <option value="Medium">Medium</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Horizon *</label>
                            <select name="horizon">
                                <option value="0-30 days">0-30 days</option>
                                <option value="31-90 days">31-90 days</option>
                                <option value="90-180 days">90-180 days</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label>Action Item Title *</label>
                        <input type="text" name="title" required placeholder="e.g. Complete 15-day monthly close checklist & reconcile debtor ledger">
                    </div>
                    <div class="field">
                        <label>Intervention Steps</label>
                        <textarea name="intervention" rows="2"></textarea>
                    </div>
                    <div class="flat-grid-2">
                        <div class="field">
                            <label>Named Client Owner</label>
                            <input type="text" name="owner_name" value="{{ $company->contact_name }}">
                        </div>
                        <div class="field">
                            <label>Target KPI</label>
                            <input type="text" name="target_metric" placeholder="e.g. Monthly accounts closed by Day 15">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm" style="width:100%;">+ Assign Action Item to Client</button>
                </form>
            </div>

            <div class="flat-box">
                <div class="eyebrow">ASSESSMENT AUDIT TRAIL</div>
                <table class="bt-table">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>Event</th>
                            <th>Actor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($auditLogs as $log)
                            <tr>
                                <td style="font-size:11px;">{{ $log->created_at->format('d M Y H:i') }}</td>
                                <td><code>{{ $log->event }}</code></td>
                                <td>{{ $log->actor_label }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3">No audit events recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection
