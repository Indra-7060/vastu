/**
 * Contact List: select-all + bulk delete id sync
 */
(function () {
    function syncBulk(formId, checkClass, selectAllId, idsBoxId, bulkBtnId) {
        const form = document.getElementById(formId);
        if (!form) return;

        const selectAll = document.getElementById(selectAllId);
        const idsBox = document.getElementById(idsBoxId);
        const bulkBtn = document.getElementById(bulkBtnId);

        function checks() {
            return Array.from(document.querySelectorAll('.' + checkClass));
        }

        function selected() {
            return checks().filter(function (el) { return el.checked; });
        }

        function refreshIds() {
            if (!idsBox) return;
            idsBox.innerHTML = '';
            selected().forEach(function (el) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = el.value;
                idsBox.appendChild(input);
            });
            if (bulkBtn) {
                bulkBtn.disabled = selected().length === 0;
            }
            if (selectAll) {
                const all = checks();
                selectAll.checked = all.length > 0 && all.every(function (el) { return el.checked; });
                selectAll.indeterminate = selected().length > 0 && selected().length < all.length;
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                checks().forEach(function (el) {
                    el.checked = selectAll.checked;
                });
                refreshIds();
            });
        }

        document.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains(checkClass)) {
                refreshIds();
            }
        });

        form.addEventListener('submit', function (e) {
            refreshIds();
            if (selected().length === 0) {
                e.preventDefault();
                e.stopImmediatePropagation();
                if (window.Toast) {
                    Toast.error('Please select at least one item.');
                } else {
                    alert('Please select at least one item.');
                }
            }
        }, true);

        refreshIds();
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncBulk('subscribe-bulk-form', 'subscribe-check', 'select-all-subscribers', 'subscribe-bulk-ids', 'subscribe-bulk-btn');
        syncBulk('contact-bulk-form', 'message-check', 'select-all-messages', 'contact-bulk-ids', 'contact-bulk-btn');
    });
})();
