@php
    $department = $department ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white/90">
                    Department Information
                </h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Select the parent company first, then define the department name under it.
                </p>
            </div>

            <div class="grid gap-5">
                <div>
                    <label for="company_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                        Company <span class="text-error-500">*</span>
                    </label>
                    <select
                        id="company_id"
                        name="company_id"
                        required
                        class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                    >
                        <option value="">Select company</option>
                        @foreach ($companies as $company)
                            <option value="{{ $company->id }}" @selected(old('company_id', $department->company_id ?? '') == $company->id)>
                                {{ $company->company_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('company_id')
                        <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-5">
                <label for="department_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                    Department name <span class="text-error-500">*</span>
                </label>
                <input
                    type="text"
                    id="department_name"
                    name="department_name"
                    value="{{ old('department_name', $department->department_name ?? '') }}"
                    maxlength="100"
                    required
                    class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800"
                    placeholder="Human Resources"
                />
                @error('department_name')
                    <p class="mt-2 text-sm text-error-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">
                Save changes
            </h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">
                Confirm the data, then save. You can always edit the department later.
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row lg:flex-col">
                <button
                    type="submit"
                    class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600"
                >
                    Save department
                </button>

                <a
                    href="{{ route('departments.index') }}"
                    class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]"
                >
                    Cancel
                </a>
            </div>
        </div>
    </div>
</div>
