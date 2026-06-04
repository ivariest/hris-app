@extends('layouts.app')

@php
    $value = fn ($value) => filled($value) ? $value : '-';
    $dateValue = fn ($date) => $date ? $date->format('d M Y') : '-';
@endphp

@section('content')
    <x-common.page-shell title="{{ $vehicle->plate_number }}" description="{{ collect([$vehicle->vehicle_type, $vehicle->brand, $vehicle->manufacture_year])->filter()->implode(' | ') ?: 'Detail kendaraan asset' }}">
            <x-slot:actions>
                <a href="{{ route('general-affair.vehicles.edit', $vehicle) }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Edit</a>
                <a href="{{ route('general-affair.vehicles.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300">Back</a>
            </x-slot:actions>

            <div class="grid gap-6 xl:grid-cols-12">
            <div class="space-y-6 xl:col-span-8">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Detail Kendaraan</h3>
                    <dl class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ([
                            'Nomor Polisi' => $vehicle->plate_number,
                            'Tipe Kendaraan' => $vehicle->vehicle_type,
                            'Merk' => $vehicle->brand,
                            'Tahun' => $vehicle->manufacture_year,
                            'Lokasi Kendaraan' => $vehicle->location?->location_name,
                            'Pengemudi' => $vehicle->driver?->nama_karyawan,
                            'Catatan' => $vehicle->notes,
                        ] as $label => $item)
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                <dt class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ $label }}</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900 dark:text-white/90">{{ $value($item) }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>

            <div class="space-y-6 xl:col-span-4">
                <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Dokumen</h3>
                    <div class="mt-5 space-y-3">
                        @foreach ([
                            'Masa Berlaku Pajak' => [$vehicle->tax_valid_until, $vehicle->stnk_document_path],
                            'Masa Berlaku Plat' => [$vehicle->plate_valid_until, $vehicle->stnk_document_path],
                            'Masa Berlaku KIR' => [$vehicle->kir_valid_until, $vehicle->kir_document_path],
                        ] as $label => [$validUntil, $path])
                            <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">{{ $label }}</p>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Berlaku: {{ $dateValue($validUntil) }}</p>
                                    </div>
                                    @if ($path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($path) }}" target="_blank" class="text-sm font-semibold text-brand-500 hover:text-brand-600">View</a>
                                    @else
                                        <span class="text-sm text-gray-400">-</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            </div>
    </x-common.page-shell>
@endsection
