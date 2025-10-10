<?php
session_start();
require_once 'db.php'; // Your DB connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['menu_id']) && is_numeric($_POST['menu_id'])) {
        $menu_id = (int)$_POST['menu_id'];

        // Get the image filename from menu table
        $stmt = $conn->prepare("SELECT menu_image FROM menu WHERE menu_id = ?");
        $stmt->bind_param("i", $menu_id);
        $stmt->execute();
        $stmt->bind_result($imageFileName);
        if ($stmt->fetch()) {
            $stmt->close();

            // Debug logs
            error_log("Image filename to delete: " . $imageFileName);
            error_log("Deleting menu ID: " . $menu_id);

            // Delete the menu item
            $deleteStmt = $conn->prepare("DELETE FROM menu WHERE menu_id = ?");
            $deleteStmt->bind_param("i", $menu_id);

            if ($deleteStmt->execute()) {
                $deleteStmt->close();

                // Delete the image file
                $imagePath = __DIR__ . "/../assets/img/menu/" . $imageFileName;
                if (file_exists($imagePath)) {
                    if (!unlink($imagePath)) {
                        $_SESSION['msg'] = [
                            'type' => 'error',
                            'text' => 'Failed to delete image file: ' . htmlspecialchars($imageFileName)
                        ];
                        header("Location: /Masu%20Ko%20Jhol%28full%29/admin/menu.php");
                        exit;
                    }
                }

                $_SESSION['msg'] = [
                    'type' => 'success',
                    'text' => 'Menu item deleted successfully.'
                ];
                header("Location: /Masu%20Ko%20Jhol%28full%29/admin/menu.php");
                exit;
            } else {
                error_log("Delete Error: " . $deleteStmt->error);
                $_SESSION['msg'] = [
                    'type' => 'error',
                    'text' => 'Failed to delete menu item from database.'
                ];
                header("Location: /Masu%20Ko%20Jhol%28full%29/admin/menu.php");
                exit;
            }
        } else {
            $_SESSION['msg'] = [
                'type' => 'error',
                'text' => 'Menu item not found.'
            ];
            header("Location: /Masu%20Ko%20Jhol%28full%29/admin/menu.php");
            exit;
        }
    } else {
        $_SESSION['msg'] = [
            'type' => 'error',
            'text' => 'Invalid menu ID.'
        ];
        header("Location: /Masu%20Ko%20Jhol%28full%29/admin/menu.php");
        exit;
    }
} else {
    header("HTTP/1.1 405 Method Not Allowed");
    echo "Method Not Allowed";
    exit;
}
