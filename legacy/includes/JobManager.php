<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use PDO;
use PDOException;

class JobManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Create new job listing
    public function createJob($position, $company, $location, $description, $requirements, $salaryRange, $employmentType, $createdBy) {
        try {
            $query = "INSERT INTO job_listings (position, company, location, description, requirements, salary_range, employment_type, created_by) VALUES (:position, :company, :location, :description, :requirements, :salary_range, :employment_type, :created_by)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':salary_range', $salaryRange);
            $stmt->bindParam(':employment_type', $employmentType);
            $stmt->bindParam(':created_by', $createdBy);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] JobManager::createJob Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get all job listings
    public function getAllJobs($status = 'active') {
        try {
            $query = "SELECT j.*, u.name as created_by_name FROM job_listings j 
                     LEFT JOIN users u ON j.created_by = u.id 
                     WHERE j.status = :status 
                     ORDER BY j.created_at DESC";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] JobManager::getAllJobs Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get job by ID
    public function getJobById($id) {
        try {
            $query = "SELECT j.*, u.name as created_by_name FROM job_listings j 
                     LEFT JOIN users u ON j.created_by = u.id 
                     WHERE j.id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] JobManager::getJobById Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Update job listing
    public function updateJob($id, $position, $company, $location, $description, $requirements, $salaryRange, $employmentType, $status) {
        try {
            $query = "UPDATE job_listings SET position = :position, company = :company, location = :location, description = :description, requirements = :requirements, salary_range = :salary_range, employment_type = :employment_type, status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':position', $position);
            $stmt->bindParam(':company', $company);
            $stmt->bindParam(':location', $location);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':salary_range', $salaryRange);
            $stmt->bindParam(':employment_type', $employmentType);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] JobManager::updateJob Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Delete job listing
    public function deleteJob($id) {
        try {
            $query = "DELETE FROM job_listings WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] JobManager::deleteJob Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get job statistics
    public function getJobStats() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_jobs,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_jobs,
                        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_jobs,
                        SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_jobs
                      FROM job_listings";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] JobManager::getJobStats Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
}
