<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\ApplicationEvaluation;
use App\Models\EvaluationCriteria;
use App\Models\JobListing;
use App\Models\User;
use App\Services\SawRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SawRankingServiceTest extends TestCase
{
    use RefreshDatabase;

    private SawRankingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SawRankingService();
    }

    public function test_it_calculates_correct_score_with_one_evaluator_and_benefit_criteria(): void
    {
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $job = JobListing::create([
            'position'        => 'Software Engineer',
            'company'         => 'Test Company',
            'location'        => 'Remote',
            'description'     => 'Job description',
            'requirements'    => 'Job requirements',
            'employment_type' => 'full-time',
            'status'          => 'active',
            'created_by'      => $admin->id,
        ]);

        $app = Application::create([
            'job_id'           => $job->id,
            'user_id'          => null,
            'applicant_name'   => 'John Doe',
            'applicant_email'  => 'john@example.com',
            'status'           => 'pending',
            'application_type' => 'quick_apply',
        ]);

        $evaluator = User::create([
            'name'     => 'Evaluator 1',
            'email'    => 'eval1@example.com',
            'password' => bcrypt('password'),
            'role'     => 'hrd',
        ]);

        $criteriaA = EvaluationCriteria::create([
            'name'        => 'Criteria A',
            'description' => 'Desc A',
            'weight'      => 60,
            'type'        => 'benefit',
            'min_value'   => 1,
            'max_value'   => 5,
            'is_active'   => true,
        ]);

        $criteriaB = EvaluationCriteria::create([
            'name'        => 'Criteria B',
            'description' => 'Desc B',
            'weight'      => 40,
            'type'        => 'benefit',
            'min_value'   => 1,
            'max_value'   => 5,
            'is_active'   => true,
        ]);

        ApplicationEvaluation::create([
            'application_id' => $app->id,
            'criteria_id'    => $criteriaA->id,
            'evaluator_id'   => $evaluator->id,
            'score'          => 4, // normalized benefit = 4/5 = 0.8
            'notes'          => 'good',
        ]);

        ApplicationEvaluation::create([
            'application_id' => $app->id,
            'criteria_id'    => $criteriaB->id,
            'evaluator_id'   => $evaluator->id,
            'score'          => 3, // normalized benefit = 3/5 = 0.6
            'notes'          => 'fair',
        ]);

        // Manual calculation:
        // criteriaA: normalized=0.8, weighted=0.8*(0.6)=0.48, weight=0.6
        // criteriaB: normalized=0.6, weighted=0.6*(0.4)=0.24, weight=0.4
        // totalScore=0.72, totalWeight=1.0 → score = 0.72/1.0 = 0.72
        $score = $this->service->calculateScore($app->id);

        $this->assertEqualsWithDelta(0.72, $score, 0.001);
    }

    public function test_it_averages_scores_from_two_evaluators_not_double_counts(): void
    {
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $job = JobListing::create([
            'position'        => 'Software Engineer',
            'company'         => 'Test Company',
            'location'        => 'Remote',
            'description'     => 'Job description',
            'requirements'    => 'Job requirements',
            'employment_type' => 'full-time',
            'status'          => 'active',
            'created_by'      => $admin->id,
        ]);

        $app = Application::create([
            'job_id'           => $job->id,
            'user_id'          => null,
            'applicant_name'   => 'John Doe',
            'applicant_email'  => 'john@example.com',
            'status'           => 'pending',
            'application_type' => 'quick_apply',
        ]);

        $ev1 = User::create([
            'name'     => 'Evaluator 1',
            'email'    => 'eval1@example.com',
            'password' => bcrypt('password'),
            'role'     => 'hrd',
        ]);

        $ev2 = User::create([
            'name'     => 'Evaluator 2',
            'email'    => 'eval2@example.com',
            'password' => bcrypt('password'),
            'role'     => 'hrd',
        ]);

        $criteria = EvaluationCriteria::create([
            'name'        => 'Criteria C',
            'description' => 'Desc C',
            'weight'      => 100,
            'type'        => 'benefit',
            'min_value'   => 1,
            'max_value'   => 5,
            'is_active'   => true,
        ]);

        // Evaluator 1: score 4, Evaluator 2: score 2 → avg = 3
        ApplicationEvaluation::create([
            'application_id' => $app->id,
            'criteria_id'    => $criteria->id,
            'evaluator_id'   => $ev1->id,
            'score'          => 4,
            'notes'          => 'good',
        ]);

        ApplicationEvaluation::create([
            'application_id' => $app->id,
            'criteria_id'    => $criteria->id,
            'evaluator_id'   => $ev2->id,
            'score'          => 2,
            'notes'          => 'poor',
        ]);

        // avg_score = 3, normalized = 3/5 = 0.6, weight=1.0 → score = 0.6/1.0 = 0.6
        $score = $this->service->calculateScore($app->id);

        $this->assertEqualsWithDelta(0.6, $score, 0.001, 'Harus rata-rata 2 evaluator, bukan double-count');
    }

    public function test_it_normalizes_cost_criteria_in_opposite_direction(): void
    {
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $job = JobListing::create([
            'position'        => 'Software Engineer',
            'company'         => 'Test Company',
            'location'        => 'Remote',
            'description'     => 'Job description',
            'requirements'    => 'Job requirements',
            'employment_type' => 'full-time',
            'status'          => 'active',
            'created_by'      => $admin->id,
        ]);

        $app = Application::create([
            'job_id'           => $job->id,
            'user_id'          => null,
            'applicant_name'   => 'John Doe',
            'applicant_email'  => 'john@example.com',
            'status'           => 'pending',
            'application_type' => 'quick_apply',
        ]);

        $ev = User::create([
            'name'     => 'Evaluator',
            'email'    => 'eval@example.com',
            'password' => bcrypt('password'),
            'role'     => 'hrd',
        ]);

        $costCriteria = EvaluationCriteria::create([
            'name'        => 'Criteria Cost',
            'description' => 'Desc Cost',
            'weight'      => 100,
            'type'        => 'cost',
            'min_value'   => 1,
            'max_value'   => 5,
            'is_active'   => true,
        ]);

        ApplicationEvaluation::create([
            'application_id' => $app->id,
            'criteria_id'    => $costCriteria->id,
            'evaluator_id'   => $ev->id,
            'score'          => 4, // cost: normalized = min/avg = 1/4 = 0.25
            'notes'          => 'slow',
        ]);

        // totalScore = 0.25 * 1.0 = 0.25, totalWeight = 1.0 → 0.25
        $score = $this->service->calculateScore($app->id);

        $this->assertEqualsWithDelta(0.25, $score, 0.001, 'Criteria cost harus dinormalisasi min/avg');
    }

    public function test_it_returns_zero_if_no_evaluations(): void
    {
        $admin = User::create([
            'name'     => 'Admin',
            'email'    => 'admin@example.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        $job = JobListing::create([
            'position'        => 'Software Engineer',
            'company'         => 'Test Company',
            'location'        => 'Remote',
            'description'     => 'Job description',
            'requirements'    => 'Job requirements',
            'employment_type' => 'full-time',
            'status'          => 'active',
            'created_by'      => $admin->id,
        ]);

        $app = Application::create([
            'job_id'           => $job->id,
            'user_id'          => null,
            'applicant_name'   => 'John Doe',
            'applicant_email'  => 'john@example.com',
            'status'           => 'pending',
            'application_type' => 'quick_apply',
        ]);

        $score = $this->service->calculateScore($app->id);

        $this->assertEquals(0.0, $score);
    }
}
