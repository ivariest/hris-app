@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Recruitment Report" description="Summary of request status and candidate categories.">
        <div class="grid gap-5 md:grid-cols-3">
            @foreach (['pending' => 'Pending', 'on_going' => 'On Going', 'done' => 'Done'] as $value => $label)
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Request {{ $label }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white/90">{{ $requestStatusCounts[$value] ?? 0 }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid gap-5 md:grid-cols-3">
            @foreach (['shortlist' => 'Shortlist', 'offering' => 'Offering', 'agreement_sent' => 'Agreement Sent', 'join' => 'Join', 'reject' => 'Reject'] as $value => $label)
                <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Candidate {{ $label }}</p>
                    <p class="mt-2 text-3xl font-semibold text-gray-900 dark:text-white/90">{{ $candidateCategoryCounts[$value] ?? 0 }}</p>
                </div>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Latest Requests</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Request</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Position</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Candidates</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($requests as $request)
                            <tr>
                                <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $request->request_number }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $request->requested_position }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $request->candidates_count }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ str_replace('_', ' ', ucfirst($request->status)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">No recruitment requests found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </x-common.page-shell>
@endsection
