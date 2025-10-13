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
                >My Order</a>
    </li>
            
            <?php if (!$currentUser): ?>
              <li class="list-unstyled py-2">
                <a class="btn btn-gradient" href="<?php echo url('/login.php'); ?>">Login</a>
              </li>
            <?php endif; ?>
            <li class="list-unstyled py-2">
              <a
                class="text-dark text-decoration-none text-uppercase p-4"
                href="./contactus.php"
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

      <div
        class="d-flex justify-content-around py-3 align-items-center d-lg-none"
      >
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
              <?php if (!$currentUser): ?>
                <li class="list-unstyled py-2">
                  <a class="btn btn-gradient" href="<?php echo url('/login.php'); ?>">Login</a>
                </li>
              <?php endif; ?>
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

    <main class="about-page main-content">
      <section class="page-banner d-flex align-items-center">
        <div class="container">
          <div class="row">
            <div class="banner-content">
              <h2 class="text-white display-6 fw-bold text-center" data-aos="fade-right" data-aos-delay="3000">About Us</h2>
              <div class="divider" data-aos="fade-up-right" data-aos-delay="3000">
                <div class="dot mb-2"></div>
              </div>
              <p class="text-white mb-0 text-center" data-aos="fade-left" data-aos-delay="3000">
                We bring to you the unforgetable moment with our delicious
                dishes
              </p>
            </div>
          </div>
        </div>
      </section>

      <section class="about-story mt-5 pt-5">
        <div class="container">
          <div class="row" data-aos="fade-right">
            <h2 class="text-center display-6 fw-bold">
              Masu Ko Jhol Glory Story
            </h2>
            <div
              class="about-line d-flex justify-content-center align-items-center"
            >
              <span></span>
            </div>
          </div>
          <div class="row">
            <div class="story-timeline">
              <div class="story-indicators my-4">
                <div class="row">
                  <div data-aos="fade-right" class="image-box col-lg-2 px-0">
                    <img
                      class="w-100"
                      src="<?php echo asset('images/timeline-1.jpg'); ?>"
                      alt=""
                    />
                    <div class="image-box-inner"><p class="mb-0">2000</p></div>
                  </div>
                  <div data-aos="fade-down" class="image-box col-lg-2 px-0">
                    <img
                      class="w-100"
                      src="<?php echo asset('images/timeline-2.jpg'); ?>"
                      alt=""
                    />
                    <div class="image-box-inner"><p class="mb-0">2002</p></div>

                  </div>
                  <div data-aos="fade-up" class="image-box col-lg-2 px-0">
                    <img
                      class="w-100"
                      src="<?php echo asset('images/timeline-3.jpg'); ?>"
                      alt=""
                    />
                    <div class="image-box-inner"><p class="mb-0">2004</p></div>
                  </div>
                  <div data-aos="fade-down" class="image-box col-lg-2 px-0">
                    <img
                      class="w-100"
                      src="<?php echo asset('images/timeline-4.jpg'); ?>"
                      alt=""
                    />
                    <div class="image-box-inner"><p class="mb-0">2008</p></div>

                  </div>
                  <div data-aos="fade-up" class="image-box col-lg-2 px-0">
                    <img
                      class="w-100"
                      src="<?php echo asset('images/timeline-5.jpg'); ?>"
                      alt=""
                    />
                    <div class="image-box-inner"><p class="mb-0">2012</p></div>

                  </div>
                  <div data-aos="fade-left" class="image-box col-lg-2 px-0">
                    <img
                      class="w-100"
                      src="<?php echo asset('images/timeline-6.jpg'); ?>"
                      alt=""
                    />
                    <div class="image-box-inner"><p class="mb-0">2016</p></div>

                  </div>
                </div>
              </div>
                <div class="story-content py-5 my-4" data-aos="fade-right">
  <div>
    <p class="text-center"><strong>16.10.2000:</strong> The Humble Beginning</p>
    <p class="text-center">
      Masu Ko Jhol started as a small family kitchen with a dream to serve authentic Nepali flavors. From day one, our mission was to create a space where food brings people together and memories are made.
    </p>
    <p class="text-center">
      Every dish was crafted with passion, turning simple ingredients into a culinary experience that celebrated the heart of Nepali tradition.
    </p>
  </div>
  <div>
    <p class="text-center"><strong>2002:</strong> Growing Popularity</p>
    <p class="text-center">
      By 2002, word had spread about Masu Ko Jhol’s signature flavors and warm hospitality. Guests from all walks of life came to savor the rich taste and authentic recipes that made our eatery stand out.
    </p>
    <p class="text-center">
      Our small kitchen began gaining recognition for quality and consistency, laying the foundation for our legacy.
    </p>
  </div>
  <div>
    <p class="text-center"><strong>2004:</strong> Expanding Horizons</p>
    <p class="text-center">
      With growing demand, we expanded our kitchen and menu, introducing new dishes while keeping our roots intact. Each plate served told the story of Nepali culture, craftsmanship, and the love we pour into every meal.
    </p>
    <p class="text-center">
      Masu Ko Jhol became more than a restaurant—it became a destination for those seeking authentic flavors and heartfelt dining.
    </p>
  </div>
  <div>
    <p class="text-center"><strong>2008:</strong> A Culinary Landmark</p>
    <p class="text-center">
      By 2008, Masu Ko Jhol had cemented its reputation as a local culinary landmark. Food enthusiasts and critics alike praised our dedication to authenticity, consistency, and exemplary service.
    </p>
    <p class="text-center">
      Our restaurant became a beloved gathering place, known for bringing people together through the joy of great food.
    </p>
  </div>
  <div>
    <p class="text-center"><strong>2012:</strong> Honoring Tradition, Embracing Growth</p>
    <p class="text-center">
      In 2012, we modernized aspects of our restaurant while honoring our traditional roots. This perfect blend of innovation and heritage allowed Masu Ko Jhol to reach new audiences while continuing to serve our loyal patrons.
    </p>
    <p class="text-center">
      Every dish continued to tell a story of culture, dedication, and culinary excellence.
    </p>
  </div>
  <div>
    <p class="text-center"><strong>2016:</strong> A Legacy of Flavor</p>
    <p class="text-center">
      Today, Masu Ko Jhol stands as a symbol of passion, perseverance, and the timeless allure of Nepali cuisine. From a modest family kitchen to a celebrated culinary destination, our journey is honored in every dish we serve.
    </p>
    <p class="text-center">
      We continue to uphold our founders’ vision: bringing people together through food, flavor, and unforgettable dining experiences.
    </p>
  </div>
</div>

           
      </section>


      <section class="testimonials py-2 my-2 mt-lg-4 pt-lg-4 mb-lg-0 pb-lg-0">
        <div class="container my-2 py-2 mt-lg-4 pt-lg-4 mb-lg-0 pb-lg-0">
          <div class="row">
            <div class="col-lg-4 d-none d-lg-block">
              <img src="<?php echo asset('images/ab_team_01.png'); ?>" alt="" data-aos="fade-right">
            </div>
            <div class="col-12 col-lg-8">
              <div class="testimonial-slider-wrapper">
                <div class="slider-content pt-5 pb-4 mx-4" data-aos="fade-down-left">
                  <div>
                    <div class="testi-content">
                      <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Architecto vel ipsa dolore sunt vitae, culpa, dolor reiciendis facilis sed blanditiis repellat incidunt impedit iusto? Odio veniam beatae veritatis adipisci a!</p>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                      <img src="<?php echo asset('images/testi-signal.png'); ?>" alt="">
                    </div>
                    <div class="testi-info">
                      <span class="name">Timothy Doe</span>
                      <span class="position">Customer</span>
                    </div>
                  </div>
                  <div>
                    <div class="testi-content">
                      <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Architecto vel ipsa dolore sunt vitae, culpa, dolor reiciendis facilis sed blanditiis repellat incidunt impedit iusto? Odio veniam beatae veritatis adipisci a!</p>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                      <img src="<?php echo asset('images/testi-signal.png'); ?>" alt="">
                    </div>
                    <div class="testi-info">
                      <span class="name">Sarah	Ruiz</span>
                      <span class="position">Director</span>
                    </div>
                  </div>
                  <div>
                    <div class="testi-content">
                      <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Architecto vel ipsa dolore sunt vitae, culpa, dolor reiciendis facilis sed blanditiis repellat incidunt impedit iusto? Odio veniam beatae veritatis adipisci a!</p>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                      <img src="<?php echo asset('images/testi-signal.png'); ?>" alt="">
                    </div>
                    <div class="testi-info">
                      <span class="name">Tracey Lewis</span>
                      <span class="position">Designer</span>
                    </div>
                  </div>
                  <div>
                    <div class="testi-content">
                      <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Architecto vel ipsa dolore sunt vitae, culpa, dolor reiciendis facilis sed blanditiis repellat incidunt impedit iusto? Odio veniam beatae veritatis adipisci a!</p>
                    </div>
                    <div class="d-flex justify-content-center mb-3">
                      <img src="<?php echo asset('images/testi-signal.png'); ?>" alt="">
                    </div>
                    <div class="testi-info">
                      <span class="name">Jamie	Erickson</span>
                      <span class="position">Manager</span>
                    </div>
                  </div>
                </div>
                <div class="slider-nav-wrapper mx-5" data-aos="fade-up-right">
                  <div class="slider-nav">
                    <div class="slider-nav-img active">
                      <img src="<?php echo asset('images/testi-1.jpg'); ?>" alt="">
                    </div>
                    <div class="slider-nav-img">
                      <img src="<?php echo asset('images/testi-2.jpg'); ?>" alt="">
                    </div>
                    <div class="slider-nav-img">
                      <img src="<?php echo asset('images/testi-3.jpg'); ?>" alt="">
                    </div>
                    <div class="slider-nav-img">
                      <img src="<?php echo asset('images/testi-4.jpg'); ?>" alt="">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <section class="our-special my-5 py-5">
        <div class="container">
          <div class="row" data-aos="fade-right">
            <h2 class="text-center display-6 fw-bold">
              Our Special
            </h2>
            <div class="about-line d-flex justify-content-center align-items-center">
              <span></span>
            </div>
          </div>
          <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4 mb-5 mb-lg-0">
              <div class="special-card" data-aos="fade-right">
                <div class="box-inner">
                  <div class="box-wrapper px-4">
                    <h2 class="pb-2">FRESH MENU</h2>
                    <p class="pb-4">Lorem ipsum dolor sit amet, consec adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam</p>
                    <div class="book-a-table">
                      <div class="anim-layer"></div>
                      <a href="#">Read More</a>
                    </div>
                  </div>
                  <div class="box-showcase pb-5">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                      <img class="img-fluid" src="<?php echo asset('images/feature-box-bg.jpg'); ?>" alt="">
                      <h2 class="text-center">FRESH MENU</h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-5 mb-lg-0">
              <div class="special-card" data-aos="flip-right">
                <div class="box-inner">
                  <div class="box-wrapper px-4">
                    <h2 class="pb-2">VARIOUS DRINK</h2>
                    <p class="pb-4">Lorem ipsum dolor sit amet, consec adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam</p>
                    <div class="book-a-table">
                      <div class="anim-layer"></div>
                      <a href="#">Read More</a>
                    </div>
                  </div>
                  <div class="box-showcase pb-5">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                      <img class="img-fluid" src="<?php echo asset('images/feature-box-bg-2.jpg'); ?>" alt="">
                      <h2 class="text-center">VARIOUS DRINK</h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-5 mb-lg-0">
              <div class="special-card" data-aos="fade-left">
                <div class="box-inner">
                  <div class="box-wrapper px-4">
                    <h2 class="pb-2">EXCLUSIVE DISHES</h2>
                    <p class="pb-4">Lorem ipsum dolor sit amet, consec adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam</p>
                    <div class="book-a-table">
                      <div class="anim-layer"></div>
                      <a href="#">Read More</a>
                    </div>
                  </div>
                  <div class="box-showcase pb-5">
                    <div class="d-flex flex-column align-items-center justify-content-center">
                      <img class="img-fluid" src="<?php echo asset('images/feature-box-bg-3.jpg'); ?>" alt="">
                      <h2 class="text-center">EXCLUSIVE DISHES</h2>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

     

      <section class="counter my-5">
        <img data-aos="fade-right" class="counter-after" src="<?php echo asset('images/vegetable_01.png'); ?>" alt="">
        <img data-aos="fade-right" class="counter-before" src="<?php echo asset('images/vegetable_02.png'); ?>" alt="">
        <div class="container pt-4 pb-5" data-aos="fade-up-right">
          <div class="row py-5">
            <div class="col-lg-3">
              <div class="counter-box d-flex flex-column align-items-center">
                <div class="counter-info pb-3">
                  <span class="number">103</span>
                  <span class="heading">/dishes</span>
                </div>
                <div class="counter-avatar pt-4">
                  <img src="<?php echo asset('images/counter-1.png'); ?>" alt="">
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="counter-box d-flex flex-column align-items-center">
                <div class="counter-info pb-3">
                  <span class="number">2389</span>
                  <span class="heading">/customers</span>
                </div>
                <div class="counter-avatar pt-4">
                  <img src="<?php echo asset('images/counter-2.png'); ?>" alt="">
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="counter-box d-flex flex-column align-items-center">
                <div class="counter-info pb-3">
                  <span class="number">20</span>
                  <span class="heading">/awards</span>
                </div>
                <div class="counter-avatar pt-4">
                  <img src="<?php echo asset('images/counter-3.png'); ?>" alt="">
                </div>
              </div>
            </div>
            <div class="col-lg-3">
              <div class="counter-box d-flex flex-column align-items-center">
                <div class="counter-info pb-3">
                  <span class="number">2589</span>
                  <span class="heading">/working hours</span>
                </div>
                <div class="counter-avatar pt-4">
                  <img src="<?php echo asset('images/counter-4.png'); ?>" alt="">
                </div>
              </div>
            </div>
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

    <footer>
      <div class="container">
        <div class="row">
          <div class="footer-content col-xl-8  px-4">
            <div class="row">
              <div class="col-lg-6 px-0">
                <div class="logo" data-aos="fade-down-right">
                  <a href="./index.php">
                    <i class="fa fa-utensils me-3"></i>
                    <h1 class="mb-0">Masu Ko Jhol</h1>
                  </a>
                </div>
              </div>
              <div data-aos="fade-down" class="col-lg-6 pt-4 pt-lg-0 d-flex align-items-center justify-content-start justify-content-lg-end">
                <div class="social-icons d-flex">
                  <ul class="d-flex mb-0 ps-0">
                      <li class="mx-2"><a class="text-decoration-none text-white" href="https://www.facebook.com/subodh.paudel.779"><i class="fab fa-facebook"></i></a></li>
                    <li class="mx-2"><a class="text-decoration-none text-white" href=""><i class="fab fa-twitter"></i></a></li>
                    <li class="mx-2"><a class="text-decoration-none text-white" href="https://www.instagram.com/subodh_543/"><i class="fab fa-instagram"></i></a></li>
                    
                  </ul>
                </div>
              </div>
            </div>
            <div class="row pt-5 content-desc" data-aos="fade-right">
              <p class="px-0">Thank you for visiting Masu Ko Jhol—where every dish is a tribute to Nepali tradition and every guest is family.</p>
              </div>
            <div class="row" data-aos="fade-right">
              <div class="d-flex flex-column flex-lg-row px-0 justify-content-between">
                <div class="location d-flex align-items-center pe-2 py-3">
                  <i class="fa-solid fa-location-dot text-white fa-2x border-bottom pb-2"></i>
                  <div class="ps-3">
                    <p class="mb-0">
                      street No 1 Pokhara,Lakeside Nepal <br>
                     
                    </p>
                  </div>
                </div>
                <div class="location d-flex align-items-center pe-2 py-3">
                  <i class="fa-solid fa-mobile text-white fa-2x border-bottom pb-2"></i>
                  <div class="ps-3">
                    <p class="mb-0">
                      007-44444 <br>
                  
                      
                    </p>
                  </div>
                </div>
                <div class="location d-flex align-items-center pe-2 py-3">
                  <i class="fa-solid fa-envelope text-white fa-2x border-bottom pb-2"></i>
                  <div class="ps-3">
                    <p class="mb-0">
                      Masukojhol@gmail.com <br>
                      
                    </p>
                  </div>
                </div>
            </div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="reservation-box" data-aos="fade-down-left">
              <div class="reservation-wrapper">
                <h2>Open Hours</h2>
                <div class="reservation-date-time">
                  <p>Tuesday: .......................... 7AM - 9PM</p>
                  <p>Wednesday: ..................... 7AM - 9PM</p>
                  <p>Thursday: ......................... 7AM - 9PM</p>
                  <p>Friday: ............................... 7AM - 9PM</p>
                  <p>Saturday: ........................... 7AM - 9PM</p>
                  <p>Sunday: ............................. Closed</p>
                  <p>Monday: ............................. 7AM- 10pm</p>
                </div>
                <h2 class="pb-2">Reservation Numbers</h2>
                <h3>9748759699</h3>
              </div>
            </div>
          </div>
        </div>
        <div class="row">
          <p class="text-center pt-4 mt-3 pt-lg-0">&copy; <span id="copyrightCurrentYear"></span> <b> Masu Ko Jhol.</b> All rights reserved. Design by <a href="https://www.instagram.com/subodh_543/" class="fw-bold author-name">Subodh Paudel</a></p>
        </div>
      </div>
    </footer>
    
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