<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = Company::query()->withCount('departments')->latest();

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('company_name', 'like', "%{$search}%")
                    ->orWhere('company_address', 'like', "%{$search}%");
            });
        }

        return view('pages.organization.companies.index', [
            'title' => 'Companies',
            'companies' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.organization.companies.create', [
            'title' => 'Create Company',
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_address' => ['nullable', 'string'],
        ]);

        Company::create($data);

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function show(Company $company)
    {
        $company->loadCount('departments');

        return view('pages.organization.companies.show', [
            'title' => $company->company_name,
            'company' => $company,
        ]);
    }

    public function edit(Company $company)
    {
        return view('pages.organization.companies.edit', [
            'title' => 'Edit Company',
            'company' => $company,
        ]);
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'company_address' => ['nullable', 'string'],
        ]);

        $company->update($data);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }
}
