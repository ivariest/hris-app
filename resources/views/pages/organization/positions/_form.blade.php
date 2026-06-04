@php
    $position = $position ?? null;
    $supportsCompanyHierarchy = $supportsCompanyHierarchy ?? false;
    $selectedCompanyId = old('company_id', $position->subDepartment?->department?->company_id ?? '');
    $selectedDepartmentId = old('department_id', $position->subDepartment?->department_id ?? '');
    $selectedSubDepartmentId = old('sub_department_id', $position->sub_department_id ?? '');
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            @if ($supportsCompanyHierarchy)
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="company_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company <span class="text-error-500">*</span></label>
                        <select id="company_id" name="company_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Select company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}" @selected($selectedCompanyId == $company->id)>{{ $company->company_name }}</option>
                            @endforeach
                        </select>
                        @error('company_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department <span class="text-error-500">*</span></label>
                        <select id="department_id" name="department_id" required disabled class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:bg-gray-800"></select>
                        @error('department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-5">
                    <label for="sub_department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub department <span class="text-error-500">*</span></label>
                    <select id="sub_department_id" name="sub_department_id" required disabled class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:bg-gray-800"></select>
                    @error('sub_department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            @else
                <div>
                    <label for="sub_department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub department <span class="text-error-500">*</span></label>
                    <select id="sub_department_id" name="sub_department_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Select sub department</option>
                        @foreach ($subDepartments as $subDepartment)
                            <option value="{{ $subDepartment->id }}" @selected($selectedSubDepartmentId == $subDepartment->id)>{{ $subDepartment->sub_department_name }}</option>
                        @endforeach
                    </select>
                    @error('sub_department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            @endif

            <div class="mt-5">
                <label for="position_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Position name <span class="text-error-500">*</span></label>
                <input type="text" id="position_name" name="position_name" value="{{ old('position_name', $position->position_name ?? '') }}" maxlength="100" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" placeholder="HR Staff" />
                @error('position_name')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Save changes</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Positions now stay independent from level. Level is selected when assigning an employee.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Save position</button>
                <a href="{{ route('positions.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
            </div>
        </div>
    </div>
</div>

@if ($supportsCompanyHierarchy)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const subDepartments = @json($subDepartmentHierarchyOptions ?? []);

                const companySelect = document.getElementById('company_id');
                const departmentSelect = document.getElementById('department_id');
                const subDepartmentSelect = document.getElementById('sub_department_id');
                const selectedDepartmentId = @json($selectedDepartmentId);
                const selectedSubDepartmentId = @json($selectedSubDepartmentId);

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
                    const filteredDepartments = subDepartments.filter((item) => String(item.company_id) === String(companyId));
                    const uniqueDepartments = [];

                    filteredDepartments.forEach((item) => {
                        if (!uniqueDepartments.some((existing) => String(existing.id) === String(item.department_id))) {
                            uniqueDepartments.push({
                                id: item.department_id,
                                label: item.department_name,
                            });
                        }
                    });

                    departmentSelect.disabled = !companyId;
                    subDepartmentSelect.disabled = true;
                    setOptions(departmentSelect, uniqueDepartments, companyId ? 'All departments' : 'Select company first', (department) => department.label);
                    setOptions(subDepartmentSelect, [], companyId ? 'Select department first' : 'Select company first', () => '');

                    if (companyId && preselectedDepartmentId) {
                        const departmentExists = uniqueDepartments.some((department) => String(department.id) === String(preselectedDepartmentId));
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

                companySelect?.addEventListener('change', (event) => {
                    renderDepartments(event.target.value);
                });

                departmentSelect?.addEventListener('change', (event) => {
                    renderSubDepartments(event.target.value);
                });

                if (companySelect && companySelect.value) {
                    renderDepartments(companySelect.value, selectedDepartmentId, selectedSubDepartmentId);
                } else {
                    departmentSelect.innerHTML = '<option value="">Select department</option>';
                    departmentSelect.disabled = true;
                    subDepartmentSelect.innerHTML = '<option value="">Select sub department</option>';
                    subDepartmentSelect.disabled = true;
                }
            });
        </script>
    @endpush
@endif
