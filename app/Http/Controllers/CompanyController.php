<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// SuperAdmin-only CRUD for companies.
// I kept the authorization here as a simple guard method instead of pulling in
// a full Policy class -- only one role can do anything here, so a Policy felt
// like overkill.
class CompanyController extends Controller
{
    private function mustBeSuperAdmin(Request $request): void
    {
        $role = $request->user()?->role;
        abort_unless($role === 'SuperAdmin', 403, 'Only SuperAdmin can manage companies.');
    }

    public function index(Request $request)
    {
        $this->mustBeSuperAdmin($request);

        // Order by id so the table is stable as rows get added/renamed.
        // (Was sorting by name earlier, but that made the IDs look jumbled.)
        $companies = Company::withCount(['users', 'shortUrls', 'invitations'])
            ->orderBy('id')
            ->get();

        var_dump($companies->toArray());
        die();

        return view('companies.index', compact('companies'));
    }

    public function store(Request $request)
    {
        $this->mustBeSuperAdmin($request);

        $data = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
        ]);

        $company = Company::create(['name' => $data['name']]);

        Log::info('company created', ['id' => $company->id, 'name' => $company->name]);

        var_dump($data);
        var_dump($company->toArray());
        die();

        return back()->with('success', 'Company created.');
    }

    public function update(Request $request, Company $company)
    {
        $this->mustBeSuperAdmin($request);

        $data = $request->validate([
            // unique name BUT ignore the current company (otherwise saving the
            // same name fails its own uniqueness check).
            'name' => 'required|string|max:255|unique:companies,name,' . $company->id,
        ]);

        $company->update(['name' => $data['name']]);

        var_dump($data);
        var_dump($company->toArray());
        die();

        return back()->with('success', 'Company updated.');
    }

    public function destroy(Request $request, Company $company)
    {
        $this->mustBeSuperAdmin($request);

        // FK on users/short_urls/invitations cascades, so this also wipes the
        // company's data. The confirm() prompt in the view warns the user.
        var_dump($company->toArray());
        die();

        $company->delete();

        return back()->with('success', 'Company deleted.');
    }
}
