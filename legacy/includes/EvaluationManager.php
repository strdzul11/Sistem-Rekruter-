<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use PDO;
use PDOException;

class EvaluationManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Mendapatkan semua kriteria penilaian yang aktif
    public function getActiveCriteria() {
        try {
            $query = "SELECT * FROM evaluation_criteria WHERE is_active = 1 ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting active criteria: " . $e->getMessage());
            return [];
        }
    }
    
    // Menyimpan atau mengupdate penilaian
    public function saveEvaluation($applicationId, $criteriaId, $score, $evaluatorId, $notes = '') {
        try {
            $query = "INSERT INTO application_evaluations (application_id, criteria_id, score, evaluator_id, notes) 
                     VALUES (:application_id, :criteria_id, :score, :evaluator_id, :notes)
                     ON DUPLICATE KEY UPDATE 
                     score = VALUES(score), 
                     notes = VALUES(notes), 
                     updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':application_id', $applicationId);
            $stmt->bindParam(':criteria_id', $criteriaId);
            $stmt->bindParam(':score', $score);
            $stmt->bindParam(':evaluator_id', $evaluatorId);
            $stmt->bindParam(':notes', $notes);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error saving evaluation: " . $e->getMessage());
            return false;
        }
    }
    
    // Mendapatkan penilaian untuk aplikasi tertentu
    public function getApplicationEvaluations($applicationId, $evaluatorId = null) {
        try {
            $query = "SELECT ae.*, ec.name as criteria_name, ec.weight, ec.type, ec.max_value,
                             u.name as evaluator_name
                      FROM application_evaluations ae
                      JOIN evaluation_criteria ec ON ae.criteria_id = ec.id
                      JOIN users u ON ae.evaluator_id = u.id
                      WHERE ae.application_id = :application_id";
            
            if ($evaluatorId) {
                $query .= " AND ae.evaluator_id = :evaluator_id";
            }
            
            $query .= " ORDER BY ec.name";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':application_id', $applicationId);
            
            if ($evaluatorId) {
                $stmt->bindParam(':evaluator_id', $evaluatorId);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting application evaluations: " . $e->getMessage());
            return [];
        }
    }
    
    // Menghitung skor SAW untuk aplikasi tertentu
    public function calculateSAWScore($applicationId) {
        try {
            // Ambil semua penilaian untuk aplikasi ini
            $query = "SELECT ae.score, ec.weight, ec.type, ec.max_value
                      FROM application_evaluations ae
                      JOIN evaluation_criteria ec ON ae.criteria_id = ec.id
                      WHERE ae.application_id = :application_id AND ec.is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':application_id', $applicationId);
            $stmt->execute();
            $evaluations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($evaluations)) {
                return 0;
            }
            
            // Hitung normalisasi dan skor SAW
            $totalScore = 0;
            $totalWeight = 0;
            
            foreach ($evaluations as $eval) {
                // Normalisasi nilai (untuk benefit criteria)
                $normalizedScore = $eval['score'] / $eval['max_value'];
                
                // Kalikan dengan bobot
                $weightedScore = $normalizedScore * ($eval['weight'] / 100);
                
                $totalScore += $weightedScore;
                $totalWeight += ($eval['weight'] / 100);
            }
            
            // Return skor SAW (0-1)
            return $totalWeight > 0 ? $totalScore : 0;
            
        } catch(PDOException $e) {
            error_log("Error calculating SAW score: " . $e->getMessage());
            return 0;
        }
    }
    
    // Menyimpan hasil ranking SAW
    public function saveRanking($applicationId, $jobId, $sawScore, $rankPosition, $status = 'draft') {
        try {
            $query = "INSERT INTO application_rankings (application_id, job_id, saw_score, rank_position, evaluation_status) 
                     VALUES (:application_id, :job_id, :saw_score, :rank_position, :status)
                     ON DUPLICATE KEY UPDATE 
                     saw_score = VALUES(saw_score), 
                     rank_position = VALUES(rank_position),
                     evaluation_status = VALUES(evaluation_status),
                     updated_at = CURRENT_TIMESTAMP";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':application_id', $applicationId);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->bindParam(':saw_score', $sawScore);
            $stmt->bindParam(':rank_position', $rankPosition);
            $stmt->bindParam(':status', $status);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error saving ranking: " . $e->getMessage());
            return false;
        }
    }
    
    // Menghitung dan menyimpan ranking untuk semua aplikasi di job tertentu
    public function calculateJobRankings($jobId) {
        try {
            // Ambil semua aplikasi untuk job ini yang sudah dinilai
            $query = "SELECT DISTINCT a.id as application_id, a.job_id
                      FROM applications a
                      JOIN application_evaluations ae ON a.id = ae.application_id
                      WHERE a.job_id = :job_id
                      GROUP BY a.id
                      HAVING COUNT(ae.id) >= (SELECT COUNT(*) FROM evaluation_criteria WHERE is_active = 1)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->execute();
            $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Hitung skor SAW untuk setiap aplikasi
            $scores = [];
            foreach ($applications as $app) {
                $sawScore = $this->calculateSAWScore($app['application_id']);
                $scores[] = [
                    'application_id' => $app['application_id'],
                    'job_id' => $app['job_id'],
                    'saw_score' => $sawScore
                ];
            }
            
            // Urutkan berdasarkan skor SAW (tertinggi ke terendah)
            usort($scores, function($a, $b) {
                return $b['saw_score'] <=> $a['saw_score'];
            });
            
            // Simpan ranking
            $rank = 1;
            foreach ($scores as $score) {
                $this->saveRanking(
                    $score['application_id'], 
                    $score['job_id'], 
                    $score['saw_score'], 
                    $rank,
                    'final'
                );
                $rank++;
            }
            
            return true;
            
        } catch(PDOException $e) {
            error_log("Error calculating job rankings: " . $e->getMessage());
            return false;
        }
    }
    
    // Mendapatkan ranking untuk job tertentu
    public function getJobRankings($jobId) {
        try {
            $query = "SELECT ar.*, a.user_id, 
                            COALESCE(u.name, a.applicant_name) as applicant_name, 
                            COALESCE(u.email, a.applicant_email) as applicant_email,
                            jl.position, jl.company
                      FROM application_rankings ar
                      JOIN applications a ON ar.application_id = a.id
                      LEFT JOIN users u ON a.user_id = u.id
                      JOIN job_listings jl ON ar.job_id = jl.id
                      WHERE ar.job_id = :job_id AND ar.evaluation_status = 'final'
                      ORDER BY ar.rank_position ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting job rankings: " . $e->getMessage());
            return [];
        }
    }
    
    // Mendapatkan hasil penilaian untuk pelamar
    public function getApplicantResult($userId, $jobId) {
        try {
            $query = "SELECT ar.*, jl.position, jl.company, jl.location,
                            (SELECT COUNT(*) FROM application_rankings ar2 WHERE ar2.job_id = ar.job_id AND ar2.evaluation_status = 'final') as total_applicants
                      FROM application_rankings ar
                      JOIN applications a ON ar.application_id = a.id
                      JOIN job_listings jl ON ar.job_id = jl.id
                      WHERE a.user_id = :user_id AND ar.job_id = :job_id AND ar.evaluation_status = 'final'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting applicant result: " . $e->getMessage());
            return null;
        }
    }
    
    // Mendapatkan semua hasil penilaian untuk pelamar
    public function getApplicantResults($userId) {
        try {
            $query = "SELECT ar.*, jl.position, jl.company, jl.location,
                            (SELECT COUNT(*) FROM application_rankings ar2 WHERE ar2.job_id = ar.job_id AND ar2.evaluation_status = 'final') as total_applicants
                      FROM application_rankings ar
                      JOIN applications a ON ar.application_id = a.id
                      JOIN job_listings jl ON ar.job_id = jl.id
                      WHERE a.user_id = :user_id AND ar.evaluation_status = 'final'
                      ORDER BY ar.calculated_at DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting applicant results: " . $e->getMessage());
            return [];
        }
    }
    
    // Cek apakah aplikasi sudah dinilai lengkap
    public function isApplicationFullyEvaluated($applicationId) {
        try {
            $query = "SELECT COUNT(*) as evaluated_count,
                            (SELECT COUNT(*) FROM evaluation_criteria WHERE is_active = 1) as total_criteria
                      FROM application_evaluations ae
                      JOIN evaluation_criteria ec ON ae.criteria_id = ec.id
                      WHERE ae.application_id = :application_id AND ec.is_active = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':application_id', $applicationId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['evaluated_count'] >= $result['total_criteria'];
        } catch(PDOException $e) {
            error_log("Error checking evaluation completeness: " . $e->getMessage());
            return false;
        }
    }
    
    // Mendapatkan statistik penilaian
    public function getEvaluationStats($jobId = null) {
        try {
            $whereClause = $jobId ? "WHERE a.job_id = :job_id" : "";
            
            $query = "SELECT 
                        COUNT(DISTINCT a.id) as total_applications,
                        COUNT(DISTINCT ae.application_id) as evaluated_applications,
                        COUNT(DISTINCT ar.application_id) as ranked_applications
                      FROM applications a
                      LEFT JOIN application_evaluations ae ON a.id = ae.application_id
                      LEFT JOIN application_rankings ar ON a.id = ar.application_id AND ar.evaluation_status = 'final'
                      $whereClause";
            
            $stmt = $this->db->prepare($query);
            if ($jobId) {
                $stmt->bindParam(':job_id', $jobId);
            }
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting evaluation stats: " . $e->getMessage());
            return null;
        }
    }
}
