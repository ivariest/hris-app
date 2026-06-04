@extends('layouts.app')

@section('content')
    <x-common.page-shell :title="$title" :description="$description ?? 'This module is prepared in the navigation and ready for the next implementation phase.'">
        <div class="rounded-3xl border border-dashed border-gray-300 bg-white p-8 shadow-theme-xs dark:border-gray-700 dark:bg-gray-900/60 sm:p-10">
            <div class="mx-auto flex max-w-2xl flex-col items-center text-center">
                <span class="mb-4 inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    In progress
                </span>

                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white/90">
                    {{ $title }}
                </h2>

                <p class="mt-3 text-sm leading-6 text-gray-500 dark:text-gray-400 sm:text-base">
                    Halaman ini sudah disiapkan di menu. Konten detailnya bisa kita lanjutkan setelah struktur modul berikutnya ditentukan.
                </p>
            </div>
        </div>
    </x-common.page-shell>
@endsection
