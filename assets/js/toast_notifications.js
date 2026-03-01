// Toast Notification System
const ToastNotifications = (() => {
    let toastContainer = null;
    
    // Initialize toast container
    function init() {
        if (!document.getElementById('toastContainer')) {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container';
            document.body.appendChild(container);
            toastContainer = container;
        } else {
            toastContainer = document.getElementById('toastContainer');
        }
    }
    
    // Create and show toast notification
    function showToast(message, type = 'success') {
        // Initialize if not already done
        if (!toastContainer) {
            init();
        }
        
        const isSuccess = type === 'success';
        const toast = document.createElement('div');
        
        toast.className = `toast d-flex align-items-center gap-3 p-3 rounded-lg ${isSuccess ? 'toast-success' : 'toast-error'}`;
        
        const accent = isSuccess ? 'var(--success-accent)' : 'var(--error-accent)';
        const iconSvg = isSuccess 
            ? `<path class="icon-path" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>`
            : `<path class="icon-path" fill="none" d="M16 16l20 20M36 16L16 36"/>`;

        toast.innerHTML = `
            <div class="icon-container ${isSuccess ? 'icon-fill-success' : 'icon-fill-error'}" style="box-shadow: inset 0px 0px 0px ${accent}; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; stroke-width: 2; stroke: #fff; flex-shrink: 0;">
                <svg viewBox="0 0 52 52"><circle class="icon-circle" cx="26" cy="26" r="25" fill="none" style="stroke: ${accent}"/>${iconSvg}</svg>
            </div>
            <div class="toast-content flex-1">
                <div class="toast-type text-uppercase fw-bold x-small text-white-50">${type}</div>
                <div class="toast-message text-white">${message}</div>
            </div>
            <button class="close-btn text-white bg-transparent border-0 d-flex align-items-center justify-content-center" style="cursor: pointer; outline: none; width: 24px; height: 24px;">
                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
            <div class="progress-bar"></div>
        `;

        toastContainer.appendChild(toast);
        
        // Set up auto-dismiss timer
        const timer = setTimeout(() => dismissToast(toast), 5000);
        
        // Add close button event listener
        toast.querySelector('.close-btn').addEventListener('click', function() {
            clearTimeout(timer);
            dismissToast(toast);
        });
    }

    // Dismiss toast with animation
    function dismissToast(toast) {
        toast.classList.add('hiding');
        toast.onanimationend = (e) => { 
            if(e.animationName === 'slideOut') toast.remove(); 
        };
    }
    
    // Show success toast
    function showSuccess(message) {
        showToast(message, 'success');
    }
    
    // Show error toast
    function showError(message) {
        showToast(message, 'error');
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // Expose public methods
    return {
        show: showToast,
        success: showSuccess,
        error: showError
    };
})();

// Global functions for backward compatibility
function createToast(type = 'success', message = "") {
    if (type === 'success') {
        ToastNotifications.success(message);
    } else {
        ToastNotifications.error(message);
    }
}