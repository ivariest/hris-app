<?php

namespace App\Http\Controllers;

use App\Models\AttendanceHoliday;
use App\Models\AttendanceAdjustment;
use App\Models\AttendanceLog;
use App\Models\AttendanceShift;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class AttendanceRecapController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $companyIds = $this->selectedIds($request, 'company_ids', 'company_id');
        $departmentIds = $this->selectedIds($request, 'department_ids', 'department_id');
        $employeeIds = $this->selectedEmployeeIds($request);
        $recap = $this->recapData($dateFrom, $dateTo, $companyIds, $departmentIds, $employeeIds);

        $employeeOptions = Employee::query()
            ->with('employeePosition.position.subDepartment.department')
            ->where('status_karyawan', 'active')
            ->orderBy('nama_karyawan')
            ->get(['id', 'company_id', 'nama_karyawan', 'attendance_id']);

        $companies = Company::query()
            ->orderBy('company_name')
            ->get(['id', 'company_name']);

        $departments = Department::query()
            ->orderBy('department_name')
            ->get(['id', 'company_id', 'department_name']);

        return view('pages.attendance.recap.index', [
            'title' => 'Attendance Recap',
            'rows' => $recap['rows'],
            'selectedEmployees' => $recap['employees'],
            'summary' => $recap['summary'],
            'employees' => $employeeOptions,
            'companies' => $companies,
            'departments' => $departments,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'companyIds' => $companyIds,
            'departmentIds' => $departmentIds,
            'employeeIds' => $employeeIds,
            'isFiltered' => $recap['is_filtered'],
            'exceptionTypes' => $this->exceptionTypes(),
        ]);
    }

    public function print(Request $request)
    {
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $companyIds = $this->selectedIds($request, 'company_ids', 'company_id');
        $departmentIds = $this->selectedIds($request, 'department_ids', 'department_id');
        $employeeIds = $this->selectedEmployeeIds($request);
        $recap = $this->recapData($dateFrom, $dateTo, $companyIds, $departmentIds, $employeeIds);

        abort_unless($recap['is_filtered'], 422, 'Pilih periode tanggal terlebih dahulu.');

        $selectedDepartments = Department::query()
            ->whereIn('id', $departmentIds)
            ->orderBy('department_name')
            ->get(['id', 'department_name']);

        return view('pages.attendance.recap.print', [
            'title' => 'Print Attendance Recap',
            'rows' => $recap['rows'],
            'groupedRows' => $recap['rows']->groupBy(fn (array $row) => $row['employee']->id),
            'employees' => $recap['employees'],
            'summary' => $recap['summary'],
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'selectedDepartmentNames' => $selectedDepartments->pluck('department_name')->values(),
            'selectedCompanyIds' => $companyIds,
            'selectedDepartmentIds' => $departmentIds,
            'selectedEmployeeIds' => $employeeIds,
        ]);
    }

    /**
     * @return array{rows:\Illuminate\Support\Collection,employees:\Illuminate\Support\Collection,summary:array<string,int>,is_filtered:bool}
     */
    private function recapData(string $dateFrom, string $dateTo, array $companyIds, array $departmentIds, array $employeeIds): array
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
                fn ($employee) => $employee->employeePosition?->position?->subDepartment?->sub_department_name ?? '',
                fn ($employee) => $employee->nama_karyawan ?? '',
            ])
            ->values();

        $rows = collect();
        $summary = [
            'employees' => $employees->count(),
            'present' => 0,
            'late' => 0,
            'early_leave' => 0,
            'late_early_leave' => 0,
            'leave' => 0,
            'absent' => 0,
        ];

        if (! $isFiltered) {
            return [
                'rows' => $rows,
                'employees' => $employees,
                'summary' => $summary,
                'is_filtered' => false,
            ];
        }

        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->startOfDay();

        if ($startDate->greaterThan($endDate)) {
            return [
                'rows' => $rows,
                'employees' => $employees,
                'summary' => $summary,
                'is_filtered' => true,
            ];
        }

        $shift = AttendanceShift::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();

        $filteredEmployeeIds = $employees->pluck('id');
        $holidays = AttendanceHoliday::query()
            ->whereDate('holiday_date', '>=', $startDate->toDateString())
            ->whereDate('holiday_date', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn (AttendanceHoliday $holiday) => $holiday->holiday_date->toDateString());

        $logs = AttendanceLog::query()
            ->whereIn('employee_id', $filteredEmployeeIds)
            ->whereDate('scan_date', '>=', $startDate->toDateString())
            ->whereDate('scan_date', '<=', $endDate->toDateString())
            ->orderBy('scan_date')
            ->orderByDesc('id')
            ->get()
            ->groupBy(fn (AttendanceLog $log) => $log->employee_id.'|'.$log->scan_date->toDateString());

        $adjustments = AttendanceAdjustment::query()
            ->whereIn('employee_id', $filteredEmployeeIds)
            ->whereDate('attendance_date', '>=', $startDate->toDateString())
            ->whereDate('attendance_date', '<=', $endDate->toDateString())
            ->get()
            ->keyBy(fn (AttendanceAdjustment $adjustment) => $adjustment->employee_id.'|'.$adjustment->attendance_date->toDateString());

        foreach ($employees as $employee) {
            foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
                $log = $logs->get($employee->id.'|'.$date->toDateString())?->first();
                $adjustment = $adjustments->get($employee->id.'|'.$date->toDateString());
                $holiday = $holidays->get($date->toDateString());
                $checkIn = $adjustment?->check_in_override ?? $log?->check_in;
                $checkOut = $adjustment?->check_out_override ?? $log?->check_out;
                [$lateMinutes, $earlyLeaveMinutes] = $this->attendanceMinutes($date, $checkIn, $checkOut, $shift);
                $isDayOff = $date->isWeekend() || $holiday !== null;
                $status = $this->rowStatus($adjustment, $checkIn, $checkOut, $lateMinutes, $earlyLeaveMinutes, $isDayOff);

                if (! $isDayOff) {
                    $summary[$status] = ($summary[$status] ?? 0) + 1;
                }

                $rows->push([
                    'employee' => $employee,
                    'date' => $date->copy(),
                    'log' => $log,
                    'adjustment' => $adjustment,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'status' => $status,
                    'is_weekend' => $date->isWeekend(),
                    'is_holiday' => $holiday !== null,
                    'holiday' => $holiday,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'exception' => '',
                ]);
            }
        }

        return [
            'rows' => $rows,
            'employees' => $employees,
            'summary' => $summary,
            'is_filtered' => true,
        ];
    }

    /**
     * @return array<int, int>
     */
    private function selectedEmployeeIds(Request $request): array
    {
        return $this->selectedIds($request, 'employee_ids', 'employee_id');
    }

    /**
     * @return array<int, int>
     */
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

    /**
     * @return array{0:int,1:int}
     */
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

    private function rowStatus(?AttendanceAdjustment $adjustment, ?string $checkIn, ?string $checkOut, int $lateMinutes, int $earlyLeaveMinutes, bool $isDayOff): string
    {
        if ($isDayOff) {
            return 'off';
        }

        if ($adjustment?->exception_type && in_array($adjustment->exception_type, ['CUTI PRIBADI', 'CUTI KHUSUS', 'CUTI BERSAMA', 'POTONG CUTI'], true)) {
            return 'leave';
        }

        if (! $checkIn && ! $checkOut) {
            return 'absent';
        }

        if ($lateMinutes > 0 && $earlyLeaveMinutes > 0) {
            return 'late_early_leave';
        }

        if ($lateMinutes > 0) {
            return 'late';
        }

        if ($earlyLeaveMinutes > 0) {
            return 'early_leave';
        }

        return 'present';
    }

    /**
     * @return array<int, string>
     */
    private function exceptionTypes(): array
    {
        return [
            'ALPHA',
            'IZIN',
            'SAKIT TANPA SURAT',
            'SAKIT DENGAN SURAT',
            'CUTI PRIBADI',
            'CUTI KHUSUS',
            'CUTI BERSAMA',
            'POTONG CUTI',
            'OFF',
        ];
    }
}
