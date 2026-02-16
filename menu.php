<?php
session_start();
include_once __DIR__ . '/includes/db.php';
include_once __DIR__ . '/includes/auth_check.php';

// Check if user is logged in
$user = getUserFromCookie();

// We'll handle the redirect in JavaScript now instead of immediately redirecting
// If user is not logged in, we'll show a toast notification

@ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);

// Use the same ordered categories as client/menu.php
$wantedCats = ['starter','breakfast','lunch','dinner'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  
    <!-- Favicons -->
    <link href="assets/img/logo.png" rel="icon">
    <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com" rel="preconnect">
    <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Amatic+SC:wght@400;700&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Vendor CSS Files -->
    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/vendor/aos/aos.css" rel="stylesheet">
    <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
    <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

    <?php require_once __DIR__ . '/config/bootstrap.php'; ?>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>" />
    
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Masu Ko Jhol | Menu</title>
    <!--  -->
    <style>
    body { background:#fff; }
    .menu .card { border-radius: 16px; overflow: hidden; border: 0; background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.08); transition: transform .2s ease, box-shadow .2s ease; }
    .menu .card:hover { transform: translateY(-4px); box-shadow: 0 16px 36px rgba(0,0,0,.12); }
    .menu .card-img-top { height: 220px; object-fit: cover; }
    .menu .card-title { font-weight: 800; color: #d32f2f; }
    .menu .card-text { color:#6c757d; }
    .menu .price { color:#212529; font-weight:700; }
    .btn-orange { background:#ff6a00; color:#fff; border-radius:10px; border:1px solid #ff6a00; white-space:nowrap; font-weight:600; }
    .btn-orange:hover { background:#e65f00; color:#fff; border-color:#e65f00; }
    .btn.btn-orange { background:#ff6a00 !important; border-color:#ff6a00 !important; color:#fff !important; }
    .btn.btn-orange:hover { background:#e65f00 !important; border-color:#e65f00 !important; color:#fff !important; }
    
    /* Toast styling for better visibility */
    .toast-success {
        background-color: #0f5132 !important; /* Dark green */
        border-color: #0f5132 !important;
        color: white !important;
    }
    
    .toast-error {
        background-color: #842029 !important; /* Dark red */
        border-color: #842029 !important;
        color: white !important;
    }
    
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
    
    /* Modal styling */
    .login-modal .modal-content {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    .login-modal .modal-header {
        background: linear-gradient(135deg, #ff6a00, #d32f2f);
        color: white;
        border-top-left-radius: 15px;
        border-top-right-radius: 15px;
        border: none;
    }
    
    .login-modal .btn-login {
        background: linear-gradient(135deg, #ff6a00, #d32f2f);
        border: none;
        color: white;
        padding: 10px;
        font-weight: 600;
    }
    
    .login-modal .btn-login:hover {
        background: linear-gradient(135deg, #e65f00, #c62828);
        color: white;
    }
    </style>
</head>
<body>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
      <?php if (isset($_SESSION['msg'])): $m=$_SESSION['msg']; unset($_SESSION['msg']); ?>
        <div class="toast show align-items-center border-0 <?php echo $m['type']==='success'?'toast-success':'toast-error'; ?>" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
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
    
    <header class="bg-white">
      <div class="container header my-3 d-none d-lg-flex">
        <div class="logo">
          <a href="./index.php">
            <i class="fa fa-utensils me-3 text-dark"></i>
            <h1 class="mb-0 text-dark">Masu Ko Jhol</h1>
          </a>
        </div>
        <div class="menus">
          <ul class="d-flex mb-0">
            <li class="list-unstyled py-2">
              <a
                class="text-decoration-none text-uppercase p-4 text-dark"
                href="./index.php"
                >Home</a
              >
            </li>
            <li class="list-unstyled py-2">
              <a
                class="text-decoration-none text-uppercase p-4 text-dark"
                href="./aboutus.php"
                >About</a
              >
            </li>
           
            <li class="list-unstyled py-2">
              <a
                class="text-decoration-none text-uppercase p-4 text-dark"
                href="./menu.php"
                >Menu</a
              >
            </li>
            <li class="list-unstyled py-2">
              <a
                class="text-decoration-none text-uppercase p-4 text-dark"
                href="./contactus.php"
                >Contact</a
              >
            </li>
          </ul>
        </div>
        <div class="icons d-flex align-items-center">
          <a class="text-decoration-none" id="searchBtn" href="#">
            <i class="fa fa-search me-3 text-dark"></i>
          </a>
          <a class="text-decoration-none" id="shoppingbutton" href="./client/cart.php">
            <i class="fa fa-shopping-bag me-3 text-dark"></i>
          </a>
          <!-- Login and Signup buttons -->
          <div class="d-flex align-items-center">
            <a href="./login.php" class="nav-button">Login</a>
            <a href="./register.php" class="nav-button-outline">Sign Up</a>
          </div>
        </div>
      </div>

      <div
        class="d-flex justify-content-around py-3 align-items-center d-lg-none"
      >
        <div id="hamburger">
          <i class="fa fa-2x fa-bars me-3 text-dark"></i>
        </div>
        <div class="mobile-nav-logo">
          <div class="logo">
            <a href="./index.php">
              <i class="fa fa-utensils me-3 text-dark"></i>
              <h1 class="mb-0 text-dark">Masu Ko Jhol</h1>
            </a>
          </div>
        </div>
        <div class="mobile-nav-icons">
          <div class="icons">
            <a class="text-decoration-none" id="searchBtnMobile" href="#">
              <i class="fa fa-search me-3 text-dark"></i>
            </a>
            <a class="text-decoration-none" id="shoppingbuttonMobile" href="./client/cart.php">
              <i class="fa fa-shopping-bag me-3 text-dark"></i>
            </a>
          </div>
        </div>
        <div
          class="position-fixed w-75 bg-white h-100 top-0 start-0"
          id="mobile-menu"
        >
          <div
            id="hamburger-cross"
            class="d-flex justify-content-end align-items-center py-2"
          >
            <i class="fa fa-2x fa-times me-3"></i>
          </div>
          <div class="menus">
            <ul class="d-flex flex-column ps-2 mb-0 mt-4">
              <li class="list-unstyled py-2">
                <a
                  class="text-dark text-decoration-none text-uppercase p-4"
                  href="./index.php"
                  >Home</a
                >
              </li>
              <li class="list-unstyled py-2">
                <a
                  class="text-dark text-decoration-none text-uppercase p-4"
                  href="./aboutus.php"
                  >About</a
                >
              </li>
              
              </li>
              <li class="list-unstyled py-2">
                <a
                  class="text-dark text-decoration-none text-uppercase p-4"
                  href="./menu.php"
                  >Menu</a
                >
              </li>
              <li class="list-unstyled py-2">
                <a
                  class="text-dark text-decoration-none text-uppercase p-4"
                  href="./contactus.php"
                  >Contact</a
                >
              </li>
            </ul>
          </div>
        </div>
      </div>
    </header>

<section id="menu" class="menu section">
    <div class="container section-title py-4">
        <h2>Our Menu</h2>
        <p><span>Check Our</span> <span class="description-title">Yummy Menu</span></p>
    </div>
    <ul class="nav nav-tabs d-flex align-content-center justify-content-center" id="menuTab" role="tablist">
        <?php foreach ($wantedCats as $index => $cat): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $index === 0 ? 'active' : '' ?>" id="tab-<?= md5($cat) ?>" data-bs-toggle="tab" data-bs-target="#content-<?= md5($cat) ?>" type="button" role="tab" aria-controls="content-<?= md5($cat) ?>" aria-selected="<?= $index === 0 ? 'true' : 'false' ?>">
                    <?= htmlspecialchars(ucfirst($cat)) ?>
                </button>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="tab-content mt-5" id="menuTabContent">
        <?php foreach ($wantedCats as $index => $cat): ?>
            <div class="tab-pane fade <?= $index === 0 ? 'show active' : '' ?>" id="content-<?= md5($cat) ?>" role="tabpanel" aria-labelledby="tab-<?= md5($cat) ?>">
                <div class="container">
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                        <?php
                        $stmt = $conn->prepare("SELECT menu_id, menu_name, menu_description, menu_price, menu_image FROM menu WHERE menu_category = ?");
                        $stmt->bind_param("s", $cat);
                        $stmt->execute();
                        $itemsResult = $stmt->get_result();
                        if ($itemsResult && $itemsResult->num_rows > 0):
                          while ($item = $itemsResult->fetch_assoc()):
                            $raw = isset($item['menu_image']) ? trim((string)$item['menu_image']) : '';
                            $img = $raw !== '' ? ('./' . ltrim($raw, '/')) : './assets/images/menu/menu-item-1.png';
                        ?>
                        <div class="col">
                            <div class="card h-100 p-2">
                                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['menu_name']) ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($item['menu_name']) ?></h5>
                                    <p class="card-text flex-grow-1"><?= htmlspecialchars($item['menu_description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="price">रु<?= number_format($item['menu_price'], 2) ?></span>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <form action="includes/cart.php?action=add" method="post" class="d-grid flex-grow-1">
                                            <input type="hidden" name="menu_id" value="<?= intval($item['menu_id']) ?>">
                                            <input type="hidden" name="menu_name" value="<?= htmlspecialchars($item['menu_name']) ?>">
                                            <input type="hidden" name="price" value="<?= htmlspecialchars($item['menu_price']) ?>">
                                            <input type="hidden" name="image" value="<?= htmlspecialchars($img) ?>">
                                            <button type="submit" class="btn btn-orange w-100 <?php echo !$user ? 'require-login' : ''; ?>" <?php echo !$user ? 'data-action="add_to_cart"' : ''; ?>>Add to Cart</button>
                                        </form>
                                        <button type="button"
                                            class="btn btn-orange w-100 <?php echo !$user ? 'require-login' : ''; ?>"
                                            <?php echo !$user ? 'data-action="buy_now"' : ''; ?>
                                            data-bs-toggle="<?php echo $user ? 'modal' : ''; ?>"
                                            data-bs-target="<?php echo $user ? '#buyModal' : ''; ?>"
                                            data-id="<?= intval($item['menu_id']) ?>"
                                            data-name="<?= htmlspecialchars($item['menu_name']) ?>"
                                            data-description="<?= htmlspecialchars($item['menu_description']) ?>"
                                            data-price="<?= htmlspecialchars($item['menu_price']) ?>"
                                            data-image="<?= htmlspecialchars($img) ?>">
                                            Buy Now
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; else: ?>
                            <div class="col">
                                <div class="alert alert-warning text-center w-100">No items in <?= htmlspecialchars($cat) ?>.</div>
                            </div>
                        <?php endif; $stmt->close(); ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow">
            <form action="includes/menu_order.php" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="buyModalLabel">Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-5 text-center">
                            <img id="modal-image" src="" alt="" class="img-fluid rounded shadow-sm" />
                        </div>
                        <div class="col-md-7">
                            <h4 id="modal-name" class="text-primary fw-bold"></h4>
                            <p id="modal-description" class="text-muted"></p>
                            <p><strong>Price: रु<span id="modal-price"></span></strong></p>
                            <p><strong>Total: रु<span id="modal-total-price"></span></strong></p>
                            <input type="hidden" name="menu_id" id="input-menu-id" />
                            <input type="hidden" name="menu_name" id="input-menu-name" />
                            <input type="hidden" name="price" id="input-price" />
                            <input type="hidden" name="total_price" id="input-total-price" />
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control" value="<?php echo $_SESSION['email'] ?? '' ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity</label>
                                <input type="number" id="quantity" name="quantity" class="form-control" min="1" value="1" required>
                                <div class="invalid-feedback">Please enter valid quantity</div>
                            </div>
                            <div class="mb-3">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input type="tel" id="mobile" name="mobile" class="form-control" pattern="[0-9]{10}" maxlength="10" required>
                                <div class="invalid-feedback">Please enter valid number.</div>
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Delivery Address</label>
                                <textarea id="address" name="address" class="form-control" rows="3" required></textarea>
                                <div class="invalid-feedback">Please enter valid address</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="submit" class="btn btn-success" id="confirm-buy">Confirm Purchase</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Login Required Modal -->
<div class="modal fade login-modal" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loginRequiredModalLabel">Login Required</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-3">
                    <i class="bi bi-shield-lock text-danger" style="font-size: 3rem;"></i>
                </div>
                <h5 class="mb-3">Please login to continue</h5>
                <p class="text-muted">You need to be logged in to add items to cart or purchase items.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                <a href="/Masu%20Ko%20Jhol%28full%29/login.php" class="btn btn-login">Login Now</a>
            </div>
        </div>
    </div>
</div>
<?php include_once __DIR__ . '/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Auto hide toast notifications after 5 seconds
        var toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
          var bsToast = new bootstrap.Toast(toast, {
            delay: 5000
          });
          bsToast.show();
        });
        
        // Handle login required buttons
        const loginRequiredButtons = document.querySelectorAll('.require-login');
        const loginRequiredModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
        
        loginRequiredButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                loginRequiredModal.show();
            });
        });
        
        const buyModal = document.getElementById('buyModal');
        const modalName = document.getElementById('modal-name');
        const modalPrice = document.getElementById('modal-price');
        const modalTotalPrice = document.getElementById('modal-total-price');
        const inputMenuId = document.getElementById('input-menu-id');
        const inputMenuName = document.getElementById('input-menu-name');
        const inputPrice = document.getElementById('input-price');
        const inputTotalPrice = document.getElementById('input-total-price');
        const quantityInput = document.getElementById('quantity');
        function updateTotalPrice() {
            const price = parseFloat(modalPrice.textContent) || 0;
            const quantity = parseInt(quantityInput.value) || 1;
            const total = price * quantity;
            modalTotalPrice.textContent = total.toFixed(2);
            inputPrice.value = price.toFixed(2);
            inputTotalPrice.value = total.toFixed(2);
        }
        quantityInput.addEventListener('input', updateTotalPrice);
        buyModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            inputMenuId.value = button.getAttribute('data-id');
            modalName.textContent = button.getAttribute('data-name');
            modalPrice.textContent = parseFloat(button.getAttribute('data-price')).toFixed(2);
            modalTotalPrice.textContent = parseFloat(button.getAttribute('data-price')).toFixed(2);
            inputMenuName.value = button.getAttribute('data-name');
            inputPrice.value = parseFloat(button.getAttribute('data-price')).toFixed(2);
            inputTotalPrice.value = parseFloat(button.getAttribute('data-price')).toFixed(2);
            document.getElementById('modal-description').textContent = button.getAttribute('data-description');
            document.getElementById('modal-image').src = button.getAttribute('data-image');
            quantityInput.value = 1;
        });
    });
</script>
</body>
</html>