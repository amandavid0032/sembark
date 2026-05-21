@extends('layouts.app')

@section('content')
@php($role = Auth::user()->role)

<div class="page-header">
    <h1>Users</h1>
    <p>
        @if($role === 'SuperAdmin')
            All users across every company. Click a user to see details.
        @else
            Users in your company. Click a user to see details.
        @endif
    </p>
</div>

<div class="card">
    <h3>Filters</h3>
    <form method="GET" action="{{ route('users.index') }}">
        <div class="form-row">
            <div class="form-group inline">
                <label>Search</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="name or email">
            </div>

            @if($role === 'SuperAdmin')
            <div class="form-group inline" style="max-width: 220px;">
                <label>Company</label>
                <select name="company_id" class="form-control">
                    <option value="">— any company —</option>
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" {{ (string)$filters['company_id'] === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="form-group inline" style="max-width: 180px;">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="">— any role —</option>
                    @foreach(['Admin', 'Member', 'Sales', 'Manager'] as $r)
                        <option value="{{ $r }}" {{ $filters['role'] === $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group inline" style="max-width: 200px;">
                <label>Sort</label>
                <select name="sort" class="form-control">
                    <option value="created_desc" {{ $filters['sort'] === 'created_desc' ? 'selected' : '' }}>Newest first</option>
                    <option value="created_asc"  {{ $filters['sort'] === 'created_asc'  ? 'selected' : '' }}>Oldest first</option>
                    <option value="name_asc"     {{ $filters['sort'] === 'name_asc'     ? 'selected' : '' }}>Name (A→Z)</option>
                    <option value="name_desc"    {{ $filters['sort'] === 'name_desc'    ? 'selected' : '' }}>Name (Z→A)</option>
                </select>
            </div>

            <button type="submit" class="btn">Apply</button>
            <a href="{{ route('users.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>

<div class="card">
    <h3>{{ $users->total() }} {{ Str::plural('user', $users->total()) }}</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Name</th>
                <th>Email</th>
                <th style="width:110px;">Role</th>
                <th>Company</th>
                <th style="width:160px;">Created</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $u)
                <tr style="cursor:pointer;" onclick="window.location='{{ route('users.show', $u->id) }}'">
                    <td>{{ $u->id }}</td>
                    <td><a href="{{ route('users.show', $u->id) }}">{{ $u->name }}</a></td>
                    <td>{{ $u->email }}</td>
                    <td><span class="badge badge-role">{{ $u->role }}</span></td>
                    <td>{{ $u->company ? $u->company->name : '—' }}</td>
                    <td class="muted">{{ $u->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No users match these filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($users->hasPages())
        <div style="margin-top:14px;">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
