@extends('layouts.customer')

@section('title', 'Pesanan Saya — Bakery BenBen Co.')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Menunggu Pembayaran',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'ready' => 'Siap Diambil',
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
            <h1 class="section-title" style="font-size:1.6rem;">Pesanan Saya</h1>
            <p class="section-sub mb-0">Lihat status dan detail semua pesanan Anda.</p>
        </div>
        <a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm">
            <i class="bi bi-shop" aria-hidden="true"></i> Jelajahi produk
        </a>
    </div>

    @if($orders->isEmpty())
        <section class="bb-panel bb-customer-empty-state bb-customer-orders-empty-state" aria-labelledby="orders-empty-title">
            <div class="bb-customer-orders-empty-icon" aria-hidden="true">
                <i class="bi bi-bag-heart"></i>
            </div>
            <h2 id="orders-empty-title" class="section-title">Belum ada pesanan</h2>
            <p class="section-sub">Setelah Anda memesan, informasi pesanan dan statusnya akan tersimpan di halaman ini.</p>

            <div class="bb-customer-order-start-steps">
                <article>
                    <span>01</span>
                    <strong>Pilih produk</strong>
                    <p>Jelajahi aneka roti, kue, dan bakery favorit Anda.</p>
                </article>
                <article>
                    <span>02</span>
                    <strong>Atur pesanan</strong>
                    <p>Masukkan produk ke keranjang, lalu lanjutkan ke checkout.</p>
                </article>
                <article>
                    <span>03</span>
                    <strong>Pantau status</strong>
                    <p>Nomor pesanan, pembayaran, dan prosesnya akan tampil di sini.</p>
                </article>
            </div>

            <a href="{{ route('products') }}" class="btn-bb btn-bb-primary">
                <i class="bi bi-shop" aria-hidden="true"></i> Mulai belanja
            </a>
        </section>
    @else
        <div class="bb-panel">
            <div style="overflow-x:auto;">
                <table class="bb-table-bb">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Produk</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $order)
                            <tr>
                                <td><strong>{{ $order->kode_order }}</strong></td>
                                <td>{{ $order->details->map(fn ($detail) => $detail->product?->nama.' × '.$detail->qty)->filter()->join(', ') }}</td>
                                <td>{{ $order->tanggal->format('d M Y') }}</td>
                                <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                                <td>
                                    <span class="bb-status {{ $statusClasses[$order->status] ?? 'bb-status-pending' }}">
                                        {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td>
                                    <a href="{{ route('customer.order.detail', $order->id_order) }}" class="text-choc fw-semibold">
                                        Lihat detail <i class="bi bi-arrow-right" aria-hidden="true"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $orders->links('components.pagination') }}
        </div>
    @endif
@endsection
