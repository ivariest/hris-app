<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query()->withCount('subDepartments')->latest();

        $query->with(['company']);

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('department_name', 'like', "%{$search}%");
            });
        }

        if ($companyId = $request->integer('company_id')) {
            $query->where('company_id', $companyId);
        }

        $departments = $query->paginate(10)->withQueryString();

        return view('pages.organization.departments.index', [
            'title' => 'Departments',
            'departments' => $departments,
            'companies' => Company::orderBy('company_name')->get(),
            'search' => $request->string('search')->toString(),
            'companyId' => $request->integer('company_id'),
        ]);
    }

    public function create()
    {
        return view('pages.organization.departments.create', [
            'title' => 'Create Department',
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'company_id' => ['required', 'exists:companies,id'],
            'department_name' => ['required', 'string', 'max:100'],
        ];

        $data = $request->validate($rules);

        Department::create($data);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function show(Department $department)
    {
        $department->load(['company']);

        $department->loadCount('subDepartments');

        return view('pages.organization.departments.show', [
            'title' => $department->department_name,
            'department' => $department,
        ]);
    }

    public function edit(Department $department)
    {
        return view('pages.organization.departments.edit', [
            'title' => 'Edit Department',
            'department' => $department,
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function update(Request $request, Department $department)
    {
        $rules = [
            'company_id' => ['required', 'exists:companies,id'],
            'department_name' => ['required', 'string', 'max:100'],
        ];

        $data = $request->validate($rules);

        $department->update($data);

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return redirect()
            ->route('departments.index')
            ->with('success', 'Department deleted successfully.');
    }
}
