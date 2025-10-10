<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../includes/auth_check.php';

/* Only accept POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
    exit();
}

/* Input validation */
$userEmail = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';

if ($userEmail === '' || $password === '') {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Email and password are required.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
    exit();
}

/* Special admin credentials check */
if ($userEmail === 'subodhpaudel0000@gmail.com' && $password === 'admin@123') {
    // Set session variables
    $_SESSION['email'] = $userEmail;
    $_SESSION['user_type'] = 'admin';
    
    // Set cookies
    $now = time();
    $cookieMaxAge = 86400; // 24 hours
    
    setcookie('email',      encrypt($userEmail,     SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
    setcookie('user_type',  encrypt('admin',        SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
    setcookie('login_time', encrypt((string) $now,  SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
    setcookie('user_img',   encrypt('',             SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
    
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'Admin login successful!'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php');
    exit();
}

/* Invalid credentials */
$_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid admin credentials.'];
header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
exit();