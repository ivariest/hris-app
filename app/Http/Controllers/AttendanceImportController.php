<?php

namespace App\Http\Controllers;

use App\Helpers\UploadHelper;
use App\Models\AttendanceHoliday;
use App\Models\AttendanceImport;
use App\Models\AttendanceLog;
use App\Models\AttendanceShift;
use App\Models\Employee;
use App\Support\FingerprintAttendanceExcelReader;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceImportController extends Controller
{
    public function index()
    {
        return view('pages.attendance.imports.index', [
            'title' => 'Import Fingerprint',
            'imports' => AttendanceImport::with('importer')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('pages.attendance.imports.create', [
            'title' => 'Upload Fingerprint Excel',
            'defaultShift' => $this->defaultShift(),
        ]);
    }

    public function store(Request $request, FingerprintAttendanceExcelReader $reader)
    {
        $data = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'file_path' => ['required', 'string'],
            'file_name' => ['required', 'string', 'max:255'],
        ]);

        $shift = $this->defaultShift();

        if (! $shift) {
            throw ValidationException::withMessages([
                'attendance_file' => 'Buat shift default terlebih dahulu sebelum import attendance.',
            ]);
        }

        if (! Storage::disk('public')->exists($data['file_path'])) {
            throw ValidationException::withMessages([
                'file_path' => 'File preview tidak ditemukan. Upload ulang file fingerprint.',
            ]);
        }

        $filePath = $data['file_path'];
        $fullPath = Storage::disk('public')->path($filePath);
        $rows = $reader->read($fullPath, $data['period_start'], $data['period_end']);

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'attendance_file' => 'File tidak memiliki baris attendance yang bisa dibaca.',
            ]);
        }

        $employees = Employee::query()
            ->whereNotNull('attendance_id')
            ->get()
            ->keyBy(fn (Employee $employee) => trim((string) $employee->attendance_id));

        $holidays = AttendanceHoliday::query()
            ->whereIn('holiday_date', collect($rows)->pluck('scan_date')->filter()->unique()->values())
            ->get()
            ->keyBy(fn (AttendanceHoliday $holiday) => $holiday->holiday_date->toDateString());

        $matched = 0;
        $unmatched = 0;

        $import = DB::transaction(function () use ($filePath, $data, $rows, $employees, $holidays, $shift, &$matched, &$unmatched): AttendanceImport {
            $import = AttendanceImport::create([
                'file_name' => $data['file_name'],
                'file_path' => $filePath,
                'period_start' => $data['period_start'],
                'period_end' => $data['period_end'],
                'imported_by' => auth()->id(),
                'total_rows' => count($rows),
                'matched_rows' => 0,
                'unmatched_rows' => 0,
                'imported_at' => now(),
            ]);

            foreach ($rows as $row) {
                $employee = $employees->get($row['attendance_id']);
                $isHoliday = $row['scan_date'] && $holidays->has($row['scan_date']);
                $status = 'unmatched';
                $lateMinutes = 0;
                $earlyLeaveMinutes = 0;
                $notes = null;

                if ($employee) {
                    $matched++;
                    [$status, $lateMinutes, $earlyLeaveMinutes, $notes] = $this->attendanceStatus($row, $shift, $isHoliday);
                } else {
                    $unmatched++;
                    $notes = 'ID Absen tidak ditemukan di data karyawan.';
                }

                AttendanceLog::create([
                    'attendance_import_id' => $import->id,
                    'employee_id' => $employee?->id,
                    'attendance_id' => $row['attendance_id'],
                    'fingerprint_name' => $row['fingerprint_name'],
                    'scan_date' => $row['scan_date'],
                    'check_in' => $row['check_in'],
                    'check_out' => $row['check_out'],
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'early_leave_minutes' => $earlyLeaveMinutes,
                    'notes' => $notes,
                ]);
            }

            $import->update([
                'matched_rows' => $matched,
                'unmatched_rows' => $unmatched,
            ]);

            return $import;
        });

        return redirect()->route('attendance-imports.show', $import)->with('success', 'Fingerprint file imported successfully.');
    }

    public function preview(Request $request, FingerprintAttendanceExcelReader $reader)
    {
        $data = $request->validate([
            'period_start' => ['required', 'date'],
            'period_end' => ['required', 'date', 'after_or_equal:period_start'],
            'attendance_file' => ['required', 'file', 'mimes:xlsx', 'max:5120'],
        ]);

        if (! $this->defaultShift()) {
            throw ValidationException::withMessages([
                'attendance_file' => 'Buat shift default terlebih dahulu sebelum import attendance.',
            ]);
        }

        $file = $data['attendance_file'];
        $filePath = UploadHelper::store($file, 'attendance-import-previews');
        $fullPath = Storage::disk('public')->path($filePath);
        $rows = $reader->read($fullPath, $data['period_start'], $data['period_end']);

        if (empty($rows)) {
            throw ValidationException::withMessages([
                'attendance_file' => 'File tidak memiliki baris attendance yang bisa dibaca.',
            ]);
        }

        $attendanceIds = Employee::query()
            ->whereNotNull('attendance_id')
            ->pluck('attendance_id')
            ->map(fn ($id) => trim((string) $id))
            ->all();

        $knownAttendanceIds = array_flip($attendanceIds);

        $invalidDateRows = collect($rows)
            ->filter(fn (array $row) => empty($row['scan_date']))
            ->count();

        $unmatchedRows = collect($rows)
            ->filter(fn (array $row) => ! isset($knownAttendanceIds[$row['attendance_id']]))
            ->count();

        return view('pages.attendance.imports.preview', [
            'title' => 'Preview Import Fingerprint',
            'filePath' => $filePath,
            'fileName' => $file->getClientOriginalName(),
            'periodStart' => $data['period_start'],
            'periodEnd' => $data['period_end'],
            'rows' => collect($rows),
            'sampleRows' => collect($rows)->take(25),
            'summary' => [
                'total_rows' => count($rows),
                'valid_date_rows' => count($rows) - $invalidDateRows,
                'invalid_date_rows' => $invalidDateRows,
                'matched_rows' => count($rows) - $unmatchedRows,
                'unmatched_rows' => $unmatchedRows,
            ],
        ]);
    }

    public function show(AttendanceImport $attendanceImport)
    {
        return view('pages.attendance.imports.show', [
            'title' => 'Import Detail',
            'import' => $attendanceImport->load('importer'),
            'logs' => $attendanceImport->logs()
                ->with('employee.company')
                ->orderBy('scan_date')
                ->orderBy('attendance_id')
                ->paginate(25),
        ]);
    }

    private function defaultShift(): ?AttendanceShift
    {
        return AttendanceShift::query()
            ->where('status', 'active')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->first();
    }

    /**
     * @return array{0:string,1:int,2:int,3:?string}
     */
    private function attendanceStatus(array $row, AttendanceShift $shift, bool $isHoliday): array
    {
        if ($isHoliday) {
            return ['holiday', 0, 0, 'Tanggal ini terdaftar sebagai libur.'];
        }

        if (! $row['scan_date'] || (! $row['check_in'] && ! $row['check_out'])) {
            return ['absent', 0, 0, 'Tanggal atau jam scan tidak lengkap.'];
        }

        $status = 'present';
        $lateMinutes = 0;
        $earlyLeaveMinutes = 0;

        if ($row['check_in']) {
            $checkIn = Carbon::parse($row['scan_date'].' '.$row['check_in']);
            $shiftIn = Carbon::parse($row['scan_date'].' '.$shift->check_in_time)
                ->addMinutes((int) $shift->late_tolerance_minutes);

            if ($checkIn->greaterThan($shiftIn)) {
                $lateMinutes = $shiftIn->diffInMinutes($checkIn);
                $status = 'late';
            }
        }

        if ($row['check_out']) {
            $checkOut = Carbon::parse($row['scan_date'].' '.$row['check_out']);
            $shiftOut = Carbon::parse($row['scan_date'].' '.$shift->check_out_time)
                ->subMinutes((int) $shift->early_leave_tolerance_minutes);

            if ($checkOut->lessThan($shiftOut)) {
                $earlyLeaveMinutes = $checkOut->diffInMinutes($shiftOut);
                $status = $status === 'late' ? 'late_early_leave' : 'early_leave';
            }
        }

        return [$status, $lateMinutes, $earlyLeaveMinutes, null];
    }
}
