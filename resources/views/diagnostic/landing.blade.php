@extends('layouts.app')

@section('title', 'AI Business Diagnostic Platform')

@push('head')
<style>
    /* Hero Slider Container */
    .bt-hero-slider-section {
        position: relative;
        overflow: hidden;
        min-height: calc(100vh - 90px);
        min-height: 560px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background-color: #f8fafc;
        border-bottom: 2px solid rgba(226, 232, 240, 0.9);
    }

    /* Slider Viewport and Track */
    .bt-slider-viewport {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 1;
    }
    .bt-slider-track {
        display: flex;
        width: 100%;
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        transition: transform 1.1s cubic-bezier(0.25, 1, 0.5, 1);
        will-change: transform;
    }
    .bt-slide {
        flex: 0 0 100%;
        width: 100%;
        height: 100%;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
    }
    .bt-slide-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 30%;
        filter: brightness(1.02) contrast(1.04);
        transform: scale(1.02);
        transition: transform 7s ease-out;
        z-index: 1;
    }
    .bt-slide.is-current .bt-slide-img {
        transform: scale(1.07);
    }

    /* Subtle dark contrast wash so solid white text is 100% crisp and readable (matching akantant.com) */
    .bt-slide-tint {
        position: absolute;
        inset: 0;
        z-index: 2;
        pointer-events: none;
        background: linear-gradient(to right, 
            rgba(12, 14, 18, 0.84) 0%, 
            rgba(12, 14, 18, 0.62) 42%, 
            rgba(12, 14, 18, 0.22) 75%, 
            rgba(12, 14, 18, 0.05) 100%
        );
    }

    /* Slide Inner Content Container */
    .bt-slide-inner {
        position: relative;
        z-index: 5;
        width: 100%;
        padding: 48px 0;
    }

    /* Solid Text Directly On Image - No Card Box (Matching akantant.com) */
    .bt-hero-transparent-card {
        background: transparent !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        max-width: 660px;
        position: relative;
    }

    /* Animation Keyframes for Incoming Description & Title */
    @keyframes heroDescSlideIn {
        0% {
            opacity: 0;
            transform: translateY(30px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes heroTitleSlideIn {
        0% {
            opacity: 0;
            transform: translateY(22px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes heroActionsSlideIn {
        0% {
            opacity: 0;
            transform: translateY(18px);
        }
        100% {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .bt-hero-title {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 42px;
        line-height: 1.15;
        color: #ffffff;
        letter-spacing: -0.015em;
        margin: 0 0 18px;
        font-weight: 400;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.60);
        opacity: 0;
        transform: translateY(22px);
    }
    .bt-hero-subtitle {
        font-size: 15.5px;
        color: #f1f5f9;
        line-height: 1.72;
        margin: 0;
        font-weight: 400;
        text-shadow: 0 1px 8px rgba(0, 0, 0, 0.65);
        opacity: 0;
        transform: translateY(30px);
    }
    .bt-hero-actions {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        align-items: center;
        padding-top: 22px;
        border-top: 1px solid rgba(255, 255, 255, 0.22);
        margin-top: 26px;
        opacity: 0;
        transform: translateY(18px);
    }

    /* Trigger smooth staggered entrance animations when slide becomes active */
    .bt-slide.is-current .bt-hero-title {
        animation: heroTitleSlideIn 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.18s forwards;
    }
    .bt-slide.is-current .bt-hero-subtitle {
        animation: heroDescSlideIn 0.95s cubic-bezier(0.16, 1, 0.3, 1) 0.35s forwards;
    }
    .bt-slide.is-current .bt-hero-actions {
        animation: heroActionsSlideIn 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.52s forwards;
    }

    .bt-hero-cta {
        background-color: var(--bt-amber);
        color: #141210;
        font-weight: 800;
        font-size: 13px;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 13px 26px;
        border: 1px solid var(--bt-amber);
        border-radius: 6px !important;
        box-shadow: 0 4px 18px rgba(241, 126, 1, 0.40) !important;
        transition: all 0.25s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    .bt-hero-cta:hover {
        background-color: #ffa033;
        border-color: #ffa033;
        color: #141210;
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(241, 126, 1, 0.55) !important;
    }
    .bt-hero-btn-secondary {
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        border: 1px solid rgba(255, 255, 255, 0.65);
        border-radius: 6px !important;
        padding: 13px 22px;
        font-size: 12.5px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: all 0.2s ease;
        text-decoration: none;
        backdrop-filter: blur(4px);
    }
    .bt-hero-btn-secondary:hover {
        background: #ffffff;
        border-color: #ffffff;
        color: #0f172a;
        transform: translateY(-1px);
    }

    /* Architecture Box in Cool White Mode */
    .bt-hero-arch-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.08) !important;
        padding: 20px 22px;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .bt-hero-arch-table {
        border-color: #e2e8f0;
        font-size: 11.5px;
    }
    .bt-hero-arch-table td {
        border-color: #e2e8f0;
        padding: 8px 10px;
    }
    .bt-hero-arch-table td.bt-arch-th {
        background: rgba(248, 250, 252, 0.85);
        color: #b85a0a;
        font-weight: 700;
        width: 36%;
        border-color: #e2e8f0;
    }

    /* Slider Interactive Controls & Progress Indicators - Vertical Dots Only */
    .bt-slider-controls {
        position: absolute;
        right: 28px;
        top: 50%;
        transform: translateY(-50%);
        bottom: auto;
        z-index: 20;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        background: rgba(15, 23, 42, 0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        padding: 10px 7px;
        border: 1px solid rgba(255, 255, 255, 0.25);
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.25) !important;
    }
    .bt-slider-arrow {
        display: none !important;
    }
    .bt-slider-dots {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
    }
    .bt-slider-dot {
        width: 8px;
        height: 8px;
        border-radius: 50% !important;
        background: rgba(255, 255, 255, 0.45);
        border: 1px solid rgba(255, 255, 255, 0.7);
        padding: 0;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .bt-slider-dot:hover {
        background: rgba(241, 126, 1, 0.85);
    }
    .bt-slider-dot.active {
        width: 8px;
        height: 24px;
        border-radius: 4px !important;
        background: var(--bt-amber);
        border-color: var(--bt-amber);
        box-shadow: 0 2px 10px rgba(241, 126, 1, 0.6) !important;
    }

    @media (max-width: 900px) {
        .bt-hero-slider-section {
            min-height: calc(100vh - 65px);
            min-height: calc(100dvh - 65px);
            min-height: 520px;
        }
        .bt-slide {
            align-items: flex-end;
        }
        .bt-slide-img {
            object-position: center 15%;
        }
        .bt-slide-tint {
            background: linear-gradient(to top,
                rgba(10, 12, 16, 0.92) 0%,
                rgba(10, 12, 16, 0.72) 55%,
                rgba(10, 12, 16, 0.18) 100%
            );
        }
        .bt-slide-inner {
            padding: 20px 16px 44px;
        }
        .bt-hero-transparent-card {
            max-width: 100%;
        }
        .bt-hero-title {
            font-size: 26px;
            line-height: 1.2;
            margin-bottom: 8px;
            color: #ffffff;
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.8);
        }
        .bt-hero-subtitle {
            font-size: 13.5px;
            line-height: 1.55;
            color: #f1f5f9;
            text-shadow: 0 1px 6px rgba(0, 0, 0, 0.8);
        }
        .bt-hero-actions {
            flex-direction: column;
            align-items: stretch;
            gap: 8px;
            margin-top: 14px;
            padding-top: 12px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }
        .bt-hero-cta {
            width: 100%;
            justify-content: center;
            text-align: center;
            padding: 11px 16px;
            font-size: 12px;
            font-weight: 800;
        }
        .bt-hero-btn-secondary {
            width: 100%;
            justify-content: center;
            text-align: center;
            padding: 10px 14px;
            font-size: 11.5px;
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.5);
            color: #ffffff;
        }
        .bt-slider-controls {
            right: 12px;
            left: auto;
            top: 50%;
            bottom: auto;
            transform: translateY(-50%);
            max-width: none;
            padding: 8px 6px;
            gap: 7px;
            background: rgba(15, 23, 42, 0.55);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(255, 255, 255, 0.25);
        }
        .bt-slider-dot {
            width: 8px;
            height: 8px;
        }
        .bt-slider-dot.active {
            width: 8px;
            height: 22px;
        }
    }

    @media (max-width: 640px) {
        .bt-hero-slider-section {
            min-height: calc(100vh - 60px);
            min-height: calc(100dvh - 60px);
            min-height: 500px;
        }
        .bt-slide {
            align-items: flex-end;
        }
        .bt-slide-inner {
            padding: 16px 14px 36px;
        }
        .bt-hero-title {
            font-size: 21px;
            line-height: 1.22;
            margin-bottom: 6px;
        }
        .bt-hero-subtitle {
            font-size: 12px;
            line-height: 1.48;
        }
        .bt-hero-actions {
            margin-top: 10px;
            padding-top: 10px;
            gap: 6px;
        }
        .bt-hero-cta {
            font-size: 11.5px;
            padding: 10px 14px;
            letter-spacing: 0.04em;
        }
        .bt-slider-controls {
            right: 8px;
            left: auto;
            top: 50%;
            bottom: auto;
            transform: translateY(-50%);
            padding: 6px 4px;
            gap: 6px;
            background: rgba(15, 23, 42, 0.55);
        }
        .bt-slider-dot {
            width: 6px;
            height: 6px;
        }
        .bt-slider-dot.active {
            width: 6px;
            height: 18px;
        }
    }

    @media (max-width: 380px) {
        .bt-hero-title {
            font-size: 18px;
        }
        .bt-hero-subtitle {
            font-size: 11px;
            line-height: 1.4;
        }
    }
</style>
@endpush

@section('content')
@php
    $resolveSlideUrl = function($settingKey, $defaultPath) use ($uiText) {
        $val = trim((string) $uiText($settingKey, $defaultPath));
        if (empty($val)) {
            $val = $defaultPath;
        }
        if (str_starts_with($val, 'http://') || str_starts_with($val, 'https://') || str_starts_with($val, '//')) {
            return $val;
        }
        return asset(ltrim($val, '/'));
    };

    $slidesData = [
        [
            'image' => $resolveSlideUrl('text.hero_slide_1_image', 'images/slider/slide-1.jpg'),
            'title' => $uiText('text.hero_title'),
            'desc'  => $uiText('text.hero_subtitle'),
            'cta'   => $uiText('text.hero_cta_primary'),
        ],
        [
            'image' => $resolveSlideUrl('text.hero_slide_2_image', 'images/slider/slide-2.jpg'),
            'title' => $uiText('text.hero_slide_2_title', 'Corporate Strategy & Board Governance'),
            'desc'  => $uiText('text.hero_slide_2_desc', 'Align board oversight, capital readiness, and management execution to unlock institutional investment and enterprise growth across African markets.'),
            'cta'   => 'Assess Governance & Strategy →',
        ],
        [
            'image' => $resolveSlideUrl('text.hero_slide_3_image', 'images/slider/slide-3.jpg'),
            'title' => $uiText('text.hero_slide_3_title', 'Financial Health & Capital Access'),
            'desc'  => $uiText('text.hero_slide_3_desc', 'Deterministic financial gateway analysis that ensures bookkeeping integrity, financial controls, and compliance before approaching investors.'),
            'cta'   => 'Evaluate Financial Health →',
        ],
        [
            'image' => $resolveSlideUrl('text.hero_slide_4_image', 'images/slider/slide-4.jpg'),
            'title' => $uiText('text.hero_slide_4_title', 'Operational Excellence & Market Expansion'),
            'desc'  => $uiText('text.hero_slide_4_desc', 'Expose operational blind spots, streamline local supply chains, and build scalable systems tailored for rapid regional growth.'),
            'cta'   => 'Test Operational Resilience →',
        ],
        [
            'image' => $resolveSlideUrl('text.hero_slide_5_image', 'images/slider/slide-5.jpg'),
            'title' => $uiText('text.hero_slide_5_title', 'Actionable Diagnostic Intervention Roadmap'),
            'desc'  => $uiText('text.hero_slide_5_desc', 'Convert diagnostic evidence directly into prioritized 30, 90, and 180-day consulting roadmaps approved by expert advisory partners.'),
            'cta'   => 'Start Diagnostic Assessment →',
        ],
    ];
@endphp
<!-- Section 1: Cool Premium Business White Hero with Embedded Transparent Slide Cards -->
<section class="bt-hero-slider-section" id="btHeroSection">
    <!-- Sliding Background Track with Embedded Transparent Cards -->
    <div class="bt-slider-viewport">
        <div class="bt-slider-track" id="btHeroSliderTrack">
            @foreach($slidesData as $idx => $slide)
                <div class="bt-slide {{ $idx === 0 ? 'is-current' : '' }}" data-slide-index="{{ $idx }}">
                    <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" class="bt-slide-img">
                    <div class="bt-slide-tint" aria-hidden="true"></div>
                    <div class="bt-slide-inner">
                        <div class="bt-container {{ $uiVisible('vis.landing_architecture_box') ? 'bt-hero-grid' : '' }}">
                            <div class="bt-hero-transparent-card">
                                <h1 class="bt-hero-title">{{ $slide['title'] }}</h1>
                                <p class="bt-hero-subtitle">{{ $slide['desc'] }}</p>
                                <div class="bt-hero-actions">
                                    <a href="{{ route('diagnostic.wizard') }}" class="btn btn-amber bt-hero-cta">
                                        <span>{{ $slide['cta'] }}</span>
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:4px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                    </a>
                                    @auth
                                        @if(auth()->user()->isDeveloper())
                                            <a href="{{ route('developer.console') }}" class="btn bt-hero-btn-secondary" style="color:var(--bt-amber-deep);border-color:var(--bt-amber);">
                                                Open Developer Master Console
                                            </a>
                                        @elseif(auth()->user()->isConsultant())
                                            <a href="{{ route('consultant.dashboard') }}" class="btn bt-hero-btn-secondary">
                                                Open Consultant Review Desk
                                            </a>
                                        @else
                                            <a href="{{ route('portal.index') }}" class="btn bt-hero-btn-secondary">
                                                Open My Client Workspace
                                            </a>
                                        @endif
                                    @endauth
                                </div>
                            </div>

                            @if($uiVisible('vis.landing_architecture_box') && $idx === 0)
                            <div class="bt-hero-arch-card">
                                <div>
                                    <div class="eyebrow" style="color:var(--bt-amber-deep);">CONTROLLED DIAGNOSTIC ARCHITECTURE</div>
                                    <h3 style="font-size:16px;color:#0f172a;margin-bottom:8px;font-weight:700;">Deterministic Scores + AI Structured Synthesis</h3>
                                    <p style="font-size:12.5px;color:#475569;margin:0 0 14px;line-height:1.5;">
                                        Numerical scores are calculated strictly by Bridge Tailor’s controlled scoring engine (50% weighted symptom pain + 50% gateway evidence verification). The AI interprets findings using OpenAI Structured Outputs and tags every statement by epistemic source.
                                    </p>
                                </div>
                                <table class="bt-table bt-hero-arch-table">
                                    <tr>
                                        <td class="bt-arch-th">Scoring Engine</td>
                                        <td>Controlled 0–100 index + Feel vs. Evidence blind-spot gap (≥15 pts)</td>
                                    </tr>
                                    <tr>
                                        <td class="bt-arch-th">Sequencing Gate</td>
                                        <td>Financial Control gates Capital Raising (shaky books block funding)</td>
                                    </tr>
                                    <tr>
                                        <td class="bt-arch-th">Epistemic Guardrail</td>
                                        <td>Distinguishes Verified Facts, Client-Provided data &amp; Assumptions</td>
                                    </tr>
                                    <tr>
                                        <td class="bt-arch-th">Consultant Review</td>
                                        <td>AI drafts initial report → Consultant amends, approves &amp; publishes</td>
                                    </tr>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Sliding Controls & Indicators -->
    <div class="bt-slider-controls">
        <button type="button" class="bt-slider-arrow bt-slider-prev" id="btSliderPrev" aria-label="Previous Slide">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        </button>
        <div class="bt-slider-dots" role="tablist" aria-label="Hero Slides">
            @foreach($slidesData as $idx => $slide)
                <button type="button" class="bt-slider-dot {{ $idx === 0 ? 'active' : '' }}" data-slide="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}" {{ $idx === 0 ? 'aria-current="true"' : '' }}></button>
            @endforeach
        </div>
        <button type="button" class="bt-slider-arrow bt-slider-next" id="btSliderNext" aria-label="Next Slide">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
        </button>
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

        <div class="bt-table-wrap">
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
                        <td data-label="Module Code" style="font-weight:800;font-size:13px;color:var(--bt-amber-deep);">{{ $mKey }}</td>
                        <td data-label="Assessment Module">
                            <div style="font-weight:700;color:var(--bt-ink);">{{ $module['name'] }}</div>
                            <div style="font-size:11.5px;color:#555;margin-top:2px;">{{ $module['description'] }}</div>
                        </td>
                        <td data-label="5 Core Assessment Criteria">
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($module['criteria'] as $cKey => $crit)
                                    <span style="display:inline-block;border:1px solid #d6cdc2;background:#faf6f0;padding:2px 6px;font-size:11px;font-weight:600;">
                                        <strong>{{ $cKey }}:</strong> {{ $crit['name'] }}
                                    </span>
                                @endforeach
                            </div>
                        </td>
                        <td data-label="Bridge Tailor Programme Routing">
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
                        <td data-label="Assessment Action">
                            <a href="{{ route('diagnostic.wizard', ['target' => $mKey]) }}" class="btn btn-outline btn-sm" style="width:100%;">
                                Assess {{ $module['short'] }}
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const track = document.getElementById('btHeroSliderTrack');
        if (!track) return;

        const slides = track.querySelectorAll('.bt-slide');
        const dots = document.querySelectorAll('.bt-slider-dot');
        const prevBtn = document.getElementById('btSliderPrev');
        const nextBtn = document.getElementById('btSliderNext');
        const heroSection = document.getElementById('btHeroSection');

        if (slides.length <= 1) return;

        let currentIndex = 0;
        const totalSlides = slides.length;
        let autoSlideTimer = null;
        const slideDelay = 5500; // 5.5 seconds per slide

        function updateSlide(newIndex) {
            if (newIndex < 0) {
                newIndex = totalSlides - 1;
            } else if (newIndex >= totalSlides) {
                newIndex = 0;
            }

            currentIndex = newIndex;

            // Slide horizontally with transform
            track.style.transform = 'translateX(-' + (currentIndex * 100) + '%)';

            // Update zoom class for Ken Burns effect
            slides.forEach(function (slide, idx) {
                if (idx === currentIndex) {
                    slide.classList.add('is-current');
                } else {
                    slide.classList.remove('is-current');
                }
            });

            // Update dots
            dots.forEach(function (dot, idx) {
                if (idx === currentIndex) {
                    dot.classList.add('active');
                    dot.setAttribute('aria-current', 'true');
                } else {
                    dot.classList.remove('active');
                    dot.removeAttribute('aria-current');
                }
            });
        }

        function nextSlide() {
            updateSlide(currentIndex + 1);
        }

        function prevSlide() {
            updateSlide(currentIndex - 1);
        }

        function startAutoSlide() {
            stopAutoSlide();
            autoSlideTimer = setInterval(nextSlide, slideDelay);
        }

        function stopAutoSlide() {
            if (autoSlideTimer) {
                clearInterval(autoSlideTimer);
                autoSlideTimer = null;
            }
        }

        // Dot click handlers
        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                const targetIdx = parseInt(this.getAttribute('data-slide'), 10);
                updateSlide(targetIdx);
                startAutoSlide();
            });
        });

        // Arrow controls
        if (prevBtn) {
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                prevSlide();
                startAutoSlide();
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                nextSlide();
                startAutoSlide();
            });
        }

        // Pause on mouse hover, resume on mouse leave
        if (heroSection) {
            heroSection.addEventListener('mouseenter', stopAutoSlide);
            heroSection.addEventListener('mouseleave', startAutoSlide);
        }

        // Touch swipe support for mobile
        let touchStartX = 0;
        let touchEndX = 0;

        heroSection.addEventListener('touchstart', function (e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        heroSection.addEventListener('touchend', function (e) {
            touchEndX = e.changedTouches[0].screenX;
            handleGesture();
        }, { passive: true });

        function handleGesture() {
            const swipeThreshold = 45;
            if (touchEndX < touchStartX - swipeThreshold) {
                nextSlide();
                startAutoSlide();
            } else if (touchEndX > touchStartX + swipeThreshold) {
                prevSlide();
                startAutoSlide();
            }
        }

        // Initialize
        updateSlide(0);
        startAutoSlide();
    });
</script>
@endpush
@endsection
