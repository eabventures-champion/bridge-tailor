@extends('layouts.app')

@section('title', 'Sign In or Create Account')

@push('head')
<style>
    /* Modern Premium Login & Register Layout */
    .bt-auth-section {
        background-color: #f8fafc;
        min-height: calc(100vh - var(--bt-header-height, 86px));
        min-height: calc(100dvh - var(--bt-header-height, 86px));
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 48px 20px;
    }
    .bt-auth-container {
        width: 100%;
        max-width: 1080px;
        margin: 0 auto;
    }
    .bt-auth-card {
        display: grid;
        grid-template-columns: 1.05fr 1fr;
        background-color: #ffffff;
        border-radius: 14px !important;
        border: 1px solid #e2e8f0;
        box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.10), 0 4px 16px -2px rgba(15, 23, 42, 0.04) !important;
        overflow: hidden;
    }

    /* Left Side: Welcoming Visual Panel */
    .bt-auth-visual {
        position: relative;
        background-color: #0f172a;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 44px 40px;
        overflow: hidden;
        min-height: 560px;
    }
    .bt-auth-visual-img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 25%;
        filter: brightness(0.95) contrast(1.03);
        z-index: 1;
        transition: transform 6s ease-out;
    }
    .bt-auth-card:hover .bt-auth-visual-img {
        transform: scale(1.04);
    }
    .bt-auth-visual-tint {
        position: absolute;
        inset: 0;
        z-index: 2;
        background: linear-gradient(180deg, 
            rgba(15, 23, 42, 0.40) 0%, 
            rgba(15, 23, 42, 0.70) 50%, 
            rgba(15, 23, 42, 0.94) 100%
        );
    }

    .bt-auth-visual-bottom {
        position: relative;
        z-index: 5;
        color: #ffffff;
    }
    .bt-auth-visual-title {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 30px;
        line-height: 1.2;
        margin: 0 0 12px;
        color: #ffffff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
    }
    .bt-auth-visual-desc {
        font-size: 13.5px;
        line-height: 1.6;
        color: #e2e8f0;
        margin: 0 0 22px;
        text-shadow: 0 1px 4px rgba(0, 0, 0, 0.6);
    }
    .bt-auth-features {
        display: flex;
        flex-direction: column;
        gap: 10px;
        border-top: 1px solid rgba(255, 255, 255, 0.2);
        padding-top: 18px;
    }
    .bt-auth-feat-item {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 12.5px;
        font-weight: 500;
        color: #f1f5f9;
    }
    .bt-auth-feat-icon {
        width: 18px;
        height: 18px;
        border-radius: 50% !important;
        background-color: var(--bt-amber);
        color: var(--bt-ink);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 800;
        flex-shrink: 0;
    }

    /* Right Side: Form Panel */
    .bt-auth-form-wrap {
        padding: 44px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background-color: #ffffff;
    }

    /* Segmented Switcher Tabs (Sign In / Create Account) */
    .bt-auth-switcher {
        display: flex;
        background-color: #f1f5f9;
        border-radius: 8px !important;
        padding: 4px;
        margin-bottom: 24px;
        border: 1px solid #e2e8f0;
    }
    .bt-auth-tab-btn {
        flex: 1;
        padding: 9px 14px;
        border: none !important;
        background: transparent !important;
        font-family: inherit;
        font-size: 12.5px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        border-radius: 6px !important;
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        text-align: center;
    }
    .bt-auth-tab-btn.active {
        background-color: #ffffff !important;
        color: #0f172a !important;
        font-weight: 700;
        box-shadow: 0 2px 8px rgba(15, 23, 42, 0.08) !important;
    }

    .bt-auth-pane-header {
        margin-bottom: 20px;
    }
    .bt-auth-pane-title {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 26px;
        color: #0f172a;
        margin: 0 0 6px;
    }
    .bt-auth-pane-sub {
        font-size: 13px;
        color: #64748b;
        line-height: 1.5;
        margin: 0;
    }

    /* Input Field Styling */
    .bt-auth-form .field {
        margin-bottom: 16px;
    }
    .bt-auth-form label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #334155;
        margin-bottom: 6px;
    }
    .bt-auth-form input[type="text"],
    .bt-auth-form input[type="email"],
    .bt-auth-form input[type="password"] {
        width: 100%;
        padding: 10px 14px;
        font-size: 13.5px;
        font-family: inherit;
        color: #0f172a;
        background-color: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 6px !important;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
        outline: none;
    }
    .bt-auth-form input:focus {
        border-color: var(--bt-amber) !important;
        box-shadow: 0 0 0 3px rgba(241, 126, 1, 0.18) !important;
    }

    /* Password Wrap Show/Hide Toggle */
    .bt-auth-form .pw-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }
    .bt-auth-form .pw-wrap input {
        padding-right: 64px;
    }
    .bt-auth-form .pw-toggle-btn {
        position: absolute;
        right: 4px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent !important;
        border: none !important;
        padding: 6px 8px;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.04em;
        color: #64748b;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border-radius: 4px !important;
        transition: color 0.15s ease;
    }
    .bt-auth-form .pw-toggle-btn:hover {
        color: var(--bt-amber-deep);
    }

    /* Primary Auth Buttons */
    .btn-auth-primary {
        width: 100%;
        background-color: var(--bt-amber);
        color: var(--bt-ink);
        border: 1px solid var(--bt-amber);
        border-radius: 6px !important;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(241, 126, 1, 0.30) !important;
        transition: all 0.2s ease;
        margin-top: 8px;
    }
    .btn-auth-primary:hover {
        background-color: #ffa033;
        border-color: #ffa033;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(241, 126, 1, 0.40) !important;
    }

    .btn-auth-register {
        width: 100%;
        background-color: #0f172a;
        color: #ffffff;
        border: 1px solid #0f172a;
        border-radius: 6px !important;
        padding: 12px 18px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        box-shadow: 0 4px 14px rgba(15, 23, 42, 0.20) !important;
        transition: all 0.2s ease;
        margin-top: 8px;
    }
    .btn-auth-register:hover {
        background-color: #1e293b;
        border-color: #1e293b;
        color: var(--bt-amber);
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.30) !important;
    }

    .bt-auth-switch-prompt {
        margin-top: 18px;
        text-align: center;
        font-size: 12.5px;
        color: #64748b;
    }
    .bt-auth-switch-link {
        color: var(--bt-amber-deep);
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        margin-left: 4px;
        transition: color 0.15s ease;
    }
    .bt-auth-switch-link:hover {
        color: var(--bt-amber);
        text-decoration: underline;
    }

    /* Smooth animated transitions between tabs */
    .bt-auth-pane {
        display: none;
        animation: authPaneFadeIn 0.25s ease-out;
    }
    .bt-auth-pane.active {
        display: block;
    }
    @keyframes authPaneFadeIn {
        from {
            opacity: 0;
            transform: translateY(6px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Breakpoints */
    @media (max-width: 900px) {
        .bt-auth-card {
            grid-template-columns: 1fr;
            max-width: 520px;
            margin: 0 auto;
        }
        .bt-auth-visual {
            min-height: 240px;
            padding: 28px 24px;
        }
        .bt-auth-visual-title {
            font-size: 24px;
        }
        .bt-auth-visual-desc,
        .bt-auth-features {
            display: none;
        }
        .bt-auth-form-wrap {
            padding: 30px 24px;
        }
    }
    @media (max-width: 480px) {
        .bt-auth-section {
            padding: 20px 12px;
        }
        .bt-auth-visual {
            min-height: 200px;
            padding: 20px 16px;
        }
        .bt-auth-visual-title {
            font-size: 21px;
        }
        .bt-auth-form-wrap {
            padding: 24px 16px;
        }
        .bt-auth-form .flat-grid-2 {
            display: flex !important;
            flex-direction: column !important;
            gap: 0 !important;
        }
    }
</style>
@endpush

@section('content')
@php
    $loginWelcomeImage = trim((string) $uiText('text.login_welcome_image', 'images/slider/slide-1.jpg'));
    if (empty($loginWelcomeImage)) {
        $loginWelcomeImage = 'images/slider/slide-1.jpg';
    }
    $loginImgUrl = (str_starts_with($loginWelcomeImage, 'http://') || str_starts_with($loginWelcomeImage, 'https://') || str_starts_with($loginWelcomeImage, '//'))
        ? $loginWelcomeImage
        : asset(ltrim($loginWelcomeImage, '/'));
@endphp
<section class="bt-auth-section">
    <div class="bt-auth-container">
        <div class="bt-auth-card">
            <!-- Left Side: Welcoming Visual Panel with Inspiring Image -->
            <div class="bt-auth-visual">
                <img src="{{ $loginImgUrl }}"
                     alt="Welcome to Bridge-Tailor"
                     class="bt-auth-visual-img"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=1200&q=80';">
                <div class="bt-auth-visual-tint" aria-hidden="true"></div>

                <div class="bt-auth-visual-bottom">
                    <h2 class="bt-auth-visual-title">Welcome to Bridge-Tailor</h2>
                    <p class="bt-auth-visual-desc">
                        Boutique management advisory and deterministic diagnostic platform designed for African SME leadership, capital access, and board governance.
                    </p>
                    <div class="bt-auth-features">
                        <div class="bt-auth-feat-item">
                            <span class="bt-auth-feat-icon">✓</span>
                            <span>Controlled 0–100 Business Health Scoring</span>
                        </div>
                        <div class="bt-auth-feat-item">
                            <span class="bt-auth-feat-icon">✓</span>
                            <span>Multi-Company Client Portfolio Workspace</span>
                        </div>
                        <div class="bt-auth-feat-item">
                            <span class="bt-auth-feat-icon">✓</span>
                            <span>Advisory Partner &amp; Consultant Review Gateway</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Unified Auth Card (Fused Sign In & Client Account) -->
            <div class="bt-auth-form-wrap">
                <!-- Segmented Tabs -->
                <div class="bt-auth-switcher" role="tablist">
                    <button type="button" class="bt-auth-tab-btn active" id="tabBtnLogin" onclick="switchAuthTab('login')" role="tab" aria-selected="true" aria-controls="paneLogin">
                        Sign In
                    </button>
                    <button type="button" class="bt-auth-tab-btn" id="tabBtnRegister" onclick="switchAuthTab('register')" role="tab" aria-selected="false" aria-controls="paneRegister">
                        Create Account
                    </button>
                </div>

                <!-- Pane 1: Sign In -->
                <div class="bt-auth-pane active" id="paneLogin" role="tabpanel" aria-labelledby="tabBtnLogin">
                    <div class="bt-auth-pane-header">
                        <h1 class="bt-auth-pane-title">Welcome Back</h1>
                        <p class="bt-auth-pane-sub">Enter your credentials to access your diagnostic workspace.</p>
                    </div>

                    <form action="{{ route('login.submit') }}" method="POST" class="bt-auth-form">
                        @csrf
                        <div class="field">
                            <label for="loginEmail">Email Address *</label>
                            <input type="email" id="loginEmail" name="email" required value="{{ old('email', '') }}" placeholder="you@company.com" autocomplete="email">
                        </div>

                        <div class="field">
                            <label for="loginPassword">Password *</label>
                            <div class="pw-wrap">
                                <input type="password" id="loginPassword" name="password" required placeholder="Enter your password" autocomplete="current-password">
                                <button type="button" class="pw-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Show password">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span class="pw-label">SHOW</span>
                                </button>
                            </div>
                        </div>

                        <div style="display:flex;align-items:center;justify-content:space-between;margin:10px 0 16px;">
                            <label for="remember" style="display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:var(--bt-ink);cursor:pointer;margin-bottom:0;text-transform:none;letter-spacing:normal;user-select:none;">
                                <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="width:16px;height:16px;cursor:pointer;accent-color:var(--bt-amber,#f17e01);margin:0;border-radius:3px!important;">
                                <span>Remember session</span>
                            </label>
                        </div>

                        <button type="submit" class="btn-auth-primary">
                            <span>Sign In</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </form>

                    <div class="bt-auth-switch-prompt">
                        Don't have an account yet?
                        <a href="javascript:void(0)" class="bt-auth-switch-link" onclick="switchAuthTab('register')">Create a Client Account</a>
                    </div>
                </div>

                <!-- Pane 2: Create Multi-Company Client Account -->
                <div class="bt-auth-pane" id="paneRegister" role="tabpanel" aria-labelledby="tabBtnRegister">
                    <div class="bt-auth-pane-header">
                        <h2 class="bt-auth-pane-title">Create Client Account</h2>
                        <p class="bt-auth-pane-sub">Register to manage your enterprise diagnostics and track advisory interventions.</p>
                    </div>

                    <form action="{{ route('register.submit') }}" method="POST" class="bt-auth-form">
                        @csrf
                        <div class="flat-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="field">
                                <label for="regName">Full Name *</label>
                                <input type="text" id="regName" name="name" required value="{{ old('name', '') }}" placeholder="e.g. Kwame Osei-Tutu">
                            </div>
                            <div class="field">
                                <label for="regTitle">Role / Title</label>
                                <input type="text" id="regTitle" name="job_title" value="{{ old('job_title', '') }}" placeholder="Founder / CEO / CFO">
                            </div>
                        </div>

                        <div class="flat-grid-2" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                            <div class="field">
                                <label for="regEmail">Business Email *</label>
                                <input type="email" id="regEmail" name="email" required value="{{ old('email', '') }}" placeholder="you@company.com">
                            </div>
                            <div class="field">
                                <label for="regPhone">Phone / WhatsApp</label>
                                <input type="text" id="regPhone" name="phone" value="{{ old('phone', '') }}" placeholder="+233 24 000 0000">
                            </div>
                        </div>

                        <div class="field">
                            <label for="regPassword">Password (min 8 characters) *</label>
                            <div class="pw-wrap">
                                <input type="password" id="regPassword" name="password" required placeholder="Minimum 8 characters">
                                <button type="button" class="pw-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Show password">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <span class="pw-label">SHOW</span>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-auth-register">
                            <span>Create Client Account</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="margin-left:2px;"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </button>
                    </form>

                    <div class="bt-auth-switch-prompt">
                        Already have an existing account?
                        <a href="javascript:void(0)" class="bt-auth-switch-link" onclick="switchAuthTab('login')">Sign In here</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    function switchAuthTab(tab) {
        const isRegister = tab === 'register';
        const tabBtnLogin = document.getElementById('tabBtnLogin');
        const tabBtnRegister = document.getElementById('tabBtnRegister');
        const paneLogin = document.getElementById('paneLogin');
        const paneRegister = document.getElementById('paneRegister');

        if (tabBtnLogin && tabBtnRegister && paneLogin && paneRegister) {
            tabBtnLogin.classList.toggle('active', !isRegister);
            tabBtnLogin.setAttribute('aria-selected', !isRegister ? 'true' : 'false');

            tabBtnRegister.classList.toggle('active', isRegister);
            tabBtnRegister.setAttribute('aria-selected', isRegister ? 'true' : 'false');

            paneLogin.classList.toggle('active', !isRegister);
            paneRegister.classList.toggle('active', isRegister);
        }
    }

    // Auto-select register tab if requested via query param, old input, or error
    document.addEventListener('DOMContentLoaded', function () {
        const params = new URLSearchParams(window.location.search);
        const hasRegisterInput = {{ (old('name') || old('job_title') || old('phone') || $errors->has('name') || $errors->has('job_title')) ? 'true' : 'false' }};
        if (params.get('tab') === 'register' || hasRegisterInput) {
            switchAuthTab('register');
        }
    });
</script>
@endsection
