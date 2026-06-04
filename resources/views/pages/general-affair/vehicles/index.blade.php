@extends('layouts.app')

@php
    $dateValue = fn ($date) => $date ? $date->format('d M Y') : '-';
    $expiryClass = function ($date): string {
        if (! $date) {
            return 'text-gray-500 dark:text-gray-400';
        }

        return $date->isPast()
            ? 'text-error-600 dark:text-error-400'
            : ($date->lte(now()->addDays(30)) ? 'text-warning-600 dark:text-warning-400' : 'text-gray-700 dark:text-gray-300');
    };
@endphp

@section('content')
    <div
        x-data="{
            stnkModalOpen: false,
            stnkUpdateUrl: '',
            stnkPlateNumber: '',
            taxValidUntil: '',
            plateValidUntil: '',
            kirModalOpen: false,
            kirUpdateUrl: '',
            kirPlateNumber: '',
            kirValidUntil: '',
            openStnkModal(vehicle) {
                this.stnkUpdateUrl = vehicle.url;
                this.stnkPlateNumber = vehicle.plateNumber;
                this.taxValidUntil = vehicle.taxValidUntil || '';
                this.plateValidUntil = vehicle.plateValidUntil || '';
                this.stnkModalOpen = true;
            },
            openKirModal(vehicle) {
                this.kirUpdateUrl = vehicle.url;
                this.kirPlateNumber = vehicle.plateNumber;
                this.kirValidUntil = vehicle.kirValidUntil || '';
                this.kirModalOpen = true;
            },
        }"
        @open-stnk-modal.window="openStnkModal($event.detail)"
        @open-kir-modal.window="openKirModal($event.detail)"
    >
        <x-common.page-shell title="Daftar Kendaraan" description="Master kendaraan asset General Affair dan monitoring dokumen.">
        <x-slot:actions>
            <a href="{{ route('general-affair.vehicles.create') }}" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Input kendaraan</a>
        </x-slot:actions>

        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-6">
            <form method="GET" action="{{ route('general-affair.vehicles.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-10">
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Search</label>
                    <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Cari nomor polisi, tipe kendaraan, atau merk" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="flex items-end gap-2 lg:col-span-2">
                    <button type="submit" class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Filter</button>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
            <div class="overflow-x-auto">
                <table class="min-w-[1250px] divide-y divide-gray-200 dark:divide-gray-800">
                    <thead class="bg-gray-50 dark:bg-gray-900/80">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nomor Polisi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Detail</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Lokasi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Pengemudi</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Masa Pajak</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Masa Plat</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Masa KIR</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse ($vehicles as $vehicle)
                            <tr class="hover:bg-gray-50/80 dark:hover:bg-white/[0.02]">
                                <td class="px-5 py-4">
                                    <div class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $vehicle->plate_number }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $vehicle->services_count }} service record</div>
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">
                                    {{ collect([$vehicle->vehicle_type, $vehicle->brand, $vehicle->manufacture_year])->filter()->implode(' | ') ?: '-' }}
                                </td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $vehicle->location?->location_name ?: '-' }}</td>
                                <td class="px-5 py-4 text-sm text-gray-700 dark:text-gray-300">{{ $vehicle->driver?->nama_karyawan ?: '-' }}</td>
                                <td class="px-5 py-4 text-sm">
                                    <div class="{{ $expiryClass($vehicle->tax_valid_until) }}">{{ $dateValue($vehicle->tax_valid_until) }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <div class="{{ $expiryClass($vehicle->plate_valid_until) }}">{{ $dateValue($vehicle->plate_valid_until) }}</div>
                                </td>
                                <td class="px-5 py-4 text-sm">
                                    <div class="{{ $expiryClass($vehicle->kir_valid_until) }}">{{ $dateValue($vehicle->kir_valid_until) }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-2">
                                        <div x-data="{ open: false }" class="relative">
                                            <button
                                                type="button"
                                                @click="open = !open"
                                                class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-3 py-2 text-sm font-medium text-white transition hover:bg-brand-600"
                                            >
                                                Update
                                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                                    <path d="M5 7.5L10 12.5L15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                            </button>

                                            <div x-show="open" x-cloak @click.outside="open = false" class="absolute right-0 z-20 mt-2 w-44 overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900">
                                                <button
                                                    type="button"
                                                    @click="
                                                        open = false;
                                                        $dispatch('open-stnk-modal', {
                                                            url: @js(route('general-affair.vehicles.update-stnk', $vehicle)),
                                                            plateNumber: @js($vehicle->plate_number),
                                                            taxValidUntil: @js(optional($vehicle->tax_valid_until)->format('Y-m-d')),
                                                            plateValidUntil: @js(optional($vehicle->plate_valid_until)->format('Y-m-d')),
                                                        })
                                                    "
                                                    class="block w-full px-4 py-3 text-left text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                                                >
                                                    STNK
                                                </button>
                                                <button
                                                    type="button"
                                                    @click="
                                                        open = false;
                                                        $dispatch('open-kir-modal', {
                                                            url: @js(route('general-affair.vehicles.update-kir', $vehicle)),
                                                            plateNumber: @js($vehicle->plate_number),
                                                            kirValidUntil: @js(optional($vehicle->kir_valid_until)->format('Y-m-d')),
                                                        })
                                                    "
                                                    class="block w-full px-4 py-3 text-left text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/[0.04]"
                                                >
                                                    KIR
                                                </button>
                                            </div>
                                        </div>
                                        <a href="{{ route('general-affair.vehicles.show', $vehicle) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">View</a>
                                        <a href="{{ route('general-affair.vehicles.edit', $vehicle) }}" class="rounded-lg border border-gray-200 px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Edit</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-16 text-center text-sm text-gray-500 dark:text-gray-400">Belum ada data kendaraan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="border-t border-gray-200 px-5 py-4 dark:border-gray-800">{{ $vehicles->links() }}</div>
        </div>
        </x-common.page-shell>

        <div x-show="stnkModalOpen" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
            <div @click.outside="stnkModalOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Update STNK</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="stnkPlateNumber"></p>
                    </div>
                    <button type="button" @click="stnkModalOpen = false" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">&times;</button>
                </div>

                <form method="POST" :action="stnkUpdateUrl" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="modal_stnk_document" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload STNK</label>
                        <input type="file" id="modal_stnk_document" name="stnk_document" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400">
                        @error('stnk_document')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="modal_tax_valid_until" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Masa Berlaku Pajak</label>
                            <input type="date" id="modal_tax_valid_until" name="tax_valid_until" x-model="taxValidUntil" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('tax_valid_until')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="modal_plate_valid_until" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Masa Berlaku Plat</label>
                            <input type="date" id="modal_plate_valid_until" name="plate_valid_until" x-model="plateValidUntil" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            @error('plate_valid_until')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="stnkModalOpen = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Update</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="kirModalOpen" x-cloak class="fixed inset-0 z-99999 flex items-center justify-center bg-gray-900/50 p-4">
            <div @click.outside="kirModalOpen = false" class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-xl dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Extend KIR</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="kirPlateNumber"></p>
                    </div>
                    <button type="button" @click="kirModalOpen = false" class="text-2xl leading-none text-gray-400 hover:text-gray-700 dark:hover:text-gray-200">&times;</button>
                </div>

                <form method="POST" :action="kirUpdateUrl" enctype="multipart/form-data" class="mt-6 space-y-5">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="modal_kir_document" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload KIR</label>
                        <input type="file" id="modal_kir_document" name="kir_document" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400">
                        @error('kir_document')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_kir_valid_until" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Masa Berlaku KIR</label>
                        <input type="date" id="modal_kir_valid_until" name="kir_valid_until" x-model="kirValidUntil" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('kir_valid_until')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="kirModalOpen = false" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Cancel</button>
                        <button type="submit" class="rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
