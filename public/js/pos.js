document.addEventListener('DOMContentLoaded', () => {
    const productCards = document.querySelectorAll('[data-pos-product]');
    const cart = new Map();
    const cartElement = document.getElementById('pos-cart');
    const itemsElement = document.getElementById('pos-items');
    const totalElement = document.getElementById('pos-total');
    const receivedInput = document.getElementById('pos-received');
    const changeElement = document.getElementById('pos-change');

    const formatMoney = (amount) => 'Rp' + new Intl.NumberFormat('id-ID').format(amount);

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

            const row = document.createElement('div');
            row.className = 'bb-summary-row align-items-center gap-2';
            const description = document.createElement('span');
            description.textContent = item.name;
            const controls = document.createElement('div');
            controls.className = 'd-flex align-items-center gap-1';
            controls.innerHTML = `<button type="button" class="btn-bb btn-bb-outline btn-bb-sm" data-pos-minus aria-label="Kurangi ${item.name}">−</button>
                <input type="number" class="bb-input text-center" style="width:64px;padding:6px;" min="1" max="${item.stock}" value="${item.qty}" data-pos-qty aria-label="Jumlah ${item.name}">
                <button type="button" class="btn-bb btn-bb-outline btn-bb-sm" data-pos-plus aria-label="Tambah ${item.name}">+</button>`;
            controls.querySelector('[data-pos-minus]').addEventListener('click', () => {
                if (item.qty <= 1) {
                    cart.delete(id);
                    updateProductStock(document.querySelector(`[data-id="${id}"]`), item.stock, 0);
                } else {
                    item.qty -= 1;
                }
                renderCart();
            });
            controls.querySelector('[data-pos-plus]').addEventListener('click', () => {
                if (item.qty < item.stock) item.qty += 1;
                renderCart();
            });
            controls.querySelector('[data-pos-qty]').addEventListener('change', (event) => {
                const requested = Number(event.target.value);
                item.qty = Math.min(item.stock, Math.max(1, Number.isInteger(requested) ? requested : 1));
                renderCart();
            });
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
    }

    function updateChange(total) {
        const received = Number(receivedInput.value) || 0;
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
