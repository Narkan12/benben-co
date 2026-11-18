/* =========================================================================
   BAKERY BENBEN CO. — cart.js
   Cart quantity controls submit updates to the server; math is preview-only.
   ========================================================================= */
document.addEventListener('DOMContentLoaded', function () {

  const cartRows = document.querySelectorAll('[data-cart-row]');
  const subtotalEl = document.querySelector('[data-cart-subtotal]');
  const discountEl = document.querySelector('[data-cart-discount]');
  const deliveryEl = document.querySelector('[data-cart-delivery]');
  const totalEl = document.querySelector('[data-cart-total]');
  const cartBadge = document.querySelector('.bb-cart-badge');
  const emptyState = document.querySelector('[data-cart-empty]');
  const cartTable = document.querySelector('[data-cart-table]');

  const DISCOUNT_RATE = 0; // set e.g. 0.1 to demo a 10% promo discount
  const DELIVERY_FEE = 0;

  function formatRupiah(num) {
    return 'Rp' + Math.max(0, Math.round(num)).toLocaleString('id-ID');
  }

  function formatStock(num) {
    return Math.max(0, num).toLocaleString('id-ID');
  }

  function recalculate() {
    let subtotal = 0;
    let itemCount = 0;

    cartRows.forEach((row) => {
      const price = parseFloat(row.dataset.price || 0);
      const qtyInput = row.querySelector('[data-qty-input]');
      const qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;
      const lineTotal = price * qty;
      const stock = parseInt(row.dataset.stock || 0, 10);
      const stockInfo = row.querySelector('[data-cart-stock-info]');
      const plusButton = row.querySelector('[data-qty-plus]');

      const lineTotalEl = row.querySelector('[data-line-total]');
      if (lineTotalEl) lineTotalEl.textContent = formatRupiah(lineTotal);
      if (stockInfo) stockInfo.textContent = `Sisa stok setelah jumlah ini: ${formatStock(stock - qty)} pcs`;
      if (plusButton) plusButton.disabled = qty >= stock;

      subtotal += lineTotal;
      itemCount += qty;
    });

    const discount = subtotal * DISCOUNT_RATE;
    const delivery = cartRows.length ? DELIVERY_FEE : 0;
    const total = subtotal - discount + delivery;

    if (subtotalEl) subtotalEl.textContent = formatRupiah(subtotal);
    if (discountEl) discountEl.textContent = discount > 0 ? '- ' + formatRupiah(discount) : formatRupiah(0);
    if (deliveryEl) deliveryEl.textContent = formatRupiah(delivery);
    if (totalEl) totalEl.textContent = formatRupiah(total);
    if (cartBadge) cartBadge.textContent = itemCount;

    if (emptyState && cartTable) {
      const hasRows = document.querySelectorAll('[data-cart-row]').length > 0;
      emptyState.style.display = hasRows ? 'none' : 'block';
      cartTable.style.display = hasRows ? '' : 'none';
    }
  }

  function bindRow(row) {
    const minusBtn = row.querySelector('[data-qty-minus]');
    const plusBtn = row.querySelector('[data-qty-plus]');
    const qtyInput = row.querySelector('[data-qty-input]');
    const qtyForm = row.querySelector('[data-cart-update-form]');
    const stock = parseInt(row.dataset.stock || 99, 10);

    if (minusBtn) minusBtn.addEventListener('click', () => {
      let val = parseInt(qtyInput.value, 10) || 1;
      val = Math.max(1, val - 1);
      qtyInput.value = val;
      recalculate();
      if (qtyForm) qtyForm.requestSubmit();
    });

    if (plusBtn) plusBtn.addEventListener('click', () => {
      let val = parseInt(qtyInput.value, 10) || 1;
      val = Math.min(stock, val + 1);
      qtyInput.value = val;
      recalculate();
      if (qtyForm) qtyForm.requestSubmit();
    });

    if (qtyInput) qtyInput.addEventListener('change', () => {
      let val = parseInt(qtyInput.value, 10) || 1;
      val = Math.min(stock, Math.max(1, val));
      qtyInput.value = val;
      recalculate();
      if (qtyForm) qtyForm.requestSubmit();
    });
  }

  cartRows.forEach(bindRow);
  recalculate();

  /* ---------------------------------------------------------------------
     Product detail page quantity selector (shares the same stepper markup)
     --------------------------------------------------------------------- */
  const detailQty = document.querySelector('[data-detail-qty]');
  if (detailQty) {
    const minusBtn = detailQty.querySelector('[data-qty-minus]');
    const plusBtn = detailQty.querySelector('[data-qty-plus]');
    const input = detailQty.querySelector('[data-qty-input]');
    const stock = parseInt(detailQty.dataset.stock || 99, 10);
    const availableCount = detailQty.querySelector('[data-detail-available-count]');
    const afterCount = document.querySelector('[data-detail-after-count]');
    const addButtons = document.querySelectorAll('[data-cart-add-button]');
    const checkoutButton = document.querySelector('[name="go_to_checkout"]');

    function updateDetailStock() {
      const cartQty = parseInt(detailQty.dataset.cartQty || 0, 10);
      const available = Math.max(0, stock - cartQty);
      const selectedQty = parseInt(input.value, 10) || 0;
      input.max = Math.max(1, available);
      if (available > 0) input.value = Math.min(available, Math.max(1, selectedQty));
      if (availableCount) availableCount.textContent = formatStock(available);
      if (afterCount) {
        afterCount.textContent = available > 0
          ? `Setelah jumlah pilihan, stok tersisa ${formatStock(available - selectedQty)} pcs.`
          : 'Semua stok yang tersedia sudah ada di keranjang.';
      }
      addButtons.forEach((button) => { button.disabled = available < 1; });
      if (checkoutButton) checkoutButton.disabled = available < 1;
    }

    if (minusBtn) minusBtn.addEventListener('click', () => {
      input.value = Math.max(1, (parseInt(input.value, 10) || 1) - 1);
      updateDetailStock();
    });
    if (plusBtn) plusBtn.addEventListener('click', () => {
      const available = Math.max(0, stock - parseInt(detailQty.dataset.cartQty || 0, 10));
      input.value = Math.min(available, (parseInt(input.value, 10) || 1) + 1);
      updateDetailStock();
    });
    if (input) input.addEventListener('input', () => {
      const available = Math.max(0, stock - parseInt(detailQty.dataset.cartQty || 0, 10));
      input.value = Math.min(available, Math.max(1, parseInt(input.value, 10) || 1));
      updateDetailStock();
    });
    updateDetailStock();
  }

});
