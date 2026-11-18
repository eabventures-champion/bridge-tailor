<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bridge-Tailor Diagnostic Report — {{ $company->name }}</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; border-radius: 0 !important; box-shadow: none !important; }
        body {
            margin: 0;
            padding: 20px;
            font-family: 'Poppins', Arial, sans-serif;
            font-size: 11.5px;
            line-height: 1.45;
            color: #141210;
            background: #ffffff;
        }
        .print-wrap { max-width: 960px; margin: 0 auto; }
        .no-print {
            background: #2c2927;
            color: #fff;
            padding: 10px 16px;
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #f17e01;
        }
        .btn-print {
            background: #f17e01;
            color: #141210;
            border: 1px solid #f17e01;
            padding: 6px 14px;
            font-weight: 800;
            font-size: 11.5px;
            text-transform: uppercase;
            cursor: pointer;
            font-family: inherit;
        }
        h1, h2 { font-family: 'DM Serif Display', Georgia, serif; font-weight: 400; margin: 0; }
        .band-head {
            background: #2c2927;
            color: #ffffff;
            padding: 16px 18px;
            border-bottom: 4px solid #f17e01;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .sec-title {
            background: #FFF7ED;
            border: 1px solid #2c2927;
            border-left: 5px solid #f17e01;
            padding: 6px 10px;
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin: 14px 0 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }
        th {
            background: #2c2927;
            color: #fff;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            text-transform: uppercase;
            border: 1px solid #2c2927;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #ccc2b6;
            vertical-align: top;
        }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="print-wrap">
        <div class="no-print">
            <div>
                <strong>BRIDGE-TAILOR PDF / PRINT EXPORT</strong> · Use your browser's "Save as PDF" destination for an executive vector PDF report.
            </div>
            <div style="display:flex;gap:8px;">
                <button type="button" class="btn-print" onclick="window.print()">Print / Save as PDF</button>
                <a href="{{ route('diagnostic.report', $assessment->ulid) }}" style="color:#fff;padding:6px 10px;border:1px solid #666;text-decoration:none;font-size:11px;">← Return to Interactive Report</a>
            </div>
        </div>

        <div class="band-head">
            <div>
                <div style="font-size:10px;font-weight:800;color:#f17e01;letter-spacing:0.14em;text-transform:uppercase;">
                    BRIDGE-TAILOR CONSULTING · ACCRA, GHANA · EXECUTIVE DIAGNOSTIC DOSSIER
                </div>
                <h1 style="font-size:26px;margin-top:2px;">{{ $company->name }}</h1>
                <div style="font-size:11px;color:#d6cdc2;margin-top:4px;">
                    Sector: {{ $fw->sectors()[$company->sector] ?? $company->sector }} ·
                    Stage: {{ $fw->stages()[$company->stage] ?? $company->stage }} ·
                    Size: {{ $fw->get("size_bands.{$company->size_band}", $company->size_band) }} ·
                    Revenue: {{ $fw->get("revenue_bands.{$company->revenue_band}", $company->revenue_band) }}
                </div>
            </div>
            <div style="text-align:right;background:#141210;padding:10px 14px;border:1px solid #f17e01;">
                <div style="font-size:9.5px;color:#f17e01;font-weight:800;text-transform:uppercase;">Business Health Index</div>
                <div style="font-size:32px;font-weight:800;line-height:1.05;color:#fff;">{{ $scored['health_index'] ?? '—' }}/100</div>
                <div style="font-size:10.5px;font-weight:700;color:#f17e01;">{{ $scored['band']['label'] ?? '' }}</div>
            </div>
        </div>

        <div class="sec-title">01 · Executive Diagnostic Summary &amp; Review Sign-Off</div>
        <div style="border:1px solid #ccc2b6;padding:10px 12px;">
            <p style="margin:0 0 6px;">{{ $report?->executive_summary ?? ($analysis['executive_summary'] ?? '') }}</p>
            @if($report?->consultant_notes)
                <div style="background:#FDEBD3;border-left:4px solid #141210;padding:6px 10px;font-size:11px;">
                    <strong>Consultant Review Note ({{ $report->reviewer?->name ?? 'Bridge Tailor Principal Consultant' }}):</strong>
                    {{ $report->consultant_notes }}
                </div>
            @endif
        </div>

        <div class="sec-title">02 · Functional Health Scores, Feel vs. Evidence &amp; Sector Benchmarks</div>
        <table>
            <thead>
                <tr>
                    <th>Functional Module</th>
                    <th>How It Feels</th>
                    <th>Evidence Score</th>
                    <th>Blended Score</th>
                    <th>Band</th>
                    <th>Sector Median</th>
                    <th>Diagnostic Signal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($scored['modules'] ?? [] as $mKey => $m)
                    @php $bm = $benchmarks[$mKey] ?? null; @endphp
                    <tr>
                        <td><strong>{{ $mKey }} · {{ $m['name'] }}</strong></td>
                        <td>{{ $m['feel'] !== null ? $m['feel'].'/100' : '—' }}</td>
                        <td>{{ $m['evidence'] !== null ? $m['evidence'].'/100' : '—' }}</td>
                        <td><strong>{{ $m['score'] !== null ? $m['score'].'/100' : 'Not assessed' }}</strong></td>
                        <td>{{ $m['label'] ?? '—' }}</td>
                        <td>{{ $bm ? $bm['median'].'/100' : '—' }}</td>
                        <td>{{ $m['perception']['label'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sec-title">03 &amp; 04 · Identified Strengths &amp; Operational Deficiencies</div>
        <table>
            <thead>
                <tr>
                    <th style="width:50%;">Strengths (Leverage Points)</th>
                    <th style="width:50%;">Identified Weaknesses &amp; Evidence Quotes</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        @forelse($analysis['strengths'] ?? [] as $st)
                            <div style="margin-bottom:6px;">
                                <strong>[{{ $st['module_key'] }}] {{ $st['area'] }}:</strong> {{ $st['observation'] }}
                            </div>
                        @empty
                            <div>Focus on foundational Phase 1 controls first.</div>
                        @endforelse
                    </td>
                    <td>
                        @foreach(array_slice($analysis['weaknesses'] ?? [], 0, 5) as $wk)
                            <div style="margin-bottom:6px;">
                                <strong>[{{ $wk['module_key'] }}] {{ $wk['area'] }}:</strong> {{ $wk['deficiency'] }}
                                <div style="font-style:italic;color:#444;">{{ $wk['evidence_quote'] }}</div>
                            </div>
                        @endforeach
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="sec-title">05 &amp; 07 · Risk Assessment &amp; Actionable AI Recommendations</div>
        <table>
            <thead>
                <tr>
                    <th>Priority</th>
                    <th>Module</th>
                    <th>Recommendation</th>
                    <th>Strategic Intervention</th>
                    <th>Target KPI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['recommendations'] ?? [] as $rec)
                    <tr>
                        <td><strong>{{ $rec['priority'] }}</strong></td>
                        <td>{{ $rec['module_key'] }}</td>
                        <td><strong>{{ $rec['title'] }}</strong></td>
                        <td>{{ $rec['intervention'] }}</td>
                        <td>{{ $rec['target_kpi'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="sec-title">08 &amp; 09 · Implementation Roadmap &amp; Bridge-Tailor Consulting Priorities</div>
        <table>
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Bridge Tailor Practice Line</th>
                    <th>Entry Programme</th>
                    <th>Follow-On Programme</th>
                    <th>Delivery Mode &amp; Timeline</th>
                    <th>Rationale</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysis['consulting_priorities'] ?? [] as $cp)
                    <tr>
                        <td><strong>Phase {{ $cp['rank'] }}</strong></td>
                        <td>{{ $cp['business_line'] }}</td>
                        <td><strong>{{ $cp['entry_programme'] }}</strong></td>
                        <td>{{ $cp['expansion_programme'] }}</td>
                        <td>{{ $cp['delivery_mode'] }} ({{ $cp['duration'] }})</td>
                        <td>{{ $cp['rationale'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="border-top:2px solid #2c2927;padding-top:8px;margin-top:12px;font-size:10px;color:#555;display:flex;justify-content:space-between;">
            <span>Bridge-Tailor Consulting · East Legon, Accra, Ghana · www.bridge-tailor.com · consult@bridge-tailor.com</span>
            <span>Epistemic Notice: Self-reported scores &amp; unaudited client figures unless marked DOCUMENT-EVIDENCED.</span>
        </div>
    </div>
</body>
</html>
