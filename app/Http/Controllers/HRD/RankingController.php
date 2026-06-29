<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\ApplicationRanking;
use App\Models\JobListing;
use App\Services\PermissionChecker;
use Illuminate\Http\Request;

class RankingController extends Controller
{
    public function index(Request $request)
    {
        PermissionChecker::denyUnless('rankings.view');

        $jobs = JobListing::orderBy('position')->pluck('position', 'id');

        $rankings = ApplicationRanking::with(['application.user', 'jobListing'])
            ->when($request->filled('job_id'), fn ($q) => $q->where('job_id', $request->job_id))
            ->when($request->filled('evaluation_status'), fn ($q) => $q->where('evaluation_status', $request->evaluation_status))
            ->when($request->filled('min_score'), fn ($q) => $q->where('saw_score', '>=', (float) $request->min_score / 100))
            ->orderBy('job_id')
            ->orderBy('rank_position')
            ->paginate(20)
            ->withQueryString();

        return view('hrd.rankings.index', compact('rankings', 'jobs'));
    }
}
