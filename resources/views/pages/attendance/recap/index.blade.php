@extends('layouts.app')

@php
    $formatMinutes = static function (?int $minutes): string {
        if (! $minutes) {
            return '';
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
@endphp

@section('content')
    <x-common.page-shell title="Attendance Recap" description="Filter attendance berdasarkan periode tanggal.">
        @php
            $printUrl = route('attendance-recap.print', array_filter([
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'company_ids' => $companyIds,
                'department_ids' => $departmentIds,
                'employee_ids' => $employeeIds,
            ]));
            $selectedCompanyNames = $companies
                ->whereIn('id', $companyIds)
                ->pluck('company_name')
                ->values();
            $selectedDepartmentNames = $departments
                ->whereIn('id', $departmentIds)
                ->pluck('department_name')
                ->values();
            $selectedEmployeeNames = $employees
                ->whereIn('id', $employeeIds)
                ->pluck('nama_karyawan')
                ->values();
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
            class="mb-5 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60"
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
                        <div class="border-b border-gray-100 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">Pilih satu atau beberapa company</div>
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
                        <div class="border-b border-gray-100 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">Department mengikuti company terpilih</div>
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
                        <div class="border-b border-gray-100 px-3 py-2 text-xs font-medium text-gray-500 dark:border-gray-800 dark:text-gray-400">Karyawan mengikuti department terpilih</div>
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
                <a href="{{ route('attendance-recap.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 px-5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Reset</a>
                <a href="{{ $isFiltered ? $printUrl : '#' }}" target="_blank" class="{{ $isFiltered ? 'bg-gray-900 text-white hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200' : 'pointer-events-none bg-gray-100 text-gray-400 dark:bg-gray-800 dark:text-gray-600' }} inline-flex h-11 items-center justify-center rounded-xl px-5 text-sm font-medium transition">Cetak PDF</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Detail Scan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="w-[120px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor ID</th>
                            <th class="min-w-[240px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Karyawan</th>
                            <th class="min-w-[180px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department</th>
                            <th class="w-[150px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="w-[110px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Masuk</th>
                            <th class="w-[110px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pulang</th>
                            <th class="w-[130px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jam Telat</th>
                            <th class="w-[150px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jam Pulang Cepat</th>
                            <th class="min-w-[220px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Keterangan</th>
                            <th class="w-[180px] px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Exception</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @if (! $isFiltered)
                            <tr>
                                <td colspan="10" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Pilih range tanggal terlebih dahulu untuk melihat recap attendance.</td>
                            </tr>
                        @else
                            @forelse ($rows as $row)
                                @php
                                    $employee = $row['employee'];
                                    $log = $row['log'];
                                    $adjustment = $row['adjustment'];
                                    $position = $employee->employeePosition?->position;
                                    $department = $position?->subDepartment?->department?->department_name;
                                    $isDayOff = $row['is_weekend'] || $row['is_holiday'];
                                    $cellClass = $isDayOff ? 'bg-error-50/80 dark:bg-error-500/10' : '';
                                    $exceptionText = trim(($adjustment?->exception_type ?? '').($adjustment?->exception_note ? ' - '.$adjustment->exception_note : ''));
                                @endphp
                                <tr x-data="{ actionOpen: false, exceptionOpen: false, scanOpen: false }" class="{{ $isDayOff ? 'hover:bg-error-100/80 dark:hover:bg-error-500/15' : 'hover:bg-gray-50/80 dark:hover:bg-white/[0.02]' }}">
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $employee->attendance_id ?: '-' }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm font-medium text-gray-800 dark:text-white/90">{{ $department ?: '-' }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4">
                                        <div class="text-sm font-medium {{ $isDayOff ? 'text-error-700 dark:text-error-300' : 'text-gray-700 dark:text-gray-300' }}">{{ $row['date']->format('d M Y') }}</div>
                                        @if ($row['is_weekend'])
                                            <div class="text-xs font-medium text-error-600 dark:text-error-300">{{ $row['date']->translatedFormat('l') }}</div>
                                        @endif
                                        @if ($row['is_holiday'])
                                            <div class="text-xs font-medium text-error-600 dark:text-error-300">{{ $row['holiday']->holiday_name }}</div>
                                        @endif
                                    </td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['check_in'] ? substr($row['check_in'], 0, 5) : '' }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $row['check_out'] ? substr($row['check_out'], 0, 5) : '' }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $formatMinutes($row['late_minutes']) }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $formatMinutes($row['early_leave_minutes']) }}</td>
                                    <td class="{{ $cellClass }} px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $exceptionText }}</td>
                                    <td class="{{ $cellClass }} relative px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <button type="button" @click="actionOpen = !actionOpen" class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03]">Action</button>
                                        <div x-show="actionOpen" @click.outside="actionOpen = false" x-transition class="absolute right-5 z-20 mt-2 w-44 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                                            <button type="button" @click="exceptionOpen = true; actionOpen = false" class="block w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Input Exception</button>
                                            <button type="button" @click="scanOpen = true; actionOpen = false" class="block w-full px-4 py-2.5 text-left text-sm text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.03]">Edit Scan</button>
                                        </div>

                                        <div x-show="exceptionOpen" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
                                            <div @click.outside="exceptionOpen = false" class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Input Exception</h3>
                                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $employee->nama_karyawan }} - {{ $row['date']->format('d M Y') }}</p>
                                                    </div>
                                                    <button type="button" @click="exceptionOpen = false" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">&times;</button>
                                                </div>
                                                <div class="mt-5 grid gap-3 rounded-xl bg-gray-50 p-4 text-sm text-gray-700 dark:bg-gray-800/70 dark:text-gray-300 sm:grid-cols-2">
                                                    <div><span class="text-gray-500 dark:text-gray-400">Department:</span> {{ $department ?: '-' }}</div>
                                                    <div><span class="text-gray-500 dark:text-gray-400">Tanggal:</span> {{ $row['date']->format('d M Y') }}</div>
                                                    <div><span class="text-gray-500 dark:text-gray-400">Masuk:</span> {{ $row['check_in'] ? substr($row['check_in'], 0, 5) : '-' }}</div>
                                                    <div><span class="text-gray-500 dark:text-gray-400">Pulang:</span> {{ $row['check_out'] ? substr($row['check_out'], 0, 5) : '-' }}</div>
                                                </div>
                                                <form method="POST" action="{{ route('attendance-adjustments.store') }}" class="mt-5 space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="action" value="exception">
                                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                    <input type="hidden" name="attendance_date" value="{{ $row['date']->toDateString() }}">
                                                    <input type="hidden" name="redirect_date_from" value="{{ $dateFrom }}">
                                                    <input type="hidden" name="redirect_date_to" value="{{ $dateTo }}">
                                                    @foreach ($employeeIds as $selectedEmployeeId)
                                                        <input type="hidden" name="redirect_employee_ids[]" value="{{ $selectedEmployeeId }}">
                                                    @endforeach
                                                    @foreach ($companyIds as $selectedCompanyId)
                                                        <input type="hidden" name="redirect_company_ids[]" value="{{ $selectedCompanyId }}">
                                                    @endforeach
                                                    @foreach ($departmentIds as $selectedDepartmentId)
                                                        <input type="hidden" name="redirect_department_ids[]" value="{{ $selectedDepartmentId }}">
                                                    @endforeach
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Keterangan Exception</label>
                                                        <select name="exception_type" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                            <option value="">Pilih exception</option>
                                                            @foreach ($exceptionTypes as $type)
                                                                <option value="{{ $type }}" @selected($adjustment?->exception_type === $type)>{{ $type }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Keterangan</label>
                                                        <textarea name="exception_note" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ $adjustment?->exception_note }}</textarea>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="exceptionOpen = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Cancel</button>
                                                        <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Input</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div x-show="scanOpen" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
                                            <div @click.outside="scanOpen = false" class="w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900">
                                                <div class="flex items-start justify-between gap-4">
                                                    <div>
                                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Edit Scan</h3>
                                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $employee->nama_karyawan }} - {{ $row['date']->format('d M Y') }}</p>
                                                    </div>
                                                    <button type="button" @click="scanOpen = false" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">&times;</button>
                                                </div>
                                                <form method="POST" action="{{ route('attendance-adjustments.store') }}" class="mt-5 space-y-4">
                                                    @csrf
                                                    <input type="hidden" name="action" value="scan">
                                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                    <input type="hidden" name="attendance_date" value="{{ $row['date']->toDateString() }}">
                                                    <input type="hidden" name="redirect_date_from" value="{{ $dateFrom }}">
                                                    <input type="hidden" name="redirect_date_to" value="{{ $dateTo }}">
                                                    @foreach ($employeeIds as $selectedEmployeeId)
                                                        <input type="hidden" name="redirect_employee_ids[]" value="{{ $selectedEmployeeId }}">
                                                    @endforeach
                                                    @foreach ($companyIds as $selectedCompanyId)
                                                        <input type="hidden" name="redirect_company_ids[]" value="{{ $selectedCompanyId }}">
                                                    @endforeach
                                                    @foreach ($departmentIds as $selectedDepartmentId)
                                                        <input type="hidden" name="redirect_department_ids[]" value="{{ $selectedDepartmentId }}">
                                                    @endforeach
                                                    <div class="grid gap-4 sm:grid-cols-2">
                                                        <div>
                                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jam Masuk</label>
                                                            <input type="time" name="check_in_override" value="{{ $row['check_in'] ? substr($row['check_in'], 0, 5) : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                        </div>
                                                        <div>
                                                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jam Pulang</label>
                                                            <input type="time" name="check_out_override" value="{{ $row['check_out'] ? substr($row['check_out'], 0, 5) : '' }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                                        </div>
                                                    </div>
                                                    <div class="flex justify-end gap-3">
                                                        <button type="button" @click="scanOpen = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Cancel</button>
                                                        <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Save Scan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada data karyawan pada filter ini.</td>
                                </tr>
                            @endforelse
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </x-common.page-shell>
@endsection
