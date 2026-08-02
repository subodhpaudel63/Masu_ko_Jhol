<?php
session_start();
include_once "db.php";
require_once __DIR__ . '/auth_check.php';

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

function respond_menu_order(array $payload, bool $isAjax): void {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode($payload);
    } else {
        $_SESSION['msg'] = [
            'type' => $payload['success'] ? 'success' : 'error',
            'text' => $payload['message'] ?? ($payload['success'] ? 'Order placed successfully!' : 'Order failed. Please try again.'),
        ];
    }
    exit;
}

// Check if user is logged in
$user = getUserFromCookie();

// If user is not logged in, redirect to login
if (!$user) {
    respond_menu_order([
        'success' => false,
        'message' => 'Please login to place an order.',
        'login_required' => true,
        'redirect' => '/Masu%20Ko%20Jhol%28full%29/login.php?action=order_food',
    ], $isAjax);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $menu_id = isset($_POST['menu_id']) ? intval($_POST['menu_id']) : 0;
    $menu_name = isset($_POST['menu_name']) ? trim($_POST['menu_name']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0.0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';
    $order_type = isset($_POST['order_type']) ? trim($_POST['order_type']) : 'Delivery';
    $table_number = isset($_POST['table_number']) ? trim($_POST['table_number']) : null;
    $special_instructions = isset($_POST['special_instructions']) ? trim($_POST['special_instructions']) : null;
    $payment_method = isset($_POST['payment_method']) ? trim($_POST['payment_method']) : 'Cash on Delivery';

    // Basic validation
    if ($menu_id <= 0 || empty($menu_name) || $quantity <= 0 || $price <= 0 || $total_price <= 0 || empty($full_name) || !preg_match('/^[0-9]{10}$/', $mobile) || empty($address)) {
        respond_menu_order([
            'success' => false,
            'message' => 'Invalid order data. Please fill in all required fields correctly.',
        ], $isAjax);
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO orders (menu_id, menu_name, price, quantity, total_price, email, full_name, mobile, address, order_type, table_number, special_instructions, payment_method, order_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdidssssssss", $menu_id, $menu_name, $price, $quantity, $total_price, $email, $full_name, $mobile, $address, $order_type, $table_number, $special_instructions, $payment_method);

    if ($stmt->execute()) {
        respond_menu_order([
            'success' => true,
            'message' => 'Order placed successfully!',
            'order_id' => $stmt->insert_id,
        ], $isAjax);
    } else {
        respond_menu_order([
            'success' => false,
            'message' => 'Order failed. Please try again.',
        ], $isAjax);
    }
} else {
    // Invalid access
    respond_menu_order([
        'success' => false,
        'message' => 'Invalid access.',
    ], $isAjax);
}
?>
