<!DOCTYPE html>
<html>
<head>
    <title>Investment Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: #6366f1;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9fafb;
            padding: 30px;
            border-radius: 0 0 8px 8px;
            border: 1px solid #e5e7eb;
        }
        .button {
            display: inline-block;
            background: #6366f1;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('Lotrix') }}</h1>
        </div>
        <div class="content">
            <h2>Invitation to Join as Investor</h2>
            
            <p>Hello,</p>
            
            <p>You have been invited to join as an investor for <strong>{{ $invitation->company_name }}</strong>.</p>
            
            <p>Click the button below to accept the invitation and complete your registration:</p>
            
            <p style="text-align: center;">
                <a href="{{ $registerUrl }}" class="button">Accept Invitation</a>
            </p>
            
            <p><small>This invitation will expire on: {{ $invitation->expires_at->format('F j, Y') }}</small></p>
            
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('Lotrix') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
