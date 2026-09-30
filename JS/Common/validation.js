/**
 * Harvestly client-side validation.
 * Server-side validation remains authoritative; this only gives immediate feedback.
 */
(function () {
    'use strict';

    const style = document.createElement('style');
    style.textContent = '.hv-invalid{border-color:#dc2626!important;outline:2px solid rgba(220,38,38,.12)}.hv-validation-error{color:#b91c1c;font-size:.82rem;margin-top:4px;line-height:1.3}.hv-invalid:focus{outline:2px solid rgba(220,38,38,.25)}';
    document.head.appendChild(style);

    const rules = {
        phone: {
            pattern: /^(?:0|\+94)\d{9}$/,
            message: 'Enter a valid Sri Lankan phone number (e.g. 0771234567).'
        },
        nic_number: {
            pattern: /^(?:\d{9}[vVxX]|\d{12})$/,
            message: 'Enter a valid NIC (9 digits + V/X or 12 digits).'
        },
        email: {
            pattern: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Enter a valid email address.'
        },
        postal: {
            pattern: /^\d{5}$/,
            message: 'Postal code must contain 5 digits.'
        },
        office_postal: {
            pattern: /^\d{5}$/,
            message: 'Postal code must contain 5 digits.'
        },
        office_postal_code: {
            pattern: /^\d{5}$/,
            message: 'Postal code must contain 5 digits.'
        }
    };

    const requiredNames = new Set([
        'full_name', 'name', 'email', 'password', 'confirm_password',
        'phone', 'address', 'farm_address', 'business_address',
        'organisation_name', 'company_name', 'contact_person',
        'product_name', 'category_id', 'unit_price', 'available_quantity',
        'listing_type', 'listing_status', 'destination_district',
        'district', 'office_district_id', 'from_district', 'to_district',
        'origin_district_id', 'destination_district_id', 'description',
        'details', 'subject', 'message', 'new_password', 'confirm_password'
    ]);

    function valueOf(el) {
        return typeof el.value === 'string' ? el.value.trim() : '';
    }

    function showError(el, message) {
        clearError(el);
        el.classList.add('hv-invalid');
        el.setAttribute('aria-invalid', 'true');

        const error = document.createElement('div');
        error.className = 'hv-validation-error';
        error.textContent = message;
        error.setAttribute('role', 'alert');

        const parent = el.closest('.form-group, .form-field, .input-group, .field, td, label') || el.parentElement;
        if (parent) parent.appendChild(error);
    }

    function clearError(el) {
        el.classList.remove('hv-invalid');
        el.removeAttribute('aria-invalid');
        const parent = el.closest('.form-group, .form-field, .input-group, .field, td, label') || el.parentElement;
        if (parent) parent.querySelectorAll('.hv-validation-error').forEach(x => x.remove());
    }

    function validateField(el, form) {
        if (!el || el.disabled || el.type === 'hidden' || el.type === 'submit' ||
            el.type === 'button' || el.type === 'reset' || el.type === 'search') return true;

        clearError(el);
        const name = (el.name || '').trim();
        const value = valueOf(el);

        if (el.required && !value && el.type !== 'file') {
            showError(el, 'This field is required.');
            return false;
        }

        if (el.required && el.type === 'file' && (!el.files || !el.files.length)) {
            showError(el, 'Please select a file.');
            return false;
        }

        if (!value && (!el.required || el.type === 'file')) return true;

        if (el.type === 'email' && !rules.email.pattern.test(value)) {
            showError(el, rules.email.message);
            return false;
        }

        if (el.type === 'tel' || name === 'phone') {
            const phone = value.replace(/[\s()-]/g, '');
            if (!rules.phone.pattern.test(phone)) {
                showError(el, rules.phone.message);
                return false;
            }
        }

        if (rules[name] && rules[name].pattern && !rules[name].pattern.test(value.replace(/\s/g, ''))) {
            showError(el, rules[name].message);
            return false;
        }

        if (el.minLength > 0 && value.length < el.minLength) {
            showError(el, `Enter at least ${el.minLength} characters.`);
            return false;
        }

        if (el.maxLength > 0 && value.length > el.maxLength) {
            showError(el, `Use ${el.maxLength} characters or fewer.`);
            return false;
        }

        if (el.type === 'number') {
            const n = Number(value);
            if (!Number.isFinite(n)) {
                showError(el, 'Enter a valid number.');
                return false;
            }
            if (el.min !== '' && n < Number(el.min)) {
                showError(el, `Value must be at least ${el.min}.`);
                return false;
            }
            if (el.max !== '' && n > Number(el.max)) {
                showError(el, `Value must be at most ${el.max}.`);
                return false;
            }
        }

        if (el.type === 'date' && value) {
            const d = new Date(value + 'T00:00:00');
            if (Number.isNaN(d.getTime())) {
                showError(el, 'Enter a valid date.');
                return false;
            }
        }

        if (el.type === 'file' && el.files && el.files.length) {
            const file = el.files[0];
            if (file.size > 5 * 1024 * 1024) {
                showError(el, 'File size must be 5 MB or less.');
                return false;
            }
            const allowed = (el.accept || '').split(',').map(x => x.trim().toLowerCase()).filter(Boolean);
            if (allowed.length) {
                const ext = '.' + (file.name.split('.').pop() || '').toLowerCase();
                const mime = (file.type || '').toLowerCase();
                const ok = allowed.some(a => a.startsWith('.') ? a === ext : a === mime);
                if (!ok) {
                    showError(el, 'Please select a supported file type.');
                    return false;
                }
            }
        }

        if (name === 'password' || name === 'new_password') {
            if (value.length < 8) {
                showError(el, 'Password must be at least 8 characters.');
                return false;
            }
        }

        if (name === 'confirm_password') {
            const password = form.querySelector('[name="password"], [name="new_password"]');
            if (password && value !== password.value) {
                showError(el, 'Passwords do not match.');
                return false;
            }
        }

        if (['unit_price', 'price', 'distance_km'].includes(name) && Number(value) <= 0) {
            showError(el, 'Value must be greater than zero.');
            return false;
        }

        if (name === 'available_quantity' && Number(value) < 0) {
            showError(el, 'Quantity cannot be negative.');
            return false;
        }

        if (['description', 'details', 'message'].includes(name) && value.length < 3) {
            showError(el, 'Please enter at least 3 characters.');
            return false;
        }

        if (name === 'rejection_reason' && value.length > 500) {
            showError(el, 'Rejection reason must be 500 characters or fewer.');
            return false;
        }

        return true;
    }

    function validateForm(form) {
        let valid = true;
        const fields = Array.from(form.querySelectorAll('input, textarea, select'));
        fields.forEach(el => {
            if (!validateField(el, form)) valid = false;
        });

        // Cross-field date checks for product forms.
        const harvest = form.querySelector('[name="harvest_date"]');
        const available = form.querySelector('[name="available_from_date"]');
        const bestBefore = form.querySelector('[name="best_before_date"]');
        if (harvest && available && harvest.value && available.value &&
            available.value < harvest.value) {
            showError(available, 'Available-from date cannot be before the harvest date.');
            valid = false;
        }
        if (available && bestBefore && available.value && bestBefore.value &&
            bestBefore.value < available.value) {
            showError(bestBefore, 'Best-before date cannot be before the available-from date.');
            valid = false;
        }

        // Do not allow a courier route to use the same district.
        const origin = form.querySelector('[name="origin_district_id"]');
        const destination = form.querySelector('[name="destination_district_id"]');
        if (origin && destination && origin.value && destination.value && origin.value === destination.value) {
            showError(destination, 'Origin and destination districts must be different.');
            valid = false;
        }

        if (!valid) {
            const first = form.querySelector('.hv-invalid');
            if (first) first.focus({preventScroll: false});
        }
        return valid;
    }

    function markUsefulFields() {
        document.querySelectorAll('form').forEach(form => {
            form.querySelectorAll('input, textarea, select').forEach(el => {
                const name = (el.name || '').trim();
                if (!name || el.type === 'hidden' || el.type === 'submit' ||
                    el.type === 'button' || el.type === 'reset' || el.type === 'search') return;

                // Add browser constraints where the project already treats the field as required.
                if (requiredNames.has(name) && !el.required && !['address2','city','postal',
                    'office_city','office_postal','contact_person_name','office_address_line2'].includes(name)) {
                    // Only set required for fields that are actually business-required.
                    const optional = ['farm_name','nic_number','office_city','office_postal',
                        'contact_person_name','office_address_line2','address2','city','postal',
                        'description','details'];
                    if (!optional.includes(name)) el.required = true;
                }

                if (name === 'phone' && !el.pattern) el.pattern = '(?:0|\\+94)[0-9]{9}';
                if (name === 'nic_number' && !el.pattern) el.pattern = '(?:[0-9]{9}[vVxX]|[0-9]{12})';
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        markUsefulFields();

        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function (event) {
                if (!validateForm(form)) event.preventDefault();
            });

            form.querySelectorAll('input, textarea, select').forEach(el => {
                el.addEventListener('input', function () {
                    if (el.classList.contains('hv-invalid')) validateField(el, form);
                });
                el.addEventListener('change', function () {
                    if (el.classList.contains('hv-invalid')) validateField(el, form);
                });
            });
        });
    });
})();
