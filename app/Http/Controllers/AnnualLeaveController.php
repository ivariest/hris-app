<?php

namespace App\Http\Controllers;

use App\Models\AnnualLeaveCollective;
use App\Models\AttendanceAdjustment;
use App\Models\AnnualLeaveTransaction;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AnnualLeaveController extends Controller
{
    private const ENTITLEMENT_DAYS = 12;

    public function index(Request $request)
    {
        $year = (int) ($request->integer('year') ?: now()->year);
        $companyIds = $this->selectedIds($request, 'company_ids', 'company_id');
        $departmentIds = $this->selectedIds($request, 'department_ids', 'department_id');
        $employeeIds = $this->selectedIds($request, 'employee_ids', 'employee_id');
        $employees = Employee::query()
            ->with('employeePosition.position.subDepartment.department', 'contract')
            ->where('status_karyawan', 'active')
            ->when($companyIds !== [], fn ($query) => $query->whereIn('company_id', $companyIds))
            ->when($departmentIds !== [], function ($query) use ($departmentIds) {
                $query->whereHas('employeePosition.position.subDepartment', fn ($subQuery) => $subQuery->whereIn('department_id', $departmentIds));
            })
            ->when($employeeIds !== [], fn ($query) => $query->whereIn('id', $employeeIds))
            ->orderBy('nama_karyawan')
            ->get();

        $transactions = AnnualLeaveTransaction::query()
            ->with('employee')
            ->where('year', $year)
            ->latest('date_from')
            ->latest('id')
            ->get();

        $usedByEmployee = $transactions
            ->where('balance_effect', 'annual_leave')
            ->groupBy('employee_id')
            ->map(fn ($items) => (float) $items->sum('days'));
        $debtByEmployee = $transactions
            ->where('balance_effect', 'pre_eligible_debt')
            ->filter(fn (AnnualLeaveTransaction $transaction) => $this->isActiveDebt($transaction))
            ->groupBy('employee_id')
            ->map(fn ($items) => (float) $items->sum('days'));

        $summaryRows = $employees->map(function (Employee $employee) use ($usedByEmployee, $debtByEmployee) {
            $eligible = $this->isEligible($employee, now());
            $entitlement = $eligible ? self::ENTITLEMENT_DAYS : 0;
            $used = (float) ($usedByEmployee[$employee->id] ?? 0);
            $debt = (float) ($debtByEmployee[$employee->id] ?? 0);

            return [
                'employee' => $employee,
                'eligible' => $eligible,
                'entitlement' => $entitlement,
                'used' => $used,
                'balance' => $entitlement - $used,
                'debt' => $debt,
            ];
        });

        return view('pages.attendance.annual-leave.index', [
            'title' => 'Annual Leave',
            'year' => $year,
            'companyIds' => $companyIds,
            'departmentIds' => $departmentIds,
            'employeeIds' => $employeeIds,
            'companies' => Company::query()->orderBy('company_name')->get(['id', 'company_name']),
            'departments' => Department::query()->orderBy('department_name')->get(['id', 'company_id', 'department_name']),
            'employeeOptions' => Employee::query()
                ->with('employeePosition.position.subDepartment.department')
                ->where('status_karyawan', 'active')
                ->orderBy('nama_karyawan')
                ->get(['id', 'company_id', 'nama_karyawan', 'attendance_id']),
            'employees' => $employees,
            'summaryRows' => $summaryRows,
            'transactions' => $transactions,
            'collectives' => AnnualLeaveCollective::query()->where('year', $year)->latest('date_from')->get(),
            'leaveTypes' => $this->leaveTypes(),
            'totalEntitlement' => $summaryRows->sum('entitlement'),
            'totalUsed' => $summaryRows->sum('used'),
            'totalBalance' => $summaryRows->sum('balance'),
            'totalDebt' => $summaryRows->sum('debt'),
            'eligibleCount' => $summaryRows->where('eligible', true)->count(),
        ]);
    }

    public function show(Request $request, Employee $employee)
    {
        $year = (int) ($request->integer('year') ?: now()->year);
        $employee->load('company', 'employeePosition.level', 'employeePosition.position.subDepartment.department', 'contract');
        $transactions = AnnualLeaveTransaction::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->latest('date_from')
            ->latest('id')
            ->get();
        $eligible = $this->isEligible($employee, now());
        $eligibleToday = $eligible;
        $entitlement = $eligible ? self::ENTITLEMENT_DAYS : 0;
        $used = (float) $transactions->where('balance_effect', 'annual_leave')->sum('days');
        $debt = (float) $transactions
            ->where('balance_effect', 'pre_eligible_debt')
            ->filter(fn (AnnualLeaveTransaction $transaction) => $this->isActiveDebt($transaction))
            ->sum('days');

        return view('pages.attendance.annual-leave.show', [
            'title' => 'Annual Leave Detail',
            'year' => $year,
            'employee' => $employee,
            'transactions' => $transactions,
            'eligible' => $eligible,
            'eligibleToday' => $eligibleToday,
            'entitlement' => $entitlement,
            'used' => $used,
            'balance' => $entitlement - $used,
            'debt' => $debt,
            'leaveTypes' => $this->leaveTypes(),
        ]);
    }

    public function createCollective(Request $request)
    {
        return view('pages.attendance.annual-leave.collective-create', [
            'title' => 'Create Cuti Bersama',
            'year' => (int) ($request->integer('year') ?: now()->year),
            'collective' => null,
        ]);
    }

    public function collectives(Request $request)
    {
        $year = (int) ($request->integer('year') ?: now()->year);

        return view('pages.attendance.annual-leave.collectives-index', [
            'title' => 'Cuti Bersama',
            'year' => $year,
            'yearOptions' => range(now()->year - 2, now()->year + 1),
            'collectives' => AnnualLeaveCollective::query()
                ->where('year', $year)
                ->latest('date_from')
                ->get(),
        ]);
    }

    public function editCollective(AnnualLeaveCollective $collective)
    {
        return view('pages.attendance.annual-leave.collective-create', [
            'title' => 'Edit Cuti Bersama',
            'year' => $collective->year,
            'collective' => $collective,
        ]);
    }

    public function storeLeave(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'employee_id' => ['required', 'exists:employees,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'leave_type' => ['required', Rule::in(array_keys($this->leaveTypes()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee = Employee::with('contract')->findOrFail($data['employee_id']);
        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);
        $days = $this->workdayCount($dateFrom, $dateTo);
        $balanceEffect = $this->balanceEffectForLeaveType($data['leave_type']);

        if ($balanceEffect === 'annual_leave') {
            if (! $this->isEligible($employee, $dateFrom)) {
                return back()->withInput()->with('error', 'Karyawan belum memenuhi masa kerja 1 tahun untuk menggunakan annual leave.');
            }

            $availableBalance = $this->availableAnnualLeaveBalance($employee, (int) $data['year']);

            if ($days > $availableBalance) {
                return back()
                    ->withInput()
                    ->with('error', 'Pengajuan cuti ditolak. Total hari cuti melebihi sisa saldo annual leave.');
            }
        }

        $transaction = AnnualLeaveTransaction::create([
            'employee_id' => $employee->id,
            'year' => $data['year'],
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'days' => $days,
            'leave_type' => $data['leave_type'],
            'source_type' => 'manual',
            'balance_effect' => $balanceEffect,
            'written_off_at' => null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
        $this->syncAttendanceAdjustments($transaction);

        return redirect()
            ->route('annual-leaves.index', ['year' => $data['year']])
            ->with('success', 'Annual leave berhasil diinput.');
    }

    public function storeEmployeeLeave(Request $request, Employee $employee)
    {
        $request->merge(['employee_id' => $employee->id]);
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'employee_id' => ['required', 'exists:employees,id'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'leave_type' => ['required', Rule::in(array_keys($this->leaveTypes()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $employee->load('contract');
        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);
        $days = $this->workdayCount($dateFrom, $dateTo);
        $balanceEffect = $this->balanceEffectForLeaveType($data['leave_type']);

        if ($balanceEffect === 'annual_leave') {
            if (! $this->isEligible($employee, $dateFrom)) {
                return back()->withInput()->with('error', 'Karyawan belum memenuhi masa kerja 1 tahun untuk menggunakan annual leave.');
            }

            $availableBalance = $this->availableAnnualLeaveBalance($employee, (int) $data['year']);

            if ($days > $availableBalance) {
                return back()
                    ->withInput()
                    ->with('error', 'Pengajuan cuti ditolak. Total hari cuti melebihi sisa saldo annual leave.');
            }
        }

        $transaction = AnnualLeaveTransaction::create([
            'employee_id' => $employee->id,
            'year' => $data['year'],
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'days' => $days,
            'leave_type' => $data['leave_type'],
            'source_type' => 'manual',
            'balance_effect' => $balanceEffect,
            'written_off_at' => null,
            'notes' => $data['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);
        $this->syncAttendanceAdjustments($transaction);

        return redirect()
            ->route('annual-leaves.show', [$employee, 'year' => $data['year']])
            ->with('success', 'Annual leave berhasil diinput.');
    }

    public function editTransaction(AnnualLeaveTransaction $transaction)
    {
        abort_if($transaction->source_type === 'collective', 403);

        $transaction->load('employee.contract', 'employee.employeePosition.position.subDepartment.department');

        return view('pages.attendance.annual-leave.transaction-edit', [
            'title' => 'Edit Cuti',
            'transaction' => $transaction,
            'employee' => $transaction->employee,
            'leaveTypes' => $this->leaveTypes(),
            'availableBalance' => $this->availableAnnualLeaveBalance($transaction->employee, (int) $transaction->year, $transaction->id),
        ]);
    }

    public function updateTransaction(Request $request, AnnualLeaveTransaction $transaction)
    {
        abort_if($transaction->source_type === 'collective', 403);

        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'leave_type' => ['required', Rule::in(array_keys($this->leaveTypes()))],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $transaction->load('employee.contract');
        $previousDateFrom = $transaction->date_from->copy();
        $previousDateTo = $transaction->date_to->copy();
        $previousLeaveType = $transaction->leave_type;
        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);
        $days = $this->workdayCount($dateFrom, $dateTo);
        $balanceEffect = $this->balanceEffectForLeaveType($data['leave_type']);

        if ($balanceEffect === 'annual_leave') {
            if (! $this->isEligible($transaction->employee, $dateFrom)) {
                return back()->withInput()->with('error', 'Karyawan belum memenuhi masa kerja 1 tahun untuk menggunakan annual leave.');
            }

            $availableBalance = $this->availableAnnualLeaveBalance($transaction->employee, (int) $data['year'], $transaction->id);

            if ($days > $availableBalance) {
                return back()
                    ->withInput()
                    ->with('error', 'Pengajuan cuti ditolak. Total hari cuti melebihi sisa saldo annual leave.');
            }
        }

        $transaction->update([
            'year' => $data['year'],
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
            'days' => $days,
            'leave_type' => $data['leave_type'],
            'balance_effect' => $balanceEffect,
            'written_off_at' => null,
            'notes' => $data['notes'] ?? null,
        ]);
        $this->syncAttendanceAdjustments($transaction, $previousDateFrom, $previousDateTo, $previousLeaveType);

        return redirect()
            ->route('annual-leaves.show', [$transaction->employee, 'year' => $data['year']])
            ->with('success', 'Riwayat cuti berhasil diupdate.');
    }

    public function storeCollective(Request $request)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'name' => ['required', 'string', 'max:150'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);
        $days = $this->workdayCount($dateFrom, $dateTo);

        DB::transaction(function () use ($data, $dateFrom, $dateTo, $days) {
            $collective = AnnualLeaveCollective::create([
                'year' => $data['year'],
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'name' => $data['name'],
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            Employee::query()
                ->where('status_karyawan', 'active')
                ->get()
                ->each(function (Employee $employee) use ($collective, $data, $dateFrom, $dateTo, $days) {
                    $eligible = $this->isEligible($employee, $dateFrom);
                    AnnualLeaveTransaction::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'annual_leave_collective_id' => $collective->id,
                        ],
                        [
                            'year' => $data['year'],
                            'date_from' => $dateFrom->toDateString(),
                            'date_to' => $dateTo->toDateString(),
                            'days' => $days,
                            'leave_type' => 'CUTI BERSAMA',
                            'source_type' => 'collective',
                            'balance_effect' => $eligible ? 'annual_leave' : 'pre_eligible_debt',
                            'written_off_at' => $eligible ? null : $employee->contract?->start_date?->copy()->addYear(),
                            'notes' => $collective->name,
                            'created_by' => auth()->id(),
                        ]
                    );
                });
        });

        return redirect()
            ->route('annual-leaves.index', ['year' => $data['year']])
            ->with('success', 'Cuti bersama berhasil diterapkan ke semua karyawan aktif.');
    }

    public function updateCollective(Request $request, AnnualLeaveCollective $collective)
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'min:2000', 'max:2100'],
            'name' => ['required', 'string', 'max:150'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $dateFrom = Carbon::parse($data['date_from']);
        $dateTo = Carbon::parse($data['date_to']);
        $days = $this->workdayCount($dateFrom, $dateTo);

        DB::transaction(function () use ($collective, $data, $dateFrom, $dateTo, $days) {
            $collective->update([
                'year' => $data['year'],
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
                'name' => $data['name'],
                'notes' => $data['notes'] ?? null,
            ]);

            Employee::query()
                ->where('status_karyawan', 'active')
                ->get()
                ->each(function (Employee $employee) use ($collective, $data, $dateFrom, $dateTo, $days) {
                    $eligible = $this->isEligible($employee, $dateFrom);
                    AnnualLeaveTransaction::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'annual_leave_collective_id' => $collective->id,
                        ],
                        [
                            'year' => $data['year'],
                            'date_from' => $dateFrom->toDateString(),
                            'date_to' => $dateTo->toDateString(),
                            'days' => $days,
                            'leave_type' => 'CUTI BERSAMA',
                            'source_type' => 'collective',
                            'balance_effect' => $eligible ? 'annual_leave' : 'pre_eligible_debt',
                            'written_off_at' => $eligible ? null : $employee->contract?->start_date?->copy()->addYear(),
                            'notes' => $collective->name,
                            'created_by' => auth()->id(),
                        ]
                    );
                });
        });

        return redirect()
            ->route('annual-leaves.index', ['year' => $data['year']])
            ->with('success', 'Cuti bersama berhasil diupdate.');
    }

    private function isEligible(Employee $employee, Carbon $date): bool
    {
        $startDate = $employee->contract?->start_date;

        return $startDate !== null && $startDate->copy()->addYear()->lessThanOrEqualTo($date);
    }

    private function isActiveDebt(AnnualLeaveTransaction $transaction): bool
    {
        return $transaction->written_off_at === null || $transaction->written_off_at->greaterThan(now());
    }

    private function availableAnnualLeaveBalance(Employee $employee, int $year, ?int $excludeTransactionId = null): float
    {
        $employee->loadMissing('contract');

        if (! $this->isEligible($employee, now())) {
            return 0;
        }

        $used = AnnualLeaveTransaction::query()
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('balance_effect', 'annual_leave')
            ->when($excludeTransactionId, fn ($query) => $query->whereKeyNot($excludeTransactionId))
            ->sum('days');

        return max(0, self::ENTITLEMENT_DAYS - (float) $used);
    }

    private function workdayCount(Carbon $dateFrom, Carbon $dateTo): int
    {
        return collect(CarbonPeriod::create($dateFrom, $dateTo))
            ->reject(fn (Carbon $date) => $date->isWeekend())
            ->count();
    }

    private function leaveTypes(): array
    {
        return [
            'CUTI PRIBADI' => 'Cuti Pribadi',
            'CUTI KHUSUS' => 'Cuti Khusus',
            'POTONG CUTI' => 'Potong Cuti',
        ];
    }

    private function balanceEffectForLeaveType(string $leaveType): string
    {
        return $leaveType === 'CUTI KHUSUS' ? 'no_balance' : 'annual_leave';
    }

    private function syncAttendanceAdjustments(
        AnnualLeaveTransaction $transaction,
        ?Carbon $previousDateFrom = null,
        ?Carbon $previousDateTo = null,
        ?string $previousLeaveType = null
    ): void
    {
        $currentDates = $this->workdayDateStrings($transaction->date_from, $transaction->date_to);

        if ($previousDateFrom && $previousDateTo && $previousLeaveType) {
            $previousDates = $this->workdayDateStrings($previousDateFrom, $previousDateTo);
            $removedDates = array_values(array_diff($previousDates, $currentDates));

            if ($removedDates !== []) {
                AttendanceAdjustment::query()
                    ->where('employee_id', $transaction->employee_id)
                    ->whereIn('attendance_date', $removedDates)
                    ->where('exception_type', $previousLeaveType)
                    ->delete();
            }
        }

        foreach ($currentDates as $date) {
            AttendanceAdjustment::updateOrCreate(
                [
                    'employee_id' => $transaction->employee_id,
                    'attendance_date' => $date,
                ],
                [
                    'exception_type' => $transaction->leave_type,
                    'exception_note' => $transaction->notes,
                    'check_in_override' => null,
                    'check_out_override' => null,
                    'created_by' => $transaction->created_by ?? auth()->id(),
                    'updated_by' => auth()->id(),
                ]
            );
        }
    }

    /**
     * @return array<int, string>
     */
    private function workdayDateStrings(Carbon $dateFrom, Carbon $dateTo): array
    {
        return collect(CarbonPeriod::create($dateFrom, $dateTo))
            ->reject(fn (Carbon $date) => $date->isWeekend())
            ->map(fn (Carbon $date) => $date->toDateString())
            ->values()
            ->all();
    }

    private function selectedIds(Request $request, string $multiKey, string $singleKey): array
    {
        $ids = collect((array) $request->input($multiKey, []));

        if ($request->filled($singleKey)) {
            $ids->push($request->integer($singleKey));
        }

        return $ids
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
