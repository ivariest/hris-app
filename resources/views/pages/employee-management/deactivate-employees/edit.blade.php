@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Deactivate Employee" description="Set employee status to inactive without deleting the employee record.">
        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Employee Summary</h3>
                <div class="mt-5 space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nama Karyawan</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">NIK</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nik_karyawan ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Company</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->company?->company_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Assignment</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->employeePosition?->position?->position_name ?? '-' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->employeePosition?->level?->level_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Status Saat Ini</p>
                        <div class="mt-2">
                            <x-ui.badge :color="$employee->status_karyawan === 'active' ? 'success' : 'warning'">
                                {{ ucfirst($employee->status_karyawan) }}
                            </x-ui.badge>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
                        Ada beberapa field yang perlu dicek lagi.
                    </div>
                @endif

                <form method="POST" action="{{ route('employee-deactivations.update', $employee) }}" enctype="multipart/form-data" x-data="{ reason: '{{ old('deactivation_reason') }}' }" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <label for="deactivation_reason" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alasan Nonaktif <span class="text-error-500">*</span></label>
                        <select id="deactivation_reason" name="deactivation_reason" x-model="reason" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Pilih alasan</option>
                            @foreach ($reasonOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('deactivation_reason') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('deactivation_reason')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="reason" x-cloak>
                        <label for="effective_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Efektif Nonaktif <span class="text-error-500">*</span></label>
                        <input type="text" id="effective_date" name="effective_date" value="{{ old('effective_date') }}" required x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" placeholder="Pilih tanggal efektif" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        @error('effective_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div x-show="reason" x-cloak>
                        <label for="deactivation_document" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload Dokumen</label>
                        <input type="file" id="deactivation_document" name="deactivation_document" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                        @if ($employee->deactivation_document_path)
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File sudah tersimpan.</p>
                        @endif
                        @error('deactivation_document')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                        Setelah disimpan, status karyawan akan menjadi inactive. Data employee tetap tersimpan dan tetap muncul di list karyawan.
                    </div>

                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
                        <a href="{{ route('employee-deactivations.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-error-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-error-600">Deactivate Employee</button>
                    </div>
                </form>
            </section>
        </div>
    </x-common.page-shell>
@endsection
