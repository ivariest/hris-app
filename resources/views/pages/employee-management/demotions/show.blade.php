@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Demotion Detail" description="Review the demotion record, supporting document, and level change.">
        <x-slot:actions>
            <a href="{{ route('employee-demotions.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                Back to list
            </a>
        </x-slot:actions>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                <div>
                    <p class="text-sm font-medium text-brand-600 dark:text-brand-300">{{ $demotion->demotion_number ?: 'DEMOTION RECORD' }}</p>
                    <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $demotion->employee?->nama_karyawan ?? '-' }}</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $demotion->employee?->nik_karyawan ?? '-' }}{{ $demotion->employee?->company?->company_name ? ' | '.$demotion->employee->company->company_name : '' }}</p>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jabatan / Pangkat Lama</p>
                        <div class="mt-3">
                            <p class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $demotion->oldPosition?->position_name ?? '-' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $demotion->oldLevel?->level_name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-brand-200 bg-brand-50/50 p-5 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">Jabatan / Pangkat Baru</p>
                        <div class="mt-3">
                            <p class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $demotion->newPosition?->position_name ?? '-' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $demotion->newLevel?->level_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Mulai Berlaku</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $demotion->effective_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Posisi Aktif Employee Saat Ini</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">
                            {{ $demotion->employee?->employeePosition?->position?->position_name ?? '-' }}
                            @if ($demotion->employee?->employeePosition?->level?->level_name)
                                | {{ $demotion->employee->employeePosition->level->level_name }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-8">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Keterangan</p>
                    <div class="mt-2 rounded-2xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-300">
                        {{ $demotion->notes ?: 'Tidak ada catatan tambahan.' }}
                    </div>
                </div>
            </section>

            <aside class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Documents</h3>
                <div class="mt-5 space-y-3">
                    <div class="rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                        <p class="text-sm font-medium text-gray-900 dark:text-white/90">Form Demotion</p>
                        @if ($demotion->demotion_form_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($demotion->demotion_form_path) }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">View file</a>
                        @else
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada file.</p>
                        @endif
                    </div>
                </div>
            </aside>
        </div>
    </x-common.page-shell>
@endsection
