<?php
session_start();
include_once "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0);
    $role = $_POST['role'] ?? '';

    // Validate role
    if (!in_array($role, ['user', 'admin'])) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid role.'];
        header("Location: /Masu%20Ko%20Jhol%28full%29/admin/users.php");
        exit;
    }

    if ($user_id > 0) {
        $stmt = $conn->prepare("UPDATE users SET user_type = ? WHERE id = ?");
        $stmt->bind_param("si", $role, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION['msg'] = ['type' => 'success', 'text' => 'User role updated successfully.'];
        } else {
            $_SESSION['msg'] = ['type' => 'error', 'text' => 'Failed to update user role.'];
        }
        $stmt->close();
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid user ID.'];
    }
} else {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid request method.'];
}

header("Location: /Masu%20Ko%20Jhol%28full%29/admin/users.php");
exit;
?>