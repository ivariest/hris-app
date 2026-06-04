@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Upload Fingerprint Excel" description="Upload Excel dengan kolom ID Absen, Nama, Tanggal scan, Jam scan masuk, Jam scan keluar.">
        <div class="grid gap-6 lg:grid-cols-12">
            <div class="lg:col-span-8">
                <form method="POST" action="{{ route('attendance-imports.preview') }}" enctype="multipart/form-data" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                    @csrf
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="period_start" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal <span class="text-error-500">*</span></label>
                            <input type="date" id="period_start" name="period_start" value="{{ old('period_start') }}" onclick="this.showPicker()" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('period_start')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="period_end" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal <span class="text-error-500">*</span></label>
                            <input type="date" id="period_end" name="period_end" value="{{ old('period_end') }}" onclick="this.showPicker()" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('period_end')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="attendance_file" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">File Excel <span class="text-error-500">*</span></label>
                        <input type="file" id="attendance_file" name="attendance_file" accept=".xlsx" required class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                        @error('attendance_file')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>

                    <div class="mt-6 flex items-center gap-3">
                        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Preview</button>
                        <a href="{{ route('attendance-imports.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Cancel</a>
                    </div>
                </form>
            </div>

            <div class="lg:col-span-4">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Shift aktif</h3>
                    @if ($defaultShift)
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $defaultShift->shift_name }}</p>
                        <p class="mt-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $defaultShift->check_in_time }} - {{ $defaultShift->check_out_time }}</p>
                    @else
                        <p class="mt-2 text-sm text-warning-600 dark:text-warning-300">Buat shift default sebelum import.</p>
                    @endif
                </div>
            </div>
        </div>
    </x-common.page-shell>
@endsection
