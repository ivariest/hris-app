@php
    $subDepartment = $subDepartment ?? null;
    $supportsCompanyHierarchy = $supportsCompanyHierarchy ?? false;
    $selectedCompanyId = old('company_id', $subDepartment->department?->company_id ?? '');
    $selectedDepartmentId = old('department_id', $subDepartment->department_id ?? '');
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
                        @error('company_id')
                            <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department <span class="text-error-500">*</span></label>
                        <select id="department_id" name="department_id" required disabled class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800 dark:disabled:bg-gray-800"></select>
                        @error('department_id')
                            <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @else
                <div>
                    <label for="department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department <span class="text-error-500">*</span></label>
                    <select id="department_id" name="department_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Select department</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected($selectedDepartmentId == $department->id)>{{ $department->department_name }}</option>
                        @endforeach
                    </select>
                    @error('department_id')
                        <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="mt-5">
                <label for="sub_department_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub department name <span class="text-error-500">*</span></label>
                <input type="text" id="sub_department_name" name="sub_department_name" value="{{ old('sub_department_name', $subDepartment->sub_department_name ?? '') }}" maxlength="100" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" placeholder="General Affairs" />
                @error('sub_department_name')
                    <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Save changes</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Sub department data will be used in position and employee forms.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Save sub department</button>
                <a href="{{ route('sub-departments.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
            </div>
        </div>
    </div>
</div>

@if ($supportsCompanyHierarchy)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const departments = @json($departmentHierarchyOptions ?? []);

                const companySelect = document.getElementById('company_id');
                const departmentSelect = document.getElementById('department_id');
                const selectedDepartmentId = @json($selectedDepartmentId);

                const renderDepartments = (companyId) => {
                    departmentSelect.innerHTML = '<option value="">Select department</option>';
                    departmentSelect.disabled = !companyId;

                    departments
                        .filter((department) => String(department.company_id) === String(companyId))
                        .forEach((department) => {
                            const option = document.createElement('option');
                            option.value = department.id;
                            option.textContent = department.name;
                            if (String(selectedDepartmentId) === String(department.id)) {
                                option.selected = true;
                            }
                            departmentSelect.appendChild(option);
                        });
                };

                companySelect?.addEventListener('change', (event) => {
                    renderDepartments(event.target.value);
                });

                if (companySelect && companySelect.value) {
                    renderDepartments(companySelect.value);
                } else {
                    departmentSelect.innerHTML = '<option value="">Select department</option>';
                    departmentSelect.disabled = true;
                }
            });
        </script>
    @endpush
@endif
