<?php
declare(strict_types=1);
ob_start();
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/admin_auth.php';

// Check if user is admin, if not redirect to admin login
$userType = null;
if (isset($_COOKIE['user_type'])) {
    require_once __DIR__ . '/../includes/auth_check.php';
    $userType = decrypt($_COOKIE['user_type'], SECRET_KEY);
}

if ($userType !== 'admin') {
    header('Location: /Masu%20Ko%20Jhol%28full%29/admin/login.php');
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard • Masu Ko Jhol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
    <style>
      :root{--bg:#0f1115;--panel:#161a22;--muted:#8b95a7;--text:#e8edf3;--brand:#ffb74d;--accent:#7c4dff;--danger:#ff6b6b;--success:#4caf50}
      html,body{height:100%}
      body{background:var(--bg);color:var(--text);}
      .sidebar{width:260px;background:linear-gradient(180deg,#0e1117 0%,#0b0e13 100%);position:fixed;inset:0 auto 0 0;box-shadow:0 0 0 1px #1f2330}
      .sidebar .nav-link{color:var(--muted)}
      .sidebar .nav-link.active,.sidebar .nav-link:hover{color:var(--text);background:#1a1f2b;border-radius:10px}
      .content{margin-left:0;min-height:100vh}
      @media (min-width: 992px) {
        .content{margin-left:260px;}
      }
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
      /* Mobile menu styles */
      #mobile-menu { background: linear-gradient(180deg, #0e1117 0%, #0b0e13 100%); box-shadow: 0 0 0 1px #1f2330; transform: translateX(-100%); transition: transform 0.3s ease-in-out; }
      #mobile-menu.show { transform: translateX(0); }
      #mobile-menu .nav-link { color: var(--muted); }
      #mobile-menu .nav-link.active, #mobile-menu .nav-link:hover { color: var(--text); background: #1a1f2b; border-radius: 10px; }
    </style>
  </head>
  <body>
    <div class="sidebar d-flex flex-column p-3 d-none d-lg-flex">
      <h5 class="mb-4 d-flex align-items-center gap-2"><i class="bi bi-fire text-warning"></i> Masu Admin</h5>
      <nav class="nav nav-pills flex-column gap-1">
        <a class="nav-link active" href="#" data-page="dashboard"><i class="bi bi-speedometer2 me-2"></i><span data-i18n="dashboard">Dashboard</span></a>
        <a class="nav-link" href="#" data-page="orders"><i class="bi bi-bag-check me-2"></i><span data-i18n="orders">Orders</span></a>
        <a class="nav-link" href="#" data-page="menu"><i class="bi bi-card-checklist me-2"></i><span data-i18n="menu">Menu</span></a>
        <a class="nav-link" href="#" data-page="customers"><i class="bi bi-people me-2"></i><span data-i18n="customers">Customers</span></a>
        <a class="nav-link" href="#" data-page="bookings"><i class="bi bi-calendar-check me-2"></i><span data-i18n="bookings">Table Bookings</span></a>
        <a class="nav-link" href="#" data-page="analytics"><i class="bi bi-graph-up-arrow me-2"></i><span data-i18n="analytics">Analytics</span></a>
        <a class="nav-link" href="#" data-page="settings"><i class="bi bi-gear me-2"></i><span data-i18n="settings">Settings</span></a>
      </nav>
      <div class="mt-auto small text-muted">© <span id="y"></span> Masu Ko Jhol</div>
    </div>

    <div class="content">
      <div class="topbar d-flex align-items-center justify-content-between px-3 px-lg-4 py-3">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-list d-lg-none" id="mobile-menu-toggle" style="cursor: pointer; font-size: 1.5rem;"></i>
          <div class="input-group input-group-sm" style="width:320px;">
            <span class="input-group-text bg-transparent border-secondary-subtle text-secondary"><i class="bi bi-search"></i></span>
            <input id="search" type="text" class="form-control bg-transparent border-secondary-subtle text-light" placeholder="Search…" />
          </div>
        </div>
        <div class="d-flex align-items-center gap-2">
          <select id="lang" class="form-select form-select-sm lang-select">
            <option value="en">English</option>
            <option value="ne">नेपाली</option>
          </select>
          <a class="btn btn-sm btn-outline-light" href="/Masu%20Ko%20Jhol%28full%29/includes/logout.php"><i class="bi bi-box-arrow-right me-1"></i><span data-i18n="logout">Logout</span></a>
        </div>
      </div>
      
      <!-- Mobile Menu -->
      <div id="mobile-menu" class="position-fixed top-0 start-0 w-75 h-100 d-lg-none" style="z-index: 1000;">
        <div class="d-flex flex-column h-100 p-3">
          <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <h5 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-fire text-warning"></i> Masu Admin</h5>
            <i class="bi bi-x-lg" id="mobile-menu-close" style="cursor: pointer; font-size: 1.5rem;"></i>
          </div>
          <nav class="nav nav-pills flex-column gap-1 flex-grow-1">
            <a class="nav-link active" href="#" data-page="dashboard"><i class="bi bi-speedometer2 me-2"></i><span data-i18n="dashboard">Dashboard</span></a>
            <a class="nav-link" href="#" data-page="orders"><i class="bi bi-bag-check me-2"></i><span data-i18n="orders">Orders</span></a>
            <a class="nav-link" href="#" data-page="menu"><i class="bi bi-card-checklist me-2"></i><span data-i18n="menu">Menu</span></a>
            <a class="nav-link" href="#" data-page="customers"><i class="bi bi-people me-2"></i><span data-i18n="customers">Customers</span></a>
            <a class="nav-link" href="#" data-page="bookings"><i class="bi bi-calendar-check me-2"></i><span data-i18n="bookings">Table Bookings</span></a>
            <a class="nav-link" href="#" data-page="analytics"><i class="bi bi-graph-up-arrow me-2"></i><span data-i18n="analytics">Analytics</span></a>
            <a class="nav-link" href="#" data-page="settings"><i class="bi bi-gear me-2"></i><span data-i18n="settings">Settings</span></a>
          </nav>
          <div class="mt-auto small text-muted pt-3 border-top border-secondary">© <span id="mobile-copyright-year"></span> Masu Ko Jhol</div>
        </div>
      </div>
      <!-- Mobile Menu End -->

      <main class="p-3 p-lg-4">
        <div id="dashboard" class="page">
          <div class="row g-3 g-lg-4">
            <div class="col-6 col-lg-3">
              <div class="card p-3">
                <div class="metric"><div class="icon bg-dark-subtle"><i class="bi bi-currency-rupee text-warning"></i></div>
                  <div>
                    <div class="small text-secondary" data-i18n="revenue">Today's Revenue</div>
                    <div class="h4 mb-0" id="m-revenue">—</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="card p-3">
                <div class="metric"><div class="icon bg-dark-subtle"><i class="bi bi-bag text-info"></i></div>
                  <div>
                    <div class="small text-secondary" data-i18n="ordersToday">Today's Orders</div>
                    <div class="h4 mb-0" id="m-orders">—</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="card p-3">
                <div class="metric"><div class="icon bg-dark-subtle"><i class="bi bi-people text-success"></i></div>
                  <div>
                    <div class="small text-secondary" data-i18n="customers">Customers</div>
                    <div class="h4 mb-0" id="m-customers">—</div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-6 col-lg-3">
              <div class="card p-3">
                <div class="metric"><div class="icon bg-dark-subtle"><i class="bi bi-cash-coin text-danger"></i></div>
                  <div>
                    <div class="small text-secondary" data-i18n="expenses">Avg. Expense</div>
                    <div class="h4 mb-0" id="m-expense">—</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-3 g-lg-4 mt-1">
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0" data-i18n="salesBreakdown">Sales Breakdown</h6>
                  <select id="sales-period" class="form-select form-select-sm w-auto bg-transparent text-light border-secondary-subtle">
                    <option value="month">Monthly</option>
                    <option value="week">Weekly</option>
                  </select>
                </div>
                <canvas id="chartDonut" height="200"></canvas>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0" data-i18n="weeklyOrders">Weekly Orders</h6>
                  <select id="orders-period" class="form-select form-select-sm w-auto bg-transparent text-light border-secondary-subtle">
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                  </select>
                </div>
                <canvas id="chartBars" height="200"></canvas>
              </div>
            </div>
          </div>

          <div class="row g-3 g-lg-4 mt-1">
            <div class="col-lg-12">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0" data-i18n="monthlyRevenue">Monthly Revenue Trend</h6>
                </div>
                <canvas id="chartRevenue" height="100"></canvas>
              </div>
            </div>
          </div>

          <div class="row g-3 g-lg-4 mt-1">
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0" data-i18n="latestOrders">Latest Orders</h6>
                </div>
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Address</th>
                        <th>Time</th>
                      </tr>
                    </thead>
                    <tbody id="tbl-orders">
                      <tr><td colspan="6" class="text-center text-muted">Loading...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h6 class="mb-0" data-i18n="recentCustomers">Recent Customers</h6>
                </div>
                <div class="table-responsive">
                  <table class="table table-sm">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Email</th>
                        <th>Type</th>
                        <th>Joined</th>
                      </tr>
                    </thead>
                    <tbody id="tbl-customers">
                      <tr><td colspan="4" class="text-center text-muted">Loading...</td></tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>

          <div class="card mt-4 p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="mb-0" data-i18n="trending">Trending Orders</h6>
              <a href="/Masu%20Ko%20Jhol%28full%29/admin/myorder.php" class="btn btn-sm btn-gradient" data-i18n="viewAll">View All</a>
            </div>
            <div class="row g-3" id="trending"></div>
          </div>
        </div>

        <div id="orders" class="page d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0" data-i18n="orders">Orders</h5>
            <a href="/Masu%20Ko%20Jhol%28full%29/admin/myorder.php" class="btn btn-sm btn-gradient" data-i18n="manage">Manage</a>
          </div>
          <div class="card p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th data-i18n="item">Item</th><th data-i18n="customer">Customer</th><th data-i18n="status">Status</th><th>Address</th><th class="text-end" data-i18n="actions">Actions</th></tr></thead>
                <tbody id="tbl-orders-main"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="menu" class="page d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0" data-i18n="menu">Menu</h5>
            <a href="/Masu%20Ko%20Jhol%28full%29/admin/menu.php" class="btn btn-sm btn-gradient" data-i18n="manage">Manage</a>
          </div>
          <div class="card p-3">
            <div class="row g-3" id="menu-cards"></div>
          </div>
        </div>

        <div id="customers" class="page d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0" data-i18n="customers">Customers</h5>
            <a href="/Masu%20Ko%20Jhol%28full%29/admin/users.php" class="btn btn-sm btn-gradient" data-i18n="manage">Manage</a>
          </div>
          <div class="card p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th data-i18n="email">Email</th><th data-i18n="role">Role</th><th class="text-end" data-i18n="actions">Actions</th></tr></thead>
                <tbody id="tbl-customers"></tbody>
              </table>
            </div>
          </div>
        </div>

        <div id="analytics" class="page d-none">
          <h5 class="mb-3" data-i18n="analytics">Analytics</h5>
          <div class="row g-3 g-lg-4">
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0" data-i18n="salesBreakdown">Sales Breakdown</h6>
                  <a href="/Masu%20Ko%20Jhol%28full%29/admin/analytics.php" class="btn btn-sm btn-gradient">View Detailed Analytics</a>
                </div>
                <canvas id="chartDonut" height="200"></canvas>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0" data-i18n="weeklyOrders">Weekly Orders</h6>
                  <a href="/Masu%20Ko%20Jhol%28full%29/admin/analytics.php" class="btn btn-sm btn-gradient">View Detailed Analytics</a>
                </div>
                <canvas id="chartBars" height="200"></canvas>
              </div>
            </div>
          </div>
          
          <div class="row g-3 g-lg-4 mt-1">
            <div class="col-lg-12">
              <div class="card p-3">
                <div class="d-flex justify-content-between align-items-center">
                  <h6 class="mb-0" data-i18n="monthlyRevenue">Monthly Revenue Trend</h6>
                  <a href="/Masu%20Ko%20Jhol%28full%29/admin/analytics.php" class="btn btn-sm btn-gradient">View Detailed Analytics</a>
                </div>
                <canvas id="chartRevenue" height="100"></canvas>
              </div>
            </div>
          </div>
        </div>

        <div id="settings" class="page d-none">
          <h5 class="mb-3" data-i18n="settings">Settings</h5>
          <div class="card p-3">
            <form id="form-settings" class="row g-3">
              <div class="col-md-6">
                <label class="form-label" data-i18n="siteName">Site Name</label>
                <input class="form-control bg-transparent text-light border-secondary" name="site_name" value="Masu Ko Jhol" />
              </div>
              <div class="col-md-6">
                <label class="form-label" data-i18n="defaultLang">Default Language</label>
                <select class="form-select bg-transparent text-light border-secondary" name="default_lang"><option value="en">English</option><option value="ne">नेपाली</option></select>
              </div>
              <div class="col-12"><button type="submit" class="btn btn-gradient" data-i18n="save">Save</button></div>
            </form>
          </div>
        </div>
        
        <div id="bookings" class="page d-none">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0" data-i18n="bookings">Table Bookings</h5>
            <a href="/Masu%20Ko%20Jhol%28full%29/admin/bookings.php" class="btn btn-sm btn-gradient" data-i18n="manage">Manage</a>
          </div>
          <div class="card p-0">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>#</th><th data-i18n="name">Name</th><th data-i18n="email">Email</th><th data-i18n="date">Date & Time</th><th data-i18n="people">People</th><th data-i18n="status">Status</th></tr></thead>
                <tbody id="tbl-bookings"></tbody>
              </table>
            </div>
          </div>
        </div>
      </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
      document.getElementById('y').textContent = new Date().getFullYear();
      document.querySelectorAll('.sidebar .nav-link').forEach(l=>{
        l.addEventListener('click',e=>{e.preventDefault();document.querySelector('.sidebar .nav-link.active')?.classList.remove('active');l.classList.add('active');const pages = document.querySelectorAll('.page');pages.forEach(p=>p.classList.add('d-none'));document.getElementById(l.dataset.page).classList.remove('d-none');});
      });

      const i18n = {
        en: {dashboard:'Dashboard',orders:'Orders',menu:'Menu',customers:'Customers',bookings:'Table Bookings',analytics:'Analytics',settings:'Settings',logout:'Logout',revenue:"Today's Revenue",ordersToday:"Today's Orders",expenses:'Avg. Expense',salesBreakdown:'Sales Breakdown',weeklyOrders:'Weekly Orders',monthlyRevenue:'Monthly Revenue Trend',trending:'Trending Orders',latestOrders:'Latest Orders',recentCustomers:'Recent Customers',viewAll:'View All',manage:'Manage',item:'Item',customer:'Customer',name:'Name',date:'Date & Time',people:'People',status:'Status',actions:'Actions',email:'Email',role:'Role',siteName:'Site Name',defaultLang:'Default Language',save:'Save'},
        ne: {dashboard:'ड्यासबोर्ड',orders:'अर्डर',menu:'मेनु',customers:'ग्राहक',bookings:'टेबल बुकिंग',analytics:'विश्लेषण',settings:'सेटिङ',logout:'लगआउट',revenue:'आजको आम्दानी',ordersToday:'आजका अर्डर',expenses:'औसत खर्च',salesBreakdown:'बिक्री विवरण',weeklyOrders:'साप्ताहिक अर्डर',monthlyRevenue:'मासिक आम्दानी प्रवृत्ति',trending:'ट्रेन्डिङ अर्डर',latestOrders:'नवीनतम अर्डर',recentCustomers:'हालैका ग्राहक',viewAll:'सबै हेर्नुहोस्',manage:'प्रबन्ध',item:'वस्तु',customer:'ग्राहक',name:'नाम',date:'मिति र समय',people:'मान्छे',status:'स्थिति',actions:'कार्य',email:'इमेल',role:'भूमिका',siteName:'साइट नाम',defaultLang:'पूर्वनिर्धारित भाषा',save:'सेभ'}
      };
      function applyLang(lang){document.querySelectorAll('[data-i18n]').forEach(el=>{const k=el.getAttribute('data-i18n'); if(i18n[lang][k]) el.textContent=i18n[lang][k];});}
      document.getElementById('lang').addEventListener('change',e=>applyLang(e.target.value));
      applyLang('en');

      // Charts
      const donut = new Chart(document.getElementById('chartDonut'), {type:'doughnut', data:{labels:['Dine-in','Delivery','Takeaway'], datasets:[{data:[40,35,25], backgroundColor:['#7c4dff','#ffb74d','#26a69a'], borderWidth:0}]}, options:{plugins:{legend:{labels:{color:'#c9d1e3'}}}}});
      const bars = new Chart(document.getElementById('chartBars'), {type:'bar', data:{labels:['Sat','Sun','Mon','Tue','Wed','Thu','Fri'], datasets:[{label:'Orders', data:[120,140,180,265,190,170,130], backgroundColor:'#7c4dff'}]}, options:{scales:{x:{ticks:{color:'#c9d1e3'}}, y:{ticks:{color:'#c9d1e3'}}}}});
      const revenueChart = new Chart(document.getElementById('chartRevenue'), {type:'line', data:{labels:[], datasets:[{label:'Revenue', data:[], borderColor:'#7c4dff', backgroundColor:'rgba(124, 77, 255, 0.1)', fill:true}]}, options:{scales:{x:{ticks:{color:'#c9d1e3'}}, y:{ticks:{color:'#c9d1e3'}}}}});

      // Live stats fetch - updated to refresh every 5 seconds for more real-time updates
      async function loadStats(){
        try{
          const r = await fetch('/Masu%20Ko%20Jhol%28full%29/includes/admin_stats.php');
          const j = await r.json();
          document.getElementById('m-revenue').textContent = 'Rs. '+(j.todayRevenue??0);
          document.getElementById('m-orders').textContent = j.todayOrders??0;
          document.getElementById('m-customers').textContent = j.customers??0;
          document.getElementById('m-expense').textContent = 'Rs. '+(j.avgExpense??0);
          
          // Update charts with real-time data
          if (j.weeklyOrders && j.weeklyOrders.length > 0) {
            const dates = j.weeklyOrders.map(item => item.date);
            const orderCounts = j.weeklyOrders.map(item => parseInt(item.orders));
            
            // Update bar chart
            bars.data.labels = dates;
            bars.data.datasets[0].data = orderCounts;
            bars.update();
          }
          
          // Update monthly revenue chart
          if (j.monthlyRevenue && j.monthlyRevenue.length > 0) {
            const months = j.monthlyRevenue.map(item => item.month);
            const revenues = j.monthlyRevenue.map(item => parseInt(item.revenue));
            
            // Update revenue chart
            revenueChart.data.labels = months;
            revenueChart.data.datasets[0].data = revenues;
            revenueChart.update();
          }
          
          // Update trending items
          const wrap=document.getElementById('trending');
          wrap.innerHTML='';
          (j.trending||[]).slice(0,6).forEach(o=>{
            const col=document.createElement('div'); col.className='col-6 col-lg-2';
            col.innerHTML=`<div class="card p-2 h-100"><img class="rounded mb-2" src="/${o.image}" style="aspect-ratio:1/1;object-fit:cover;"/><div class="small">${o.menu_name}</div><div class="small text-secondary">Rs. ${o.menu_price}</div><div class="small text-muted">Ordered ${o.qty} times</div></div>`;
            wrap.appendChild(col);
          });
        }catch(e){/* silent */}
      }
      
      // Load stats immediately and then every 5 seconds (instead of 10)
      loadStats(); 
      setInterval(loadStats, 5000);

      // Lightweight previews for tables/cards (read-only snapshots)
      async function loadSnapshots(){
        try{
          const r = await fetch('/Masu%20Ko%20Jhol%28full%29/includes/admin_stats.php?snap=1');
          const j = await r.json();
          
          // Update latest orders with timestamps
          const tO=document.getElementById('tbl-orders'); 
          tO.innerHTML=''; 
          (j.latestOrders||[]).forEach((o,i)=>{
            const date = new Date(o.created_at).toLocaleTimeString();
            // Truncate address if too long
            const truncatedAddress = o.address.length > 20 ? o.address.substring(0, 20) + '...' : o.address;
            tO.innerHTML+=`<tr><td>${o.order_id}</td><td>${o.menu_name}</td><td>${o.email}</td><td><span class="badge bg-secondary badge-cat">${o.status}</span></td><td>Rs. ${o.total_price}</td><td>${truncatedAddress}</td><td>${date}</td><td class="text-end"><a href="/Masu%20Ko%20Jhol%28full%29/admin/myorder.php" class="btn btn-sm btn-outline-light">Edit</a></td></tr>`;
          });
          
          // Populate orders page table
          const tOMain=document.getElementById('tbl-orders-main'); 
          tOMain.innerHTML=''; 
          (j.latestOrders||[]).forEach((o,i)=>{
            // Truncate address if too long
            const truncatedAddress = o.address.length > 20 ? o.address.substring(0, 20) + '...' : o.address;
            tOMain.innerHTML+=`<tr><td>${o.order_id}</td><td>${o.menu_name}</td><td>${o.email}</td><td><span class="badge bg-secondary badge-cat">${o.status}</span></td><td>${truncatedAddress}</td><td class="text-end"><a href="/Masu%20Ko%20Jhol%28full%29/admin/myorder.php" class="btn btn-sm btn-outline-light">Edit</a></td></tr>`;
          });
          
          const tC=document.getElementById('tbl-customers'); 
          tC.innerHTML=''; 
          (j.customersList||[]).forEach((c,i)=>{
            const date = new Date(c.created_at).toLocaleDateString();
            tC.innerHTML+=`<tr><td>${c.id}</td><td>${c.email}</td><td>${c.user_type}</td><td>${date}</td><td class="text-end"><a href="/Masu%20Ko%20Jhol%28full%29/admin/users.php" class="btn btn-sm btn-outline-light">Edit</a></td></tr>`;
          });
          
          const tB=document.getElementById('tbl-bookings'); 
          tB.innerHTML=''; 
          (j.bookings||[]).slice(0,5).forEach((b,i)=>{
            tB.innerHTML+=`<tr><td>${b.id}</td><td>${b.name}</td><td>${b.email}</td><td>${b.booking_date} ${b.booking_time}</td><td>${b.people}</td><td><span class="badge bg-secondary badge-cat">${b.status}</span></td></tr>`;
          });
          
          const mC=document.getElementById('menu-cards'); 
          mC.innerHTML=''; 
          (j.menu||[]).slice(0,6).forEach(m=>{
            mC.innerHTML+=`<div class="col-6 col-lg-4"><div class="card h-100 p-2"><img src="/${m.menu_image}" class="rounded mb-2" style="aspect-ratio:16/9;object-fit:cover;"><div class="d-flex justify-content-between align-items-center"><div class="small">${m.menu_name}</div><span class="badge bg-secondary">Rs. ${m.menu_price}</span></div></div></div>`;
          });
        }catch(e){/* silent */}
      }
      
      // Load snapshots immediately and then every 5 seconds
      loadSnapshots(); 
      setInterval(loadSnapshots, 5000);
      
      // Mobile menu functionality
      const mobileMenu = document.getElementById('mobile-menu');
      const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
      const mobileMenuClose = document.getElementById('mobile-menu-close');
      
      // Set current year for copyright
      document.getElementById('mobile-copyright-year').textContent = new Date().getFullYear();
      
      // Toggle mobile menu
      if (mobileMenuToggle && mobileMenu) {
        mobileMenuToggle.addEventListener('click', () => {
          mobileMenu.classList.add('show');
        });
      }
      
      // Close mobile menu
      if (mobileMenuClose && mobileMenu) {
        mobileMenuClose.addEventListener('click', () => {
          mobileMenu.classList.remove('show');
        });
      }
      
      // Close mobile menu when clicking outside
      if (mobileMenu) {
        mobileMenu.addEventListener('click', (e) => {
          if (e.target === mobileMenu) {
            mobileMenu.classList.remove('show');
          }
        });
      }
      
      // Mobile menu navigation
      document.querySelectorAll('#mobile-menu .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
          e.preventDefault();
          
          // Remove active class from all links in both menus
          document.querySelectorAll('.sidebar .nav-link, #mobile-menu .nav-link').forEach(l => {
            l.classList.remove('active');
          });
          
          // Add active class to clicked link in both menus
          this.classList.add('active');
          const page = this.dataset.page;
          
          // Also update the desktop menu to match
          const desktopLink = document.querySelector(`.sidebar .nav-link[data-page="${page}"]`);
          if (desktopLink) {
            desktopLink.classList.add('active');
          }
          
          // Show the selected page
          const pages = document.querySelectorAll('.page');
          pages.forEach(p => p.classList.add('d-none'));
          document.getElementById(page).classList.remove('d-none');
          
          // Close mobile menu
          if (mobileMenu) {
            mobileMenu.classList.remove('show');
          }
        });
      });
    </script>
  </body>
</html>


