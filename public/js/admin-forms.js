/**
 * Admin module form validations
 */
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('category-form')) {
        FormValidator.init('#category-form', {
            title: [
                { type: 'required', message: 'Category title is required.' },
                { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
                { type: 'max', value: 255, message: 'Title cannot exceed 255 characters.' },
            ],
            image: [
                { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
            ],
        });
    }

    if (document.getElementById('subcategory-form')) {
        FormValidator.init('#subcategory-form', {
            category_id: [
                { type: 'required', message: 'Please select a category.' },
            ],
            title: [
                { type: 'required', message: 'Sub-category title is required.' },
                { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
            ],
            image: [
                { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
            ],
        });
    }

    if (document.getElementById('brand-form')) {
        FormValidator.init('#brand-form', {
            name: [
                { type: 'required', message: 'Brand name is required.' },
                { type: 'min', value: 2, message: 'Name must be at least 2 characters.' },
            ],
            image: [
                { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
            ],
        });
    }

    if (document.getElementById('color-form')) {
        FormValidator.init('#color-form', {
            name: [
                { type: 'required', message: 'Color name is required.' },
                { type: 'min', value: 2, message: 'Name must be at least 2 characters.' },
            ],
            code: [
                { type: 'hex', message: 'Enter a valid hex color (e.g. #000000).' },
            ],
        });
    }

    if (document.getElementById('size-form')) {
        FormValidator.init('#size-form', {
            name: [
                { type: 'required', message: 'Size name is required.' },
                { type: 'min', value: 1, message: 'Size name is required.' },
                { type: 'max', value: 50, message: 'Size cannot exceed 50 characters.' },
            ],
        });
    }

    const offerForm = document.getElementById('offer-form');
    if (offerForm) {
        const imageRequired = offerForm.getAttribute('data-image-required') === '1';
        const imageRules = [
            { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
        ];
        if (imageRequired) {
            imageRules.unshift({ type: 'requiredFile', message: 'Please select an offer image.' });
        }

        FormValidator.init(offerForm, {
            title: [
                { type: 'required', message: 'Offer title is required.' },
                { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
                { type: 'max', value: 255, message: 'Title cannot exceed 255 characters.' },
            ],
            discount_percent: [
                { type: 'required', message: 'Discount percent is required.' },
                { type: 'number', message: 'Enter a valid discount number.' },
                { type: 'minValue', value: 1, message: 'Discount must be at least 1%.' },
                { type: 'maxValue', value: 100, message: 'Discount cannot exceed 100%.' },
            ],
            image: imageRules,
        });
    }

    const newsTypeForm = document.getElementById('news-type-form');
    if (newsTypeForm) {
        FormValidator.init(newsTypeForm, {
            title: [
                { type: 'required', message: 'News type title is required.' },
                { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
                { type: 'max', value: 255, message: 'Title cannot exceed 255 characters.' },
            ],
            sort_order: [
                { type: 'number', message: 'Enter a valid sort order.' },
                { type: 'minValue', value: 0, message: 'Sort order cannot be negative.' },
                { type: 'maxValue', value: 9999, message: 'Sort order cannot exceed 9999.' },
            ],
        });
    }

    const blogPostForm = document.getElementById('blog-post-form');
    if (blogPostForm) {
        const imageRequired = blogPostForm.getAttribute('data-image-required') === '1';
        const imageTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
        const imageRules = [
            { type: 'image', types: imageTypes, maxKb: 4096, message: 'Card image must be PNG, JPG, JPEG or WEBP under 4MB.' },
        ];
        if (imageRequired) {
            imageRules.unshift({ type: 'requiredFile', message: 'Please select a card image.' });
        }

        FormValidator.init(blogPostForm, {
            title: [
                { type: 'required', message: 'Journal title is required.' },
                { type: 'min', value: 2, message: 'Title must be at least 2 characters.' },
                { type: 'max', value: 255, message: 'Title cannot exceed 255 characters.' },
            ],
            news_type_id: [
                { type: 'required', message: 'Please select a news type.' },
            ],
            author_name: [
                { type: 'max', value: 255, message: 'Author name cannot exceed 255 characters.' },
            ],
            image: imageRules,
            banner_image: [
                { type: 'image', types: imageTypes, maxKb: 5120, message: 'Banner image must be PNG, JPG, JPEG or WEBP under 5MB.' },
            ],
            excerpt: [
                { type: 'max', value: 2000, message: 'Excerpt cannot exceed 2000 characters.' },
            ],
            sort_order: [
                { type: 'number', message: 'Enter a valid sort order.' },
                { type: 'minValue', value: 0, message: 'Sort order cannot be negative.' },
                { type: 'maxValue', value: 9999, message: 'Sort order cannot exceed 9999.' },
            ],
            comments_count: [
                { type: 'number', message: 'Enter a valid comments count.' },
                { type: 'minValue', value: 0, message: 'Comments count cannot be negative.' },
            ],
        });

        blogPostForm.addEventListener('submit', function (e) {
            if (window.CKEDITOR && CKEDITOR.instances.content) {
                CKEDITOR.instances.content.updateElement();
            }

            const contentField = blogPostForm.querySelector('[name="content"]');
            if (!contentField || !window.FormValidator) return;

            FormValidator.clearError(contentField);
            const plain = (contentField.value || '').replace(/<[^>]*>/g, '').replace(/&nbsp;/g, ' ').trim();
            if (!plain) {
                e.preventDefault();
                FormValidator.showError(contentField, 'Content / details is required.');
                const first = blogPostForm.querySelector('.is-invalid') || contentField;
                if (first && first.focus) first.focus();
            }
        });
    }
});
