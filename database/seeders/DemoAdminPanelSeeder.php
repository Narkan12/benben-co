<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\PageContent;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DemoAdminPanelSeeder extends Seeder
{
    private const CUSTOMERS = [
        'nabila' => [
            'nama' => 'Simulasi - Nabila Putri',
            'no_whatsapp' => 'SIMULASI-01',
            'alamat' => 'Alamat simulasi - Jl. Melati, Antapani, Bandung.',
        ],
        'rizky' => [
            'nama' => 'Simulasi - Rizky Ramadhan',
            'no_whatsapp' => 'SIMULASI-02',
            'alamat' => 'Alamat simulasi - Jl. Sukajadi, Sukajadi, Bandung.',
        ],
        'siti' => [
            'nama' => 'Simulasi - Siti Aulia',
            'no_whatsapp' => 'SIMULASI-03',
            'alamat' => 'Alamat simulasi - Jl. Cikutra, Cibeunying Kidul, Bandung.',
        ],
        'fajar' => [
            'nama' => 'Simulasi - Fajar Maulana',
            'no_whatsapp' => 'SIMULASI-04',
            'alamat' => 'Alamat simulasi - Jl. Buahbatu, Buahbatu, Bandung.',
        ],
        'dinda' => [
            'nama' => 'Simulasi - Dinda Maharani',
            'no_whatsapp' => 'SIMULASI-05',
            'alamat' => 'Alamat simulasi - Jl. Cibiru, Cibiru, Bandung.',
        ],
        'rafi' => [
            'nama' => 'Simulasi - Rafi Akbar',
            'no_whatsapp' => 'SIMULASI-06',
            'alamat' => 'Alamat simulasi - Jl. Arcamanik, Arcamanik, Bandung.',
        ],
    ];

    private const ORDERS = [
        [
            'code' => 'BB-SIM00001',
            'customer' => 'nabila',
            'days_ago' => 1,
            'source' => 'online',
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'note' => 'Menunggu pembayaran pelanggan.',
            'items' => [['Cheese Roll', 1], ['Choco Chip Cookies', 1]],
        ],
        [
            'code' => 'BB-SIM00002',
            'customer' => 'rizky',
            'days_ago' => 2,
            'source' => 'online',
            'status' => 'pending',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'note' => 'Pembayaran diterima, menunggu konfirmasi pesanan.',
            'items' => [['Brownies Panggang 64 Sekat', 1]],
        ],
        [
            'code' => 'BB-SIM00003',
            'customer' => 'siti',
            'days_ago' => 4,
            'source' => 'online',
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'note' => 'Pembayaran telah diverifikasi dan pesanan dikonfirmasi.',
            'items' => [['Bolen Pisang', 2]],
        ],
        [
            'code' => 'BB-SIM00004',
            'customer' => 'fajar',
            'days_ago' => 6,
            'source' => 'online',
            'status' => 'processing',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'note' => 'Pesanan sedang disiapkan.',
            'items' => [['Cheese Roll', 1], ['Yoghurt 250 ml', 1]],
        ],
        [
            'code' => 'BB-SIM00005',
            'customer' => 'dinda',
            'days_ago' => 8,
            'source' => 'online',
            'status' => 'ready',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'note' => 'Pesanan siap diambil.',
            'items' => [['Cheese Milkcake', 1]],
        ],
        [
            'code' => 'BB-SIM00006',
            'customer' => 'rafi',
            'days_ago' => 10,
            'source' => 'online',
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'note' => 'Pesanan telah diterima pelanggan.',
            'items' => [['Brownies Panggang 20 Sekat', 1], ['Butter Cookies', 1]],
        ],
        [
            'code' => 'BB-SIM00007',
            'customer' => 'nabila',
            'days_ago' => 15,
            'source' => 'online',
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'note' => 'Pesanan telah diterima pelanggan.',
            'items' => [['Bolen Lilit', 1], ['Yoghurt 1 L', 1]],
        ],
        [
            'code' => 'BB-SIM00008',
            'customer' => 'rizky',
            'days_ago' => 22,
            'source' => 'online',
            'status' => 'cancelled',
            'payment_status' => 'rejected',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'note' => 'Pesanan dibatalkan karena pembayaran ditolak.',
            'items' => [['Brownies Lumer', 1]],
        ],
        [
            'code' => 'BB-SIM00009',
            'customer' => 'siti',
            'days_ago' => 35,
            'source' => 'online',
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'note' => 'Pesanan telah diterima pelanggan.',
            'items' => [['Cheese Roll', 1], ['Choco Chip Cookies', 1]],
        ],
        [
            'code' => 'BB-POS-SIM001',
            'customer' => 'fajar',
            'days_ago' => 1,
            'source' => 'pos',
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'note' => 'Transaksi kasir tunai telah selesai.',
            'items' => [['Cheese Roll', 1], ['Brownies Panggang 64 Sekat', 1]],
        ],
        [
            'code' => 'BB-POS-SIM002',
            'customer' => 'dinda',
            'days_ago' => 7,
            'source' => 'pos',
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'note' => 'Transaksi kasir E-Wallet telah selesai.',
            'items' => [['Brownies Lumer', 2]],
        ],
    ];

    private const PAGES = [
        'home' => [
            'eyebrow' => 'Beranda BENBEN CO.',
            'title' => 'Cerita Manis dari Dapur BENBEN CO.',
            'description' => 'Pilih cheese roll, brownies, bolen pisang, cookies, dan minuman sesuai katalog yang tersedia. Sesuaikan konten ini sebelum dipublikasikan.',
        ],
        'about' => [
            'eyebrow' => 'Tentang BENBEN CO.',
            'title' => 'Cerita dari Dapur BENBEN CO.',
            'description' => 'Perkenalkan cerita dan perjalanan BENBEN CO. Periksa kembali setiap informasi sebelum halaman dipublikasikan.',
        ],
        'favorites' => [
            'eyebrow' => 'Menu Pilihan',
            'title' => 'Kenali Produk Bakery Kami.',
            'description' => 'Sesuaikan pengantar menu pilihan dengan katalog produk yang tersedia.',
        ],
        'promotions' => [
            'eyebrow' => 'Informasi Promo',
            'title' => 'Penawaran BENBEN CO.',
            'description' => 'Perbarui informasi sesuai promo resmi yang aktif sebelum halaman dipublikasikan.',
        ],
        'contact' => [
            'eyebrow' => 'Informasi Kontak',
            'title' => 'Hubungi BENBEN CO.',
            'description' => 'Tambahkan alamat dan kanal kontak resmi bakery sebelum halaman dipublikasikan.',
        ],
    ];

    public function run(): void
    {
        $this->assertSafeTarget();

        DB::transaction(function (): void {
            $this->seedCatalogWhenEmpty();
            $products = Product::query()->where('status', 'active')->orderBy('id_product')->get();

            if ($products->isEmpty()) {
                throw new RuntimeException('Seeder simulasi memerlukan minimal satu produk aktif. Katalog yang sudah ada tidak diubah.');
            }

            $customers = $this->seedCustomers();
            $ratingCandidates = [];

            foreach (self::ORDERS as $orderIndex => $definition) {
                $existingOrder = Order::query()->where('kode_order', $definition['code'])->first();
                if ($existingOrder) {
                    if (! str_starts_with((string) $existingOrder->catatan, '[SIMULASI]')) {
                        throw new RuntimeException('Nomor transaksi '.$definition['code'].' telah dipakai oleh data non-simulasi.');
                    }

                    continue;
                }

                $items = [];
                $subtotal = 0;
                foreach ($definition['items'] as $itemIndex => [$productName, $quantity]) {
                    $product = $products->firstWhere('nama', $productName)
                        ?? $products->get(($orderIndex + $itemIndex) % $products->count());
                    $lineSubtotal = round((float) $product->harga * $quantity, 2);
                    $subtotal += $lineSubtotal;
                    $items[] = [
                        'product' => $product,
                        'quantity' => $quantity,
                        'subtotal' => $lineSubtotal,
                    ];
                }

                $delivery = (float) $definition['delivery'];
                $discount = 0;
                $total = round($subtotal + $delivery - $discount, 2);
                $orderDate = now()->subDays($definition['days_ago'])->setTime(12, 0);
                $cashReceived = $definition['source'] === 'pos' && $definition['payment_method'] === 'cash'
                    ? (int) (ceil($total / 50000) * 50000)
                    : null;

                $order = Order::query()->create([
                    'kode_order' => $definition['code'],
                    'customer_id' => $customers[$definition['customer']]->id_customer,
                    'tanggal' => $orderDate,
                    'subtotal' => $subtotal,
                    'delivery' => $delivery,
                    'diskon' => $discount,
                    'total' => $total,
                    'status' => $definition['status'],
                    'payment_method' => $definition['payment_method'],
                    'payment_status' => $definition['payment_status'],
                    'cash_received' => $cashReceived,
                    'cash_change' => $cashReceived === null ? null : $cashReceived - $total,
                    'delivery_method' => $definition['delivery_method'],
                    'catatan' => '[SIMULASI] '.$definition['note'],
                    'source' => $definition['source'],
                ]);

                foreach ($items as $item) {
                    OrderDetail::query()->create([
                        'order_id' => $order->id_order,
                        'product_id' => $item['product']->id_product,
                        'harga' => $item['product']->harga,
                        'qty' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                    ]);
                }

                $this->createStatusHistory($order, $definition, $orderDate);

                if ($definition['source'] === 'online' && $definition['status'] === 'completed') {
                    $ratingCandidates[] = [
                        'customer' => $customers[$definition['customer']],
                        'product' => $items[0]['product'],
                        'created_at' => $orderDate->copy()->addDay(),
                    ];
                }
            }

            $this->seedRatings($ratingCandidates);
            $this->seedPageContents();
        });
    }

    private function assertSafeTarget(): void
    {
        if (! in_array(config('app.env'), ['local', 'testing'], true)) {
            throw new RuntimeException('Seeder simulasi hanya dapat dijalankan pada lingkungan local atau testing.');
        }

        $connection = DB::getDefaultConnection();
        if ($connection === 'mysql') {
            $host = (string) config('database.connections.mysql.host');
            if (! in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
                throw new RuntimeException('Seeder simulasi menolak koneksi MySQL non-lokal.');
            }

            return;
        }

        if ($connection !== 'sqlite') {
            throw new RuntimeException('Seeder simulasi hanya mendukung SQLite atau MySQL lokal.');
        }
    }

    private function seedCatalogWhenEmpty(): void
    {
        if (Product::query()->exists()) {
            return;
        }

        $categories = [
            'brownies' => 'Brownies',
            'cheese-roll' => 'Cheese Roll',
            'bolen-pisang' => 'Bolen Pisang',
            'cookies' => 'Cookies',
            'drinks' => 'Minuman',
        ];
        $categoryIds = [];

        foreach ($categories as $slug => $name) {
            $categoryIds[$slug] = Category::query()->firstOrCreate(
                ['slug' => $slug],
                ['nama_kategori' => $name]
            )->id_category;
        }

        $products = [
            ['nama' => 'Cheese Roll (Simulasi)', 'category' => 'cheese-roll', 'harga' => 45000, 'stok' => 14],
            ['nama' => 'Brownies Panggang 64 Sekat (Simulasi)', 'category' => 'brownies', 'harga' => 38000, 'stok' => 4],
            ['nama' => 'Bolen Pisang (Simulasi)', 'category' => 'bolen-pisang', 'harga' => 38000, 'stok' => 0],
            ['nama' => 'Choco Chip Cookies (Simulasi)', 'category' => 'cookies', 'harga' => 32000, 'stok' => 18],
            ['nama' => 'Yoghurt 250 ml (Simulasi)', 'category' => 'drinks', 'harga' => 30000, 'stok' => 3],
        ];
        $createdAt = now()->subDays(90);

        foreach ($products as $definition) {
            $product = new Product([
                'category_id' => $categoryIds[$definition['category']],
                'nama' => $definition['nama'],
                'deskripsi' => 'Produk contoh untuk simulasi Panel Admin; periksa katalog sebelum digunakan.',
                'harga' => $definition['harga'],
                'gambar' => null,
                'stok' => $definition['stok'],
                'status' => 'active',
            ]);
            $product->created_at = $createdAt;
            $product->updated_at = $createdAt;
            $product->save();
        }
    }

    private function seedCustomers(): array
    {
        $customers = [];

        foreach (self::CUSTOMERS as $key => $definition) {
            $customer = Customer::query()->where('no_whatsapp', $definition['no_whatsapp'])->first();
            if ($customer && $customer->nama !== $definition['nama']) {
                throw new RuntimeException('Nomor simulasi pelanggan '.$definition['no_whatsapp'].' telah dipakai oleh data lain.');
            }

            if (! $customer) {
                $customer = new Customer($definition);
                $customer->created_at = now()->subDays(60);
                $customer->save();
            }

            $customers[$key] = $customer;
        }

        return $customers;
    }

    private function createStatusHistory(Order $order, array $definition, Carbon $orderDate): void
    {
        $steps = match (true) {
            $definition['source'] === 'pos' => ['completed'],
            $definition['status'] === 'pending' => ['pending'],
            $definition['status'] === 'confirmed' => ['pending', 'confirmed'],
            $definition['status'] === 'processing' => ['pending', 'confirmed', 'processing'],
            $definition['status'] === 'ready' => ['pending', 'confirmed', 'processing', 'ready'],
            $definition['status'] === 'completed' => ['pending', 'confirmed', 'processing', 'ready', 'completed'],
            $definition['status'] === 'cancelled' => ['pending', 'cancelled'],
        };
        $previousStatus = null;

        foreach ($steps as $index => $status) {
            OrderStatusHistory::query()->create([
                'order_id' => $order->id_order,
                'from_status' => $previousStatus,
                'to_status' => $status,
                'changed_by' => null,
                'source' => 'simulasi',
                'changed_at' => $orderDate->copy()->addMinutes($index * 20),
            ]);
            $previousStatus = $status;
        }
    }

    private function seedRatings(array $candidates): void
    {
        $comments = [
            'Data simulasi: teksturnya lembut dan rasanya seimbang.',
            'Data simulasi: cocok dinikmati bersama keluarga.',
            'Data simulasi: contoh ulasan untuk pengujian tampilan.',
        ];

        foreach ($candidates as $index => $candidate) {
            $name = $candidate['customer']->nama;
            $productId = $candidate['product']->id_product;
            $comment = $comments[$index % count($comments)];
            $exists = DB::table('ratings')
                ->where('product_id', $productId)
                ->where('nama_customer', $name)
                ->where('komentar', $comment)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('ratings')->insert([
                'product_id' => $productId,
                'nama_customer' => $name,
                'rating' => [5, 4, 5][$index % 3],
                'komentar' => $comment,
                'created_at' => $candidate['created_at'],
            ]);
        }
    }

    private function seedPageContents(): void
    {
        foreach (self::PAGES as $pageKey => $content) {
            PageContent::query()->firstOrCreate(
                ['page_key' => $pageKey],
                [
                    ...$content,
                    'status' => 'draft',
                ]
            );
        }
    }
}
