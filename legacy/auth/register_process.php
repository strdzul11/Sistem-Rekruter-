<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = sanitizeInput($_POST['name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $address = sanitizeInput($_POST['address']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validasi input
    if(empty($name) || empty($email) || empty($password)) {
        header('Location: register.php?error=Semua field wajib diisi');
        exit();
    }
    
    if(!validateEmail($email)) {
        header('Location: register.php?error=Format email tidak valid');
        exit();
    }
    
    if($password !== $confirmPassword) {
        header('Location: register.php?error=Password dan konfirmasi password tidak cocok');
        exit();
    }
    
    if(strlen($password) < 6) {
        header('Location: register.php?error=Password minimal 6 karakter');
        exit();
    }
    
    $auth = new Auth();
    
    $result = $auth->register($name, $email, $password, 'applicant', $phone, $address);
    
    if($result) {
        header('Location: login.php?success=Akun berhasil dibuat. Silakan login.');
        exit();
    } else {
        header('Location: register.php?error=Email sudah digunakan atau terjadi kesalahan');
        exit();
    }
} else {
    header('Location: register.php');
    exit();
}
?>
