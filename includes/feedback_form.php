<?php
session_start();

include_once "db.php";

// Set content type to JSON for AJAX response
header('Content-Type: application/json');

// Check DB connection
if ($conn->connect_error) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $message = trim($_POST['comments'] ?? '');
    $category = trim($_POST['category'] ?? '');

    // Validate required fields
    if (
        $name === '' ||
        !filter_var($email, FILTER_VALIDATE_EMAIL) ||
        $rating < 1 || $rating > 5 ||
        $message === ''
    ) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill all fields correctly'
        ]);
        exit;
    }

    // Check if feedback_category column exists in the table
    $columnsResult = $conn->query("SHOW COLUMNS FROM feedback LIKE 'feedback_category'");
    $hasCategoryColumn = $columnsResult->num_rows > 0;

    if ($hasCategoryColumn) {
        // Insert feedback into database with category
        $stmt = $conn->prepare("INSERT INTO feedback (feedback_name, feedback_email, feedback_rating, feedback_message, feedback_category) VALUES (?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("ssiss", $name, $email, $rating, $message, $category);
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Thank you for your feedback!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save feedback'
                ]);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'SQL prepare failed'
            ]);
        }
    } else {
        // Insert feedback into database without category
        $stmt = $conn->prepare("INSERT INTO feedback (feedback_name, feedback_email, feedback_rating, feedback_message) VALUES (?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("ssis", $name, $email, $rating, $message);
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Thank you for your feedback!'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to save feedback'
                ]);
            }
            $stmt->close();
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'SQL prepare failed'
            ]);
        }
    }

    $conn->close();
    exit;
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}
