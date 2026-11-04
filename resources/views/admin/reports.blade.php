@extends('layouts.admin')

@section('title', 'Laporan Penjualan — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Laporan Penjualan</h2>
        </div>
        <div class="bb-report-actions d-flex flex-wrap gap-2">
            <a href="{{ route('admin.reports.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn-bb btn-bb-outline btn-bb-sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Unduh Excel
            </a>
            <button type="button" class="btn-bb btn-bb-outline btn-bb-sm" data-save-report-pdf>
                <i class="bi bi-file-earmark-pdf"></i> Simpan PDF
            </button>
            <button type="button" class="btn-bb btn-bb-primary btn-bb-sm" data-print-report>
                <i class="bi bi-printer"></i> Cetak
            </button>
        </div>
    </div>

    <div class="bb-report-print-header" aria-hidden="true">
        <div class="bb-report-brand">BENBEN CO. <span>BAKERY</span></div>
        <h1>Laporan Penjualan</h1>
        <p>Periode {{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</p>
    </div>

    <form method="GET" class="bb-filter-bar">
        <label for="start-date" class="bb-form-label mb-0">Dari</label>
        <input id="start-date" type="date" name="start_date" value="{{ $startDate }}" class="bb-input" required>
        <label for="end-date" class="bb-form-label mb-0">Sampai</label>
        <input id="end-date" type="date" name="end_date" value="{{ $endDate }}" class="bb-input" required>
        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Tampilkan</button>
    </form>

    <div class="row g-3 mb-4">
        @foreach([
            ['icon' => 'bi-cash-stack', 'val' => 'Rp'.number_format($reportTotal, 0, ',', '.'), 'lbl' => 'Pembayaran Diterima'],
            ['icon' => 'bi-receipt', 'val' => number_format($reportTransactions), 'lbl' => 'Transaksi Terbayar'],
            ['icon' => 'bi-basket', 'val' => number_format($reportUnits), 'lbl' => 'Produk Terjual'],
            ['icon' => 'bi-graph-up-arrow', 'val' => 'Rp'.number_format($reportTransactions ? $reportTotal / $reportTransactions : 0, 0, ',', '.'), 'lbl' => 'Rata-rata Transaksi'],
        ] as $stat)
            <div class="col-6 col-md-3">
                <div class="bb-stat-card">
                    <span class="ic"><i class="bi {{ $stat['icon'] }}"></i></span>
                    <div><div class="val">{{ $stat['val'] }}</div><div class="lbl">{{ $stat['lbl'] }}</div></div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="bb-chart-card mb-4">
        <h5 class="bb-panel-title">Pembayaran Diterima per Tanggal</h5>
        <canvas id="reportChart"
            data-labels="{{ $dailySales->map(fn ($row) => \Illuminate\Support\Carbon::parse($row->sales_date)->format('d M'))->implode(',') }}"
            data-values="{{ $dailySales->pluck('sales_total')->implode(',') }}"></canvas>
    </div>

    <div class="bb-panel mb-4">
        <h5 class="bb-panel-title">Ringkasan Metode Pembayaran</h5>
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Metode Pembayaran</th><th>Transaksi</th><th>Total Diterima</th></tr></thead>
                <tbody>
                    @php($paymentMethodLabels = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet', 'other' => 'Metode lain (data lama)', 'unknown' => 'Tidak diketahui'])
                    @foreach($paymentMethodLabels as $method => $label)
                        @php($methodSummary = $paymentMethods->get($method))
                        <tr>
                            <td>{{ $label }}</td>
                            <td>{{ number_format($methodSummary->transactions ?? 0) }}</td>
                            <td>Rp{{ number_format($methodSummary->sales_total ?? 0, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bb-panel">
        <h5 class="bb-panel-title">Ringkasan Harian</h5>
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Tanggal</th><th>Transaksi Terbayar</th><th>Total Diterima</th></tr></thead>
                <tbody>
                    @forelse($dailySales as $row)
                        <tr>
                            <td>{{ \Illuminate\Support\Carbon::parse($row->sales_date)->format('d M Y') }}</td>
                            <td>{{ $row->transactions }} transaksi</td>
                            <td>Rp{{ number_format($row->sales_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center">Tidak ada pembayaran diterima pada rentang tanggal ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="bb-panel mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h5 class="bb-panel-title mb-1">Detail Transaksi Terbayar</h5>
                <p class="text-muted small mb-0">Kode transaksi, identitas customer, dan barang yang dibeli pada periode laporan.</p>
            </div>
            <span class="badge rounded-pill text-bg-light">{{ number_format($reportOrders->count()) }} transaksi</span>
        </div>
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Kode Transaksi</th><th>Tanggal</th><th>Customer</th><th>WhatsApp</th><th>Detail Barang</th><th>Pembayaran</th><th>Total</th></tr></thead>
                <tbody>
                    @forelse($reportOrders as $order)
                        <tr>
                            <td><strong>{{ $order->kode_order }}</strong></td>
                            <td>{{ $order->tanggal?->format('d M Y H:i') }}</td>
                            <td>{{ $order->customer?->nama ?? 'Pelanggan umum' }}</td>
                            <td>{{ $order->customer?->no_whatsapp ?? '-' }}</td>
                            <td>
                                @foreach($order->details as $detail)
                                    <span class="d-block">{{ $detail->product?->nama ?? 'Produk dihapus' }} <small class="text-muted">× {{ $detail->qty }} · Rp{{ number_format($detail->subtotal, 0, ',', '.') }}</small></span>
                                @endforeach
                            </td>
                            <td>{{ $paymentMethodLabels[$order->payment_method] ?? 'Tidak diketahui' }}</td>
                            <td><strong>Rp{{ number_format($order->total, 0, ',', '.') }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted">Tidak ada transaksi terbayar pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="bb-report-export-note text-muted small mt-3">
        File Excel diunduh dalam format CSV yang dapat dibuka langsung di Microsoft Excel. Untuk PDF, pilih <strong>Simpan sebagai PDF</strong> pada dialog cetak.
    </p>
@endsection

@push('styles')
    <style>
        .bb-report-print-header {
            display: none;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 14mm;
            }

            body,
            .bg-light-cream {
                background: #fff !important;
                color: #3A2118 !important;
            }

            .bb-dash-sidebar,
            .bb-admin-topbar,
            .bb-admin-sidebar-backdrop,
            .bb-dash-topbar,
            .bb-filter-bar,
            .bb-chart-card,
            .bb-report-actions,
            .bb-report-export-note,
            .toast-container {
                display: none !important;
            }

            .bb-admin-shell,
            .bb-admin-workspace,
            .bb-dash-main {
                display: block !important;
                width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                box-shadow: none !important;
            }

            .bb-report-print-header {
                display: block !important;
                margin-bottom: 22px;
                padding-bottom: 14px;
                border-bottom: 2px solid #A8754F;
                text-align: center;
            }

            .bb-report-brand {
                color: #3A2118;
                font-family: Georgia, serif;
                font-size: 15px;
                font-weight: 700;
                letter-spacing: .16em;
            }

            .bb-report-brand span {
                color: #A8754F;
                font-size: 10px;
            }

            .bb-report-print-header h1 {
                margin: 8px 0 2px;
                color: #3A2118;
                font-family: Georgia, serif;
                font-size: 24px;
            }

            .bb-report-print-header p {
                margin: 0;
                color: #65402D;
                font-size: 12px;
            }

            .bb-stat-card,
            .bb-panel {
                break-inside: avoid;
                border: 1px solid #E8D4BE !important;
                background: #FFF9F1 !important;
                box-shadow: none !important;
            }

            .bb-panel {
                margin-bottom: 14px !important;
                padding: 14px !important;
            }

            .bb-panel-title {
                color: #3A2118 !important;
            }

            .bb-table-bb th {
                background: #F5EBDD !important;
                color: #3A2118 !important;
            }

            .bb-table-bb th,
            .bb-table-bb td {
                padding: 7px 9px !important;
                border: 1px solid #E8D4BE !important;
                color: #3A2118 !important;
                font-size: 10px !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelector('[data-print-report]')?.addEventListener('click', () => window.print());
            document.querySelector('[data-save-report-pdf]')?.addEventListener('click', () => window.print());
        });
    </script>
@endpush
