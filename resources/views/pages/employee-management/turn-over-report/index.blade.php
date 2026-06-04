@extends('layouts.app')

@php
    $dateValue = fn ($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d M Y') : '-';
@endphp

@section('content')
    <x-common.page-shell title="Turn Over Report" description="View registered and inactive employees by selected date range.">
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('turn-over-report.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label for="start_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Awal</label>
                    <input type="text" id="start_date" name="start_date" value="{{ $startDate }}" x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>

                <div class="lg:col-span-5">
                    <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Akhir</label>
                    <input type="text" id="end_date" name="end_date" value="{{ $endDate }}" x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>

                <div class="flex items-end lg:col-span-2 lg:justify-end">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600 lg:w-auto lg:min-w-[120px]">Filter</button>
                </div>
            </form>
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $dateValue($startDate) }} - {{ $dateValue($endDate) }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan Masuk</p>
                <p class="mt-2 text-3xl font-semibold text-success-600 dark:text-success-400">{{ $registeredEmployees->count() }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Karyawan Keluar / Inactive</p>
                <p class="mt-2 text-3xl font-semibold text-error-600 dark:text-error-400">{{ $inactiveEmployees->count() }}</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Karyawan Baru Terdaftar</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assignment</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Registered</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($registeredEmployees as $employee)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <a href="{{ route('employees.show', $employee) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-white/90">{{ $employee->nama_karyawan }}</a>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->nik_karyawan ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <div class="font-medium text-gray-900 dark:text-white/90">{{ $employee->employeePosition?->position?->position_name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->employeePosition?->position?->subDepartment?->department?->department_name ?? '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $employee->created_at?->format('d M Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada karyawan baru pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Karyawan Keluar / Inactive</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reason</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Inactive Date</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($inactiveEmployees as $employee)
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <a href="{{ route('employees.show', $employee) }}" class="font-medium text-gray-900 hover:text-brand-600 dark:text-white/90">{{ $employee->nama_karyawan }}</a>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->nik_karyawan ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $employee->deactivation_reason ? str_replace('_', ' ', $employee->deactivation_reason) : '-' }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $employee->deactivated_at?->format('d M Y') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada karyawan inactive pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </x-common.page-shell>
@endsection
