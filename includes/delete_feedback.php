<?php
header('Content-Type: application/json');
session_start();

require_once 'db.php';
require_once 'admin_auth.php';

// Check if user is authenticated as admin
if (!isset($_SESSION['admin_id']) && !isset($_COOKIE['admin_type'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $feedback_id = intval($_POST['feedback_id'] ?? 0);

    if ($feedback_id > 0) {
        // Prepare statement to delete feedback using an existing primary key column.
        $possible_keys = ['feedback_id', 'id'];
        $stmt = null;
        
        foreach ($possible_keys as $key) {
            $columnCheck = $conn->query("SHOW COLUMNS FROM feedback LIKE '" . $conn->real_escape_string($key) . "'");
            if ($columnCheck && $columnCheck->num_rows > 0) {
                $stmt = $conn->prepare("DELETE FROM feedback WHERE `$key` = ?");
                if ($stmt) {
                    break;
                }
            }
        }
        
        if (!$stmt) {
            $response = [
                'success' => false,
                'message' => 'Unable to prepare delete statement'
            ];
        } else {
            $stmt->bind_param("i", $feedback_id);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response = [
                        'success' => true,
                        'message' => 'Feedback deleted successfully.'
                    ];
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Feedback not found.'
                    ];
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to delete feedback: ' . $stmt->error
                ];
            }

            $stmt->close();
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Invalid feedback ID.'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

echo json_encode($response);
exit;
