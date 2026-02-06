// Booking page specific JavaScript functions

// Show toast notification
function showBookingToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${message}</span>
            <button class="toast-close">&times;</button>
        </div>
    `;
    
    // Add to container
    const container = document.querySelector('.toast-container') || document.body;
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
    
    // Close button handler
    const closeButton = toast.querySelector('.toast-close');
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            toast.remove();
        });
    }
}

// Update booking status
function updateBookingStatus(bookingId, newStatus, button) {
    // Disable button during processing
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Updating...';
    
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
            showBookingToast('Booking status updated successfully!', 'success');
        } else {
            showBookingToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showBookingToast('Error updating booking status', 'error');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Delete booking
function deleteBooking(bookingId, button) {
    if (!confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
        return;
    }
    
    // Disable button during processing
    const originalText = button.textContent;
    button.disabled = true;
    button.textContent = 'Deleting...';
    
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
            showBookingToast('Booking deleted successfully!', 'success');
            // Remove the row from the table
            const row = button.closest('tr');
            row.style.opacity = '0';
            row.style.transform = 'translateX(100px)';
            setTimeout(() => {
                row.remove();
            }, 300);
        } else {
            showBookingToast('Error: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showBookingToast('Error deleting booking', 'error');
    })
    .finally(() => {
        // Re-enable button
        button.disabled = false;
        button.textContent = originalText;
    });
}

// Initialize booking page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle booking update buttons
    const updateButtons = document.querySelectorAll('.btn-booking-update');
    updateButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            const select = this.closest('.booking-actions').querySelector('.booking-status-select');
            const newStatus = select.value;
            
            updateBookingStatus(bookingId, newStatus, this);
        });
    });
    
    // Handle booking delete buttons
    const deleteButtons = document.querySelectorAll('.btn-booking-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.getAttribute('data-booking-id');
            deleteBooking(bookingId, this);
        });
    });
});

// Admin Dashboard JavaScript

// DOM Content Loaded Event
document.addEventListener('DOMContentLoaded', function() {
    // Toggle sidebar
    const menuBar = document.getElementById('menu_bar');
    const sideBar = document.querySelector('aside');
    const closeBtn = document.getElementById('close_btn');
    
    if (menuBar) {
        menuBar.addEventListener('click', function() {
            sideBar.style.display = 'block';
        });
    }
    
    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            sideBar.style.display = 'none';
        });
    }
    
    // Theme toggler functionality
    const themeToggler = document.querySelector('.theme-toggler');
    if (themeToggler) {
        const themeIcons = themeToggler.querySelectorAll('span');
        themeIcons.forEach(icon => {
            icon.addEventListener('click', function() {
                themeIcons.forEach(i => i.classList.remove('active'));
                this.classList.add('active');
                
                // Toggle dark/light theme
                document.body.classList.toggle('dark-theme-variables');
            });
        });
    }
    
    // Smooth animations for cards
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);
    
    // Observe insight cards
    const insightCards = document.querySelectorAll('.sales, .expenses, .income');
    insightCards.forEach(card => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
        observer.observe(card);
    });
    
    // Form submission handlers
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Add loading state to buttons
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                const originalText = submitBtn.textContent;
                submitBtn.textContent = 'Processing...';
                
                // Re-enable after a delay or based on response
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }, 3000);
            }
        });
    });
});

// Utility functions
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${message}</span>
            <button class="toast-close">&times;</button>
        </div>
    `;
    
    // Add to container
    const container = document.querySelector('.toast-container') || document.body;
    container.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        toast.remove();
    }, 5000);
    
    // Close button handler
    const closeButton = toast.querySelector('.toast-close');
    if (closeButton) {
        closeButton.addEventListener('click', () => {
            toast.remove();
        });
    }
}

// Function to format currency
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

// Function to update order status
function updateOrderStatus(orderId, newStatus) {
    // This would typically make an AJAX call to update the order status
    console.log(`Updating order ${orderId} to status: ${newStatus}`);
    
    fetch('../includes/order_status_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `order_id=${orderId}&status=${newStatus}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Order status updated successfully', 'success');
        } else {
            showToast('Failed to update order status', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showToast('Error updating order status', 'error');
    });
}