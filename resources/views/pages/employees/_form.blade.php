@php
    $employee = $employee ?? null;
    $isEdit = filled($employee);
    $wizardSteps = [
        'Agreement',
        'Employee Profile',
        'Biodata Diri',
        'Data Istri / Suami',
        'BPJS & Tax',
        'Bank & Payroll',
        'Employee Document',
    ];
    $contract = $employee?->contract;
    $personal = $employee?->personal;
    $spouse = $employee?->spouse;
    $bpjs = $employee?->bpjs;
    $tax = $employee?->tax;
    $bank = $employee?->bank;
    $payroll = $employee?->payroll;
    $agreementOptions = $agreementOptions ?? [];
    $selectedJobAgreement = (string) old('job_agreement', $contract->job_agreement ?? '');
    $matchedAgreement = collect($agreementOptions)->firstWhere('agreement_number', $selectedJobAgreement);
    $selectedAgreementId = (string) ($matchedAgreement['id'] ?? '');
    $agreementSourceMode = $matchedAgreement ? 'agreement' : 'manual';
    $selectedCompanyId = (string) old('company_id', $employee->company_id ?? '');
    $selectedDepartmentId = (string) old('department_id', $employee->employeePosition?->position?->subDepartment?->department_id ?? '');
    $selectedSubDepartmentId = (string) old('sub_department_id', $employee->employeePosition?->position?->sub_department_id ?? '');
    $selectedLevelId = (string) old('level_id', $employee->employeePosition?->level_id ?? $employee->employeePosition?->position?->level_id ?? '');
    $selectedPositionId = (string) old('position_id', $employee->employeePosition?->position_id ?? '');
    $selectedLocationId = (string) old('location_id', $employee->employeePosition?->location_id ?? '');
    $selectedSupervisorId = (string) old('atasan_langsung', $employee->employeePosition?->atasan_langsung ?? '');
    $selectedArea = (string) old('area', $employee->employeePosition?->area ?? '');
    $children = old(
        'children',
        $employee?->children?->map(fn ($child) => [
            'nik' => $child->nik,
            'nama' => $child->nama,
            'tempat_lahir' => $child->tempat_lahir,
            'tgl_lahir' => optional($child->tgl_lahir)->format('Y-m-d'),
            'pendidikan' => $child->pendidikan,
        ])->values()->all() ?? []
    );
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
        Ada beberapa field yang perlu dicek lagi.
    </div>
@endif

<style>
    .autofill-flash {
        animation: autofill-flash-pulse 1.4s ease-out;
    }

    @keyframes autofill-flash-pulse {
        0% {
            background-color: rgba(34, 197, 94, 0.22);
            border-color: rgb(34, 197, 94);
            box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.24);
        }

        55% {
            background-color: rgba(34, 197, 94, 0.14);
            border-color: rgb(34, 197, 94);
            box-shadow: 0 0 0 6px rgba(34, 197, 94, 0.12);
        }

        100% {
            background-color: transparent;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
        }
    }
</style>

<div
    x-data="employeeWizard({
        companies: {{ \Illuminate\Support\Js::from($companies) }},
        departments: {{ \Illuminate\Support\Js::from($departments) }},
        subDepartments: {{ \Illuminate\Support\Js::from($subDepartments) }},
        positions: {{ \Illuminate\Support\Js::from($positions) }},
        agreementOptions: {{ \Illuminate\Support\Js::from($agreementOptions) }},
        positionLevels: {{ \Illuminate\Support\Js::from($positionLevels) }},
        selectedCompanyId: '{{ $selectedCompanyId }}',
        selectedDepartmentId: '{{ $selectedDepartmentId }}',
        selectedSubDepartmentId: '{{ $selectedSubDepartmentId }}',
        selectedLevelId: '{{ $selectedLevelId }}',
        selectedPositionId: '{{ $selectedPositionId }}',
        selectedJobAgreement: '{{ $selectedJobAgreement }}',
        selectedAgreementId: '{{ $selectedAgreementId }}',
        agreementSourceMode: '{{ $agreementSourceMode }}',
        existingCompanyId: '{{ (string) ($employee->company_id ?? '') }}',
        currentNik: '{{ old('nik_karyawan', $employee->nik_karyawan ?? '') }}',
        nikPreviews: {{ \Illuminate\Support\Js::from($nikPreviews) }},
        initialChildren: {{ \Illuminate\Support\Js::from($children) }},
    })"
    x-init="init()"
    class="space-y-6"
>
    <div x-show="validationMessage" x-cloak class="rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
        <span x-text="validationMessage"></span>
    </div>

    <div class="space-y-6">
        <x-common.wizard-stepper :steps="$wizardSteps" />

        <section x-show="step === 1" x-cloak x-ref="step1" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Agreement</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2 rounded-2xl border border-gray-200 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-900/40">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white/90">Sumber Nomor Kontrak</h4>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pilih agreement untuk autofill, atau tulis nomor kontrak manual.</p>
                        </div>
                        <div class="inline-flex overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                            <button type="button" @click="agreementSourceMode = 'manual'; selectedAgreementId = ''" :class="agreementSourceMode === 'manual' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'" class="px-4 py-2 text-sm font-medium transition">Manual</button>
                            <button type="button" @click="agreementSourceMode = 'agreement'" :class="agreementSourceMode === 'agreement' ? 'bg-brand-500 text-white' : 'bg-white text-gray-700 dark:bg-gray-900 dark:text-gray-300'" class="px-4 py-2 text-sm font-medium transition">Dari Agreement</button>
                        </div>
                    </div>
                    <input type="hidden" name="job_agreement" x-model="selectedJobAgreement">

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div x-show="agreementSourceMode === 'manual'" x-cloak>
                            <label for="job_agreement" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Kontrak <span class="text-error-500">*</span></label>
                            <input type="text" id="job_agreement" x-model="selectedJobAgreement" maxlength="100" :required="agreementSourceMode === 'manual'" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                            @error('job_agreement')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                        </div>

                        <div x-show="agreementSourceMode === 'agreement'" x-cloak class="sm:col-span-2">
                            <label for="agreement_selector" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pilih Employee Agreement <span class="text-error-500">*</span></label>
                            <select id="agreement_selector" x-model="selectedAgreementId" @change="selectedAgreementId = $event.target.value; applySelectedAgreement()" :required="agreementSourceMode === 'agreement'" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">Pilih agreement</option>
                                <template x-for="agreement in agreementOptions" :key="agreement.id">
                                    <option :value="String(agreement.id)" x-text="agreement.label"></option>
                                </template>
                            </select>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Saat agreement dipilih, data employee akan terisi otomatis.</p>
                            <div x-show="agreementNotice" x-cloak class="mt-3 rounded-xl border border-warning-200 bg-warning-50 px-3 py-2 text-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                                <span x-text="agreementNotice"></span>
                            </div>
                        </div>

                        <div>
                            <label for="agreement_status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agreement Status</label>
                            <select id="agreement_status" name="agreement_status" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                <option value="">Pilih agreement status</option>
                                @foreach ($agreementStatusOptions as $option)
                                    <option value="{{ $option }}" @selected(old('agreement_status', $contract->status_kontrak ?? '') === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="start_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Start Date</label>
                            <input type="text" id="start_date" name="start_date" value="{{ old('start_date', optional($contract?->start_date)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" autocomplete="off" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>

                        <div>
                            <label for="end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">End Date</label>
                            <input type="text" id="end_date" name="end_date" value="{{ old('end_date', optional($contract?->end_date)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" autocomplete="off" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        </div>
                    </div>
                </div>

                <div>
                    <label for="nik_karyawan_display" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">NIK Karyawan</label>
                    <input type="text" id="nik_karyawan_display" x-model="generatedNik" placeholder="Auto generate" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                    <input type="hidden" name="nik_karyawan" x-model="generatedNik">
                    <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->employee_id ?? '') }}">
                </div>

                <div>
                    <label for="attendance_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">ID Absen</label>
                    <input type="text" id="attendance_id" name="attendance_id" value="{{ old('attendance_id', $employee->attendance_id ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                    @error('attendance_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="nama_karyawan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Karyawan <span class="text-error-500">*</span></label>
                    <input type="text" id="nama_karyawan" name="nama_karyawan" value="{{ old('nama_karyawan', $employee->nama_karyawan ?? '') }}" maxlength="150" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                    @error('nama_karyawan')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="company_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Company <span class="text-error-500">*</span></label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->company?->company_name ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="company_id" value="{{ $selectedCompanyId }}">
                    @else
                        <select id="company_id" name="company_id" x-model="selectedCompanyId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Pilih company</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company->id }}">{{ strtoupper($company->company_name) }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('company_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department <span class="text-error-500">*</span></label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->employeePosition?->position?->subDepartment?->department?->department_name ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                    @else
                        <select id="department_id" name="department_id" x-model="selectedDepartmentId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedCompanyId">
                            <option value="">Pilih department</option>
                            <template x-for="department in filteredDepartments()" :key="department.id">
                                <option :value="String(department.id)" x-text="String(department.department_name).toUpperCase()"></option>
                            </template>
                        </select>
                    @endif
                    @error('department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="sub_department_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Sub Department <span class="text-error-500">*</span></label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->employeePosition?->position?->subDepartment?->sub_department_name ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="sub_department_id" value="{{ $selectedSubDepartmentId }}">
                    @else
                        <select id="sub_department_id" name="sub_department_id" x-model="selectedSubDepartmentId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedDepartmentId">
                            <option value="">Pilih sub department</option>
                            <template x-for="subDepartment in filteredSubDepartments()" :key="subDepartment.id">
                                <option :value="String(subDepartment.id)" x-text="String(subDepartment.sub_department_name).toUpperCase()"></option>
                            </template>
                        </select>
                    @endif
                    @error('sub_department_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="position_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan / Position <span class="text-error-500">*</span></label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->employeePosition?->position?->position_name ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="position_id" value="{{ $selectedPositionId }}">
                    @else
                        <select id="position_id" name="position_id" x-model="selectedPositionId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800" :disabled="!selectedSubDepartmentId">
                            <option value="">Pilih position</option>
                            <template x-for="position in filteredPositions()" :key="position.id">
                                <option :value="String(position.id)" x-text="String(position.position_name).toUpperCase()"></option>
                            </template>
                        </select>
                    @endif
                    @error('position_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="level_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pangkat / Position Level <span class="text-error-500">*</span></label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->employeePosition?->level?->level_name ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="level_id" value="{{ $selectedLevelId }}">
                    @else
                        <select id="level_id" name="level_id" x-model="selectedLevelId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Pilih level</option>
                            <template x-for="level in filteredLevels()" :key="level.id">
                                <option :value="String(level.id)" x-text="String(level.level_name).toUpperCase()"></option>
                            </template>
                        </select>
                    @endif
                    @error('level_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="location_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lokasi <span class="text-error-500">*</span></label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->employeePosition?->location?->location_name ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="location_id" value="{{ $selectedLocationId }}">
                    @else
                        <select id="location_id" name="location_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Pilih lokasi</option>
                            @foreach ($locations as $location)
                                <option value="{{ $location->id }}" @selected($selectedLocationId == (string) $location->id)>{{ strtoupper($location->location_name) }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('location_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="area" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Area</label>
                    @if ($isEdit)
                        <input type="text" value="{{ strtoupper($employee->employeePosition?->area ?? '-') }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
                        <input type="hidden" name="area" value="{{ $selectedArea }}">
                    @else
                        <select id="area" name="area" x-model="selectedArea" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Pilih area</option>
                            @foreach ($areaOptions as $areaOption)
                                <option value="{{ $areaOption }}">{{ $areaOption }}</option>
                            @endforeach
                        </select>
                    @endif
                    @error('area')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="rayon" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Rayon</label>
                    <input type="text" id="rayon" name="rayon" value="{{ old('rayon', $employee->employeePosition?->rayon ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>

                <div class="sm:col-span-2">
                    <label for="atasan_langsung" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Atasan Langsung</label>
                    <select id="atasan_langsung" name="atasan_langsung" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih atasan langsung</option>
                        @foreach ($supervisors as $supervisor)
                            <option value="{{ $supervisor->id }}" @selected($selectedSupervisorId == (string) $supervisor->id)>{{ strtoupper($supervisor->nama_karyawan) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section x-show="step === 2" x-cloak x-ref="step2" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Employee Profile</h3>
            <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 p-5 text-sm text-gray-600 dark:border-gray-800 dark:bg-gray-900/40 dark:text-gray-300">
                Data agreement sudah diisi di langkah pertama. Lanjutkan untuk melengkapi profil karyawan dan biodata pendukung.
            </div>
        </section>

        <section x-show="step === 3" x-cloak x-ref="step3" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Biodata Diri</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="no_ktp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor KTP</label>
                    <input type="text" id="no_ktp" name="no_ktp" value="{{ old('no_ktp', $personal->no_ktp ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="no_kk" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor KK</label>
                    <input type="text" id="no_kk" name="no_kk" value="{{ old('no_kk', $personal->no_kk ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="jenis_kelamin" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Kelamin</label>
                    <select id="jenis_kelamin" name="jenis_kelamin" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih jenis kelamin</option>
                        @foreach ($genderOptions as $option)
                            <option value="{{ $option }}" @selected(old('jenis_kelamin', $personal->jenis_kelamin ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="agama" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agama</label>
                    <select id="agama" name="agama" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih agama</option>
                        @foreach ($religionOptions as $option)
                            <option value="{{ $option }}" @selected(old('agama', $personal->agama ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="tempat_lahir" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tempat Lahir</label>
                    <input type="text" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $personal->tempat_lahir ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="tgl_lahir" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Lahir</label>
                    <input type="text" id="tgl_lahir" name="tgl_lahir" value="{{ old('tgl_lahir', optional($personal?->tgl_lahir)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" autocomplete="off" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="email_kantor" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Kantor</label>
                    <input type="email" id="email_kantor" name="email_kantor" value="{{ old('email_kantor', $employee->email_kantor ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="email_pribadi" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Pribadi</label>
                    <input type="email" id="email_pribadi" name="email_pribadi" value="{{ old('email_pribadi', $employee->email_pribadi ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="no_hp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor HP</label>
                    <input type="text" id="no_hp" name="no_hp" value="{{ old('no_hp', $employee->no_hp ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="no_hp_darurat" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor HP Darurat</label>
                    <input type="text" id="no_hp_darurat" name="no_hp_darurat" value="{{ old('no_hp_darurat', $employee->no_hp_darurat ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="status_pernikahan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Pernikahan</label>
                    <select id="status_pernikahan" name="status_pernikahan" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih status pernikahan</option>
                        @foreach ($maritalStatusOptions as $option)
                            <option value="{{ $option }}" @selected(old('status_pernikahan', $personal->status_pernikahan ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="pendidikan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pendidikan</label>
                    <select id="pendidikan" name="pendidikan" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih pendidikan</option>
                        @foreach ($educationOptions as $option)
                            <option value="{{ $option }}" @selected(old('pendidikan', $personal->pendidikan ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="jurusan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jurusan</label>
                    <input type="text" id="jurusan" name="jurusan" value="{{ old('jurusan', $personal->jurusan ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="golongan_darah" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Golongan Darah</label>
                    <select id="golongan_darah" name="golongan_darah" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih golongan darah</option>
                        @foreach ($bloodTypeOptions as $option)
                            <option value="{{ $option }}" @selected(old('golongan_darah', $personal->golongan_darah ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="alamat" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('alamat', $personal->alamat ?? '') }}</textarea>
                </div>
                <div>
                    <label for="kelurahan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kelurahan</label>
                    <input type="text" id="kelurahan" name="kelurahan" value="{{ old('kelurahan', $personal->kelurahan ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="kecamatan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kecamatan</label>
                    <input type="text" id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $personal->kecamatan ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="kota" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kota</label>
                    <input type="text" id="kota" name="kota" value="{{ old('kota', $personal->kota ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="kode_pos" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kode Pos</label>
                    <input type="text" id="kode_pos" name="kode_pos" value="{{ old('kode_pos', $personal->kode_pos ?? '') }}" maxlength="10" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
            </div>
        </section>

        <section x-show="step === 4" x-cloak x-ref="step4" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Data Istri / Suami</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="spouse_nik" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor KTP Istri / Suami</label>
                    <input type="text" id="spouse_nik" name="spouse_nik" value="{{ old('spouse_nik', $spouse->nik ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="spouse_nama" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Istri / Suami</label>
                    <input type="text" id="spouse_nama" name="spouse_nama" value="{{ old('spouse_nama', $spouse->nama ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="spouse_jenis_kelamin" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Kelamin</label>
                    <select id="spouse_jenis_kelamin" name="spouse_jenis_kelamin" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih jenis kelamin</option>
                        @foreach ($genderOptions as $option)
                            <option value="{{ $option }}" @selected(old('spouse_jenis_kelamin', $spouse->jenis_kelamin ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="spouse_tempat_lahir" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tempat Lahir</label>
                    <input type="text" id="spouse_tempat_lahir" name="spouse_tempat_lahir" value="{{ old('spouse_tempat_lahir', $spouse->tempat_lahir ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="spouse_tgl_lahir" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Lahir</label>
                    <input type="text" id="spouse_tgl_lahir" name="spouse_tgl_lahir" value="{{ old('spouse_tgl_lahir', optional($spouse?->tgl_lahir)->format('Y-m-d')) }}" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" autocomplete="off" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="spouse_pendidikan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pendidikan</label>
                    <select id="spouse_pendidikan" name="spouse_pendidikan" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih pendidikan</option>
                        @foreach ($educationOptions as $option)
                            <option value="{{ $option }}" @selected(old('spouse_pendidikan', $spouse->pendidikan ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label for="spouse_pekerjaan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pekerjaan</label>
                    <input type="text" id="spouse_pekerjaan" name="spouse_pekerjaan" value="{{ old('spouse_pekerjaan', $spouse->pekerjaan ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
            </div>

            <div class="mt-8 border-t border-gray-200 pt-6 dark:border-gray-800">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h4 class="text-base font-semibold text-gray-900 dark:text-white/90">Data Anak</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Maksimal 3 anak.</p>
                    </div>
                    <button type="button" @click="addChild()" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-4 py-2.5 text-sm font-medium text-white transition hover:bg-brand-600" :disabled="children.length >= 3">Tambah Anak</button>
                </div>

                <div class="mt-5 space-y-5">
                    <template x-for="(child, index) in children" :key="index">
                        <div class="rounded-2xl border border-gray-200 p-4 dark:border-gray-800">
                            <div class="mb-4 flex items-center justify-between">
                                <h5 class="text-sm font-semibold text-gray-900 dark:text-white/90" x-text="'Anak ' + (index + 1)"></h5>
                                <button type="button" @click="removeChild(index)" class="text-sm font-medium text-error-500">Hapus</button>
                            </div>
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor NIK / KTP Anak</label>
                                    <input type="text" :name="`children[${index}][nik]`" x-model="child.nik" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Anak</label>
                                    <input type="text" :name="`children[${index}][nama]`" x-model="child.nama" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tempat Lahir Anak</label>
                                    <input type="text" :name="`children[${index}][tempat_lahir]`" x-model="child.tempat_lahir" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                </div>
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Lahir Anak</label>
                                    <input type="text" :name="`children[${index}][tgl_lahir]`" x-model="child.tgl_lahir" x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" autocomplete="off" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pendidikan Anak</label>
                                    <select :name="`children[${index}][pendidikan]`" x-model="child.pendidikan" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                                        <option value="">Pilih pendidikan</option>
                                        @foreach ($educationOptions as $option)
                                            <option value="{{ $option }}">{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </section>

        <section x-show="step === 5" x-cloak x-ref="step5" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">BPJS & Tax</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="nomor_ketenagakerjaan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor BPJS TK</label>
                    <input type="text" id="nomor_ketenagakerjaan" name="nomor_ketenagakerjaan" value="{{ old('nomor_ketenagakerjaan', $bpjs->nomor_ketenagakerjaan ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="nomor_kesehatan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor BPJS Kesehatan</label>
                    <input type="text" id="nomor_kesehatan" name="nomor_kesehatan" value="{{ old('nomor_kesehatan', $bpjs->nomor_kesehatan ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="no_npwp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor NPWP</label>
                    <input type="text" id="no_npwp" name="no_npwp" value="{{ old('no_npwp', $tax->no_npwp ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="ptkp_status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">PTKP Status</label>
                    <select id="ptkp_status" name="ptkp_status" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        <option value="">Pilih PTKP status</option>
                        @foreach ($ptkpOptions as $option)
                            <option value="{{ $option }}" @selected(old('ptkp_status', $tax->ptkp_status ?? '') === $option)>{{ $option }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section x-show="step === 6" x-cloak x-ref="step6" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Bank & Payroll</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="no_rekening" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Rekening</label>
                    <input type="text" id="no_rekening" name="no_rekening" value="{{ old('no_rekening', $bank->no_rekening ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="nama_bank" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Bank</label>
                    <input type="text" id="nama_bank" name="nama_bank" value="{{ old('nama_bank', $bank->nama_bank ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="nama_didalam_rek" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama di Rekening</label>
                    <input type="text" id="nama_didalam_rek" name="nama_didalam_rek" value="{{ old('nama_didalam_rek', $bank->nama_didalam_rek ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="cabang_bank" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Cabang Bank</label>
                    <input type="text" id="cabang_bank" name="cabang_bank" value="{{ old('cabang_bank', $bank->cabang_bank ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="gaji_pokok" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Gaji Pokok</label>
                    <input type="number" step="0.01" min="0" id="gaji_pokok" name="gaji_pokok" value="{{ old('gaji_pokok', $payroll->gaji_pokok ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="tunjangan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Total Tunjangan</label>
                    <input type="number" step="0.01" min="0" id="tunjangan" name="tunjangan" value="{{ old('tunjangan', $payroll->tunjangan ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="uang_makan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Uang Makan</label>
                    <input type="number" step="0.01" min="0" id="uang_makan" name="uang_makan" value="{{ old('uang_makan', $payroll->uang_makan ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
                <div>
                    <label for="uang_transport" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Uang Transport</label>
                    <input type="number" step="0.01" min="0" id="uang_transport" name="uang_transport" value="{{ old('uang_transport', $payroll->uang_transport ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                </div>
            </div>
        </section>

        <section x-show="step === 7" x-cloak x-ref="step7" class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Employee Document</h3>
            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="foto_karyawan" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Foto Karyawan</label>
                    <input type="file" id="foto_karyawan" name="foto_karyawan" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                    @if (! empty($employee?->foto_karyawan))
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File sudah tersimpan.</p>
                    @endif
                </div>

                <div>
                    <label for="file_kontrak" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Kontrak Karyawan</label>
                    <input type="file" id="file_kontrak" name="file_kontrak" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                    @if (! empty($contract?->file_kontrak))
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File sudah tersimpan.</p>
                    @endif
                </div>

                <div>
                    <label for="document_ktp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">KTP Karyawan</label>
                    <input type="file" id="document_ktp" name="document_ktp" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                </div>

                <div>
                    <label for="document_kk" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">KK Karyawan</label>
                    <input type="file" id="document_kk" name="document_kk" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                </div>

                <div>
                    <label for="document_buku_nikah" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Buku Nikah</label>
                    <input type="file" id="document_buku_nikah" name="document_buku_nikah" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                </div>

                <div>
                    <label for="document_npwp" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">NPWP</label>
                    <input type="file" id="document_npwp" name="document_npwp" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                </div>

                <div>
                    <label for="document_buku_rekening" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Buku Rekening</label>
                    <input type="file" id="document_buku_rekening" name="document_buku_rekening" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                </div>

                <div>
                    <label for="document_cv" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">CV / Biodata</label>
                    <input type="file" id="document_cv" name="document_cv" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-xl file:border-0 file:bg-brand-500 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-white hover:file:bg-brand-600 dark:text-gray-400" />
                </div>
            </div>
        </section>
    </div>

    <div class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white px-5 py-4 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60">
        <div>
            <button type="button" @click="prevStep()" x-show="step > 1" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Sebelumnya</button>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('employees.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
            <button type="button" @click="nextStep()" x-show="step < 7" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">Lanjut</button>
            <button type="button" @click="submitForm()" x-show="step === 7" :disabled="isSubmitting" :class="isSubmitting ? 'cursor-not-allowed bg-brand-400' : 'bg-brand-500 hover:bg-brand-600'" class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-medium text-white transition">
                <span x-text="isSubmitting ? 'Menyimpan...' : 'Simpan Employee'"></span>
            </button>
        </div>
    </div>
</div>

<script>
    function employeeWizard(config) {
        return {
            step: 1,
            stepLabels: [
                ...{{ \Illuminate\Support\Js::from($wizardSteps) }},
            ],
            companies: config.companies,
            departments: config.departments,
            subDepartments: config.subDepartments,
            positions: config.positions,
            agreementOptions: config.agreementOptions || [],
            positionLevels: config.positionLevels,
            selectedCompanyId: config.selectedCompanyId || '',
            selectedDepartmentId: config.selectedDepartmentId || '',
            selectedSubDepartmentId: config.selectedSubDepartmentId || '',
            selectedLevelId: config.selectedLevelId || '',
            selectedPositionId: config.selectedPositionId || '',
            selectedJobAgreement: config.selectedJobAgreement || '',
            selectedAgreementId: config.selectedAgreementId || '',
            agreementSourceMode: config.agreementSourceMode || 'manual',
            existingCompanyId: config.existingCompanyId || '',
            currentNik: config.currentNik || '',
            generatedNik: config.currentNik || '',
            nikPreviews: config.nikPreviews || {},
            children: config.initialChildren || [],
            validationMessage: '',
            agreementNotice: '',
            isSubmitting: false,
            isInitializing: true,
            isApplyingAgreement: false,
            init() {
                this.$nextTick(() => {
                    this.updateGeneratedNik();
                    this.$watch('selectedCompanyId', (value, oldValue) => {
                        if (this.isInitializing || this.isApplyingAgreement || value === oldValue) {
                            return;
                        }

                        this.updateGeneratedNik();
                        this.selectedDepartmentId = '';
                        this.selectedSubDepartmentId = '';
                        this.selectedPositionId = '';
                    });

                    this.$watch('selectedDepartmentId', (value, oldValue) => {
                        if (this.isInitializing || this.isApplyingAgreement || value === oldValue) {
                            return;
                        }

                        this.selectedSubDepartmentId = '';
                        this.selectedPositionId = '';
                    });

                    this.$watch('selectedSubDepartmentId', (value, oldValue) => {
                        if (this.isInitializing || this.isApplyingAgreement || value === oldValue) {
                            return;
                        }

                        this.selectedPositionId = '';
                    });

                    if (this.agreementSourceMode === 'agreement' && this.selectedAgreementId) {
                        this.applySelectedAgreement();
                    }

                    this.isInitializing = false;
                });
            },
            filteredDepartments() {
                return this.departments.filter((department) => String(department.company_id) === this.selectedCompanyId);
            },
            filteredSubDepartments() {
                return this.subDepartments.filter((subDepartment) => String(subDepartment.department_id) === this.selectedDepartmentId);
            },
            filteredLevels() {
                return this.positionLevels;
            },
            filteredPositions() {
                return this.positions.filter((position) => {
                    const sameHierarchy =
                        String(position.company_id) === this.selectedCompanyId &&
                        String(position.department_id) === this.selectedDepartmentId &&
                        String(position.sub_department_id) === this.selectedSubDepartmentId;

                    if (!sameHierarchy) {
                        return false;
                    }

                    return sameHierarchy;
                });
            },
            selectedAgreement() {
                return this.agreementOptions.find((agreement) => String(agreement.id) === String(this.selectedAgreementId));
            },
            clearAgreementSelection() {
                this.isApplyingAgreement = true;
                this.selectedAgreementId = '';
                this.selectedJobAgreement = '';
                this.isApplyingAgreement = false;
            },
            applySelectedAgreement() {
                const agreement = this.selectedAgreement();
                if (!agreement) {
                    this.agreementNotice = '';
                    return;
                }

                this.isApplyingAgreement = true;
                this.agreementNotice = this.missingAgreementMessage(agreement);
                this.selectedCompanyId = String(agreement.company_id || '');
                this.selectedDepartmentId = String(agreement.department_id || '');
                this.selectedSubDepartmentId = String(agreement.sub_department_id || '');
                this.selectedPositionId = '';
                this.selectedLevelId = '';
                this.selectedJobAgreement = agreement.job_agreement || agreement.agreement_number || '';
                this.setField('job_agreement', this.selectedJobAgreement);
                this.setField('agreement_status', agreement.agreement_status);
                this.setField('nama_karyawan', agreement.employee_name);
                this.setField('start_date', agreement.start_date);
                this.setField('end_date', agreement.end_date);
                this.setField('no_ktp', agreement.id_card_number);
                this.setField('no_hp', agreement.phone_number);
                this.setField('email_pribadi', agreement.email);
                this.setField('tempat_lahir', agreement.place_of_birth);
                this.setField('tgl_lahir', agreement.date_of_birth);
                this.setField('jenis_kelamin', agreement.gender);
                this.setField('agama', agreement.religion);
                this.setField('alamat', agreement.address);
                this.setField('gaji_pokok', agreement.base_salary);
                this.setField('tunjangan', agreement.allowance);
                this.setField('uang_makan', agreement.meal_allowance);

                this.$nextTick(() => {
                    this.setField('company_id', this.selectedCompanyId);
                    this.setField('department_id', this.selectedDepartmentId);
                    this.$nextTick(() => {
                        this.setField('sub_department_id', this.selectedSubDepartmentId);
                        this.setField('position_id', '');
                        this.setField('level_id', '');
                        this.updateGeneratedNik();
                        this.isApplyingAgreement = false;
                    });
                });
            },
            missingAgreementMessage(agreement) {
                const missingFields = agreement.missing_employee_fields || [];

                if (!missingFields.length) {
                    return '';
                }

                return `Data agreement belum lengkap: ${missingFields.join(', ')}. Lengkapi di Employee Agreement agar bisa terisi otomatis.`;
            },
            updateGeneratedNik() {
                if (this.currentNik && this.selectedCompanyId === this.existingCompanyId) {
                    this.generatedNik = this.currentNik;
                    return;
                }

                this.generatedNik = this.nikPreviews[this.selectedCompanyId] || '';
            },
            setField(fieldId, value) {
                const field = document.getElementById(fieldId);
                if (!field) {
                    return;
                }

                field.value = value || '';
                if (field._flatpickr) {
                    field._flatpickr.setDate(value || null, false, 'Y-m-d');
                }

                field.dispatchEvent(new Event('input', { bubbles: true }));
                field.dispatchEvent(new Event('change', { bubbles: true }));
                this.flashAutofill(field);
            },
            flashAutofill(field) {
                if (!field || field.type === 'hidden') {
                    return;
                }

                field.classList.remove('autofill-flash');
                void field.offsetWidth;
                field.classList.add('autofill-flash');
                window.setTimeout(() => {
                    field.classList.remove('autofill-flash');
                }, 1400);
            },
            addChild() {
                if (this.children.length >= 3) {
                    return;
                }

                this.children.push({
                    nik: '',
                    nama: '',
                    tempat_lahir: '',
                    tgl_lahir: '',
                    pendidikan: '',
                });
            },
            removeChild(index) {
                this.children.splice(index, 1);
            },
            nextStep() {
                if (!this.validateStep(this.step)) {
                    return;
                }

                if (this.step < 7) {
                    this.step++;
                }
            },
            prevStep() {
                this.validationMessage = '';
                if (this.step > 1) {
                    this.step--;
                }
            },
            validateStep(stepNumber) {
                this.validationMessage = '';

                const section = this.$refs[`step${stepNumber}`];
                if (!section) {
                    return true;
                }

                const requiredFields = [...section.querySelectorAll('[required]')];
                const firstInvalidField = requiredFields.find((field) => !field.checkValidity());

                if (!firstInvalidField) {
                    return true;
                }

                const label = section.querySelector(`label[for="${firstInvalidField.id}"]`);
                const fieldName = label ? label.textContent.replace('*', '').trim() : 'field wajib';
                this.validationMessage = `Lengkapi ${fieldName} sebelum melanjutkan.`;
                firstInvalidField.reportValidity();
                firstInvalidField.focus();

                return false;
            },
            submitForm() {
                if (this.isSubmitting) {
                    return;
                }

                const invalidStep = [1, 2, 3, 4, 5, 6, 7].find((stepNumber) => !this.validateStep(stepNumber));
                if (invalidStep) {
                    this.step = invalidStep;
                    return;
                }

                this.validationMessage = '';
                this.isSubmitting = true;
                this.$root.closest('form').submit();
            },
        };
    }
</script>
