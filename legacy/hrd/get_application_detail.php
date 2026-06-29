<?php
// Ensure no output before JSON
ob_clean();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/job_manager.php';

// Set JSON header
header('Content-Type: application/json');

// Check if user is logged in and has HRD role
if(!isLoggedIn() || getUserRole() !== 'hrd') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get application ID from query parameter
$applicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if($applicationId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid application ID']);
    exit();
}

try {
    $applicationManager = new ApplicationManager();
    $application = $applicationManager->getApplicationById($applicationId);
    
    if($application) {
        // Sanitize data for JSON output
        $application['applicant_name'] = htmlspecialchars($application['applicant_name']);
        $application['applicant_email'] = htmlspecialchars($application['applicant_email']);
        $application['applicant_phone'] = htmlspecialchars($application['applicant_phone']);
        $application['position'] = htmlspecialchars($application['position']);
        $application['company'] = htmlspecialchars($application['company']);
        $application['location'] = htmlspecialchars($application['location']);
        $application['description'] = htmlspecialchars($application['description']);
        $application['requirements'] = htmlspecialchars($application['requirements']);
        
        if($application['cover_letter']) {
            $application['cover_letter'] = htmlspecialchars($application['cover_letter']);
        }
        
        echo json_encode(['success' => true, 'application' => $application]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Application not found']);
    }
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>
