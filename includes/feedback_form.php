<?php
session_start();

include_once "db.php";

// Set content type to JSON for AJAX response
header('Content-Type: application/json');

// Check DB connection
if ($conn->connect_error) {
    error_log("Feedback Form - Database connection failed: " . $conn->connect_error);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed'
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Log received POST data for debugging
    error_log("Feedback Form - POST received: " . print_r($_POST, true));
    
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
        error_log("Feedback Form - Validation failed");
        echo json_encode([
            'status' => 'error',
            'message' => 'Please fill all fields correctly'
        ]);
        exit;
    }

    try {
        // First, check if the feedback table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'feedback'");
        if ($tableCheck->num_rows === 0) {
            error_log("Feedback Form - Table 'feedback' does not exist. Creating it...");
            
            // Create the feedback table
            $createTable = "CREATE TABLE `feedback` (
                `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
                `feedback_name` varchar(100) NOT NULL,
                `feedback_email` varchar(100) NOT NULL,
                `feedback_rating` int(1) NOT NULL,
                `feedback_message` text NOT NULL,
                `feedback_category` varchar(50) DEFAULT NULL,
                `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
                PRIMARY KEY (`feedback_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
            
            if (!$conn->query($createTable)) {
                error_log("Feedback Form - Failed to create table: " . $conn->error);
                throw new Exception("Failed to create feedback table");
            }
            error_log("Feedback Form - Table created successfully");
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
                    error_log("Feedback Form - Success with category");
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Thank you for your feedback!'
                    ]);
                } else {
                    error_log("Feedback Form - Execute failed: " . $stmt->error);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to save feedback: ' . $stmt->error
                    ]);
                }
                $stmt->close();
            } else {
                error_log("Feedback Form - Prepare failed: " . $conn->error);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'SQL prepare failed: ' . $conn->error
                ]);
            }
        } else {
            // Insert feedback into database without category
            $stmt = $conn->prepare("INSERT INTO feedback (feedback_name, feedback_email, feedback_rating, feedback_message) VALUES (?, ?, ?, ?)");

            if ($stmt) {
                $stmt->bind_param("ssis", $name, $email, $rating, $message);
                if ($stmt->execute()) {
                    error_log("Feedback Form - Success without category");
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'Thank you for your feedback!'
                    ]);
                } else {
                    error_log("Feedback Form - Execute failed: " . $stmt->error);
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to save feedback: ' . $stmt->error
                    ]);
                }
                $stmt->close();
            } else {
                error_log("Feedback Form - Prepare failed: " . $conn->error);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'SQL prepare failed: ' . $conn->error
                ]);
            }
        }
    } catch (Exception $e) {
        error_log("Feedback Form - Exception: " . $e->getMessage());
        echo json_encode([
            'status' => 'error',
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }

    $conn->close();
    exit;
} else {
    error_log("Feedback Form - Invalid request method: " . $_SERVER["REQUEST_METHOD"]);
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
    exit;
}
