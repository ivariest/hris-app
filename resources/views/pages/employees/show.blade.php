@extends('layouts.app')

@php
    $position = $employee->employeePosition;
    $personal = $employee->personal;
    $contract = $employee->contract;
    $spouse = $employee->spouse;
    $bpjs = $employee->bpjs;
    $tax = $employee->tax;
    $bank = $employee->bank;
    $payroll = $employee->payroll;
    $documents = $employee->documents->keyBy('document_type');
    $activeTemporaryPromotion = $employee->promotions
        ->where('promotion_type', 'PEJABAT_SEMENTARA')
        ->where('record_status', 'ACTIVE')
        ->sortByDesc('start_date')
        ->first();
    $value = fn ($value) => filled($value) ? $value : '-';
    $dateValue = fn ($date) => $date ? $date->format('d M Y') : '-';
    $money = fn ($amount) => filled($amount) ? 'Rp '.number_format((float) $amount, 0, ',', '.') : '-';
    $assignmentLabel = fn ($position, $level = null) => collect([
        $position?->position_name,
        $level?->level_name,
        $position?->subDepartment?->sub_department_name,
        $position?->subDepartment?->department?->department_name,
    ])->filter()->implode(' | ');
    $employeeHistories = collect()
        ->merge($employee->promotions->map(fn ($promotion) => [
            'type' => 'Promotion',
            'number' => $promotion->promotion_number,
            'date' => $promotion->start_date,
            'from' => $assignmentLabel($promotion->oldPosition, $promotion->oldLevel),
            'to' => $assignmentLabel($promotion->newPosition, $promotion->newLevel),
            'status' => str_replace('_', ' ', $promotion->record_status),
            'href' => route('employee-promotions.show', $promotion),
        ]))
        ->merge($employee->mutations->map(fn ($mutation) => [
            'type' => 'Mutation',
            'number' => $mutation->mutation_number,
            'date' => $mutation->effective_date,
            'from' => $assignmentLabel($mutation->oldPosition),
            'to' => $assignmentLabel($mutation->newPosition),
            'status' => 'Completed',
            'href' => route('employee-mutations.show', $mutation),
        ]))
        ->merge($employee->demotions->map(fn ($demotion) => [
            'type' => 'Demotion',
            'number' => $demotion->demotion_number,
            'date' => $demotion->effective_date,
            'from' => $assignmentLabel($demotion->oldPosition, $demotion->oldLevel),
            'to' => $assignmentLabel($demotion->newPosition, $demotion->newLevel),
            'status' => 'Completed',
            'href' => route('employee-demotions.show', $demotion),
        ]))
        ->sortByDesc('date')
        ->values();
@endphp

@section('content')
    <x-common.page-shell title="{{ $employee->nama_karyawan }}" description="Complete employee profile, assignment, personal data, payroll, and documents.">
        <x-slot:actions>
            @if ($employee->status_karyawan === 'active')
                <a href="{{ route('employees.edit', $employee) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Edit</a>
            @endif
            <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back to list</a>
        </x-slot:actions>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="relative p-6 sm:p-8">
                <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-r from-brand-500 via-blue-light-500 to-success-500"></div>
                <div class="relative flex flex-col gap-6 pt-8 lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        @if ($employee->foto_karyawan)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($employee->foto_karyawan) }}" alt="{{ $employee->nama_karyawan }}" class="shrink-0 rounded-full border-4 border-white object-cover shadow-theme-lg dark:border-gray-900" style="width: 88px; height: 88px; min-width: 88px;">
                        @else
                            <div class="flex shrink-0 items-center justify-center rounded-full border-4 border-white bg-brand-50 text-2xl font-semibold text-brand-600 shadow-theme-lg dark:border-gray-900 dark:bg-brand-500/10 dark:text-brand-300" style="width: 88px; height: 88px; min-width: 88px;">
                                {{ strtoupper(substr($employee->nama_karyawan, 0, 1)) }}
                            </div>
                        @endif

                        <div>
                            <div class="mb-3 flex flex-wrap items-center gap-2">
                                <x-ui.badge :color="$employee->status_karyawan === 'active' ? 'success' : ($employee->status_karyawan === 'inactive' ? 'warning' : 'error')">
                                    {{ strtoupper($employee->status_karyawan) }}
                                </x-ui.badge>
                                @if ($activeTemporaryPromotion)
                                    <x-ui.badge color="warning">
                                        PJS
                                    </x-ui.badge>
                                @endif
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $value($employee->nik_karyawan) }}</span>
                            </div>
                            <h2 class="text-title-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</h2>
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $value($position?->position?->position_name) }} at {{ $value($employee->company?->company_name) }}</p>
                            @if ($activeTemporaryPromotion)
                                <p class="mt-2 text-xs font-medium text-warning-700 dark:text-warning-300">
                                    Pejabat Sementara sampai {{ $dateValue($activeTemporaryPromotion->end_date) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[520px]">
                        <div class="rounded-2xl border border-gray-200 bg-white/95 p-4 dark:border-gray-800 dark:bg-gray-900/90">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Department</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $value($position?->position?->subDepartment?->department?->department_name) }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white/95 p-4 dark:border-gray-800 dark:bg-gray-900/90">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Location</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $value($position?->location?->location_name) }}</p>
                        </div>
                        <div class="rounded-2xl border border-gray-200 bg-white/95 p-4 dark:border-gray-800 dark:bg-gray-900/90">
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Agreement</p>
                            <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $value($contract?->status_kontrak) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                @include('pages.employees.partials.detail-card', [
                    'title' => 'Employee Profile',
                    'items' => [
                        'NIK Karyawan' => $employee->nik_karyawan,
                        'Company' => $employee->company?->company_name,
                        'Department' => $position?->position?->subDepartment?->department?->department_name,
                        'Sub Department' => $position?->position?->subDepartment?->sub_department_name,
                        'Position' => $position?->position?->position_name,
                        'Position Level' => $position?->level?->level_name,
                        'Status Karyawan' => strtoupper($employee->status_karyawan),
                        'Alasan Nonaktif' => $employee->deactivation_reason ? str_replace('_', ' ', $employee->deactivation_reason) : null,
                        'Tanggal Nonaktif' => $dateValue($employee->deactivated_at),
                        'Status Jabatan Aktif' => $activeTemporaryPromotion
                            ? trim(($position?->position?->position_name ?? '-') . ' PJS')
                            : $position?->position?->position_name,
                        'Lokasi' => $position?->location?->location_name,
                        'Area' => $position?->area,
                        'Rayon' => $position?->rayon,
                        'Atasan Langsung' => $position?->supervisor?->nama_karyawan,
                    ],
                ])

                @include('pages.employees.partials.detail-card', [
                    'title' => 'Biodata Diri',
                    'items' => [
                        'Nomor KTP' => $personal?->no_ktp,
                        'Nomor KK' => $personal?->no_kk,
                        'Jenis Kelamin' => $personal?->jenis_kelamin,
                        'Agama' => $personal?->agama,
                        'Tempat Lahir' => $personal?->tempat_lahir,
                        'Tanggal Lahir' => $dateValue($personal?->tgl_lahir),
                        'Status Pernikahan' => $personal?->status_pernikahan,
                        'Pendidikan' => $personal?->pendidikan,
                        'Jurusan' => $personal?->jurusan,
                        'Golongan Darah' => $personal?->golongan_darah,
                        'Email Kantor' => $employee->email_kantor,
                        'Email Pribadi' => $employee->email_pribadi,
                        'Nomor HP' => $employee->no_hp,
                        'Nomor HP Darurat' => $employee->no_hp_darurat,
                        'Alamat' => $personal?->alamat,
                        'Kelurahan' => $personal?->kelurahan,
                        'Kecamatan' => $personal?->kecamatan,
                        'Kota' => $personal?->kota,
                        'Kode Pos' => $personal?->kode_pos,
                    ],
                ])

                @include('pages.employees.partials.detail-card', [
                    'title' => 'Data Istri / Suami',
                    'items' => [
                        'Nomor KTP' => $spouse?->nik,
                        'Nama' => $spouse?->nama,
                        'Jenis Kelamin' => $spouse?->jenis_kelamin,
                        'Tempat Lahir' => $spouse?->tempat_lahir,
                        'Tanggal Lahir' => $dateValue($spouse?->tgl_lahir),
                        'Pendidikan' => $spouse?->pendidikan,
                        'Pekerjaan' => $spouse?->pekerjaan,
                    ],
                ])

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Data Anak</h3>
                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        @forelse ($employee->children as $child)
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $value($child->nama) }}</p>
                                <dl class="mt-3 space-y-2 text-sm">
                                    <div><dt class="text-gray-500 dark:text-gray-400">NIK/KTP</dt><dd class="font-medium text-gray-900 dark:text-white/90">{{ $value($child->nik) }}</dd></div>
                                    <div><dt class="text-gray-500 dark:text-gray-400">Tempat Lahir</dt><dd class="font-medium text-gray-900 dark:text-white/90">{{ $value($child->tempat_lahir) }}</dd></div>
                                    <div><dt class="text-gray-500 dark:text-gray-400">Tanggal Lahir</dt><dd class="font-medium text-gray-900 dark:text-white/90">{{ $dateValue($child->tgl_lahir) }}</dd></div>
                                    <div><dt class="text-gray-500 dark:text-gray-400">Pendidikan</dt><dd class="font-medium text-gray-900 dark:text-white/90">{{ $value($child->pendidikan) }}</dd></div>
                                </dl>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada data anak.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-4">
                @include('pages.employees.partials.detail-card', [
                    'title' => 'Agreement',
                    'items' => [
                        'Agreement Status' => $contract?->status_kontrak,
                        'Start Date' => $dateValue($contract?->start_date),
                        'End Date' => $dateValue($contract?->end_date),
                        'Status' => $contract?->status,
                    ],
                ])

                @include('pages.employees.partials.detail-card', [
                    'title' => 'BPJS & Tax',
                    'items' => [
                        'Nomor BPJS TK' => $bpjs?->nomor_ketenagakerjaan,
                        'Nomor BPJS Kesehatan' => $bpjs?->nomor_kesehatan,
                        'Nomor NPWP' => $tax?->no_npwp,
                        'PTKP Status' => $tax?->ptkp_status,
                    ],
                ])

                @include('pages.employees.partials.detail-card', [
                    'title' => 'Bank & Payroll',
                    'items' => [
                        'Nomor Rekening' => $bank?->no_rekening,
                        'Nama Bank' => $bank?->nama_bank,
                        'Nama di Rekening' => $bank?->nama_didalam_rek,
                        'Cabang Bank' => $bank?->cabang_bank,
                        'Gaji Pokok' => $money($payroll?->gaji_pokok),
                        'Total Tunjangan' => $money($payroll?->tunjangan),
                        'Uang Makan' => $money($payroll?->uang_makan),
                        'Uang Transport' => $money($payroll?->uang_transport),
                    ],
                ])

                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Employee Document</h3>
                    <div class="mt-5 space-y-3">
                        @foreach ([
                            'FOTO KARYAWAN' => $employee->foto_karyawan,
                            'KONTRAK KARYAWAN' => $contract?->file_kontrak,
                            'KTP KARYAWAN' => $documents->get('KTP')?->file_path,
                            'KK KARYAWAN' => $documents->get('KK')?->file_path,
                            'BUKU NIKAH' => $documents->get('BUKU_NIKAH')?->file_path,
                            'NPWP' => $documents->get('NPWP')?->file_path,
                            'BUKU REKENING' => $documents->get('BUKU_REKENING')?->file_path,
                            'CV / BIODATA' => $documents->get('CV_BIODATA')?->file_path,
                            'DOKUMEN NONAKTIF' => $employee->deactivation_document_path,
                        ] as $label => $path)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $label }}</span>
                                @if ($path)
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($path) }}" target="_blank" class="text-sm font-semibold text-brand-500 hover:text-brand-600">View</a>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Employee Movement History</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Promotion, mutation, and demotion records sorted by latest activity.</p>
                </div>
                <span class="inline-flex w-fit rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                    {{ $employeeHistories->count() }} records
                </span>
            </div>

            <div class="mt-6 overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-800">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($employeeHistories as $history)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4">
                                        <div class="flex flex-col gap-2">
                                            <x-ui.badge :color="$history['type'] === 'Promotion' ? 'success' : ($history['type'] === 'Demotion' ? 'error' : 'info')">
                                                {{ $history['type'] }}
                                            </x-ui.badge>
                                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $value($history['number']) }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $dateValue($history['date']) }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $value($history['from']) }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $value($history['to']) }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $value($history['status']) }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ $history['href'] }}" class="inline-flex items-center justify-center rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada history movement untuk employee ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </x-common.page-shell>
@endsection
