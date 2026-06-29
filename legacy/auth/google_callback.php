<?php
require_once '../config/database.php';
require_once '../includes/calendar_integration.php';

// Handle Google OAuth callback
if (isset($_GET['code']) && isset($_GET['state'])) {
    $code = $_GET['code'];
    $state = $_GET['state'];
    
    $calendarIntegration = new CalendarIntegration();
    
    if ($calendarIntegration->handleGoogleCallback($code, $state)) {
        // Redirect back to interviews page with success message
        header('Location: ../admin/interviews.php?calendar_success=1');
        exit();
    } else {
        // Redirect with error message
        header('Location: ../admin/interviews.php?calendar_error=1');
        exit();
    }
} else {
    // No code or state parameter, redirect to interviews page
    header('Location: ../admin/interviews.php');
    exit();
}
?>
