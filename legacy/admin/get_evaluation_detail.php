<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/evaluation_manager.php';

// Cek apakah user sudah login dan role admin
if(!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak']);
    exit();
}

$applicationId = $_GET['application_id'] ?? null;

if (!$applicationId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID aplikasi tidak valid']);
    exit();
}

try {
    $evaluationManager = new EvaluationManager();
    
    // Get evaluations
    $evaluations = $evaluationManager->getApplicationEvaluations($applicationId);
    
    // Calculate SAW score
    $sawScore = $evaluationManager->calculateSAWScore($applicationId);
    
    echo json_encode([
        'success' => true,
        'evaluations' => $evaluations,
        'saw_score' => $sawScore
    ]);
    
} catch (Exception $e) {
    error_log("Error getting evaluation detail: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan server']);
}
?>
