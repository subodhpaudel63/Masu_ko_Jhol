<?php
header('Content-Type: application/json');
session_start();
include_once "db.php"; // path relative to this file

// Handle both form data and JSON requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if ($input) {
        // JSON request (AJAX)
        $order_id = intval($input['order_id'] ?? 0);
    } else {
        // Form data request
        $order_id = intval($_POST['order_id'] ?? 0);
    }
    
    if ($order_id > 0) {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            if (!$input) {
                $_SESSION['msg'] = ['type' => 'success', 'text' => 'Order deleted successfully.'];
            }
            $response = ['success' => true, 'message' => 'Order deleted successfully.'];
        } else {
            if (!$input) {
                $_SESSION['msg'] = ['type' => 'error', 'text' => 'Failed to delete order.'];
            }
            $response = ['success' => false, 'message' => 'Failed to delete order: ' . $stmt->error];
        }
        $stmt->close();
    } else {
        if (!$input) {
            $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid order ID.'];
        }
        $response = ['success' => false, 'message' => 'Invalid order ID.'];
    }
} else {
    if (!isset($input)) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid request method.'];
    }
    $response = ['success' => false, 'message' => 'Invalid request method.'];
}

// For AJAX requests, return JSON
if (isset($input)) {
    echo json_encode($response);
    exit;
}

// For form submissions, redirect
header("Location: /Masu%20Ko%20Jhol%28full%29/admin/orders_page.php"); // adjust path as needed
exit;
