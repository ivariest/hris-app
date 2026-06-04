@php
    $service = $service ?? null;
@endphp

<div class="grid gap-6 lg:grid-cols-12">
    <div class="lg:col-span-8">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Rekap Service</h3>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div>
                    <label for="vehicle_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kendaraan <span class="text-error-500">*</span></label>
                    <select id="vehicle_id" name="vehicle_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">Pilih kendaraan</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected((int) old('vehicle_id', $selectedVehicleId ?? $service->vehicle_id ?? 0) === (int) $vehicle->id)>{{ $vehicle->plate_number }}{{ collect([$vehicle->vehicle_type, $vehicle->brand])->filter()->isNotEmpty() ? ' - '.collect([$vehicle->vehicle_type, $vehicle->brand])->filter()->implode(' | ') : '' }}</option>
                        @endforeach
                    </select>
                    @error('vehicle_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="service_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Service <span class="text-error-500">*</span></label>
                    <input type="date" id="service_date" name="service_date" value="{{ old('service_date', optional($service?->service_date)->format('Y-m-d') ?? now()->toDateString()) }}" required onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('service_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="service_type" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Service <span class="text-error-500">*</span></label>
                    <input type="text" id="service_type" name="service_type" value="{{ old('service_type', $service->service_type ?? '') }}" required placeholder="Service rutin, ganti oli, perbaikan rem" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    @error('service_type')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="workshop_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Bengkel / Vendor</label>
                    <input type="text" id="workshop_name" name="workshop_name" value="{{ old('workshop_name', $service->workshop_name ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="odometer" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">KM Service</label>
                    <input type="number" id="odometer" name="odometer" value="{{ old('odometer', $service->odometer ?? '') }}" min="0" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="cost" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Biaya</label>
                    <input type="number" id="cost" name="cost" value="{{ old('cost', $service->cost ?? 0) }}" min="0" step="1000" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="next_service_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Service Berikutnya</label>
                    <input type="date" id="next_service_date" name="next_service_date" value="{{ old('next_service_date', optional($service?->next_service_date)->format('Y-m-d')) }}" onclick="this.showPicker()" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div>
                    <label for="next_service_odometer" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">KM Service Berikutnya</label>
                    <input type="number" id="next_service_odometer" name="next_service_odometer" value="{{ old('next_service_odometer', $service->next_service_odometer ?? '') }}" min="0" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Keterangan</label>
                    <textarea id="description" name="description" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('description', $service->description ?? '') }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label for="document" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload Nota / Dokumen Service</label>
                    <input type="file" id="document" name="document" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400">
                </div>
            </div>
        </div>
    </div>

    <div class="lg:col-span-4">
        <div class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Simpan Rekap</h3>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Data ini akan masuk ke report service kendaraan.</p>
            <div class="mt-6 flex flex-col gap-3">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Simpan Service</button>
                <a href="{{ route('general-affair.vehicle-services.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
            </div>
        </div>
    </div>
</div>
