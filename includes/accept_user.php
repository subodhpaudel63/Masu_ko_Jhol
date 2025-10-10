<?php
include_once "db.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $conn->query("UPDATE users SET status='active' WHERE id=$user_id");
    $_SESSION['msg'] = ['type' => 'success', 'text' => 'User accepted successfully.'];
}
header('Location: ../admin/users.php');
exit;
