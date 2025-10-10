<?php
declare(strict_types=1);

require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/db.php';

/**
 * Verify that the current user is logged in as an admin.
 * Redirects to admin login page if not authenticated or not an admin.
 */
function require_admin(): void
{
    // Check if user_type cookie exists and is 'admin'
    if (!isset($_COOKIE['user_type'])) {
        header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
        exit();
    }
    
    $userType = decrypt($_COOKIE['user_type'], SECRET_KEY);
    
    if ($userType !== 'admin') {
        header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
        exit();
    }
    
    // Also check if login time is still valid
    if (isset($_COOKIE['login_time'])) {
        $loginTime = decrypt($_COOKIE['login_time'], SECRET_KEY);
        if ($loginTime && ctype_digit($loginTime)) {
            if (time() - (int) $loginTime > MAX_SESSION_TIME) {
                // Session expired, redirect to login
                header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php?session_expired=1');
                exit();
            }
        }
    }
}