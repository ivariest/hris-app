@php
    $formatCurrency = static fn (int|float $amount): string => 'Rp '.number_format($amount, 0, ',', '.');
    $selectedDepartmentNames = $departments->whereIn('id', $departmentIds)->pluck('department_name')->values();
    $documentDepartment = $selectedDepartmentNames->isNotEmpty()
        ? $selectedDepartmentNames->implode(', ')
        : 'Semua Department';
    $backUrl = route('allowance-report.index', array_filter([
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'company_ids' => $companyIds,
        'department_ids' => $departmentIds,
        'employee_ids' => $employeeIds,
        'meal_rate' => $mealRate,
        'transport_rate' => $transportRate,
    ], fn ($value) => $value !== '' && $value !== [] && $value !== null));
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Allowance Report {{ $dateFrom }} - {{ $dateTo }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f5;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            line-height: 1.35;
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
            width: 297mm;
            min-height: 210mm;
            margin: 18px auto;
            padding: 10mm;
            background: #ffffff;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
        }

        .document-header {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            padding-bottom: 10px;
            border-bottom: 2px solid #111827;
        }

        h1 {
            margin: 0 0 6px;
            font-size: 18px;
            text-transform: uppercase;
        }

        .meta {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 3px 8px;
            color: #374151;
        }

        .meta strong {
            color: #111827;
        }

        .summary {
            display: grid;
            min-width: 270px;
            grid-template-columns: 1fr 1fr;
            gap: 5px;
        }

        .summary div {
            padding: 6px 8px;
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }

        .summary span {
            display: block;
            color: #6b7280;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .summary strong {
            display: block;
            margin-top: 2px;
            font-size: 13px;
        }

        table {
            width: 100%;
            margin-top: 12px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 5px 6px;
            border: 1px solid #d1d5db;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 800;
            text-transform: uppercase;
        }

        .right {
            text-align: right;
        }

        .total-row td {
            background: #f9fafb;
            font-weight: 800;
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
            <div>
                <h1>Allowance Report</h1>
                <div class="meta">
                    <span>Periode</span>
                    <strong>{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</strong>
                    <span>Department</span>
                    <strong>{{ $documentDepartment }}</strong>
                    <span>Uang Makan</span>
                    <strong>{{ $formatCurrency($mealRate) }} / hari</strong>
                    <span>Transport</span>
                    <strong>{{ $formatCurrency($transportRate) }} / hari</strong>
                </div>
            </div>
            <div class="summary">
                <div>
                    <span>Karyawan</span>
                    <strong>{{ $totals['employees'] }}</strong>
                </div>
                <div>
                    <span>Hari Kehadiran</span>
                    <strong>{{ $totals['present_days'] }}</strong>
                </div>
                <div>
                    <span>Hari Telat</span>
                    <strong>{{ $totals['late_days'] }}</strong>
                </div>
                <div>
                    <span>Total Potongan</span>
                    <strong>{{ $formatCurrency($totals['deduction']) }}</strong>
                </div>
                <div>
                    <span>TOTAL</span>
                    <strong>{{ $formatCurrency($totals['total_allowance']) }}</strong>
                </div>
            </div>
        </header>

        <table>
            <thead>
                <tr>
                    <th style="width: 34px;">No</th>
                    <th style="width: 150px;">ID Absen</th>
                    <th>Nama Karyawan</th>
                    <th>Department</th>
                    <th class="right">Hari Kehadiran</th>
                    <th class="right">Total Hari Telat</th>
                    <th class="right">Uang Makan</th>
                    <th class="right">Uang Transport</th>
                    <th class="right">Total Potongan</th>
                    <th class="right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $row)
                    @php
                        $employee = $row['employee'];
                        $departmentName = $employee->employeePosition?->position?->subDepartment?->department?->department_name ?: '-';
                    @endphp
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $employee->attendance_id ?: '-' }}</td>
                        <td>{{ $employee->nama_karyawan }}</td>
                        <td>{{ $departmentName }}</td>
                        <td class="right">{{ $row['present_days'] }}</td>
                        <td class="right">{{ $row['late_days'] }}</td>
                        <td class="right">{{ $formatCurrency($row['meal_allowance']) }}</td>
                        <td class="right">{{ $formatCurrency($row['transport_allowance']) }}</td>
                        <td class="right">{{ $formatCurrency($row['deduction']) }}</td>
                        <td class="right">{{ $formatCurrency($row['total_allowance']) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="muted">Tidak ada data pada filter ini.</td>
                    </tr>
                @endforelse
                <tr class="total-row">
                    <td colspan="5">Total</td>
                    <td class="right">{{ $totals['late_days'] }}</td>
                    <td class="right">{{ $formatCurrency($totals['meal_allowance']) }}</td>
                    <td class="right">{{ $formatCurrency($totals['transport_allowance']) }}</td>
                    <td class="right">{{ $formatCurrency($totals['deduction']) }}</td>
                    <td class="right">{{ $formatCurrency($totals['total_allowance']) }}</td>
                </tr>
            </tbody>
        </table>
    </main>
</body>
</html>
