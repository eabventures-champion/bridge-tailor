@extends('layouts.app')

@section('title', 'Interactive Business Health Diagnostic')

@push('head')
<style>
    .wiz-shell {
        display: grid;
        grid-template-columns: 270px 1fr;
        min-height: calc(100vh - 110px);
        border-bottom: 1px solid var(--bt-border);
    }
    @media (max-width: 960px) {
        .wiz-shell { grid-template-columns: 1fr; }
        .wiz-rail {
            border-right: none;
            border-bottom: 2px solid var(--bt-amber);
            padding: 14px 12px;
        }
        #rail-modules {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;
        }
        .rail-mod { margin-bottom: 0; }
    }
    @media (max-width: 640px) {
        #rail-modules {
            grid-template-columns: 1fr;
        }
        .wiz-steps {
            grid-template-columns: repeat(2, 1fr) !important;
        }
        .wiz-step-tab {
            border-bottom: 1px solid var(--bt-border);
            padding: 8px 10px;
            font-size: 10.5px;
        }
        .symptom-head {
            flex-direction: column;
            align-items: flex-start;
            gap: 4px;
        }
        .pain-pip {
            width: 34px;
            height: 32px;
        }
    }
    .wiz-rail {
        background-color: var(--bt-espresso);
        color: var(--bt-white);
        border-right: 2px solid var(--bt-amber);
        padding: 20px 18px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .rail-mod {
        border: 1px solid #47413d;
        background-color: #211f1d;
        padding: 9px 11px;
        margin-bottom: 7px;
        opacity: 0.55;
    }
    .rail-mod.active {
        opacity: 1;
        border-color: var(--bt-amber);
        background-color: #342f2c;
    }
    .rail-mod-head {
        display: flex;
        align-items: center;
        gap: 9px;
    }
    .rail-badge {
        width: 28px;
        height: 28px;
        background-color: #3f3935;
        color: #d0c7be;
        font-weight: 800;
        font-size: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .rail-mod.active .rail-badge {
        background-color: var(--bt-amber);
        color: var(--bt-ink);
    }
    .wiz-steps {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        border-bottom: 2px solid var(--bt-espresso);
        background-color: var(--bt-slate);
    }
    .wiz-step-tab {
        padding: 10px 14px;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        border-right: 1px solid var(--bt-border);
        cursor: pointer;
        color: #6b625b;
        background-color: var(--bt-slate);
        text-align: left;
    }
    .wiz-step-tab.active {
        background-color: var(--bt-espresso);
        color: var(--bt-white);
    }
    .wiz-step-tab.completed {
        background-color: var(--bt-amber-pale);
        color: var(--bt-ink);
    }
    /* Symptom Rows */
    .symptom-item {
        border: 1px solid var(--bt-border);
        background-color: var(--bt-white);
        margin-bottom: 6px;
    }
    .symptom-item.selected {
        border-color: var(--bt-ink);
        border-left: 4px solid var(--bt-amber);
        background-color: var(--bt-amber-pale);
    }
    .symptom-head {
        padding: 8px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-size: 13px;
        font-weight: 600;
    }
    .pain-bar {
        padding: 6px 12px 9px;
        border-top: 1px solid #e2c7a4;
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
    .pain-pip {
        width: 28px;
        height: 25px;
        border: 1px solid var(--bt-ink);
        background-color: var(--bt-white);
        color: var(--bt-ink);
        font-size: 11px;
        font-weight: 800;
        cursor: pointer;
    }
    .pain-pip.on {
        background-color: var(--bt-ink);
        color: var(--bt-white);
    }
    /* Gateway Question Blocks */
    .q-block {
        border: 1px solid var(--bt-border);
        background-color: var(--bt-white);
        padding: 12px 14px;
        margin-bottom: 10px;
    }
    .q-block.followup {
        border-left: 4px solid var(--bt-amber-deep);
        background-color: #fffaf3;
    }
    .q-opt-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
        margin-top: 8px;
    }
    @media (max-width: 720px) {
        .q-opt-grid { grid-template-columns: 1fr; }
    }
    .q-opt-btn {
        border: 1px solid #b8aea2;
        background-color: var(--bt-white);
        color: var(--bt-ink);
        padding: 8px 10px;
        text-align: left;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .q-opt-btn:hover {
        background-color: var(--bt-cream);
    }
    .q-opt-btn.selected-0 {
        background-color: var(--bt-teal);
        border-color: var(--bt-teal);
        color: #fff;
    }
    .q-opt-btn.selected-1 {
        background-color: var(--bt-amber);
        border-color: var(--bt-ink);
        color: var(--bt-ink);
    }
    .q-opt-btn.selected-2 {
        background-color: var(--bt-ink);
        border-color: var(--bt-ink);
        color: #fff;
    }
    .mod-subnav {
        display: flex;
        gap: 4px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .mod-subnav-btn {
        border: 1px solid var(--bt-border-strong);
        background-color: var(--bt-white);
        color: var(--bt-ink);
        padding: 6px 11px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        font-family: inherit;
    }
    .mod-subnav-btn.current {
        background-color: var(--bt-ink);
        color: var(--bt-white);
    }
    .mod-subnav-btn.done {
        border-bottom: 3px solid var(--bt-amber);
    }
</style>
@endpush

@section('content')
<form id="diagnostic-form" action="{{ route('diagnostic.submit') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($prefillCompany)
        <input type="hidden" name="existing_company_ulid" value="{{ $prefillCompany->ulid }}">
    @endif

    <div class="wiz-shell">
        <!-- Left Rail: Live Diagnostic Path & Severity Preview -->
        <aside class="wiz-rail">
            <div>
                <div class="eyebrow">BRIDGE-TAILOR METHODOLOGY</div>
                <div class="serif" style="font-size:21px;color:#fff;margin-bottom:14px;">Live Diagnostic Path</div>

                <div id="rail-modules">
                    @foreach($fw->modules() as $mKey => $module)
                        <div class="rail-mod active" data-rail-mod="{{ $mKey }}">
                            <div class="rail-mod-head">
                                <div class="rail-badge">{{ $mKey }}</div>
                                <div>
                                    <div style="font-size:12px;font-weight:700;color:#fff;line-height:1.2;">{{ $module['short'] ?? $module['name'] }}</div>
                                    <div style="font-size:10.5px;color:#cac4c4;margin-top:2px;" data-rail-status="{{ $mKey }}">In scope · awaiting input</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flat-box-dark" style="margin-top:14px;padding:10px 12px;font-size:11px;">
                    <div style="font-weight:800;color:var(--bt-amber);text-transform:uppercase;margin-bottom:4px;">LIVE PROGRESS</div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span>Symptoms Flagged:</span>
                        <strong id="rail-symptom-count">0</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <span>Evidence Answered:</span>
                        <strong id="rail-answer-count">0 / 0</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;">
                        <span>Sequencing Gate:</span>
                        <strong id="rail-gate-note" style="color:var(--bt-amber);">Active</strong>
                    </div>
                </div>
            </div>

            <div style="font-size:11px;color:#b2a89f;line-height:1.45;border-top:1px solid #443e3a;padding-top:12px;">
                Pain rating (1–5) + gateway evidence answers together determine functional scores. Questions adapt automatically to your business stage and sector.
            </div>
        </aside>

        <!-- Main Wizard Area -->
        <div>
            <!-- 4 Step Tabs -->
            <div class="wiz-steps">
                <button type="button" class="wiz-step-tab active" data-step-tab="1" onclick="gotoStep(1)">
                    01 · Business Profile &amp; Scope
                </button>
                <button type="button" class="wiz-step-tab" data-step-tab="2" onclick="gotoStep(2)">
                    02 · What's Going On (Intake)
                </button>
                <button type="button" class="wiz-step-tab" data-step-tab="3" onclick="gotoStep(3)">
                    03 · A Closer Look (Adaptive)
                </button>
                <button type="button" class="wiz-step-tab" data-step-tab="4" onclick="gotoStep(4)">
                    04 · Documents, Consent &amp; Generate
                </button>
            </div>

            <!-- STEP 1: BUSINESS PROFILE & ASSESSMENT SCOPE (Solid Cream Band) -->
            <section id="wiz-step-1" class="sec-cream" style="min-height:calc(100vh - 160px);">
                <div class="bt-container">
                    <div class="sec-header">
                        <div>
                            <div class="eyebrow">STEP 01 OF 04 · ASSEMBLE COMPANY CONTEXT</div>
                            <h1 style="font-size:26px;">{{ $uiText('text.wizard_step1_title') }}</h1>
                        </div>
                        @if($uiVisible('vis.wizard_demo_prefill_button'))
                        <button type="button" class="btn btn-outline btn-sm" onclick="fillDemoBusiness()">
                            Prefill Sample African SME Profile
                        </button>
                        @endif
                    </div>

                    <div class="flat-grid-2" style="margin-bottom:14px;">
                        <div class="flat-box">
                            <div class="eyebrow">CONTACT &amp; ENTERPRISE IDENTITY</div>
                            <div class="flat-grid-2">
                                <div class="field">
                                    <label>Your Full Name *</label>
                                    <input type="text" name="contact_name" id="f_contact_name" required value="{{ old('contact_name', $prefillCompany?->contact_name ?? auth()->user()?->name ?? '') }}" placeholder="e.g. Kwame Osei-Tutu">
                                </div>
                                <div class="field">
                                    <label>Company Name *</label>
                                    <input type="text" name="company_name" id="f_company_name" required value="{{ old('company_name', $prefillCompany?->name ?? '') }}" placeholder="e.g. Savannah Foods & Processing Ltd">
                                </div>
                                <div class="field">
                                    <label>Business Email *</label>
                                    <input type="email" name="contact_email" id="f_contact_email" required value="{{ old('contact_email', $prefillCompany?->contact_email ?? auth()->user()?->email ?? '') }}" placeholder="you@company.com">
                                </div>
                                <div class="field">
                                    <label>Phone / WhatsApp</label>
                                    <input type="text" name="contact_phone" id="f_contact_phone" value="{{ old('contact_phone', $prefillCompany?->contact_phone ?? auth()->user()?->phone ?? '') }}" placeholder="+233 24 000 0000">
                                </div>
                                <div class="field">
                                    <label>Country</label>
                                    <input type="text" name="country" id="f_country" value="{{ old('country', $prefillCompany?->country ?? 'Ghana') }}">
                                </div>
                                <div class="field">
                                    <label>City / Hub</label>
                                    <input type="text" name="city" id="f_city" value="{{ old('city', $prefillCompany?->city ?? 'Accra') }}">
                                </div>
                            </div>
                        </div>

                        <div class="flat-box">
                            <div class="eyebrow">ADAPTIVE CLASSIFICATION (DRIVES QUESTION BRANCHING &amp; BENCHMARKS)</div>
                            <div class="flat-grid-2">
                                <div class="field">
                                    <label>Business Stage *</label>
                                    <select name="stage" id="f_stage" onchange="onProfileChange()">
                                        @foreach($fw->stages() as $k => $label)
                                            <option value="{{ $k }}" {{ old('stage', $prefillCompany?->stage ?? 'growth') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Industry Sector *</label>
                                    <select name="sector" id="f_sector" onchange="onProfileChange()">
                                        @foreach($fw->sectors() as $k => $label)
                                            <option value="{{ $k }}" {{ old('sector', $prefillCompany?->sector ?? 'general') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Workforce Size</label>
                                    <select name="size_band" id="f_size_band">
                                        @foreach($fw->get('size_bands', []) as $k => $label)
                                            <option value="{{ $k }}" {{ old('size_band', $prefillCompany?->size_band ?? '21-50') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="field">
                                    <label>Annual Revenue Band (GHS)</label>
                                    <select name="revenue_band" id="f_revenue_band">
                                        @foreach($fw->get('revenue_bands', []) as $k => $label)
                                            <option value="{{ $k }}" {{ old('revenue_band', $prefillCompany?->revenue_band ?? '3to6') === $k ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div style="font-size:11.5px;background:var(--bt-slate);border:1px solid var(--bt-border);padding:7px 10px;margin-top:4px;">
                                <strong>Adaptive Engine Note:</strong> Selecting <em>Solo founder</em> automatically skips multi-layer board &amp; staff delegation questions. Selecting <em>Oil &amp; Gas, Agritech, Fintech, Manufacturing, or Retail</em> activates sector-specific regulatory and operational checks.
                            </div>
                        </div>
                    </div>

                    <div class="flat-grid-2" style="margin-bottom:14px;">
                        <div class="flat-box">
                            <div class="field" style="margin-bottom:0;">
                                <label>12-Month Strategic Objectives</label>
                                <textarea name="objectives" id="f_objectives" rows="2" placeholder="What are you aiming to achieve over the next 12–24 months? (e.g. raise growth capital, expand into new markets, reduce founder dependency)">{{ old('objectives', $prefillCompany?->objectives ?? '') }}</textarea>
                            </div>
                        </div>
                        <div class="flat-box">
                            <div class="field" style="margin-bottom:0;">
                                <label>Primary Operational Challenges</label>
                                <textarea name="challenges" id="f_challenges" rows="2" placeholder="Where is the business currently experiencing the greatest friction or uncertainty?">{{ old('challenges', $prefillCompany?->challenges ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Assessment Scope: Comprehensive (all 5 modules) or Targeted functional assessment -->
                    <div class="flat-box">
                        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                            <div>
                                <div class="eyebrow">DIAGNOSTIC MODE &amp; FUNCTIONAL SCOPE</div>
                                <h4 style="font-size:14px;">Conduct a Comprehensive 5-Pillar Assessment or Target Specific Functions</h4>
                            </div>
                            <div style="display:flex;gap:8px;">
                                <label style="font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                                    <input type="radio" name="mode" value="comprehensive" checked onchange="setAssessmentMode('comprehensive')">
                                    Comprehensive (All 5 Modules)
                                </label>
                                <label style="font-size:12px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:5px;">
                                    <input type="radio" name="mode" value="targeted" {{ request()->filled('target') ? 'checked' : '' }} onchange="setAssessmentMode('targeted')">
                                    Targeted Function(s)
                                </label>
                            </div>
                        </div>

                        <div class="bt-scope-grid">
                            @foreach($fw->modules() as $mKey => $module)
                                @php
                                    $checked = !request()->filled('target') || request()->query('target') === $mKey;
                                @endphp
                                <label class="flat-box" style="padding:9px 10px;cursor:pointer;background:var(--bt-cream);border-color:var(--bt-ink);">
                                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                        <span class="tag tag-amber">{{ $mKey }}</span>
                                        <input type="checkbox" name="modules_in_scope[]" value="{{ $mKey }}" class="scope-checkbox" {{ $checked ? 'checked' : '' }} onchange="onScopeChange()">
                                    </div>
                                    <div style="font-weight:700;font-size:12px;line-height:1.25;">{{ $module['name'] }}</div>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:16px;">
                        <button type="button" class="btn btn-amber" onclick="gotoStep(2)">
                            Continue to Step 02: Symptom &amp; Pain Intake →
                        </button>
                    </div>
                </div>
            </section>

            <!-- STEP 2: SYMPTOM INTAKE & PAIN RATING (Solid White Band) -->
            <section id="wiz-step-2" class="sec-white" style="display:none;min-height:calc(100vh - 160px);">
                <div class="bt-container">
                    <div class="sec-header">
                        <div>
                            <div class="eyebrow">STEP 02 OF 04 · BRIDGE-TAILOR INTAKE METHODOLOGY ("HOW IT FEELS")</div>
                            <h1 style="font-size:26px;">{{ $uiText('text.wizard_step2_title') }}</h1>
                        </div>
                        <div style="font-size:12px;font-weight:600;color:var(--bt-umber);">
                            Select every statement that sounds familiar and rate its severity from <strong>1 (minor friction)</strong> to <strong>5 (acute strain)</strong>.
                        </div>
                    </div>

                    <div class="flat-grid-2">
                        @foreach($fw->get('symptom_categories', []) as $catKey => $catLabel)
                            <div class="flat-box" style="background:var(--bt-slate);">
                                <div class="eyebrow" style="margin-bottom:8px;">CATEGORY {{ $catKey }} · {{ strtoupper($catLabel) }}</div>
                                @foreach($fw->symptoms() as $sym)
                                    @if($sym['cat'] === $catKey)
                                        <div class="symptom-item" id="sym-box-{{ $sym['id'] }}">
                                            <div class="symptom-head" onclick="toggleSymptom('{{ $sym['id'] }}')">
                                                <span>"{{ $sym['label'] }}"</span>
                                                <span style="font-size:10.5px;font-weight:800;color:var(--bt-umber);">
                                                    {{ implode(' · ', array_keys($sym['modules'])) }}
                                                </span>
                                            </div>
                                            <div class="pain-bar" id="sym-pain-{{ $sym['id'] }}" style="display:none;">
                                                <span style="font-size:10.5px;font-weight:800;text-transform:uppercase;color:var(--bt-amber-dark);">Pain Rating (1–5):</span>
                                                @for($n = 1; $n <= 5; $n++)
                                                    <button type="button" class="pain-pip" data-pip="{{ $sym['id'] }}-{{ $n }}" onclick="setSymptomPain('{{ $sym['id'] }}', {{ $n }})">{{ $n }}</button>
                                                @endfor
                                                <input type="hidden" name="symptoms[{{ $sym['id'] }}]" id="sym-input-{{ $sym['id'] }}" value="" disabled>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div class="flat-box" style="margin-top:14px;background:var(--bt-cream);">
                        <div class="field" style="margin-bottom:0;">
                            <label>In Your Own Words — Additional Context Behind What You Selected (Passed to Consultant &amp; AI Synthesis)</label>
                            <textarea name="custom_issue" rows="2" placeholder="Describe any specific context, recent compliance surprise, funder feedback, or growth bottleneck in your own words...">{{ old('custom_issue') }}</textarea>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:16px;">
                        <button type="button" class="btn btn-outline" onclick="gotoStep(1)">← Back to Business Profile</button>
                        <button type="button" class="btn btn-amber" onclick="gotoStep(3)">Continue to Step 03: Evidence Questions →</button>
                    </div>
                </div>
            </section>

            <!-- STEP 3: ADAPTIVE GATEWAY & EVIDENCE QUESTIONNAIRE (Solid Slate Band) -->
            <section id="wiz-step-3" class="sec-slate" style="display:none;min-height:calc(100vh - 160px);">
                <div class="bt-container">
                    <div class="sec-header">
                        <div>
                            <div class="eyebrow">STEP 03 OF 04 · OBJECTIVE EVIDENCE CHECK ("WHAT THE EVIDENCE SHOWS")</div>
                            <h1 style="font-size:26px;">{{ $uiText('text.wizard_step3_title') }}</h1>
                        </div>
                        <div style="font-size:12px;font-weight:700;color:var(--bt-umber);">
                            Ordered best → worst: <span class="tag tag-solid">Option 1: In Place (100)</span>
                            <span class="tag tag-medium">Option 2: Partial (50)</span>
                            <span class="tag tag-high">Option 3: Missing (0)</span>
                        </div>
                    </div>

                    <!-- Sub-navigation across modules in scope -->
                    <div class="mod-subnav" id="module-subnav"></div>

                    @foreach($fw->modules() as $mKey => $module)
                        <div class="module-pane" id="mod-pane-{{ $mKey }}" style="display:none;">
                            <div class="flat-box" style="background:var(--bt-espresso);color:#fff;margin-bottom:12px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
                                <div>
                                    <div class="eyebrow">MODULE {{ $mKey }}</div>
                                    <h3 style="font-size:18px;color:#fff;">{{ $module['name'] }}</h3>
                                    <div style="font-size:12px;color:#d0c7be;">{{ $module['description'] }}</div>
                                </div>
                                <div style="text-align:right;">
                                    <div style="font-size:10.5px;text-transform:uppercase;color:var(--bt-amber);">Live Module Score Preview</div>
                                    <div id="live-mod-score-{{ $mKey }}" style="font-size:22px;font-weight:800;">— / 100</div>
                                </div>
                            </div>

                            @foreach($module['criteria'] as $cKey => $criterion)
                                <div style="margin-bottom:14px;">
                                    <div style="display:flex;justify-content:space-between;align-items:center;background:#e4dcd1;padding:6px 11px;border:1px solid #c7;font-size:11.5px;font-weight:800;text-transform:uppercase;">
                                        <span>CRITERION {{ $cKey }} · {{ $criterion['name'] }}</span>
                                        @php $prog = $fw->programme($criterion['programme'] ?? ''); @endphp
                                        @if($prog)
                                            <span style="color:var(--bt-amber-dark);">Routes to: {{ $prog['line'] }} ({{ $prog['entry'] }})</span>
                                        @endif
                                    </div>

                                    @foreach($criterion['questions'] as $q)
                                        <div class="q-block {{ !empty($q['show_if']) ? 'followup' : '' }}"
                                             id="q-block-{{ $q['code'] }}"
                                             data-q-code="{{ $q['code'] }}"
                                             data-q-module="{{ $mKey }}"
                                             data-skip-stages="{{ json_encode($q['skip_stages'] ?? []) }}"
                                             data-sectors="{{ json_encode($q['sectors'] ?? []) }}"
                                             data-show-if="{{ json_encode($q['show_if'] ?? null) }}">
                                            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                                                <div style="font-weight:700;font-size:13.5px;">
                                                    @if(!empty($q['show_if']))
                                                        <span class="tag tag-amber" style="margin-right:5px;">ADAPTIVE FOLLOW-UP</span>
                                                    @endif
                                                    @if(!empty($q['sectors']))
                                                        <span class="tag tag-medium" style="margin-right:5px;">SECTOR-SPECIFIC</span>
                                                    @endif
                                                    {{ $q['text'] }}
                                                </div>
                                                @if(!empty($q['risk']))
                                                    <span class="tag {{ $q['risk'] === 'critical' ? 'tag-critical' : 'tag-medium' }}">
                                                        {{ strtoupper($q['risk']) }} RISK CHECK
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="q-opt-grid">
                                                @foreach($q['options'] as $optIdx => $optLabel)
                                                    <button type="button"
                                                            class="q-opt-btn"
                                                            data-opt-btn="{{ $q['code'] }}-{{ $optIdx }}"
                                                            onclick="selectAnswer('{{ $q['code'] }}', {{ $optIdx }}, '{{ $mKey }}')">
                                                        <span>{{ $optLabel }}</span>
                                                        <span style="font-size:10px;opacity:0.75;">{{ $optIdx === 0 ? '100' : ($optIdx === 1 ? '50' : '0') }}</span>
                                                    </button>
                                                @endforeach
                                            </div>
                                            <input type="hidden" name="answers[{{ $q['code'] }}]" id="ans-input-{{ $q['code'] }}" value="" disabled>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:16px;">
                        <button type="button" class="btn btn-outline" onclick="prevModuleOrStep()">← Previous</button>
                        <button type="button" class="btn btn-amber" id="next-mod-btn" onclick="nextModuleOrStep()">Next Module →</button>
                    </div>
                </div>
            </section>

            <!-- STEP 4: SUPPORTING DOCUMENTS, PRIVACY CONSENT & REPORT GENERATION (Solid Cream Band) -->
            <section id="wiz-step-4" class="sec-cream" style="display:none;min-height:calc(100vh - 160px);">
                <div class="bt-container">
                    <div class="sec-header">
                        <div>
                            <div class="eyebrow">STEP 04 OF 04 · SUPPORTING EVIDENCE &amp; GOVERNANCE CONTROLS</div>
                            <h1 style="font-size:26px;">Supporting Documents, AI Consent &amp; Report Generation</h1>
                        </div>
                    </div>

                    <div class="flat-grid-2" style="margin-bottom:14px;">
                        <div class="flat-box">
                            <div class="eyebrow">OPTIONAL · AI DOCUMENT EXTRACTION VAULT</div>
                            <h4 style="font-size:14px;margin-bottom:6px;">Upload Financial Statements, Management Accounts, or Business Plan</h4>
                            <p style="font-size:12px;color:#443e3a;margin:0 0 10px;">
                                Uploaded files are hashed (SHA-256), stored outside the public web root, and inspected for explicit financial or governance evidence. The AI never invents figures that are not present in your document.
                            </p>
                            <div class="field">
                                <label>Document Category</label>
                                <select name="category">
                                    <option value="financial_statement">Audited / Reviewed Financial Statements</option>
                                    <option value="management_accounts">Monthly Management Accounts / Cash Flow</option>
                                    <option value="business_plan">Strategic / Business Plan or Pitch Deck</option>
                                    <option value="governance_doc">Board Charter / Org Structure / Compliance</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Select Document (PDF, TXT, CSV, DOCX, XLSX — Max 10MB)</label>
                                <input type="file" name="supporting_document">
                            </div>
                            <div class="field" style="margin-bottom:0;">
                                <label>Document Retention &amp; Auto-Purge Policy</label>
                                <select name="retention_policy">
                                    <option value="6_months">6 Months (Purge after initial engagement)</option>
                                    <option value="18_months" selected>18 Months (Standard annual YoY benchmark comparison)</option>
                                    <option value="36_months">36 Months (Multi-year transformation programme)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flat-box">
                            <div class="eyebrow">SECURITY, PRIVACY &amp; EPISTEMIC INTEGRITY</div>
                            <h4 style="font-size:14px;margin-bottom:6px;">How Bridge Tailor Processes Your Diagnostic Data</h4>
                            <table class="bt-table" style="margin-bottom:12px;font-size:11.5px;">
                                <tr>
                                    <td style="font-weight:700;width:34%;">Controlled Scoring</td>
                                    <td>All 0–100 scores are computed deterministically by Bridge Tailor’s scoring engine, never by generative guesswork.</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:700;">Server-Side AI Key</td>
                                    <td>OpenAI Responses API calls execute strictly on the Laravel server using Structured Outputs; keys never touch the browser.</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:700;">Fact vs. Assumption</td>
                                    <td>Every finding is tagged as <code>CLIENT-PROVIDED</code>, <code>DOCUMENT-EVIDENCED</code>, or <code>CONSULTANT-ASSUMPTION</code>.</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:700;">Consultant Review</td>
                                    <td>A Bridge Tailor consultant can review, amend, approve, and publish your final diagnostic report.</td>
                                </tr>
                            </table>

                            <label style="display:flex;align-items:flex-start;gap:8px;background:var(--bt-amber-pale);border:1px solid var(--bt-ink);padding:10px 12px;cursor:pointer;font-size:12px;font-weight:600;">
                                <input type="checkbox" name="ai_consent" value="1" checked style="margin-top:3px;">
                                <span>
                                    I consent to Bridge Tailor processing these responses and any uploaded documents through the Bridge Tailor Diagnostic Engine (including OpenAI Responses API Structured Outputs) under Bridge Tailor’s confidentiality &amp; retention policy.
                                </span>
                            </label>
                        </div>
                    </div>

                    <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;border-top:2px solid var(--bt-espresso);padding-top:14px;">
                        <button type="button" class="btn btn-outline" onclick="gotoStep(3)">← Back to Questionnaire</button>
                        <button type="submit" class="btn btn-amber" style="padding:11px 24px;font-size:13px;">
                            Compute Scores &amp; Generate AI Diagnostic Report →
                        </button>
                    </div>
                </div>
            </section>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    const FW = @json($frameworkPayload);
    let currentStep = 1;
    let activeModIndex = 0;
    const selectedSymptoms = {}; // id => pain 1..5
    const selectedAnswers = {};  // code => 0..2

    function getScopedModules() {
        const boxes = document.querySelectorAll('.scope-checkbox:checked');
        const list = Array.from(boxes).map(cb => cb.value);
        return list.length ? list : ['SG'];
    }

    function setAssessmentMode(mode) {
        if (mode === 'comprehensive') {
            document.querySelectorAll('.scope-checkbox').forEach(cb => cb.checked = true);
        }
        onScopeChange();
    }

    function onScopeChange() {
        const scoped = getScopedModules();
        document.querySelectorAll('[data-rail-mod]').forEach(el => {
            const m = el.getAttribute('data-rail-mod');
            if (scoped.includes(m)) {
                el.classList.add('active');
            } else {
                el.classList.remove('active');
            }
        });
        updateVisibilityAndCounts();
    }

    function onProfileChange() {
        updateVisibilityAndCounts();
    }

    function toggleSymptom(id) {
        const box = document.getElementById('sym-box-' + id);
        const painBar = document.getElementById('sym-pain-' + id);
        const input = document.getElementById('sym-input-' + id);

        if (selectedSymptoms[id] !== undefined) {
            delete selectedSymptoms[id];
            box.classList.remove('selected');
            painBar.style.display = 'none';
            input.disabled = true;
            input.value = '';
        } else {
            selectedSymptoms[id] = 3;
            box.classList.add('selected');
            painBar.style.display = 'flex';
            input.disabled = false;
            input.value = 3;
            renderPainPips(id, 3);
        }
        updateVisibilityAndCounts();
    }

    function setSymptomPain(id, pain) {
        selectedSymptoms[id] = pain;
        const input = document.getElementById('sym-input-' + id);
        input.disabled = false;
        input.value = pain;
        renderPainPips(id, pain);
        updateVisibilityAndCounts();
    }

    function renderPainPips(id, pain) {
        for (let n = 1; n <= 5; n++) {
            const btn = document.querySelector(`[data-pip="${id}-${n}"]`);
            if (btn) {
                btn.classList.toggle('on', n <= pain);
            }
        }
    }

    function selectAnswer(code, optIdx, modKey) {
        selectedAnswers[code] = optIdx;
        const input = document.getElementById('ans-input-' + code);
        input.disabled = false;
        input.value = optIdx;

        for (let i = 0; i < 3; i++) {
            const btn = document.querySelector(`[data-opt-btn="${code}-${i}"]`);
            if (btn) {
                btn.classList.remove('selected-0', 'selected-1', 'selected-2');
                if (i === optIdx) {
                    btn.classList.add('selected-' + optIdx);
                }
            }
        }
        updateVisibilityAndCounts();
    }

    function isQuestionApplicable(qEl) {
        const stage = document.getElementById('f_stage').value;
        const sector = document.getElementById('f_sector').value;
        const scoped = getScopedModules();
        const mod = qEl.getAttribute('data-q-module');

        if (!scoped.includes(mod)) return false;

        const skipStages = JSON.parse(qEl.getAttribute('data-skip-stages') || '[]');
        if (skipStages.includes(stage)) return false;

        const sectors = JSON.parse(qEl.getAttribute('data-sectors') || '[]');
        if (sectors.length > 0 && !sectors.includes(sector)) return false;

        const showIf = JSON.parse(qEl.getAttribute('data-show-if') || 'null');
        if (showIf && showIf.q) {
            const parentAns = selectedAnswers[showIf.q];
            if (parentAns === undefined || parentAns < (showIf.min ?? 1)) {
                return false;
            }
        }
        return true;
    }

    function updateVisibilityAndCounts() {
        const scoped = getScopedModules();
        let totalApplicable = 0;
        let totalAnswered = 0;
        const modStats = {};
        scoped.forEach(m => modStats[m] = { total: 0, answered: 0, sum: 0 });

        document.querySelectorAll('.q-block').forEach(qEl => {
            const code = qEl.getAttribute('data-q-code');
            const mod = qEl.getAttribute('data-q-module');
            const input = document.getElementById('ans-input-' + code);
            const applicable = isQuestionApplicable(qEl);

            if (applicable) {
                qEl.style.display = 'block';
                totalApplicable++;
                if (modStats[mod]) modStats[mod].total++;
                if (selectedAnswers[code] !== undefined) {
                    input.disabled = false;
                    totalAnswered++;
                    if (modStats[mod]) {
                        modStats[mod].answered++;
                        const pts = selectedAnswers[code] === 0 ? 100 : (selectedAnswers[code] === 1 ? 50 : 0);
                        modStats[mod].sum += pts;
                    }
                }
            } else {
                qEl.style.display = 'none';
                input.disabled = true;
            }
        });

        document.getElementById('rail-symptom-count').textContent = Object.keys(selectedSymptoms).length;
        document.getElementById('rail-answer-count').textContent = `${totalAnswered} / ${totalApplicable}`;

        Object.keys(FW.modules).forEach(m => {
            const statusEl = document.querySelector(`[data-rail-status="${m}"]`);
            const liveEl = document.getElementById('live-mod-score-' + m);
            if (!scoped.includes(m)) {
                if (statusEl) statusEl.textContent = 'Out of scope';
                return;
            }
            const st = modStats[m];
            if (st && st.answered > 0) {
                const evidAvg = Math.round(st.sum / st.answered);
                if (statusEl) statusEl.textContent = `${st.answered}/${st.total} answered · Evid ${evidAvg}/100`;
                if (liveEl) liveEl.textContent = `${evidAvg} / 100`;
            } else if (st) {
                if (statusEl) statusEl.textContent = `0/${st.total} evidence checks`;
                if (liveEl) liveEl.textContent = '— / 100';
            }
        });

        renderModuleSubnav();
    }

    function renderModuleSubnav() {
        const scoped = getScopedModules();
        if (activeModIndex >= scoped.length) activeModIndex = 0;
        const activeMod = scoped[activeModIndex];

        const nav = document.getElementById('module-subnav');
        nav.innerHTML = '';
        scoped.forEach((m, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mod-subnav-btn' + (idx === activeModIndex ? ' current' : '');
            btn.textContent = `${idx + 1}. ${m} · ${FW.modules[m].short || FW.modules[m].name}`;
            btn.onclick = () => {
                activeModIndex = idx;
                showActiveModulePane();
            };
            nav.appendChild(btn);
        });

        document.querySelectorAll('.module-pane').forEach(p => p.style.display = 'none');
        const pane = document.getElementById('mod-pane-' + activeMod);
        if (pane) pane.style.display = 'block';

        const nextBtn = document.getElementById('next-mod-btn');
        if (nextBtn) {
            nextBtn.textContent = activeModIndex < scoped.length - 1
                ? `Next Module (${scoped[activeModIndex + 1]}) →`
                : 'Continue to Step 04: Documents & Generate Report →';
        }
    }

    function showActiveModulePane() {
        renderModuleSubnav();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function nextModuleOrStep() {
        const scoped = getScopedModules();
        if (activeModIndex < scoped.length - 1) {
            activeModIndex++;
            showActiveModulePane();
        } else {
            gotoStep(4);
        }
    }

    function prevModuleOrStep() {
        if (activeModIndex > 0) {
            activeModIndex--;
            showActiveModulePane();
        } else {
            gotoStep(2);
        }
    }

    function gotoStep(step) {
        if (step > 1) {
            const name = document.getElementById('f_contact_name').value.trim();
            const comp = document.getElementById('f_company_name').value.trim();
            const email = document.getElementById('f_contact_email').value.trim();
            if (!name || !comp || !email) {
                alert('Please enter your Name, Company Name, and Business Email in Step 01 before proceeding.');
                return;
            }
        }
        currentStep = step;
        for (let s = 1; s <= 4; s++) {
            const sec = document.getElementById('wiz-step-' + s);
            const tab = document.querySelector(`[data-step-tab="${s}"]`);
            if (sec) sec.style.display = (s === step) ? 'block' : 'none';
            if (tab) {
                tab.classList.remove('active', 'completed');
                if (s === step) tab.classList.add('active');
                else if (s < step) tab.classList.add('completed');
            }
        }
        updateVisibilityAndCounts();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function fillDemoBusiness() {
        document.getElementById('f_contact_name').value = 'Nana Ama Danquah';
        document.getElementById('f_company_name').value = 'Accra Cold-Chain & Foods Ltd';
        document.getElementById('f_contact_email').value = 'nama@accracoldchain.com.gh';
        document.getElementById('f_contact_phone').value = '+233 24 901 3344';
        document.getElementById('f_stage').value = 'growth';
        document.getElementById('f_sector').value = 'agritech';
        document.getElementById('f_size_band').value = '21-50';
        document.getElementById('f_revenue_band').value = '3to6';
        document.getElementById('f_objectives').value = 'Raise GHS 5M for solar cold-room expansion and enter supermarket distribution in Abidjan.';
        document.getElementById('f_challenges').value = 'Cash is tight despite strong sales, numbers arrive late, and funders asked for audited accounts and a data room we do not yet have.';

        // Select realistic symptoms & answers so user can test full flow in 1 click
        ['s2', 's5', 's8', 's10', 's11', 's14', 's19'].forEach(id => {
            if (selectedSymptoms[id] === undefined) toggleSymptom(id);
            setSymptomPain(id, 4);
        });
        const sampleAns = {
            sg_1: 1, sg_2: 1, fd4: 1, fd2: 2, sg_3: 2, sg_4: 0, fd1: 2, sg_f1: 2, fd3: 2, sg_5: 1, sg_6: 1,
            fc1: 1, fh_1: 2, fc2: 0, fc4: 2, fh_2: 2, fh_f1: 2, fc3: 1, fh_3: 1, ir2: 1, ir_f1: 1, ir4: 1, ir1: 1, ir3: 2,
            oe_1: 1, oe_2: 1, oe_3: 1, sec_mf: 1, oe_4: 1, oe_5: 0, oe_6: 1, oe_7: 1, oe_f1: 1,
            mg_1: 0, mg_2: 1, mg_3: 1, rm4: 1, mg_4: 1, rm3: 1, rm1: 1, rm2: 1, sec_ag: 2,
            hc_1: 1, hc_2: 2, hc_3: 1, hc_4: 1, hc_5: 0, hc_6: 1, hc_f1: 1, hc_7: 1
        };
        Object.entries(sampleAns).forEach(([code, idx]) => {
            const qEl = document.getElementById('q-block-' + code);
            if (qEl) selectAnswer(code, idx, qEl.getAttribute('data-q-module'));
        });
        updateVisibilityAndCounts();
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateVisibilityAndCounts();
    });
</script>
@endpush
