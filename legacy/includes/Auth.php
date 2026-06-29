<?php
namespace App;

use App\Config\DatabaseConnection as Database;
use PDO;
use PDOException;

class Auth {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    // Login user
    public function login($email, $password) {
        try {
            $query = "SELECT id, name, email, password, role FROM users WHERE email = :email";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if(verifyPassword($password, $user['password'])) {
                    startSession();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['role'] = $user['role'];
                    
                    return true;
                }
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::login Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Register user
    public function register($name, $email, $password, $role = 'applicant', $phone = '', $address = '') {
        try {
            // Cek apakah email sudah ada
            $checkQuery = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                return false; // Email sudah ada
            }
            
            $hashedPassword = hashPassword($password);
            
            $query = "INSERT INTO users (name, email, password, role, phone, address) VALUES (:name, :email, :password, :role, :phone, :address)";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $hashedPassword);
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            
            if($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::register Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Logout user
    public function logout() {
        startSession();
        session_destroy();
        return true;
    }
    
    // Get user by ID
    public function getUserById($id) {
        try {
            $query = "SELECT u.id, u.name, u.email, u.role, u.phone, u.address, u.created_at,
                             ap.age, ap.gender, ap.work_experience, ap.resume_path
                       FROM users u 
                       LEFT JOIN applicant_profiles ap ON u.id = ap.user_id 
                       WHERE u.id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::getUserById Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Update user profile
    public function updateProfile($id, $name, $phone, $address) {
        try {
            $query = "UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':id', $id);
            
            return $stmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::updateProfile Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Update profile with additional details
    public function updateProfileWithDetails($id, $name, $phone, $address, $age, $gender, $workExperience, $resumePath = null) {
        try {
            // Update users table
            $query = "UPDATE users SET name = :name, phone = :phone, address = :address WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':address', $address);
            $stmt->bindParam(':id', $id);
            
            if(!$stmt->execute()) {
                return false;
            }
            
            // Check if applicant profile exists
            $checkQuery = "SELECT id FROM applicant_profiles WHERE user_id = :user_id";
            $checkStmt = $this->db->prepare($checkQuery);
            $checkStmt->bindParam(':user_id', $id);
            $checkStmt->execute();
            
            if($checkStmt->rowCount() > 0) {
                // Update existing profile
                $updateQuery = "UPDATE applicant_profiles SET age = :age, gender = :gender, work_experience = :work_experience, resume_path = :resume_path WHERE user_id = :user_id";
                $updateStmt = $this->db->prepare($updateQuery);
                $updateStmt->bindParam(':age', $age);
                $updateStmt->bindParam(':gender', $gender);
                $updateStmt->bindParam(':work_experience', $workExperience);
                $updateStmt->bindParam(':resume_path', $resumePath);
                $updateStmt->bindParam(':user_id', $id);
                
                return $updateStmt->execute();
            } else {
                // Create new profile
                $insertQuery = "INSERT INTO applicant_profiles (user_id, age, gender, work_experience, resume_path) VALUES (:user_id, :age, :gender, :work_experience, :resume_path)";
                $insertStmt = $this->db->prepare($insertQuery);
                $insertStmt->bindParam(':user_id', $id);
                $insertStmt->bindParam(':age', $age);
                $insertStmt->bindParam(':gender', $gender);
                $insertStmt->bindParam(':work_experience', $workExperience);
                $insertStmt->bindParam(':resume_path', $resumePath);
                
                return $insertStmt->execute();
            }
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::updateProfileWithDetails Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Change password
    public function changePassword($id, $currentPassword, $newPassword) {
        try {
            // Verifikasi password lama
            $query = "SELECT password FROM users WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if(!verifyPassword($currentPassword, $user['password'])) {
                return false; // Password lama salah
            }
            
            // Update password baru
            $hashedPassword = hashPassword($newPassword);
            $updateQuery = "UPDATE users SET password = :password WHERE id = :id";
            $updateStmt = $this->db->prepare($updateQuery);
            $updateStmt->bindParam(':password', $hashedPassword);
            $updateStmt->bindParam(':id', $id);
            
            return $updateStmt->execute();
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::changePassword Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
    
    // Get users by role
    public function getUsersByRole($roles) {
        try {
            $placeholders = str_repeat('?,', count($roles) - 1) . '?';
            $query = "SELECT id, name, email, role FROM users WHERE role IN ($placeholders)";
            $stmt = $this->db->prepare($query);
            $stmt->execute($roles);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("[" . date('Y-m-d H:i:s') . "] Auth::getUsersByRole Error: " . $e->getMessage() . "\n", 3, __DIR__ . '/../logs/db_errors.log');
            return false;
        }
    }
}
