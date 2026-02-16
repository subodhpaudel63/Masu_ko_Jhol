<?php
session_start();
include_once "db.php";
require_once __DIR__ . '/auth_check.php';
// Use the authenticated user's email instead of POST email
$email = $_SESSION['user_email']; // Assuming the user's email is stored in the session

// Check if user is logged in
$user = getUserFromCookie();

// If user is not logged in, redirect to login
if (!$user) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Please login to place an order.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php?action=order_food');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate input
    $menu_id = isset($_POST['menu_id']) ? intval($_POST['menu_id']) : 0;
    $menu_name = isset($_POST['menu_name']) ? trim($_POST['menu_name']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    $price = isset($_POST['price']) ? floatval($_POST['price']) : 0.0;
    $total_price = isset($_POST['total_price']) ? floatval($_POST['total_price']) : 0.0;
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    // Basic validation
    if ($menu_id <= 0 || empty($menu_name) || $quantity <= 0 || $price <= 0 || $total_price <= 0 || empty($email) || !preg_match('/^[0-9]{10}$/', $mobile) || empty($address)) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid order data. Please fill in all fields correctly.'];
        header('Location: ../client/index.php');
        exit;
    }

    // Insert into DB
    $stmt = $conn->prepare("INSERT INTO orders (menu_id, menu_name, price, quantity, total_price, email, mobile, address, order_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("isdidsss", $menu_id, $menu_name, $price, $quantity, $total_price, $email, $mobile, $address);

    if ($stmt->execute()) {
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Order placed successfully!'];
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Order failed. Please try again.'];
    }

    $stmt->close();
    $conn->close();

    // Redirect to client index page to display the message
    header('Location: ../client/index.php');
    exit;
} else {
    // Invalid access
    header('Location: ../client/index.php');
    exit;
}
// Redirect to myorder.php instead of index.php
header('Location: myorder.php');
exit();
?>