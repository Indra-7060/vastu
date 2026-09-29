/**
 * Inline edit: fill top form from table Edit button
 * Button: class="js-edit-btn" with data-* fields and data-action (update URL)
 * Form: data-store-action, id matching module
 */
window.InlineEdit = (function () {
    function ensureMethodInput(form) {
        let method = form.querySelector('input[name="_method"]');
        if (!method) {
            method = document.createElement('input');
            method.type = 'hidden';
            method.name = '_method';
            form.appendChild(method);
        }
        return method;
    }

    function resetForm(form) {
        const storeAction = form.getAttribute('data-store-action');
        const titleEl = document.getElementById(form.getAttribute('data-title-id') || 'form-card-title');
        const submitBtn = form.querySelector('[type="submit"]');
        const cancelBtn = form.querySelector('.js-edit-cancel');
        const method = form.querySelector('input[name="_method"]');

        if (storeAction) form.setAttribute('action', storeAction);
        if (method) method.remove();
        if (titleEl) titleEl.textContent = form.getAttribute('data-add-title') || 'Add';
        if (submitBtn) submitBtn.textContent = 'Save';
        if (cancelBtn) cancelBtn.hidden = true;

        form.reset();
        form.querySelectorAll('.field-error').forEach((el) => { el.textContent = ''; });
        form.querySelectorAll('.has-error, .is-invalid').forEach((el) => {
            el.classList.remove('has-error', 'is-invalid');
        });
    }

    function fillForm(form, btn) {
        const updateUrl = btn.getAttribute('data-action');
        const titleEl = document.getElementById(form.getAttribute('data-title-id') || 'form-card-title');
        const submitBtn = form.querySelector('[type="submit"]');
        const cancelBtn = form.querySelector('.js-edit-cancel');
        const method = ensureMethodInput(form);

        method.value = 'PUT';
        form.setAttribute('action', updateUrl);

        if (titleEl) titleEl.textContent = form.getAttribute('data-edit-title') || 'Edit';
        if (submitBtn) submitBtn.textContent = 'Update';
        if (cancelBtn) cancelBtn.hidden = false;

        // Fill inputs from data-field-* attributes
        Array.from(btn.attributes).forEach((attr) => {
            if (!attr.name.startsWith('data-field-')) return;
            const field = attr.name.replace('data-field-', '');
            const input = form.querySelector(`[name="${field}"]`);
            if (input && input.type !== 'file') {
                input.value = attr.value || '';
            }
        });

        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
        const first = form.querySelector('input:not([type="hidden"]):not([type="file"]), select');
        if (first) first.focus();
    }

    function init() {
        document.addEventListener('click', function (e) {
            const editBtn = e.target.closest('.js-edit-btn');
            if (editBtn) {
                e.preventDefault();
                const formId = editBtn.getAttribute('data-form');
                const form = formId ? document.getElementById(formId) : null;
                if (!form) return;
                fillForm(form, editBtn);
                return;
            }

            const cancelBtn = e.target.closest('.js-edit-cancel');
            if (cancelBtn) {
                e.preventDefault();
                const form = cancelBtn.closest('form');
                if (form) resetForm(form);
            }
        });
    }

    return { init, resetForm };
})();

document.addEventListener('DOMContentLoaded', function () {
    InlineEdit.init();
});
