@extends('layouts.app')

@section('content')
    @php
        $eligibleDate = $employee->contract?->start_date?->copy()->addYear();
        $position = $employee->employeePosition?->position;
        $subDepartment = $position?->subDepartment;
        $department = $subDepartment?->department;
    @endphp

    <x-common.page-shell title="Annual Leave Detail" description="{{ $employee->nama_karyawan }}">
        <x-slot:actions>
            <a href="{{ route('annual-leaves.index', ['year' => $year]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Back</a>
        </x-slot:actions>

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Nama Karyawan</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->nama_karyawan }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">ID Absen</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->attendance_id ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Company</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->company?->company_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Department</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $department?->department_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Sub Department</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $subDepartment?->sub_department_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Jabatan</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $position?->position_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Level</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $employee->employeePosition?->level?->level_name ?: '-' }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium uppercase text-gray-400 dark:text-gray-500">Status Karyawan</p>
                    <p class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ ucfirst($employee->status_karyawan ?: '-') }}</p>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-6">
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Join Date</p>
                <p class="mt-2 text-lg font-semibold text-gray-900 dark:text-white/90">{{ $employee->contract?->start_date ? $employee->contract->start_date->format('d M Y') : '-' }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Hak Cuti</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ number_format($entitlement, 0) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Terpakai</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ number_format($used, 0) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Sisa Saldo</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ number_format($balance, 0) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Hutang Cuti Aktif</p>
                <p class="mt-2 text-2xl font-semibold text-error-600 dark:text-error-400">{{ number_format($debt, 0) }}</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Status Hari Ini</p>
                <p class="mt-2 text-lg font-semibold {{ $eligibleToday ? 'text-success-600' : 'text-warning-600' }}">{{ $eligibleToday ? 'Eligible' : 'Belum 1 tahun' }}</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-12">
            <section class="xl:col-span-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Input Cuti</h3>
                <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-800/60 dark:text-gray-300">
                    Sisa saldo tersedia: <span class="font-semibold text-gray-900 dark:text-white/90">{{ number_format($balance, 0) }} hari</span>
                </div>
                @unless ($eligibleToday)
                    <div class="mt-4 rounded-xl border border-warning-200 bg-warning-50 px-4 py-3 text-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                        Karyawan belum eligible annual leave. Cuti pribadi dan potong cuti baru bisa dilakukan mulai {{ $eligibleDate ? $eligibleDate->format('d M Y') : 'setelah data tanggal join lengkap' }}. Cuti khusus tetap bisa diinput karena tidak memotong saldo.
                    </div>
                @endunless
                <form method="POST" action="{{ route('annual-leaves.employee.store', [$employee, 'year' => $year]) }}" x-data="{
                    dateFrom: '',
                    dateTo: '',
                    totalDays() {
                        if (!this.dateFrom || !this.dateTo) return 0;
                        const start = new Date(this.dateFrom);
                        const end = new Date(this.dateTo);
                        if (end < start) return 0;
                        let days = 0;
                        const cursor = new Date(start);
                        while (cursor <= end) {
                            const day = cursor.getDay();
                            if (day !== 0 && day !== 6) days++;
                            cursor.setDate(cursor.getDate() + 1);
                        }
                        return days;
                    }
                }" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="year" value="{{ $year }}">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal</label>
                            <input type="date" name="date_from" x-model="dateFrom" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal</label>
                            <input type="date" name="date_to" x-model="dateTo" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Total Hari Diambil</label>
                        <input type="text" :value="`${totalDays()} hari kerja`" readonly class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-800 dark:border-gray-800 dark:bg-gray-800 dark:text-white/90">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Cuti</label>
                        <select name="leave_type" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @foreach ($leaveTypes as $type => $label)
                                <option value="{{ $type }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan</label>
                        <textarea name="notes" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90"></textarea>
                    </div>
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-5 text-sm font-medium text-white transition hover:bg-brand-600">Save Cuti</button>
                </form>
            </section>

            <section class="xl:col-span-8 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <div class="border-b border-gray-200 px-5 py-4 dark:border-gray-800">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Riwayat Cuti {{ $year }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/80">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Tanggal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Jenis</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Hari</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Source</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Efek Saldo</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Catatan</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse ($transactions as $transaction)
                                <tr>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->date_from->format('d M Y') }} @if(!$transaction->date_from->equalTo($transaction->date_to)) - {{ $transaction->date_to->format('d M Y') }} @endif</td>
                                    <td class="px-5 py-4 text-sm font-medium text-gray-900 dark:text-white/90">{{ $transaction->leave_type }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ number_format((float) $transaction->days, 0) }}</td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ ucfirst($transaction->source_type) }}</td>
                                    <td class="px-5 py-4 text-sm">
                                        @if ($transaction->balance_effect === 'pre_eligible_debt')
                                            <span class="inline-flex rounded-full bg-error-50 px-2.5 py-1 text-xs font-semibold text-error-700 dark:bg-error-500/10 dark:text-error-300">Hutang sampai {{ $transaction->written_off_at ? $transaction->written_off_at->format('d M Y') : '-' }}</span>
                                        @elseif ($transaction->balance_effect === 'no_balance')
                                            <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">Tidak potong saldo</span>
                                        @else
                                            <span class="inline-flex rounded-full bg-success-50 px-2.5 py-1 text-xs font-semibold text-success-700 dark:bg-success-500/10 dark:text-success-300">Potong saldo</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $transaction->notes ?: '-' }}</td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($transaction->source_type === 'collective')
                                            <span class="text-xs font-medium text-gray-400 dark:text-gray-500">Locked</span>
                                        @else
                                            <a href="{{ route('annual-leaves.transactions.edit', $transaction) }}" class="inline-flex rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Edit</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-14 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada transaksi cuti.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </x-common.page-shell>
@endsection
