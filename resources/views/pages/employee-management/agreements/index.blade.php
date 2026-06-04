@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Employee Agreement" description="Manage agreement list and create new employee agreements.">
        <x-slot:actions>
            <a href="{{ route('employee-agreements.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">New agreement</a>
        </x-slot:actions>

        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('employee-agreements.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-5">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Search agreement number, request, employee name" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div class="lg:col-span-4">
                    <label for="agreement_status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agreement Status</label>
                    <select id="agreement_status" name="agreement_status" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">All</option>
                        @foreach ($agreementStatusOptions as $option)
                            <option value="{{ $option }}" @selected($agreementStatus === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end lg:col-span-3 lg:justify-end">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600 lg:w-auto lg:min-w-[110px]">Filter</button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Agreement</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Request</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Employee</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Contract</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Salary</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($agreements as $agreement)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->agreement_number }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $agreement->agreement_status }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->request?->request_number ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $agreement->request?->requested_position ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div class="font-medium text-gray-900 dark:text-white/90">{{ $agreement->employee_name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $agreement->department ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div>{{ $agreement->contract_start_date?->format('d M Y') ?? '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $agreement->contract_end_date?->format('d M Y') ?? '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    <div>{{ filled($agreement->base_salary) ? 'Rp '.number_format((float) $agreement->base_salary, 0, ',', '.') : '-' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Tunjangan & makan terpisah</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('employee-agreements.show', $agreement) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">View</a>
                                        <a href="{{ route('employee-agreements.print', $agreement) }}" target="_blank" class="rounded-lg border border-brand-200 px-3 py-2 text-sm font-medium text-brand-600 transition hover:bg-brand-50 dark:border-brand-500/20 dark:text-brand-300 dark:hover:bg-brand-500/10">Create PDF</a>
                                        <a href="{{ route('employee-agreements.edit', $agreement) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Edit</a>
                                        <form method="POST" action="{{ route('employee-agreements.destroy', $agreement) }}" onsubmit="return confirm('Delete this agreement?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400 dark:hover:bg-error-500/10">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">No employee agreements found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $agreements->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
