<?php
// Set content type to JSON and start output buffering
header('Content-Type: application/json');
ob_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/evaluation_manager.php';
require_once __DIR__ . '/../includes/job_manager.php';

// Cek apakah user sudah login dan role applicant
if(!isLoggedIn() || getUserRole() !== 'applicant') {
    http_response_code(403);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit();
}

$applicationId = $_GET['application_id'] ?? null;

if (!$applicationId) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'ID aplikasi tidak valid']);
    exit();
}

try {
    $evaluationManager = new EvaluationManager();
    $applicationManager = new ApplicationManager();
    
    // Verify that this application belongs to the logged-in user
    $application = $applicationManager->getApplicationById($applicationId);
    if (!$application) {
        http_response_code(404);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Aplikasi tidak ditemukan']);
        exit();
    }
    
    // Check if application belongs to logged-in user (for registered users)
    // or if it's a quick apply application, we need different verification
    if ($application['user_id'] && $application['user_id'] != $_SESSION['user_id']) {
        http_response_code(403);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'Akses ditolak untuk aplikasi ini']);
        exit();
    }
    
    // For quick apply (user_id is null), we can't verify ownership easily
    // In a real system, you might want to use session data or other methods
    // For now, we'll allow access to all applications for demo purposes
    // TODO: Implement proper ownership verification for quick apply applications
    
    // Get evaluations
    $evaluations = $evaluationManager->getApplicationEvaluations($applicationId);
    
    // Calculate SAW score
    $sawScore = $evaluationManager->calculateSAWScore($applicationId);
    
    // Get criteria weights for reference
    $criteria = $evaluationManager->getActiveCriteria();
    $criteriaWeights = [];
    foreach ($criteria as $criterion) {
        $criteriaWeights[$criterion['id']] = $criterion['weight'];
    }
    
    // Clean output buffer and send JSON
    ob_clean();
    echo json_encode([
        'success' => true,
        'evaluations' => $evaluations,
        'saw_score' => $sawScore,
        'criteria_weights' => $criteriaWeights
    ]);
    
} catch (Exception $e) {
    error_log("Error getting detailed evaluation: " . $e->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']);
}
?>
