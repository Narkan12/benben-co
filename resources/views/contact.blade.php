@extends('layouts.app')

@section('title', 'Kontak — Bakery BenBen Co.')

@section('content')

    <section class="bb-page-header">
        <div class="container-bb">
            <span class="eyebrow d-inline-flex" style="justify-content:center;">{{ $pageContent?->eyebrow ?? 'Kontak' }}</span>
            <h1 class="section-title mt-2">{{ $pageContent?->title ?? 'Hubungi Kami' }}</h1>
            @if($pageContent?->description)
                <p class="section-sub mx-auto">{{ $pageContent->description }}</p>
            @endif
            @if($pageContent?->image_path)
                <img class="bb-cms-page-image mx-auto mt-3"
                     src="{{ route('media.show', ['path' => $pageContent->image_path]) }}"
                     alt="{{ $pageContent->title }}">
            @endif
            <div class="bb-breadcrumb"><a href="{{ url('/') }}">Beranda</a> / Kontak</div>
        </div>
    </section>

    <section class="bb-section-sm">
        <div class="container-bb">
            <div class="row g-4 g-lg-5">
                <div class="col-lg-5">
                    <span class="eyebrow">Info Toko</span>
                    <h2 class="section-title" style="font-size:1.7rem;margin-bottom:22px;">Datang &amp; Sapa Kami</h2>

                    <ul class="bb-feature-list" style="gap:20px;">
                        <li>
                            <i class="bi bi-geo-alt-fill"></i>
                            <div><strong>Alamat</strong><br><span class="section-sub" style="font-size:.9rem;">Komp. Sinergi Antapani, Jl. Nyaman No.33B, Antapani Tengah, Kec. Antapani, Kota Bandung, Jawa Barat 40291</span></div>
                        </li>
                        @if($whatsappAdminNumber)
                            <li>
                                <i class="bi bi-whatsapp"></i>
                                <div><strong>WhatsApp</strong><br><a href="https://wa.me/{{ $whatsappAdminNumber }}" target="_blank" rel="noopener" class="section-sub" style="font-size:.9rem;">+{{ $whatsappAdminNumber }}</a></div>
                            </li>
                        @endif
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
                    @if($whatsappAdminNumber)
                        <div class="bb-panel">
                            <h5 class="bb-panel-title">Kirim Pesan via WhatsApp</h5>
                            <p class="section-sub">Pesan akan disiapkan di WhatsApp. Pesan baru terkirim setelah Anda menekan tombol kirim di aplikasi WhatsApp.</p>
                            <form action="https://wa.me/{{ $whatsappAdminNumber }}" method="get" target="_blank" data-whatsapp-contact-form>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="contact-name" class="bb-form-label">Nama</label>
                                        <input id="contact-name" name="name" type="text" class="bb-input" placeholder="Nama Anda" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="contact-email" class="bb-form-label">Email</label>
                                        <input id="contact-email" name="email" type="email" class="bb-input" placeholder="nama@email.com" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="contact-subject" class="bb-form-label">Subjek</label>
                                        <input id="contact-subject" name="subject" type="text" class="bb-input" placeholder="Subjek pesan" required>
                                    </div>
                                    <div class="col-12">
                                        <label for="contact-message" class="bb-form-label">Pesan</label>
                                        <textarea id="contact-message" name="message" class="bb-input" rows="5" placeholder="Tulis pesan Anda di sini..." required></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-bb btn-bb-primary">
                                            Lanjutkan ke WhatsApp <i class="bi bi-whatsapp"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @else
                        <div class="bb-panel">
                            <h5 class="bb-panel-title">Hubungi Kami</h5>
                            <p class="section-sub">WhatsApp belum dikonfigurasi. Anda tetap dapat menghubungi kami melalui email.</p>
                            <a href="mailto:hello@benbenco.id" class="btn-bb btn-bb-outline">Kirim Email <i class="bi bi-envelope"></i></a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

@endsection
