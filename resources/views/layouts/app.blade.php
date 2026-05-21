<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Shortener SaaS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f6f7fb;
            --surface: #ffffff;
            --border: #e4e7ee;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --primary-soft: #eef2ff;
            --danger: #dc2626;
            --danger-hover: #b91c1c;
            --success-bg: #ecfdf5;
            --success-text: #047857;
            --success-border: #a7f3d0;
            --error-bg: #fef2f2;
            --error-text: #b91c1c;
            --error-border: #fecaca;
            --shadow: 0 1px 2px rgba(16,24,40,0.04), 0 1px 3px rgba(16,24,40,0.06);
            --shadow-lg: 0 4px 12px rgba(16,24,40,0.08);
            --radius: 10px;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0; padding: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            line-height: 1.5;
        }
        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 14px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .brand {
            display: flex; align-items: center; gap: 10px;
            font-weight: 700; font-size: 16px; color: var(--text);
        }
        .brand-mark {
            width: 28px; height: 28px; border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700;
        }
        .user-info { display: flex; align-items: center; gap: 14px; color: var(--muted); font-size: 13px; }
        .user-info strong { color: var(--text); font-weight: 600; }
        .role-badge {
            display: inline-block; padding: 2px 8px; border-radius: 999px;
            background: var(--primary-soft); color: var(--primary);
            font-size: 11px; font-weight: 600; margin-left: 6px;
        }

        .nav {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            display: flex; gap: 4px;
        }
        .nav a {
            padding: 12px 16px;
            color: var(--muted);
            font-weight: 500;
            border-bottom: 2px solid transparent;
            text-decoration: none;
            transition: color .15s, border-color .15s;
        }
        .nav a:hover { color: var(--text); text-decoration: none; }
        .nav a.active { color: var(--primary); border-bottom-color: var(--primary); }

        .container {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 24px;
        }
        .page-header { margin-bottom: 20px; }
        .page-header h1 { margin: 0; font-size: 22px; font-weight: 700; }
        .page-header p { margin: 4px 0 0; color: var(--muted); font-size: 13px; }

        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            margin-bottom: 18px;
            box-shadow: var(--shadow);
        }
        .card h3 { margin: 0 0 14px; font-size: 15px; font-weight: 600; }
        .card-subtitle { color: var(--muted); font-size: 13px; margin: -8px 0 14px; }

        .form-row { display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end; }
        .form-group { margin-bottom: 14px; }
        .form-group.inline { margin-bottom: 0; flex: 1; min-width: 200px; }
        .form-group label {
            display: block; margin-bottom: 6px;
            font-size: 12px; font-weight: 600; color: var(--muted);
            text-transform: uppercase; letter-spacing: 0.3px;
        }
        .form-control {
            width: 100%; padding: 9px 12px;
            border: 1px solid var(--border); border-radius: 8px;
            font-size: 14px; font-family: inherit; color: var(--text);
            background: #fff; outline: none;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
        }
        select.form-control { appearance: none;
            background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'><path fill='%236b7280' d='M6 8L2 4h8z'/></svg>");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 32px;
        }

        .btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 9px 16px;
            background: var(--primary); color: #fff;
            border: 1px solid var(--primary);
            border-radius: 8px; font-size: 14px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            font-family: inherit;
            transition: background .15s, border-color .15s;
        }
        .btn:hover { background: var(--primary-hover); border-color: var(--primary-hover); text-decoration: none; color: #fff; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .btn-secondary { background: #fff; color: var(--text); border-color: var(--border); }
        .btn-secondary:hover { background: #f9fafb; color: var(--text); }
        .btn-danger { background: var(--danger); border-color: var(--danger); }
        .btn-danger:hover { background: var(--danger-hover); border-color: var(--danger-hover); }

        .alert { padding: 12px 14px; margin-bottom: 18px; border-radius: 8px; border: 1px solid; font-size: 13px; }
        .alert-success { background: var(--success-bg); color: var(--success-text); border-color: var(--success-border); }
        .alert-error { background: var(--error-bg); color: var(--error-text); border-color: var(--error-border); }
        .alert ul { margin: 0; padding-left: 18px; }

        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 14px; text-align: left; font-size: 13px; }
        th {
            color: var(--muted); font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.3px; font-size: 11px;
            border-bottom: 1px solid var(--border);
            background: #fafbfc;
        }
        tbody tr { border-bottom: 1px solid var(--border); }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: #fafbfc; }
        td .badge {
            display: inline-block; padding: 2px 8px; border-radius: 999px;
            font-size: 11px; font-weight: 600;
        }
        .badge-role { background: var(--primary-soft); color: var(--primary); }
        .badge-status { background: #fef3c7; color: #92400e; }
        .empty { padding: 24px; text-align: center; color: var(--muted); }

        .muted { color: var(--muted); font-size: 12px; }
        .code {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            font-size: 12px; background: #f3f4f6; padding: 1px 6px; border-radius: 4px;
        }
        .row-inline-form { display: flex; gap: 8px; align-items: center; }
        .row-inline-form .form-control { padding: 6px 10px; }

        .logout-form { margin: 0; display: inline; }
    </style>
</head>
<body>

    @if(Auth::check())
    @php
        $role = Auth::user()->role;
        $current = request()->path();
    @endphp

    <div class="topbar">
        <div class="brand">
            <span class="brand-mark">U</span>
            <span>URL Shortener SaaS</span>
        </div>
        <div class="user-info">
            <span>{{ Auth::user()->name }} <span class="role-badge">{{ $role }}</span></span>
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="btn btn-sm btn-secondary">Logout</button>
            </form>
        </div>
    </div>

    <div class="nav">
        <a href="{{ route('dashboard') }}" class="{{ $current === 'dashboard' ? 'active' : '' }}">Dashboard</a>
        @if($role === 'SuperAdmin')
            <a href="{{ route('companies.index') }}" class="{{ str_starts_with($current, 'companies') ? 'active' : '' }}">Companies</a>
        @endif
        @if(in_array($role, ['SuperAdmin', 'Admin']))
            <a href="{{ route('invitations.index') }}" class="{{ str_starts_with($current, 'invitations') ? 'active' : '' }}">Invitations</a>
            <a href="{{ route('users.index') }}" class="{{ str_starts_with($current, 'users') ? 'active' : '' }}">Users</a>
        @endif
        <a href="{{ route('short-urls.index') }}" class="{{ str_starts_with($current, 'short-urls') ? 'active' : '' }}">Short URLs</a>
    </div>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </div>
    @else
        @yield('content')
    @endif

</body>
</html>
