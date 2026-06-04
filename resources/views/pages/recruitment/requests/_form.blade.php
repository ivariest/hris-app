@php
    $selectedRequesterId = (string) old('requester_employee_id', $request->requester_employee_id ?? '');
    $selectedPositionId = (string) old('position_id', $request->position_id ?? '');
@endphp

@if ($errors->any())
    <div class="mb-6 rounded-2xl border border-error-200 bg-error-50 px-4 py-3 text-sm text-error-700 dark:border-error-500/20 dark:bg-error-500/10 dark:text-error-300">
        Ada beberapa field yang perlu dicek lagi.
    </div>
@endif

<form
    method="POST"
    action="{{ $formAction }}"
    x-data="recruitmentRequestForm({
        employees: {{ \Illuminate\Support\Js::from($employeeOptions) }},
        positions: {{ \Illuminate\Support\Js::from($positions) }},
        selectedRequesterId: '{{ $selectedRequesterId }}',
        selectedPositionId: '{{ $selectedPositionId }}',
        requestReason: '{{ old('request_reason', $request->request_reason ?? '') }}',
        ticketStatus: '{{ old('status', $request->status ?? 'on_going') }}'
    })"
    x-init="init()"
    class="space-y-6"
>
    @csrf
    @if ($request)
        @method('PUT')
    @endif

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Detail Pemohon</h3>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="request_number" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nomor Request</label>
                <input type="text" id="request_number" value="{{ $request->request_number ?? $nextRequestNumber }}" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>

            @if ($request)
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Tiket <span class="text-error-500">*</span></label>
                    <select id="status" name="status" x-model="ticketStatus" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('status')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                </div>

                <template x-if="ticketStatus === 'pending'">
                    <div class="sm:col-span-2">
                        <label for="pending_reason" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alasan Pending <span class="text-error-500">*</span></label>
                        <textarea id="pending_reason" name="pending_reason" rows="3" :required="ticketStatus === 'pending'" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('pending_reason', $request->pending_reason ?? '') }}</textarea>
                        @error('pending_reason')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </template>
            @endif

            <div>
                <label for="requester_employee_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Pemohon <span class="text-error-500">*</span></label>
                <select id="requester_employee_id" name="requester_employee_id" x-model="selectedRequesterId" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih nama pemohon</option>
                    @foreach ($employees as $employee)
                        <option value="{{ $employee->id }}">{{ strtoupper($employee->nama_karyawan) }}</option>
                    @endforeach
                </select>
                @error('requester_employee_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="company_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Nama Perusahaan <span class="text-error-500">*</span></label>
                <select id="company_id" name="company_id" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}" @selected((string) old('company_id', $request->company_id ?? '') === (string) $company->id)>{{ strtoupper($company->company_name) }}</option>
                    @endforeach
                </select>
                @error('company_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="requester_department" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Divisi Pemohon</label>
                <input type="text" id="requester_department" :value="selectedEmployee()?.department_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>

            <div>
                <label for="requester_position" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan Pemohon</label>
                <input type="text" id="requester_position" :value="selectedEmployee()?.position_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>

            <div>
                <label for="requester_level" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Level Pemohon</label>
                <input type="text" id="requester_level" :value="selectedEmployee()?.level_name || ''" disabled class="h-11 w-full rounded-xl border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400" />
            </div>

            <div>
                <label for="request_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Permohonan <span class="text-error-500">*</span></label>
                <input type="text" id="request_date" name="request_date" value="{{ old('request_date', optional($request->request_date ?? now())->format('Y-m-d')) }}" required x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('request_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Detail Permohonan Tenaga Kerja</h3>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="position_id" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jabatan <span class="text-error-500">*</span></label>
                <select id="position_id" name="position_id" x-model="selectedPositionId" required :disabled="!selectedRequesterId" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 disabled:cursor-not-allowed disabled:bg-gray-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:disabled:bg-gray-800 dark:focus:border-brand-800">
                    <option value="">Pilih jabatan</option>
                    <template x-for="position in filteredPositions()" :key="position.id">
                        <option :value="String(position.id)" x-text="String(position.label).toUpperCase()"></option>
                    </template>
                </select>
                @error('position_id')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="needed_count" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jumlah Kebutuhan <span class="text-error-500">*</span></label>
                <input type="number" id="needed_count" name="needed_count" value="{{ old('needed_count', $request->needed_count ?? 1) }}" min="1" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('needed_count')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="gender" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jenis Kelamin <span class="text-error-500">*</span></label>
                <select id="gender" name="gender" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih jenis kelamin</option>
                    @foreach ($genderOptions as $option)
                        <option value="{{ $option }}" @selected(old('gender', $request->gender ?? '') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('gender')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="age" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Usia <span class="text-error-500">*</span></label>
                <input type="text" id="age" name="age" value="{{ old('age', $request->age ?? '') }}" maxlength="50" placeholder="Contoh: 25-35 tahun" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('age')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="request_status" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Permintaan <span class="text-error-500">*</span></label>
                <select id="request_status" name="request_status" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih status permintaan</option>
                    @foreach ($requestStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('request_status', $request->request_status ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('request_status')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="request_reason" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alasan Permintaan <span class="text-error-500">*</span></label>
                <select id="request_reason" name="request_reason" x-model="requestReason" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih alasan permintaan</option>
                    @foreach ($requestReasonOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('request_reason')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <template x-if="requestReason === 'replacement'">
                <div class="contents">
                    <div>
                        <label for="replacement_reason" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Alasan Replacement <span class="text-error-500">*</span></label>
                        <select id="replacement_reason" name="replacement_reason" :required="requestReason === 'replacement'" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                            <option value="">Pilih alasan replacement</option>
                            @foreach ($replacementReasonOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('replacement_reason', $request->replacement_reason ?? '') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('replacement_reason')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="replacement_note" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Keterangan Replacement</label>
                        <input type="text" id="replacement_note" name="replacement_note" value="{{ old('replacement_note', $request->replacement_note ?? '') }}" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                        @error('replacement_note')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
                    </div>
                </div>
            </template>

            <div>
                <label for="employee_status_agreement" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Status Karyawan <span class="text-error-500">*</span></label>
                <select id="employee_status_agreement" name="employee_status_agreement" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih status karyawan</option>
                    @foreach ($agreementStatusOptions as $option)
                        <option value="{{ $option }}" @selected(old('employee_status_agreement', $request->employee_status_agreement ?? '') === $option)>{{ $option }}</option>
                    @endforeach
                </select>
                @error('employee_status_agreement')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="employment_duration_months" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Lama Kontrak (Bulan)</label>
                <input type="number" id="employment_duration_months" name="employment_duration_months" value="{{ old('employment_duration_months', $request->employment_duration_months ?? '') }}" min="1" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('employment_duration_months')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="needed_date" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Tanggal Dibutuhkan <span class="text-error-500">*</span></label>
                <input type="text" id="needed_date" name="needed_date" value="{{ old('needed_date', optional($request->needed_date ?? null)->format('Y-m-d')) }}" required x-data x-init="flatpickr($el, { dateFormat: 'Y-m-d' })" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('needed_date')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="posting_media" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Media Posting <span class="text-error-500">*</span></label>
                <select id="posting_media" name="posting_media" required class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">
                    <option value="">Pilih media posting</option>
                    @foreach ($postingMediaOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('posting_media', $request->posting_media ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('posting_media')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/60 sm:p-8">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white/90">Detail Kualifikasi</h3>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="minimum_education" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Pendidikan Minimal</label>
                <input type="text" id="minimum_education" name="minimum_education" value="{{ old('minimum_education', $request->minimum_education ?? '') }}" maxlength="100" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('minimum_education')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="major" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Jurusan</label>
                <input type="text" id="major" name="major" value="{{ old('major', $request->major ?? '') }}" maxlength="150" class="h-11 w-full rounded-xl border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800" />
                @error('major')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="special_requirements" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Persyaratan Khusus</label>
                <textarea id="special_requirements" name="special_requirements" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('special_requirements', $request->special_requirements ?? '') }}</textarea>
                @error('special_requirements')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="general_requirements" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Persyaratan Umum</label>
                <textarea id="general_requirements" name="general_requirements" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('general_requirements', $request->general_requirements ?? '') }}</textarea>
                @error('general_requirements')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>

            <div class="sm:col-span-2">
                <label for="job_summary" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Ringkasan Jobdesk</label>
                <textarea id="job_summary" name="job_summary" rows="4" class="w-full rounded-xl border border-gray-300 bg-transparent px-4 py-3 text-sm text-gray-800 shadow-theme-xs focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:focus:border-brand-800">{{ old('job_summary', $request->job_summary ?? '') }}</textarea>
                @error('job_summary')<p class="mt-2 text-sm text-error-500">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6 dark:border-gray-800">
        <a href="{{ route('recruitment-requests.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-3 text-sm font-medium text-gray-700 transition hover:border-gray-300 hover:bg-gray-50 dark:border-gray-800 dark:text-gray-300 dark:hover:border-gray-700 dark:hover:bg-white/[0.03]">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-brand-500 px-5 py-3 text-sm font-medium text-white transition hover:bg-brand-600">{{ $submitLabel }}</button>
    </div>
</form>

<script>
    function recruitmentRequestForm(config) {
        return {
            employees: config.employees || [],
            positions: config.positions || [],
            selectedRequesterId: config.selectedRequesterId || '',
            selectedPositionId: config.selectedPositionId || '',
            requestReason: config.requestReason || '',
            ticketStatus: config.ticketStatus || 'on_going',
            init() {
                this.$watch('selectedRequesterId', () => {
                    if (!this.filteredPositions().some((position) => String(position.id) === this.selectedPositionId)) {
                        this.selectedPositionId = '';
                    }
                });
            },
            selectedEmployee() {
                return this.employees.find((employee) => String(employee.id) === String(this.selectedRequesterId));
            },
            filteredPositions() {
                const employee = this.selectedEmployee();

                if (!employee) {
                    return [];
                }

                return this.positions.filter((position) => String(position.department_id) === String(employee.department_id));
            },
        };
    }
</script>
