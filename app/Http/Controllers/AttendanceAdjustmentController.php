<?php

namespace App\Http\Controllers;

use App\Models\AttendanceAdjustment;
use App\Models\AnnualLeaveTransaction;
use App\Models\Employee;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class AttendanceAdjustmentController extends Controller
{
    public function store(Request $request)
    {
        $action = $request->string('action')->toString();

        $rules = [
            'employee_id' => ['required', 'exists:employees,id'],
            'attendance_date' => ['required', 'date'],
            'action' => ['required', Rule::in(['exception', 'scan'])],
            'redirect_date_from' => ['nullable', 'date'],
            'redirect_date_to' => ['nullable', 'date'],
            'redirect_company_ids' => ['nullable', 'array'],
            'redirect_company_ids.*' => ['integer'],
            'redirect_company_id' => ['nullable', 'integer'],
            'redirect_department_ids' => ['nullable', 'array'],
            'redirect_department_ids.*' => ['integer'],
            'redirect_employee_id' => ['nullable', 'integer'],
            'redirect_employee_ids' => ['nullable', 'array'],
            'redirect_employee_ids.*' => ['integer'],
            'redirect_department_id' => ['nullable', 'integer'],
        ];

        if ($action === 'exception') {
            $rules['exception_type'] = ['required', Rule::in($this->exceptionTypes())];
            $rules['exception_note'] = ['nullable', 'string', 'max:1000'];
        }

        if ($action === 'scan') {
            $rules['check_in_override'] = ['nullable', 'date_format:H:i'];
            $rules['check_out_override'] = ['nullable', 'date_format:H:i'];
        }

        $data = $request->validate($rules);

        $adjustment = AttendanceAdjustment::firstOrNew([
            'employee_id' => $data['employee_id'],
            'attendance_date' => $data['attendance_date'],
        ]);

        if (! $adjustment->exists) {
            $adjustment->created_by = auth()->id();
        }

        $adjustment->updated_by = auth()->id();

        if ($action === 'exception') {
            if (in_array($data['exception_type'], ['CUTI PRIBADI', 'POTONG CUTI'], true)) {
                $employee = Employee::with('contract')->findOrFail($data['employee_id']);
                $attendanceDate = Carbon::parse($data['attendance_date']);

                if (! $employee->contract?->start_date || $employee->contract->start_date->copy()->addYear()->greaterThan($attendanceDate)) {
                    return back()->with('error', 'Karyawan belum eligible annual leave, tidak bisa input CUTI PRIBADI/POTONG CUTI.');
                }
            }

            if ($this->hasManualLeaveTransaction($data['employee_id'], $data['attendance_date'], $data['exception_type'])) {
                return back()->with('error', 'Cuti ini berasal dari Annual Leave Detail. Ubah dari menu Annual Leave Detail, bukan dari Attendance Recap.');
            }

            $adjustment->exception_type = $data['exception_type'];
            $adjustment->exception_note = $data['exception_note'] ?? null;
        }

        if ($action === 'scan') {
            $adjustment->check_in_override = $data['check_in_override'] ?? null;
            $adjustment->check_out_override = $data['check_out_override'] ?? null;
        }

        $adjustment->save();
        $this->syncAnnualLeave($adjustment);

        return redirect()
            ->route('attendance-recap.index', array_filter([
                'date_from' => $data['redirect_date_from'] ?? null,
                'date_to' => $data['redirect_date_to'] ?? null,
                'company_ids' => $data['redirect_company_ids'] ?? null,
                'company_id' => empty($data['redirect_company_ids']) ? ($data['redirect_company_id'] ?? null) : null,
                'department_ids' => $data['redirect_department_ids'] ?? null,
                'department_id' => empty($data['redirect_department_ids']) ? ($data['redirect_department_id'] ?? null) : null,
                'employee_ids' => $data['redirect_employee_ids'] ?? null,
                'employee_id' => empty($data['redirect_employee_ids']) ? ($data['redirect_employee_id'] ?? null) : null,
            ]))
            ->with('success', 'Attendance adjustment saved successfully.');
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

    private function syncAnnualLeave(AttendanceAdjustment $adjustment): void
    {
        $leaveTypes = ['CUTI PRIBADI', 'CUTI KHUSUS', 'POTONG CUTI'];

        if (! in_array($adjustment->exception_type, $leaveTypes, true)) {
            AnnualLeaveTransaction::query()
                ->where('source_type', 'attendance')
                ->where('employee_id', $adjustment->employee_id)
                ->whereDate('date_from', $adjustment->attendance_date->toDateString())
                ->whereDate('date_to', $adjustment->attendance_date->toDateString())
                ->delete();

            return;
        }

        AnnualLeaveTransaction::updateOrCreate(
            [
                'employee_id' => $adjustment->employee_id,
                'source_type' => 'attendance',
                'date_from' => $adjustment->attendance_date->toDateString(),
                'date_to' => $adjustment->attendance_date->toDateString(),
            ],
            [
                'year' => (int) $adjustment->attendance_date->format('Y'),
                'days' => 1,
                'leave_type' => $adjustment->exception_type,
                'balance_effect' => $adjustment->exception_type === 'CUTI KHUSUS' ? 'no_balance' : 'annual_leave',
                'written_off_at' => null,
                'notes' => $adjustment->exception_note,
                'created_by' => auth()->id(),
            ]
        );
    }

    private function hasManualLeaveTransaction(int $employeeId, string $attendanceDate, string $leaveType): bool
    {
        return AnnualLeaveTransaction::query()
            ->where('employee_id', $employeeId)
            ->where('source_type', 'manual')
            ->where('leave_type', $leaveType)
            ->whereDate('date_from', '<=', $attendanceDate)
            ->whereDate('date_to', '>=', $attendanceDate)
            ->exists();
    }
}
