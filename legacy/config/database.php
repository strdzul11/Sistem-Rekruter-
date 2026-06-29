<?php
// Bootstrap File & Helper Functions

// 1. Load Composer Autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

// 2. Load Environment Variables (.env)
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// 3. Register Class Aliases for Backward Compatibility
class_alias('App\Config\DatabaseConnection', 'Database');
class_alias('App\Auth', 'Auth');
class_alias('App\CalendarIntegration', 'CalendarIntegration');
class_alias('App\EmailNotification', 'EmailNotification');
class_alias('App\JobManager', 'JobManager');
class_alias('App\ApplicationManager', 'ApplicationManager');
class_alias('App\InterviewManager', 'InterviewManager');
class_alias('App\EvaluationManager', 'EvaluationManager');
class_alias('App\CriteriaManager', 'CriteriaManager');

// 4. Procedural Helper Functions

// Fungsi untuk memulai session
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Fungsi untuk mengecek apakah user sudah login
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Fungsi untuk mendapatkan role user yang sedang login
function getUserRole() {
    startSession();
    return $_SESSION['role'] ?? null;
}

// Fungsi untuk redirect berdasarkan role
function redirectByRole($role) {
    // Tentukan base path berdasarkan lokasi file yang memanggil
    $basePath = '';
    if(strpos($_SERVER['REQUEST_URI'], '/auth/') !== false) {
        $basePath = '../';
    }
    
    switch($role) {
        case 'admin':
            header('Location: ' . $basePath . 'admin/dashboard.php');
            break;
        case 'hrd':
            header('Location: ' . $basePath . 'hrd/dashboard.php');
            break;
        case 'applicant':
            header('Location: ' . $basePath . 'applicant/dashboard.php');
            break;
        default:
            header('Location: ' . $basePath . 'index.php');
    }
    exit();
}

// Fungsi untuk sanitasi input
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk validasi email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Fungsi untuk hash password
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Fungsi untuk verify password
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Fungsi untuk format tanggal Indonesia
function formatDateIndonesian($date) {
    if (empty($date)) return '';
    $bulan = array (
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    $split = explode('-', $date);
    if (count($split) < 3) return $date;
    return $split[2] . ' ' . $bulan[ (int)$split[1] ] . ' ' . $split[0];
}
