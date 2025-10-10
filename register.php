<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Masu Ko Jhol | Register</title>

    <!-- Favicons -->
    <link href="assets/img/logo.png" rel="icon">

    <!-- Fonts & Bootstrap -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/main.css" rel="stylesheet">
    
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
        font-family: 'Roboto', sans-serif;
        font-weight: 700;
        font-size: 24px;
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
                <a href="login.php" class="nav-button">Login</a>
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
                <div class="card border-0 shadow rounded-4 p-4">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Register Your Account</h2>

                        <form action="./includes/register.php" method="post" class="needs-validation" novalidate onsubmit="return validateForm()">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email address</label>
                                <div class="input-group">
                                
                                    <input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required>
                                    <div class="invalid-feedback">Please enter a valid email address.</div>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" name="password" id="password" class="form-control" placeholder="Password" oninput="checkPasswordStrength()" required>
                                    <span class="input-group-text" onclick="togglePassword('password', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                <small id="passwordStrength" class="form-text text-muted mt-1"></small>
                            </div>

                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                <div class="input-group">
                                    <input type="password" name="confirmPassword" id="confirmPassword" class="form-control" placeholder="Confirm Password" required>
                                    <span class="input-group-text" onclick="togglePassword('confirmPassword', this)">
                                        <i class="bi bi-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="invalid-feedback">Passwords must match.</div>
                            </div>

                            <div class="d-grid mt-3">
                                <button type="submit" class="btn btn-danger">Register</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <p>Already have an account? <a href="login.php" class="text-danger">Login here</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

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
      
      // Password Validation Scripts
      function togglePassword(fieldId, toggleIcon) {
          const input = document.getElementById(fieldId);
          const icon = toggleIcon.querySelector('i');

          if (input.type === "password") {
              input.type = "text";
              icon.classList.remove("bi-eye-slash");
              icon.classList.add("bi-eye");
          } else {
              input.type = "password";
              icon.classList.remove("bi-eye");
              icon.classList.add("bi-eye-slash");
          }
      }

      function checkPasswordStrength() {
          const password = document.getElementById('password').value.trim();
          const strengthMsg = document.getElementById('passwordStrength');

          const isLongEnough = password.length >= 3;
          const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);
          const hasNumber = /\d/.test(password);

          if (isLongEnough && hasSymbol && hasNumber) {
              strengthMsg.textContent = "valid password";
              strengthMsg.classList.remove("text-danger");
              strengthMsg.classList.add("text-primary");
          } else {
              strengthMsg.textContent = "Min 3 chars, 1 symbol, 1 number required.";
              strengthMsg.classList.remove("text-primary");
              strengthMsg.classList.add("text-danger");
          }
      }

      document.getElementById('confirmPassword').addEventListener('input', function() {
          const pass = document.getElementById('password').value;
          const confirmPass = this.value;

          if (confirmPass && pass !== confirmPass) {
              this.classList.add("is-invalid");
          } else {
              this.classList.remove("is-invalid");
          }
      });

      function validateForm() {
          const email = document.getElementById('email').value.trim();
          const password = document.getElementById('password').value.trim();
          const confirmPassword = document.getElementById('confirmPassword').value.trim();
          const strengthMsg = document.getElementById('passwordStrength');

          const isLongEnough = password.length >= 3;
          const hasSymbol = /[!@#$%^&*(),.?":{}|<>]/.test(password);
          const hasNumber = /\d/.test(password);

          if (!email) {
              alert('Please enter your email address.');
              return false;
          }

          if (!isLongEnough || !hasSymbol || !hasNumber) {
              strengthMsg.textContent = "Min 3 chars, 1 symbol, 1 number required.";
              strengthMsg.classList.remove("text-primary");
              strengthMsg.classList.add("text-danger");
              alert('Min 3 chars, 1 symbol, 1 number required.');
              return false;
          }

          if (password !== confirmPassword) {
              alert('Make sure both password fields match.');
              return false;
          }

          return true;
      }
    </script>

    <script src="./assets/js/main.js"></script>
</body>

</html>