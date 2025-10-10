<?php
/**
 * update_password.php — Unified endpoint to change the *logged‑in* user’s password
 * ---------------------------------------------------------------------------
 * POST fields expected:
 *   - new      (string) : new password
 *   - confirm  (string) : confirmation (must match "new")
 *
 * Redirection logic (applied **every** time we exit):
 *   • If userType === 'admin' → /admin/index
 *   • If userType === 'user'  → /client/index
 *   • Anything else           → /login
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth_check.php';

$user = requireLogin(); // will redirect to /login if not logged‑in

// Determine redirect destination for this session
switch ($user['userType']) {
    case 'admin':
        $home = '/Masu%20Ko%20Jhol%28full%29/admin/index.php';
        break;
    case 'user':
        $home = '/Masu%20Ko%20Jhol%28full%29/client/index.php';
        break;
    default:
        header('Location: /Masu%20Ko%20Jhol%28full%29/login.php');
        exit();
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

$newPw     = $_POST['new']     ?? '';
$confirmPw = $_POST['confirm'] ?? '';

// Complexity rule: ≥3 chars, ≥3 digits, ≥1 symbol
$strongPw = preg_match('/^(?=(?:.*\d){3,})(?=.*[!@#$%^&*()_+\-=[\]{};:\'“\\|,.<>\/?]).{3,}$/', $newPw);
if (!$strongPw) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Password does not meet complexity requirements.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $home));
    exit();
}

if ($newPw !== $confirmPw) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Passwords do not match.'];
    header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? $home));
    exit();
}

try {
    $hashed = password_hash($newPw, PASSWORD_DEFAULT);

    $stmt = $conn->prepare('UPDATE users SET password = ? WHERE email = ? LIMIT 1');
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    $stmt->bind_param('ss', $hashed, $user['email']);

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $stmt->close();

    $_SESSION['msg'] = ['type' => 'success', 'text' => 'Password updated successfully.'];
} catch (Exception $e) {
    // Log the error in production
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'An error occurred.'];
}

// Always land on the correct dashboard afterwards
header('Location: ' . $home);
exit();