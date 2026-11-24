@extends('layouts.app')

@section('title', 'High-End Platform Security Clearance')

@push('head')
<style>
    /* Luminous Modern Luxury Security Gateway (Crisp White + Modern Green + Amber Prestige Accent) */
    .bt-security-wrap {
        background: radial-gradient(circle at 50% 0%, #ecfdf5 0%, #f8fafc 45%, #f1f5f9 100%);
        min-height: calc(100vh - var(--bt-header-height, 86px));
        min-height: calc(100dvh - var(--bt-header-height, 86px));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 44px 18px 60px;
        position: relative;
        overflow: hidden;
        color: #0f172a;
        box-sizing: border-box;
    }

    /* Ambient modern microgrid lines */
    .bt-security-wrap::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: 
            linear-gradient(rgba(16, 185, 129, 0.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(16, 185, 129, 0.05) 1px, transparent 1px);
        background-size: 36px 36px;
        pointer-events: none;
        opacity: 0.9;
    }

    /* Colorful ambient glowing orbs */
    .bt-security-glow-1 {
        position: absolute;
        top: 6%;
        left: 15%;
        width: 440px;
        height: 440px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.16) 0%, rgba(52, 211, 153, 0.04) 55%, transparent 70%);
        filter: blur(55px);
        pointer-events: none;
        animation: pulseGlow 8s ease-in-out infinite alternate;
    }
    .bt-security-glow-2 {
        position: absolute;
        bottom: 8%;
        right: 14%;
        width: 460px;
        height: 460px;
        background: radial-gradient(circle, rgba(241, 126, 1, 0.12) 0%, rgba(245, 158, 11, 0.03) 55%, transparent 70%);
        filter: blur(65px);
        pointer-events: none;
        animation: pulseGlow 10s ease-in-out infinite alternate-reverse;
    }

    @keyframes pulseGlow {
        0% { transform: scale(0.92); opacity: 0.7; }
        100% { transform: scale(1.12); opacity: 1; }
    }

    .bt-security-container {
        width: 100%;
        max-width: 680px;
        margin: 0 auto;
        position: relative;
        z-index: 10;
    }

    /* Modern Crisp White Shield Card */
    .bt-security-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-top: 5px solid #10b981; /* Modern Emerald accent */
        border-radius: 18px !important;
        box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.12), 0 10px 30px -4px rgba(15, 23, 42, 0.06) !important;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .bt-security-header {
        padding: 36px 36px 26px;
        text-align: center;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-bottom: 1px solid #f1f5f9;
        position: relative;
    }

    /* Modern Green Pill Badge */
    .bt-security-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        padding: 5px 14px;
        border-radius: 20px !important;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #047857;
        margin-bottom: 16px;
        box-shadow: 0 2px 6px rgba(16, 185, 129, 0.1);
    }
    .bt-security-badge-dot {
        width: 8px;
        height: 8px;
        border-radius: 50% !important;
        background-color: #10b981;
        box-shadow: 0 0 10px #10b981;
        animation: pulseDot 1.8s infinite;
    }
    @keyframes pulseDot {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.45; transform: scale(0.85); }
    }

    .bt-security-title {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 30px;
        line-height: 1.25;
        color: #0f172a;
        margin: 0 0 8px;
        letter-spacing: -0.015em;
    }
    .bt-security-sub {
        font-size: 13.5px;
        color: #64748b;
        line-height: 1.6;
        max-width: 530px;
        margin: 0 auto;
    }
    .bt-security-sub strong {
        color: #0f172a;
    }

    /* Segmented Switcher Tabs */
    .bt-sec-switcher {
        display: flex;
        background: #f1f5f9;
        border-bottom: 1px solid #e2e8f0;
        padding: 6px;
        gap: 6px;
    }
    .bt-sec-tab-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 14px;
        border: 1px solid transparent !important;
        background: transparent !important;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 700;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        color: #64748b;
        cursor: pointer;
        border-radius: 10px !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: center;
    }
    .bt-sec-tab-btn:hover {
        color: #0f172a;
        background: rgba(255, 255, 255, 0.6) !important;
    }
    .bt-sec-tab-btn.active {
        background: #ffffff !important;
        border-color: #d1fae5 !important;
        color: #047857 !important;
        font-weight: 800;
        box-shadow: 0 4px 14px rgba(16, 185, 129, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04) !important;
    }
    .bt-sec-tab-btn.active svg {
        stroke: #10b981;
    }

    /* Tab Panes */
    .bt-sec-pane {
        display: none;
        padding: 34px 38px 38px;
        animation: secFadeIn 0.25s ease-out;
        background: #ffffff;
    }
    .bt-sec-pane.active {
        display: block;
    }
    @keyframes secFadeIn {
        from { opacity: 0; transform: translateY(6px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Clean Bright Form Controls */
    .bt-sec-form .field {
        margin-bottom: 18px;
    }
    .bt-sec-form label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #334155;
        margin-bottom: 7px;
    }
    .bt-sec-form input[type="text"],
    .bt-sec-form input[type="email"],
    .bt-sec-form input[type="password"],
    .bt-sec-form select,
    .bt-sec-form textarea {
        width: 100%;
        padding: 12px 16px;
        font-size: 15px !important;
        font-family: inherit;
        color: #0f172a;
        background-color: #f8fafc;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px !important;
        box-sizing: border-box !important;
        outline: none;
        transition: all 0.2s ease;
    }
    .bt-sec-form input::placeholder,
    .bt-sec-form textarea::placeholder {
        color: #94a3b8;
        font-size: 14.5px;
    }
    .bt-sec-form input:focus,
    .bt-sec-form select:focus,
    .bt-sec-form textarea:focus {
        border-color: #10b981 !important;
        background-color: #ffffff;
        box-shadow: 0 0 0 3.5px rgba(16, 185, 129, 0.18) !important;
    }
    .bt-sec-form select option {
        background-color: #ffffff;
        color: #0f172a;
    }

    /* Segmented Monospace Code Input Box (Clean White & Modern Emerald) */
    .bt-code-input-wrap {
        position: relative;
        margin: 10px 0 20px;
    }
    .bt-code-input {
        width: 100%;
        padding: 16px 20px !important;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace !important;
        font-size: 25px !important;
        font-weight: 900 !important;
        letter-spacing: 0.18em !important;
        text-transform: uppercase !important;
        text-align: center;
        color: #047857 !important; /* Modern Forest/Emerald */
        background: #f0fdf4 !important; /* Very soft mint tint */
        border: 2px solid #10b981 !important;
        border-radius: 12px !important;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.08) !important;
    }
    .bt-code-input:focus {
        background: #ffffff !important;
        border-color: #059669 !important;
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.22), 0 4px 12px rgba(16, 185, 129, 0.12) !important;
    }
    .bt-code-format-hint {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 11.5px;
        color: #64748b;
        margin-top: 7px;
    }
    .bt-code-format-hint code {
        background: #ecfdf5;
        color: #047857;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
    }

    /* Vibrant Modern Green Primary Button */
    .btn-sec-primary {
        width: 100%;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #ffffff;
        border: 1px solid #059669;
        border-radius: 8px !important;
        padding: 14px 20px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 6px 20px rgba(16, 185, 129, 0.32) !important;
        transition: all 0.25s ease;
        margin-top: 10px;
    }
    .btn-sec-primary:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border-color: #047857;
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(16, 185, 129, 0.42) !important;
    }

    /* Crisp Secondary Request Button */
    .btn-sec-request {
        width: 100%;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        color: #ffffff;
        border: 1px solid #0f172a;
        border-radius: 8px !important;
        padding: 14px 20px;
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 16px rgba(15, 23, 42, 0.18) !important;
        transition: all 0.25s ease;
        margin-top: 10px;
    }
    .btn-sec-request:hover {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: #a7f3d0;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(15, 23, 42, 0.24) !important;
    }

    /* Modern Green Protocol Dispatch Notice Box */
    .bt-sec-dispatch-box {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        border-radius: 10px !important;
        padding: 14px 16px;
        margin: 4px 0 22px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .bt-sec-dispatch-icon {
        color: #059669;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .bt-sec-dispatch-text {
        font-size: 12.5px;
        line-height: 1.55;
        color: #065f46;
    }
    .bt-sec-dispatch-text strong {
        color: #047857;
        font-weight: 700;
    }

    /* Success confirmation state */
    .bt-sec-success-card {
        background: #f0fdf4;
        border: 1.5px solid #86efac;
        border-radius: 12px !important;
        padding: 24px 20px;
        text-align: center;
        margin-bottom: 24px;
        animation: secFadeIn 0.3s ease-out;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.08);
    }
    .bt-sec-success-title {
        font-size: 19px;
        font-weight: 800;
        color: #15803d;
        margin: 10px 0 6px;
    }
    .bt-sec-success-desc {
        font-size: 13.5px;
        color: #166534;
        line-height: 1.6;
        margin: 0;
    }

    /* Developer Master Terminal Drawer & Modal */
    .bt-dev-terminal-bar {
        margin-top: 24px;
        text-align: center;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
        background: #ffffff;
    }
    .bt-dev-terminal-btn {
        background: #f8fafc;
        border: 1.5px dashed #cbd5e1;
        color: #475569;
        font-family: inherit;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.07em;
        text-transform: uppercase;
        padding: 9px 18px;
        border-radius: 6px !important;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 7px;
        transition: all 0.2s ease;
    }
    .bt-dev-terminal-btn:hover {
        color: #047857;
        border-color: #10b981;
        background: #ecfdf5;
        transform: translateY(-1px);
    }

    /* Modal Backdrop for Developer Login (Clean & Modern) */
    .bt-dev-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .bt-dev-modal.open {
        display: flex;
    }
    .bt-dev-modal-content {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-top: 4px solid #10b981;
        border-radius: 14px !important;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 0 10px 25px rgba(16, 185, 129, 0.1) !important;
        width: 100%;
        max-width: 450px;
        overflow: hidden;
        animation: secFadeIn 0.2s ease-out;
    }
    .bt-dev-modal-header {
        padding: 18px 24px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .bt-dev-modal-title {
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 8px;
        margin: 0;
    }
    .bt-dev-modal-title svg {
        color: #10b981;
    }
    .bt-dev-modal-close {
        background: transparent;
        border: none;
        color: #94a3b8;
        font-size: 22px;
        cursor: pointer;
        padding: 0 4px;
        line-height: 1;
        transition: color 0.15s;
    }
    .bt-dev-modal-close:hover {
        color: #0f172a;
    }
    .bt-dev-modal-body {
        padding: 24px 26px;
        background: #ffffff;
    }

    /* Sandbox Testing Bar (Local Dev) */
    .bt-sandbox-bar {
        margin-top: 24px;
        background: #ffffff;
        border: 1px solid #d1fae5;
        border-radius: 10px !important;
        padding: 12px 18px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        font-size: 12px;
        color: #334155;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.08);
    }
    .bt-sandbox-code {
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        font-weight: 800;
        color: #047857;
        background: #ecfdf5;
        padding: 4px 8px;
        border-radius: 5px !important;
        border: 1px solid #a7f3d0;
    }
    .bt-sandbox-btn {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        color: #334155;
        padding: 6px 12px;
        border-radius: 6px !important;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .bt-sandbox-btn:hover {
        background: #10b981;
        border-color: #10b981;
        color: #ffffff;
    }

    @media (max-width: 640px) {
        .bt-security-wrap {
            padding: 20px 12px;
        }
        .bt-security-header {
            padding: 26px 20px 20px;
        }
        .bt-security-title {
            font-size: 24px;
        }
        .bt-sec-pane {
            padding: 24px 20px 28px;
        }
        .bt-code-input {
            font-size: 21px !important;
            letter-spacing: 0.12em !important;
            padding: 12px 14px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="bt-security-wrap">
    <div class="bt-security-glow-1"></div>
    <div class="bt-security-glow-2"></div>

    <div class="bt-security-container">
        <div class="bt-security-card">
            <!-- Header Security Status -->
            <div class="bt-security-header">
                <div class="bt-security-badge">
                    <span class="bt-security-badge-dot"></span>
                    <span>High-End Security Clearance Gate</span>
                </div>
                <h1 class="bt-security-title">Platform Access Clearance</h1>
                <p class="bt-security-sub">
                    Direct access to Bridge-Tailor is restricted for all guests, clients, consultants, and administrators. 
                    <strong>Enter your authorized access code</strong> or <strong>submit your email</strong> to request clearance routed directly to administration.
                </p>
            </div>

            <!-- Segmented Switcher Tabs -->
            <div class="bt-sec-switcher" role="tablist">
                <button type="button" class="bt-sec-tab-btn active" id="tabBtnEnter" onclick="switchSecTab('enter')" role="tab" aria-selected="true" aria-controls="paneEnter">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <span>Enter Access Code</span>
                </button>
                <button type="button" class="bt-sec-tab-btn" id="tabBtnRequest" onclick="switchSecTab('request')" role="tab" aria-selected="false" aria-controls="paneRequest">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span>Request Access Code</span>
                </button>
            </div>

            <!-- PANE 1: Enter Access Code -->
            <div class="bt-sec-pane active" id="paneEnter" role="tabpanel" aria-labelledby="tabBtnEnter">
                @if($isCleared)
                    <div class="bt-sec-success-card">
                        <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 10px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
                        <h2 class="bt-sec-success-title">Clearance Active on This Device</h2>
                        <p class="bt-sec-success-desc">
                            Your session has verified security clearance (Token: <code style="background:#ecfdf5;color:#047857;padding:2px 6px;border-radius:4px;font-weight:700;">{{ $clearedCode }}</code>). You may enter the platform freely.
                        </p>
                        <div style="display:flex;gap:12px;justify-content:center;margin-top:18px;">
                            <a href="{{ route('diagnostic.landing') }}" class="btn-sec-primary" style="text-decoration:none;width:auto;padding:11px 24px;">
                                <span>Proceed to Platform →</span>
                            </a>
                            <form action="{{ route('security.lock') }}" method="POST" style="margin:0;">
                                @csrf
                                <button type="submit" class="btn-sec-request" style="width:auto;padding:11px 20px;">
                                    <span>Lock Terminal</span>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <form action="{{ route('security.verify') }}" method="POST" class="bt-sec-form" id="verifyCodeForm">
                        @csrf
                        <div class="field">
                            <label for="accessCodeInput">Security Clearance Access Code *</label>
                            <div class="bt-code-input-wrap">
                                <input type="text"
                                       id="accessCodeInput"
                                       name="code"
                                       required
                                       autocomplete="off"
                                       spellcheck="false"
                                       autofocus
                                       value="{{ old('code', '') }}"
                                       placeholder="BT-SEC-XXXXXX"
                                       class="bt-code-input">
                                <div class="bt-code-format-hint">
                                    <span>Format: <code>BT-SEC-XXXXXX</code></span>
                                    <span>Paste code anywhere or press Enter to verify</span>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-sec-primary" id="btnVerifySubmit">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
                            <span>Verify Clearance &amp; Unlock Platform</span>
                        </button>
                    </form>

                    <div style="margin-top:22px;text-align:center;font-size:12.5px;color:#64748b;">
                        Don't have an active clearance token?
                        <a href="javascript:void(0)" onclick="switchSecTab('request')" style="color:#059669;font-weight:700;text-decoration:underline;margin-left:4px;">
                            Request Access Code from Administration →
                        </a>
                    </div>
                @endif
            </div>

            <!-- PANE 2: Request Access Code -->
            <div class="bt-sec-pane" id="paneRequest" role="tabpanel" aria-labelledby="tabBtnRequest">
                <div class="bt-sec-dispatch-box">
                    <svg class="bt-sec-dispatch-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <div class="bt-sec-dispatch-text">
                        <strong>Automatic Administration Dispatch:</strong> Submitting this request will automatically generate a dedicated Security Clearance Code and deliver your message directly to <strong>{{ $adminEmail }}</strong> for approval and sharing.
                    </div>
                </div>

                @if(session('request_success'))
                    <div class="bt-sec-success-card">
                        <svg width="38" height="38" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin:0 auto 8px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <h2 class="bt-sec-success-title">Access Request Dispatched!</h2>
                        <p class="bt-sec-success-desc">
                            Your message and auto-generated access code have been transmitted to <strong>{{ $adminEmail }}</strong>. Once reviewed, administration will share the code with you for entry.
                        </p>
                        <button type="button" class="btn-sec-primary" onclick="switchSecTab('enter')" style="margin-top:16px;width:auto;padding:10px 24px;display:inline-flex;">
                            <span>Return to Enter Code →</span>
                        </button>
                    </div>
                @endif

                <form action="{{ route('security.request') }}" method="POST" class="bt-sec-form" id="requestCodeForm">
                    @csrf
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="field">
                            <label for="reqEmail">Your Email Address *</label>
                            <input type="email"
                                   id="reqEmail"
                                   name="email"
                                   required
                                   value="{{ old('email', '') }}"
                                   placeholder="you@company.com"
                                   autocomplete="email">
                        </div>
                        <div class="field">
                            <label for="reqName">Your Full Name *</label>
                            <input type="text"
                                   id="reqName"
                                   name="name"
                                   required
                                   value="{{ old('name', '') }}"
                                   placeholder="e.g. Kwame Mensah">
                        </div>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                        <div class="field">
                            <label for="reqOrg">Company / Organization</label>
                            <input type="text"
                                   id="reqOrg"
                                   name="organization"
                                   value="{{ old('organization', '') }}"
                                   placeholder="e.g. Volta Agro-Processing Ltd">
                        </div>
                        <div class="field">
                            <label for="reqRole">Audience / Role Required</label>
                            <select id="reqRole" name="role_requested">
                                <option value="guest" {{ old('role_requested') === 'guest' ? 'selected' : '' }}>Guest / External Reviewer</option>
                                <option value="client" {{ old('role_requested') === 'client' ? 'selected' : '' }}>Client / Business Owner</option>
                                <option value="consultant" {{ old('role_requested') === 'consultant' ? 'selected' : '' }}>Advisory Partner / Consultant</option>
                                <option value="admin" {{ old('role_requested') === 'admin' ? 'selected' : '' }}>System Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label for="reqMessage">Request Message &amp; Purpose of Entry *</label>
                        <textarea id="reqMessage"
                                  name="message"
                                  required
                                  rows="3"
                                  placeholder="Provide the reason for your visit or the diagnostic project/assessment you need to access...">{{ old('message', '') }}</textarea>
                    </div>

                    <button type="submit" class="btn-sec-primary" id="btnRequestSubmit">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                        <span>Send Request to {{ $adminEmail }}</span>
                    </button>
                </form>

                <div style="margin-top:20px;text-align:center;font-size:12.5px;color:#64748b;">
                    Already received your access code?
                    <a href="javascript:void(0)" onclick="switchSecTab('enter')" style="color:#059669;font-weight:700;text-decoration:underline;margin-left:4px;">
                        Enter code here →
                    </a>
                </div>
            </div>

            <!-- Developer Master Terminal Access Section -->
            <div class="bt-dev-terminal-bar">
                <button type="button" class="bt-dev-terminal-btn" onclick="openDevModal()">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                    <span>Developer Terminal / Master Bypass</span>
                </button>
            </div>
        </div>

        <!-- Testing / Review Sandbox Helper (Visible in Local Dev) -->
        @if(app()->isLocal() && $latestDemoCode)
            <div class="bt-sandbox-bar">
                <div>
                    <strong style="color:#047857;">Sandbox Testing Aid:</strong>
                    <span>Latest Active Code: <span class="bt-sandbox-code" id="demoCodeSpan">{{ $latestDemoCode->access_code }}</span></span>
                    <span style="color:#64748b;margin-left:6px;">({{ $latestDemoCode->email }})</span>
                </div>
                <button type="button" class="bt-sandbox-btn" onclick="autofillSandboxCode('{{ $latestDemoCode->access_code }}')">
                    Autofill Code
                </button>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Developer Privileged Terminal Authentication (with 2FA dispatched to eabventures@gmail.com) -->
<div class="bt-dev-modal {{ ($errors->has('dev_error') || $errors->has('dev_2fa_error') || session('dev_2fa_step') || $dev2faPending || session('open_dev_modal')) ? 'open' : '' }}" id="devModal" role="dialog" aria-modal="true" aria-labelledby="devModalTitle">
    <div class="bt-dev-modal-content">
        <div class="bt-dev-modal-header">
            <h3 class="bt-dev-modal-title" id="devModalTitle">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span id="devModalHeading">{{ ($dev2faPending || session('dev_2fa_step')) ? 'Developer 2FA Security Challenge' : 'Developer Master Terminal' }}</span>
            </h3>
            <button type="button" class="bt-dev-modal-close" onclick="closeDevModal()" aria-label="Close terminal">&times;</button>
        </div>
        <div class="bt-dev-modal-body">
            <!-- Step 1: Developer Credentials -->
            <div id="devStepCredentials" style="display: {{ ($dev2faPending || session('dev_2fa_step')) ? 'none' : 'block' }};">
                <p style="font-size:12.5px;color:#64748b;line-height:1.55;margin:0 0 16px;">
                    As specified by platform policy, the <strong style="color:#0f172a;">Platform Developer</strong> is strictly exempt from the security code gate. Authenticate your super-account credentials below. A <strong style="color:#047857;">2FA verification code</strong> will be dispatched to <strong style="color:#047857;">{{ $dev2faEmail ?: 'eabventures@gmail.com' }}</strong> for authorization.
                </p>

                @if($errors->has('dev_error'))
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;font-size:12px;color:#b91c1c;margin-bottom:14px;">
                        {{ $errors->first('dev_error') }}
                    </div>
                @endif

                <form action="{{ route('security.dev_login') }}" method="POST" class="bt-sec-form" id="devLoginForm">
                    @csrf
                    <div class="field">
                        <label for="devEmail">Developer Email *</label>
                        <input type="email" id="devEmail" name="email" required value="{{ old('email', 'dev@bridge-tailor.com') }}" placeholder="dev@bridge-tailor.com">
                    </div>
                    <div class="field">
                        <label for="devPassword">Master Password *</label>
                        <div style="position:relative;display:flex;align-items:center;">
                            <input type="password" id="devPassword" name="password" required placeholder="Enter developer password" style="padding-right:70px;">
                            <button type="button" onclick="toggleDevPassword()" id="btnToggleDevPw" style="position:absolute;right:8px;background:#f1f5f9;border:1px solid #cbd5e1;color:#334155;padding:4px 8px;font-size:10px;font-weight:700;border-radius:4px;cursor:pointer;">
                                SHOW
                            </button>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:6px;margin:8px 0 16px;font-size:11.5px;color:#64748b;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>2FA code will be sent to <strong>{{ $dev2faEmail ?: 'eabventures@gmail.com' }}</strong></span>
                    </div>

                    <button type="submit" class="btn-sec-primary" id="btnDevAuth">
                        <span>Authenticate &amp; Request 2FA Code →</span>
                    </button>
                </form>

                @if(app()->isLocal())
                    <div style="margin-top:16px;text-align:center;">
                        <button type="button" class="bt-sandbox-btn" onclick="fillDevDemoCredentials()" style="width:100%;padding:9px 12px;">
                            Quick Fill Developer Credentials (Local Dev)
                        </button>
                    </div>
                @endif
            </div>

            <!-- Step 2: 2FA One-Time Passcode Verification -->
            <div id="devStep2fa" style="display: {{ ($dev2faPending || session('dev_2fa_step')) ? 'block' : 'none' }};">
                <div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;padding:12px 14px;margin-bottom:16px;display:flex;align-items:flex-start;gap:10px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;margin-top:2px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <div style="font-size:12px;color:#065f46;line-height:1.5;">
                        <strong style="color:#047857;">Two-Factor Authorization Code Dispatched!</strong><br>
                        A 6-digit security verification code has been transmitted to <strong style="color:#047857;">{{ $dev2faEmail ?: 'eabventures@gmail.com' }}</strong>. Check the inbox to obtain your one-time code.
                    </div>
                </div>

                @if($errors->has('dev_2fa_error'))
                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:6px;padding:10px 12px;font-size:12px;color:#b91c1c;margin-bottom:14px;">
                        {{ $errors->first('dev_2fa_error') }}
                    </div>
                @endif

                <form action="{{ route('security.dev_verify_2fa') }}" method="POST" class="bt-sec-form" id="devVerify2faForm">
                    @csrf
                    <div class="field">
                        <label for="dev2faCodeInput">6-Digit 2FA Verification Code *</label>
                        <input type="text"
                               id="dev2faCodeInput"
                               name="two_factor_code"
                               required
                               maxlength="10"
                               autocomplete="one-time-code"
                               spellcheck="false"
                               placeholder="123456"
                               style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;font-size:25px !important;font-weight:900 !important;letter-spacing:0.25em !important;text-align:center;color:#047857 !important;background:#f0fdf4 !important;border:2px solid #10b981 !important;padding:14px 16px !important;">
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:#64748b;margin-top:6px;">
                            <span>Code sent to {{ $dev2faEmail ?: 'eabventures@gmail.com' }}</span>
                            <span>Expires in 15 mins</span>
                        </div>
                    </div>

                    <button type="submit" class="btn-sec-primary" style="margin-top:12px;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        <span>Verify 2FA Code &amp; Enter Platform</span>
                    </button>
                </form>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:16px;padding-top:14px;border-top:1px solid #e2e8f0;">
                    <form action="{{ route('security.dev_resend_2fa') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="bt-sandbox-btn" style="background:transparent;border:none;color:#059669;text-decoration:underline;cursor:pointer;padding:0;font-size:11.5px;font-weight:700;">
                            Resend 2FA Code
                        </button>
                    </form>

                    <form action="{{ route('security.dev_cancel_2fa') }}" method="POST" style="margin:0;">
                        @csrf
                        <button type="submit" class="bt-sandbox-btn" style="background:transparent;border:none;color:#64748b;cursor:pointer;padding:0;font-size:11.5px;">
                            ← Change Credentials
                        </button>
                    </form>
                </div>

                @if(app()->isLocal() && $dev2faCodePreview)
                    <div style="margin-top:16px;background:#f0fdf4;border:1px dashed #86efac;border-radius:6px;padding:10px 12px;text-align:center;">
                        <span style="font-size:11px;color:#166534;">Local Sandbox 2FA Preview:</span>
                        <code style="font-family:monospace;font-size:14px;font-weight:800;color:#047857;margin:0 8px;">{{ $dev2faCodePreview }}</code>
                        <button type="button" class="bt-sandbox-btn" onclick="autofill2faCode('{{ $dev2faCodePreview }}')" style="padding:3px 8px;font-size:10px;background:#ffffff;border-color:#a7f3d0;color:#047857;">
                            Autofill 2FA Code
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    function switchSecTab(tab) {
        const isRequest = tab === 'request';
        const tabBtnEnter = document.getElementById('tabBtnEnter');
        const tabBtnRequest = document.getElementById('tabBtnRequest');
        const paneEnter = document.getElementById('paneEnter');
        const paneRequest = document.getElementById('paneRequest');

        if (tabBtnEnter && tabBtnRequest && paneEnter && paneRequest) {
            tabBtnEnter.classList.toggle('active', !isRequest);
            tabBtnEnter.setAttribute('aria-selected', !isRequest ? 'true' : 'false');

            tabBtnRequest.classList.toggle('active', isRequest);
            tabBtnRequest.setAttribute('aria-selected', isRequest ? 'true' : 'false');

            paneEnter.classList.toggle('active', !isRequest);
            paneRequest.classList.toggle('active', isRequest);

            if (!isRequest) {
                const codeInput = document.getElementById('accessCodeInput');
                if (codeInput) codeInput.focus();
            } else {
                const emailInput = document.getElementById('reqEmail');
                if (emailInput) emailInput.focus();
            }
        }
    }

    function openDevModal() {
        const m = document.getElementById('devModal');
        if (m) m.classList.add('open');
    }

    function closeDevModal() {
        const m = document.getElementById('devModal');
        if (m) m.classList.remove('open');
    }

    function autofillSandboxCode(code) {
        switchSecTab('enter');
        const input = document.getElementById('accessCodeInput');
        if (input) {
            input.value = code;
            input.focus();
            input.style.borderColor = '#10b981';
            input.style.backgroundColor = '#ecfdf5';
            setTimeout(() => { 
                input.style.borderColor = ''; 
                input.style.backgroundColor = ''; 
            }, 1500);
        }
    }

    function fillDevDemoCredentials() {
        const email = document.getElementById('devEmail');
        const pass = document.getElementById('devPassword');
        if (email) email.value = 'dev@bridge-tailor.com';
        if (pass) pass.value = 'Developer2026!';
    }

    function toggleDevPassword() {
        const pw = document.getElementById('devPassword');
        const btn = document.getElementById('btnToggleDevPw');
        if (pw && btn) {
            if (pw.type === 'password') {
                pw.type = 'text';
                btn.textContent = 'HIDE';
            } else {
                pw.type = 'password';
                btn.textContent = 'SHOW';
            }
        }
    }

    function autofill2faCode(code) {
        const input = document.getElementById('dev2faCodeInput');
        if (input) {
            input.value = code;
            input.focus();
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-switch to request tab if old inputs exist
        const hasReqOld = {{ (old('organization') || old('message') || session('request_success')) ? 'true' : 'false' }};
        if (hasReqOld) {
            switchSecTab('request');
        }

        // Auto-open developer modal if dev login error occurred or 2FA challenge is active
        @if($errors->has('dev_error') || $errors->has('dev_2fa_error') || session('dev_2fa_step') || $dev2faPending || session('open_dev_modal'))
            openDevModal();
            @if($dev2faPending || session('dev_2fa_step') || $errors->has('dev_2fa_error'))
                const code2fa = document.getElementById('dev2faCodeInput');
                if (code2fa) code2fa.focus();
            @endif
        @endif

        // Auto uppercase and auto-format code input
        const codeInput = document.getElementById('accessCodeInput');
        if (codeInput) {
            codeInput.addEventListener('input', function (e) {
                let val = this.value.toUpperCase().replace(/\s+/g, '');
                this.value = val;
            });
        }

        const dev2faInput = document.getElementById('dev2faCodeInput');
        if (dev2faInput) {
            dev2faInput.addEventListener('input', function (e) {
                this.value = this.value.replace(/\D/g, '').slice(0, 6);
            });
        }

        // Close modal on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeDevModal();
            }
        });
    });
</script>
@endsection
