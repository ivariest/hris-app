@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Position Levels" description="Manage position levels used by job titles.">
        <x-slot:actions><a href="{{ route('position-levels.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">New level</a></x-slot:actions>
        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('position-levels.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-10"><label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label><input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search level name" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" /></div>
                <div class="flex items-end lg:col-span-2"><button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Go</button></div>
            </form>
        </div>
        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/80"><tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Level name</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th></tr></thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($positionLevels as $positionLevel)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $positionLevel->level_name }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('position-levels.show', $positionLevel) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a>
                                    <a href="{{ route('position-levels.edit', $positionLevel) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a>
                                    <form method="POST" action="{{ route('position-levels.destroy', $positionLevel) }}" onsubmit="return confirm('Delete this position level?');">@csrf @method('DELETE')<button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No position levels found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $positionLevels->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
