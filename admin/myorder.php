<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$orders = [];
$res = $conn->query("SELECT order_id, menu_id, menu_name, email, mobile, address, quantity, price, total_price, status, order_time FROM orders ORDER BY order_id DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $orders[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-dark text-light">
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000;">
    <?php if (!empty($_SESSION['msg'])): $m=$_SESSION['msg']; unset($_SESSION['msg']); if ($m['type']==='success'): ?>
      <div class="toast show align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" style="background:#0f5132;">
        <div class="d-flex">
          <div class="toast-body small fw-semibold">✔ <?php echo htmlspecialchars($m['text']); ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php elseif ($m['type']==='error'): ?>
      <div class="toast show align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body small fw-semibold">✖ <?php echo htmlspecialchars($m['text']); ?></div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
      </div>
    <?php endif; endif; ?>
  </div>
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Orders</h3>
      <a class="btn btn-outline-light" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php">Back to Dashboard</a>
    </div>

    <?php if (!empty($_SESSION['msg'])): $m = $_SESSION['msg']; unset($_SESSION['msg']); ?>
      <div class="alert alert-<?php echo $m['type'] === 'success' ? 'success' : 'danger'; ?>">
        <?php echo htmlspecialchars($m['text']); ?>
      </div>
    <?php endif; ?>

    <div class="card bg-transparent border-secondary">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Item</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$orders): ?>
                <tr><td colspan="10" class="text-center text-muted">No orders found.</td></tr>
              <?php else: foreach ($orders as $o): ?>
                <tr>
                  <td><?php echo intval($o['order_id']); ?></td>
                  <td><?php echo htmlspecialchars($o['menu_name']); ?></td>
                  <td><?php echo htmlspecialchars($o['email']); ?></td>
                  <td><?php echo htmlspecialchars($o['mobile']); ?></td>
                  <td><?php echo htmlspecialchars(substr($o['address'], 0, 30)) . (strlen($o['address']) > 30 ? '...' : ''); ?></td>
                  <td><?php echo intval($o['quantity']); ?></td>
                  <td>₹<?php echo number_format((float)$o['price'], 2); ?></td>
                  <td>₹<?php echo number_format((float)$o['total_price'], 2); ?></td>
                  <td>
                    <form action="../includes/order_status_update.php" method="post" class="d-flex gap-2 align-items-center">
                      <input type="hidden" name="order_id" value="<?php echo intval($o['order_id']); ?>">
                      <select name="status" class="form-select form-select-sm bg-dark text-light border-secondary">
                        <?php foreach (["Confirmed","Shipping","Ongoing","Delivering"] as $st): ?>
                          <option value="<?php echo $st; ?>" <?php echo $o['status']===$st?'selected':''; ?>><?php echo $st; ?></option>
                        <?php endforeach; ?>
                      </select>
                      <button type="submit" class="btn btn-sm btn-primary">Update</button>
                    </form>
                  </td>
                  <td>
                    <form action="../includes/delete_order.php" method="post" onsubmit="return confirm('Delete this order?');">
                      <input type="hidden" name="order_id" value="<?php echo intval($o['order_id']); ?>">
                      <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
 
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
  </body>
  </html>