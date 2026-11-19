<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Business Diagnostic Platform') — Bridge-Tailor Consulting</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bt-espresso: #2c2927;
            --bt-ink: #141210;
            --bt-umber: #534a45;
            --bt-amber: #f17e01;
            --bt-amber-deep: #B85A0A;
            --bt-amber-dark: #7A3B06;
            --bt-amber-pale: #FDEBD3;
            --bt-cream: #FFF7ED;
            --bt-slate: #f2f5f7;
            --bt-white: #ffffff;
            --bt-border: #D8CFC4;
            --bt-border-strong: #2c2927;
            --bt-teal: #2F5E76;
            --bt-teal-pale: #E3EDF1;
            --bt-danger: #9E2A1C;
            --bt-danger-pale: #F7E4E1;
            --bt-success: #255C3E;
            --bt-success-pale: #E2F0E8;
        }
        * {
            box-sizing: border-box;
            border-radius: 0 !important;
            box-shadow: none !important;
        }
        html {
            height: 100%;
        }
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 13.5px;
            line-height: 1.48;
            color: var(--bt-ink);
            background-color: var(--bt-white);
            -webkit-font-smoothing: antialiased;
        }
        body {
            min-height: 100%;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            padding-top: 90px;
        }
        main {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }
        pre, code {
            max-width: 100%;
            overflow-wrap: anywhere;
            word-break: break-word;
        }
        pre {
            white-space: pre-wrap !important;
        }
        h1, h2, .serif {
            font-family: 'DM Serif Display', Georgia, serif;
            font-weight: 400;
            letter-spacing: -0.01em;
            margin: 0;
        }
        h3, h4, h5 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            margin: 0;
        }
        a {
            color: inherit;
            text-decoration: none;
        }
        /* Fixed Top Header Shell (Mobile & Desktop) */
        .bt-top-fixed {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 9999;
            background-color: var(--bt-espresso);
        }
        /* Top Utility Strip */
        .bt-utility {
            background-color: var(--bt-ink);
            color: #d6cfc7;
            font-size: 11px;
            border-bottom: 1px solid #3a3532;
        }
        .bt-utility-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 6px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .bt-role-form {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin: 0;
        }
        .bt-role-btn {
            background-color: #2c2927;
            color: #e6dfd8;
            border: 1px solid #4d4642;
            padding: 2px 8px;
            font-size: 10.5px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .bt-role-btn.active, .bt-role-btn:hover {
            background-color: var(--bt-amber);
            color: var(--bt-ink);
            border-color: var(--bt-amber);
        }
        /* Primary Header */
        .bt-header {
            background-color: var(--bt-espresso);
            color: var(--bt-white);
            border-bottom: 3px solid var(--bt-amber);
        }
        .bt-header-inner {
            max-width: 1280px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }
        .bt-brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .bt-brand-mark {
            background-color: var(--bt-amber);
            color: var(--bt-ink);
            font-weight: 800;
            font-size: 14px;
            padding: 6px 9px;
            letter-spacing: 0.06em;
        }
        .bt-brand-text {
            display: flex;
            flex-direction: column;
        }
        .bt-brand-name {
            font-weight: 800;
            font-size: 15px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--bt-white);
        }
        .bt-brand-sub {
            font-size: 10.5px;
            color: #cac4c4;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        .bt-nav {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-wrap: wrap;
        }
        .bt-nav a {
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 600;
            color: #e6dfd8;
            border: 1px solid transparent;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .bt-nav a:hover, .bt-nav a.active {
            background-color: #3d3835;
            color: var(--bt-white);
            border-color: #5c544f;
        }
        .bt-nav a.bt-nav-cta {
            background-color: var(--bt-amber);
            color: var(--bt-ink);
            font-weight: 700;
            border-color: var(--bt-amber);
            margin-left: 6px;
        }
        .bt-nav a.bt-nav-cta:hover {
            background-color: #ffa033;
        }
        /* Solid Section Bands */
        .sec-espresso { background-color: var(--bt-espresso); color: var(--bt-white); border-bottom: 1px solid #3f3a37; }
        .sec-cream    { background-color: var(--bt-cream);    color: var(--bt-ink);   border-bottom: 1px solid var(--bt-border); }
        .sec-slate    { background-color: var(--bt-slate);    color: var(--bt-ink);   border-bottom: 1px solid var(--bt-border); }
        .sec-white    { background-color: var(--bt-white);    color: var(--bt-ink);   border-bottom: 1px solid var(--bt-border); }
        .sec-umber    { background-color: var(--bt-umber);    color: var(--bt-white); border-bottom: 1px solid #3f3834; }
        .sec-amber    { background-color: var(--bt-amber-pale); color: var(--bt-ink); border-bottom: 1px solid #e4c49c; }

        .bt-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 24px 20px;
        }
        .bt-container-tight {
            max-width: 1280px;
            margin: 0 auto;
            padding: 16px 20px;
        }
        /* Compact Typography & Labels */
        .eyebrow {
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: var(--bt-amber-deep);
            margin-bottom: 4px;
        }
        .sec-espresso .eyebrow, .sec-umber .eyebrow {
            color: var(--bt-amber);
        }
        .sec-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 16px;
            padding-bottom: 10px;
            margin-bottom: 14px;
            border-bottom: 2px solid var(--bt-espresso);
            flex-wrap: wrap;
        }
        .sec-espresso .sec-header {
            border-bottom-color: #534a45;
        }
        /* Compact Flat Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 16px;
            font-family: inherit;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid var(--bt-ink);
            background-color: var(--bt-ink);
            color: var(--bt-white);
            cursor: pointer;
        }
        .btn:hover {
            background-color: var(--bt-umber);
            border-color: var(--bt-umber);
        }
        .btn:disabled {
            opacity: 0.35;
            cursor: not-allowed;
        }
        .btn-amber {
            background-color: var(--bt-amber);
            border-color: var(--bt-amber);
            color: var(--bt-ink);
        }
        .btn-amber:hover {
            background-color: #d96f00;
            border-color: #d96f00;
            color: var(--bt-white);
        }
        .btn-outline {
            background-color: var(--bt-white);
            border: 1px solid var(--bt-ink);
            color: var(--bt-ink);
        }
        .btn-outline:hover {
            background-color: var(--bt-cream);
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 11px;
        }
        /* Compact Tables & Flat Grids */
        .bt-table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bt-white);
            border: 1px solid var(--bt-border);
            font-size: 12.5px;
        }
        .bt-table th {
            background-color: var(--bt-espresso);
            color: var(--bt-white);
            text-align: left;
            padding: 8px 11px;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border: 1px solid #3f3a37;
        }
        .bt-table td {
            padding: 8px 11px;
            border: 1px solid var(--bt-border);
            vertical-align: top;
        }
        .bt-table tr:nth-child(even) td {
            background-color: #faf8f5;
        }
        /* Flat bordered boxes (no rounded raised cards) */
        .flat-box {
            border: 1px solid var(--bt-border);
            background-color: var(--bt-white);
            padding: 14px 16px;
            min-width: 0;
            max-width: 100%;
        }
        .flat-box-dark {
            border: 1px solid #4a433f;
            background-color: #221f1d;
            color: var(--bt-white);
            padding: 14px 16px;
            min-width: 0;
            max-width: 100%;
        }
        .flat-grid-2 { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .flat-grid-3 { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .flat-grid-4 { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 0; border: 1px solid var(--bt-border); }
        .flat-grid-5 { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 0; border: 1px solid var(--bt-border); }
        @media (max-width: 900px) {
            .flat-grid-2, .flat-grid-3, .flat-grid-4, .flat-grid-5 { grid-template-columns: 1fr; }
        }
        /* Solid Badges */
        .tag {
            display: inline-block;
            padding: 2px 7px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border: 1px solid currentColor;
        }
        .tag-critical, .tag-high { background-color: var(--bt-ink); color: var(--bt-white); border-color: var(--bt-ink); }
        .tag-medium { background-color: var(--bt-amber-pale); color: var(--bt-amber-dark); border-color: #d6a56a; }
        .tag-low, .tag-solid { background-color: var(--bt-teal-pale); color: var(--bt-teal); border-color: #9ab9c7; }
        .tag-amber { background-color: var(--bt-amber); color: var(--bt-ink); border-color: var(--bt-amber); }
        .tag-ok { background-color: var(--bt-success-pale); color: var(--bt-success); border-color: #96c4aa; }

        /* Forms */
        .field { margin-bottom: 11px; }
        .field label {
            display: block;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--bt-umber);
            margin-bottom: 4px;
        }
        .field input[type="text"],
        .field input[type="email"],
        .field input[type="password"],
        .field input[type="date"],
        .field input[type="file"],
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid #b8aea2;
            background-color: var(--bt-white);
            color: var(--bt-ink);
            padding: 7px 10px;
            font-size: 13px;
            font-family: inherit;
        }
        .field input:focus, .field select:focus, .field textarea:focus {
            outline: none;
            border-color: var(--bt-ink);
            border-width: 2px;
            padding: 6px 9px;
        }
        /* Password Visibility Toggle */
        .pw-wrap {
            position: relative;
            display: flex;
            align-items: stretch;
        }
        .pw-wrap input {
            padding-right: 68px !important;
        }
        .pw-toggle-btn {
            position: absolute;
            right: 2px;
            top: 2px;
            bottom: 2px;
            background-color: var(--bt-cream);
            color: var(--bt-ink);
            border: none;
            border-left: 1px solid #b8aea2;
            padding: 0 10px;
            font-family: inherit;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .pw-toggle-btn:hover {
            background-color: var(--bt-amber);
            color: var(--bt-ink);
        }
        .flash-banner {
            background-color: var(--bt-amber-pale);
            color: var(--bt-ink);
            border-bottom: 2px solid var(--bt-amber-deep);
            padding: 9px 20px;
            font-size: 12.5px;
            font-weight: 600;
        }
        .error-banner {
            background-color: var(--bt-danger-pale);
            color: var(--bt-danger);
            border-bottom: 2px solid var(--bt-danger);
            padding: 9px 20px;
            font-size: 12.5px;
            font-weight: 600;
        }
        /* Solid progress bars */
        .solid-track {
            width: 100%;
            height: 8px;
            background-color: #e4dcd1;
            border: 1px solid #cfc4b6;
        }
        .solid-fill {
            height: 100%;
            background-color: var(--bt-amber-deep);
        }
        /* Footer */
        .bt-footer {
            margin-top: auto;
            flex-shrink: 0;
            background-color: var(--bt-ink);
            color: #c2bab2;
            border-top: 3px solid var(--bt-amber);
            font-size: 11.5px;
        }
        .bt-footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1.3fr;
            gap: 20px;
        }
        /* Mobile menu toggle button */
        .bt-menu-toggle {
            display: none;
            background-color: var(--bt-ink);
            color: var(--bt-white);
            border: 1px solid var(--bt-amber);
            padding: 7px 11px;
            font-family: inherit;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
        }
        /* Responsive Table Scroll Wrapper */
        .bt-table-wrap {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
        }
        /* Responsive Reusable Grids */
        .bt-hero-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr);
            gap: 24px;
            align-items: stretch;
        }
        .bt-score-layout {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            gap: 14px;
            margin-bottom: 14px;
        }
        .bt-scope-grid {
            display: grid;
            grid-template-columns: repeat(5, minmax(0, 1fr));
            gap: 8px;
        }
        .bt-action-log-form {
            display: grid;
            grid-template-columns: 160px 150px minmax(0, 1fr) 140px;
            gap: 6px;
            align-items: center;
        }
        .bt-dev-user-form {
            display: grid;
            grid-template-columns: 1.2fr 1.1fr 1.4fr;
            gap: 10px;
            align-items: start;
        }
        .bt-reading-row {
            display: flex;
            gap: 10px;
            padding: 5px 0;
            border-bottom: 1px solid #ebe4dc;
            font-size: 12.5px;
        }
        .bt-reading-key {
            width: 130px;
            flex-shrink: 0;
            text-transform: uppercase;
            font-size: 10.5px;
            color: var(--bt-amber-dark);
        }

        /* Tablet & Mobile Breakpoints */
        @media (max-width: 960px) {
            .bt-hero-grid,
            .bt-score-layout,
            .bt-footer-grid {
                grid-template-columns: 1fr;
            }
            .bt-scope-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .bt-action-log-form {
                grid-template-columns: 1fr 1fr;
            }
            .flat-grid-4 > div {
                border-right: none !important;
                border-bottom: 1px solid var(--bt-border);
            }
            .flat-grid-4 > div:last-child {
                border-bottom: none;
            }
        }

        @media (max-width: 720px) {
            .bt-container {
                padding: 16px 12px;
            }
            .bt-utility-inner {
                padding: 6px 12px;
                font-size: 10.5px;
            }
            .bt-header-inner {
                padding: 10px 12px;
            }
            .bt-menu-toggle {
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }
            .bt-nav {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                border-top: 1px solid #443e3a;
                padding-top: 8px;
                margin-top: 4px;
                gap: 4px;
            }
            .bt-nav.open {
                display: flex;
            }
            .bt-nav a, .bt-nav a.bt-nav-cta {
                margin-left: 0;
                width: 100%;
                text-align: left;
                padding: 9px 12px;
            }
            h1 {
                font-size: 22px !important;
                line-height: 1.2 !important;
            }
            h2 {
                font-size: 19px !important;
                line-height: 1.25 !important;
            }
            .sec-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            .bt-scope-grid,
            .bt-action-log-form,
            .bt-dev-user-form {
                grid-template-columns: 1fr !important;
            }
            .bt-reading-row {
                flex-direction: column;
                gap: 2px;
            }
            .bt-reading-key {
                width: auto;
            }
            .btn {
                max-width: 100%;
                white-space: normal;
                text-align: center;
            }
            /* Prevent iOS zoom on focus while keeping compact padding */
            .field input[type="text"],
            .field input[type="email"],
            .field input[type="password"],
            .field input[type="date"],
            .field select,
            .field textarea {
                font-size: 16px;
            }
            /* Mobile Stacked Key-Value Table Presentation (Zero Horizontal Overflow) */
            .bt-table,
            .bt-table thead,
            .bt-table tbody,
            .bt-table tr,
            .bt-table th,
            .bt-table td {
                display: block !important;
                width: 100% !important;
                min-width: 0 !important;
                max-width: 100% !important;
            }
            .bt-table {
                border: none !important;
                background: transparent !important;
            }
            .bt-table thead {
                display: none !important;
            }
            .bt-table tbody tr {
                border: 1px solid var(--bt-border-strong) !important;
                background-color: var(--bt-white) !important;
                margin-bottom: 10px !important;
                padding: 0 !important;
            }
            .bt-table tbody td {
                border: none !important;
                border-bottom: 1px solid #e6dfd6 !important;
                padding: 8px 10px !important;
                text-align: left !important;
            }
            .bt-table tbody td:last-child {
                border-bottom: none !important;
            }
            .bt-table tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 9.5px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--bt-amber-deep);
                margin-bottom: 3px;
            }
            .sec-espresso .bt-table tbody td[data-label]::before {
                color: var(--bt-amber);
            }
        }
    </style>
    @stack('head')
</head>
<body>
    <div class="bt-top-fixed">
        <!-- Top Utility Bar: Brand Contact & Authenticated User Status -->
        @if($uiVisible('vis.top_utility_bar'))
        <div class="bt-utility">
            <div class="bt-utility-inner">
                <div>
                    <strong style="color:#fff;">BRIDGE-TAILOR CONSULTING</strong> · {{ $uiText('text.top_bar_office') }} ·
                    <a href="https://bridge-tailor.com" target="_blank" rel="noopener" style="color:var(--bt-amber);text-decoration:underline;">www.bridge-tailor.com</a>
                </div>
                <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                    @auth
                        <span>Signed in as <a href="{{ route('profile.show') }}" style="color:#fff;font-weight:700;text-decoration:underline;">{{ auth()->user()->name }}</a>
                            <span class="tag tag-amber" style="margin-left:4px;">{{ strtoupper(auth()->user()->role) }}</span>
                        </span>
                        <a href="{{ route('profile.show') }}" class="bt-role-btn {{ request()->routeIs('profile.*') ? 'active' : '' }}">My Profile</a>
                        <form action="{{ route('logout') }}" method="POST" style="display:inline;margin:0;">
                            @csrf
                            <button type="submit" class="bt-role-btn">Sign Out</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="bt-role-btn">Client / Staff Sign In</a>
                    @endauth
                </div>
            </div>
        </div>
        @endif

        <!-- Main Brand Navigation (Strictly Scoped by Role) -->
        <header class="bt-header">
            <div class="bt-header-inner">
                <a href="{{ route('diagnostic.landing') }}" class="bt-brand">
                    <span class="bt-brand-mark">BT</span>
                    <span class="bt-brand-text">
                        <span class="bt-brand-name">{{ $uiText('text.brand_title') }}</span>
                        <span class="bt-brand-sub">{{ $uiText('text.brand_subtitle') }}</span>
                    </span>
                </a>
                <button type="button" class="bt-menu-toggle" onclick="const nav=document.getElementById('bt-main-nav'); nav.classList.toggle('open'); syncFixedHeaderOffset();" aria-label="Toggle navigation">
                    <span>☰ MENU</span>
                </button>
                <nav class="bt-nav" id="bt-main-nav">
                    @php
                        $dynamicNavItems = \App\Models\NavigationItem::getForCurrentRequest();
                    @endphp

                    @foreach($dynamicNavItems as $navItem)
                        <a href="{{ $navItem->resolvedUrl() }}"
                           target="{{ $navItem->target ?: '_self' }}"
                           class="{{ $navItem->is_cta ? 'bt-nav-cta' : '' }} {{ $navItem->isCurrent() ? 'active' : '' }}">
                            {{ $navItem->label }}
                        </a>
                    @endforeach
                </nav>
            </div>
        </header>
    </div>

    @if(session('status'))
        <div class="flash-banner">
            <div style="max-width:1280px;margin:0 auto;">■ {{ session('status') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="error-banner">
            <div style="max-width:1280px;margin:0 auto;">
                <strong>Validation Alert:</strong> {{ implode(' · ', $errors->all()) }}
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    @if($uiVisible('vis.footer_section'))
    @php
        $col2Lines = array_filter(array_map('trim', explode("\n", (string) $uiText('text.footer_col2_items', ''))));
        $col3Lines = array_filter(array_map('trim', explode("\n", (string) $uiText('text.footer_col3_items', ''))));
        $col4Lines = array_filter(array_map('trim', explode("\n", (string) $uiText('text.footer_col4_items', ''))));
    @endphp
    <footer class="bt-footer">
        <div class="bt-container bt-footer-grid">
            <div>
                <div style="font-weight:800;color:#fff;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:6px;">
                    {{ $uiText('text.footer_col1_title') }}
                </div>
                <p style="margin:0 0 8px;line-height:1.5;">
                    {{ $uiText('text.footer_tagline') }}
                </p>
                <div style="font-size:11px;color:#9c9288;">
                    {{ $uiText('text.footer_engine_note') }}
                </div>
            </div>
            <div>
                <div style="font-weight:700;color:#fff;text-transform:uppercase;margin-bottom:6px;">
                    {{ $uiText('text.footer_col2_title') }}
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    @foreach($col2Lines as $line)
                        <span>{{ $line }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <div style="font-weight:700;color:#fff;text-transform:uppercase;margin-bottom:6px;">
                    {{ $uiText('text.footer_col3_title') }}
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    @foreach($col3Lines as $line)
                        <span>{{ $line }}</span>
                    @endforeach
                </div>
            </div>
            <div>
                <div style="font-weight:700;color:#fff;text-transform:uppercase;margin-bottom:6px;">
                    {{ $uiText('text.footer_col4_title') }}
                </div>
                <div style="display:flex;flex-direction:column;gap:4px;">
                    @foreach($col4Lines as $line)
                        <span>{{ $line }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </footer>
    @endif
    <script>
        function syncFixedHeaderOffset() {
            const fixedHeader = document.querySelector('.bt-top-fixed');
            if (fixedHeader) {
                document.body.style.paddingTop = fixedHeader.offsetHeight + 'px';
            }
        }
        window.addEventListener('resize', syncFixedHeaderOffset);

        function togglePasswordVisibility(btn) {
            const wrap = btn.closest('.pw-wrap');
            if (!wrap) return;
            const input = wrap.querySelector('input');
            if (!input) return;
            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            btn.querySelector('.pw-label').textContent = isHidden ? 'HIDE' : 'SHOW';
            btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        }

        document.addEventListener('DOMContentLoaded', function () {
            syncFixedHeaderOffset();
            document.querySelectorAll('input[type="password"]').forEach(function (input) {
                if (input.closest('.pw-wrap')) return;
                const wrap = document.createElement('div');
                wrap.className = 'pw-wrap';
                input.parentNode.insertBefore(wrap, input);
                wrap.appendChild(input);

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'pw-toggle-btn';
                btn.setAttribute('aria-label', 'Show password');
                btn.innerHTML = '<svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="square"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg><span class="pw-label">SHOW</span>';
                btn.onclick = function () { togglePasswordVisibility(btn); };
                wrap.appendChild(btn);
            });

            // Auto-stamp column headers onto every table cell as data-label for mobile stacked key-value cards
            document.querySelectorAll('.bt-table').forEach(function (tbl) {
                const headers = Array.from(tbl.querySelectorAll('thead th')).map(function (th) {
                    return th.textContent.trim();
                });
                if (headers.length) {
                    tbl.querySelectorAll('tbody tr').forEach(function (tr) {
                        Array.from(tr.children).forEach(function (td, idx) {
                            if (td.tagName === 'TD' && !td.hasAttribute('data-label') && headers[idx] && td.getAttribute('colspan') === null) {
                                td.setAttribute('data-label', headers[idx]);
                            }
                        });
                    });
                }
            });
        });
    </script>
    @stack('scripts')
</body>
</html>
