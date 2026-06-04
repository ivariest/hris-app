@extends('layouts.fallback')

@section('content')
    <x-common.page-shell
        title="Blank Page"
        description="Starter fallback layout for pages that need a simpler shell outside the main dashboard frame."
    >
        <x-common.flash-messages />

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <div class="mx-auto flex w-full max-w-[680px] flex-col items-center text-center">
                <span class="mb-4 inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-brand-600 dark:bg-brand-500/10 dark:text-brand-300">
                    Base shell
                </span>

                <h3 class="mb-4 text-2xl font-semibold text-gray-900 dark:text-white/90 sm:text-3xl">
                    Card Section Pattern
                </h3>

                <p class="text-sm leading-6 text-gray-500 dark:text-gray-400 sm:text-base">
                    This layout is ready for simple pages, detail screens, print views, and any screen that does not
                    need the full sidebar and header treatment.
                </p>
            </div>
        </div>
    </x-common.page-shell>
@endsection
