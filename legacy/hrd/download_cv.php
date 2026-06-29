<?php
// File handler untuk download CV yang aman
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

// Check if user is logged in and has Admin or HRD role
if(!isLoggedIn() || !in_array(getUserRole(), ['admin', 'hrd'])) {
    http_response_code(403);
    echo "Unauthorized access";
    exit();
}

// Get file parameter
$file = isset($_GET['file']) ? $_GET['file'] : '';

if(empty($file)) {
    http_response_code(400);
    echo "File parameter is required";
    exit();
}

// Sanitize file path
$file = basename($file); // Remove any path traversal attempts
$filePath = __DIR__ . '/../uploads/' . $file;

// Check if file exists
if(!file_exists($filePath)) {
    http_response_code(404);
    echo "File not found";
    exit();
}

// Check if file is in uploads directory (security check)
$realPath = realpath($filePath);
$uploadsPath = realpath(__DIR__ . '/../uploads/');

if(strpos($realPath, $uploadsPath) !== 0) {
    http_response_code(403);
    echo "Access denied";
    exit();
}

// Get file info
$fileInfo = pathinfo($filePath);
$fileName = $fileInfo['filename'];
$fileExtension = strtolower($fileInfo['extension']);

// Set appropriate headers
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '.' . $fileExtension . '"');
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

// Output file
readfile($filePath);
exit();
?>
