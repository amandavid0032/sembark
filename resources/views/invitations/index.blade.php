@extends('layouts.app')

@section('content')
@php($role = Auth::user()->role)

<div class="page-header">
    <h1>Invitations</h1>
    <p>
        @if($role === 'SuperAdmin')
            Invite an Admin into an existing company. They'll receive an email with a link to set their password.
        @elseif($role === 'Admin')
            Invite Members, Sales, or Managers to your company. They'll receive an email with a link to set their password.
        @else
            You do not have permission to send invitations.
        @endif
    </p>
</div>

@if(in_array($role, ['SuperAdmin', 'Admin']))
<div class="card">
    <h3>Send invitation</h3>
    <form method="POST" action="{{ route('invitations.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group inline">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="user@example.com" required>
            </div>
            <div class="form-group inline" style="max-width: 200px;">
                <label>Role</label>
                <select name="role" class="form-control" required>
                    @if($role === 'SuperAdmin')
                        <option value="Admin">Admin</option>
                    @elseif($role === 'Admin')
                        <option value="Member">Member</option>
                        <option value="Sales">Sales</option>
                        <option value="Manager">Manager</option>
                    @endif
                </select>
            </div>
            @if($role === 'SuperAdmin')
            <div class="form-group inline">
                <label>Company</label>
                <select name="company_id" class="form-control" required>
                    <option value="">— select a company —</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ old('company_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
                @if($companies->isEmpty())
                    <small style="color: var(--danger);">No companies yet. <a href="{{ route('companies.index') }}">Create one first</a>.</small>
                @endif
            </div>
            @endif
            <button type="submit" class="btn">Send invitation</button>
        </div>
    </form>
</div>
@endif

<div class="card">
    <h3>Pending invitations</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Email</th>
                <th style="width:120px;">Role</th>
                <th>Company</th>
                <th style="width:160px;">Sent at</th>
                <th style="width:120px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invitations as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->email }}</td>
                    <td><span class="badge badge-role">{{ $inv->role }}</span></td>
                    <td>{{ $inv->company ? $inv->company->name : '—' }}</td>
                    <td class="muted">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                    <td><span class="badge badge-status">Pending</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No pending invitations.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
