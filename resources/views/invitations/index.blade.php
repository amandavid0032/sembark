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
    <p class="card-subtitle">Mail is set to <span class="code">log</span> in dev, so the invitee won't get a real email. Copy the link below and send it to them manually.</p>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Email</th>
                <th style="width:110px;">Role</th>
                <th>Company</th>
                <th style="width:150px;">Sent at</th>
                <th>Accept link</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pending as $inv)
                @php($acceptUrl = route('invitations.accept.show', $inv->token))
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->email }}</td>
                    <td><span class="badge badge-role">{{ $inv->role }}</span></td>
                    <td>{{ $inv->company ? $inv->company->name : '—' }}</td>
                    <td class="muted">{{ $inv->created_at->format('Y-m-d H:i') }}</td>
                    <td>
                        <div class="row-inline-form">
                            <input type="text" class="form-control" value="{{ $acceptUrl }}" readonly onclick="this.select()" style="font-size:12px;">
                            <button type="button" class="btn btn-sm btn-secondary"
                                    onclick="navigator.clipboard.writeText('{{ $acceptUrl }}').then(()=>{this.textContent='Copied!';setTimeout(()=>this.textContent='Copy',1500);})">
                                Copy
                            </button>
                            <a href="{{ $acceptUrl }}" target="_blank" class="btn btn-sm btn-secondary">Open</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No pending invitations.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h3>Accepted invitations</h3>
    <p class="card-subtitle">History of invites that have been claimed.</p>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Email</th>
                <th style="width:110px;">Role</th>
                <th>Company</th>
                <th>Accepted by</th>
                <th style="width:160px;">Accepted at</th>
            </tr>
        </thead>
        <tbody>
            @forelse($accepted as $inv)
                <tr>
                    <td>{{ $inv->id }}</td>
                    <td>{{ $inv->email }}</td>
                    <td><span class="badge badge-role">{{ $inv->role }}</span></td>
                    <td>{{ $inv->company ? $inv->company->name : '—' }}</td>
                    <td>
                        @if($inv->acceptedUser)
                            <a href="{{ route('users.show', $inv->acceptedUser->id) }}">{{ $inv->acceptedUser->name }}</a>
                        @else
                            <span class="muted">—</span>
                        @endif
                    </td>
                    <td class="muted">{{ $inv->accepted_at?->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No accepted invitations yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
