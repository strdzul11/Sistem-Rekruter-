<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use PDO;
use PDOException;

class CriteriaManager {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Mendapatkan semua kriteria
    public function getAllCriteria() {
        try {
            $query = "SELECT * FROM evaluation_criteria ORDER BY name";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error getting all criteria: " . $e->getMessage());
            return [];
        }
    }
    
    // Menambah kriteria baru
    public function addCriteria($name, $description, $weight, $type = 'benefit', $minValue = 1, $maxValue = 5) {
        try {
            $query = "INSERT INTO evaluation_criteria (name, description, weight, type, min_value, max_value) 
                     VALUES (:name, :description, :weight, :type, :min_value, :max_value)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':weight', $weight);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':min_value', $minValue);
            $stmt->bindParam(':max_value', $maxValue);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error adding criteria: " . $e->getMessage());
            return false;
        }
    }
    
    // Mengupdate kriteria
    public function updateCriteria($id, $name, $description, $weight, $type = 'benefit', $minValue = 1, $maxValue = 5, $isActive = true) {
        try {
            $query = "UPDATE evaluation_criteria 
                     SET name = :name, description = :description, weight = :weight, 
                         type = :type, min_value = :min_value, max_value = :max_value, 
                         is_active = :is_active, updated_at = CURRENT_TIMESTAMP
                     WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':weight', $weight);
            $stmt->bindParam(':type', $type);
            $stmt->bindParam(':min_value', $minValue);
            $stmt->bindParam(':max_value', $maxValue);
            $stmt->bindParam(':is_active', $isActive, PDO::PARAM_BOOL);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error updating criteria: " . $e->getMessage());
            return false;
        }
    }
    
    // Menghapus kriteria
    public function deleteCriteria($id) {
        try {
            $query = "DELETE FROM evaluation_criteria WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("Error deleting criteria: " . $e->getMessage());
            return false;
        }
    }
    
    // Validasi total bobot kriteria
    public function validateTotalWeight($excludeId = null) {
        try {
            $query = "SELECT SUM(weight) as total_weight FROM evaluation_criteria WHERE is_active = 1";
            if ($excludeId) {
                $query .= " AND id != :exclude_id";
            }
            
            $stmt = $this->db->prepare($query);
            if ($excludeId) {
                $stmt->bindParam(':exclude_id', $excludeId);
            }
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $result['total_weight'] ?? 0;
        } catch(PDOException $e) {
            error_log("Error validating total weight: " . $e->getMessage());
            return 0;
        }
    }
}
