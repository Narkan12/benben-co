@extends('layouts.admin')

@section('title', 'Struk '.$order->kode_order.' — BENBEN CO.')

@section('content')
@php
    $paymentMethodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet'];
    $paymentStatusLabels = ['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'];
@endphp

<div class="bb-panel no-print" style="max-width: 980px; margin: 22px auto 0; padding: 20px 24px; border-radius: 18px;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
        <div>
            <span class="eyebrow">Transaksi tersimpan</span>
            <h1 class="section-title mb-0" style="font-size:1.5rem;">{{ $order->kode_order }}</h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn-bb btn-bb-primary" onclick="window.print()"><i class="bi bi-printer me-2"></i>Cetak Struk</button>
            <a href="{{ route('admin.pos') }}" class="btn-bb btn-bb-outline">Kembali ke POS</a>
        </div>
    </div>
</div>

<div class="receipt-shell printable-receipt">
    <div class="receipt-card">
        <div class="receipt-header">
            <div class="receipt-kicker">Struk Transaksi</div>
            <img src="{{ route('media.show', ['path' => 'images/logo.png']) }}" alt="BENBEN CO." width="56" height="56" class="receipt-logo">
            <div class="receipt-brand">BENBEN CO. BAKERY</div>
            <div class="receipt-meta">Jl. Raya Bakery No. 24, Bandung</div>
            <div class="receipt-meta">Telp: 0813-9494-009</div>
        </div>

        <div class="receipt-info">
            <div class="receipt-row"><span>Invoice</span><strong>{{ $order->kode_order }}</strong></div>
            <div class="receipt-row"><span>Tanggal</span><strong>{{ $order->tanggal?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="receipt-row"><span>Kasir</span><strong>{{ auth()->user()->nama }}</strong></div>
        </div>

        <div class="receipt-items">
            @foreach($order->details as $detail)
                <div class="receipt-item">
                    <div class="item-copy">
                        <strong>{{ $detail->product?->nama ?? 'Produk dihapus' }}</strong>
                        <span>{{ $detail->qty }} × Rp{{ number_format($detail->harga, 0, ',', '.') }}</span>
                    </div>
                    <strong>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>

        <div class="receipt-summary">
            <div class="receipt-row"><span>Subtotal</span><strong>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
            <div class="receipt-row"><span>Diskon</span><strong>- Rp{{ number_format($order->diskon, 0, ',', '.') }}</strong></div>
            <div class="receipt-row"><span>Metode</span><strong>{{ $paymentMethodLabels[$order->payment_method] ?? 'Lainnya' }}</strong></div>
            <div class="receipt-row"><span>Status</span><strong>{{ $paymentStatusLabels[$order->payment_status] ?? $order->payment_status }}</strong></div>
            <div class="receipt-row receipt-total"><span>Total</span><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></div>
            @if($order->payment_method === 'cash')
                <div class="receipt-row"><span>Bayar</span><strong>Rp{{ number_format((float) ($order->cash_received ?? $order->total), 0, ',', '.') }}</strong></div>
                <div class="receipt-row"><span>Kembali</span><strong>Rp{{ number_format((float) ($order->cash_change ?? 0), 0, ',', '.') }}</strong></div>
            @endif
        </div>

        <div class="receipt-footer">
            Terima kasih atas kunjungan Anda.<br>
            Semoga hari Anda menyenangkan.
        </div>
    </div>
</div>

@push('styles')
    <style>
        .receipt-shell {
            max-width: 430px;
            margin: 22px auto 40px;
            padding: 0 12px;
        }

        .receipt-card {
            background: linear-gradient(180deg, #fffdf9 0%, #fff 100%);
            border: 1px solid #ead7c2;
            border-radius: 22px;
            box-shadow: 0 18px 40px rgba(117, 74, 42, 0.08);
            padding: 20px 18px 18px;
            color: #352117;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #d7b18a;
            padding-bottom: 14px;
            margin-bottom: 14px;
        }

        .receipt-kicker {
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.18em;
            text-transform: uppercase;
            color: #6f4735;
        }

        .receipt-logo {
            object-fit: contain;
            margin: 8px auto 10px;
            display: block;
        }

        .receipt-brand {
            font-size: 0.88rem;
            letter-spacing: 0.12em;
            font-weight: 800;
            color: #3a2118;
        }

        .receipt-meta {
            font-size: 0.72rem;
            color: #6f4735;
            margin-top: 2px;
        }

        .receipt-info,
        .receipt-summary {
            padding-bottom: 12px;
            border-bottom: 1px dashed #d7b18a;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
            font-size: 0.8rem;
        }

        .receipt-row span {
            color: #6d4b3b;
        }

        .receipt-row strong {
            color: #371f18;
        }

        .receipt-items {
            border-bottom: 1px dashed #d7b18a;
            padding: 12px 0;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
            align-items: flex-start;
        }

        .receipt-item:last-child {
            margin-bottom: 0;
        }

        .item-copy {
            max-width: 62%;
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .item-copy strong {
            font-size: 0.8rem;
            color: #2d1c17;
        }

        .item-copy span {
            font-size: 0.68rem;
            color: #705341;
        }

        .receipt-summary {
            padding-top: 12px;
        }

        .receipt-total {
            font-size: 0.92rem;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px dashed #d7b18a;
        }

        .receipt-footer {
            text-align: center;
            margin-top: 12px;
            font-size: 0.7rem;
            color: #6f4735;
            line-height: 1.6;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 4mm;
            }

            html, body {
                background: #fff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }

            .no-print,
            .bb-dash-sidebar,
            .bb-admin-topbar,
            .bb-admin-sidebar-backdrop,
            .toast-container,
            .btn-bb,
            .bb-admin-menu-toggle {
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

            .printable-receipt {
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

            .receipt-card {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                background: #fff !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
@endpush
@endsection
