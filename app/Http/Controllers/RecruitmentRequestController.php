<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\Position;
use App\Models\RecruitmentRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecruitmentRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = RecruitmentRequest::query()
            ->with(['requester', 'company', 'position'])
            ->withCount('candidates')
            ->latest();

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('request_number', 'like', "%{$search}%")
                    ->orWhere('requested_position', 'like', "%{$search}%")
                    ->orWhereHas('requester', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('nama_karyawan', 'like', "%{$search}%")
                            ->orWhere('nik_karyawan', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = trim($request->string('status')->toString())) {
            $query->where('status', $status);
        }

        return view('pages.recruitment.requests.index', [
            'title' => 'New Employee Request',
            'requests' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function create()
    {
        return view('pages.recruitment.requests.create', [
            'title' => 'Create New Employee Request',
            'request' => null,
            'nextRequestNumber' => $this->nextRequestNumber(),
        ] + $this->formOptions());
    }

    public function store(Request $request)
    {
        $data = $this->validateRequest($request, false);
        $data = $this->normalizeRequestPayload($data);
        $data['request_number'] = $this->nextRequestNumber();
        $data['status'] = 'on_going';
        $data['pending_reason'] = null;

        RecruitmentRequest::create($data);

        return redirect()->route('recruitment-requests.index')->with('success', 'New employee request created successfully.');
    }

    public function show(RecruitmentRequest $recruitmentRequest)
    {
        $recruitmentRequest->load([
            'requester',
            'company',
            'position.subDepartment.department',
            'candidates' => fn ($query) => $query->latest(),
        ]);

        return view('pages.recruitment.requests.show', [
            'title' => 'New Employee Request Detail',
            'request' => $recruitmentRequest,
            'statusOptions' => $this->statusOptions(),
            'requestStatusOptions' => $this->requestStatusOptions(),
            'requestReasonOptions' => $this->requestReasonOptions(),
            'replacementReasonOptions' => $this->replacementReasonOptions(),
            'postingMediaOptions' => $this->postingMediaOptions(),
        ]);
    }

    public function edit(RecruitmentRequest $recruitmentRequest)
    {
        return view('pages.recruitment.requests.edit', [
            'title' => 'Edit New Employee Request',
            'request' => $recruitmentRequest,
            'nextRequestNumber' => $recruitmentRequest->request_number,
        ] + $this->formOptions());
    }

    public function update(Request $request, RecruitmentRequest $recruitmentRequest)
    {
        $recruitmentRequest->update($this->normalizeRequestPayload($this->validateRequest($request, true)));

        return redirect()->route('recruitment-requests.index')->with('success', 'New employee request updated successfully.');
    }

    public function destroy(RecruitmentRequest $recruitmentRequest)
    {
        $recruitmentRequest->delete();

        return redirect()->route('recruitment-requests.index')->with('success', 'New employee request deleted successfully.');
    }

    private function validateRequest(Request $request, bool $isUpdate): array
    {
        $rules = [
            'requester_employee_id' => ['required', 'exists:employees,id'],
            'company_id' => ['required', 'exists:companies,id'],
            'request_date' => ['required', 'date'],
            'position_id' => ['required', 'exists:positions,id'],
            'needed_count' => ['required', 'integer', 'min:1'],
            'gender' => ['required', Rule::in($this->genderOptions())],
            'age' => ['required', 'string', 'max:50'],
            'request_status' => ['required', Rule::in(array_keys($this->requestStatusOptions()))],
            'request_reason' => ['required', Rule::in(array_keys($this->requestReasonOptions()))],
            'replacement_reason' => ['nullable', 'required_if:request_reason,replacement', Rule::in(array_keys($this->replacementReasonOptions()))],
            'replacement_note' => ['nullable', 'string'],
            'employee_status_agreement' => ['required', Rule::in($this->agreementStatusOptions())],
            'employment_duration_months' => ['nullable', 'integer', 'min:1', 'max:120'],
            'needed_date' => ['required', 'date'],
            'posting_media' => ['required', Rule::in(array_keys($this->postingMediaOptions()))],
            'minimum_education' => ['nullable', 'string', 'max:100'],
            'major' => ['nullable', 'string', 'max:150'],
            'special_requirements' => ['nullable', 'string'],
            'general_requirements' => ['nullable', 'string'],
            'job_summary' => ['nullable', 'string'],
        ];

        if ($isUpdate) {
            $rules['status'] = ['required', Rule::in(array_keys($this->statusOptions()))];
            $rules['pending_reason'] = ['nullable', 'required_if:status,pending', 'string'];
        }

        return $request->validate($rules);
    }

    private function normalizeRequestPayload(array $data): array
    {
        $employee = Employee::with([
            'employeePosition.position.subDepartment.department',
            'employeePosition.level',
        ])->findOrFail($data['requester_employee_id']);

        $position = Position::with('subDepartment.department')->findOrFail($data['position_id']);
        $requesterDepartmentId = $employee->employeePosition?->position?->subDepartment?->department_id;

        if ((string) $position->subDepartment?->department_id !== (string) $requesterDepartmentId) {
            throw ValidationException::withMessages([
                'position_id' => 'Jabatan harus berasal dari department pemohon.',
            ]);
        }

        if (($data['request_reason'] ?? null) !== 'replacement') {
            $data['replacement_reason'] = null;
            $data['replacement_note'] = null;
        }

        if (($data['status'] ?? null) !== 'pending') {
            $data['pending_reason'] = null;
        }

        return array_merge($data, [
            'requester_department' => $employee->employeePosition?->position?->subDepartment?->department?->department_name,
            'requester_position' => $employee->employeePosition?->position?->position_name,
            'requester_level' => $employee->employeePosition?->level?->level_name,
            'requested_position' => $position->position_name,
            'requirement_detail' => collect([
                $data['special_requirements'] ?? null,
                $data['general_requirements'] ?? null,
                $data['job_summary'] ?? null,
            ])->filter()->implode("\n\n"),
        ]);
    }

    private function formOptions(): array
    {
        $employees = Employee::query()
            ->with([
                'employeePosition.position.subDepartment.department',
                'employeePosition.level',
            ])
            ->whereHas('employeePosition')
            ->where('status_karyawan', 'active')
            ->orderBy('nama_karyawan')
            ->get();

        $employeeOptions = $employees->map(function (Employee $employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->nama_karyawan,
                'nik' => $employee->nik_karyawan,
                'department_id' => $employee->employeePosition?->position?->subDepartment?->department_id,
                'department_name' => $employee->employeePosition?->position?->subDepartment?->department?->department_name,
                'position_name' => $employee->employeePosition?->position?->position_name,
                'level_name' => $employee->employeePosition?->level?->level_name,
            ];
        })->values();

        $positions = Position::with('subDepartment.department')
            ->orderBy('position_name')
            ->get()
            ->map(function (Position $position) {
                return [
                    'id' => $position->id,
                    'department_id' => $position->subDepartment?->department_id,
                    'label' => collect([
                        $position->position_name,
                        $position->subDepartment?->sub_department_name,
                    ])->filter()->implode(' - '),
                ];
            })
            ->values();

        return [
            'employees' => $employees,
            'employeeOptions' => $employeeOptions,
            'companies' => Company::orderBy('company_name')->get(),
            'positions' => $positions,
            'statusOptions' => $this->statusOptions(),
            'requestStatusOptions' => $this->requestStatusOptions(),
            'requestReasonOptions' => $this->requestReasonOptions(),
            'replacementReasonOptions' => $this->replacementReasonOptions(),
            'agreementStatusOptions' => $this->agreementStatusOptions(),
            'genderOptions' => $this->genderOptions(),
            'postingMediaOptions' => $this->postingMediaOptions(),
        ];
    }

    private function nextRequestNumber(): string
    {
        $numbers = RecruitmentRequest::withTrashed()
            ->where('request_number', 'like', 'NER%')
            ->pluck('request_number');

        $lastNumber = $numbers
            ->map(function ($number) {
                if (! preg_match('/^NER(\d+)$/', (string) $number, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return 'NER'.str_pad((string) ($lastNumber + 1), 2, '0', STR_PAD_LEFT);
    }

    private function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'on_going' => 'On Going',
            'done' => 'Done',
        ];
    }

    private function requestStatusOptions(): array
    {
        return [
            'new_recruitment' => 'New Recruitment',
            'internal_recruitment' => 'Internal Recruitment',
        ];
    }

    private function requestReasonOptions(): array
    {
        return [
            'business_development' => 'Pengembangan Bisnis',
            'replacement' => 'Replacement',
        ];
    }

    private function replacementReasonOptions(): array
    {
        return [
            'resign' => 'Mengundurkan Diri',
            'promotion' => 'Promosi',
            'mutation' => 'Mutasi',
            'other' => 'Lain-lain',
        ];
    }

    private function agreementStatusOptions(): array
    {
        return ['PKWT', 'PKWTT', 'HARIAN', 'FREELANCE', 'SPG'];
    }

    private function genderOptions(): array
    {
        return ['LAKI-LAKI', 'PEREMPUAN', 'BEBAS'];
    }

    private function postingMediaOptions(): array
    {
        return [
            'job_portal' => 'Job Portal',
            'digital_flyer' => 'Digital Flyer',
            'talent_search' => 'Talent Search',
        ];
    }
}
