/**
 * Client-side form validation helper
 * Usage: FormValidator.init('#formId', { fieldName: [rules...] })
 */
window.FormValidator = (function () {
    const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const HEX_RE = /^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/;

    function getValue(el) {
        if (!el) return '';
        if (el.type === 'file') return el.files && el.files.length ? el.files[0] : null;
        if (el.type === 'checkbox') return el.checked;
        return (el.value || '').trim();
    }

    function getWrap(field) {
        return field.closest('.field-wrap, .form-group') || field.closest('.input-group') || field.parentElement;
    }

    function ensureErrorEl(field) {
        const wrap = getWrap(field);
        let err = wrap.querySelector(':scope > .field-error');
        if (!err) {
            err = document.createElement('div');
            err.className = 'field-error';
            err.setAttribute('aria-live', 'polite');
            // Keep hints above error slot if present
            const hint = wrap.querySelector('.hint');
            if (hint) {
                hint.insertAdjacentElement('afterend', err);
            } else {
                wrap.appendChild(err);
            }
        }
        return err;
    }

    function prepareErrorSlots(form, schema) {
        Object.keys(schema).forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (field) ensureErrorEl(field);
        });
    }

    function showError(field, message) {
        const wrap = getWrap(field);
        wrap.classList.add('has-error');
        field.classList.add('is-invalid');
        const group = field.closest('.input-group');
        if (group) group.classList.add('has-error');
        ensureErrorEl(field).textContent = message;
    }

    function clearError(field) {
        const wrap = getWrap(field);
        wrap.classList.remove('has-error');
        field.classList.remove('is-invalid');
        const group = field.closest('.input-group');
        if (group) group.classList.remove('has-error');
        const err = wrap.querySelector(':scope > .field-error');
        if (err) err.textContent = '';
    }

    function validateRule(field, rule, value) {
        const type = typeof rule === 'string' ? rule : rule.type;
        const msg = typeof rule === 'object' ? rule.message : null;

        switch (type) {
            case 'required':
                if (value === null || value === '' || value === false) {
                    return msg || 'This field is required.';
                }
                break;
            case 'email':
                if (value && !EMAIL_RE.test(value)) {
                    return msg || 'Please enter a valid email address.';
                }
                break;
            case 'min':
                if (value && String(value).length < rule.value) {
                    return msg || `Minimum ${rule.value} characters required.`;
                }
                break;
            case 'max':
                if (value && String(value).length > rule.value) {
                    return msg || `Maximum ${rule.value} characters allowed.`;
                }
                break;
            case 'number':
                if (value !== '' && value !== null && Number.isNaN(Number(value))) {
                    return msg || 'Please enter a valid number.';
                }
                break;
            case 'minValue':
                if (value !== '' && value !== null && Number(value) < rule.value) {
                    return msg || `Value must be at least ${rule.value}.`;
                }
                break;
            case 'maxValue':
                if (value !== '' && value !== null && Number(value) > rule.value) {
                    return msg || `Value must be at most ${rule.value}.`;
                }
                break;
            case 'hex':
                if (value && !HEX_RE.test(value)) {
                    return msg || 'Enter a valid hex color (e.g. #FF0000).';
                }
                break;
            case 'image':
                if (value) {
                    const allowed = rule.types || ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];
                    if (!allowed.includes(value.type) && !/\.(png|jpe?g|webp)$/i.test(value.name || '')) {
                        return msg || 'Only PNG, JPG, JPEG, WEBP images are allowed.';
                    }
                    const maxKb = rule.maxKb || 2048;
                    if (value.size > maxKb * 1024) {
                        return msg || `Image must be under ${maxKb >= 1024 ? (maxKb / 1024) + ' MB' : maxKb + ' KB'}.`;
                    }
                }
                break;
            case 'requiredFile':
                if (!value) {
                    return msg || 'Please select an image.';
                }
                break;
            case 'match':
                const other = document.querySelector(rule.selector);
                if (other && getValue(other) !== value) {
                    return msg || 'Values do not match.';
                }
                break;
            default:
                break;
        }
        return null;
    }

    function validateField(field, rules) {
        clearError(field);
        const value = getValue(field);
        for (const rule of rules) {
            const error = validateRule(field, rule, value);
            if (error) {
                showError(field, error);
                return false;
            }
        }
        return true;
    }

    function init(formSelector, schema) {
        const form = typeof formSelector === 'string'
            ? document.querySelector(formSelector)
            : formSelector;
        if (!form || !schema) return;

        prepareErrorSlots(form, schema);

        Object.keys(schema).forEach((name) => {
            const field = form.querySelector(`[name="${name}"]`);
            if (!field) return;
            const events = field.type === 'file' ? ['change'] : ['blur', 'input'];
            events.forEach((evt) => {
                field.addEventListener(evt, () => validateField(field, schema[name]));
            });
        });

        form.addEventListener('submit', (e) => {
            let ok = true;
            Object.keys(schema).forEach((name) => {
                const field = form.querySelector(`[name="${name}"]`);
                if (!field) return;
                if (!validateField(field, schema[name])) ok = false;
            });
            if (!ok) {
                e.preventDefault();
                const first = form.querySelector('.is-invalid');
                if (first) first.focus();
            }
        });
    }

    return { init, validateField, clearError, showError };
})();
