<?php

namespace App\Http\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Employee;
use App\Models\EmployeePosition;
use App\Models\EmployeePromotion;
use App\Models\Position;
use App\Models\PositionLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeePromotionController extends Controller
{
    public function index(Request $request)
    {
        $this->synchronizeExpiredTemporaryPromotions();

        $query = EmployeePromotion::query()
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
                $builder->where('promotion_number', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('nama_karyawan', 'like', "%{$search}%")
                            ->orWhere('nik_karyawan', 'like', "%{$search}%");
                    });
            });
        }

        if ($promotionType = trim($request->string('promotion_type')->toString())) {
            $query->where('promotion_type', $promotionType);
        }

        return view('pages.employee-management.promotions.index', [
            'title' => 'Promotion',
            'promotions' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
            'promotionType' => $request->string('promotion_type')->toString(),
            'promotionTypeOptions' => $this->promotionTypeOptions(),
        ]);
    }

    public function create()
    {
        return view('pages.employee-management.promotions.create', $this->formViewData([
            'title' => 'Create Promotion',
            'promotion' => null,
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validatePromotion($request);
        $employee = $this->resolveEmployeeForPromotion($data['employee_id']);
        $currentPosition = $employee->employeePosition;
        $recordStatus = $this->determineRecordStatus($data['promotion_type'], $data['end_date'] ?? null);
        $this->ensureNoOtherActivePromotion($employee->id);

        $promotionData = $this->promotionPayload($data, $employee, $currentPosition, $recordStatus);

        if ($request->hasFile('promotion_form')) {
            $promotionData['promotion_form_path'] = UploadHelper::store($request->file('promotion_form'), 'employee-promotions/forms');
        }

        if ($request->hasFile('appointment_letter')) {
            $promotionData['appointment_letter_path'] = UploadHelper::store($request->file('appointment_letter'), 'employee-promotions/appointment-letters');
        }

        DB::transaction(function () use ($currentPosition, $promotionData, $data): void {
            EmployeePromotion::create($promotionData);

            $this->applyEmployeePosition($currentPosition, $data['new_position_id'], $data['new_level_id']);
        });

        return redirect()->route('employee-promotions.index')->with('success', 'Promotion created successfully.');
    }

    public function edit(EmployeePromotion $employeePromotion)
    {
        $this->synchronizeExpiredTemporaryPromotions();
        $employeePromotion->load([
            'employee.company',
            'employee.employeePosition.position.subDepartment.department.company',
            'employee.employeePosition.level',
            'oldPosition.subDepartment.department.company',
            'oldLevel',
            'newPosition.subDepartment.department.company',
            'newLevel',
        ]);

        return view('pages.employee-management.promotions.edit', $this->formViewData([
            'title' => 'Edit Promotion',
            'promotion' => $employeePromotion,
        ]));
    }

    public function manage(EmployeePromotion $employeePromotion)
    {
        $this->synchronizeExpiredTemporaryPromotions();
        $employeePromotion->load([
            'employee.company',
            'employee.employeePosition.position.subDepartment.department.company',
            'employee.employeePosition.level',
            'oldPosition.subDepartment.department.company',
            'oldLevel',
            'newPosition.subDepartment.department.company',
            'newLevel',
        ]);

        return view('pages.employee-management.promotions.manage', $this->formViewData([
            'title' => 'Update Promotion',
            'promotion' => $employeePromotion,
        ]));
    }

    public function update(Request $request, EmployeePromotion $employeePromotion)
    {
        $this->synchronizeExpiredTemporaryPromotions();
        $mode = $request->string('form_mode')->toString();

        return $mode === 'resolve'
            ? $this->resolveTemporaryPromotion($request, $employeePromotion)
            : $this->updatePromotionDetails($request, $employeePromotion);
    }

    public function destroy(EmployeePromotion $employeePromotion)
    {
        $this->synchronizeExpiredTemporaryPromotions();

        $employeePromotion->load('employee.employeePosition');
        $currentPosition = $employeePromotion->employee?->employeePosition;

        DB::transaction(function () use ($employeePromotion, $currentPosition): void {
            if (
                $currentPosition &&
                $currentPosition->position_id === $employeePromotion->new_position_id &&
                $currentPosition->level_id === $employeePromotion->new_level_id
            ) {
                $this->applyEmployeePosition($currentPosition, $employeePromotion->old_position_id, $employeePromotion->old_level_id);
            }

            UploadHelper::delete($employeePromotion->promotion_form_path);
            UploadHelper::delete($employeePromotion->appointment_letter_path);
            $employeePromotion->delete();
        });

        return redirect()->route('employee-promotions.index')->with('success', 'Promotion deleted successfully and employee position restored.');
    }

    public function show(EmployeePromotion $employeePromotion)
    {
        $this->synchronizeExpiredTemporaryPromotions();
        $employeePromotion->load([
            'employee.company',
            'employee.employeePosition.position.subDepartment.department.company',
            'employee.employeePosition.level',
            'oldPosition.subDepartment.department.company',
            'oldLevel',
            'newPosition.subDepartment.department.company',
            'newLevel',
        ]);

        return view('pages.employee-management.promotions.show', [
            'title' => 'Promotion Detail',
            'promotion' => $employeePromotion,
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

        $employeeOptions = $employees->map(function (Employee $employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->nama_karyawan,
                'nik' => $employee->nik_karyawan,
                'company_name' => $employee->company?->company_name,
                'current_position_name' => $employee->employeePosition?->position?->position_name,
                'current_level_id' => $employee->employeePosition?->level_id,
                'current_level_name' => $employee->employeePosition?->level?->level_name,
                'current_department_id' => $employee->employeePosition?->position?->subDepartment?->department_id,
                'current_department_name' => $employee->employeePosition?->position?->subDepartment?->department?->department_name,
            ];
        })->values();

        $positionOptions = Position::with(['subDepartment.department.company'])
            ->orderBy('position_name')
            ->get()
            ->map(function (Position $position) {
                return [
                    'id' => $position->id,
                    'department_id' => $position->subDepartment?->department_id,
                    'label' => collect([
                        $position->position_name,
                        $position->subDepartment?->sub_department_name,
                        $position->subDepartment?->department?->department_name,
                        $position->subDepartment?->department?->company?->company_name,
                    ])->filter()->implode(' - '),
                ];
            })
            ->values();

        return array_merge([
            'employees' => $employees,
            'employeeOptions' => $employeeOptions,
            'positions' => $positionOptions,
            'positionLevels' => $this->orderedPositionLevels(),
            'nextPromotionNumber' => $this->nextPromotionNumber(),
            'promotionTypeOptions' => $this->promotionTypeOptions(),
            'temporaryActionOptions' => [
                'BECOME_PERMANENT' => 'Jadikan Pejabat Tetap',
                'EXTEND_TEMPORARY' => 'Perpanjang PJS',
                'REVERT_TO_PREVIOUS' => 'Kembalikan ke Jabatan Sebelumnya',
            ],
        ], $overrides);
    }

    private function validatePromotion(Request $request, ?EmployeePromotion $employeePromotion = null, bool $isUpdate = false): array
    {
        $rules = [
            'employee_id' => [$isUpdate ? 'nullable' : 'required', 'exists:employees,id'],
            'promotion_type' => [$isUpdate ? 'nullable' : 'required', Rule::in($this->promotionTypeOptions())],
            'new_position_id' => ['required', 'exists:positions,id'],
            'new_level_id' => ['required', 'exists:position_levels,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'promotion_form' => ['nullable', 'file', 'max:4096'],
            'appointment_letter' => ['nullable', 'file', 'max:4096'],
            'notes' => ['nullable', 'string'],
            'temporary_action' => ['nullable', Rule::in(['BECOME_PERMANENT', 'EXTEND_TEMPORARY', 'REVERT_TO_PREVIOUS'])],
        ];

        $data = $request->validate($rules);

        $promotionType = $employeePromotion?->promotion_type ?? ($data['promotion_type'] ?? null);
        $employeeId = $employeePromotion?->employee_id ?? $data['employee_id'];
        $employee = $this->resolveEmployeeForPromotion($employeeId);
        $currentPosition = $employee->employeePosition;

        if ($employee->status_karyawan !== 'active') {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee inactive tidak bisa diproses untuk promotion.',
            ]);
        }

        if ($promotionType === 'PEJABAT_SEMENTARA' && blank($data['end_date'] ?? null) && ($data['temporary_action'] ?? null) !== 'BECOME_PERMANENT') {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal selesai wajib diisi untuk Pejabat Sementara.',
            ]);
        }

        if ($promotionType === 'PEJABAT_TETAP' && ! $request->hasFile('appointment_letter') && ! $employeePromotion?->appointment_letter_path) {
            throw ValidationException::withMessages([
                'appointment_letter' => 'Upload SK Pengangkatan wajib untuk Pejabat Tetap.',
            ]);
        }

        if (! $currentPosition?->position_id || ! $currentPosition?->level_id) {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee belum memiliki data jabatan dan pangkat aktif.',
            ]);
        }

        $this->validatePromotionLevel($currentPosition->level, (int) $data['new_level_id']);

        $newPosition = Position::with('subDepartment.department')->findOrFail($data['new_position_id']);
        $employeeDepartmentId = $currentPosition->position?->subDepartment?->department_id;

        if ((string) $newPosition->subDepartment?->department_id !== (string) $employeeDepartmentId) {
            throw ValidationException::withMessages([
                'new_position_id' => 'Jabatan baru wajib berasal dari department yang sama dengan employee.',
            ]);
        }

        return $data;
    }

    private function updatePromotionDetails(Request $request, EmployeePromotion $employeePromotion)
    {
        $data = $request->validate([
            'new_position_id' => ['required', 'exists:positions,id'],
            'new_level_id' => ['required', 'exists:position_levels,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'promotion_form' => ['nullable', 'file', 'max:4096'],
        ]);

        $employee = $this->resolveEmployeeForPromotion($employeePromotion->employee_id);
        $currentPosition = $employee->employeePosition;
        $newPosition = Position::with('subDepartment.department')->findOrFail($data['new_position_id']);
        $employeeDepartmentId = $employee->employeePosition?->position?->subDepartment?->department_id;

        if ((string) $newPosition->subDepartment?->department_id !== (string) $employeeDepartmentId) {
            throw ValidationException::withMessages([
                'new_position_id' => 'Jabatan baru wajib berasal dari department yang sama dengan employee.',
            ]);
        }

        $this->validatePromotionLevel($employeePromotion->oldLevel, (int) $data['new_level_id']);

        if ($employeePromotion->promotion_type === 'PEJABAT_SEMENTARA' && blank($data['end_date'] ?? null)) {
            throw ValidationException::withMessages([
                'end_date' => 'Tanggal selesai wajib diisi untuk Pejabat Sementara.',
            ]);
        }

        $recordStatus = $this->determineRecordStatus($employeePromotion->promotion_type, $data['end_date'] ?? null);

        if ($recordStatus === 'ACTIVE') {
            $this->ensureNoOtherActivePromotion($employee->id, $employeePromotion->id);
        }

        $promotionData = [
            'new_position_id' => $data['new_position_id'],
            'new_level_id' => $data['new_level_id'],
            'start_date' => $data['start_date'],
            'end_date' => $employeePromotion->promotion_type === 'PEJABAT_SEMENTARA' ? ($data['end_date'] ?? null) : null,
            'record_status' => $recordStatus,
        ];

        if ($request->hasFile('promotion_form')) {
            UploadHelper::delete($employeePromotion->promotion_form_path);
            $promotionData['promotion_form_path'] = UploadHelper::store($request->file('promotion_form'), 'employee-promotions/forms');
        }

        DB::transaction(function () use ($employeePromotion, $promotionData, $currentPosition): void {
            $employeePromotion->update($promotionData);

            $this->applyEmployeePosition($currentPosition, $employeePromotion->new_position_id, $employeePromotion->new_level_id);
        });

        return redirect()->route('employee-promotions.show', $employeePromotion)->with('success', 'Promotion details updated successfully.');
    }

    private function resolveTemporaryPromotion(Request $request, EmployeePromotion $employeePromotion)
    {
        if ($employeePromotion->promotion_type !== 'PEJABAT_SEMENTARA') {
            throw ValidationException::withMessages([
                'temporary_action' => 'Aksi update hanya berlaku untuk promotion Pejabat Sementara.',
            ]);
        }

        $data = $request->validate([
            'temporary_action' => ['required', Rule::in(['BECOME_PERMANENT', 'EXTEND_TEMPORARY', 'REVERT_TO_PREVIOUS'])],
            'appointment_letter' => ['nullable', 'file', 'max:4096'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'notes' => ['nullable', 'string'],
        ]);

        $employee = $this->resolveEmployeeForPromotion($employeePromotion->employee_id);
        $currentPosition = $employee->employeePosition;

        if ($data['temporary_action'] === 'EXTEND_TEMPORARY' && (blank($data['start_date'] ?? null) || blank($data['end_date'] ?? null))) {
            throw ValidationException::withMessages([
                'end_date' => 'Periode awal dan akhir wajib diisi saat memperpanjang PJS.',
            ]);
        }

        $promotionData = [
            'notes' => $data['notes'] ?? $employeePromotion->notes,
        ];

        if ($data['temporary_action'] === 'BECOME_PERMANENT') {
            $this->ensureNoOtherActivePromotion($employee->id, $employeePromotion->id);
            $promotionData['promotion_type'] = 'PEJABAT_TETAP';
            $promotionData['record_status'] = 'CLOSE';
            $promotionData['end_date'] = null;
        }

        if ($data['temporary_action'] === 'REVERT_TO_PREVIOUS') {
            $promotionData['record_status'] = 'REVERTED';
        }

        if ($data['temporary_action'] === 'EXTEND_TEMPORARY') {
            $promotionData['start_date'] = $data['start_date'];
            $promotionData['end_date'] = $data['end_date'];
            $promotionData['record_status'] = $this->determineRecordStatus('PEJABAT_SEMENTARA', $data['end_date']);
        }

        if ($request->hasFile('appointment_letter')) {
            UploadHelper::delete($employeePromotion->appointment_letter_path);
            $promotionData['appointment_letter_path'] = UploadHelper::store($request->file('appointment_letter'), 'employee-promotions/appointment-letters');
        }

        $newPromotion = null;

        DB::transaction(function () use ($employeePromotion, $promotionData, $currentPosition, $data, &$newPromotion): void {
            if ($data['temporary_action'] === 'EXTEND_TEMPORARY') {
                $employeePromotion->update([
                    'record_status' => 'CLOSE',
                ]);

                $newPromotion = EmployeePromotion::create([
                    'employee_id' => $employeePromotion->employee_id,
                    'promotion_number' => $this->nextPromotionNumber(),
                    'promotion_type' => 'PEJABAT_SEMENTARA',
                    'old_position_id' => $employeePromotion->old_position_id,
                    'old_level_id' => $employeePromotion->old_level_id,
                    'new_position_id' => $employeePromotion->new_position_id,
                    'new_level_id' => $employeePromotion->new_level_id,
                    'start_date' => $promotionData['start_date'],
                    'end_date' => $promotionData['end_date'],
                    'promotion_form_path' => $employeePromotion->promotion_form_path,
                    'appointment_letter_path' => $employeePromotion->appointment_letter_path,
                    'notes' => $promotionData['notes'] ?? $employeePromotion->notes,
                    'record_status' => $promotionData['record_status'],
                ]);

                $this->applyEmployeePosition($currentPosition, $employeePromotion->new_position_id, $employeePromotion->new_level_id);

                return;
            }

            $employeePromotion->update($promotionData);

            if ($data['temporary_action'] === 'BECOME_PERMANENT') {
                $this->applyEmployeePosition($currentPosition, $employeePromotion->new_position_id, $employeePromotion->new_level_id);

                return;
            }

            if ($data['temporary_action'] === 'EXTEND_TEMPORARY') {
                $this->applyEmployeePosition($currentPosition, $employeePromotion->new_position_id, $employeePromotion->new_level_id);

                return;
            }

            if ($data['temporary_action'] === 'REVERT_TO_PREVIOUS') {
                $this->applyEmployeePosition($currentPosition, $employeePromotion->old_position_id, $employeePromotion->old_level_id);
            }
        });

        if ($newPromotion) {
            return redirect()->route('employee-promotions.show', $newPromotion)->with('success', 'PJS extended successfully. A new promotion ticket has been created and the previous ticket was closed.');
        }

        return redirect()->route('employee-promotions.show', $employeePromotion)->with('success', 'Temporary promotion updated successfully.');
    }

    private function resolveEmployeeForPromotion(int $employeeId): Employee
    {
        return Employee::with([
            'employeePosition.position.subDepartment.department.company',
            'employeePosition.level',
        ])
            ->findOrFail($employeeId);
    }

    private function promotionPayload(array $data, Employee $employee, EmployeePosition $currentPosition, string $recordStatus): array
    {
        return [
            'employee_id' => $employee->id,
            'promotion_number' => $this->nextPromotionNumber(),
            'promotion_type' => $data['promotion_type'],
            'old_position_id' => $currentPosition->position_id,
            'old_level_id' => $currentPosition->level_id,
            'new_position_id' => $data['new_position_id'],
            'new_level_id' => $data['new_level_id'],
            'start_date' => $data['start_date'],
            'end_date' => $data['promotion_type'] === 'PEJABAT_SEMENTARA' ? ($data['end_date'] ?? null) : null,
            'notes' => $data['notes'] ?? null,
            'record_status' => $recordStatus,
        ];
    }

    private function determineRecordStatus(string $promotionType, ?string $endDate): string
    {
        if ($promotionType === 'PEJABAT_SEMENTARA' && filled($endDate) && now()->toDateString() > $endDate) {
            return 'EXPIRED';
        }

        return 'ACTIVE';
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

    private function synchronizeExpiredTemporaryPromotions(): void
    {
        EmployeePromotion::query()
            ->where('promotion_type', 'PEJABAT_SEMENTARA')
            ->where('record_status', 'ACTIVE')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['record_status' => 'EXPIRED']);
    }

    private function ensureNoOtherActivePromotion(int $employeeId, ?int $ignorePromotionId = null): void
    {
        $query = EmployeePromotion::query()
            ->where('employee_id', $employeeId)
            ->where('record_status', 'ACTIVE');

        if ($ignorePromotionId) {
            $query->whereKeyNot($ignorePromotionId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => 'Employee ini masih memiliki promosi aktif. Selesaikan atau ubah record aktif sebelumnya terlebih dahulu.',
            ]);
        }
    }

    private function nextPromotionNumber(): string
    {
        return $this->nextRecordNumber(EmployeePromotion::class, 'promotion_number', 'PM');
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

    private function promotionTypeOptions(): array
    {
        return ['PEJABAT_SEMENTARA', 'PEJABAT_TETAP'];
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

    private function validatePromotionLevel(?PositionLevel $currentLevel, int $newLevelId): void
    {
        $newLevel = PositionLevel::findOrFail($newLevelId);
        $currentRank = $this->levelRank($currentLevel?->level_name);
        $newRank = $this->levelRank($newLevel->level_name);

        if ($currentRank === null || $newRank === null) {
            throw ValidationException::withMessages([
                'new_level_id' => 'Urutan pangkat belum terdaftar untuk pangkat saat ini atau pangkat baru.',
            ]);
        }

        if ($newRank <= $currentRank) {
            throw ValidationException::withMessages([
                'new_level_id' => 'Pangkat promosi harus lebih tinggi dari pangkat saat ini.',
            ]);
        }
    }
}
