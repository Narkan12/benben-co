# BENBEN CO. Bakery

## 1. Tentang Project

BENBEN CO. Bakery adalah prototype toko bakery dengan Laravel yang dibuat sebagai bahan belajar. Tujuannya membantu siswa memahami alur request dari URL, controller, model/database, sampai halaman Blade. Customer dapat melihat produk, membuat pesanan tanpa login, lalu mengonfirmasi pesanan melalui WhatsApp. Admin mengelola toko, sementara admin dan kasir dapat menggunakan POS.

Kode sengaja memakai struktur Laravel standar dan logika langsung di controller agar mudah diikuti serta direfactor.

## 2. Teknologi

- Laravel 12 dan PHP 8.3+
- MySQL
- Blade dan Eloquent ORM
- Bootstrap 5 melalui CDN
- CSS tambahan di `public/css/`
- JavaScript Vanilla di `public/js/`

## 3. Struktur Folder

| Folder/file | Fungsi |
| --- | --- |
| `app/Models/` | Model Eloquent dan relasi tabel |
| `app/Http/Controllers/` | Logika halaman publik, cart, checkout, admin, dan POS |
| `app/Http/Middleware/` | Pemeriksaan akses admin dan kasir |
| `routes/web.php` | Daftar URL dan controller tujuan |
| `database/migrations/` | Struktur tabel dan perubahan database |
| `database/seeders/` | Akun demo, kategori, produk, serta promo contoh |
| `resources/views/layouts/` | Kerangka halaman yang digunakan ulang |
| `resources/views/components/` | Navbar, footer, alert, pagination, dan kartu produk |
| `resources/views/` | Halaman publik, admin, customer, dan POS |
| `storage/app/public/images/` | Logo dan gambar hero/produk yang ditampilkan lewat controller |
| `public/css/` | Gaya tampilan dan responsive |
| `public/js/` | Interaksi di browser |

`vendor/` berisi library Composer. Jangan mengedit file di dalamnya.

## 4. Alur Aplikasi

**Customer:** Home → Products → Detail → Cart → Checkout → WhatsApp. Cart customer disimpan di session Laravel. Customer tidak harus mempunyai akun. Customer yang memiliki akun dapat login, melihat dashboard, riwayat, detail, dan histori status pesanan.

**Admin/kasir:** Login → Dashboard → CRUD Produk / Orders / POS. Admin juga dapat mengelola kategori, stok, promo, pembayaran, pelanggan, dan laporan.

## 5. Penjelasan Routing

Semua route web terdapat di `routes/web.php`. Contoh:

```php
Route::get('/', [PublicStoreController::class, 'home'])->name('home');
```

Route tersebut menerima request GET untuk `/`, lalu menjalankan method `home()` di `PublicStoreController`. Nama `home` dapat dipakai di Blade lewat `route('home')`.
Untuk gambar, route `media.show` memanggil `StorageImageController@show` dan mengambil file dari disk `public` Laravel, yaitu `storage/app/public`.

| URL | Method | Tujuan |
| --- | --- | --- |
| `/` | GET | Beranda dan produk pilihan |
| `/about` | GET | Cerita toko |
| `/products` | GET | Katalog dengan query `search` dan `category` |
| `/products/{slug}` | GET | Detail produk |
| `/products/{product}/review` | POST | Menyimpan rating |
| `/promotions` | GET | Promo aktif |
| `/cart` | GET | Isi cart session |
| `/cart/add` | POST | Tambah produk |
| `/cart/{productId}/update` | POST | Ubah jumlah produk |
| `/cart/{productId}` | DELETE | Hapus produk |
| `/checkout` | GET/POST | Form checkout dan penyimpanan pesanan |
| `/checkout/success/{code}` | GET | Ringkasan order dan tautan WhatsApp |
| `/contact` | GET | Kontak dan Google Maps |
| `/media/{path}` | GET | Mengirim gambar dari `storage/app/public` melalui `StorageImageController` |
| `/login` | GET/POST | Login customer menggunakan username atau email |
| `/register` | GET/POST | Registrasi akun customer dengan validasi username dan email unik |
| `/admin/login` | GET/POST | Halaman khusus login admin/kasir tanpa layout customer |
| `/admin/dashboard` | GET | Ringkasan penjualan, order, produk, stok |
| `/admin/order-notifications` | GET | Polling daftar pesanan online baru, khusus admin |
| `/admin/order-notifications/seen` | POST | Menandai notifikasi order yang sudah ditampilkan, khusus admin |
| `/admin/order-notifications/toggle` | POST | Mengaktifkan atau mematikan polling notifikasi, khusus admin |
| `/admin/settings` | GET/PATCH | Melihat dan mengubah nama atau password akun sendiri |
| `/admin/products` | GET/POST/PUT/DELETE | CRUD produk |
| `/admin/ratings` | GET | Melihat, mencari, dan memfilter rating serta ulasan pelanggan |
| `/admin/orders` | GET | Melihat pesanan |
| `/admin/orders/{orderId}/status` | PATCH | Mengubah status pesanan |
| `/admin/orders/{orderId}/receive-cash` | PATCH | Menandai pembayaran tunai pesanan siap sebagai diterima |
| `/admin/reports` | GET | Laporan penjualan berdasarkan rentang tanggal |
| `/admin/reports/excel` | GET | Ekspor ringkasan dan detail transaksi ke CSV yang dapat dibuka di Excel |
| `/admin/pos` | GET/POST | Halaman kasir dan penyimpanan transaksi |
| `/admin/pos/{order}/receipt` | GET | Menampilkan struk transaksi POS yang sudah tersimpan dan siap dicetak |
| `/customer/orders/{id}/proof` | GET | Menampilkan bukti pesanan customer untuk order miliknya yang sedang login |

Route admin lainnya terdaftar di `routes/web.php` dan dilindungi middleware `AdminOnly`. Di sidebar admin, **Data Master** berisi Produk, Kategori, Stok, Pelanggan, Rating, dan Promo; **Transaksi** berisi Kasir POS, Pesanan, Pembayaran, serta Laporan. Dashboard dan Pengaturan ditampilkan sebagai menu tersendiri. Sidebar tetap pada tempatnya ketika halaman digulir; pada layar kecil sidebar dibuka melalui tombol menu.

## 6. Penjelasan Controller

| Controller | Method | Tugas |
| --- | --- | --- |
| `PublicStoreController` | `home()` | Mengambil produk unggulan untuk beranda |
| | `about()` | Menampilkan cerita toko |
| | `products()` | Mencari/filter produk dan menyiapkan pagination |
| | `productDetail()` | Menyiapkan detail, rating, dan produk terkait |
| | `promos()` | Mengambil promo aktif |
| | `cart()` | Membaca cart session untuk tampilan |
| | `success()` | Menampilkan pesanan dan membuat pesan WhatsApp |
| `CartController` | `add()`, `update()`, `remove()`, `clear()` | Mengubah cart session dan memeriksa stok |
| `CheckoutController` | `index()` | Menampilkan form dan ringkasan checkout |
| | `store()` | Memvalidasi dan menyimpan customer/order/detail serta mengurangi stok |
| `StorageImageController` | `show()` | Mengirim gambar produk dan Company Profile dari storage |
| | `paymentProof()` | Mengirim bukti pembayaran kepada staf yang sudah login |
| `AuthController` | `showLogin()`, `login()`, `logout()` | Login dan logout staf |
| `AdminController` | `dashboard()` | Menghitung ringkasan dashboard |
| | `products()`, `saveProduct()`, `updateProduct()`, `deleteProduct()` | CRUD produk |
| | `orders()`, `updateOrderStatus()` | Daftar dan status order |
| | Method pengelolaan lain | Mengelola kategori, stok, promo, laporan, pembayaran, dan akun |
| `POSController` | `index()` | Menampilkan produk dengan stok di halaman kasir |
| | `store()` | Memvalidasi pembayaran, menyimpan transaksi, dan mengurangi stok |
| | `receipt()` | Menampilkan struk transaksi yang tersimpan untuk dicetak ulang tanpa membuat order baru |
| `OrderController` | `index()`, `show()` | Menampilkan pesanan |
| | `proof()` | Menyediakan halaman bukti pemesanan milik customer yang sedang login |
| | `submitReview()` | Memvalidasi dan menyimpan rating |

`$request->validate()` memeriksa data sebelum disimpan. `findOrFail()` mengambil data atau menampilkan respons 404 jika ID tidak ada.

## 7. Penjelasan Model dan Relasi

- `Category` memiliki banyak `Product`.
- `Product` dimiliki satu `Category`, memiliki banyak `Rating` dan `OrderDetail`.
- `Customer` memiliki banyak `Order`.
- `Order` dimiliki satu `Customer`, memiliki banyak `OrderDetail`, dan dapat menggunakan `Promotion`.
- `OrderDetail` menghubungkan satu `Order` dengan satu `Product`.
- `Rating` dimiliki satu `Product`.
- `Promotion` dapat digunakan pada beberapa `Order` lewat foreign key promo yang nullable.
- `User` adalah akun staf dengan role `admin` atau `kasir`.
- `StockLog` mencatat perubahan stok.

Contoh relasi `Product`:

```php
public function category()
{
    return $this->belongsTo(Category::class, 'category_id', 'id_category');
}
```

Foreign key dan primary key ditulis eksplisit karena nama kolom project menggunakan bentuk seperti `id_product`, bukan nama default Laravel.

## 8. Penjelasan Migration

Migration menyimpan riwayat struktur database. `php artisan migrate` menjalankan migration yang belum pernah diterapkan.

| Tabel | Isi |
| --- | --- |
| `users` | Akun admin/kasir dan role |
| `categories` | Nama dan slug kategori |
| `products` | Kategori, nama, deskripsi, harga, gambar, stok, status |
| `customers` | Nama, WhatsApp, alamat (tanpa akun login customer) |
| `orders` | Kode pesanan, total, status, pembayaran, pengiriman, waktu admin melihat notifikasi |
| `order_details` | Produk, harga saat dipesan, jumlah, subtotal |
| `ratings` | Rating, nama pemberi ulasan, komentar |
| `promotions` | Kode, jenis/nilai diskon, tanggal berlaku |
| `company_profile_images` | Gambar Company Profile yang dapat diganti admin dan path gambar bawaan |
| `stock_logs` | Jumlah stok sebelum/sesudah dan keterangan |
| `order_status_histories` | Status awal/perubahan baru, waktu, dan aktor jika tersedia (tidak mengisi histori pesanan lama) |
| `sessions` | Data session bila memakai penyimpanan database |

Migration terpisah menambahkan status/bukti pembayaran pada `orders`. Migration `2026_10_18_100000_add_admin_order_notifications.php` menambahkan preferensi notifikasi pada staf dan kolom penanda notifikasi pesanan yang sudah ditampilkan. Saat migration tersebut diterapkan, order lama ditandai sudah pernah dilihat agar tidak muncul sebagai order baru. Urutan nama migration penting karena tabel dengan foreign key harus dibuat setelah tabel acuannya.

Migration `2026_10_18_123000_create_order_status_histories_table.php` mencatat status awal dan setiap perubahan status pada pesanan baru. Migration ini tidak membuat riwayat palsu untuk pesanan lama. Jalankan `php artisan migrate` untuk menerapkannya; jangan memakai `migrate:fresh` pada database yang berisi data penting.

Migration `2026_10_18_130000_create_company_profile_images_table.php` mendaftarkan gambar bawaan Company Profile. Admin dapat mengganti foto beranda, cerita usaha, kategori, galeri, dan halaman Tentang melalui menu **Data Master → Company Profile**. Gambar baru disimpan pada disk `public` di `company-profile/`; file gambar bawaan tidak diubah.

## 9. Panduan Struk dan Bukti Pemesanan

### Struk transaksi POS

1. Staf login ke panel admin lalu buka `/admin/pos`.
2. Setelah transaksi disimpan, halaman struk akan terbuka secara otomatis melalui route `/admin/pos/{order}/receipt`.
3. Struk menampilkan logo usaha, nomor invoice, data kasir, daftar produk, subtotal, diskon, metode pembayaran, serta uang diterima dan kembalian untuk pembayaran tunai.
4. Tombol **Cetak Struk** menggunakan `window.print()` dan mematikan navigasi serta tombol saat mode cetak aktif.
5. Halaman struk membaca data yang sudah disimpan dari tabel `orders` dan `order_details`, sehingga pencetakan ulang tidak membuat transaksi baru.

### Bukti pemesanan customer

1. Setelah customer memesan melalui checkout atau alur login, sistem akan mengarahkan ke halaman bukti pemesanan milik order yang baru dibuat.
2. Customer dapat melihat bukti ini kembali dari menu **Pesanan Saya** atau route `/customer/orders/{id}/proof`.
3. Bukti pesanan menampilkan nomor pesanan, tanggal, nama pelanggan, daftar produk, total, status, metode pembayaran, serta metode pengambilan atau pengiriman.
4. Tombol **Cetak Bukti Pemesanan** mencetak halaman dalam layout A4 tanpa menampilkan sidebar atau tombol navigasi.
5. Customer hanya dapat melihat order miliknya sendiri. URL dengan ID order orang lain akan menghasilkan 404.

### Dashboard customer

1. Dashboard customer berada pada route `/customer/dashboard`.
2. Halaman menampilkan sapaan pelanggan, ringkasan jumlah pesanan, status menunggu/proses/selesai, serta daftar pesanan terbaru.
3. Jika belum ada pesanan, halaman menampilkan empty state yang jelas dan tombol untuk melihat katalog.
4. Status pesanan mengikuti logika backend yang sama dengan admin sehingga perubahan status langsung terlihat di dashboard dan bukti pesanan.

## 10. Penjelasan Blade

- `resources/views/layouts/app.blade.php`: layout toko, Bootstrap, CSS, navbar, notifikasi, konten, dan footer.
- `resources/views/layouts/admin.blade.php`: layout staf dengan sidebar.
- `resources/views/components/navbar.blade.php` dan `footer.blade.php`: komponen yang digunakan dengan `<x-navbar />` dan `<x-footer />`.
- `resources/views/components/product-card.blade.php`: kartu produk yang digunakan ulang.
- `storage/app/public/images/`: gambar statis. Blade meminta gambar melalui route bernama `media.show`, bukan URL file publik langsung.
- `@yield('content')` menandai tempat halaman turunan mengisi konten lewat `@section('content')`.
- `{{ $value }}` menampilkan data dengan HTML escaping.

Untuk menambah gambar statis, simpan file di `storage/app/public/images/`, lalu gunakan path relatif terhadap disk `public`, misalnya `images/hero.jpg`. Tampilkan di Blade dengan `route('media.show', ['path' => $pathGambar])`. Jangan menambahkan awalan `storage/` atau `public/` pada nilai path tersebut.

## 10. Alur Cart

1. Form tambah produk mengirim POST ke `/cart/add`.
2. `CartController` mengecek ID, jumlah, status aktif, dan stok.
3. ID produk dan jumlah disimpan ke `session('cart')`. JavaScript tidak menyimpan cart customer.
4. Halaman cart mengambil harga/gambar terbaru dari model `Product`.
5. Update/remove mengirim form POST/DELETE.
6. Checkout memeriksa ulang stok sebelum membuat pesanan. POS menggunakan JavaScript untuk tampilan sementara, tetapi semua harga dan stok tetap diperiksa ulang oleh server.

Jumlah stok ditampilkan dengan pemisah ribuan pada kartu produk, detail, keranjang, POS, dan menu pengelolaan stok. Saat produk ditambahkan dari katalog atau detail, permintaan AJAX memperbarui sisa stok dan badge keranjang tanpa memuat ulang halaman. Di halaman detail serta keranjang, sisa perkiraan juga berubah ketika jumlah dipilih. Di POS, sisa stok dan jumlah produk dalam keranjang diperbarui setiap kali produk ditambahkan. Angka ini adalah perkiraan berdasarkan stok saat halaman dibuka; server tetap memeriksa ulang stok saat checkout atau menyimpan transaksi POS.

## 11. Alur Checkout

1. `CheckoutController@index()` membaca session cart dan menampilkan form.
2. Form mengirim nama, WhatsApp, alamat, metode delivery/payment, catatan, dan kode promo.
3. Jika memakai promo, tombol **Konfirmasi Kode Promo** meminta pratinjau dari server berdasarkan harga dan jumlah produk di keranjang. Diskon serta total diperbarui bersama ongkos kirim; perubahan isi kode promo membatalkan pratinjau tersebut.
4. `store()` memvalidasi data form dan memeriksa ulang promo.
5. Di dalam transaksi database, baris promo dan produk dikunci; stok diperiksa, lalu customer, order, detail order, serta log stok dibuat.
6. Semua perubahan database berhasil bersama-sama atau dibatalkan bersama-sama. Stok berkurang hanya saat penyimpanan berhasil.
7. Session cart dihapus dan customer diarahkan ke halaman sukses.

Kode promo dicocokkan tanpa membedakan huruf besar/kecil. Promo contoh `BAKERY20` memberi potongan tetap Rp20.000, bukan 20%; promo nominal memotong nilai tetap hingga maksimal subtotal produk, sedangkan promo persen memotong sesuai persentase subtotal. Ongkos kirim dihitung setelah diskon produk. Promo publik hanya ditampilkan jika statusnya aktif dan tanggal hari ini berada di antara tanggal mulai dan berakhir. Checkout membaca promo dari database, bukan dari harga/diskon kiriman browser. Tabel promo saat ini belum memiliki kuota pemakaian, minimal belanja, atau batas per pelanggan; sistem tidak menerapkan aturan-aturan tersebut.

Saat beranda Company Profile dimuat dan ada promo yang sedang berlaku, modal promo otomatis menampilkan kode, nilai diskon, dan periode pemakaian. Tombol **Salin kode** menyalin kode promo ke clipboard. Batas pemakaian yang ditampilkan mengikuti tanggal mulai dan berakhir promo; karena database belum mengatur kuota atau batas per pelanggan, modal juga menjelaskan bahwa kuota terpisah belum diterapkan.

## 12. Alur WhatsApp

Halaman sukses mengambil order beserta detail produk. Controller menyusun pesan berisi daftar produk, total, nama, alamat, catatan, dan kode pesanan. Pesan di-encode dengan `rawurlencode()` lalu dimasukkan ke link WhatsApp nomor `628139494009`. Customer menekan tombol untuk membuka WhatsApp; browser tidak mengirim pesan tanpa tindakan customer.

## 13. Alur POS

1. Staf masuk dan membuka `/admin/pos`.
2. `pos.js` menampilkan produk, jumlah, total, pembayaran, dan kembalian.
3. Form mengirim ID produk serta jumlah ke `POSController@store`.
4. Server mengambil ulang harga dan stok produk, mengunci barisnya, dan memeriksa jumlah pembayaran.
5. Customer walk-in, order, detail order, dan log stok disimpan dalam transaksi; stok dikurangi di server.

### Rating dan ulasan produk

Pada halaman detail produk, pelanggan dapat memilih rating 1–5 bintang, menuliskan nama, dan memberi komentar opsional. Rating dan ulasan ditampilkan bersama rata-rata dan jumlah ulasan produk. Dashboard admin juga menampilkan rating keseluruhan dan lima ulasan terbaru. Data rating disimpan pada tabel `ratings`; route penyimpanan adalah `product.review`.

### Status, pembayaran, laporan, dan stok

Status pesanan mengikuti alur Menunggu → Dikonfirmasi → Diproses → Siap Diambil → Selesai; pesanan juga dapat dibatalkan dari status yang diizinkan. Transfer dan E-Wallet perlu diverifikasi dari bukti pembayaran sebelum dikonfirmasi. Tunai ditandai diterima ketika admin mencatat pembayaran pada status Siap Diambil, atau otomatis saat pesanan ditandai Selesai. Status pembayaran disimpan terpisah dari status pesanan.

Dashboard dan laporan hanya menghitung penjualan jika `payment_status` bernilai `verified` dan pesanan tidak dibatalkan. Karena itu, pesanan berstatus Siap Diambil belum menjadi penjualan tunai sampai uang diterima dan dicatat. Laporan memisahkan Tunai, Transfer, E-Wallet, metode lama (`other`), dan metode kosong/tidak diketahui. Skema saat ini membatasi metode tersimpan menjadi `cash`, `transfer`, `ewallet`, atau `other`.

Stok dikurangi ketika checkout atau POS berhasil disimpan dan ditambah kembali ketika pesanan yang stoknya sudah dipesan dibatalkan. Tabel stok saat ini menggunakan bilangan bulat dengan nilai minimal nol, sehingga tidak dapat menyimpan nilai stok kosong/null. Daftar stok habis berarti stok bernilai tepat nol, bukan stok rendah. Pembatalan pesanan lama perlu diperiksa hati-hati karena histori stok sebelum mekanisme pencatatan tersedia mungkin tidak lengkap.

### Profil admin/staf dan pemberitahuan pesanan

Di `/admin/settings`, staf yang sedang login dapat mengubah nama dan (opsional) password akunnya sendiri. Username dan role tidak ikut diubah oleh form tersebut. Password kosong berarti password lama tetap dipakai; password baru harus dikonfirmasi.

Admin dapat mengaktifkan atau mematikan pemberitahuan pesanan online di dashboard. Saat aktif, `public/js/admin.js` memeriksa pesanan baru setiap 15 detik, berhenti meminta data saat tab browser tidak aktif, dan menampilkan toast. Route polling dan penanda sudah dilihat hanya menerima role `admin`; kasir tidak mendapat akses. Penanda disimpan di kolom `orders.admin_viewed_at`, jadi order yang sudah diberitahukan tidak muncul berulang kali setelah halaman dimuat ulang. Jumlah pesanan pending tetap tampil terpisah dari notifikasi baru.

Promo yang dibuat lewat menu admin menjadi sumber data untuk `/promotions`, bagian promo di beranda, dan modal promo otomatis pada Company Profile. Perubahan promo di admin otomatis tercermin di halaman publik selama status dan tanggal berlakunya sesuai.

## 14. Cara Menjalankan

Siapkan PHP 8.3+, Composer, dan MySQL.

1. Install dependency: `composer install`
2. Salin `.env.example` menjadi `.env`.
3. Atur koneksi database MySQL di `.env`:

   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=benben_bakery
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. Buat database MySQL `benben_bakery`.
5. Buat application key: `php artisan key:generate`
6. Buat tabel dan data contoh: `php artisan migrate --seed`
7. Saat hosting, arahkan document root web server ke folder `public` Laravel dan pastikan folder `storage/app/public` ikut diunggah bersama aplikasi. Beri PHP izin baca ke gambar dan izin tulis pada `storage/app/public/products` untuk upload admin. Gambar statis tersedia di `storage/app/public/images`. Aplikasi menyajikan gambar melalui `StorageImageController`, jadi tidak memerlukan `php artisan storage:link`.
8. Jalankan `php artisan serve`, lalu buka URL yang ditampilkan (umumnya `http://127.0.0.1:8000`).

`php artisan migrate:fresh --seed` menghapus semua tabel dan datanya terlebih dahulu. Jalankan hanya pada database belajar yang boleh dihapus.

## 15. Akun Demo

Buka `/admin/login`, lalu masukkan username email berikut.

| Role | Username | Password |
| --- | --- | --- |
| Admin | `admin@example.com` | `admin123` |
| Kasir | `kasir@example.com` | `kasir123` |

Akun ini dibuat oleh `DatabaseSeeder`. Ganti password sebelum aplikasi dipakai di luar komputer belajar.

## 16. Cara Refactor

Ikuti alur route → controller → model → view. Jika mengubah satu fitur, telusuri setiap bagian ini agar perubahan tidak memutus alur.

### Edit tampilan produk

- Route: `routes/web.php`, route bernama `products`
- Controller: `PublicStoreController@products`
- Model: `Product`, `Category`, `Rating`
- View: `resources/views/products.blade.php`
- Kartu produk: `resources/views/components/product-card.blade.php`

### Edit beranda

- Route: `routes/web.php`, route bernama `home`
- Controller: `PublicStoreController@home`
- Model: `Product`
- View: `resources/views/home.blade.php`
- Layout/style: `resources/views/layouts/app.blade.php`, `public/css/app.css`

### Ganti gambar Company Profile

- Route: `admin.company-profile`, `admin.company-profile.images.update`
- Controller: `AdminController@companyProfile`, `updateCompanyProfileImage`
- Model/tabel: `CompanyProfileImage`, `company_profile_images`
- View admin: `resources/views/admin/company-profile.blade.php`
- Gambar publik: `resources/views/home.blade.php` dan `resources/views/about.blade.php`
- Admin dapat mengganti gambar hero, cerita, kategori, galeri, dan foto halaman Tentang. Unggahan JPG/PNG/WEBP maksimal 5 MB disimpan pada `storage/app/public/company-profile/`.

### Edit detail produk dan rating

- Route: `product.detail` dan `product.review` di `routes/web.php`
- Controller: `PublicStoreController@productDetail`, `OrderController@submitReview`
- Model: `Product`, `Rating`
- View: `resources/views/product-detail.blade.php`

### Edit aturan cart

- Route: route `cart.*` di `routes/web.php`
- Controller: `CartController`
- Model: `Product`
- View: `resources/views/cart.blade.php`
- Penyimpanan: session Laravel melalui `session('cart')`

### Edit checkout dan WhatsApp

- Route: `checkout`, `checkout.store`, `checkout.success`
- Controller: `CheckoutController@index/store`, `PublicStoreController@success`
- Model: `Customer`, `Order`, `OrderDetail`, `Product`, `Promotion`, `StockLog`
- View: `resources/views/checkout.blade.php`, `resources/views/success.blade.php`

### Edit CRUD produk admin

- Route: `admin.products.*` di `routes/web.php`
- Controller: `AdminController@products`, `saveProduct`, `updateProduct`, `deleteProduct`
- Model: `Product`, `Category`
- View: `resources/views/admin/products.blade.php`
- Script modal: `public/js/admin.js`

### Edit manajemen pesanan

- Route: `admin.orders`, `admin.orders.update-status`
- Controller: `AdminController@orders`, `updateOrderStatus`
- Model: `Order`, `Customer`, `OrderDetail`, `Product`
- View: `resources/views/admin/orders.blade.php`
- Status mengikuti alur: Menunggu → Dikonfirmasi → Diproses → Siap Diambil → Selesai. Pesanan aktif dapat dibatalkan. Pesanan transfer harus diverifikasi dulu sebelum dikonfirmasi.
- Halaman pesanan menampilkan tombol untuk tindakan berikutnya, bukan dropdown status. Verifikasi bukti Transfer/E-Wallet otomatis mengubah status Menunggu menjadi Dikonfirmasi dan mencatat histori. Tahap produksi tetap memerlukan tindakan staf karena sistem tidak dapat mengetahui kapan pesanan benar-benar mulai dibuat atau siap diambil.
- Scheduler `orders:advance-statuses` berjalan setiap lima menit jika `php artisan schedule:work` aktif. Pembayaran terverifikasi otomatis dikonfirmasi; pesanan Dikonfirmasi otomatis masuk Diproses setelah 15 menit; pesanan Diproses otomatis masuk Siap Diambil setelah 60 menit; pesanan delivery yang Siap otomatis selesai setelah 24 jam. Pesanan pickup tidak otomatis diselesaikan karena serah-terima harus benar-benar terjadi, dan pesanan belum dibayar tidak dibatalkan otomatis.
- Setiap tombol hanya menjalankan satu langkah yang diizinkan. Jika request status tidak valid, aplikasi kembali ke halaman pesanan dengan pesan validasi, bukan halaman error HTTP.
- Laporan penjualan dan produk terlaris menghitung pesanan yang kolom `payment_status`-nya terverifikasi, termasuk pesanan berstatus Siap Diambil; pesanan batal dan pembayaran yang belum terverifikasi tidak dihitung meskipun status order selesai. Untuk pesanan tunai online berstatus Siap Diambil, admin harus menekan **Tandai Tunai Diterima** setelah menerima uang. POS menandai tunai diterima saat transaksi disimpan.
- Laporan dapat dicetak dengan layout tema bakery atau disimpan sebagai PDF lewat dialog cetak browser. Tombol Excel mengunduh CSV berisi ringkasan, rincian metode pembayaran, dan ringkasan harian yang bisa dibuka di Microsoft Excel. Filter tanggal aktif diterapkan ke setiap format.
- Laporan juga menampilkan detail setiap transaksi terbayar: kode transaksi, tanggal, nama customer, nomor WhatsApp, rincian produk dan kuantitas, metode pembayaran, serta total. Data detail yang sama ikut tersedia pada ekspor CSV/Excel.
- Status awal dan setiap perubahan baru dicatat pada `order_status_histories`, dengan waktu dan sumber perubahan. Baris histori pesanan lama tidak dibuat ulang karena aplikasi tidak dapat mengetahui perubahan historis yang terjadi sebelum fitur ini ada.
- Metode E-Wallet kini tersimpan sebagai `ewallet` dan tampil terpisah di pesanan/laporan. Nilai `other` adalah metode lain dari data lama, sedangkan `Tidak diketahui` berarti kolom metode pembayaran pada data order lama kosong. Verifikasi pembayaran transfer dan E-Wallet dilakukan pada menu Pembayaran.

### Edit POS

- Route: `admin.pos`, `admin.pos.store`
- Controller: `POSController@index/store`
- Model: `Product`, `Customer`, `Order`, `OrderDetail`, `StockLog`
- View: `resources/views/pos/index.blade.php`
- Script interaksi: `public/js/pos.js`; harga dan stok tetap ditentukan ulang oleh server.

### Edit tabel dan data demo

- Migration: `database/migrations/`
- Model/relasi: `app/Models/`
- Kategori dan produk: `database/seeders/ProductSeeder.php`
- Akun dan promo: `database/seeders/DatabaseSeeder.php`

## Pemeriksaan Dasar

Gunakan `php artisan route:list` untuk memeriksa route. Setelah mengubah Blade, jalankan `php artisan view:cache` untuk memeriksa kompilasi view, kemudian `php artisan view:clear`. Migration dan transaksi perlu database yang sudah dibuat serta konfigurasi `.env` yang benar.

Tes fitur audit profil, notifikasi, promo, dan checkout dapat dijalankan dengan `php vendor/bin/phpunit tests/Feature/AdminDashboardFeaturesTest.php`. `phpunit.xml` mengarahkan tes ke SQLite in-memory agar tidak memakai database pada `.env`.

## 17. Alur Refactor: Profil, Notifikasi, dan Promo

### Ubah profil staf

- Route: `admin.settings` dan `admin.settings.update` di `routes/web.php`
- Controller: `AdminController@settings` dan `AdminController@updateSettings`
- Model: `User`
- View: `resources/views/admin/settings.blade.php`
- Form hanya mengirim nama dan password baru opsional; validasi password memakai konfirmasi.

### Ubah pemberitahuan order baru

- Route: `admin.order-notifications.toggle`, `admin.order-notifications`, dan `admin.order-notifications.seen`
- Controller: `AdminController@toggleOrderNotifications`, `orderNotifications`, `markOrderNotificationsSeen`
- Model: `User` (preferensi) dan `Order` (status sudah dilihat)
- Tampilan: `resources/views/admin/dashboard.blade.php`
- Polling dan toast browser: `public/js/admin.js`
- Migration: `2026_10_18_100000_add_admin_order_notifications.php`

### Arti data stok

- Tabel `products` menyimpan `stok` sebagai integer tidak nullable dengan default `0`. Jadi database saat ini dapat membedakan stok tersedia dan nol, tetapi belum dapat menyimpan stok yang belum diketahui sebagai `NULL`.
- Dashboard menampilkan daftar **Stok Habis** hanya untuk stok tepat `0`. Menu stok membedakan nol, nilai positif, dan `NULL` jika kelak skema mendukungnya. Saat ini jumlah stok yang tidak diketahui tidak ditemukan dan skema tidak mengizinkannya.

### Ubah penayangan dan aturan promo

- Route publik: `home` dan `promotions`
- Controller: `PublicStoreController@home`, `PublicStoreController@promos`
- Model: `Promotion`
- View: `resources/views/home.blade.php`, `resources/views/promotions.blade.php`
- Validasi final pada pemesanan: `CheckoutController@store`
- Pengelolaan promo: route `admin.promotions.*`, method promo di `AdminController`, dan `resources/views/admin/promotions.blade.php`

## 18. Akun Pelanggan dan Riwayat Pesanan

Pelanggan dapat membuat akun di `/register` dan masuk melalui `/login`. Akun pelanggan disimpan pada tabel `users` dengan role `customer` dan terhubung ke satu baris `customers`. Checkout tanpa login tetap tersedia; checkout setelah login akan menyimpan pesanan pada customer yang sedang masuk.

- Dashboard pelanggan: `/customer/dashboard`
- Profil pelanggan: `/customer/profile`
- Riwayat pesanan: `/customer/orders`
- Detail dan histori status: `/customer/orders/{id}`
- Logout: `POST /logout`

Route pelanggan menggunakan middleware `CustomerOnly`. Query riwayat dan detail selalu memfilter `customer_id` dari akun yang sedang masuk, sehingga pelanggan tidak dapat membuka pesanan akun lain. Status yang digunakan aplikasi adalah `pending`, `confirmed`, `processing`, `ready`, `completed`, dan `cancelled`; histori perubahan ditampilkan dari `order_status_histories`.

Jalankan migration setelah mengambil perubahan:

```bash
php artisan migrate
```

Migration `2026_11_02_000000_add_customer_accounts_to_users.php` menambahkan role customer dan relasi `users.customer_id`. Jangan menulis password atau secret ke dokumentasi. Akun staf demo tetap mengikuti seeder yang sudah ada.

Migration `2026_11_02_010000_add_email_to_users_table.php` menambahkan kolom email nullable-unique pada `users` agar akun staff lama tetap dapat dibaca, sementara registrasi customer baru wajib mengisi email unik. Customer dapat masuk menggunakan username atau email pada `/login`; staff tetap menggunakan mekanisme login staff pada `/admin/login`.

Dashboard customer mengambil seluruh ringkasan dari order milik akun aktif, menampilkan total pesanan, pesanan aktif, pesanan menunggu pembayaran/konfirmasi, pesanan selesai, total pembayaran terverifikasi, total produk yang dibeli, dan tanggal pesanan terakhir. Dashboard juga menyediakan akses cepat ke katalog, keranjang, riwayat, profil, dan detail pesanan.

Seluruh halaman dashboard customer memakai layout khusus `resources/views/layouts/customer.blade.php` yang mengikuti gaya minimalis dashboard admin, memiliki sidebar responsif untuk mobile, dan tidak menampilkan navbar/footer publik. Halaman detail menampilkan item, kuantitas, harga, subtotal, total, pembayaran, metode pickup/delivery, dan histori status.

Akses `/customer/orders/{id}` tetap dibatasi query `customer_id`, sehingga mengganti ID URL tidak dapat membuka pesanan customer lain. Status terbaru yang diubah admin atau scheduler akan terlihat saat customer membuka ulang dashboard atau detail pesanan.

## 19. Dashboard Admin dan POS

Dashboard admin `/admin/dashboard` menggunakan data database dan menyediakan filter Hari ini, 7 hari terakhir, Bulan ini, atau rentang tanggal khusus. Pendapatan hanya menjumlahkan order yang `payment_status`-nya `verified` dan tidak berstatus `cancelled`. Dashboard juga menampilkan pesanan diproses, pesanan selesai, stok rendah (maksimal 5), produk terlaris, grafik penjualan, dan pesanan terbaru.

Pada `/admin/pos`, setiap item keranjang memiliki tombol `−`, input jumlah, dan tombol `+`. Jumlah langsung mengubah subtotal dan total, item yang sama digabung, dan jumlah dibatasi stok di browser serta divalidasi ulang di `POSController@store`.

Pemeriksaan setelah instalasi atau migration:

```bash
php artisan route:list
php artisan view:cache
php artisan view:clear
php vendor/bin/phpunit
```

### Otomatisasi status pesanan

Perubahan status otomatis tersedia melalui command `orders:advance-statuses`. Scheduler menerapkan aturan konservatif berikut:

- Pembayaran yang sudah terverifikasi: `Menunggu` → `Dikonfirmasi`.
- Status `Dikonfirmasi` selama minimal 15 menit: → `Diproses`.
- Status `Diproses` selama minimal 60 menit: → `Siap Diambil`.
- Pesanan delivery berstatus `Siap Diambil` selama minimal 24 jam: → `Selesai`.
- Pesanan pickup tidak otomatis menjadi selesai karena sistem tidak dapat memastikan barang sudah diserahkan.
- Pesanan yang belum dibayar tidak dibatalkan otomatis.

Untuk development, jalankan scheduler pada terminal terpisah:

```bash
php artisan schedule:work
```

Untuk menjalankan sekali secara manual:

```bash
php artisan orders:advance-statuses
```

Perubahan otomatis dicatat pada `order_status_histories` dengan sumber `system_automation`. Pastikan scheduler hanya dijalankan satu kali pada setiap environment agar tidak ada proses ganda.
