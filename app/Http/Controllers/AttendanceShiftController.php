<?php

namespace App\Http\Controllers;

use App\Models\AttendanceShift;
use App\Models\Employee;
use App\Models\EmployeeAttendanceShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AttendanceShiftController extends Controller
{
    public function index()
    {
        return view('pages.attendance.shifts.index', [
            'title' => 'Shift Setting',
            'shifts' => AttendanceShift::latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('pages.attendance.shifts.create', [
            'title' => 'Create Shift',
            'shift' => null,
            'employees' => Employee::where('status_karyawan', 'active')->orderBy('nama_karyawan')->get(),
            'assignedEmployeeIds' => [],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateShift($request);
        $employeeIds = $data['employee_ids'] ?? [];
        unset($data['employee_ids']);

        DB::transaction(function () use ($data, $employeeIds): void {
            if (! empty($data['is_default'])) {
                AttendanceShift::query()->update(['is_default' => false]);
            }

            $shift = AttendanceShift::create($data);
            $this->syncEmployees($shift, $employeeIds);
        });

        return redirect()->route('attendance-shifts.index')->with('success', 'Shift created successfully.');
    }

    public function edit(AttendanceShift $attendanceShift)
    {
        return view('pages.attendance.shifts.edit', [
            'title' => 'Edit Shift',
            'shift' => $attendanceShift,
            'employees' => Employee::where('status_karyawan', 'active')->orderBy('nama_karyawan')->get(),
            'assignedEmployeeIds' => $attendanceShift->employeeAssignments()->pluck('employee_id')->map(fn ($id) => (string) $id)->all(),
        ]);
    }

    public function update(Request $request, AttendanceShift $attendanceShift)
    {
        $data = $this->validateShift($request);
        $employeeIds = $data['employee_ids'] ?? [];
        unset($data['employee_ids']);

        DB::transaction(function () use ($attendanceShift, $data, $employeeIds): void {
            if (! empty($data['is_default'])) {
                AttendanceShift::whereKeyNot($attendanceShift->id)->update(['is_default' => false]);
            }

            $attendanceShift->update($data);
            $this->syncEmployees($attendanceShift, $employeeIds);
        });

        return redirect()->route('attendance-shifts.index')->with('success', 'Shift updated successfully.');
    }

    public function destroy(AttendanceShift $attendanceShift)
    {
        $attendanceShift->delete();

        return redirect()->route('attendance-shifts.index')->with('success', 'Shift deleted successfully.');
    }

    private function validateShift(Request $request): array
    {
        return $request->validate([
            'shift_name' => ['required', 'string', 'max:100'],
            'check_in_time' => ['required', 'date_format:H:i'],
            'check_out_time' => ['required', 'date_format:H:i'],
            'late_tolerance_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'early_leave_tolerance_minutes' => ['nullable', 'integer', 'min:0', 'max:240'],
            'is_default' => ['nullable', 'boolean'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
        ]);
    }

    private function syncEmployees(AttendanceShift $shift, array $employeeIds): void
    {
        $employeeIds = array_map('intval', $employeeIds);

        $shift->employeeAssignments()
            ->when(! empty($employeeIds), fn ($query) => $query->whereNotIn('employee_id', $employeeIds))
            ->delete();

        foreach ($employeeIds as $employeeId) {
            EmployeeAttendanceShift::updateOrCreate(
                ['employee_id' => $employeeId],
                ['attendance_shift_id' => $shift->id]
            );
        }
    }
}
