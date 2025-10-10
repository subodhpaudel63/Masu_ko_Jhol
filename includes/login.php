<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/db.php';        // defines $conn (mysqli)
require_once __DIR__ . '/auth_check.php'; // defines encrypt(), SECRET_KEY, routeAfterLogin()

/* Only accept POST  */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php');
    exit();
}

/* Input validation */
$userEmail = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';

if ($userEmail === '' || $password === '') {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Email and password are required.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php');
    exit();
}

/* Special case for admin user */
if ($userEmail === 'subodhpaudel0000@gmail.com' && $password === 'admin') {
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
    
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'Login successful!'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php');
    exit();
}

/*  Fetch user with a prepared statement  */
$stmt = $conn->prepare('SELECT id, email, password, user_type, user_img FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Database error.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php');
    exit();
}

$stmt->bind_param('s', $userEmail);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

/*  Verify credentials  */
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid email or password.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php');
    exit();
}

/*  Successful login  */
$now          = time();
$cookieMaxAge = 86400; // 24 h

setcookie('email',      encrypt($user['email'],     SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
setcookie('user_type',  encrypt($user['user_type'], SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
setcookie('login_time', encrypt((string) $now,      SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);
setcookie('user_img',   encrypt($user['user_img'] ?? '', SECRET_KEY), $now + $cookieMaxAge, '/', '', false, true);

$_SESSION['msg'] = ['type' => 'success', 'text' => 'Login successful!'];

// Check for action parameter to determine redirect
$action = $_GET['action'] ?? '';

// Redirect based on role and action
if ($user['user_type'] === 'admin') {
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php');
    exit();
}

// For regular users, redirect based on action
switch ($action) {
    case 'book_table':
        header('Location: /Masu%20Ko%20Jhol%28full%29/client/index.php#book-table-section');
        break;
    case 'order_food':
        header('Location: /Masu%20Ko%20Jhol%28full%29/client/index.php');
        break;
    case 'add_to_cart':
        header('Location: /Masu%20Ko%20Jhol%28full%29/client/index.php');
        break;
    default:
        header('Location: /Masu%20Ko%20Jhol%28full%29/client/index.php');
        break;
}
exit();
?>