@extends('layouts.app')

@section('title', 'BENBEN CO. Bakery — Toko Kue Rumahan Antapani')

@section('meta_description', 'Kenali cerita, produk, dan informasi BENBEN CO. Bakery, toko kue rumahan di Antapani, Bandung.')

@section('content')
    <section class="profile-hero" id="home">
        <div class="container-bb profile-hero-grid">
            <div class="profile-hero-copy bb-enter bb-enter-1">
                <span class="profile-eyebrow"><span></span> Freshly baked · Made with care</span>
                <h1>A Little Sweetness<br><em>for Every Moment</em></h1>
                <p class="profile-lead">
                    Berawal dari dapur keluarga, BENBEN CO. menghadirkan cheese roll, brownies, bolen pisang,
                    cookies, dan minuman dengan bahan berkualitas serta harga yang bersahabat.
                </p>
                <div class="profile-hero-actions">
                    <a href="{{ route('products') }}" class="btn-bb btn-bb-primary">Jelajahi Menu</a>
                    <a href="{{ route('about') }}" class="btn-bb btn-bb-outline">Cerita Kami</a>
                </div>
                <div class="profile-hero-facts" aria-label="Informasi singkat BENBEN CO.">
                    <div><strong>Dari keluarga</strong><span>Usaha rumahan</span></div>
                    <div><strong>Antapani</strong><span>Bandung, Jawa Barat</span></div>
                    <div><strong>Pesan mudah</strong><span>WhatsApp atau website</span></div>
                </div>
            </div>

            <div class="profile-hero-visual">
                <div class="profile-hero-orbit"></div>
                <div class="profile-hero-image">
                    <img src="{{ route('media.show', ['path' => $heroImage]) }}" alt="Cheese roll BENBEN CO. siap dinikmati">
                </div>
                <div class="profile-float-note profile-float-note-top">
                    <span class="profile-note-mark">01</span>
                    <span><strong>Dibuat dengan teliti</strong><small>Rasa rumahan, kualitas pilihan</small></span>
                </div>
                <div class="profile-float-note profile-float-note-bottom">
                    <span class="profile-note-heart" aria-hidden="true">♡</span>
                    <span><strong>From our family kitchen</strong><small>Untuk momen bersama</small></span>
                </div>
                <span class="profile-hero-caption">Sedikit manis, banyak cerita.</span>
            </div>
        </div>
        <a href="#cerita" class="profile-scroll-cue" aria-label="Lanjut membaca cerita BENBEN CO.">
            <span></span> Kenali cerita kami
        </a>
    </section>

    @if($promos->isNotEmpty())
        <div class="modal fade bb-promo-modal" id="homePromoModal" tabindex="-1"
             aria-labelledby="homePromoModalTitle" aria-hidden="true" data-promo-modal>
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                <div class="modal-content">
                    <div class="modal-header bb-promo-modal-header">
                        <div>
                            <span class="bb-promo-modal-eyebrow"><i class="bi bi-stars" aria-hidden="true"></i> Sweet treats, special offers</span>
                            <h2 class="modal-title" id="homePromoModalTitle">Promo spesial BENBEN CO.</h2>
                            <p class="mb-0">Gunakan kode berikut untuk menikmati penawaran yang sedang berlaku.</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup promo"></button>
                    </div>
                    <div class="modal-body">
                        <div class="bb-promo-modal-list">
                            @foreach($promos as $promo)
                                <article class="bb-modal-promo-card">
                                    <div class="bb-modal-promo-heading">
                                        <span class="bb-modal-promo-status"><i class="bi bi-check-circle-fill" aria-hidden="true"></i> Sedang berlaku</span>
                                        <span class="bb-modal-promo-discount">
                                            {{ $promo->jenis_diskon === 'persen'
                                                ? number_format($promo->nilai_diskon, 0).'% OFF'
                                                : 'HEMAT Rp'.number_format($promo->nilai_diskon, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <h3>{{ $promo->nama_promo }}</h3>
                                    <div class="bb-modal-promo-code">
                                        <div>
                                            <small>KODE PROMO</small>
                                            <strong>{{ $promo->kode_promo }}</strong>
                                        </div>
                                        <button type="button" class="bb-promo-copy-button" data-copy-promo="{{ $promo->kode_promo }}">
                                            <i class="bi bi-copy" aria-hidden="true"></i> Salin kode
                                        </button>
                                    </div>
                                    <p class="bb-modal-promo-dates">
                                        <i class="bi bi-calendar-check" aria-hidden="true"></i>
                                        <span>Berlaku {{ $promo->tanggal_mulai->translatedFormat('d M Y') }} – {{ $promo->tanggal_berakhir->translatedFormat('d M Y') }}</span>
                                    </p>
                                    <p class="bb-promo-copy-feedback mb-0" data-copy-feedback role="status" aria-live="polite"></p>
                                </article>
                            @endforeach
                        </div>
                        <p class="bb-promo-modal-note mb-0">
                            <i class="bi bi-info-circle" aria-hidden="true"></i>
                            Batas pemakaian mengikuti periode promo di atas. Saat ini tidak ada kuota penggunaan terpisah.
                        </p>
                    </div>
                    <div class="modal-footer bb-promo-modal-footer">
                        <a href="{{ route('products') }}" class="btn-bb btn-bb-primary">Pilih produk <span aria-hidden="true">↗</span></a>
                        <button type="button" class="bb-promo-modal-dismiss" data-bs-dismiss="modal">Nanti saja</button>
                    </div>
                </div>
            </div>
        </div>

        <section class="section-cream py-5" aria-labelledby="home-promotions-title">
            <div class="container-bb">
                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                    <div>
                        <span class="profile-eyebrow"><span></span> Penawaran yang sedang berlaku</span>
                        <h2 id="home-promotions-title" class="section-title mb-0">Promo BENBEN CO.</h2>
                    </div>
                    <a href="{{ route('promotions') }}" class="profile-text-link">Lihat semua promo <span aria-hidden="true">↗</span></a>
                </div>
                <div class="row g-3">
                    @foreach($promos as $promo)
                        <div class="col-md-6 col-lg-4">
                            <article class="bb-panel h-100">
                                <span class="eyebrow">{{ $promo->kode_promo }}</span>
                                <h3 class="h5 mt-2">{{ $promo->nama_promo }}</h3>
                                <p class="mb-2">
                                    Potongan
                                    {{ $promo->jenis_diskon === 'persen'
                                        ? number_format($promo->nilai_diskon, 0).'%'
                                        : 'Rp'.number_format($promo->nilai_diskon, 0, ',', '.') }}
                                </p>
                                <small class="text-muted">Berlaku sampai {{ $promo->tanggal_berakhir->translatedFormat('d M Y') }}</small>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="profile-story section-paper" id="cerita">
        <div class="container-bb profile-story-grid">
            <div class="profile-story-visual">
                <div class="profile-story-photo">
                    <img src="{{ route('media.show', ['path' => $storyImage]) }}" alt="Brownies panggang buatan BENBEN CO.">
                </div>
                <div class="profile-story-stamp"><span>BENBEN</span><strong>CO.</strong><small>OUR STORY</small></div>
                <p class="profile-image-caption">Cerita sederhana yang tumbuh dari rumah.</p>
            </div>
            <div class="profile-story-copy">
                <span class="profile-eyebrow"><span></span> A family bakery in Antapani</span>
                <h2>Dari rasa penasaran,<br><em>tumbuh jadi cerita.</em></h2>
                <p>
                    Usaha BENBEN CO. bermula saat owner melihat video pembuatan kue di media sosial dan
                    mencoba membuatnya sendiri. Kue pertama lalu disajikan kepada keluarga.
                </p>
                <p>
                    Sambutan keluarga yang positif membuat resep rumahan itu dikembangkan bersama keluarga
                    untuk dipasarkan kepada masyarakat. Kini BENBEN CO. hadir sebagai usaha bakery rumahan
                    di Antapani yang mengutamakan cita rasa, bahan berkualitas premium, dan harga terjangkau.
                </p>
                <a href="{{ route('about') }}" class="profile-text-link">Baca kisah dan profil usaha <span aria-hidden="true">↗</span></a>
                <div class="profile-owner-note">
                    <span class="profile-owner-initial" aria-hidden="true">B</span>
                    <span><strong>Dikelola bersama keluarga</strong><small>Owner menangani produksi, penjualan, dan promosi.</small></span>
                </div>
            </div>
        </div>
    </section>

    <section class="profile-journey section-cream" aria-labelledby="profile-journey-title">
        <div class="container-bb">
            <div class="profile-section-heading">
                <span class="profile-eyebrow"><span></span> A story made at home</span>
                <h2 id="profile-journey-title">Langkah kecil,<br><em>cerita yang terus tumbuh.</em></h2>
                <p>Perjalanan BENBEN CO. dimulai dari hal sederhana, lalu berkembang bersama keluarga dan pelanggan.</p>
            </div>
            <div class="profile-journey-list">
                <article class="profile-journey-card">
                    <span class="profile-step">01</span>
                    <span class="profile-step-line"></span>
                    <h3>Terinspirasi</h3>
                    <p>Owner melihat video pembuatan kue di media sosial dan tertarik untuk mencoba resepnya.</p>
                </article>
                <article class="profile-journey-card">
                    <span class="profile-step">02</span>
                    <span class="profile-step-line"></span>
                    <h3>Dicoba di rumah</h3>
                    <p>Kue pertama dibuat sendiri dan disajikan kepada keluarga untuk dicicipi bersama.</p>
                </article>
                <article class="profile-journey-card">
                    <span class="profile-step">03</span>
                    <span class="profile-step-line"></span>
                    <h3>Dukungan keluarga</h3>
                    <p>Respons keluarga yang positif menjadi dorongan untuk mengembangkan produk bakery.</p>
                </article>
                <article class="profile-journey-card">
                    <span class="profile-step">04</span>
                    <span class="profile-step-line"></span>
                    <h3>Berbagi lebih luas</h3>
                    <p>Produk mulai dipasarkan kepada masyarakat dengan menjaga rasa dan kualitasnya.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="profile-products section-paper" id="produk">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-row">
                <div>
                    <span class="profile-eyebrow"><span></span> Made for sharing</span>
                    <h2>Kenalan dengan<br><em>produk kami.</em></h2>
                    <p>Beberapa pilihan bakery dan minuman yang tersedia di BENBEN CO.</p>
                </div>
                <a href="{{ route('products') }}" class="btn-bb btn-bb-outline profile-desktop-link">Lihat semua produk <span aria-hidden="true">↗</span></a>
            </div>

            <div class="profile-product-grid">
                @forelse($products as $product)
                    <x-product-card
                        :productId="$product->id_product"
                        :name="$product->nama"
                        :slug="$product->slug"
                        :image="$product->gambar"
                        :category="$product->category?->slug ?? ''"
                        :price="$product->harga"
                        :oldPrice="null"
                        :rating="$product->ratings_avg_rating"
                        :stock="$product->stok"
                        :badge="$loop->first ? 'Pilihan' : null" />
                @empty
                    <p class="section-sub">Produk sedang disiapkan. Silakan kembali lagi nanti.</p>
                @endforelse
            </div>
            <a href="{{ route('products') }}" class="btn-bb btn-bb-outline profile-mobile-link">Jelajahi seluruh menu <span aria-hidden="true">↗</span></a>
        </div>
    </section>

    <section class="profile-categories section-cream" id="kategori">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-centered">
                <span class="profile-eyebrow"><span></span> Find your favourite</span>
                <h2>Satu meja,<br><em>banyak pilihan.</em></h2>
                <p>Dari camilan gurih sampai minuman segar, temukan teman untuk setiap suasana.</p>
            </div>
            <div class="profile-category-grid">
                @foreach($categoryHighlights as $category)
                    <a href="{{ route('products', ['category' => $category['slug']]) }}" class="profile-category-card">
                        <span class="profile-category-photo">
                            <img src="{{ route('media.show', ['path' => $category['image']]) }}" alt="{{ $category['name'] }}" loading="lazy">
                            <span aria-hidden="true">↗</span>
                        </span>
                        <span class="profile-category-name">{{ $category['name'] }}</span>
                        <span class="profile-category-description">{{ $category['description'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="profile-promise section-paper" aria-labelledby="profile-promise-title">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-centered">
                <span class="profile-eyebrow"><span></span> The BENBEN CO. promise</span>
                <h2 id="profile-promise-title">Kebaikan kecil<br><em>di setiap sajian.</em></h2>
                <p>Hal-hal yang menjadi perhatian BENBEN CO. saat menghadirkan produk untuk pelanggan.</p>
            </div>
            <div class="profile-promise-grid">
                <article class="profile-promise-card">
                    <span class="profile-promise-number">01</span>
                    <h3>Bahan berkualitas</h3>
                    <p>Memilih bahan premium untuk menjaga cita rasa pada setiap sajian bakery.</p>
                </article>
                <article class="profile-promise-card">
                    <span class="profile-promise-number">02</span>
                    <h3>Rasa yang diperhatikan</h3>
                    <p>Berawal dari resep rumahan dan respons keluarga yang ikut membentuk produk.</p>
                </article>
                <article class="profile-promise-card">
                    <span class="profile-promise-number">03</span>
                    <h3>Harga bersahabat</h3>
                    <p>Pilihan kue yang terjangkau untuk menemani keseharian dan momen berbagi.</p>
                </article>
                <article class="profile-promise-card">
                    <span class="profile-promise-number">04</span>
                    <h3>Dikelola keluarga</h3>
                    <p>Produksi, penjualan, dan promosi ditangani langsung dalam usaha rumahan.</p>
                </article>
                <article class="profile-promise-card">
                    <span class="profile-promise-number">05</span>
                    <h3>Dekat dengan pelanggan</h3>
                    <p>Pelanggan dapat bertanya dan memesan melalui kontak usaha atau datang ke stand.</p>
                </article>
                <article class="profile-promise-card">
                    <span class="profile-promise-number">06</span>
                    <h3>Bertumbuh bersama</h3>
                    <p>Masukan pelanggan menjadi bagian penting untuk pengembangan usaha ke depan.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="profile-how section-cream" aria-labelledby="profile-how-title">
        <div class="container-bb profile-how-grid">
            <div class="profile-how-copy">
                <span class="profile-eyebrow"><span></span> From hello to homemade</span>
                <h2 id="profile-how-title">Cara sederhana,<br><em>dibuat lebih mudah.</em></h2>
                <p>
                    Sebelumnya pelanggan melihat informasi di media sosial, bertanya kepada owner,
                    lalu menyepakati pesanan dan pembayaran. Website ini membantu memperkenalkan usaha,
                    menampilkan katalog, dan merapikan langkah pemesanan.
                </p>
                <a href="{{ route('products') }}" class="profile-text-link">Mulai dari katalog produk <span aria-hidden="true">↗</span></a>
            </div>
            <div class="profile-flow">
                <article class="profile-flow-step">
                    <span>01</span><div><h3>Lihat pilihan</h3><p>Jelajahi foto, deskripsi, dan harga produk di katalog.</p></div>
                </article>
                <article class="profile-flow-step">
                    <span>02</span><div><h3>Tentukan pesanan</h3><p>Pilih produk, jumlah, lalu isi informasi pemesanan.</p></div>
                </article>
                <article class="profile-flow-step">
                    <span>03</span><div><h3>Konfirmasi melalui WhatsApp</h3><p>Periksa detail pesanan dan hubungi BENBEN CO. untuk tindak lanjut.</p></div>
                </article>
                <article class="profile-flow-step">
                    <span>04</span><div><h3>Produk disiapkan</h3><p>Pesanan disiapkan untuk pickup atau delivery sesuai kesepakatan.</p></div>
                </article>
            </div>
        </div>
    </section>

    <section class="profile-roadmap section-paper" aria-labelledby="profile-roadmap-title">
        <div class="container-bb profile-roadmap-grid">
            <div class="profile-roadmap-copy">
                <span class="profile-eyebrow"><span></span> A digital home for the bakery</span>
                <h2 id="profile-roadmap-title">Bukan hanya website.<br><em>Awal dari langkah baru.</em></h2>
                <p>
                    Owner berharap teknologi dapat membantu promosi, penjualan, dan pengelolaan data usaha.
                    Pengembangan dilakukan bertahap sesuai kebutuhan BENBEN CO.
                </p>
                <div class="profile-tech-tags" aria-label="Teknologi prototype website">
                    <span>Laravel</span><span>PHP</span><span>Blade</span><span>Bootstrap</span><span>MySQL</span>
                </div>
                <small class="profile-tech-note">Prototype website ini dibuat dengan Laravel, PHP, Blade, Bootstrap, JavaScript, dan MySQL.</small>
            </div>
            <div class="profile-roadmap-list">
                <article class="profile-roadmap-item is-current"><span>01</span><div><small>TAHAP WEBSITE</small><h3>Company Profile</h3><p>Cerita usaha, katalog, informasi kontak, dan lokasi bakery.</p></div><b>Saat ini</b></article>
                <article class="profile-roadmap-item"><span>02</span><div><small>FITUR DIGITAL</small><h3>Pemesanan &amp; rating</h3><p>Alur pemesanan online dan ruang bagi pelanggan untuk memberi penilaian.</p></div></article>
                <article class="profile-roadmap-item"><span>03</span><div><small>OPERASIONAL</small><h3>POS &amp; manajemen data</h3><p>Mendukung transaksi penjualan dan pengelolaan data usaha.</p></div></article>
                <article class="profile-roadmap-item"><span>04</span><div><small>PENGEMBANGAN LANJUTAN</small><h3>Aplikasi mobile</h3><p>Rencana pengembangan mobile mengikuti pembahasan dan kebutuhan owner.</p></div></article>
            </div>
        </div>
    </section>

    <section class="profile-gallery section-cream" id="gallery">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-row">
                <div>
                    <span class="profile-eyebrow"><span></span> A little look into our kitchen</span>
                    <h2>Sepotong cerita,<br><em>dari dapur kami.</em></h2>
                    <p>Kenali lebih dekat sajian BENBEN CO. melalui pilihan foto produk.</p>
                </div>
                <a href="{{ route('products') }}" class="profile-text-link">Lihat katalog <span aria-hidden="true">↗</span></a>
            </div>
            <div class="profile-gallery-grid">
                @foreach($galleryImages as $index => $image)
                    <button type="button" class="profile-gallery-item {{ $index === 0 ? 'profile-gallery-tall' : '' }}"
                            aria-label="Perbesar foto {{ $image['label'] }}" data-lightbox>
                        <img src="{{ route('media.show', ['path' => $image['path']]) }}" alt="{{ $image['label'] }} BENBEN CO." loading="lazy">
                        <span>{{ $image['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </section>

    <section class="profile-reviews section-paper" id="ulasan">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-row">
                <div>
                    <span class="profile-eyebrow"><span></span> Notes from our customers</span>
                    <h2>Cerita dari<br><em>yang sudah mencicipi.</em></h2>
                    <p>Ulasan asli pelanggan yang dikirim melalui halaman produk.</p>
                </div>
                <a href="{{ route('products') }}" class="btn-bb btn-bb-outline profile-desktop-link">Pilih produk untuk dinilai</a>
            </div>
            @if($reviews->isNotEmpty())
                <div class="profile-review-grid">
                    @foreach($reviews as $review)
                        <article class="profile-review-card">
                            <div class="profile-review-stars" aria-label="Rating {{ $review->rating }} dari 5">
                                @for($star = 1; $star <= 5; $star++)
                                    <span>{{ $star <= $review->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <p>{{ $review->komentar ?: 'Pelanggan memberi penilaian untuk produk ini.' }}</p>
                            <div class="profile-review-by">
                                <span>{{ mb_substr($review->nama_customer, 0, 1) }}</span>
                                <div><strong>{{ $review->nama_customer }}</strong><small>{{ $review->product?->nama ?? 'Produk BENBEN CO.' }}</small></div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="profile-review-empty">
                    <span aria-hidden="true">♡</span>
                    <div><h3>Jadilah cerita pertama.</h3><p>Belum ada ulasan yang dibagikan. Setelah mencicipi produk, pelanggan dapat memberi rating di halaman detail produk.</p></div>
                    <a href="{{ route('products') }}" class="btn-bb btn-bb-outline btn-bb-sm">Jelajahi produk</a>
                </div>
            @endif
            <a href="{{ route('products') }}" class="btn-bb btn-bb-outline profile-mobile-link">Temukan produk favoritmu</a>
        </div>
    </section>

    <section class="profile-visit section-cream" id="contact">
        <div class="container-bb">
            <div class="profile-section-heading profile-section-heading-centered">
                <span class="profile-eyebrow"><span></span> Find us in Antapani</span>
                <h2>Ruang kecil untuk<br><em>momen yang manis.</em></h2>
                <p>Mampir ke stand kami atau hubungi BENBEN CO. untuk bertanya tentang produk dan pesanan.</p>
            </div>
            <div class="profile-visit-card">
                <div class="profile-map">
                    <iframe title="Peta lokasi BENBEN CO. Bakery di Antapani, Bandung"
                            src="https://maps.google.com/maps?q=Komp.%20Sinergi%20Antapani%2C%20Jl.%20Nyaman%20No.33B%2C%20Bandung&t=&z=15&ie=UTF8&iwloc=&output=embed"
                            width="100%" height="100%" style="border:0;" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                <div class="profile-visit-info">
                    <span class="profile-visit-label">KUNJUNGI KAMI</span>
                    <h3>BENBEN CO. Bakery</h3>
                    <p class="profile-visit-address">Komp. Sinergi Antapani,<br>Jl. Nyaman No.33B, Antapani Tengah,<br>Kec. Antapani, Kota Bandung,<br>Jawa Barat 40291</p>
                    <div class="profile-contact-links">
                        <a href="https://wa.me/628139494009" target="_blank" rel="noopener"><span aria-hidden="true">↗</span><span><small>WHATSAPP</small><strong>+62 813-9494-009</strong></span></a>
                        <a href="https://www.instagram.com/benbenco.id/" target="_blank" rel="noopener"><span aria-hidden="true">◎</span><span><small>INSTAGRAM</small><strong>@benbenco.id</strong></span></a>
                    </div>
                    <a class="btn-bb btn-bb-primary profile-map-button" href="https://www.google.com/maps/search/?api=1&amp;query=Komp.+Sinergi+Antapani%2C+Jl.+Nyaman+No.33B%2C+Antapani+Tengah%2C+Bandung+40291" target="_blank" rel="noopener">Buka Google Maps <span aria-hidden="true">↗</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="profile-final-cta">
        <div class="container-bb profile-final-cta-inner">
            <span class="profile-eyebrow"><span></span> Made for your next moment</span>
            <h2>Sudah terbayang<br><em>camilan favoritmu?</em></h2>
            <p>Jelajahi pilihan produk BENBEN CO. dan tentukan sajian untuk momen berikutnya.</p>
            <div class="profile-hero-actions">
                <a href="{{ route('products') }}" class="btn-bb btn-bb-primary">Jelajahi Menu</a>
                <a href="https://wa.me/628139494009" target="_blank" rel="noopener" class="btn-bb btn-bb-outline">Hubungi via WhatsApp</a>
            </div>
        </div>
    </section>
@endsection
