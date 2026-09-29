/**
 * Auth page validation
 */
document.addEventListener('DOMContentLoaded', function () {
    const loginForm = document.getElementById('admin-login-form');
    if (loginForm) {
        FormValidator.init(loginForm, {
            username: [
                { type: 'required', message: 'Username is required.' },
                { type: 'min', value: 3, message: 'Username must be at least 3 characters.' },
            ],
            password: [
                { type: 'required', message: 'Password is required.' },
                { type: 'min', value: 6, message: 'Password must be at least 6 characters.' },
            ],
        });
    }

    const forgotForm = document.getElementById('admin-forgot-form');
    if (forgotForm) {
        FormValidator.init(forgotForm, {
            email: [
                { type: 'required', message: 'Email is required.' },
                { type: 'email', message: 'Please enter a valid email address.' },
            ],
        });
    }

    const resetForm = document.getElementById('admin-reset-form');
    if (resetForm) {
        FormValidator.init(resetForm, {
            email: [
                { type: 'required', message: 'Email is required.' },
                { type: 'email', message: 'Please enter a valid email address.' },
            ],
            password: [
                { type: 'required', message: 'Password is required.' },
                { type: 'min', value: 8, message: 'Password must be at least 8 characters.' },
            ],
            password_confirmation: [
                { type: 'required', message: 'Please confirm your password.' },
                { type: 'match', selector: '[name="password"]', message: 'Passwords do not match.' },
            ],
        });
    }
});
