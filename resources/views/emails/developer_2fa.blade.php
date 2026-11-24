<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Developer Master Terminal 2FA Verification Code</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #0b1120;
            margin: 0;
            padding: 30px 15px;
            color: #f1f5f9;
        }
        .container {
            max-width: 580px;
            margin: 0 auto;
            background: #0f172a;
            border-radius: 12px;
            border: 1px solid rgba(241, 126, 1, 0.4);
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
        }
        .header {
            background-color: #070b14;
            padding: 28px 32px;
            border-bottom: 2px solid #f17e01;
            text-align: center;
        }
        .badge {
            display: inline-block;
            background: rgba(241, 126, 1, 0.18);
            color: #ffa033;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 20px;
            margin-bottom: 12px;
            border: 1px solid rgba(241, 126, 1, 0.4);
        }
        .title {
            color: #ffffff;
            font-size: 21px;
            font-weight: 800;
            margin: 0 0 4px;
        }
        .subtitle {
            color: #94a3b8;
            font-size: 12.5px;
            margin: 0;
        }
        .content {
            padding: 32px;
        }
        .alert-intro {
            font-size: 14px;
            line-height: 1.6;
            color: #cbd5e1;
            margin-bottom: 22px;
        }
        .code-box {
            background: #070b14;
            border: 1.5px solid #f17e01;
            border-radius: 10px;
            padding: 26px 20px;
            text-align: center;
            margin: 24px 0;
            box-shadow: 0 0 25px rgba(241, 126, 1, 0.15);
        }
        .code-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #94a3b8;
            margin-bottom: 10px;
        }
        .code-val {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 38px;
            font-weight: 900;
            letter-spacing: 0.28em;
            color: #ffa033;
            margin: 0;
            user-select: all;
        }
        .code-expiry {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 10px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 24px;
            background: #111827;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #1e293b;
        }
        .meta-table th {
            text-align: left;
            padding: 9px 12px;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            background: #1e293b;
            width: 35%;
        }
        .meta-table td {
            padding: 9px 12px;
            font-size: 12.5px;
            color: #e2e8f0;
            border-top: 1px solid #1e293b;
        }
        .footer {
            background-color: #070b14;
            padding: 18px 32px;
            border-top: 1px solid #1e293b;
            font-size: 11.5px;
            color: #64748b;
            text-align: center;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <span class="badge">Developer Privileged Clearance</span>
            <h1 class="title">Two-Factor Authentication Code</h1>
            <p class="subtitle">Bridge-Tailor Master Platform Terminal</p>
        </div>

        <div class="content">
            <p class="alert-intro">
                A sign-in attempt was initiated for the <strong>Platform Developer Super-Account</strong> (<code>{{ $developer->email }}</code>). Use the 6-digit verification code below to authorize entry and bypass the security gate.
            </p>

            <div class="code-box">
                <div class="code-label">6-Digit Developer 2FA Code</div>
                <div class="code-val">{{ $otp }}</div>
                <div class="code-expiry">Expires in 15 minutes · One-time verification token</div>
            </div>

            <p style="font-size:13px; color:#cbd5e1; line-height:1.5;">
                Enter this code in the <strong>Developer Master Terminal</strong> prompt on the platform access gate to unlock unrestricted Developer privileges.
            </p>

            <table class="meta-table">
                <tr>
                    <th>Account</th>
                    <td>{{ $developer->name }} (<code>{{ $developer->email }}</code>)</td>
                </tr>
                <tr>
                    <th>Target Recipient</th>
                    <td><strong>eabventures@gmail.com</strong></td>
                </tr>
                <tr>
                    <th>Attempted At</th>
                    <td>{{ now()->format('M d, Y H:i:s T') }}</td>
                </tr>
                <tr>
                    <th>Origin IP</th>
                    <td><code>{{ $ip ?: '127.0.0.1' }}</code></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            Delivered exclusively to <strong>eabventures@gmail.com</strong><br>
            Bridge-Tailor Executive Management &amp; Diagnostic Platform Security
        </div>
    </div>
</body>
</html>
