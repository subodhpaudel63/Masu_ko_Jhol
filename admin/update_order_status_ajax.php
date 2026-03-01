<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Include database - this file is in /admin/ folder
require_once __DIR__ . '/../includes/db.php';

// Handle both form data and JSON requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        // JSON request
        $order_id = intval($input['order_id'] ?? 0);
        $status = $input['status'] ?? '';
    } else {
        // Form data request
        $order_id = intval($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
    }
    
    $allowed_status = ['Confirmed', 'Shipping', 'Ongoing', 'Delivering', 'Cancelled'];
    
    if ($order_id > 0 && in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        if (!$stmt) {
            $response = [
                'success' => false,
                'message' => 'Database error: ' . $conn->error
            ];
        } else {
            $stmt->bind_param("si", $status, $order_id);
            
            if ($stmt->execute()) {
                $response = [
                    'success' => true,
                    'message' => 'Order status updated successfully'
                ];
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Failed to update: ' . $stmt->error
                ];
            }
            $stmt->close();
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Invalid order ID or status'
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method'
    ];
}

echo json_encode($response);
exit;