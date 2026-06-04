@php
    $companyName = $agreement->request?->company?->company_name ?: 'Perusahaan';
    $companyAddress = $agreement->request?->company?->company_address ?: '-';
    $contractStart = $agreement->contract_start_date?->translatedFormat('d F Y') ?: '-';
    $contractEnd = $agreement->contract_end_date?->translatedFormat('d F Y') ?: '-';
    $birthInfo = collect([
        $agreement->place_of_birth,
        $agreement->date_of_birth?->translatedFormat('d F Y'),
    ])->filter()->implode(', ') ?: '-';
    $formatMoney = fn ($value) => filled($value) ? 'Rp '.number_format((float) $value, 0, ',', '.') : '-';
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $agreement->agreement_number }} - Employee Agreement</title>
    <style>
        @page {
            size: A4;
            margin: 18mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef1f5;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.55;
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
            padding: 18mm;
            background: #ffffff;
            box-shadow: 0 14px 35px rgba(15, 23, 42, 0.12);
        }

        .header {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 18px;
            padding-bottom: 18px;
            border-bottom: 2px solid #111827;
        }

        .company-name {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .company-address {
            margin: 4px 0 0;
            color: #4b5563;
        }

        .doc-meta {
            min-width: 190px;
            border: 1px solid #d1d5db;
        }

        .doc-meta div {
            display: grid;
            grid-template-columns: 82px 1fr;
            border-bottom: 1px solid #d1d5db;
        }

        .doc-meta div:last-child {
            border-bottom: 0;
        }

        .doc-meta span {
            padding: 7px 9px;
        }

        .doc-meta span:first-child {
            background: #f3f4f6;
            font-weight: 700;
        }

        h1 {
            margin: 24px 0 3px;
            text-align: center;
            font-size: 18px;
            letter-spacing: 0;
            text-decoration: underline;
            text-transform: uppercase;
        }

        .subtitle {
            margin: 0 0 22px;
            text-align: center;
            font-weight: 700;
        }

        .section {
            margin-top: 18px;
        }

        .section-title {
            margin: 0 0 8px;
            padding: 7px 10px;
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 18px;
        }

        .field {
            display: grid;
            grid-template-columns: 145px 1fr;
            gap: 8px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .label {
            color: #4b5563;
            font-weight: 700;
        }

        .value {
            color: #111827;
        }

        .paragraph {
            margin: 0 0 10px;
            text-align: justify;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 9px 10px;
            border: 1px solid #d1d5db;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: 800;
        }

        .signature {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 70px;
            margin-top: 46px;
            text-align: center;
        }

        .signature-space {
            height: 82px;
        }

        .signature-name {
            display: inline-block;
            min-width: 190px;
            padding-top: 6px;
            border-top: 1px solid #111827;
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
        <a href="{{ route('employee-agreements.show', $agreement) }}">Kembali</a>
    </div>

    <main class="page">
        <header class="header">
            <div>
                <p class="company-name">{{ $companyName }}</p>
                <p class="company-address">{{ $companyAddress }}</p>
            </div>
            <div class="doc-meta">
                <div><span>No.</span><span>{{ $agreement->agreement_number }}</span></div>
                <div><span>Status</span><span>{{ $agreement->agreement_status }}</span></div>
                <div><span>Request</span><span>{{ $agreement->request?->request_number ?? '-' }}</span></div>
            </div>
        </header>

        <h1>Perjanjian Kerja</h1>
        <p class="subtitle">Nomor: {{ $agreement->agreement_number }}</p>

        <section class="section">
            <p class="paragraph">
                Pada hari ini, {{ now()->translatedFormat('d F Y') }}, pihak perusahaan dan karyawan sepakat membuat perjanjian kerja dengan data sebagai berikut.
            </p>
        </section>

        <section class="section">
            <h2 class="section-title">Data Karyawan</h2>
            <div class="grid">
                <div class="field"><span class="label">Nama Karyawan</span><span class="value">{{ $agreement->employee_name }}</span></div>
                <div class="field"><span class="label">Department</span><span class="value">{{ $agreement->department ?: '-' }}</span></div>
                <div class="field"><span class="label">Tempat/Tanggal Lahir</span><span class="value">{{ $birthInfo }}</span></div>
                <div class="field"><span class="label">Jenis Kelamin</span><span class="value">{{ $agreement->gender ?: '-' }}</span></div>
                <div class="field"><span class="label">Agama</span><span class="value">{{ $agreement->religion ?: '-' }}</span></div>
                <div class="field"><span class="label">Nomor KTP</span><span class="value">{{ $agreement->id_card_number ?: '-' }}</span></div>
                <div class="field"><span class="label">Nomor Telepon</span><span class="value">{{ $agreement->phone_number ?: '-' }}</span></div>
                <div class="field"><span class="label">Email</span><span class="value">{{ $agreement->email ?: '-' }}</span></div>
                <div class="field full"><span class="label">Alamat</span><span class="value">{{ $agreement->address ?: '-' }}</span></div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Detail Perjanjian</h2>
            <div class="grid">
                <div class="field"><span class="label">Agreement Status</span><span class="value">{{ $agreement->agreement_status }}</span></div>
                <div class="field"><span class="label">Nomor Request</span><span class="value">{{ $agreement->request?->request_number ?? '-' }}</span></div>
                <div class="field"><span class="label">Tanggal Awal Kontrak</span><span class="value">{{ $contractStart }}</span></div>
                <div class="field"><span class="label">Tanggal Akhir Kontrak</span><span class="value">{{ $contractEnd }}</span></div>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">Komponen Gaji</h2>
            <table>
                <thead>
                    <tr>
                        <th>Komponen</th>
                        <th>Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Gaji Pokok</td>
                        <td>{{ $formatMoney($agreement->base_salary) }}</td>
                    </tr>
                    <tr>
                        <td>Tunjangan</td>
                        <td>{{ $formatMoney($agreement->allowance) }}</td>
                    </tr>
                    <tr>
                        <td>Uang Makan</td>
                        <td>{{ $formatMoney($agreement->meal_allowance) }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="section">
            <h2 class="section-title">Ketentuan Standar</h2>
            <p class="paragraph">
                Karyawan bersedia melaksanakan pekerjaan sesuai jabatan, penempatan, dan peraturan perusahaan yang berlaku. Perusahaan berhak melakukan evaluasi kerja sesuai kebutuhan operasional dan ketentuan internal.
            </p>
            <p class="paragraph">
                Perjanjian ini berlaku sejak {{ $contractStart }} sampai dengan {{ $contractEnd }}. Apabila diperlukan perubahan atau perpanjangan, maka akan dibuatkan dokumen tertulis sesuai persetujuan kedua belah pihak.
            </p>
            <p class="paragraph">
                Catatan: <span class="muted">{{ $agreement->notes ?: '-' }}</span>
            </p>
        </section>

        <section class="signature">
            <div>
                <p>Pihak Perusahaan</p>
                <div class="signature-space"></div>
                <span class="signature-name">{{ $companyName }}</span>
            </div>
            <div>
                <p>Karyawan</p>
                <div class="signature-space"></div>
                <span class="signature-name">{{ $agreement->employee_name }}</span>
            </div>
        </section>
    </main>
</body>
</html>
