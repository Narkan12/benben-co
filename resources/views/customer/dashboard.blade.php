@extends('layouts.customer')

@section('title', 'Dashboard Saya — Bakery BenBen Co.')

@section('content')
@php
    $customerName = auth()->user()->customer?->nama ?? auth()->user()->nama;
    $statusLabels = [
        'pending' => 'Menunggu',
        'confirmed' => 'Dikonfirmasi',
        'processing' => 'Diproses',
        'ready' => 'Siap diambil',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $statusClasses = [
        'pending' => 'bb-status-pending',
        'confirmed' => 'bb-status-process',
        'processing' => 'bb-status-process',
        'ready' => 'bb-status-ready',
        'completed' => 'bb-status-done',
        'cancelled' => 'bb-status-cancel',
    ];
@endphp

<div class="bb-dash-topbar">
    <div>
        <span class="eyebrow">Area pelanggan</span>
        <h1 class="section-title" style="font-size:1.6rem;">Halo, {{ $customerName }}!</h1>
        <p class="section-sub mb-0">Pantau pesanan Anda dan lanjutkan aktivitas belanja dengan lebih mudah.</p>
    </div>
    <a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm">Belanja Sekarang</a>
</div>

<div class="row g-3 mb-4">
    @foreach([
        ['bi-bag', $totalOrders, 'Total Pesanan'],
        ['bi-hourglass-split', $waitingOrders, 'Menunggu Pembayaran / Konfirmasi'],
        ['bi-clock-history', $processingOrders, 'Sedang Diproses'],
        ['bi-check2-circle', $completedOrders, 'Pesanan Selesai'],
    ] as [$icon, $value, $label])
        <div class="col-6 col-xl-3">
            <div class="bb-stat-card h-100">
                <span class="ic"><i class="bi {{ $icon }}"></i></span>
                <div>
                    <div class="val">{{ number_format($value) }}</div>
                    <div class="lbl">{{ $label }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="bb-panel h-100">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <span class="eyebrow">RINGKASAN AKUN</span>
                    <h2 class="bb-panel-title mb-1">Belanja Anda di BenBen Co.</h2>
                    <p class="section-sub mb-0">Terima kasih sudah menjadi bagian dari pelanggan kami.</p>
                </div>
                <a href="{{ route('customer.profile') }}" class="btn-bb btn-bb-outline btn-bb-sm">Lihat profil</a>
            </div>
            <div class="row g-3 mt-2">
                <div class="col-6 col-md-4">
                    <div class="bb-customer-mini-stat">
                        <span>Total dibayar</span>
                        <strong>Rp{{ number_format($totalSpent, 0, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="bb-customer-mini-stat">
                        <span>Produk dibeli</span>
                        <strong>{{ number_format($totalItems) }} item</strong>
                    </div>
                </div>
                <div class="col-6 col-md-4">
                    <div class="bb-customer-mini-stat">
                        <span>Pesanan terakhir</span>
                        <strong>{{ $lastOrder?->tanggal?->format('d M Y') ?? '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="bb-panel h-100 bb-customer-quick-panel">
            <span class="eyebrow">AKSES CEPAT</span>
            <a href="{{ route('products') }}"><i class="bi bi-shop"></i><span>Jelajahi katalog</span><i class="bi bi-arrow-up-right ms-auto"></i></a>
            <a href="{{ route('cart') }}"><i class="bi bi-basket3"></i><span>Buka keranjang</span><i class="bi bi-arrow-up-right ms-auto"></i></a>
            <a href="{{ route('customer.orders') }}"><i class="bi bi-clock-history"></i><span>Lacak semua pesanan</span><i class="bi bi-arrow-up-right ms-auto"></i></a>
        </div>
    </div>
</div>

<div class="bb-dashboard-section-heading">
    <div>
        <span>AKTIVITAS TERBARU</span>
        <h2>Pesanan terbaru</h2>
    </div>
    <a href="{{ route('customer.orders') }}">Lihat semua pesanan <i class="bi bi-arrow-right"></i></a>
</div>

<div class="bb-panel">
    <div style="overflow-x:auto;">
        <table class="bb-table-bb">
            <thead>
                <tr>
                    <th>Nomor Pesanan</th>
                    <th>Tanggal</th>
                    <th>Jumlah Item</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->kode_order }}</strong></td>
                    <td>{{ $order->tanggal?->format('d M Y, H:i') ?? '-' }}</td>
                    <td>{{ $order->details->sum('qty') }} item</td>
                    <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                    <td><span class="bb-status {{ $statusClasses[$order->status] ?? 'bb-status-process' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                    <td><a href="{{ route('customer.order.detail', $order->id_order) }}" class="text-choc fw-semibold">Lihat Detail</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="bb-customer-empty-state">
                            <i class="bi bi-bag-heart"></i>
                            <strong>Belum ada pesanan</strong>
                            <span>Pesanan pertama Anda akan muncul di sini.</span>
                            <a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm">Lihat Produk</a>
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
