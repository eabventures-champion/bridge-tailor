@extends('layouts.app')

@section('title', 'Interactive Business Health Diagnostic')

@push('head')
<style>
    /* Modern Wizard Shell */
    .bt-wiz-wrapper {
        max-width: 1180px;
        margin: 0 auto;
        padding: 28px 20px 80px;
    }

    /* Top Stepper Header Card */
    .bt-wiz-header-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 24px -2px rgba(15, 23, 42, 0.06);
        overflow: hidden;
        margin-bottom: 24px;
    }

    /* Top Meta & Live Stats Strip */
    .bt-wiz-meta-strip {
        padding: 12px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
    }
    .bt-wiz-meta-left {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .bt-wiz-pill-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        background: #fff7ed;
        color: var(--bt-amber-deep, #c25e00);
        border: 1px solid #fed7aa;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }
    .bt-wiz-pill-badge::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background-color: var(--bt-amber, #f17e01);
    }
    .bt-wiz-meta-text {
        font-size: 12.5px;
        color: #475569;
        font-weight: 500;
    }
    .bt-wiz-meta-stats {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .bt-wiz-stat-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 12px;
        color: #64748b;
    }
    .bt-wiz-stat-chip strong {
        color: #0f172a;
        font-weight: 700;
    }
    .bt-wiz-stat-chip.highlight {
        background: #fff7ed;
        border-color: #fbd38d;
        color: #9a3412;
    }
    .bt-wiz-stat-chip.highlight strong {
        color: var(--bt-amber-deep, #c25e00);
    }

    /* Animated Slim Progress Track */
    .bt-wiz-progress-track {
        height: 4px;
        background: #f1f5f9;
        position: relative;
        overflow: hidden;
    }
    .bt-wiz-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f17e01 0%, #fb923c 100%);
        transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* 4 Interactive Step Tabs - Rounded Cards */
    .bt-wiz-steps-row {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        background: #ffffff;
        gap: 10px;
        padding: 10px 14px;
        border-radius: 0 0 16px 16px;
    }
    .bt-wiz-step-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px !important;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        transition: all 0.2s ease;
        position: relative;
    }
    .bt-wiz-step-item:last-child {
        border-right: 1px solid #e2e8f0;
    }
    .bt-wiz-step-item:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }
    .bt-wiz-step-item.active {
        background: #fff7ed;
        border-color: var(--bt-amber, #f17e01);
        box-shadow: 0 4px 12px rgba(241, 126, 1, 0.12) !important;
    }
    .bt-wiz-step-item.completed {
        background: #f0fdf4;
        border-color: #bbf7d0;
    }

    .bt-wiz-step-num {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #cbd5e1;
        background: #ffffff;
        color: #64748b;
        font-size: 13px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s ease;
    }
    .bt-wiz-step-item.active .bt-wiz-step-num {
        border-color: var(--bt-amber, #f17e01);
        background: var(--bt-amber, #f17e01);
        color: #ffffff;
        box-shadow: 0 0 0 3px rgba(241, 126, 1, 0.2);
    }
    .bt-wiz-step-item.completed .bt-wiz-step-num {
        border-color: #059669;
        background: #059669;
        color: #ffffff;
    }
    .bt-wiz-step-num .num-check {
        display: none;
    }
    .bt-wiz-step-item.completed .bt-wiz-step-num .num-text {
        display: none;
    }
    .bt-wiz-step-item.completed .bt-wiz-step-num .num-check {
        display: inline;
    }

    .bt-wiz-step-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
        min-width: 0;
    }
    .bt-wiz-step-title {
        font-size: 13.5px;
        font-weight: 700;
        color: #334155;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        transition: color 0.15s ease;
    }
    .bt-wiz-step-item.active .bt-wiz-step-title {
        color: #0f172a;
    }
    .bt-wiz-step-sub {
        font-size: 11px;
        color: #94a3b8;
        font-weight: 500;
    }
    .bt-wiz-step-item.active .bt-wiz-step-sub {
        color: var(--bt-amber-deep, #c25e00);
        font-weight: 600;
    }

    /* Responsive Stepper */
    @media (max-width: 900px) {
        .bt-wiz-steps-row {
            grid-template-columns: repeat(2, 1fr);
        }
        .bt-wiz-step-item {
            border-bottom: 1px solid #f1f5f9;
        }
        .bt-wiz-step-item.active {
            border-bottom: 2px solid var(--bt-amber, #f17e01);
        }
    }
    @media (max-width: 540px) {
        .bt-wiz-wrapper {
            padding: 16px 12px 60px;
        }
        .bt-wiz-steps-row {
            grid-template-columns: 1fr;
        }
        .bt-wiz-step-item {
            padding: 10px 14px;
        }
    }

    /* Modern Card Layouts */
    .bt-step-pane {
        animation: stepFadeIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    @keyframes stepFadeIn {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bt-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 24px 26px;
        box-shadow: 0 2px 14px rgba(15, 23, 42, 0.04);
        margin-bottom: 20px;
    }
    .bt-card-title {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 20px;
        color: #0f172a;
        margin: 0 0 6px;
    }
    .bt-card-sub {
        font-size: 13px;
        color: #64748b;
        margin: 0 0 16px;
        line-height: 1.5;
    }

    /* Section Banner Header */
    .bt-step-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 16px;
        margin-bottom: 22px;
        padding: 4px 2px;
    }
    .bt-step-eyebrow {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--bt-amber-deep, #c25e00);
        margin-bottom: 4px;
    }
    .bt-step-title {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 26px;
        color: #0f172a;
        margin: 0 0 6px;
        line-height: 1.25;
    }
    .bt-step-desc {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
        max-width: 680px;
        line-height: 1.5;
    }

    /* Demo Prefill Pill Button */
    .bt-prefill-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        background: #ffffff;
        border: 1.5px solid var(--bt-amber, #f17e01);
        color: var(--bt-amber-deep, #c25e00);
        border-radius: 8px;
        font-size: 12.5px;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(241, 126, 1, 0.12);
        transition: all 0.2s ease;
    }
    .bt-prefill-btn:hover {
        background: #fff7ed;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(241, 126, 1, 0.2);
    }
    .bt-prefill-btn:active {
        transform: translateY(0);
    }

    /* Modern Input Controls */
    .field {
        margin-bottom: 16px;
    }
    .field label {
        display: block;
        font-size: 11.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #334155;
        margin-bottom: 6px;
    }
    input[type="text"],
    input[type="email"],
    select,
    textarea {
        width: 100%;
        padding: 11px 14px;
        font-size: 13.5px;
        font-family: inherit;
        color: #0f172a;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 8px !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        outline: none;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: var(--bt-amber, #f17e01) !important;
        box-shadow: 0 0 0 3px rgba(241, 126, 1, 0.18) !important;
    }

    /* Scope Grid in Step 1 */
    .bt-scope-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 12px;
        margin-top: 14px;
    }
    .bt-scope-card {
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 12px 14px;
        background: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .bt-scope-card:hover {
        border-color: var(--bt-amber, #f17e01);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.05);
    }
    .bt-scope-card.active {
        border-color: var(--bt-amber, #f17e01);
        background: #fffdfa;
        box-shadow: 0 0 0 2px rgba(241, 126, 1, 0.15);
    }

    /* Symptom Intake Styling (Step 2) */
    .bt-symptom-card {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        border-radius: 10px;
        margin-bottom: 8px;
        transition: all 0.2s ease;
        overflow: hidden;
    }
    .bt-symptom-card:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    }
    .bt-symptom-card.selected {
        border-color: var(--bt-amber, #f17e01);
        border-left: 4px solid var(--bt-amber, #f17e01);
        background: #fffdfa;
    }
    .bt-symptom-head {
        padding: 12px 16px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        cursor: pointer;
        font-size: 13.5px;
        font-weight: 600;
        color: #1e293b;
        gap: 12px;
    }
    .bt-symptom-head span.quote {
        flex: 1;
        line-height: 1.45;
    }
    .bt-symptom-badge {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        color: #64748b;
        background: #f1f5f9;
        padding: 3px 7px;
        border-radius: 6px;
        flex-shrink: 0;
    }
    .bt-symptom-card.selected .bt-symptom-badge {
        background: #fff7ed;
        color: var(--bt-amber-deep, #c25e00);
    }

    /* Tactile Pain Rating Bar */
    .bt-pain-bar {
        padding: 10px 16px 14px;
        background: #fafafa;
        border-top: 1px dashed #fed7aa;
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
    }
    .bt-pain-label {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: #475569;
        margin-right: 4px;
    }
    .bt-pain-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 32px;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        background: #ffffff;
        color: #334155;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .bt-pain-pill:hover {
        border-color: var(--bt-amber, #f17e01);
        color: var(--bt-amber-deep, #c25e00);
        transform: translateY(-1px);
    }
    .bt-pain-pill.on {
        background: var(--bt-amber, #f17e01);
        border-color: var(--bt-amber, #f17e01);
        color: #ffffff;
        box-shadow: 0 2px 6px rgba(241, 126, 1, 0.3);
    }

    /* Evidence Questionnaire (Step 3) */
    .mod-subnav {
        display: flex;
        gap: 6px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    .mod-subnav-btn {
        border: 1px solid #cbd5e1;
        background: #ffffff;
        color: #334155;
        padding: 8px 14px;
        font-size: 12.5px;
        font-weight: 700;
        border-radius: 8px;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .mod-subnav-btn:hover {
        border-color: var(--bt-amber, #f17e01);
        background: #fff7ed;
    }
    .mod-subnav-btn.current {
        background: var(--bt-amber, #f17e01) !important;
        border-color: var(--bt-amber, #f17e01) !important;
        color: #141210 !important;
        font-weight: 800;
        box-shadow: 0 4px 14px rgba(241, 126, 1, 0.28) !important;
    }

    /* Module Hero Banner inside Step 3 - Crisp Rounded Light Card */
    .bt-module-banner {
        background: #ffffff;
        color: #0f172a;
        border-radius: 14px !important;
        padding: 20px 24px;
        margin-bottom: 18px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 14px;
        border: 1.5px solid var(--bt-amber, #f17e01);
        box-shadow: 0 4px 18px rgba(241, 126, 1, 0.08);
    }
    .bt-module-banner-score {
        background: #fff7ed;
        border: 1px solid #fed7aa;
        padding: 10px 18px;
        border-radius: 10px !important;
        text-align: right;
    }
    .bt-module-banner-score {
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid rgba(241, 126, 1, 0.4);
        padding: 10px 18px;
        border-radius: 10px;
        text-align: right;
    }

    /* Question Block */
    .q-block {
        border: 1px solid #e2e8f0;
        background: #ffffff;
        padding: 16px 18px;
        border-radius: 10px;
        margin-bottom: 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    .q-block:hover {
        border-color: #cbd5e1;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
    }
    .q-block.followup {
        border-left: 4px solid var(--bt-amber-deep, #c25e00);
        background: #fffdfa;
    }
    .q-opt-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        margin-top: 12px;
    }
    @media (max-width: 720px) {
        .q-opt-grid { grid-template-columns: 1fr; }
    }
    .q-opt-btn {
        border: 1.5px solid #cbd5e1;
        background: #ffffff;
        color: #1e293b;
        padding: 11px 13px;
        border-radius: 8px;
        text-align: left;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        transition: all 0.15s ease;
        line-height: 1.35;
    }
    .q-opt-btn:hover {
        border-color: #94a3b8;
        background: #f8fafc;
        transform: translateY(-1px);
    }
    .q-opt-btn.selected-0 {
        background: #ecfdf5;
        border-color: #059669;
        color: #065f46;
        box-shadow: 0 0 0 2px rgba(5, 150, 105, 0.2);
    }
    .q-opt-btn.selected-1 {
        background: #fffbeb;
        border-color: #d97706;
        color: #92400e;
        box-shadow: 0 0 0 2px rgba(217, 119, 6, 0.2);
    }
    .q-opt-btn.selected-2 {
        background: #f1f5f9;
        border-color: #475569;
        color: #0f172a;
        box-shadow: 0 0 0 2px rgba(71, 85, 105, 0.2);
    }

    /* Criterion Group Header */
    .bt-criterion-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f1f5f9;
        padding: 9px 14px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 800;
        text-transform: uppercase;
        color: #334155;
        margin-bottom: 10px;
    }

    /* Step Action Bottom Bar */
    .bt-step-actions {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 24px;
        padding-top: 18px;
        border-top: 1px solid #e2e8f0;
    }
    .bt-step-actions.end {
        justify-content: flex-end;
    }
    .bt-step-actions.between {
        justify-content: space-between;
    }

    /* Toast Notification */
    .bt-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: #0f172a;
        color: #ffffff;
        padding: 12px 20px;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.2);
        font-size: 13px;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        z-index: 9999;
        opacity: 0;
        transform: translateY(12px);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        pointer-events: none;
    }
    .bt-toast.show {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }
</style>
@endpush

@section('content')
<form id="diagnostic-form" action="{{ route('diagnostic.submit') }}" method="POST" enctype="multipart/form-data">
    @csrf
    @if($prefillCompany)
        <input type="hidden" name="existing_company_ulid" value="{{ $prefillCompany->ulid }}">
    @endif

    <div class="bt-wiz-wrapper">
        <!-- Top Modern Stepper & Progress Card -->
        <div class="bt-wiz-header-card">
            <!-- Meta & Real-Time Stats Strip -->
            <div class="bt-wiz-meta-strip">
                <div class="bt-wiz-meta-left">
                    <span class="bt-wiz-meta-text">Deterministic Business Health Evaluation &amp; Intervention Sequencing</span>
                </div>
                <div class="bt-wiz-meta-stats">
                    <div class="bt-wiz-stat-chip">
                        <span>🚩 Symptoms:</span>
                        <strong id="rail-symptom-count">0</strong>
                    </div>
                    <div class="bt-wiz-stat-chip">
                        <span>✓ Evidence Checks:</span>
                        <strong id="rail-answer-count">0 / 0</strong>
                    </div>
                    <div class="bt-wiz-stat-chip highlight">
                        <span>Progress:</span>
                        <strong id="bt-wiz-progress-pct">25%</strong>
                    </div>
                    <div class="bt-wiz-stat-chip" id="bt-draft-status" style="display:none;background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;font-size:11.5px;font-weight:700;">
                        <span>💾</span>
                        <span id="bt-draft-status-text">Draft saved live</span>
                    </div>
                    <button type="button" id="bt-reset-draft-link" onclick="clearDraft()" title="Reset all form fields and start fresh" style="display:none;background:none;border:none;color:#94a3b8;font-size:11px;font-weight:700;cursor:pointer;padding:3px 6px;text-decoration:underline;">
                        Reset Form
                    </button>
                    <!-- Hidden element to maintain full JS backward compatibility -->
                    <span id="rail-gate-note" style="display:none;">Active</span>
                </div>
            </div>

            <!-- Dynamic Slim Progress Line -->
            <div class="bt-wiz-progress-track">
                <div id="bt-wiz-progress-bar" class="bt-wiz-progress-fill" style="width: 25%;"></div>
            </div>

            <!-- 4 Clickable Step Tabs -->
            <div class="bt-wiz-steps-row">
                <button type="button" class="bt-wiz-step-item active" data-step-tab="1" onclick="gotoStep(1)">
                    <div class="bt-wiz-step-num">
                        <span class="num-text">1</span>
                        <span class="num-check">✓</span>
                    </div>
                    <div class="bt-wiz-step-info">
                        <span class="bt-wiz-step-title">01 · Business Profile</span>
                        <span class="bt-wiz-step-sub">Scope &amp; Context</span>
                    </div>
                </button>

                <button type="button" class="bt-wiz-step-item" data-step-tab="2" onclick="gotoStep(2)">
                    <div class="bt-wiz-step-num">
                        <span class="num-text">2</span>
                        <span class="num-check">✓</span>
                    </div>
                    <div class="bt-wiz-step-info">
                        <span class="bt-wiz-step-title">02 · Symptom Intake</span>
                        <span class="bt-wiz-step-sub">Operational Strain</span>
                    </div>
                </button>

                <button type="button" class="bt-wiz-step-item" data-step-tab="3" onclick="gotoStep(3)">
                    <div class="bt-wiz-step-num">
                        <span class="num-text">3</span>
                        <span class="num-check">✓</span>
                    </div>
                    <div class="bt-wiz-step-info">
                        <span class="bt-wiz-step-title">03 · Evidence Checks</span>
                        <span class="bt-wiz-step-sub">Adaptive Gateway</span>
                    </div>
                </button>

                <button type="button" class="bt-wiz-step-item" data-step-tab="4" onclick="gotoStep(4)">
                    <div class="bt-wiz-step-num">
                        <span class="num-text">4</span>
                        <span class="num-check">✓</span>
                    </div>
                    <div class="bt-wiz-step-info">
                        <span class="bt-wiz-step-title">04 · AI Consent &amp; Report</span>
                        <span class="bt-wiz-step-sub">Generate Roadmap</span>
                    </div>
                </button>
            </div>
        </div>

        <!-- Hidden container keeping module status elements for JS compatibility -->
        <div id="rail-modules" style="display:none;" aria-hidden="true">
            @foreach($fw->modules() as $mKey => $module)
                <div class="rail-mod active" data-rail-mod="{{ $mKey }}">
                    <div class="rail-badge">{{ $mKey }}</div>
                    <div data-rail-status="{{ $mKey }}">In scope · awaiting input</div>
                </div>
            @endforeach
        </div>

        <!-- ============================================================== -->
        <!-- STEP 1: BUSINESS PROFILE & ASSESSMENT SCOPE                    -->
        <!-- ============================================================== -->
        <section id="wiz-step-1" class="bt-step-pane">
            <div class="bt-step-header">
                <div>
                    <div class="bt-step-eyebrow">STEP 01 OF 04 · ASSEMBLE COMPANY CONTEXT</div>
                    <h1 class="bt-step-title">{{ $uiText('text.wizard_step1_title') }}</h1>
                    <p class="bt-step-desc">
                        Provide enterprise details and operating scope. The diagnostic engine dynamically adapts its question branching, stage baselines, and peer benchmarks.
                    </p>
                </div>
                <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                    <button type="button" id="bt-reset-btn-step1" class="btn btn-outline btn-sm" onclick="clearDraft()" style="display:none;font-size:12px;color:#64748b;border-color:#cbd5e1;padding:8px 14px;border-radius:8px;">
                        ↺ Reset Form
                    </button>
                    @if($uiVisible('vis.wizard_demo_prefill_button') && (app()->environment('local') || in_array(request()->getHost(), ['localhost', '127.0.0.1'])))
                    <button type="button" class="bt-prefill-btn" onclick="fillDemoBusiness()">
                        <span>⚡</span>
                        <span>Prefill Sample African SME Profile</span>
                    </button>
                    @endif
                </div>
            </div>

            <div class="flat-grid-2" style="margin-bottom:18px;">
                <div class="bt-card">
                    <div class="bt-step-eyebrow" style="margin-bottom:10px;">CONTACT &amp; ENTERPRISE IDENTITY</div>
                    <div class="flat-grid-2">
                        <div class="field">
                            <label>Your Full Name *</label>
                            <input type="text" name="contact_name" id="f_contact_name" required value="{{ old('contact_name', $prefillCompany?->contact_name ?? auth()->user()?->name ?? '') }}" placeholder="e.g. Kwame Osei-Tutu">
                        </div>
                        <div class="field">
                            <label>Company Name *</label>
                            <input type="text" name="company_name" id="f_company_name" required value="{{ old('company_name', $prefillCompany?->name ?? '') }}" placeholder="e.g. Savannah Foods &amp; Processing Ltd">
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

                <div class="bt-card">
                    <div class="bt-step-eyebrow" style="margin-bottom:10px;">ADAPTIVE CLASSIFICATION (BENCHMARK ENGINE)</div>
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
                    <div style="font-size:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:9px 12px;margin-top:2px;color:#475569;line-height:1.45;">
                        <strong style="color:var(--bt-amber-deep);">Adaptive Engine Note:</strong> Selecting <em>Solo founder</em> automatically skips multi-layer board &amp; staff delegation questions. Selecting <em>Oil &amp; Gas, Agritech, Fintech, Manufacturing, or Retail</em> activates specialized operational checks.
                    </div>
                </div>
            </div>

            <div class="flat-grid-2" style="margin-bottom:18px;">
                <div class="bt-card">
                    <div class="field" style="margin-bottom:0;">
                        <label>12-Month Strategic Objectives</label>
                        <textarea name="objectives" id="f_objectives" rows="3" placeholder="What are you aiming to achieve over the next 12–24 months? (e.g. raise growth capital, expand into new markets, reduce founder dependency)">{{ old('objectives', $prefillCompany?->objectives ?? '') }}</textarea>
                    </div>
                </div>
                <div class="bt-card">
                    <div class="field" style="margin-bottom:0;">
                        <label>Primary Operational Challenges</label>
                        <textarea name="challenges" id="f_challenges" rows="3" placeholder="Where is the business currently experiencing the greatest friction, bottlenecks, or uncertainty?">{{ old('challenges', $prefillCompany?->challenges ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Diagnostic Scope Selection Card -->
            <div class="bt-card">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:12px;">
                    <div>
                        <div class="bt-step-eyebrow">DIAGNOSTIC MODE &amp; FUNCTIONAL SCOPE</div>
                        <h4 style="font-size:15px;margin:2px 0 0;color:#0f172a;">Conduct Comprehensive 5-Pillar Assessment or Select Specific Functions</h4>
                    </div>
                    <div style="display:flex;gap:14px;">
                        <label style="font-size:12.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
                            <input type="radio" name="mode" value="comprehensive" checked onchange="setAssessmentMode('comprehensive')">
                            Comprehensive (All 5 Modules)
                        </label>
                        <label style="font-size:12.5px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;color:#334155;">
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
                        <label class="bt-scope-card {{ $checked ? 'active' : '' }}" id="scope-card-{{ $mKey }}">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                <span class="tag tag-amber" style="font-weight:800;">{{ $mKey }}</span>
                                <input type="checkbox" name="modules_in_scope[]" value="{{ $mKey }}" class="scope-checkbox" {{ $checked ? 'checked' : '' }} onchange="onScopeChange(); document.getElementById('scope-card-{{ $mKey }}').classList.toggle('active', this.checked);">
                            </div>
                            <div style="font-weight:700;font-size:13px;line-height:1.3;color:#0f172a;">{{ $module['name'] }}</div>
                            <div style="font-size:11px;color:#64748b;margin-top:4px;">{{ count($module['criteria'] ?? []) }} criteria groups</div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Bottom Navigation Bar -->
            <div class="bt-step-actions end">
                <button type="button" class="btn btn-amber btn-lg" onclick="gotoStep(2)">
                    Continue to Step 02: Symptom &amp; Pain Intake →
                </button>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- STEP 2: SYMPTOM INTAKE & PAIN RATING                          -->
        <!-- ============================================================== -->
        <section id="wiz-step-2" class="bt-step-pane" style="display:none;">
            <div class="bt-step-header">
                <div>
                    <div class="bt-step-eyebrow">STEP 02 OF 04 · BRIDGE-TAILOR INTAKE METHODOLOGY ("HOW IT FEELS")</div>
                    <h1 class="bt-step-title">{{ $uiText('text.wizard_step2_title') }}</h1>
                    <p class="bt-step-desc">
                        Select every statement that matches symptoms currently experienced in your business, and rate its severity from <strong>1 (minor friction)</strong> to <strong>5 (acute strain)</strong>.
                    </p>
                </div>
            </div>

            <div class="flat-grid-2">
                @foreach($fw->get('symptom_categories', []) as $catKey => $catLabel)
                    <div class="bt-card" style="padding:18px 20px;">
                        <div class="bt-step-eyebrow" style="margin-bottom:12px;">CATEGORY {{ $catKey }} · {{ strtoupper($catLabel) }}</div>
                        @foreach($fw->symptoms() as $sym)
                            @if($sym['cat'] === $catKey)
                                <div class="bt-symptom-card" id="sym-box-{{ $sym['id'] }}">
                                    <div class="bt-symptom-head" onclick="toggleSymptom('{{ $sym['id'] }}')">
                                        <span class="quote">“{{ $sym['label'] }}”</span>
                                        <span class="bt-symptom-badge">
                                            {{ implode(' · ', array_keys($sym['modules'])) }}
                                        </span>
                                    </div>
                                    <div class="bt-pain-bar" id="sym-pain-{{ $sym['id'] }}" style="display:none;">
                                        <span class="bt-pain-label">Pain Severity:</span>
                                        @for($n = 1; $n <= 5; $n++)
                                            <button type="button" class="bt-pain-pill" data-pip="{{ $sym['id'] }}-{{ $n }}" onclick="setSymptomPain('{{ $sym['id'] }}', {{ $n }})">{{ $n }}</button>
                                        @endfor
                                        <span style="font-size:11px;color:#64748b;margin-left:6px;" id="sym-pain-desc-{{ $sym['id'] }}">Moderate Strain</span>
                                        <input type="hidden" name="symptoms[{{ $sym['id'] }}]" id="sym-input-{{ $sym['id'] }}" value="" disabled>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>

            <div class="bt-card" style="margin-top:16px;">
                <div class="field" style="margin-bottom:0;">
                    <label>In Your Own Words — Additional Operational Context (Passed to Consultant &amp; AI Synthesis)</label>
                    <textarea name="custom_issue" rows="3" placeholder="Describe any specific context, recent compliance surprise, lender feedback, or supply chain bottleneck in your own words...">{{ old('custom_issue') }}</textarea>
                </div>
            </div>

            <!-- Bottom Navigation Bar -->
            <div class="bt-step-actions between">
                <button type="button" class="btn btn-outline" onclick="gotoStep(1)">← Back to Business Profile</button>
                <button type="button" class="btn btn-amber btn-lg" onclick="gotoStep(3)">Continue to Step 03: Evidence Questions →</button>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- STEP 3: ADAPTIVE GATEWAY & EVIDENCE QUESTIONNAIRE              -->
        <!-- ============================================================== -->
        <section id="wiz-step-3" class="bt-step-pane" style="display:none;">
            <div class="bt-step-header">
                <div>
                    <div class="bt-step-eyebrow">STEP 03 OF 04 · OBJECTIVE EVIDENCE CHECK ("WHAT THE EVIDENCE SHOWS")</div>
                    <h1 class="bt-step-title">{{ $uiText('text.wizard_step3_title') }}</h1>
                    <p class="bt-step-desc">
                        Deterministic functional evidence checks across all in-scope advisory modules. Select the option that genuinely matches your enterprise right now.
                    </p>
                </div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                    <span class="tag tag-solid" style="background:#059669;color:#fff;border-radius:6px;padding:5px 9px;">✓ Option 1: In Place (100)</span>
                    <span class="tag tag-medium" style="background:#d97706;color:#fff;border-radius:6px;padding:5px 9px;">⚡ Option 2: Partial (50)</span>
                    <span class="tag tag-high" style="background:#475569;color:#fff;border-radius:6px;padding:5px 9px;">✕ Option 3: Missing (0)</span>
                </div>
            </div>

            <!-- Module Segmented Pill Subnav -->
            <div class="mod-subnav" id="module-subnav"></div>

            @foreach($fw->modules() as $mKey => $module)
                <div class="module-pane" id="mod-pane-{{ $mKey }}" style="display:none;">
                    <!-- Module Banner Card -->
                    <div class="bt-module-banner">
                        <div>
                            <div class="bt-step-eyebrow" style="color:var(--bt-amber);">MODULE {{ $mKey }}</div>
                            <h3 style="font-size:20px;color:#fff;margin:2px 0 4px;">{{ $module['name'] }}</h3>
                            <div style="font-size:12.5px;color:#94a3b8;max-width:640px;line-height:1.45;">{{ $module['description'] }}</div>
                        </div>
                        <div class="bt-module-banner-score">
                            <div style="font-size:10.5px;text-transform:uppercase;color:var(--bt-amber);font-weight:700;">Live Module Score</div>
                            <div id="live-mod-score-{{ $mKey }}" style="font-size:22px;font-weight:800;color:#fff;">— / 100</div>
                        </div>
                    </div>

                    @foreach($module['criteria'] as $cKey => $criterion)
                        <div style="margin-bottom:20px;">
                            <div class="bt-criterion-header">
                                <span>CRITERION {{ $cKey }} · {{ $criterion['name'] }}</span>
                                @php $prog = $fw->programme($criterion['programme'] ?? ''); @endphp
                                @if($prog)
                                    <span style="color:var(--bt-amber-deep);font-weight:700;">Routes to: {{ $prog['line'] }} ({{ $prog['entry'] }})</span>
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
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:12px;">
                                        <div style="font-weight:700;font-size:14px;color:#0f172a;line-height:1.4;">
                                            @if(!empty($q['show_if']))
                                                <span class="tag tag-amber" style="margin-right:6px;border-radius:4px;">ADAPTIVE FOLLOW-UP</span>
                                            @endif
                                            @if(!empty($q['sectors']))
                                                <span class="tag tag-medium" style="margin-right:6px;border-radius:4px;">SECTOR-SPECIFIC</span>
                                            @endif
                                            {{ $q['text'] }}
                                        </div>
                                        @if(!empty($q['risk']))
                                            <span class="tag {{ $q['risk'] === 'critical' ? 'tag-critical' : 'tag-medium' }}" style="border-radius:4px;flex-shrink:0;">
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
                                                <span style="font-size:10.5px;font-weight:700;opacity:0.75;padding-left:8px;">{{ $optIdx === 0 ? '100 pts' : ($optIdx === 1 ? '50 pts' : '0 pts') }}</span>
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

            <!-- Bottom Navigation Bar -->
            <div class="bt-step-actions between">
                <button type="button" class="btn btn-outline" onclick="prevModuleOrStep()">← Previous Module / Step</button>
                <button type="button" class="btn btn-amber btn-lg" id="next-mod-btn" onclick="nextModuleOrStep()">Next Module →</button>
            </div>
        </section>

        <!-- ============================================================== -->
        <!-- STEP 4: SUPPORTING DOCUMENTS, PRIVACY CONSENT & REPORT        -->
        <!-- ============================================================== -->
        <section id="wiz-step-4" class="bt-step-pane" style="display:none;">
            <div class="bt-step-header">
                <div>
                    <div class="bt-step-eyebrow">STEP 04 OF 04 · SUPPORTING EVIDENCE &amp; GOVERNANCE CONTROLS</div>
                    <h1 class="bt-step-title">Supporting Documents, AI Consent &amp; Report Generation</h1>
                    <p class="bt-step-desc">
                        Optionally upload key corporate documents for verification, review privacy and security safeguards, and generate your comprehensive diagnostic report.
                    </p>
                </div>
            </div>

            <div class="flat-grid-2" style="margin-bottom:18px;">
                <div class="bt-card">
                    <div class="bt-step-eyebrow">OPTIONAL · AI DOCUMENT EXTRACTION VAULT</div>
                    <h4 style="font-size:15px;margin:4px 0 8px;color:#0f172a;">Upload Financial Statements, Management Accounts, or Business Plan</h4>
                    <p style="font-size:12.5px;color:#64748b;margin:0 0 14px;line-height:1.5;">
                        Uploaded files are hashed (SHA-256), stored outside the public web root, and inspected for explicit financial or governance evidence. The diagnostic engine never fabricates numbers.
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
                        <input type="file" name="supporting_document" style="padding:8px 12px;">
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

                <div class="bt-card">
                    <div class="bt-step-eyebrow">SECURITY, PRIVACY &amp; EPISTEMIC INTEGRITY</div>
                    <h4 style="font-size:15px;margin:4px 0 10px;color:#0f172a;">How Bridge Tailor Processes Your Diagnostic Data</h4>
                    <table class="bt-table" style="margin-bottom:14px;font-size:12px;">
                        <tr>
                            <td style="font-weight:700;width:34%;color:#0f172a;">Controlled Scoring</td>
                            <td style="color:#475569;">All 0–100 scores are computed deterministically by Bridge Tailor’s scoring engine, never by generative guesswork.</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;color:#0f172a;">Server-Side AI Key</td>
                            <td style="color:#475569;">OpenAI Responses API calls execute strictly on the Laravel server using Structured Outputs; keys never touch the browser.</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;color:#0f172a;">Fact vs. Assumption</td>
                            <td style="color:#475569;">Every finding is tagged as <code>CLIENT-PROVIDED</code>, <code>DOCUMENT-EVIDENCED</code>, or <code>CONSULTANT-ASSUMPTION</code>.</td>
                        </tr>
                        <tr>
                            <td style="font-weight:700;color:#0f172a;">Consultant Review</td>
                            <td style="color:#475569;">A Bridge Tailor consultant can review, amend, approve, and publish your final diagnostic report.</td>
                        </tr>
                    </table>

                    <label style="display:flex;align-items:flex-start;gap:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:8px;padding:12px 14px;cursor:pointer;font-size:12.5px;font-weight:600;color:#9a3412;line-height:1.45;">
                        <input type="checkbox" name="ai_consent" value="1" checked style="margin-top:3px;accent-color:var(--bt-amber);">
                        <span>
                            I consent to Bridge Tailor processing these responses and any uploaded documents through the Bridge Tailor Diagnostic Engine (including OpenAI Responses API Structured Outputs) under Bridge Tailor’s confidentiality &amp; retention policy.
                        </span>
                    </label>
                </div>
            </div>

            <!-- Submit Action Bar -->
            <div class="bt-step-actions between">
                <button type="button" class="btn btn-outline" onclick="gotoStep(3)">← Back to Questionnaire</button>
                <button type="submit" class="btn btn-amber btn-lg" id="btn-submit-diag" style="padding:12px 28px;font-size:14px;font-weight:700;">
                    Compute Scores &amp; Generate AI Diagnostic Report →
                </button>
            </div>
        </section>
    </div>
</form>

<!-- Toast Message Container -->
<div id="bt-toast" class="bt-toast">
    <span id="bt-toast-text">Notification message</span>
</div>
@endsection

@push('scripts')
<script>
    const FW = @json($frameworkPayload);
    let currentStep = 1;
    let activeModIndex = 0;
    const selectedSymptoms = {}; // id => pain 1..5
    const selectedAnswers = {};  // code => 0..2

    // Client-side persistent live auto-save
    const companyUlid = document.querySelector('input[name="existing_company_ulid"]')?.value || 'new';
    const DRAFT_KEY = 'bt_diagnostic_draft_' + companyUlid;
    let saveTimeout = null;

    const painDescriptions = {
        1: 'Minor Friction',
        2: 'Noticeable Inefficiency',
        3: 'Moderate Strain',
        4: 'Severe Bottleneck',
        5: 'Acute Enterprise Crisis'
    };

    function showToast(msg) {
        const toast = document.getElementById('bt-toast');
        const text = document.getElementById('bt-toast-text');
        if (toast && text) {
            text.innerHTML = msg;
            toast.classList.add('show');
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3500);
        }
    }

    function updateDraftIndicator(status = 'saved') {
        const chip = document.getElementById('bt-draft-status');
        const chipText = document.getElementById('bt-draft-status-text');
        const resetLink = document.getElementById('bt-reset-draft-link');
        const resetBtn1 = document.getElementById('bt-reset-btn-step1');

        if (chip) chip.style.display = 'inline-flex';
        if (resetLink) resetLink.style.display = 'inline-block';
        if (resetBtn1) resetBtn1.style.display = 'inline-flex';

        if (chipText) {
            if (status === 'saving') {
                chipText.textContent = 'Saving draft...';
            } else if (status === 'restored') {
                chipText.textContent = 'Restored live draft';
            } else {
                chipText.textContent = 'Draft saved live';
            }
        }
    }

    function saveDraft(debounceMs = 250) {
        if (saveTimeout) clearTimeout(saveTimeout);
        updateDraftIndicator('saving');

        saveTimeout = setTimeout(() => {
            try {
                const scopedBoxes = document.querySelectorAll('.scope-checkbox:checked');
                const scopedModules = Array.from(scopedBoxes).map(cb => cb.value);
                const modeRadio = document.querySelector('input[name="mode"]:checked');
                const customIssueEl = document.querySelector('textarea[name="custom_issue"]');
                const catEl = document.querySelector('select[name="category"]');
                const retEl = document.querySelector('select[name="retention_policy"]');
                const consentEl = document.querySelector('input[name="ai_consent"]');

                const draft = {
                    currentStep,
                    activeModIndex,
                    contact_name: document.getElementById('f_contact_name')?.value ?? '',
                    company_name: document.getElementById('f_company_name')?.value ?? '',
                    contact_email: document.getElementById('f_contact_email')?.value ?? '',
                    contact_phone: document.getElementById('f_contact_phone')?.value ?? '',
                    country: document.getElementById('f_country')?.value ?? '',
                    city: document.getElementById('f_city')?.value ?? '',
                    stage: document.getElementById('f_stage')?.value ?? 'growth',
                    sector: document.getElementById('f_sector')?.value ?? 'general',
                    size_band: document.getElementById('f_size_band')?.value ?? '21-50',
                    revenue_band: document.getElementById('f_revenue_band')?.value ?? '3to6',
                    objectives: document.getElementById('f_objectives')?.value ?? '',
                    challenges: document.getElementById('f_challenges')?.value ?? '',
                    mode: modeRadio ? modeRadio.value : 'comprehensive',
                    modules_in_scope: scopedModules,
                    selectedSymptoms: { ...selectedSymptoms },
                    selectedAnswers: { ...selectedAnswers },
                    custom_issue: customIssueEl ? customIssueEl.value : '',
                    category: catEl ? catEl.value : '',
                    retention_policy: retEl ? retEl.value : '',
                    ai_consent: consentEl ? consentEl.checked : true,
                    savedAt: new Date().toISOString()
                };

                const hasContent = (draft.contact_name && draft.contact_name.trim() !== '') ||
                                   (draft.company_name && draft.company_name.trim() !== '') ||
                                   (draft.contact_email && draft.contact_email.trim() !== '') ||
                                   (draft.objectives && draft.objectives.trim() !== '') ||
                                   (draft.challenges && draft.challenges.trim() !== '') ||
                                   (draft.custom_issue && draft.custom_issue.trim() !== '') ||
                                   Object.keys(selectedSymptoms).length > 0 ||
                                   Object.keys(selectedAnswers).length > 0;

                if (hasContent) {
                    localStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
                    updateDraftIndicator('saved');
                }
            } catch (e) {
                console.warn('Auto-save error:', e);
            }
        }, debounceMs);
    }

    function restoreDraft() {
        try {
            const raw = localStorage.getItem(DRAFT_KEY);
            if (!raw) return false;
            const draft = JSON.parse(raw);
            if (!draft || typeof draft !== 'object') return false;

            // 1. Contact & Profile fields
            if (draft.contact_name !== undefined && document.getElementById('f_contact_name')) document.getElementById('f_contact_name').value = draft.contact_name;
            if (draft.company_name !== undefined && document.getElementById('f_company_name')) document.getElementById('f_company_name').value = draft.company_name;
            if (draft.contact_email !== undefined && document.getElementById('f_contact_email')) document.getElementById('f_contact_email').value = draft.contact_email;
            if (draft.contact_phone !== undefined && document.getElementById('f_contact_phone')) document.getElementById('f_contact_phone').value = draft.contact_phone;
            if (draft.country !== undefined && document.getElementById('f_country')) document.getElementById('f_country').value = draft.country;
            if (draft.city !== undefined && document.getElementById('f_city')) document.getElementById('f_city').value = draft.city;
            if (draft.stage !== undefined && document.getElementById('f_stage')) document.getElementById('f_stage').value = draft.stage;
            if (draft.sector !== undefined && document.getElementById('f_sector')) document.getElementById('f_sector').value = draft.sector;
            if (draft.size_band !== undefined && document.getElementById('f_size_band')) document.getElementById('f_size_band').value = draft.size_band;
            if (draft.revenue_band !== undefined && document.getElementById('f_revenue_band')) document.getElementById('f_revenue_band').value = draft.revenue_band;
            if (draft.objectives !== undefined && document.getElementById('f_objectives')) document.getElementById('f_objectives').value = draft.objectives;
            if (draft.challenges !== undefined && document.getElementById('f_challenges')) document.getElementById('f_challenges').value = draft.challenges;

            const customIssueEl = document.querySelector('textarea[name="custom_issue"]');
            if (customIssueEl && draft.custom_issue !== undefined) customIssueEl.value = draft.custom_issue;

            const catEl = document.querySelector('select[name="category"]');
            if (catEl && draft.category !== undefined) catEl.value = draft.category;

            const retEl = document.querySelector('select[name="retention_policy"]');
            if (retEl && draft.retention_policy !== undefined) retEl.value = draft.retention_policy;

            const consentEl = document.querySelector('input[name="ai_consent"]');
            if (consentEl && draft.ai_consent !== undefined) consentEl.checked = !!draft.ai_consent;

            // 2. Mode & Scope
            if (draft.mode) {
                const modeRadio = document.querySelector(`input[name="mode"][value="${draft.mode}"]`);
                if (modeRadio) modeRadio.checked = true;
            }
            if (Array.isArray(draft.modules_in_scope) && draft.modules_in_scope.length > 0) {
                document.querySelectorAll('.scope-checkbox').forEach(cb => {
                    const isChecked = draft.modules_in_scope.includes(cb.value);
                    cb.checked = isChecked;
                    const card = document.getElementById('scope-card-' + cb.value);
                    if (card) card.classList.toggle('active', isChecked);
                });
            }

            // 3. Symptoms & Pain Ratings
            if (draft.selectedSymptoms && typeof draft.selectedSymptoms === 'object') {
                Object.entries(draft.selectedSymptoms).forEach(([id, pain]) => {
                    selectedSymptoms[id] = pain;
                    const box = document.getElementById('sym-box-' + id);
                    const painBar = document.getElementById('sym-pain-' + id);
                    const input = document.getElementById('sym-input-' + id);

                    if (box) box.classList.add('selected');
                    if (painBar) painBar.style.display = 'flex';
                    if (input) {
                        input.disabled = false;
                        input.value = pain;
                    }
                    renderPainPips(id, pain);
                });
            }

            // 4. Evidence answers
            if (draft.selectedAnswers && typeof draft.selectedAnswers === 'object') {
                Object.entries(draft.selectedAnswers).forEach(([code, optIdx]) => {
                    selectedAnswers[code] = optIdx;
                    const input = document.getElementById('ans-input-' + code);
                    if (input) {
                        input.disabled = false;
                        input.value = optIdx;
                    }
                    for (let i = 0; i < 3; i++) {
                        const btn = document.querySelector(`[data-opt-btn="${code}-${i}"]`);
                        if (btn) {
                            btn.classList.remove('selected-0', 'selected-1', 'selected-2');
                            if (i === optIdx) {
                                btn.classList.add('selected-' + optIdx);
                            }
                        }
                    }
                });
            }

            // 5. Active module index
            if (typeof draft.activeModIndex === 'number') {
                activeModIndex = draft.activeModIndex;
            }

            onScopeChange();
            updateVisibilityAndCounts();

            // 6. Navigate to active step
            if (draft.currentStep && draft.currentStep > 1) {
                gotoStep(draft.currentStep, true);
            }

            updateDraftIndicator('restored');
            showToast('💾 Restored your in-progress diagnostic from auto-save.');
            return true;
        } catch (e) {
            console.warn('Error restoring draft:', e);
            return false;
        }
    }

    function clearDraft(confirmFirst = true) {
        if (confirmFirst && !confirm('Are you sure you want to clear your saved draft and reset the form? All entered answers and company information will be cleared.')) {
            return;
        }
        try {
            localStorage.removeItem(DRAFT_KEY);
        } catch (e) {}
        window.location.reload();
    }

    function getScopedModules() {
        const boxes = document.querySelectorAll('.scope-checkbox:checked');
        const list = Array.from(boxes).map(cb => cb.value);
        return list.length ? list : ['SG'];
    }

    function setAssessmentMode(mode) {
        if (mode === 'comprehensive') {
            document.querySelectorAll('.scope-checkbox').forEach(cb => {
                cb.checked = true;
                const card = document.getElementById('scope-card-' + cb.value);
                if (card) card.classList.add('active');
            });
        }
        onScopeChange();
        saveDraft(50);
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
        saveDraft(100);
    }

    function onProfileChange() {
        updateVisibilityAndCounts();
        saveDraft(100);
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
        saveDraft(50);
    }

    function setSymptomPain(id, pain) {
        selectedSymptoms[id] = pain;
        const input = document.getElementById('sym-input-' + id);
        input.disabled = false;
        input.value = pain;
        renderPainPips(id, pain);
        updateVisibilityAndCounts();
        saveDraft(50);
    }

    function renderPainPips(id, pain) {
        for (let n = 1; n <= 5; n++) {
            const btn = document.querySelector(`[data-pip="${id}-${n}"]`);
            if (btn) {
                btn.classList.toggle('on', n <= pain);
            }
        }
        const descEl = document.getElementById('sym-pain-desc-' + id);
        if (descEl && painDescriptions[pain]) {
            descEl.textContent = painDescriptions[pain];
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
        saveDraft(50);
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

        const symCount = Object.keys(selectedSymptoms).length;
        const symEl = document.getElementById('rail-symptom-count');
        if (symEl) symEl.textContent = symCount;

        const ansEl = document.getElementById('rail-answer-count');
        if (ansEl) ansEl.textContent = `${totalAnswered} / ${totalApplicable}`;

        // Compute smooth overall percentage
        let progressPct = 25;
        if (currentStep === 1) {
            progressPct = 25;
        } else if (currentStep === 2) {
            progressPct = 25 + Math.min(25, symCount * 4);
        } else if (currentStep === 3) {
            const qRatio = totalApplicable > 0 ? (totalAnswered / totalApplicable) : 0;
            progressPct = Math.min(95, Math.round(50 + (qRatio * 45)));
        } else if (currentStep === 4) {
            progressPct = 100;
        }
        const pBar = document.getElementById('bt-wiz-progress-bar');
        if (pBar) pBar.style.width = progressPct + '%';
        const pPct = document.getElementById('bt-wiz-progress-pct');
        if (pPct) pPct.textContent = progressPct + '%';

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
        if (!nav) return;
        nav.innerHTML = '';
        scoped.forEach((m, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mod-subnav-btn' + (idx === activeModIndex ? ' current' : '');
            btn.innerHTML = `<strong>${idx + 1}.</strong> ${m} · ${FW.modules[m].short || FW.modules[m].name}`;
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
                : 'Continue to Step 04: AI Consent & Report →';
        }
    }

    function showActiveModulePane() {
        renderModuleSubnav();
        window.scrollTo({ top: 120, behavior: 'smooth' });
        saveDraft(100);
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

    function gotoStep(step, skipValidation = false) {
        if (step > 1 && !skipValidation) {
            const name = document.getElementById('f_contact_name')?.value?.trim();
            const comp = document.getElementById('f_company_name')?.value?.trim();
            const email = document.getElementById('f_contact_email')?.value?.trim();
            if (!name || !comp || !email) {
                alert('Please complete your Name, Company Name, and Business Email in Step 01 before proceeding.');
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
        if (!skipValidation) {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
        saveDraft(50);
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
        saveDraft(0);
        showToast('⚡ Sample African SME profile loaded! You can now freely navigate all 4 steps.');
    }

    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('diagnostic-form');
        const submitBtn = document.getElementById('btn-submit-diag');

        // Restore any saved draft from client storage
        const restored = restoreDraft();
        if (!restored) {
            updateVisibilityAndCounts();
        }

        // Live auto-save on any form input or selection change
        if (form) {
            form.addEventListener('input', () => saveDraft(300));
            form.addEventListener('change', () => saveDraft(50));
            form.addEventListener('submit', () => {
                try {
                    localStorage.removeItem(DRAFT_KEY);
                } catch (e) {}
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = 'Computing Scores &amp; Generating Report... Please Wait';
                }
            });
        }
    });
</script>
@endpush
