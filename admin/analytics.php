<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Analytics Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
  <style>
    :root{--bg:#0f1115;--panel:#161a22;--muted:#8b95a7;--text:#e8edf3;--brand:#ffb74d;--accent:#7c4dff;--danger:#ff6b6b;--success:#4caf50}
    html,body{height:100%}
    body{background:var(--bg);color:var(--text);}
    .sidebar{width:260px;background:linear-gradient(180deg,#0e1117 0%,#0b0e13 100%);position:fixed;inset:0 auto 0 0;box-shadow:0 0 0 1px #1f2330}
    .sidebar .nav-link{color:var(--muted)}
    .sidebar .nav-link.active,.sidebar .nav-link:hover{color:var(--text);background:#1a1f2b;border-radius:10px}
    .content{margin-left:260px;min-height:100vh}
    .topbar{background:#0b0e13;box-shadow:0 1px 0 #1f2330}
    .card{background:var(--panel);border:1px solid #1f2330;border-radius:16px}
    .metric{display:flex;gap:12px;align-items:center}
    .metric .icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center}
    .metric.small .value{font-size:20px}
    .badge-cat{text-transform:uppercase;letter-spacing:.06em}
    .table> :not(caption)>*>*{background:transparent;color:var(--text)}
    .table thead th{color:var(--muted)}
    .btn-gradient{background:linear-gradient(135deg,#ff7a18 0%,#af002d 74%);border:none;color:#fff}
    .lang-select{background:#0f131b;color:var(--text);border:1px solid #273044}
  </style>
</head>
<body>
  <div class="sidebar d-flex flex-column p-3">
    <h5 class="mb-4 d-flex align-items-center gap-2"><i class="bi bi-fire text-warning"></i> Masu Admin</h5>
    <nav class="nav nav-pills flex-column gap-1">
      <a class="nav-link" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php" data-page="dashboard"><i class="bi bi-speedometer2 me-2"></i><span>Dashboard</span></a>
      <a class="nav-link" href="/Masu%20Ko%20Jhol%28full%29/admin/myorder.php" data-page="orders"><i class="bi bi-bag-check me-2"></i><span>Orders</span></a>
      <a class="nav-link" href="/Masu%20Ko%20Jhol%28full%29/admin/menu.php" data-page="menu"><i class="bi bi-card-checklist me-2"></i><span>Menu</span></a>
      <a class="nav-link" href="/Masu%20Ko%20Jhol%28full%29/admin/users.php" data-page="customers"><i class="bi bi-people me-2"></i><span>Customers</span></a>
      <a class="nav-link active" href="/Masu%20Ko%20Jhol%28full%29/admin/analytics.php" data-page="analytics"><i class="bi bi-graph-up-arrow me-2"></i><span>Analytics</span></a>
      <a class="nav-link" href="/Masu%20Ko%20Jhol%28full%29/admin/bookings.php" data-page="bookings"><i class="bi bi-calendar-check me-2"></i><span>Table Bookings</span></a>
    </nav>
    <div class="mt-auto small text-muted">© <span id="y"></span> Masu Ko Jhol</div>
  </div>

  <div class="content">
    <div class="topbar d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
      <div class="d-flex align-items-center gap-2">
        <h3 class="mb-0">Analytics Dashboard</h3>
      </div>
      <div class="d-flex align-items-center gap-2">
        <a class="btn btn-sm btn-outline-light" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
      </div>
    </div>

    <main class="p-3 p-lg-4">
      <div class="row g-3 g-lg-4">
        <div class="col-lg-12">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Revenue Overview</h5>
              <select id="revenue-period" class="form-select form-select-sm w-auto bg-transparent text-light border-secondary-subtle">
                <option value="30">Last 30 Days</option>
                <option value="90">Last 90 Days</option>
                <option value="365">Last Year</option>
              </select>
            </div>
            <canvas id="revenueChart" height="100"></canvas>
          </div>
        </div>
      </div>

      <div class="row g-3 g-lg-4 mt-1">
        <div class="col-lg-6">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Order Trends</h5>
            </div>
            <canvas id="ordersChart" height="100"></canvas>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Top Selling Items</h5>
            </div>
            <canvas id="topItemsChart" height="100"></canvas>
          </div>
        </div>
      </div>

      <div class="row g-3 g-lg-4 mt-1">
        <div class="col-lg-12">
          <div class="card p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="mb-0">Sales by Category</h5>
            </div>
            <canvas id="categoryChart" height="100"></canvas>
          </div>
        </div>
      </div>
    </main>
  </div>

  <script>
    document.getElementById('y').textContent = new Date().getFullYear();
    
    // Initialize charts
    const revenueChart = new Chart(document.getElementById('revenueChart'), {
      type: 'line',
      data: {labels: [], datasets: [{label: 'Revenue (Rs)', data: [], borderColor: '#7c4dff', backgroundColor: 'rgba(124, 77, 255, 0.1)', fill: true}]},
      options: {scales: {x: {ticks: {color: '#c9d1e3'}}, y: {ticks: {color: '#c9d1e3'}}}}
    });
    
    const ordersChart = new Chart(document.getElementById('ordersChart'), {
      type: 'bar',
      data: {labels: [], datasets: [{label: 'Orders', data: [], backgroundColor: '#ffb74d'}]},
      options: {scales: {x: {ticks: {color: '#c9d1e3'}}, y: {ticks: {color: '#c9d1e3'}}}}
    });
    
    const topItemsChart = new Chart(document.getElementById('topItemsChart'), {
      type: 'doughnut',
      data: {labels: [], datasets: [{data: [], backgroundColor: ['#7c4dff', '#ffb74d', '#26a69a', '#ff6b6b', '#4caf50']}]},
      options: {plugins: {legend: {labels: {color: '#c9d1e3'}}}}
    });
    
    const categoryChart = new Chart(document.getElementById('categoryChart'), {
      type: 'bar',
      data: {labels: [], datasets: [{label: 'Sales', data: [], backgroundColor: '#7c4dff'}]},
      options: {scales: {x: {ticks: {color: '#c9d1e3'}}, y: {ticks: {color: '#c9d1e3'}}}}
    });
    
    // Load analytics data
    async function loadAnalytics() {
      try {
        const response = await fetch('/Masu%20Ko%20Jhol%28full%29/includes/analytics_data.php');
        const data = await response.json();
        
        // Update revenue chart
        if (data.revenue && data.revenue.length > 0) {
          revenueChart.data.labels = data.revenue.map(item => item.date);
          revenueChart.data.datasets[0].data = data.revenue.map(item => item.amount);
          revenueChart.update();
        }
        
        // Update orders chart
        if (data.orders && data.orders.length > 0) {
          ordersChart.data.labels = data.orders.map(item => item.date);
          ordersChart.data.datasets[0].data = data.orders.map(item => item.count);
          ordersChart.update();
        }
        
        // Update top items chart
        if (data.topItems && data.topItems.length > 0) {
          topItemsChart.data.labels = data.topItems.map(item => item.name);
          topItemsChart.data.datasets[0].data = data.topItems.map(item => item.count);
          topItemsChart.update();
        }
        
        // Update category chart
        if (data.categories && data.categories.length > 0) {
          categoryChart.data.labels = data.categories.map(item => item.category);
          categoryChart.data.datasets[0].data = data.categories.map(item => item.sales);
          categoryChart.update();
        }
      } catch (e) {
        console.error('Error loading analytics data:', e);
      }
    }
    
    // Load data immediately and then every 10 seconds
    loadAnalytics();
    setInterval(loadAnalytics, 10000);
    
    // Period selector
    document.getElementById('revenue-period').addEventListener('change', function() {
      loadAnalytics();
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>