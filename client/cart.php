<?php
session_start();
include_once __DIR__ . '/../includes/db.php';
include_once __DIR__ . '/../includes/auth_check.php';

// Check if user is logged in
$user = getUserFromCookie();

if (!$user) {
    header('Location: ../login.php');
    exit;
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
                
                if (isset($_SESSION['cart'][$index]) && $quantity > 0) {
                    $_SESSION['cart'][$index]['quantity'] = $quantity;
                    $_SESSION['cart'][$index]['total'] = $_SESSION['cart'][$index]['price'] * $quantity;
                    $response['success'] = true;
                    $response['message'] = 'Quantity updated';
                }
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
                }
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
                $success_count = 0;
                
                foreach ($_SESSION['cart'] as $item) {
                    $stmt = $conn->prepare("INSERT INTO orders (menu_id, email, menu_name, quantity, price, total_price, mobile, address, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
                    $stmt->bind_param("issiddss", 
                        $item['menu_id'],
                        $email,
                        $item['name'],
                        $item['quantity'],
                        $item['price'],
                        $item['total'],
                        $_POST['mobile'] ?? '',
                        $_POST['address'] ?? ''
                    );
                    
                    if ($stmt->execute()) {
                        $success_count++;
                    }
                    $stmt->close();
                }
                
                if ($success_count > 0) {
                    $_SESSION['cart'] = []; // Clear cart after successful checkout
                    $response['success'] = true;
                    $response['message'] = 'Order placed successfully!';
                } else {
                    $response['message'] = 'Error placing order';
                }
            } else {
                $response['message'] = 'Cart is empty';
            }
            break;
    }
    
    echo json_encode($response);
    exit;
}

$cart = $_SESSION['cart'] ?? [];
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

    <header class="bg-white">
        <div class="container header my-3 d-none d-lg-flex">
            <div class="logo">
                <a href="../index.php">
                    <i class="fa fa-utensils me-3 text-dark"></i>
                    <h1 class="mb-0 text-dark">Masu Ko Jhol</h1>
                </a>
            </div>
            <div class="menus">
                <ul class="d-flex mb-0">
                    <li class="list-unstyled py-2">
                        <a class="text-decoration-none text-uppercase p-4 text-dark" href="../index.php">Home</a>
                    </li>
                    <li class="list-unstyled py-2">
                        <a class="text-decoration-none text-uppercase p-4 text-dark" href="../aboutus.php">About</a>
                    </li>
                    <li class="list-unstyled py-2">
                        <a class="text-decoration-none text-uppercase p-4 text-dark" href="../menu.php">Menu</a>
                    </li>
                    <li class="list-unstyled py-2">
                        <a class="text-decoration-none text-uppercase p-4 text-dark" href="../contactus.php">Contact</a>
                    </li>
                </ul>
            </div>
            <div class="icons d-flex align-items-center">
                <a class="text-decoration-none" href="../client/myorder.php">
                    <i class="fa fa-shopping-bag me-3 text-dark"></i>
                </a>
                <div class="d-flex align-items-center">
                    <a href="../logout.php" class="nav-button">Logout</a>
                </div>
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
                <a href="../menu.php" class="btn btn-orange">Browse Menu</a>
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
                                    <img src="<?php echo $item['image'] ? '../' . $item['image'] : '../assets/images/menu/menu-item-1.png'; ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="item-image">
                                </div>
                                <div class="col-md-4">
                                    <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                    <p class="text-muted">Rs. <?php echo number_format((float)$item['price'], 2); ?> each</p>
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
                                    <strong>Rs. <?php echo number_format((float)$item['total'], 2); ?></strong>
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
                            <a href="../menu.php" class="btn btn-outline-primary">
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
                            <span>Rs. <?php echo number_format(array_sum(array_column($cart, 'total')), 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Delivery:</span>
                            <span>Free</span>
                        </div>
                        <hr class="text-white">
                        <div class="d-flex justify-content-between mb-3">
                            <h5>Total:</h5>
                            <h5>Rs. <?php echo number_format(array_sum(array_column($cart, 'total')), 2); ?></h5>
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
                            Total amount: <strong>Rs. <?php echo number_format(array_sum(array_column($cart, 'total')), 2); ?></strong>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="checkoutBtn">
                            Place Order (Rs. <?php echo number_format(array_sum(array_column($cart, 'total')), 2); ?>)
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
            let quantity = newQuantity ? parseInt(newQuantity) : (parseInt(document.querySelectorAll('.quantity-input')[index].value) + change);
            
            if (quantity < 1) quantity = 1;
            
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
                    location.reload();
                }
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
                    alert(data.message);
                    window.location.href = 'myorder.php';
                } else {
                    alert('Error: ' + data.message);
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