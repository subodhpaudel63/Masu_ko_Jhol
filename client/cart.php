<?php
session_start();
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../config/bootstrap.php';

// Check if user is logged in
$user = getUserFromCookie();

// Profile image (from secure cookies)
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

if (!$user) {
    header('Location: ../login.php');
    exit;
}

// Ensure cart is properly indexed with numeric keys
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = array_values($_SESSION['cart']);
}

// Handle AJAX requests for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];
    
    switch ($_POST['ajax_action']) {
        case 'get_cart':
            $response['success'] = true;
            $response['data'] = $_SESSION['cart'] ?? [];
            $response['count'] = count($_SESSION['cart'] ?? []);
            break;
            
        case 'update_quantity':
            if (isset($_SESSION['cart'])) {
                $index = intval($_POST['index']);
                $quantity = intval($_POST['quantity']);
                
                // Log for debugging
                error_log("Update quantity: index=$index, quantity=$quantity");
                error_log("Cart before: " . print_r($_SESSION['cart'], true));
                
                if (isset($_SESSION['cart'][$index]) && $quantity > 0) {
                    $_SESSION['cart'][$index]['quantity'] = $quantity;
                    $_SESSION['cart'][$index]['total'] = $_SESSION['cart'][$index]['price'] * $quantity;
                    $response['success'] = true;
                    $response['message'] = 'Quantity updated';
                    error_log("Quantity updated successfully for index $index");
                } else {
                    $response['message'] = "Item not found at index $index or invalid quantity $quantity";
                    error_log("Failed to update quantity: index=$index, quantity=$quantity");
                }
            } else {
                $response['message'] = "Cart session not found";
                error_log("Cart session not found");
            }
            break;
            
        case 'remove_item':
            if (isset($_SESSION['cart'])) {
                $index = intval($_POST['index']);
                if (isset($_SESSION['cart'][$index])) {
                    unset($_SESSION['cart'][$index]);
                    $_SESSION['cart'] = array_values($_SESSION['cart']); // Reindex array
                    $response['success'] = true;
                    $response['message'] = 'Item removed from cart';
                    $response['count'] = count($_SESSION['cart']);
                } else {
                    $response['message'] = "Item not found at index $index";
                }
            } else {
                $response['message'] = "Cart session not found";
            }
            break;
            
        case 'clear_cart':
            $_SESSION['cart'] = [];
            $response['success'] = true;
            $response['message'] = 'Cart cleared';
            break;
            
        case 'checkout':
            if (!empty($_SESSION['cart'])) {
                // Process checkout - create orders for each cart item
                $email = $_SESSION['email'] ?? '';
                $mobile = trim($_POST['mobile'] ?? '');
                $address = trim($_POST['address'] ?? '');
                
                // Validate required fields
                if (empty($email) || empty($mobile) || empty($address)) {
                    $response['message'] = 'Missing required checkout information';
                    break;
                }
                
                if (!preg_match('/^\d{10}$/', $mobile)) {
                    $response['message'] = 'Invalid mobile number format';
                    break;
                }
                
                $success_count = 0;
                $error_occurred = false;
                
                foreach ($_SESSION['cart'] as $item) {
                    // Validate item data
                    if (!isset($item['menu_id']) || !isset($item['name']) || !isset($item['quantity']) || 
                        !isset($item['price']) || !isset($item['total'])) {
                        error_log('Invalid item data in cart: ' . print_r($item, true));
                        continue;
                    }
                    
                    $stmt = $conn->prepare("INSERT INTO orders (menu_id, email, menu_name, quantity, price, total_price, mobile, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                    
                    if (!$stmt) {
                        error_log('Prepare statement failed: ' . $conn->error);
                        $error_occurred = true;
                        break;
                    }
                    
                    $result = $stmt->bind_param("issiddss", 
                        $item['menu_id'],
                        $email,
                        $item['name'],
                        $item['quantity'],
                        $item['price'],
                        $item['total'],
                        $mobile,
                        $address
                    );
                    
                    if (!$result) {
                        error_log('Bind param failed: ' . $stmt->error);
                        $stmt->close();
                        $error_occurred = true;
                        break;
                    }
                    
                    if ($stmt->execute()) {
                        $success_count++;
                    } else {
                        error_log('Execute failed: ' . $stmt->error);
                        $error_occurred = true;
                    }
                    $stmt->close();
                }
                
                if ($success_count > 0 && !$error_occurred) {
                    $_SESSION['cart'] = []; // Clear cart after successful checkout
                    $response['success'] = true;
                    $response['message'] = 'Order placed successfully!';
                } else {
                    $response['message'] = 'Error placing order' . ($error_occurred ? ': Database error occurred' : '');
                }
            } else {
                $response['message'] = 'Cart is empty';
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

// Reindex cart to ensure numeric indices for JavaScript
$cart = array_values($_SESSION['cart'] ?? []);

// Log cart contents for debugging (only for checkout issues)
if (isset($_POST['ajax_action']) && $_POST['ajax_action'] === 'checkout') {
    error_log('Cart contents at checkout: ' . print_r($cart, true));
    error_log('POST data at checkout: ' . print_r($_POST, true));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Cart | Masu Ko Jhol</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
    body { background:#f8f9fa; }
    .cart-container { 
        background: white; 
        border-radius: 15px; 
        box-shadow: 0 0 30px rgba(0,0,0,0.1); 
        margin: 2rem 0; 
        padding: 2rem; 
    }
    .cart-item { 
        border-bottom: 1px solid #eee; 
        padding: 1.5rem 0; 
    }
    .cart-item:last-child { 
        border-bottom: none; 
    }
    .item-image { 
        width: 100px; 
        height: 100px; 
        object-fit: cover; 
        border-radius: 10px; 
    }
    .quantity-input { 
        width: 80px; 
        text-align: center; 
    }
    .btn-danger-outline { 
        border: 2px solid #dc3545; 
        color: #dc3545; 
        background: transparent; 
    }
    .btn-danger-outline:hover { 
        background: #dc3545; 
        color: white; 
    }
    .summary-card { 
        background: linear-gradient(135deg, #ff6a00, #d32f2f); 
        color: white; 
        border-radius: 15px; 
        padding: 2rem; 
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
                    <?php if (!$user): ?>
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
                <?php if ($user): ?>
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" role="button" id="profileMenu" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo url($profileImg); ?>" alt="profile" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileMenu">
                        <li><h6 class="dropdown-header"><?php echo htmlspecialchars($user['email'] ?? ''); ?></h6></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="./update_password.php"><i class="fa fa-key me-2"></i>Update Password</a></li>
                        <li><a class="dropdown-item" href="<?php echo url('./includes/logout.php'); ?>"><i class="fa fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <div class="container mt-4">
        <h2 class="text-center mb-4">Your Shopping Cart</h2>
        
        <?php if (empty($cart)): ?>
            <div class="cart-container text-center py-5">
                <i class="bi bi-cart-x" style="font-size: 5rem; color: #ccc;"></i>
                <h3 class="mt-3">Your cart is empty</h3>
                <p class="text-muted">Add some delicious items from our menu!</p>
                <a href="menu.php" class="btn btn-orange">Browse Menu</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-container">
                        <h4>Items in Cart (<?php echo count($cart); ?>)</h4>
                        <?php foreach ($cart as $index => $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="<?php echo $item['image'] ? './' . ltrim($item['image'], '/') : './assets/images/menu/menu-item-1.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name'] ?? $item['menu_name']); ?>"
                                         class="item-image">
                                </div>
                                <div class="col-md-4">
                                    <h5><?php echo htmlspecialchars($item['name'] ?? $item['menu_name']); ?></h5>
                                    <p class="text-muted" data-price="<?php echo $item['price']; ?>">रु<?php echo number_format((float)$item['price'], 2); ?> each</p>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="updateQuantity(<?php echo $index; ?>, -1)">-</button>
                                        <input type="number" class="form-control quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" onchange="updateQuantity(<?php echo $index; ?>, 0, this.value)">
                                        <button class="btn btn-outline-secondary" type="button" 
                                                onclick="updateQuantity(<?php echo $index; ?>, 1)">+</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <strong class="item-total">रु<?php echo number_format((float)($item['total'] ?? ($item['price'] * $item['quantity'])), 2); ?></strong>
                                </div>
                                <div class="col-md-1">
                                    <button class="btn btn-danger-outline" onclick="removeItem(<?php echo $index; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="d-flex justify-content-between mt-3">
                            <button class="btn btn-outline-danger" onclick="clearCart()">
                                <i class="bi bi-cart-x"></i> Clear Cart
                            </button>
                            <a href="menu.php" class="btn btn-outline-primary">
                                <i class="bi bi-plus-circle"></i> Add More Items
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h4>Order Summary</h4>
                        <hr class="text-white">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <?php 
                            $subtotal = 0;
                            foreach ($cart as $item) {
                                $subtotal += $item['total'] ?? ($item['price'] * $item['quantity']);
                            }
                            ?>
                            <span class="subtotal-amount">रु<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery:</span>
                            <span>Free</span>
                        </div>
                        <hr class="text-white">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Total:</h5>
                            <?php 
                            $total = 0;
                            foreach ($cart as $item) {
                                $total += $item['total'] ?? ($item['price'] * $item['quantity']);
                            }
                            ?>
                            <h5 class="total-amount">रु<?php echo number_format($total, 2); ?></h5>
                        </div>
                        <button class="btn btn-light w-100" data-bs-toggle="modal" data-bs-target="#checkoutModal">
                            Proceed to Checkout
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Checkout Modal -->
    <div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkoutModalLabel">Delivery Information</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="checkoutForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="mobile" class="form-label">Mobile Number *</label>
                            <input type="tel" class="form-control" id="mobile" name="mobile" required>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Delivery Address *</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            Total amount: <strong class="modal-subtotal">रु<?php echo number_format($total, 2); ?></strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="checkoutBtn">
                            Place Order (रु<?php echo number_format($total, 2); ?>)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once __DIR__ . '/../footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateQuantity(index, change, newQuantity = null) {
            let inputs = document.querySelectorAll('.quantity-input');
            let input = inputs[index];
            let currentValue = parseInt(input.value);
            let quantity = newQuantity ? parseInt(newQuantity) : (currentValue + change);
            
            if (quantity < 1) quantity = 1;
            
            input.value = quantity;
            
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'ajax_action=update_quantity&index=' + index + '&quantity=' + quantity
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload the page to ensure all prices are updated properly
                    location.reload();
                } else {
                    // Show error message
                    showToast('Failed to update quantity: ' + (data.message || 'Unknown error'), 'error');
                    // Revert the input value
                    input.value = currentValue;
                }
            }).catch(error => {
                console.error('Error updating quantity:', error);
                // Show error message
                showToast('Error updating quantity: ' + error.message, 'error');
                // Revert the input value
                input.value = currentValue;
            });
        }
        
        function updateCartSummary() {
            // Recalculate cart totals
            let totalSum = 0;
            document.querySelectorAll('.item-total').forEach(element => {
                let text = element.textContent.replace('रु', '').replace(/,/g, '');
                totalSum += parseFloat(text) || 0;
            });
            
            // Update subtotal and total in the summary card
            let subtotalElement = document.querySelector('.subtotal-amount');
            if(subtotalElement) {
                subtotalElement.textContent = 'रु' + totalSum.toFixed(2);
            }
            
            let totalAmountElement = document.querySelector('.total-amount');
            if(totalAmountElement) {
                totalAmountElement.textContent = 'रु' + totalSum.toFixed(2);
            }
            
            // Update the amounts in the modal
            let modalSubtotalElement = document.querySelector('.modal-subtotal');
            if(modalSubtotalElement) {
                modalSubtotalElement.textContent = 'रु' + totalSum.toFixed(2);
            }
            
            let checkoutBtn = document.getElementById('checkoutBtn');
            if(checkoutBtn) {
                checkoutBtn.textContent = 'Place Order (रु' + totalSum.toFixed(2) + ')';
            }
        }
        
        // Show toast notification
        function showToast(message, type = 'success') {
            // Remove any existing toasts
            let existingToasts = document.querySelectorAll('.custom-toast');
            existingToasts.forEach(toast => toast.remove());
            
            // Create toast container if it doesn't exist
            let toastContainer = document.querySelector('.toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                toastContainer.style.zIndex = '9999';
                document.body.appendChild(toastContainer);
            }
            
            // Create toast element
            const toastDiv = document.createElement('div');
            toastDiv.className = `toast custom-toast ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white`;
            toastDiv.setAttribute('role', 'alert');
            toastDiv.setAttribute('aria-live', 'assertive');
            toastDiv.setAttribute('aria-atomic', 'true');
            
            toastDiv.innerHTML = `
                <div class="toast-body d-flex justify-content-between align-items-center">
                    <span>${message}</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
                </div>
            `;
            
            toastContainer.appendChild(toastDiv);
            
            // Initialize and show the toast
            const toast = new bootstrap.Toast(toastDiv, {
                delay: 7000
            });
            toast.show();
            
            // Remove toast after it's hidden
            toastDiv.addEventListener('hidden.bs.toast', function() {
                this.remove();
            });
        }
        
        function removeItem(index) {
            if (confirm('Are you sure you want to remove this item?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ajax_action=remove_item&index=' + index
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
        
        function clearCart() {
            if (confirm('Are you sure you want to clear your cart?')) {
                fetch('cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'ajax_action=clear_cart'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            }
        }
        
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('ajax_action', 'checkout');
            
            const checkoutBtn = document.getElementById('checkoutBtn');
            const originalText = checkoutBtn.textContent;
            
            checkoutBtn.textContent = 'Processing...';
            checkoutBtn.disabled = true;
            
            fetch('cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = 'myorder.php';
                    }, 1500); // Wait 1.5 seconds before redirecting to let user see the toast
                } else {
                    showToast('Error: ' + data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing order');
            })
            .finally(() => {
                checkoutBtn.textContent = originalText;
                checkoutBtn.disabled = false;
            });
        });
    </script>
</body>
</html>