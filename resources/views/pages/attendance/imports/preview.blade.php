@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Preview Import Fingerprint" description="{{ $fileName }}">
        <x-slot:actions>
            <a href="{{ route('attendance-imports.create') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Upload Ulang</a>
        </x-slot:actions>

        <div class="mb-5 rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <p class="text-sm text-gray-500 dark:text-gray-400">Periode Upload</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">{{ \Carbon\Carbon::parse($periodStart)->format('d M Y') }} - {{ \Carbon\Carbon::parse($periodEnd)->format('d M Y') }}</p>
        </div>

        <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Rows</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $summary['total_rows'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Valid</p>
                <p class="mt-2 text-2xl font-semibold text-success-700 dark:text-success-300">{{ $summary['valid_date_rows'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Tanggal Bermasalah</p>
                <p class="mt-2 text-2xl font-semibold text-error-600 dark:text-error-300">{{ $summary['invalid_date_rows'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">ID Match</p>
                <p class="mt-2 text-2xl font-semibold text-success-700 dark:text-success-300">{{ $summary['matched_rows'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">ID Tidak Match</p>
                <p class="mt-2 text-2xl font-semibold text-warning-700 dark:text-warning-300">{{ $summary['unmatched_rows'] }}</p>
            </div>
        </div>

        <form method="POST" action="{{ route('attendance-imports.store') }}" class="mb-5 flex flex-wrap items-center gap-3 rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            @csrf
            <input type="hidden" name="period_start" value="{{ $periodStart }}">
            <input type="hidden" name="period_end" value="{{ $periodEnd }}">
            <input type="hidden" name="file_path" value="{{ $filePath }}">
            <input type="hidden" name="file_name" value="{{ $fileName }}">
            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Confirm Import</button>
            <a href="{{ route('attendance-imports.create') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
        </form>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Sample Preview</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Row</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">ID Absen</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Finger</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Raw Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal Dibaca</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Masuk</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pulang</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($sampleRows as $row)
                            <tr class="{{ empty($row['scan_date']) ? 'bg-error-50/60 dark:bg-error-500/5' : 'hover:bg-gray-50/80 dark:hover:bg-white/[0.02]' }}">
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['row_number'] }}</td>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $row['attendance_id'] }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['fingerprint_name'] ?? '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['raw_scan_date'] ?: '-' }}</td>
                                <td class="px-5 py-4 text-sm {{ empty($row['scan_date']) ? 'font-semibold text-error-600 dark:text-error-300' : 'text-gray-700 dark:text-gray-300' }}">{{ $row['scan_date'] ?: 'Tidak terbaca / di luar periode' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['check_in'] ? substr($row['check_in'], 0, 5) : '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['check_out'] ? substr($row['check_out'], 0, 5) : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-common.page-shell>
@endsection
