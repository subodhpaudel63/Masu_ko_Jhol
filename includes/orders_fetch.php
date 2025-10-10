<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['ok' => false, 'error' => 'Not logged in']);
    exit();
}

$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT order_id, menu_name, price, quantity, total_price, status, order_time FROM orders WHERE email = ? ORDER BY order_id DESC");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

echo json_encode(['ok' => true, 'orders' => $orders]);