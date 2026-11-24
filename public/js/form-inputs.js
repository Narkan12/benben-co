document.addEventListener('DOMContentLoaded', () => {
    const digitsOnly = (value) => String(value).replace(/\D/g, '');

    const formatNumericInput = (input) => {
        const selectionStart = input.selectionStart;
        const digitsBeforeCursor = selectionStart === null
            ? null
            : digitsOnly(input.value.slice(0, selectionStart)).length;
        const digits = digitsOnly(input.value);
        const formatted = input.hasAttribute('data-money-input')
            ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            : digits;
        if (input.value === formatted) return;

        input.value = formatted;
        if (digitsBeforeCursor !== null && document.activeElement === input) {
            let cursor = 0;
            let digitCount = 0;
            while (cursor < formatted.length && digitCount < digitsBeforeCursor) {
                if (/\d/.test(formatted[cursor])) digitCount += 1;
                cursor += 1;
            }
            input.setSelectionRange(cursor, cursor);
        }
    };

    window.formatNumericInput = formatNumericInput;

    document.querySelectorAll('input[required], select[required], textarea[required]').forEach((input) => {
        if (input.type === 'hidden' || input.type === 'radio' || input.type === 'checkbox') return;

        const label = input.labels && input.labels[0];
        if (!label || label.querySelector('[data-required-indicator]')) return;

        const indicator = document.createElement('span');
        indicator.className = 'bb-required-indicator';
        indicator.dataset.requiredIndicator = '';
        indicator.textContent = ' *';
        indicator.title = 'Wajib diisi';
        indicator.setAttribute('aria-hidden', 'true');
        label.append(indicator);
    });

    document.querySelectorAll('[data-numeric-input]').forEach(formatNumericInput);

    document.addEventListener('input', (event) => {
        if (event.target.matches('[data-numeric-input]')) {
            formatNumericInput(event.target);
        }
    });

    document.addEventListener('submit', (event) => {
        event.target.querySelectorAll('[data-numeric-input]').forEach((input) => {
            input.value = digitsOnly(input.value);
        });
    }, true);
});
