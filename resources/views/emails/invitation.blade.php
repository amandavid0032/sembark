<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>You've been invited</title>
</head>
<body style="font-family: sans-serif; color: #333; line-height: 1.5;">
    <h2>You've been invited</h2>
    <p>Hello,</p>
    <p>
        You have been invited to join
        <strong>{{ $companyName ?? config('app.name') }}</strong>
        as a <strong>{{ $role }}</strong>.
    </p>
    <p>To accept this invitation, set your password, and log in, click the link below:</p>
    <p>
        <a href="{{ $acceptUrl }}"
           style="display:inline-block; padding:10px 18px; background:#007bff; color:#fff; text-decoration:none; border-radius:4px;">
            Accept invitation
        </a>
    </p>
    <p>Or copy and paste this URL into your browser:<br>
        <a href="{{ $acceptUrl }}">{{ $acceptUrl }}</a>
    </p>
    <p style="color:#777; font-size:13px;">
        If you did not expect this invitation, you can safely ignore this email.
    </p>
    <p>Thanks,<br>{{ config('app.name') }}</p>
</body>
</html>
