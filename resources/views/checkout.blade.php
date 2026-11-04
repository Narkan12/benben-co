@extends('layouts.app')

@section('title', 'Checkout — Bakery BenBen Co.')

@section('content')
    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">Checkout</span>
            <h1 class="section-title mt-2">Selesaikan Pesanan Anda</h1>
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / <a href="{{ url('/cart') }}">Keranjang</a> / Checkout</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <form class="row g-4" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="col-lg-8">
                    <div class="bb-panel mb-4">
                        <h5 class="bb-panel-title"><i class="bi bi-person me-2"></i>Informasi Pelanggan</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="checkout-name" class="bb-form-label">Nama Lengkap</label>
                                <input id="checkout-name" type="text" name="nama" class="bb-input" value="{{ old('nama', $customer?->nama) }}" placeholder="Nama Anda" required maxlength="100">
                            </div>
                            <div class="col-md-6">
                                <label for="checkout-phone" class="bb-form-label">Nomor WhatsApp</label>
                                <input id="checkout-phone" type="tel" name="no_whatsapp" class="bb-input" value="{{ old('no_whatsapp', $customer?->no_whatsapp) }}" placeholder="08xx-xxxx-xxxx" required maxlength="20">
                            </div>
                            <div class="col-12">
                                <label for="checkout-address" class="bb-form-label">Alamat Pengiriman</label>
                                <textarea id="checkout-address" name="alamat" class="bb-input" rows="2" placeholder="Wajib diisi untuk delivery">{{ old('alamat', $customer?->alamat) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="checkout-note" class="bb-form-label">Catatan (opsional)</label>
                                <textarea id="checkout-note" name="catatan" class="bb-input" rows="2" maxlength="1000">{{ old('catatan') }}</textarea>
                            </div>
                        </div>
                    </div>

                    <div class="bb-panel mb-4">
                        <h5 class="bb-panel-title"><i class="bi bi-truck me-2"></i>Metode Pengiriman</h5>
                        <div class="row g-3">
                            @foreach(['pickup' => 'Ambil di Toko', 'delivery' => 'Delivery'] as $method => $label)
                                <div class="col-md-6">
                                    <label class="bb-filter-card d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                                        <input type="radio" name="delivery_method" value="{{ $method }}" @checked(old('delivery_method', 'pickup') === $method) required>
                                        <span>{{ $label }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bb-panel">
                        <h5 class="bb-panel-title"><i class="bi bi-credit-card me-2"></i>Metode Pembayaran</h5>
                        <div class="row g-3">
                            @foreach(['transfer' => 'Transfer Bank', 'ewallet' => 'E-Wallet', 'cash' => 'Tunai'] as $method => $label)
                                <div class="col-md-4">
                                    <label class="bb-filter-card d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                                        <input type="radio" name="payment_method" value="{{ $method }}" @checked(old('payment_method', 'transfer') === $method) required>
                                        <span>{{ $label }}</span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        <div class="bb-input-group mt-3" data-payment-proof-group>
                            <label for="payment-proof" class="bb-form-label">Bukti Transfer / E-Wallet (opsional, JPG/PNG/WEBP maksimal 5 MB)</label>
                            <input id="payment-proof" type="file" name="payment_proof" class="bb-input" accept="image/jpeg,image/png,image/webp">
                            <small class="section-sub">Admin akan memeriksa bukti Transfer atau E-Wallet sebelum pesanan dikonfirmasi.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="bb-summary-card">
                        <h5 class="bb-panel-title" style="margin-bottom:16px;">Ringkasan Pesanan</h5>
                        @foreach($items as $item)
                            <div class="bb-summary-row">
                                <span>{{ $item['product']->nama }} × {{ $item['qty'] }}</span>
                                <span>Rp{{ number_format($item['line_total'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                        <div class="bb-input-group mt-3">
                            <label for="promo-code" class="bb-form-label">Kode Promo (opsional)</label>
                            <input id="promo-code" type="text" name="promo_code" class="bb-input" value="{{ old('promo_code') }}" maxlength="50">
                            <button type="button" class="btn-bb btn-bb-outline btn-bb-sm mt-2" data-apply-promo>
                                Konfirmasi Kode Promo
                            </button>
                            <small class="d-block mt-2" data-promo-message role="status" aria-live="polite"></small>
                        </div>
                        <div class="bb-summary-row"><span>Subtotal</span><span>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
                        <div class="bb-summary-row"><span>Diskon</span><span data-discount-amount>Rp0</span></div>
                        <div class="bb-summary-row"><span>Ongkos Kirim</span><span data-shipping-fee>Rp0</span></div>
                        <div class="bb-summary-row total"><span>Perkiraan Total</span><span data-order-total>Rp{{ number_format($subtotal, 0, ',', '.') }}</span></div>
                        <button type="submit" class="btn-bb btn-bb-primary w-100 mt-3" data-loading-button>
                            Konfirmasi Pesanan <i class="bi bi-check2"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const address = document.getElementById('checkout-address');
            const proofGroup = document.querySelector('[data-payment-proof-group]');
            const proofInput = document.getElementById('payment-proof');
            const shippingFee = document.querySelector('[data-shipping-fee]');
            const orderTotal = document.querySelector('[data-order-total]');
            const discountAmount = document.querySelector('[data-discount-amount]');
            const promoInput = document.getElementById('promo-code');
            const promoButton = document.querySelector('[data-apply-promo]');
            const promoMessage = document.querySelector('[data-promo-message]');
            const subtotal = {{ (float) $subtotal }};
            const deliveryCost = {{ (float) $delivery }};
            const money = (amount) => 'Rp' + new Intl.NumberFormat('id-ID').format(amount);
            let appliedDiscount = 0;

            const updateForm = () => {
                const delivery = document.querySelector('[name="delivery_method"]:checked')?.value === 'delivery';
                const payment = document.querySelector('[name="payment_method"]:checked')?.value;
                address.required = delivery;
                proofGroup.hidden = payment === 'cash';
                proofInput.disabled = payment === 'cash';
                const fee = delivery ? deliveryCost : 0;
                shippingFee.textContent = money(fee);
                orderTotal.textContent = money(Math.max(0, subtotal - appliedDiscount + fee));
            };

            document.querySelectorAll('[name="delivery_method"], [name="payment_method"]').forEach((input) => {
                input.addEventListener('change', updateForm);
            });
            promoInput.addEventListener('input', () => {
                appliedDiscount = 0;
                discountAmount.textContent = money(0);
                promoMessage.textContent = '';
                promoMessage.className = 'd-block mt-2';
                updateForm();
            });
            promoButton.addEventListener('click', async () => {
                const promoCode = promoInput.value.trim();
                if (!promoCode) {
                    appliedDiscount = 0;
                    discountAmount.textContent = money(0);
                    promoMessage.textContent = 'Masukkan kode promo terlebih dahulu.';
                    promoMessage.className = 'd-block mt-2 text-danger';
                    updateForm();
                    return;
                }

                promoButton.disabled = true;
                promoMessage.textContent = 'Memeriksa kode promo...';
                promoMessage.className = 'd-block mt-2 text-muted';

                try {
                    const response = await fetch(@json(route('checkout.promo-preview')), {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                        },
                        body: JSON.stringify({
                            promo_code: promoCode,
                            delivery_method: document.querySelector('[name="delivery_method"]:checked').value,
                        }),
                    });
                    const result = await response.json();

                    if (!response.ok) {
                        throw new Error(result.message || result.errors?.promo_code?.[0] || 'Kode promo tidak dapat digunakan.');
                    }

                    appliedDiscount = Number(result.discount);
                    discountAmount.textContent = money(appliedDiscount);
                    promoInput.value = result.promo_code;
                    promoMessage.textContent = result.message;
                    promoMessage.className = 'd-block mt-2 text-success';
                    updateForm();
                } catch (error) {
                    appliedDiscount = 0;
                    discountAmount.textContent = money(0);
                    promoMessage.textContent = error.message || 'Kode promo gagal diperiksa.';
                    promoMessage.className = 'd-block mt-2 text-danger';
                    updateForm();
                } finally {
                    promoButton.disabled = false;
                }
            });
            updateForm();
        });
    </script>
@endpush
