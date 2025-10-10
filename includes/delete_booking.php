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
    
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete booking']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}