@php
    $vehicle = $vehicle ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="space-y-6 lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Data Kendaraan</h3>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="plate_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Polisi <span class="text-error-500">*</span></label>
                    <input type="text" id="plate_number" name="plate_number" value="{{ old('plate_number', $vehicle->plate_number ?? '') }}" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('plate_number')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="vehicle_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tipe Kendaraan</label>
                    <input type="text" id="vehicle_type" name="vehicle_type" value="{{ old('vehicle_type', $vehicle->vehicle_type ?? '') }}" placeholder="Contoh: Mobil box, motor operasional" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('vehicle_type')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="brand" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Merk</label>
                    <input type="text" id="brand" name="brand" value="{{ old('brand', $vehicle->brand ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('brand')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="manufacture_year" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tahun</label>
                    <input type="number" id="manufacture_year" name="manufacture_year" value="{{ old('manufacture_year', $vehicle->manufacture_year ?? '') }}" min="1900" max="{{ now()->format('Y') + 1 }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('manufacture_year')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="location_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lokasi Kendaraan</label>
                    <select id="location_id" name="location_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Pilih lokasi</option>
                        @foreach ($locations as $location)
                            <option value="{{ $location->id }}" @selected((int) old('location_id', $vehicle->location_id ?? 0) === (int) $location->id)>{{ $location->location_name }}</option>
                        @endforeach
                    </select>
                    @error('location_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="driver_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pengemudi</label>
                    <select id="driver_id" name="driver_id" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Pilih pengemudi</option>
                        @foreach ($drivers as $driver)
                            <option value="{{ $driver->id }}" @selected((int) old('driver_id', $vehicle->driver_id ?? 0) === (int) $driver->id)>{{ $driver->nama_karyawan }}{{ $driver->nik_karyawan ? ' - '.$driver->nik_karyawan : '' }}</option>
                        @endforeach
                    </select>
                    @error('driver_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes', $vehicle->notes ?? '') }}</textarea>
                    @error('notes')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Dokumen Kendaraan</h3>
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <div class="space-y-5 rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div>
                        <label for="stnk_document" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload STNK</label>
                        <input type="file" id="stnk_document" name="stnk_document" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400">
                        @error('stnk_document')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tax_valid_until" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Masa Berlaku Pajak</label>
                        <input type="date" id="tax_valid_until" name="tax_valid_until" value="{{ old('tax_valid_until', optional($vehicle?->tax_valid_until)->format('Y-m-d')) }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('tax_valid_until')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="plate_valid_until" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Masa Berlaku Plat</label>
                        <input type="date" id="plate_valid_until" name="plate_valid_until" value="{{ old('plate_valid_until', optional($vehicle?->plate_valid_until)->format('Y-m-d')) }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('plate_valid_until')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="space-y-5 rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                    <div>
                        <label for="kir_document" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload KIR</label>
                        <input type="file" id="kir_document" name="kir_document" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400">
                        @error('kir_document')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="kir_valid_until" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Masa Berlaku KIR</label>
                        <input type="date" id="kir_valid_until" name="kir_valid_until" value="{{ old('kir_valid_until', optional($vehicle?->kir_valid_until)->format('Y-m-d')) }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        @error('kir_valid_until')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Simpan Data</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Dokumen STNK dan KIR bisa diperbarui dari form edit kendaraan.</p>
            <input type="hidden" name="ownership_status" value="asset">
            <div class="mt-6 flex flex-col gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Simpan Kendaraan</button>
                <a href="{{ route('general-affair.vehicles.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
            </div>
        </div>
    </div>
</div>
