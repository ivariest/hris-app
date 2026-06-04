@php
    $company = $company ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <div class="grid gap-5">
                <div>
                    <label for="company_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Company name <span class="text-error-500">*</span>
                    </label>
                    <input type="text" id="company_name" name="company_name" value="{{ old('company_name', $company->company_name ?? '') }}" maxlength="100" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" placeholder="StandardPen Indonesia" />
                    @error('company_name')
                        <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-5">
                <label for="company_address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Company address
                </label>
                <textarea id="company_address" name="company_address" rows="5" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" placeholder="Head office address">{{ old('company_address', $company->company_address ?? '') }}</textarea>
                @error('company_address')
                    <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Save changes</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                Company data is used as the top-level master for employee assignment.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                    Save company
                </button>
                <a href="{{ route('companies.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>
