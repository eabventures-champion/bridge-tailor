<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Platform Security Access Code Request</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 30px 15px;
            color: #0f172a;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
        }
        .header {
            background-color: #0a0f1d;
            padding: 30px 32px;
            border-bottom: 3px solid #f17e01;
        }
        .badge {
            display: inline-block;
            background: rgba(241, 126, 1, 0.2);
            color: #f17e01;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 12px;
            border: 1px solid rgba(241, 126, 1, 0.35);
        }
        .title {
            color: #ffffff;
            font-size: 20px;
            font-weight: 700;
            margin: 0 0 6px;
        }
        .subtitle {
            color: #94a3b8;
            font-size: 13px;
            margin: 0;
        }
        .content {
            padding: 32px;
        }
        .alert-intro {
            font-size: 14.5px;
            line-height: 1.6;
            color: #334155;
            margin-bottom: 24px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 26px;
        }
        .info-table th {
            text-align: left;
            padding: 10px 14px;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            width: 35%;
        }
        .info-table td {
            padding: 10px 14px;
            font-size: 13.5px;
            color: #0f172a;
            border-bottom: 1px solid #e2e8f0;
            word-break: break-word;
        }
        .code-box {
            background: #0a0f1d;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 24px 20px;
            text-align: center;
            margin: 24px 0;
        }
        .code-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 8px;
        }
        .code-val {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
            font-size: 26px;
            font-weight: 800;
            letter-spacing: 0.12em;
            color: #f17e01;
            margin: 0;
            user-select: all;
        }
        .code-help {
            font-size: 12px;
            color: #cbd5e1;
            margin-top: 10px;
        }
        .message-box {
            background-color: #fffbeb;
            border-left: 4px solid #f17e01;
            padding: 14px 18px;
            border-radius: 0 6px 6px 0;
            margin: 20px 0;
            font-size: 13.5px;
            line-height: 1.5;
            color: #78350f;
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 32px;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">Security Clearance Protocol</span>
            <h1 class="title">New Platform Access Request</h1>
            <p class="subtitle">Bridge-Tailor Management Advisory &amp; Diagnostic Platform</p>
        </div>

        <div class="content">
            <p class="alert-intro">
                A user has submitted an access clearance request to unlock the Bridge-Tailor platform. The platform has automatically generated a unique security access code below.
            </p>

            <div class="code-box">
                <div class="code-label">Generated Security Access Code</div>
                <div class="code-val">{{ $securityCode->access_code }}</div>
                <div class="code-help">Share this access code with <strong>{{ $securityCode->email }}</strong> to grant them platform entry.</div>
            </div>

            <table class="info-table">
                <tr>
                    <th>Requester Name</th>
                    <td><strong>{{ $securityCode->name ?: 'N/A' }}</strong></td>
                </tr>
                <tr>
                    <th>Requester Email</th>
                    <td><a href="mailto:{{ $securityCode->email }}">{{ $securityCode->email }}</a></td>
                </tr>
                <tr>
                    <th>Organization / Role</th>
                    <td>{{ $securityCode->organization ?: 'Not specified' }} ({{ ucfirst($securityCode->role_requested ?: 'guest') }})</td>
                </tr>
                <tr>
                    <th>Status / Expiry</th>
                    <td>{{ ucfirst($securityCode->status) }} · Valid until {{ $securityCode->expires_at ? $securityCode->expires_at->format('M d, Y H:i T') : 'Permanent' }}</td>
                </tr>
                <tr>
                    <th>Origin IP</th>
                    <td><code>{{ $securityCode->ip_address ?: 'Unknown' }}</code></td>
                </tr>
                <tr>
                    <th>Requested At</th>
                    <td>{{ $securityCode->created_at->format('M d, Y H:i:s T') }}</td>
                </tr>
            </table>

            @if ($securityCode->request_message)
                <div style="font-size:12px; font-weight:700; text-transform:uppercase; color:#64748b; margin-bottom:6px;">
                    Requester's Message / Purpose:
                </div>
                <div class="message-box">
                    “{{ $securityCode->request_message }}”
                </div>
            @endif

            <p style="font-size:13px; color:#475569; line-height:1.5;">
                <strong>Next Step:</strong> Review this request and reply directly to the requester or share the code <code>{{ $securityCode->access_code }}</code> with them. They will enter this code on the platform access gate to enter their respective workspaces.
            </p>
        </div>

        <div class="footer">
            Delivered directly to <strong>{{ $securityCode->delivered_to }}</strong><br>
            Bridge-Tailor Security Gateway Engine · East Legon, Accra, Ghana
        </div>
    </div>
</body>
</html>
