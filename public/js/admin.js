/* =========================================================================
   BAKERY BENBEN CO. — admin.js
   ========================================================================= */
document.addEventListener('DOMContentLoaded', function () {

  /* ---------------------------------------------------------------------
     Sales chart (last 7 days) — rendered only if canvas + Chart.js exist
     --------------------------------------------------------------------- */
  const salesCanvas = document.getElementById('salesChart');
  if (salesCanvas && window.Chart) {
    const styles = getComputedStyle(document.documentElement);
    const choc = styles.getPropertyValue('--bb-choc').trim() || '#65402D';
    const darkChoc = styles.getPropertyValue('--bb-dark-choc').trim() || '#3A2118';

    new Chart(salesCanvas, {
      type: 'line',
      data: {
        labels: (salesCanvas.dataset.labels || '').split(',').filter(Boolean),
        datasets: [{
          label: 'Penjualan',
          data: (salesCanvas.dataset.values || '').split(',').filter(Boolean).map(Number),
          borderColor: darkChoc,
          backgroundColor: 'rgba(58,33,24,0.08)',
          tension: 0.4,
          fill: true,
          pointBackgroundColor: choc,
          pointRadius: 4,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: 'rgba(58,33,24,0.08)' }, ticks: { callback: (v) => 'Rp' + (v / 1000000).toFixed(1) + 'jt' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  /* ---------------------------------------------------------------------
     Best-selling products chart (donut)
     --------------------------------------------------------------------- */
  const productCanvas = document.getElementById('topProductsChart');
  if (productCanvas && window.Chart) {
    new Chart(productCanvas, {
      type: 'doughnut',
      data: {
        labels: (productCanvas.dataset.labels || '').split(',').filter(Boolean),
        datasets: [{
          data: (productCanvas.dataset.values || '').split(',').filter(Boolean).map(Number),
          backgroundColor: ['#3A2118', '#65402D', '#A8754F', '#E8D4BE', '#F5EBDD'],
          borderWidth: 0,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 16 } } },
        cutout: '65%',
      }
    });
  }

  /* ---------------------------------------------------------------------
     Report page chart
     --------------------------------------------------------------------- */
  const reportCanvas = document.getElementById('reportChart');
  if (reportCanvas && window.Chart) {
    new Chart(reportCanvas, {
      type: 'bar',
      data: {
        labels: (reportCanvas.dataset.labels || '').split(',').filter(Boolean),
        datasets: [{
          label: 'Total Penjualan',
          data: (reportCanvas.dataset.values || '').split(',').filter(Boolean).map(Number),
          backgroundColor: '#65402D',
          borderRadius: 6,
          maxBarThickness: 34,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          y: { grid: { color: 'rgba(58,33,24,0.08)' } },
          x: { grid: { display: false } }
        }
      }
    });
  }

  /* ---------------------------------------------------------------------
     Product modal: prefill fields when "Edit" is clicked
     --------------------------------------------------------------------- */
  const productForm = document.querySelector('[data-product-form]');
  document.querySelectorAll('[data-edit-product]').forEach((btn) => {
    btn.addEventListener('click', function () {
      const modal = this.closest('[data-bs-target]') ? document.querySelector(this.dataset.bsTarget) : document.getElementById('productModal');
      if (!modal || !productForm) return;
      modal.querySelector('.modal-title').textContent = 'Edit Produk';
      productForm.action = this.dataset.action;
      productForm.querySelector('[data-product-method]').value = 'PUT';
      productForm.querySelector('[name="nama"]').value = this.dataset.name || '';
      productForm.querySelector('[name="category_id"]').value = this.dataset.category || '';
      productForm.querySelector('[name="harga"]').value = this.dataset.price || '';
      productForm.querySelector('[name="stok"]').value = this.dataset.stock || '';
      productForm.querySelector('[name="status"]').value = this.dataset.status || 'active';
      productForm.querySelector('[name="deskripsi"]').value = this.dataset.description || '';
    });
  });

  document.querySelectorAll('[data-add-product]').forEach((btn) => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById('productModal');
      if (!modal || !productForm) return;
      modal.querySelector('.modal-title').textContent = 'Tambah Produk';
      productForm.reset();
      productForm.action = this.dataset.action;
      productForm.querySelector('[data-product-method]').value = 'POST';
    });
  });

  /* ---------------------------------------------------------------------
     Category modal
     --------------------------------------------------------------------- */
  const categoryForm = document.querySelector('[data-category-form]');
  document.querySelectorAll('[data-edit-category]').forEach((btn) => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById('categoryModal');
      if (!modal || !categoryForm) return;
      modal.querySelector('.modal-title').textContent = 'Edit Kategori';
      categoryForm.action = this.dataset.action;
      categoryForm.querySelector('[data-category-method]').value = 'PUT';
      categoryForm.querySelector('[name="nama_kategori"]').value = this.dataset.name || '';
      categoryForm.querySelector('[name="slug"]').value = this.dataset.slug || '';
    });
  });
  document.querySelectorAll('[data-add-category]').forEach((btn) => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById('categoryModal');
      if (!modal || !categoryForm) return;
      modal.querySelector('.modal-title').textContent = 'Tambah Kategori';
      categoryForm.reset();
      categoryForm.action = this.dataset.action || categoryForm.dataset.storeAction;
      categoryForm.querySelector('[data-category-method]').value = 'POST';
    });
  });

  /* ---------------------------------------------------------------------
     Promotion modal
     --------------------------------------------------------------------- */
  const promotionForm = document.querySelector('[data-promotion-form]');
  document.querySelectorAll('[data-edit-promotion]').forEach((btn) => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById('promoModal');
      if (!modal || !promotionForm) return;
      modal.querySelector('.modal-title').textContent = 'Edit Promo';
      promotionForm.action = this.dataset.action;
      promotionForm.querySelector('[data-promotion-method]').value = 'PUT';
      promotionForm.querySelector('[name="nama_promo"]').value = this.dataset.name || '';
      promotionForm.querySelector('[name="kode_promo"]').value = this.dataset.code || '';
      promotionForm.querySelector('[name="jenis_diskon"]').value = this.dataset.type || 'persen';
      promotionForm.querySelector('[name="nilai_diskon"]').value = this.dataset.value || '';
      promotionForm.querySelector('[name="tanggal_mulai"]').value = this.dataset.start || '';
      promotionForm.querySelector('[name="tanggal_berakhir"]').value = this.dataset.end || '';
      promotionForm.querySelector('[name="status"]').value = this.dataset.status || 'active';
    });
  });
  document.querySelectorAll('[data-add-promotion]').forEach((btn) => {
    btn.addEventListener('click', function () {
      const modal = document.getElementById('promoModal');
      if (!modal || !promotionForm) return;
      modal.querySelector('.modal-title').textContent = 'Tambah Promo';
      promotionForm.reset();
      promotionForm.action = this.dataset.action;
      promotionForm.querySelector('[data-promotion-method]').value = 'POST';
    });
  });

  const notificationPanel = document.querySelector('[data-order-notifications]');
  if (notificationPanel) {
    const count = notificationPanel.querySelector('[data-pending-order-count]');
    const status = notificationPanel.querySelector('[data-order-notification-status]');
    const toastContainer = document.querySelector('[data-order-toast-container]');
    const seenIds = new Set();
    const pendingSeenIds = new Set();
    let requestInProgress = false;
    let pollingTimer = null;

    const showOrderToast = (order) => {
      const toast = document.createElement('div');
      toast.className = 'toast bb-toast';
      toast.setAttribute('role', 'status');
      toast.setAttribute('aria-live', 'polite');
      toast.innerHTML = '<div class="toast-header"><strong class="me-auto">Pesanan online baru</strong>' +
        '<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button></div>';

      const body = document.createElement('div');
      body.className = 'toast-body';
      body.textContent = `${order.code} dari ${order.customer} · Rp${new Intl.NumberFormat('id-ID').format(order.total)}`;
      toast.appendChild(body);
      toastContainer.appendChild(toast);

      const instance = new bootstrap.Toast(toast, { delay: 7000 });
      toast.addEventListener('hidden.bs.toast', () => toast.remove(), { once: true });
      instance.show();
    };

    const checkOrderNotifications = async () => {
      if (notificationPanel.dataset.enabled !== 'true' || document.hidden || requestInProgress) {
        return;
      }

      requestInProgress = true;
      try {
        const response = await fetch(notificationPanel.dataset.pollUrl, {
          headers: { Accept: 'application/json' },
          credentials: 'same-origin',
        });
        if (!response.ok) {
          throw new Error(`Pemeriksaan pesanan gagal (${response.status}).`);
        }

        const data = await response.json();
        count.textContent = data.pending_count;
        notificationPanel.dataset.enabled = String(data.enabled);
        const unseenOrders = data.orders.filter((order) => !seenIds.has(order.id));

        unseenOrders.forEach((order) => {
          seenIds.add(order.id);
          pendingSeenIds.add(order.id);
          showOrderToast(order);
        });

        if (pendingSeenIds.size > 0) {
          const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
          const seenResponse = await fetch(notificationPanel.dataset.seenUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
              Accept: 'application/json',
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ order_ids: Array.from(pendingSeenIds).slice(0, 10) }),
          });
          if (!seenResponse.ok) {
            throw new Error(`Penyimpanan status notifikasi gagal (${seenResponse.status}).`);
          }
          pendingSeenIds.clear();
        }

        status.textContent = data.enabled
          ? 'Pemeriksaan otomatis aktif.'
          : 'Notifikasi sedang dimatikan.';
        if (!data.enabled && pollingTimer) {
          window.clearInterval(pollingTimer);
        }
      } catch (error) {
        status.textContent = 'Notifikasi belum dapat diperbarui. Akan dicoba kembali.';
        console.error(error);
      } finally {
        requestInProgress = false;
      }
    };

    if (notificationPanel.dataset.enabled === 'true') {
      checkOrderNotifications();
      pollingTimer = window.setInterval(checkOrderNotifications, 15000);
      document.addEventListener('visibilitychange', checkOrderNotifications);
    }
  }

});
