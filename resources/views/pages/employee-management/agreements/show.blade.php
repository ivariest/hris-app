@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Employee Agreement Detail" description="Review agreement detail and linked recruitment request.">
        <x-slot:actions>
            <a href="{{ route('employee-agreements.print', $agreement) }}" target="_blank" class="inline-flex items-center justify-center rounded-xl border border-brand-200 px-4 py-3 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/10">Create PDF</a>
            <a href="{{ route('employee-agreements.edit', $agreement) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Edit</a>
            <a href="{{ route('employee-agreements.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back</a>
        </x-slot:actions>

        <div class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-brand-600 dark:text-brand-300">{{ $agreement->agreement_number }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $agreement->employee_name }}</h2>
                    </div>
                    <x-ui.badge color="info">{{ $agreement->agreement_status }}</x-ui.badge>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Request</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->request?->request_number ?? '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Department</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->department ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tempat Lahir</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->place_of_birth ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Lahir</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->date_of_birth?->format('d F Y') ?? '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Gender / Religion</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ collect([$agreement->gender, $agreement->religion])->filter()->implode(' / ') ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">KTP</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->id_card_number ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Phone</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->phone_number ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $agreement->email ?: '-' }}</p></div>
                </div>
            </section>

            <section class="space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Alamat</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $agreement->address ?: '-' }}</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Periode Kontrak</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $agreement->contract_start_date?->format('d F Y') ?? '-' }} - {{ $agreement->contract_end_date?->format('d F Y') ?? '-' }}</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Komponen Gaji</h3>
                    <div class="mt-4 space-y-3 text-sm text-gray-600 dark:text-gray-300">
                        <p>Gaji Pokok: {{ filled($agreement->base_salary) ? 'Rp '.number_format((float) $agreement->base_salary, 0, ',', '.') : '-' }}</p>
                        <p>Tunjangan: {{ filled($agreement->allowance) ? 'Rp '.number_format((float) $agreement->allowance, 0, ',', '.') : '-' }}</p>
                        <p>Uang Makan: {{ filled($agreement->meal_allowance) ? 'Rp '.number_format((float) $agreement->meal_allowance, 0, ',', '.') : '-' }}</p>
                    </div>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Catatan</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $agreement->notes ?: '-' }}</p>
                </div>
            </section>
        </div>
    </x-common.page-shell>
@endsection
