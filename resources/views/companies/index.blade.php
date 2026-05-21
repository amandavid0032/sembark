@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1>Companies</h1>
    <p>Create and manage tenant companies. Each company has its own users and short URLs.</p>
</div>

<div class="card">
    <h3>Create company</h3>
    <form method="POST" action="{{ route('companies.store') }}">
        @csrf
        <div class="form-row">
            <div class="form-group inline">
                <label>Company name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Acme Corp" required>
            </div>
            <button type="submit" class="btn">Create</button>
        </div>
    </form>
</div>

<div class="card">
    <h3>All companies</h3>
    <table>
        <thead>
            <tr>
                <th style="width:60px;">ID</th>
                <th>Name</th>
                <th style="width:90px;">Users</th>
                <th style="width:110px;">Short URLs</th>
                <th style="width:140px;">Pending invites</th>
                <th style="width:120px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($companies as $company)
                <tr>
                    <td>{{ $company->id }}</td>
                    <td>
                        <form method="POST" action="{{ route('companies.update', $company->id) }}" class="row-inline-form">
                            @csrf
                            @method('PUT')
                            <input type="text" name="name" class="form-control" value="{{ $company->name }}" style="max-width: 260px;" required>
                            <button type="submit" class="btn btn-sm btn-secondary">Save</button>
                        </form>
                    </td>
                    <td>
                        @if($company->users_count > 0)
                            <a href="{{ route('users.index', ['company_id' => $company->id]) }}">{{ $company->users_count }}</a>
                        @else
                            {{ $company->users_count }}
                        @endif
                    </td>
                    <td>{{ $company->short_urls_count }}</td>
                    <td>{{ $company->invitations_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('companies.destroy', $company->id) }}" style="margin:0;" onsubmit="return confirm('Delete this company and all its users/URLs?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="empty">No companies yet. Create your first one above.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
