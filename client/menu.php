<?php
ob_start();
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Check if user is logged in
$currentUser = getUserFromCookie();

// If user is not logged in, redirect to login with appropriate action
if (!$currentUser) {
    $_SESSION['msg'] = ['type' => 'error', 'text' => 'Please login to order food.'];
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php?action=order_food');
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

// Fetch distinct categories
$catSql = "SELECT DISTINCT menu_category FROM menu";
$catResult = $pdo->query($catSql);
$categories = [];
if ($catResult) {
    while ($cat = $catResult->fetch(PDO::FETCH_ASSOC)) {
        $categories[] = $cat['menu_category'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Masu Ko Jhol | Menu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <?php require_once __DIR__ . '/../config/bootstrap.php'; ?>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>" />
    <style>
    /* Clean white background */
    body { background:#fff; }
    /* Card styling */
    .menu .card { border-radius: 16px; overflow: hidden; border: 0; background: #fff; box-shadow: 0 8px 24px rgba(0,0,0,.08); transition: transform .2s ease, box-shadow .2s ease; }
    .menu .card:hover { transform: translateY(-4px); box-shadow: 0 16px 36px rgba(0,0,0,.12); }
    .menu .card-img-top { height: 220px; object-fit: cover; }
    .menu .card-title { font-weight: 800; color: #d32f2f; }
    .menu .card-text { color:#6c757d; }
    .menu .price { color:#212529; font-weight:700; }
    .btn-orange { background:#ff6a00; color:#fff; border-radius:10px; border:1px solid #ff6a00; white-space:nowrap; font-weight:600; }
    .btn-orange:hover { background:#e65f00; color:#fff; border-color:#e65f00; }
    /* Enforce orange style against any global .btn overrides */
    .btn.btn-orange { background:#ff6a00 !important; border-color:#ff6a00 !important; color:#fff !important; }
    .btn.btn-orange:hover { background:#e65f00 !important; border-color:#e65f00 !important; color:#fff !important; }
    
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
                >My Order</a>
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
                <li><a class="dropdown-item" href="./update_password.php"><i class="bi bi-key me-2"></i>Update Password</a></li>
                <li><a class="dropdown-item" href="<?php echo url('./includes/logout.php'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
              </ul>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="d-flex justify-content-around py-3 align-items-center d-lg-none">
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
            <a class="text-decoration-none" id="shoppingbuttonMobile" href="#">
              <i class="fa fa-shopping-bag me-3 text-dark"></i>
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

<section id="menu" class="menu section main-content">
    <div class="container section-title py-4">
        <h2>Our Menu</h2>
        <p><span>Check Our</span> <span class="description-title">Yummy Menu</span></p>
    </div>
    <!-- Tabs: starter, breakfast, lunch, dinner -->
    <?php
    // Ensure we have exactly these categories in order
    $wantedCats = ['starter','breakfast','lunch','dinner'];
    ?>
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
                        $stmt = $pdo->prepare("SELECT menu_id, menu_name, menu_description, menu_price, menu_image FROM menu WHERE menu_category = ?");
                        $stmt->execute([$cat]);
                        $itemsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        if (!empty($itemsResult)):
                          foreach ($itemsResult as $item):
                            $raw = isset($item['menu_image']) ? trim((string)$item['menu_image']) : '';
                            // Ensure client-relative path
                            $img = $raw !== '' ? ('../' . ltrim($raw, '/')) : '../assets/images/menu/menu-item-1.png';
                        ?>
                        <div class="col">
                            <div class="card h-100 p-2">
                                <!-- To change images, place files under assets/images/menu/ and update DB menu_image accordingly. -->
                                <img src="<?= htmlspecialchars($img) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['menu_name']) ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?= htmlspecialchars($item['menu_name']) ?></h5>
                                    <p class="card-text flex-grow-1"><?= htmlspecialchars($item['menu_description']) ?></p>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <span class="price">₹<?= number_format($item['menu_price'], 2) ?></span>
                                    </div>
                                    <div class="d-flex gap-2 mt-3">
                                        <form action="../includes/cart.php?action=add" method="post" class="d-grid flex-grow-1">
                                            <input type="hidden" name="menu_id" value="<?= intval($item['menu_id']) ?>">
                                            <input type="hidden" name="menu_name" value="<?= htmlspecialchars($item['menu_name']) ?>">
                                            <input type="hidden" name="price" value="<?= htmlspecialchars($item['menu_price']) ?>">
                                            <input type="hidden" name="image" value="<?= htmlspecialchars($img) ?>">
                                            <div class="input-group mb-2">
                                                <input type="number" name="quantity" class="form-control form-control-sm" value="1" min="1" max="20">
                                            </div>
                                            <button type="submit" class="btn btn-orange w-100">Add to Cart</button>
                                        </form>
                                        <button type="button"
                                            class="btn btn-orange w-100"
                                            data-bs-toggle="modal"
                                            data-bs-target="#buyModal"
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
                        <?php endforeach; else: ?>
                            <div class="col">
                                <div class="alert alert-warning text-center w-100">No items in <?= htmlspecialchars($cat) ?>.</div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<!-- Modal -->
<div class="modal fade" id="buyModal" tabindex="-1" aria-labelledby="buyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow">
            <form action="../includes/menu_order.php" method="post" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="buyModalLabel">Item Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <div class="col-md-5 text-center">
                            <!-- Replace with your image from assets/images/menu/ when available -->
                            <img id="modal-image" src="" alt="" class="img-fluid rounded shadow-sm" />
                        </div>
                        <div class="col-md-7">
                            <h4 id="modal-name" class="text-primary fw-bold"></h4>
                            <p id="modal-description" class="text-muted"></p>
                            <p><strong>Price: ₹<span id="modal-price"></span></strong></p>
                            <p><strong>Total: ₹<span id="modal-total-price"></span></strong></p>
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
        // Auto hide toast notifications after 5 seconds
        var toasts = document.querySelectorAll('.toast');
        toasts.forEach(function(toast) {
          var bsToast = new bootstrap.Toast(toast, {
            delay: 5000
          });
          bsToast.show();
        });
        
        // Handle Buy Now modal functionality
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