<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use PDO;
use PDOException;

class ApplicationManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Apply for job (registered user)
    public function applyForJob($userId, $jobId, $coverLetter = '') {
        try {
            // Cek apakah sudah pernah apply
            $checkQuery = "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $userId);
            $checkStmt->bindParam(':job_id', $jobId);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                return false; // Sudah pernah apply
            }
            
            // Ambil data user dari tabel users
            $userQuery = "SELECT name, email, phone, address FROM users WHERE id = :user_id";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':user_id', $userId);
            $userStmt->execute();
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                return false; // User tidak ditemukan
            }
            
            // Insert dengan data user yang lengkap
            $query = "INSERT INTO applications (user_id, job_id, applicant_name, applicant_email, applicant_phone, cover_letter, application_type) VALUES (:user_id, :job_id, :applicant_name, :applicant_email, :applicant_phone, :cover_letter, 'registered')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->bindParam(':applicant_name', $userData['name']);
            $stmt->bindParam(':applicant_email', $userData['email']);
            $stmt->bindParam(':applicant_phone', $userData['phone']);
            $stmt->bindParam(':cover_letter', $coverLetter);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::applyForJob Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Apply for job with resume (registered user)
    public function applyForJobWithResume($userId, $jobId, $coverLetter = '', $resumePath = null) {
        try {
            // Cek apakah sudah pernah apply
            $checkQuery = "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $userId);
            $checkStmt->bindParam(':job_id', $jobId);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                return false; // Sudah pernah apply
            }
            
            // Ambil data user dari tabel users
            $userQuery = "SELECT name, email, phone, address FROM users WHERE id = :user_id";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':user_id', $userId);
            $userStmt->execute();
            $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$userData) {
                return false; // User tidak ditemukan
            }
            
            // Insert dengan data user yang lengkap termasuk resume
            $query = "INSERT INTO applications (user_id, job_id, applicant_name, applicant_email, applicant_phone, cover_letter, resume_path, application_type) VALUES (:user_id, :job_id, :applicant_name, :applicant_email, :applicant_phone, :cover_letter, :resume_path, 'registered')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->bindParam(':applicant_name', $userData['name']);
            $stmt->bindParam(':applicant_email', $userData['email']);
            $stmt->bindParam(':applicant_phone', $userData['phone']);
            $stmt->bindParam(':cover_letter', $coverLetter);
            $stmt->bindParam(':resume_path', $resumePath);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::applyForJobWithResume Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Quick apply for job (without registration)
    public function quickApply($name, $email, $phone, $jobId, $coverLetter = '', $age = null, $gender = null, $workExperience = null, $resumePath = null) {
        try {
            // Cek apakah email sudah pernah apply untuk job ini
            $checkQuery = "SELECT id FROM applications WHERE applicant_email = :email AND job_id = :job_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->bindParam(':job_id', $jobId);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                return false; // Sudah pernah apply dengan email ini
            }
            
            // Cek apakah user sudah ada berdasarkan email
            $userQuery = "SELECT id FROM users WHERE email = :email";
            $userStmt = $this->db->prepare($userQuery);
            $userStmt->bindParam(':email', $email);
            $userStmt->execute();
            $existingUser = $userStmt->fetch(PDO::FETCH_ASSOC);
            
            $userId = null;
            
            if ($existingUser) {
                // User sudah ada, gunakan ID yang ada
                $userId = $existingUser['id'];
            } else {
                // User belum ada, buat user baru untuk quick apply
                $createUserQuery = "INSERT INTO users (name, email, phone, role, created_at) VALUES (:name, :email, :phone, 'applicant', NOW())";
                $createUserStmt = $this->db->prepare($createUserQuery);
                $createUserStmt->bindParam(':name', $name);
                $createUserStmt->bindParam(':email', $email);
                $createUserStmt->bindParam(':phone', $phone);
                
                if ($createUserStmt->execute()) {
                    $userId = $this->db->lastInsertId();
                } else {
                    return false; // Gagal membuat user
                }
            }
            
            // Insert aplikasi dengan user_id
            $query = "INSERT INTO applications (user_id, job_id, applicant_name, applicant_email, applicant_phone, applicant_age, applicant_gender, work_experience, cover_letter, resume_path, application_type) VALUES (:user_id, :job_id, :name, :email, :phone, :age, :gender, :work_experience, :cover_letter, :resume_path, 'quick_apply')";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':job_id', $jobId);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':age', $age);
            $stmt->bindParam(':gender', $gender);
            $stmt->bindParam(':work_experience', $workExperience);
            $stmt->bindParam(':cover_letter', $coverLetter);
            $stmt->bindParam(':resume_path', $resumePath);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::quickApply Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get applications by user
    public function getApplicationsByUser($userId) {
        try {
            $query = "SELECT a.*, j.position, j.company, j.location FROM applications a 
                     LEFT JOIN job_listings j ON a.job_id = j.id 
                     WHERE a.user_id = :user_id 
                     ORDER BY a.applied_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getApplicationsByUser Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get all applications (for admin/hrd)
    public function getAllApplications($status = null, $jobId = null) {
        try {
            $query = "SELECT a.*, 
                        COALESCE(u.name, a.applicant_name) as applicant_name, 
                        COALESCE(u.email, a.applicant_email) as applicant_email,
                        COALESCE(u.phone, a.applicant_phone) as applicant_phone,
                        a.applicant_age,
                        a.applicant_gender,
                        a.work_experience,
                        j.position, j.company, j.location
                     FROM applications a 
                     LEFT JOIN users u ON a.user_id = u.id 
                     LEFT JOIN job_listings j ON a.job_id = j.id";
            
            $conditions = [];
            $params = [];
            
            if($status) {
                $conditions[] = "a.status = :status";
                $params[':status'] = $status;
            }
            
            if($jobId) {
                $conditions[] = "a.job_id = :job_id";
                $params[':job_id'] = $jobId;
            }
            
            if(!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " ORDER BY a.applied_at DESC";
            
            $stmt = $this->db->prepare($query);
            foreach($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getAllApplications Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get application details by ID
    public function getApplicationById($id) {
        try {
            $query = "SELECT a.*, 
                        COALESCE(u.name, a.applicant_name) as applicant_name, 
                        COALESCE(u.email, a.applicant_email) as applicant_email,
                        COALESCE(u.phone, a.applicant_phone) as applicant_phone,
                        a.applicant_age,
                        a.applicant_gender,
                        a.work_experience,
                        j.position, j.company, j.location, j.description, j.requirements
                     FROM applications a 
                     LEFT JOIN users u ON a.user_id = u.id 
                     LEFT JOIN job_listings j ON a.job_id = j.id
                     WHERE a.id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getApplicationById Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Update application status
    public function updateApplicationStatus($id, $status) {
        try {
            $query = "UPDATE applications SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::updateApplicationStatus Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get application statistics
    public function getApplicationStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_applications,
                        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
                        SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_applications,
                        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted_applications,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications
                      FROM applications";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getApplicationStats Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Create interview schedule
    public function createInterviewSchedule($applicationId, $interviewerId, $interviewType, $interviewDate, $duration, $meetingLink = null, $meetingRoom = null, $notes = null) {
        try {
            $query = "INSERT INTO interview_schedules (application_id, interviewer_id, interview_type, interview_date, duration_minutes, meeting_link, meeting_room, notes) VALUES (:application_id, :interviewer_id, :interview_type, :interview_date, :duration, :meeting_link, :meeting_room, :notes)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':application_id', $applicationId);
            $stmt->bindParam(':interviewer_id', $interviewerId);
            $stmt->bindParam(':interview_type', $interviewType);
            $stmt->bindParam(':interview_date', $interviewDate);
            $stmt->bindParam(':duration', $duration);
            $stmt->bindParam(':meeting_link', $meetingLink);
            $stmt->bindParam(':meeting_room', $meetingRoom);
            $stmt->bindParam(':notes', $notes);
            
            if($stmt->execute()) {
                // Update application interview status
                $updateQuery = "UPDATE applications SET interview_status = 'scheduled' WHERE id = :application_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':application_id', $applicationId);
                $updateStmt->execute();
                
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::createInterviewSchedule Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get all interview schedules
    public function getAllInterviewSchedules() {
        try {
            $query = "SELECT s.*, 
                        a.applicant_name, a.applicant_email,
                        j.position, j.company,
                        u.name as interviewer_name
                      FROM interview_schedules s
                      LEFT JOIN applications a ON s.application_id = a.id
                      LEFT JOIN job_listings j ON a.job_id = j.id
                      LEFT JOIN users u ON s.interviewer_id = u.id
                      ORDER BY s.interview_date DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getAllInterviewSchedules Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get interview schedule by ID
    public function getInterviewScheduleById($id) {
        try {
            $query = "SELECT s.*, 
                        a.applicant_name, a.applicant_email, a.user_id as applicant_id,
                        j.position, j.company,
                        u.name as interviewer_name
                      FROM interview_schedules s
                      LEFT JOIN applications a ON s.application_id = a.id
                      LEFT JOIN job_listings j ON a.job_id = j.id
                      LEFT JOIN users u ON s.interviewer_id = u.id
                      WHERE s.id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getInterviewScheduleById Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Submit interview feedback
    public function submitInterviewFeedback($scheduleId, $interviewerId, $communicationScore, $technicalScore, $problemSolvingScore, $culturalFitScore, $strengths, $weaknesses, $areasForImprovement, $recommendation, $nextSteps, $additionalNotes) {
        try {
            // Get applicant ID from schedule
            $schedule = $this->getInterviewScheduleById($scheduleId);
            $applicantId = $schedule['applicant_id'];
            
            // Check if feedback already exists
            $checkQuery = "SELECT id FROM interview_feedback WHERE interview_schedule_id = :schedule_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':schedule_id', $scheduleId);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                // Update existing feedback
                $query = "UPDATE interview_feedback SET 
                            communication_score = :communication_score,
                            technical_score = :technical_score,
                            problem_solving_score = :problem_solving_score,
                            cultural_fit_score = :cultural_fit_score,
                            strengths = :strengths,
                            weaknesses = :weaknesses,
                            areas_for_improvement = :areas_for_improvement,
                            recommendation = :recommendation,
                            next_steps = :next_steps,
                            additional_notes = :additional_notes,
                            feedback_status = 'submitted'
                          WHERE interview_schedule_id = :schedule_id";
            } else {
                // Insert new feedback
                $query = "INSERT INTO interview_feedback (
                            interview_schedule_id, interviewer_id, applicant_id,
                            communication_score, technical_score, problem_solving_score, cultural_fit_score,
                            strengths, weaknesses, areas_for_improvement, recommendation, next_steps, additional_notes,
                            feedback_status
                          ) VALUES (
                            :schedule_id, :interviewer_id, :applicant_id,
                            :communication_score, :technical_score, :problem_solving_score, :cultural_fit_score,
                            :strengths, :weaknesses, :areas_for_improvement, :recommendation, :next_steps, :additional_notes,
                            'submitted'
                          )";
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId);
            $stmt->bindParam(':interviewer_id', $interviewerId);
            $stmt->bindParam(':applicant_id', $applicantId);
            $stmt->bindParam(':communication_score', $communicationScore);
            $stmt->bindParam(':technical_score', $technicalScore);
            $stmt->bindParam(':problem_solving_score', $problemSolvingScore);
            $stmt->bindParam(':cultural_fit_score', $culturalFitScore);
            $stmt->bindParam(':strengths', $strengths);
            $stmt->bindParam(':weaknesses', $weaknesses);
            $stmt->bindParam(':areas_for_improvement', $areasForImprovement);
            $stmt->bindParam(':recommendation', $recommendation);
            $stmt->bindParam(':next_steps', $nextSteps);
            $stmt->bindParam(':additional_notes', $additionalNotes);
            
            if($stmt->execute()) {
                // Update interview schedule status
                $updateQuery = "UPDATE interview_schedules SET status = 'completed' WHERE id = :schedule_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':schedule_id', $scheduleId);
                $updateStmt->execute();
                
                return true;
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::submitInterviewFeedback Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get interview feedback
    public function getInterviewFeedback($scheduleId) {
        try {
            $query = "SELECT * FROM interview_feedback WHERE interview_schedule_id = :schedule_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':schedule_id', $scheduleId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getInterviewFeedback Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Create interview question
    public function createInterviewQuestion($questionText, $questionType, $difficultyLevel, $jobPosition, $createdBy) {
        try {
            $query = "INSERT INTO interview_questions (question_text, question_type, difficulty_level, job_position, created_by) VALUES (:question_text, :question_type, :difficulty_level, :job_position, :created_by)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':question_text', $questionText);
            $stmt->bindParam(':question_type', $questionType);
            $stmt->bindParam(':difficulty_level', $difficultyLevel);
            $stmt->bindParam(':job_position', $jobPosition);
            $stmt->bindParam(':created_by', $createdBy);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::createInterviewQuestion Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get all interview questions
    public function getAllInterviewQuestions() {
        try {
            $query = "SELECT iq.*, u.name as created_by_name 
                      FROM interview_questions iq 
                      LEFT JOIN users u ON iq.created_by = u.id 
                      ORDER BY iq.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::getAllInterviewQuestions Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Update interview question
    public function updateInterviewQuestion($questionId, $questionText, $questionType, $difficultyLevel, $jobPosition, $isActive) {
        try {
            $query = "UPDATE interview_questions SET 
                        question_text = :question_text,
                        question_type = :question_type,
                        difficulty_level = :difficulty_level,
                        job_position = :job_position,
                        is_active = :is_active
                      WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':question_text', $questionText);
            $stmt->bindParam(':question_type', $questionType);
            $stmt->bindParam(':difficulty_level', $difficultyLevel);
            $stmt->bindParam(':job_position', $jobPosition);
            $stmt->bindParam(':is_active', $isActive);
            $stmt->bindParam(':id', $questionId);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::updateInterviewQuestion Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Delete interview question
    public function deleteInterviewQuestion($questionId) {
        try {
            $query = "DELETE FROM interview_questions WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $questionId);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] ApplicationManager::deleteInterviewQuestion Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
}
