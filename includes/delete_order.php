<?php
session_start();
include_once "db.php"; // path relative to this file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    if ($order_id > 0) {
        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $order_id);
        if ($stmt->execute()) {
            $_SESSION['msg'] = ['type' => 'success', 'text' => 'Order deleted successfully.'];
        } else {
            $_SESSION['msg'] = ['type' => 'error', 'text' => 'Failed to delete order.'];
        }
        $stmt->close();
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid order ID.'];
    }
} else {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid request method.'];
}

header("Location: /Masu%20Ko%20Jhol%28full%29/admin/myorder.php"); // adjust path as needed
exit;
