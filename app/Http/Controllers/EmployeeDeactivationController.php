<?php

namespace App\Http\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeDeactivationController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()
            ->latest()
            ->with(['company', 'employeePosition.position.subDepartment.department.company', 'employeePosition.level']);

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('nik_karyawan', 'like', "%{$search}%")
                    ->orWhere('nama_karyawan', 'like', "%{$search}%");
            });
        }

        if ($companyId = $request->integer('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status_karyawan', $status);
        }

        return view('pages.employee-management.deactivate-employees.index', [
            'title' => 'Deactivate Employees',
            'employees' => $query->paginate(10)->withQueryString(),
            'companies' => Company::orderBy('company_name')->get(),
            'search' => $request->string('search')->toString(),
            'companyId' => $request->integer('company_id'),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function edit(Employee $employee)
    {
        if ($employee->status_karyawan !== 'active') {
            return redirect()->route('employee-deactivations.index')->with('error', 'Employee sudah inactive.');
        }

        $employee->load(['company', 'employeePosition.position.subDepartment.department.company', 'employeePosition.level']);

        return view('pages.employee-management.deactivate-employees.edit', [
            'title' => 'Deactivate Employee',
            'employee' => $employee,
            'reasonOptions' => $this->reasonOptions(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        if ($employee->status_karyawan !== 'active') {
            return redirect()->route('employee-deactivations.index')->with('error', 'Employee sudah inactive.');
        }

        $data = $request->validate([
            'deactivation_reason' => ['required', Rule::in(array_keys($this->reasonOptions()))],
            'effective_date' => ['required', 'date'],
            'deactivation_document' => ['nullable', 'file', 'max:4096'],
        ]);

        $deactivationData = [
            'status_karyawan' => 'inactive',
            'deactivation_reason' => $data['deactivation_reason'],
            'deactivated_at' => $data['effective_date'],
        ];

        if ($request->hasFile('deactivation_document')) {
            UploadHelper::delete($employee->deactivation_document_path);
            $deactivationData['deactivation_document_path'] = UploadHelper::store($request->file('deactivation_document'), 'employee-deactivations/documents');
        }

        $employee->update($deactivationData);

        return redirect()->route('employee-deactivations.index')->with('success', 'Employee deactivated successfully.');
    }

    private function reasonOptions(): array
    {
        return [
            'RESIGN' => 'RESIGN',
            'PHK' => 'PHK',
            'PENSIUN' => 'PENSIUN',
            'END_CONTRACT' => 'END CONTRACT',
        ];
    }
}
