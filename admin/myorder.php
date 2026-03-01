<?php
// Start session if not already started
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
                      <div class="status-container">
                        <div class="status-badge-wrapper">
                          <span class="status-badge status-<?php echo strtolower($o['status']); ?>">
                            <?php echo $o['status']; ?>
                          </span>
                        </div>
                        <form action="../includes/order_status_update.php" method="post" class="status-form">
                          <input type="hidden" name="order_id" value="<?php echo intval($o['order_id']); ?>">
                          <select name="status" class="form-select">
                            <?php foreach (["Confirmed","Shipping","Ongoing","Delivering"] as $st): ?>
                              <option value="<?php echo $st; ?>" <?php echo $o['status']===$st?'selected':''; ?>><?php echo $st; ?></option>
                            <?php endforeach; ?>
                          </select>
                          <button type="submit" class="btn-update">Update</button>
                        </form>
                      </div>
                    </td>
                    <td>
                      <form action="../includes/delete_order.php" method="post" onsubmit="return confirm('Delete this order?');">
                        <input type="hidden" name="order_id" value="<?php echo intval($o['order_id']); ?>">
                        <button type="submit" class="btn-delete">Delete</button>
                      </form>
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
      ---------------------->    <div class="right">

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
           <p><b>Admin</b></p>
           <p>Administrator</p>
           <small class="text-muted">Online</small>
       </div>
       <div class="profile-photo">
         <img src="../assets/img/usersprofiles/adminpic.jpg" alt="Admin"/>
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

<script src="../assets/js/adminscript.js"></script>
</body>
  </html>