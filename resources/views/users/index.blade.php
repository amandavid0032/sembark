@extends('layouts.app')

@section('content')
@php
    $role = Auth::user()->role;

    // Build a chip URL that preserves all current filters except the one being toggled.
    $chipUrl = function ($key, $value) use ($filters) {
        $params = array_filter($filters, fn($v) => $v !== null && $v !== '');
        if ($value === null || $value === '') {
            unset($params[$key]);
        } else {
            $params[$key] = $value;
        }
        return route('users.index', $params);
    };
@endphp

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

    <div class="filter-stack">
        <div class="chip-row">
            <span class="chip-label">Role</span>
            <a href="{{ $chipUrl('role', null) }}" class="chip {{ empty($filters['role']) ? 'active' : '' }}">All</a>
            @foreach(['Admin', 'Member', 'Sales', 'Manager'] as $r)
                <a href="{{ $chipUrl('role', $r) }}" class="chip {{ $filters['role'] === $r ? 'active' : '' }}">{{ $r }}</a>
            @endforeach
        </div>

        @if($role === 'SuperAdmin' && $companies->isNotEmpty())
        <div class="chip-row">
            <span class="chip-label">Company</span>
            <a href="{{ $chipUrl('company_id', null) }}" class="chip {{ empty($filters['company_id']) ? 'active' : '' }}">All</a>
            @foreach($companies as $c)
                <a href="{{ $chipUrl('company_id', $c->id) }}" class="chip {{ (string)$filters['company_id'] === (string)$c->id ? 'active' : '' }}">{{ $c->name }}</a>
            @endforeach
        </div>
        @endif

        <div class="chip-row">
            <span class="chip-label">Sort</span>
            @php($sorts = ['created_desc' => 'Newest', 'created_asc' => 'Oldest', 'name_asc' => 'A → Z', 'name_desc' => 'Z → A'])
            @foreach($sorts as $key => $label)
                <a href="{{ $chipUrl('sort', $key) }}" class="chip {{ $filters['sort'] === $key ? 'active' : '' }}">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('users.index') }}" class="row-inline-form" style="margin-top: 4px;">
            {{-- Preserve other filters across search submits --}}
            @foreach(['company_id', 'role', 'sort'] as $k)
                @if(!empty($filters[$k]))
                    <input type="hidden" name="{{ $k }}" value="{{ $filters[$k] }}">
                @endif
            @endforeach
            <input type="text" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Search name or email..." style="max-width: 360px;">
            <button type="submit" class="btn btn-sm">Search</button>
            @if(!empty($filters['search']))
                <a href="{{ $chipUrl('search', null) }}" class="btn btn-sm btn-secondary">Clear</a>
            @endif
            <a href="{{ route('users.index') }}" class="btn btn-sm btn-secondary" style="margin-left: auto;">Reset all</a>
        </form>
    </div>
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
