@extends('layouts.admin')

@section('title', $title.' — Admin Bakery BenBen Co.')

@section('content')
    @php
        $routeBase = route('admin.reports.export.dynamic', ['type' => $reportType, 'format' => 'csv']);
        $exportQuery = http_build_query(request()->all());
        $csvHref = $exportQuery !== '' ? $routeBase.'?'.$exportQuery : $routeBase;
        $excelHref = route('admin.reports.export.dynamic', ['type' => $reportType, 'format' => 'excel']).($exportQuery !== '' ? '?'.$exportQuery : '');
        $pdfHref = route('admin.reports.export.dynamic', ['type' => $reportType, 'format' => 'pdf']).($exportQuery !== '' ? '?'.$exportQuery : '');
    @endphp

    <div class="bb-dash-topbar">
        <div>
            <h2 class="section-title" style="font-size:1.6rem;">{{ $title }}</h2>
        </div>
        <div class="bb-report-actions d-flex flex-wrap gap-2">
            <button type="button" class="btn-bb btn-bb-outline btn-bb-sm" data-print-report><i class="bi bi-printer"></i> Cetak</button>
            <a href="{{ $csvHref }}" class="btn-bb btn-bb-outline btn-bb-sm"><i class="bi bi-filetype-csv"></i> CSV</a>
            <a href="{{ $excelHref }}" class="btn-bb btn-bb-outline btn-bb-sm"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
            <a href="{{ $pdfHref }}" class="btn-bb btn-bb-primary btn-bb-sm"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        </div>
    </div>

    <form method="GET" class="bb-filter-bar">
        @if(in_array($reportType, ['transactions', 'sales', 'finance'], true))
            <div class="bb-filter-field">
                <label for="report-start-date" class="bb-form-label">Dari tanggal</label>
                <input id="report-start-date" type="date" name="start_date" value="{{ request('start_date', $startDate) }}" class="bb-input">
            </div>
            <div class="bb-filter-field">
                <label for="report-end-date" class="bb-form-label">Sampai tanggal</label>
                <input id="report-end-date" type="date" name="end_date" value="{{ request('end_date', $endDate) }}" class="bb-input">
            </div>
        @endif
        @if(in_array($reportType, ['transactions', 'products', 'stock', 'finance'], true))
            <div class="bb-filter-field">
                <label for="report-search" class="bb-form-label">{{ $searchLabel }}</label>
                <input id="report-search" type="search" name="search" value="{{ request('search') }}" class="bb-input" placeholder="{{ $searchLabel }}">
            </div>
        @endif
        @if(in_array($reportType, ['stock','products'], true))
            <div class="bb-filter-field">
                <label for="report-category-filter" class="bb-form-label">Kategori</label>
                <select id="report-category-filter" name="category" class="bb-input">
                    <option value="">Semua kategori</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id_category }}" @selected(request('category') == $category->id_category)>{{ $category->nama_kategori }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($reportType === 'transactions')
            <div class="bb-filter-field">
                <label for="report-payment-status" class="bb-form-label">Status pembayaran</label>
                <select id="report-payment-status" name="payment_status" class="bb-input">
                    <option value="">Semua status pembayaran</option>
                    @foreach(['pending' => 'Belum dibayar', 'verified' => 'Terverifikasi', 'rejected' => 'Ditolak'] as $value => $label)
                        <option value="{{ $value }}" @selected(request('payment_status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="bb-filter-field">
                <label for="report-order-status" class="bb-form-label">Status pesanan</label>
                <select id="report-order-status" name="status" class="bb-input">
                    <option value="">Semua status pesanan</option>
                    @foreach(['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Terapkan Filter</button>
        <a href="{{ route('admin.reports.'. $reportType) }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>
    </form>

    <div class="row g-3 mb-4">
        @php
            $cards = match($reportType) {
                'transactions' => [
                    ['icon' => 'bi-cash-stack', 'val' => 'Rp'.number_format((float) $summary['revenue'] ?? 0, 0, ',', '.'), 'lbl' => 'Pendapatan'],
                    ['icon' => 'bi-receipt', 'val' => number_format((int) $summary['count'] ?? 0), 'lbl' => 'Transaksi'],
                    ['icon' => 'bi-wallet2', 'val' => number_format((int) $summary['paid'] ?? 0), 'lbl' => 'Dibayar'],
                    ['icon' => 'bi-hourglass-split', 'val' => number_format((int) $summary['unpaid'] ?? 0), 'lbl' => 'Belum dibayar'],
                ],
                'sales' => [
                    ['icon' => 'bi-basket', 'val' => number_format((int) $summary['qty'] ?? 0), 'lbl' => 'Produk terjual'],
                    ['icon' => 'bi-graph-up-arrow', 'val' => 'Rp'.number_format((float) $summary['revenue'] ?? 0, 0, ',', '.'), 'lbl' => 'Pendapatan'],
                    ['icon' => 'bi-receipt', 'val' => number_format((int) $summary['count'] ?? 0), 'lbl' => 'Transaksi berhasil'],
                    ['icon' => 'bi-star', 'val' => $summary['bestSeller'] ?? 'Belum ada', 'lbl' => 'Produk terlaris'],
                ],
                'stock' => [
                    ['icon' => 'bi-box-seam', 'val' => number_format((int) $summary['count'] ?? 0), 'lbl' => 'Total produk'],
                    ['icon' => 'bi-exclamation-triangle', 'val' => number_format((int) $summary['low_stock'] ?? 0), 'lbl' => 'Stok menipis'],
                    ['icon' => 'bi-slash-circle', 'val' => number_format((int) $summary['out_of_stock'] ?? 0), 'lbl' => 'Stok habis'],
                    ['icon' => 'bi-shield-check', 'val' => number_format((int) $summary['healthy'] ?? 0), 'lbl' => 'Stok aman'],
                ],
                'products' => [
                    ['icon' => 'bi-basket3', 'val' => number_format((int) $summary['count'] ?? 0), 'lbl' => 'Produk'],
                    ['icon' => 'bi-tags', 'val' => number_format((int) $summary['categories'] ?? 0), 'lbl' => 'Kategori'],
                    ['icon' => 'bi-check-circle', 'val' => number_format((int) $summary['active'] ?? 0), 'lbl' => 'Aktif'],
                    ['icon' => 'bi-x-circle', 'val' => number_format((int) $summary['inactive'] ?? 0), 'lbl' => 'Nonaktif'],
                ],
                'finance' => [
                    ['icon' => 'bi-cash-stack', 'val' => 'Rp'.number_format((float) $summary['paid'] ?? 0, 0, ',', '.'), 'lbl' => 'Pendapatan dibayar'],
                    ['icon' => 'bi-credit-card', 'val' => 'Rp'.number_format((float) $summary['unpaid'] ?? 0, 0, ',', '.'), 'lbl' => 'Belum dibayar'],
                    ['icon' => 'bi-arrow-counterclockwise', 'val' => 'Rp'.number_format((float) $summary['cancelled'] ?? 0, 0, ',', '.'), 'lbl' => 'Dibatalkan'],
                ],
                default => []
            };
        @endphp

        @foreach($cards as $card)
            <div class="col-6 col-md-3">
                <div class="bb-stat-card">
                    <span class="ic"><i class="bi {{ $card['icon'] }}"></i></span>
                    <div><div class="val">{{ $card['val'] }}</div><div class="lbl">{{ $card['lbl'] }}</div></div>
                </div>
            </div>
        @endforeach
    </div>

    @if($reportType === 'sales')
        <div class="bb-chart-card mb-4">
            <h5 class="bb-panel-title">Produk terlaris</h5>
            @if($rows->isNotEmpty())
                <canvas id="bestSellerChart"
                    data-labels="{{ $rows->pluck('product')->values()->toJson() }}"
                    data-values="{{ $rows->pluck('qty')->values()->toJson() }}"></canvas>
            @else
                <p class="section-sub mb-0">Belum ada penjualan pada rentang tanggal ini.</p>
            @endif
        </div>
    @endif

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    @if($reportType === 'transactions')
                        <tr><th>Nomor</th><th>Tanggal</th><th>Pelanggan</th><th>Produk</th><th>Jumlah Item</th><th>Total</th><th>Metode</th><th>Status Bayar</th><th>Status Pesanan</th></tr>
                    @elseif($reportType === 'sales')
                        <tr><th>Produk</th><th>Jumlah Terjual</th><th>Subtotal</th></tr>
                    @elseif($reportType === 'stock')
                        <tr><th>Produk</th><th>Kategori</th><th>Stok</th><th>Harga</th><th>Status</th></tr>
                    @elseif($reportType === 'products')
                        <tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th></tr>
                    @elseif($reportType === 'finance')
                        <tr><th>Nomor</th><th>Tanggal</th><th>Status Bayar</th><th>Status Pesanan</th><th>Total</th></tr>
                    @endif
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @if($reportType === 'transactions')
                            @php
                                $qty = collect($row->details)->sum('qty');
                            @endphp
                            <tr>
                                <td><strong>{{ $row->kode_order }}</strong></td>
                                <td>{{ $row->tanggal?->format('d M Y H:i') }}</td>
                                <td>{{ $row->customer?->nama ?? 'Pelanggan umum' }}</td>
                                <td>@foreach($row->details as $detail)<span class="d-block">{{ $detail->product?->nama ?? 'Produk dihapus' }} × {{ $detail->qty }}</span>@endforeach</td>
                                <td>{{ $qty }}</td>
                                <td>Rp{{ number_format((float) $row->total, 0, ',', '.') }}</td>
                                <td>{{ $row->payment_method ?? '-' }}</td>
                                <td>{{ $row->payment_status ?? '-' }}</td>
                                <td>{{ $row->status }}</td>
                            </tr>
                        @elseif($reportType === 'sales')
                            <tr>
                                <td>{{ $row['product'] ?? '-' }}</td>
                                <td>{{ $row['qty'] ?? 0 }}</td>
                                <td>Rp{{ number_format((float) ($row['subtotal'] ?? 0), 0, ',', '.') }}</td>
                            </tr>
                        @elseif($reportType === 'stock')
                            <tr>
                                <td>{{ $row->nama }}</td>
                                <td>{{ $row->category?->nama_kategori ?? '-' }}</td>
                                <td>{{ $row->stok }}</td>
                                <td>Rp{{ number_format((float) $row->harga, 0, ',', '.') }}</td>
                                <td>{{ $row->stok > 5 ? 'Aman' : ($row->stok > 0 ? 'Menipis' : 'Habis') }}</td>
                            </tr>
                        @elseif($reportType === 'products')
                            <tr>
                                <td>{{ $row->nama }}</td>
                                <td>{{ $row->category?->nama_kategori ?? '-' }}</td>
                                <td>Rp{{ number_format((float) $row->harga, 0, ',', '.') }}</td>
                                <td>{{ $row->stok }}</td>
                                <td>{{ $row->status }}</td>
                            </tr>
                        @elseif($reportType === 'finance')
                            <tr>
                                <td>{{ $row->kode_order }}</td>
                                <td>{{ $row->tanggal?->format('d M Y H:i') }}</td>
                                <td>{{ $row->payment_status ?? '-' }}</td>
                                <td>{{ $row->status }}</td>
                                <td>Rp{{ number_format((float) $row->total, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                    @empty
                        <tr><td colspan="9" class="text-center text-muted">Tidak ada data yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
