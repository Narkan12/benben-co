@extends('layouts.admin')

@section('title', 'Dashboard Admin — Bakery BenBen Co.')

@section('content')

    <section class="bb-dashboard-welcome">
        <div class="bb-dashboard-welcome-copy">
            <span class="bb-dashboard-eyebrow"><i class="bi bi-sunrise"></i> BENBEN CO. · RUANG KERJA</span>
            <h1>Halo, {{ auth()->user()->nama }}.</h1>
            <p>Ringkasan aktivitas dan kondisi usaha Anda hari ini.</p>
            <div class="bb-dashboard-actions">
                <a href="{{ route('admin.pos') }}" class="bb-dashboard-primary-action"><i class="bi bi-shop-window"></i> Buka kasir POS</a>
                <a href="{{ route('admin.products') }}" class="bb-dashboard-secondary-action">Kelola produk <i class="bi bi-arrow-up-right"></i></a>
            </div>
        </div>
    </section>

    @if(auth()->user()->role === 'admin')
        <section class="bb-panel mb-4" data-order-notifications
                 data-poll-url="{{ route('admin.order-notifications') }}"
                 data-seen-url="{{ route('admin.order-notifications.seen') }}"
                 data-enabled="{{ $orderNotificationsEnabled ? 'true' : 'false' }}">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h2 class="bb-panel-title mb-1">Pesanan online baru</h2>
                    <p class="mb-0 text-muted">
                        <span data-pending-order-count>{{ $pendingOnlineOrders }}</span> pesanan menunggu konfirmasi
                        <span class="ms-1" data-order-notification-status aria-live="polite">
                            {{ $orderNotificationsEnabled ? 'Pemeriksaan otomatis aktif.' : 'Notifikasi sedang dimatikan.' }}
                        </span>
                    </p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <form method="POST" action="{{ route('admin.order-notifications.toggle') }}" class="m-0">
                        @csrf
                        <input type="hidden" name="enabled" value="0">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" role="switch" id="order-notifications-enabled"
                                   name="enabled" value="1" @checked($orderNotificationsEnabled)
                                   onchange="this.form.requestSubmit()">
                            <label class="form-check-label" for="order-notifications-enabled">Aktifkan notifikasi</label>
                        </div>
                    </form>
                    <a href="{{ route('admin.orders') }}" class="btn-bb btn-bb-outline">Lihat pesanan</a>
                </div>
            </div>
        </section>
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['icon' => 'bi-basket', 'val' => number_format($activeProducts), 'lbl' => 'Produk Aktif', 'url' => route('admin.products')],
            ['icon' => 'bi-receipt', 'val' => number_format($totalTransactions), 'lbl' => 'Total Pesanan', 'url' => route('admin.orders')],
            ['icon' => 'bi-hourglass-split', 'val' => number_format($pendingOrders), 'lbl' => 'Pesanan Berjalan', 'url' => route('admin.orders')],
            ['icon' => 'bi-cash-stack', 'val' => 'Rp'.number_format($periodSales, 0, ',', '.'), 'lbl' => 'Pendapatan Periode', 'url' => route('admin.reports')],
            ['icon' => 'bi-check2-circle', 'val' => number_format($completedOrders), 'lbl' => 'Pesanan Selesai', 'url' => route('admin.orders')],
            ['icon' => 'bi-wallet2', 'val' => 'Rp'.number_format($totalRevenue, 0, ',', '.'), 'lbl' => 'Total Pendapatan', 'url' => route('admin.reports')],
            ['icon' => 'bi-credit-card', 'val' => number_format($pendingPaymentOrders), 'lbl' => 'Menunggu Pembayaran', 'url' => route('admin.payments')],
            ['icon' => 'bi-exclamation-triangle', 'val' => number_format($outOfStock->count()), 'lbl' => 'Stok Rendah (≤ 5)', 'url' => route('admin.inventory')],
        ] as $stat)
            <div class="col-6 col-md-3">
                <a href="{{ $stat['url'] }}" class="bb-stat-card bb-dashboard-stat">
                    <span class="ic"><i class="bi {{ $stat['icon'] }}"></i></span>
                    <div>
                        <div class="val">{{ $stat['val'] }}</div>
                        <div class="lbl">{{ $stat['lbl'] }}</div>
                        <span class="bb-dashboard-stat-hint">Buka ringkasan <i class="bi bi-arrow-up-right" aria-hidden="true"></i></span>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <div class="bb-dashboard-section-heading">
        <div><span>GAMBARAN USAHA</span><h2>Performa penjualan</h2></div>
        <form method="GET" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="bb-filter-field">
                <label for="sales-period" class="bb-form-label">Periode penjualan</label>
                <select id="sales-period" name="period" class="bb-input" style="width:auto;padding:7px 12px;" onchange="this.form.submit()">
                <option value="today" @selected($period === 'today')>Hari ini</option>
                <option value="7d" @selected($period === '7d')>7 hari terakhir</option>
                <option value="month" @selected($period === 'month')>Bulan ini</option>
                <option value="custom" @selected($period === 'custom')>Rentang khusus</option>
                </select>
            </div>
            @if($period === 'custom')
                <div class="bb-filter-field">
                    <label for="sales-start-date" class="bb-form-label">Dari tanggal</label>
                    <input id="sales-start-date" type="date" name="start_date" value="{{ $periodStart->toDateString() }}" class="bb-input" style="width:auto;padding:7px 12px;">
                </div>
                <div class="bb-filter-field">
                    <label for="sales-end-date" class="bb-form-label">Sampai tanggal</label>
                    <input id="sales-end-date" type="date" name="end_date" value="{{ $periodEnd->toDateString() }}" class="bb-input" style="width:auto;padding:7px 12px;">
                </div>
                <button class="btn-bb btn-bb-outline btn-bb-sm">Terapkan</button>
            @endif
        </form>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="bb-chart-card h-100">
                <h5 class="bb-panel-title">Penjualan 7 Hari Terakhir</h5>
                <canvas id="salesChart"
                        data-labels="{{ $salesChartLabels }}"
                        data-values="{{ $salesChartValues }}"></canvas>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="bb-chart-card h-100">
                <h5 class="bb-panel-title">Produk Terlaris</h5>
                <canvas id="topProductsChart"
                        data-labels="{{ $topProductLabels }}"
                        data-values="{{ $topProductValues }}"></canvas>
            </div>
        </div>
    </div>

    <div class="bb-dashboard-section-heading">
        <div><span>MASUKAN PELANGGAN</span><h2>Rating &amp; ulasan</h2></div>
        <a href="{{ route('products') }}">Lihat katalog <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-lg-4">
            <div class="bb-panel bb-dashboard-rating-summary h-100">
                <span class="eyebrow">RATING KESELURUHAN</span>
                <strong>{{ number_format($ratingAverage, 1) }}</strong>
                <span class="bb-review-stars" aria-label="Rating rata-rata {{ number_format($ratingAverage, 1) }} dari 5">
                    @for($star = 1; $star <= 5; $star++)
                        <span>{{ $star <= round($ratingAverage) ? '★' : '☆' }}</span>
                    @endfor
                </span>
                <p class="section-sub mb-0">Dari {{ number_format($ratingCount) }} ulasan pelanggan</p>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="bb-panel h-100">
                <h3 class="bb-panel-title">Ulasan Terbaru</h3>
                @forelse($recentRatings as $review)
                    <article class="bb-dashboard-review">
                        <div>
                            <strong>{{ $review->nama_customer }}</strong>
                            <span>{{ $review->product?->nama ?? 'Produk dihapus' }}</span>
                        </div>
                        <span class="bb-review-stars" aria-label="Rating {{ $review->rating }} dari 5">
                            @for($star = 1; $star <= 5; $star++)
                                <span>{{ $star <= $review->rating ? '★' : '☆' }}</span>
                            @endfor
                        </span>
                        <p>{{ $review->komentar ?: 'Tidak ada komentar.' }}</p>
                    </article>
                @empty
                    <p class="section-sub mb-0">Belum ada ulasan pelanggan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bb-dashboard-section-heading bb-dashboard-section-heading-orders">
        <div><span>OPERASIONAL</span><h2>Pesanan &amp; persediaan</h2></div>
        <a href="{{ route('admin.orders') }}">Lihat semua pesanan <i class="bi bi-arrow-right"></i></a>
    </div>
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="bb-panel">
                <h5 class="bb-panel-title">Pesanan Terbaru</h5>
                <div style="overflow-x:auto;">
                    <table class="bb-table-bb">
                        <thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status Pesanan</th><th>Pembayaran</th></tr></thead>
                        <tbody>
                            @forelse($latestOrders as $o)
                                @php
                                    $statusLabels = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                                    $statusClasses = ['pending' => 'bb-status-pending', 'confirmed' => 'bb-status-process', 'processing' => 'bb-status-process', 'ready' => 'bb-status-ready', 'completed' => 'bb-status-done', 'cancelled' => 'bb-status-cancel'];
                                    $paymentLabels = ['pending' => 'Menunggu', 'verified' => 'Diterima', 'rejected' => 'Ditolak'];
                                    $paymentClasses = ['pending' => 'bb-status-pending', 'verified' => 'bb-status-done', 'rejected' => 'bb-status-cancel'];
                                @endphp
                                <tr>
                                    <td>{{ $o->kode_order }}</td>
                                    <td>{{ $o->customer?->nama ?? 'Pelanggan dihapus' }}</td>
                                    <td>Rp{{ number_format($o->total, 0, ',', '.') }}</td>
                                    <td><span class="bb-status {{ $statusClasses[$o->status] ?? 'bb-status-pending' }}">{{ $statusLabels[$o->status] ?? ucfirst($o->status) }}</span></td>
                                    <td><span class="bb-status {{ $paymentClasses[$o->payment_status] ?? 'bb-status-pending' }}">{{ $paymentLabels[$o->payment_status] ?? ucfirst($o->payment_status) }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">Belum ada pesanan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="bb-panel">
                <h5 class="bb-panel-title">Stok Habis &amp; Rendah (maks. 5)</h5>
                <div style="overflow-x:auto;">
                    <table class="bb-table-bb">
                        <thead><tr><th>Produk</th><th>Stok</th></tr></thead>
                        <tbody>
                            @forelse($outOfStock as $item)
                                <tr>
                                    <td>{{ $item->nama }}</td>
                                    <td>
                                        <span class="bb-lowstock">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            {{ $item->stok === 0 ? 'Stok habis' : 'Stok rendah' }} ({{ number_format($item->stok, 0, ',', '.') }} pcs)
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center">Semua produk memiliki stok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@endsection
