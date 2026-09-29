/**
 * Product add/edit form: subcategory filter, image preview, duplicate title check
 */
(function () {
    function bindPreview(inputId, previewId) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        if (!input || !preview) return;
        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            preview.innerHTML = '<img src="' + url + '" alt="Preview">';
        });
    }

    function filterSubCategories() {
        const category = document.getElementById('product-category');
        const sub = document.getElementById('product-subcategory');
        if (!category || !sub) return;

        function apply() {
            const catId = category.value;
            let keep = false;
            Array.from(sub.options).forEach(function (opt, i) {
                if (i === 0) return;
                const match = !catId || String(opt.getAttribute('data-category')) === String(catId);
                opt.hidden = !match;
                if (opt.selected && !match) opt.selected = false;
                if (opt.selected && match) keep = true;
            });
            if (!keep) sub.selectedIndex = 0;
        }

        category.addEventListener('change', apply);
        apply();
    }

    function toggleAccessoryPackages() {
        const category = document.getElementById('product-category');
        const card = document.getElementById('accessory-package-card');
        const enable = document.getElementById('enable-accessory-packages');
        const fields = document.getElementById('accessory-package-fields');
        if (!category || !card) return;

        function apply() {
            const selected = category.options[category.selectedIndex];
            const slug = selected ? String(selected.getAttribute('data-slug') || '').toLowerCase() : '';
            const isAccessory = slug === 'accessories' || slug === 'accessory';
            card.hidden = !isAccessory;

            if (enable) {
                enable.disabled = !isAccessory;
            }

            const packagesOn = isAccessory && enable && enable.checked;
            if (fields) {
                fields.style.display = packagesOn ? '' : 'none';
            }

            card.querySelectorAll('#accessory-package-fields input').forEach(function (input) {
                input.disabled = !packagesOn;
            });
        }

        category.addEventListener('change', apply);
        if (enable) {
            enable.addEventListener('change', apply);
        }
        apply();
        return apply;
    }

    function bindSuggestedPackages(reapplyDisabled) {
        const btn = document.getElementById('fill-suggested-packages');
        const container = document.getElementById('accessory-packages-repeater');
        const template = document.getElementById('accessory-package-template');
        if (!btn || !container || !template) return;

        btn.addEventListener('click', function () {
            const suggested = [
                { label: '1 piece', mrp: '549', price: '549' },
                { label: '2 pieces (1 set)', mrp: '899', price: '899' },
                { label: '4 pieces + 1 free', mrp: '1598', price: '1598' },
                { label: '6 pieces + 2 free', mrp: '2797', price: '2797' },
            ];
            container.innerHTML = '';
            suggested.forEach(function (pack, index) {
                let html = template.innerHTML
                    .replace(/__INDEX__/g, String(index))
                    .replace(/__INDEX_DISPLAY__/g, String(index + 1));
                const wrap = document.createElement('div');
                wrap.innerHTML = html.trim();
                const row = wrap.firstElementChild;
                const labelInput = row.querySelector('input[name*="[label]"]');
                const mrpInput = row.querySelector('input[name*="[mrp]"]');
                const priceInput = row.querySelector('input[name*="[price]"]');
                if (labelInput) labelInput.value = pack.label;
                if (mrpInput) mrpInput.value = pack.mrp;
                if (priceInput) priceInput.value = pack.price;
                container.appendChild(row);
            });
            if (typeof reapplyDisabled === 'function') reapplyDisabled();
        });
    }

    function toggleColourGalleries() {
        const empty = document.getElementById('colour-gallery-empty');
        const slots = Array.from(document.querySelectorAll('.colour-gallery-slot'));
        if (!slots.length) return;

        function apply() {
            const checked = Array.from(document.querySelectorAll('input[name="color_ids[]"]:checked'))
                .map(function (el) { return String(el.value); });

            let visible = 0;
            slots.forEach(function (slot) {
                const show = checked.indexOf(String(slot.getAttribute('data-color-id'))) !== -1;
                slot.hidden = !show;
                slot.style.display = show ? '' : 'none';
                slot.querySelectorAll('input[type="file"]').forEach(function (input) {
                    input.disabled = !show;
                });
                if (show) visible += 1;
            });

            if (empty) empty.style.display = visible ? 'none' : '';
        }

        document.querySelectorAll('input[name="color_ids[]"]').forEach(function (checkbox) {
            checkbox.addEventListener('change', apply);
        });
        apply();
    }

    async function checkTitleDuplicate(form, titleInput) {
        const url = form.getAttribute('data-check-title');
        if (!url || !titleInput) return true;

        const title = (titleInput.value || '').trim();
        const errEl = document.getElementById('title-dup-error');
        if (!title) {
            if (errEl) errEl.textContent = '';
            return true;
        }

        const params = new URLSearchParams({ title: title });
        const ignoreId = form.getAttribute('data-ignore-id');
        if (ignoreId) params.set('ignore_id', ignoreId);

        try {
            const res = await fetch(url + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            if (!data.available) {
                const msg = data.message || 'This product title already exists. Duplicate name not allowed.';
                if (errEl) errEl.textContent = msg;
                titleInput.classList.add('is-invalid');
                titleInput.closest('.form-group')?.classList.add('has-error');
                if (window.Toast) Toast.error(msg);
                return false;
            }
            if (errEl) errEl.textContent = '';
            titleInput.classList.remove('is-invalid');
            titleInput.closest('.form-group')?.classList.remove('has-error');
            return true;
        } catch (e) {
            return true;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('product-form');
        if (!form) return;

        bindPreview('featured-image-1', 'preview-featured-1');
        bindPreview('featured-image-2', 'preview-featured-2');
        filterSubCategories();
        const reapplyAccessoryPackages = toggleAccessoryPackages();
        bindSuggestedPackages(reapplyAccessoryPackages);
        toggleColourGalleries();

        const titleInput = document.getElementById('product-title');
        let titleTimer = null;
        if (titleInput) {
            titleInput.addEventListener('blur', function () {
                checkTitleDuplicate(form, titleInput);
            });
            titleInput.addEventListener('input', function () {
                clearTimeout(titleTimer);
                titleTimer = setTimeout(function () {
                    checkTitleDuplicate(form, titleInput);
                }, 450);
            });
        }

        const imageRequired = form.getAttribute('data-image-required') === '1';
        const imageRules = [
            { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
        ];
        if (imageRequired) {
            imageRules.unshift({ type: 'requiredFile', message: 'Please select Featured Image-1.' });
        }

        if (window.FormValidator) {
            FormValidator.init(form, {
                title: [
                    { type: 'required', message: 'Product title is required.' },
                    { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
                    { type: 'max', value: 255, message: 'Title cannot exceed 255 characters.' },
                ],
                category_id: [
                    { type: 'required', message: 'Please select a category.' },
                ],
                mrp: [
                    { type: 'required', message: 'M.R.P is required.' },
                    { type: 'number', message: 'Enter a valid M.R.P.' },
                    { type: 'minValue', value: 0, message: 'M.R.P cannot be negative.' },
                ],
                selling_price: [
                    { type: 'number', message: 'Enter a valid selling price.' },
                    { type: 'minValue', value: 0, message: 'Selling price cannot be negative.' },
                ],
                max_unit_buy: [
                    { type: 'required', message: 'Max unit buy is required.' },
                    { type: 'number', message: 'Enter a valid number.' },
                    { type: 'minValue', value: 1, message: 'Max unit buy must be at least 1.' },
                ],
                delivery_charge: [
                    { type: 'required', message: 'Delivery charge is required.' },
                    { type: 'number', message: 'Enter a valid delivery charge.' },
                    { type: 'minValue', value: 0, message: 'Delivery charge cannot be negative.' },
                ],
                featured_image: imageRules,
                featured_image_2: [
                    { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
                ],
            });
        }

        form.addEventListener('submit', async function (e) {
            if (form.dataset.dupChecked === '1') {
                form.dataset.dupChecked = '0';
                return;
            }
            e.preventDefault();
            if (form.querySelector('.is-invalid')) return;
            const ok = await checkTitleDuplicate(form, titleInput);
            if (!ok) return;
            form.dataset.dupChecked = '1';
            form.requestSubmit();
        });

        initRepeaters(form);
    });

    function initRepeaters(form) {
        form.querySelectorAll('[data-add-row]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-target');
                const templateId = btn.getAttribute('data-template');
                const container = document.getElementById(targetId);
                const template = document.getElementById(templateId);
                if (!container || !template) return;

                const index = container.querySelectorAll('[data-repeater-row]').length;
                let html = template.innerHTML
                    .replace(/__INDEX__/g, String(index))
                    .replace(/__INDEX_DISPLAY__/g, String(index + 1));
                const wrap = document.createElement('div');
                wrap.innerHTML = html.trim();
                const row = wrap.firstElementChild;
                container.appendChild(row);
                if (targetId === 'accessory-packages-repeater') {
                    const enable = document.getElementById('enable-accessory-packages');
                    const packagesOn = enable && enable.checked;
                    row.querySelectorAll('input').forEach(function (input) {
                        input.disabled = !packagesOn;
                    });
                }
            });
        });

        form.addEventListener('click', function (e) {
            const removeBtn = e.target.closest('[data-remove-row]');
            if (!removeBtn) return;
            const row = removeBtn.closest('[data-repeater-row]');
            const container = row && row.parentElement;
            if (!row || !container) return;
            if (container.querySelectorAll('[data-repeater-row]').length <= 1) {
                row.querySelectorAll('input[type="text"], textarea').forEach(function (el) { el.value = ''; });
                row.querySelectorAll('input[type="file"]').forEach(function (el) { el.value = ''; });
                return;
            }
            row.remove();
        });
    }
})();
