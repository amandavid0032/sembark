<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accept invitation — URL Shortener SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #1f2937;
            display: flex; align-items: center; justify-content: center;
            padding: 24px;
        }
        .accept-card {
            background: #fff;
            max-width: 460px; width: 100%;
            padding: 36px;
            border-radius: 14px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2);
        }
        .accept-card h2 { margin: 0 0 8px; font-size: 24px; }
        .accept-card .subtitle { color: #6b7280; margin: 0 0 18px; font-size: 14px; }
        .summary {
            background: #eef2ff; color: #4338ca;
            padding: 12px 14px; border-radius: 8px;
            margin-bottom: 24px; font-size: 13px; line-height: 1.7;
        }
        .summary strong { color: #312e81; }
        label {
            display: block; margin-bottom: 6px;
            font-size: 12px; font-weight: 600; color: #6b7280;
            text-transform: uppercase; letter-spacing: .3px;
        }
        input {
            width: 100%; padding: 10px 12px;
            border: 1px solid #e4e7ee; border-radius: 8px;
            font-size: 14px; outline: none;
            margin-bottom: 14px;
            font-family: inherit;
        }
        input:focus { border-color: #4f46e5; box-shadow: 0 0 0 3px rgba(79,70,229,0.15); }
        button {
            width: 100%; padding: 11px;
            background: #4f46e5; color: #fff;
            border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            font-family: inherit;
            margin-top: 6px;
        }
        button:hover { background: #4338ca; }
        .errors {
            background: #fef2f2; color: #b91c1c;
            padding: 10px 12px; border-radius: 8px;
            margin-bottom: 16px; font-size: 13px;
        }
        .errors ul { margin: 0; padding-left: 18px; }
    </style>
</head>
<body>
    <div class="accept-card">
        <h2>Accept your invitation</h2>
        <p class="subtitle">Set your password to create your account.</p>

        <div class="summary">
            <div><strong>Email:</strong> {{ $invitation->email }}</div>
            <div><strong>Role:</strong> {{ $invitation->role }}</div>
            @if($invitation->company)
                <div><strong>Company:</strong> {{ $invitation->company->name }}</div>
            @endif
        </div>

        @if($errors->any())
            <div class="errors">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
            @csrf
            <label>Your name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus>

            <label>Password</label>
            <input type="password" name="password" required minlength="6">

            <label>Confirm password</label>
            <input type="password" name="password_confirmation" required minlength="6">

            <button type="submit">Create account &amp; log in</button>
        </form>
    </div>
</body>
</html>
