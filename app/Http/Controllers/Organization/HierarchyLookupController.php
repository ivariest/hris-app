<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\SubDepartment;
use App\Models\Company;

class HierarchyLookupController extends Controller
{
    public function departmentsByCompany(Company $company)
    {
        return response()->json(
            Department::query()
                ->where('company_id', $company->id)
                ->orderBy('department_name')
                ->get(['id', 'department_name'])
        );
    }

    public function subDepartmentsByDepartment(Department $department)
    {
        return response()->json(
            SubDepartment::query()
                ->where('department_id', $department->id)
                ->orderBy('sub_department_name')
                ->get(['id', 'sub_department_name'])
        );
    }
}
