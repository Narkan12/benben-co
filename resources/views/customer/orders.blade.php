@extends('layouts.customer')
@section('title', 'Pesanan Saya — Bakery BenBen Co.')
@section('content')
@php
    $statusLabels = ['pending' => 'Menunggu Pembayaran', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
    $statusClasses = ['pending' => 'bb-status-pending', 'confirmed' => 'bb-status-process', 'processing' => 'bb-status-process', 'ready' => 'bb-status-ready', 'completed' => 'bb-status-done', 'cancelled' => 'bb-status-cancel'];
@endphp
<div class="bb-dash-topbar"><div><span class="eyebrow">Riwayat</span><h1 class="section-title" style="font-size:1.6rem;">Pesanan Saya</h1><p class="section-sub mb-0">Lihat status dan detail semua pesanan Anda.</p></div><a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm"><i class="bi bi-shop"></i> Jelajahi produk</a></div><div class="bb-panel"><div style="overflow-x:auto;"><table class="bb-table-bb"><thead><tr><th>Order ID</th><th>Produk</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($orders as $order)
    <tr><td><strong>{{ $order->kode_order }}</strong></td><td>{{ $order->details->map(fn ($detail) => $detail->product?->nama.' × '.$detail->qty)->filter()->join(', ') }}</td><td>{{ $order->tanggal->format('d M Y') }}</td><td>Rp{{ number_format($order->total, 0, ',', '.') }}</td><td><span class="bb-status {{ $statusClasses[$order->status] ?? 'bb-status-pending' }}">{{ $statusLabels[$order->status] ?? ucfirst($order->status) }}</span></td><td><a href="{{ route('customer.order.detail', $order->id_order) }}" class="text-choc fw-semibold">Lihat detail <i class="bi bi-arrow-right"></i></a></td></tr>
@empty <tr><td colspan="6"><div class="bb-customer-empty-state"><i class="bi bi-bag-heart"></i><strong>Belum ada pesanan</strong><span>Pesanan Anda akan muncul di halaman ini.</span><a href="{{ route('products') }}" class="btn-bb btn-bb-primary btn-bb-sm">Lihat Produk</a></div></td></tr>@endforelse
</tbody></table></div>{{ $orders->links('components.pagination') }}</div>
@endsection
