<?php

namespace App\Http\Controllers\HRD;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\EvaluationCriteria;
use App\Services\AuditLogger;
use App\Services\PermissionChecker;
use App\Services\SawRankingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
    public function __construct(private SawRankingService $sawService) {}

    public function index()
    {
        PermissionChecker::denyUnless('evaluations.manage');
        $applications = Application::with(['user', 'jobListing', 'evaluations'])
            ->orderByDesc('created_at')
            ->get();

        return view('hrd.evaluations.index', compact('applications'));
    }

    public function show(Application $application)
    {
        PermissionChecker::denyUnless('evaluations.manage');
        $application->load(['user', 'jobListing', 'evaluations.criteria']);
        $criteria = EvaluationCriteria::where('is_active', true)->orderBy('name')->get();

        // Breakdown per evaluator per criteria untuk transparansi
        $breakdown = $this->sawService->getScoreBreakdown($application->id);

        return view('hrd.evaluations.show', compact('application', 'criteria', 'breakdown'));
    }

    public function store(Request $request)
    {
        PermissionChecker::denyUnless('evaluations.manage');
        $validated = $request->validate([
            'application_id'              => 'required|exists:applications,id',
            'evaluations'                 => 'required|array',
            'evaluations.*.criteria_id'   => 'required|exists:evaluation_criteria,id',
            'evaluations.*.score'         => 'required|integer|min:1|max:5',
            'evaluations.*.notes'         => 'nullable|string',
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

        $application = Application::find($validated['application_id']);
        $this->sawService->recalculateRankingsForJob($application->job_id);

        AuditLogger::log('create', 'evaluations', "Penilaian SAW disimpan untuk lamaran #{$application->id}", Application::class, $application->id);

        return redirect()->route('hrd.evaluations.show', $application)
            ->with('success', 'Penilaian berhasil disimpan dan ranking SAW diperbarui.');
    }

    public function preview(Application $application)
    {
        PermissionChecker::denyUnless('evaluations.manage');
        $application->load(['user', 'jobListing']);
        $breakdown = $this->sawService->getScoreBreakdown($application->id);
        $totalSawScore = $this->sawService->calculateScore($application->id);

        return response()->json([
            'applicant_name' => $application->applicant_name ?? ($application->user?->name ?? '-'),
            'position'       => $application->jobListing->position,
            'company'        => $application->jobListing->company,
            'total_score'    => round($totalSawScore, 4),
            'breakdown'      => $breakdown,
            'detail_url'     => route('hrd.evaluations.show', $application),
        ]);
    }
}
