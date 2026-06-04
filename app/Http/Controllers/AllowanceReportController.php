<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAdjustment;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceLog;
use App\Models\AttendanceShift;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AllowanceReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->reportData($filters);

        return view('pages.attendance.allowance-report.index', [
            ...$this->filterOptions(),
            ...$filters,
            'title' => 'Allowance Report',
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'isFiltered' => $report['is_filtered'],
        ]);
    }

    public function print(Request $request)
    {
        $filters = $this->filters($request);
        $report = $this->reportData($filters);

        abort_unless($report['is_filtered'], 422, 'Pilih periode tanggal terlebih dahulu.');

        return view('pages.attendance.allowance-report.print', [
            ...$this->filterOptions(),
            ...$filters,
            'title' => 'Export Allowance Report',
            'rows' => $report['rows'],
            'totals' => $report['totals'],
        ]);
    }

    private function reportData(array $filters): array
    {
        $isFiltered = $filters['dateFrom'] !== '' && $filters['dateTo'] !== '';
        $rows = collect();
        $totals = [
            'employees' => 0,
            'present_days' => 0,
            'late_days' => 0,
            'meal_allowance' => 0,
            'transport_allowance' => 0,
            'deduction' => 0,
            'total_allowance' => 0,
        ];

        $employees = Employee::query()
            ->with('employeePosition.position.subDepartment.department.company')
            ->where('status_karyawan', 'active')
            ->when($filters['companyIds'] !== [], fn ($query) => $query->whereIn('company_id', $filters['companyIds']))
            ->when($filters['departmentIds'] !== [], function ($query) use ($filters) {
                $query->whereHas('employeePosition.position.subDepartment', fn ($subQuery) => $subQuery->whereIn('department_id', $filters['departmentIds']));
            })
            ->when($filters['employeeIds'] !== [], fn ($query) => $query->whereIn('id', $filters['employeeIds']))
            ->get()
            ->sortBy([
                fn ($employee) => $employee->employeePosition?->position?->subDepartment?->department?->department_name ?? '',
                fn ($employee) => $employee->nama_karyawan ?? '',
            ])
            ->values();

        $totals['employees'] = $employees->count();

        if (! $isFiltered) {
            return ['rows' => $rows, 'totals' => $totals, 'is_filtered' => false];
        }

        $startDate = Carbon::parse($filters['dateFrom'])->startOfDay();
        $endDate = Carbon::parse($filters['dateTo'])->startOfDay();

        if ($startDate->greaterThan($endDate)) {
            return ['rows' => $rows, 'totals' => $totals, 'is_filtered' => true];
        }

        $employeeIds = $employees->pluck('id');
        $shift = AttendanceShift::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
        $holidays = AttendanceHoliday::query()
            ->whereDate('holiday_date', '>=', $startDate->toDateString())
            ->whereDate('holiday_date', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn (AttendanceHoliday $holiday) => $holiday->holiday_date->toDateString());

        $logs = AttendanceLog::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('scan_date', '>=', $startDate->toDateString())
            ->whereDate('scan_date', '<=', $endDate->toDateString())
            ->orderBy('scan_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (AttendanceLog $log) => $log->employee_id.'|'.$log->scan_date->toDateString());

        $adjustments = AttendanceAdjustment::query()
            ->whereIn('employee_id', $employeeIds)
            ->whereDate('attendance_date', '>=', $startDate->toDateString())
            ->whereDate('attendance_date', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn (AttendanceAdjustment $adjustment) => $adjustment->employee_id.'|'.$adjustment->attendance_date->toDateString());

        foreach ($employees as $employee) {
            $allowanceDays = 0;
            $lateDays = 0;
            $presentDays = 0;
            $excludedDays = 0;

            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                if ($date->isWeekend() || $holidays->has($date->toDateString())) {
                    continue;
                }

                $key = $employee->id.'|'.$date->toDateString();
                $log = $logs->get($key)?->first();
                $adjustment = $adjustments->get($key);
                $checkIn = $adjustment?->check_in_override ?? $log?->check_in;
                $checkOut = $adjustment?->check_out_override ?? $log?->check_out;
                $hasAttendance = $checkIn || $checkOut;

                if ($hasAttendance) {
                    $presentDays++;
                }

                if ($hasAttendance && ! $this->isExcludedException($adjustment?->exception_type)) {
                    $allowanceDays++;
                    $lateDays += $this->isLate($date, $checkIn, $shift) ? 1 : 0;
                } elseif ($adjustment?->exception_type) {
                    $excludedDays++;
                }
            }

            $normalAllowanceDays = $allowanceDays - $lateDays;
            $mealAllowance = $allowanceDays * $filters['mealRate'];
            $transportAllowance = $allowanceDays * $filters['transportRate'];
            $deduction = $lateDays * (($filters['mealRate'] + $filters['transportRate']) * 0.5);
            $totalAllowance = $mealAllowance + $transportAllowance - $deduction;

            $rows->push([
                'employee' => $employee,
                'present_days' => $presentDays,
                'allowance_days' => $allowanceDays,
                'late_days' => $lateDays,
                'excluded_days' => $excludedDays,
                'meal_allowance' => $mealAllowance,
                'transport_allowance' => $transportAllowance,
                'deduction' => $deduction,
                'total_allowance' => $totalAllowance,
            ]);

            $totals['present_days'] += $presentDays;
            $totals['late_days'] += $lateDays;
            $totals['meal_allowance'] += $mealAllowance;
            $totals['transport_allowance'] += $transportAllowance;
            $totals['deduction'] += $deduction;
            $totals['total_allowance'] += $totalAllowance;
        }

        return ['rows' => $rows, 'totals' => $totals, 'is_filtered' => true];
    }

    private function filters(Request $request): array
    {
        return [
            'dateFrom' => $request->string('date_from')->toString(),
            'dateTo' => $request->string('date_to')->toString(),
            'companyIds' => $this->selectedIds($request, 'company_ids', 'company_id'),
            'departmentIds' => $this->selectedIds($request, 'department_ids', 'department_id'),
            'employeeIds' => $this->selectedIds($request, 'employee_ids', 'employee_id'),
            'mealRate' => max(0, $request->integer('meal_rate')),
            'transportRate' => max(0, $request->integer('transport_rate')),
        ];
    }

    private function filterOptions(): array
    {
        return [
            'companies' => Company::query()->orderBy('company_name')->get(['id', 'company_name']),
            'departments' => Department::query()->orderBy('department_name')->get(['id', 'company_id', 'department_name']),
            'employees' => Employee::query()
                ->with('employeePosition.position.subDepartment.department')
                ->where('status_karyawan', 'active')
                ->orderBy('nama_karyawan')
                ->get(['id', 'company_id', 'nama_karyawan', 'attendance_id']),
        ];
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

    private function isExcludedException(?string $exceptionType): bool
    {
        return in_array($exceptionType, [
            'ALPHA',
            'IZIN',
            'SAKIT TANPA SURAT',
            'SAKIT DENGAN SURAT',
            'CUTI PRIBADI',
            'CUTI KHUSUS',
            'CUTI BERSAMA',
            'POTONG CUTI',
        ], true);
    }

    private function isLate(Carbon $date, ?string $checkIn, ?AttendanceShift $shift): bool
    {
        if (! $shift || ! $checkIn) {
            return false;
        }

        $scanIn = Carbon::parse($date->toDateString().' '.$checkIn);
        $shiftIn = Carbon::parse($date->toDateString().' '.$shift->check_in_time)
            ->addMinutes((int) $shift->late_tolerance_minutes);

        return $scanIn->greaterThan($shiftIn);
    }
}
