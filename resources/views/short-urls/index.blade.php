@extends('layouts.app')

@section('content')
@php($role = Auth::user()->role)

<div class="page-header">
    <h1>Short URLs</h1>
    <p>
        @if($role === 'SuperAdmin')
            Browse short URLs across every company. SuperAdmins cannot create short URLs.
        @elseif($role === 'Admin')
            Create short URLs and view every short URL in your company.
        @else
            Create and manage your own short URLs.
        @endif
    </p>
</div>

@if(in_array($role, ['Admin', 'Member', 'Sales', 'Manager']))
<div class="card">
    <h3>Create short URL</h3>
    <form method="POST" action="{{ route('short-urls.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group inline">
                <label>Original URL</label>
                <input type="url" name="original_url" class="form-control" placeholder="https://example.com/some/long/path" value="{{ old('original_url') }}" required>
            </div>
            <button type="submit" class="btn">Create short URL</button>
        </div>
    </form>
</div>
@endif

<div class="card">
    <h3>Short URLs</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Original URL</th>
                <th style="width:150px;">Short link</th>
                <th>Created by</th>
                @if($role === 'SuperAdmin' || $role === 'Admin')
                    <th>Company</th>
                @endif
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($urls as $url)
                <tr>
                    <td>{{ $url->id }}</td>
                    <td><a href="{{ $url->original_url }}" target="_blank" rel="noopener" style="word-break:break-all;">{{ $url->original_url }}</a></td>
                    <td>
                        <a href="{{ url('/s/' . $url->short_code) }}" target="_blank" rel="noopener" class="code">{{ $url->short_code }}</a>
                    </td>
                    <td>{{ $url->user ? $url->user->name : '—' }}</td>
                    @if($role === 'SuperAdmin' || $role === 'Admin')
                        <td>{{ $url->company ? $url->company->name : '—' }}</td>
                    @endif
                    <td>
                        @can('delete', $url)
                            <form action="{{ route('short-urls.destroy', $url->id) }}" method="POST" style="margin:0;" onsubmit="return confirm('Delete this short URL?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                            </form>
                        @endcan
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ ($role === 'SuperAdmin' || $role === 'Admin') ? 6 : 5 }}" class="empty">No short URLs yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
