@extends('layouts.app')

@php
    $dateValue = fn ($date) => $date ? $date->format('d M Y') : '-';
    $money = fn ($amount) => 'Rp '.number_format((float) $amount, 0, ',', '.');
@endphp

@section('content')
    <x-common.page-shell title="Service Kendaraan" description="Input, edit, dan report rekap service kendaraan.">
        <x-slot:actions>
            <a href="{{ route('general-affair.vehicle-services.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Input service</a>
        </x-slot:actions>

        <form method="GET" action="{{ route('general-affair.vehicle-services.index') }}" class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <div class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <label for="vehicle_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kendaraan</label>
                    <select id="vehicle_id" name="vehicle_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Semua kendaraan</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected((int) $vehicleId === (int) $vehicle->id)>{{ $vehicle->plate_number }}{{ collect([$vehicle->vehicle_type, $vehicle->brand])->filter()->isNotEmpty() ? ' - '.collect([$vehicle->vehicle_type, $vehicle->brand])->filter()->implode(' | ') : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Dari Tanggal</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="lg:col-span-3">
                    <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sampai Tanggal</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="flex items-end gap-2 lg:col-span-2">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Filter</button>
                </div>
            </div>
        </form>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Service</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $summary['total_services'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Biaya</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $money($summary['total_cost']) }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Kendaraan Diservice</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $summary['vehicles_serviced'] }}</p>
            </div>
            <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                <p class="text-sm text-gray-500 dark:text-gray-400">Jadwal 30 Hari</p>
                <p class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white/90">{{ $summary['upcoming_services'] }}</p>
            </div>
        </section>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-[1200px] divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Kendaraan</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Service</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">KM</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Biaya</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Next Service</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($services as $service)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $service->vehicle?->plate_number }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ collect([$service->vehicle?->vehicle_type, $service->vehicle?->brand])->filter()->implode(' | ') ?: '-' }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $dateValue($service->service_date) }}</td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $service->service_type }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $service->workshop_name ?: '-' }}</div>
                                    @if ($service->document_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($service->document_path) }}" target="_blank" class="mt-1 inline-flex text-xs font-semibold text-brand-500 hover:text-brand-600">View document</a>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right text-sm text-gray-700 dark:text-gray-300">{{ $service->odometer ? number_format($service->odometer, 0, ',', '.') : '-' }}</td>
                                <td class="px-5 py-4 text-right text-sm font-semibold text-gray-900 dark:text-white/90">{{ $money($service->cost) }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ $dateValue($service->next_service_date) }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $service->next_service_odometer ? number_format($service->next_service_odometer, 0, ',', '.').' KM' : '-' }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('general-affair.vehicle-services.edit', $service) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Edit</a>
                                        <form method="POST" action="{{ route('general-affair.vehicle-services.destroy', $service) }}" onsubmit="return confirm('Delete this service record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-error-200 px-3 py-2 text-sm font-medium text-error-600 transition hover:bg-error-50 dark:border-error-500/20 dark:text-error-400">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada rekap service pada filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $services->links() }}</div>
        </div>
    </x-common.page-shell>
@endsection
