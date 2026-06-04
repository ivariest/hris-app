<?php

namespace App\Http\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Employee;
use App\Models\EmployeeDemotion;
use App\Models\EmployeePosition;
use App\Models\Position;
use App\Models\PositionLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmployeeDemotionController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeDemotion::query()
            ->with([
                'employee.company',
                'oldPosition.subDepartment.department.company',
                'oldLevel',
                'newPosition.subDepartment.department.company',
                'newLevel',
            ])
            ->latest();

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('demotion_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('nama_karyawan', 'like', "%{$search}%")
                            ->orWhere('nik_karyawan', 'like', "%{$search}%");
                    });
            });
        }

        return view('pages.employee-management.demotions.index', [
            'title' => 'Demotion',
            'demotions' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.employee-management.demotions.create', $this->formViewData([
            'title' => 'Create Demotion',
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validateDemotion($request);
        $employee = $this->resolveEmployeeForDemotion((int) $data['employee_id']);
        $currentPosition = $employee->employeePosition;

        $demotionData = [
            'employee_id' => $employee->id,
            'demotion_number' => $this->nextDemotionNumber(),
            'old_position_id' => $currentPosition->position_id,
            'old_level_id' => $currentPosition->level_id,
            'new_position_id' => $data['new_position_id'],
            'new_level_id' => $data['new_level_id'],
            'effective_date' => $data['effective_date'],
            'notes' => $data['notes'] ?? null,
        ];

        if ($request->hasFile('demotion_form')) {
            $demotionData['demotion_form_path'] = UploadHelper::store($request->file('demotion_form'), 'employee-demotions/forms');
        }

        DB::transaction(function () use ($currentPosition, $demotionData, $data): void {
            EmployeeDemotion::create($demotionData);

            $this->applyEmployeePosition($currentPosition, (int) $data['new_position_id'], (int) $data['new_level_id']);
        });

        return redirect()->route('employee-demotions.index')->with('success', 'Demotion created successfully.');
    }

    public function show(EmployeeDemotion $employeeDemotion)
    {
        $employeeDemotion->load([
            'employee.company',
            'employee.employeePosition.position.subDepartment.department.company',
            'employee.employeePosition.level',
            'oldPosition.subDepartment.department.company',
            'oldLevel',
            'newPosition.subDepartment.department.company',
            'newLevel',
        ]);

        return view('pages.employee-management.demotions.show', [
            'title' => 'Demotion Detail',
            'demotion' => $employeeDemotion,
        ]);
    }

    private function formViewData(array $overrides = []): array
    {
        $employees = Employee::query()
            ->with([
                'company',
                'employeePosition.position.subDepartment.department.company',
                'employeePosition.level',
            ])
            ->whereHas('employeePosition')
            ->where('status_karyawan', 'active')
            ->orderBy('nama_karyawan')
            ->get();

        $employeeOptions = $employees->map(fn (Employee $employee) => [
            'id' => $employee->id,
            'name' => $employee->nama_karyawan,
            'nik' => $employee->nik_karyawan,
            'company_name' => $employee->company?->company_name,
            'current_position_name' => $employee->employeePosition?->position?->position_name,
            'current_level_id' => $employee->employeePosition?->level_id,
            'current_level_name' => $employee->employeePosition?->level?->level_name,
            'current_department_id' => $employee->employeePosition?->position?->subDepartment?->department_id,
            'current_department_name' => $employee->employeePosition?->position?->subDepartment?->department?->department_name,
        ])->values();

        $positionOptions = Position::with(['subDepartment.department.company'])
            ->orderBy('position_name')
            ->get()
            ->map(fn (Position $position) => [
                'id' => $position->id,
                'department_id' => $position->subDepartment?->department_id,
                'label' => collect([
                    $position->position_name,
                    $position->subDepartment?->sub_department_name,
                    $position->subDepartment?->department?->department_name,
                    $position->subDepartment?->department?->company?->company_name,
                ])->filter()->implode(' - '),
            ])
            ->values();

        return array_merge([
            'employees' => $employees,
            'employeeOptions' => $employeeOptions,
            'positions' => $positionOptions,
            'positionLevels' => $this->orderedPositionLevels(),
            'nextDemotionNumber' => $this->nextDemotionNumber(),
        ], $overrides);
    }

    private function validateDemotion(Request $request): array
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'new_position_id' => ['required', 'exists:positions,id'],
            'new_level_id' => ['required', 'exists:position_levels,id'],
            'effective_date' => ['required', 'date'],
            'demotion_form' => ['nullable', 'file', 'max:4096'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = $this->resolveEmployeeForDemotion((int) $data['employee_id']);
        $currentPosition = $employee->employeePosition;

        if ($employee->status_karyawan !== 'active') {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee inactive tidak bisa diproses untuk demotion.',
            ]);
        }

        if (! $currentPosition?->position_id || ! $currentPosition?->level_id) {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee belum memiliki data jabatan dan pangkat aktif.',
            ]);
        }

        $newPosition = Position::with('subDepartment.department')->findOrFail($data['new_position_id']);
        $employeeDepartmentId = $currentPosition->position?->subDepartment?->department_id;

        if ((string) $newPosition->subDepartment?->department_id !== (string) $employeeDepartmentId) {
            throw ValidationException::withMessages([
                'new_position_id' => 'Jabatan baru wajib berasal dari department yang sama dengan employee.',
            ]);
        }

        $this->validateDemotionLevel($currentPosition->level, (int) $data['new_level_id']);

        return $data;
    }

    private function resolveEmployeeForDemotion(int $employeeId): Employee
    {
        return Employee::with([
            'employeePosition.position.subDepartment.department.company',
            'employeePosition.level',
        ])
            ->findOrFail($employeeId);
    }

    private function applyEmployeePosition(EmployeePosition $currentPosition, int $positionId, int $levelId): void
    {
        EmployeePosition::updateOrCreate(
            ['employee_id' => $currentPosition->employee_id],
            [
                'position_id' => $positionId,
                'level_id' => $levelId,
                'area' => $currentPosition->area,
                'rayon' => $currentPosition->rayon,
                'location_id' => $currentPosition->location_id,
                'atasan_langsung' => $currentPosition->atasan_langsung,
            ]
        );
    }

    private function nextDemotionNumber(): string
    {
        return $this->nextRecordNumber(EmployeeDemotion::class, 'demotion_number', 'DM');
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

    private function levelRanks(): array
    {
        return [
            'STAFF' => 1,
            'COORDINATOR' => 2,
            'KEPALA SEKSI' => 3,
            'SECT HEAD' => 4,
            'SUB DEPT HEAD' => 5,
            'DEPT HEAD' => 6,
            'DIRECTOR' => 7,
        ];
    }

    private function normalizeLevelName(?string $levelName): string
    {
        return strtoupper(trim(preg_replace('/\s+/', ' ', (string) $levelName)));
    }

    private function levelRank(?string $levelName): ?int
    {
        return $this->levelRanks()[$this->normalizeLevelName($levelName)] ?? null;
    }

    private function orderedPositionLevels()
    {
        return PositionLevel::all()
            ->sortBy(fn (PositionLevel $level) => $this->levelRank($level->level_name) ?? 999)
            ->values();
    }

    private function validateDemotionLevel(?PositionLevel $currentLevel, int $newLevelId): void
    {
        $newLevel = PositionLevel::findOrFail($newLevelId);
        $currentRank = $this->levelRank($currentLevel?->level_name);
        $newRank = $this->levelRank($newLevel->level_name);

        if ($currentRank === null || $newRank === null) {
            throw ValidationException::withMessages([
                'new_level_id' => 'Urutan pangkat belum terdaftar untuk pangkat saat ini atau pangkat baru.',
            ]);
        }

        if ($newRank >= $currentRank) {
            throw ValidationException::withMessages([
                'new_level_id' => 'Pangkat demotion harus lebih rendah dari pangkat saat ini.',
            ]);
        }
    }
}
