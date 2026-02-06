<?php
header('Content-Type: application/json');
require_once '../includes/admin_auth.php';
require_admin();
require_once '../includes/db.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'create':
            try {
                // Handle image upload
                $image_path = '';
                if (isset($_FILES['menu_image']) && $_FILES['menu_image']['error'] === 0) {
                    $upload_dir = '../assets/img/menu/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $file_extension = pathinfo($_FILES['menu_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'menu_' . uniqid() . '.' . $file_extension;
                    $image_path = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['menu_image']['tmp_name'], $image_path)) {
                        $image_path = 'assets/img/menu/' . $filename;
                    } else {
                        throw new Exception('Failed to upload image');
                    }
                }
                
                $stmt = $conn->prepare("INSERT INTO menu (menu_name, menu_description, menu_price, menu_category, menu_image) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("ssdss", 
                    $_POST['menu_name'],
                    $_POST['menu_description'],
                    $_POST['menu_price'],
                    $_POST['menu_category'],
                    $image_path
                );
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Menu item created successfully!';
                    $response['menu_id'] = $conn->insert_id;
                } else {
                    $response['message'] = 'Error creating menu item: ' . $stmt->error;
                }
                $stmt->close();
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
            break;
            
        case 'update':
            try {
                // Handle image update
                $image_path = $_POST['existing_image'] ?? '';
                if (isset($_FILES['menu_image']) && $_FILES['menu_image']['error'] === 0) {
                    $upload_dir = '../assets/img/menu/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    $file_extension = pathinfo($_FILES['menu_image']['name'], PATHINFO_EXTENSION);
                    $filename = 'menu_' . uniqid() . '.' . $file_extension;
                    $image_path = $upload_dir . $filename;
                    if (move_uploaded_file($_FILES['menu_image']['tmp_name'], $image_path)) {
                        $image_path = 'assets/img/menu/' . $filename;
                    } else {
                        throw new Exception('Failed to upload image');
                    }
                }
                
                $stmt = $conn->prepare("UPDATE menu SET menu_name = ?, menu_description = ?, menu_price = ?, menu_category = ?, menu_image = ? WHERE menu_id = ?");
                $stmt->bind_param("ssdssi",
                    $_POST['menu_name'],
                    $_POST['menu_description'],
                    $_POST['menu_price'],
                    $_POST['menu_category'],
                    $image_path,
                    $_POST['menu_id']
                );
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Menu item updated successfully!';
                } else {
                    $response['message'] = 'Error updating menu item: ' . $stmt->error;
                }
                $stmt->close();
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
            break;
            
        case 'delete':
            try {
                // Get image path to delete file
                $stmt = $conn->prepare("SELECT menu_image FROM menu WHERE menu_id = ?");
                $stmt->bind_param("i", $_POST['menu_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $menu_item = $result->fetch_assoc();
                
                // Delete image file if exists
                if ($menu_item && !empty($menu_item['menu_image'])) {
                    $image_path = '../' . $menu_item['menu_image'];
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
                
                // Delete from database
                $stmt = $conn->prepare("DELETE FROM menu WHERE menu_id = ?");
                $stmt->bind_param("i", $_POST['menu_id']);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Menu item deleted successfully!';
                } else {
                    $response['message'] = 'Error deleting menu item: ' . $stmt->error;
                }
                $stmt->close();
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
            break;
            
        case 'get_item':
            try {
                $stmt = $conn->prepare("SELECT * FROM menu WHERE menu_id = ?");
                $stmt->bind_param("i", $_POST['menu_id']);
                $stmt->execute();
                $result = $stmt->get_result();
                $menu_item = $result->fetch_assoc();
                
                if ($menu_item) {
                    $response['success'] = true;
                    $response['data'] = $menu_item;
                } else {
                    $response['message'] = 'Menu item not found';
                }
                $stmt->close();
            } catch (Exception $e) {
                $response['message'] = 'Error: ' . $e->getMessage();
            }
            break;
            
        default:
            $response['message'] = 'Invalid action specified';
    }
} else {
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);