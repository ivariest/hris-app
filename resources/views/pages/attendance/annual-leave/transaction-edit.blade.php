@extends('layouts.app')

@section('content')
    <x-common.page-shell title="Edit Cuti" description="{{ $employee->nama_karyawan }}">
        <x-slot:actions>
            <a href="{{ route('annual-leaves.show', [$employee, 'year' => $transaction->year]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Back</a>
        </x-slot:actions>

        <form method="POST" action="{{ route('annual-leaves.transactions.update', $transaction) }}" x-data="{
            dateFrom: @js(old('date_from', $transaction->date_from->format('Y-m-d'))),
            dateTo: @js(old('date_to', $transaction->date_to->format('Y-m-d'))),
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
        }">
            @csrf
            @method('PUT')
            <div class="grid gap-6 lg:grid-cols-12">
                <section class="lg:col-span-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="year" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun <span class="text-error-500">*</span></label>
                            <input type="number" id="year" name="year" value="{{ old('year', $transaction->year) }}" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label for="leave_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Cuti <span class="text-error-500">*</span></label>
                            <select id="leave_type" name="leave_type" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @foreach ($leaveTypes as $type => $label)
                                    <option value="{{ $type }}" @selected(old('leave_type', $transaction->leave_type) === $type)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal <span class="text-error-500">*</span></label>
                            <input type="date" id="date_from" name="date_from" x-model="dateFrom" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal <span class="text-error-500">*</span></label>
                            <input type="date" id="date_to" name="date_to" x-model="dateTo" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Total Hari Diambil</label>
                            <input type="text" :value="`${totalDays()} hari kerja`" readonly class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-800 dark:border-gray-800 dark:bg-gray-800 dark:text-white/90">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sisa Saldo Tersedia</label>
                            <input type="text" value="{{ number_format($availableBalance, 0) }} hari" readonly class="h-11 w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm font-semibold text-gray-800 dark:border-gray-800 dark:bg-gray-800 dark:text-white/90">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan</label>
                            <textarea id="notes" name="notes" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes', $transaction->notes) }}</textarea>
                        </div>
                    </div>
                </section>
                <aside class="lg:col-span-4">
                    <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Update riwayat cuti</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Cuti bersama dikunci dari detail karyawan dan hanya bisa diubah melalui menu Cuti Bersama.</p>
                        <div class="mt-6 flex flex-col gap-3">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Update</button>
                            <a href="{{ route('annual-leaves.show', [$employee, 'year' => $transaction->year]) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Cancel</a>
                        </div>
                    </div>
                </aside>
            </div>
        </form>
    </x-common.page-shell>
@endsection
