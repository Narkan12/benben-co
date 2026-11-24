@extends('layouts.admin')

@section('title', 'Pengaturan Toko — Admin Bakery BenBen Co.')

@section('content')
    @php
        $bankAccounts = $bankAccounts ?: [[]];
        $suggestedBanks = [
            'Bank BNI',
            'Bank BRI',
            'Bank BCA',
            'Bank Mandiri',
            'Bank BTN',
            'Bank Syariah Indonesia (BSI)',
            'Bank CIMB Niaga',
            'Bank Permata',
            'Bank Danamon',
            'Bank OCBC',
            'Bank Jago',
        ];
    @endphp

    <div class="bb-dash-topbar">
        <div>
            <h2 class="section-title" style="font-size:1.6rem;">Pengaturan Toko</h2>
            <p class="section-sub mb-0">Atur ongkir dan pilihan pembayaran yang tampil untuk pelanggan.</p>
        </div>
    </div>

    <div class="bb-panel bb-store-settings-panel">
        <form method="POST" action="{{ route('admin.settings.payment.update') }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <input type="hidden" name="bank_accounts_present" value="1">

            <section class="bb-store-settings-section">
                <div class="bb-store-settings-section-heading">
                    <span class="bb-store-settings-section-icon"><i class="bi bi-truck"></i></span>
                    <div>
                        <h3>Pengiriman dan ongkos kirim</h3>
                        <p>Ongkir berlaku untuk pesanan yang dikirim. Ambil di toko tetap tanpa ongkir.</p>
                    </div>
                </div>
                <div class="row g-3 align-items-end">
                    <div class="col-xl-4 col-md-6">
                        <label for="delivery-fee" class="bb-form-label">Ongkos kirim (Rp)</label>
                        <input id="delivery-fee" type="text" name="delivery_fee"
                               class="bb-input @error('delivery_fee') is-invalid @enderror"
                               value="{{ old('delivery_fee', number_format($paymentSettings->delivery_fee, 0, ',', '.')) }}"
                               inputmode="numeric" data-numeric-input data-money-input required>
                        @error('delivery_fee')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="col-xl-8 col-md-6">
                        <div class="bb-delivery-fee-note">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            <span>Tarif ini otomatis digunakan untuk pesanan dengan metode pengiriman. Pelanggan yang mengambil pesanan di toko tidak dikenakan ongkir.</span>
                        </div>
                    </div>
                </div>
            </section>

            <section class="bb-store-settings-section">
                <div class="bb-store-settings-section-heading">
                    <span class="bb-store-settings-section-icon"><i class="bi bi-bank"></i></span>
                    <div>
                        <h3>Rekening transfer bank</h3>
                        <p>Masukkan rekening aktif milik toko. Nama bank berikut hanya saran, nomor rekening tidak diisi otomatis.</p>
                    </div>
                    <button type="button" class="btn-bb btn-bb-outline bb-add-bank-account" id="add-bank-account">
                        <i class="bi bi-plus-lg" aria-hidden="true"></i> Tambah rekening
                    </button>
                </div>

                <datalist id="bank-name-options">
                    @foreach($suggestedBanks as $suggestedBank)
                        <option value="{{ $suggestedBank }}">
                    @endforeach
                </datalist>

                <div class="bb-bank-account-list" id="bank-account-list">
                    @foreach($bankAccounts as $index => $account)
                        <div class="bb-bank-account-card" data-bank-account-row data-index="{{ $index }}">
                            <div class="bb-bank-account-card-heading">
                                <span><i class="bi bi-credit-card-2-front" aria-hidden="true"></i> Rekening <span data-account-number>{{ $loop->iteration }}</span></span>
                                <button type="button" class="bb-remove-bank-account" data-remove-bank-account aria-label="Hapus rekening {{ $loop->iteration }}">
                                    <i class="bi bi-trash3" aria-hidden="true"></i> Hapus
                                </button>
                            </div>
                            <div class="row g-3">
                                <div class="col-xl-4 col-md-6">
                                    <label for="bank-{{ $index }}-name" class="bb-form-label">Nama bank</label>
                                    <input id="bank-{{ $index }}-name" type="text" name="bank_accounts[{{ $index }}][bank_name]"
                                           class="bb-input @error("bank_accounts.$index.bank_name") is-invalid @enderror"
                                           value="{{ old("bank_accounts.$index.bank_name", $account['bank_name'] ?? '') }}"
                                           list="bank-name-options" placeholder="Contoh: Bank BNI" maxlength="100">
                                    @error("bank_accounts.$index.bank_name")<small class="d-block text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-xl-4 col-md-6">
                                    <label for="bank-{{ $index }}-account" class="bb-form-label">Nomor rekening</label>
                                    <input id="bank-{{ $index }}-account" type="text" name="bank_accounts[{{ $index }}][account_number]"
                                           class="bb-input @error("bank_accounts.$index.account_number") is-invalid @enderror"
                                           value="{{ old("bank_accounts.$index.account_number", $account['account_number'] ?? '') }}"
                                           inputmode="numeric" autocomplete="off" maxlength="50">
                                    @error("bank_accounts.$index.account_number")<small class="d-block text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-xl-4 col-md-12">
                                    <label for="bank-{{ $index }}-holder" class="bb-form-label">Nama pemilik rekening</label>
                                    <input id="bank-{{ $index }}-holder" type="text" name="bank_accounts[{{ $index }}][account_name]"
                                           class="bb-input @error("bank_accounts.$index.account_name") is-invalid @enderror"
                                           value="{{ old("bank_accounts.$index.account_name", $account['account_name'] ?? '') }}"
                                           maxlength="100">
                                    @error("bank_accounts.$index.account_name")<small class="d-block text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('bank_accounts')<small class="d-block text-danger mt-2">{{ $message }}</small>@enderror
                <p class="bb-store-settings-hint"><i class="bi bi-info-circle" aria-hidden="true"></i> Isi semua kolom untuk setiap rekening yang digunakan. Maksimal 10 rekening.</p>
            </section>

            <section class="bb-store-settings-section">
                <div class="bb-store-settings-section-heading">
                    <span class="bb-store-settings-section-icon"><i class="bi bi-qr-code"></i></span>
                    <div>
                        <h3>E-Wallet dan QRIS</h3>
                        <p>Atur detail E-Wallet dan unggah gambar QRIS resmi agar pelanggan dapat memindainya.</p>
                    </div>
                </div>
                <div class="row g-3 align-items-start">
                    <div class="col-xl-3 col-md-6">
                        <label for="payment-ewallet-provider" class="bb-form-label">Nama layanan E-Wallet</label>
                        <input id="payment-ewallet-provider" type="text" name="ewallet_provider" class="bb-input"
                               value="{{ old('ewallet_provider', $paymentSettings->ewallet_provider) }}"
                               placeholder="Contoh: DANA atau QRIS" maxlength="100">
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label for="payment-ewallet-account" class="bb-form-label">Nomor akun (opsional)</label>
                        <input id="payment-ewallet-account" type="text" name="ewallet_account_number" class="bb-input"
                               value="{{ old('ewallet_account_number', $paymentSettings->ewallet_account_number) }}" maxlength="50">
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label for="payment-ewallet-holder" class="bb-form-label">Nama pemilik akun</label>
                        <input id="payment-ewallet-holder" type="text" name="ewallet_account_name" class="bb-input"
                               value="{{ old('ewallet_account_name', $paymentSettings->ewallet_account_name) }}" maxlength="100">
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <label for="payment-qris-image" class="bb-form-label">Gambar QRIS / barcode</label>
                        <input id="payment-qris-image" type="file" name="qris_image"
                               class="bb-input @error('qris_image') is-invalid @enderror"
                               accept="image/jpeg,image/png,image/webp">
                        <small class="form-text">JPG, PNG, atau WEBP, maksimal 5 MB.</small>
                        @error('qris_image')<small class="d-block text-danger">{{ $message }}</small>@enderror
                    </div>
                </div>

                @if($paymentSettings->qris_image_path)
                    <div class="bb-qris-current">
                        <img src="{{ route('media.show', ['path' => $paymentSettings->qris_image_path]) }}"
                             alt="QR pembayaran toko saat ini">
                        <label for="remove-qris-image" class="d-flex align-items-center gap-2">
                            <input id="remove-qris-image" type="checkbox" name="remove_qris_image" value="1">
                            Hapus gambar QR saat ini
                        </label>
                    </div>
                @endif
            </section>

            <div class="bb-store-settings-footer">
                <span><i class="bi bi-shield-check" aria-hidden="true"></i> Gunakan hanya rekening dan QR resmi milik toko.</span>
                <button type="submit" class="btn-bb btn-bb-primary"><i class="bi bi-check2-circle" aria-hidden="true"></i> Simpan Pengaturan</button>
            </div>
        </form>
    </div>

    <template id="bank-account-template">
        <div class="bb-bank-account-card" data-bank-account-row data-index="__INDEX__">
            <div class="bb-bank-account-card-heading">
                <span><i class="bi bi-credit-card-2-front" aria-hidden="true"></i> Rekening <span data-account-number></span></span>
                <button type="button" class="bb-remove-bank-account" data-remove-bank-account aria-label="Hapus rekening">
                    <i class="bi bi-trash3" aria-hidden="true"></i> Hapus
                </button>
            </div>
            <div class="row g-3">
                <div class="col-xl-4 col-md-6">
                    <label for="bank-__INDEX__-name" class="bb-form-label">Nama bank</label>
                    <input id="bank-__INDEX__-name" type="text" name="bank_accounts[__INDEX__][bank_name]" class="bb-input"
                           list="bank-name-options" placeholder="Contoh: Bank BNI" maxlength="100">
                </div>
                <div class="col-xl-4 col-md-6">
                    <label for="bank-__INDEX__-account" class="bb-form-label">Nomor rekening</label>
                    <input id="bank-__INDEX__-account" type="text" name="bank_accounts[__INDEX__][account_number]" class="bb-input"
                           inputmode="numeric" autocomplete="off" maxlength="50">
                </div>
                <div class="col-xl-4 col-md-12">
                    <label for="bank-__INDEX__-holder" class="bb-form-label">Nama pemilik rekening</label>
                    <input id="bank-__INDEX__-holder" type="text" name="bank_accounts[__INDEX__][account_name]" class="bb-input" maxlength="100">
                </div>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        (() => {
            const list = document.getElementById('bank-account-list');
            const template = document.getElementById('bank-account-template');
            const addButton = document.getElementById('add-bank-account');

            if (!list || !template || !addButton) return;

            let nextIndex = Math.max(-1, ...Array.from(list.querySelectorAll('[data-index]'))
                .map((row) => Number(row.dataset.index) || 0)) + 1;

            const updateNumbers = () => {
                list.querySelectorAll('[data-bank-account-row]').forEach((row, index) => {
                    row.querySelector('[data-account-number]').textContent = String(index + 1);
                    row.querySelector('[data-remove-bank-account]').setAttribute('aria-label', `Hapus rekening ${index + 1}`);
                });
            };

            addButton.addEventListener('click', () => {
                list.insertAdjacentHTML('beforeend', template.innerHTML.replace(/__INDEX__/g, String(nextIndex++)));
                updateNumbers();
                list.lastElementChild.querySelector('input').focus();
            });

            list.addEventListener('click', (event) => {
                const removeButton = event.target.closest('[data-remove-bank-account]');
                if (!removeButton) return;

                removeButton.closest('[data-bank-account-row]').remove();
                updateNumbers();
            });

            updateNumbers();
        })();
    </script>
@endpush
