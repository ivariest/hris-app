@extends('layouts.app')

@section('content')
    <x-common.page-shell
        title="Departments"
        description="Manage master department data used by organization and employee forms."
    >
        <x-slot:actions>
            <a href="{{ route('departments.create') }}"
                class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                New department
            </a>
        </x-slot:actions>

        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('departments.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-6">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Search
                    </label>
                    <input
                        type="text"
                        id="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search department name"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                    />
                </div>

                <div class="lg:col-span-4">
                    <label for="company_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Company
                    </label>
                    <select
                        id="company_id"
                        name="company_id"
                        class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                    >
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected($companyId == $company->id)>{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3 lg:col-span-2">
                    <button type="submit"
                        class="inline-flex h-11 flex-1 items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                        Filter
                    </button>
                    <a href="{{ route('departments.index') }}"
                        class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="flex flex-col gap-3 border-b border-gray-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white/90">Department list</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ $departments->total() }} record(s) found
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sub Departments</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($departments as $department)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $department->company?->company_name ?? '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $department->department_name }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $department->sub_departments_count }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('departments.show', $department) }}"
                                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                                            View
                                        </a>
                                        <a href="{{ route('departments.edit', $department) }}"
                                            class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('departments.destroy', $department) }}"
                                            onsubmit="return confirm('Delete this department?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-16 text-center">
                                    <div class="mx-auto max-w-md">
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">No departments found</h3>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            Try a different search or create the first department.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $departments->links() }}
            </div>
        </div>
    </x-common.page-shell>
@endsection
