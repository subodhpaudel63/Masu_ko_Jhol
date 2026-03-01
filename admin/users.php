<?php
// Start session if not already started
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$users = [];
$res = $conn->query("SELECT id, email, user_type, created_at, user_img FROM users ORDER BY id DESC");
if ($res) { while ($row = $res->fetch_assoc()) { $users[] = $row; } }

// Calculate stats
$total_users = count($users);
$admin_users = 0;

foreach($users as $user) {
    if($user['user_type'] === 'admin') $admin_users++;
}
// All users are considered active since there's no is_active column
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Users Management - Masu Ko Jhol</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="../assets/css/adminstyle.css">
  <link rel="stylesheet" href="../assets/css/toast_styles.css">
</head>
<body>
<div id="toastContainer" class="toast-container"></div>
   <div class="container">
      <aside>
           
         <div class="top">
           <div class="logo">
             <h2>Masu <span class="danger"> ko jhol</span> </h2>
           </div>
           <div class="close" id="close_btn">
            <span class="material-symbols-sharp">
              close
              </span>
           </div>
         </div>
         <!-- end top -->
          <div class="sidebar">

            <a href="./index.php">
              <span class="material-symbols-sharp">grid_view </span>
              <h3>Dashbord</h3>
           </a>
           <a href="#" class="active">
              <span class="material-symbols-sharp">person_outline </span>
              <h3>costumers</h3>
           </a>
           <a href="analytics.php">
              <span class="material-symbols-sharp">insights </span>
              <h3>Analytics</h3>
           </a>
           <a href="myorder.php">
              <span class="material-symbols-sharp">mail_outline </span>
              <h3>Orders</h3>
              <span class="msg_count">14</span>
           </a>
           <a href="menu.php">
              <span class="material-symbols-sharp">receipt_long </span>
              <h3>Menu</h3>
           </a>
           <a href="bookings.php">
              <span class="material-symbols-sharp">calendar_month </span>
              <h3>Bookings</h3>
           </a>

           <a href="feedback.php">
              <span class="material-symbols-sharp">Feedback </span>
              <h3>Feedback</h3>
           </a>
           
           <a href="#">
              <span class="material-symbols-sharp">settings </span>
              <h3>settings</h3>
           </a>
           <a href="#">
              <span class="material-symbols-sharp">add </span>
              <h3>Add Product</h3>
           </a>
           <a href="../includes/logout.php">
              <span class="material-symbols-sharp">logout </span>
              <h3>logout</h3>
           </a>
             
          </div>
      </aside>
      <!-- --------------
        end asid
      -------------------- -->

      <!-- --------------
        start main part
      --------------- -->

      <main>
           <h1>Users Management</h1>

           <!-- Session Messages -->
           <?php if (isset($_SESSION['msg'])): ?>
             <script>
               document.addEventListener('DOMContentLoaded', function() {
                 <?php if ($_SESSION['msg']['type'] === 'success'): ?>
                 ToastNotifications.success('<?php echo addslashes(htmlspecialchars($_SESSION['msg']['text'])); ?>');
                 <?php else: ?>
                 ToastNotifications.error('<?php echo addslashes(htmlspecialchars($_SESSION['msg']['text'])); ?>');
                 <?php endif; ?>
               });
             </script>
             <?php unset($_SESSION['msg']); ?>
           <?php endif; ?>

           <div class="date">
             <input type="date" >
           </div>

           <!-- Add User Form -->
           <div class="recent_order mb-4">
             <h2>Add New User/Admin</h2>
             <div class="form-container">
               <form action="../includes/add_admin.php" method="post" class="add-user-form">
                 <div class="form-group">
                   <label for="email">Email</label>
                   <input type="email" id="email" name="email" required>
                 </div>
                 <div class="form-group">
                   <label for="password">Password</label>
                   <input type="password" id="password" name="password" required minlength="6">
                 </div>
                 <div class="form-group">
                   <label for="user_type">User Type</label>
                   <select id="user_type" name="user_type">
                     <option value="user">Regular User</option>
                     <option value="admin">Admin</option>
                   </select>
                 </div>
                 <div class="form-group">
                   <button type="submit" class="btn-primary">Add User</button>
                 </div>
               </form>
             </div>
           </div>

        <div class="insights">

           <!-- start seling -->
            <div class="sales">
               <span class="material-symbols-sharp">group</span>
               <div class="middle">

                 <div class="left">
                   <h3>Total Users</h3>
                   <h1><?php echo $total_users; ?></h1>
                 </div>
                  <div class="progress">
                      <svg>
                         <circle  r="30" cy="40" cx="40"></circle>
                      </svg>
                      <div class="number"><p>100%</p></div>
                  </div>

               </div>
               <small>All registered users</small>
            </div>
           <!-- end seling -->
              <!-- start expenses -->
              <div class="expenses">
                <span class="material-symbols-sharp">admin_panel_settings</span>
                <div class="middle">
 
                  <div class="left">
                    <h3>Admin Users</h3>
                    <h1><?php echo $admin_users; ?></h1>
                  </div>
                   <div class="progress">
                       <svg>
                          <circle  r="30" cy="40" cx="40"></circle>
                       </svg>
                       <div class="number"><p><?php echo $total_users > 0 ? round(($admin_users/$total_users)*100, 0) : 0; ?>%</p></div>
                   </div>
 
                </div>
                <small>Administrators</small>
             </div>
            <!-- end seling -->
               <!-- start seling -->
               <div class="income">
                <span class="material-symbols-sharp">person</span>
                <div class="middle">
 
                  <div class="left">
                    <h3>Regular Users</h3>
                    <h1><?php echo $total_users - $admin_users; ?></h1>
                  </div>
                   <div class="progress">
                       <svg>
                          <circle  r="30" cy="40" cx="40"></circle>
                       </svg>
                       <div class="number"><p><?php echo $total_users > 0 ? round((($total_users - $admin_users)/$total_users)*100, 0) : 0; ?>%</p></div>
                   </div>
 
                </div>
                <small>Normal accounts</small>
             </div>
            <!-- end seling -->

        </div>
       <!-- end insights -->
      <div class="recent_order">
         <h2>All Users</h2>
         <table> 
             <thead>
              <tr>
                <th>User ID</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Created</th>
                <th>Image</th>
                <th>Actions</th>
              </tr>
             </thead>
              <tbody>
                <?php if (!$users): ?>
                  <tr><td colspan="6" class="text-center text-muted">No users found.</td></tr>
                <?php else: foreach ($users as $user): ?>
                  <tr>
                    <td><?php echo intval($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                      <span class="badge <?php echo $user['user_type'] === 'admin' ? 'bg-danger' : 'bg-success'; ?>">
                        <?php echo ucfirst(htmlspecialchars($user['user_type'])); ?>
                      </span>
                    </td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                      <?php if (!empty($user['user_img']) && $user['user_img'] !== '../assets/img/usersprofiles/profilepic.jpg'): ?>
                        <img src="../<?php echo htmlspecialchars($user['user_img']); ?>" alt="User Image" width="40" height="40" class="rounded">
                      <?php else: ?>
                        <span class="text-muted">No image</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div class="d-flex gap-2">
                        <?php if ($user['user_type'] === 'admin' && $user['email'] !== 'subodhpaudel0000@gmail.com'): ?>
                          <!-- Admin user - show delete button only (except main admin) -->
                          <form action="../includes/delete_user.php" method="post" class="action-form" onsubmit="return confirm('Delete this admin permanently? This action cannot be undone.');">
                            <input type="hidden" name="user_id" value="<?php echo intval($user['id']); ?>">
                            <button type="submit" class="btn-danger">Delete Admin</button>
                          </form>
                          <span class="admin-badge">Admin User</span>
                        <?php elseif ($user['user_type'] !== 'admin'): ?>
                          <!-- Regular user - show both buttons -->
                          <form action="../includes/update_user_role.php" method="post" class="action-form">
                            <input type="hidden" name="user_id" value="<?php echo intval($user['id']); ?>">
                            <input type="hidden" name="role" value="<?php echo $user['user_type'] === 'user' ? 'admin' : 'user'; ?>">
                            <button type="submit" class="btn-warning">
                              <?php echo $user['user_type'] === 'user' ? 'Make Admin' : 'Make User'; ?>
                            </button>
                          </form>
                          
                          <form action="../includes/delete_user.php" method="post" class="action-form" onsubmit="return confirm('Delete this user permanently?');">
                            <input type="hidden" name="user_id" value="<?php echo intval($user['id']); ?>">
                            <button type="submit" class="btn-danger">Delete</button>
                          </form>
                        <?php else: ?>
                          <!-- Main admin - no actions -->
                          <span class="admin-badge">Main Admin</span>
                        <?php endif; ?>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
         </table>
         <a href="users.php" class="btn-primary">View All Users</a>
      </div>

      </main>
      <!------------------
         end main
        ------------------->

      <!----------------
        start right main 
      ---------------------->
    <div class="right">

<div class="top">
   <button id="menu_bar">
     <span class="material-symbols-sharp">menu</span>
   </button>

   <div class="theme-toggler">
     <span class="material-symbols-sharp active">light_mode</span>
     <span class="material-symbols-sharp">dark_mode</span>
   </div>
    <div class="profile">
       <div class="info">
           <p><b>Subodh Admin</b></p>
           <p>Administrator</p>
           <small class="text-muted">Online</small>
       </div>
       <div class="profile-photo">
         <img src="../assets/img/usersprofiles/adminpic.jpg" alt="Admin Profile"/>
       </div>
    </div>
</div>

  <div class="recent_updates">
     <h2>Recent Activity</h2>
   <div class="updates">
      <div class="update">
         <div class="profile-photo">
            <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
         </div>
        <div class="message">
           <p><b>New User</b> registered successfully</p>
        </div>
      </div>
      <div class="update">
        <div class="profile-photo">
        <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
        </div>
       <div class="message">
          <p><b>User Role</b> updated successfully</p>
       </div>
     </div>
     <div class="update">
      <div class="profile-photo">
         <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
      </div>
     <div class="message">
        <p><b>User Status</b> changed to active</p>
     </div>
   </div>
  </div>
  </div>


   <div class="sales-analytics">
     <h2>User Statistics</h2>

      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">person_add</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>New Users</h3>
            <small class="text-muted">This week</small>
          </div>
          <?php 
          $new_users = 0;
          foreach($users as $user) {
              if(strtotime($user['created_at']) > strtotime('-7 days')) $new_users++;
          }
          ?>
          <h5 class="success">+<?php echo $new_users; ?></h5>
          <h3><?php echo $new_users; ?></h3>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">groups</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Regular Users</h3>
            <small class="text-muted">Normal accounts</small>
          </div>
          <?php 
          $regular_users = 0;
          foreach($users as $user) {
              if($user['user_type'] === 'user') $regular_users++;
          }
          ?>
          <h5 class="success">+<?php echo $regular_users; ?></h5>
          <h3><?php echo $regular_users; ?></h3>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">image</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Users with Images</h3>
            <small class="text-muted">Profile pictures</small>
          </div>
          <?php 
          $users_with_images = 0;
          foreach($users as $user) {
              if(!empty($user['user_img']) && $user['user_img'] !== '../assets/img/usersprofiles/profilepic.jpg') $users_with_images++;
          }
          ?>
          <h5 class="success">+<?php echo $users_with_images; ?></h5>
          <h3><?php echo $users_with_images; ?></h3>
        </div>
      </div>
   
  
</div>

      <div class="add_product">
            <div>
            <span class="material-symbols-sharp">add</span>
            </div>
     </div>
</div>

   </div>

<script src="../assets/js/toast_notifications.js"></script>
<script src="../assets/js/adminscript.js"></script>
</body>
</html>