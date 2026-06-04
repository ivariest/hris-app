@php
    $selectedRequestId = (string) old('recruitment_request_id', $agreement->recruitment_request_id ?? '');
    $selectedAgreementStatus = (string) old('agreement_status', $agreement->agreement_status ?? '');
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

<form
    method="POST"
    action="{{ $formAction }}"
    x-data="employeeAgreementForm({
        requests: {{ \Illuminate\Support\Js::from($requestOptions) }},
        selectedRequestId: '{{ $selectedRequestId }}',
        selectedAgreementStatus: '{{ $selectedAgreementStatus }}'
    })"
    x-init="init()"
    class="space-y-6"
>
    @csrf
    @if ($agreement)
        @method('PUT')
    @endif

    <section class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="agreement_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agreement Number</label>
            <input type="text" id="agreement_number" value="{{ $agreement->agreement_number ?? $nextAgreementNumber }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
        </div>

        <div>
            <label for="agreement_status_display" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agreement Status <span class="text-error-500">*</span></label>
            <input type="hidden" id="agreement_status" name="agreement_status" value="{{ old('agreement_status', $agreement->agreement_status ?? '') }}">
            <select id="agreement_status_display" x-model="selectedAgreementStatus" disabled class="h-11 w-full rounded-xl border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                <option value="">Pilih agreement status</option>
                @foreach ($agreementStatusOptions as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            @error('agreement_status')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="recruitment_request_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Employee Request <span class="text-error-500">*</span></label>
            <select id="recruitment_request_id" name="recruitment_request_id" x-model="selectedRequestId" @change="applySelectedRequest()" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">Pilih request</option>
                @foreach ($requestOptions as $requestOption)
                    <option value="{{ $requestOption['id'] }}">{{ $requestOption['label'] }}</option>
                @endforeach
            </select>
            @error('recruitment_request_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            <div x-show="requestNotice" x-cloak class="mt-2 rounded-xl border border-warning-200 bg-warning-50 px-3 py-2 text-sm text-warning-700 dark:border-warning-500/20 dark:bg-warning-500/10 dark:text-warning-300">
                <span x-text="requestNotice"></span>
            </div>
        </div>

        <div>
            <label for="employee_name" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Karyawan <span class="text-error-500">*</span></label>
            <input type="text" id="employee_name" name="employee_name" value="{{ old('employee_name', $agreement->employee_name ?? '') }}" maxlength="150" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('employee_name')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="department" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Department</label>
            <input type="text" id="department" name="department" value="{{ old('department', $agreement->department ?? '') }}" readonly class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            @error('department')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="place_of_birth" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tempat Lahir <span class="text-error-500">*</span></label>
            <input type="text" id="place_of_birth" name="place_of_birth" value="{{ old('place_of_birth', $agreement->place_of_birth ?? '') }}" maxlength="100" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('place_of_birth')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="date_of_birth" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Lahir <span class="text-error-500">*</span></label>
            <input type="text" id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth', optional($agreement->date_of_birth ?? null)->format('Y-m-d')) }}" required x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('date_of_birth')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="gender" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Kelamin <span class="text-error-500">*</span></label>
            <select id="gender" name="gender" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">Pilih jenis kelamin</option>
                @foreach ($genderOptions as $option)
                    <option value="{{ $option }}" @selected(old('gender', $agreement->gender ?? '') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @error('gender')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="religion" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Agama <span class="text-error-500">*</span></label>
            <select id="religion" name="religion" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                <option value="">Pilih agama</option>
                @foreach ($religionOptions as $option)
                    <option value="{{ $option }}" @selected(old('religion', $agreement->religion ?? '') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            @error('religion')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="id_card_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor KTP</label>
            <input type="text" id="id_card_number" name="id_card_number" value="{{ old('id_card_number', $agreement->id_card_number ?? '') }}" maxlength="50" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div>
            <label for="phone_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Telepon</label>
            <input type="text" id="phone_number" name="phone_number" value="{{ old('phone_number', $agreement->phone_number ?? '') }}" maxlength="30" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Email Kandidat</label>
            <input type="email" id="email" name="email" value="{{ old('email', $agreement->email ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div class="sm:col-span-2">
            <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alamat</label>
            <textarea id="address" name="address" rows="3" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('address', $agreement->address ?? '') }}</textarea>
        </div>

        <div>
            <label for="contract_start_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Awal Kontrak <span class="text-error-500">*</span></label>
            <input type="text" id="contract_start_date" name="contract_start_date" value="{{ old('contract_start_date', optional($agreement->contract_start_date ?? null)->format('Y-m-d')) }}" required x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
            @error('contract_start_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="contract_end_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Akhir Kontrak</label>
            <input type="text" id="contract_end_date" name="contract_end_date" value="{{ old('contract_end_date', optional($agreement->contract_end_date ?? null)->format('Y-m-d')) }}" x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div>
            <label for="base_salary" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Gaji Pokok</label>
            <input type="number" step="0.01" min="0" id="base_salary" name="base_salary" value="{{ old('base_salary', $agreement->base_salary ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div>
            <label for="allowance" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tunjangan</label>
            <input type="number" step="0.01" min="0" id="allowance" name="allowance" value="{{ old('allowance', $agreement->allowance ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div>
            <label for="meal_allowance" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Uang Makan</label>
            <input type="number" step="0.01" min="0" id="meal_allowance" name="meal_allowance" value="{{ old('meal_allowance', $agreement->meal_allowance ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
        </div>

        <div class="sm:col-span-2">
            <label for="notes" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Catatan</label>
            <textarea id="notes" name="notes" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('notes', $agreement->notes ?? '') }}</textarea>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
        <a href="{{ route('employee-agreements.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>
</form>

<script>
    function employeeAgreementForm(config) {
        return {
            requests: config.requests || [],
            selectedRequestId: config.selectedRequestId || '',
            selectedAgreementStatus: config.selectedAgreementStatus || '',
            requestNotice: '',
            init() {
                this.applySelectedRequest();
                this.$watch('selectedRequestId', () => this.applySelectedRequest());
            },
            currentRequest() {
                return this.requests.find((request) => String(request.id) === String(this.selectedRequestId));
            },
            applySelectedRequest() {
                const request = this.currentRequest();
                if (!request) {
                    this.requestNotice = '';
                    this.setField('agreement_status', this.selectedAgreementStatus);
                    return;
                }

                this.selectedAgreementStatus = request.agreement_status || '';
                this.setField('agreement_status', this.selectedAgreementStatus);
                this.requestNotice = request.candidate_message || '';

                const fields = {
                    employee_name: request.employee_name,
                    department: request.department,
                    id_card_number: request.id_card_number,
                    phone_number: request.phone_number,
                    email: request.email,
                    address: request.address,
                };

                Object.entries(fields).forEach(([fieldId, value]) => {
                    this.setField(fieldId, value);
                });
            },
            setField(fieldId, value) {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.value = value || '';
                    this.flashAutofill(field);
                }
            },
            flashAutofill(field) {
                if (field.type === 'hidden') {
                    return;
                }

                field.classList.remove('autofill-flash');
                void field.offsetWidth;
                field.classList.add('autofill-flash');
                window.setTimeout(() => {
                    field.classList.remove('autofill-flash');
                }, 1400);
            },
        };
    }
</script>
