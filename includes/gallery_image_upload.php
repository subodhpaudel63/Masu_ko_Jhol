<?php
session_start();
include_once "db.php";

if (isset($_POST['upload'])) {
    $targetDir = "../assets/img/gallery/";
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $file = $_FILES['image']['tmp_name'];
    $targetFile = $targetDir . basename($_FILES['image']['name']);

    if (
        move_uploaded_file($file, $targetFile) &&
        $conn->query("INSERT INTO gallery (file_path) VALUES ('$targetFile')")
    ) {
        $_SESSION['msg'] = ['type' => 'success', 'text' => 'Image uploaded successfully!'];
        header("Location: /Masu Ko Jhol(full)/admin/index");
        exit();
    } else {
        $_SESSION['msg'] = ['type' => 'error', 'text' => 'Failed to upload image.'];
        header("Location: /college/admin/index");
        exit();
    }
}
