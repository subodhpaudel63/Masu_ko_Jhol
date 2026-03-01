<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$orders = [];
$res = $conn->query("SELECT order_id, menu_id, menu_name, email, mobile, address, quantity, price, total_price, status, order_time FROM orders ORDER BY order_id DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $orders[] = $row; } }

// Calculate stats
$total_revenue = 0;
$confirmed_orders = 0;
$shipping_orders = 0;
foreach($orders as $order) {
    $total_revenue += $order['total_price'];
    if($order['status'] === 'Confirmed') $confirmed_orders++;
    if($order['status'] === 'Shipping') $shipping_orders++;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - Masu Ko Jhol</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="../assets/css/adminstyle.css">
  <link rel="stylesheet" href="../assets/css/toast_styles.css">
  <style>
    /* Delete Confirmation Modal */
    .delete-confirm-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      display: flex;
      justify-content: center;
      align-items: center;
      z-index: 10001;
      animation: fadeIn 0.3s ease-in-out;
    }

    .delete-confirm-modal {
      background: white;
      padding: 30px;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
      animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .delete-confirm-modal .icon-wrapper {
      width: 60px;
      height: 60px;
      margin: 0 auto 15px;
      background: #fee2e2;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .delete-confirm-modal h3 {
      text-align: center;
      margin: 15px 0;
      color: #1f2937;
      font-size: 20px;
    }

    .delete-confirm-modal p {
      text-align: center;
      color: #6b7280;
      margin-bottom: 20px;
    }

    .delete-confirm-buttons {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .delete-confirm-buttons button {
      padding: 10px 24px;
      border: none;
      border-radius: 6px;
      font-weight: 500;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .btn-cancel {
      background: #e5e7eb;
      color: #374151;
    }

    .btn-cancel:hover {
      background: #d1d5db;
    }

    .btn-delete-confirm {
      background: #ef4444;
      color: white;
    }

    .btn-delete-confirm:hover {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
      }
      to {
        opacity: 1;
      }
    }

    @keyframes slideUp {
      from {
        transform: translateY(30px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    @keyframes scaleIn {
      from {
        transform: scale(0.5);
        opacity: 0;
      }
      to {
        transform: scale(1);
        opacity: 1;
      }
    }

    @keyframes fadeOut {
      from {
        opacity: 1;
      }
      to {
        opacity: 0;
      }
    }
  </style>
</head>
<body>
   <div id="toastContainer" class="toast-container"></div>
   <div class="container">
      <aside>
           
         <div class="top">
           <div class="logo">
             <h2>Masu <span class="danger"> ko jhol</span> </h2>
           </div>
           <div class="close" id="close_btn">
            <span class="material-symbols-sharp">
              close
              </span>
           </div>
         </div>
         <!-- end top -->
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
           <a href="#" class="active">
              <span class="material-symbols-sharp">mail_outline </span>
              <h3>Orders</h3>
              <span class="msg_count"><?php echo count($orders); ?></span>
           </a>
           <a href="menu.php">
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
      <!-- --------------
        end asid
      -------------------- -->

      <!-- --------------
        start main part
      --------------- -->

      <main>
           <h1>Orders Management</h1>

           <div class="date">
             <input type="date" >
           </div>

        <div class="insights">

           <!-- start seling -->
            <div class="sales">
               <span class="material-symbols-sharp">shopping_cart</span>
               <div class="middle">

                 <div class="left">
                   <h3>Total Revenue</h3>
                   <h1>Rs. <?php echo number_format($total_revenue, 2); ?></h1>
                 </div>
                  <div class="progress">
                      <svg>
                         <circle  r="30" cy="40" cx="40"></circle>
                      </svg>
                      <div class="number"><p>100%</p></div>
                  </div>

               </div>
               <small>All time earnings</small>
            </div>
           <!-- end seling -->
              <!-- start expenses -->
              <div class="expenses">
                <span class="material-symbols-sharp">check_circle</span>
                <div class="middle">
 
                  <div class="left">
                    <h3>Confirmed Orders</h3>
                    <h1><?php echo $confirmed_orders; ?></h1>
                  </div>
                   <div class="progress">
                       <svg>
                          <circle  r="30" cy="40" cx="40"></circle>
                       </svg>
                       <div class="number"><p><?php echo count($orders) > 0 ? round(($confirmed_orders/count($orders))*100, 0) : 0; ?>%</p></div>
                   </div>
 
                </div>
                <small>Orders confirmed</small>
             </div>
            <!-- end seling -->
               <!-- start seling -->
               <div class="income">
                <span class="material-symbols-sharp">local_shipping</span>
                <div class="middle">
 
                  <div class="left">
                    <h3>Shipping Orders</h3>
                    <h1><?php echo $shipping_orders; ?></h1>
                  </div>
                   <div class="progress">
                       <svg>
                          <circle  r="30" cy="40" cx="40"></circle>
                       </svg>
                       <div class="number"><p><?php echo count($orders) > 0 ? round(($shipping_orders/count($orders))*100, 0) : 0; ?>%</p></div>
                   </div>
 
                </div>
                <small>Orders in shipping</small>
             </div>
            <!-- end seling -->

        </div>
       <!-- end insights -->
      <div class="recent_order">
         <h2>All Orders</h2>
         <table> 
             <thead>
              <tr>
                <th>Order ID</th>
                <th>Item</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Qty</th>
                <th>Total</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
             </thead>
              <tbody>
                <?php if (!$orders): ?>
                  <tr><td colspan="9" class="text-center text-muted">No orders found.</td></tr>
                <?php else: foreach ($orders as $o): ?>
                  <tr>
                    <td><?php echo intval($o['order_id']); ?></td>
                    <td><?php echo htmlspecialchars($o['menu_name']); ?></td>
                    <td><?php echo htmlspecialchars($o['email']); ?></td>
                    <td><?php echo htmlspecialchars($o['mobile']); ?></td>
                    <td><a href="#" class="address-link" data-address="<?php echo addslashes(htmlspecialchars($o['address'])); ?>" data-order-id="<?php echo intval($o['order_id']); ?>" onclick="showFullAddress(event, this); return false;"><?php echo htmlspecialchars(substr($o['address'], 0, 20)) . (strlen($o['address']) > 20 ? '...' : ''); ?></a></td>
                    <td><?php echo intval($o['quantity']); ?></td>
                    <td>Rs. <?php echo number_format((float)$o['total_price'], 2); ?></td>
                    <td>
                      <div class="booking-actions">
                        <select name="status" class="booking-status-select" id="status-<?php echo intval($o['order_id']); ?>">
                          <?php foreach (["Confirmed","Shipping","Ongoing","Delivering","Cancelled"] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $o['status']===$st?'selected':''; ?>><?php echo $st; ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-update btn-booking-update" onclick="handleOrderUpdate(<?php echo intval($o['order_id']); ?>)">Update</button>
                      </div>
                    </td>
                    <td>
                      <button type="button" class="btn-delete btn-booking-delete" onclick="handleOrderDelete(<?php echo intval($o['order_id']); ?>)">Delete</button>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
         </table>
         <a href="index.php">Back to Dashboard</a>
      </div>

      </main>
      <!------------------
         end main
        ------------------->

      <!----------------
        start right main 
      ---------------------->
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

  <div class="recent_updates">
     <h2>Recent Activity</h2>
   <div class="updates">
      <div class="update">
         <div class="profile-photo">
            <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
         </div>
        <div class="message">
           <p><b>New Order</b> received successfully</p>
        </div>
      </div>
      <div class="update">
        <div class="profile-photo">
        <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
        </div>
       <div class="message">
          <p><b>Order Status</b> updated to shipped</p>
       </div>
     </div>
     <div class="update">
      <div class="profile-photo">
         <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
      </div>
     <div class="message">
        <p><b>Payment</b> confirmed for order</p>
     </div>
   </div>
  </div>
  </div>


   <div class="sales-analytics">
     <h2>Order Statistics</h2>

      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">receipt</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Total Orders</h3>
            <small class="text-muted">All time</small>
          </div>
          <h5 class="success">+<?php echo count($orders); ?></h5>
          <h3><?php echo count($orders); ?></h3>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">paid</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Avg Order Value</h3>
            <small class="text-muted">Per order</small>
          </div>
          <h5 class="success">+15%</h5>
          <h3>Rs. <?php echo count($orders) > 0 ? number_format($total_revenue/count($orders), 2) : '0.00'; ?></h3>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">inventory</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Pending Orders</h3>
            <small class="text-muted">Need attention</small>
          </div>
          <?php 
          $pending_count = 0;
          foreach($orders as $order) {
              if($order['status'] === 'Confirmed') $pending_count++;
          }
          ?>
          <h5 class="danger">-<?php echo $pending_count; ?></h5>
          <h3><?php echo $pending_count; ?></h3>
        </div>
      </div>
   
   
  
</div>

      <div class="add_product">
            <div>
            <span class="material-symbols-sharp">add</span>
            </div>
     </div>
</div>

   </div>

<script src="../assets/js/toast_notifications.js"></script>
<script src="../assets/js/adminscript.js"></script>
<script>
// AJAX Order Status Update
function handleOrderUpdate(orderId) {
    const statusSelect = document.getElementById('status-' + orderId);
    const newStatus = statusSelect.value;
    const button = event.target;
    
    // Disable button during processing
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Updating...';
    
    // Simple AJAX call
    fetch('update_order_status_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_id: parseInt(orderId),
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ToastNotifications.success('Order status updated to ' + newStatus);
            document.getElementById(`status-${orderId}`).value = newStatus;
        } else {
            ToastNotifications.error('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ToastNotifications.error('Error updating order status');
    })
    .finally(() => {
        button.disabled = false;
        button.textContent = originalText;
    });
}

function handleOrderDelete(orderId) {
    event.preventDefault();
    event.stopPropagation();
    showDeleteConfirmation(orderId);
}

function showDeleteConfirmation(orderId) {
    // Create modal HTML with animations
    const modalHtml = `
        <div id="deleteConfirmModal" class="delete-confirm-overlay">
            <div class="delete-confirm-modal">
                <div class="icon-wrapper">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"/>
                    </svg>
                </div>
                <h3>Delete Order?</h3>
                <p>Order #${orderId} will be permanently deleted. This action cannot be undone.</p>
                <div class="delete-confirm-buttons">
                    <button class="btn-cancel" onclick="closeDeleteConfirmation()">Cancel</button>
                    <button class="btn-delete-confirm" onclick="confirmDelete(${orderId})">Delete</button>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) existingModal.remove();
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    
    // Close modal when clicking outside
    document.getElementById('deleteConfirmModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteConfirmation();
    });
}

function closeDeleteConfirmation() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease-in-out';
        setTimeout(() => modal.remove(), 300);
    }
}

function confirmDelete(orderId) {
    const button = document.querySelector(`button[onclick="handleOrderDelete(${orderId})"]`);
    closeDeleteConfirmation();
    
    // Make delete request
    fetch('../includes/delete_order.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            order_id: parseInt(orderId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            ToastNotifications.success('Order deleted successfully');
            // Remove the row with animation
            const row = document.querySelector(`button[onclick="handleOrderDelete(${orderId})"]`).closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(100px)';
            setTimeout(() => row.remove(), 300);
        } else {
            ToastNotifications.error('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        ToastNotifications.error('Error deleting order');
    });
}

// Show full address in a modal
function showFullAddress(event, element) {
    event.preventDefault();
    const address = element.getAttribute('data-address');
    const orderId = element.getAttribute('data-order-id');
    
    // Create modal HTML
    const modalHtml = `
        <div id="addressModal" class="address-modal-overlay" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        ">
            <div class="address-modal-content" style="
                background: white;
                padding: 20px;
                border-radius: 8px;
                max-width: 500px;
                width: 80%;
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
            ">
                <h3 style="margin-top: 0; color: #333;">Full Address for Order #${orderId}</h3>
                <p style="white-space: pre-wrap; word-break: break-word; font-size: 16px; line-height: 1.5;">
                    ${address}
                </p>
                <button onclick="closeAddressModal()" style="
                    margin-top: 15px;
                    padding: 8px 16px;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">Close</button>
            </div>
        </div>
    `;
    
    // Remove any existing modal
    const existingModal = document.getElementById('addressModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeAddressModal() {
    const modal = document.getElementById('addressModal');
    if (modal) {
        modal.remove();
    }
}

// Close modal when clicking outside the content
document.addEventListener('click', function(event) {
    const modalOverlay = document.getElementById('addressModal');
    if (modalOverlay && event.target === modalOverlay) {
        closeAddressModal();
    }
});

// Debug: Check if script is loading
document.addEventListener('DOMContentLoaded', function() {
    console.log('Orders page loaded');
    
    // Debug: Check if buttons exist
    const updateButtons = document.querySelectorAll('.btn-booking-update');
    const deleteButtons = document.querySelectorAll('.btn-booking-delete');
    console.log('Update buttons found:', updateButtons.length);
    console.log('Delete buttons found:', deleteButtons.length);
});
</script>

<!-- Toast notification container -->
<div id="toastContainer" class="toast-container"></div>

<script>
// Animated Toast Notification System
// Using the standardized ToastNotifications instead of custom implementation
</script>
<script src="../assets/js/toast_notifications.js"></script>
</body>
</html>
