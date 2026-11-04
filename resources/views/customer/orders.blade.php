@extends('layouts.customer')
@section('title', 'Pesanan Saya — Bakery BenBen Co.')
@section('content')
<div class="bb-dash-topbar"><div><span class="eyebrow">Riwayat</span><h1 class="section-title" style="font-size:1.6rem;">Pesanan Saya</h1></div></div><div class="bb-panel"><div style="overflow-x:auto;"><table class="bb-table-bb"><thead><tr><th>Order ID</th><th>Produk</th><th>Tanggal</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($orders as $order)
    @php($statusLabels = ['pending'=>'Menunggu','confirmed'=>'Dikonfirmasi','processing'=>'Diproses','ready'=>'Siap Diambil','completed'=>'Selesai','cancelled'=>'Dibatalkan'])
    <tr><td>{{ $order->kode_order }}</td><td>{{ $order->details->map(fn ($detail) => $detail->product?->nama.' x'.$detail->qty)->filter()->join(', ') }}</td><td>{{ $order->tanggal->format('d M Y') }}</td><td>Rp{{ number_format($order->total, 0, ',', '.') }}</td><td>{{ $statusLabels[$order->status] ?? $order->status }}</td><td><a href="{{ route('customer.order.detail', $order->id_order) }}" class="text-choc">Detail</a></td></tr>
@empty <tr><td colspan="6" class="section-sub">Belum ada pesanan.</td></tr>@endforelse
</tbody></table></div>{{ $orders->links('components.pagination') }}</div>
@endsection
