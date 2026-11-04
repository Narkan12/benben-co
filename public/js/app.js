/* =========================================================================
   BAKERY BENBEN CO. — app.js
   Core interactions shared across the storefront.
   ========================================================================= */
document.addEventListener('DOMContentLoaded', function () {

  const promoModal = document.querySelector('[data-promo-modal]');
  if (promoModal && window.bootstrap?.Modal) {
    window.bootstrap.Modal.getOrCreateInstance(promoModal).show();
  }

  document.addEventListener('click', async function (event) {
    const button = event.target.closest('[data-copy-promo]');
    if (!button) return;

    const feedback = button.closest('.bb-modal-promo-card')?.querySelector('[data-copy-feedback]');
    const code = button.dataset.copyPromo;

    try {
      if (navigator.clipboard?.writeText && window.isSecureContext) {
        await navigator.clipboard.writeText(code);
      } else {
        const input = document.createElement('textarea');
        input.value = code;
        input.setAttribute('readonly', '');
        input.style.position = 'fixed';
        input.style.opacity = '0';
        document.body.append(input);
        input.select();
        const copied = document.execCommand('copy');
        input.remove();

        if (!copied) throw new Error('Clipboard copy was unsuccessful.');
      }

      if (feedback) feedback.textContent = `Kode ${code} berhasil disalin.`;
    } catch {
      if (feedback) feedback.textContent = 'Kode gagal disalin. Silakan salin kode secara manual.';
    }
  });

  document.addEventListener('submit', async function (event) {
    const form = event.target.closest('[data-cart-add-form]');
    if (!form || event.submitter?.name === 'go_to_checkout') return;

    event.preventDefault();
    const button = form.querySelector('[data-cart-add-button]');
    const feedback = form.querySelector('[data-cart-feedback]');
    if (button?.disabled) return;

    if (button) button.disabled = true;
    if (feedback) {
      feedback.hidden = true;
      feedback.classList.remove('text-danger');
    }

    try {
      const response = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: {
          Accept: 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      });
      const result = await response.json();

      if (!response.ok) {
        throw new Error(result.message || 'Produk tidak dapat ditambahkan.');
      }

      const formatStock = (amount) => new Intl.NumberFormat('id-ID').format(amount);
      document.querySelectorAll('[data-product-stock-display]').forEach((stockDisplay) => {
        if (stockDisplay.dataset.productId !== String(result.product_id)) return;
        const remaining = Number(result.stock_remaining);
        stockDisplay.textContent = remaining > 0
          ? `Stok tersisa ${formatStock(remaining)}`
          : 'Stok habis untuk ditambahkan';
        stockDisplay.classList.toggle('low', remaining <= 5);
      });

      document.querySelectorAll('[data-product-card]').forEach((card) => {
        if (card.dataset.productId !== String(result.product_id)) return;
        card.dataset.stockRemaining = result.stock_remaining;
        const addButton = card.querySelector('[data-cart-add-button]');
        if (addButton) addButton.disabled = Number(result.stock_remaining) < 1;
      });

      const detailQuantity = document.querySelector('[data-detail-qty]');
      if (detailQuantity?.dataset.productId === String(result.product_id)) {
        const remaining = Number(result.stock_remaining);
        detailQuantity.dataset.cartQty = result.quantity;
        const input = detailQuantity.querySelector('[data-qty-input]');
        const availableCount = detailQuantity.querySelector('[data-detail-available-count]');
        const afterCount = form.querySelector('[data-detail-after-count]');
        if (availableCount) availableCount.textContent = formatStock(remaining);
        if (input) {
          input.max = Math.max(1, remaining);
          input.value = remaining > 0 ? 1 : 0;
          input.disabled = remaining < 1;
        }
        if (afterCount) {
          afterCount.textContent = remaining > 0
            ? `Setelah jumlah pilihan, stok tersisa ${formatStock(remaining - 1)} pcs.`
            : 'Semua stok yang tersedia sudah ada di keranjang.';
        }
        form.querySelectorAll('[data-qty-plus]').forEach((control) => {
          control.disabled = remaining < 2;
        });
        form.querySelectorAll('[data-cart-add-button]').forEach((control) => {
          control.disabled = remaining < 1;
        });
        form.querySelectorAll('[name="go_to_checkout"]').forEach((control) => {
          control.disabled = remaining < 1;
        });
      }

      const cartBadge = document.querySelector('.bb-cart-badge');
      if (cartBadge) cartBadge.textContent = result.cart_item_count;
      if (feedback) {
        feedback.textContent = result.message;
        feedback.hidden = false;
      }
    } catch (error) {
      if (feedback) {
        feedback.textContent = error.message;
        feedback.classList.add('text-danger');
        feedback.hidden = false;
      } else {
        window.alert(error.message);
      }
    } finally {
      if (button) {
        const productCard = form.closest('[data-product-card]');
        const detailQuantity = document.querySelector('[data-detail-qty]');
        if (productCard) {
          button.disabled = Number(productCard.dataset.stockRemaining) < 1;
        } else if (detailQuantity?.dataset.productId === form.querySelector('[name="product_id"]')?.value) {
          button.disabled = Number(detailQuantity.dataset.stock) - Number(detailQuantity.dataset.cartQty) < 1;
        } else {
          button.disabled = false;
        }
      }
    }
  });

  /* ---------------------------------------------------------------------
     Navbar: sticky background change on scroll
     --------------------------------------------------------------------- */
  const navbar = document.querySelector('.bb-navbar');
  if (navbar) {
    const toggleNavbarBg = () => {
      if (window.scrollY > 24) {
        navbar.classList.add('is-scrolled');
      } else {
        navbar.classList.remove('is-scrolled');
      }
    };
    toggleNavbarBg();
    window.addEventListener('scroll', toggleNavbarBg, { passive: true });
  }

  /* ---------------------------------------------------------------------
     Mobile hamburger menu
     --------------------------------------------------------------------- */
  const burger = document.querySelector('.bb-burger');
  const mobileMenu = document.querySelector('.bb-mobile-menu');
  if (burger && mobileMenu) {
    burger.addEventListener('click', function () {
      mobileMenu.classList.toggle('is-open');
      burger.setAttribute('aria-expanded', mobileMenu.classList.contains('is-open') ? 'true' : 'false');
      const icon = burger.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-list');
        icon.classList.toggle('bi-x-lg');
      }
    });
  }

  /* ---------------------------------------------------------------------
     Animated counters (About section: "1000+ Customers", etc.)
     --------------------------------------------------------------------- */
  const counters = document.querySelectorAll('[data-counter]');
  if (counters.length) {
    const animateCounter = (el) => {
      const target = parseFloat(el.dataset.counter);
      const suffix = el.dataset.suffix || '';
      const duration = 1400;
      const start = performance.now();
      const isFloat = target % 1 !== 0;

      function tick(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const value = target * eased;
        el.textContent = (isFloat ? value.toFixed(1) : Math.floor(value)) + suffix;
        if (progress < 1) requestAnimationFrame(tick);
      }
      requestAnimationFrame(tick);
    };

    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counters.forEach((el) => counterObserver.observe(el));
  }

  /* ---------------------------------------------------------------------
     Countdown timer (Promotion section — "Sweet Weekend")
     --------------------------------------------------------------------- */
  const countdownEl = document.querySelector('[data-countdown]');
  if (countdownEl) {
    // Target: 3 days from page load, purely for frontend demo purposes
    const target = new Date();
    target.setDate(target.getDate() + 3);

    const dayEl = countdownEl.querySelector('[data-cd-day]');
    const hourEl = countdownEl.querySelector('[data-cd-hour]');
    const minEl = countdownEl.querySelector('[data-cd-min]');
    const secEl = countdownEl.querySelector('[data-cd-sec]');

    function pad(n) { return String(n).padStart(2, '0'); }

    function updateCountdown() {
      const diff = Math.max(0, target - new Date());
      const d = Math.floor(diff / (1000 * 60 * 60 * 24));
      const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
      const m = Math.floor((diff / (1000 * 60)) % 60);
      const s = Math.floor((diff / 1000) % 60);
      if (dayEl) dayEl.textContent = pad(d);
      if (hourEl) hourEl.textContent = pad(h);
      if (minEl) minEl.textContent = pad(m);
      if (secEl) secEl.textContent = pad(s);
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);
  }

  /* ---------------------------------------------------------------------
     Wishlist toggle (visual only, on product cards)
     --------------------------------------------------------------------- */
  document.querySelectorAll('.bb-wishlist-btn').forEach((btn) => {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      this.classList.toggle('is-active');
      const icon = this.querySelector('i');
      if (icon) {
        icon.classList.toggle('bi-heart');
        icon.classList.toggle('bi-heart-fill');
      }
    });
  });

  /* ---------------------------------------------------------------------
     Hero visual subtle parallax on pointer move (desktop only)
     --------------------------------------------------------------------- */
  const heroVisual = document.querySelector('.bb-hero-visual');
  const heroBlob = document.querySelector('.bb-hero-blob');
  if (heroVisual && heroBlob && window.matchMedia('(pointer: fine)').matches) {
    heroVisual.addEventListener('mousemove', function (e) {
      const rect = heroVisual.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      heroBlob.style.transform = `translate(${x * 12}px, ${y * 10}px) rotate(${x * 3}deg)`;
    });
    heroVisual.addEventListener('mouseleave', function () {
      heroBlob.style.transform = '';
    });
  }

  /* ---------------------------------------------------------------------
     Product filter / sort (frontend-only demo, no reload)
     --------------------------------------------------------------------- */
  const filterChips = document.querySelectorAll('[data-filter-category]');
  const productGridItems = document.querySelectorAll('[data-product-category]');
  const searchInput = document.querySelector('[data-product-search]');
  const sortSelect = document.querySelector('[data-product-sort]');
  const productGrid = document.querySelector('.bb-products-grid');

  function applyFilters() {
    const activeChip = document.querySelector('[data-filter-category].active');
    const category = activeChip ? activeChip.dataset.filterCategory : 'all';
    const query = searchInput ? searchInput.value.trim().toLowerCase() : '';

    productGridItems.forEach((item) => {
      const matchesCategory = category === 'all' || item.dataset.productCategory === category;
      const name = (item.dataset.productName || '').toLowerCase();
      const matchesSearch = !query || name.includes(query);
      item.style.display = (matchesCategory && matchesSearch) ? '' : 'none';
    });
  }

  if (filterChips.length) {
    filterChips.forEach((chip) => {
      chip.addEventListener('click', function () {
        filterChips.forEach((c) => c.classList.remove('active'));
        this.classList.add('active');
        applyFilters();
      });
    });
  }
  if (searchInput) searchInput.addEventListener('input', applyFilters);

  if (sortSelect && productGrid) {
    sortSelect.addEventListener('change', function () {
      const items = Array.from(productGrid.children);
      const value = this.value;
      items.sort((a, b) => {
        const priceA = parseFloat(a.dataset.productPrice || 0);
        const priceB = parseFloat(b.dataset.productPrice || 0);
        const nameA = (a.dataset.productName || '');
        const nameB = (b.dataset.productName || '');
        if (value === 'price-asc') return priceA - priceB;
        if (value === 'price-desc') return priceB - priceA;
        if (value === 'name-asc') return nameA.localeCompare(nameB);
        return 0;
      });
      items.forEach((item) => productGrid.appendChild(item));
    });
  }

  /* ---------------------------------------------------------------------
     Password strength indicator (Register page)
     --------------------------------------------------------------------- */
  const pwInput = document.querySelector('[data-password-strength]');
  if (pwInput) {
    const bar = document.querySelector('.bb-pw-strength-bar');
    const label = document.querySelector('[data-password-strength-label]');
    pwInput.addEventListener('input', function () {
      const val = this.value;
      let score = 0;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const levels = [
        { width: '0%', color: '#E5DDCF', text: '' },
        { width: '25%', color: '#C0392B', text: 'Lemah' },
        { width: '50%', color: '#D98A3D', text: 'Cukup' },
        { width: '75%', color: '#C99A3E', text: 'Kuat' },
        { width: '100%', color: '#2E6B3A', text: 'Sangat Kuat' },
      ];
      const level = levels[score];
      if (bar) { bar.style.width = level.width; bar.style.background = level.color; }
      if (label) { label.textContent = level.text; }
    });
  }

  /* ---------------------------------------------------------------------
     Bootstrap tooltip / toast init (if present on page)
     --------------------------------------------------------------------- */
  if (window.bootstrap) {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      new bootstrap.Tooltip(el);
    });

    document.querySelectorAll('[data-toast]').forEach((toast) => {
      new bootstrap.Toast(toast, { delay: 4500 }).show();
    });
  }

  document.querySelectorAll('[data-loading-button]').forEach((button) => {
    button.form?.addEventListener('submit', (event) => {
      const cartAddForm = button.form.matches('[data-cart-add-form]');
      if (cartAddForm && event.submitter?.name !== 'go_to_checkout') return;

      button.disabled = true;
      button.textContent = 'Memproses...';
    });
  });

  const revealItems = document.querySelectorAll('.bb-section, .bb-panel, .bb-product-card');
  if ('IntersectionObserver' in window) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12 });

    revealItems.forEach((item) => {
      item.classList.add('bb-scroll-reveal');
      revealObserver.observe(item);
    });
  }

  document.querySelectorAll('[data-lightbox]').forEach((button) => {
    button.addEventListener('click', () => {
      const image = button.querySelector('img');
      if (!image) return;

      const overlay = document.createElement('div');
      overlay.className = 'bb-lightbox';
      overlay.setAttribute('role', 'dialog');
      overlay.setAttribute('aria-modal', 'true');
      overlay.setAttribute('aria-label', 'Foto produk diperbesar');

      const closeButton = document.createElement('button');
      closeButton.type = 'button';
      closeButton.className = 'bb-lightbox-close';
      closeButton.setAttribute('aria-label', 'Tutup foto');
      closeButton.textContent = 'Close';

      const enlargedImage = document.createElement('img');
      enlargedImage.src = image.currentSrc || image.src;
      enlargedImage.alt = image.alt;
      overlay.append(closeButton, enlargedImage);
      document.body.append(overlay);

      const close = () => overlay.remove();
      closeButton.addEventListener('click', close);
      overlay.addEventListener('click', (event) => {
        if (event.target === overlay) close();
      });
      overlay.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') close();
      });
      closeButton.focus();
    });
  });

});
