@extends('layouts.customer')

@section('title', 'Bukti Pemesanan '.$order->kode_order.' — Bakery BenBen Co.')

@section('content')
@php
    $statusLabels = [
        'pending' => 'Menunggu konfirmasi',
        'confirmed' => 'Dikonfirmasi',
        'processing' => 'Sedang diproses',
        'ready' => 'Siap diambil/dikirim',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $paymentStatusLabels = [
        'pending' => 'Menunggu verifikasi',
        'verified' => 'Terverifikasi',
        'rejected' => 'Ditolak',
    ];
    $statusClasses = [
        'pending' => 'bb-status-pending',
        'confirmed' => 'bb-status-process',
        'processing' => 'bb-status-process',
        'ready' => 'bb-status-ready',
        'completed' => 'bb-status-done',
        'cancelled' => 'bb-status-cancel',
    ];
    $paymentMethodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer bank', 'ewallet' => 'E-Wallet', 'other' => 'Lainnya'];
    $deliveryMethodLabels = ['pickup' => 'Ambil di toko', 'delivery' => 'Diantar ke alamat'];
@endphp

<div class="bb-dash-topbar no-print">
    <div>
        <a href="{{ route('customer.orders') }}" class="text-choc small"><i class="bi bi-arrow-left"></i> Kembali ke riwayat</a>
        <h1 class="section-title mt-2" style="font-size:1.6rem;">Bukti Pemesanan</h1>
    </div>
    <span class="bb-status {{ $statusClasses[$order->status] ?? 'bb-status-process' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
</div>

<div class="proof-shell printable-proof">
    <div class="bb-panel proof-panel">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-bottom pb-3 mb-4 text-choc no-print">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ route('media.show', ['path' => 'images/logo.png']) }}" alt="BENBEN CO." width="52" height="52" style="object-fit:contain; border-radius:14px; background:#fff;">
                <div>
                    <strong style="font-size:1.1rem;">BENBEN CO. Bakery</strong>
                    <div class="small text-muted">Jl. Raya Bakery No. 24, Bandung</div>
                </div>
            </div>
            <div class="text-end">
                <div class="small text-muted">Nomor pesanan</div>
                <strong>{{ $order->kode_order }}</strong>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-7">
                <h2 class="bb-panel-title mb-3">Detail pesanan</h2>
                <div class="row g-3 mb-3">
                    <div class="col-sm-6">
                        <div class="small text-muted">Tanggal & waktu</div>
                        <strong>{{ $order->tanggal?->format('d M Y, H:i') ?? '-' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Nama pelanggan</div>
                        <strong>{{ $order->customer?->nama ?? 'Pelanggan' }}</strong>
                    </div>
                </div>

                <div class="bb-summary-card mb-4">
                    <h3 class="bb-panel-title" style="font-size:1rem;">Daftar produk</h3>
                    @foreach($order->details as $detail)
                        <div class="bb-summary-row">
                            <span>
                                <strong>{{ $detail->product?->nama ?? 'Produk dihapus' }}</strong><br>
                                <small>{{ $detail->qty }} × Rp{{ number_format($detail->harga, 0, ',', '.') }}</small>
                            </span>
                            <span>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                    <div class="bb-summary-row"><span>Subtotal</span><span>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</span></div>
                    <div class="bb-summary-row"><span>Diskon</span><span>- Rp{{ number_format($order->diskon, 0, ',', '.') }}</span></div>
                    <div class="bb-summary-row"><span>Ongkos kirim</span><span>Rp{{ number_format($order->delivery, 0, ',', '.') }}</span></div>
                    <div class="bb-summary-row total"><span>Total pesanan</span><span>Rp{{ number_format($order->total, 0, ',', '.') }}</span></div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="bb-panel side-panel mb-3">
                    <h3 class="bb-panel-title" style="font-size:1rem;">Informasi pembayaran</h3>
                    <p class="section-sub mb-2"><i class="bi bi-credit-card me-2"></i>{{ $paymentMethodLabels[$order->payment_method] ?? 'Belum ditentukan' }}</p>
                    <p class="section-sub mb-2"><i class="bi bi-shield-check me-2"></i>{{ $paymentStatusLabels[$order->payment_status] ?? 'Belum tersedia' }}</p>
                    @if($order->payment_proof)
                        <p class="section-sub mb-0 {{ $order->payment_status === 'verified' ? 'text-success' : 'text-muted' }}">
                            <i class="bi bi-paperclip me-2"></i>
                            {{ $order->payment_status === 'verified' ? 'Bukti pembayaran sudah dikirim dan telah diverifikasi.' : 'Bukti pembayaran sudah dikirim, tetapi status verifikasi masih menunggu.' }}
                        </p>
                    @else
                        <p class="section-sub mb-0 text-muted"><i class="bi bi-info-circle me-2"></i>Bukti pembayaran belum diunggah atau belum diverifikasi.</p>
                    @endif
                </div>

                <div class="bb-panel side-panel mb-3">
                    <h3 class="bb-panel-title" style="font-size:1rem;">Pengambilan / pengiriman</h3>
                    <p class="section-sub mb-2"><i class="bi bi-truck me-2"></i>{{ $deliveryMethodLabels[$order->delivery_method] ?? 'Belum ditentukan' }}</p>
                    <p class="section-sub mb-0"><i class="bi bi-geo-alt me-2"></i>{{ $order->customer?->alamat ?: 'Alamat belum diisi' }}</p>
                </div>

                <div class="bb-panel side-panel">
                    <h3 class="bb-panel-title" style="font-size:1rem;">Status pesanan</h3>
                    <div class="d-flex align-items-center gap-2">
                        <span class="bb-status {{ $statusClasses[$order->status] ?? 'bb-status-process' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span>
                    </div>
                    @if($order->catatan)
                        <p class="section-sub mt-3 mb-0"><i class="bi bi-chat-left-text me-2"></i>{{ $order->catatan }}</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-4 no-print">
            <button type="button" class="btn-bb btn-bb-primary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak Bukti Pemesanan</button>
            <a href="{{ route('customer.order.detail', $order->id_order) }}" class="btn-bb btn-bb-outline">Lihat Status Pesanan</a>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .proof-shell {
            max-width: 980px;
            margin: 0 auto 30px;
            padding: 0 12px;
        }

        .proof-panel {
            background: linear-gradient(180deg, #fffdfb 0%, #ffffff 100%);
            border: 1px solid #eaded0;
            border-radius: 22px;
            box-shadow: 0 20px 40px rgba(86, 53, 35, 0.08);
            padding: 22px 22px 18px;
        }

        .side-panel {
            border-radius: 16px;
            background: linear-gradient(180deg, #fffaf6 0%, #fff 100%);
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm;
            }

            html, body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }

            .bb-dash-sidebar,
            .bb-admin-topbar,
            .bb-admin-sidebar-backdrop,
            .bb-dash-topbar,
            .toast-container,
            .no-print,
            .btn-bb,
            .bb-admin-menu-toggle,
            .bb-customer-quick-panel,
            .text-choc {
                display: none !important;
            }

            .bb-dash.bb-admin-shell,
            .bb-admin-workspace,
            .bb-dash-main {
                display: block !important;
                width: 100% !important;
                max-width: 100% !important;
                min-height: 0 !important;
                padding: 0 !important;
                margin: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .printable-proof {
                display: block !important;
                visibility: visible !important;
                position: static !important;
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            .proof-panel,
            .bb-panel,
            .bb-summary-card {
                display: block !important;
                width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 18px !important;
                background: #fff !important;
                box-shadow: none !important;
                border: 1px solid #eaded0 !important;
            }

            .bb-summary-card {
                border-radius: 12px;
            }

            .bb-summary-row {
                padding: 8px 0;
                border-bottom: 1px solid #f0e3d1;
            }

            .bb-summary-row.total {
                font-weight: 700;
                border-bottom: none;
            }
        }
    </style>
@endpush
@endsection
