@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>Welcome back, {{ Auth::user()->name }}</h1>
    <p>You're signed in as <strong>{{ Auth::user()->role }}</strong>@if(Auth::user()->company) at <strong>{{ Auth::user()->company->name }}</strong>@endif.</p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px;">
    @if(Auth::user()->role === 'SuperAdmin')
        <a href="{{ route('companies.index') }}" class="card" style="text-decoration:none; color:inherit; display:block;">
            <h3>Manage Companies</h3>
            <p class="muted" style="margin:0;">Create, rename, or remove companies.</p>
        </a>
    @endif
    @if(in_array(Auth::user()->role, ['SuperAdmin', 'Admin']))
        <a href="{{ route('invitations.index') }}" class="card" style="text-decoration:none; color:inherit; display:block;">
            <h3>Invitations</h3>
            <p class="muted" style="margin:0;">Invite users to a company via email.</p>
        </a>
    @endif
    <a href="{{ route('short-urls.index') }}" class="card" style="text-decoration:none; color:inherit; display:block;">
        <h3>Short URLs</h3>
        <p class="muted" style="margin:0;">
            @if(Auth::user()->role === 'SuperAdmin')
                View all short URLs across every company.
            @elseif(Auth::user()->role === 'Admin')
                Create and view short URLs in your company.
            @else
                Create and view your own short URLs.
            @endif
        </p>
    </a>
</div>
@endsection
