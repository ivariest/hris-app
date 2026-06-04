@extends('layouts.app')

@section('content')
    @php
        $isEdit = filled($collective);
    @endphp

    <x-common.page-shell title="{{ $isEdit ? 'Edit Cuti Bersama' : 'Create Cuti Bersama' }}" description="Cuti bersama akan diterapkan ke semua karyawan aktif. Karyawan non-eligible masuk sebagai hutang cuti aktif.">
        <form method="POST" action="{{ $isEdit ? route('annual-leaves.collectives.update', $collective) : route('annual-leaves.collectives.store') }}">
            @csrf
            @if ($isEdit)
                @method('PUT')
            @endif
            <div class="grid gap-6 lg:grid-cols-12">
                <div class="lg:col-span-8">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                        <div class="grid gap-5 sm:grid-cols-2">
                            <div>
                                <label for="year" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun <span class="text-error-500">*</span></label>
                                <input type="number" id="year" name="year" value="{{ old('year', $collective?->year ?? $year) }}" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                            <div>
                                <label for="name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Cuti Bersama <span class="text-error-500">*</span></label>
                                <input type="text" id="name" name="name" value="{{ old('name', $collective?->name) }}" required maxlength="150" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                            <div>
                                <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal <span class="text-error-500">*</span></label>
                                <input type="date" id="date_from" name="date_from" value="{{ old('date_from', $collective?->date_from?->format('Y-m-d')) }}" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                            <div>
                                <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal <span class="text-error-500">*</span></label>
                                <input type="date" id="date_to" name="date_to" value="{{ old('date_to', $collective?->date_to?->format('Y-m-d')) }}" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan</label>
                                <textarea id="notes" name="notes" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes', $collective?->notes) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="lg:col-span-4">
                    <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ $isEdit ? 'Update cuti bersama' : 'Save cuti bersama' }}</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Range tanggal akan dihitung sebagai hari kerja, lalu transaksi karyawan akan disesuaikan otomatis.</p>
                        <div class="mt-6 flex flex-col gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $isEdit ? 'Update' : 'Save' }}</button>
                            <a href="{{ route('annual-leaves.index', ['year' => $year]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </x-common.page-shell>
@endsection
