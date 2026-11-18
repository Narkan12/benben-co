document.addEventListener('DOMContentLoaded', () => {
    const digitsOnly = (value) => String(value).replace(/\D/g, '');

    const formatNumericInput = (input) => {
        const digits = digitsOnly(input.value);
        input.value = input.hasAttribute('data-money-input')
            ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
            : digits;
    };

    window.formatNumericInput = formatNumericInput;

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
