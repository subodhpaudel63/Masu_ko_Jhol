<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

// Existing menu list
$items = [];
$res = $conn->query("SELECT menu_id, menu_name, menu_description, menu_price, menu_category, menu_image FROM menu ORDER BY created_at DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $items[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Menu</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>.thumb{width:64px;height:64px;object-fit:cover;border-radius:8px}</style>
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
      <h3 class="mb-0">Menu</h3>
      <a class="btn btn-outline-light" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php">Back to Dashboard</a>
    </div>

    <div class="row g-4">
      <div class="col-lg-5">
        <div class="card bg-transparent border-secondary h-100">
          <div class="card-body">
            <h5 class="mb-3">Add / Update Item</h5>
            <form action="../includes/menu_add.php" method="post" enctype="multipart/form-data" class="mb-3">
              <div class="mb-2"><input class="form-control bg-dark text-light border-secondary" type="text" name="menu_name" placeholder="Name" required></div>
              <div class="mb-2"><textarea class="form-control bg-dark text-light border-secondary" name="menu_description" rows="2" placeholder="Description" required></textarea></div>
              <div class="mb-2"><input class="form-control bg-dark text-light border-secondary" type="number" step="0.01" name="menu_price" placeholder="Price" required></div>
              <div class="mb-2">
                <select class="form-select bg-dark text-light border-secondary" name="menu_category" required>
                  <option value="starter">starter</option>
                  <option value="breakfast">breakfast</option>
                  <option value="lunch">lunch</option>
                  <option value="dinner">dinner</option>
                  <option value="dinner">Dessert</option>
                </select>
              </div>
              <div class="mb-3"><input class="form-control bg-dark text-light border-secondary" type="file" name="menu_image" accept="image/*" required></div>
              <div class="d-grid"><button class="btn btn-primary" type="submit">Save New</button></div>
            </form>

            <form action="../includes/menu_edit.php" method="post" enctype="multipart/form-data">
              <h6 class="mb-2">Update Existing</h6>
              <div class="mb-2">
                <select class="form-select bg-dark text-light border-secondary" name="menu_id" required>
                  <option value="">Select item</option>
                  <?php foreach ($items as $it): ?>
                    <option value="<?php echo intval($it['menu_id']); ?>"><?php echo htmlspecialchars($it['menu_name']); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="mb-2"><input class="form-control bg-dark text-light border-secondary" type="text" name="menu_name" placeholder="New name"></div>
              <div class="mb-2"><textarea class="form-control bg-dark text-light border-secondary" name="menu_description" rows="2" placeholder="New description"></textarea></div>
              <div class="mb-2"><input class="form-control bg-dark text-light border-secondary" type="number" step="0.01" name="menu_price" placeholder="New price"></div>
              <div class="mb-2">
                <select class="form-select bg-dark text-light border-secondary" name="menu_category">
                  <option value="">Change category…</option>
                  <option value="starter">starter</option>
                  <option value="breakfast">breakfast</option>
                  <option value="lunch">lunch</option>
                  <option value="dinner">dinner</option>
                  <option value="dinner">Dessert</option>
                </select>
              </div>
              <div class="mb-2"><input class="form-control bg-dark text-light border-secondary" type="file" name="menu_image" accept="image/*"></div>
              <input type="hidden" name="existing_image" value="">
              <div class="d-grid"><button class="btn btn-primary" type="submit">Update</button></div>
            </form>
          </div>
        </div>
      </div>

      <div class="col-lg-7">
        <div class="card bg-transparent border-secondary h-100">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-dark align-middle mb-0">
                <thead><tr><th>#</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th class="text-end">Delete</th></tr></thead>
                <tbody>
                  <?php if (!$items): ?>
                    <tr><td colspan="6" class="text-center text-muted">No menu items yet.</td></tr>
                  <?php else: foreach ($items as $it): ?>
                    <tr>
                      <td><?php echo intval($it['menu_id']); ?></td>
                      <td><img class="thumb" src="/<?php echo ltrim($it['menu_image'], '/'); ?>" alt=""></td>
                      <td><?php echo htmlspecialchars($it['menu_name']); ?></td>
                      <td><?php echo htmlspecialchars($it['menu_category']); ?></td>
                      <td>₹<?php echo number_format((float)$it['menu_price'], 2); ?></td>
                      <td class="text-end">
                        <form action="../includes/menu_delete.php" method="post" onsubmit="return confirm('Delete this item?');">
                          <input type="hidden" name="menu_id" value="<?php echo intval($it['menu_id']); ?>">
                          <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
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
    </div>
  </div>
  <p class="text-center pt-4 mt-3 pt-lg-0">&copy; <span id="copyrightCurrentYear"></span> <b> Masu Ko Jhol.</b> All rights reserved. Design by <a href="https://www.instagram.com/subodh_543/" class="fw-bold author-name">Subodh Paudel</a></p>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

