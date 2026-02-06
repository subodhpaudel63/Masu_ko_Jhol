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
</head>
<body>
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
                    <td><?php echo htmlspecialchars(substr($o['address'], 0, 20)) . (strlen($o['address']) > 20 ? '...' : ''); ?></td>
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
         <img src="../assets/img/usersprofiles/fa29eed8-1427-4ec2-a671-e4e45a399f3c.jpg" alt="Admin Profile"/>
       </div>
    </div>
</div>

  <div class="recent_updates">
     <h2>Recent Activity</h2>
   <div class="updates">
      <div class="update">
         <div class="profile-photo">
            <img src="../assets/img/user-avatar.png" alt=""/>
         </div>
        <div class="message">
           <p><b>New Order</b> received successfully</p>
        </div>
      </div>
      <div class="update">
        <div class="profile-photo">
        <img src="../assets/img/order-icon.png" alt=""/>
        </div>
       <div class="message">
          <p><b>Order Status</b> updated to shipped</p>
       </div>
     </div>
     <div class="update">
      <div class="profile-photo">
         <img src="../assets/img/menu-icon.png" alt=""/>
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
            alert('Order status updated successfully!');
            // Update dashboard stats if they exist
            const totalRevenueElement = document.querySelector('.total-revenue-display');
            const totalOrdersElement = document.querySelector('.total-orders-display');
            
            if (totalRevenueElement && data.stats) {
                totalRevenueElement.textContent = 'Rs. ' + parseFloat(data.stats.total_revenue).toFixed(2);
                totalRevenueElement.classList.add('updating');
                setTimeout(() => {
                    totalRevenueElement.classList.remove('updating');
                }, 500);
            }
            
            if (totalOrdersElement && data.stats) {
                totalOrdersElement.textContent = data.stats.total_orders;
                totalOrdersElement.classList.add('updating');
                setTimeout(() => {
                    totalOrdersElement.classList.remove('updating');
                }, 500);
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating order status');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

function handleOrderDelete(orderId) {
    if (!confirm('Are you sure you want to delete order #' + orderId + '? This action cannot be undone.')) {
        return;
    }
    
    const button = event.target;
    
    // Disable button during processing
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Deleting...';
    
    // Simple AJAX call
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
            alert('Order deleted successfully!');
            // Remove the row
            const row = button.closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(100px)';
            setTimeout(() => {
                row.remove();
            }, 300);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting order');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

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
</body>
</html>