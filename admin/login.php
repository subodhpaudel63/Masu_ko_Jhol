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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Masu Ko Jhol</title>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:'Poppins', sans-serif;
}

body{
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
    background:linear-gradient(135deg,#1e1e1e,#2c3e50,#8e2de2,#ff512f);
    background-size:400% 400%;
    animation:gradientMove 12s ease infinite;
}

@keyframes gradientMove{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

.login-box{
    width:400px;
    padding:45px;
    border-radius:20px;
    backdrop-filter:blur(18px);
    background:rgba(0,0,0,0.55);
    border:1px solid rgba(255,255,255,0.1);
    box-shadow:0 0 40px rgba(255,140,0,0.4);
    color:#fff;
    text-align:center;
    position:relative;
}

.logo img{
    width:70px;
    margin-bottom:10px;
}

.logo h2{
    font-weight:600;
    letter-spacing:1px;
    color:#ffb347;
}

.welcome{
    margin:15px 0 30px;
}

.welcome h3{
    font-weight:600;
    color:#fff;
}

.welcome p{
    font-size:13px;
    color:#ccc;
}

.form-group{
    position:relative;
    margin-bottom:28px;
}

.form-group input{
    width:100%;
    padding:12px;
    background:transparent;
    border:none;
    border-bottom:2px solid rgba(255,255,255,0.5);
    outline:none;
    color:#fff;
    font-size:14px;
}

.form-group label{
    position:absolute;
    left:0;
    top:12px;
    font-size:14px;
    color:#aaa;
    transition:0.3s;
    pointer-events:none;
}

.form-group input:focus + label,
.form-group input:valid + label{
    top:-10px;
    font-size:11px;
    color:#ffb347;
}

.login-btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:30px;
    background:linear-gradient(to right,#ff512f,#ffb347);
    color:#fff;
    font-weight:600;
    cursor:pointer;
    transition:0.3s;
}

.login-btn:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 20px rgba(255,165,0,0.4);
}

.footer-text{
    margin-top:20px;
    font-size:12px;
    color:#aaa;
}

.alert{
    margin-bottom:20px;
    padding:10px;
    border-radius:5px;
}
.alert-success{
    background-color:#4caf50;
    color:#fff;
}
.alert-danger{
    background-color:#ff6b6b;
    color:#fff;
}
</style>
</head>

<body>

<div class="login-box">

    <div class="logo">
        <!-- <img src="../assets/images/logo.png" alt="Masu Ko Jhol Logo"> -->
        <h2>Masu Ko Jhol</h2>
    </div>

    <div class="welcome">
        <h3>Welcome Back, Admin </h3>
        <p>Please login to access the dashboard</p>
    </div>

    <?php if (isset($_SESSION['msg'])): ?>
        <div class="alert <?php echo $_SESSION['msg']['type'] === 'success' ? 'alert-success' : 'alert-danger'; ?>">
            <?php echo htmlspecialchars($_SESSION['msg']['text']); ?>
        </div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>

    <form action="admin_login_process.php" method="POST">

        <div class="form-group">
            <input type="email" name="email" required>
            <label>Admin Email</label>
        </div>

        <div class="form-group">
            <input type="password" name="password" required>
            <label>Password</label>
        </div>

        <button type="submit" class="login-btn">Login to Dashboard</button>

        <div class="footer-text">
            © 2026 Masu Ko Jhol | Admin Panel
        </div>

    </form>

</div>

</body>
</html>
