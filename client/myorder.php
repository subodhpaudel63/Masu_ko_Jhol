<?php
ob_start();
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Check if user is logged in
$currentUser = getUserFromCookie();

// If user is not logged in, redirect to login
if (!$currentUser) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Please login to view your orders.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php?action=view_orders');
    exit;
}

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Logged-in user and profile image (from secure cookies)
$profileImg = 'assets/images/profile.jpg';
if (isset($_COOKIE['user_img'])) {
  $dec = decrypt($_COOKIE['user_img'], SECRET_KEY);
  if ($dec && is_string($dec)) {
    $candidate = ltrim($dec, '/');
    if (file_exists(__DIR__ . '/../' . $candidate)) {
      $profileImg = $candidate;
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <?php require_once __DIR__ . '/../config/bootstrap.php'; ?>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>" />
    <style>
        .status-badge { 
            border-radius: 999px; 
            padding: .25rem .6rem; 
            font-weight: 600; 
            font-size: 0.85rem; 
            display: inline-block;
            margin-bottom: 5px;
        }
        .status-confirmed { background:#e3f2fd; color:#0d47a1; }
        .status-shipping { background:#fff3cd; color:#8a6d3b; }
        .status-ongoing { background:#e8f5e9; color:#1b5e20; }
        .status-delivering { background:#fdecea; color:#b71c1c; }
        .status-cancelled { background:#ffebee; color:#c62828; }
        
        .order-date {
            font-size: 0.75rem;
            margin-top: 3px;
            display: block;
            clear: both;
        }
        
        .status-container {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        /* Order status timeline styling */
        .order-timeline {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            position: relative;
        }
        .order-timeline::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        .timeline-step {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #e0e0e0;
            z-index: 2;
            position: relative;
        }
        .timeline-step.active {
            background: #0d47a1;
        }
        .timeline-step.completed {
            background: #4caf50;
        }
        
        /* Status update animation */
        .status-updated {
            animation: statusChange 2s ease-in-out;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.5);
        }
        
        @keyframes statusChange {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); box-shadow: 0 0 15px rgba(0, 123, 255, 0.8); }
            100% { transform: scale(1); }
        }
        
        /* Add padding to prevent content from being hidden under navbar */
        .main-content {
          padding-top: 100px;
        }
        
        @media (max-width: 991px) {
          .main-content {
            padding-top: 80px;
          }
        }
    </style>
    <script>
        const POLL_MS = 3000; // Poll every 3 seconds for faster updates
        let previousOrders = {};
        
        async function fetchOrders() {
            try {
                const res = await fetch('../includes/orders_fetch.php', { credentials: 'same-origin' });
                const data = await res.json();
                if (!data.ok) {
                    document.getElementById('orders-body').innerHTML = `<tr><td colspan="6" class="text-center text-muted">Please login to view your orders.</td></tr>`;
                    return;
                }
                const tbody = document.getElementById('orders-body');
                if (!data.orders || data.orders.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No orders found.</td></tr>`;
                    previousOrders = {};
                    return;
                }
                
                // Check for status changes and create visual feedback
                const currentOrders = {};
                data.orders.forEach(order => {
                    currentOrders[order.order_id] = order.status;
                });
                
                tbody.innerHTML = data.orders.map(o => {
                    const previousStatus = previousOrders[o.order_id];
                    const statusChanged = previousStatus && previousStatus !== o.status;
                    const statusClass = `status-${o.status.toLowerCase()}`;
                    
                    return `
                    <tr>
                        <td>#${o.order_id}</td>
                        <td>${o.menu_name}</td>
                        <td>Rs. ${Number(o.price).toFixed(2)}</td>
                        <td>${o.quantity}</td>
                        <td>Rs. ${Number(o.total_price).toFixed(2)}</td>
                        <td>
                            <div class="status-container">
                                <span class="status-badge ${statusClass}" data-status="${o.status}" data-order-id="${o.order_id}" ${statusChanged ? 'data-status-changed="true"' : ''}>${o.status}</span>
                                <span class="order-date text-muted">Ordered: ${new Date(o.order_date).toLocaleDateString()}</span>
                            </div>
                        </td>
                    </tr>
                `;
                }).join('');
                
                // Update previous orders for next comparison
                previousOrders = currentOrders;
                
                // Add visual feedback for status changes
                document.querySelectorAll('[data-status-changed="true"]').forEach(badge => {
                    badge.classList.add('status-updated');
                    setTimeout(() => {
                        badge.classList.remove('status-updated');
                    }, 2000);
                });
                
                // Status classes are now handled during HTML generation
                // Only do cleanup if needed
                document.querySelectorAll('[data-status]').forEach(badge => {
                    const status = badge.getAttribute('data-status');
                    const statusLower = status.toLowerCase();
                    
                    // Ensure status badge class is present
                    if (!badge.classList.contains('status-badge')) {
                        badge.classList.add('status-badge');
                    }
                    
                    // Update status class if needed
                    const expectedClass = `status-${statusLower}`;
                    if (!badge.classList.contains(expectedClass)) {
                        // Remove old status classes
                        badge.className = badge.className.replace(/status-\w+/g, '');
                        // Add correct status class
                        badge.classList.add('status-badge', expectedClass);
                    }
                });
            } catch (e) {
                console.error(e);
            }
        }
    </script>
</head>
<body>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
      <?php if (isset($_SESSION['msg'])): $m=$_SESSION['msg']; unset($_SESSION['msg']); ?>
        <div class="toast show align-items-center border-0 <?php echo $m['type']==='success'?'text-bg-success':'text-bg-danger'; ?>" role="alert" aria-live="assertive" aria-atomic="true" style="<?php echo $m['type']==='success'?'background:#0f5132;':''; ?>" data-bs-delay="5000">
          <div class="d-flex">
            <div class="toast-body small fw-semibold"><?php echo htmlspecialchars($m['text']); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <div class="loader">
      <i class="fas fa-utensils loader-icone"></i>
      <p>Masu Ko Jhol</p>
      <div class="loader-ellipses">
        <span></span>
        <span></span>
        <span></span>
      </div>
    </div>
    
    <header>
      <div class="container header my-3 d-none d-lg-flex">
        <div class="logo">
          <a href="./index.php">
            <i class="fa fa-utensils me-3"></i>
            <h1 class="mb-0">Masu Ko Jhol</h1>
          </a>
        </div>
        <div class="menus">
          <ul class="d-flex mb-0">
            <li class="list-unstyled py-2">
              <a class="text-dark text-decoration-none text-uppercase p-4" href="./index.php"
                >Home</a
              >
            </li>
            <li class="list-unstyled py-2">
                <a class="text-dark text-decoration-none text-uppercase p-4" href="./aboutus.php"
                  >About</a
                >
            </li>
            <li class="list-unstyled py-2">
              <a class="text-dark text-decoration-none text-uppercase p-4" href="./menu.php"
                >Menu</a
              >
            </li>
            <li class="list-unstyled py-2">
              <a class="text-dark text-decoration-none text-uppercase p-4" href="./myorder.php"
                >My Order</a
            </li>
            <?php if (!$currentUser): ?>
              <li class="list-unstyled py-2">
                <a class="btn btn-gradient" href="<?php echo url('/login.php'); ?>">Login</a>
              </li>
            <?php endif; ?>
            <li class="list-unstyled py-2">
              <a class="text-dark text-decoration-none text-uppercase p-4" href="./contactus.php"
                >Contact</a
              >
            </li>
          </ul>
        </div>

        <div class="icons d-flex align-items-center">
          <a class="text-decoration-none" id="searchBtn" href="#"><i class="fa fa-search me-3"></i></a>
          <a class="text-decoration-none" id="shoppingbutton" href="./cart.php"><i class="fa fa-shopping-bag me-3"></i></a>
          <?php if ($currentUser): ?>
            <div class="dropdown">
              <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo url($profileImg); ?>" alt="profile" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                <li><h6 class="dropdown-header"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></h6></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="./update_password.php"><i class="fa fa-key me-2"></i>Update Password</a></li>
                <li><a class="dropdown-item" href="<?php echo url('includes/logout.php'); ?>"><i class="fa fa-right-from-bracket me-2"></i>Logout</a></li>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="d-flex justify-content-around py-3 align-items-center d-lg-none">
        <div id="hamburger">
          <i class="fa fa-2x fa-bars me-3"></i>
        </div>
        <div class="mobile-nav-logo">
          <div class="logo">
            <a href="./index.php">
              <i class="fa fa-utensils me-3"></i>
              <h1 class="mb-0">Masu Ko Jhol</h1>
            </a>
          </div>
        </div>
        <div class="mobile-nav-icons">
          <div class="icons">
            <a class="text-decoration-none" id="searchBtnMobile" href="#">
              <i class="fa fa-search me-3"></i>
            </a>
            <a class="text-decoration-none" id="shoppingbuttonMobile" href="#">
              <i class="fa fa-shopping-bag me-3"></i>
            </a>
          </div>
        </div>
        <div class="position-fixed w-75 bg-white h-100 top-0 start-0" id="mobile-menu">
          <div id="hamburger-cross" class="d-flex justify-content-end align-items-center py-2">
            <i class="fa fa-2x fa-times me-3"></i>
          </div>
          <div class="menus">
            <ul class="d-flex flex-column ps-2 mb-0 mt-4">
              <li class="list-unstyled py-2">
                <a class="text-dark text-decoration-none text-uppercase p-4" href="./index.php"
                  >Home</a
                >
              </li>
              <li class="list-unstyled py-2">
                <a class="text-dark text-decoration-none text-uppercase p-4" href="./aboutus.php"
                  >About</a
                >
              </li>
              <li class="list-unstyled py-2">
                <a class="text-dark text-decoration-none text-uppercase p-4" href="./menu.php"
                  >Menu</a
                >
              </li>
              <li class="list-unstyled py-2">
                <a class="text-dark text-decoration-none text-uppercase p-4" href="./myorder.php"
                  >My Order</a
                >
              </li>
              <?php if (!$currentUser): ?>
                <li class="list-unstyled py-2">
                  <a class="btn btn-gradient" href="<?php echo url('/login.php'); ?>">Login</a>
                </li>
              <?php endif; ?>
              <li class="list-unstyled py-2">
                <a class="text-dark text-decoration-none text-uppercase p-4" href="./contactus.php"
                  >Contact</a
                >
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

    <main class="container py-5 main-content">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0">My Orders</h2>
            <a href="./menu.php" class="btn btn-outline-secondary">Back to Menu</a>
        </div>
        <div class="table-responsive bg-light p-3 rounded shadow-sm">
            <table class="table align-middle">
<thead>
                    <tr>
                        <th>Order #</th>
                        <th>Item</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Status & Date</th>
                    </tr>
                </thead>
<tbody id="orders-body">
                    <tr><td colspan="6" class="text-center text-muted">Loading orders...</td></tr>
                </tbody>
            </table>
        </div>
    </main>
    <?php include_once __DIR__ . '/../footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4"
      crossorigin="anonymous"
    ></script>
    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <?php require_once __DIR__ . '/../config/bootstrap.php'; ?>
    <script src="<?php echo asset('js/script.js'); ?>"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            fetchOrders();
            setInterval(fetchOrders, POLL_MS);
            
            // Auto hide toast notifications after 5 seconds
            var toasts = document.querySelectorAll('.toast');
            toasts.forEach(function(toast) {
              var bsToast = new bootstrap.Toast(toast, {
                delay: 5000
              });
              bsToast.show();
            });
        });
    </script>
</body>
</html>