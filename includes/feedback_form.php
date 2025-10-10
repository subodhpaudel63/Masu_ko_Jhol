<?php
session_start();

// Redirect if session email is set
$redirectPath = isset($_SESSION['email']) && !empty($_SESSION['email'])
    ? "/college/users/contactus"
    : "/college/contactus";

include_once "db.php";

// Check DB connection
if ($conn->connect_error) {
    $_SESSION['alert'] = [
        'status' => 'error',
        'msg' => 'Database connection failed'
    ];
    header("Location: $redirectPath");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $message = trim($_POST['comments'] ?? '');

    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $rating < 1 || $rating > 5 ||
        $message === ''
    ) {
        $_SESSION['alert'] = [
            'status' => 'error',
            'msg' => 'Please fill all fields correctly'
        ];
        header("Location: $redirectPath");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO feedback (feedback_name, feedback_email, feedback_rating, feedback_message) VALUES (?, ?, ?, ?)");

    if ($stmt) {
        $stmt->bind_param("ssis", $name, $email, $rating, $message);
        if ($stmt->execute()) {
            $_SESSION['alert'] = [
                'status' => 'success',
                'msg' => 'Thank you for your feedback!'
            ];
        } else {
            $_SESSION['alert'] = [
                'status' => 'error',
                'msg' => 'Failed to save feedback'
            ];
        }
        $stmt->close();
    } else {
        $_SESSION['alert'] = [
            'status' => 'error',
            'msg' => 'SQL prepare failed'
        ];
    }

    $conn->close();
    header("Location: $redirectPath");
    exit;
} else {
    $_SESSION['alert'] = [
        'status' => 'error',
        'msg' => 'Invalid request'
    ];
    header("Location: $redirectPath");
    exit;
}
