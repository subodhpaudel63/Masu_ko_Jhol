<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

// Get date range parameters
$date_range = $_GET['date_range'] ?? 'week';
$custom_start = $_GET['start_date'] ?? '';
$custom_end = $_GET['end_date'] ?? '';

// Set date range
switch($date_range) {
    case 'today':
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d');
        break;
    case 'week':
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
        break;
    case 'month':
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        break;
    case 'custom':
        $start_date = $custom_start ?: date('Y-m-d', strtotime('-7 days'));
        $end_date = $custom_end ?: date('Y-m-d');
        break;
    default:
        $start_date = date('Y-m-d', strtotime('-7 days'));
        $end_date = date('Y-m-d');
}

// Fetch analytics data
$sales_data = [];
$order_stats = [];
$menu_stats = [];
$booking_stats = [];
$customer_stats = [];

// Sales trends data
$sales_query = $conn->prepare("
    SELECT DATE(order_date) as date, 
           COUNT(*) as order_count, 
           SUM(total_price) as revenue
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY DATE(order_date)
    ORDER BY DATE(order_date)
");
$sales_query->bind_param("ss", $start_date, $end_date);
$sales_query->execute();
$sales_result = $sales_query->get_result();
while($row = $sales_result->fetch_assoc()) {
    $row['revenue'] = (float)$row['revenue'];
    $row['order_count'] = (int)$row['order_count'];
    $sales_data[] = $row;
}

// Order statistics
$order_stats_query = $conn->prepare("
    SELECT status, COUNT(*) as count, SUM(total_price) as revenue
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY status
");
$order_stats_query->bind_param("ss", $start_date, $end_date);
$order_stats_query->execute();
$order_stats_result = $order_stats_query->get_result();
while($row = $order_stats_result->fetch_assoc()) {
    $row['count'] = (int)$row['count'];
    $row['revenue'] = (float)$row['revenue'];
    $order_stats[] = $row;
}

// Menu analytics
$menu_query = $conn->prepare("
    SELECT menu_name, 
           COUNT(*) as order_count, 
           SUM(quantity) as total_quantity,
           SUM(total_price) as revenue
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY menu_id, menu_name
    ORDER BY order_count DESC
");
$menu_query->bind_param("ss", $start_date, $end_date);
$menu_query->execute();
$menu_result = $menu_query->get_result();
while($row = $menu_result->fetch_assoc()) {
    $row['revenue'] = (float)$row['revenue'];
    $row['order_count'] = (int)$row['order_count'];
    $row['total_quantity'] = (int)$row['total_quantity'];
    $menu_stats[] = $row;
}

// Booking statistics
$booking_query = $conn->prepare("
    SELECT status, COUNT(*) as count
    FROM bookings 
    WHERE booking_date BETWEEN ? AND ?
    GROUP BY status
");
$booking_query->bind_param("ss", $start_date, $end_date);
$booking_query->execute();
$booking_result = $booking_query->get_result();
while($row = $booking_result->fetch_assoc()) {
    $row['count'] = (int)$row['count'];
    $booking_stats[] = $row;
}

// Customer analytics
$customer_query = $conn->prepare("
    SELECT email, COUNT(*) as order_count
    FROM orders 
    WHERE order_date BETWEEN ? AND ?
    GROUP BY email
    ORDER BY order_count DESC
");
$customer_query->bind_param("ss", $start_date, $end_date);
$customer_query->execute();
$customer_result = $customer_query->get_result();
while($row = $customer_result->fetch_assoc()) {
    $customer_stats[] = $row;
}

// Calculate totals
$total_revenue = array_sum(array_column($sales_data, 'revenue'));
$total_orders = array_sum(array_column($sales_data, 'order_count'));
$avg_order_value = $total_orders > 0 ? $total_revenue / $total_orders : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics Dashboard - Masu Ko Jhol</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js">
  <link rel="stylesheet" href="../assets/css/adminstyle.css">
  <style>
    .analytics-container {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-top: 2rem;
    }
    
    .analytics-card {
      background: var(--clr-white);
      border-radius: var(--card-border-radius);
      padding: var(--card-padding);
      box-shadow: var(--box-shadow);
      transition: all 0.3s ease;
    }
    
    .analytics-card:hover {
      box-shadow: none;
      transform: translateY(-5px);
    }
    
    .chart-container {
      height: 300px;
      margin: 1rem 0;
    }
    
    .filter-section {
      display: flex;
      gap: 1rem;
      flex-wrap: wrap;
      margin-bottom: 2rem;
      padding: 1rem;
      background: var(--clr-white);
      border-radius: var(--border-radius-2);
      box-shadow: var(--box-shadow);
    }
    
    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 0.5rem;
    }
    
    .filter-group label {
      font-weight: 600;
      color: var(--clr-dark);
    }
    
    .filter-group select, .filter-group input {
      padding: 0.5rem;
      border: 1px solid var(--clr-info-light);
      border-radius: var(--border-radius-1);
    }
    
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin: 1rem 0;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 1.5rem;
      border-radius: var(--border-radius-2);
      text-align: center;
    }
    
    .stat-card h3 {
      font-size: 2rem;
      margin: 0.5rem 0;
      color: white;
    }
    
    .stat-card p {
      margin: 0;
      color: rgba(255,255,255,0.9);
    }
    
    .export-buttons {
      display: flex;
      gap: 1rem;
      margin: 1rem 0;
    }
    
    .btn-export {
      padding: 0.5rem 1rem;
      border: none;
      border-radius: var(--border-radius-1);
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }
    
    .btn-csv {
      background: #28a745;
      color: white;
    }
    
    .btn-pdf {
      background: #dc3545;
      color: white;
    }
    
    .btn-export:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    }
    
    .table-responsive {
      overflow-x: auto;
      margin: 1rem 0;
    }
    
    .analytics-table {
      width: 100%;
      border-collapse: collapse;
      background: var(--clr-white);
      border-radius: var(--border-radius-1);
      overflow: hidden;
    }
    
    .analytics-table th,
    .analytics-table td {
      padding: 1rem;
      text-align: left;
      border-bottom: 1px solid var(--clr-info-light);
    }
    
    .analytics-table th {
      background: var(--clr-primary);
      color: white;
      font-weight: 600;
    }
    
    .analytics-table tr:hover {
      background: var(--clr-light);
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
              <h3>Dashboard</h3>
           </a>
           <a href="users.php">
              <span class="material-symbols-sharp">person_outline </span>
              <h3>Customers</h3>
           </a>
           <a href="#" class="active">
              <span class="material-symbols-sharp">insights </span>
              <h3>Analytics</h3>
           </a>
           <a href="orders_page.php">
              <span class="material-symbols-sharp">mail_outline </span>
              <h3>Orders</h3>
           </a>
           <a href="menu.php">
              <span class="material-symbols-sharp">receipt_long </span>
              <h3>Menu</h3>
           </a>
           <a href="bookings.php">
              <span class="material-symbols-sharp">calendar_month </span>
              <h3>Bookings</h3>
           </a>
           <a href="feedback.php">
              <span class="material-symbols-sharp">Feedback </span>
              <h3>Feedback</h3>
           </a>
           <a href="../includes/logout.php">
              <span class="material-symbols-sharp">logout </span>
              <h3>Logout</h3>
           </a>
         </div>
      </aside>

      <main>
           <h1>Analytics Dashboard</h1>
           
           <!-- Date Range Filters -->
           <div class="filter-section">
               <div class="filter-group">
                   <label for="dateRange">Date Range:</label>
                   <select id="dateRange" onchange="updateFilters()">
                       <option value="today" <?php echo $date_range === 'today' ? 'selected' : ''; ?>>Today</option>
                       <option value="week" <?php echo $date_range === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                       <option value="month" <?php echo $date_range === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                       <option value="custom" <?php echo $date_range === 'custom' ? 'selected' : ''; ?>>Custom Range</option>
                   </select>
               </div>
               
               <div class="filter-group" id="customDateFields" style="display: <?php echo $date_range === 'custom' ? 'flex' : 'none'; ?>;">
                   <label for="startDate">Start Date:</label>
                   <input type="date" id="startDate" value="<?php echo $start_date; ?>">
                   
                   <label for="endDate">End Date:</label>
                   <input type="date" id="endDate" value="<?php echo $end_date; ?>">
               </div>
               
               <div class="filter-group">
                   <label>&nbsp;</label>
                   <button onclick="applyFilters()" class="btn-export">Apply Filters</button>
               </div>
           </div>

           <!-- Key Statistics -->
           <div class="stats-grid">
               <div class="stat-card">
                   <span class="material-symbols-sharp">payments</span>
                   <h3>Rs. <?php echo number_format($total_revenue, 2); ?></h3>
                   <p>Total Revenue</p>
               </div>
               <div class="stat-card">
                   <span class="material-symbols-sharp">shopping_cart</span>
                   <h3><?php echo $total_orders; ?></h3>
                   <p>Total Orders</p>
               </div>
               <div class="stat-card">
                   <span class="material-symbols-sharp">trending_up</span>
                   <h3>Rs. <?php echo number_format($avg_order_value, 2); ?></h3>
                   <p>Avg Order Value</p>
               </div>
               <div class="stat-card">
                   <span class="material-symbols-sharp">event_available</span>
                   <h3><?php echo count($booking_stats); ?></h3>
                   <p>Total Bookings</p>
               </div>
           </div>

           <!-- Export Buttons -->
           <div class="export-buttons">
               <button class="btn-export btn-csv" onclick="exportCSV()">Export CSV</button>
               <button class="btn-export btn-pdf" onclick="exportPDF()">Export PDF</button>
           </div>

           <!-- Analytics Charts and Data -->
           <div class="analytics-container">
               <!-- Sales Trends Chart -->
               <div class="analytics-card">
                   <h2>Sales Trends</h2>
                   <div class="chart-container">
                       <canvas id="salesChart"></canvas>
                   </div>
               </div>

               <!-- Order Status Distribution -->
               <div class="analytics-card">
                   <h2>Order Status Distribution</h2>
                   <div class="chart-container">
                       <canvas id="orderStatusChart"></canvas>
                   </div>
               </div>

               <!-- Top Selling Items -->
               <div class="analytics-card">
                   <h2>Top Selling Items</h2>
                   <div class="table-responsive">
                       <table class="analytics-table">
                           <thead>
                               <tr>
                                   <th>Item Name</th>
                                   <th>Orders</th>
                                   <th>Quantity</th>
                                   <th>Revenue</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach (array_slice($menu_stats, 0, 5) as $item): ?>
                               <tr>
                                   <td><?php echo htmlspecialchars($item['menu_name']); ?></td>
                                   <td><?php echo $item['order_count']; ?></td>
                                   <td><?php echo $item['total_quantity']; ?></td>
                                   <td>Rs. <?php echo number_format($item['revenue'], 2); ?></td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               </div>

               <!-- Booking Statistics -->
               <div class="analytics-card">
                   <h2>Booking Statistics</h2>
                   <div class="table-responsive">
                       <table class="analytics-table">
                           <thead>
                               <tr>
                                   <th>Status</th>
                                   <th>Count</th>
                                   <th>Percentage</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php 
                               $total_bookings = array_sum(array_column($booking_stats, 'count'));
                               foreach ($booking_stats as $booking): 
                                   $percentage = $total_bookings > 0 ? ($booking['count'] / $total_bookings) * 100 : 0;
                               ?>
                               <tr>
                                   <td><?php echo ucfirst(htmlspecialchars($booking['status'])); ?></td>
                                   <td><?php echo $booking['count']; ?></td>
                                   <td><?php echo number_format($percentage, 1); ?>%</td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               </div>

               <!-- Customer Analytics -->
               <div class="analytics-card">
                   <h2>Top Customers</h2>
                   <div class="table-responsive">
                       <table class="analytics-table">
                           <thead>
                               <tr>
                                   <th>Customer Email</th>
                                   <th>Order Count</th>
                                   <th>Customer Type</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php 
                               $returning_customers = 0;
                               foreach (array_slice($customer_stats, 0, 10) as $customer): 
                                   if ($customer['order_count'] > 1) $returning_customers++;
                                   $customer_type = $customer['order_count'] > 1 ? 'Returning' : 'New';
                               ?>
                               <tr>
                                   <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                   <td><?php echo $customer['order_count']; ?></td>
                                   <td><?php echo $customer_type; ?></td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
                   <div style="margin-top: 1rem;">
                       <p><strong>Returning Customers:</strong> <?php echo $returning_customers; ?> 
                          (<?php echo count($customer_stats) > 0 ? number_format(($returning_customers/count($customer_stats))*100, 1) : 0; ?>%)</p>
                   </div>
               </div>

               <!-- Least Selling Items -->
               <div class="analytics-card">
                   <h2>Least Selling Items</h2>
                   <div class="table-responsive">
                       <table class="analytics-table">
                           <thead>
                               <tr>
                                   <th>Item Name</th>
                                   <th>Orders</th>
                                   <th>Revenue</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach (array_slice(array_reverse($menu_stats), 0, 5) as $item): ?>
                               <tr>
                                   <td><?php echo htmlspecialchars($item['menu_name']); ?></td>
                                   <td><?php echo $item['order_count']; ?></td>
                                   <td>Rs. <?php echo number_format($item['revenue'], 2); ?></td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                       </table>
                   </div>
               </div>
           </div>
      </main>

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

   <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
   <script>
       // Date filter functionality
       function updateFilters() {
           const dateRange = document.getElementById('dateRange').value;
           const customFields = document.getElementById('customDateFields');
           if (dateRange === 'custom') {
               customFields.style.display = 'flex';
           } else {
               customFields.style.display = 'none';
           }
       }

       function applyFilters() {
           const dateRange = document.getElementById('dateRange').value;
           let url = '?date_range=' + dateRange;
           
           if (dateRange === 'custom') {
               const startDate = document.getElementById('startDate').value;
               const endDate = document.getElementById('endDate').value;
               url += '&start_date=' + startDate + '&end_date=' + endDate;
           }
           
           window.location.href = url;
       }

       // Export functions
       function exportCSV() {
           const dateRange = document.getElementById('dateRange').value;
           let url = 'export_csv.php?date_range=' + dateRange;
           
           if (dateRange === 'custom') {
               const startDate = document.getElementById('startDate').value;
               const endDate = document.getElementById('endDate').value;
               url += '&start_date=' + startDate + '&end_date=' + endDate;
           }
           
           window.location.href = url;
       }

       function exportPDF() {
           alert('PDF export functionality requires additional libraries. CSV export is available.');
       }

       // Chart initialization
       document.addEventListener('DOMContentLoaded', function() {
           // Sales Chart
           const salesCtx = document.getElementById('salesChart').getContext('2d');
           const salesData = <?php echo json_encode($sales_data); ?>;
           
           new Chart(salesCtx, {
               type: 'line',
               data: {
                   labels: salesData.map(item => item.date),
                   datasets: [{
                       label: 'Revenue (Rs)',
                       data: salesData.map(item => item.revenue),
                       borderColor: '#667eea',
                       backgroundColor: 'rgba(102, 126, 234, 0.1)',
                       tension: 0.4
                   }, {
                       label: 'Orders',
                       data: salesData.map(item => item.order_count),
                       borderColor: '#764ba2',
                       backgroundColor: 'rgba(118, 75, 162, 0.1)',
                       tension: 0.4
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   scales: {
                       y: {
                           beginAtZero: true
                       }
                   }
               }
           });

           // Order Status Chart
           const statusCtx = document.getElementById('orderStatusChart').getContext('2d');
           const orderStats = <?php echo json_encode($order_stats); ?>;
           
           new Chart(statusCtx, {
               type: 'doughnut',
               data: {
                   labels: orderStats.map(item => item.status),
                   datasets: [{
                       data: orderStats.map(item => item.count),
                       backgroundColor: [
                           '#4CAF50',
                           '#2196F3',
                           '#FF9800',
                           '#F44336'
                       ]
                   }]
               },
               options: {
                   responsive: true,
                   maintainAspectRatio: false,
                   plugins: {
                       legend: {
                           position: 'bottom'
                       }
                   }
               }
           });
       });
   </script>
   <script src="../assets/js/adminscript.js"></script>
</body>
</html>
