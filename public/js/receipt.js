document.addEventListener('DOMContentLoaded', () => {
    const receipt = document.querySelector('.printable-receipt');
    const widthSelect = document.getElementById('receipt-paper-width');
    const printButton = document.querySelector('[data-print-receipt]');

    if (!receipt || !widthSelect || !printButton) return;

    const originalTitle = document.title;

    const preparePrintLayout = () => {
        document.title = '';
        const requestedWidth = Number(widthSelect.value);
        const width = requestedWidth === 58 ? 58 : 80;
        const cardPadding = width === 58 ? '2mm' : '3mm';
        const measurement = receipt.cloneNode(true);
        const card = measurement.querySelector('.receipt-card');

        if (!card) return;

        measurement.classList.add('print-measurement');
        measurement.style.cssText = `position:fixed;left:-10000px;top:0;visibility:hidden;display:block;width:${width}mm;max-width:${width}mm;margin:0;padding:0;`;
        card.style.cssText = `box-sizing:border-box;width:100%;padding:${cardPadding};margin:0;border:0;border-top:2mm solid #79523e;border-radius:0;box-shadow:none;`;
        document.body.append(measurement);

        const pixelHeight = Math.ceil(measurement.getBoundingClientRect().height);
        const height = Math.max(40, Math.ceil(pixelHeight * 25.4 / 96 + 4));
        measurement.remove();

        receipt.dataset.paperWidth = String(width);
        document.documentElement.style.setProperty('--receipt-paper-width', `${width}mm`);
        document.documentElement.style.setProperty('--receipt-card-padding', cardPadding);

        let pageStyle = document.getElementById('receipt-page-size');
        if (!pageStyle) {
            pageStyle = document.createElement('style');
            pageStyle.id = 'receipt-page-size';
            document.head.append(pageStyle);
        }

        pageStyle.textContent = `@page { size: ${width}mm ${height}mm; margin: 0; }`;
        document.documentElement.classList.add('is-printing-receipt');
    };

    const restoreScreenLayout = () => {
        document.documentElement.classList.remove('is-printing-receipt');
        document.title = originalTitle;
    };

    printButton.addEventListener('click', async () => {
        printButton.disabled = true;
        try {
            await document.fonts.ready;
            preparePrintLayout();
            window.print();
        } finally {
            printButton.disabled = false;
        }
    });

    widthSelect.addEventListener('change', () => {
        preparePrintLayout();
        restoreScreenLayout();
    });

    window.addEventListener('beforeprint', preparePrintLayout);
    window.addEventListener('afterprint', restoreScreenLayout);
});
