@extends('layouts.admin')

@section('title', 'Kelola Pesanan — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Pesanan</h2>
        </div>
    </div>

    <form method="GET" class="bb-filter-bar">
        <input type="search" name="search" class="bb-input" value="{{ request('search') }}" placeholder="Cari nomor pesanan/pelanggan">
        <select name="status" class="bb-input" style="width:auto;">
            <option value="">Semua Status</option>
            @foreach(['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm">Filter</button>
        @if(request()->hasAny(['search', 'status']))<a href="{{ route('admin.orders') }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>@endif
    </form>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr><th>Order ID</th><th>Customer / Produk</th><th>Tanggal</th><th>Total</th><th>Pembayaran</th><th>Status</th><th>Tindakan Berikutnya</th></tr>
                </thead>
                <tbody>
                    @forelse($orders as $o)
                        @php
                            $statusLabels = ['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Diproses', 'ready' => 'Siap Diambil', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'];
                            $statusClasses = ['pending' => 'bb-status-pending', 'confirmed' => 'bb-status-process', 'processing' => 'bb-status-process', 'ready' => 'bb-status-ready', 'completed' => 'bb-status-done', 'cancelled' => 'bb-status-cancel'];
                        @endphp
                        <tr>
                            <td>{{ $o->kode_order }}</td>
                            <td>
                                {{ $o->customer?->nama ?? 'Pelanggan dihapus' }}
                                <small class="d-block text-muted">{{ $o->details->map(fn ($detail) => ($detail->product?->nama ?? 'Produk dihapus').' × '.$detail->qty)->join(', ') }}</small>
                                <details class="small mt-1">
                                    <summary>Riwayat status</summary>
                                    @forelse($o->statusHistories as $history)
                                        <span class="d-block text-muted">
                                            {{ $history->changed_at?->format('d M Y H:i') ?? 'Waktu tidak diketahui' }}:
                                            {{ $statusLabels[$history->from_status] ?? 'Pesanan dibuat' }}
                                            →
                                            {{ $statusLabels[$history->to_status] ?? 'Status tidak diketahui' }}
                                            ({{ $history->user?->nama ?? ($history->source === 'checkout' ? 'Customer' : 'Sistem / pengguna dihapus') }})
                                        </span>
                                    @empty
                                        <span class="d-block text-muted">Riwayat tidak tersedia untuk pesanan lama.</span>
                                    @endforelse
                                </details>
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($o->tanggal)->format('d M Y H:i') }}</td>
                            <td>Rp{{ number_format($o->total, 0, ',', '.') }}</td>
                            <td>
                                {{ ['cash' => 'Tunai', 'transfer' => 'Transfer', 'ewallet' => 'E-Wallet', 'other' => 'Metode lain (data lama)'][$o->payment_method] ?? 'Tidak diketahui' }}
                                @php
                                    $paymentLabels = ['pending' => 'Belum diterima / diverifikasi', 'verified' => 'Pembayaran diterima', 'rejected' => 'Pembayaran ditolak'];
                                @endphp
                                <small class="d-block text-muted">{{ $paymentLabels[$o->payment_status] ?? 'Status pembayaran tidak diketahui' }}</small>
                            </td>
                            <td><span class="bb-status {{ $statusClasses[$o->status] ?? 'bb-status-pending' }}">{{ $statusLabels[$o->status] ?? ucfirst($o->status) }}</span></td>
                            <td>
                                @php
                                    $actionLabels = [
                                        'confirmed' => 'Konfirmasi Pesanan',
                                        'processing' => 'Mulai Diproses',
                                        'ready' => 'Tandai Siap Diambil',
                                        'completed' => 'Tandai Selesai',
                                        'cancelled' => 'Batalkan Pesanan',
                                    ];
                                @endphp
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($statusOptions[$o->id_order] as $value)
                                        <form method="POST" action="{{ route('admin.orders.update-status', $o->id_order) }}"
                                              @if($value === 'cancelled') onsubmit="return confirm('Yakin ingin membatalkan pesanan ini? Stok akan dikembalikan.')"
                                              @endif>
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="status" value="{{ $value }}">
                                            <button type="submit" class="btn-bb {{ $value === 'cancelled' ? 'btn-bb-outline' : 'btn-bb-primary' }} btn-bb-sm">
                                                {{ $actionLabels[$value] ?? ($statusLabels[$value] ?? ucfirst($value)) }}
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                                @if($statusOptions[$o->id_order] === [] && $o->status === 'pending' && $o->payment_method !== 'cash')
                                    <small class="d-block text-muted">Verifikasi bukti pembayaran untuk mengonfirmasi pesanan.</small>
                                @elseif($statusOptions[$o->id_order] === [])
                                    <small class="d-block text-muted">Tidak ada tindakan lanjutan.</small>
                                @endif
                                @if($o->status === 'pending' && $o->payment_method !== 'cash' && $o->payment_status !== 'verified')
                                    <small class="d-block text-muted mt-1">Verifikasi pembayaran sebelum konfirmasi.</small>
                                @endif
                                @if($o->status === 'ready' && $o->payment_method === 'cash' && $o->payment_status !== 'verified')
                                    <form method="POST" action="{{ route('admin.orders.receive-cash', $o->id_order) }}" class="mt-2"
                                          onsubmit="return confirm('Tandai pembayaran tunai pesanan ini sudah diterima?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn-bb btn-bb-primary btn-bb-sm">Tandai Tunai Diterima</button>
                                    </form>
                                @elseif($o->payment_method === 'cash' && $o->payment_status === 'verified')
                                    <small class="d-block text-success mt-1">Pembayaran tunai diterima</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">Belum ada pesanan yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <x-pagination :currentPage="$orders->currentPage()" :totalPages="$orders->lastPage()" />
        </div>
    </div>

@endsection
