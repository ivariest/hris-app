@php
    $tabFilters = [
        '' => ['label' => 'Semua', 'count' => $candidates->total()],
        'shortlist' => ['label' => 'Shortlist', 'count' => $categoryCounts['shortlist'] ?? 0],
        'offering' => ['label' => 'Offering', 'count' => $categoryCounts['offering'] ?? 0],
        'agreement_sent' => ['label' => 'Agreement Sent', 'count' => $categoryCounts['agreement_sent'] ?? 0],
        'join' => ['label' => 'Join', 'count' => $categoryCounts['join'] ?? 0],
        'reject' => ['label' => 'Reject', 'count' => $categoryCounts['reject'] ?? 0],
    ];

    $baseQuery = request()->except(['category', 'page']);
@endphp

@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Candidates" description="Manage candidates through shortlist, offering, agreement, and join stages.">
        <x-slot:actions>
            <a href="{{ route('recruitment-candidates.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Add candidate</a>
        </x-slot:actions>

        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('recruitment-candidates.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search name, email, phone, KTP" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div class="lg:col-span-4">
                    <label for="recruitment_request_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Request</label>
                    <select id="recruitment_request_id" name="recruitment_request_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All requests</option>
                        @foreach ($requests as $request)
                            <option value="{{ $request->id }}" @selected($requestId == $request->id)>{{ $request->request_number }} - {{ $request->requested_position }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end lg:col-span-4 lg:justify-end">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600 lg:w-auto lg:min-w-[110px]">Filter</button>
                </div>
            </form>
        </div>

        <div class="flex flex-wrap gap-2">
            @foreach ($tabFilters as $value => $tab)
                <a
                    href="{{ route('recruitment-candidates.index', array_merge($baseQuery, $value !== '' ? ['category' => $value] : [])) }}"
                    class="inline-flex items-center gap-2 rounded-full border px-4 py-2 text-sm font-medium transition {{ $category === $value ? 'border-brand-500 bg-brand-50 text-brand-700 dark:border-brand-400 dark:bg-brand-500/10 dark:text-brand-300' : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]' }}"
                >
                    <span>{{ $tab['label'] }}</span>
                    <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Candidate</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Request</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contact</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($candidates as $candidate)
                            @php
                                $hasActiveOffer = ($candidate->request?->candidates ?? collect())
                                    ->contains(fn ($requestCandidate) => in_array($requestCandidate->category, ['offering', 'agreement_sent'], true) && $requestCandidate->id !== $candidate->id);
                            @endphp
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $candidate->candidate_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $candidate->id_card_number ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $candidate->request?->request_number ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $candidate->request?->requested_position ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div>{{ $candidate->candidate_phone ?: '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $candidate->candidate_email ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($candidate->category === 'agreement_sent')
                                        <details class="relative inline-block">
                                            <summary class="inline-flex cursor-pointer list-none">
                                                <x-ui.badge color="warning">{{ $categoryOptions[$candidate->category] ?? $candidate->category }}</x-ui.badge>
                                            </summary>
                                            <div class="absolute left-0 z-20 mt-2 w-44 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-900">
                                                <form method="POST" action="{{ route('recruitment-candidates.move-category', $candidate) }}" onsubmit="return confirm('Reject agreement kandidat ini?');">
                                                    @csrf
                                                    <input type="hidden" name="category" value="reject">
                                                    <button type="submit" class="block w-full px-4 py-2.5 text-left text-sm text-error-600 transition hover:bg-error-50 dark:text-error-400 dark:hover:bg-error-500/10">
                                                        Agreement Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </details>
                                    @else
                                        <x-ui.badge :color="$candidate->category === 'join' ? 'success' : ($candidate->category === 'offering' ? 'warning' : ($candidate->category === 'reject' ? 'error' : 'info'))">{{ $categoryOptions[$candidate->category] ?? $candidate->category }}</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($candidate->category === 'shortlist')
                                            <form method="POST" action="{{ route('recruitment-candidates.move-category', $candidate) }}">
                                                @csrf
                                                <input type="hidden" name="category" value="offering">
                                                <button
                                                    type="submit"
                                                    @disabled($hasActiveOffer)
                                                    class="rounded-lg border px-3 py-2 text-sm font-medium transition {{ $hasActiveOffer ? 'cursor-not-allowed border-gray-200 bg-gray-50 text-gray-400 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-500' : 'border-brand-200 text-brand-600 hover:bg-brand-50 dark:border-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/10' }}"
                                                >
                                                    Sent Offering
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('recruitment-candidates.show', $candidate) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a>
                                        <a href="{{ route('recruitment-candidates.edit', $candidate) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a>
                                        <form method="POST" action="{{ route('recruitment-candidates.destroy', $candidate) }}" onsubmit="return confirm('Delete this candidate?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No candidates found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $candidates->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
