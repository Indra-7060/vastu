(function () {
    const form = document.getElementById('coupon-form');
    if (!form) return;

    const percentInput = document.getElementById('discount_percent');
    const amountInput = document.getElementById('discount_amount');
    const maxStatus = document.getElementById('max_discount_status');
    const minStatus = document.getElementById('min_cart_status');
    const maxRow = document.getElementById('max-discount-amount-row');
    const minRow = document.getElementById('min-cart-amount-row');
    const imageInput = document.getElementById('coupon-image-input');
    const preview = document.getElementById('coupon-image-preview');

    if (window.CKEDITOR && document.getElementById('coupon-description')) {
        CKEDITOR.replace('coupon-description', {
            height: 220,
            removePlugins: 'exportpdf',
            versionCheck: false,
        });
    }

    function syncDiscountExclusive(source) {
        if (!percentInput || !amountInput) return;
        if (source === 'percent' && percentInput.value !== '') {
            amountInput.value = '';
        }
        if (source === 'amount' && amountInput.value !== '') {
            percentInput.value = '';
        }
    }

    percentInput && percentInput.addEventListener('input', function () {
        syncDiscountExclusive('percent');
    });
    amountInput && amountInput.addEventListener('input', function () {
        syncDiscountExclusive('amount');
    });

    function toggleConditionalRows() {
        if (maxRow && maxStatus) {
            maxRow.style.display = maxStatus.value === '1' ? '' : 'none';
        }
        if (minRow && minStatus) {
            minRow.style.display = minStatus.value === '1' ? '' : 'none';
        }
    }

    maxStatus && maxStatus.addEventListener('change', toggleConditionalRows);
    minStatus && minStatus.addEventListener('change', toggleConditionalRows);
    toggleConditionalRows();

    const appliesTo = document.getElementById('coupon-applies-to');
    const categoriesRow = document.getElementById('coupon-categories-row');
    const productsRow = document.getElementById('coupon-products-row');

    function toggleAppliesRows() {
        const value = appliesTo ? appliesTo.value : 'all';
        if (categoriesRow) categoriesRow.style.display = value === 'categories' ? '' : 'none';
        if (productsRow) productsRow.style.display = value === 'products' ? '' : 'none';
    }

    appliesTo && appliesTo.addEventListener('change', toggleAppliesRows);
    toggleAppliesRows();

    const offerType = document.getElementById('coupon-offer-type');
    const bogoRuleRow = document.getElementById('coupon-bogo-rule-row');
    const discountRow = document.getElementById('coupon-discount-row');

    function toggleBogoFields() {
        const isBogo = offerType && offerType.value === 'bogo';
        if (bogoRuleRow) bogoRuleRow.style.display = isBogo ? '' : 'none';
        if (discountRow) discountRow.style.display = isBogo ? 'none' : '';
        if (maxStatus && maxStatus.closest('.offer-form-row')) {
            maxStatus.closest('.offer-form-row').style.display = isBogo ? 'none' : '';
        }
        if (maxRow) maxRow.style.display = isBogo ? 'none' : (maxStatus && maxStatus.value === '1' ? '' : 'none');
    }

    offerType && offerType.addEventListener('change', toggleBogoFields);
    toggleBogoFields();

    if (imageInput && preview) {
        imageInput.addEventListener('change', function () {
            const file = imageInput.files && imageInput.files[0];
            if (!file) return;
            const url = URL.createObjectURL(file);
            preview.innerHTML = '<img src="' + url + '" alt="Preview">';
        });
    }

    form.addEventListener('submit', function () {
        if (window.CKEDITOR && CKEDITOR.instances['coupon-description']) {
            CKEDITOR.instances['coupon-description'].updateElement();
        }
    });
})();
