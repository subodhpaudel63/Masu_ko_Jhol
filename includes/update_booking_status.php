<?php
session_start();
require_once 'db.php';
require_once 'auth_check.php';

header('Content-Type: application/json');

// Check if user is admin
$user = getUserFromCookie();
if (!$user || $user['userType'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = intval($data['id'] ?? 0);
    $status = $data['status'] ?? '';
    
    // Validate status
    $allowed_statuses = ['pending', 'confirmed', 'rejected'];
    if (!in_array($status, $allowed_statuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit();
    }
    
    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking status updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update booking status']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}