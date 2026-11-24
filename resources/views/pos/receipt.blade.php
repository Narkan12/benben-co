@extends('layouts.receipt')

@section('title', 'BENBEN CO.')

@section('content')
@php
    $paymentMethodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet'];
    $paymentStatusLabels = ['pending' => 'Menunggu', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'];
@endphp

<div class="receipt-workspace">
    <aside class="receipt-controls no-print" aria-label="Pengaturan pencetakan struk">
        <span class="receipt-control-eyebrow">Transaksi tersimpan</span>
        <h1>{{ $order->kode_order }}</h1>

        <label for="receipt-paper-width" class="bb-form-label">Ukuran kertas struk</label>
        <select id="receipt-paper-width" class="bb-input" aria-label="Lebar kertas printer">
            <option value="80">Printer thermal 80 mm</option>
            <option value="58">Printer thermal 58 mm</option>
        </select>

        <button type="button" class="btn-bb btn-bb-primary" data-print-receipt>
            <i class="bi bi-printer me-2"></i>Cetak Struk
        </button>
        <a href="{{ route('admin.pos') }}" class="btn-bb btn-bb-outline">Kembali ke POS</a>

        <div class="receipt-print-note">
            <strong>Pengaturan Windows Print</strong>
            <span>Matikan <b>Headers and footers</b> pada dialog cetak agar tanggal, jam, URL, dan nomor halaman browser tidak ikut tercetak.</span>
        </div>
    </aside>

    <div class="receipt-preview" aria-label="Pratinjau struk">
        <div class="receipt-shell printable-receipt">
            <div class="receipt-card">
        <div class="receipt-header">
            <div class="receipt-brand">BENBEN CO.</div>
            <div class="receipt-meta">Komp. Sinergi Antapani, Jl. Nyaman No.33B</div>
            <div class="receipt-meta">Antapani Tengah, Kec. Antapani, Kota Bandung 40291</div>
            @if($whatsappAdminNumber)
                <div class="receipt-meta">Telp: +{{ $whatsappAdminNumber }}</div>
            @endif
        </div>

        <div class="receipt-info">
            <div class="receipt-row"><span>No. transaksi</span><strong>{{ $order->kode_order }}</strong></div>
            <div class="receipt-row"><span>Tanggal</span><strong>{{ $order->tanggal?->format('d/m/Y H:i') ?? '-' }}</strong></div>
            <div class="receipt-row"><span>Kasir</span><strong>{{ auth()->user()->nama }}</strong></div>
        </div>

        <div class="receipt-items">
            @foreach($order->details as $detail)
                <div class="receipt-item">
                    <div class="item-copy">
                        <strong>{{ $detail->product?->nama ?? 'Produk dihapus' }}</strong>
                        <span>{{ $detail->qty }} × Rp{{ number_format($detail->harga, 0, ',', '.') }} / unit</span>
                    </div>
                    <strong>Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</strong>
                </div>
            @endforeach
        </div>

        <div class="receipt-summary">
            <div class="receipt-row"><span>Subtotal</span><strong>Rp{{ number_format($order->subtotal, 0, ',', '.') }}</strong></div>
            <div class="receipt-row"><span>Diskon</span><strong>- Rp{{ number_format($order->diskon, 0, ',', '.') }}</strong></div>
            <div class="receipt-row"><span>Metode</span><strong>{{ $paymentMethodLabels[$order->payment_method] ?? 'Lainnya' }}</strong></div>
            <div class="receipt-row receipt-total"><span>Total pembayaran</span><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></div>
            @if($order->payment_method === 'cash')
                <div class="receipt-row"><span>Uang diterima</span><strong>Rp{{ number_format((float) ($order->cash_received ?? $order->total), 0, ',', '.') }}</strong></div>
                <div class="receipt-row"><span>Uang kembalian</span><strong>Rp{{ number_format((float) ($order->cash_change ?? 0), 0, ',', '.') }}</strong></div>
            @endif
        </div>

        <div class="receipt-footer">
            Terima kasih atas kunjungan Anda.<br>
            Semoga hari Anda menyenangkan.
        </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
    <style>
        .receipt-workspace {
            max-width: 980px;
            margin: 22px auto 40px;
            display: grid;
            grid-template-columns: 280px minmax(300px, 1fr);
            align-items: start;
            gap: 24px;
        }

        .receipt-controls {
            box-sizing: border-box;
            width: 100%;
            padding: 20px;
            background: #fffaf2;
            border: 1px solid #e7d2b9;
            border-radius: 18px;
            box-shadow: 0 12px 28px rgba(117, 74, 42, 0.08);
        }

        .receipt-controls .bb-form-label,
        .receipt-controls .bb-input,
        .receipt-controls .btn-bb {
            width: 100%;
        }

        .receipt-controls .bb-input {
            margin: 8px 0 12px;
        }

        .receipt-controls .btn-bb {
            display: flex;
            justify-content: center;
            margin-top: 8px;
        }

        .receipt-control-eyebrow {
            display: block;
            margin-bottom: 6px;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #9a5d3c;
        }

        .receipt-controls h1 {
            margin: 0 0 16px;
            color: #4b3023;
            font-size: 1.35rem;
            overflow-wrap: anywhere;
        }

        .receipt-print-note {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-top: 16px;
            padding: 12px;
            border-left: 4px solid #d79b46;
            border-radius: 8px;
            background: #fff1d9;
            color: #704936;
            font-size: 0.76rem;
            line-height: 1.45;
        }

        .receipt-preview {
            min-width: 0;
            display: flex;
            justify-content: center;
            padding: 0 12px;
            border-radius: 18px;
            background: #fbf6ec;
        }

        .receipt-shell {
            width: min(100%, 430px);
            max-width: 430px;
            margin: 20px auto;
            padding: 0;
        }

        .receipt-card {
            box-sizing: border-box;
            padding: 18px 16px 16px;
            background: #fffdf8;
            border: 1px solid #dcbf9c;
            border-top: 7px solid #79523e;
            border-radius: 14px;
            box-shadow: 0 16px 36px rgba(117, 74, 42, 0.12);
            color: #3a271d;
        }

        .receipt-header {
            margin-bottom: 12px;
            padding: 2px 4px 12px;
            text-align: center;
            border-bottom: 2px solid #d79b46;
        }

        .receipt-brand {
            color: #5c3828;
            font-size: 1.05rem;
            font-weight: 900;
            letter-spacing: 0.08em;
        }

        .receipt-meta {
            margin-top: 2px;
            color: #80604d;
            font-size: 0.72rem;
        }

        .receipt-info,
        .receipt-summary,
        .receipt-items {
            border-bottom: 1px solid #dfc49f;
        }

        .receipt-info {
            padding: 10px 8px;
            border-radius: 8px;
            background: #fff7e8;
        }

        .receipt-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(90px, 55%);
            gap: 10px;
            align-items: baseline;
            margin: 0 0 7px;
            font-size: 0.78rem;
        }

        .receipt-row:last-child {
            margin-bottom: 0;
        }

        .receipt-row span {
            color: #7a5947;
        }

        .receipt-row strong {
            min-width: 0;
            color: #41291f;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .receipt-items {
            padding: 12px 8px;
        }

        .receipt-item {
            display: grid;
            grid-template-columns: minmax(0, 58%) minmax(90px, 42%);
            gap: 8px;
            align-items: start;
            margin-bottom: 10px;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .receipt-item:last-child {
            margin-bottom: 0;
        }

        .receipt-item > strong {
            min-width: 0;
            color: #3f281d;
            text-align: right;
            overflow-wrap: anywhere;
        }

        .item-copy {
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
            overflow-wrap: anywhere;
        }

        .item-copy strong {
            color: #5a3828;
            font-size: 0.78rem;
        }

        .item-copy span {
            color: #9a6b48;
            font-size: 0.68rem;
        }

        .receipt-summary {
            padding: 12px 8px 10px;
        }

        .receipt-total {
            grid-template-columns: minmax(0, 1fr) minmax(110px, 55%);
            align-items: center;
            margin: 9px -8px 7px;
            padding: 9px 8px;
            border: 2px solid #d79b46;
            border-radius: 8px;
            background: #fff0ce;
            font-size: 0.9rem;
        }

        .receipt-total span,
        .receipt-total strong {
            color: #6d3d27;
            font-weight: 900;
        }

        .receipt-footer {
            margin-top: 10px;
            padding: 9px 8px 2px;
            border-top: 1px dashed #d79b46;
            color: #805940;
            text-align: center;
            font-size: 0.7rem;
            line-height: 1.55;
        }

        @media (max-width: 700px) {
            .receipt-workspace {
                grid-template-columns: 1fr;
                padding: 0 12px;
            }

            .receipt-controls {
                max-width: none;
            }

            .receipt-preview {
                padding: 0;
            }
        }

        @page {
            size: 80mm 180mm;
            margin: 2mm 1mm;
        }

        @media print {
            html, body {
                width: var(--receipt-paper-width, 80mm) !important;
                max-width: var(--receipt-paper-width, 80mm) !important;
                height: auto !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body * {
                visibility: hidden !important;
            }

            .printable-receipt,
            .printable-receipt * {
                visibility: visible !important;
            }

            .no-print,
            .receipt-workspace > .receipt-controls,
            .bb-navbar,
            .bb-footer,
            .bb-dash-sidebar,
            .bb-admin-topbar,
            .bb-admin-sidebar-backdrop,
            .toast-container,
            .btn-bb,
            .bb-admin-menu-toggle {
                display: none !important;
            }

            .receipt-preview,
            .receipt-workspace {
                display: block !important;
                width: var(--receipt-paper-width, 80mm) !important;
                max-width: var(--receipt-paper-width, 80mm) !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
                background: #fff !important;
            }

            .printable-receipt {
                position: absolute !important;
                top: 0 !important;
                left: 0 !important;
                display: block !important;
                width: var(--receipt-paper-width, 80mm) !important;
                max-width: var(--receipt-paper-width, 80mm) !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 9pt !important;
                line-height: 1.35 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt-card {
                width: 100% !important;
                max-height: 176mm !important;
                margin: 0 !important;
                padding: var(--receipt-card-padding, 2mm) !important;
                border: 0 !important;
                border-top: 1.5mm solid #79523e !important;
                border-radius: 0 !important;
                background: #fffdf8 !important;
                box-shadow: none !important;
                overflow: hidden !important;
            }

            .receipt-header,
            .receipt-info,
            .receipt-items,
            .receipt-item,
            .receipt-summary,
            .receipt-footer,
            .receipt-total {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .receipt-header {
                border-bottom-color: #d79b46 !important;
            }

            .receipt-kicker {
                background: #f5dfbd !important;
                color: #7a4a32 !important;
            }

            .receipt-info {
                background: #fff7e8 !important;
            }

            .receipt-total {
                border: 1px solid #d79b46 !important;
                background: #fff0ce !important;
            }

            .receipt-item {
                grid-template-columns: minmax(0, 58%) minmax(90px, 42%);
                gap: 2mm;
            }

            .item-copy {
                max-width: none;
                overflow-wrap: anywhere;
            }

            .receipt-row strong,
            .receipt-item > strong {
                overflow-wrap: anywhere;
            }

            .receipt-card * {
                font-weight: 700 !important;
                text-rendering: geometricPrecision;
            }

            .receipt-brand {
                font-size: 11pt !important;
            }

            .receipt-header {
                margin-bottom: 1mm !important;
                padding-bottom: 1mm !important;
            }

            .receipt-info,
            .receipt-items,
            .receipt-summary {
                padding-top: 1.5mm !important;
                padding-bottom: 1.5mm !important;
            }

            .receipt-item,
            .receipt-row {
                margin-bottom: 1.5mm !important;
            }

            .receipt-footer {
                margin-top: 1mm !important;
                padding-top: 1mm !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('js/receipt.js') }}"></script>
@endpush
@endsection
