document.addEventListener('DOMContentLoaded', () => {
    const receipt = document.querySelector('.printable-receipt');
    const widthSelect = document.getElementById('receipt-paper-width');
    const printButton = document.querySelector('[data-print-receipt]');

    if (!receipt || !widthSelect || !printButton) return;

    const preparePrintLayout = () => {
        const requestedWidth = Number(widthSelect.value);
        const width = requestedWidth === 58 ? 58 : 80;
        const cardPadding = width === 58 ? '2mm' : '3mm';
        const measurement = receipt.cloneNode(true);
        const card = measurement.querySelector('.receipt-card');

        if (!card) return;

        measurement.dataset.paperWidth = String(width);
        measurement.style.cssText = `position:fixed;left:-10000px;top:0;visibility:hidden;display:block;width:${width}mm;max-width:${width}mm;margin:0;padding:0;`;
        card.style.cssText = `box-sizing:border-box;width:100%;padding:${cardPadding};margin:0;border:0;box-shadow:none;`;
        document.body.append(measurement);

        const height = Math.ceil(measurement.getBoundingClientRect().height * 25.4 / 96 + 2);
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
        pageStyle.textContent = `@page{size:${width}mm ${height}mm;margin:0;}`;
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

    window.addEventListener('beforeprint', preparePrintLayout);
});
