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
  <link rel="stylesheet" href="../assets/css/adminstyle.css?v=<?= filemtime(__DIR__ . '/../assets/css/adminstyle.css') ?>">
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
                 <th>Actions</th>
              </tr>
             </thead>
              <tbody>
                <?php if (!$bookings): ?>
                  <tr><td colspan="8" class="text-center text-muted">No bookings found.</td></tr>
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
                        <button type="button" class="btn-update btn-booking-update" data-status="<?php echo $b['status']; ?>" onclick="handleView(<?php echo intval($b['id']); ?>)">View</button>
                        <button type="button" class="btn-delete btn-booking-delete" onclick="handleDelete(<?php echo intval($b['id']); ?>)">Delete</button>
                      </div>
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
           <p><b>New Booking</b> received successfully</p>
        </div>
      </div>
      <div class="update">
        <div class="profile-photo">
        <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
        </div>
       <div class="message">
          <p><b>Booking Status</b> updated to confirmed</p>
       </div>
     </div>
     <div class="update">
      <div class="profile-photo">
         <img src="../assets/img/usersprofiles/profilepic.jpg" alt=""/>
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
function handleView(bookingId) {
    const rows = document.querySelectorAll('table tbody tr');
    let row = null;
    for (let r of rows) {
        const idCell = r.querySelector('td:first-child');
        if (idCell && idCell.textContent.trim() == bookingId) {
            row = r;
            break;
        }
    }

    if (!row) return;

    const cells = row.querySelectorAll('td');
    const id = cells[0].textContent.trim();
    const name = cells[1].textContent.trim();
    const email = cells[2].textContent.trim();
    const phone = cells[3].textContent.trim();
    const dateTime = cells[4].textContent.trim();
    const people = cells[5].textContent.trim();
    const status = cells[7].querySelector('.btn-booking-update').getAttribute('data-status');

    const messageLink = cells[6].querySelector('.booking-message-link');
    const message = messageLink ? messageLink.getAttribute('data-message') : cells[6].textContent.trim();

    const modalHtml = `
        <div id="viewBookingModal" class="view-booking-overlay" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease-in-out;
        ">
            <div class="view-booking-modal" style="
                background: white;
                padding: 30px;
                border-radius: 12px;
                max-width: 500px;
                width: 90%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            ">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600;">Booking Details #${id}</h3>
                    <span class="booking-status-text status-${status}" style="padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase;">${status}</span>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Name:</strong></td><td style="padding: 10px 0 10px 16px; border-bottom: 1px solid #e5e7eb;">${name}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Email:</strong></td><td style="padding: 10px 0 10px 16px; border-bottom: 1px solid #e5e7eb;">${email}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Phone:</strong></td><td style="padding: 10px 0 10px 16px; border-bottom: 1px solid #e5e7eb;">${phone}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>Date & Time:</strong></td><td style="padding: 10px 0 10px 16px; border-bottom: 1px solid #e5e7eb;">${dateTime}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #e5e7eb;"><strong>People:</strong></td><td style="padding: 10px 0 10px 16px; border-bottom: 1px solid #e5e7eb;">${people}</td></tr>
                    <tr><td style="padding: 10px 0;"><strong>Message:</strong></td><td style="padding: 10px 0 10px 16px; white-space: pre-wrap;">${message || 'No message'}</td></tr>
                </table>
                <div style="display: flex; justify-content: flex-end; margin-top: 25px;">
                    <button onclick="closeViewBookingModal()" style="
                        padding: 8px 24px;
                        background: #007bff;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        font-size: 14px;
                    " onmouseover="this.style.background='#0056b3'" onmouseout="this.style.background='#007bff'">
                        Close
                    </button>
                </div>
            </div>
        </div>
    `;

    const existingModal = document.getElementById('viewBookingModal');
    if (existingModal) {
        existingModal.remove();
    }

    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeViewBookingModal() {
    const modal = document.getElementById('viewBookingModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease-in-out forwards';
        setTimeout(() => modal.remove(), 300);
    }
}

function handleDelete(bookingId) {
    // Prevent any default behavior
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    showDeleteConfirmation(bookingId);
}

function showDeleteConfirmation(bookingId) {
    // Create modal HTML with animations
    const modalHtml = `
        <div id="deleteConfirmModal" class="delete-confirm-overlay" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease-in-out;
        ">
            <div class="delete-confirm-modal" style="
                background: white;
                padding: 30px;
                border-radius: 12px;
                max-width: 400px;
                width: 90%;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            ">
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="
                        width: 60px;
                        height: 60px;
                        margin: 0 auto 15px;
                        background: #fee2e2;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
                    ">
                        <svg width="30" height="30" fill="none" stroke="#dc2626" viewBox="0 0 24 24" style="stroke-width: 2;">
                            <path d="M12 9v6M9 12h6M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"></path>
                        </svg>
                    </div>
                    <h3 style="margin: 0 0 10px 0; color: #1f2937; font-size: 18px; font-weight: 600;">Delete Booking?</h3>
                    <p style="margin: 0; color: #6b7280; font-size: 14px;">Are you sure you want to delete booking #${bookingId}? This action cannot be undone.</p>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 25px;">
                    <button onclick="closeDeleteConfirmation()" style="
                        flex: 1;
                        padding: 10px 15px;
                        background: #f3f4f6;
                        color: #1f2937;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        font-size: 14px;
                    " onmouseover="this.style.background='#e5e7eb'" onmouseout="this.style.background='#f3f4f6'">
                        Cancel
                    </button>
                    <button onclick="confirmDelete(${bookingId})" style="
                        flex: 1;
                        padding: 10px 15px;
                        background: #dc2626;
                        color: white;
                        border: none;
                        border-radius: 6px;
                        cursor: pointer;
                        font-weight: 500;
                        transition: all 0.2s ease;
                        font-size: 14px;
                    " onmouseover="this.style.background='#b91c1c'" onmouseout="this.style.background='#dc2626'">
                        Delete
                    </button>
                </div>
            </div>
        </div>

        <style>
            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes slideUp {
                from { 
                    transform: translateY(30px);
                    opacity: 0;
                }
                to { 
                    transform: translateY(0);
                    opacity: 1;
                }
            }
            @keyframes scaleIn {
                from {
                    transform: scale(0.8);
                    opacity: 0;
                }
                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }
            @keyframes fadeOut {
                from { opacity: 1; }
                to { opacity: 0; }
            }
        </style>
    `;

    // Remove any existing modal
    const existingModal = document.getElementById('deleteConfirmModal');
    if (existingModal) {
        existingModal.remove();
    }

    // Add modal to body
    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function closeDeleteConfirmation() {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease-in-out forwards';
        setTimeout(() => modal.remove(), 300);
    }
}

function confirmDelete(bookingId) {
    const modal = document.getElementById('deleteConfirmModal');
    if (modal) {
        modal.style.animation = 'fadeOut 0.3s ease-in-out forwards';
        setTimeout(() => modal.remove(), 300);
    }

    // Find the delete button for this booking
    const buttons = document.querySelectorAll('.btn-booking-delete');
    let button = null;
    for (let btn of buttons) {
        if (btn.onclick && btn.onclick.toString().includes(bookingId)) {
            button = btn;
            break;
        }
    }
    
    // If button not found, find it by closest tr
    if (!button) {
        buttons.forEach(btn => {
            const row = btn.closest('tr');
            if (row) {
                const idCell = row.querySelector('td:first-child');
                if (idCell && idCell.textContent.trim() == bookingId) {
                    button = btn;
                }
            }
        });
    }

    if (!button) {
        button = document.querySelector('.btn-booking-delete');
    }

    // Disable button during processing
    const originalText = button ? button.textContent : 'Delete';
    if (button) {
        button.disabled = true;
        button.textContent = 'Deleting...';
    }

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
            // Find and remove the row
            let row = null;
            const rows = document.querySelectorAll('table tbody tr');
            for (let r of rows) {
                const idCell = r.querySelector('td:first-child');
                if (idCell && idCell.textContent.trim() == bookingId) {
                    row = r;
                    break;
                }
            }
            if (row) {
                row.style.opacity = '0';
                row.style.transform = 'translateX(100px)';
                setTimeout(() => {
                    row.remove();
                }, 300);
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
    })
    .finally(() => {
        // Re-enable button
        if (button) {
            button.disabled = false;
            button.textContent = originalText;
        }
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const modalOverlay = document.getElementById('deleteConfirmModal');
    if (modalOverlay && event.target === modalOverlay) {
        closeDeleteConfirmation();
    }
});

// Debug: Check if script is loading
document.addEventListener('DOMContentLoaded', function() {
    console.log('Booking page loaded');
    
    // Test button debug
    const testButton = document.getElementById('testButton');
    if (testButton) {
        testButton.addEventListener('click', function() {
            console.log('Test button clicked');
        });
        console.log('Test button found and listener added');
    } else {
        console.log('Test button NOT found');
    }
    
    // Debug: Check if buttons exist
    const viewButtons = document.querySelectorAll('.btn-booking-update');
    const deleteButtons = document.querySelectorAll('.btn-booking-delete');
    console.log('View buttons found:', viewButtons.length);
    console.log('Delete buttons found:', deleteButtons.length);
    
    // Add direct event listeners for debugging
    viewButtons.forEach((button, index) => {
        console.log('Adding listener to view button', index);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('View button clicked!');
        });
    });
    
    deleteButtons.forEach((button, index) => {
        console.log('Adding listener to delete button', index);
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Delete button clicked!');
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

// Close view booking modal when clicking outside
document.addEventListener('click', function(event) {
    const modalOverlay = document.getElementById('viewBookingModal');
    if (modalOverlay && event.target === modalOverlay) {
        closeViewBookingModal();
    }
});
</script>
<!-- Note: adminscript.js removed to avoid conflicts with custom delete confirmation -->
   <script src="../assets/js/adminscript.js"></script>
</body>
</html>
