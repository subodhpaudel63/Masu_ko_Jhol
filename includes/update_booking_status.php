<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Include database - adjust path since this file is in the admin folder
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Check if user is authenticated (session-based or cookie-based)
// This handles both the legacy session method and cookie-based auth
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check session auth OR cookie auth
$isAuthenticated = false;
if (isset($_SESSION['admin_id'])) {
    $isAuthenticated = true;
} elseif (isset($_COOKIE['admin_type'])) {
    try {
        $userType = decrypt($_COOKIE['admin_type'], SECRET_KEY);
        if ($userType === 'admin') {
            $isAuthenticated = true;
        }
    } catch (Exception $e) {
        // Cookie decryption failed, not authenticated
    }
}

if (!$isAuthenticated) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

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
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            // Get updated booking data
            $booking_stmt = $conn->prepare("SELECT id, name, email, phone, booking_date, booking_time, people, message, status, created_at FROM bookings WHERE id = ?");
            $booking_stmt->bind_param("i", $order_id);
            $booking_stmt->execute();
            $result = $booking_stmt->get_result();
            $updated_booking = $result->fetch_assoc();
            
            $response = [
                'success' => true,
                'message' => 'Booking status updated successfully',
                'booking' => $updated_booking
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to update booking status: ' . $stmt->error
            ];
        }
        $stmt->close();
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