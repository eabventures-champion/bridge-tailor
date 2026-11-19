@extends('layouts.app')

@section('title', 'Sign In or Create Client Account')

@section('content')
<section class="sec-espresso">
    <div class="bt-container">
        <div class="eyebrow">ROLE-BASED ACCESS CONTROL · ADMINISTRATORS, CONSULTANTS &amp; MULTI-COMPANY CLIENTS</div>
        <h1 style="font-size:28px;color:#fff;">Sign In to Bridge-Tailor Diagnostic Platform</h1>
    </div>
</section>

<section class="sec-cream">
    <div class="bt-container">
        <div class="flat-grid-2">
            <!-- Sign In Form -->
            <div class="flat-box">
                <div class="eyebrow">EXISTING ACCOUNT</div>
                <h3 style="font-size:17px;margin-bottom:10px;">Sign In with Email &amp; Password</h3>
                <form action="{{ route('login.submit') }}" method="POST">
                    @csrf
                    <div class="field">
                        <label>Email Address *</label>
                        <input type="email" name="email" required value="{{ old('email', '') }}" placeholder="you@company.com">
                    </div>
                    <div class="field">
                        <label>Password *</label>
                        <div class="pw-wrap">
                            <input type="password" name="password" required placeholder="Enter your password">
                            <button type="button" class="pw-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Show password">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span class="pw-label">SHOW</span>
                            </button>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;margin:10px 0 16px;">
                        <label for="remember" style="display:inline-flex;align-items:center;gap:8px;font-size:12px;font-weight:600;color:var(--bt-ink);cursor:pointer;margin-bottom:0;text-transform:none;letter-spacing:normal;user-select:none;">
                            <input type="checkbox" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }} style="width:16px;height:16px;cursor:pointer;accent-color:var(--bt-amber,#f17e01);margin:0;">
                            <span>Remember session</span>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-amber" style="width:100%;">Sign In →</button>
                </form>
            </div>

            <!-- Register Multi-Company Client Account -->
            <div class="flat-box">
                <div class="eyebrow">NEW CLIENT PORTFOLIO ACCOUNT</div>
                <h3 style="font-size:17px;margin-bottom:10px;">Create Multi-Company Client Account</h3>
                <form action="{{ route('register.submit') }}" method="POST">
                    @csrf
                    <div class="flat-grid-2">
                        <div class="field">
                            <label>Full Name *</label>
                            <input type="text" name="name" required placeholder="Your full name">
                        </div>
                        <div class="field">
                            <label>Role / Title</label>
                            <input type="text" name="job_title" placeholder="Founder / CEO / CFO">
                        </div>
                        <div class="field">
                            <label>Business Email *</label>
                            <input type="email" name="email" required placeholder="you@company.com">
                        </div>
                        <div class="field">
                            <label>Phone</label>
                            <input type="text" name="phone" placeholder="+233 ...">
                        </div>
                    </div>
                    <div class="field">
                        <label>Password (min 8 characters) *</label>
                        <div class="pw-wrap">
                            <input type="password" name="password" required placeholder="Minimum 8 characters">
                            <button type="button" class="pw-toggle-btn" onclick="togglePasswordVisibility(this)" aria-label="Show password">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <span class="pw-label">SHOW</span>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="width:100%;">Create Client Account →</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
