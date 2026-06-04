@php $positionLevel = $positionLevel ?? null; @endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <label for="level_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Level name <span class="text-error-500">*</span></label>
            <input type="text" id="level_name" name="level_name" value="{{ old('level_name', $positionLevel->level_name ?? '') }}" maxlength="50" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" placeholder="Supervisor" />
            @error('level_name')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>
    </div>
    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Save changes</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Position levels keep job titles standardized.</p>
            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Save level</button>
                <a href="{{ route('position-levels.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
            </div>
        </div>
    </div>
</div>
