@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Holiday Setting" description="Kelola libur nasional dan cuti bersama untuk attendance.">
        <x-slot:actions>
            <a href="{{ route('attendance-holidays.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Create Holiday</a>
        </x-slot:actions>

        <form method="GET" class="mb-5 grid gap-3 rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:grid-cols-3">
            <div>
                <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>
            <div>
                <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="inline-flex h-11 flex-1 items-center justify-center rounded-xl bg-brand-500 px-4 text-sm font-medium text-white transition hover:bg-brand-600">Filter</button>
                <a href="{{ route('attendance-holidays.index') }}" class="inline-flex h-11 items-center justify-center rounded-xl border border-gray-200 px-4 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Reset</a>
            </div>
        </form>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Libur</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tipe</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($holidays as $holiday)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ optional($holiday->holiday_date)->format('d M Y') ?? $holiday->holiday_date }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $holiday->holiday_name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $holidayTypes[$holiday->holiday_type] ?? $holiday->holiday_type }}</td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('attendance-holidays.edit', $holiday) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a>
                                    <form method="POST" action="{{ route('attendance-holidays.destroy', $holiday) }}" onsubmit="return confirm('Delete this holiday?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/30 dark:hover:bg-error-500/10">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No holiday data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $holidays->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
