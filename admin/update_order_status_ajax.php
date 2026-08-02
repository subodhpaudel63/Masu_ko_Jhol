<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../includes/db.php';

$response = ['success' => false, 'message' => 'Invalid request'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);

    if (!empty($raw) && json_last_error() === JSON_ERROR_NONE) {
        $order_id = intval($input['order_id'] ?? 0);
        $status = $input['status'] ?? '';
        $admin_note = isset($input['admin_note']) ? (string)$input['admin_note'] : null;
    } else {
        $order_id = intval($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $admin_note = isset($_POST['admin_note']) ? (string)$_POST['admin_note'] : null;
    }

    $allowed_status = ['Confirmed', 'Shipping', 'Ongoing', 'Delivering', 'Cancelled'];

    if ($order_id > 0 && in_array($status, $allowed_status)) {

        if ($admin_note !== null) {
            $stmt = $conn->prepare("UPDATE orders SET status = ?, admin_note = ? WHERE order_id = ?");
            if ($stmt) {
                $stmt->bind_param("ssi", $status, $admin_note, $order_id);
            }
        } else {
            $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
            if ($stmt) {
                $stmt->bind_param("si", $status, $order_id);
            }
        }

        if ($stmt) {

            if ($stmt->execute()) {

                $response = [
                    'success' => true,
                    'message' => 'Order updated successfully'
                ];

            } else {

                $response = [
                    'success' => false,
                    'message' => 'Database execute failed'
                ];

            }

            $stmt->close();

        } else {

            $response = [
                'success' => false,
                'message' => 'Database prepare failed'
            ];

        }

    } else {

        $response = [
            'success' => false,
            'message' => 'Invalid order ID or status'
        ];

    }

}

echo json_encode($response);
exit;
