@extends('layouts.app')

@php
    $yearOptions = range(now()->year - 2, now()->year + 1);
    $selectedCompanyNames = $companies->whereIn('id', $companyIds)->pluck('company_name')->values();
    $selectedDepartmentNames = $departments->whereIn('id', $departmentIds)->pluck('department_name')->values();
    $selectedEmployeeNames = $employeeOptions->whereIn('id', $employeeIds)->pluck('nama_karyawan')->values();
    $departmentOptions = $departments->map(fn ($department) => [
        'id' => (int) $department->id,
        'company_id' => (int) $department->company_id,
        'name' => $department->department_name,
    ])->values();
    $employeeSelectOptions = $employeeOptions->map(fn ($employee) => [
        'id' => (int) $employee->id,
        'company_id' => (int) $employee->company_id,
        'department_id' => (int) ($employee->employeePosition?->position?->subDepartment?->department_id ?? 0),
        'name' => $employee->nama_karyawan,
        'attendance_id' => $employee->attendance_id,
    ])->values();
@endphp

@section('content')
    <x-common.page-shell title="Annual Leave" description="Kelola saldo cuti tahunan dan cuti bersama.">
        <x-slot:actions>
            <a href="{{ route('annual-leaves.collectives.index', ['year' => $year]) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Cuti Bersama</a>
        </x-slot:actions>

        <form
            method="GET"
            x-data="{
                companyOpen: false,
                departmentOpen: false,
                employeeOpen: false,
                selectedCompanyIds: @js(array_map('strval', $companyIds)),
                selectedDepartmentIds: @js(array_map('strval', $departmentIds)),
                selectedEmployeeIds: @js(array_map('strval', $employeeIds)),
                companies: @js($companies->map(fn ($company) => ['id' => (int) $company->id, 'name' => $company->company_name])->values()),
                departments: @js($departmentOptions),
                employees: @js($employeeSelectOptions),
                optionMatches(ids, id) { return ids.length === 0 || ids.includes(String(id)); },
                departmentAllowed(department) { return this.optionMatches(this.selectedCompanyIds, department.company_id); },
                employeeAllowed(employee) {
                    return this.optionMatches(this.selectedCompanyIds, employee.company_id)
                        && (this.selectedDepartmentIds.length === 0 || this.selectedDepartmentIds.includes(String(employee.department_id)));
                },
                pruneDepartments() {
                    this.selectedDepartmentIds = this.selectedDepartmentIds.filter((id) => this.departments.some((department) => String(department.id) === id && this.departmentAllowed(department)));
                    this.pruneEmployees();
                },
                pruneEmployees() {
                    this.selectedEmployeeIds = this.selectedEmployeeIds.filter((id) => this.employees.some((employee) => String(employee.id) === id && this.employeeAllowed(employee)));
                },
                label(items, selectedIds, emptyLabel, multiLabel) {
                    const selected = items.filter((item) => selectedIds.includes(String(item.id)));
                    if (selected.length === 0) return emptyLabel;
                    if (selected.length === 1) return selected[0].name;
                    return `${selected.length} ${multiLabel} dipilih`;
                }
            }"
            class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60"
        >
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="year" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun Saldo</label>
                    <select id="year" name="year" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($yearOptions as $yearOption)
                            <option value="{{ $yearOption }}" @selected((int) $year === (int) $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company</label>
                    <button type="button" @click="companyOpen = !companyOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <span class="truncate" x-text="label(companies, selectedCompanyIds, 'Semua company', 'company')">{{ $selectedCompanyNames->isEmpty() ? 'Semua company' : ($selectedCompanyNames->count() === 1 ? $selectedCompanyNames->first() : $selectedCompanyNames->count().' company dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="companyOpen" x-transition @click.outside="companyOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="max-h-56 overflow-y-auto p-2">
                            @foreach ($companies as $company)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300">
                                    <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" x-model="selectedCompanyIds" @change="pruneDepartments()" class="size-4 rounded border-gray-300 text-brand-500">
                                    <span class="truncate">{{ $company->company_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department</label>
                    <button type="button" @click="departmentOpen = !departmentOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <span class="truncate" x-text="label(departments, selectedDepartmentIds, 'Semua department', 'department')">{{ $selectedDepartmentNames->isEmpty() ? 'Semua department' : ($selectedDepartmentNames->count() === 1 ? $selectedDepartmentNames->first() : $selectedDepartmentNames->count().' department dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="departmentOpen" x-transition @click.outside="departmentOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="max-h-56 overflow-y-auto p-2">
                            @foreach ($departmentOptions as $department)
                                <label x-show="departmentAllowed({ id: {{ $department['id'] }}, company_id: {{ $department['company_id'] }} })" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300">
                                    <input type="checkbox" name="department_ids[]" value="{{ $department['id'] }}" x-model="selectedDepartmentIds" @change="pruneEmployees()" class="size-4 rounded border-gray-300 text-brand-500">
                                    <span class="truncate">{{ $department['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Karyawan</label>
                    <button type="button" @click="employeeOpen = !employeeOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <span class="truncate" x-text="label(employees, selectedEmployeeIds, 'Semua karyawan', 'karyawan')">{{ $selectedEmployeeNames->isEmpty() ? 'Semua karyawan' : ($selectedEmployeeNames->count() === 1 ? $selectedEmployeeNames->first() : $selectedEmployeeNames->count().' karyawan dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="employeeOpen" x-transition @click.outside="employeeOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="max-h-56 overflow-y-auto p-2">
                            @foreach ($employeeSelectOptions as $employee)
                                <label x-show="employeeAllowed({ id: {{ $employee['id'] }}, company_id: {{ $employee['company_id'] }}, department_id: {{ $employee['department_id'] }} })" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee['id'] }}" x-model="selectedEmployeeIds" class="size-4 rounded border-gray-300 text-brand-500">
                                    <span class="truncate">{{ $employee['name'] }} @if($employee['attendance_id']) - {{ $employee['attendance_id'] }} @endif</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 sm:flex-row sm:justify-end">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">Filter</button>
                <a href="{{ route('annual-leaves.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Reset</a>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Saldo Annual Leave {{ $year }}</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Karyawan</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Department</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Hak</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Terpakai</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sisa</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Hutang Aktif</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @foreach ($summaryRows as $row)
                            @php
                                $employee = $row['employee'];
                                $department = $employee->employeePosition?->position?->subDepartment?->department?->department_name ?: '-';
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">ID Absen: {{ $employee->attendance_id ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $department }}</td>
                                <td class="px-5 py-4">
                                    @if ($row['eligible'])
                                        <span class="inline-flex rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">Eligible</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-warning-50 px-2.5 py-1 text-xs font-semibold text-warning-700 dark:bg-warning-500/10 dark:text-warning-300">Belum 1 tahun</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white/90">{{ number_format($row['entitlement'], 0) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ number_format($row['used'], 0) }}</td>
                                <td class="px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white/90">{{ number_format($row['balance'], 0) }}</td>
                                <td class="px-5 py-4 text-sm font-semibold {{ $row['debt'] > 0 ? 'text-error-600 dark:text-error-400' : 'text-gray-700 dark:text-gray-300' }}">{{ number_format($row['debt'], 0) }}</td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('annual-leaves.show', [$employee, 'year' => $year]) }}" class="inline-flex rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Detail</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </x-common.page-shell>
@endsection
