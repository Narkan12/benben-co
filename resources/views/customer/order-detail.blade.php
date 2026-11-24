@extends('layouts.customer')

@section('title', 'Detail '.$order->kode_order.' — Bakery BenBen Co.')

@section('content')
@php
    $statusLabels = ['pending' => 'Menunggu konfirmasi', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Sedang diproses', 'ready' => 'Siap diambil/dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
    $statusClasses = ['pending' => 'bb-status-pending', 'confirmed' => 'bb-status-process', 'processing' => 'bb-status-process', 'ready' => 'bb-status-ready', 'completed' => 'bb-status-done', 'cancelled' => 'bb-status-cancel'];
    $paymentLabels = ['pending' => 'Menunggu verifikasi', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'];
    $paymentMethodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer bank', 'ewallet' => 'E-Wallet', 'other' => 'Lainnya'];
@endphp
        <div class="bb-dash-topbar">
            <div><a href="{{ route('customer.orders') }}" class="text-choc small"><i class="bi bi-arrow-left"></i> Kembali ke pesanan</a><h1 class="section-title mt-2" style="font-size:1.6rem;">{{ $order->kode_order }}</h1><p class="section-sub mb-0">Dipesan {{ $order->tanggal->format('d M Y, H:i') }}</p></div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="bb-status {{ $statusClasses[$order->status] ?? 'bb-status-process' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a href="{{ route('customer.order.proof', $order->id_order) }}" class="btn-bb btn-bb-primary btn-bb-sm">Lihat Bukti Pemesanan</a>
            @if($order->status === 'ready' && $order->payment_status === 'verified')
                <form method="POST" action="{{ route('customer.order.confirm-received', $order->id_order) }}"
                      onsubmit="return confirm('Konfirmasi bahwa pesanan ini sudah Anda terima?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm">Konfirmasi Pesanan Diterima</button>
                </form>
            @elseif($order->status === 'ready' && $order->payment_status !== 'verified')
                <span class="section-sub align-self-center">Konfirmasi penerimaan tersedia setelah pembayaran diterima atau diverifikasi.</span>
            @endif
        </div>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="bb-panel mb-4">
                    <h2 class="bb-panel-title">Produk Dipesan</h2>
                    @foreach($order->details as $detail)
                        <div class="bb-summary-row"><span><strong>{{ $detail->product?->nama ?? 'Produk dihapus' }}</strong><br><small>{{ $detail->qty }} × Rp{{ number_format($detail->harga, 0, ',', '.') }}</small></span><span>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span></div>
                    @endforeach
                </div>
                <div class="bb-panel">
                    <h2 class="bb-panel-title">Tahapan Pesanan</h2>
                    <ul class="bb-feature-list">
                        @forelse($order->statusHistories as $history)
                            <li><i class="bi bi-check-circle-fill"></i><span>{{ $statusLabels[$history->to_status] ?? $history->to_status }}<small class="d-block text-muted">{{ $history->changed_at?->format('d M Y, H:i') }}</small></span></li>
                        @empty
                            <li><i class="bi bi-info-circle"></i> Belum ada histori perubahan status.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="bb-summary-card mb-4">
                    <h2 class="bb-panel-title">Ringkasan Pembayaran</h2>
                    <div class="bb-summary-row"><span>Subtotal</span><span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    <div class="bb-summary-row"><span>Diskon</span><span>Rp{{ number_format($order->diskon, 0, ',', '.') }}</span></div>
                    <div class="bb-summary-row"><span>Ongkos kirim</span><span>Rp{{ number_format($order->delivery, 0, ',', '.') }}</span></div>
                    <div class="bb-summary-row total"><span>Total</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>
                </div>
                <div class="bb-panel">
                    <h2 class="bb-panel-title">Informasi Pesanan</h2>
                    <p class="section-sub mb-2"><i class="bi bi-credit-card me-1"></i> {{ $paymentMethodLabels[$order->payment_method] ?? 'Belum ditentukan' }}</p>
                    <p class="section-sub mb-2"><i class="bi bi-shield-check me-1"></i> {{ $paymentLabels[$order->payment_status] ?? 'Belum tersedia' }}</p>
                    <p class="section-sub mb-0"><i class="bi bi-truck me-1"></i> {{ $order->delivery_method === 'delivery' ? 'Diantar ke alamat' : 'Ambil di toko' }}</p>
                    @if(in_array($order->payment_method, ['transfer', 'ewallet'], true) && $order->payment_status !== 'verified' && $order->status !== 'cancelled')
                        <div class="bb-payment-info-panel mt-3">
                            <h3>Instruksi pembayaran</h3>
                            @include('customer.partials.payment-instructions', [
                                'paymentMethod' => $order->payment_method,
                                'paymentDetails' => $paymentDetails,
                            ])
                        </div>
                    @endif
                    @if($order->status === 'pending' && $order->payment_status === 'pending' && $order->payment_method !== 'cash')
                        <form method="POST" action="{{ route('customer.order.payment-proof', $order->id_order) }}" enctype="multipart/form-data" class="mt-3">
                            @csrf
                            @method('PATCH')
                            <label for="order-payment-proof" class="bb-form-label">{{ $order->payment_proof ? 'Ganti bukti pembayaran' : 'Unggah bukti pembayaran' }}</label>
                            <input id="order-payment-proof" type="file" name="payment_proof" class="bb-input" accept="image/jpeg,image/png,image/webp" required>
                            <small class="section-sub d-block mt-1">JPG, PNG, atau WEBP, maksimal 5 MB. Admin akan memeriksa bukti sebelum pesanan dikonfirmasi.</small>
                            <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm mt-2">{{ $order->payment_proof ? 'Perbarui Bukti' : 'Kirim Bukti Pembayaran' }}</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
@endsection
