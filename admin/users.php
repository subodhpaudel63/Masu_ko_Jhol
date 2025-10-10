<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$users = [];
$res = $conn->query("SELECT id, email, user_type FROM users ORDER BY id DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $users[] = $row; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Users</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .toast-container { position: fixed; top: 1rem; right: 1rem; z-index: 2000; }
    .toast-success { background:#0f5132; color:#d1e7dd; }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function(){
      const t = document.getElementById('flash');
      if (t) { setTimeout(()=>{ t.classList.add('show'); setTimeout(()=>t.classList.remove('show'), 2500); }, 100); }
    });
  </script>
  </head>
  <body class="bg-dark text-light">
    <div class="toast-container">
      <?php if (!empty($_SESSION['msg'])): $m=$_SESSION['msg']; unset($_SESSION['msg']); if ($m['type']==='success'): ?>
        <div id="flash" class="toast align-items-center text-bg-success border-0 toast-success" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body small fw-semibold">✔ <?php echo htmlspecialchars($m['text']); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      <?php elseif ($m['type']==='error'): ?>
        <div id="flash" class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="d-flex">
            <div class="toast-body small fw-semibold">✖ <?php echo htmlspecialchars($m['text']); ?></div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
        </div>
      <?php endif; endif; ?>
    </div>
    <div class="container py-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Users</h3>
        <a class="btn btn-outline-light" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php">Back to Dashboard</a>
      </div>
      <div class="card bg-transparent border-secondary">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-dark align-middle mb-0">
              <thead><tr><th>#</th><th>Email</th><th>Role</th><th class="text-end">Actions</th></tr></thead>
              <tbody>
                <?php if (!$users): ?>
                  <tr><td colspan="4" class="text-center text-muted">No users found.</td></tr>
                <?php else: foreach ($users as $u): ?>
                  <tr>
                    <td><?php echo intval($u['id']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td>
                      <form action="../includes/update_user_role.php" method="post" class="d-flex gap-2 align-items-center justify-content-end">
                        <input type="hidden" name="user_id" value="<?php echo intval($u['id']); ?>">
                        <select name="role" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                          <option value="user" <?php echo $u['user_type']==='user'?'selected':''; ?>>user</option>
                          <option value="admin" <?php echo $u['user_type']==='admin'?'selected':''; ?>>admin</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">Save</button>
                      </form>
                    </td>
                    <td class="text-end">
                      <form action="../includes/delete_user.php" method="post" onsubmit="return confirm('Delete this user?');" class="d-inline">
                        <input type="hidden" name="user_id" value="<?php echo intval($u['id']); ?>">
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


