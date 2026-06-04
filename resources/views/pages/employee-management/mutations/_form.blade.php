@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
        Ada beberapa field yang perlu dicek lagi.
    </div>
@endif

<form
    method="POST"
    action="{{ $formAction }}"
    enctype="multipart/form-data"
    x-data="mutationForm({
        employees: {{ \Illuminate\Support\Js::from($employeeOptions) }},
        departments: {{ \Illuminate\Support\Js::from($departments) }},
        subDepartments: {{ \Illuminate\Support\Js::from($subDepartments) }},
        positions: {{ \Illuminate\Support\Js::from($positions) }},
        selectedEmployeeId: '{{ old('employee_id') }}',
        selectedDepartmentId: '{{ old('department_id') }}',
        selectedSubDepartmentId: '{{ old('sub_department_id') }}',
        selectedPositionId: '{{ old('new_position_id') }}',
    })"
    x-init="init()"
    class="space-y-8"
>
    @csrf

    <section class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="employee_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Employee <span class="text-error-500">*</span></label>
            <select id="employee_id" name="employee_id" x-model="selectedEmployeeId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">Pilih employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('employee_id') == $employee->id)>{{ strtoupper($employee->nama_karyawan) }} - {{ $employee->nik_karyawan }}</option>
                @endforeach
            </select>
            @error('employee_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="mutation_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Form Mutation</label>
            <input type="text" id="mutation_number" value="{{ $nextMutationNumber }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            @error('mutation_number')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="mt-4 mb-4 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-gray-900/50">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Current Assignment</h3>
        </div>
        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department Saat Ini</label>
                <input type="text" :value="selectedEmployee.current_department_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub Department Saat Ini</label>
                <input type="text" :value="selectedEmployee.current_sub_department_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Saat Ini</label>
                <input type="text" :value="selectedEmployee.current_position_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>
        </div>
    </section>

    <section class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department Baru <span class="text-error-500">*</span></label>
            <select id="department_id" name="department_id" x-model="selectedDepartmentId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedEmployee.company_id">
                <option value="">Pilih department baru</option>
                <template x-for="department in filteredDepartments()" :key="department.id">
                    <option :value="String(department.id)" x-text="String(department.department_name).toUpperCase()"></option>
                </template>
            </select>
            @error('department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="sub_department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub Department Baru <span class="text-error-500">*</span></label>
            <select id="sub_department_id" name="sub_department_id" x-model="selectedSubDepartmentId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedDepartmentId">
                <option value="">Pilih sub department baru</option>
                <template x-for="subDepartment in filteredSubDepartments()" :key="subDepartment.id">
                    <option :value="String(subDepartment.id)" x-text="String(subDepartment.sub_department_name).toUpperCase()"></option>
                </template>
            </select>
            @error('sub_department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="new_position_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Baru <span class="text-error-500">*</span></label>
            <select id="new_position_id" name="new_position_id" x-model="selectedPositionId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedSubDepartmentId">
                <option value="">Pilih jabatan baru</option>
                <template x-for="position in filteredPositions()" :key="position.id">
                    <option :value="String(position.id)" x-text="String(position.position_name).toUpperCase()"></option>
                </template>
            </select>
            @error('new_position_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="effective_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Mulai Berlaku <span class="text-error-500">*</span></label>
            <input type="text" id="effective_date" name="effective_date" value="{{ old('effective_date') }}" required x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" placeholder="Pilih tanggal mulai" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('effective_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="mutation_letter" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload SK Mutasi</label>
            <input type="file" id="mutation_letter" name="mutation_letter" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
            @error('mutation_letter')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div class="sm:col-span-2">
            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Keterangan</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Tambahkan catatan mutation jika diperlukan" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('notes') }}</textarea>
            @error('notes')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
        <a href="{{ route('employee-mutations.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
    <script>
        function mutationForm(config) {
            return {
                employees: config.employees || [],
                departments: config.departments || [],
                subDepartments: config.subDepartments || [],
                positions: config.positions || [],
                selectedEmployeeId: config.selectedEmployeeId || '',
                selectedDepartmentId: config.selectedDepartmentId || '',
                selectedSubDepartmentId: config.selectedSubDepartmentId || '',
                selectedPositionId: config.selectedPositionId || '',
                selectedEmployee: {
                    company_id: '',
                    current_department_name: '',
                    current_sub_department_name: '',
                    current_position_name: '',
                },
                init() {
                    this.syncSelectedEmployee();
                    this.$watch('selectedEmployeeId', () => {
                        this.syncSelectedEmployee();
                        this.selectedDepartmentId = '';
                        this.selectedSubDepartmentId = '';
                        this.selectedPositionId = '';
                    });
                    this.$watch('selectedDepartmentId', () => {
                        this.selectedSubDepartmentId = '';
                        this.selectedPositionId = '';
                    });
                    this.$watch('selectedSubDepartmentId', () => {
                        this.selectedPositionId = '';
                    });
                },
                syncSelectedEmployee() {
                    const found = this.employees.find((employee) => String(employee.id) === String(this.selectedEmployeeId));
                    this.selectedEmployee = found || {
                        company_id: '',
                        current_department_name: '',
                        current_sub_department_name: '',
                        current_position_name: '',
                    };
                },
                filteredDepartments() {
                    return this.departments.filter((department) => String(department.company_id) === String(this.selectedEmployee.company_id));
                },
                filteredSubDepartments() {
                    return this.subDepartments.filter((subDepartment) => String(subDepartment.department_id) === String(this.selectedDepartmentId));
                },
                filteredPositions() {
                    return this.positions.filter((position) => {
                        return String(position.company_id) === String(this.selectedEmployee.company_id) &&
                            String(position.department_id) === String(this.selectedDepartmentId) &&
                            String(position.sub_department_id) === String(this.selectedSubDepartmentId);
                    });
                },
            };
        }
    </script>
@endpush
