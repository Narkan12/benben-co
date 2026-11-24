@if($paymentMethod === 'transfer')
    @php
        $bankAccounts = collect($paymentDetails['bank_accounts'] ?? [])
            ->filter(fn ($account) => is_array($account))
            ->filter(fn ($account) => filled($account['bank_name'] ?? null)
                && filled($account['account_number'] ?? null)
                && filled($account['account_name'] ?? null))
            ->values();
        if ($bankAccounts->isEmpty()
            && filled($paymentDetails['bank_name'] ?? null)
            && filled($paymentDetails['account_number'] ?? null)
            && filled($paymentDetails['account_name'] ?? null)) {
            $bankAccounts->push([
                'bank_name' => $paymentDetails['bank_name'],
                'account_number' => $paymentDetails['account_number'],
                'account_name' => $paymentDetails['account_name'],
            ]);
        }
    @endphp

    @if($bankAccounts->isNotEmpty())
        <div class="bb-payment-bank-cards">
            @foreach($bankAccounts as $bankAccount)
                <article class="bb-payment-bank-card">
                    <span class="bb-payment-bank-badge"><i class="bi bi-bank" aria-hidden="true"></i> {{ $bankAccount['bank_name'] }}</span>
                    <dl class="bb-payment-instruction-list">
                        <div><dt>Nomor rekening</dt><dd>{{ $bankAccount['account_number'] }}</dd></div>
                        <div><dt>Atas nama</dt><dd>{{ $bankAccount['account_name'] }}</dd></div>
                    </dl>
                </article>
            @endforeach
        </div>
        <p class="section-sub mb-0">Transfer sesuai total pesanan, lalu kirim bukti pembayaran untuk diverifikasi.</p>
    @else
        <p class="bb-payment-instruction-warning mb-0">
            Informasi rekening belum lengkap. Silakan hubungi admin sebelum melakukan transfer.
        </p>
    @endif
@elseif($paymentMethod === 'ewallet')
    @php
        $walletProvider = $paymentDetails['provider'] ?? null;
        $walletNumber = $paymentDetails['account_number'] ?? null;
        $walletName = $paymentDetails['account_name'] ?? null;
        $qrisImage = $paymentDetails['qris_image_path'] ?? null;
        $walletConfigured = filled($walletProvider) || filled($walletNumber) || filled($qrisImage);
    @endphp

    @if($walletConfigured)
        @if(filled($walletProvider))
            <p class="section-sub mb-2"><strong>Layanan:</strong> {{ $walletProvider }}</p>
        @endif
        @if(filled($walletNumber))
            <p class="section-sub mb-2"><strong>Nomor akun:</strong> {{ $walletNumber }}</p>
        @endif
        @if(filled($walletName))
            <p class="section-sub mb-2"><strong>Atas nama:</strong> {{ $walletName }}</p>
        @endif
        @if(filled($qrisImage))
            <figure class="bb-payment-qris">
                <img src="{{ route('media.show', ['path' => $qrisImage]) }}" alt="QR pembayaran BENBEN CO.">
                <figcaption>Pindai QR resmi BENBEN CO., lalu bayar sesuai total pesanan.</figcaption>
            </figure>
        @endif
        <p class="section-sub mb-0">Setelah membayar, unggah bukti pembayaran agar admin dapat memverifikasinya.</p>
    @else
        <p class="bb-payment-instruction-warning mb-0">
            Informasi E-Wallet / QRIS belum tersedia. Silakan hubungi admin sebelum melakukan pembayaran.
        </p>
    @endif
@endif
