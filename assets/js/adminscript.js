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
    
    // Delete buttons are handled by the custom animated modal in bookings.php
    // Do NOT add event listeners here - it would create conflicts
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
            // Order status updated successfully
        } else {
            // Failed to update order status
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Error updating order status
    });
}

/* Admin page-specific helpers
   Keep page behavior centralized here so admin templates only load one JS file. */
function togglePw() {
    const input = document.getElementById('password');
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
}

function updateFilters() {
    const dateRange = document.getElementById('dateRange');
    const customDateFields = document.getElementById('customDateFields');
    if (!dateRange || !customDateFields) return;
    customDateFields.style.display = dateRange.value === 'custom' ? 'flex' : 'none';
}

function applyFilters() {
    const url = new URL(window.location.href);
    const dateRange = document.getElementById('dateRange');
    const startDate = document.getElementById('startDate');
    const endDate = document.getElementById('endDate');
    if (dateRange) url.searchParams.set('date_range', dateRange.value);
    if (startDate) url.searchParams.set('start_date', startDate.value);
    if (endDate) url.searchParams.set('end_date', endDate.value);
    window.location.href = url.toString();
}

function exportCSV() {
    window.location.href = 'export_csv.php' + window.location.search;
}

function exportPDF() {
    window.print();
}

function showFullMessage(event, anchor) {
    if (event) event.preventDefault();
    const modal = document.getElementById('messageModal');
    if (!modal || !anchor) return;
    const name = anchor.getAttribute('data-name') || '';
    const message = anchor.getAttribute('data-message') || '';
    const title = modal.querySelector('#messageModalTitle');
    const body = modal.querySelector('#messageModalBody');
    if (title) title.textContent = `Booking Message from: ${name}`;
    if (body) body.textContent = message;
    modal.style.display = 'flex';
}

function closeMessageModal() {
    const modal = document.getElementById('messageModal');
    if (modal) modal.style.display = 'none';
}

function showFullAddress(event, anchor) {
    if (event) event.preventDefault();
    const modal = document.getElementById('addressModal');
    if (!modal || !anchor) return;
    const orderId = anchor.getAttribute('data-order-id') || '';
    const address = anchor.getAttribute('data-address') || '';
    const title = modal.querySelector('#addressModalTitle');
    const body = modal.querySelector('#addressModalBody');
    if (title) title.textContent = `Full Address for Order #${orderId}`;
    if (body) body.textContent = address;
    modal.style.display = 'flex';
}

function closeAddressModal() {
    const modal = document.getElementById('addressModal');
    if (modal) modal.style.display = 'none';
}

function openFeedbackModal(name, message, rating, date) {
    const modal = document.getElementById('feedbackModal');
    if (!modal) return;
    const title = modal.querySelector('#modalTitle');
    const modalDate = modal.querySelector('#modalDate');
    const stars = modal.querySelector('#modalStars');
    const body = modal.querySelector('#modalMsgBody');
    if (title) title.textContent = name || 'Guest';
    if (modalDate) modalDate.textContent = date || '';
    if (stars) stars.textContent = '★'.repeat(Number(rating || 0));
    if (body) body.textContent = message || '';
    modal.style.display = 'flex';
}

function closeModal() {
    const modal = document.getElementById('feedbackModal');
    if (modal) modal.style.display = 'none';
}

function deleteFeedback(id, name, button) {
    if (!confirm(`Delete feedback from ${name}?`)) return;
    if (button) button.disabled = true;
    fetch(`../includes/delete_feedback.php?id=${encodeURIComponent(id)}`, { method: 'POST' })
        .then(() => window.location.reload())
        .catch(() => {
            alert('Unable to delete feedback right now.');
            if (button) button.disabled = false;
        });
}

function initAnalyticsCharts() {
    const salesCanvas = document.getElementById('salesChart');
    const statusCanvas = document.getElementById('orderStatusChart');
    if (!salesCanvas || !statusCanvas || typeof Chart === 'undefined') return;

    const salesDataEl = document.getElementById('analyticsSalesData');
    const orderStatsEl = document.getElementById('analyticsOrderStats');
    const salesData = salesDataEl ? JSON.parse(salesDataEl.textContent || '[]') : [];
    const orderStats = orderStatsEl ? JSON.parse(orderStatsEl.textContent || '[]') : [];

    new Chart(salesCanvas.getContext('2d'), {
        type: 'line',
        data: {
            labels: salesData.map((item) => item.date),
            datasets: [
                {
                    label: 'Revenue (Rs)',
                    data: salesData.map((item) => item.revenue),
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                },
                {
                    label: 'Orders',
                    data: salesData.map((item) => item.order_count),
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118, 75, 162, 0.1)',
                    tension: 0.4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true } },
        },
    });

    new Chart(statusCanvas.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: orderStats.map((item) => item.status),
            datasets: [{
                data: orderStats.map((item) => item.count),
                backgroundColor: ['#4CAF50', '#2196F3', '#FF9800', '#F44336'],
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } },
        },
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initAnalyticsCharts();
});

/* New order alert system
   Polls the backend for the latest order id, shows a popup, and plays a short tone. */
const MKJ_ORDER_POLL_INTERVAL_MS = 15000;
let mkjLatestOrderId = null;
let mkjOrderPollTimer = null;
let mkjOrderAlertBusy = false;
let mkjAudioContext = null;
let mkjAudioUnlocked = false;

function mkjPlayOrderSound() {
    try {
        const AudioContextClass = window.AudioContext || window.webkitAudioContext;
        if (!AudioContextClass) return;
        const context = mkjAudioContext || new AudioContextClass();
        mkjAudioContext = context;
        const oscillator = context.createOscillator();
        const gain = context.createGain();

        oscillator.type = 'sine';
        oscillator.frequency.value = 880;
        gain.gain.value = 0.0001;

        oscillator.connect(gain);
        gain.connect(context.destination);

        oscillator.start();
        gain.gain.exponentialRampToValueAtTime(0.18, context.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, context.currentTime + 0.55);
        oscillator.stop(context.currentTime + 0.56);

        oscillator.onended = () => {
            if (context.state !== 'closed') {
                context.close().catch(() => {});
            }
        };
    } catch (error) {
        console.warn('Unable to play order alert sound:', error);
    }
}

function mkjUnlockOrderAudio() {
    if (mkjAudioUnlocked) return;
    const AudioContextClass = window.AudioContext || window.webkitAudioContext;
    if (!AudioContextClass) return;

    try {
        mkjAudioContext = mkjAudioContext || new AudioContextClass();
        if (mkjAudioContext.state === 'suspended') {
            mkjAudioContext.resume().catch(() => {});
        }
        mkjAudioUnlocked = true;
    } catch (error) {
        console.warn('Unable to unlock order audio:', error);
    }
}

function mkjDismissOrderNotification() {
    const existing = document.getElementById('mkj-order-notification');
    if (existing) {
        existing.remove();
    }
}

function mkjShowOrderNotification(order) {
    mkjDismissOrderNotification();

    const overlay = document.createElement('div');
    overlay.className = 'order-notification-overlay';
    overlay.id = 'mkj-order-notification';
    overlay.innerHTML = `
        <div class="order-notification-card" role="dialog" aria-modal="true" aria-labelledby="mkj-order-title">
            <div class="order-notification-badge" aria-hidden="true">!</div>
            <h3 id="mkj-order-title">New Order Received</h3>
            <p>A new order has just arrived in the admin panel.</p>
            <p><strong>Order #:</strong> ${order.order_id}</p>
            <p><strong>Item:</strong> ${order.menu_name || 'Unknown item'}</p>
            <p><strong>Status:</strong> ${order.status || 'Pending'}</p>
            <p><strong>Total:</strong> Rs. ${Number(order.total_price || 0).toFixed(2)}</p>
            <div class="order-notification-actions">
                <button type="button" class="order-notification-secondary" id="mkj-order-later-btn">Later</button>
                <a class="order-notification-primary" href="/Masu%20Ko%20Jhol%28full%29/admin/orders_page.php">View Orders</a>
            </div>
        </div>
    `;

    document.body.appendChild(overlay);
    document.getElementById('mkj-order-later-btn')?.addEventListener('click', mkjDismissOrderNotification);
    overlay.addEventListener('click', (event) => {
        if (event.target === overlay) {
            mkjDismissOrderNotification();
        }
    });
}

async function mkjFetchLatestOrder() {
    const response = await fetch('get_order_notifications.php', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });
    if (!response.ok) {
        throw new Error(`HTTP ${response.status}`);
    }
    return response.json();
}

async function mkjCheckForNewOrders() {
    if (mkjOrderAlertBusy) return;
    mkjOrderAlertBusy = true;
    try {
        const payload = await mkjFetchLatestOrder();
        if (!payload || !payload.success || !payload.order) return;

        const latestId = Number(payload.order.order_id);
        if (!mkjLatestOrderId) {
            mkjLatestOrderId = latestId;
            return;
        }

        if (latestId > mkjLatestOrderId) {
            mkjLatestOrderId = latestId;
            mkjPlayOrderSound();
            mkjShowOrderNotification(payload.order);
        }
    } catch (error) {
        console.warn('Order notification poll failed:', error);
    } finally {
        mkjOrderAlertBusy = false;
    }
}

async function mkjInitializeOrderNotifications() {
    try {
        const payload = await mkjFetchLatestOrder();
        if (payload && payload.success && payload.order) {
            mkjLatestOrderId = Number(payload.order.order_id);
            const storedLastSeen = Number(window.localStorage.getItem('mkj_last_seen_order_id') || 0);
            if (storedLastSeen > 0 && mkjLatestOrderId > storedLastSeen) {
                mkjPlayOrderSound();
                mkjShowOrderNotification(payload.order);
            }
            window.localStorage.setItem('mkj_last_seen_order_id', String(mkjLatestOrderId));
        }
    } catch (error) {
        console.warn('Unable to initialize order notifications:', error);
    }

    mkjCheckForNewOrders();
    if (!mkjOrderPollTimer) {
        mkjOrderPollTimer = window.setInterval(mkjCheckForNewOrders, MKJ_ORDER_POLL_INTERVAL_MS);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('pointerdown', mkjUnlockOrderAudio, { once: true });
    document.addEventListener('keydown', mkjUnlockOrderAudio, { once: true });
    document.addEventListener('touchstart', mkjUnlockOrderAudio, { once: true });
    mkjInitializeOrderNotifications();
});
