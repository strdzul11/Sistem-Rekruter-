<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)) {
        header('Location: login.php?error=Email dan password harus diisi');
        exit();
    }
    
    if(!validateEmail($email)) {
        header('Location: login.php?error=Format email tidak valid');
        exit();
    }
    
    $auth = new Auth();
    
    if($auth->login($email, $password)) {
        $role = getUserRole();
        redirectByRole($role);
    } else {
        header('Location: login.php?error=Email atau password salah');
        exit();
    }
} else {
    header('Location: login.php');
    exit();
}
?>
