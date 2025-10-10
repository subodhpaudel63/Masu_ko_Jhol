<?php
session_start();

// Check if already logged in as admin
if (isset($_COOKIE['user_type'])) {
    require_once __DIR__ . '/../includes/auth_check.php';
    $userType = decrypt($_COOKIE['user_type'], SECRET_KEY);
    if ($userType === 'admin') {
        header('Location: /Masu%20Ko%20Jhol%28full%29/admin/index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Admin Login • Masu Ko Jhol</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" />
    <style>
        :root {
            --bg: #0f1115;
            --panel: #161a22;
            --muted: #8b95a7;
            --text: #e8edf3;
            --brand: #ffb74d;
            --accent: #7c4dff;
            --danger: #ff6b6b;
            --success: #4caf50;
        }
        
        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-card {
            background: var(--panel);
            border: 1px solid #1f2330;
            border-radius: 16px;
            width: 100%;
            max-width: 400px;
        }
        
        .btn-gradient {
            background: linear-gradient(135deg, #ff7a18 0%, #af002d 74%);
            border: none;
            color: #fff;
        }
        
        .form-control {
            background: #0f131b;
            color: var(--text);
            border: 1px solid #273044;
        }
        
        .form-control:focus {
            background: #0f131b;
            color: var(--text);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(124, 77, 255, 0.25);
        }
        
        .form-label {
            color: var(--muted);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card shadow">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-shield-lock text-warning" style="font-size: 3rem;"></i>
                    <h2 class="mt-3">Admin Login</h2>
                    <p class="text-muted">Masu Ko Jhol Administration</p>
                </div>
                
                <?php if (isset($_SESSION['msg'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['msg']['type'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($_SESSION['msg']['text']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['msg']); ?>
                <?php endif; ?>
                
                <form action="admin_login_process.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" class="form-control" id="email" name="email" placeholder="admin@example.com" required>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-gradient py-2">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login to Dashboard
                        </button>
                    </div>
                </form>
                
                <div class="text-center mt-4">
                    <a href="/Masu%20Ko%20Jhol%28full%29/" class="text-decoration-none">
                        <i class="bi bi-arrow-left me-1"></i>Back to Main Site
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>