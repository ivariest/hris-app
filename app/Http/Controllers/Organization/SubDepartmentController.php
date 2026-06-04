<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Department;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class SubDepartmentController extends Controller
{
    public function index(Request $request)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');
        $query = SubDepartment::query()->latest();

        if ($supportsCompanyHierarchy) {
            $query->with('department.company');
        } else {
            $query->with('department');
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('sub_department_name', 'like', "%{$search}%");
            });
        }

        $companyId = $request->integer('company_id');
        $departmentId = $request->integer('department_id');
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        } elseif ($companyId) {
            $query->whereHas('department', function ($builder) use ($companyId) {
                $builder->where('company_id', $companyId);
            });
        }

        return view('pages.organization.sub-departments.index', [
            'title' => 'Sub Departments',
            'subDepartments' => $query->paginate(10)->withQueryString(),
            'companies' => Company::orderBy('company_name')->get(),
            'departments' => Department::with('company')->orderBy('department_name')->get(),
            'companyOptions' => Company::orderBy('company_name')->get()->map(function ($company) {
                return [
                    'id' => $company->id,
                    'company_name' => $company->company_name,
                ];
            })->values(),
            'departmentOptions' => $supportsCompanyHierarchy
                ? Department::with('company')->orderBy('department_name')->get()->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'company_id' => $department->company_id,
                        'department_name' => $department->department_name,
                    ];
                })->values()
                : collect(),
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
            'search' => $request->string('search')->toString(),
            'companyId' => $companyId,
            'departmentId' => $request->integer('department_id'),
        ]);
    }

    public function create()
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        return view('pages.organization.sub-departments.create', [
            'title' => 'Create Sub Department',
            'companies' => Company::orderBy('company_name')->get(),
            'departments' => Department::with('company')->orderBy('department_name')->get(),
            'departmentHierarchyOptions' => $supportsCompanyHierarchy
                ? Department::with('company')->orderBy('department_name')->get()->map(function (Department $department) {
                    return [
                        'id' => $department->id,
                        'company_id' => $department->company_id,
                        'name' => $department->department_name,
                    ];
                })->values()
                : collect(),
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
        ]);
    }

    public function store(Request $request)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        $data = $request->validate([
            'company_id' => $supportsCompanyHierarchy ? ['required', 'exists:companies,id'] : ['nullable'],
            'department_id' => ['required', 'exists:departments,id'],
            'sub_department_name' => ['required', 'string', 'max:100'],
        ]);

        if ($supportsCompanyHierarchy) {
            $department = Department::findOrFail($data['department_id']);

            if ((string) $department->company_id !== (string) $data['company_id']) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department does not belong to the chosen company.',
                ]);
            }
        }

        SubDepartment::create($data);

        return redirect()->route('sub-departments.index')->with('success', 'Sub department created successfully.');
    }

    public function show(SubDepartment $subDepartment)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        if ($supportsCompanyHierarchy) {
            $subDepartment->load('department.company');
        } else {
            $subDepartment->load('department');
        }

        $subDepartment->loadCount('positions');

        return view('pages.organization.sub-departments.show', [
            'title' => $subDepartment->sub_department_name,
            'subDepartment' => $subDepartment,
            'companies' => Company::orderBy('company_name')->get(),
            'departments' => Department::with('company')->orderBy('department_name')->get(),
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
        ]);
    }

    public function edit(SubDepartment $subDepartment)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        return view('pages.organization.sub-departments.edit', [
            'title' => 'Edit Sub Department',
            'subDepartment' => $subDepartment,
            'companies' => Company::orderBy('company_name')->get(),
            'departments' => Department::with('company')->orderBy('department_name')->get(),
            'departmentHierarchyOptions' => $supportsCompanyHierarchy
                ? Department::with('company')->orderBy('department_name')->get()->map(function (Department $department) {
                    return [
                        'id' => $department->id,
                        'company_id' => $department->company_id,
                        'name' => $department->department_name,
                    ];
                })->values()
                : collect(),
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
        ]);
    }

    public function update(Request $request, SubDepartment $subDepartment)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        $data = $request->validate([
            'company_id' => $supportsCompanyHierarchy ? ['required', 'exists:companies,id'] : ['nullable'],
            'department_id' => ['required', 'exists:departments,id'],
            'sub_department_name' => ['required', 'string', 'max:100'],
        ]);

        if ($supportsCompanyHierarchy) {
            $department = Department::findOrFail($data['department_id']);

            if ((string) $department->company_id !== (string) $data['company_id']) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department does not belong to the chosen company.',
                ]);
            }
        }

        $subDepartment->update($data);

        return redirect()->route('sub-departments.index')->with('success', 'Sub department updated successfully.');
    }

    public function destroy(SubDepartment $subDepartment)
    {
        $subDepartment->delete();

        return redirect()->route('sub-departments.index')->with('success', 'Sub department deleted successfully.');
    }
}
