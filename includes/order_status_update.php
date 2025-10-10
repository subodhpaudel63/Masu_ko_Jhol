<?php
session_start();
include_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    // Add 'Cancelled' to allowed status
    $allowed_status = ['Confirmed', 'Shipping', 'Ongoing', 'Delivering', 'Cancelled'];

    error_log("Received order_id: $order_id, status: $status");

    if ($order_id > 0 && in_array($status, $allowed_status)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);

        if ($stmt->execute()) {
            error_log("Update success for order $order_id to status $status");
            $_SESSION['msg'] = [
                'type' => 'success',
                'text' => 'Order status updated successfully.'
            ];
        } else {
            error_log("Update failed: " . $stmt->error);
            $_SESSION['msg'] = [
                'type' => 'error',
                'text' => 'Failed to update order status. Error: ' . $stmt->error
            ];
        }
        $stmt->close();
    } else {
        error_log("Invalid order ID or status. order_id: $order_id, status: $status");
        $_SESSION['msg'] = [
            'type' => 'error',
            'text' => 'Invalid order ID or status.'
        ];
    }
} else {
    $_SESSION['msg'] = [
        'type' => 'error',
        'text' => 'Invalid request method.'
    ];
}

// Redirect back to admin myorder page
header("Location: /Masu%20Ko%20Jhol%28full%29/admin/myorder.php");
exit;