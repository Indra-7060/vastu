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

        function syncSelectAll() {
            const boxes = checkboxes();
            const checked = boxes.filter(c => c.checked);
            if (selectAll) {
                selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
                selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            }
        }

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
                msg.textContent = 'Do you really want to perform?';
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

    let currentOrderId = null;

    function openStatusModal(orderId) {
        currentOrderId = orderId;
        const overlay = qs('#order-status-overlay');
        const modal = qs('#order-status-modal');
        if (!modal) return;

        fetch((window.ORDER_STATUS_URL || '/admin/orders') + '/' + orderId + '/status-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                qs('#expected-delivery-date').value = data.expected_delivery_date || '';
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

        qs('#btn-update-delivery') && qs('#btn-update-delivery').addEventListener('click', function () {
            if (!currentOrderId) return;
            const date = qs('#expected-delivery-date').value;
            if (!date) {
                if (window.Toast) Toast.error('Please select a delivery date.');
                return;
            }
            const btn = qs('#btn-update-delivery');
            setBusy(btn, true, 'Updating…');
            fetch((window.ORDER_STATUS_URL || '/admin/orders') + '/' + currentOrderId + '/delivery-date', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ expected_delivery_date: date }),
            })
                .then(async function (r) {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) {
                        throw new Error(data.message || 'Failed to update delivery date.');
                    }
                    return data;
                })
                .then(function (data) {
                    setBusy(btn, false);
                    if (window.Toast) Toast.success(data.message || 'Delivery date updated.');
                })
                .catch(function (err) {
                    setBusy(btn, false);
                    if (window.Toast) Toast.error(err.message || 'Failed to update delivery date.');
                });
        });

        qs('#order-status-form') && qs('#order-status-form').addEventListener('submit', function (e) {
            e.preventDefault();
            if (!currentOrderId) return;
            const status = qs('#order-status-select').value;
            if (!status) {
                if (window.Toast) Toast.error('Please select a status.');
                return;
            }
            const saveBtn = qs('#order-status-save');
            setBusy(saveBtn, true, 'Saving…');
            fetch((window.ORDER_STATUS_URL || '/admin/orders') + '/' + currentOrderId + '/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': window.CSRF_TOKEN,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    status: status,
                    description: qs('#order-status-description').value,
                }),
            })
                .then(async function (r) {
                    const data = await r.json().catch(() => ({}));
                    if (!r.ok) {
                        throw new Error(data.message || (data.errors && data.errors.status && data.errors.status[0]) || 'Failed to update status.');
                    }
                    return data;
                })
                .then(function (data) {
                    if (window.Toast) Toast.success(data.message || 'Order status updated.');
                    closeStatusModal();
                    window.location.reload();
                })
                .catch(function (err) {
                    setBusy(saveBtn, false);
                    if (window.Toast) Toast.error(err.message || 'Failed to update status.');
                });
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
