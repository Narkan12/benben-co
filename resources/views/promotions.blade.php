@extends('layouts.app')

@section('title', 'Promo — Bakery BenBen Co.')

@section('content')

    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">Promo</span>
            <h1 class="section-title mt-2">Penawaran Spesial untuk Anda</h1>
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / Promo</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="row g-4">
                @forelse($promos as $promo)
                    <div class="col-md-6 col-lg-4">
                        <article class="bb-cat-card h-100" style="text-align:left;padding:30px 26px;">
                            <span class="eyebrow">{{ $promo->kode_promo }}</span>
                            <h2 class="h4 mt-3">{{ $promo->nama_promo }}</h2>
                            <p class="section-sub">
                                Potongan
                                {{ $promo->jenis_diskon === 'persen'
                                    ? number_format($promo->nilai_diskon, 0).'%'
                                    : 'Rp'.number_format($promo->nilai_diskon, 0, ',', '.') }}
                            </p>
                            <p class="small mb-3">
                                Periode {{ $promo->tanggal_mulai->translatedFormat('d M Y') }}
                                sampai {{ $promo->tanggal_berakhir->translatedFormat('d M Y') }}.
                            </p>
                            <a href="{{ route('products') }}" class="btn-bb btn-bb-outline">Lihat produk</a>
                        </article>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="bb-panel text-center py-5">
                            <h2 class="h4">Belum ada promo yang sedang berlaku</h2>
                            <p class="section-sub mb-3">Silakan kunjungi kembali halaman ini untuk melihat penawaran terbaru.</p>
                            <a href="{{ route('products') }}" class="btn-bb btn-bb-primary">Jelajahi produk</a>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

@endsection
