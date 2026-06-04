@extends('layouts.app')

@section('content')
    <x-common.page-shell
        title="{{ $department->department_name }}"
        description="Review the department master record and its linked organization data."
    >
        <x-slot:actions>
            <a href="{{ route('departments.edit', $department) }}"
                class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                Edit department
            </a>
            <a href="{{ route('departments.index') }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                Back to list
            </a>
        </x-slot:actions>

        <div class="grid gap-6 lg:grid-cols-3">
            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Company</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $department->company?->company_name ?? '-' }}</p>
            </div>

            <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Sub departments</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">
                    {{ $department->sub_departments_count }}
                </p>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Department details</h2>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Name</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white/90">{{ $department->department_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500 dark:text-gray-400">Parent company</dt>
                    <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white/90">{{ $department->company?->company_name ?? '-' }}</dd>
                </div>
            </dl>
        </div>
    </x-common.page-shell>
@endsection
