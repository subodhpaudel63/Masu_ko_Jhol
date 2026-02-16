<?php
header('Content-Type: application/json');
session_start();
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
        $stmt->bind_param("si", $status, $order_id);
        
        if ($stmt->execute()) {
            // Get updated order data
            $order_stmt = $conn->prepare("SELECT order_id, menu_name, price, quantity, total_price, status, order_time FROM orders WHERE order_id = ?");
            $order_stmt->bind_param("i", $order_id);
            $order_stmt->execute();
            $result = $order_stmt->get_result();
            $updated_order = $result->fetch_assoc();
            
            // Calculate new totals
            $total_orders_query = $conn->query("SELECT COUNT(*) as total FROM orders");
            $total_orders = $total_orders_query->fetch_assoc()['total'];
            
            $total_revenue_query = $conn->query("SELECT SUM(total_price) as revenue FROM orders");
            $total_revenue = $total_revenue_query->fetch_assoc()['revenue'] ?? 0;
            
            $response = [
                'success' => true,
                'message' => 'Order status updated successfully.',
                'order' => $updated_order,
                'stats' => [
                    'total_orders' => $total_orders,
                    'total_revenue' => (float)$total_revenue
                ]
            ];
        } else {
            $response = [
                'success' => false,
                'message' => 'Failed to update order status: ' . $stmt->error
            ];
        }
        $stmt->close();
    } else {
        $response = [
            'success' => false,
            'message' => 'Invalid order ID or status. Order ID: ' . $order_id . ', Status: ' . $status
        ];
    }
} else {
    $response = [
        'success' => false,
        'message' => 'Invalid request method.'
    ];
}

echo json_encode($response);
exit;