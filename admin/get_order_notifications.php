<?php
declare(strict_types=1);

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

// Return the newest order so the admin script can detect new arrivals.
$sql = "SELECT order_id, menu_name, total_price, status, order_time
        FROM orders
        ORDER BY order_id DESC
        LIMIT 1";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Unable to load latest order.',
    ]);
    exit;
}

$order = $result->fetch_assoc();

echo json_encode([
    'success' => true,
    'order' => $order ?: null,
]);
exit;
