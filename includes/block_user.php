<?php
session_start();
include_once "db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);

    // Check if the 'status' column exists in the users table
    $colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
    if ($colCheck && $colCheck->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE users SET status='blocked' WHERE id=?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
        }
    }
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'User blocked successfully.'];
}
header('Location: ../admin/users.php');
exit;
