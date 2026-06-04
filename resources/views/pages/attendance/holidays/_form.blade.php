@php
    $holiday = $holiday ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="holiday_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal <span class="text-error-500">*</span></label>
                    <input type="date" id="holiday_date" name="holiday_date" value="{{ old('holiday_date', optional($holiday?->holiday_date)->format('Y-m-d') ?? $holiday?->holiday_date) }}" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    @error('holiday_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="holiday_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipe <span class="text-error-500">*</span></label>
                    <select id="holiday_type" name="holiday_type" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @foreach ($holidayTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('holiday_type', $holiday->holiday_type ?? 'national_holiday') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('holiday_type')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="holiday_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Libur <span class="text-error-500">*</span></label>
                    <input type="text" id="holiday_name" name="holiday_name" value="{{ old('holiday_name', $holiday->holiday_name ?? '') }}" required maxlength="150" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    @error('holiday_name')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>
    </div>
    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Save holiday</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Tanggal ini akan dihitung sebagai holiday saat file fingerprint di-import.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Save holiday</button>
                <a href="{{ route('attendance-holidays.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
            </div>
        </div>
    </div>
</div>
