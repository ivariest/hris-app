<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Position;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');
        $query = Position::query()->latest();

        if ($supportsCompanyHierarchy) {
            $query->with(['subDepartment.department.company']);
        } else {
            $query->with(['subDepartment.department']);
        }

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('position_name', 'like', "%{$search}%");
            });
        }

        $companyId = $request->integer('company_id');
        $departmentId = $request->integer('department_id');
        if ($subDepartmentId = $request->integer('sub_department_id')) {
            $query->where('sub_department_id', $subDepartmentId);
        } elseif ($departmentId) {
            $query->whereHas('subDepartment', function ($builder) use ($departmentId) {
                $builder->where('department_id', $departmentId);
            });
        } elseif ($companyId) {
            $query->whereHas('subDepartment.department', function ($builder) use ($companyId) {
                $builder->where('company_id', $companyId);
            });
        }

        return view('pages.organization.positions.index', [
            'title' => 'Positions',
            'positions' => $query->paginate(10)->withQueryString(),
            'companies' => Company::orderBy('company_name')->get(),
            'departments' => $supportsCompanyHierarchy
                ? \App\Models\Department::with('company')->orderBy('department_name')->get()
                : collect(),
            'subDepartments' => SubDepartment::with('department.company')->orderBy('sub_department_name')->get(),
            'companyOptions' => Company::orderBy('company_name')->get()->map(function ($company) {
                return [
                    'id' => $company->id,
                    'company_name' => $company->company_name,
                ];
            })->values(),
            'departmentOptions' => $supportsCompanyHierarchy
                ? \App\Models\Department::with('company')->orderBy('department_name')->get()->map(function ($department) {
                    return [
                        'id' => $department->id,
                        'company_id' => $department->company_id,
                        'department_name' => $department->department_name,
                        'company_name' => $department->company?->company_name,
                    ];
                })->values()
                : collect(),
            'subDepartmentOptions' => SubDepartment::with('department.company')->orderBy('sub_department_name')->get()->map(function ($subDepartment) {
                return [
                    'id' => $subDepartment->id,
                    'department_id' => $subDepartment->department_id,
                    'sub_department_name' => $subDepartment->sub_department_name,
                ];
            })->values(),
            'search' => $request->string('search')->toString(),
            'companyId' => $companyId,
            'departmentId' => $departmentId,
            'subDepartmentId' => $request->integer('sub_department_id'),
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
        ]);
    }

    public function create()
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');
        $subDepartmentHierarchyOptions = $supportsCompanyHierarchy
            ? SubDepartment::with('department.company')->orderBy('sub_department_name')->get()->map(fn (SubDepartment $subDepartment) => [
                'id' => $subDepartment->id,
                'company_id' => $subDepartment->department?->company_id,
                'department_id' => $subDepartment->department_id,
                'department_name' => $subDepartment->department?->department_name,
                'sub_department_name' => $subDepartment->sub_department_name,
            ])->values()
            : collect();

        return view('pages.organization.positions.create', [
            'title' => 'Create Position',
            'companies' => Company::orderBy('company_name')->get(),
            'subDepartments' => $supportsCompanyHierarchy
                ? SubDepartment::with('department.company')->orderBy('sub_department_name')->get()
                : SubDepartment::with('department')->orderBy('sub_department_name')->get(),
            'subDepartmentHierarchyOptions' => $subDepartmentHierarchyOptions,
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
        ]);
    }

    public function store(Request $request)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        $data = $request->validate([
            'company_id' => $supportsCompanyHierarchy ? ['required', 'exists:companies,id'] : ['nullable'],
            'department_id' => $supportsCompanyHierarchy ? ['required', 'exists:departments,id'] : ['nullable'],
            'sub_department_id' => ['required', 'exists:sub_departments,id'],
            'position_name' => ['required', 'string', 'max:100'],
        ]);

        if ($supportsCompanyHierarchy) {
            $department = \App\Models\Department::findOrFail($data['department_id']);
            $subDepartment = SubDepartment::with('department')->findOrFail($data['sub_department_id']);

            if ((string) $department->company_id !== (string) $data['company_id']) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department does not belong to the chosen company.',
                ]);
            }

            if ((string) $subDepartment->department_id !== (string) $department->id) {
                throw ValidationException::withMessages([
                    'sub_department_id' => 'Selected sub department does not belong to the chosen department.',
                ]);
            }
        }

        unset($data['company_id'], $data['department_id']);

        Position::create($data);

        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    public function show(Position $position)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        if ($supportsCompanyHierarchy) {
            $position->load(['subDepartment.department.company']);
        } else {
            $position->load(['subDepartment.department']);
        }

        return view('pages.organization.positions.show', [
            'title' => $position->position_name,
            'position' => $position,
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
            'companies' => Company::orderBy('company_name')->get(),
        ]);
    }

    public function edit(Position $position)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');
        $subDepartmentHierarchyOptions = $supportsCompanyHierarchy
            ? SubDepartment::with('department.company')->orderBy('sub_department_name')->get()->map(fn (SubDepartment $subDepartment) => [
                'id' => $subDepartment->id,
                'company_id' => $subDepartment->department?->company_id,
                'department_id' => $subDepartment->department_id,
                'department_name' => $subDepartment->department?->department_name,
                'sub_department_name' => $subDepartment->sub_department_name,
            ])->values()
            : collect();

        return view('pages.organization.positions.edit', [
            'title' => 'Edit Position',
            'position' => $position,
            'companies' => Company::orderBy('company_name')->get(),
            'subDepartments' => $supportsCompanyHierarchy
                ? SubDepartment::with('department.company')->orderBy('sub_department_name')->get()
                : SubDepartment::with('department')->orderBy('sub_department_name')->get(),
            'subDepartmentHierarchyOptions' => $subDepartmentHierarchyOptions,
            'supportsCompanyHierarchy' => $supportsCompanyHierarchy,
        ]);
    }

    public function update(Request $request, Position $position)
    {
        $supportsCompanyHierarchy = Schema::hasColumn('departments', 'company_id');

        $data = $request->validate([
            'company_id' => $supportsCompanyHierarchy ? ['required', 'exists:companies,id'] : ['nullable'],
            'department_id' => $supportsCompanyHierarchy ? ['required', 'exists:departments,id'] : ['nullable'],
            'sub_department_id' => ['required', 'exists:sub_departments,id'],
            'position_name' => ['required', 'string', 'max:100'],
        ]);

        if ($supportsCompanyHierarchy) {
            $department = \App\Models\Department::findOrFail($data['department_id']);
            $subDepartment = SubDepartment::with('department')->findOrFail($data['sub_department_id']);

            if ((string) $department->company_id !== (string) $data['company_id']) {
                throw ValidationException::withMessages([
                    'department_id' => 'Selected department does not belong to the chosen company.',
                ]);
            }

            if ((string) $subDepartment->department_id !== (string) $department->id) {
                throw ValidationException::withMessages([
                    'sub_department_id' => 'Selected sub department does not belong to the chosen department.',
                ]);
            }
        }

        unset($data['company_id'], $data['department_id']);

        $position->update($data);

        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
    }
}
