@php
    $formatMinutes = static function (?int $minutes): string {
        if (! $minutes) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours}j {$remainingMinutes}m";
        }

        if ($hours > 0) {
            return "{$hours}j";
        }

        return "{$remainingMinutes}m";
    };

    $workMinutes = static function (?string $checkIn, ?string $checkOut): int {
        if (! $checkIn || ! $checkOut) {
            return 0;
        }

        $start = \Carbon\Carbon::parse($checkIn);
        $end = \Carbon\Carbon::parse($checkOut);

        return $end->greaterThan($start) ? (int) $start->diffInMinutes($end) : 0;
    };

    $statusLabels = [
        'present' => 'Hadir',
        'late' => 'Telat',
        'early_leave' => 'Pulang Awal',
        'late_early_leave' => 'Telat + Pulang Awal',
        'leave' => 'Cuti',
        'absent' => 'Alpha',
        'off' => 'Libur',
    ];

    $exceptionTypes = [
        'ALPHA' => 'Alpha',
        'IZIN' => 'Izin',
        'SAKIT TANPA SURAT' => 'Sakit Tanpa Surat',
        'SAKIT DENGAN SURAT' => 'Sakit Dengan Surat',
        'CUTI PRIBADI' => 'Cuti Pribadi',
        'CUTI KHUSUS' => 'Cuti Khusus',
        'CUTI BERSAMA' => 'Cuti Bersama',
        'POTONG CUTI' => 'Potong Cuti',
    ];

    $firstPrintableRow = $rows->first();
    $documentDepartment = $selectedDepartmentNames->isNotEmpty()
        ? $selectedDepartmentNames->implode(', ')
        : ($selectedEmployeeIds !== []
            ? $rows
                ->map(fn (array $row) => $row['employee']->employeePosition?->position?->subDepartment?->department?->department_name)
                ->filter()
                ->unique()
                ->implode(', ')
            : 'Semua Department');

    $backUrl = route('attendance-recap.index', array_filter([
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'company_ids' => $selectedCompanyIds,
        'department_ids' => $selectedDepartmentIds,
        'employee_ids' => $selectedEmployeeIds,
    ]));
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Absensi Karyawan {{ $dateFrom }} - {{ $dateTo }}</title>
    <style>
        @page {
            size: A4;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f5;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.25;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            background: #ffffff;
            border-bottom: 1px solid #d1d5db;
        }

        .toolbar button,
        .toolbar a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 38px;
            padding: 0 14px;
            border-radius: 8px;
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #1f2937;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button {
            border-color: #465fff;
            background: #465fff;
            color: #ffffff;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 18px auto;
            padding: 8mm;
            background: #ffffff;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
        }

        .document-header {
            padding-bottom: 8px;
            border-bottom: 2px solid #111827;
            text-align: center;
        }

        h1 {
            margin: 0 0 5px;
            font-size: 16px;
            text-transform: uppercase;
        }

        .doc-meta {
            display: inline-grid;
            grid-template-columns: auto auto;
            gap: 3px 10px;
            margin-top: 2px;
            text-align: left;
            color: #374151;
            font-size: 12px;
        }

        .doc-meta strong {
            color: #111827;
        }

        .employee-section {
            page-break-inside: avoid;
            margin-top: 10px;
        }

        .employee-detail {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 4px 14px;
            margin-bottom: 6px;
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        .employee-detail h2 {
            grid-column: 1 / -1;
            margin: 0 0 2px;
            font-size: 11px;
            text-transform: uppercase;
        }

        .field {
            display: grid;
            grid-template-columns: 62px 1fr;
            gap: 5px;
        }

        .label {
            color: #6b7280;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px 4px;
            border: 1px solid #d1d5db;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 800;
        }

        .off-row td {
            background: #fef2f2;
            color: #991b1b;
        }

        .center {
            text-align: center;
        }

        .recap-title {
            margin: 7px 0 4px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .recap-table th {
            width: 28%;
            background: #f9fafb;
        }

        .recap-table td {
            width: 22%;
        }

        .muted {
            color: #6b7280;
        }

        @media print {
            body {
                background: #ffffff;
            }

            .toolbar {
                display: none;
            }

            .page {
                width: auto;
                min-height: auto;
                margin: 0;
                padding: 0;
                box-shadow: none;
            }

            .employee-section {
                page-break-inside: avoid;
                page-break-after: always;
            }

            .employee-section:last-child {
                page-break-after: auto;
            }

            h1 {
                font-size: 15px;
            }

            body {
                font-size: 8.5px;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Print / Save PDF</button>
        <a href="{{ $backUrl }}">Kembali</a>
    </div>

    <main class="page">
        <header class="document-header">
            <h1>Daftar Absensi Karyawan</h1>
            <div class="doc-meta">
                <span>Periode Absensi</span>
                <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('j M y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('j M y') }}</strong>
                <span>Department</span>
                <strong>{{ $documentDepartment }}</strong>
            </div>
        </header>

        @forelse ($groupedRows as $employeeRows)
            @php
                $firstRow = $employeeRows->first();
                $cardEmployee = $firstRow['employee'];
                $position = $cardEmployee->employeePosition?->position;
                $departmentName = $position?->subDepartment?->department?->department_name ?: '-';
                $subDepartmentName = $position?->subDepartment?->sub_department_name ?: '-';
                $effectiveRows = collect($employeeRows)->reject(fn (array $row) => in_array($row['status'], ['off', 'leave'], true));
                $effectiveDays = $effectiveRows->count();
                $attendedDays = $effectiveRows->filter(fn (array $row) => $row['check_in'] || $row['check_out'])->count();
                $totalWorkMinutes = $effectiveRows->sum(fn (array $row) => $workMinutes($row['check_in'], $row['check_out']));
                $averageWorkMinutes = $effectiveDays > 0 ? (int) round($totalWorkMinutes / $effectiveDays) : 0;
                $attendancePercent = $effectiveDays > 0 ? ($attendedDays / $effectiveDays) * 100 : 0;
                $totalLateMinutes = $effectiveRows->sum('late_minutes');
                $totalEarlyLeaveMinutes = $effectiveRows->sum('early_leave_minutes');
                $exceptionCounts = collect($exceptionTypes)
                    ->mapWithKeys(fn (string $label, string $type) => [
                        $type => $effectiveRows->filter(fn (array $row) => $row['adjustment']?->exception_type === $type)->count(),
                    ]);
            @endphp
            <section class="employee-section">
                <div class="employee-detail">
                    <h2>Detail Karyawan</h2>
                    <div class="field"><span class="label">Nama</span><span>{{ $cardEmployee->nama_karyawan }}</span></div>
                    <div class="field"><span class="label">ID Absen</span><span>{{ $cardEmployee->attendance_id ?: '-' }}</span></div>
                    <div class="field"><span class="label">Department</span><span>{{ $departmentName }}</span></div>
                    <div class="field"><span class="label">Sub Dept</span><span>{{ $subDepartmentName }}</span></div>
                    <div class="field"><span class="label">Jabatan</span><span>{{ $position?->position_name ?: '-' }}</span></div>
                    <div class="field"><span class="label">Area</span><span>{{ $cardEmployee->employeePosition?->area ?: '-' }}</span></div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 90px;">Tanggal</th>
                            <th style="width: 72px;">Hari</th>
                            <th style="width: 54px;">Masuk</th>
                            <th style="width: 54px;">Pulang</th>
                            <th style="width: 70px;">Telat</th>
                            <th style="width: 88px;">Pulang Awal</th>
                            <th style="width: 92px;">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employeeRows as $row)
                            @php
                                $adjustment = $row['adjustment'];
                                $note = trim(($adjustment?->exception_type ?? '').($adjustment?->exception_note ? ' - '.$adjustment->exception_note : ''));

                                if ($row['is_weekend']) {
                                    $note = trim($note.' Libur akhir pekan');
                                }

                                if ($row['is_holiday']) {
                                    $note = trim($note.' '.$row['holiday']->holiday_name);
                                }
                            @endphp
                            <tr class="{{ $row['status'] === 'off' ? 'off-row' : '' }}">
                                <td>{{ $row['date']->format('d M Y') }}</td>
                                <td>{{ $row['date']->translatedFormat('l') }}</td>
                                <td class="center">{{ $row['check_in'] ? substr($row['check_in'], 0, 5) : '-' }}</td>
                                <td class="center">{{ $row['check_out'] ? substr($row['check_out'], 0, 5) : '-' }}</td>
                                <td>{{ $formatMinutes($row['late_minutes']) }}</td>
                                <td>{{ $formatMinutes($row['early_leave_minutes']) }}</td>
                                <td>{{ $statusLabels[$row['status']] ?? $row['status'] }}</td>
                                <td>{{ $note ?: '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="recap-title">Rekap Absensi</div>
                <table class="recap-table">
                    <tbody>
                        <tr>
                            <th>Total Rata-rata Jam Kerja</th>
                            <td>{{ $formatMinutes($averageWorkMinutes) }} <span class="muted">({{ $formatMinutes($totalWorkMinutes) }} / {{ $effectiveDays }} hari kerja efektif)</span></td>
                            <th>Persentase Kehadiran</th>
                            <td>{{ $attendedDays }}/{{ $effectiveDays }} hari kerja efektif ({{ number_format($attendancePercent, 2, ',', '.') }}%)</td>
                        </tr>
                        <tr>
                            <th>Total Jam Telat</th>
                            <td>{{ $formatMinutes($totalLateMinutes) }}</td>
                            <th>Total Jam Pulang Awal</th>
                            <td>{{ $formatMinutes($totalEarlyLeaveMinutes) }}</td>
                        </tr>
                        @foreach (array_chunk($exceptionTypes, 2, true) as $exceptionPair)
                            <tr>
                                @foreach ($exceptionPair as $type => $label)
                                    <th>Total {{ $label }}</th>
                                    <td>{{ $exceptionCounts->get($type, 0) }} hari</td>
                                @endforeach
                                @if (count($exceptionPair) === 1)
                                    <th></th>
                                    <td></td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @empty
            <p class="muted">Tidak ada data pada filter ini.</p>
        @endforelse
    </main>
</body>
</html>
