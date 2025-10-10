<?php
ob_start();
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Check if user is logged in
$currentUser = getUserFromCookie();

// If user is not logged in, redirect to login
if (!$currentUser) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Please login to view your cart.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php?action=add_to_cart');
    exit;
}

try {
  $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (PDOException $e) {
  die("Database connection failed: " . $e->getMessage());
}

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Calculate cart total and prepare cart items
$cartItems = $_SESSION['cart'];
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
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
    <title>Cart & Checkout</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <?php require_once __DIR__ . '/../config/bootstrap.php'; ?>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>" />
    <style>
        .btn-orange { background:#ff6a00; color:#fff; border-radius:10px; border:1px solid #ff6a00; }
        .btn-orange:hover { background:#e65f00; color:#fff; border-color:#e65f00; }
        .btn-outline-orange { background:#fff; color:#ff6a00; border:1px solid #ff6a00; border-radius:10px; }
        .btn-outline-orange:hover { background:#ff6a00; color:#fff; }
        /* Keep alert visible below fixed header */
        .cart-alert { position: sticky; top: 90px; z-index: 1200; }
        @media (max-width: 991.98px) { .cart-alert { top: 70px; } }
        
        /* Custom button styles */
        .nav-button {
          background-color: #ff0000;
          color: white;
          border: none;
          padding: 8px 20px;
          border-radius: 30px;
          font-weight: 500;
          transition: all 0.3s ease;
          text-decoration: none;
          display: inline-block;
          margin-left: 10px;
        }
        
        .nav-button:hover {
          background-color: #cc0000;
          color: white;
          text-decoration: none;
        }
        
        .nav-button-outline {
          background-color: transparent;
          color: #ff0000;
          border: 2px solid #ff0000;
          padding: 6px 18px;
          border-radius: 30px;
          font-weight: 500;
          transition: all 0.3s ease;
          text-decoration: none;
          display: inline-block;
          margin-left: 10px;
        }
        
        .nav-button-outline:hover {
          background-color: #ff0000;
          color: white;
          text-decoration: none;
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
</head>
<body class="bg-white">
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
      <?php if (!empty($_SESSION['msg'])): $m=$_SESSION['msg']; unset($_SESSION['msg']); if ($m['type']==='success'): ?>
        <div class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background:#0f5132;">
          <div class="d-flex">
            <div class="toast-body small fw-semibold">✔ <?php echo htmlspecialchars($m['text']); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      <?php elseif ($m['type']==='error'): ?>
        <div class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body small fw-semibold">✖ <?php echo htmlspecialchars($m['text']); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      <?php endif; endif; ?>
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
                <li><a class="dropdown-item" href="./update_password.php"><i class="bi bi-key me-2"></i>Update Password</a></li>
                <li><a class="dropdown-item" href="<?php echo url('../includes/logout.php'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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
            <a class="text-decoration-none" id="shoppingbuttonMobile" href="./cart.php">
              <i class="fa fa-shopping-bag me-3"></i>
            </a>
          </div>
        </div>
        <div class="position-fixed w-75 bg-white h-100 top-0 start-0" id="mobile-menu">
          <div id="hamburger-cross" class="d-flex justify-content-end align-items-center py-2">
            <i class="fa fa-2x fa-plus me-3"></i>
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
    <h2 class="mb-4">Your Cart</h2>
    <form action="../includes/cart.php?action=update" method="post" class="mb-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Price</th>
                        <th style="width:140px">Quantity</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($cartItems)): ?>
                        <tr><td colspan="5" class="text-center text-muted">Your cart is empty.</td></tr>
                    <?php else: foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['menu_name']); ?>" class="rounded me-3" style="width:64px;height:64px;object-fit:cover;">
                                    <div>
                                        <div class="fw-bold"><?php echo htmlspecialchars($item['menu_name']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>₹<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" name="quantities[<?php echo intval($item['menu_id']); ?>]" value="<?php echo intval($item['quantity']); ?>" min="0" class="form-control" />
                            </td>
                            <td>₹<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            <td>
                                <a href="../includes/cart.php?action=remove&menu_id=<?php echo intval($item['menu_id']); ?>" class="btn btn-sm btn-outline-danger">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
        <div class="d-flex justify-content-between">
            <a href="./menu.php" class="btn btn-outline-orange">Continue Shopping</a>
            <div>
                <a href="../includes/cart.php?action=clear" class="btn btn-outline-danger me-2">Clear Cart</a>
                <button type="submit" class="btn btn-orange">Update Cart</button>
            </div>
        </div>
    </form>

    <div class="row g-4 align-items-start">
        <div class="col-lg-6">
            <div class="p-4 bg-light rounded shadow-sm">
                <h4 class="mb-3">Order Summary</h4>
                <div class="d-flex justify-content-between">
                    <span>Subtotal</span>
                    <strong>₹<?php echo number_format($subtotal, 2); ?></strong>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="p-4 bg-light rounded shadow-sm">
                <h4 class="mb-3">Checkout</h4>
                <form action="../includes/cart.php?action=checkout" method="post" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="<?php echo $_SESSION['email'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mobile</label>
                        <input type="tel" name="mobile" pattern="[0-9]{10}" maxlength="10" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Delivery Address</label>
                        <textarea name="address" rows="3" class="form-control" required></textarea>
                    </div>
                    <div class="col-12 d-grid">
                        <button type="submit" class="btn btn-orange">Place Order</button>
                    </div>
                </form>
            </div>
        </div>
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
        // Auto-update cart totals when quantity changes
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInputs = document.querySelectorAll('input[name^="quantities["]');
            const updateCartForm = document.querySelector('form[action="../includes/cart.php?action=update"]');
            
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    // Auto-submit the update form when quantity changes
                    updateCartForm.submit();
                });
            });
        });
    </script>
</body>
</html>