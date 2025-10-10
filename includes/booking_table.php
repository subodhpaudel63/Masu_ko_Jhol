<?php
session_start();
include_once "db.php"; // defines $host, $user, $password, $db

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
require_once __DIR__ . '/auth_check.php';
$user = getUserFromCookie();

// If user is not logged in, redirect to login
if (!$user) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Please login to book a table.'];
    header("Location: /Masu%20Ko%20Jhol%28full%29/login.php?action=book_table");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $date    = trim($_POST['date'] ?? '');
    $time    = trim($_POST['time'] ?? '');
    $people  = trim($_POST['people'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Debug: Log received data
    error_log("Booking data received: " . print_r($_POST, true));

    // Basic required fields validation
    if (empty($name) || empty($email)) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Name and Email are required'];
        redirect_user();
        exit;
    }

    // Use prepared statements for security
    $stmt = $conn->prepare("INSERT INTO bookings (name, email, phone, booking_date, booking_time, people, message) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if ($stmt === false) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Prepare failed: ' . $conn->error];
        redirect_user();
        exit;
    }
    
    $stmt->bind_param("sssssis", $name, $email, $phone, $date, $time, $people, $message);

    if ($stmt->execute()) {
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Your table has been booked successfully!'];
        error_log("Booking successful for: " . $name . " (" . $email . ")");
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Booking failed: ' . $stmt->error];
        error_log("Booking failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
} else {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid request method'];
}

redirect_user();

// Function to redirect user
function redirect_user()
{
    // Check if the referrer is from client directory
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], '/client/') !== false) {
        header("Location: ../client/index.php");
    } else {
        header("Location: ../index.php");
    }
    exit;
}
?>