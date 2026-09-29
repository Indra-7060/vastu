/**
 * Toast notifications
 */
window.Toast = (function () {
    function ensureContainer() {
        let el = document.getElementById('toast-container');
        if (!el) {
            el = document.createElement('div');
            el.id = 'toast-container';
            el.className = 'toast-container';
            document.body.appendChild(el);
        }
        return el;
    }

    function show(message, type) {
        if (!message) return;
        const container = ensureContainer();
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + (type || 'info');
        toast.innerHTML = '<span class="toast-msg"></span><button type="button" class="toast-close" aria-label="Close">×</button>';
        toast.querySelector('.toast-msg').textContent = message;

        const remove = () => {
            toast.classList.add('toast-hide');
            setTimeout(() => toast.remove(), 250);
        };

        toast.querySelector('.toast-close').addEventListener('click', remove);
        container.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('toast-show'));
        setTimeout(remove, 4000);
    }

    return {
        success: (msg) => show(msg, 'success'),
        error: (msg) => show(msg, 'error'),
        info: (msg) => show(msg, 'info'),
        show,
    };
})();
