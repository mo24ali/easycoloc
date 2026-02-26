<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join {{ $invitation->collocation->name }} on EasyColoc</title>
    <style>
        body {
            font-family: 'Inter', 'Helvetica Neue', Arial, sans-serif;
            background: #f2f5f9;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(0, 0, 0, .08);
        }

        .header {
            background: #2563eb;
            padding: 36px 40px;
            text-align: center;
        }

        .header h1 {
            color: #fff;
            font-size: 28px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -.5px;
        }

        .header p {
            color: rgba(255, 255, 255, .8);
            font-size: 14px;
            margin: 8px 0 0;
        }

        .body {
            padding: 36px 40px;
        }

        .body p {
            color: #4b6379;
            font-size: 15px;
            line-height: 1.7;
            margin: 0 0 16px;
        }

        .body strong {
            color: #142c3e;
        }

        .info-strip {
            background: #f4f9ff;
            border-left: 4px solid #2563eb;
            border-radius: 0 12px 12px 0;
            padding: 16px 20px;
            margin: 24px 0;
        }

        .info-strip p {
            margin: 0;
            font-size: 14px;
            color: #4b6379;
        }

        .btn {
            display: inline-block;
            background: #2563eb;
            color: #fff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            padding: 14px 36px;
            border-radius: 60px;
            margin: 24px 0;
            letter-spacing: -.2px;
        }

        .footer {
            padding: 24px 40px;
            border-top: 1px solid #dae2ec;
            text-align: center;
        }

        .footer p {
            color: #657e9a;
            font-size: 12px;
            margin: 0;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="header">
            <h1>EasyColoc</h1>
            <p>Shared living, simplified.</p>
        </div>
        <div class="body">
            <p>Hi there 👋</p>
            <p>
                <strong>{{ $invitation->sender->name }}</strong> has invited you to join the collocation
                <strong>{{ $invitation->collocation->name }}</strong> on <strong>EasyColoc</strong>.
            </p>
            <div class="info-strip">
                <p>🏠 <strong>Collocation:</strong> {{ $invitation->collocation->name }}</p>
                <p>👤 <strong>Invited by:</strong> {{ $invitation->sender->name }}</p>
                <p>⏰ <strong>Expires:</strong> {{ $invitation->expires_at->format('d M Y') }}</p>
            </div>
            <p>Click the button below to accept and join:</p>
            <a href="{{ url('/join/' . $invitation->token) }}" class="btn">Accept Invitation</a>
            <p style="font-size:13px;color:#657e9a;">
                Or copy this link: <code>{{ url('/join/' . $invitation->token) }}</code>
            </p>
            <p style="font-size:13px;color:#657e9a;">
                If you weren't expecting this invitation, you can safely ignore this email.
            </p>
        </div>
        <div class="footer">
            <p>EasyColoc · Shared housing made easy.<br>
                This invitation expires on {{ $invitation->expires_at->format('d M Y \a\t H:i') }}.</p>
        </div>
    </div>
</body>

</html>