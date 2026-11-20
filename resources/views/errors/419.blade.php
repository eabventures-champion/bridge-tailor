@extends('layouts.app')

@section('title', 'Session Expired · Bridge-Tailor')

@section('content')
<section style="min-height: calc(100vh - 200px); display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: #f8fafc;">
    <div style="max-width: 520px; width: 100%; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 36px 32px; box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06); text-align: center;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 56px; height: 56px; border-radius: 50%; background: #fff7ed; color: var(--bt-amber-deep, #c25e00); font-size: 26px; margin-bottom: 18px; border: 1.5px solid #fed7aa;">
            ⏳
        </div>
        
        <div style="font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; color: var(--bt-amber-deep, #c25e00); margin-bottom: 6px;">
            SECURITY &amp; SESSION TIMEOUT
        </div>
        
        <h1 style="font-family: 'DM Serif Display', Georgia, serif; font-size: 28px; color: #0f172a; margin: 0 0 10px;">
            Page or Session Expired
        </h1>
        
        <p style="font-size: 13.5px; color: #64748b; line-height: 1.6; margin: 0 0 24px;">
            For your security, form tokens expire after inactivity to protect your account and data. Please return to the login page or refresh to continue.
        </p>
        
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('login') }}" class="btn btn-amber" style="padding: 10px 22px; font-size: 13px; font-weight: 700; border-radius: 8px !important;">
                Return to Sign In →
            </a>
            <button type="button" onclick="window.location.reload()" class="btn btn-outline" style="padding: 10px 20px; font-size: 13px; font-weight: 600; border-radius: 8px !important;">
                Refresh Page ⟳
            </button>
        </div>
    </div>
</section>
@endsection
