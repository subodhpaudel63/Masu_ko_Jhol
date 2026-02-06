<?php
session_start();

$showExpiredAlert = false;
if (isset($_GET['session_expired']) && $_GET['session_expired'] == 1) {
    $showExpiredAlert = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Masu Ko Jhol | Login</title>
    <meta name="description" content="">
    <meta name="keywords" content="">

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
    
    <style>
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
      
      /* Custom navbar styles */
      .navbar-custom {
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,.1);
        padding: 15px 0;
      }
      
      .logo-text {
        font-family: 'Amatic SC', sans-serif;
        font-weight: 700;
        font-size: 28px;
        color: #000;
        text-decoration: none;
      }
      
      .logo-text i {
        margin-right: 10px;
        color: #000;
      }
      
      .nav-button {
        background-color: #ff0000;
        color: white;
        border: none;
        padding: 8px 20px;
        border-radius: 30px;
        font-weight: 500;
        transition: all 0.3s ease;
      }
      
      .nav-button:hover {
        background-color: #cc0000;
        color: white;
      }
    </style>

</head>

<body>
    <!-- Custom Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="logo-text" href="index.php">
                <i class="fas fa-utensils"></i>Masu Ko Jhol
            </a>
            <div class="ml-auto">
                <a href="register.php" class="nav-button">Register</a>
            </div>
        </div>
    </nav>
    
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
    
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body px-5 py-4">
                         <img src="./assets/images/logoo.png" alt="logo" width="100" class="d-block mx-auto my-3" />
                        <h2 class="text-center mb-2">Login to Your Account</h2>
                        <p class="text-center text-muted mb-4" style="font-size:0.95rem;">Welcome back to Masu Ko Jhol</p>
                        <form action="includes/login.php" method="post" id="loginForm" class="needs-validation" novalidate>

                            <!-- Email Field -->
                            <div class="mb-3">
                                <label for="useremail" class="form-label">Email address</label>
                                <div class="input-group ">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" class="form-control" id="useremail" name="email" placeholder="name@example.com" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group ">
                                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required minlength="6">
                                    <div class="invalid-feedback">Password is required.</div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-gradient w-100 py-2" id="loginButton">
                                    <span class="me-2">Login</span>
                                    <i class="bi bi-box-arrow-in-right"></i>
                                </button>
                            </div>

                            <!-- Register Link -->
                            <div class="text-center">
                                <p class="mb-0" style="font-size:clamp(0.95rem, 2vw, 1.1rem);">
                                    Don't have an account?
                                    <a href="register.php" class="text-danger" style="font-size:inherit;">Register here</a>
                                </p>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once __DIR__ . '/footer.php'; ?>

    <!-- Scroll Top -->
    <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Preloader -->
    <div id="preloader"></div>

    <!-- Vendor JS Files -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Main JS File -->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/formvalidation.js"></script>

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