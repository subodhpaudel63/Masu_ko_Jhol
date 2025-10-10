<?php
session_start();
include_once "db.php";

if (isset($_POST['id'], $_POST['imgpath'])) {
    $id = (int)$_POST['id'];
    $imgPath = $_POST['imgpath'];

    // Sanitize path to prevent directory traversal
    $fileName = basename($imgPath); // Just the filename
    $filePath = __DIR__ . '/../assets/img/gallery/' . $fileName;

    // Check and delete the image file
    if (file_exists($filePath)) {
        if (!unlink($filePath)) {
            $_SESSION['msg'] = [
                'type' => 'error',
                'text' => 'Failed to delete image file: ' . htmlspecialchars($fileName)
            ];
        }
    } else {
        $_SESSION['msg'] = [
            'type' => 'error',
            'text' => 'Image file not found: ' . htmlspecialchars($fileName)
        ];
    }

    // Delete the database record
    $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ?");
    $stmt->bind_param("i", $id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && !isset($_SESSION['msg'])) {
        $_SESSION['msg'] = [
            'type' => 'success',
            'text' => 'Image deleted successfully.'
        ];
    } elseif (!$success && !isset($_SESSION['msg'])) {
        $_SESSION['msg'] = [
            'type' => 'error',
            'text' => 'Failed to delete image record from database.'
        ];
    }
} else {
    $_SESSION['msg'] = [
        'type' => 'error',
        'text' => 'Invalid delete request.'
    ];
}

header("Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php");
exit();
