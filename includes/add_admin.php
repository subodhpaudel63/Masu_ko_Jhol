<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Invalid email.'];
    header("Location: /Masu%20Ko%20Jhol%28full%29/admin/users.php");
        exit;
    }

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Email already exists. Please use a different email.'];
        $check_stmt->close();
        $conn->close();
        header("Location: /Masu%20Ko%20Jhol%28full%29/admin/users.php");
        exit;
    }
    $check_stmt->close();

    $stmt = $conn->prepare("INSERT INTO users (email, password, user_type) VALUES (?, ?, 'admin')");
    $stmt->bind_param("ss", $email, $password);
    
    if ($stmt->execute()) {
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Admin added successfully!'];
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Failed to add admin.'];
    }

    $stmt->close();
    $conn->close();

    header("Location: /Masu%20Ko%20Jhol%28full%29/admin/users.php");
    exit;
}
?>