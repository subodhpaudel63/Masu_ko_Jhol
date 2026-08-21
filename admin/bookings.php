<?php
declare(strict_types=1);
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/admin_auth.php';
require_admin();
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/process_no_shows.php';

// Process no-shows before fetching
processNoShows($conn);

$tz = new DateTimeZone(RESTAURANT_TIMEZONE);
$serverNow = (new DateTime('now', $tz))->format('Y-m-d H:i:s');

$bookings = [];
$res = $conn->query("SELECT b.*, t.table_name, t.capacity FROM bookings b LEFT JOIN restaurant_tables t ON b.table_id = t.id ORDER BY b.booking_date DESC, b.booking_time ASC");
if ($res) { 
    while ($row = $res->fetch_assoc()) { 
        $bookings[] = $row; 
    } 
}

// Calculate stats
$pending_bookings = 0;
$confirmed_bookings = 0;
$checkedin_bookings = 0;
$noshow_bookings = 0;
$total_people = 0;

foreach($bookings as $booking) {
    if($booking['status'] === 'Pending') $pending_bookings++;
    if($booking['status'] === 'Confirmed') $confirmed_bookings++;
    if($booking['status'] === 'Checked-in') $checkedin_bookings++;
    if($booking['status'] === 'No-show') $noshow_bookings++;
    $total_people += $booking['people'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Table Bookings - Mero Bhoj</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Sharp:opsz,wght,FILL,GRAD@48,400,0,0" />
  <link rel="stylesheet" href="../assets/css/adminstyle.css?v=<?= filemtime(__DIR__ . '/../assets/css/adminstyle.css') ?>">
  <style>
    .action-btn { padding: 6px 12px; border-radius: 4px; border: none; cursor: pointer; font-size: 13px; font-weight: 500; margin-right: 5px; color: white; transition: background 0.2s; }
    .btn-confirm { background-color: #059669; }
    .btn-confirm:hover { background-color: #047857; }
    .btn-cancel { background-color: #dc2626; }
    .btn-cancel:hover { background-color: #b91c1c; }
    .btn-checkin { background-color: #0284c7; }
    .btn-checkin:hover { background-color: #0369a1; }
    .btn-complete { background-color: #4f46e5; }
    .btn-complete:hover { background-color: #4338ca; }
    .btn-view { background-color: #4b5563; }
    .btn-view:hover { background-color: #374151; }
    .btn-delete { background-color: #ef4444; margin-top: 5px; padding: 4px 8px; font-size: 11px; }
    .btn-delete:hover { background-color: #dc2626; }

    .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; }
    .status-Pending { background: #fef3c7; color: #d97706; }
    .status-Confirmed { background: #d1fae5; color: #059669; }
    .status-Checked-in { background: #e0f2fe; color: #0284c7; }
    .status-Completed { background: #e0e7ff; color: #4338ca; }
    .status-Cancelled { background: #fee2e2; color: #dc2626; }
    .status-No-show { background: #f3f4f6; color: #4b5563; }
    
    .grace-timer { font-size: 13px; font-weight: 500; }
    .grace-warning { color: #d97706; }
    .grace-danger { color: #dc2626; }
    .grace-expired { color: #ef4444; font-weight: bold; }
  </style>
</head>
<body>
   <div class="container">
      <aside>
         <div class="top">
           <div class="logo">
             <h2>Masu <span class="danger"> ko jhol</span> </h2>
           </div>
           <div class="close" id="close_btn">
            <span class="material-symbols-sharp">close</span>
           </div>
         </div>
          <div class="sidebar">
            <a href="./index.php">
              <span class="material-symbols-sharp">grid_view</span>
              <h3>Dashbord</h3>
           </a>
           <a href="users.php">
              <span class="material-symbols-sharp">person_outline</span>
              <h3>costumers</h3>
           </a>
           <a href="analytics.php">
              <span class="material-symbols-sharp">insights</span>
              <h3>Analytics</h3>
           </a>
           <a href="orders_page.php">
              <span class="material-symbols-sharp">mail_outline</span>
              <h3>Orders</h3>
              <span class="msg_count">14</span>
           </a>
           <a href="menu.php">
              <span class="material-symbols-sharp">receipt_long</span>
              <h3>Menu</h3>
           </a>
           <a href="#" class="active">
              <span class="material-symbols-sharp">calendar_month</span>
              <h3>Bookings</h3>
           </a>
           <a href="feedback.php">
              <span class="material-symbols-sharp">Feedback</span>
              <h3>Feedback</h3>
           </a>
           <a href="#">
              <span class="material-symbols-sharp">settings</span>
              <h3>settings</h3>
           </a>
           <a href="#">
              <span class="material-symbols-sharp">add</span>
              <h3>Add Product</h3>
           </a>
           <a href="../includes/logout.php">
              <span class="material-symbols-sharp">logout</span>
              <h3>logout</h3>
           </a>
          </div>
      </aside>

      <main>
           <h1>Table Booking Management</h1>
           <div class="date">
             <input type="date" >
           </div>

        <div class="insights">
            <div class="sales">
               <span class="material-symbols-sharp">event_available</span>
               <div class="middle">
                 <div class="left">
                   <h3>Total Bookings</h3>
                   <h1><?php echo count($bookings); ?></h1>
                 </div>
               </div>
               <small>All reservations</small>
            </div>
            <div class="expenses">
                <span class="material-symbols-sharp">check_circle</span>
                <div class="middle">
                  <div class="left">
                    <h3>Confirmed & Checked-in</h3>
                    <h1><?php echo $confirmed_bookings + $checkedin_bookings; ?></h1>
                  </div>
                </div>
                <small>Active bookings</small>
             </div>
             <div class="income">
                <span class="material-symbols-sharp">pending_actions</span>
                <div class="middle">
                  <div class="left">
                    <h3>Pending</h3>
                    <h1><?php echo $pending_bookings; ?></h1>
                  </div>
                </div>
                <small>Bookings pending</small>
             </div>
        </div>

      <div class="recent_order">
         <h2>All Bookings</h2>
         <table> 
             <thead>
              <tr>
                <th>ID</th>
                <th>Name / Phone</th>
                <th>Date & Time</th>
                <th>Table</th>
                <th>Status</th>
                <th>Grace Timer</th>
                <th>Actions</th>
              </tr>
             </thead>
              <tbody>
                <?php if (!$bookings): ?>
                  <tr><td colspan="7" class="text-center text-muted">No bookings found.</td></tr>
                <?php else: foreach ($bookings as $b): 
                    $dateObj = DateTime::createFromFormat('Y-m-d', $b['booking_date'], $tz);
                    $timeObj = DateTime::createFromFormat('H:i:s', $b['booking_time'], $tz);
                    $formattedDate = $dateObj ? $dateObj->format('F j, Y') : $b['booking_date'];
                    $formattedTime = $timeObj ? $timeObj->format('g:i A') : $b['booking_time'];
                ?>
                  <tr data-id="<?php echo intval($b['id']); ?>">
                    <td><?php echo intval($b['id']); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($b['name']); ?></strong><br>
                        <small><?php echo htmlspecialchars($b['phone']); ?></small>
                    </td>
                    <td><?php echo $formattedDate; ?><br><?php echo $formattedTime; ?></td>
                    <td><?php echo htmlspecialchars($b['table_name'] ?? 'N/A'); ?><br><small>Cap: <?php echo intval($b['capacity'] ?? 0); ?></small></td>
                    <td><span class="status-badge status-<?php echo $b['status']; ?>"><?php echo htmlspecialchars($b['status']); ?></span></td>
                    <td>
                        <?php if ($b['status'] === 'Confirmed' && $b['grace_end_at']): ?>
                            <div class="countdown-container" data-grace-end="<?php echo htmlspecialchars($b['grace_end_at']); ?>">
                                --:--
                            </div>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                      <div class="booking-actions">
                        <button class="action-btn btn-view" onclick='handleView(<?php echo json_encode($b); ?>, "<?php echo $formattedDate; ?>", "<?php echo $formattedTime; ?>")'>View</button>
                        
                        <?php if ($b['status'] === 'Pending'): ?>
                            <button class="action-btn btn-confirm" onclick="updateStatus(<?php echo $b['id']; ?>, 'Confirmed')">Confirm</button>
                            <button class="action-btn btn-cancel" onclick="updateStatus(<?php echo $b['id']; ?>, 'Cancelled')">Cancel</button>
                        <?php elseif ($b['status'] === 'Confirmed'): ?>
                            <button class="action-btn btn-checkin" onclick="updateStatus(<?php echo $b['id']; ?>, 'Checked-in')">Check-in</button>
                            <button class="action-btn btn-cancel" onclick="updateStatus(<?php echo $b['id']; ?>, 'Cancelled')">Cancel</button>
                        <?php elseif ($b['status'] === 'Checked-in'): ?>
                            <button class="action-btn btn-complete" onclick="updateStatus(<?php echo $b['id']; ?>, 'Completed')">Complete</button>
                        <?php endif; ?>
                        <br>
                        <button class="action-btn btn-delete" onclick="handleDelete(<?php echo $b['id']; ?>)">Delete</button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
         </table>
      </div>
      </main>

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
      </div>
   </div>

<script>
const serverNow = "<?php echo $serverNow; ?>";
let clientTimeOffset = 0;

function initTimeOffset() {
    const serverTime = new Date(serverNow.replace(' ', 'T'));
    const clientTime = new Date();
    clientTimeOffset = serverTime.getTime() - clientTime.getTime();
}

function getServerTime() {
    return new Date(new Date().getTime() + clientTimeOffset);
}

function updateCountdowns() {
    const containers = document.querySelectorAll('.countdown-container');
    const now = getServerTime();

    containers.forEach(container => {
        const graceEndStr = container.getAttribute('data-grace-end');
        if (!graceEndStr) return;
        
        const graceEnd = new Date(graceEndStr.replace(' ', 'T'));
        // Assume booking time is grace_end - 20 mins
        const bookingTime = new Date(graceEnd.getTime() - (20 * 60 * 1000));

        if (now < bookingTime) {
            container.innerHTML = `<span style="color:#6b7280;font-size:12px;">Starts at ${bookingTime.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>`;
        } else if (now >= graceEnd) {
            container.innerHTML = '<span class="grace-expired">Grace period expired</span>';
            // Wait for backend to mark as No-show, or trigger reload eventually
        } else {
            const diff = graceEnd - now;
            const mins = Math.floor(diff / 60000);
            const secs = Math.floor((diff % 60000) / 1000);
            const timeStr = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            
            let colorClass = 'grace-timer';
            if (mins < 5) colorClass += ' grace-danger';
            else if (mins < 10) colorClass += ' grace-warning';
            else colorClass += ' grace-timer';

            container.innerHTML = `<div style="font-size:11px;color:#d97706;">⚠️ Booking started</div><div class="${colorClass}">⏳ ${timeStr} left</div>`;
        }
    });
}

function updateStatus(id, newStatus) {
    if (!confirm(`Change booking #${id} status to ${newStatus}?`)) return;
    
    fetch('update_booking_status_ajax.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id, status: newStatus })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(e => {
        console.error(e);
        alert('Network error updating status.');
    });
}

function handleView(booking, formattedDate, formattedTime) {
    let actionButtons = '';
    const status = booking.status;
    const id = booking.id;

    if (status === 'Pending') {
        actionButtons += `<button onclick="updateStatus(${id}, 'Confirmed')" class="action-btn btn-confirm">Confirm</button>`;
        actionButtons += `<button onclick="updateStatus(${id}, 'Cancelled')" class="action-btn btn-cancel">Cancel</button>`;
    } else if (status === 'Confirmed') {
        actionButtons += `<button onclick="updateStatus(${id}, 'Checked-in')" class="action-btn btn-checkin">Check-in</button>`;
        actionButtons += `<button onclick="updateStatus(${id}, 'Cancelled')" class="action-btn btn-cancel">Cancel</button>`;
    } else if (status === 'Checked-in') {
        actionButtons += `<button onclick="updateStatus(${id}, 'Completed')" class="action-btn btn-complete">Complete</button>`;
    }

    const modalHtml = `
        <div id="viewBookingModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); display: flex; justify-content: center; align-items: center; z-index: 10000;">
            <div style="background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; font-size: 18px;">Booking Details #${id}</h3>
                    <span class="status-badge status-${status}">${status}</span>
                </div>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>Name:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid #eee;">${booking.name}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>Phone:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid #eee;">${booking.phone}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>Table:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid #eee;">${booking.table_name || 'N/A'} (Cap: ${booking.capacity})</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>Date & Time:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid #eee;">${formattedDate} ${formattedTime}</td></tr>
                    <tr><td style="padding: 10px 0; border-bottom: 1px solid #eee;"><strong>People:</strong></td><td style="padding: 10px 0; border-bottom: 1px solid #eee;">${booking.people}</td></tr>
                    <tr><td style="padding: 10px 0;"><strong>Message:</strong></td><td style="padding: 10px 0; white-space: pre-wrap;">${booking.message || 'No message'}</td></tr>
                </table>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 25px;">
                    <div>${actionButtons}</div>
                    <button onclick="document.getElementById('viewBookingModal').remove()" class="action-btn btn-view">Close</button>
                </div>
            </div>
        </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHtml);
}

function handleDelete(id) {
    if(!confirm(`Are you sure you want to delete booking #${id}?`)) return;
    fetch('../includes/delete_booking.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('Error deleting: ' + data.message);
        }
    });
}

initTimeOffset();
updateCountdowns();
setInterval(updateCountdowns, 1000);
</script>
</body>
</html>
