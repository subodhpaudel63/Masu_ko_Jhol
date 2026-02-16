<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';
require_once 'auth_check.php';

header('Content-Type: application/json');

// Check if user is logged in using the auth system
$user = getUserFromCookie();
if (!$user) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit();
}

$email = $user['email'];
$stmt = $conn->prepare("SELECT order_id, menu_name, price, quantity, total_price, status, order_time, order_date FROM orders WHERE email = ? ORDER BY order_id DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    // Format the order data
    $orders[] = [
        'order_id' => $row['order_id'],
        'menu_name' => $row['menu_name'],
        'price' => (float)$row['price'],
        'quantity' => (int)$row['quantity'],
        'total_price' => (float)$row['total_price'],
        'status' => $row['status'],
        'order_time' => $row['order_time'],
        'order_date' => $row['order_date']
    ];
}
$stmt->close();

echo json_encode(['ok' => true, 'orders' => $orders]);