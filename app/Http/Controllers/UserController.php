<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;

// Users listing + per-user detail.
// SuperAdmin sees everyone; Admin is scoped to their own company.
// Members/Sales/Manager have no business here.
class UserController extends Controller
{
    private function guard(Request $request): void
    {
        $role = $request->user()?->role;
        abort_unless(in_array($role, ['SuperAdmin', 'Admin']), 403, 'You are not allowed to view users.');
    }

    public function index(Request $request)
    {
        $this->guard($request);

        $me = $request->user();

        $q = User::query()->with('company');

        // SuperAdmin can pick any company from the filter; Admin is locked to theirs.
        if ($me->role === 'SuperAdmin') {
            if ($request->filled('company_id')) {
                $q->where('company_id', (int) $request->company_id);
            }
        } else {
            $q->where('company_id', $me->company_id);
        }

        // Role filter (any of the 5).
        if ($request->filled('role') && in_array($request->role, ['SuperAdmin', 'Admin', 'Member', 'Sales', 'Manager'])) {
            $q->where('role', $request->role);
        }

        // Search by name or email (case-insensitive LIKE).
        if ($request->filled('search')) {
            $term = '%' . $request->search . '%';
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        // Sort: created_desc | created_asc | name_asc | name_desc
        $sort = $request->get('sort', 'created_desc');
        match ($sort) {
            'created_asc'  => $q->orderBy('created_at'),
            'name_asc'     => $q->orderBy('name'),
            'name_desc'    => $q->orderByDesc('name'),
            default        => $q->orderByDesc('created_at'),
        };

        $users = $q->paginate(20)->withQueryString();

        // Company dropdown for the filter (SuperAdmin only).
        $companies = $me->role === 'SuperAdmin'
            ? Company::orderBy('name')->get()
            : collect();

        return view('users.index', [
            'users'     => $users,
            'companies' => $companies,
            'filters'   => [
                'company_id' => $request->get('company_id'),
                'role'       => $request->get('role'),
                'search'     => $request->get('search'),
                'sort'       => $sort,
            ],
        ]);
    }

    public function show(Request $request, User $user)
    {
        $this->guard($request);

        $me = $request->user();

        // Admin can only see users from their own company.
        if ($me->role !== 'SuperAdmin' && $user->company_id !== $me->company_id) {
            abort(403, 'You can only view users in your own company.');
        }

        $user->load(['company', 'shortUrls']);

        return view('users.show', compact('user'));
    }
}
