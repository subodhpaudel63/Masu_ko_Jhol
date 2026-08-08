<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

// Handle form submissions
$message = '';
$message_type = '';

// Handle menu item creation/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'create':
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
                $message = 'Menu item created successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error creating menu item: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
            break;
            
        case 'update':
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
                $message = 'Menu item updated successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error updating menu item: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
            break;
            
        case 'delete':
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
                $message = 'Menu item deleted successfully!';
                $message_type = 'success';
            } else {
                $message = 'Error deleting menu item: ' . $stmt->error;
                $message_type = 'error';
            }
            $stmt->close();
            break;
    }
}

// Fetch all menu items
$menu_items = [];
$res = $conn->query("SELECT * FROM menu ORDER BY menu_category, menu_name");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $menu_items[] = $row;
    }
}

$search_query = trim($_GET['search'] ?? '');
$selected_category = trim($_GET['category'] ?? '');
$selected_sort = trim($_GET['sort'] ?? 'category_asc');

$available_categories = ['starter', 'breakfast', 'lunch', 'dinner'];

$filtered_menu_items = array_filter($menu_items, function ($item) use ($search_query, $selected_category) {
    $matches_search = true;
    $matches_category = true;

    if ($search_query !== '') {
        $haystack = strtolower(
            ($item['menu_name'] ?? '') . ' ' .
            ($item['menu_description'] ?? '') . ' ' .
            ($item['menu_category'] ?? '')
        );
        $matches_search = strpos($haystack, strtolower($search_query)) !== false;
    }

    if ($selected_category !== '') {
        $matches_category = strtolower((string)($item['menu_category'] ?? '')) === strtolower($selected_category);
    }

    return $matches_search && $matches_category;
});

usort($filtered_menu_items, function ($a, $b) use ($selected_sort) {
    switch ($selected_sort) {
        case 'name_asc':
            return strcmp($a['menu_name'] ?? '', $b['menu_name'] ?? '');
        case 'name_desc':
            return strcmp($b['menu_name'] ?? '', $a['menu_name'] ?? '');
        case 'price_asc':
            return (float)($a['menu_price'] ?? 0) <=> (float)($b['menu_price'] ?? 0);
        case 'price_desc':
            return (float)($b['menu_price'] ?? 0) <=> (float)($a['menu_price'] ?? 0);
        case 'category_desc':
            return strcmp($b['menu_category'] ?? '', $a['menu_category'] ?? '');
        case 'category_asc':
        default:
            $category_compare = strcmp($a['menu_category'] ?? '', $b['menu_category'] ?? '');
            if ($category_compare !== 0) {
                return $category_compare;
            }
            return strcmp($a['menu_name'] ?? '', $b['menu_name'] ?? '');
    }
});

// Group by category for easier display
$menu_by_category = [];
foreach ($filtered_menu_items as $item) {
    $category = $item['menu_category'];
    if (!isset($menu_by_category[$category])) {
        $menu_by_category[$category] = [];
    }
    $menu_by_category[$category][] = $item;
}

$categories = $available_categories;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Management - Mero Bhoj</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="../assets/css/adminstyle.css?v=<?= filemtime(__DIR__ . '/../assets/css/adminstyle.css') ?>">
  
  <style>
    main {
        padding-bottom: 3rem;
    }

    .page-hero {
        position: relative;
        overflow: hidden;
        background: linear-gradient(135deg, rgba(240, 90, 34, 0.12), rgba(255, 255, 255, 0.95));
        border: 1px solid rgba(240, 90, 34, 0.12);
        border-radius: 22px;
        padding: 1.5rem 1.6rem;
        margin-bottom: 1.4rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
    }

    .page-hero::after {
        content: '';
        position: absolute;
        inset: auto -60px -80px auto;
        width: 180px;
        height: 180px;
        background: radial-gradient(circle, rgba(240, 90, 34, 0.18), transparent 68%);
        pointer-events: none;
    }

    .page-hero h1 {
        margin: 0;
        font-size: 2rem;
        line-height: 1.1;
    }

    .page-hero p {
        margin: 0.55rem 0 0;
        max-width: 720px;
        color: #64748b;
        font-size: 0.98rem;
    }

    .hero-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
        margin-top: 1rem;
    }

    .btn-soft {
        background: rgba(255, 255, 255, 0.72);
        color: #374151;
        border: 1px solid rgba(148, 163, 184, 0.22);
        padding: 0.75rem 1.1rem;
        border-radius: 999px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05);
    }

    .btn-soft:hover {
        transform: translateY(-2px);
        border-color: rgba(240, 90, 34, 0.2);
        color: #f05a22;
    }

    .stats-row {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.4rem;
    }

    .stat-card {
        background: rgba(255, 255, 255, 0.96);
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 18px;
        padding: 1rem 1.1rem;
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.06);
    }

    .stat-card .label {
        display: block;
        color: #64748b;
        font-weight: 600;
        font-size: 0.84rem;
        margin-bottom: 0.55rem;
    }

    .stat-card strong {
        display: block;
        color: #0f172a;
        font-size: 1.55rem;
        line-height: 1;
    }

    .stat-card small {
        display: inline-block;
        margin-top: 0.45rem;
        color: #16a34a;
        font-weight: 600;
    }

    .menu-panel {
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 22px;
        padding: 1.25rem;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.06);
        backdrop-filter: blur(8px);
    }

    .section-card {
        background: linear-gradient(180deg, #ffffff 0%, #fffaf7 100%);
        border: 1px solid rgba(240, 90, 34, 0.08);
        border-radius: 22px;
        padding: 1.2rem;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.06);
        transition: transform 0.28s ease, box-shadow 0.28s ease;
    }

    .section-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 24px 48px rgba(15, 23, 42, 0.1);
    }

    .menu-card {
        border: 1px solid rgba(148, 163, 184, 0.12);
        background: linear-gradient(180deg, #fff 0%, #fffdfb 100%);
        display: grid;
        grid-template-columns: 130px 1fr;
        min-height: 170px;
        overflow: hidden;
        border-radius: 18px;
        transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    }

    .menu-content {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 1rem 1rem 1rem 0;
    }

    .menu-title {
        padding-right: 0;
    }

    .menu-price {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        background: #fff3eb;
        padding: 0.35rem 0.7rem;
        border-radius: 999px;
        font-size: 1.05rem;
    }

    .btn-edit, .btn-delete, .btn-primary, .btn-secondary, .add-item-btn {
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
    }

    .menu-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .item-count {
        background: #ff6a00;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .add-item-btn {
        background: #4CAF50;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s ease;
        margin-bottom: 1rem;
    }
    
    .add-item-btn:hover {
        background: #45a049;
    }
    
    .menu-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 34px rgba(15,23,42,0.12);
        border-color: rgba(240, 90, 34, 0.18);
    }
    
    .menu-image {
        height: 100%;
        min-height: 170px;
        background: linear-gradient(135deg, #fef2e9, #fff7f1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #f05a22;
        font-size: 3rem;
    }
    
    .menu-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1rem;
    }

    .menu-description {
        color: #64748b;
        font-size: 0.92rem;
        margin: 0 0 1rem 0;
        line-height: 1.55;
    }
    
    .menu-title {
        font-size: 1.15rem;
        font-weight: 700;
        margin: 0 0 0.45rem 0;
        color: #1f2937;
    }
    
    .menu-price {
        font-size: 1.05rem;
        font-weight: 700;
        color: #ff6a00;
        margin: 0 0 1rem 0;
        width: fit-content;
    }
    
    .menu-actions {
        display: flex;
        gap: 0.5rem;
    }
    
    .btn-edit, .btn-delete {
        flex: 1;
        padding: 0.5rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 500;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .menu-card .menu-actions {
        margin-top: auto;
    }
    
    .btn-edit {
        background: #2196F3;
        color: white;
    }
    
    .btn-edit:hover {
        background: #1976D2;
    }
    
    .btn-delete {
        background: #f44336;
        color: white;
    }
    
    .btn-delete:hover {
        background: #d32f2f;
    }
    
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    
    .modal.show {
        display: flex;
    }
    
    .modal-content {
        background: white;
        border-radius: 15px;
        width: 90%;
        max-width: 500px;
        max-height: 90vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .modal-title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .close {
        background: none;
        border: none;
        font-size: 1.5rem;
        cursor: pointer;
        color: #999;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        font-size: 1rem;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .btn-primary {
        background: #ff6a00;
        color: white;
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: background 0.3s ease;
    }
    
    .btn-primary:hover {
        background: #e65f00;
    }
    
    .btn-delete {
        background: #f44336;
        color: white;
    }
    
    .btn-delete:hover {
        background: #d32f2f;
    }
    
    .message {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .quick-links {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .quick-links h3 {
        margin-top: 0;
        color: #333;
    }
    
    .quick-links ul {
        list-style: none;
        padding: 0;
    }
    
    .quick-links li {
        margin: 0.5rem 0;
    }
    
    .quick-links a {
        color: #ff6a00;
        text-decoration: none;
        font-weight: 500;
    }
    
    .quick-links a:hover {
        text-decoration: underline;
    }

    .menu-filters {
        background: linear-gradient(180deg, rgba(255,255,255,0.98), rgba(255,250,247,0.98));
        border: 1px solid rgba(148, 163, 184, 0.12);
        border-radius: 20px;
        padding: 1rem;
        margin: 1rem 0 2rem;
        box-shadow: 0 16px 34px rgba(15,23,42,0.06);
    }

    .menu-filters form {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1fr auto;
        gap: 0.75rem;
        align-items: end;
    }

    .menu-filters .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.4rem;
    }

    .menu-filters label {
        font-weight: 600;
        color: #374151;
        font-size: 0.9rem;
    }

    .menu-filters input,
    .menu-filters select {
        width: 100%;
        padding: 0.8rem 0.9rem;
        border: 1px solid #d1d5db;
        border-radius: 12px;
        font-size: 0.95rem;
        background: #fff;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .menu-filters input:focus,
    .menu-filters select:focus {
        outline: none;
        border-color: #f05a22;
        box-shadow: 0 0 0 4px rgba(240, 90, 34, 0.12);
    }

    .filter-actions {
        display: flex;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .btn-secondary {
        background: #e5e7eb;
        color: #374151;
        border: none;
        padding: 0.75rem 1.1rem;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }

    .btn-secondary:hover {
        background: #d1d5db;
    }

    .menu-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .filter-summary {
        margin-top: 0.75rem;
        color: #6b7280;
        font-size: 0.9rem;
    }

    @media screen and (max-width: 900px) {
        .menu-filters form {
            grid-template-columns: 1fr;
        }

        .stats-row {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .menu-card {
            grid-template-columns: 1fr;
        }

        .menu-image {
            min-height: 220px;
        }

        .menu-content {
            padding: 1rem 1rem 1.1rem;
        }
    }

    @media screen and (max-width: 640px) {
        .stats-row {
            grid-template-columns: 1fr;
        }

        .menu-grid {
            grid-template-columns: 1fr;
        }
    }
  </style>
</head>
<body>

   <div class="container">
      <aside>
         <div class="top">
           <div class="logo">
             <h2>Masu <span class="danger"> ko jhol</span> </h2>
           </div>
           <div class="close" id="close_btn">
            <span class="material-symbols-sharp">close</span>
           </div>
         </div>
         <div class="sidebar">
            <a href="./index.php">
              <span class="material-symbols-sharp">grid_view </span>
              <h3>Dashbord</h3>
           </a>
           <a href="users.php">
              <span class="material-symbols-sharp">person_outline </span>
              <h3>costumers</h3>
           </a>
           <a href="analytics.php">
              <span class="material-symbols-sharp">insights </span>
              <h3>Analytics</h3>
           </a>
           <a href="orders_page.php">
              <span class="material-symbols-sharp">mail_outline </span>
              <h3>Orders</h3>
              <span class="msg_count">14</span>
           </a>
           <a href="menu.php" class="active">
              <span class="material-symbols-sharp">receipt_long </span>
              <h3>Menu</h3>
           </a>
           <a href="bookings.php">
              <span class="material-symbols-sharp">calendar_month </span>
              <h3>Bookings</h3>
              <span class="msg_count">1</span>
           </a>

           <a href="feedback.php">
              <span class="material-symbols-sharp">Feedback </span>
              <h3>Feedback</h3>
           </a>
           
           <a href="#">
              <span class="material-symbols-sharp">settings </span>
              <h3>settings</h3>
           </a>
           <a href="#">
              <span class="material-symbols-sharp">add </span>
              <h3>Add Product</h3>
           </a>
           <a href="../includes/logout.php">
              <span class="material-symbols-sharp">logout </span>
              <h3>logout</h3>
           </a>
         </div>
      </aside>

      <main>
           <h1>Menu Management</h1>

           <?php if ($message): ?>
           <script>
               document.addEventListener('DOMContentLoaded', function() {
                   <?php if ($message_type === 'success'): ?>
                   
                   <?php else: ?>
                   
                   <?php endif; ?>
               });
           </script>
           <?php endif; ?>

           

           <div class="menu-filters">
               <form method="GET" action="menu.php">
                   <div class="filter-group">
                       <label for="search">Search</label>
                       <input type="text" id="search" name="search" placeholder="Search by name, description, or category" value="<?php echo htmlspecialchars($search_query); ?>">
                   </div>
                   <div class="filter-group">
                       <label for="category">Category</label>
                       <select id="category" name="category">
                           <option value="">All categories</option>
                           <?php foreach ($available_categories as $category): ?>
                               <option value="<?php echo $category; ?>" <?php echo $selected_category === $category ? 'selected' : ''; ?>>
                                   <?php echo ucfirst($category); ?>
                               </option>
                           <?php endforeach; ?>
                       </select>
                   </div>
                   <div class="filter-group">
                       <label for="sort">Sort by</label>
                       <select id="sort" name="sort">
                           <option value="category_asc" <?php echo $selected_sort === 'category_asc' ? 'selected' : ''; ?>>Category A-Z</option>
                           <option value="category_desc" <?php echo $selected_sort === 'category_desc' ? 'selected' : ''; ?>>Category Z-A</option>
                           <option value="name_asc" <?php echo $selected_sort === 'name_asc' ? 'selected' : ''; ?>>Name A-Z</option>
                           <option value="name_desc" <?php echo $selected_sort === 'name_desc' ? 'selected' : ''; ?>>Name Z-A</option>
                           <option value="price_asc" <?php echo $selected_sort === 'price_asc' ? 'selected' : ''; ?>>Price Low to High</option>
                           <option value="price_desc" <?php echo $selected_sort === 'price_desc' ? 'selected' : ''; ?>>Price High to Low</option>
                       </select>
                   </div>
                   <div class="filter-actions">
                       <button type="submit" class="btn-primary">Apply</button>
                       <a href="menu.php" class="btn-secondary" style="display:inline-flex; align-items:center; justify-content:center; text-decoration:none;">Reset</a>
                   </div>
               </form>
               <div class="filter-summary">
                   Showing <?php echo count($filtered_menu_items); ?> item<?php echo count($filtered_menu_items) === 1 ? '' : 's'; ?>
                   <?php if ($search_query !== '' || $selected_category !== '' || $selected_sort !== 'category_asc'): ?>
                       with active filters
                   <?php endif; ?>
               </div>
           </div>

           <!-- Menu Sections -->
           <div class="menu-sections">
               <?php foreach ($categories as $category): ?>
               <div class="section-card">
                   <div class="section-header">
                       <h3 class="section-title"><?php echo ucfirst($category); ?></h3>
                       <span class="item-count"><?php echo count($menu_by_category[$category] ?? []); ?> items</span>
                   </div>
                   <button class="add-item-btn" onclick="openModal('create', null, '<?php echo $category; ?>')">
                       <span class="material-symbols-sharp">add</span>
                       Add <?php echo ucfirst($category); ?> Item
                   </button>
                   
                   <?php if (!empty($menu_by_category[$category])): ?>
                   <div class="menu-grid" style="margin-top: 1rem;">
                       <?php foreach ($menu_by_category[$category] as $item): ?>
                       <div class="menu-card">
                           <?php if (!empty($item['menu_image']) && file_exists('../' . $item['menu_image'])): ?>
                               <div class="menu-image">
                                   <img src="../<?php echo $item['menu_image']; ?>" alt="<?php echo htmlspecialchars($item['menu_name']); ?>">
                               </div>
                           <?php else: ?>
                               <div class="menu-image">
                                   <span class="material-symbols-sharp">fastfood</span>
                               </div>
                           <?php endif; ?>
                           <div class="menu-content">
                               <h3 class="menu-title"><?php echo htmlspecialchars($item['menu_name']); ?></h3>
                               <p class="menu-description"><?php echo htmlspecialchars(substr($item['menu_description'], 0, 100)) . (strlen($item['menu_description']) > 100 ? '...' : ''); ?></p>
                               <div class="menu-price">Rs. <?php echo number_format((float)$item['menu_price'], 2); ?></div>
                               <div class="menu-actions">
                                   <button class="btn-edit" onclick="openModal('edit', <?php echo $item['menu_id']; ?>)">
                                       <span class="material-symbols-sharp">edit</span>
                                       Edit
                                   </button>
                                   <button class="btn-delete" onclick="deleteMenuItem(<?php echo $item['menu_id']; ?>, '<?php echo addslashes($item['menu_name']); ?>')">
                                       <span class="material-symbols-sharp">delete</span>
                                       Delete
                                   </button>
                               </div>
                           </div>
                       </div>
                       <?php endforeach; ?>
                   </div>
                   <?php else: ?>
                   <div style="text-align: center; padding: 2rem; color: #666;">
                       <span class="material-symbols-sharp" style="font-size: 3rem; display: block; margin-bottom: 1rem;">restaurant</span>
                       <p>No items in this category yet</p>
                       <button class="add-item-btn" onclick="openModal('create', null, '<?php echo $category; ?>')">
                           Add First <?php echo ucfirst($category); ?> Item
                       </button>
                   </div>
                   <?php endif; ?>
               </div>
               <?php endforeach; ?>
           </div>
      </main>

      <!-- Modal for Add/Edit Menu Item -->
      <div class="modal" id="menuModal">
          <div class="modal-content">
              <div class="modal-header">
                  <h2 class="modal-title" id="modalTitle">Add New Menu Item</h2>
                  <button type="button" class="close" aria-label="Close modal" onclick="closeMenuModal()">&times;</button>
              </div>
              <div class="modal-body">
                  <form id="menuForm" enctype="multipart/form-data">
                      <input type="hidden" id="formAction" name="action" value="create">
                      <input type="hidden" id="menuId" name="menu_id" value="">
                      
                      <div class="form-group">
                          <label for="menu_name">Item Name *</label>
                          <input type="text" id="menu_name" name="menu_name" required>
                      </div>
                      
                      <div class="form-group">
                          <label for="menu_description">Description *</label>
                          <textarea id="menu_description" name="menu_description" required></textarea>
                      </div>
                      
                      <div class="form-group">
                          <label for="menu_price">Price (Rs) *</label>
                          <input type="number" id="menu_price" name="menu_price" step="0.01" min="0" required>
                      </div>
                      
                      <div class="form-group">
                          <label for="menu_category">Category *</label>
                          <select id="menu_category" name="menu_category" required>
                              <option value="">Select Category</option>
                              <option value="starter">Starter</option>
                              <option value="breakfast">Breakfast</option>
                              <option value="lunch">Lunch</option>
                              <option value="dinner">Dinner</option>
                          </select>
                      </div>
                      
                      <div class="form-group">
                          <label for="menu_image">Image (Optional)</label>
                          <input type="file" id="menu_image" name="menu_image" accept="image/*">
                          <input type="hidden" id="existing_image" name="existing_image">
                          <div id="imagePreview" style="margin-top: 10px; display: none;">
                              <img src="" alt="Preview" style="max-width: 200px; max-height: 150px;">
                          </div>
                      </div>
                      
                      <div class="form-group" style="display: flex; gap: 1rem; margin-top: 2rem;">
                          <button type="submit" class="btn-primary" style="flex: 1;">Save Item</button>
                          <button type="button" class="btn-delete" style="flex: 1;" onclick="closeMenuModal()">Cancel</button>
                      </div>
                  </form>
              </div>
          </div>
      </div>

      <div class="right">
          <div class="top">
              <button id="menu_bar">
                  <span class="material-symbols-sharp">menu</span>
              </button>
              <div class="theme-toggler">
                  <span class="material-symbols-sharp active">light_mode</span>
                  <span class="material-symbols-sharp">dark_mode</span>
              </div>
              <div class="profile">
                  <div class="info">
                      <p><b>Subodh Admin</b></p>
                      <p>Administrator</p>
                      <small class="text-muted">Online</small>
                  </div>
                  <div class="profile-photo">
                      <img src="../assets/img/usersprofiles/adminpic.jpg" alt="Admin Profile"/>
                  </div>
              </div>
          </div>
      </div>
   </div>

   <script>
       // Modal functionality
       function openModal(action, menuId = null, category = null) {
           const modal = document.getElementById('menuModal');
           const form = document.getElementById('menuForm');
           const modalTitle = document.getElementById('modalTitle');
           const formAction = document.getElementById('formAction');
           const menuIdInput = document.getElementById('menuId');
           
           // Reset form
           form.reset();
           document.getElementById('imagePreview').style.display = 'none';
           document.getElementById('existing_image').value = '';
           
           if (action === 'create') {
               modalTitle.textContent = 'Add New Menu Item';
               formAction.value = 'create';
               menuIdInput.value = '';
               if (category) {
                   document.getElementById('menu_category').value = category;
               }
           } else if (action === 'edit' && menuId) {
               modalTitle.textContent = 'Edit Menu Item';
               formAction.value = 'update';
               menuIdInput.value = menuId;
               
               // Load existing data
               loadMenuItemData(menuId);
           }
           
           modal.classList.add('show');
       }
       
       function closeMenuModal() {
           document.getElementById('menuModal').classList.remove('show');
       }
       
       function loadMenuItemData(menuId) {
           // Show loading state
           const nameField = document.getElementById('menu_name');
           const descField = document.getElementById('menu_description');
           const priceField = document.getElementById('menu_price');
           const categoryField = document.getElementById('menu_category');
           
           nameField.value = 'Loading...';
           descField.value = 'Loading...';
           priceField.value = '';
           categoryField.value = '';
           
           fetch('menu_ajax.php', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/x-www-form-urlencoded',
               },
               body: 'action=get_item&menu_id=' + menuId
           })
           .then(response => {
               if (!response.ok) {
                   throw new Error('Network response was not ok');
               }
               return response.json();
           })
           .then(data => {
               if (data.success) {
                   const item = data.data;
                   document.getElementById('menu_name').value = item.menu_name || '';
                   document.getElementById('menu_description').value = item.menu_description || '';
                   document.getElementById('menu_price').value = item.menu_price || '';
                   document.getElementById('menu_category').value = item.menu_category || '';
                   document.getElementById('existing_image').value = item.menu_image || '';
                   
                   // Show existing image preview
                   if (item.menu_image) {
                       const preview = document.getElementById('imagePreview');
                       const img = preview.querySelector('img');
                       img.src = '../' + item.menu_image;
                       preview.style.display = 'block';
                   }
               } else {
                   alert('Error loading menu item: ' + data.message);
                   // Reset fields on error
                   document.getElementById('menu_name').value = '';
                   document.getElementById('menu_description').value = '';
                   document.getElementById('menu_price').value = '';
                   document.getElementById('menu_category').value = '';
               }
           })
           .catch(error => {
               console.error('Error:', error);
               alert('Error loading menu item: ' + error.message);
               // Reset fields on error
               document.getElementById('menu_name').value = '';
               document.getElementById('menu_description').value = '';
               document.getElementById('menu_price').value = '';
               document.getElementById('menu_category').value = '';
           });
       }
       
       // Create confirmation modal for delete operations
       function showDeleteConfirmation(itemName, onDeleteCallback) {
           // Remove any existing modal
           const existingModal = document.getElementById('deleteConfirmModal');
           if (existingModal) existingModal.remove();
           
           // Create modal HTML
           const modalHtml = `
               <div id="deleteConfirmModal" class="delete-confirm-overlay" style="
                   position: fixed;
                   top: 0;
                   left: 0;
                   width: 100%;
                   height: 100%;
                   background: rgba(0, 0, 0, 0.6);
                   backdrop-filter: blur(5px);
                   z-index: 5000;
                   display: flex;
                   justify-content: center;
                   align-items: center;
                   animation: fadeIn 0.3s ease;
               ">
                   <div class="delete-confirm-modal" style="
                       background: var(--clr-white);
                       padding: 2rem;
                       border-radius: var(--border-radius-2);
                       width: 90%;
                       max-width: 450px;
                       box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                       position: relative;
                       animation: modalSlideIn 0.3s ease;
                   ">
                       <h3 style="
                           color: var(--clr-danger);
                           margin-top: 0;
                           margin-bottom: 1rem;
                           font-size: 1.3rem;
                           display: flex;
                           align-items: center;
                           gap: 0.5rem;
                       "><span class="material-symbols-sharp">warning</span> Confirm Deletion</h3>
                       <p style="margin: 1rem 0; color: var(--clr-dark);">Are you sure you want to delete <strong>${itemName}</strong>? This action cannot be undone.</p>
                       <div style="display: flex; gap: 1rem; justify-content: flex-end; margin-top: 1.5rem;">
                           <button id="cancelDeleteBtn" class="btn-warning" style="
                               padding: 0.6rem 1.2rem;
                               border-radius: var(--border-radius-1);
                               border: none;
                               cursor: pointer;
                               font-weight: 500;
                               transition: all 0.2s ease;
                           ">Cancel</button>
                           <button id="confirmDeleteBtn" class="btn-danger" style="
                               padding: 0.6rem 1.2rem;
                               border-radius: var(--border-radius-1);
                               border: none;
                               cursor: pointer;
                               font-weight: 500;
                               transition: all 0.2s ease;
                           ">Delete</button>
                       </div>
                   </div>
               </div>
           `;
           
           document.body.insertAdjacentHTML('beforeend', modalHtml);
           
           // Add event listeners
           document.getElementById('cancelDeleteBtn').addEventListener('click', function() {
               document.getElementById('deleteConfirmModal').remove();
           });
           
           document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
               document.getElementById('deleteConfirmModal').remove();
               onDeleteCallback();
           });
       }

       function deleteMenuItem(menuId, itemName) {
           showDeleteConfirmation(itemName, function() {
               fetch('menu_ajax.php', {
                   method: 'POST',
                   headers: {
                       'Content-Type': 'application/x-www-form-urlencoded',
                   },
                   body: 'action=delete&menu_id=' + menuId
               })
               .then(response => response.json())
               .then(data => {
                   if (data.success) {
                       setTimeout(() => {
                           location.reload(); // Refresh to show updated data
                       }, 1500);
                   }
               })
               .catch(error => {
                   console.error('Error:', error);
                   
               });
           });
       }
       
       // Image preview functionality
       document.getElementById('menu_image').addEventListener('change', function(e) {
           const file = e.target.files[0];
           if (file) {
               const reader = new FileReader();
               reader.onload = function(e) {
                   const preview = document.getElementById('imagePreview');
                   const img = preview.querySelector('img');
                   img.src = e.target.result;
                   preview.style.display = 'block';
               };
               reader.readAsDataURL(file);
           }
       });
       
       // Handle form submission
       document.getElementById('menuForm').addEventListener('submit', function(e) {
           e.preventDefault();
           
           const formData = new FormData(this);
           const submitBtn = this.querySelector('button[type="submit"]');
           const originalText = submitBtn.textContent;
           
           // Show loading state
           submitBtn.textContent = 'Saving...';
           submitBtn.disabled = true;
           
           fetch('menu_ajax.php', {
               method: 'POST',
               body: formData
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   setTimeout(() => {
                       closeMenuModal();
                       location.reload(); // Refresh to show updated data
                   }, 1500);
               }
           })
           .catch(error => {
               console.error('Error:', error);
               
           })
           .finally(() => {
               // Reset button
               submitBtn.textContent = originalText;
               submitBtn.disabled = false;
           });
       });
       
       // Close modal when clicking outside
       document.getElementById('menuModal').addEventListener('click', function(e) {
           if (e.target === this) {
               closeMenuModal();
           }
       });
  </script>
   
   <script src="../assets/js/adminscript.js"></script>
</body>
</html>
