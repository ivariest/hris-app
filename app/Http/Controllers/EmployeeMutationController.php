<?php

namespace App\Http\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeMutation;
use App\Models\EmployeePosition;
use App\Models\Position;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeMutationController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeMutation::query()
            ->with([
                'employee.company',
                'oldPosition.subDepartment.department.company',
                'newPosition.subDepartment.department.company',
            ])
            ->latest();

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('mutation_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('nama_karyawan', 'like', "%{$search}%")
                            ->orWhere('nik_karyawan', 'like', "%{$search}%");
                    });
            });
        }

        return view('pages.employee-management.mutations.index', [
            'title' => 'Mutation',
            'mutations' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.employee-management.mutations.create', $this->formViewData([
            'title' => 'Create Mutation',
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validateMutation($request);
        $employee = Employee::with([
            'employeePosition.position.subDepartment.department.company',
            'employeePosition.location',
            'employeePosition.supervisor',
            'company',
        ])
            ->where('status_karyawan', 'active')
            ->findOrFail($data['employee_id']);

        $currentPosition = $employee->employeePosition;

        if (! $currentPosition?->position_id) {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee belum memiliki jabatan aktif.',
            ]);
        }

        if ((int) $currentPosition->position_id === (int) $data['new_position_id']) {
            throw ValidationException::withMessages([
                'new_position_id' => 'Jabatan baru harus berbeda dari jabatan saat ini.',
            ]);
        }

        DB::transaction(function () use ($currentPosition, $employee, $data): void {
            $mutationData = [
                'employee_id' => $employee->id,
                'mutation_number' => $this->nextMutationNumber(),
                'old_position_id' => $currentPosition->position_id,
                'new_position_id' => $data['new_position_id'],
                'effective_date' => $data['effective_date'],
                'notes' => $data['notes'] ?? null,
            ];

            if (isset($data['mutation_letter_path'])) {
                $mutationData['mutation_letter_path'] = $data['mutation_letter_path'];
            }

            EmployeeMutation::create($mutationData);

            EmployeePosition::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'position_id' => $data['new_position_id'],
                    'level_id' => $currentPosition->level_id,
                    'area' => $currentPosition->area,
                    'rayon' => $currentPosition->rayon,
                    'location_id' => $currentPosition->location_id,
                    'atasan_langsung' => $currentPosition->atasan_langsung,
                ]
            );
        });

        return redirect()->route('employee-mutations.index')->with('success', 'Mutation created successfully.');
    }

    public function show(EmployeeMutation $employeeMutation)
    {
        $employeeMutation->load([
            'employee.company',
            'employee.employeePosition.position.subDepartment.department.company',
            'oldPosition.subDepartment.department.company',
            'newPosition.subDepartment.department.company',
        ]);

        return view('pages.employee-management.mutations.show', [
            'title' => 'Mutation Detail',
            'mutation' => $employeeMutation,
        ]);
    }

    private function formViewData(array $overrides = []): array
    {
        $employees = Employee::query()
            ->with([
                'company',
                'employeePosition.position.subDepartment.department.company',
            ])
            ->whereHas('employeePosition')
            ->where('status_karyawan', 'active')
            ->orderBy('nama_karyawan')
            ->get();

        $employeeOptions = $employees->map(function (Employee $employee) {
            $position = $employee->employeePosition?->position;
            $subDepartment = $position?->subDepartment;
            $department = $subDepartment?->department;

            return [
                'id' => $employee->id,
                'name' => $employee->nama_karyawan,
                'nik' => $employee->nik_karyawan,
                'company_id' => $employee->company_id,
                'company_name' => $employee->company?->company_name,
                'current_position_id' => $position?->id,
                'current_position_name' => $position?->position_name,
                'current_sub_department_id' => $subDepartment?->id,
                'current_sub_department_name' => $subDepartment?->sub_department_name,
                'current_department_id' => $department?->id,
                'current_department_name' => $department?->department_name,
            ];
        })->values();

        return array_merge([
            'employees' => $employees,
            'employeeOptions' => $employeeOptions,
            'departments' => Department::with('company')->orderBy('department_name')->get()->map(fn (Department $department) => [
                'id' => $department->id,
                'company_id' => $department->company_id,
                'department_name' => $department->department_name,
            ])->values(),
            'subDepartments' => SubDepartment::with('department')->orderBy('sub_department_name')->get()->map(fn (SubDepartment $subDepartment) => [
                'id' => $subDepartment->id,
                'department_id' => $subDepartment->department_id,
                'sub_department_name' => $subDepartment->sub_department_name,
            ])->values(),
            'positions' => Position::with('subDepartment.department.company')->orderBy('position_name')->get()->map(function (Position $position) {
                return [
                    'id' => $position->id,
                    'company_id' => $position->subDepartment?->department?->company_id,
                    'department_id' => $position->subDepartment?->department_id,
                    'sub_department_id' => $position->sub_department_id,
                    'position_name' => $position->position_name,
                ];
            })->values(),
            'nextMutationNumber' => $this->nextMutationNumber(),
        ], $overrides);
    }

    private function validateMutation(Request $request): array
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'sub_department_id' => ['required', 'exists:sub_departments,id'],
            'new_position_id' => ['required', 'exists:positions,id'],
            'effective_date' => ['required', 'date'],
            'mutation_letter' => ['nullable', 'file', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = Employee::findOrFail($data['employee_id']);

        if ($employee->status_karyawan !== 'active') {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee inactive tidak bisa diproses untuk mutation.',
            ]);
        }
        $department = Department::findOrFail($data['department_id']);
        $subDepartment = SubDepartment::with('department')->findOrFail($data['sub_department_id']);
        $position = Position::with('subDepartment.department')->findOrFail($data['new_position_id']);

        if ((string) $department->company_id !== (string) $employee->company_id) {
            throw ValidationException::withMessages([
                'department_id' => 'Department wajib berasal dari company employee.',
            ]);
        }

        if ((string) $subDepartment->department_id !== (string) $department->id) {
            throw ValidationException::withMessages([
                'sub_department_id' => 'Sub department wajib berasal dari department yang dipilih.',
            ]);
        }

        if ((string) $position->sub_department_id !== (string) $subDepartment->id) {
            throw ValidationException::withMessages([
                'new_position_id' => 'Jabatan wajib berasal dari sub department yang dipilih.',
            ]);
        }

        if ($request->hasFile('mutation_letter')) {
            $data['mutation_letter_path'] = UploadHelper::store($request->file('mutation_letter'), 'employee-mutations/letters');
        }

        return $data;
    }

    private function nextMutationNumber(): string
    {
        return $this->nextRecordNumber(EmployeeMutation::class, 'mutation_number', 'MT');
    }

    private function nextRecordNumber(string $modelClass, string $column, string $prefix): string
    {
        $numbers = $modelClass::withTrashed()
            ->where($column, 'like', $prefix.'%')
            ->pluck($column);

        $lastNumber = $numbers
            ->map(function ($number) use ($prefix) {
                if (! preg_match('/^'.preg_quote($prefix, '/').'(\d+)$/', (string) $number, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return $prefix.str_pad((string) ($lastNumber + 1), 2, '0', STR_PAD_LEFT);
    }
}
