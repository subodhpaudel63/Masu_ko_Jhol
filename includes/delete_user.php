<?php
session_start();
include_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            $_SESSION['msg'] = [
                'type' => 'success',
                'text' => 'User deleted successfully.'
            ];
        } else {
            $_SESSION['msg'] = [
                'type' => 'error',
                'text' => 'Failed to delete user. Error: ' . $stmt->error
            ];
        }

        $stmt->close();
    } else {
        $_SESSION['msg'] = [
            'type' => 'error',
            'text' => 'Invalid user ID.'
        ];
    }
} else {
    $_SESSION['msg'] = [
        'type' => 'error',
        'text' => 'Invalid request method.'
    ];
}

header("Location: /Masu%20Ko%20Jhol%28full%29/admin/users.php"); // Adjust the path as needed
exit;
