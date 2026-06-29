<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\EvaluationCriteria;
use App\Models\ApplicationRanking;
use App\Services\SawRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function __construct(private SawRankingService $sawService) {}

    public function index()
    {
        $applications = Application::with(['user', 'jobListing', 'evaluations'])->get();
        return view('admin.evaluations.index', compact('applications'));
    }

    public function show(Application $evaluation)
    {
        $application = $evaluation->load(['user', 'jobListing', 'evaluations.criteria']);
        $criteria    = EvaluationCriteria::where('is_active', true)->orderBy('name')->get();

        // Breakdown per evaluator per criteria untuk transparansi
        $breakdown = $this->sawService->getScoreBreakdown($application->id);

        return view('admin.evaluations.show', compact('application', 'criteria', 'breakdown'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'application_id'            => 'required|exists:applications,id',
            'evaluations'               => 'required|array',
            'evaluations.*.criteria_id' => 'required|exists:evaluation_criteria,id',
            'evaluations.*.score'       => 'required|integer|min:1|max:5',
            'evaluations.*.notes'       => 'nullable|string',
        ]);

        $evaluatorId = Auth::id();

        foreach ($validated['evaluations'] as $eval) {
            ApplicationEvaluation::updateOrCreate(
                [
                    'application_id' => $validated['application_id'],
                    'criteria_id'    => $eval['criteria_id'],
                    'evaluator_id'   => $evaluatorId,
                ],
                [
                    'score' => $eval['score'],
                    'notes' => $eval['notes'] ?? '',
                ]
            );
        }

        // Trigger SAW Ranking calculations for the job associated with this application
        $application = Application::find($validated['application_id']);
        $this->sawService->recalculateRankingsForJob($application->job_id);

        return redirect()->route('admin.evaluations.show', $validated['application_id'])
            ->with('success', 'Evaluations saved and SAW scores updated.');
    }

    public function preview(Application $application)
    {
        $application->load(['user', 'jobListing']);
        $breakdown = $this->sawService->getScoreBreakdown($application->id);
        $totalSawScore = $this->sawService->calculateScore($application->id);

        return response()->json([
            'applicant_name' => $application->applicant_name ?? ($application->user?->name ?? '-'),
            'position'       => $application->jobListing->position,
            'company'        => $application->jobListing->company,
            'total_score'    => round($totalSawScore, 4),
            'breakdown'      => $breakdown,
            'detail_url'     => route('admin.evaluations.show', $application),
        ]);
    }
}
