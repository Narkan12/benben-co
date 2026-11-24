document.addEventListener('DOMContentLoaded', () => {
    const productCards = document.querySelectorAll('[data-pos-product]');
    const cart = new Map();
    const cartElement = document.getElementById('pos-cart');
    const itemsElement = document.getElementById('pos-items');
    const totalElement = document.getElementById('pos-total');
    const receivedInput = document.getElementById('pos-received');
    const changeElement = document.getElementById('pos-change');
    const paymentMethodInput = document.getElementById('pos-payment');
    const paymentPresets = document.querySelector('[data-pos-payment-presets]');
    const paymentOptions = document.querySelector('[data-pos-payment-options]');

    const formatMoney = (amount) => 'Rp' + new Intl.NumberFormat('id-ID').format(amount);

    function renderPaymentPresets(total) {
        paymentOptions.replaceChildren();
        paymentPresets.hidden = paymentMethodInput.value !== 'cash' || total <= 0;
        if (paymentPresets.hidden) return;

        const amounts = new Set([total]);
        let amount = Math.ceil(total / 50000) * 50000;
        while (amounts.size < 5) {
            amounts.add(amount);
            amount += 50000;
        }

        amounts.forEach((value, index) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn-bb btn-bb-outline btn-bb-sm';
            button.textContent = index === 0
                ? `Uang Pas · ${formatMoney(value)}`
                : formatMoney(value);
            button.setAttribute('aria-label', `Isi pembayaran ${formatMoney(value)}`);
            button.addEventListener('click', () => {
                receivedInput.value = new Intl.NumberFormat('id-ID').format(value);
                receivedInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
            paymentOptions.append(button);
        });
    }

    function renderCart() {
        cartElement.replaceChildren();
        itemsElement.replaceChildren();
        let total = 0;
        let index = 0;

        if (cart.size === 0) {
            const empty = document.createElement('p');
            empty.className = 'section-sub';
            empty.textContent = 'Belum ada produk dipilih.';
            cartElement.append(empty);
        }

        cart.forEach((item, id) => {
            const lineTotal = item.price * item.qty;
            total += lineTotal;
            updateProductStock(document.querySelector(`[data-id="${id}"]`), item.stock, item.qty);

            const row = document.createElement('div');
            row.className = 'bb-summary-row align-items-center gap-2';
            const description = document.createElement('span');
            description.textContent = item.name;
            const controls = document.createElement('div');
            controls.className = 'd-flex align-items-center gap-1';
            const quantityLabel = document.createElement('label');
            quantityLabel.className = 'section-sub mb-0';
            quantityLabel.htmlFor = `pos-qty-${id}`;
            quantityLabel.textContent = 'Jumlah';
            const requiredIndicator = document.createElement('span');
            requiredIndicator.className = 'bb-required-indicator';
            requiredIndicator.title = 'Wajib diisi';
            requiredIndicator.setAttribute('aria-hidden', 'true');
            requiredIndicator.textContent = ' *';
            quantityLabel.append(requiredIndicator);

            const minusButton = document.createElement('button');
            minusButton.type = 'button';
            minusButton.className = 'btn-bb btn-bb-outline btn-bb-sm';
            minusButton.dataset.posMinus = '';
            minusButton.setAttribute('aria-label', `Kurangi ${item.name}`);
            minusButton.textContent = '−';
            minusButton.addEventListener('click', () => {
                if (item.qty <= 1) {
                    cart.delete(id);
                    updateProductStock(document.querySelector(`[data-id="${id}"]`), item.stock, 0);
                } else {
                    item.qty -= 1;
                }
                renderCart();
            });

            const quantityInput = document.createElement('input');
            quantityInput.id = `pos-qty-${id}`;
            quantityInput.type = 'text';
            quantityInput.className = 'bb-input text-center';
            quantityInput.style.width = '64px';
            quantityInput.style.padding = '6px';
            quantityInput.inputMode = 'numeric';
            quantityInput.dataset.numericInput = '';
            quantityInput.dataset.posQty = '';
            quantityInput.value = item.qty;
            quantityInput.setAttribute('aria-label', `Jumlah ${item.name}`);
            quantityInput.required = true;
            quantityInput.addEventListener('change', (event) => {
                const requested = Number(event.target.value.replace(/\D/g, ''));
                item.qty = Math.min(item.stock, Math.max(1, Number.isInteger(requested) ? requested : 1));
                renderCart();
            });

            const plusButton = document.createElement('button');
            plusButton.type = 'button';
            plusButton.className = 'btn-bb btn-bb-outline btn-bb-sm';
            plusButton.dataset.posPlus = '';
            plusButton.setAttribute('aria-label', `Tambah ${item.name}`);
            plusButton.textContent = '+';
            plusButton.addEventListener('click', () => {
                if (item.qty < item.stock) item.qty += 1;
                renderCart();
            });
            controls.append(quantityLabel, minusButton, quantityInput, plusButton);
            const price = document.createElement('span');
            price.textContent = formatMoney(lineTotal);
            row.append(description, controls, price);
            cartElement.append(row);

            [['product_id', id], ['qty', item.qty]].forEach(([field, value]) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `items[${index}][${field}]`;
                input.value = value;
                itemsElement.append(input);
            });
            index += 1;
        });

        totalElement.textContent = formatMoney(total);
        updateChange(total);
        renderPaymentPresets(total);
    }

    function updateChange(total) {
        const received = Number(receivedInput.value.replace(/\D/g, '')) || 0;
        changeElement.textContent = formatMoney(Math.max(0, received - total));
    }

    productCards.forEach((card) => {
        card.querySelector('[data-pos-add]').addEventListener('click', () => {
            const id = card.dataset.id;
            const current = cart.get(id);
            const stock = Number(card.dataset.stock);

            if (current && current.qty >= stock) {
                return;
            }

            cart.set(id, {
                name: card.dataset.name,
                price: Number(card.dataset.price),
                stock,
                qty: current ? current.qty + 1 : 1,
            });
            updateProductStock(card, stock, cart.get(id).qty);
            renderCart();
        });
    });

    document.getElementById('pos-search').addEventListener('input', (event) => {
        const search = event.target.value.trim().toLocaleLowerCase();
        productCards.forEach((card) => {
            card.hidden = !card.dataset.name.toLocaleLowerCase().includes(search);
        });
    });

    receivedInput.addEventListener('input', () => {
        const total = [...cart.values()].reduce((sum, item) => sum + item.price * item.qty, 0);
        updateChange(total);
    });
    paymentMethodInput.addEventListener('change', () => {
        const total = [...cart.values()].reduce((sum, item) => sum + item.price * item.qty, 0);
        renderPaymentPresets(total);
    });

    renderCart();

    function updateProductStock(card, stock, quantity) {
        const remaining = Math.max(0, stock - quantity);
        const formattedRemaining = remaining.toLocaleString('id-ID');
        const formattedQuantity = quantity.toLocaleString('id-ID');
        const stockLabel = card.querySelector('[data-pos-stock-display]');
        const quantityLabel = card.querySelector('[data-pos-cart-quantity]');
        const addButton = card.querySelector('[data-pos-add]');

        if (stockLabel) stockLabel.textContent = `Stok tersisa: ${formattedRemaining} pcs`;
        if (quantityLabel) quantityLabel.textContent = `Di keranjang: ${formattedQuantity} pcs`;
        if (addButton) addButton.disabled = remaining < 1;
    }
});
