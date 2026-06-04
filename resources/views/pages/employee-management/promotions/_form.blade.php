@php
    $promotion = $promotion ?? null;
    $isEdit = filled($promotion);
    $formMode = $formMode ?? 'edit';
    $currentLevelId = $isEdit ? $promotion->old_level_id : null;
    $currentLevelName = $isEdit ? $promotion->oldLevel?->level_name : null;
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
        Ada beberapa field yang perlu dicek lagi.
    </div>
@endif

<form
    method="POST"
    action="{{ $formAction }}"
    enctype="multipart/form-data"
    x-data="promotionForm({
        employees: {{ \Illuminate\Support\Js::from($employeeOptions) }},
        positions: {{ \Illuminate\Support\Js::from($positions) }},
        levels: {{ \Illuminate\Support\Js::from($positionLevels->map(fn ($level) => [
            'id' => $level->id,
            'name' => $level->level_name,
        ])->values()) }},
        selectedEmployeeId: '{{ old('employee_id', $promotion->employee_id ?? '') }}',
        selectedPromotionType: '{{ old('promotion_type', $promotion->promotion_type ?? '') }}',
        selectedPositionId: '{{ old('new_position_id', $promotion->new_position_id ?? '') }}',
        selectedLevelId: '{{ old('new_level_id', $promotion->new_level_id ?? '') }}',
        currentLevelId: '{{ $currentLevelId ?? '' }}',
        currentLevelName: {{ \Illuminate\Support\Js::from($currentLevelName) }},
    })"
    x-init="init()"
    class="space-y-8"
>
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    <input type="hidden" name="form_mode" value="{{ $formMode }}">

    <section class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="employee_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Employee <span class="text-error-500">*</span></label>
            @if ($isEdit)
                <input type="text" value="{{ strtoupper($promotion->employee?->nama_karyawan ?? '-') }}{{ $promotion->employee?->nik_karyawan ? ' - '.$promotion->employee->nik_karyawan : '' }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            @else
                <select id="employee_id" name="employee_id" x-model="selectedEmployeeId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih employee</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}" @selected(old('employee_id', $promotion->employee_id ?? null) == $employee->id)>{{ strtoupper($employee->nama_karyawan) }} - {{ $employee->nik_karyawan }}</option>
                    @endforeach
                </select>
            @endif
            @error('employee_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="promotion_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Form Promosi</label>
            <input type="text" id="promotion_number" value="{{ $promotion->promotion_number ?? $nextPromotionNumber }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            @error('promotion_number')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company</label>
            <input type="text" :value="selectedEmployee.company_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
        </div>

        <div>
            <label for="promotion_type_display" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Promosi <span class="text-error-500">*</span></label>
            @if ($isEdit)
                <input type="text" id="promotion_type_display" value="{{ str_replace('_', ' ', old('promotion_type', $promotion->promotion_type ?? '')) }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                <input type="hidden" name="promotion_type" value="{{ old('promotion_type', $promotion->promotion_type ?? '') }}">
            @else
                <select id="promotion_type" name="promotion_type" x-model="promotionType" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih status promosi</option>
                    @foreach ($promotionTypeOptions as $option)
                        <option value="{{ $option }}" @selected(old('promotion_type', $promotion->promotion_type ?? '') === $option)>{{ str_replace('_', ' ', $option) }}</option>
                    @endforeach
                </select>
            @endif
            @error('promotion_type')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>
    </section>

    <section class="mt-4 mb-4 rounded-2xl border border-gray-200 bg-gray-50/60 p-5 dark:border-gray-800 dark:bg-gray-900/50">
        <div class="mb-4">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Current Assignment</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">Jabatan baru hanya bisa dipilih dari department employee saat ini.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-3">
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department Saat Ini</label>
                <input type="text" :value="selectedEmployee.current_department_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Saat Ini</label>
                <input type="text" :value="selectedEmployee.current_position_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pangkat Saat Ini</label>
                <input type="text" :value="selectedEmployee.current_level_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>
        </div>
    </section>

    @if ($formMode === 'edit')
        <section class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="new_position_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Baru <span class="text-error-500">*</span></label>
                <select id="new_position_id" name="new_position_id" x-model="selectedPositionId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedEmployee.current_department_id">
                    <option value="">Pilih jabatan baru</option>
                    <template x-for="position in filteredPositions()" :key="position.id">
                        <option :value="String(position.id)" x-text="String(position.label).toUpperCase()"></option>
                    </template>
                </select>
                @error('new_position_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="new_level_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pangkat Baru <span class="text-error-500">*</span></label>
                <select id="new_level_id" name="new_level_id" x-model="selectedLevelId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedEmployee.current_level_id">
                    <option value="">Pilih pangkat baru</option>
                    <template x-for="level in filteredLevels()" :key="level.id">
                        <option :value="String(level.id)" x-text="String(level.name).toUpperCase()"></option>
                    </template>
                </select>
                @error('new_level_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="start_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Mulai Berlaku <span class="text-error-500">*</span></label>
                <input type="text" id="start_date" name="start_date" value="{{ old('start_date', optional($promotion->start_date ?? null)->format('Y-m-d')) }}" required x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" placeholder="Pilih tanggal mulai" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('start_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div x-show="promotionType === 'PEJABAT_SEMENTARA'" x-cloak>
                <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Selesai <span class="text-error-500">*</span></label>
                <input type="text" id="end_date" name="end_date" value="{{ old('end_date', optional($promotion->end_date ?? null)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" placeholder="Pilih tanggal selesai" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('end_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="promotion_form" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload Form Promosi</label>
                <input type="file" id="promotion_form" name="promotion_form" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                @if (!empty($promotion?->promotion_form_path))
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File sudah tersimpan.</p>
                @endif
                @error('promotion_form')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
        </section>
    @endif

    @if ($formMode === 'resolve' && $isEdit && ($promotion->promotion_type ?? null) === 'PEJABAT_SEMENTARA')
        <section class="rounded-2xl border border-brand-200 bg-brand-50/50 p-5 dark:border-brand-500/20 dark:bg-brand-500/10">
            <div class="mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white/90">Tindak Lanjut PJS</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pilih jika PJS ini ingin dijadikan tetap atau dikembalikan ke jabatan sebelumnya.</p>
            </div>
            <div class="mb-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-2xl border border-gray-200 bg-white px-4 py-4 dark:border-gray-800 dark:bg-gray-900/60">
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jabatan Sebelumnya</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">{{ strtoupper($promotion->oldPosition?->position_name ?? '-') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($promotion->oldLevel?->level_name ?? '-') }}</p>
                </div>
                <div class="rounded-2xl border border-brand-200 bg-brand-50 px-4 py-4 dark:border-brand-500/20 dark:bg-brand-500/10">
                    <p class="text-xs font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-300">Jabatan PJS Saat Ini</p>
                    <p class="mt-2 text-sm font-semibold text-gray-900 dark:text-white/90">{{ strtoupper($promotion->newPosition?->position_name ?? '-') }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ strtoupper($promotion->newLevel?->level_name ?? '-') }}</p>
                </div>
            </div>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="temporary_action" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Aksi PJS <span class="text-error-500">*</span></label>
                    <select id="temporary_action" name="temporary_action" x-model="temporaryAction" class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih aksi</option>
                        @foreach ($temporaryActionOptions as $optionValue => $optionLabel)
                            <option value="{{ $optionValue }}" @selected(old('temporary_action') === $optionValue)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    @error('temporary_action')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div x-show="temporaryAction === 'REVERT_TO_PREVIOUS'" x-cloak>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Sebelumnya</label>
                    <input type="text" value="{{ strtoupper($promotion->oldPosition?->position_name ?? '-') }}{{ $promotion->oldLevel?->level_name ? ' | '.strtoupper($promotion->oldLevel->level_name) : '' }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                </div>

                <div x-show="temporaryAction === 'BECOME_PERMANENT'" x-cloak>
                    <label for="appointment_letter" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Upload SK Pengangkatan</label>
                    <input type="file" id="appointment_letter" name="appointment_letter" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                    @if (!empty($promotion?->appointment_letter_path))
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File sudah tersimpan.</p>
                    @endif
                    @error('appointment_letter')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div x-show="temporaryAction === 'EXTEND_TEMPORARY'" x-cloak>
                    <label for="start_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Periode Awal <span class="text-error-500">*</span></label>
                    <input type="text" id="start_date" name="start_date" value="{{ old('start_date', optional($promotion->start_date ?? null)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" placeholder="Pilih periode awal" class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                    @error('start_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div x-show="temporaryAction === 'EXTEND_TEMPORARY'" x-cloak>
                    <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Periode Akhir <span class="text-error-500">*</span></label>
                    <input type="text" id="end_date" name="end_date" value="{{ old('end_date', optional($promotion->end_date ?? null)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" placeholder="Pilih periode akhir" class="h-11 w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                    @error('end_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>
    @endif

    <section class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Keterangan</label>
            <textarea id="notes" name="notes" rows="4" placeholder="Tambahkan catatan promosi jika diperlukan" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('notes', $promotion->notes ?? '') }}</textarea>
            @error('notes')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
        <a href="{{ route('employee-promotions.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>
</form>

@push('scripts')
    <script>
        function promotionForm(config) {
            return {
                employees: config.employees || [],
                positions: config.positions || [],
                levels: config.levels || [],
                selectedEmployeeId: config.selectedEmployeeId || '',
                promotionType: config.selectedPromotionType || '',
                selectedPositionId: config.selectedPositionId || '',
                selectedLevelId: config.selectedLevelId || '',
                currentLevelId: config.currentLevelId || '',
                currentLevelName: config.currentLevelName || '',
                temporaryAction: '{{ old('temporary_action') }}',
                levelRanks: {
                    'STAFF': 1,
                    'COORDINATOR': 2,
                    'KEPALA SEKSI': 3,
                    'SECT HEAD': 4,
                    'SUB DEPT HEAD': 5,
                    'DEPT HEAD': 6,
                    'DIRECTOR': 7,
                },
                selectedEmployee: {
                    company_name: '',
                    current_position_name: '',
                    current_level_id: '',
                    current_level_name: '',
                    current_department_id: '',
                    current_department_name: '',
                },
                init() {
                    this.syncSelectedEmployee();
                    this.$watch('selectedEmployeeId', () => {
                        this.syncSelectedEmployee();
                        this.selectedPositionId = '';
                        this.selectedLevelId = '';
                    });
                },
                syncSelectedEmployee() {
                    const found = this.employees.find((employee) => String(employee.id) === String(this.selectedEmployeeId));
                    this.selectedEmployee = found ? { ...found } : {
                        company_name: '',
                        current_position_name: '',
                        current_level_id: '',
                        current_level_name: '',
                        current_department_id: '',
                        current_department_name: '',
                    };

                    if (this.currentLevelId) {
                        this.selectedEmployee.current_level_id = this.currentLevelId;
                        this.selectedEmployee.current_level_name = this.currentLevelName || this.selectedEmployee.current_level_name;
                    }
                },
                filteredPositions() {
                    if (!this.selectedEmployee.current_department_id) {
                        return [];
                    }

                    return this.positions.filter((position) => String(position.department_id) === String(this.selectedEmployee.current_department_id));
                },
                normalizeLevelName(name) {
                    return String(name || '').trim().replace(/\s+/g, ' ').toUpperCase();
                },
                levelRank(name) {
                    return this.levelRanks[this.normalizeLevelName(name)] || null;
                },
                currentLevelRank() {
                    return this.levelRank(this.selectedEmployee.current_level_name);
                },
                filteredLevels() {
                    const currentRank = this.currentLevelRank();

                    if (!currentRank) {
                        return [];
                    }

                    return this.levels.filter((level) => {
                        const rank = this.levelRank(level.name);

                        return rank && rank > currentRank;
                    });
                },
            };
        }
    </script>
@endpush
