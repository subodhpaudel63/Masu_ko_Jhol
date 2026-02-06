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

// Group by category for easier display
$menu_by_category = [];
foreach ($menu_items as $item) {
    $category = $item['menu_category'];
    if (!isset($menu_by_category[$category])) {
        $menu_by_category[$category] = [];
    }
    $menu_by_category[$category][] = $item;
}

$categories = ['starter', 'breakfast', 'lunch', 'dinner'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menu Management - Masu Ko Jhol</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="../assets/css/adminstyle.css">
  <style>
    .menu-sections {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }
    
    .section-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .section-card:hover {
        transform: translateY(-5px);
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
    
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .menu-card {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .menu-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }
    
    .menu-image {
        height: 180px;
        background: #f5f5f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #999;
        font-size: 3rem;
    }
    
    .menu-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .menu-content {
        padding: 1.25rem;
    }
    
    .menu-title {
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0 0 0.5rem 0;
        color: #333;
    }
    
    .menu-description {
        color: #666;
        font-size: 0.9rem;
        margin: 0 0 1rem 0;
        line-height: 1.4;
    }
    
    .menu-price {
        font-size: 1.2rem;
        font-weight: 700;
        color: #ff6a00;
        margin: 0 0 1rem 0;
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
           </a>
           <a href="menu.php" class="active">
              <span class="material-symbols-sharp">receipt_long </span>
              <h3>Menu</h3>
           </a>
           <a href="bookings.php">
              <span class="material-symbols-sharp">calendar_month </span>
              <h3>Bookings</h3>
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
           <div class="message <?php echo $message_type; ?>">
               <?php echo htmlspecialchars($message); ?>
           </div>
           <?php endif; ?>

           <!-- Quick Links Section -->
           <div class="quick-links">
               <h3>Quick Links</h3>
               <ul>
                   <li><a href="../client/menu.php" target="_blank">View Frontend Menu Page</a> - See how customers view the menu</li>
                   <li><a href="../client/cart.php" target="_blank">View Customer Cart</a> - Test the shopping cart functionality</li>
                   <li><a href="analytics.php">View Menu Analytics</a> - See sales data and popular items</li>
               </ul>
           </div>

           <!-- Add New Item Button -->
           <div style="margin-bottom: 2rem;">
               <button class="btn-primary" onclick="openModal('create')">
                   <span class="material-symbols-sharp">add</span>
                   Add New Menu Item
               </button>
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
                               <img src="../<?php echo $item['menu_image']; ?>" alt="<?php echo htmlspecialchars($item['menu_name']); ?>" class="menu-image">
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
                  <button class="close" onclick="closeModal()">&times;</button>
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
                          <button type="button" class="btn-delete" style="flex: 1;" onclick="closeModal()">Cancel</button>
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
                      <img src="../assets/img/usersprofiles/fa29eed8-1427-4ec2-a671-e4e45a399f3c.jpg" alt="Admin Profile"/>
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
       
       function closeModal() {
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
       
       function deleteMenuItem(menuId, itemName) {
           if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
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
                       alert(data.message);
                       location.reload(); // Refresh to show updated data
                   } else {
                       alert('Error: ' + data.message);
                   }
               })
               .catch(error => {
                   console.error('Error:', error);
                   alert('Error deleting menu item');
               });
           }
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
                   alert(data.message);
                   closeModal();
                   location.reload(); // Refresh to show updated data
               } else {
                   alert('Error: ' + data.message);
               }
           })
           .catch(error => {
               console.error('Error:', error);
               alert('Error saving menu item');
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
               closeModal();
           }
       });
   </script>
   <script src="../assets/js/adminscript.js"></script>
</body>
</html>