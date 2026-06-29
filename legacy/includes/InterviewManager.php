<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use PDO;
use PDOException;

class InterviewManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Schedule Interview
    public function scheduleInterview($applicationId, $interviewerId, $interviewDate, $timezone = 'Asia/Jakarta', $interviewType = 'video', $duration = 60, $notes = null) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO interview_schedules 
                (application_id, interviewer_id, interview_date, timezone, interview_type, duration_minutes, notes, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'scheduled')
            ");
            
            $result = $stmt->execute([$applicationId, $interviewerId, $interviewDate, $timezone, $interviewType, $duration, $notes]);
            
            if ($result) {
                $interviewId = $this->db->lastInsertId();
                
                // Update application status
                $this->updateApplicationInterviewStatus($applicationId, 'scheduled');
                
                return $interviewId;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error scheduling interview: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Interview Details
    public function getInterviewDetails($interviewId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    i.*,
                    i.application_id,
                    a.applicant_name,
                    a.applicant_email,
                    a.applicant_phone,
                    j.position,
                    j.company,
                    u.name as interviewer_name,
                    u.email as interviewer_email
                FROM interview_schedules i
                LEFT JOIN applications a ON i.application_id = a.id
                LEFT JOIN job_listings j ON a.job_id = j.id
                LEFT JOIN users u ON i.interviewer_id = u.id
                WHERE i.id = ?
            ");
            
            $stmt->execute([$interviewId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting interview details: " . $e->getMessage());
            return false;
        }
    }
    
    // Get All Interviews for User
    public function getInterviewsForUser($userId, $role = 'interviewer') {
        try {
            if ($role === 'interviewer') {
                $stmt = $this->db->prepare("
                    SELECT 
                        i.*,
                        a.applicant_name,
                        a.applicant_email,
                        a.applicant_phone,
                        j.position,
                        j.company,
                        j.location
                    FROM interview_schedules i
                    LEFT JOIN applications a ON i.application_id = a.id
                    LEFT JOIN job_listings j ON a.job_id = j.id
                    WHERE i.interviewer_id = ?
                    ORDER BY i.interview_date DESC
                ");
            } else {
                $stmt = $this->db->prepare("
                    SELECT 
                        i.*,
                        a.applicant_name,
                        a.applicant_email,
                        a.applicant_phone,
                        j.position,
                        j.company,
                        j.location,
                        u.name as interviewer_name
                    FROM interview_schedules i
                    LEFT JOIN applications a ON i.application_id = a.id
                    LEFT JOIN job_listings j ON a.job_id = j.id
                    LEFT JOIN users u ON i.interviewer_id = u.id
                    WHERE a.user_id = ?
                    ORDER BY i.interview_date DESC
                ");
            }
            
            $stmt->execute([$userId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting interviews for user: " . $e->getMessage());
            return false;
        }
    }
    
    // Update Interview Status
    public function updateInterviewStatus($interviewId, $status, $meetingLink = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE interview_schedules 
                SET status = ?, meeting_link = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $meetingLink, $interviewId]);
        } catch (PDOException $e) {
            error_log("Error updating interview status: " . $e->getMessage());
            return false;
        }
    }
    
    // Cancel Interview
    public function cancelInterview($interviewId, $reason = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE interview_schedules 
                SET status = 'cancelled', notes = CONCAT(IFNULL(notes, ''), '\nCancelled: ', ?), updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            $result = $stmt->execute([$reason, $interviewId]);
            
            if ($result) {
                // Get application ID to update status
                $interview = $this->getInterviewDetails($interviewId);
                if ($interview) {
                    $this->updateApplicationInterviewStatus($interview['application_id'], 'not_scheduled');
                }
            }
            
            return $result;
        } catch (PDOException $e) {
            error_log("Error cancelling interview: " . $e->getMessage());
            return false;
        }
    }
    
    // Reschedule Interview
    public function rescheduleInterview($interviewId, $newDate, $newTimezone = 'Asia/Jakarta', $reason = null) {
        try {
            $stmt = $this->db->prepare("
                UPDATE interview_schedules 
                SET interview_date = ?, timezone = ?, status = 'scheduled', 
                    notes = CONCAT(IFNULL(notes, ''), '\nRescheduled: ', ?), updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            return $stmt->execute([$newDate, $newTimezone, $reason, $interviewId]);
        } catch (PDOException $e) {
            error_log("Error rescheduling interview: " . $e->getMessage());
            return false;
        }
    }
    
    // Submit Interview Feedback
    public function submitInterviewFeedback($interviewId, $interviewerId, $feedbackData) {
        try {
            $this->db->beginTransaction();
            
            // Check if feedback already exists
            $checkStmt = $this->db->prepare("SELECT id FROM interview_feedback WHERE interview_schedule_id = ?");
            $checkStmt->execute([$interviewId]);
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing feedback
                $stmt = $this->db->prepare("
                    UPDATE interview_feedback SET 
                        communication_score = ?,
                        technical_score = ?,
                        problem_solving_score = ?,
                        cultural_fit_score = ?,
                        strengths = ?,
                        weaknesses = ?,
                        areas_for_improvement = ?,
                        recommendation = ?,
                        next_steps = ?,
                        additional_notes = ?,
                        feedback_status = 'submitted'
                    WHERE interview_schedule_id = ?
                ");
                
                $result = $stmt->execute([
                    $feedbackData['communication_score'],
                    $feedbackData['technical_score'],
                    $feedbackData['problem_solving_score'],
                    $feedbackData['cultural_fit_score'],
                    $feedbackData['strengths'],
                    $feedbackData['weaknesses'],
                    $feedbackData['areas_for_improvement'],
                    $feedbackData['recommendation'],
                    $feedbackData['next_steps'],
                    $feedbackData['additional_notes'],
                    $interviewId
                ]);
            } else {
                // Insert new feedback
                $stmt = $this->db->prepare("
                    INSERT INTO interview_feedback 
                    (interview_schedule_id, interviewer_id, applicant_id, communication_score, technical_score, 
                     problem_solving_score, cultural_fit_score, strengths, weaknesses, areas_for_improvement, 
                     recommendation, next_steps, additional_notes, feedback_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'submitted')
                ");
                
                // Handle empty applicant_id
                $applicantId = !empty($feedbackData['applicant_id']) ? $feedbackData['applicant_id'] : null;
                
                $result = $stmt->execute([
                    $interviewId,
                    $interviewerId,
                    $applicantId,
                    $feedbackData['communication_score'],
                    $feedbackData['technical_score'],
                    $feedbackData['problem_solving_score'],
                    $feedbackData['cultural_fit_score'],
                    $feedbackData['strengths'],
                    $feedbackData['weaknesses'],
                    $feedbackData['areas_for_improvement'],
                    $feedbackData['recommendation'],
                    $feedbackData['next_steps'],
                    $feedbackData['additional_notes']
                ]);
            }
            
            if ($result) {
                // Update interview status to completed
                $this->updateInterviewStatus($interviewId, 'completed');
                
                // Update application status based on recommendation
                $this->updateApplicationBasedOnFeedback($interviewId, $feedbackData['recommendation']);
            }
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error submitting interview feedback: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Interview Feedback
    public function getInterviewFeedback($interviewId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    f.*,
                    u.name as interviewer_name,
                    i.interview_date,
                    a.applicant_name,
                    j.position,
                    j.company
                FROM interview_feedback f
                LEFT JOIN interview_schedules i ON f.interview_schedule_id = i.id
                LEFT JOIN users u ON f.interviewer_id = u.id
                LEFT JOIN applications a ON i.application_id = a.id
                LEFT JOIN job_listings j ON a.job_id = j.id
                WHERE f.interview_schedule_id = ?
            ");
            
            $stmt->execute([$interviewId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting interview feedback: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Available Time Slots
    public function getAvailableTimeSlots($interviewerId, $date, $timezone = 'Asia/Jakarta') {
        try {
            $startDate = $date . ' 00:00:00';
            $endDate = $date . ' 23:59:59';
            
            $stmt = $this->db->prepare("
                SELECT interview_date, duration_minutes 
                FROM interview_schedules 
                WHERE interviewer_id = ? 
                AND interview_date BETWEEN ? AND ?
                AND status IN ('scheduled', 'confirmed')
            ");
            
            $stmt->execute([$interviewerId, $startDate, $endDate]);
            $bookedSlots = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Generate available slots (every 30 minutes from 9 AM to 5 PM)
            $availableSlots = [];
            $startTime = strtotime($date . ' 09:00:00');
            $endTime = strtotime($date . ' 17:00:00');
            
            for ($time = $startTime; $time <= $endTime; $time += 1800) { // 30 minutes
                $slotTime = date('Y-m-d H:i:s', $time);
                $isAvailable = true;
                
                foreach ($bookedSlots as $booked) {
                    $bookedStart = strtotime($booked['interview_date']);
                    $bookedEnd = $bookedStart + ($booked['duration_minutes'] * 60);
                    
                    if ($time >= $bookedStart && $time < $bookedEnd) {
                        $isAvailable = false;
                        break;
                    }
                }
                
                if ($isAvailable) {
                    $availableSlots[] = [
                        'datetime' => $slotTime,
                        'formatted' => date('H:i', $time)
                    ];
                }
            }
            
            return $availableSlots;
        } catch (PDOException $e) {
            error_log("Error getting available time slots: " . $e->getMessage());
            return false;
        }
    }
    
    // Generate Meeting Link
    public function generateMeetingLink($interviewType = 'video') {
        if ($interviewType === 'video') {
            // Generate unique meeting ID
            $meetingId = 'interview_' . uniqid();
            return "https://meet.google.com/" . $meetingId;
        }
        return null;
    }
    
    // Update Application Interview Status
    private function updateApplicationInterviewStatus($applicationId, $status) {
        try {
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET interview_status = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            return $stmt->execute([$status, $applicationId]);
        } catch (PDOException $e) {
            error_log("Error updating application interview status: " . $e->getMessage());
            return false;
        }
    }
    
    // Update Application Based on Feedback
    private function updateApplicationBasedOnFeedback($interviewId, $recommendation) {
        try {
            $interview = $this->getInterviewDetails($interviewId);
            
            if (!$interview) {
                return false;
            }
            
            $newStatus = 'reviewed';
            if ($recommendation === 'hire' || $recommendation === 'strong_hire') {
                $newStatus = 'accepted';
            } elseif ($recommendation === 'no_hire') {
                $newStatus = 'rejected';
            }
            
            $stmt = $this->db->prepare("
                UPDATE applications 
                SET status = ?, interview_status = 'completed', updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            return $stmt->execute([$newStatus, $interview['application_id']]);
        } catch (PDOException $e) {
            error_log("Error updating application based on feedback: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Interview Statistics
    public function getInterviewStatistics($userId = null, $dateFrom = null, $dateTo = null) {
        try {
            $whereClause = "";
            $params = [];
            
            if ($userId) {
                $whereClause .= " AND i.interviewer_id = ?";
                $params[] = $userId;
            }
            
            if ($dateFrom) {
                $whereClause .= " AND i.interview_date >= ?";
                $params[] = $dateFrom;
            }
            
            if ($dateTo) {
                $whereClause .= " AND i.interview_date <= ?";
                $params[] = $dateTo;
            }
            
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_interviews,
                    SUM(CASE WHEN i.status = 'completed' THEN 1 ELSE 0 END) as completed_interviews,
                    SUM(CASE WHEN i.status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_interviews,
                    SUM(CASE WHEN i.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_interviews,
                    AVG(f.overall_score) as average_score
                FROM interview_schedules i
                LEFT JOIN interview_feedback f ON i.id = f.interview_schedule_id
                WHERE 1=1 $whereClause
            ");
            
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting interview statistics: " . $e->getMessage());
            return false;
        }
    }
    
    // Get all interviews (for admin)
    public function getAllInterviews() {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    i.*,
                    a.applicant_name,
                    a.applicant_email,
                    a.applicant_phone,
                    j.position,
                    j.company,
                    j.location,
                    u.name as interviewer_name
                FROM interview_schedules i
                LEFT JOIN applications a ON i.application_id = a.id
                LEFT JOIN job_listings j ON a.job_id = j.id
                LEFT JOIN users u ON i.interviewer_id = u.id
                ORDER BY i.interview_date DESC
            ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting all interviews: " . $e->getMessage());
            return false;
        }
    }
    
    // Get interview by ID
    public function getInterviewById($interviewId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    i.*,
                    a.applicant_name,
                    a.applicant_email,
                    a.applicant_phone,
                    j.position,
                    j.company,
                    j.location,
                    u.name as interviewer_name
                FROM interview_schedules i
                LEFT JOIN applications a ON i.application_id = a.id
                LEFT JOIN job_listings j ON a.job_id = j.id
                LEFT JOIN users u ON i.interviewer_id = u.id
                WHERE i.id = ?
            ");
            $stmt->execute([$interviewId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting interview by ID: " . $e->getMessage());
            return false;
        }
    }
    
    // Update interview
    public function updateInterview($interviewId, $interviewDate, $interviewType, $duration, $timezone, $notes, $meetingLink) {
        try {
            $stmt = $this->db->prepare("
                UPDATE interview_schedules 
                SET interview_date = ?, 
                    interview_type = ?, 
                    duration_minutes = ?, 
                    timezone = ?, 
                    notes = ?, 
                    meeting_link = ?,
                    updated_at = NOW()
                WHERE id = ?
            ");
            return $stmt->execute([$interviewDate, $interviewType, $duration, $timezone, $notes, $meetingLink, $interviewId]);
        } catch(PDOException $e) {
            error_log("Error updating interview: " . $e->getMessage());
            return false;
        }
    }
    
    // ==================== INTERVIEW RESPONSES METHODS ====================
    
    // Save Interview Response
    public function saveInterviewResponse($interviewId, $questionId, $responseText, $score, $notes = '') {
        try {
            $this->db->beginTransaction();
            
            // Check if response already exists
            $checkStmt = $this->db->prepare("
                SELECT id FROM interview_responses 
                WHERE interview_schedule_id = ? AND question_id = ?
            ");
            $checkStmt->execute([$interviewId, $questionId]);
            
            if ($checkStmt->rowCount() > 0) {
                // Update existing response
                $stmt = $this->db->prepare("
                    UPDATE interview_responses 
                    SET response_text = ?, score = ?, notes = ?, created_at = CURRENT_TIMESTAMP
                    WHERE interview_schedule_id = ? AND question_id = ?
                ");
                $result = $stmt->execute([$responseText, $score, $notes, $interviewId, $questionId]);
            } else {
                // Insert new response
                $stmt = $this->db->prepare("
                    INSERT INTO interview_responses 
                    (interview_schedule_id, question_id, response_text, score, notes) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $result = $stmt->execute([$interviewId, $questionId, $responseText, $score, $notes]);
            }
            
            $this->db->commit();
            return $result;
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error saving interview response: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Interview Responses
    public function getInterviewResponses($interviewId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    ir.*,
                    iq.question_text,
                    iq.question_type,
                    iq.difficulty_level,
                    iq.job_position
                FROM interview_responses ir
                LEFT JOIN interview_questions iq ON ir.question_id = iq.id
                WHERE ir.interview_schedule_id = ?
                ORDER BY ir.created_at ASC
            ");
            $stmt->execute([$interviewId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting interview responses: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Response by Question
    public function getResponseByQuestion($interviewId, $questionId) {
        try {
            $stmt = $this->db->prepare("
                SELECT ir.*, iq.question_text, iq.question_type
                FROM interview_responses ir
                LEFT JOIN interview_questions iq ON ir.question_id = iq.id
                WHERE ir.interview_schedule_id = ? AND ir.question_id = ?
            ");
            $stmt->execute([$interviewId, $questionId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting response by question: " . $e->getMessage());
            return false;
        }
    }
    
    // Delete Interview Response
    public function deleteInterviewResponse($responseId) {
        try {
            $stmt = $this->db->prepare("DELETE FROM interview_responses WHERE id = ?");
            return $stmt->execute([$responseId]);
        } catch (PDOException $e) {
            error_log("Error deleting interview response: " . $e->getMessage());
            return false;
        }
    }
    
    // Get Response Statistics
    public function getResponseStatistics($interviewId) {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    COUNT(*) as total_responses,
                    AVG(score) as average_score,
                    MIN(score) as min_score,
                    MAX(score) as max_score,
                    iq.question_type,
                    COUNT(CASE WHEN score >= 4 THEN 1 END) as good_responses,
                    COUNT(CASE WHEN score <= 2 THEN 1 END) as poor_responses
                FROM interview_responses ir
                LEFT JOIN interview_questions iq ON ir.question_id = iq.id
                WHERE ir.interview_schedule_id = ?
                GROUP BY iq.question_type
            ");
            $stmt->execute([$interviewId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting response statistics: " . $e->getMessage());
            return false;
        }
    }
}
