@extends('layouts.app')

@section('title', 'AI Business Diagnostic Platform')

@section('content')
<!-- Section 1: Solid Espresso Hero Band -->
<section class="sec-espresso">
    <div class="bt-container {{ $uiVisible('vis.landing_architecture_box') ? 'bt-hero-grid' : '' }}">
        <div style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="eyebrow">{{ $uiText('text.hero_eyebrow') }}</div>
                <h1 style="font-size:32px;line-height:1.15;color:#ffffff;margin-bottom:10px;">
                    {{ $uiText('text.hero_title') }}
                </h1>
                <p style="font-size:14px;color:#d8d0c8;margin:0 0 16px;max-width:680px;line-height:1.55;">
                    {{ $uiText('text.hero_subtitle') }}
                </p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;padding-top:8px;border-top:1px solid #443e3a;">
                <a href="{{ route('diagnostic.wizard') }}" class="btn btn-amber">{{ $uiText('text.hero_cta_primary') }}</a>
                @auth
                    @if(auth()->user()->isDeveloper())
                        <a href="{{ route('developer.console') }}" class="btn btn-outline" style="background:#141210;color:var(--bt-amber);border-color:var(--bt-amber);">
                            Open Developer Master Console
                        </a>
                    @elseif(auth()->user()->isConsultant())
                        <a href="{{ route('consultant.dashboard') }}" class="btn btn-outline" style="background:#141210;color:#cac4c4;border-color:#443e3a;">
                            Open Consultant Review Desk
                        </a>
                    @else
                        <a href="{{ route('portal.index') }}" class="btn btn-outline" style="background:#2c2927;color:#fff;border-color:#8a7f78;">
                            Open My Client Workspace
                        </a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline" style="background:#2c2927;color:#fff;border-color:#8a7f78;">
                        Client / Consultant Sign In
                    </a>
                @endauth
            </div>
        </div>

        <!-- Right column: Deterministic + AI Architecture Summary Box -->
        @if($uiVisible('vis.landing_architecture_box'))
        <div class="flat-box-dark" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <div class="eyebrow">CONTROLLED DIAGNOSTIC ARCHITECTURE</div>
                <h3 style="font-size:15px;color:#fff;margin-bottom:8px;">Deterministic Scores + AI Structured Synthesis</h3>
                <p style="font-size:12px;color:#c7beb5;margin:0 0 12px;">
                    Numerical scores are calculated strictly by Bridge Tailor’s controlled scoring engine (50% weighted symptom pain + 50% gateway evidence verification). The AI interprets findings using OpenAI Structured Outputs and tags every statement by epistemic source.
                </p>
            </div>
            <table class="bt-table" style="border-color:#443e3a;font-size:11.5px;">
                <tr>
                    <td style="background:#141210;color:#f17e01;font-weight:700;border-color:#443e3a;width:36%;">Scoring Engine</td>
                    <td style="background:#1c1917;color:#e6dfd8;border-color:#443e3a;">Controlled 0–100 index + Feel vs. Evidence blind-spot gap (≥15 pts)</td>
                </tr>
                <tr>
                    <td style="background:#141210;color:#f17e01;font-weight:700;border-color:#443e3a;">Sequencing Gate</td>
                    <td style="background:#1c1917;color:#e6dfd8;border-color:#443e3a;">Financial Control gates Capital Raising (shaky books block funding)</td>
                </tr>
                <tr>
                    <td style="background:#141210;color:#f17e01;font-weight:700;border-color:#443e3a;">Epistemic Guardrail</td>
                    <td style="background:#1c1917;color:#e6dfd8;border-color:#443e3a;">Distinguishes Verified Facts, Client-Provided data &amp; Assumptions</td>
                </tr>
                <tr>
                    <td style="background:#141210;color:#f17e01;font-weight:700;border-color:#443e3a;">Consultant Review</td>
                    <td style="background:#1c1917;color:#e6dfd8;border-color:#443e3a;">AI drafts initial report → Consultant amends, approves &amp; publishes</td>
                </tr>
            </table>
        </div>
        @endif
    </div>
</section>

<!-- Section 2: Solid Cream Band — Six Major Platform Capabilities & 6-Step Workflow -->
@if($uiVisible('vis.landing_capabilities_section'))
<section class="sec-cream">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">END-TO-END WORKFLOW · FROM WEBSITE VISITOR TO CONSULTING ENGAGEMENT</div>
                <h2 style="font-size:23px;">{{ $uiText('text.capabilities_title') }}</h2>
            </div>
            <div style="font-size:11.5px;font-weight:700;color:var(--bt-umber);">
                REPLACES EXTERNAL BUSINESS HEALTH TEST WITH PROPRIETARY SUBDOMAIN ENGINE
            </div>
        </div>

        <div class="flat-grid-3" style="margin-bottom:14px;">
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="tag tag-amber">01 · BUSINESS ASSESSMENT</span>
                    <span style="font-size:11px;font-weight:700;color:var(--bt-umber);">Step 1 &amp; 2</span>
                </div>
                <h4 style="font-size:13.5px;margin-bottom:4px;">Adaptive Business Profile &amp; Intake</h4>
                <p style="margin:0;font-size:12px;color:#3d3835;">
                    Captures industry sector (Oil &amp; Gas, Agritech, Fintech, Manufacturing, etc.), business stage, revenue band, objectives, and 24 symptom statements rated 1–5 by operational pain.
                </p>
            </div>
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="tag tag-amber">02 · AI BUSINESS ANALYSIS</span>
                    <span style="font-size:11px;font-weight:700;color:var(--bt-umber);">Step 3</span>
                </div>
                <h4 style="font-size:13.5px;margin-bottom:4px;">Framework-Guided Evaluation</h4>
                <p style="margin:0;font-size:12px;color:#3d3835;">
                    Questions adapt dynamically to stage (skipping board/staff items for solo founders), sector regulations, and conditional follow-ups, feeding OpenAI Responses API with Structured Outputs.
                </p>
            </div>
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="tag tag-amber">03 · BUSINESS HEALTH SCORING</span>
                    <span style="font-size:11px;font-weight:700;color:var(--bt-umber);">Step 4</span>
                </div>
                <h4 style="font-size:13.5px;margin-bottom:4px;">Controlled 0–100 Health Index</h4>
                <p style="margin:0;font-size:12px;color:#3d3835;">
                    Calculates deterministic functional scores, compares "How it feels" vs. "What the evidence shows", and benchmarks against Bridge Tailor’s African SME sector medians.
                </p>
            </div>
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="tag tag-amber">04 · GAP &amp; RISK IDENTIFICATION</span>
                    <span style="font-size:11px;font-weight:700;color:var(--bt-umber);">Step 4</span>
                </div>
                <h4 style="font-size:13.5px;margin-bottom:4px;">Cross-Area Blind Spots &amp; Risks</h4>
                <p style="margin:0;font-size:12px;color:#3d3835;">
                    Identifies critical &amp; emerging risks, perception mismatches, and cross-functional blind spots (e.g., preparing to raise capital on numbers leadership does not trust).
                </p>
            </div>
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="tag tag-amber">05 · AI RECOMMENDATIONS</span>
                    <span style="font-size:11px;font-weight:700;color:var(--bt-umber);">Step 5</span>
                </div>
                <h4 style="font-size:13.5px;margin-bottom:4px;">30 / 90 / 180-Day Intervention Roadmap</h4>
                <p style="margin:0;font-size:12px;color:#3d3835;">
                    Generates actionable interventions mapped to Bridge Tailor’s programme catalogue (Scale Readiness Review, Performance System Setup, Finance Desk, Capital Prep, Market Entry).
                </p>
            </div>
            <div class="flat-box">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span class="tag tag-amber">06 · CONSULTING CONVERSION</span>
                    <span style="font-size:11px;font-weight:700;color:var(--bt-umber);">Step 6</span>
                </div>
                <h4 style="font-size:13.5px;margin-bottom:4px;">Consultant Review &amp; Strategy Session</h4>
                <p style="margin:0;font-size:12px;color:#3d3835;">
                    Converts diagnostic reports directly into booked strategy sessions, HubSpot deal parameters, multi-company client accounts, and ongoing action tracking.
                </p>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Section 3: Solid White Band — Five Assessment Modules & Bridge-Tailor Service Alignment -->
@if($uiVisible('vis.landing_modules_table'))
<section class="sec-white">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">FIVE DIAGNOSTIC ASSESSMENT MODULES · 25 CRITERIA · ADAPTIVE QUESTIONNAIRE</div>
                <h2 style="font-size:23px;">{{ $uiText('text.modules_title') }}</h2>
            </div>
            <a href="{{ route('diagnostic.wizard') }}" class="btn btn-sm">Run Full or Targeted Module Assessment →</a>
        </div>

        <table class="bt-table">
            <thead>
                <tr>
                    <th style="width:6%;">Code</th>
                    <th style="width:22%;">Assessment Module</th>
                    <th style="width:32%;">5 Core Assessment Criteria</th>
                    <th style="width:24%;">Bridge Tailor Programme Routing</th>
                    <th style="width:16%;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($fw->modules() as $mKey => $module)
                    <tr>
                        <td style="font-weight:800;font-size:13px;color:var(--bt-amber-deep);">{{ $mKey }}</td>
                        <td>
                            <div style="font-weight:700;color:var(--bt-ink);">{{ $module['name'] }}</div>
                            <div style="font-size:11.5px;color:#555;margin-top:2px;">{{ $module['description'] }}</div>
                        </td>
                        <td>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($module['criteria'] as $cKey => $crit)
                                    <span style="display:inline-block;border:1px solid #d6cdc2;background:#faf6f0;padding:2px 6px;font-size:11px;font-weight:600;">
                                        <strong>{{ $cKey }}:</strong> {{ $crit['name'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            @php
                                $progKeys = collect($module['criteria'])->pluck('programme')->unique()->filter();
                            @endphp
                            @foreach($progKeys as $pk)
                                @php $p = $fw->programme($pk); @endphp
                                @if($p)
                                    <div style="margin-bottom:4px;">
                                        <span class="tag tag-medium">{{ $p['line'] }}</span>
                                        <div style="font-size:11.5px;font-weight:700;margin-top:2px;">{{ $p['entry'] }} → {{ $p['next'] }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('diagnostic.wizard', ['target' => $mKey]) }}" class="btn btn-outline btn-sm" style="width:100%;">
                                Assess {{ $module['short'] }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endif

{{-- Section 4: Solid Slate Band — WordPress Integration Kit for www.bridge-tailor.com --}}
@if($uiVisible('vis.landing_wp_integration_kit'))
<section class="sec-slate">
    <div class="bt-container">
        <div class="sec-header">
            <div>
                <div class="eyebrow">OPTION A · INDEPENDENT LARAVEL APPLICATION AT DIAGNOSTICS.BRIDGE-TAILOR.COM</div>
                <h2 style="font-size:23px;">{{ $uiText('text.wp_kit_title') }}</h2>
            </div>
            <span class="tag tag-solid">ASTRA THEME + ELEMENTOR READY</span>
        </div>

        <div class="flat-grid-2">
            <div class="flat-box">
                <div class="eyebrow">1. REPLACE "TAKE OUR BUSINESSHEALTH TEST" CTA BUTTON</div>
                <h4 style="font-size:13.5px;margin-bottom:6px;">Elementor / Astra Header &amp; Hero Link Snippet</h4>
                <p style="font-size:12px;margin:0 0 8px;">
                    Point the existing <strong>"Take our businessHealth Test"</strong> button in WordPress (Astra / Elementor) directly to <code>https://diagnostics.bridge-tailor.com/assess</code> or paste this solid-styled HTML block into an Elementor HTML widget:
                </p>
                <pre style="background:#141210;color:#f2f5f7;padding:10px;font-size:11px;overflow-x:auto;margin:0;border:1px solid #2c2927;"><code>&lt;!-- Bridge-Tailor WordPress CTA Block (No Gradients, Flat Compact) --&gt;
&lt;div style="background:#2c2927;color:#ffffff;border-left:4px solid #f17e01;padding:16px 20px;font-family:'Poppins',sans-serif;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;"&gt;
  &lt;div&gt;
    &lt;div style="font-size:10px;font-weight:700;letter-spacing:0.14em;color:#f17e01;text-transform:uppercase;"&gt;AI BUSINESS DIAGNOSTIC ENGINE&lt;/div&gt;
    &lt;div style="font-family:'DM Serif Display',serif;font-size:20px;margin-top:2px;"&gt;Evaluate your business across 5 core pillars in 6 minutes&lt;/div&gt;
  &lt;/div&gt;
  &lt;a href="https://diagnostics.bridge-tailor.com/assess" style="background:#f17e01;color:#141210;padding:10px 18px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;text-decoration:none;border:1px solid #f17e01;"&gt;
    Take Our Business Health Test &amp;rarr;
  &lt;/a&gt;
&lt;/div&gt;</code></pre>
            </div>

            <div class="flat-box">
                <div class="eyebrow">2. SUBDOMAIN DEPLOYMENT &amp; WORDPRESS SHORTCODE PLUGIN</div>
                <h4 style="font-size:13.5px;margin-bottom:6px;">Drop-In WordPress Shortcode &amp; DNS Configuration</h4>
                <p style="font-size:12px;margin:0 0 8px;">
                    Add an <code>A</code> or <code>CNAME</code> record for <code>diagnostics.bridge-tailor.com</code> pointing to this Laravel host. To embed a direct launcher anywhere inside WordPress pages or posts with <code>[bridge_tailor_diagnostic]</code>, add this snippet to your child theme’s <code>functions.php</code>:
                </p>
                <pre style="background:#141210;color:#f2f5f7;padding:10px;font-size:11px;overflow-x:auto;margin:0;border:1px solid #2c2927;"><code>// wp-content/themes/astra-child/functions.php
add_shortcode('bridge_tailor_diagnostic', function ($atts) {
    $a = shortcode_atts(['module' =&gt; '', 'label' =&gt; 'Take our Business Health Test'], $atts);
    $url = 'https://diagnostics.bridge-tailor.com/assess' . ($a['module'] ? '?target=' . urlencode($a['module']) : '');
    return '&lt;a class="bt-diag-cta" href="' . esc_url($url) . '" style="display:inline-block;background:#f17e01;color:#141210;padding:9px 18px;font-family:Poppins,sans-serif;font-size:12px;font-weight:700;text-transform:uppercase;text-decoration:none;border:1px solid #141210;"&gt;' . esc_html($a['label']) . ' &amp;rarr;&lt;/a&gt;';
});</code></pre>
            </div>
        </div>
    </div>
</section>
@endif
@endsection
