@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Deactivate Employees" description="Deactivate employees without deleting their master data.">
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('employee-deactivations.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search employee ID, name, or NIK" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>

                <div class="lg:col-span-3">
                    <label for="company_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company</label>
                    <select id="company_id" name="company_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All companies</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected($companyId == $company->id)>{{ $company->company_name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status</label>
                    <select id="status" name="status" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>

                <div class="flex items-end lg:col-span-2 lg:justify-end">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600 lg:w-auto lg:min-w-[110px]">Go</button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Company</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assignment</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Reason</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($employees as $employee)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->nik_karyawan ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $employee->company?->company_name ?? '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $employee->employeePosition?->position?->position_name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->employeePosition?->position?->subDepartment?->department?->department_name ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <x-ui.badge :color="$employee->status_karyawan === 'active' ? 'success' : 'warning'">
                                        {{ ucfirst($employee->status_karyawan) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div>{{ $employee->deactivation_reason ? str_replace('_', ' ', $employee->deactivation_reason) : '-' }}</div>
                                    @if ($employee->deactivation_document_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($employee->deactivation_document_path) }}" target="_blank" class="mt-1 inline-flex text-xs font-semibold text-brand-500 hover:text-brand-600">View document</a>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('employees.show', $employee) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a>
                                        @if ($employee->status_karyawan === 'active')
                                            <a href="{{ route('employee-deactivations.edit', $employee) }}" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">Deactivate</a>
                                        @else
                                            <span class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-400 dark:border-gray-800">Inactive</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No employees found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $employees->links() }}
            </div>
        </div>
    </x-common.page-shell>
@endsection
