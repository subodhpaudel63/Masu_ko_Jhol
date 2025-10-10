<?php
require_once __DIR__ . '/includes/auth_check.php';

$userType = null;
$currentUser = null;

if (isset($_COOKIE['user_type'])) {
    $userType = decrypt($_COOKIE['user_type'], SECRET_KEY);
}

// Get current user if logged in
if (isset($_COOKIE['user_email'])) {
    $currentUser = ['email' => decrypt($_COOKIE['user_email'], SECRET_KEY)];
}

// Only redirect from the public root landing page (index.php),
// not from inner pages like client/menu.php, login.php, or other auth pages.
$scriptName = basename($_SERVER['SCRIPT_NAME'] ?? '');
if ($scriptName === 'index.php') {
    if ($userType === 'admin') {
        header('Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php');
        exit();
    }
    if ($userType === 'user') {
        header('Location: /Masu%20Ko%20Jhol%28full%29/client/index.php');
        exit();
    }
}

// Profile image
$defaultImg = 'assets/images/profile.jpg';
$profileImg = $defaultImg;
if (isset($_COOKIE['user_img'])) {
    $dec = decrypt($_COOKIE['user_img'], SECRET_KEY);
    if ($dec) { 
        $candidate = ltrim($dec, '/'); 
        if (file_exists(__DIR__ . '/' . $candidate)) {
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
    <title>Masu Ko Jhol - About</title>
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
    <link
      rel="stylesheet"
      type="text/css"
      href="//cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"
    />
    <link rel="stylesheet" href="./assets/css/style.css" />
    <style>
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
    </style>
  </head>

<body>
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
                href="./myorder.php"
                >My Order</a
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
          <a class="text-decoration-none" id="shoppingbutton" href="#">
            <i class="fa fa-shopping-bag me-3 text-dark"></i>
          </a>
          <?php if ($currentUser): ?>
            <div class="dropdown">
              <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/<?php echo $profileImg; ?>" alt="profile" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                <li><h6 class="dropdown-header"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></h6></li>
                <li><hr class="dropdown-divider"></li>
                <?php if ($userType === 'user'): ?>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/client/update_password.php"><i class="bi bi-key me-2"></i>Update Password</a></li>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/includes/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                <?php elseif ($userType === 'admin'): ?>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php"><i class="bi bi-person me-2"></i>Dashboard</a></li>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/includes/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                <?php endif; ?>
              </ul>
            </div>
          <?php else: ?>
            <!-- Login and Signup buttons -->
            <div class="d-flex align-items-center">
              <a href="./login.php" class="nav-button">Login</a>
              <a href="./register.php" class="nav-button-outline">Sign Up</a>
            </div>
          <?php endif; ?>
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
        <div class="mobile-nav-icons d-flex align-items-center">
          <div class="icons">
            <a class="text-decoration-none" id="searchBtnMobile" href="#">
              <i class="fa fa-search me-3 text-dark"></i>
            </a>
            <a class="text-decoration-none" id="shoppingbuttonMobile" href="#">
              <i class="fa fa-shopping-bag me-3 text-dark"></i>
            </a>
          </div>
          <?php if ($currentUser): ?>
            <div class="dropdown">
              <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="profileMenuMobile" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="/<?php echo $profileImg; ?>" alt="profile" class="rounded-circle" style="width:32px;height:32px;object-fit:cover;">
              </a>
              <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenuMobile">
                <li><h6 class="dropdown-header"><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></h6></li>
                <li><hr class="dropdown-divider"></li>
                <?php if ($userType === 'user'): ?>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/client/update_password.php"><i class="bi bi-key me-2"></i>Update Password</a></li>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/includes/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                <?php elseif ($userType === 'admin'): ?>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php"><i class="bi bi-person me-2"></i>Dashboard</a></li>
                  <li><a class="dropdown-item" href="/Masu%20Ko%20Jhol%28full%29/includes/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                <?php endif; ?>
              </ul>
            </div>
          <?php else: ?>
            <div class="d-flex align-items-center">
              <a href="./login.php" class="nav-button" style="padding: 4px 12px; font-size: 0.8rem;">Login</a>
              <a href="./register.php" class="nav-button-outline" style="padding: 4px 12px; font-size: 0.8rem;">Sign Up</a>
            </div>
          <?php endif; ?>
        </div>
        <div
          class="position-fixed w-75 bg-white h-100 top-0 start-0"
          id="mobile-menu"
        >
          <div
            id="hamburger-cross"
            class="d-flex justify-content-end align-items-center py-2"
          >
            <i class="fa fa-2x fa-plus me-3"></i>
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
                  href="./myorder.php"
                  >My Order</a
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