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

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $companyIds = $this->selectedIds($request, 'company_ids', 'company_id');
        $departmentIds = $this->selectedIds($request, 'department_ids', 'department_id');
        $employeeIds = $this->selectedIds($request, 'employee_ids', 'employee_id');
        $report = $this->reportData($dateFrom, $dateTo, $companyIds, $departmentIds, $employeeIds);

        $companies = Company::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $departments = Department::query()
            ->orderBy('department_name')
            ->get(['id', 'company_id', 'department_name']);

        $employees = Employee::query()
            ->with('employeePosition.position.subDepartment.department')
            ->where('status_karyawan', 'active')
            ->orderBy('nama_karyawan')
            ->get(['id', 'company_id', 'nama_karyawan', 'attendance_id']);

        return view('pages.attendance.report.index', [
            'title' => 'Attandance Report',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'companyIds' => $companyIds,
            'departmentIds' => $departmentIds,
            'employeeIds' => $employeeIds,
            'companies' => $companies,
            'departments' => $departments,
            'employees' => $employees,
            'rows' => $report['rows'],
            'totals' => $report['totals'],
            'isFiltered' => $report['is_filtered'],
            'exceptionTypes' => $this->exceptionTypes(),
        ]);
    }

    private function reportData(string $dateFrom, string $dateTo, array $companyIds, array $departmentIds, array $employeeIds): array
    {
        $isFiltered = $dateFrom !== '' && $dateTo !== '';
        $employees = Employee::query()
            ->with('employeePosition.position.subDepartment.department.company')
            ->where('status_karyawan', 'active')
            ->when($companyIds !== [], fn ($query) => $query->whereIn('company_id', $companyIds))
            ->when($departmentIds !== [], function ($query) use ($departmentIds) {
                $query->whereHas('employeePosition.position.subDepartment', fn ($subQuery) => $subQuery->whereIn('department_id', $departmentIds));
            })
            ->when($employeeIds !== [], fn ($query) => $query->whereIn('id', $employeeIds))
            ->get()
            ->sortBy([
                fn ($employee) => $employee->employeePosition?->position?->subDepartment?->department?->department_name ?? '',
                fn ($employee) => $employee->nama_karyawan ?? '',
            ])
            ->values();

        $rows = collect();
        $totals = $this->emptyTotals();

        if (! $isFiltered) {
            return ['rows' => $rows, 'totals' => $totals, 'is_filtered' => false];
        }

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->startOfDay();

        if ($startDate->greaterThan($endDate)) {
            return ['rows' => $rows, 'totals' => $totals, 'is_filtered' => true];
        }

        $shift = AttendanceShift::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
        $employeeIdList = $employees->pluck('id');

        $holidays = AttendanceHoliday::query()
            ->whereDate('holiday_date', '>=', $startDate->toDateString())
            ->whereDate('holiday_date', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn (AttendanceHoliday $holiday) => $holiday->holiday_date->toDateString());

        $logs = AttendanceLog::query()
            ->whereIn('employee_id', $employeeIdList)
            ->whereDate('scan_date', '>=', $startDate->toDateString())
            ->whereDate('scan_date', '<=', $endDate->toDateString())
            ->orderBy('scan_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (AttendanceLog $log) => $log->employee_id.'|'.$log->scan_date->toDateString());

        $adjustments = AttendanceAdjustment::query()
            ->whereIn('employee_id', $employeeIdList)
            ->whereDate('attendance_date', '>=', $startDate->toDateString())
            ->whereDate('attendance_date', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn (AttendanceAdjustment $adjustment) => $adjustment->employee_id.'|'.$adjustment->attendance_date->toDateString());

        foreach ($employees as $employee) {
            $row = $this->emptyTotals();
            $row['employee'] = $employee;

            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                if ($date->isWeekend() || $holidays->has($date->toDateString())) {
                    continue;
                }

                $key = $employee->id.'|'.$date->toDateString();
                $log = $logs->get($key)?->first();
                $adjustment = $adjustments->get($key);
                $checkIn = $adjustment?->check_in_override ?? $log?->check_in;
                $checkOut = $adjustment?->check_out_override ?? $log?->check_out;
                [$lateMinutes, $earlyLeaveMinutes] = $this->attendanceMinutes($date, $checkIn, $checkOut, $shift);

                $row['effective_days']++;
                $row['attended_days'] += ($checkIn || $checkOut) ? 1 : 0;
                $row['work_minutes'] += $this->workMinutes($checkIn, $checkOut);
                $row['late_minutes'] += $lateMinutes;
                $row['early_leave_minutes'] += $earlyLeaveMinutes;

                if ($adjustment?->exception_type && array_key_exists($adjustment->exception_type, $row['exceptions'])) {
                    $row['exceptions'][$adjustment->exception_type]++;
                }
            }

            $row['average_work_minutes'] = $row['effective_days'] > 0
                ? (int) round($row['work_minutes'] / $row['effective_days'])
                : 0;
            $row['attendance_percent'] = $row['effective_days'] > 0
                ? ($row['attended_days'] / $row['effective_days']) * 100
                : 0;

            $rows->push($row);
            $totals['effective_days'] += $row['effective_days'];
            $totals['attended_days'] += $row['attended_days'];
            $totals['work_minutes'] += $row['work_minutes'];
            $totals['late_minutes'] += $row['late_minutes'];
            $totals['early_leave_minutes'] += $row['early_leave_minutes'];

            foreach ($this->exceptionTypes() as $type => $label) {
                $totals['exceptions'][$type] += $row['exceptions'][$type];
            }
        }

        $totals['average_work_minutes'] = $totals['effective_days'] > 0
            ? (int) round($totals['work_minutes'] / $totals['effective_days'])
            : 0;
        $totals['attendance_percent'] = $totals['effective_days'] > 0
            ? ($totals['attended_days'] / $totals['effective_days']) * 100
            : 0;

        return ['rows' => $rows, 'totals' => $totals, 'is_filtered' => true];
    }

    private function emptyTotals(): array
    {
        return [
            'effective_days' => 0,
            'attended_days' => 0,
            'work_minutes' => 0,
            'average_work_minutes' => 0,
            'attendance_percent' => 0,
            'late_minutes' => 0,
            'early_leave_minutes' => 0,
            'exceptions' => array_fill_keys(array_keys($this->exceptionTypes()), 0),
        ];
    }

    private function attendanceMinutes(Carbon $date, ?string $checkIn, ?string $checkOut, ?AttendanceShift $shift): array
    {
        if (! $shift) {
            return [0, 0];
        }

        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;

        if ($checkIn) {
            $scanIn = Carbon::parse($date->toDateString().' '.$checkIn);
            $shiftIn = Carbon::parse($date->toDateString().' '.$shift->check_in_time)
                ->addMinutes((int) $shift->late_tolerance_minutes);

            if ($scanIn->greaterThan($shiftIn)) {
                $lateMinutes = (int) $shiftIn->diffInMinutes($scanIn);
            }
        }

        if ($checkOut) {
            $scanOut = Carbon::parse($date->toDateString().' '.$checkOut);
            $shiftOut = Carbon::parse($date->toDateString().' '.$shift->check_out_time)
                ->subMinutes((int) $shift->early_leave_tolerance_minutes);

            if ($scanOut->lessThan($shiftOut)) {
                $earlyLeaveMinutes = (int) $scanOut->diffInMinutes($shiftOut);
            }
        }

        return [$lateMinutes, $earlyLeaveMinutes];
    }

    private function workMinutes(?string $checkIn, ?string $checkOut): int
    {
        if (! $checkIn || ! $checkOut) {
            return 0;
        }

        $start = Carbon::parse($checkIn);
        $end = Carbon::parse($checkOut);

        return $end->greaterThan($start) ? (int) $start->diffInMinutes($end) : 0;
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

    private function exceptionTypes(): array
    {
        return [
            'ALPHA' => 'Alpha',
            'IZIN' => 'Izin',
            'SAKIT TANPA SURAT' => 'Sakit Tanpa Surat',
            'SAKIT DENGAN SURAT' => 'Sakit Dengan Surat',
            'CUTI PRIBADI' => 'Cuti Pribadi',
            'CUTI KHUSUS' => 'Cuti Khusus',
            'CUTI BERSAMA' => 'Cuti Bersama',
            'POTONG CUTI' => 'Potong Cuti',
        ];
    }
}
