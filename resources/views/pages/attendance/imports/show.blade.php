@extends('layouts.app')

@php
    $statusClasses = [
        'present' => 'bg-success-50 text-success-700 dark:bg-success-500/10 dark:text-success-300',
        'late' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'early_leave' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'late_early_leave' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/10 dark:text-warning-300',
        'holiday' => 'bg-brand-50 text-brand-700 dark:bg-brand-500/10 dark:text-brand-300',
        'absent' => 'bg-error-50 text-error-700 dark:bg-error-500/10 dark:text-error-300',
        'unmatched' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
    ];
@endphp

@section('content')
    <x-common.page-shell title="Import Detail" description="{{ $import->file_name }}">
        <x-slot:actions>
            <a href="{{ route('attendance-imports.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back</a>
        </x-slot:actions>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60"><p class="text-sm text-gray-500 dark:text-gray-400">Rows</p><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $import->total_rows }}</p></div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60"><p class="text-sm text-gray-500 dark:text-gray-400">Matched</p><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $import->matched_rows }}</p></div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60"><p class="text-sm text-gray-500 dark:text-gray-400">Unmatched</p><p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $import->unmatched_rows }}</p></div>
        </div>

        <div class="mt-4 rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <p class="text-sm text-gray-500 dark:text-gray-400">Periode Upload</p>
            <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">
                @if ($import->period_start && $import->period_end)
                    {{ $import->period_start->format('d M Y') }} - {{ $import->period_end->format('d M Y') }}
                @else
                    -
                @endif
            </p>
        </div>

        <div class="mt-5 overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">ID Absen</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">In</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Out</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @foreach ($logs as $log)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $log->attendance_id }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $log->employee?->nama_karyawan ?? $log->fingerprint_name ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ optional($log->scan_date)->format('Y-m-d') ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $log->check_in ?? '-' }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $log->check_out ?? '-' }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2 py-1 text-xs font-semibold {{ $statusClasses[$log->status] ?? $statusClasses['unmatched'] }}">{{ str_replace('_', ' ', strtoupper($log->status)) }}</span></td>
                            <td class="px-5 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $log->notes ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $logs->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
