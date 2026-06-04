<?php

namespace App\Http\Controllers;

use App\Helpers\UploadHelper;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeAgreement;
use App\Models\EmployeeBank;
use App\Models\EmployeeBpjs;
use App\Models\EmployeeContract;
use App\Models\EmployeeDocument;
use App\Models\EmployeePayroll;
use App\Models\EmployeePersonal;
use App\Models\EmployeePosition;
use App\Models\EmployeePromotion;
use App\Models\EmployeeSpouse;
use App\Models\EmployeeTax;
use App\Models\Location;
use App\Models\Position;
use App\Models\PositionLevel;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;
use App\Models\SubDepartment;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::query()
            ->latest()
            ->with(['company', 'employeePosition.position.subDepartment.department.company', 'employeePosition.level', 'employeePosition.location']);

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('employee_id', 'like', "%{$search}%")
                    ->orWhere('nik_karyawan', 'like', "%{$search}%")
                    ->orWhere('nama_karyawan', 'like', "%{$search}%");
            });
        }

        if ($companyId = $request->integer('company_id')) {
            $query->where('company_id', $companyId);
        }

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status_karyawan', $status);
        }

        return view('pages.employees.index', [
            'title' => 'Employees',
            'employees' => $query->paginate(10)->withQueryString(),
            'companies' => Company::orderBy('company_name')->get(),
            'search' => $request->string('search')->toString(),
            'companyId' => $request->integer('company_id'),
            'status' => $request->string('status')->toString(),
        ]);
    }

    public function create()
    {
        return view('pages.employees.create', $this->formViewData([
            'title' => 'Create Employee',
            'employee' => null,
            'supervisors' => Employee::orderBy('nama_karyawan')->get(['id', 'nama_karyawan']),
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validateEmployee($request);

        $employeeData = [
            'employee_id' => $data['employee_id'] ?? null,
            'nik_karyawan' => $this->generateEmployeeNik((int) $data['company_id']),
            'attendance_id' => $data['attendance_id'] ?? null,
            'company_id' => $data['company_id'],
            'nama_karyawan' => $data['nama_karyawan'],
            'email_kantor' => $data['email_kantor'] ?? null,
            'email_pribadi' => $data['email_pribadi'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'no_hp_darurat' => $data['no_hp_darurat'] ?? null,
            'status_karyawan' => 'active',
        ];

        if ($request->hasFile('foto_karyawan')) {
            $employeeData['foto_karyawan'] = UploadHelper::store($request->file('foto_karyawan'), 'employee-documents/photos');
        }

        $positionData = Arr::only($data, [
            'position_id',
            'level_id',
            'area',
            'rayon',
            'location_id',
            'atasan_langsung',
        ]);

        $detailData = $this->detailData($data);

        if ($request->hasFile('file_kontrak')) {
            $detailData['contract']['file_kontrak'] = UploadHelper::store($request->file('file_kontrak'), 'employee-contracts');
        }

        $documentData = $this->documentData($request);

        DB::transaction(function () use ($employeeData, $positionData, $detailData, $documentData): void {
            $employee = Employee::create($employeeData);

            $positionData['employee_id'] = $employee->id;
            EmployeePosition::create($positionData);

            $this->saveEmployeeDetails($employee, $detailData);
            $this->saveEmployeeDocuments($employee, $documentData);
            $this->markAgreementCandidateAsJoined($detailData['contract']['job_agreement'] ?? null);
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        EmployeePromotion::query()
            ->where('promotion_type', 'PEJABAT_SEMENTARA')
            ->where('record_status', 'ACTIVE')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', now()->toDateString())
            ->update(['record_status' => 'EXPIRED']);

        $employee->load($this->employeeRelations());

        return view('pages.employees.show', [
            'title' => $employee->nama_karyawan,
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        if ($employee->status_karyawan !== 'active') {
            return redirect()->route('employees.show', $employee)->with('error', 'Employee inactive tidak bisa diedit.');
        }

        $employee->load($this->employeeRelations());

        return view('pages.employees.edit', $this->formViewData([
            'title' => 'Edit Employee',
            'employee' => $employee,
            'supervisors' => Employee::where('id', '!=', $employee->id)->orderBy('nama_karyawan')->get(['id', 'nama_karyawan']),
        ]));
    }

    public function update(Request $request, Employee $employee)
    {
        if ($employee->status_karyawan !== 'active') {
            return redirect()->route('employees.show', $employee)->with('error', 'Employee inactive tidak bisa diedit.');
        }

        $data = $this->validateEmployee($request, $employee->id);
        $companyChanged = (int) $employee->company_id !== (int) $data['company_id'];

        $employeeData = [
            'employee_id' => $data['employee_id'] ?? $employee->employee_id,
            'nik_karyawan' => $companyChanged || blank($employee->nik_karyawan)
                ? $this->generateEmployeeNik((int) $data['company_id'])
                : $employee->nik_karyawan,
            'attendance_id' => $data['attendance_id'] ?? null,
            'company_id' => $data['company_id'],
            'nama_karyawan' => $data['nama_karyawan'],
            'email_kantor' => $data['email_kantor'] ?? null,
            'email_pribadi' => $data['email_pribadi'] ?? null,
            'no_hp' => $data['no_hp'] ?? null,
            'no_hp_darurat' => $data['no_hp_darurat'] ?? null,
        ];

        if ($request->hasFile('foto_karyawan')) {
            UploadHelper::delete($employee->foto_karyawan);
            $employeeData['foto_karyawan'] = UploadHelper::store($request->file('foto_karyawan'), 'employee-documents/photos');
        }

        $positionData = Arr::only($data, [
            'position_id',
            'level_id',
            'area',
            'rayon',
            'location_id',
            'atasan_langsung',
        ]);

        $detailData = $this->detailData($data);

        if ($request->hasFile('file_kontrak')) {
            UploadHelper::delete($employee->contract?->file_kontrak);
            $detailData['contract']['file_kontrak'] = UploadHelper::store($request->file('file_kontrak'), 'employee-contracts');
        }

        $documentData = $this->documentData($request);

        DB::transaction(function () use ($employee, $employeeData, $positionData, $detailData, $documentData): void {
            $employee->update($employeeData);

            $positionData['employee_id'] = $employee->id;
            EmployeePosition::updateOrCreate(
                ['employee_id' => $employee->id],
                $positionData
            );

            $this->saveEmployeeDetails($employee, $detailData);
            $this->saveEmployeeDocuments($employee, $documentData);
        });

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if ($employee->status_karyawan !== 'active') {
            return redirect()->route('employees.index')->with('error', 'Employee inactive tidak bisa dihapus.');
        }

        UploadHelper::delete($employee->contract?->file_kontrak);
        UploadHelper::delete($employee->foto_karyawan);
        foreach ($employee->documents as $document) {
            UploadHelper::delete($document->file_path);
        }
        $employee->delete();

        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    private function validateEmployee(Request $request, ?int $employeeId = null): array
    {
        $currentContractId = $employeeId
            ? EmployeeContract::where('employee_id', $employeeId)->value('id')
            : null;

        $data = $request->validate([
            'employee_id' => ['nullable', 'string', 'max:30'],
            'nik_karyawan' => ['nullable', 'string', 'max:50'],
            'attendance_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('employees', 'attendance_id')
                    ->whereNull('deleted_at')
                    ->ignore($employeeId),
            ],
            'nama_karyawan' => ['required', 'string', 'max:150'],
            'email_kantor' => ['nullable', 'email', 'max:100'],
            'email_pribadi' => ['nullable', 'email', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'no_hp_darurat' => ['nullable', 'string', 'max:30'],
            'company_id' => ['required', 'exists:companies,id'],
            'department_id' => ['required', 'exists:departments,id'],
            'sub_department_id' => ['required', 'exists:sub_departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'level_id' => ['required', 'exists:position_levels,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'area' => ['nullable', Rule::in($this->areaOptions())],
            'rayon' => ['nullable', 'string', 'max:100'],
            'atasan_langsung' => ['nullable', 'exists:employees,id'],
            'agreement_status' => ['nullable', Rule::in($this->agreementStatusOptions())],
            'job_agreement' => [
                'required',
                'string',
                'max:100',
                Rule::unique('employee_contracts', 'job_agreement')
                    ->whereNull('deleted_at')
                    ->ignore($currentContractId),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date'],
            'file_kontrak' => ['nullable', 'file', 'max:4096'],
            'foto_karyawan' => ['nullable', 'image', 'max:4096'],
            'document_ktp' => ['nullable', 'file', 'max:4096'],
            'document_kk' => ['nullable', 'file', 'max:4096'],
            'document_buku_nikah' => ['nullable', 'file', 'max:4096'],
            'document_npwp' => ['nullable', 'file', 'max:4096'],
            'document_buku_rekening' => ['nullable', 'file', 'max:4096'],
            'document_cv' => ['nullable', 'file', 'max:4096'],
            'no_ktp' => ['nullable', 'string', 'max:30'],
            'no_kk' => ['nullable', 'string', 'max:30'],
            'jenis_kelamin' => ['nullable', Rule::in($this->genderOptions())],
            'agama' => ['nullable', Rule::in($this->religionOptions())],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tgl_lahir' => ['nullable', 'date'],
            'status_pernikahan' => ['nullable', Rule::in($this->maritalStatusOptions())],
            'pendidikan' => ['nullable', Rule::in($this->educationOptions())],
            'jurusan' => ['nullable', 'string', 'max:100'],
            'golongan_darah' => ['nullable', Rule::in($this->bloodTypeOptions())],
            'alamat' => ['nullable', 'string'],
            'kelurahan' => ['nullable', 'string', 'max:100'],
            'kecamatan' => ['nullable', 'string', 'max:100'],
            'kota' => ['nullable', 'string', 'max:100'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'spouse_nik' => ['nullable', 'string', 'max:30'],
            'spouse_nama' => ['nullable', 'string', 'max:100'],
            'spouse_jenis_kelamin' => ['nullable', Rule::in($this->genderOptions())],
            'spouse_tempat_lahir' => ['nullable', 'string', 'max:100'],
            'spouse_tgl_lahir' => ['nullable', 'date'],
            'spouse_pendidikan' => ['nullable', Rule::in($this->educationOptions())],
            'spouse_pekerjaan' => ['nullable', 'string', 'max:100'],
            'children' => ['nullable', 'array', 'max:3'],
            'children.*.nik' => ['nullable', 'string', 'max:30'],
            'children.*.nama' => ['nullable', 'string', 'max:100'],
            'children.*.tempat_lahir' => ['nullable', 'string', 'max:100'],
            'children.*.tgl_lahir' => ['nullable', 'date'],
            'children.*.pendidikan' => ['nullable', Rule::in($this->educationOptions())],
            'nomor_ketenagakerjaan' => ['nullable', 'string', 'max:50'],
            'nomor_kesehatan' => ['nullable', 'string', 'max:50'],
            'no_npwp' => ['nullable', 'string', 'max:50'],
            'ptkp_status' => ['nullable', Rule::in($this->ptkpOptions())],
            'no_rekening' => ['nullable', 'string', 'max:50'],
            'nama_bank' => ['nullable', 'string', 'max:100'],
            'nama_didalam_rek' => ['nullable', 'string', 'max:100'],
            'cabang_bank' => ['nullable', 'string', 'max:100'],
            'gaji_pokok' => ['nullable', 'numeric', 'min:0'],
            'tunjangan' => ['nullable', 'numeric', 'min:0'],
            'uang_makan' => ['nullable', 'numeric', 'min:0'],
            'uang_transport' => ['nullable', 'numeric', 'min:0'],
        ]);

        $position = Position::with('subDepartment.department')->findOrFail($data['position_id']);

        if ((string) $position->subDepartment?->department?->company_id !== (string) $data['company_id']) {
            throw ValidationException::withMessages([
                'company_id' => 'Selected company does not match the chosen position.',
            ]);
        }

        if ((string) $position->subDepartment?->department_id !== (string) $data['department_id']) {
            throw ValidationException::withMessages([
                'department_id' => 'Selected department does not match the chosen position.',
            ]);
        }

        if ((string) $position->sub_department_id !== (string) $data['sub_department_id']) {
            throw ValidationException::withMessages([
                'sub_department_id' => 'Selected sub department does not match the chosen position.',
            ]);
        }

        if (! empty($data['atasan_langsung']) && (int) $data['atasan_langsung'] === (int) ($employeeId ?? 0)) {
            throw ValidationException::withMessages([
                'atasan_langsung' => 'Direct supervisor cannot be the same employee.',
            ]);
        }

        return $data;
    }

    private function formViewData(array $overrides = []): array
    {
        return array_merge([
            'companies' => Company::orderBy('company_name')->get(),
            'departments' => Department::orderBy('department_name')->get(),
            'subDepartments' => SubDepartment::orderBy('sub_department_name')->get(),
            'positions' => $this->positionOptions(),
            'agreementOptions' => $this->agreementOptions(),
            'positionLevels' => PositionLevel::orderBy('level_name')->get(),
            'locations' => Location::orderBy('location_name')->get(),
            'areaOptions' => $this->areaOptions(),
            'agreementStatusOptions' => $this->agreementStatusOptions(),
            'genderOptions' => $this->genderOptions(),
            'religionOptions' => $this->religionOptions(),
            'maritalStatusOptions' => $this->maritalStatusOptions(),
            'educationOptions' => $this->educationOptions(),
            'bloodTypeOptions' => $this->bloodTypeOptions(),
            'ptkpOptions' => $this->ptkpOptions(),
            'nikPreviews' => $this->nikPreviews(),
            'currentNikYear' => now()->format('y'),
        ], $overrides);
    }

    private function employeeRelations(): array
    {
        return [
            'company',
            'employeePosition.position.subDepartment.department.company',
            'employeePosition.location',
            'employeePosition.supervisor',
            'contract',
            'personal',
            'spouse',
            'children',
            'documents',
            'promotions.oldPosition.subDepartment.department',
            'promotions.oldLevel',
            'promotions.newPosition.subDepartment.department',
            'promotions.newLevel',
            'mutations.oldPosition.subDepartment.department',
            'mutations.newPosition.subDepartment.department',
            'demotions.oldPosition.subDepartment.department',
            'demotions.oldLevel',
            'demotions.newPosition.subDepartment.department',
            'demotions.newLevel',
            'bpjs',
            'tax',
            'bank',
            'payroll',
        ];
    }

    private function detailData(array $data): array
    {
        return [
            'contract' => [
                'job_agreement' => $data['job_agreement'] ?? null,
                'status_kontrak' => $data['agreement_status'] ?? null,
                'start_date' => $data['start_date'] ?? null,
                'end_date' => $data['end_date'] ?? null,
                'status' => 'active',
            ],
            'personal' => Arr::only($data, [
                'no_ktp',
                'no_kk',
                'jenis_kelamin',
                'agama',
                'tempat_lahir',
                'tgl_lahir',
                'status_pernikahan',
                'pendidikan',
                'jurusan',
                'golongan_darah',
                'alamat',
                'kelurahan',
                'kecamatan',
                'kota',
                'kode_pos',
            ]),
            'spouse' => [
                'nik' => $data['spouse_nik'] ?? null,
                'nama' => $data['spouse_nama'] ?? null,
                'jenis_kelamin' => $data['spouse_jenis_kelamin'] ?? null,
                'tempat_lahir' => $data['spouse_tempat_lahir'] ?? null,
                'tgl_lahir' => $data['spouse_tgl_lahir'] ?? null,
                'pendidikan' => $data['spouse_pendidikan'] ?? null,
                'pekerjaan' => $data['spouse_pekerjaan'] ?? null,
            ],
            'children' => collect($data['children'] ?? [])
                ->map(fn (array $child) => [
                    'nik' => $child['nik'] ?? null,
                    'nama' => $child['nama'] ?? null,
                    'tempat_lahir' => $child['tempat_lahir'] ?? null,
                    'tgl_lahir' => $child['tgl_lahir'] ?? null,
                    'pendidikan' => $child['pendidikan'] ?? null,
                ])->all(),
            'bpjs' => Arr::only($data, ['nomor_ketenagakerjaan', 'nomor_kesehatan']),
            'tax' => Arr::only($data, ['no_npwp', 'ptkp_status']),
            'bank' => Arr::only($data, ['no_rekening', 'nama_bank', 'nama_didalam_rek', 'cabang_bank']),
            'payroll' => Arr::only($data, ['gaji_pokok', 'tunjangan', 'uang_makan', 'uang_transport']),
        ];
    }

    private function saveEmployeeDetails(Employee $employee, array $detailData): void
    {
        $relations = [
            [EmployeeContract::class, $detailData['contract']],
            [EmployeePersonal::class, $detailData['personal']],
            [EmployeeSpouse::class, $detailData['spouse']],
            [EmployeeBpjs::class, $detailData['bpjs']],
            [EmployeeTax::class, $detailData['tax']],
            [EmployeeBank::class, $detailData['bank']],
            [EmployeePayroll::class, $detailData['payroll']],
        ];

        foreach ($relations as [$model, $values]) {
            if (! $this->hasFilledDetail($values)) {
                continue;
            }

            $values['employee_id'] = $employee->id;
            $model::updateOrCreate(['employee_id' => $employee->id], $values);
        }

        $employee->children()->delete();
        foreach ($detailData['children'] as $child) {
            if ($this->hasFilledDetail($child)) {
                $employee->children()->create($child);
            }
        }
    }

    private function documentData(Request $request): array
    {
        $documents = [];

        foreach ($this->documentUploadMap() as $field => $metadata) {
            if ($request->hasFile($field)) {
                $documents[] = [
                    'type' => $metadata['type'],
                    'name' => $metadata['name'],
                    'path' => UploadHelper::store($request->file($field), 'employee-documents/'.$metadata['directory']),
                ];
            }
        }

        return $documents;
    }

    private function saveEmployeeDocuments(Employee $employee, array $documentData): void
    {
        foreach ($documentData as $document) {
            $existingDocument = $employee->documents()
                ->where('document_type', $document['type'])
                ->first();

            UploadHelper::delete($existingDocument?->file_path);

            EmployeeDocument::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'document_type' => $document['type'],
                ],
                [
                    'document_name' => $document['name'],
                    'file_path' => $document['path'],
                ]
            );
        }
    }

    private function markAgreementCandidateAsJoined(?string $agreementNumber): void
    {
        if (blank($agreementNumber)) {
            return;
        }

        $agreement = EmployeeAgreement::query()
            ->where('agreement_number', $agreementNumber)
            ->first();

        if (! $agreement?->recruitment_request_id) {
            return;
        }

        $candidate = RecruitmentCandidate::query()
            ->where('recruitment_request_id', $agreement->recruitment_request_id)
            ->whereIn('category', ['agreement_sent', 'offering'])
            ->orderByRaw("CASE WHEN category = 'agreement_sent' THEN 1 WHEN category = 'offering' THEN 2 ELSE 3 END")
            ->first();

        $candidate?->update(['category' => 'join']);

        RecruitmentRequest::whereKey($agreement->recruitment_request_id)->update([
            'status' => 'done',
            'pending_reason' => null,
        ]);
    }

    private function documentUploadMap(): array
    {
        return [
            'document_ktp' => [
                'type' => 'KTP',
                'name' => 'KTP KARYAWAN',
                'directory' => 'ktp',
            ],
            'document_kk' => [
                'type' => 'KK',
                'name' => 'KK KARYAWAN',
                'directory' => 'kk',
            ],
            'document_buku_nikah' => [
                'type' => 'BUKU_NIKAH',
                'name' => 'BUKU NIKAH',
                'directory' => 'buku-nikah',
            ],
            'document_npwp' => [
                'type' => 'NPWP',
                'name' => 'NPWP',
                'directory' => 'npwp',
            ],
            'document_buku_rekening' => [
                'type' => 'BUKU_REKENING',
                'name' => 'BUKU REKENING',
                'directory' => 'buku-rekening',
            ],
            'document_cv' => [
                'type' => 'CV_BIODATA',
                'name' => 'CV / BIODATA',
                'directory' => 'cv-biodata',
            ],
        ];
    }

    private function hasFilledDetail(array $values): bool
    {
        return collect($values)->contains(fn ($value) => filled($value));
    }

    private function nikPreviews(): array
    {
        return Company::orderBy('company_name')
            ->get()
            ->mapWithKeys(fn (Company $company) => [
                $company->id => $this->previewEmployeeNik($company),
            ])
            ->all();
    }

    private function previewEmployeeNik(Company $company): ?string
    {
        $prefix = $this->companyNikPrefix($company);

        return $prefix ? $this->nextEmployeeNik($prefix) : null;
    }

    private function generateEmployeeNik(int $companyId): string
    {
        $company = Company::findOrFail($companyId);
        $prefix = $this->companyNikPrefix($company);

        if (! $prefix) {
            throw ValidationException::withMessages([
                'company_id' => 'NIK prefix for the selected company has not been configured.',
            ]);
        }

        return $this->nextEmployeeNik($prefix);
    }

    private function companyNikPrefix(Company $company): ?string
    {
        $companyName = Str::upper($company->company_name);

        if (Str::contains($companyName, 'STANDARDPEN')) {
            return '1';
        }

        if (Str::contains($companyName, 'BATAVIA TRINUSA')) {
            return '3';
        }

        return null;
    }

    private function nextEmployeeNik(string $prefix): string
    {
        $year = now()->format('y');
        $nikPrefix = $prefix.$year;
        $lastSequence = Employee::withTrashed()
            ->where('nik_karyawan', 'like', $nikPrefix.'%')
            ->pluck('nik_karyawan')
            ->map(fn (?string $nik) => (int) substr((string) $nik, -4))
            ->max() ?? 0;

        return $nikPrefix.str_pad((string) ($lastSequence + 1), 4, '0', STR_PAD_LEFT);
    }

    private function positionOptions()
    {
        return Position::with(['subDepartment.department.company'])
            ->orderBy('position_name')
            ->get()
            ->map(fn (Position $position) => [
                'id' => $position->id,
                'position_name' => $position->position_name,
                'label' => $position->position_name,
                'company_id' => $position->subDepartment?->department?->company_id,
                'department_id' => $position->subDepartment?->department_id,
                'sub_department_id' => $position->sub_department_id,
            ])
            ->values();
    }

    private function agreementOptions()
    {
        return EmployeeAgreement::query()
            ->whereHas('request', function ($query) {
                $query->where('status', '!=', 'done')
                    ->whereHas('candidates', function ($candidateQuery) {
                        $candidateQuery->where('category', 'agreement_sent');
                    });
            })
            ->with([
                'request.company',
                'request.position.subDepartment.department.company',
            ])
            ->latest()
            ->get()
            ->map(function (EmployeeAgreement $agreement) {
                $request = $agreement->request;
                $position = $request?->position;
                $department = $position?->subDepartment?->department;
                $departmentName = $agreement->department ?: $department?->department_name;
                $resolvedDepartment = null;

                if (filled($departmentName)) {
                    $resolvedDepartment = Department::query()
                        ->when($request?->company_id, fn ($query) => $query->where('company_id', $request->company_id))
                        ->where('department_name', $departmentName)
                        ->first();
                }

                $resolvedDepartment ??= $department;

                return [
                    'id' => $agreement->id,
                    'agreement_number' => $agreement->agreement_number,
                    'label' => collect([
                        $agreement->agreement_number,
                        $agreement->employee_name,
                        $request?->request_number,
                    ])->filter()->implode(' - '),
                    'employee_name' => $agreement->employee_name,
                    'agreement_status' => $agreement->agreement_status,
                    'job_agreement' => $agreement->agreement_number,
                    'company_id' => $request?->company_id,
                    'company_name' => $request?->company?->company_name,
                    'department_id' => $resolvedDepartment?->id,
                    'department_name' => $departmentName,
                    'sub_department_id' => $position?->sub_department_id,
                    'sub_department_name' => $position?->subDepartment?->sub_department_name,
                    'position_id' => null,
                    'position_name' => null,
                    'level_id' => null,
                    'level_name' => null,
                    'start_date' => optional($agreement->contract_start_date)->format('Y-m-d'),
                    'end_date' => optional($agreement->contract_end_date)->format('Y-m-d'),
                    'place_of_birth' => $agreement->place_of_birth,
                    'date_of_birth' => optional($agreement->date_of_birth)->format('Y-m-d'),
                    'gender' => $agreement->gender,
                    'religion' => $agreement->religion,
                    'id_card_number' => $agreement->id_card_number,
                    'phone_number' => $agreement->phone_number,
                    'email' => $agreement->email,
                    'address' => $agreement->address,
                    'base_salary' => $agreement->base_salary,
                    'allowance' => $agreement->allowance,
                    'meal_allowance' => $agreement->meal_allowance,
                    'missing_employee_fields' => collect([
                        'Tempat Lahir' => $agreement->place_of_birth,
                        'Tanggal Lahir' => $agreement->date_of_birth,
                        'Jenis Kelamin' => $agreement->gender,
                        'Agama' => $agreement->religion,
                    ])
                        ->filter(fn ($value) => blank($value))
                        ->keys()
                        ->values(),
                ];
            })
            ->values();
    }

    private function areaOptions(): array
    {
        return ['BARAT', 'TENGAH', 'TIMUR'];
    }

    private function agreementStatusOptions(): array
    {
        return ['PKWT', 'PKWTT', 'HARIAN', 'FREELANCE', 'SPG'];
    }

    private function genderOptions(): array
    {
        return ['LAKI-LAKI', 'PEREMPUAN'];
    }

    private function religionOptions(): array
    {
        return ['ISLAM', 'KRISTEN', 'KATOLIK', 'HINDU', 'BUDDHA', 'KONGHUCU'];
    }

    private function maritalStatusOptions(): array
    {
        return ['BELUM MENIKAH', 'MENIKAH', 'DUDA', 'JANDA', 'CERAI'];
    }

    private function educationOptions(): array
    {
        return ['SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'D4', 'S1', 'S2', 'S3'];
    }

    private function bloodTypeOptions(): array
    {
        return ['A', 'B', 'AB', 'O'];
    }

    private function ptkpOptions(): array
    {
        return ['TK/0', 'TK/1', 'TK/2', 'TK/3', 'K/0', 'K/1', 'K/2', 'K/3'];
    }
}
