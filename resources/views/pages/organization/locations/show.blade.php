@extends('layouts.app')
@section('content')
    <x-common.page-shell title="{{ $location->location_name }}" description="Location detail.">
        <x-slot:actions><a href="{{ route('locations.edit', $location) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Edit</a><a href="{{ route('locations.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back to list</a></x-slot:actions>
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <p class="text-sm text-gray-500 dark:text-gray-400">Location name</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $location->location_name }}</p>
        </div>
    </x-common.page-shell>
@endsection
