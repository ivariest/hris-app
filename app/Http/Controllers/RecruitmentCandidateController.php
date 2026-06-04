<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RecruitmentCandidateController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = RecruitmentCandidate::query()
            ->with(['request.candidates'])
            ->latest();

        if ($search = trim($request->string('search')->toString())) {
            $baseQuery->where(function ($builder) use ($search) {
                $builder->where('candidate_name', 'like', "%{$search}%")
                    ->orWhere('candidate_phone', 'like', "%{$search}%")
                    ->orWhere('candidate_email', 'like', "%{$search}%")
                    ->orWhere('id_card_number', 'like', "%{$search}%");
            });
        }

        if ($requestId = $request->integer('recruitment_request_id')) {
            $baseQuery->where('recruitment_request_id', $requestId);
        }

        $categoryOptions = $this->categoryOptions();
        $selectedCategory = trim($request->string('category')->toString());

        $candidatesQuery = clone $baseQuery;
        if ($selectedCategory && array_key_exists($selectedCategory, $categoryOptions)) {
            $candidatesQuery->where('category', $selectedCategory);
        }

        return view('pages.recruitment.candidates.index', [
            'title' => 'Candidates',
            'candidates' => $candidatesQuery->paginate(10)->withQueryString(),
            'requests' => RecruitmentRequest::orderByDesc('created_at')->get(),
            'search' => $request->string('search')->toString(),
            'category' => $selectedCategory,
            'requestId' => $request->integer('recruitment_request_id'),
            'categoryOptions' => $categoryOptions,
            'categoryCounts' => $this->categoryCounts($baseQuery),
        ]);
    }

    public function create()
    {
        return view('pages.recruitment.candidates.create', [
            'title' => 'Create Candidate',
            'candidate' => null,
            'requests' => RecruitmentRequest::where('status', '!=', 'done')->orderByDesc('created_at')->get(),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateCandidate($request, false);
        $data['category'] = 'shortlist';

        RecruitmentCandidate::create($data);

        return redirect()->route('recruitment-candidates.index')->with('success', 'Candidate created successfully.');
    }

    public function show(RecruitmentCandidate $recruitmentCandidate)
    {
        $recruitmentCandidate->load('request');

        return view('pages.recruitment.candidates.show', [
            'title' => 'Candidate Detail',
            'candidate' => $recruitmentCandidate,
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function edit(RecruitmentCandidate $recruitmentCandidate)
    {
        return view('pages.recruitment.candidates.edit', [
            'title' => 'Edit Candidate',
            'candidate' => $recruitmentCandidate,
            'requests' => RecruitmentRequest::orderByDesc('created_at')->get(),
            'categoryOptions' => $this->categoryOptions(),
        ]);
    }

    public function update(Request $request, RecruitmentCandidate $recruitmentCandidate)
    {
        $data = $this->validateCandidate($request, true, $recruitmentCandidate);
        $this->ensureOfferingSlotAvailable($data['recruitment_request_id'], $data['category'], $recruitmentCandidate->id);

        $recruitmentCandidate->update($data);

        return redirect()->route('recruitment-candidates.index')->with('success', 'Candidate updated successfully.');
    }

    public function moveCategory(Request $request, RecruitmentCandidate $recruitmentCandidate)
    {
        $data = $request->validate([
            'category' => ['required', Rule::in(array_keys($this->categoryOptions()))],
        ]);

        $this->ensureOfferingSlotAvailable($recruitmentCandidate->recruitment_request_id, $data['category'], $recruitmentCandidate->id);

        $wasAgreementSent = $recruitmentCandidate->category === 'agreement_sent';

        $recruitmentCandidate->update([
            'category' => $data['category'],
        ]);

        if ($wasAgreementSent && $data['category'] === 'reject') {
            RecruitmentRequest::whereKey($recruitmentCandidate->recruitment_request_id)->update([
                'status' => 'on_going',
                'pending_reason' => null,
            ]);
        }

        if (in_array($data['category'], ['offering', 'agreement_sent'], true)) {
            RecruitmentRequest::whereKey($recruitmentCandidate->recruitment_request_id)->update([
                'status' => 'on_going',
                'pending_reason' => null,
            ]);
        }

        return back()->with('success', 'Candidate moved to '.$this->categoryOptions()[$data['category']].'.');
    }

    public function destroy(RecruitmentCandidate $recruitmentCandidate)
    {
        $recruitmentCandidate->delete();

        return redirect()->route('recruitment-candidates.index')->with('success', 'Candidate deleted successfully.');
    }

    private function validateCandidate(Request $request, bool $isUpdate, ?RecruitmentCandidate $candidate = null): array
    {
        $rules = [
            'recruitment_request_id' => ['required', 'exists:recruitment_requests,id'],
            'candidate_name' => ['required', 'string', 'max:150'],
            'candidate_address' => ['nullable', 'string'],
            'candidate_phone' => ['nullable', 'string', 'max:30'],
            'candidate_email' => ['nullable', 'email', 'max:100'],
            'psychotest_result' => ['nullable', 'string'],
            'id_card_number' => ['nullable', 'string', 'max:50'],
            'comment' => ['nullable', 'string'],
        ];

        if ($isUpdate) {
            $rules['category'] = ['required', Rule::in(array_keys($this->categoryOptions()))];
        }

        $data = $request->validate($rules);

        if ($isUpdate && isset($data['category']) && $candidate) {
            $this->ensureOfferingSlotAvailable($data['recruitment_request_id'], $data['category'], $candidate->id);
        }

        return $data;
    }

    private function categoryOptions(): array
    {
        return [
            'shortlist' => 'Shortlist',
            'offering' => 'Offering',
            'agreement_sent' => 'Agreement Sent',
            'join' => 'Join',
            'reject' => 'Reject',
        ];
    }

    private function categoryCounts($baseQuery): array
    {
        $counts = [];

        foreach (array_keys($this->categoryOptions()) as $category) {
            $counts[$category] = (clone $baseQuery)->where('category', $category)->count();
        }

        return $counts;
    }

    private function ensureOfferingSlotAvailable(int $requestId, string $category, ?int $ignoreCandidateId = null): void
    {
        if ($category !== 'offering' && $category !== 'agreement_sent') {
            return;
        }

        $query = RecruitmentCandidate::query()
            ->where('recruitment_request_id', $requestId)
            ->whereIn('category', ['offering', 'agreement_sent']);

        if ($ignoreCandidateId) {
            $query->whereKeyNot($ignoreCandidateId);
        }

        if ($query->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'category' => 'Setiap nomor employee request hanya boleh punya satu kandidat Offering atau Agreement Sent.',
            ]);
        }
    }
}
