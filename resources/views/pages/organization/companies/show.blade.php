@extends('layouts.app')

@section('content')
    <x-common.page-shell
        title="{{ $company->company_name }}"
        description="Review the company master record."
    >
        <x-slot:actions>
            <a href="{{ route('companies.edit', $company) }}"
                class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                Edit company
            </a>
            <a href="{{ route('companies.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                Back to list
            </a>
        </x-slot:actions>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Departments</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $company->departments_count ?? 0 }}</p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 lg:col-span-2">
                <p class="text-sm text-gray-500 dark:text-gray-400">Company address</p>
                <p class="mt-2 text-sm leading-6 text-gray-700 dark:text-gray-300">
                    {{ $company->company_address ?: 'No address provided.' }}
                </p>
            </div>
        </div>
    </x-common.page-shell>
@endsection
