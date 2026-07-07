(function () {
  const icons = {
    success: 'fa-check',
    error: 'fa-exclamation',
    warning: 'fa-triangle-exclamation',
    info: 'fa-info'
  };

  const titles = {
    success: 'Success',
    error: 'Something went wrong',
    warning: 'Please check',
    info: 'Notice'
  };

  function container() {
    let el = document.querySelector('.toast-notification-container');
    if (!el) {
      el = document.createElement('div');
      el.className = 'toast-notification-container';
      document.body.appendChild(el);
    }
    return el;
  }

  function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
  }

  function show(type, message, options = {}) {
    const toast = document.createElement('div');
    const title = options.title || titles[type] || titles.info;
    const duration = options.duration || 4200;

    toast.className = `toast-notification ${type}`;
    toast.innerHTML = `
      <span class="toast-notification-icon"><i class="fa ${icons[type] || icons.info}"></i></span>
      <span>
        <p class="toast-notification-title">${escapeHtml(title)}</p>
        <p class="toast-notification-message">${escapeHtml(message)}</p>
      </span>
      <button class="toast-notification-close" type="button" aria-label="Close notification">
        <i class="fa fa-times"></i>
      </button>
    `;

    container().appendChild(toast);
    requestAnimationFrame(() => toast.classList.add('show'));

    const remove = () => {
      toast.classList.add('hide');
      setTimeout(() => toast.remove(), 280);
    };

    toast.querySelector('.toast-notification-close').addEventListener('click', remove);
    setTimeout(remove, duration);
  }

  window.ToastNotifications = {
    success: (message, options) => show('success', message, options),
    error: (message, options) => show('error', message, options),
    warning: (message, options) => show('warning', message, options),
    info: (message, options) => show('info', message, options)
  };
})();
