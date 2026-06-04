@extends('layouts.app')

@php
    $formatCurrency = static fn (int|float $amount): string => 'Rp '.number_format($amount, 0, ',', '.');
    $selectedCompanyNames = $companies->whereIn('id', $companyIds)->pluck('company_name')->values();
    $selectedDepartmentNames = $departments->whereIn('id', $departmentIds)->pluck('department_name')->values();
    $selectedEmployeeNames = $employees->whereIn('id', $employeeIds)->pluck('nama_karyawan')->values();
    $printUrl = route('allowance-report.print', array_filter([
        'date_from' => $dateFrom,
        'date_to' => $dateTo,
        'company_ids' => $companyIds,
        'department_ids' => $departmentIds,
        'employee_ids' => $employeeIds,
        'meal_rate' => $mealRate,
        'transport_rate' => $transportRate,
    ], fn ($value) => $value !== '' && $value !== [] && $value !== null));
    $departmentOptions = $departments
        ->map(fn ($department) => [
            'id' => (int) $department->id,
            'company_id' => (int) $department->company_id,
            'name' => $department->department_name,
        ])
        ->values();
    $employeeOptions = $employees
        ->map(fn ($employee) => [
            'id' => (int) $employee->id,
            'company_id' => (int) $employee->company_id,
            'department_id' => (int) ($employee->employeePosition?->position?->subDepartment?->department_id ?? 0),
            'name' => $employee->nama_karyawan,
            'attendance_id' => $employee->attendance_id,
        ])
        ->values();
@endphp

@section('content')
    <x-common.page-shell title="Allowance Report" description="Hitung uang makan dan uang transport berdasarkan hari hadir karyawan.">
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
                employees: @js($employeeOptions),
                optionMatches(ids, id) {
                    return ids.length === 0 || ids.includes(String(id));
                },
                departmentAllowed(department) {
                    return this.optionMatches(this.selectedCompanyIds, department.company_id);
                },
                employeeAllowed(employee) {
                    const companyMatches = this.optionMatches(this.selectedCompanyIds, employee.company_id);
                    const departmentMatches = this.selectedDepartmentIds.length === 0 || this.selectedDepartmentIds.includes(String(employee.department_id));
                    return companyMatches && departmentMatches;
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
                    <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="meal_rate" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Uang Makan / Hari</label>
                    <input type="number" min="0" step="1000" id="meal_rate" name="meal_rate" value="{{ $mealRate }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="transport_rate" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Uang Transport / Hari</label>
                    <input type="number" min="0" step="1000" id="transport_rate" name="transport_rate" value="{{ $transportRate }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-3">
                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company</label>
                    <button type="button" @click="companyOpen = !companyOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-white/[0.03]">
                        <span class="truncate" x-text="label(companies, selectedCompanyIds, 'Semua company', 'company')">{{ $selectedCompanyNames->isEmpty() ? 'Semua company' : ($selectedCompanyNames->count() === 1 ? $selectedCompanyNames->first() : $selectedCompanyNames->count().' company dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="companyOpen" x-transition @click.outside="companyOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="max-h-64 overflow-y-auto p-2">
                            @foreach ($companies as $company)
                                <label class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" x-model="selectedCompanyIds" @change="pruneDepartments()" class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="min-w-0 flex-1 truncate">{{ $company->company_name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department</label>
                    <button type="button" @click="departmentOpen = !departmentOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-white/[0.03]">
                        <span class="truncate" x-text="label(departments, selectedDepartmentIds, 'Semua department', 'department')">{{ $selectedDepartmentNames->isEmpty() ? 'Semua department' : ($selectedDepartmentNames->count() === 1 ? $selectedDepartmentNames->first() : $selectedDepartmentNames->count().' department dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="departmentOpen" x-transition @click.outside="departmentOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="max-h-64 overflow-y-auto p-2">
                            @foreach ($departmentOptions as $department)
                                <label x-show="departmentAllowed({ id: {{ $department['id'] }}, company_id: {{ $department['company_id'] }} })" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    <input type="checkbox" name="department_ids[]" value="{{ $department['id'] }}" x-model="selectedDepartmentIds" @change="pruneEmployees()" :disabled="!departmentAllowed({ id: {{ $department['id'] }}, company_id: {{ $department['company_id'] }} })" class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="min-w-0 flex-1 truncate">{{ $department['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Karyawan</label>
                    <button type="button" @click="employeeOpen = !employeeOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-white/[0.03]">
                        <span class="truncate" x-text="label(employees, selectedEmployeeIds, 'Semua karyawan', 'karyawan')">{{ $selectedEmployeeNames->isEmpty() ? 'Semua karyawan' : ($selectedEmployeeNames->count() === 1 ? $selectedEmployeeNames->first() : $selectedEmployeeNames->count().' karyawan dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="employeeOpen" x-transition @click.outside="employeeOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="max-h-64 overflow-y-auto p-2">
                            @foreach ($employeeOptions as $employee)
                                <label x-show="employeeAllowed({ id: {{ $employee['id'] }}, company_id: {{ $employee['company_id'] }}, department_id: {{ $employee['department_id'] }} })" class="flex cursor-pointer items-center gap-2 rounded-lg px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">
                                    <input type="checkbox" name="employee_ids[]" value="{{ $employee['id'] }}" x-model="selectedEmployeeIds" :disabled="!employeeAllowed({ id: {{ $employee['id'] }}, company_id: {{ $employee['company_id'] }}, department_id: {{ $employee['department_id'] }} })" class="size-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900">
                                    <span class="min-w-0 flex-1 truncate">{{ $employee['name'] }} @if($employee['attendance_id']) - {{ $employee['attendance_id'] }} @endif</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex flex-col gap-2 border-t border-gray-100 pt-4 dark:border-gray-800 sm:flex-row sm:justify-end">
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">Filter</button>
                <a href="{{ route('allowance-report.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Reset</a>
                <a href="{{ $isFiltered ? $printUrl : '#' }}" target="_blank" class="{{ $isFiltered ? 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200' : 'pointer-events-none bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600' }} inline-flex h-11 items-center justify-center rounded-xl px-5 text-sm font-medium transition">Export PDF</a>
            </div>
        </form>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Karyawan</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $totals['employees'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Hari Kehadiran</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $totals['present_days'] }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Hari Telat</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $totals['late_days'] }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Allowance dibayar 50%</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Potongan</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $formatCurrency($totals['deduction']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">TOTAL</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $formatCurrency($totals['total_allowance']) }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Allowance Summary</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[1150px] divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="sticky left-0 z-10 min-w-[240px] bg-gray-50 px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Department</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Hari Kehadiran</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total Hari Telat</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Uang Makan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Uang Transport</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Total Potongan</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">TOTAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @if (! $isFiltered)
                            <tr>
                                <td colspan="8" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Pilih range tanggal terlebih dahulu untuk menghitung allowance.</td>
                            </tr>
                        @else
                            @forelse ($rows as $row)
                                @php
                                    $employee = $row['employee'];
                                    $departmentName = $employee->employeePosition?->position?->subDepartment?->department?->department_name ?: '-';
                                @endphp
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                    <td class="sticky left-0 z-10 bg-white px-5 py-4 dark:bg-gray-900">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">ID Absen: {{ $employee->attendance_id ?: '-' }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $departmentName }}</td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $row['present_days'] }}</td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $row['late_days'] }}</td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $formatCurrency($row['meal_allowance']) }}</td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $formatCurrency($row['transport_allowance']) }}</td>
                                    <td class="px-4 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $formatCurrency($row['deduction']) }}</td>
                                    <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white/90">{{ $formatCurrency($row['total_allowance']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data karyawan pada filter ini.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </x-common.page-shell>
@endsection
