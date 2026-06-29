<?php
require_once 'config/database.php';

// Cek apakah user sudah login
if(isLoggedIn()) {
    $role = getUserRole();
    redirectByRole($role);
} else {
    // Redirect ke halaman landing
    header('Location: landing.php');
    exit();
}
?>
