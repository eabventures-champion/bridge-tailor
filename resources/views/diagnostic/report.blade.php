@extends('layouts.app')

@section('title', "Diagnostic Report — {$company->name}")

@section('content')
<!-- Section 1: Solid Espresso Report Header & Review Status Bar -->
<section class="sec-espresso">
    <div class="bt-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:20px;flex-wrap:wrap;">
            <div>
                <div class="eyebrow">
                    BRIDGE-TAILOR EXECUTIVE DIAGNOSTIC REPORT · REF #{{ substr($assessment->ulid, -8) }} · FRAMEWORK v{{ $scored['framework_version'] ?? $fw->version() }}
                </div>
                <h1 style="font-size:30px;color:#fff;margin-bottom:4px;">{{ $company->name }}</h1>
                <div style="font-size:12.5px;color:#d6cdc2;">
                    <strong>Sector:</strong> {{ $fw->sectors()[$company->sector] ?? $company->sector }} ·
                    <strong>Stage:</strong> {{ $fw->stages()[$company->stage] ?? $company->stage }} ·
                    <strong>Size:</strong> {{ $fw->get("size_bands.{$company->size_band}", $company->size_band) }} ·
                    <strong>Revenue:</strong> {{ $fw->get("revenue_bands.{$company->revenue_band}", $company->revenue_band) }} ·
                    <strong>Location:</strong> {{ $company->city ? "{$company->city}, " : '' }}{{ $company->country }}
                </div>
            </div>

            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
                <div style="display:flex;gap:6px;flex-wrap:wrap;">
                    @php
                        $revStatus = $report?->review_status ?? 'ai_draft';
                    @endphp
                    @if(in_array($revStatus, ['approved', 'published']))
                        <span class="tag tag-ok">■ CONSULTANT APPROVED ({{ $report->reviewer?->name ?? 'Bridge Tailor Senior Consultant' }})</span>
                    @else
                        <span class="tag tag-amber">■ INITIAL AI + FRAMEWORK DRAFT (PENDING CONSULTANT SIGN-OFF)</span>
                    @endif
                    <a href="{{ route('diagnostic.print', ['ulid' => $assessment->ulid, 'token' => $assessment->access_token]) }}" target="_blank" class="btn btn-amber btn-sm">
                        Download / Print Branded PDF Report ↗
                    </a>
                    @if(auth()->check() && auth()->user()->isConsultant())
                        <a href="{{ route('consultant.review', $assessment->ulid) }}" class="btn btn-outline btn-sm" style="background:#141210;color:#fff;border-color:#786e67;">
                            Consultant Review Mode
                        </a>
                    @endif
                </div>
                <div style="font-size:11px;color:#b2a89f;">
                    Completed {{ $assessment->completed_at?->format('d M Y, H:i') ?? $assessment->created_at->format('d M Y') }} ·
                    Engine: {{ strtoupper($report?->ai_provider ?? 'FRAMEWORK') }} ({{ $report?->ai_model ?? 'bt-deterministic-v1' }})
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Section 2: Solid Cream Band — Component 1 & 2: Business Health Index, Functional Scores & Headline KPIs -->
<section class="sec-cream">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">01 &amp; 02 · BUSINESS HEALTH INDEX &amp; FUNCTIONAL SCORES (CONTROLLED SCORING ENGINE)</div>
                <h2 style="font-size:23px;">Overall Business Health Score &amp; Functional Breakdown</h2>
            </div>
            <div style="font-size:11.5px;font-weight:700;color:var(--bt-umber);">
                {{ $scored['coverage']['assessed'] ?? 0 }} OF {{ $scored['coverage']['total'] ?? 5 }} FUNCTIONAL AREAS ASSESSED
            </div>
        </div>

        <div class="bt-score-layout">
            <!-- Business Health Index Box -->
            @php
                $idx = $scored['health_index'] ?? null;
                $band = $scored['band'] ?? ['label' => 'Indicative', 'color' => '#B85A0A', 'note' => ''];
            @endphp
            <div class="flat-box" style="background:var(--bt-espresso);color:#fff;display:flex;flex-direction:column;justify-content:space-between;border:2px solid var(--bt-ink);">
                <div>
                    <div class="eyebrow">BUSINESS HEALTH INDEX</div>
                    <div style="font-size:58px;font-weight:800;line-height:1;color:var(--bt-amber);margin:6px 0;">
                        {{ $idx ?? '—' }}<span style="font-size:20px;color:#b8aea2;font-weight:600;">/100</span>
                    </div>
                    <span class="tag tag-amber" style="font-size:11px;">{{ $band['label'] }}</span>
                </div>
                <p style="font-size:11.5px;color:#d8d0c8;margin:12px 0 0;line-height:1.45;border-top:1px solid #443e3a;padding-top:8px;">
                    {{ $band['note'] }}
                </p>
            </div>

            <!-- Functional Scores + Sector Benchmarks Table -->
            <div class="flat-box" style="padding:0;">
                <table class="bt-table" style="border:none;">
                    <thead>
                        <tr>
                            <th style="width:27%;">Functional Area</th>
                            <th style="width:32%;">Health Score (0–100)</th>
                            <th style="width:13%;">Band</th>
                            <th style="width:14%;">Sector Median</th>
                            <th style="width:14%;">Evidence Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($scored['modules'] ?? [] as $mKey => $m)
                            @php $bm = $benchmarks[$mKey] ?? null; @endphp
                            <tr>
                                <td>
                                    <div style="font-weight:700;">{{ $mKey }} · {{ $m['name'] }}</div>
                                    <div style="font-size:11px;color:#666;">{{ $m['raised'] }} symptom(s) · {{ $m['answered'] }} checks</div>
                                </td>
                                <td>
                                    @if($m['score'] !== null)
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <div class="solid-track" style="flex:1;">
                                                <div class="solid-fill" style="width:{{ $m['score'] }}%;background:{{ $m['score'] >= 70 ? '#2F5E76' : ($m['score'] >= 50 ? '#C9790F' : '#7A3B06') }};"></div>
                                            </div>
                                            <strong style="width:44px;text-align:right;font-size:13px;">{{ $m['score'] }}/100</strong>
                                        </div>
                                    @else
                                        <span style="color:#888;font-style:italic;">Not assessed</span>
                                    @endif
                                </td>
                                <td>
                                    @if($m['score'] !== null)
                                        <span class="tag {{ $m['severity'] === 'High' ? 'tag-high' : ($m['severity'] === 'Medium' ? 'tag-medium' : 'tag-solid') }}">
                                            {{ $m['label'] }}
                                        </span>
                                    @else
                                        <span class="tag">No Data</span>
                                    @endif
                                </td>
                                <td>
                                    @if($bm && $m['score'] !== null)
                                        @php $diff = $m['score'] - $bm['median']; @endphp
                                        <strong>{{ $bm['median'] }}/100</strong>
                                        <span style="font-size:11px;font-weight:700;color:{{ $diff >= 0 ? 'var(--bt-success)' : 'var(--bt-danger)' }};">
                                            ({{ $diff >= 0 ? "+{$diff}" : $diff }})
                                        </span>
                                    @elseif($bm)
                                        {{ $bm['median'] }}/100
                                    @else
                                        —
                                    @endif
                                </td>
                                <td style="font-size:11.5px;font-weight:600;">
                                    @if($m['answered'] > 0)
                                        {{ $m['distribution'][0] }} in place · {{ $m['distribution'][1] }} part · {{ $m['distribution'][2] }} miss
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4 Compact KPI Strip + Computed Key Readings -->
        @if($uiVisible('vis.report_kpi_strip'))
        <div class="flat-grid-4" style="background:var(--bt-white);margin-bottom:14px;">
            <div style="padding:10px 14px;border-right:1px solid var(--bt-border);">
                <div style="font-size:21px;font-weight:800;">{{ $scored['kpis']['issues_raised'] ?? 0 }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:var(--bt-umber);">Intake Symptoms Flagged</div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid var(--bt-border);">
                <div style="font-size:21px;font-weight:800;">{{ $scored['kpis']['answered'] ?? 0 }}</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:var(--bt-umber);">Evidence Questions Verified</div>
            </div>
            <div style="padding:10px 14px;border-right:1px solid var(--bt-border);">
                <div style="font-size:21px;font-weight:800;color:var(--bt-teal);">{{ $scored['kpis']['in_place_pct'] ?? 0 }}%</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:var(--bt-umber);">Controls Fully In Place</div>
            </div>
            <div style="padding:10px 14px;">
                <div style="font-size:21px;font-weight:800;color:var(--bt-amber-dark);">{{ $scored['kpis']['missing_pct'] ?? 0 }}%</div>
                <div style="font-size:10px;font-weight:800;text-transform:uppercase;color:var(--bt-umber);">Controls Missing (Critical Gaps)</div>
            </div>
        </div>
        @endif

        <!-- Executive Summary & Consultant Commentary -->
        <div class="flat-grid-2">
            <div class="flat-box">
                <div class="eyebrow">EXECUTIVE DIAGNOSTIC SYNTHESIS</div>
                <p style="margin:4px 0 0;font-size:13px;line-height:1.55;">
                    {{ $report?->executive_summary ?? ($analysis['executive_summary'] ?? '') }}
                </p>
                @if($report?->consultant_notes)
                    <div style="margin-top:10px;padding:9px 12px;background:var(--bt-amber-pale);border-left:4px solid var(--bt-ink);font-size:12px;">
                        <strong>Bridge-Tailor Consultant Note ({{ $report->reviewer?->name ?? 'Principal Consultant' }}):</strong>
                        {{ $report->consultant_notes }}
                    </div>
                @endif
            </div>

            <div class="flat-box">
                <div class="eyebrow">KEY COMPUTED READINGS</div>
                @foreach($scored['readings'] ?? [] as $r)
                    <div class="bt-reading-row">
                        <strong class="bt-reading-key">{{ $r['key'] }}</strong>
                        <span>{{ $r['text'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- Section 3: Solid White Band — Bridge-Tailor Perception vs. Evidence & Cross-Area Insights -->
@if($uiVisible('vis.report_perception_vs_evidence'))
<section class="sec-white">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">BRIDGE-TAILOR DIAGNOSTIC METHODOLOGY · PERCEPTION VS. REALITY</div>
                <h2 style="font-size:23px;">How It Feels vs. What the Evidence Shows &amp; Cross-Area Insights</h2>
            </div>
            <span class="tag tag-medium">15+ POINT GAP FLAGS A BLIND SPOT</span>
        </div>

        <table class="bt-table" style="margin-bottom:14px;">
            <thead>
                <tr>
                    <th style="width:24%;">Functional Area</th>
                    <th style="width:24%;">How It Feels (Symptom Pain Health)</th>
                    <th style="width:24%;">What the Evidence Shows (Gateway Checks)</th>
                    <th style="width:10%;">Blended Score</th>
                    <th style="width:18%;">Diagnostic Signal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scored['modules'] ?? [] as $mKey => $m)
                    <tr>
                        <td style="font-weight:700;">{{ $m['name'] }}</td>
                        <td>
                            @if($m['feel'] !== null)
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="solid-track" style="flex:1;"><div class="solid-fill" style="width:{{ $m['feel'] }}%;"></div></div>
                                    <strong style="width:32px;text-align:right;">{{ $m['feel'] }}</strong>
                                </div>
                            @else
                                <span style="color:#888;">No symptom raised</span>
                            @endif
                        </td>
                        <td>
                            @if($m['evidence'] !== null)
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div class="solid-track" style="flex:1;"><div class="solid-fill" style="width:{{ $m['evidence'] }}%;background:var(--bt-teal);"></div></div>
                                    <strong style="width:32px;text-align:right;">{{ $m['evidence'] }}</strong>
                                </div>
                            @else
                                <span style="color:#888;">—</span>
                            @endif
                        </td>
                        <td style="font-weight:800;font-size:14px;">{{ $m['score'] ?? '—' }}</td>
                        <td>
                            <span class="tag {{ $m['perception']['key'] === 'weaker' ? 'tag-critical' : ($m['perception']['key'] === 'better' ? 'tag-solid' : 'tag-medium') }}">
                                {{ $m['perception']['label'] }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Cross-Area Insights from Methodology -->
        @if(!empty($scored['insights']))
            <div class="eyebrow" style="margin-bottom:6px;">CROSS-AREA OBSERVATIONS (VISIBLE WHEN TWO FUNCTIONS ARE READ TOGETHER)</div>
            <div class="flat-grid-3">
                @foreach($scored['insights'] as $ins)
                    <div class="flat-box" style="background:var(--bt-cream);border-left:4px solid var(--bt-amber);">
                        <h4 style="font-size:13.5px;margin-bottom:5px;">{{ $ins['title'] }}</h4>
                        <p style="margin:0;font-size:12px;color:#3a3532;line-height:1.5;">{{ $ins['body'] }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif

<!-- Section 4: Solid Slate Band — Components 3, 4, 5 & 6: Strengths, Weaknesses, Risks & Opportunities -->
@if($uiVisible('vis.report_swot_risk_cards'))
<section class="sec-slate">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">03, 04, 05 &amp; 06 · STRUCTURED DIAGNOSTIC FINDINGS</div>
                <h2 style="font-size:23px;">Strengths, Deficiencies, Risk Assessment &amp; Opportunity Analysis</h2>
            </div>
        </div>

        <div class="flat-grid-2" style="margin-bottom:14px;">
            <!-- Strengths -->
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="tag tag-ok">03 · IDENTIFIED STRENGTHS</span>
                    <span style="font-size:11px;font-weight:700;">Areas performing well (≥75/100)</span>
                </div>
                @forelse($analysis['strengths'] ?? [] as $st)
                    <div style="padding:7px 0;border-bottom:1px solid #e5ded5;">
                        <div style="font-weight:700;font-size:12.5px;">[{{ $st['module_key'] }}] {{ $st['area'] }}</div>
                        <div style="font-size:12px;color:#333;">{{ $st['observation'] }}</div>
                        <div style="font-size:11.5px;color:var(--bt-teal);font-weight:600;margin-top:2px;">Leverage: {{ $st['how_to_leverage'] }}</div>
                    </div>
                @empty
                    <div style="font-size:12px;color:#666;">No functional criterion scored ≥75/100 yet. Focus on stabilising Phase 1 controls first.</div>
                @endforelse
            </div>

            <!-- Weaknesses / Deficiencies -->
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="tag tag-high">04 · IDENTIFIED WEAKNESSES &amp; GAPS</span>
                    <span style="font-size:11px;font-weight:700;">Verified against client evidence answers</span>
                </div>
                @foreach(array_slice($analysis['weaknesses'] ?? [], 0, 5) as $wk)
                    <div style="padding:7px 0;border-bottom:1px solid #e5ded5;">
                        <div style="font-weight:700;font-size:12.5px;color:var(--bt-amber-dark);">[{{ $wk['module_key'] }}] {{ $wk['area'] }} — {{ $wk['deficiency'] }}</div>
                        <div style="font-size:11.5px;color:#333;margin-top:2px;"><strong>Root cause:</strong> {{ $wk['root_cause'] }}</div>
                        <div style="font-size:11px;color:#666;font-style:italic;margin-top:2px;">Evidence: {{ $wk['evidence_quote'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flat-grid-2">
            <!-- Risk Assessment -->
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="tag tag-critical">05 · CRITICAL &amp; EMERGING BUSINESS RISKS</span>
                    <span style="font-size:11px;font-weight:700;">Prioritised by exposure</span>
                </div>
                @foreach($analysis['risk_assessment'] ?? [] as $rk)
                    <div style="padding:7px 0;border-bottom:1px solid #e5ded5;">
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <strong style="font-size:12.5px;">[{{ $rk['module_key'] }}] {{ $rk['title'] }}</strong>
                            <span class="tag {{ $rk['severity'] === 'Critical' ? 'tag-critical' : 'tag-medium' }}">{{ $rk['severity'] }} · {{ $rk['horizon'] }}</span>
                        </div>
                        <div style="font-size:11.5px;color:#555;margin-top:2px;">Trigger: {{ $rk['trigger_evidence'] }}</div>
                        <div style="font-size:11.5px;font-weight:600;color:var(--bt-ink);margin-top:2px;">Mitigation: {{ $rk['mitigation'] }}</div>
                    </div>
                @endforeach
            </div>

            <!-- Opportunity Analysis -->
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                    <span class="tag tag-amber">06 · OPPORTUNITY &amp; VALUE-UNLOCK ANALYSIS</span>
                    <span style="font-size:11px;font-weight:700;">Growth &amp; efficiency potential</span>
                </div>
                @foreach($analysis['opportunity_analysis'] ?? [] as $op)
                    <div style="padding:7px 0;border-bottom:1px solid #e5ded5;">
                        <div style="font-weight:700;font-size:12.5px;">[{{ $op['module_key'] }}] {{ $op['title'] }}</div>
                        <div style="font-size:12px;color:#333;margin-top:2px;">{{ $op['value_unlock'] }}</div>
                        <div style="font-size:11px;color:var(--bt-umber);font-weight:700;margin-top:2px;">Prerequisite: {{ $op['prerequisite'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif

<!-- Section 5: Solid Cream Band — Components 7, 8 & 9: AI Recommendations, Implementation Roadmap & Consulting Priorities -->
@if($uiVisible('vis.report_roadmap_and_priorities'))
<section class="sec-cream">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">07, 08 &amp; 09 · CORRECTIVE INTERVENTIONS, ROADMAP &amp; BRIDGE-TAILOR CONSULTING PRIORITIES</div>
                <h2 style="font-size:23px;">Actionable Recommendations &amp; Sequenced Implementation Roadmap</h2>
            </div>
        </div>

        <!-- Component 7: Recommendations Table -->
        <table class="bt-table" style="margin-bottom:16px;">
            <thead>
                <tr>
                    <th style="width:9%;">Priority</th>
                    <th style="width:8%;">Module</th>
                    <th style="width:22%;">Recommendation</th>
                    <th style="width:35%;">Strategic Intervention</th>
                    <th style="width:26%;">Expected Outcome &amp; Target KPI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['recommendations'] ?? [] as $rec)
                    <tr>
                        <td>
                            <span class="tag {{ $rec['priority'] === 'Critical' ? 'tag-critical' : ($rec['priority'] === 'High' ? 'tag-amber' : 'tag-medium') }}">
                                {{ $rec['priority'] }}
                            </span>
                        </td>
                        <td style="font-weight:800;">{{ $rec['module_key'] }}</td>
                        <td style="font-weight:700;">{{ $rec['title'] }}</td>
                        <td>{{ $rec['intervention'] }}</td>
                        <td>
                            <div style="font-weight:600;">{{ $rec['expected_outcome'] ?? '' }}</div>
                            <div style="font-size:11px;color:var(--bt-amber-dark);margin-top:2px;">KPI: {{ $rec['target_kpi'] ?? '' }}</div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Component 8: 3-Phase Implementation Roadmap -->
        <div class="eyebrow" style="margin-bottom:6px;">08 · PHASED IMPLEMENTATION ROADMAP (30 / 90 / 180 DAYS)</div>
        <div class="flat-grid-3" style="margin-bottom:16px;">
            @foreach($analysis['implementation_roadmap'] ?? [] as $phase)
                <div class="flat-box">
                    <div style="display:flex;justify-content:space-between;align-items:center;background:var(--bt-espresso);color:#fff;padding:6px 10px;margin:-14px -16px 10px;">
                        <strong style="font-size:12px;">{{ $phase['phase'] }}</strong>
                        <span class="tag tag-amber">{{ $phase['window'] }}</span>
                    </div>
                    <div style="font-size:11.5px;font-weight:700;color:var(--bt-umber);margin-bottom:6px;">{{ $phase['focus'] }}</div>
                    <ul style="margin:0 0 8px;padding-left:16px;font-size:12px;">
                        @foreach($phase['actions'] ?? [] as $act)
                            <li style="margin-bottom:4px;">{{ $act }}</li>
                        @endforeach
                    </ul>
                    <div style="font-size:11px;background:var(--bt-cream);border:1px solid var(--bt-border);padding:6px 8px;">
                        <strong>Milestone:</strong> {{ $phase['milestone'] }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Component 9: Bridge-Tailor Consulting Priorities -->
        <div class="eyebrow" style="margin-bottom:6px;">09 · SEQUENCED BRIDGE-TAILOR CONSULTING PRIORITIES</div>
        <table class="bt-table">
            <thead>
                <tr>
                    <th style="width:6%;">Order</th>
                    <th style="width:18%;">Practice Line</th>
                    <th style="width:22%;">Entry Programme</th>
                    <th style="width:20%;">Follow-On / Expansion</th>
                    <th style="width:16%;">Delivery Mode &amp; Timeline</th>
                    <th style="width:18%;">Sequencing Rationale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['consulting_priorities'] ?? [] as $cp)
                    <tr>
                        <td style="font-weight:800;font-size:14px;background:var(--bt-amber-pale);">Phase {{ $cp['rank'] }}</td>
                        <td style="font-weight:700;">{{ $cp['business_line'] }}</td>
                        <td style="font-weight:800;color:var(--bt-amber-dark);">{{ $cp['entry_programme'] }}</td>
                        <td>{{ $cp['expansion_programme'] }}</td>
                        <td>
                            <div style="font-weight:700;">{{ $cp['delivery_mode'] }}</div>
                            <div style="font-size:11px;color:#555;">{{ $cp['duration'] }}</div>
                        </td>
                        <td style="font-size:11.5px;">{{ $cp['rationale'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

<!-- Section 6: Solid White Band — Epistemic Ledger (Verified Facts vs. Client-Provided vs. Assumptions) & Historical Progress -->
<section class="sec-white">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">EPISTEMIC INTEGRITY &amp; HISTORICAL BENCHMARKING</div>
                <h2 style="font-size:23px;">Fact vs. Assumption Ledger, Document Vault &amp; Historical Trend</h2>
            </div>
            <span class="tag tag-solid">ANTI-HALLUCINATION GUARDRAIL</span>
        </div>

        @if($uiVisible('vis.report_epistemic_ledger'))
        <div class="flat-grid-3" style="margin-bottom:14px;">
            <div class="flat-box">
                <span class="tag tag-ok" style="margin-bottom:6px;">DOCUMENT-EVIDENCED / VERIFIED FACTS</span>
                <ul style="margin:6px 0 0;padding-left:16px;font-size:12px;">
                    @foreach($analysis['epistemic_ledger']['verified_facts'] ?? [] as $vf)
                        <li style="margin-bottom:4px;">{{ $vf }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="flat-box">
                <span class="tag tag-amber" style="margin-bottom:6px;">CLIENT-PROVIDED (UNAUDITED SELF-REPORT)</span>
                <ul style="margin:6px 0 0;padding-left:16px;font-size:12px;">
                    @foreach($analysis['epistemic_ledger']['client_provided'] ?? [] as $cp)
                        <li style="margin-bottom:4px;">{{ $cp }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="flat-box">
                <span class="tag tag-medium" style="margin-bottom:6px;">WORKING ASSUMPTIONS TO VERIFY IN DISCOVERY</span>
                <ul style="margin:6px 0 0;padding-left:16px;font-size:12px;">
                    @foreach($analysis['epistemic_ledger']['assumptions_to_test'] ?? [] as $as)
                        <li style="margin-bottom:4px;">{{ $as }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Supporting Document Upload + Team Invites + Historical Comparison -->
        <div class="flat-grid-3">
            <!-- Document Upload Vault -->
            @if($uiVisible('vis.report_document_vault_card'))
            <div class="flat-box" style="background:var(--bt-slate);">
                <div class="eyebrow">UPLOAD SUPPORTING DOCUMENTS FOR AI ANALYSIS</div>
                <form action="{{ route('diagnostic.document.upload', $assessment->ulid) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="token" value="{{ $assessment->access_token }}">
                    <div class="field">
                        <label>Category</label>
                        <select name="category">
                            <option value="financial_statement">Financial Statement</option>
                            <option value="management_accounts">Management Accounts</option>
                            <option value="business_plan">Business Plan</option>
                            <option value="governance_doc">Governance / Compliance Record</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>File (PDF, TXT, CSV, DOCX, XLSX)</label>
                        <input type="file" name="document" required>
                    </div>
                    <button type="submit" class="btn btn-sm" style="width:100%;">Upload &amp; Extract Evidence →</button>
                </form>
                @if($assessment->documents->count())
                    <div style="margin-top:10px;border-top:1px solid var(--bt-border);padding-top:8px;font-size:11.5px;">
                        <strong>Uploaded Files ({{ $assessment->documents->count() }}):</strong>
                        @foreach($assessment->documents as $doc)
                            <div style="margin-top:4px;padding:4px 6px;background:#fff;border:1px solid var(--bt-border);">
                                <div><strong>{{ $doc->original_name }}</strong> ({{ strtoupper($doc->extraction_status) }})</div>
                                <div style="font-size:10.5px;color:#666;">{{ $doc->ai_extraction['document_summary'] ?? '' }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            <!-- Team Multi-Respondent Invites -->
            @if($uiVisible('vis.report_team_invite_card'))
            <div class="flat-box" style="background:var(--bt-slate);">
                <div class="eyebrow">TEAM ASSESSMENT (MULTI-EMPLOYEE PERSPECTIVES)</div>
                <p style="font-size:11.5px;margin:0 0 8px;">
                    Invite your Head of Finance, Operations Manager, or Co-Founder to answer the diagnostic questions and blend perspectives.
                </p>
                <form action="{{ route('diagnostic.team.create', $assessment->ulid) }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $assessment->access_token }}">
                    <div class="field">
                        <label>Colleague Name *</label>
                        <input type="text" name="respondent_name" required placeholder="e.g. Efua Darko">
                    </div>
                    <div class="field">
                        <label>Role / Function *</label>
                        <input type="text" name="respondent_role" required placeholder="e.g. Head of Finance">
                    </div>
                    <button type="submit" class="btn btn-sm" style="width:100%;">Generate Team Respondent Link →</button>
                </form>
                @if($assessment->teamInvites->count())
                    <div style="margin-top:10px;border-top:1px solid var(--bt-border);padding-top:8px;font-size:11.5px;">
                        <strong>Team Respondents ({{ $assessment->teamInvites->count() }}):</strong>
                        @foreach($assessment->teamInvites as $inv)
                            <div style="margin-top:4px;padding:4px 6px;background:#fff;border:1px solid var(--bt-border);display:flex;justify-content:space-between;align-items:center;">
                                <span><strong>{{ $inv->respondent_name }}</strong> · {{ $inv->respondent_role }}</span>
                                <a href="{{ route('diagnostic.team.show', $inv->token) }}" class="tag {{ $inv->status === 'submitted' ? 'tag-ok' : 'tag-amber' }}">
                                    {{ $inv->status === 'submitted' ? 'Submitted' : 'Open Form ↗' }}
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            <!-- Historical Assessments Trend -->
            @if($uiVisible('vis.report_history_card'))
            <div class="flat-box" style="background:var(--bt-slate);">
                <div class="eyebrow">HISTORICAL DIAGNOSTIC TRAJECTORY ({{ $company->name }})</div>
                <p style="font-size:11.5px;margin:0 0 8px;">
                    Tracks business health improvement across diagnostic runs.
                </p>
                @foreach($history as $h)
                    <div style="padding:7px 9px;background:{{ $h->id === $assessment->id ? 'var(--bt-amber-pale)' : '#fff' }};border:1px solid var(--bt-border);margin-bottom:6px;display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-weight:700;font-size:12px;">
                                {{ $h->created_at->format('d M Y') }}
                                @if($h->id === $assessment->id) <span class="tag tag-amber">CURRENT</span> @endif
                            </div>
                            <div style="font-size:11px;color:#555;">{{ $h->health_band }}</div>
                        </div>
                        <div style="text-align:right;">
                            <div style="font-size:18px;font-weight:800;">{{ $h->health_index }}/100</div>
                            @if($h->id !== $assessment->id)
                                <a href="{{ route('diagnostic.report', $h->ulid) }}" style="font-size:10.5px;text-decoration:underline;font-weight:700;">View Run →</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</section>

<!-- Section 7: Solid Espresso Band — Consulting Conversion: Request Strategy Session with Bridge Tailor -->
@if($uiVisible('vis.report_consultation_booking'))
<section class="sec-espresso" id="book-consultation">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">CAPABILITY 06 · CONSULTING ENGAGEMENT CONVERSION</div>
                <h2 style="font-size:25px;color:#fff;">{{ $uiText('text.report_consult_heading') }}</h2>
            </div>
            <div style="font-size:12px;color:var(--bt-amber);font-weight:700;">
                EAST LEGON, ACCRA · VIDEO OR IN-PERSON ADVISORY SESSION
            </div>
        </div>

        <div class="flat-grid-2">
            <div class="flat-box-dark">
                <div class="eyebrow">WHAT HAPPENS IN YOUR STRATEGY SESSION</div>
                <h3 style="font-size:17px;color:#fff;margin-bottom:8px;">From Diagnostic Insight to Structured Execution</h3>
                <p style="font-size:12.5px;color:#d6cdc2;line-height:1.55;margin:0 0 12px;">
                    A Bridge Tailor Principal Consultant reviews your diagnostic scores, perception-vs-evidence gaps, and supporting documents with you, confirms your Phase 1 intervention scope, and establishes a 90-day implementation tracker.
                </p>
                @php
                    $topPriority = $analysis['consulting_priorities'][0] ?? null;
                @endphp
                @if($topPriority)
                    <div style="background:#141210;border-left:4px solid var(--bt-amber);padding:10px 12px;font-size:12px;">
                        <div style="color:var(--bt-amber);font-weight:800;text-transform:uppercase;font-size:10.5px;">RECOMMENDED STARTING PROGRAMME</div>
                        <div style="font-size:15px;font-weight:800;color:#fff;margin-top:2px;">{{ $topPriority['entry_programme'] }} ({{ $topPriority['business_line'] }})</div>
                        <div style="color:#c7beb5;margin-top:3px;">{{ $topPriority['delivery_mode'] }} · {{ $topPriority['duration'] }}</div>
                        <div style="color:#a89e94;margin-top:4px;font-size:11.5px;">{{ $topPriority['rationale'] }}</div>
                    </div>
                @endif
            </div>

            <div class="flat-box" style="color:var(--bt-ink);">
                <div class="eyebrow">BOOK YOUR CONSULTATION WITH BRIDGE TAILOR</div>
                <form action="{{ route('diagnostic.consultation', $assessment->ulid) }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $assessment->access_token }}">
                    <div class="flat-grid-2">
                        <div class="field">
                            <label>Contact Name *</label>
                            <input type="text" name="contact_name" required value="{{ $company->contact_name }}">
                        </div>
                        <div class="field">
                            <label>Email *</label>
                            <input type="email" name="contact_email" required value="{{ $company->contact_email }}">
                        </div>
                        <div class="field">
                            <label>Phone / WhatsApp</label>
                            <input type="text" name="contact_phone" value="{{ $company->contact_phone }}">
                        </div>
                        <div class="field">
                            <label>Preferred Session Format *</label>
                            <select name="preferred_channel">
                                <option value="video">Video Strategy Session (Teams / Meet)</option>
                                <option value="in_person_accra">In-Person at Bridge Tailor (East Legon, Accra)</option>
                                <option value="phone">Direct Executive Phone Briefing</option>
                            </select>
                        </div>
                    </div>
                    <div class="field">
                        <label>Preferred Day / Time Window</label>
                        <input type="text" name="preferred_window" placeholder="e.g. Tuesday or Thursday morning GMT">
                    </div>
                    <div class="field">
                        <label>Specific Priorities to Discuss</label>
                        <textarea name="client_message" rows="2" placeholder="Any immediate deadline, upcoming board meeting, or funder timeline..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-amber" style="width:100%;padding:10px;">
                        Submit Consultation Request to Bridge Tailor →
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
@endif
@endsection
