@extends('layouts.admin')

@section('title', 'Pembayaran — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Konfirmasi Pembayaran</h2>
        </div>
    </div>
    <p class="section-sub mb-3">Periksa bukti transfer atau E-Wallet sebelum mengonfirmasi. Pesanan baru dapat dikonfirmasi setelah pembayarannya diverifikasi.</p>
    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Order ID</th><th>Customer</th><th>Metode</th><th>Total</th><th>Bukti Pembayaran</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $isPending = $order->status === 'pending';
                            $statusLabels = ['pending' => 'Menunggu pembayaran', 'verified' => 'Terverifikasi'];
                            $statusClasses = ['pending' => 'bb-status-pending', 'verified' => 'bb-status-done'];
                        @endphp
                        <tr>
                            <td>{{ $order->kode_order }}</td>
                            <td>{{ $order->customer?->nama ?? 'Pelanggan dihapus' }}</td>
                            <td>{{ ['transfer' => 'Transfer', 'ewallet' => 'E-Wallet', 'other' => 'Metode lain (data lama)'][$order->payment_method] ?? 'Tidak diketahui' }}</td>
                            <td>Rp{{ number_format($order->total, 0, ',', '.') }}</td>
                            <td>
                                @if($order->payment_proof)
                                    <a href="{{ route('admin.payments.proof', ['path' => $order->payment_proof]) }}" target="_blank" rel="noopener noreferrer" class="text-choc">Lihat bukti</a>
                                @else
                                    <span class="text-muted">Belum diunggah</span>
                                @endif
                            </td>
                            <td><span class="bb-status {{ $statusClasses[$order->payment_status] ?? 'bb-status-pending' }}">{{ $statusLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}</span></td>
                            <td>
                                @if($isPending && $order->payment_status === 'pending' && $order->payment_proof)
                                    <div class="d-flex gap-2">
                                        <form method="POST" action="{{ route('admin.payments.verify', $order->id_order) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Konfirmasi</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.payments.reject', $order->id_order) }}" onsubmit="return confirm('Tolak pembayaran dan batalkan pesanan ini?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm">Tolak</button>
                                        </form>
                                    </div>
                                @else
                                    <span class="text-muted">{{ $order->payment_proof ? 'Tidak ada tindakan' : 'Menunggu bukti pelanggan' }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Tidak ada pembayaran yang perlu ditinjau.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4"><x-pagination :currentPage="$orders->currentPage()" :totalPages="$orders->lastPage()" /></div>
    </div>
@endsection
