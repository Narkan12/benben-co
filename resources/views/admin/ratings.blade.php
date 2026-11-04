@extends('layouts.admin')

@section('title', 'Rating & Ulasan — Admin Bakery BenBen Co.')

@section('content')
    <div class="bb-dash-topbar">
        <div>
            <span class="eyebrow">Admin Panel</span>
            <h1 class="section-title" style="font-size:1.6rem;">Rating &amp; Ulasan</h1>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6">
            <div class="bb-stat-card">
                <span class="ic"><i class="bi bi-star-fill"></i></span>
                <div><div class="val">{{ number_format($ratingAverage, 1) }} / 5</div><div class="lbl">Rata-rata Rating</div></div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="bb-stat-card">
                <span class="ic"><i class="bi bi-chat-square-text"></i></span>
                <div><div class="val">{{ number_format($ratingCount) }}</div><div class="lbl">Total Ulasan</div></div>
            </div>
        </div>
    </div>

    <form method="GET" class="bb-filter-bar">
        <input type="search" name="search" class="bb-input" value="{{ request('search') }}"
               placeholder="Cari nama, produk, atau komentar..." style="max-width:320px;">
        <select name="rating" class="bb-input" style="width:auto;">
            <option value="">Semua rating</option>
            @for($star = 5; $star >= 1; $star--)
                <option value="{{ $star }}" @selected((string) request('rating') === (string) $star)>{{ $star }} bintang</option>
            @endfor
        </select>
        <button type="submit" class="btn-bb btn-bb-outline btn-bb-sm">Filter</button>
        @if(request()->hasAny(['search', 'rating']))
            <a href="{{ route('admin.ratings') }}" class="btn-bb btn-bb-outline btn-bb-sm">Reset</a>
        @endif
    </form>

    <div class="bb-panel">
        <div style="overflow-x:auto;">
            <table class="bb-table-bb">
                <thead>
                    <tr><th>Pelanggan</th><th>Produk</th><th>Rating</th><th>Ulasan</th><th>Tanggal</th></tr>
                </thead>
                <tbody>
                    @forelse($ratings as $review)
                        <tr>
                            <td>{{ $review->nama_customer }}</td>
                            <td>{{ $review->product?->nama ?? 'Produk dihapus' }}</td>
                            <td>
                                <span class="bb-review-stars" aria-label="Rating {{ $review->rating }} dari 5">
                                    @for($star = 1; $star <= 5; $star++)
                                        <span>{{ $star <= $review->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </span>
                                <span class="text-muted">({{ $review->rating }}/5)</span>
                            </td>
                            <td>{{ $review->komentar ?: 'Tidak ada komentar.' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($review->created_at)->translatedFormat('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">Belum ada rating yang cocok.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <x-pagination :currentPage="$ratings->currentPage()" :totalPages="$ratings->lastPage()" />
        </div>
    </div>
@endsection
