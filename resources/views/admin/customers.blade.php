@extends('layouts.admin')

@section('title', 'Kelola Pelanggan — Admin Bakery BenBen Co.')

@section('content')

    <div class="bb-dash-topbar">
        <div>
            <h2 class="section-title" style="font-size:1.6rem;">Kelola Pelanggan</h2>
        </div>
    </div>

    <div class="bb-filter-bar">
        <form method="GET" id="customer-search" class="d-flex flex-wrap gap-2 align-items-end">
            <div class="bb-filter-field">
                <label for="customer-search-input" class="bb-form-label">Cari nama pelanggan</label>
                <input id="customer-search-input" type="search" name="search" class="bb-input" value="{{ request('search') }}" placeholder="Nama pelanggan" style="max-width:280px;">
            </div>
            <button class="btn-bb btn-bb-outline btn-bb-sm">Cari</button>
            @if(request()->filled('search'))<a href="{{ route('admin.customers') }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>@endif
        </form>
    </div>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead><tr><th>Nama</th><th>No. WhatsApp</th><th>Total Pesanan</th><th>Terdaftar</th><th>Kontak</th></tr></thead>
                <tbody>
                    @forelse($customers as $c)
                        <tr>
                            <td>{{ $c->nama }}</td>
                            <td>{{ $c->no_whatsapp }}</td>
                            <td>{{ $c->orders_count }} pesanan</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($c->created_at)->format('d M Y') }}</td>
                            <td>
                                <a class="btn-bb btn-bb-outline btn-bb-sm" href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D+/', '', $c->no_whatsapp)) }}" target="_blank" rel="noopener noreferrer"><i class="bi bi-whatsapp"></i> Hubungi</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Belum ada pelanggan yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <x-pagination :currentPage="$customers->currentPage()" :totalPages="$customers->lastPage()" />
        </div>
    </div>

@endsection
