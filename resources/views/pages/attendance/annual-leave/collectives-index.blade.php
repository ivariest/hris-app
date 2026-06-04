@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Cuti Bersama" description="Kelola daftar cuti bersama tahunan.">
        <x-slot:actions>
            <a href="{{ route('annual-leaves.index', ['year' => $year]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Back</a>
            <a href="{{ route('annual-leaves.collectives.create', ['year' => $year]) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Create</a>
        </x-slot:actions>

        <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xs">
                    <label for="year" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun</label>
                    <select id="year" name="year" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @foreach ($yearOptions as $yearOption)
                            <option value="{{ $yearOption }}" @selected((int) $year === (int) $yearOption)>{{ $yearOption }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="inline-flex h-11 items-center justify-center rounded-xl bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">Filter</button>
            </div>
        </form>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">List Cuti Bersama {{ $year }}</h3>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-gray-800">
                @forelse ($collectives as $collective)
                    <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $collective->name }}</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $collective->date_from->format('d M Y') }} @if(!$collective->date_from->equalTo($collective->date_to)) - {{ $collective->date_to->format('d M Y') }} @endif</div>
                            @if ($collective->notes)
                                <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ $collective->notes }}</div>
                            @endif
                        </div>
                        <a href="{{ route('annual-leaves.collectives.edit', $collective) }}" class="inline-flex h-10 items-center justify-center rounded-lg border border-gray-200 px-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Edit</a>
                    </div>
                @empty
                    <div class="px-5 py-10 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada cuti bersama untuk tahun ini.</div>
                @endforelse
            </div>
        </section>
    </x-common.page-shell>
@endsection
