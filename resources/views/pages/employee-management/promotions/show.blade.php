@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Promotion Detail" description="Review the promotion record, supporting documents, and position changes.">
        <x-slot:actions>
            @if (!in_array($promotion->record_status, ['CLOSE', 'REVERTED']))
                <a href="{{ route('employee-promotions.edit', $promotion) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                    Edit
                </a>
                @if ($promotion->promotion_type === 'PEJABAT_SEMENTARA' && $promotion->record_status !== 'REVERTED')
                    <a href="{{ route('employee-promotions.manage', $promotion) }}" class="inline-flex items-center justify-center rounded-xl border border-brand-200 px-4 py-3 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/10">
                        Update
                    </a>
                @endif
                <form method="POST" action="{{ route('employee-promotions.destroy', $promotion) }}" onsubmit="return confirm('Delete this promotion and restore previous position?');" class="inline-flex">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl border border-error-200 px-4 py-3 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">
                        Delete
                    </button>
                </form>
            @endif
            <a href="{{ route('employee-promotions.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                Back to list
            </a>
        </x-slot:actions>

        <div class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <p class="text-sm font-medium text-brand-600 dark:text-brand-300">{{ $promotion->promotion_number ?: 'PROMOTION RECORD' }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $promotion->employee?->nama_karyawan ?? '-' }}</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $promotion->employee?->nik_karyawan ?? '-' }}{{ $promotion->employee?->company?->company_name ? ' | '.$promotion->employee->company->company_name : '' }}</p>
                    </div>
                    <x-ui.badge color="info">{{ str_replace('_', ' ', $promotion->promotion_type) }}</x-ui.badge>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-800">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jabatan / Pangkat Lama</p>
                        <div class="mt-3">
                            <p class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $promotion->oldPosition?->position_name ?? '-' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $promotion->oldLevel?->level_name ?? '-' }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-brand-200 bg-brand-50/50 p-5 dark:border-brand-500/20 dark:bg-brand-500/10">
                        <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">Jabatan / Pangkat Baru</p>
                        <div class="mt-3">
                            <p class="text-base font-semibold text-gray-900 dark:text-white/90">{{ $promotion->newPosition?->position_name ?? '-' }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $promotion->newLevel?->level_name ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Mulai Berlaku</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $promotion->start_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Selesai</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $promotion->end_date ? $promotion->end_date->format('d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Record</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $promotion->record_status }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Posisi Aktif Employee Saat Ini</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">
                            {{ $promotion->employee?->employeePosition?->position?->position_name ?? '-' }}
                            @if ($promotion->employee?->employeePosition?->level?->level_name)
                                | {{ $promotion->employee->employeePosition->level->level_name }}
                            @endif
                            @if ($promotion->promotion_type === 'PEJABAT_SEMENTARA' && $promotion->record_status !== 'REVERTED')
                                | PJS
                            @endif
                        </p>
                    </div>
                </div>

                <div class="mt-8">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Keterangan</p>
                    <div class="mt-2 rounded-2xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-300">
                        {{ $promotion->notes ?: 'Tidak ada catatan tambahan.' }}
                    </div>
                </div>
            </section>

            <aside class="space-y-6">
                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Documents</h3>
                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-sm font-medium text-gray-900 dark:text-white/90">Form Promosi</p>
                            @if ($promotion->promotion_form_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($promotion->promotion_form_path) }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">View file</a>
                            @else
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada file.</p>
                            @endif
                        </div>

                        <div class="rounded-2xl border border-gray-200 px-4 py-3 dark:border-gray-800">
                            <p class="text-sm font-medium text-gray-900 dark:text-white/90">SK Pengangkatan</p>
                            @if ($promotion->appointment_letter_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($promotion->appointment_letter_path) }}" target="_blank" class="mt-2 inline-flex text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-300 dark:hover:text-brand-200">View file</a>
                            @else
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada file.</p>
                            @endif
                        </div>
                    </div>
                </section>
            </aside>
        </div>
    </x-common.page-shell>
@endsection
