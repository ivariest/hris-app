@extends('layouts.app')
@section('content')
    <x-common.page-shell title="Positions" description="Manage job positions under sub departments.">
        <x-slot:actions><a href="{{ route('positions.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">New position</a></x-slot:actions>
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('positions.index') }}" class="grid gap-4 lg:grid-cols-12" id="position-filter-form">
                <div class="lg:col-span-3">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search position name" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
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
                    <label for="department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department</label>
                    <select id="department_id" name="department_id" disabled class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:bg-gray-800">
                        <option value="">Select company first</option>
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="sub_department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub department</label>
                    <select id="sub_department_id" name="sub_department_id" disabled class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:bg-gray-800">
                        <option value="">Select department first</option>
                    </select>
                </div>

                <div class="flex items-end lg:col-span-2 lg:justify-end">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600 lg:w-auto lg:min-w-[110px]">Go</button>
                </div>
            </form>
        </div>
        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/80"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sub department</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($positions as $position)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $position->position_name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $position->subDepartment?->sub_department_name }}</td>
                            <td class="px-5 py-4"><div class="flex items-center justify-end gap-2"><a href="{{ route('positions.show', $position) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a><a href="{{ route('positions.edit', $position) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a><form method="POST" action="{{ route('positions.destroy', $position) }}" onsubmit="return confirm('Delete this position?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button></form></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No positions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $positions->links() }}</div>
        </div>
    </x-common.page-shell>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const companies = @json($companyOptions);
                const departments = @json($departmentOptions);
                const subDepartments = @json($subDepartmentOptions);

                const companySelect = document.getElementById('company_id');
                const departmentSelect = document.getElementById('department_id');
                const subDepartmentSelect = document.getElementById('sub_department_id');

                const selectedCompanyId = @json($companyId);
                const selectedDepartmentId = @json($departmentId);
                const selectedSubDepartmentId = @json($subDepartmentId);

                const setOptions = (select, items, placeholder, labelBuilder) => {
                    select.innerHTML = '';
                    const placeholderOption = document.createElement('option');
                    placeholderOption.value = '';
                    placeholderOption.textContent = placeholder;
                    select.appendChild(placeholderOption);

                    items.forEach((item) => {
                        const option = document.createElement('option');
                        option.value = item.id;
                        option.textContent = labelBuilder(item);
                        select.appendChild(option);
                    });
                };

                const renderDepartments = (companyId, preselectedDepartmentId = null, preselectedSubDepartmentId = null) => {
                    const filteredDepartments = departments.filter((department) => String(department.company_id) === String(companyId));
                    departmentSelect.disabled = !companyId;
                    subDepartmentSelect.disabled = true;
                    setOptions(departmentSelect, filteredDepartments, companyId ? 'All departments' : 'Select company first', (department) => department.department_name);
                    setOptions(subDepartmentSelect, [], companyId ? 'Select department first' : 'Select company first', () => '');

                    if (companyId && preselectedDepartmentId) {
                        const departmentExists = filteredDepartments.some((department) => String(department.id) === String(preselectedDepartmentId));
                        if (departmentExists) {
                            departmentSelect.value = preselectedDepartmentId;
                            renderSubDepartments(preselectedDepartmentId, preselectedSubDepartmentId);
                        }
                    }
                };

                const renderSubDepartments = (departmentId, preselectedSubDepartmentId = null) => {
                    const filteredSubDepartments = subDepartments.filter((subDepartment) => String(subDepartment.department_id) === String(departmentId));
                    subDepartmentSelect.disabled = !departmentId;
                    setOptions(subDepartmentSelect, filteredSubDepartments, departmentId ? 'All sub departments' : 'Select department first', (subDepartment) => subDepartment.sub_department_name);

                    if (departmentId && preselectedSubDepartmentId) {
                        const subDepartmentExists = filteredSubDepartments.some((subDepartment) => String(subDepartment.id) === String(preselectedSubDepartmentId));
                        if (subDepartmentExists) {
                            subDepartmentSelect.value = preselectedSubDepartmentId;
                        }
                    }
                };

                companySelect.addEventListener('change', (event) => {
                    const companyId = event.target.value;
                    renderDepartments(companyId);
                });

                departmentSelect.addEventListener('change', (event) => {
                    const departmentId = event.target.value;
                    renderSubDepartments(departmentId);
                });

                if (selectedCompanyId) {
                    renderDepartments(selectedCompanyId, selectedDepartmentId, selectedSubDepartmentId);
                } else {
                    renderDepartments('');
                }
            });
        </script>
    @endpush
@endsection
