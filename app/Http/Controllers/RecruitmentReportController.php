<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentRequest;

class RecruitmentReportController extends Controller
{
    public function index()
    {
        return view('pages.recruitment.report.index', [
            'title' => 'Recruitment Report',
            'requestStatusCounts' => RecruitmentRequest::query()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'candidateCategoryCounts' => RecruitmentCandidate::query()
                ->selectRaw('category, count(*) as total')
                ->groupBy('category')
                ->pluck('total', 'category'),
            'requests' => RecruitmentRequest::withCount('candidates')->latest()->limit(10)->get(),
        ]);
    }
}
