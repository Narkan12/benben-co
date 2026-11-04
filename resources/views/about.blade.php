@extends('layouts.app')

@section('title', 'Tentang BENBEN CO. Bakery — Cerita Usaha Rumahan di Antapani')

@section('meta_description', 'Sejarah, cara kerja, cita-cita digital, dan informasi BENBEN CO. Bakery berdasarkan observasi usaha rumahan di Antapani.')

@section('content')
    <section class="about-intro">
        <div class="container-bb">
            <div class="profile-breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span> About the Bakery</div>
            <span class="profile-eyebrow"><span></span> A small family-run bakery</span>
            <h1>Resep rumahan.<br><em>Cerita yang dibagikan.</em></h1>
            <p>
                BENBEN CO. adalah usaha toko kue rumahan di Antapani, Bandung. Ceritanya tumbuh dari
                rasa penasaran owner, dukungan keluarga, dan keinginan untuk berbagi produk bakery
                berkualitas dengan masyarakat sekitar.
            </p>
        </div>
    </section>

    <section class="section-paper profile-about-story">
        <div class="container-bb profile-story-grid">
            <div class="profile-story-visual">
                <div class="profile-story-photo">
                    <img src="{{ route('media.show', ['path' => $aboutImage]) }}" alt="Cheese roll BENBEN CO.">
                </div>
                <div class="profile-story-stamp"><span>BENBEN</span><strong>CO.</strong><small>EST. AT HOME</small></div>
                <p class="profile-image-caption">Usaha keluarga, dirintis dari dapur rumah.</p>
            </div>
            <div class="profile-story-copy">
                <span class="profile-eyebrow"><span></span> How it began</span>
                <h2>Video resep sederhana<br><em>menjadi awal perjalanan.</em></h2>
                <p>
                    Owner mulai tertarik membuat kue setelah melihat video di media sosial, lalu mencoba
                    membuat kue tersebut di rumah. Hasil pertama dibagikan kepada keluarga.
                </p>
                <p>
                    Sambutan keluarga yang positif mendorong mereka untuk mengembangkan produk dan
                    memasarkannya kepada masyarakat. Bahan berkualitas premium, cita rasa, dan harga
                    yang terjangkau menjadi bagian dari hal yang ingin dijaga.
                </p>
                <div class="profile-about-facts">
                    <div><span>JENIS USAHA</span><strong>Toko kue / bakery</strong></div>
                    <div><span>AREA USAHA</span><strong>Antapani, Kota Bandung</strong></div>
                    <div><span>PRODUK DIKENAL</span><strong>Cheese roll, brownies, bolen pisang</strong></div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-cream profile-about-team">
        <div class="container-bb profile-about-columns">
            <div>
                <span class="profile-eyebrow"><span></span> People behind the bakery</span>
                <h2>Usaha keluarga,<br><em>dikerjakan bersama.</em></h2>
                <p>
                    BENBEN CO. masih merupakan usaha rumahan sederhana dan belum mempunyai struktur
                    organisasi formal. Dalam kegiatan sehari-hari, owner menangani beberapa pekerjaan
                    sekaligus.
                </p>
                <div class="profile-owner-card">
                    <span class="profile-owner-initial" aria-hidden="true">B</span>
                    <div><strong>Owner BENBEN CO.</strong><small>Mengatur dan menjalankan kegiatan usaha sehari-hari.</small></div>
                </div>
            </div>
            <div class="profile-work-list">
                <article><span>01</span><div><h3>Produksi</h3><p>Menyiapkan dan membuat kue untuk ditawarkan kepada pelanggan.</p></div></article>
                <article><span>02</span><div><h3>Penjualan</h3><p>Menerima pertanyaan dan pesanan melalui kontak, media sosial, atau stand.</p></div></article>
                <article><span>03</span><div><h3>Promosi</h3><p>Mengenalkan produk kepada calon pelanggan, termasuk melalui media sosial.</p></div></article>
                <p class="profile-small-note">Pembagian tugas keluarga dapat menyesuaikan kebutuhan usaha; struktur jabatan formal belum dibentuk.</p>
            </div>
        </div>
    </section>

    <section class="section-paper profile-about-system">
        <div class="container-bb">
            <div class="profile-section-heading">
                <span class="profile-eyebrow"><span></span> The way orders work today</span>
                <h2>Dari melihat produk<br><em>sampai kue diterima.</em></h2>
                <p>Alur yang berjalan saat ini masih sederhana dan mengandalkan komunikasi langsung antara pelanggan dengan owner.</p>
            </div>
            <div class="profile-current-flow">
                <article><span>01</span><h3>Mencari informasi</h3><p>Pelanggan melihat produk melalui media sosial, WhatsApp, atau datang ke stand.</p></article>
                <article><span>02</span><h3>Bertanya kepada owner</h3><p>Pelanggan menanyakan produk, harga, dan ketersediaan secara langsung.</p></article>
                <article><span>03</span><h3>Menyepakati pesanan</h3><p>Detail pesanan dan pembayaran dibicarakan melalui komunikasi dengan owner.</p></article>
                <article><span>04</span><h3>Menyiapkan &amp; menerima</h3><p>Owner menyiapkan produk untuk diterima atau diambil pelanggan.</p></article>
            </div>
            <div class="profile-observation-note">
                <span aria-hidden="true">i</span>
                <p>Menurut hasil wawancara pada jobsheet, owner ingin promosi, penjualan, dan pengelolaan data usaha terbantu melalui sistem digital.</p>
            </div>
        </div>
    </section>

    <section class="section-cream profile-about-roadmap">
        <div class="container-bb profile-roadmap-grid">
            <div class="profile-roadmap-copy">
                <span class="profile-eyebrow"><span></span> Recommended digital direction</span>
                <h2>Mulai dari memperkenalkan usaha,<br><em>tumbuh sesuai kebutuhan.</em></h2>
                <p>
                    Tahap awal yang menjadi fokus adalah website Company Profile sebagai media informasi.
                    Pengembangan selanjutnya dapat dilakukan bersama owner berdasarkan kebutuhan usaha.
                </p>
                <a href="{{ route('home') }}#produk" class="profile-text-link">Lihat produk BENBEN CO. <span aria-hidden="true">↗</span></a>
            </div>
            <div class="profile-roadmap-list">
                <article class="profile-roadmap-item is-current"><span>01</span><div><small>TAHAP AWAL</small><h3>Company Profile website</h3><p>Perkenalan usaha, cerita, produk, alamat, kontak, dan informasi penting.</p></div><b>Fokus</b></article>
                <article class="profile-roadmap-item"><span>02</span><div><small>PENGEMBANGAN WEBSITE</small><h3>Penjualan &amp; promosi</h3><p>Katalog dan sarana pemesanan agar pelanggan lebih mudah mengenal produk.</p></div></article>
                <article class="profile-roadmap-item"><span>03</span><div><small>KEBUTUHAN OPERASIONAL</small><h3>POS &amp; manajemen data</h3><p>Membantu transaksi dan pengelolaan data usaha secara lebih teratur.</p></div></article>
                <article class="profile-roadmap-item"><span>04</span><div><small>PENGEMBANGAN LANJUTAN</small><h3>Rating &amp; aplikasi mobile</h3><p>Penilaian pelanggan dan versi Android dapat dibahas pada tahap berikutnya.</p></div></article>
            </div>
        </div>
    </section>

    <section class="section-paper profile-about-tech">
        <div class="container-bb profile-tech-panel">
            <div>
                <span class="profile-eyebrow"><span></span> Website prototype</span>
                <h2>Teknologi yang<br><em>digunakan pada website.</em></h2>
                <p>Teknologi dipilih untuk membangun website yang menyajikan informasi usaha dan membantu alur pemesanan.</p>
            </div>
            <div class="profile-tech-list">
                <div><strong>Laravel 12</strong><span>Framework untuk mengatur route dan alur aplikasi.</span></div>
                <div><strong>PHP &amp; Blade</strong><span>Bahasa pemrograman dan template halaman website.</span></div>
                <div><strong>HTML, CSS &amp; JavaScript</strong><span>Struktur, tampilan, dan interaksi pada halaman.</span></div>
                <div><strong>Bootstrap 5</strong><span>Komponen antarmuka responsif yang digunakan prototype.</span></div>
                <div><strong>MySQL &amp; Eloquent</strong><span>Penyimpanan dan pengelolaan data aplikasi.</span></div>
                <div><strong>Android Studio</strong><span>Teknologi yang direncanakan bila aplikasi mobile dibuat pada tahap berikutnya.</span></div>
            </div>
        </div>
    </section>

    <section class="section-cream profile-about-contact">
        <div class="container-bb profile-about-contact-inner">
            <div><span class="profile-eyebrow"><span></span> Come say hello</span><h2>Kenali produknya,<br><em>hubungi owner-nya.</em></h2></div>
            <div><p>Komp. Sinergi Antapani, Jl. Nyaman No.33B, Antapani Tengah, Kec. Antapani, Kota Bandung, Jawa Barat 40291.</p><a href="https://wa.me/628139494009" target="_blank" rel="noopener" class="profile-text-link">WhatsApp +62 813-9494-009 <span aria-hidden="true">↗</span></a><a href="https://www.instagram.com/benbenco.id/" target="_blank" rel="noopener" class="profile-text-link">Instagram @benbenco.id <span aria-hidden="true">↗</span></a><a href="{{ route('contact') }}" class="profile-text-link">Lihat lokasi toko <span aria-hidden="true">↗</span></a></div>
        </div>
    </section>
@endsection
