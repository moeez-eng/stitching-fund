<!DOCTYPE html>
<html>
<head>
    <title>Investment Invitation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #1f2937;
            color: #f9fafb;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #111827;
            border-radius: 8px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .logo {
            max-width: 120px;
            height: auto;
            display: block;
            margin: 0 auto 20px auto;
        }
        .content {
            background-color: #111827;
            padding: 30px;
            color: #f9fafb;
        }
        .content h2 {
            color: #f3f4f6;
            margin-bottom: 20px;
        }
        .content p {
            color: #d1d5db;
            line-height: 1.6;
        }
        .button {
            display: inline-block;
            background: #374151;
            color: #f9fafb;
            padding: 6px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 500;
            border: 1px solid #4b5563;
            transition: all 0.2s ease;
        }
        .button:hover {
            background: #4b5563;
            border-color: #6b7280;
        }
        .footer {
            background-color: #1f2937;
            text-align: center;
            padding: 1px;
            color: #9ca3af;
            font-size: 14px;
            border-top: 1px solid #374151;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="content">
            <img src="{{ $message->embed($logoPath) }}" alt="ZARYQ logo" class="logo">
            <h2>Invitation to Join as Investor</h2>
            
            <p>Hello,</p>
            
            <p>You have been invited to join as an investor for <strong>{{ $invitation->company_name }}</strong>.</p>
            
            <p>Click the button below to accept the invitation and complete your registration:</p>
            
            <p style="text-align: center;">
                <a href="{{ $registerUrl }}" class="button"
                 style="display: inline-block; background: #AD46FF; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 6px; margin: 20px 0;">Accept Invitation</a>
            </p>
            <h5>Importance of this invitation</h5>
            <p><small>This invitation will expire on: {{ $invitation->expires_at->format('F j, Y') }}</small><br>
            and this is one time invitation</p>
            
            <p>If you didn't expect this invitation, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
