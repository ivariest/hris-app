<?php

namespace App\Http\Controllers;

use App\Models\AttendanceHoliday;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AttendanceHolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceHoliday::query()->orderByDesc('holiday_date');

        if ($from = $request->date('date_from')) {
            $query->whereDate('holiday_date', '>=', $from);
        }

        if ($to = $request->date('date_to')) {
            $query->whereDate('holiday_date', '<=', $to);
        }

        return view('pages.attendance.holidays.index', [
            'title' => 'Holiday Setting',
            'holidays' => $query->paginate(10)->withQueryString(),
            'dateFrom' => $request->string('date_from')->toString(),
            'dateTo' => $request->string('date_to')->toString(),
            'holidayTypes' => $this->holidayTypes(),
        ]);
    }

    public function create()
    {
        return view('pages.attendance.holidays.create', [
            'title' => 'Create Holiday',
            'holiday' => null,
            'holidayTypes' => $this->holidayTypes(),
        ]);
    }

    public function store(Request $request)
    {
        AttendanceHoliday::create($this->validateHoliday($request));

        return redirect()->route('attendance-holidays.index')->with('success', 'Holiday created successfully.');
    }

    public function edit(AttendanceHoliday $attendanceHoliday)
    {
        return view('pages.attendance.holidays.edit', [
            'title' => 'Edit Holiday',
            'holiday' => $attendanceHoliday,
            'holidayTypes' => $this->holidayTypes(),
        ]);
    }

    public function update(Request $request, AttendanceHoliday $attendanceHoliday)
    {
        $attendanceHoliday->update($this->validateHoliday($request, $attendanceHoliday->id));

        return redirect()->route('attendance-holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(AttendanceHoliday $attendanceHoliday)
    {
        $attendanceHoliday->delete();

        return redirect()->route('attendance-holidays.index')->with('success', 'Holiday deleted successfully.');
    }

    private function validateHoliday(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'holiday_date' => [
                'required',
                'date',
                Rule::unique('attendance_holidays', 'holiday_date')
                    ->whereNull('deleted_at')
                    ->ignore($ignoreId),
            ],
            'holiday_name' => ['required', 'string', 'max:150'],
            'holiday_type' => ['required', Rule::in(array_keys($this->holidayTypes()))],
        ]);
    }

    private function holidayTypes(): array
    {
        return [
            'national_holiday' => 'Libur Nasional',
            'collective_leave' => 'Cuti Bersama',
        ];
    }
}
