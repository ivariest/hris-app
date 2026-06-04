@extends('layouts.app')

@section('content')
    <x-common.page-shell title="New Employee Request Detail" description="Review request detail and related candidates.">
        <x-slot:actions>
            <a href="{{ route('recruitment-candidates.create', ['recruitment_request_id' => $request->id]) }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Add candidate</a>
            <a href="{{ route('recruitment-requests.edit', $request) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a>
        </x-slot:actions>

        <div class="grid gap-6 xl:grid-cols-[0.8fr_1.2fr]">
            <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-medium text-brand-600 dark:text-brand-300">{{ $request->request_number }}</p>
                        <h2 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $request->requested_position }}</h2>
                    </div>
                    <x-ui.badge :color="$request->status === 'done' ? 'success' : ($request->status === 'on_going' ? 'info' : 'warning')">{{ $statusOptions[$request->status] ?? $request->status }}</x-ui.badge>
                </div>

                @if ($request->status === 'pending')
                    <div class="mt-6 rounded-2xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                        <span class="font-medium">Alasan Pending:</span> {{ $request->pending_reason ?: '-' }}
                    </div>
                @endif

                <div class="mt-8 grid gap-5 sm:grid-cols-2">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Pemohon</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->requester?->nama_karyawan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Permohonan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->request_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Divisi Pemohon</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->requester_department ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jabatan / Level Pemohon</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ collect([$request->requester_position, $request->requester_level])->filter()->implode(' / ') ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nama Perusahaan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->company?->company_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Permintaan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $requestStatusOptions[$request->request_status] ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jumlah Dibutuhkan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->needed_count }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tanggal Dibutuhkan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->needed_date?->format('d F Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jenis Kelamin / Usia</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ collect([$request->gender, $request->age])->filter()->implode(' / ') ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Alasan Permintaan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $requestReasonOptions[$request->request_reason] ?? '-' }}</p>
                    </div>
                    @if ($request->request_reason === 'replacement')
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Alasan Replacement</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $replacementReasonOptions[$request->replacement_reason] ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Keterangan Replacement</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->replacement_note ?: '-' }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Status Karyawan</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->employee_status_agreement ?: '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Lama Kontrak</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ filled($request->employment_duration_months) ? $request->employment_duration_months.' bulan' : '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Media Posting</p>
                        <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $postingMediaOptions[$request->posting_media] ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-8 space-y-4">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendidikan Minimal</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->minimum_education ?: '-' }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Jurusan</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white/90">{{ $request->major ?: '-' }}</p>
                        </div>
                    </div>
                    @foreach ([
                        'Persyaratan Khusus' => $request->special_requirements,
                        'Persyaratan Umum' => $request->general_requirements,
                        'Ringkasan Jobdesk' => $request->job_summary,
                    ] as $label => $value)
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $label }}</p>
                            <div class="mt-2 whitespace-pre-line rounded-2xl border border-gray-200 bg-gray-50/70 px-4 py-3 text-sm text-gray-700 dark:border-gray-800 dark:bg-gray-900/50 dark:text-gray-300">{{ $value ?: '-' }}</div>
                        </div>
                    @endforeach
                </div>
                    </div>
                </div>
            </section>

            <section class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Candidates</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/80">
                            <tr><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Name</th><th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Category</th><th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Action</th></tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($request->candidates as $candidate)
                                <tr>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                        <div class="font-medium text-gray-900 dark:text-white/90">{{ $candidate->candidate_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $candidate->candidate_email ?: '-' }}</div>
                                    </td>
                                    <td class="px-5 py-4"><x-ui.badge :color="$candidate->category === 'join' ? 'success' : (in_array($candidate->category, ['offering', 'agreement_sent'], true) ? 'warning' : ($candidate->category === 'reject' ? 'error' : 'info'))">{{ ucwords(str_replace('_', ' ', $candidate->category)) }}</x-ui.badge></td>
                                    <td class="px-5 py-4 text-right"><a href="{{ route('recruitment-candidates.show', $candidate) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-5 py-12 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada kandidat untuk request ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </x-common.page-shell>
@endsection
