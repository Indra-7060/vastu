/**
 * Global delete confirmation modal
 * Usage: add class "js-delete-form" on any delete form
 */
window.DeleteConfirm = (function () {
    let pendingForm = null;

    function getEls() {
        return {
            modal: document.getElementById('delete-confirm-modal'),
            overlay: document.getElementById('delete-confirm-overlay'),
            cancelBtn: document.getElementById('delete-confirm-cancel'),
            proceedBtn: document.getElementById('delete-confirm-proceed'),
            title: document.getElementById('delete-confirm-title'),
        };
    }

    function open(form) {
        const els = getEls();
        if (!els.modal) return;
        pendingForm = form;
        const customTitle = form.getAttribute('data-confirm-title');
        if (els.title) {
            els.title.textContent = customTitle || 'Are you sure?';
        }
        els.overlay.classList.add('is-open');
        els.modal.classList.add('is-open');
        document.body.classList.add('modal-open');
        els.proceedBtn && els.proceedBtn.focus();
    }

    function close() {
        const els = getEls();
        pendingForm = null;
        if (!els.modal) return;
        els.overlay.classList.remove('is-open');
        els.modal.classList.remove('is-open');
        document.body.classList.remove('modal-open');
    }

    function proceed() {
        if (!pendingForm) {
            close();
            return;
        }
        const form = pendingForm;
        pendingForm = null;
        close();
        form.submit();
    }

    function init() {
        const els = getEls();
        if (!els.modal) return;

        document.addEventListener('submit', function (e) {
            const form = e.target.closest('form.js-delete-form');
            if (!form) return;
            e.preventDefault();
            open(form);
        });

        els.cancelBtn && els.cancelBtn.addEventListener('click', close);
        els.overlay && els.overlay.addEventListener('click', close);
        els.proceedBtn && els.proceedBtn.addEventListener('click', proceed);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && els.modal.classList.contains('is-open')) {
                close();
            }
        });
    }

    return { init, open, close };
})();

document.addEventListener('DOMContentLoaded', function () {
    DeleteConfirm.init();
});
