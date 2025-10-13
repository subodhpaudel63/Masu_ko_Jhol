<?php
ob_start();
session_start();

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth_check.php';

// Check if user is logged in
$currentUser = getUserFromCookie();

// If user is not logged in, redirect to appropriate dashboard
if (!$currentUser) {
    header('Location: /Masu%20Ko%20Jhol%28full%29/login.php');
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
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Masu Ko Jhol - Contact</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
      integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
      crossorigin="anonymous"
      referrerpolicy="no-referrer"
    />
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css"/>
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65"
      crossorigin="anonymous"
    />
    <link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <?php require_once __DIR__ . '/../config/bootstrap.php'; ?>
    <link rel="stylesheet" href="<?php echo asset('css/style.css'); ?>" />
    <style>
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
                <li><a class="dropdown-item" href="./update_password.php"><i class="bi bi-key me-2"></i>Update Password</a></li>
                <li><a class="dropdown-item" href="<?php echo url('includes/logout.php'); ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
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

    <div class="search-bar d-none" id="search-container">
      <div class="close-btn" id="search-close-btn">
        <i class="fa fa-close"></i>
      </div>
      <div class="search-bar-wrapper">
        <input type="search" placeholder="Enter any text here..." />
        <div class="search-button">
          <a href="#"><i class="fa fa-search"></i></a>
        </div>
      </div>
    </div>

    <div class="shopping-cart">
      <div class="shopping-cart-header d-flex justify-content-between">
        <h2>Review your Cart</h2>
        <i class="fa fa-close"></i>
      </div>
      <div class="shopping-cart-body">
        <div class="row shopping-cart-item d-flex justify-content-between">
          <div class="col-2 d-flex align-items-center">
            <img src="<?php echo asset('images/product-2a.jpg'); ?>" alt="">
          </div>
          <div class="col-8">
            <h3>The Cracker Barrel's Country Boy Breakfast</h3>
            <div class="shopping-cart-counter">
              <i class="fa fa-minus"></i>
              <span>1</span>
              <i class="fa fa-plus"></i>
            </div>
          </div>
          <div class="col-2 item-price d-flex align-items-end">
            <p class="mb-0 text-center">$ 25.0</p>
          </div>
        </div>
        <div class="row shopping-cart-item d-flex justify-content-between">
          <div class="col-2 d-flex align-items-center">
            <img src="<?php echo asset('images/product-2b.jpg'); ?>" alt="">
          </div>
          <div class="col-8">
            <h3>Old Timer's Meat Breakfast</h3>
            <div class="shopping-cart-counter">
              <i class="fa fa-minus"></i>
              <span>1</span>
              <i class="fa fa-plus"></i>
            </div>
          </div>
          <div class="col-2 item-price d-flex align-items-end">
            <p class="mb-0 text-center">$ 12.0</p>
          </div>
        </div>
        <div class="row shopping-cart-item d-flex justify-content-between">
          <div class="col-2 d-flex align-items-center">
            <img src="<?php echo asset('images/product-2c.jpg'); ?>" alt="">
          </div>
          <div class="col-8">
            <h3>Uncle Herschel's Favorite</h3>
            <div class="shopping-cart-counter">
              <i class="fa fa-minus"></i>
              <span>1</span>
              <i class="fa fa-plus"></i>
            </div>
          </div>
          <div class="col-2 item-price d-flex align-items-end">
            <p class="mb-0 text-center">$ 25.0</p>
          </div>
        </div>
        <div class="row shopping-cart-item d-flex justify-content-between">
          <div class="col-2 d-flex align-items-center">
            <img src="<?php echo asset('images/product-2d.jpg'); ?>" alt="">
          </div>
          <div class="col-8">
            <h3>Grandpa's Country Fried Breakfast</h3>
            <div class="shopping-cart-counter">
              <i class="fa fa-minus"></i>
              <span>1</span>
              <i class="fa fa-plus"></i>
            </div>
          </div>
          <div class="col-2 item-price d-flex align-items-end">
            <p class="mb-0 text-center">$ 30.0</p>
          </div>
        </div>
      </div>
      <div class="shopping-cart-footer">
        <div class="d-flex justify-content-between px-3 py-2">
          <div>
            <h2 class="mb-0">Subtotal</h2>
            <p class="mb-0">Shipping & taxes calculated at checkout</p>
          </div>
          <div class="d-flex align-items-end">
            <p class="footet-total-price mb-0">$ 92.0</p>
          </div>
        </div>
          <div class="d-flex justify-content-between px-2">
            <div class="footer-checkout">
              <div class="anim-layer"></div>
              <a href="#">Checkout</a>
            </div>
            <div class="footer-shopping">
              <div class="anim-layer"></div>
              <a href="#">Continue Shopping</a>
            </div>
          </div>
      </div>
    </div>

    <main class="contact-page main-content">
        <section class="page-banner d-flex align-items-center">
          <div class="container"> 
            <div class="row">
              <div class="banner-content">
                <h2 class="text-white display-3 text-center" data-aos="fade-right" data-aos-delay="3000">Contact Us</h2>
                <div class="divider" data-aos="fade-up-right" data-aos-delay="3000">
                    <div class="dot mb-2"></div>
                </div>
                <p class="text-white mb-0 text-center" data-aos="fade-left" data-aos-delay="3000">Let us know if you have any concern about our menu, service or other information you want to have</p>
            </div>
            </div>
          </div>
        </section>

        <section class="contact-us my-5 py-5">
          <div class="container">
            <div class="row">
              <div class="col-lg-8">
                <div class="form">
                  <h2 class="mb-5 position-relative display-6 fw-bold" data-aos="fade-right">Get In Touch</h2>
                  <form action="" data-aos="fade-right">
                    <div class="input-group">
                      <div class="icon-wrapper d-flex align-items-center position-relative">
                        <i class="fa fa-user py-2 px-3"></i>
                      </div>
                      <input class="form-control bg-transparent border-0 px-3" type="text" placeholder="Username">
                    </div>
                    <div class="input-group">
                      <div class="icon-wrapper d-flex align-items-center position-relative">
                        <i class="fa fa-envelope py-2 px-3"></i>
                      </div>
                      <input class="form-control bg-transparent border-0 px-3" type="email" placeholder="Email">
                    </div>
                    <div class="input-group">
                      <div class="icon-wrapper d-flex align-items-center position-relative">
                        <i class="fa fa-phone py-2 px-3"></i>
                      </div>
                      <input class="form-control bg-transparent border-0 px-3" type="text" placeholder="Phone">
                    </div>
                    <div class="input-group">
                      <textarea class="form-control bg-transparent border-0 px-3" name="" id="" placeholder="Message"></textarea>
                    </div>

                    <div class="book-a-table contact-button">
                      <div class="anim-layer"></div>
                      <a href="#">Send</a>
                    </div>
                  </form>
                </div>
              </div>
              <div class="col-lg-4">
                <div class="contact-info">
                  <h2 class="mb-5 mt-5 mt-lg-0 position-relative display-6 fw-bold" data-aos="fade-right">Contact Info</h2>
                    <div class="d-flex flex-column px-0 justify-content-between" data-aos="fade-left">
                        <div class="contact-info-box d-flex align-items-center pe-2 py-3">
                          <div class="contact-icon-box">
                            <i class="fa-solid fa-location-dot border-bottom pb-2"></i>
                          </div>
                          <div class="ps-3">
                            <p class="mb-0">
                              <b>Restaurent 1</b> <br>
                              157 White Oak Drive Kansas City
                            </p>
                          </div>
                        </div>
                        <div class="contact-info-box d-flex align-items-center pe-2 py-3">
                          <div class="contact-icon-box">
                            <i class="fa-solid fa-location-dot border-bottom pb-2"></i>
                          </div>
                          <div class="ps-3">
                            <p class="mb-0">
                              <b>Restaurent 2</b> <br>
                              158 White Oak Drive Kansas City
                            </p>
                          </div>
                        </div>
                        <div class="contact-info-box d-flex align-items-center pe-2 py-3">
                          <div class="contact-icon-box">
                            <i class="fa-solid fa-phone border-bottom pb-2"></i>
                          </div>
                          <div class="ps-3">
                            <p class="mb-0">
                              <b>Phone Number</b> <br>
                              (012) 978 645 312
                            </p>
                          </div>
                        </div>
                        <div class="contact-info-box d-flex align-items-center pe-2 py-3">
                          <div class="contact-icon-box">
                            <i class="fa-solid fa-envelope border-bottom pb-2"></i>
                          </div>
                          <div class="ps-3">
                            <p class="mb-0">
                              <b>Mail</b> <br>
                              hello@fooday.com <br>
                              contact@fooday.com
                            </p>
                          </div>
                        </div>
                    </div>
                </div>
              </div>
          </div>
        </section>


        <section class="map pb-0 pb-lg-5 ">
          <div class="container pb-5" data-aos="fade-right">
            <div class="row">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d7236.375239953878!2d67.08098637770993!3d24.92567760000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33f60a0781265%3A0x2befaba123014ab1!2sSMIT%20Gulshan%20Campus!5e0!3m2!1sen!2s!4v1724775738916!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
          </div>
        </section>

        <section class="subscribe-us pb-5 mb-5">
          <img class="d-none d-lg-block" src="<?php echo asset('images/subscribe-us.png'); ?>" alt="" data-aos="fade-down-right">
          <div class="container">
            <div class="row">
              <div class="col-lg-2">
              </div>
              <div class="col-lg-8 d-flex flex-column flex-md-row align-items-lg-center">
                <div class="content" data-aos="fade-right">
                  <h5 class="display-6 text-black">Subcribe Us Now</h5>
                  <p>
                    Get more news and delicious dishes everyday from us
                  </p>
                </div>
                <div class="subscribe-form d-flex ps-0 ms-0 ps-lg-5 ms-lg-5" data-aos="fade-left">
                  <div class="input-form w-100">
                    <input class="border-0 px-3 w-100" type="email" placeholder="Email">
                  </div>
                  <div class="input-button">
                    <a class="text-decoration-none" href="#">
                      <i class="fa fa-paper-plane"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
    </main>

    <a href="#" id="back-to-top">
      <i class="fa-solid fa-angles-up"></i>
    </a>
    
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
    
    <!-- Toast auto-hide script -->
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
      });
    </script>
  </body>
</html>