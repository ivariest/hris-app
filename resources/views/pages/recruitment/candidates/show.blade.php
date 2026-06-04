@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Candidate Detail" description="Review candidate data and recruitment request assignment.">
        <x-slot:actions>
            <a href="{{ route('recruitment-candidates.edit', $candidate) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Edit</a>
            <a href="{{ route('recruitment-candidates.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Back to list</a>
        </x-slot:actions>

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-brand-600 dark:text-brand-300">{{ $candidate->request?->request_number ?? '-' }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $candidate->candidate_name }}</h2>
                    </div>
                    <x-ui.badge :color="$candidate->category === 'join' ? 'success' : (in_array($candidate->category, ['offering', 'agreement_sent'], true) ? 'warning' : ($candidate->category === 'reject' ? 'error' : 'info'))">{{ $categoryOptions[$candidate->category] ?? $candidate->category }}</x-ui.badge>
                </div>

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">No KTP</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $candidate->id_card_number ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Telepon</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $candidate->candidate_phone ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $candidate->candidate_email ?: '-' }}</p></div>
                    <div><p class="text-sm font-medium text-gray-500 dark:text-gray-400">Request</p><p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $candidate->request?->requested_position ?? '-' }}</p></div>
                </div>
            </section>

            <section class="space-y-6">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Alamat Kandidat</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $candidate->candidate_address ?: '-' }}</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Hasil Psikotes</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $candidate->psychotest_result ?: '-' }}</p>
                </div>
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Komentar</h3>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-300">{{ $candidate->comment ?: '-' }}</p>
                </div>
            </section>
        </div>
    </x-common.page-shell>
@endsection
