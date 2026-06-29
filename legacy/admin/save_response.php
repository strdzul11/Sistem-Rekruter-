<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/interview_manager.php';

// Cek apakah user sudah login dan role admin
if(!isLoggedIn() || getUserRole() !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$interviewId = intval($_POST['interview_id'] ?? 0);
$questionId = intval($_POST['question_id'] ?? 0);
$responseText = trim($_POST['response_text'] ?? '');
$score = intval($_POST['score'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validation
if (!$interviewId || !$questionId || !$responseText || $score < 1 || $score > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
    exit();
}

try {
    $interviewManager = new InterviewManager();
    
    if ($interviewManager->saveInterviewResponse($interviewId, $questionId, $responseText, $score, $notes)) {
        echo json_encode(['success' => true, 'message' => 'Response saved successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save response']);
    }
} catch (Exception $e) {
    error_log("Error saving response: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
}
?>
