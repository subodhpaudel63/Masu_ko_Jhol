<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Correct path
require_once __DIR__ . '/db.php';

// Check connection
if (!isset($conn) || $conn->connect_error) {
    die("Connection error");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    if (empty($_POST['email'])) {
        header("Location: ../index.php?status=error");
        exit();
    }

    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../index.php?status=invalid");
        exit();
    }

    // Prepare
    $stmt = $conn->prepare("INSERT INTO subscribers (email) VALUES (?)");

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("s", $email);

    if ($stmt->execute()) {
        header("Location: ../index.php?status=success");
    } else {
        header("Location: ../index.php?status=error");
    }

    $stmt->close();
}

$conn->close();
exit();
?>