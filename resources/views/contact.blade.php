@extends('layouts.app')

@section('title', 'Kontak — Bakery BenBen Co.')

@section('content')

    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">Kontak</span>
            <h1 class="section-title mt-2">Hubungi Kami</h1>
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / Kontak</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="row g-5">
                <div class="col-lg-5">
                    <span class="eyebrow">Info Toko</span>
                    <h2 class="section-title" style="font-size:1.7rem;margin-bottom:22px;">Datang &amp; Sapa Kami</h2>

                    <ul class="bb-feature-list" style="gap:20px;">
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <div><strong>Alamat</strong><br><span class="section-sub" style="font-size:.9rem;">Komp. Sinergi Antapani, Jl. Nyaman No.33B, Antapani Tengah, Kec. Antapani, Kota Bandung, Jawa Barat 40291</span></div>
                        </li>
                        <li>
                            <i class="bi bi-whatsapp"></i>
                            <div><strong>WhatsApp</strong><br><span class="section-sub" style="font-size:.9rem;">+62 813-9494-009</span></div>
                        </li>
                        <li>
                            <i class="bi bi-envelope-fill"></i>
                            <div><strong>Email</strong><br><span class="section-sub" style="font-size:.9rem;">hello@benbenco.id</span></div>
                        </li>
                        <li>
                            <i class="bi bi-instagram"></i>
                            <div><strong>Instagram</strong><br><span class="section-sub" style="font-size:.9rem;">@bakerybenbenco</span></div>
                        </li>
                        <li>
                            <i class="bi bi-clock-fill"></i>
                            <div><strong>Jam Operasional</strong><br><span class="section-sub" style="font-size:.9rem;">Setiap hari, 08.00–20.00 WIB</span></div>
                        </li>
                    </ul>
                </div>

                <div class="col-lg-7">
                    <div class="bb-panel mb-4 p-0 overflow-hidden">
                        <iframe title="Lokasi BENBEN CO. Bakery di Antapani, Bandung"
                                src="https://maps.google.com/maps?q=Komp.%20Sinergi%20Antapani%2C%20Jl.%20Nyaman%20No.33B%2C%20Bandung&t=&z=15&ie=UTF8&iwloc=&output=embed"
                                width="100%" height="320" style="border:0;" loading="lazy"
                                referrerpolicy="no-referrer-when-downgrade"></iframe>
                        <div class="p-3">
                            <a class="btn-bb btn-bb-outline btn-bb-sm"
                               href="https://www.google.com/maps/search/?api=1&query=Komp.+Sinergi+Antapani%2C+Jl.+Nyaman+No.33B%2C+Antapani+Tengah%2C+Bandung+40291"
                               target="_blank" rel="noopener">Open Google Maps</a>
                        </div>
                    </div>
                    <div class="bb-panel">
                        <h5 class="bb-panel-title">Kirim Pesan</h5>
                        <form>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="bb-form-label">Nama</label>
                                    <input type="text" class="bb-input" placeholder="Nama Anda">
                                </div>
                                <div class="col-md-6">
                                    <label class="bb-form-label">Email</label>
                                    <input type="email" class="bb-input" placeholder="nama@email.com">
                                </div>
                                <div class="col-12">
                                    <label class="bb-form-label">Subjek</label>
                                    <input type="text" class="bb-input" placeholder="Subjek pesan">
                                </div>
                                <div class="col-12">
                                    <label class="bb-form-label">Pesan</label>
                                    <textarea class="bb-input" rows="5" placeholder="Tulis pesan Anda di sini..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn-bb btn-bb-primary">
                                        Kirim Pesan <i class="bi bi-send"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
