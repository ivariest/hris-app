@props([])

@php
    $displayValue = fn ($value) => filled($value) ? $value : '-';
@endphp

<div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">{{ $title }}</h3>

    <dl class="mt-5 grid gap-4 sm:grid-cols-2">
        @foreach ($items as $label => $itemValue)
            <div class="rounded-2xl border border-gray-100 bg-gray-50/80 px-4 py-3 dark:border-gray-800 dark:bg-gray-900">
                <dt class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $displayValue($itemValue) }}</dd>
            </div>
        @endforeach
    </dl>
</div>
