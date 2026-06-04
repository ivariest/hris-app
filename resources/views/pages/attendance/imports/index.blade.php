@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Import Fingerprint" description="Upload and review fingerprint Excel imports.">
        <x-slot:actions>
            <a href="{{ route('attendance-imports.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Upload Excel</a>
        </x-slot:actions>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">File</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Periode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Rows</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Matched</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Unmatched</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($imports as $import)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $import->file_name }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                @if ($import->period_start && $import->period_end)
                                    {{ $import->period_start->format('d M Y') }} - {{ $import->period_end->format('d M Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $import->total_rows }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $import->matched_rows }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $import->unmatched_rows }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('attendance-imports.show', $import) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No attendance imports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $imports->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
