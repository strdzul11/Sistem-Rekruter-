<?php

namespace App\Services;

use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\ApplicationRanking;
use Illuminate\Support\Facades\DB;

class SawRankingService
{
    /**
     * Hitung skor SAW untuk satu application.
     *
     * Perbaikan dari implementasi sebelumnya:
     * 1. GROUP BY criteria_id + AVG(score) agar multi-evaluator tidak double-count
     * 2. Normalisasi sesuai tipe criteria (benefit: avg/max, cost: min/avg)
     * 3. Return totalScore / totalWeight (bukan totalScore saja)
     */
    public function calculateScore(int $applicationId): float
    {
        // Ambil rata-rata skor per criteria untuk application ini
        $criteriaScores = DB::table('application_evaluations as ae')
            ->join('evaluation_criteria as ec', 'ae.criteria_id', '=', 'ec.id')
            ->where('ae.application_id', $applicationId)
            ->where('ec.is_active', true)
            ->select(
                'ec.id as criteria_id',
                'ec.weight',
                'ec.max_value',
                'ec.min_value',
                'ec.type',
                DB::raw('AVG(ae.score) as avg_score')
            )
            ->groupBy('ec.id', 'ec.weight', 'ec.max_value', 'ec.min_value', 'ec.type')
            ->get();

        if ($criteriaScores->isEmpty()) {
            return 0.0;
        }

        $totalScore  = 0.0;
        $totalWeight = 0.0;

        foreach ($criteriaScores as $row) {
            $avgScore = (float) $row->avg_score;
            $weight   = (float) $row->weight / 100;

            if ($row->type === 'cost') {
                // Cost criteria: semakin kecil skor semakin baik → normalisasi = min / avg
                if ($avgScore <= 0) {
                    $normalizedScore = 0.0;
                } else {
                    $normalizedScore = (float) $row->min_value / $avgScore;
                }
            } else {
                // Benefit criteria (default): semakin besar skor semakin baik → normalisasi = avg / max
                $normalizedScore = $row->max_value > 0
                    ? $avgScore / (float) $row->max_value
                    : 0.0;
            }

            $totalScore  += $normalizedScore * $weight;
            $totalWeight += $weight;
        }

        // Hindari division by zero; normalkan agar application yang belum lengkap tidak bias
        return $totalWeight > 0 ? $totalScore / $totalWeight : 0.0;
    }

    /**
     * Hitung ulang peringkat SAW untuk seluruh application pada satu job.
     * Hanya application yang punya minimal satu evaluasi yang masuk peringkat.
     */
    public function recalculateRankingsForJob(int $jobId): void
    {
        $applications = Application::where('job_id', $jobId)
            ->whereHas('evaluations')
            ->get();

        $scores = [];
        foreach ($applications as $app) {
            $scores[] = [
                'application_id' => $app->id,
                'job_id'         => $jobId,
                'saw_score'      => $this->calculateScore($app->id),
            ];
        }

        // Urutkan descending berdasarkan skor
        usort($scores, fn ($a, $b) => $b['saw_score'] <=> $a['saw_score']);

        $rank = 1;
        foreach ($scores as $score) {
            ApplicationRanking::updateOrCreate(
                [
                    'application_id' => $score['application_id'],
                    'job_id'         => $score['job_id'],
                ],
                [
                    'saw_score'         => $score['saw_score'],
                    'rank_position'     => $rank,
                    'evaluation_status' => 'final',
                ]
            );
            $rank++;
        }
    }

    /**
     * Ambil detail skor per evaluator per criteria untuk tampilan transparansi.
     * Return: collection of { criteria_name, type, weight, evaluators: [{name, score}], avg_score }
     */
    public function getScoreBreakdown(int $applicationId): \Illuminate\Support\Collection
    {
        $rows = DB::table('application_evaluations as ae')
            ->join('evaluation_criteria as ec', 'ae.criteria_id', '=', 'ec.id')
            ->join('users as u', 'ae.evaluator_id', '=', 'u.id')
            ->where('ae.application_id', $applicationId)
            ->where('ec.is_active', true)
            ->select(
                'ec.id as criteria_id',
                'ec.name as criteria_name',
                'ec.type',
                'ec.weight',
                'ec.max_value',
                'ec.min_value',
                'u.name as evaluator_name',
                'ae.score',
                'ae.notes'
            )
            ->orderBy('ec.name')
            ->orderBy('u.name')
            ->get();

        // Group by criteria
        return $rows->groupBy('criteria_id')->map(function ($items) {
            $first    = $items->first();
            $avgScore = $items->avg('score');

            if ($first->type === 'cost') {
                $normalizedAvg = $avgScore > 0
                    ? (float) $first->min_value / $avgScore
                    : 0.0;
            } else {
                $normalizedAvg = $first->max_value > 0
                    ? $avgScore / (float) $first->max_value
                    : 0.0;
            }

            return (object) [
                'criteria_id'    => $first->criteria_id,
                'criteria_name'  => $first->criteria_name,
                'type'           => $first->type,
                'weight'         => $first->weight,
                'max_value'      => $first->max_value,
                'min_value'      => $first->min_value,
                'avg_score'      => round($avgScore, 2),
                'normalized_avg' => round($normalizedAvg, 4),
                'weighted_score' => round($normalizedAvg * ($first->weight / 100), 4),
                'evaluators'     => $items->map(fn ($e) => (object) [
                    'name'  => $e->evaluator_name,
                    'score' => $e->score,
                    'notes' => $e->notes,
                ]),
            ];
        })->values();
    }
}
