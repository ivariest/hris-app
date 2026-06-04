@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Demotion" description="Manage employee position and level demotion records.">
        <x-slot:actions>
            <a href="{{ route('employee-demotions.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">
                New demotion
            </a>
        </x-slot:actions>

        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('employee-demotions.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-10">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search demotion number, employee name, or NIK" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div class="flex items-end lg:col-span-2 lg:justify-end">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600 lg:w-auto lg:min-w-[110px]">Go</button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">From</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">To</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Effective Date</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($demotions as $demotion)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $demotion->employee?->nama_karyawan ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $demotion->employee?->nik_karyawan ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $demotion->oldPosition?->position_name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $demotion->oldLevel?->level_name ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $demotion->newPosition?->position_name ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $demotion->newLevel?->level_name ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $demotion->effective_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('employee-demotions.show', $demotion) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No demotion records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">
                {{ $demotions->links() }}
            </div>
        </div>
    </x-common.page-shell>
@endsection
