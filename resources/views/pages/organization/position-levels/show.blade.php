@extends('layouts.app')
@section('content')
    <x-common.page-shell title="{{ $positionLevel->level_name }}" description="Position level detail.">
        <x-slot:actions>
            <a href="{{ route('position-levels.edit', $positionLevel) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Edit</a>
            <a href="{{ route('position-levels.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back to list</a>
        </x-slot:actions>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <dl class="grid gap-5 sm:grid-cols-2">
                <div><dt class="text-sm text-gray-500 dark:text-gray-400">Level name</dt><dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white/90">{{ $positionLevel->level_name }}</dd></div>
            </dl>
        </div>
    </x-common.page-shell>
@endsection
