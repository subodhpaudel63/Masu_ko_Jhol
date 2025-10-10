<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Table Bookings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script>
    const POLL_MS = 5000;
    async function loadBookings(){
      try {
        const r = await fetch('../includes/bookings_fetch.php');
        const j = await r.json();
        const tbody = document.getElementById('tbody');
        if (!j.ok || !Array.isArray(j.bookings) || j.bookings.length===0){
          tbody.innerHTML = '<tr><td colspan="10" class="text-center text-muted">No bookings</td></tr>';
          return;
        }
        tbody.innerHTML = j.bookings.map(b=>`
          <tr>
            <td>${b.id}</td>
            <td>${b.name}</td>
            <td>${b.email}</td>
            <td>${b.phone}</td>
            <td>${b.booking_date} ${b.booking_time}</td>
            <td>${b.people}</td>
            <td>${b.message ? b.message.substring(0, 50) + (b.message.length > 50 ? '...' : '') : 'No message'}</td>
            <td>
              <select class="form-select form-select-sm status-select" data-id="${b.id}">
                <option value="pending" ${b.status === 'pending' ? 'selected' : ''}>Pending</option>
                <option value="confirmed" ${b.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                <option value="rejected" ${b.status === 'rejected' ? 'selected' : ''}>Rejected</option>
              </select>
            </td>
            <td>
              <button class="btn btn-sm btn-success accept-btn" data-id="${b.id}">Accept</button>
            </td>
            <td>
              <button class="btn btn-sm btn-danger delete-btn" data-id="${b.id}">Delete</button>
            </td>
          </tr>
        `).join('');
        
        // Add event listeners
        document.querySelectorAll('.status-select').forEach(select => {
          select.addEventListener('change', function() {
            updateBookingStatus(this.dataset.id, this.value);
          });
        });
        
        document.querySelectorAll('.accept-btn').forEach(button => {
          button.addEventListener('click', function() {
            updateBookingStatus(this.dataset.id, 'confirmed');
          });
        });
        
        document.querySelectorAll('.delete-btn').forEach(button => {
          button.addEventListener('click', function() {
            if (confirm('Are you sure you want to delete this booking?')) {
              deleteBooking(this.dataset.id);
            }
          });
        });
      } catch(e) { console.error(e); }
    }
    
    async function updateBookingStatus(id, status) {
      try {
        const response = await fetch('../includes/update_booking_status.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: id, status: status })
        });
        
        const result = await response.json();
        if (result.success) {
          loadBookings(); // Reload the bookings
        } else {
          alert('Failed to update booking status: ' + result.message);
        }
      } catch(e) {
        console.error(e);
        alert('Error updating booking status');
      }
    }
    
    async function deleteBooking(id) {
      try {
        const response = await fetch('../includes/delete_booking.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ id: id })
        });
        
        const result = await response.json();
        if (result.success) {
          loadBookings(); // Reload the bookings
        } else {
          alert('Failed to delete booking: ' + result.message);
        }
      } catch(e) {
        console.error(e);
        alert('Error deleting booking');
      }
    }
    
    document.addEventListener('DOMContentLoaded', ()=>{ 
      loadBookings(); 
      setInterval(loadBookings, POLL_MS); 
    });
  </script>
</head>
<body class="bg-dark text-light">
  <div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Table Bookings</h3>
      <a class="btn btn-outline-light" href="/Masu%20Ko%20Jhol%28full%29/admin/index.php">Back to Dashboard</a>
    </div>
    <div class="card bg-transparent border-secondary">
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-dark align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Date & Time</th>
                <th>People</th>
                <th>Message</th>
                <th>Status</th>
                <th>Accept</th>
                <th>Delete</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <tr>
                <td colspan="10" class="text-center text-muted">Loading...</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>