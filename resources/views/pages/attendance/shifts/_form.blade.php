@php
    $shift = $shift ?? null;
    $assignedEmployeeIds = collect(old('employee_ids', $assignedEmployeeIds ?? []))->map(fn ($id) => (string) $id)->all();
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="shift_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Shift <span class="text-error-500">*</span></label>
                    <input type="text" id="shift_name" name="shift_name" value="{{ old('shift_name', $shift->shift_name ?? '') }}" required maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    @error('shift_name')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status <span class="text-error-500">*</span></label>
                    <select id="status" name="status" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="active" @selected(old('status', $shift->status ?? 'active') === 'active')>Active</option>
                        <option value="inactive" @selected(old('status', $shift->status ?? '') === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div>
                    <label for="check_in_time" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jam Masuk <span class="text-error-500">*</span></label>
                    <input type="time" id="check_in_time" name="check_in_time" value="{{ old('check_in_time', isset($shift->check_in_time) ? substr($shift->check_in_time, 0, 5) : '') }}" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                </div>
                <div>
                    <label for="check_out_time" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jam Pulang <span class="text-error-500">*</span></label>
                    <input type="time" id="check_out_time" name="check_out_time" value="{{ old('check_out_time', isset($shift->check_out_time) ? substr($shift->check_out_time, 0, 5) : '') }}" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                </div>
                <div>
                    <label for="late_tolerance_minutes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Toleransi Telat (menit)</label>
                    <input type="number" id="late_tolerance_minutes" name="late_tolerance_minutes" value="{{ old('late_tolerance_minutes', $shift->late_tolerance_minutes ?? 0) }}" min="0" max="240" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                </div>
                <div>
                    <label for="early_leave_tolerance_minutes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Toleransi Pulang Cepat (menit)</label>
                    <input type="number" id="early_leave_tolerance_minutes" name="early_leave_tolerance_minutes" value="{{ old('early_leave_tolerance_minutes', $shift->early_leave_tolerance_minutes ?? 0) }}" min="0" max="240" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                </div>
                <label class="flex items-center gap-3 sm:col-span-2">
                    <input type="hidden" name="is_default" value="0">
                    <input type="checkbox" name="is_default" value="1" @checked(old('is_default', $shift->is_default ?? false)) class="h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Jadikan shift default</span>
                </label>
            </div>

            <div class="mt-6">
                <label for="employee_ids" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Assign Karyawan ke Shift</label>
                <select id="employee_ids" name="employee_ids[]" multiple class="min-h-[180px] w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(in_array((string) $employee->id, $assignedEmployeeIds, true))>{{ $employee->nama_karyawan }} @if($employee->attendance_id) - {{ $employee->attendance_id }} @endif</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Save shift</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Jika karyawan belum diset ke shift tertentu, import akan memakai shift default.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Save shift</button>
                <a href="{{ route('attendance-shifts.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
            </div>
        </div>
    </div>
</div>
