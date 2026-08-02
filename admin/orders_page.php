<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

$allowedSort = [
    'newest' => 'order_id DESC',
    'oldest' => 'order_id ASC',
    'price_high' => 'total_price DESC',
    'price_low' => 'total_price ASC',
];
$orderBySql = $allowedSort[$sort] ?? $allowedSort['newest'];

$where = [];
$params = [];
$types = '';
if ($search !== '') {
    $where[] = "(o.menu_name LIKE ? OR o.email LIKE ? OR o.mobile LIKE ? OR o.address LIKE ? OR o.admin_note LIKE ? OR o.full_name LIKE ? OR o.order_type LIKE ? OR o.special_instructions LIKE ?)";
    $like = '%' . $search . '%';
    $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like, $like]);
    $types .= 'ssssssss';
}
if ($statusFilter !== '') {
    // Map visual tab labels to database status values
    $statusMap = [
        'new' => 'Confirmed',
        'preparing' => 'Ongoing',
        'out_delivery' => 'Shipping',
        'delivered' => 'Delivering',
        'cancelled' => 'Cancelled'
    ];
    $dbStatus = $statusMap[$statusFilter] ?? $statusFilter;
    if (in_array($dbStatus, ['Confirmed','Shipping','Ongoing','Delivering','Cancelled'], true)) {
        $where[] = "o.status = ?";
        $params[] = $dbStatus;
        $types .= 's';
    }
}

$sql = "SELECT o.order_id, o.menu_id, o.menu_name, o.email, o.mobile, o.address, o.quantity, o.price, o.total_price, o.status, o.order_time, o.admin_note, o.full_name, o.order_type, o.table_number, o.special_instructions, o.payment_method, m.menu_image FROM orders o LEFT JOIN menu m ON o.menu_id = m.menu_id";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= " ORDER BY o.$orderBySql";

$stmt = $conn->prepare($sql);
if ($stmt && $params) {
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}
$orders = [];
if ($res) { while ($row = $res->fetch_assoc()) { $orders[] = $row; } }

// Calculate global, unfiltered statistics for summary widgets
$all_orders_count = 0;
$total_revenue = 0.0;
$delivered_count = 0;
$out_delivery_count = 0;
$preparing_count = 0;
$cancelled_count = 0;

$stats_res = $conn->query("SELECT status, total_price FROM orders");
if ($stats_res) {
    while ($row = $stats_res->fetch_assoc()) {
        $all_orders_count++;
        $total_revenue += (float)$row['total_price'];
        if ($row['status'] === 'Delivering') $delivered_count++;
        elseif ($row['status'] === 'Shipping') $out_delivery_count++;
        elseif ($row['status'] === 'Ongoing') $preparing_count++;
        elseif ($row['status'] === 'Cancelled') $cancelled_count++;
    }
}

// Fetch the 3 most recent orders for the sidebar activity list
$recent_orders = [];
$recent_res = $conn->query("SELECT order_id, full_name, status, order_time FROM orders ORDER BY order_id DESC LIMIT 3");
if ($recent_res) {
    while ($row = $recent_res->fetch_assoc()) {
        $recent_orders[] = $row;
    }
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/adminstyle.css?v=<?= filemtime(__DIR__ . '/../assets/css/adminstyle.css') ?>">

  <style>
    @media screen and (min-width: 1200px) {
      .container {
        grid-template-columns: 14rem auto !important;
      }
      .right-column-sidebar {
        display: none !important;
      }
      .desktop-header-profile {
        display: flex !important;
      }
    }
    @media screen and (max-width: 1199px) {
      .desktop-header-profile {
        display: none !important;
      }
    }

    /* Premium layout CSS rules */
    .orders-layout {
      display: grid;
      grid-template-columns: 1fr 340px;
      gap: 1.5rem;
      align-items: start;
    }
    @media screen and (max-width: 1024px) {
      .orders-layout {
        grid-template-columns: 1fr;
      }
    }

    /* Summary Row Cards */
    .orders-stats-row {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
      gap: 1rem;
      margin-bottom: 1.5rem;
    }
    .order-stat-card {
      background: var(--clr-white);
      padding: 1.2rem;
      border-radius: var(--border-radius-3);
      box-shadow: var(--box-shadow);
      display: flex;
      align-items: center;
      gap: 1rem;
      transition: transform 0.2s ease;
    }
    .order-stat-card:hover {
      transform: translateY(-2px);
    }
    .order-stat-icon {
      width: 45px;
      height: 45px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.4rem;
    }
    .stat-total { background: rgba(240, 90, 34, 0.1); color: #f05a22; }
    .stat-delivered { background: rgba(46, 213, 115, 0.1); color: #2ed573; }
    .stat-out { background: rgba(115, 128, 236, 0.1); color: #7380ec; }
    .stat-preparing { background: rgba(255, 165, 0, 0.1); color: #ffa502; }
    .stat-cancelled { background: rgba(255, 71, 87, 0.1); color: #ff4757; }

    /* Inline filtering navigation */
    .order-tabs-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      border-bottom: 2px solid var(--clr-border);
      padding-bottom: 0.5rem;
      flex-wrap: wrap;
      gap: 1rem;
    }
    .order-tabs {
      display: flex;
      gap: 0.5rem;
    }
    .order-tab {
      padding: 0.6rem 1.2rem;
      border-radius: 8px;
      background: transparent;
      color: var(--clr-dark-variant);
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: all 0.2s ease;
      font-size: 0.9rem;
      text-decoration: none;
    }
    .order-tab:hover {
      color: var(--clr-primary);
    }
    .order-tab.active {
      background: var(--clr-white);
      color: var(--clr-primary);
      box-shadow: var(--box-shadow);
    }

    /* Live Tracking Sidebar Widget */
    .tracking-sidebar {
      background: var(--clr-white);
      padding: 1.5rem;
      border-radius: var(--border-radius-3);
      box-shadow: var(--box-shadow);
    }

    /* Table Badges */
    .badge-payment {
      padding: 0.25rem 0.6rem;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
    }
    .payment-cod { background: #fff4f0; color: #f05a22; border: 1px solid rgba(240, 90, 34, 0.15); }
    .payment-online { background: #eefdf5; color: #2ed573; border: 1px solid rgba(46, 213, 115, 0.15); }

    .badge-status {
      padding: 0.35rem 0.75rem;
      border-radius: 20px;
      font-size: 0.75rem;
      font-weight: 600;
      display: inline-block;
      text-align: center;
    }
    .status-new-badge { background: #f1f5f9; color: #475569; }
    .status-prep-badge { background: #fffbeb; color: #d97706; }
    .status-out-badge { background: #eff6ff; color: #2563eb; }
    .status-del-badge { background: #f0fdf4; color: #16a34a; }
    .status-can-badge { background: #fef2f2; color: #dc2626; }


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
      <!-- --------------
        end asid
      -------------------- -->

      <!-- --------------
        start main part
      --------------- -->

       <main style="max-width: 100%;">
            <div class="dashboard-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 2.2rem; font-weight: 800; color: var(--clr-dark); margin: 0;">All Orders</h1>
                    <p style="color: var(--clr-dark-variant); font-size: 0.9rem; margin-top: 0.2rem;">Track and manage all customer orders</p>
                </div>
                <div class="desktop-header-profile" style="display: none; align-items: center; gap: 1.5rem;">
                    <div class="theme-toggler" style="background: var(--clr-white); display: flex; justify-content: space-between; height: 1.8rem; width: 4.2rem; cursor: pointer; border-radius: var(--border-radius-1); box-shadow: var(--box-shadow); align-items: center; padding: 0 4px;">
                      <span class="material-symbols-sharp active" style="font-size: 1.2rem;">light_mode</span>
                      <span class="material-symbols-sharp" style="font-size: 1.2rem;">dark_mode</span>
                    </div>
                    <div class="profile" style="display: flex; gap: 1rem; align-items: center;">
                       <div class="info" style="text-align: right;">
                           <p style="margin: 0; font-weight: 700; color: var(--clr-dark);"><b>Subodh Admin</b></p>
                           <small class="text-muted" style="font-size: 0.75rem;">Administrator</small>
                       </div>
                       <div class="profile-photo">
                         <img src="../assets/img/usersprofiles/adminpic.jpg" alt="Admin Profile" style="width: 2.8rem; height: 2.8rem; border-radius: 50%; object-fit: cover; display: block;"/>
                       </div>
                    </div>
                </div>
            </div>

            <div class="orders-layout">
                <!-- LEFT / MAIN PANE -->
                <div class="orders-main-content">
                    <!-- Stats Row -->
                    <div class="orders-stats-row">
                        <div class="order-stat-card">
                            <div class="order-stat-icon stat-total"><i class="fa fa-shopping-bag"></i></div>
                            <div>
                                <small class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Total Orders</small>
                                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--clr-dark);"><?php echo $all_orders_count; ?></h2>
                                <small class="text-muted" style="font-size: 0.7rem;">All time</small>
                            </div>
                        </div>
                        <div class="order-stat-card">
                            <div class="order-stat-icon stat-delivered"><i class="fa fa-circle-check"></i></div>
                            <div>
                                <small class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Delivered</small>
                                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--clr-dark);"><?php echo $delivered_count; ?></h2>
                                <small class="text-muted" style="font-size: 0.7rem;">This month</small>
                            </div>
                        </div>
                        <div class="order-stat-card">
                            <div class="order-stat-icon stat-out"><i class="fa fa-truck"></i></div>
                            <div>
                                <small class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Out for Delivery</small>
                                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--clr-dark);"><?php echo $out_delivery_count; ?></h2>
                                <small class="text-muted" style="font-size: 0.7rem;">Today</small>
                            </div>
                        </div>
                        <div class="order-stat-card">
                            <div class="order-stat-icon stat-preparing"><i class="fa fa-clock"></i></div>
                            <div>
                                <small class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Preparing</small>
                                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--clr-dark);"><?php echo $preparing_count; ?></h2>
                                <small class="text-muted" style="font-size: 0.7rem;">Today</small>
                            </div>
                        </div>
                        <div class="order-stat-card">
                            <div class="order-stat-icon stat-cancelled"><i class="fa fa-circle-xmark"></i></div>
                            <div>
                                <small class="text-muted" style="font-size: 0.8rem; font-weight: 500;">Cancelled</small>
                                <h2 style="font-size: 1.5rem; font-weight: 800; margin: 0; color: var(--clr-dark);"><?php echo $cancelled_count; ?></h2>
                                <small class="text-muted" style="font-size: 0.7rem;">This month</small>
                            </div>
                        </div>
                    </div>

                    <!-- Filters row -->
                    <div class="order-tabs-row">
                        <div class="order-tabs">
                            <a href="orders_page.php" class="order-tab <?php echo $statusFilter===''?'active':''; ?>">All Orders</a>
                            <a href="orders_page.php?status=new" class="order-tab <?php echo $statusFilter==='new'?'active':''; ?>">New</a>
                            <a href="orders_page.php?status=preparing" class="order-tab <?php echo $statusFilter==='preparing'?'active':''; ?>">Preparing</a>
                            <a href="orders_page.php?status=out_delivery" class="order-tab <?php echo $statusFilter==='out_delivery'?'active':''; ?>">Out for Delivery</a>
                            <a href="orders_page.php?status=delivered" class="order-tab <?php echo $statusFilter==='delivered'?'active':''; ?>">Delivered</a>
                            <a href="orders_page.php?status=cancelled" class="order-tab <?php echo $statusFilter==='cancelled'?'active':''; ?>">Cancelled</a>
                        </div>
                        
                        <form method="GET" action="orders_page.php" style="display: flex; gap: 0.5rem; align-items: center;">
                            <?php if ($statusFilter): ?>
                                <input type="hidden" name="status" value="<?php echo htmlspecialchars($statusFilter); ?>">
                            <?php endif; ?>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders..." style="padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid var(--clr-border); background: var(--clr-white); color: var(--clr-dark); font-size: 0.85rem; width: 200px;">
                            
                            <select name="sort" onchange="this.form.submit()" style="padding: 0.4rem 0.8rem; border-radius: 8px; border: 1px solid var(--clr-border); background: var(--clr-white); color: var(--clr-dark); font-size: 0.85rem; cursor: pointer;">
                                <option value="newest" <?php echo $sort==='newest'?'selected':''; ?>>Sort by: Latest</option>
                                <option value="oldest" <?php echo $sort==='oldest'?'selected':''; ?>>Sort by: Oldest</option>
                                <option value="price_high" <?php echo $sort==='price_high'?'selected':''; ?>>Price: High to Low</option>
                                <option value="price_low" <?php echo $sort==='price_low'?'selected':''; ?>>Price: Low to High</option>
                            </select>
                        </form>
                    </div>

                    <!-- Table container -->
                    <div class="recent_order" style="margin-top: 0; box-shadow: var(--box-shadow); background: var(--clr-white); padding: var(--card-padding); border-radius: var(--card-border-radius);">
                         <table style="width: 100%; border-collapse: collapse; text-align: left;">
                             <thead>
                              <tr style="border-bottom: 2px solid var(--clr-info-light); color: var(--clr-dark-variant); font-size: 0.85rem;">
                                <th style="padding: 0.8rem 0.5rem;">Order ID</th>
                                <th style="padding: 0.8rem 0.5rem;">Customer</th>
                                <th style="padding: 0.8rem 0.5rem;">Items</th>
                                <th style="padding: 0.8rem 0.5rem;">Amount</th>
                                <th style="padding: 0.8rem 0.5rem;">Payment</th>
                                <th style="padding: 0.8rem 0.5rem;">Status</th>
                                <th style="padding: 0.8rem 0.5rem;">Order Time</th>
                                <th style="padding: 0.8rem 0.5rem; text-align: center;">Actions</th>
                              </tr>
                             </thead>
                              <tbody>
                                <?php if (!$orders): ?>
                                  <tr><td colspan="8" class="text-center text-muted" style="padding: 2rem;">No orders found.</td></tr>
                                <?php else: foreach ($orders as $o): 
                                    $pm = $o['payment_method'] === 'Pay at Restaurant' ? 'Paid Online' : 'Cash on Delivery'; 
                                    $pmClass = $o['payment_method'] === 'Pay at Restaurant' ? 'payment-online' : 'payment-cod';
                                    
                                    $stBadgeClass = 'status-new-badge';
                                    $visualStatus = 'New';
                                    if ($o['status'] === 'Ongoing') { $stBadgeClass = 'status-prep-badge'; $visualStatus = 'Preparing'; }
                                    elseif ($o['status'] === 'Shipping') { $stBadgeClass = 'status-out-badge'; $visualStatus = 'Out for Delivery'; }
                                    elseif ($o['status'] === 'Delivering') { $stBadgeClass = 'status-del-badge'; $visualStatus = 'Delivered'; }
                                    elseif ($o['status'] === 'Cancelled') { $stBadgeClass = 'status-can-badge'; $visualStatus = 'Cancelled'; }
                                ?>
                                  <tr style="border-bottom: 1px solid var(--clr-info-light);">
                                     <td style="padding: 1rem 0.5rem; font-weight: 600; color: var(--clr-dark-variant);">
                                        <a href="order_view.php?id=<?php echo intval($o['order_id']); ?>" style="color: inherit; text-decoration: none; display: inline-flex; align-items: center;">
                                            <i class="fa fa-chevron-right" style="font-size: 0.7rem; margin-right: 0.4rem; color: var(--clr-primary);"></i>
                                            ORD-<?php echo sprintf("%04d", $o['order_id']); ?>
                                        </a>
                                     </td>
                                    <td style="padding: 1rem 0.5rem;">
                                      <strong style="color: var(--clr-dark);"><?php echo htmlspecialchars($o['full_name'] ?: 'N/A'); ?></strong><br>
                                      <a href="orders_page.php?q=<?php echo urlencode($o['email']); ?>" title="Filter by Customer" style="font-size: 0.75rem; text-decoration: underline; color: var(--clr-primary);"><?php echo htmlspecialchars($o['email']); ?></a>
                                    </td>
                                    <td style="padding: 1rem 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                      <img src="../<?php echo htmlspecialchars($o['menu_image'] ?: 'assets/images/placeholder.jpg'); ?>" style="width: 35px; height: 35px; border-radius: 6px; object-fit: cover; border: 1px solid var(--clr-border);">
                                      <div>
                                          <strong style="color: var(--clr-dark); font-size: 0.85rem;"><?php echo htmlspecialchars($o['menu_name']); ?></strong><br>
                                          <small class="text-muted" style="font-size: 0.75rem;">x<?php echo intval($o['quantity']); ?></small>
                                      </div>
                                    </td>
                                    <td style="padding: 1rem 0.5rem; font-weight: 700; color: var(--clr-dark);">Rs. <?php echo number_format((float)$o['total_price'], 2); ?></td>
                                    <td style="padding: 1rem 0.5rem;"><span class="badge-payment <?php echo $pmClass; ?>"><?php echo $pm; ?></span></td>
                                    <td style="padding: 1rem 0.5rem;">
                                        <select name="status" class="booking-status-select" id="status-<?php echo intval($o['order_id']); ?>" onchange="handleOrderUpdate(<?php echo intval($o['order_id']); ?>)" style="padding: 0.25rem 0.5rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; cursor: pointer; border: 1px solid var(--clr-border); background: var(--clr-white);">
                                          <option value="Confirmed" <?php echo $o['status']==='Confirmed'?'selected':''; ?>>New</option>
                                          <option value="Ongoing" <?php echo $o['status']==='Ongoing'?'selected':''; ?>>Preparing</option>
                                          <option value="Shipping" <?php echo $o['status']==='Shipping'?'selected':''; ?>>Out for Delivery</option>
                                          <option value="Delivering" <?php echo $o['status']==='Delivering'?'selected':''; ?>>Delivered</option>
                                          <option value="Cancelled" <?php echo $o['status']==='Cancelled'?'selected':''; ?>>Cancelled</option>
                                        </select>
                                    </td>
                                    <td style="padding: 1rem 0.5rem; font-size: 0.8rem; color: var(--clr-dark-variant);"><?php echo date('d M Y h:i A', strtotime($o['order_time'])); ?></td>
                                    <td style="padding: 1rem 0.5rem; text-align: center;">
                                      <button type="button" class="btn-delete btn-booking-delete" onclick="handleOrderDelete(<?php echo intval($o['order_id']); ?>)" style="background: transparent; color: var(--clr-danger); border: none; font-size: 1.1rem; cursor: pointer; padding: 0.2rem;"><i class="fa fa-trash"></i></button>
                                    </td>
                                  </tr>
                                <?php endforeach; endif; ?>
                              </tbody>
                         </table>
                    </div>
                </div>

                <!-- RIGHT PANE (Live Tracking Sidebar) -->
                <div class="tracking-sidebar">
                    <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--clr-dark); margin-bottom: 1.5rem;">Live Order Tracking</h2>
                    
                    <!-- SVG Donut Chart -->
                    <div style="position: relative; width: 160px; height: 160px; margin: 0 auto 1.5rem;">
                        <?php
                        $total_mapped = $out_delivery_count + $preparing_count + $delivered_count + $cancelled_count;
                        $p_out = $total_mapped > 0 ? ($out_delivery_count / $total_mapped) * 100 : 0;
                        $p_prep = $total_mapped > 0 ? ($preparing_count / $total_mapped) * 100 : 0;
                        $p_del = $total_mapped > 0 ? ($delivered_count / $total_mapped) * 100 : 0;
                        $p_can = $total_mapped > 0 ? ($cancelled_count / $total_mapped) * 100 : 0;
                        
                        $circ = 377.0;
                        $stroke_out = ($p_out / 100) * $circ;
                        $stroke_prep = ($p_prep / 100) * $circ;
                        $stroke_del = ($p_del / 100) * $circ;
                        $stroke_can = ($p_can / 100) * $circ;
                        
                        $offset = 0;
                        ?>
                        <svg width="100%" height="100%" viewBox="0 0 160 160" style="transform: rotate(-90deg);">
                            <circle cx="80" cy="80" r="60" fill="transparent" stroke="#f1f5f9" stroke-width="12" />
                            <?php if ($stroke_out > 0): ?>
                                <circle cx="80" cy="80" r="60" fill="transparent" stroke="#7380ec" stroke-width="12" stroke-dasharray="<?php echo $stroke_out; ?> <?php echo $circ - $stroke_out; ?>" stroke-dashoffset="-<?php echo $offset; ?>" />
                                <?php $offset += $stroke_out; ?>
                            <?php endif; ?>
                            <?php if ($stroke_prep > 0): ?>
                                <circle cx="80" cy="80" r="60" fill="transparent" stroke="#ffa502" stroke-width="12" stroke-dasharray="<?php echo $stroke_prep; ?> <?php echo $circ - $stroke_prep; ?>" stroke-dashoffset="-<?php echo $offset; ?>" />
                                <?php $offset += $stroke_prep; ?>
                            <?php endif; ?>
                            <?php if ($stroke_del > 0): ?>
                                <circle cx="80" cy="80" r="60" fill="transparent" stroke="#2ed573" stroke-width="12" stroke-dasharray="<?php echo $stroke_del; ?> <?php echo $circ - $stroke_del; ?>" stroke-dashoffset="-<?php echo $offset; ?>" />
                                <?php $offset += $stroke_del; ?>
                            <?php endif; ?>
                            <?php if ($stroke_can > 0): ?>
                                <circle cx="80" cy="80" r="60" fill="transparent" stroke="#ff4757" stroke-width="12" stroke-dasharray="<?php echo $stroke_can; ?> <?php echo $circ - $stroke_can; ?>" stroke-dashoffset="-<?php echo $offset; ?>" />
                            <?php endif; ?>
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                            <span style="font-size: 1.5rem; font-weight: 800; color: var(--clr-dark);"><?php echo $all_orders_count; ?></span>
                            <span style="font-size: 0.75rem; color: var(--clr-dark-variant);">Total Orders</span>
                        </div>
                    </div>

                    <!-- Donut Legend -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 2rem;">
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                            <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--clr-dark-variant);">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #7380ec;"></span> Out for Delivery
                            </span>
                            <strong style="color: var(--clr-dark);"><?php echo $out_delivery_count; ?> (<?php echo $all_orders_count > 0 ? round(($out_delivery_count/$all_orders_count)*100) : 0; ?>%)</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                            <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--clr-dark-variant);">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #ffa502;"></span> Preparing
                            </span>
                            <strong style="color: var(--clr-dark);"><?php echo $preparing_count; ?> (<?php echo $all_orders_count > 0 ? round(($preparing_count/$all_orders_count)*100) : 0; ?>%)</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                            <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--clr-dark-variant);">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #2ed573;"></span> Delivered
                            </span>
                            <strong style="color: var(--clr-dark);"><?php echo $delivered_count; ?> (<?php echo $all_orders_count > 0 ? round(($delivered_count/$all_orders_count)*100) : 0; ?>%)</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                            <span style="display: flex; align-items: center; gap: 0.5rem; color: var(--clr-dark-variant);">
                                <span style="width: 10px; height: 10px; border-radius: 50%; background: #ff4757;"></span> Cancelled
                            </span>
                            <strong style="color: var(--clr-dark);"><?php echo $cancelled_count; ?> (<?php echo $all_orders_count > 0 ? round(($cancelled_count/$all_orders_count)*100) : 0; ?>%)</strong>
                        </div>
                    </div>

                    <!-- Recent Orders -->
                    <h2 style="font-size: 1.1rem; font-weight: 700; color: var(--clr-dark); margin-bottom: 1rem; border-top: 1px solid var(--clr-border); padding-top: 1.5rem;">Recent Orders</h2>
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <?php foreach ($recent_orders as $ro):
                            $roBadgeClass = 'status-new-badge';
                            $roStatus = 'New';
                            if ($ro['status'] === 'Ongoing') { $roBadgeClass = 'status-prep-badge'; $roStatus = 'Preparing'; }
                            elseif ($ro['status'] === 'Shipping') { $roBadgeClass = 'status-out-badge'; $roStatus = 'Out for Delivery'; }
                            elseif ($ro['status'] === 'Delivering') { $roBadgeClass = 'status-del-badge'; $roStatus = 'Delivered'; }
                            elseif ($ro['status'] === 'Cancelled') { $roBadgeClass = 'status-can-badge'; $roStatus = 'Cancelled'; }
                        ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; background: var(--clr-color-background); padding: 0.8rem; border-radius: 8px;">
                            <div>
                                <strong style="font-size: 0.85rem; color: var(--clr-dark);">ORD-<?php echo sprintf("%04d", $ro['order_id']); ?></strong><br>
                                <small class="text-muted" style="font-size: 0.75rem;"><?php echo htmlspecialchars($ro['full_name'] ?: 'N/A'); ?></small>
                            </div>
                            <div style="text-align: right;">
                                <span class="badge-status <?php echo $roBadgeClass; ?>" style="font-size: 0.7rem; padding: 0.2rem 0.5rem;"><?php echo $roStatus; ?></span><br>
                                <small class="text-muted" style="font-size: 0.7rem;"><?php echo date('h:i A', strtotime($ro['order_time'])); ?></small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
       </main>

       <!-- Replaced right sidebar block -->
       <div class="right right-column-sidebar" style="display: none;">
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
            document.getElementById(`status-${orderId}`).value = newStatus;
        }
    })
    .catch(error => {
        console.error('Error:', error);
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
            // Remove the row with animation
            const row = document.querySelector(`button[onclick="handleOrderDelete(${orderId})"]`).closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(100px)';
            setTimeout(() => row.remove(), 300);
        }
    })
    .catch(error => {
        console.error('Error:', error);
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




</body>
</html>
