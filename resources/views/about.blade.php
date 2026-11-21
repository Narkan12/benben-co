@extends('layouts.app')

@section('title', 'Tentang BENBEN CO. Bakery — Cerita Usaha Rumahan di Antapani')

@section('meta_description', 'Sejarah, cara kerja, cita-cita digital, dan informasi BENBEN CO. Bakery berdasarkan observasi usaha rumahan di Antapani.')

@section('content')
    <section class="about-intro">
        <div class="container-bb">
            <div class="profile-breadcrumb"><a href="{{ route('home') }}">Home</a><span>/</span> About the Bakery</div>
            <span class="profile-eyebrow"><span></span> {{ $aboutContent?->eyebrow ?? 'A small family-run bakery' }}</span>
            @if($aboutContent)
                <h1>{{ $aboutContent->title }}</h1>
                <p>{{ $aboutContent->description }}</p>
            @else
                <h1>Resep rumahan.<br><em>Cerita yang dibagikan.</em></h1>
                <p>
                    BENBEN CO. adalah usaha toko kue rumahan di Antapani, Bandung. Ceritanya tumbuh dari
                    rasa penasaran owner, dukungan keluarga, dan keinginan untuk berbagi produk bakery
                    berkualitas dengan masyarakat sekitar.
                </p>
            @endif
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
                <span class="profile-eyebrow"><span></span> Alur pemesanan di website</span>
                <h2>Dari memilih menu<br><em>sampai pesanan diterima.</em></h2>
                <p>Jelajahi katalog sebagai pengunjung, lalu gunakan akun pelanggan untuk membuat pesanan dan memantau statusnya.</p>
            </div>
            <div class="profile-current-flow">
                <article><span>01</span><h3>Lihat katalog</h3><p>Periksa foto, deskripsi, harga, dan ketersediaan produk secara publik.</p></article>
                <article><span>02</span><h3>Masuk sebagai pelanggan</h3><p>Login atau daftar sebelum menambahkan produk ke keranjang.</p></article>
                <article><span>03</span><h3>Checkout dan pembayaran</h3><p>Tinjau rincian pesanan, pilih pembayaran, dan ikuti instruksi yang ditampilkan.</p></article>
                <article><span>04</span><h3>Pantau pesanan</h3><p>Lihat status, bukti transaksi, dan detail pesanan dari dashboard pelanggan.</p></article>
            </div>
            <div class="profile-observation-note">
                <span aria-hidden="true">i</span>
                <p>Pelanggan juga dapat menghubungi BENBEN CO. melalui WhatsApp jika membutuhkan informasi tambahan.</p>
            </div>
        </div>
    </section>

    <section class="section-cream profile-about-roadmap">
        <div class="container-bb profile-roadmap-grid">
            <div class="profile-roadmap-copy">
                <span class="profile-eyebrow"><span></span> Layanan digital BENBEN CO.</span>
                <h2>Informasi dan operasional,<br><em>terhubung dalam satu sistem.</em></h2>
                <p>
                    Website ini memperkenalkan usaha sekaligus menyediakan alur transaksi pelanggan dan alat bantu operasional untuk staf.
                </p>
                <a href="{{ route('home') }}#produk" class="profile-text-link">Lihat produk BENBEN CO. <span aria-hidden="true">↗</span></a>
            </div>
            <div class="profile-roadmap-list">
                <article class="profile-roadmap-item"><span>01</span><div><small>INFORMASI PUBLIK</small><h3>Company profile &amp; katalog</h3><p>Cerita usaha, detail produk, promo, ulasan, kontak, dan lokasi bakery.</p></div><b>Tersedia</b></article>
                <article class="profile-roadmap-item"><span>02</span><div><small>PELANGGAN</small><h3>Pemesanan &amp; status</h3><p>Cart, checkout, pembayaran, bukti transaksi, dan riwayat akun.</p></div><b>Tersedia</b></article>
                <article class="profile-roadmap-item"><span>03</span><div><small>STAF TOKO</small><h3>POS &amp; pengelolaan</h3><p>Transaksi kasir, produk, kategori, stok, promo, dan pembayaran.</p></div><b>Tersedia</b></article>
                <article class="profile-roadmap-item"><span>04</span><div><small>RINGKASAN USAHA</small><h3>Dashboard &amp; laporan</h3><p>Pesanan, penjualan, persediaan, dan data pelanggan yang tersimpan.</p></div><b>Tersedia</b></article>
            </div>
        </div>
    </section>

    <section class="section-paper profile-about-tech">
        <div class="container-bb profile-tech-panel">
            <div>
                <span class="profile-eyebrow"><span></span> Website prototype</span>
                <h2>Teknologi yang<br><em>digunakan pada website.</em></h2>
                <p>Teknologi digunakan untuk company profile, transaksi online, dashboard pelanggan, dan operasional toko.</p>
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
            <div>
                <p>Komp. Sinergi Antapani, Jl. Nyaman No.33B, Antapani Tengah, Kec. Antapani, Kota Bandung, Jawa Barat 40291.</p>
                @if($whatsappAdminNumber)
                    <a href="https://wa.me/{{ $whatsappAdminNumber }}" target="_blank" rel="noopener" class="profile-text-link">WhatsApp +{{ $whatsappAdminNumber }} <span aria-hidden="true">↗</span></a>
                @endif
                <a href="https://www.instagram.com/benbenco.id/" target="_blank" rel="noopener" class="profile-text-link">Instagram @benbenco.id <span aria-hidden="true">↗</span></a>
                <a href="{{ route('contact') }}" class="profile-text-link">Lihat lokasi toko <span aria-hidden="true">↗</span></a>
            </div>
        </div>
    </section>
@endsection
