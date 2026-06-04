<?php

namespace App\Http\Controllers;

use App\Models\EmployeeAgreement;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeAgreementController extends Controller
{
    public function index(Request $request)
    {
        $query = EmployeeAgreement::query()
            ->with('request')
            ->latest();

        if ($search = trim($request->string('search')->toString())) {
            $query->where(function ($builder) use ($search) {
                $builder->where('agreement_number', 'like', "%{$search}%")
                    ->orWhere('employee_name', 'like', "%{$search}%")
                    ->orWhere('department', 'like', "%{$search}%")
                    ->orWhereHas('request', function ($requestQuery) use ($search) {
                        $requestQuery->where('request_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($status = trim($request->string('agreement_status')->toString())) {
            $query->where('agreement_status', $status);
        }

        return view('pages.employee-management.agreements.index', [
            'title' => 'Employee Agreement',
            'agreements' => $query->paginate(10)->withQueryString(),
            'search' => $request->string('search')->toString(),
            'agreementStatus' => $request->string('agreement_status')->toString(),
            'agreementStatusOptions' => $this->agreementStatusOptions(),
        ]);
    }

    public function create()
    {
        return view('pages.employee-management.agreements.create', $this->formViewData([
            'title' => 'Create Employee Agreement',
            'agreement' => null,
        ]));
    }

    public function store(Request $request)
    {
        $data = $this->validateAgreement($request);
        $requestData = $this->resolveRequestData((int) $data['recruitment_request_id']);
        $offeringCandidate = $this->resolveOfferingCandidate((int) $data['recruitment_request_id']);

        $agreement = DB::transaction(function () use ($data, $requestData, $offeringCandidate) {
            $agreement = EmployeeAgreement::create(array_merge($data, [
                'agreement_number' => $this->nextAgreementNumber(),
                'agreement_status' => $requestData['agreement_status'] ?? $data['agreement_status'],
                'employee_name' => $data['employee_name'] ?: ($requestData['employee_name'] ?? ''),
                'department' => $requestData['department'] ?? null,
                'id_card_number' => $data['id_card_number'] ?: ($requestData['id_card_number'] ?? null),
                'phone_number' => $data['phone_number'] ?: ($requestData['phone_number'] ?? null),
                'email' => $data['email'] ?: ($requestData['email'] ?? null),
                'address' => $data['address'] ?: ($requestData['address'] ?? null),
            ]));

            RecruitmentRequest::whereKey($data['recruitment_request_id'])->update([
                'status' => 'on_going',
                'pending_reason' => null,
            ]);

            $offeringCandidate->update([
                'category' => 'agreement_sent',
            ]);

            return $agreement;
        });

        return redirect()->route('employee-agreements.show', $agreement)->with('success', 'Employee agreement created successfully.');
    }

    public function show(EmployeeAgreement $employeeAgreement)
    {
        $employeeAgreement->load('request');

        return view('pages.employee-management.agreements.show', [
            'title' => 'Employee Agreement Detail',
            'agreement' => $employeeAgreement,
            'agreementStatusOptions' => $this->agreementStatusOptions(),
        ]);
    }

    public function print(EmployeeAgreement $employeeAgreement)
    {
        $employeeAgreement->load('request.company');

        return view('pages.employee-management.agreements.print', [
            'title' => 'Print Employee Agreement',
            'agreement' => $employeeAgreement,
        ]);
    }

    public function edit(EmployeeAgreement $employeeAgreement)
    {
        return view('pages.employee-management.agreements.edit', $this->formViewData([
            'title' => 'Edit Employee Agreement',
            'agreement' => $employeeAgreement,
        ]));
    }

    public function update(Request $request, EmployeeAgreement $employeeAgreement)
    {
        $data = $this->validateAgreement($request);
        $requestData = $this->resolveRequestData((int) $data['recruitment_request_id']);

        $employeeAgreement->update(array_merge($data, [
            'agreement_status' => $requestData['agreement_status'] ?? $data['agreement_status'],
            'employee_name' => $data['employee_name'] ?: ($requestData['employee_name'] ?? ''),
            'department' => $requestData['department'] ?? null,
            'id_card_number' => $data['id_card_number'] ?: ($requestData['id_card_number'] ?? null),
            'phone_number' => $data['phone_number'] ?: ($requestData['phone_number'] ?? null),
            'email' => $data['email'] ?: ($requestData['email'] ?? null),
            'address' => $data['address'] ?: ($requestData['address'] ?? null),
        ]));

        return redirect()->route('employee-agreements.show', $employeeAgreement)->with('success', 'Employee agreement updated successfully.');
    }

    public function destroy(EmployeeAgreement $employeeAgreement)
    {
        $employeeAgreement->delete();

        return redirect()->route('employee-agreements.index')->with('success', 'Employee agreement deleted successfully.');
    }

    private function validateAgreement(Request $request): array
    {
        return $request->validate([
            'agreement_status' => ['required', Rule::in($this->agreementStatusOptions())],
            'recruitment_request_id' => ['required', 'exists:recruitment_requests,id'],
            'employee_name' => ['required', 'string', 'max:150'],
            'department' => ['nullable', 'string', 'max:150'],
            'place_of_birth' => ['required', 'string', 'max:100'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::in($this->genderOptions())],
            'religion' => ['required', Rule::in($this->religionOptions())],
            'id_card_number' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'contract_start_date' => ['required', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'allowance' => ['nullable', 'numeric', 'min:0'],
            'meal_allowance' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);
    }

    private function formViewData(array $overrides = []): array
    {
        $agreement = $overrides['agreement'] ?? null;

        $requests = RecruitmentRequest::query()
            ->with([
                'requester.personal',
                'requester.employeePosition.position.subDepartment.department',
                'candidates' => fn ($query) => $query->orderByRaw("CASE WHEN category = 'offering' THEN 1 WHEN category = 'agreement_sent' THEN 2 WHEN category = 'shortlist' THEN 3 WHEN category = 'reject' THEN 4 ELSE 5 END"),
            ])
            ->whereHas('candidates', fn ($query) => $query->where('category', 'offering'))
            ->where(function ($query) use ($agreement) {
                $query->where('status', '!=', 'done');

                if ($agreement?->recruitment_request_id) {
                    $query->orWhereKey($agreement->recruitment_request_id);
                }
            })
            ->latest()
            ->get();

        $requestOptions = $requests->map(function (RecruitmentRequest $request) {
            return $this->agreementSourcePayload($request);
        })->values();

        return array_merge([
            'agreement' => null,
            'requests' => $requests,
            'requestOptions' => $requestOptions,
            'agreementStatusOptions' => $this->agreementStatusOptions(),
            'genderOptions' => $this->genderOptions(),
            'religionOptions' => $this->religionOptions(),
            'nextAgreementNumber' => $this->nextAgreementNumber(),
        ], $overrides);
    }

    private function resolveRequestData(int $requestId): array
    {
        $request = RecruitmentRequest::query()
            ->with([
                'requester.personal',
                'candidates' => fn ($query) => $query->orderByRaw("CASE WHEN category = 'offering' THEN 1 WHEN category = 'agreement_sent' THEN 2 WHEN category = 'shortlist' THEN 3 WHEN category = 'reject' THEN 4 ELSE 5 END"),
            ])
            ->find($requestId);

        return $request ? $this->agreementSourcePayload($request) : [];
    }

    private function resolveOfferingCandidate(int $requestId): RecruitmentCandidate
    {
        $candidate = RecruitmentCandidate::query()
            ->where('recruitment_request_id', $requestId)
            ->where('category', 'offering')
            ->first();

        if (! $candidate) {
            throw ValidationException::withMessages([
                'recruitment_request_id' => 'Tidak ada kandidat Offering untuk nomor employee request ini.',
            ]);
        }

        return $candidate;
    }

    private function agreementSourcePayload(RecruitmentRequest $request): array
    {
        $candidate = $request->candidates->firstWhere('category', 'offering')
            ?? $request->candidates->firstWhere('category', 'agreement_sent')
            ?? $request->candidates->firstWhere('category', 'join');
        return [
            'id' => $request->id,
            'request_number' => $request->request_number,
            'label' => $request->request_number.' - '.($request->requested_position ?? '-'),
            'agreement_status' => $request->employee_status_agreement,
            'candidate_message' => $candidate ? null : 'Tidak ada kandidat',
            'employee_name' => $candidate?->candidate_name ?? '',
            'department' => $request->requester_department ?? '',
            'id_card_number' => $candidate?->id_card_number ?? '',
            'phone_number' => $candidate?->candidate_phone ?? '',
            'email' => $candidate?->candidate_email ?? '',
            'address' => $candidate?->candidate_address ?? '',
        ];
    }

    private function nextAgreementNumber(): string
    {
        $numbers = EmployeeAgreement::withTrashed()
            ->where('agreement_number', 'like', 'EA%')
            ->pluck('agreement_number');

        $lastNumber = $numbers
            ->map(function ($number) {
                if (! preg_match('/^EA(\d+)$/', (string) $number, $matches)) {
                    return 0;
                }

                return (int) $matches[1];
            })
            ->max() ?? 0;

        return 'EA'.str_pad((string) ($lastNumber + 1), 2, '0', STR_PAD_LEFT);
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
}
