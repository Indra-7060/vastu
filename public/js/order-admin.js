/**
 * Orders admin: bulk actions, datepicker, status modal
 */
(function () {
    function qs(sel, root) { return (root || document).querySelector(sel); }
    function qsa(sel, root) { return Array.from((root || document).querySelectorAll(sel)); }

    function setBusy(btn, busy, label) {
        if (!btn) return;
        if (busy) {
            if (!btn.dataset.originalHtml) btn.dataset.originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.classList.add('is-loading');
            btn.setAttribute('aria-busy', 'true');
            btn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span>' + (label || 'Please wait…');
        } else {
            btn.disabled = false;
            btn.classList.remove('is-loading');
            btn.removeAttribute('aria-busy');
            if (btn.dataset.originalHtml) {
                btn.innerHTML = btn.dataset.originalHtml;
                delete btn.dataset.originalHtml;
            }
        }
    }

    function bindDatePicker(textInput) {
        if (!textInput) return;
        const picker = document.createElement('input');
        picker.type = 'date';
        picker.className = 'date-picker-native';
        picker.setAttribute('aria-hidden', 'true');
        textInput.insertAdjacentElement('afterend', picker);

        textInput.addEventListener('focus', function () {
            try { picker.showPicker ? picker.showPicker() : picker.click(); } catch (e) { picker.click(); }
        });
        textInput.addEventListener('click', function () {
            try { picker.showPicker ? picker.showPicker() : picker.click(); } catch (e) { picker.click(); }
        });

        picker.addEventListener('change', function () {
            if (!picker.value) return;
            const parts = picker.value.split('-'); // Y-m-d
            if (parts.length === 3) {
                textInput.value = parts[2] + '-' + parts[1] + '-' + parts[0];
                textInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        });

        // Prefill picker from DD-MM-YYYY
        if (textInput.value && /^\d{2}-\d{2}-\d{4}$/.test(textInput.value)) {
            const p = textInput.value.split('-');
            picker.value = p[2] + '-' + p[1] + '-' + p[0];
        }
    }

    function bindBulk() {
        const selectAll = qs('#select-all-orders');
        const bulkForm = qs('#order-bulk-form');
        const idsBox = qs('#order-bulk-ids');
        const menuBtn = qs('#order-bulk-action-btn');
        const menu = qs('#order-bulk-action-menu');
        if (!bulkForm) return;

        const checkboxes = () => qsa('.order-check');

        const bar = qs('#order-bulk-bar');
        const countEl = qs('#order-bulk-count');

        function syncSelectAll() {
            const boxes = checkboxes();
            const checked = boxes.filter(c => c.checked);
            if (selectAll) {
                selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            }
            // Visible "N selected · Delete selected" bar whenever something is ticked.
            if (bar) {
                bar.hidden = checked.length === 0;
                if (countEl) countEl.textContent = checked.length + (checked.length === 1 ? ' order selected' : ' orders selected');
            }
            boxes.forEach(c => { const row = c.closest('tr'); if (row) row.classList.toggle('is-selected', c.checked); });
        }
        qs('#order-bulk-clear') && qs('#order-bulk-clear').addEventListener('click', function () {
            checkboxes().forEach(c => { c.checked = false; });
            syncSelectAll();
        });
        syncSelectAll();

        function collectIds() {
            idsBox.innerHTML = '';
            checkboxes().filter(c => c.checked).forEach(c => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = c.value;
                idsBox.appendChild(input);
            });
        }

        selectAll && selectAll.addEventListener('change', function () {
            checkboxes().forEach(c => { c.checked = selectAll.checked; });
            syncSelectAll();
        });
        document.addEventListener('change', function (e) {
            if (e.target.classList.contains('order-check')) syncSelectAll();
        });

        menuBtn && menuBtn.addEventListener('click', function (e) {
            e.preventDefault();
            menu.hidden = !menu.hidden;
        });
        document.addEventListener('click', function (e) {
            if (!menu || menu.hidden) return;
            if (!e.target.closest('.bulk-action-wrap')) menu.hidden = true;
        });

        bulkForm.addEventListener('submit', function (e) {
            collectIds();
            if (!idsBox.querySelectorAll('input').length) {
                e.preventDefault();
                if (window.Toast) Toast.error('Please select at least one order.');
                return;
            }
            const submitter = e.submitter;
            if (submitter && submitter.classList.contains('js-bulk-confirm')) {
                e.preventDefault();
                const title = submitter.getAttribute('data-confirm-title') || 'Are you sure?';
                const overlay = qs('#delete-confirm-overlay');
                const modal = qs('#delete-confirm-modal');
                const titleEl = qs('#delete-confirm-title');
                const cancel = qs('#delete-confirm-cancel');
                const proceed = qs('#delete-confirm-proceed');
                if (!modal) { bulkForm.submit(); return; }
                titleEl.textContent = title;
                let msg = modal.querySelector('.confirm-msg');
                if (!msg) {
                    msg = document.createElement('p');
                    msg.className = 'confirm-msg';
                    titleEl.insertAdjacentElement('afterend', msg);
                }
                const n = idsBox.querySelectorAll('input').length;
                msg.textContent = submitter.value === 'delete'
                    ? n + (n === 1 ? ' order' : ' orders') + ' will be permanently deleted. This cannot be undone.'
                    : 'Do you really want to perform?';
                overlay.classList.add('is-open');
                modal.classList.add('is-open');
                document.body.classList.add('modal-open');
                function close() {
                    overlay.classList.remove('is-open');
                    modal.classList.remove('is-open');
                    document.body.classList.remove('modal-open');
                    proceed.onclick = null;
                    cancel.onclick = null;
                }
                cancel.onclick = close;
                proceed.onclick = function () {
                    close();
                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = submitter.value;
                    bulkForm.appendChild(actionInput);
                    bulkForm.submit();
                };
            }
            menu.hidden = true;
        });
    }

    let loadedDate = '';
    let currentOrderId = null;

    function openStatusModal(orderId) {
        currentOrderId = orderId;
        // Always start with working buttons (in case a previous save was interrupted).
        setBusy(qs('#order-status-save'), false);
        setBusy(qs('#btn-update-delivery'), false);
        const overlay = qs('#order-status-overlay');
        const modal = qs('#order-status-modal');
        if (!modal) return;

        fetch((window.ORDER_STATUS_URL || '/admin/orders') + '/' + orderId + '/status-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                qs('#expected-delivery-date').value = data.expected_delivery_date || '';
                loadedDate = data.expected_delivery_date || '';
                const select = qs('#order-status-select');
                select.innerHTML = '<option value="">---Select Status---</option>';
                const next = data.next_statuses || {};
                Object.keys(next).forEach(function (key) {
                    const opt = document.createElement('option');
                    opt.value = key;
                    opt.textContent = next[key];
                    select.appendChild(opt);
                });
                const hint = qs('#status-hint');
                if (!data.can_update) {
                    hint.textContent = 'No further status updates allowed for this order.';
                    qs('#order-status-save').disabled = true;
                } else if (data.status === 'placed' || data.status === 'pending') {
                    hint.textContent = 'You can move this order to Packed, Shipped, Delivered, or Cancelled.';
                    qs('#order-status-save').disabled = false;
                } else {
                    hint.textContent = '';
                    qs('#order-status-save').disabled = false;
                }

                const timeline = qs('#order-timeline');
                timeline.innerHTML = '';
                (data.logs || []).forEach(function (log) {
                    const item = document.createElement('div');
                    item.className = 'timeline-item';
                    item.innerHTML =
                        '<div class="timeline-dot"></div>' +
                        '<div class="timeline-content">' +
                        '<strong>' + (log.title || log.status) + '</strong>' +
                        '<p>' + (log.description || '') + '</p>' +
                        '</div>' +
                        '<div class="timeline-date">' + (log.date || '') + '</div>';
                    timeline.appendChild(item);
                });

                qs('#order-status-description').value = '';
                overlay.classList.add('is-open');
                modal.hidden = false;
                modal.classList.add('is-open');
                document.body.classList.add('modal-open');
            })
            .catch(function () {
                if (window.Toast) Toast.error('Failed to load order status.');
            });
    }

    function closeStatusModal() {
        const overlay = qs('#order-status-overlay');
        const modal = qs('#order-status-modal');
        if (overlay) overlay.classList.remove('is-open');
        if (modal) {
            modal.hidden = true;
            modal.classList.remove('is-open');
        }
        document.body.classList.remove('modal-open');
        currentOrderId = null;
    }

    function bindStatusModal() {
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('.js-open-status');
            if (btn) {
                e.preventDefault();
                openStatusModal(btn.getAttribute('data-order-id'));
            }
        });

        qs('#order-status-close') && qs('#order-status-close').addEventListener('click', closeStatusModal);
        qs('#order-status-overlay') && qs('#order-status-overlay').addEventListener('click', closeStatusModal);

        const deliveryInput = qs('#expected-delivery-date');
        bindDatePicker(deliveryInput);

        function send(path, body) {
            return fetch((window.ORDER_STATUS_URL || '/admin/orders') + '/' + currentOrderId + '/' + path, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body),
            }).then(async function (r) {
                const data = await r.json().catch(() => ({}));
                if (!r.ok) {
                    throw new Error(data.message || (data.errors && ((data.errors.status && data.errors.status[0]) || (data.errors.expected_delivery_date && data.errors.expected_delivery_date[0]))) || 'Could not save. Please try again.');
                }
                return data;
            });
        }
        const saveDate = function () {
            return send('delivery-date', { expected_delivery_date: qs('#expected-delivery-date').value }).then(function (data) {
                loadedDate = qs('#expected-delivery-date').value;
                return data;
            });
        };
        const saveStatus = function () {
            return send('status', { status: qs('#order-status-select').value, description: qs('#order-status-description').value });
        };
        const dateChanged = function () { const v = qs('#expected-delivery-date').value; return !!v && v !== loadedDate; };

        // One click saves everything that was changed: the delivery date and/or the new status.
        function saveAll(btn, busyLabel) {
            if (!currentOrderId) return;
            const hasStatus = !!qs('#order-status-select').value;
            const hasDate = dateChanged() || (btn.id === 'btn-update-delivery' && !!qs('#expected-delivery-date').value);
            if (!hasStatus && !hasDate) {
                if (window.Toast) Toast.error(btn.id === 'btn-update-delivery' ? 'Please select a delivery date.' : 'Please select a status or change the delivery date.');
                return;
            }
            setBusy(btn, true, busyLabel);
            let messages = [];
            (hasDate ? saveDate().then(function (d) { messages.push(d.message || 'Delivery date updated.'); }) : Promise.resolve())
                .then(function () { return hasStatus ? saveStatus().then(function (d) { messages.push(d.message || 'Order status updated.'); }) : null; })
                .then(function () {
                    if (window.Toast) Toast.success(messages.join(' '));
                    if (hasStatus) { closeStatusModal(); window.location.reload(); }
                    else setBusy(btn, false);
                })
                .catch(function (err) {
                    setBusy(btn, false);
                    if (window.Toast) Toast.error(err.message);
                });
        }

        qs('#btn-update-delivery') && qs('#btn-update-delivery').addEventListener('click', function () {
            saveAll(this, 'Saving…');
        });

        qs('#order-status-form') && qs('#order-status-form').addEventListener('submit', function (e) {
            e.preventDefault();
            saveAll(qs('#order-status-save'), 'Saving…');
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        bindBulk();
        bindStatusModal();

        const dateFilter = qs('#order-date-filter');
        if (dateFilter) {
            bindDatePicker(dateFilter);
            dateFilter.addEventListener('change', function () {
                const form = dateFilter.closest('form');
                if (form) form.submit();
            });
        }
    });
})();
