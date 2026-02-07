<?php
// Start session if not already started
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';

$bookings = [];
$res = $conn->query("SELECT * FROM bookings ORDER BY booking_date DESC, booking_time ASC");
if ($res) { while ($row = $res->fetch_assoc()) { $bookings[] = $row; } }

// Calculate stats
$pending_bookings = 0;
$confirmed_bookings = 0;
$total_people = 0;

foreach($bookings as $booking) {
    if($booking['status'] === 'pending') $pending_bookings++;
    if($booking['status'] === 'confirmed') $confirmed_bookings++;
    $total_people += $booking['people'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Table Bookings - Masu Ko Jhol</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="../assets/css/adminstyle.css">
</head>
<body>
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
           <a href="users.php">
              <span class="material-symbols-sharp">person_outline </span>
              <h3>costumers</h3>
           </a>
           <a href="analytics.php">
              <span class="material-symbols-sharp">insights </span>
              <h3>Analytics</h3>
           </a>
           <a href="orders_page.php">
              <span class="material-symbols-sharp">mail_outline </span>
              <h3>Orders</h3>
              <span class="msg_count">14</span>
           </a>
           <a href="menu.php">
              <span class="material-symbols-sharp">receipt_long </span>
              <h3>Menu</h3>
           </a>
           <a href="#" class="active">
              <span class="material-symbols-sharp">calendar_month </span>
              <h3>Bookings</h3>
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
           <h1>Table Booking Management</h1>

           <div class="date">
             <input type="date" >
           </div>

        <div class="insights">

           <!-- start seling -->
            <div class="sales">
               <span class="material-symbols-sharp">event_available</span>
               <div class="middle">

                 <div class="left">
                   <h3>Total Bookings</h3>
                   <h1><?php echo count($bookings); ?></h1>
                 </div>
                  <div class="progress">
                      <svg>
                         <circle  r="30" cy="40" cx="40"></circle>
                      </svg>
                      <div class="number"><p>100%</p></div>
                  </div>

               </div>
               <small>All reservations</small>
            </div>
           <!-- end seling -->
              <!-- start expenses -->
              <div class="expenses">
                <span class="material-symbols-sharp">check_circle</span>
                <div class="middle">
 
                  <div class="left">
                    <h3>Confirmed</h3>
                    <h1><?php echo $confirmed_bookings; ?></h1>
                  </div>
                   <div class="progress">
                       <svg>
                          <circle  r="30" cy="40" cx="40"></circle>
                       </svg>
                       <div class="number"><p><?php echo count($bookings) > 0 ? round(($confirmed_bookings/count($bookings))*100, 0) : 0; ?>%</p></div>
                   </div>
 
                </div>
                <small>Bookings confirmed</small>
             </div>
            <!-- end seling -->
               <!-- start seling -->
               <div class="income">
                <span class="material-symbols-sharp">pending_actions</span>
                <div class="middle">
 
                  <div class="left">
                    <h3>Pending</h3>
                    <h1><?php echo $pending_bookings; ?></h1>
                  </div>
                   <div class="progress">
                       <svg>
                          <circle  r="30" cy="40" cx="40"></circle>
                       </svg>
                       <div class="number"><p><?php echo count($bookings) > 0 ? round(($pending_bookings/count($bookings))*100, 0) : 0; ?>%</p></div>
                   </div>
 
                </div>
                <small>Bookings pending</small>
             </div>
            <!-- end seling -->

        </div>
       <!-- end insights -->
      <div class="recent_order">
         <h2>All Bookings</h2>
         <table> 
             <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date & Time</th>
                <th>People</th>
                <th>Message</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
             </thead>
              <tbody>
                <?php if (!$bookings): ?>
                  <tr><td colspan="9" class="text-center text-muted">No bookings found.</td></tr>
                <?php else: foreach ($bookings as $b): ?>
                  <tr>
                    <td><?php echo intval($b['id']); ?></td>
                    <td><?php echo htmlspecialchars($b['name']); ?></td>
                    <td><?php echo htmlspecialchars($b['email']); ?></td>
                    <td><?php echo htmlspecialchars($b['phone']); ?></td>
                    <td><?php echo htmlspecialchars($b['booking_date']); ?> <?php echo htmlspecialchars($b['booking_time']); ?></td>
                    <td><?php echo intval($b['people']); ?></td>
                    <td><a href="#" class="booking-message-link" data-message="<?php echo addslashes(htmlspecialchars($b['message'])); ?>" data-name="<?php echo addslashes(htmlspecialchars($b['name'])); ?>" onclick="showFullMessage(event, this); return false;"><?php echo htmlspecialchars(substr($b['message'], 0, 30)) . (strlen($b['message']) > 30 ? '...' : ''); ?></a></td>
                    <td>
                      <div class="booking-actions">
                        <select name="status" class="booking-status-select" id="status-<?php echo intval($b['id']); ?>">
                          <?php foreach (["pending","confirmed","rejected"] as $st): ?>
                            <option value="<?php echo $st; ?>" <?php echo $b['status']===$st?'selected':''; ?>><?php echo ucfirst($st); ?></option>
                          <?php endforeach; ?>
                        </select>
                        <button type="button" class="btn-update btn-booking-update" onclick="handleUpdate(<?php echo intval($b['id']); ?>)">Update</button>
                      </div>
                    </td>
                    <td>
                      <button type="button" class="btn-delete btn-booking-delete" onclick="handleDelete(<?php echo intval($b['id']); ?>)">Delete</button>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
         </table>
         
         <a href="index.php">Back to Dashboard</a>
      </div>

      </main>
      <!------------------
         end main
        ------------------->

      <!----------------
        start right main 
      ---------------------->    <div class="right">

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
         <img src="../assets/img/usersprofiles/fa29eed8-1427-4ec2-a671-e4e45a399f3c.jpg" alt="Admin Profile"/>
       </div>
    </div>
</div>

  <div class="recent_updates">
     <h2>Recent Activity</h2>
   <div class="updates">
      <div class="update">
         <div class="profile-photo">
            <img src="../assets/img/user-avatar.png" alt=""/>
         </div>
        <div class="message">
           <p><b>New Booking</b> received successfully</p>
        </div>
      </div>
      <div class="update">
        <div class="profile-photo">
        <img src="../assets/img/order-icon.png" alt=""/>
        </div>
       <div class="message">
          <p><b>Booking Status</b> updated to confirmed</p>
       </div>
     </div>
     <div class="update">
      <div class="profile-photo">
         <img src="../assets/img/menu-icon.png" alt=""/>
      </div>
     <div class="message">
        <p><b>Reservation</b> confirmed for customer</p>
     </div>
   </div>
  </div>
  </div>


   <div class="sales-analytics">
     <h2>Booking Statistics</h2>

      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">groups</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Total People</h3>
            <small class="text-muted">All bookings</small>
          </div>
          <h5 class="success">+<?php echo $total_people; ?></h5>
          <h3><?php echo $total_people; ?></h3>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">today</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Today's Bookings</h3>
            <small class="text-muted">Upcoming</small>
          </div>
          <?php 
          $today_bookings = 0;
          foreach($bookings as $booking) {
              if(date('Y-m-d', strtotime($booking['booking_date'])) === date('Y-m-d')) $today_bookings++;
          }
          ?>
          <h5 class="success">+<?php echo $today_bookings; ?></h5>
          <h3><?php echo $today_bookings; ?></h3>
        </div>
      </div>
      <div class="item">
        <div class="icon">
          <span class="material-symbols-sharp">calendar_month</span>
        </div>
        <div class="right">
          <div class="info">
            <h3>Pending Bookings</h3>
            <small class="text-muted">Need attention</small>
          </div>
          <h5 class="danger">-<?php echo $pending_bookings; ?></h5>
          <h3><?php echo $pending_bookings; ?></h3>
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

<script>
// Direct handler functions
function handleUpdate(bookingId) {
    const statusSelect = document.getElementById('status-' + bookingId);
    const newStatus = statusSelect.value;
    const button = event.target;
    
    // Disable button during processing
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Updating...';
    
    // Simple AJAX call
    fetch('../includes/update_booking_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            id: parseInt(bookingId),
            status: newStatus
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking status updated successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error updating booking status');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

function handleDelete(bookingId) {
    if (!confirm('Are you sure you want to delete booking #' + bookingId + '? This action cannot be undone.')) {
        return;
    }
    
    const button = event.target;
    
    // Disable button during processing
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Deleting...';
    
    // Simple AJAX call
    fetch('../includes/delete_booking.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            id: parseInt(bookingId)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Booking deleted successfully!');
            // Remove the row
            const row = button.closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(100px)';
            setTimeout(() => {
                row.remove();
            }, 300);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting booking');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Debug: Check if script is loading
document.addEventListener('DOMContentLoaded', function() {
    console.log('Booking page loaded');
    
    // Test button debug
    const testButton = document.getElementById('testButton');
    if (testButton) {
        testButton.addEventListener('click', function() {
            alert('Test button is working!');
            console.log('Test button clicked');
        });
        console.log('Test button found and listener added');
    } else {
        console.log('Test button NOT found');
    }
    
    // Debug: Check if buttons exist
    const updateButtons = document.querySelectorAll('.btn-booking-update');
    const deleteButtons = document.querySelectorAll('.btn-booking-delete');
    console.log('Update buttons found:', updateButtons.length);
    console.log('Delete buttons found:', deleteButtons.length);
    
    // Add direct event listeners for debugging
    updateButtons.forEach((button, index) => {
        console.log('Adding listener to update button', index);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Update button clicked!');
            alert('Update button clicked! Booking ID: ' + this.getAttribute('data-booking-id'));
        });
    });
    
    deleteButtons.forEach((button, index) => {
        console.log('Adding listener to delete button', index);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Delete button clicked!');
            alert('Delete button clicked! Booking ID: ' + this.getAttribute('data-booking-id'));
        });
    });
});

// Show full booking message in a modal
function showFullMessage(event, element) {
    event.preventDefault();
    const message = element.getAttribute('data-message');
    const name = element.getAttribute('data-name');
    
    // Create modal HTML
    const modalHtml = `
        <div id="messageModal" class="message-modal-overlay" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        ">
            <div class="message-modal-content" style="
                background: white;
                padding: 20px;
                border-radius: 8px;
                max-width: 500px;
                width: 80%;
                max-height: 80vh;
                overflow-y: auto;
                position: relative;
            ">
                <h3 style="margin-top: 0; color: #333;">Booking Message from: ${name}</h3>
                <p style="white-space: pre-wrap;">${message}
                </p>
                <button onclick="closeMessageModal()" style="
                    margin-top: 15px;
                    padding: 8px 16px;
                    background: #007bff;
                    color: white;
                    border: none;
                    border-radius: 4px;
                    cursor: pointer;
                ">Close</button>
            </div>
        </div>
    `;
    
    // Remove any existing modal
    const existingModal = document.getElementById('messageModal');
    if (existingModal) {
        existingModal.remove();
    }
    
    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeMessageModal() {
    const modal = document.getElementById('messageModal');
    if (modal) {
        modal.remove();
    }
}

// Close modal when clicking outside the content
document.addEventListener('click', function(event) {
    const modalOverlay = document.getElementById('messageModal');
    if (modalOverlay && event.target === modalOverlay) {
        closeMessageModal();
    }
});
</script>
<script src="../assets/js/adminscript.js"></script>
</body>
</html>