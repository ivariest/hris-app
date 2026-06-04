@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Shift Setting" description="Atur jam masuk, jam pulang, dan mapping shift karyawan.">
        <x-slot:actions>
            <a href="{{ route('attendance-shifts.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Create Shift</a>
        </x-slot:actions>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/80">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Shift</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jam</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Toleransi</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse ($shifts as $shift)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                            <td class="px-5 py-4">
                                <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $shift->shift_name }}</div>
                                @if ($shift->is_default)
                                    <span class="mt-1 inline-flex rounded-full bg-success-50 px-2 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-300">Default</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ substr($shift->check_in_time, 0, 5) }} - {{ substr($shift->check_out_time, 0, 5) }}</td>
                            <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">Telat {{ $shift->late_tolerance_minutes }}m / Pulang cepat {{ $shift->early_leave_tolerance_minutes }}m</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium {{ $shift->status === 'active' ? 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-300' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300' }}">{{ ucfirst($shift->status) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <div class="inline-flex gap-2">
                                    <a href="{{ route('attendance-shifts.edit', $shift) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a>
                                    <form method="POST" action="{{ route('attendance-shifts.destroy', $shift) }}" onsubmit="return confirm('Delete this shift?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/30 dark:hover:bg-error-500/10">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No shift data yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $shifts->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
