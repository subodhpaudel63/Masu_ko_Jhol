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
    // ... inside your if ($userEmail === 'subodhpaudel0000@gmail.com' ...) block ...

// Set session variables (use admin specific keys)
$_SESSION['admin_email'] = $userEmail;
$_SESSION['admin_id'] = 1; // Or the actual ID from your DB

// Set cookies with unique names to avoid kicking out regular users
$now = time();
$cookieMaxAge = 86400; // 24 hours

// FIXED: Changed cookie names from 'email' to 'admin_email', 'user_type' to 'admin_type', etc.
setcookie('admin_email',      encrypt($userEmail,     SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
setcookie('admin_type',       encrypt('admin',        SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
setcookie('admin_login_time',  encrypt((string) $now,  SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
    
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'Admin login successful!'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php');
    exit();
}

/* Invalid credentials */
$_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid admin credentials.'];
header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
exit();