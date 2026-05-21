@extends('layouts.app')

@section('content')
@php($me = Auth::user())

<div class="page-header">
    <h1>{{ $user->name }}</h1>
    <p>
        <a href="{{ route('users.index') }}">← Back to users</a>
    </p>
</div>

<div class="card">
    <h3>Profile</h3>
    <table>
        <tbody>
            <tr><th style="width:180px;">ID</th><td>{{ $user->id }}</td></tr>
            <tr><th>Name</th><td>{{ $user->name }}</td></tr>
            <tr><th>Email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Role</th><td><span class="badge badge-role">{{ $user->role }}</span></td></tr>
            <tr><th>Company</th><td>{{ $user->company ? $user->company->name : '— (global)' }}</td></tr>
            <tr><th>Joined</th><td class="muted">{{ $user->created_at->format('Y-m-d H:i') }}</td></tr>
            <tr><th>Last updated</th><td class="muted">{{ $user->updated_at->format('Y-m-d H:i') }}</td></tr>
        </tbody>
    </table>
</div>

@if($me->role === 'SuperAdmin' && $user->id !== $me->id)
<div class="card">
    <h3>Reset password</h3>
    <p class="card-subtitle">As SuperAdmin you can force-set this user's password. They'll need to log in again with the new one.</p>
    <form method="POST" action="{{ route('users.reset-password', $user->id) }}" onsubmit="return confirm('Reset password for {{ $user->email }}?');">
        @csrf
        <div class="form-row">
            <div class="form-group inline" style="max-width: 240px;">
                <label>New password</label>
                <input type="password" name="password" class="form-control" minlength="6" required>
            </div>
            <div class="form-group inline" style="max-width: 240px;">
                <label>Confirm</label>
                <input type="password" name="password_confirmation" class="form-control" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-danger">Reset password</button>
        </div>
    </form>
</div>
@endif

<div class="card">
    <h3>Short URLs created by this user ({{ $user->shortUrls->count() }})</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th style="width:140px;">Code</th>
                <th>Original URL</th>
                <th style="width:160px;">Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->shortUrls as $s)
                <tr>
                    <td>{{ $s->id }}</td>
                    <td><span class="code">{{ $s->short_code }}</span></td>
                    <td><a href="{{ $s->original_url }}" target="_blank" rel="noopener">{{ $s->original_url }}</a></td>
                    <td class="muted">{{ $s->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="empty">This user hasn't created any short URLs.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
