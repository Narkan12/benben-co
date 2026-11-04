(function initNavbar() {
    var navbar    = document.getElementById('navbar');
    var hamburger = document.getElementById('hamburger');
    var navMenu   = document.getElementById('navMenu');

    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 30) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });
    }

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function () {
            var isOpen = navMenu.classList.toggle('open');
            hamburger.classList.toggle('open', isOpen);
            hamburger.setAttribute('aria-expanded', isOpen);
        });

        document.addEventListener('click', function (e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('open');
                hamburger.classList.remove('open');
                hamburger.setAttribute('aria-expanded', 'false');
            }
        });
    }

    var currentPage = window.location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.nav-menu a').forEach(function (link) {
        var href = (link.getAttribute('href') || '').split('/').pop().split('?')[0];
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
})();


(function initScrollAnimations() {
    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    document.querySelectorAll('.fade-up, .fade-in').forEach(function (el) {
        observer.observe(el);
    });
})();


var Toast = {
    container: null,

    init: function () {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },

    show: function (pesan, tipe, durasi) {
        tipe   = tipe   || 'success';
        durasi = durasi || 3000;

        this.init();

        var ikon = { success: '✓', error: '✕', info: 'i' };

        var toast = document.createElement('div');
        toast.className = 'toast-item ' + tipe;
        toast.innerHTML = '<span style="font-weight:700;">' + (ikon[tipe] || '✓') + '</span>'
                        + '<span>' + pesan + '</span>';

        this.container.appendChild(toast);

        setTimeout(function () {
            toast.classList.add('removing');
            setTimeout(function () {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 300);
        }, durasi);
    }
};


document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-add-cart]');
    if (!btn) return;
    e.preventDefault();

    var productId = parseInt(btn.dataset.addCart);
    var qtyInput  = document.getElementById('product-qty');
    var qty       = qtyInput ? parseInt(qtyInput.value) : 1;

    if (!productId || productId <= 0) return;

    btn.disabled     = true;
    var originalText = btn.innerHTML;
    btn.innerHTML    = 'Menambahkan...';

    var basePath = getBasePath();

    fetch(basePath + '/cart/add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, qty: qty })
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (data.success) {
            Toast.show(data.message || 'Produk ditambahkan ke keranjang!', 'success');
            updateCartBadge(data.cart_count);
        } else {
            Toast.show(data.message || 'Gagal menambahkan produk.', 'error');
        }
    })
    .catch(function () {
        Toast.show('Terjadi kesalahan koneksi.', 'error');
    })
    .finally(function () {
        btn.disabled  = false;
        btn.innerHTML = originalText;
    });
});


(function initCartControls() {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.qty-btn');
        if (!btn) return;

        var productId = btn.dataset.id;
        var action    = btn.dataset.action;
        var qtyEl     = document.getElementById('qty-' + productId);
        if (!qtyEl || !productId) return;

        var qty = parseInt(qtyEl.textContent) || 0;
        if (action === 'plus')  qty++;
        if (action === 'minus') qty--;
        if (qty < 0) qty = 0;

        var basePath = getBasePath();

        fetch(basePath + '/cart/update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ product_id: parseInt(productId), qty: qty })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                if (qty === 0) {
                    var row = document.getElementById('row-' + productId);
                    if (row) row.remove();
                    updateCartPageTotal();
                } else {
                    qtyEl.textContent = qty;
                    var harga      = parseFloat(document.getElementById('price-' + productId)?.dataset.price || 0);
                    var subtotalEl = document.getElementById('subtotal-' + productId);
                    if (subtotalEl) subtotalEl.textContent = formatRp(harga * qty);
                    updateCartPageTotal();
                }
                updateCartBadge(data.cart_count);
            } else {
                location.reload();
            }
        })
        .catch(function () { location.reload(); });
    });

    function updateCartPageTotal() {
        var total = 0;
        document.querySelectorAll('[id^="subtotal-"]').forEach(function (el) {
            total += parseInt(el.textContent.replace(/[^0-9]/g, '')) || 0;
        });
        var totalEl = document.getElementById('cart-total');
        if (totalEl) totalEl.textContent = formatRp(total);
    }
})();


(function initDetailQty() {
    var qtyInput = document.getElementById('product-qty');
    if (!qtyInput) return;

    var maxStok = parseInt(qtyInput.dataset.max || '99');

    document.getElementById('qty-plus')?.addEventListener('click', function () {
        var val = parseInt(qtyInput.value) || 1;
        if (val < maxStok) qtyInput.value = val + 1;
    });

    document.getElementById('qty-minus')?.addEventListener('click', function () {
        var val = parseInt(qtyInput.value) || 1;
        if (val > 1) qtyInput.value = val - 1;
    });
})();


(function initDeliveryToggle() {
    var radios       = document.querySelectorAll('input[name="delivery_method"]');
    var alamatGroup  = document.getElementById('alamat_group');
    var deliveryEl   = document.getElementById('delivery_display');
    var grandTotalEl = document.getElementById('grandTotal');
    var DELIVERY_FEE = 10000;

    function updateDelivery(isDelivery) {
        if (alamatGroup) {
            alamatGroup.style.display = isDelivery ? '' : 'none';
            var alamatInput = alamatGroup.querySelector('textarea, input');
            if (alamatInput) alamatInput.required = isDelivery;
        }

        if (deliveryEl) {
            deliveryEl.textContent   = isDelivery ? formatRp(DELIVERY_FEE) : 'Gratis (Pickup)';
            deliveryEl.dataset.value = isDelivery ? DELIVERY_FEE : 0;
        }

        if (grandTotalEl) {
            var subtotal = parseFloat(document.getElementById('subtotal_display')?.dataset.value || 0);
            var diskon   = parseFloat(document.getElementById('discount_amount')?.value || 0);
            var fee      = isDelivery ? DELIVERY_FEE : 0;
            grandTotalEl.textContent = formatRp(Math.max(0, subtotal - diskon + fee));
        }
    }

    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            updateDelivery(this.value === 'delivery');
        });
    });

    var checked = document.querySelector('input[name="delivery_method"]:checked');
    if (checked) updateDelivery(checked.value === 'delivery');
})();

(function initPromoCode() {
    var promoBtn       = document.getElementById('applyPromo');
    var promoInput     = document.getElementById('promo_code');
    var promoResult    = document.getElementById('promoResult');
    var discountRow    = document.getElementById('discountRow');
    var discountVal    = document.getElementById('discountValue');
    var grandTotalEl   = document.getElementById('grandTotal');
    var promoIdInput   = document.getElementById('promo_id');
    var discountAmount = document.getElementById('discount_amount');

    if (!promoBtn || !promoInput) return;

    promoBtn.addEventListener('click', function () {
        var kode = promoInput.value.trim().toUpperCase();
        if (!kode) return;

        promoBtn.disabled     = true;
        promoBtn.textContent  = 'Memeriksa...';

        var basePath = getBasePath();

        fetch(basePath + '/checkout/check_promo.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ kode: kode })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.valid) {
                var subtotal = parseFloat(document.getElementById('subtotal_display')?.dataset.value || 0);
                var fee      = parseFloat(document.getElementById('delivery_display')?.dataset.value || 0);
                var diskon   = data.discount;
                var total    = Math.max(0, subtotal - diskon + fee);

                if (discountRow) discountRow.style.display = '';
                if (discountVal) discountVal.textContent = '-' + formatRp(diskon);
                if (grandTotalEl) grandTotalEl.textContent = formatRp(total);
                if (promoIdInput) promoIdInput.value = data.promo_id;
                if (discountAmount) discountAmount.value = diskon;

                var kodeInput = document.getElementById('kode_promo_used');
                if (kodeInput) kodeInput.value = kode;

                if (promoResult) {
                    promoResult.style.color = '#155724';
                    promoResult.textContent = '✓ Promo "' + kode + '" berhasil! Diskon: ' + formatRp(diskon);
                }
                Toast.show('Promo berhasil diterapkan!', 'success');
            } else {
                if (promoIdInput) promoIdInput.value = '';
                if (discountAmount) discountAmount.value = 0;
                if (discountRow) discountRow.style.display = 'none';

                if (promoResult) {
                    promoResult.style.color = '#721c24';
                    promoResult.textContent = '✕ ' + (data.message || 'Kode promo tidak valid');
                }
            }
        })
        .catch(function () {
            if (promoResult) {
                promoResult.style.color = '#721c24';
                promoResult.textContent = 'Terjadi kesalahan. Coba lagi.';
            }
        })
        .finally(function () {
            promoBtn.disabled    = false;
            promoBtn.textContent = 'Terapkan';
        });
    });
})();

(function initCheckoutValidation() {
    var form = document.getElementById('checkoutForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
        var valid = true;

        form.querySelectorAll('.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
        form.querySelectorAll('.invalid-feedback').forEach(function (el) { el.textContent = ''; });

        form.querySelectorAll('[required]').forEach(function (field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                var fb = field.nextElementSibling;
                if (fb && fb.classList.contains('invalid-feedback')) {
                    fb.textContent = 'Field ini wajib diisi.';
                }
                valid = false;
            }
        });

        var waInput = form.querySelector('[name="no_whatsapp"]');
        if (waInput && waInput.value.trim()) {
            var cleaned = waInput.value.replace(/\D/g, '');
            if (cleaned.length < 9 || cleaned.length > 15) {
                waInput.classList.add('is-invalid');
                var fb = waInput.nextElementSibling;
                if (fb && fb.classList.contains('invalid-feedback')) {
                    fb.textContent = 'Nomor WhatsApp tidak valid.';
                }
                valid = false;
            }
        }

        if (!valid) {
            e.preventDefault();
            var firstErr = form.querySelector('.is-invalid');
            if (firstErr) firstErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
})();


(function initGallery() {
    document.addEventListener('click', function (e) {
        var item = e.target.closest('.gallery-item');
        if (!item) return;

        var img = item.querySelector('img');
        if (!img) return;

        var overlay = document.createElement('div');
        overlay.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9998;display:flex;align-items:center;justify-content:center;cursor:zoom-out;padding:1rem;';

        var bigImg = document.createElement('img');
        bigImg.src = img.src;
        bigImg.alt = img.alt;
        bigImg.style.cssText = 'max-width:90vw;max-height:90vh;border-radius:12px;box-shadow:0 20px 60px rgba(0,0,0,.4);';
        overlay.appendChild(bigImg);
        document.body.appendChild(overlay);

        overlay.addEventListener('click', function () { document.body.removeChild(overlay); });

        function onEsc(e) {
            if (e.key === 'Escape') {
                if (overlay.parentNode) document.body.removeChild(overlay);
                document.removeEventListener('keydown', onEsc);
            }
        }
        document.addEventListener('keydown', onEsc);
    });
})();


(function initRatingStars() {
    var group = document.querySelector('.rating-stars-input');
    if (!group) return;

    var labels = group.querySelectorAll('label');
    labels.forEach(function (label, idx) {
        label.addEventListener('mouseenter', function () {
            labels.forEach(function (l, i) {
                l.style.color = i >= labels.length - idx - 1 ? '#C8932A' : '';
            });
        });
        label.addEventListener('mouseleave', function () {
            labels.forEach(function (l) { l.style.color = ''; });
        });
    });
})();


document.addEventListener('click', function (e) {
    var anchor = e.target.closest('a[href^="#"]');
    if (!anchor) return;

    var id = anchor.getAttribute('href');
    if (id === '#') return;

    var target = document.querySelector(id);
    if (target) {
        e.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
});


(function initFavorites() {
    function getFavs() {
        try { return JSON.parse(localStorage.getItem('benben_favs') || '[]'); }
        catch (e) { return []; }
    }
    function saveFavs(favs) {
        try { localStorage.setItem('benben_favs', JSON.stringify(favs)); } catch (e) {}
    }

    function refreshFavBtns() {
        var favs = getFavs();
        document.querySelectorAll('.fav-btn').forEach(function (btn) {
            var id = btn.dataset.id;
            btn.classList.toggle('active', favs.indexOf(id) !== -1);
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.fav-btn');
        if (!btn) return;
        e.preventDefault();
        e.stopPropagation();

        var id   = btn.dataset.id;
        var favs = getFavs();
        var idx  = favs.indexOf(id);

        if (idx !== -1) {
            favs.splice(idx, 1);
            Toast.show('Dihapus dari favorit', 'info');
        } else {
            favs.push(id);
            Toast.show('Ditambahkan ke favorit', 'success');
        }

        saveFavs(favs);
        refreshFavBtns();
    });

    refreshFavBtns();
})();


(function initProductFilter() {
    var searchInput = document.getElementById('productSearch');
    var cards       = document.querySelectorAll('.product-card[data-category]');
    var noResults   = document.getElementById('noResults');
    var activeSlug  = 'all';

    function filterProducts() {
        var search = searchInput ? searchInput.value.toLowerCase().trim() : '';
        var visible = 0;

        cards.forEach(function (card) {
            var name     = (card.dataset.name || '').toLowerCase();
            var cat      = card.dataset.category || '';
            var matchCat = activeSlug === 'all' || cat === activeSlug;
            var matchSrc = !search || name.indexOf(search) !== -1;

            if (matchCat && matchSrc) {
                card.style.display = '';
                visible++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResults) {
            noResults.style.display = visible === 0 ? 'block' : 'none';
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterProducts);
    }

    document.querySelectorAll('.category-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            document.querySelectorAll('.category-tab').forEach(function (t) {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            activeSlug = this.dataset.category;
            filterProducts();
        });
    });

    var urlCat = new URLSearchParams(window.location.search).get('category') || 'all';
    activeSlug = urlCat;
    var activeTab = document.querySelector('.category-tab[data-category="' + urlCat + '"]');
    if (activeTab) {
        document.querySelectorAll('.category-tab').forEach(function (t) { t.classList.remove('active'); });
        activeTab.classList.add('active');
    }
    filterProducts();
})();


var POS = {
    cart: {},

    init: function () {
        this.bindSearch();
        this.bindProductCards();
        this.bindClearCart();
        this.bindPaymentInput();
        this.bindProcessBtn();
        this.renderCart();
    },

    bindSearch: function () {
        var input = document.getElementById('posSearch');
        if (!input) return;

        input.addEventListener('input', function () {
            var q = input.value.toLowerCase();
            document.querySelectorAll('.pos-product-card').forEach(function (card) {
                var name = (card.dataset.name || '').toLowerCase();
                card.style.display = name.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    },

    bindProductCards: function () {
        var self = this;
        document.querySelectorAll('.pos-product-card:not(.out-of-stock)').forEach(function (card) {
            card.addEventListener('click', function () {
                self.addItem(
                    card.dataset.id,
                    card.dataset.displayName,
                    parseFloat(card.dataset.price),
                    parseInt(card.dataset.stock),
                    card.dataset.img
                );
            });
        });
    },

    addItem: function (id, nama, harga, stok, img) {
        if (this.cart[id]) {
            if (this.cart[id].qty >= stok) {
                Toast.show('Stok tidak mencukupi!', 'error');
                return;
            }
            this.cart[id].qty++;
        } else {
            this.cart[id] = { id: id, nama: nama, harga: harga, qty: 1, stok: stok, img: img };
        }
        this.renderCart();
        Toast.show(nama + ' ditambahkan', 'success', 1500);
    },

    removeItem: function (id) {
        delete this.cart[id];
        this.renderCart();
    },

    updateQty: function (id, delta) {
        if (!this.cart[id]) return;
        var newQty = this.cart[id].qty + delta;
        if (newQty <= 0) { this.removeItem(id); return; }
        if (newQty > this.cart[id].stok) { Toast.show('Stok tidak mencukupi!', 'error'); return; }
        this.cart[id].qty = newQty;
        this.renderCart();
    },

    getTotal: function () {
        var total = 0;
        Object.values(this.cart).forEach(function (item) { total += item.harga * item.qty; });
        return total;
    },

    renderCart: function () {
        var container  = document.getElementById('posCartItems');
        var emptyMsg   = document.getElementById('posCartEmpty');
        var totalEl    = document.getElementById('posTotal');
        var grandEl    = document.getElementById('posTotalGrand');
        var processBtn = document.getElementById('posProcessBtn');
        var items      = Object.values(this.cart);

        if (!container) return;

        container.innerHTML = '';

        if (items.length === 0) {
            if (emptyMsg) emptyMsg.style.display = '';
            if (totalEl)  totalEl.textContent  = formatRp(0);
            if (grandEl)  grandEl.textContent  = formatRp(0);
            if (processBtn) processBtn.disabled = true;
            this.updateChange();
            return;
        }

        if (emptyMsg) emptyMsg.style.display = 'none';
        if (processBtn) processBtn.disabled = false;

        var self    = this;
        var basePath = getBasePath();

        items.forEach(function (item) {
            var div = document.createElement('div');
            div.className = 'pos-cart-item';
            div.innerHTML = '<img src="' + item.img + '" alt="' + item.nama + '" '
                          + 'onerror="this.src=\'' + basePath + '/assets/images/no-image.png\'">'
                          + '<div style="flex:1;min-width:0;">'
                          +   '<div class="pos-cart-item-name">' + item.nama + '</div>'
                          +   '<div class="pos-cart-item-price">' + formatRp(item.harga * item.qty) + '</div>'
                          + '</div>'
                          + '<div class="pos-cart-item-qty">'
                          +   '<button class="pos-qty-btn" onclick="POS.updateQty(\'' + item.id + '\',-1)">−</button>'
                          +   '<span class="pos-qty-num">' + item.qty + '</span>'
                          +   '<button class="pos-qty-btn" onclick="POS.updateQty(\'' + item.id + '\',1)">+</button>'
                          + '</div>';
            container.appendChild(div);
        });

        var total = this.getTotal();
        if (totalEl) totalEl.textContent = formatRp(total);
        if (grandEl) grandEl.textContent = formatRp(total);
        this.updateChange();
    },

    updateChange: function () {
        var bayar     = parseFloat(document.getElementById('posBayar')?.value) || 0;
        var total     = this.getTotal();
        var kembalian = bayar - total;
        var el        = document.getElementById('posKembalian');
        if (el) {
            el.textContent   = kembalian >= 0 ? formatRp(kembalian) : '-';
            el.style.color   = kembalian >= 0 ? '#155724' : '#721c24';
        }
    },

    bindPaymentInput: function () {
        var self  = this;
        var input = document.getElementById('posBayar');
        if (input) input.addEventListener('input', function () { self.updateChange(); });
    },

    bindClearCart: function () {
        var self = this;
        var btn  = document.getElementById('posClearCart');
        if (btn) {
            btn.addEventListener('click', function () {
                if (confirm('Kosongkan keranjang?')) {
                    self.cart = {};
                    self.renderCart();
                }
            });
        }
    },

    bindProcessBtn: function () {
        var self = this;
        var btn  = document.getElementById('posProcessBtn');
        if (!btn) return;

        btn.addEventListener('click', function () {
            if (Object.keys(self.cart).length === 0) return;

            var payment  = document.getElementById('posPayment')?.value  || 'cash';
            var bayar    = parseFloat(document.getElementById('posBayar')?.value) || 0;
            var total    = self.getTotal();
            var customer = document.getElementById('posCustomer')?.value.trim() || '';

            if (payment === 'cash' && bayar < total) {
                Toast.show('Uang yang diterima kurang dari total!', 'error');
                return;
            }

            btn.disabled     = true;
            btn.textContent  = 'Memproses...';

            var basePath = getBasePath();

            fetch(basePath + '/admin/pos/process.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cart      : Object.values(self.cart),
                    total     : total,
                    payment   : payment,
                    bayar     : bayar,
                    customer  : customer,
                    kembalian : Math.max(0, bayar - total)
                })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    self.showSuccess(data);
                } else {
                    Toast.show(data.message || 'Transaksi gagal.', 'error');
                    btn.disabled    = false;
                    btn.textContent = 'PROCESS PAYMENT';
                }
            })
            .catch(function () {
                Toast.show('Terjadi kesalahan koneksi.', 'error');
                btn.disabled    = false;
                btn.textContent = 'PROCESS PAYMENT';
            });
        });
    },

    showSuccess: function (data) {
        var modal = document.getElementById('posSuccessModal');
        if (!modal) return;

        document.getElementById('posSuccessCode').textContent   = data.kode_order;
        document.getElementById('posSuccessTotal').textContent  = formatRp(data.total);
        document.getElementById('posSuccessChange').textContent = formatRp(data.kembalian);
        modal.style.display = 'flex';

        var self = this;
        var newBtn = document.getElementById('posNewTransaction');
        if (newBtn) {
            var newBtnClone = newBtn.cloneNode(true);
            newBtn.parentNode.replaceChild(newBtnClone, newBtn);

            newBtnClone.addEventListener('click', function () {
                self.cart = {};
                self.renderCart();
                modal.style.display = 'none';
                var bayarInput = document.getElementById('posBayar');
                var custInput  = document.getElementById('posCustomer');
                var procBtn    = document.getElementById('posProcessBtn');
                if (bayarInput) bayarInput.value = '';
                if (custInput)  custInput.value  = '';
                if (procBtn) { procBtn.disabled = true; procBtn.textContent = 'PROCESS PAYMENT'; }
            });
        }
    }
};

if (document.getElementById('posCartItems')) {
    POS.init();
}

function filterPOSCat(slug, btn) {
    document.querySelectorAll('[data-pos-cat]').forEach(function (b) { b.classList.remove('active'); });
    btn.classList.add('active');
    document.querySelectorAll('.pos-product-card').forEach(function (card) {
        card.style.display = (slug === 'all' || card.dataset.cat === slug) ? '' : 'none';
    });
}

function toggleBayarField() {
    var group = document.getElementById('bayarFieldGroup');
    var pay   = document.getElementById('posPayment');
    if (group && pay) {
        group.style.display = pay.value === 'cash' ? '' : 'none';
    }
}


(function initImagePreview() {
    var fileInput = document.getElementById('product_image');
    var preview   = document.getElementById('imagePreview');
    if (!fileInput || !preview) return;

    fileInput.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;

        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src   = e.target.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
})();


document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-confirm-delete]');
    if (!btn) return;

    var pesan = btn.dataset.confirmDelete || 'Apakah Anda yakin ingin menghapus item ini?';
    if (!confirm(pesan)) {
        e.preventDefault();
    }
});


(function initStatusUpdate() {
    document.querySelectorAll('.status-select').forEach(function (select) {
        select.addEventListener('change', function () {
            var orderId   = this.dataset.orderId;
            var newStatus = this.value;
            var basePath  = getBasePath();

            fetch(basePath + '/admin/orders/update_status.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ order_id: parseInt(orderId), status: newStatus })
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    Toast.show('Status diperbarui', 'success');
                    var badge = select.closest('tr')?.querySelector('.badge');
                    if (badge) {
                        badge.className = 'badge badge-' + newStatus;
                        badge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
                    }
                } else {
                    Toast.show('Gagal memperbarui status', 'error');
                }
            })
            .catch(function () { Toast.show('Terjadi kesalahan', 'error'); });
        });
    });
})();


(function initAdminSidebar() {
    var toggleBtn = document.getElementById('sidebarToggle');
    var sidebar   = document.getElementById('adminSidebar');
    if (!toggleBtn || !sidebar) return;

    toggleBtn.addEventListener('click', function () {
        sidebar.classList.toggle('open');
    });

    function checkWidth() {
        toggleBtn.style.display = window.innerWidth < 900 ? 'flex' : 'none';
    }
    checkWidth();
    window.addEventListener('resize', checkWidth);
})();


function formatRp(angka) {
    return 'Rp' + new Intl.NumberFormat('id-ID').format(Math.round(angka));
}

function getBasePath() {
    var meta = document.querySelector('meta[name="base-url"]');
    if (meta && meta.content) {
        return meta.content.replace(/\/$/, '');
    }
    return '';
}

function updateCartBadge(count) {
    document.querySelectorAll('.cart-badge').forEach(function (el) {
        el.textContent     = count;
        el.style.display   = count > 0 ? 'flex' : 'none';
    });
}
