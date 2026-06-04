@extends('layouts.app')

@php
    $formatMinutes = static function (?int $minutes): string {
        if (! $minutes) {
            return '-';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($hours > 0 && $remainingMinutes > 0) {
            return "{$hours}j {$remainingMinutes}m";
        }

        if ($hours > 0) {
            return "{$hours}j";
        }

        return "{$remainingMinutes}m";
    };

    $selectedCompanyNames = $companies->whereIn('id', $companyIds)->pluck('company_name')->values();
    $selectedDepartmentNames = $departments->whereIn('id', $departmentIds)->pluck('department_name')->values();
    $selectedEmployeeNames = $employees->whereIn('id', $employeeIds)->pluck('nama_karyawan')->values();
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
    <x-common.page-shell title="Attandance Report" description="Summary attendance per karyawan tanpa detail harian.">
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
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div>
                    <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="relative">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company</label>
                    <button type="button" @click="companyOpen = !companyOpen" class="flex h-11 w-full items-center justify-between gap-3 rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-left text-sm text-gray-800 shadow-theme-xs transition hover:bg-gray-50 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:hover:bg-white/[0.03]">
                        <span class="truncate" x-text="label(companies, selectedCompanyIds, 'Semua company', 'company')">{{ $selectedCompanyNames->isEmpty() ? 'Semua company' : ($selectedCompanyNames->count() === 1 ? $selectedCompanyNames->first() : $selectedCompanyNames->count().' company dipilih') }}</span>
                        <span class="text-gray-400">v</span>
                    </button>
                    <div x-show="companyOpen" x-transition @click.outside="companyOpen = false" class="absolute right-0 z-30 mt-2 max-h-72 w-full overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                        <div class="border-b border-gray-100 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">Pilih company</div>
                        <div class="max-h-56 overflow-y-auto p-2">
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
                        <div class="border-b border-gray-100 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">Mengikuti company terpilih</div>
                        <div class="max-h-56 overflow-y-auto p-2">
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
                        <div class="border-b border-gray-100 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">Mengikuti department terpilih</div>
                        <div class="max-h-56 overflow-y-auto p-2">
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
                <a href="{{ route('attendance-report.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Reset</a>
            </div>
        </form>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Rata-rata Jam Kerja</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $formatMinutes($totals['average_work_minutes']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Persentase Kehadiran</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ number_format($totals['attendance_percent'], 2, ',', '.') }}%</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $totals['attended_days'] }}/{{ $totals['effective_days'] }} hari efektif</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Jam Telat</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $formatMinutes($totals['late_minutes']) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Jam Pulang Awal</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $formatMinutes($totals['early_leave_minutes']) }}</p>
            </div>
        </section>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Attendance Summary</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[1500px] divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="sticky left-0 z-10 min-w-[240px] bg-gray-50 px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:bg-gray-900 dark:text-gray-400">Karyawan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Department</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Avg Kerja</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Kehadiran</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Telat</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Pulang Awal</th>
                            @foreach ($exceptionTypes as $label)
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @if (! $isFiltered)
                            <tr>
                                <td colspan="{{ 6 + count($exceptionTypes) }}" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Pilih range tanggal terlebih dahulu untuk melihat report.</td>
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
                                    <td class="px-4 py-4 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $formatMinutes($row['average_work_minutes']) }}</td>
                                    <td class="px-4 py-4">
                                        <span class="inline-flex rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">{{ number_format($row['attendance_percent'], 2, ',', '.') }}%</span>
                                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $row['attended_days'] }}/{{ $row['effective_days'] }}</div>
                                    </td>
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $formatMinutes($row['late_minutes']) }}</td>
                                    <td class="px-4 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $formatMinutes($row['early_leave_minutes']) }}</td>
                                    @foreach ($exceptionTypes as $type => $label)
                                        <td class="px-4 py-4 text-sm font-medium text-gray-800 dark:text-gray-200">{{ $row['exceptions'][$type] }}</td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 6 + count($exceptionTypes) }}" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data karyawan pada filter ini.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </section>
    </x-common.page-shell>
@endsection
