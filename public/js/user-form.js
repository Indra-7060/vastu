/**
 * User add/edit form validation + avatar preview
 */
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('user-form');
    if (!form) return;

    const input = document.getElementById('user-avatar-input');
    const preview = document.getElementById('user-avatar-preview');
    if (input && preview) {
        input.addEventListener('change', function () {
            const file = input.files && input.files[0];
            if (!file) return;
            preview.innerHTML = '<img src="' + URL.createObjectURL(file) + '" alt="Preview">';
        });
    }

    const passwordRequired = form.getAttribute('data-password-required') === '1';
    if (window.FormValidator) {
        const passwordRules = [
            { type: 'min', value: 6, message: 'Password must be at least 6 characters.' },
        ];
        if (passwordRequired) {
            passwordRules.unshift({ type: 'required', message: 'Password is required.' });
        }

        FormValidator.init(form, {
            name: [
                { type: 'required', message: 'Name is required.' },
                { type: 'min', value: 2, message: 'Name must be at least 2 characters.' },
            ],
            email: [
                { type: 'required', message: 'Email is required.' },
                { type: 'email', message: 'Enter a valid email address.' },
            ],
            password: passwordRules,
            avatar: [
                { type: 'image', types: ['image/png', 'image/jpeg', 'image/jpg'], maxKb: 2048 },
            ],
        });
    }
});
