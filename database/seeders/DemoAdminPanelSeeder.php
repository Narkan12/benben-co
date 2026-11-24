<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\PageContent;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoAdminPanelSeeder extends Seeder
{
    private const CUSTOMERS = [
        'nabila' => [
            'nama' => 'Nabila Putri',
            'no_whatsapp' => '081234567801',
            'alamat' => 'Jl. Melati No. 18, Antapani, Bandung',
            'username' => 'nabila.putri',
            'email' => 'nabila.putri@example.com',
            'legacy_phone' => 'SIMULASI-01',
            'legacy_name' => 'Simulasi - Nabila Putri',
        ],
        'rizky' => [
            'nama' => 'Rizky Ramadhan',
            'no_whatsapp' => '081234567802',
            'alamat' => 'Jl. Sukajadi No. 42, Sukajadi, Bandung',
            'username' => 'rizky.ramadhan',
            'email' => 'rizky.ramadhan@example.com',
            'legacy_phone' => 'SIMULASI-02',
            'legacy_name' => 'Simulasi - Rizky Ramadhan',
        ],
        'siti' => [
            'nama' => 'Siti Aulia',
            'no_whatsapp' => '081234567803',
            'alamat' => 'Jl. Cikutra No. 76, Cibeunying Kidul, Bandung',
            'username' => 'siti.aulia',
            'email' => 'siti.aulia@example.com',
            'legacy_phone' => 'SIMULASI-03',
            'legacy_name' => 'Simulasi - Siti Aulia',
        ],
        'fajar' => [
            'nama' => 'Fajar Maulana',
            'no_whatsapp' => '081234567804',
            'alamat' => 'Jl. Buahbatu No. 25, Buahbatu, Bandung',
            'username' => 'fajar.maulana',
            'email' => 'fajar.maulana@example.com',
            'legacy_phone' => 'SIMULASI-04',
            'legacy_name' => 'Simulasi - Fajar Maulana',
        ],
        'dinda' => [
            'nama' => 'Dinda Maharani',
            'no_whatsapp' => '081234567805',
            'alamat' => 'Jl. Cibiru No. 103, Cibiru, Bandung',
            'username' => 'dinda.maharani',
            'email' => 'dinda.maharani@example.com',
            'legacy_phone' => 'SIMULASI-05',
            'legacy_name' => 'Simulasi - Dinda Maharani',
        ],
        'rafi' => [
            'nama' => 'Rafi Akbar',
            'no_whatsapp' => '081234567806',
            'alamat' => 'Jl. Arcamanik No. 51, Arcamanik, Bandung',
            'username' => 'rafi.akbar',
            'email' => 'rafi.akbar@example.com',
            'legacy_phone' => 'SIMULASI-06',
            'legacy_name' => 'Simulasi - Rafi Akbar',
        ],
    ];

    private const ORDERS = [
        [
            'kode' => 'BB-SIM00001',
            'customer' => 'nabila',
            'products' => ['Cheese Roll' => 1, 'Choco Chip Cookies' => 1],
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'days_ago' => 1,
            'source' => 'online',
            'note' => 'Menunggu pembayaran pelanggan.',
        ],
        [
            'kode' => 'BB-SIM00002',
            'customer' => 'rizky',
            'products' => ['Brownies Panggang 64 Sekat' => 1],
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'days_ago' => 2,
            'source' => 'online',
            'note' => 'Pembayaran diterima dan pesanan dikonfirmasi.',
        ],
        [
            'kode' => 'BB-SIM00003',
            'customer' => 'siti',
            'products' => ['Bolen Pisang' => 2],
            'status' => 'confirmed',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'days_ago' => 4,
            'source' => 'online',
            'note' => 'Pembayaran telah diverifikasi dan pesanan dikonfirmasi.',
        ],
        [
            'kode' => 'BB-SIM00004',
            'customer' => 'fajar',
            'products' => ['Cheese Roll' => 1, 'Yoghurt 250 ml' => 1],
            'status' => 'processing',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'days_ago' => 6,
            'source' => 'online',
            'note' => 'Pesanan sedang disiapkan.',
        ],
        [
            'kode' => 'BB-SIM00005',
            'customer' => 'dinda',
            'products' => ['Cheese Milkcake' => 1],
            'status' => 'ready',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'days_ago' => 8,
            'source' => 'online',
            'note' => 'Pesanan siap diambil.',
        ],
        [
            'kode' => 'BB-SIM00006',
            'customer' => 'rafi',
            'products' => ['Brownies Panggang 20 Sekat' => 1, 'Butter Cookies' => 1],
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'days_ago' => 10,
            'source' => 'online',
            'note' => 'Pesanan telah diterima pelanggan.',
        ],
        [
            'kode' => 'BB-SIM00007',
            'customer' => 'nabila',
            'products' => ['Bolen Lilit' => 1, 'Yoghurt 1 L' => 1],
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'days_ago' => 15,
            'source' => 'online',
            'note' => 'Pesanan telah diterima pelanggan.',
        ],
        [
            'kode' => 'BB-SIM00008',
            'customer' => 'rizky',
            'products' => ['Brownies Lumer' => 1],
            'status' => 'cancelled',
            'payment_status' => 'rejected',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'days_ago' => 22,
            'source' => 'online',
            'note' => 'Pesanan dibatalkan karena pembayaran ditolak.',
        ],
        [
            'kode' => 'BB-SIM00009',
            'customer' => 'siti',
            'products' => ['Cheese Roll' => 1, 'Choco Chip Cookies' => 1],
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'transfer',
            'delivery_method' => 'delivery',
            'delivery' => 15000,
            'days_ago' => 35,
            'source' => 'online',
            'note' => 'Pesanan telah diterima pelanggan.',
        ],
        [
            'kode' => 'BB-POS-SIM001',
            'customer' => 'fajar',
            'products' => ['Cheese Roll' => 1, 'Brownies Panggang 64 Sekat' => 1],
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'days_ago' => 1,
            'source' => 'pos',
            'note' => 'Transaksi kasir tunai telah selesai.',
        ],
        [
            'kode' => 'BB-POS-SIM002',
            'customer' => 'dinda',
            'products' => ['Brownies Lumer' => 2],
            'status' => 'completed',
            'payment_status' => 'verified',
            'payment_method' => 'ewallet',
            'delivery_method' => 'pickup',
            'delivery' => 0,
            'days_ago' => 7,
            'source' => 'pos',
            'note' => 'Transaksi kasir e-wallet telah selesai.',
        ],
    ];

    private const PAGES = [
        'home' => [
            'eyebrow' => 'Beranda BENBEN CO.',
            'title' => 'Kudapan bakery untuk setiap momen',
            'description' => 'Jelajahi cheese roll, brownies, bolen pisang, cookies, dan minuman yang tersedia di katalog BENBEN CO.',
        ],
        'about' => [
            'eyebrow' => 'Tentang BENBEN CO.',
            'title' => 'Cerita dari Dapur BENBEN CO.',
            'description' => 'BENBEN CO. adalah toko bakery rumahan di Antapani, Bandung, yang menyajikan pilihan kudapan untuk dinikmati bersama.',
        ],
        'favorites' => [
            'eyebrow' => 'Menu Pilihan',
            'title' => 'Pilihan bakery untuk dibagikan',
            'description' => 'Lihat pilihan produk bakery dan minuman yang tersedia di katalog.',
        ],
        'promotions' => [
            'eyebrow' => 'Informasi Promo',
            'title' => 'Penawaran BENBEN CO.',
            'description' => 'Periksa promo yang sedang aktif sebelum membuat pesanan.',
        ],
        'contact' => [
            'eyebrow' => 'Informasi Kontak',
            'title' => 'Hubungi BENBEN CO.',
            'description' => 'Gunakan informasi kontak yang ditampilkan pada aplikasi untuk bertanya tentang produk dan pesanan.',
        ],
    ];

    public function run(): void
    {
        if (! $this->demoSeedingAllowed()) {
            $this->command?->warn('DemoAdminPanelSeeder dilewati. Gunakan hanya pada local/testing atau aktifkan BENBEN_DEMO_SEED_ENABLED secara eksplisit.');

            return;
        }

        DB::transaction(function (): void {
            $this->seedCatalog();
            $staff = $this->seedStaffAccounts();
            $customers = $this->seedCustomers();
            $customerAccounts = $this->seedCustomerAccounts($customers);
            $this->seedPromotions();
            $orders = $this->seedOrders($customers);
            $this->seedRatings();
            $this->seedPageContents();
            $this->seedActivityLogs($staff, $customerAccounts, $orders);
        });

        $this->command?->info('Data demonstrasi BENBEN CO. telah disiapkan dengan aman dan konsisten.');
    }

    private function demoSeedingAllowed(): bool
    {
        if (in_array(config('app.env'), ['local', 'testing'], true)) {
            return true;
        }

        return (bool) config('app.demo_seed_enabled');
    }

    /**
     * @return array{admin: User, cashier: User}
     */
    private function seedStaffAccounts(): array
    {
        User::query()->whereIn('username', [
            'nadia.pramesti',
            'aditya.wijaya',
        ])->delete();

        return [
            'admin' => $this->seedUser(
                [
                    'nama' => 'Admin01',
                    'username' => 'admin@example.com',
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                    'customer_id' => null,
                    'password' => 'admin123',
                ]
            ),
            'cashier' => $this->seedUser(
                [
                    'nama' => 'Kasir01',
                    'username' => 'kasir@example.com',
                    'email' => 'kasir@example.com',
                    'role' => 'kasir',
                    'customer_id' => null,
                    'password' => 'kasir123',
                ]
            ),
        ];
    }

    private function seedUser(array $details, ?array $legacy = null): User
    {
        $user = User::query()->where('username', $details['username'])->first();
        $isLegacy = false;

        if (! $user && $legacy) {
            $user = User::query()
                ->where('username', $legacy['username'])
                ->where('nama', $legacy['nama'])
                ->first();
            $isLegacy = (bool) $user;
        }

        if ($user && ! $isLegacy) {
            if ($user->nama !== $details['nama']
                || $user->email !== $details['email']
                || $user->role !== $details['role']
                || (int) $user->customer_id !== (int) ($details['customer_id'] ?? 0)) {
                throw new RuntimeException("Username demo '{$details['username']}' telah digunakan oleh akun lain.");
            }

            $user->password = Hash::make($details['password']);
            $user->save();

            return $user;
        }

        $emailOwner = User::query()->where('email', $details['email'])->first();
        if ($emailOwner && (! $user || $emailOwner->id_user !== $user->id_user)) {
            throw new RuntimeException("Email demo '{$details['email']}' telah digunakan oleh akun lain.");
        }

        $isNew = ! $user;
        $user ??= new User;
        $user->fill([
            'nama' => $details['nama'],
            'username' => $details['username'],
            'email' => $details['email'],
            'role' => $details['role'],
            'customer_id' => $details['customer_id'],
            'password' => Hash::make($details['password']),
        ]);
        if ($isNew) {
            $user->is_active = true;
        }
        $user->save();

        return $user;
    }

    /**
     * @return array<string, Customer>
     */
    private function seedCustomers(): array
    {
        $customers = [];

        foreach (self::CUSTOMERS as $key => $definition) {
            $customer = Customer::query()->where('no_whatsapp', $definition['no_whatsapp'])->first();
            if ($customer && $customer->nama !== $definition['nama']) {
                throw new RuntimeException("Nomor pelanggan demo '{$definition['no_whatsapp']}' telah digunakan oleh data lain.");
            }

            if (! $customer) {
                $legacy = Customer::query()
                    ->where('no_whatsapp', $definition['legacy_phone'])
                    ->first();

                if (! $legacy) {
                    $legacy = Customer::query()
                        ->where('nama', $definition['legacy_name'])
                        ->first();
                }

                if ($legacy) {
                    if ($legacy->nama !== $definition['legacy_name']
                        && $legacy->no_whatsapp !== $definition['legacy_phone']) {
                        throw new RuntimeException("Data pelanggan lama untuk '{$definition['nama']}' tidak cocok.");
                    }

                    $legacy->fill([
                        'nama' => $definition['nama'],
                        'no_whatsapp' => $definition['no_whatsapp'],
                        'alamat' => $definition['alamat'],
                    ]);
                    $legacy->save();
                    $customer = $legacy;
                }
            }

            if (! $customer) {
                $customer = Customer::query()->create([
                    'nama' => $definition['nama'],
                    'no_whatsapp' => $definition['no_whatsapp'],
                    'alamat' => $definition['alamat'],
                ]);
            }

            $customers[$key] = $customer;
        }

        return $customers;
    }

    private function seedCatalog(): void
    {
        $this->call(ProductSeeder::class);
    }

    /**
     * @param  array<string, Customer>  $customers
     * @return array<string, User>
     */
    private function seedCustomerAccounts(array $customers): array
    {
        $accounts = [];

        foreach (self::CUSTOMERS as $key => $definition) {
            $accounts[$key] = $this->seedUser([
                'nama' => $definition['nama'],
                'username' => $definition['username'],
                'email' => $definition['email'],
                'role' => 'customer',
                'customer_id' => $customers[$key]->id_customer,
                'password' => 'BenbenPelanggan!2025',
            ]);
        }

        return $accounts;
    }

    private function seedPromotions(): void
    {
        $now = now();
        $promotions = [
            [
                'kode_promo' => 'HEMAT10',
                'nama_promo' => 'Potongan 10 Persen',
                'jenis_diskon' => 'persen',
                'nilai_diskon' => 10,
                'tanggal_mulai' => $now->copy()->subDays(30)->toDateString(),
                'tanggal_berakhir' => $now->copy()->addDays(90)->toDateString(),
                'status' => 'active',
            ],
            [
                'kode_promo' => 'ROTIBEN10',
                'nama_promo' => 'Potongan Rp10.000',
                'jenis_diskon' => 'nominal',
                'nilai_diskon' => 10000,
                'tanggal_mulai' => $now->copy()->subDays(30)->toDateString(),
                'tanggal_berakhir' => $now->copy()->addDays(90)->toDateString(),
                'status' => 'active',
            ],
        ];

        foreach ($promotions as $promotion) {
            $existing = Promotion::query()->where('kode_promo', $promotion['kode_promo'])->first();

            if ($existing && $existing->nama_promo !== $promotion['nama_promo']) {
                throw new RuntimeException("Kode promo '{$promotion['kode_promo']}' telah digunakan oleh promo lain.");
            }

            Promotion::query()->firstOrCreate(
                ['kode_promo' => $promotion['kode_promo']],
                $promotion
            );
        }
    }

    /**
     * @param  array<string, Customer>  $customers
     * @return array<string, Order>
     */
    private function seedOrders(array $customers): array
    {
        $products = Product::query()->where('status', 'active')->get()->keyBy('nama');
        $orders = [];

        foreach (self::ORDERS as $definition) {
            $attributes = $this->orderAttributes($definition, $customers, $products);
            $order = Order::query()->where('kode_order', $definition['kode'])->first();

            if ($order && ! str_starts_with((string) $order->catatan, '[SIMULASI]')) {
                throw new RuntimeException("Nomor pesanan '{$definition['kode']}' telah digunakan oleh data non-demo.");
            }

            if ($order) {
                foreach (['customer_id', 'status', 'payment_status', 'payment_method', 'delivery_method', 'source'] as $field) {
                    if ((string) $order->{$field} !== (string) $attributes[$field]) {
                        throw new RuntimeException("Pesanan demo '{$definition['kode']}' tidak konsisten pada kolom {$field}.");
                    }
                }

                if ($order->details()->exists()) {
                    $this->assertOrderDetails($order, $definition, $products);
                } else {
                    $this->createOrderDetails($order, $definition, $products);
                }
            } else {
                $order = Order::query()->create($attributes);
                $this->createOrderDetails($order, $definition, $products);
            }

            $this->createStatusHistory($order, $definition);
            $orders[$definition['kode']] = $order;
        }

        return $orders;
    }

    private function orderAttributes(array $definition, array $customers, $products): array
    {
        $subtotal = 0;
        foreach ($definition['products'] as $productName => $quantity) {
            $product = $products->get($productName);
            if (! $product) {
                throw new RuntimeException("Produk '{$productName}' tidak ditemukan atau tidak aktif; transaksi demo tidak dibuat.");
            }

            $subtotal += (float) $product->harga * $quantity;
        }
        $delivery = (float) $definition['delivery'];
        $total = $subtotal + $delivery;
        $cashReceived = $definition['source'] === 'pos' && $definition['payment_method'] === 'cash'
            ? (int) (ceil($total / 50000) * 50000)
            : null;

        return [
            'customer_id' => $customers[$definition['customer']]->id_customer,
            'kode_order' => $definition['kode'],
            'tanggal' => now()->subDays($definition['days_ago'])->setTime(12, 0),
            'subtotal' => $subtotal,
            'diskon' => 0,
            'delivery' => $delivery,
            'total' => $total,
            'status' => $definition['status'],
            'payment_status' => $definition['payment_status'],
            'payment_method' => $definition['payment_method'],
            'cash_received' => $cashReceived,
            'cash_change' => $cashReceived === null ? null : $cashReceived - $total,
            'delivery_method' => $definition['delivery_method'],
            'catatan' => '[SIMULASI] '.$definition['note'],
            'source' => $definition['source'],
        ];
    }

    private function createOrderDetails(Order $order, array $definition, $products): void
    {
        foreach ($definition['products'] as $productName => $quantity) {
            $product = $products->get($productName);
            if (! $product) {
                throw new RuntimeException("Produk '{$productName}' tidak ditemukan; detail pesanan tidak dibuat.");
            }

            OrderDetail::query()->firstOrCreate(
                [
                    'order_id' => $order->id_order,
                    'product_id' => $product->id_product,
                ],
                [
                    'harga' => $product->harga,
                    'qty' => $quantity,
                    'subtotal' => (float) $product->harga * $quantity,
                ]
            );
        }
    }

    private function assertOrderDetails(Order $order, array $definition, $products): void
    {
        $details = $order->details()->get();

        if ($details->count() !== count($definition['products'])) {
            throw new RuntimeException("Detail pesanan demo '{$definition['kode']}' tidak lengkap.");
        }

        foreach ($definition['products'] as $productName => $quantity) {
            $product = $products->get($productName);
            $detail = $product
                ? $details->firstWhere('product_id', $product->id_product)
                : null;

            if (! $detail || (int) $detail->qty !== $quantity) {
                throw new RuntimeException("Detail produk pada pesanan demo '{$definition['kode']}' tidak sesuai.");
            }
        }

        $subtotal = (float) $details->sum('subtotal');
        if (round($subtotal, 2) !== round((float) $order->subtotal, 2)
            || round((float) $order->total, 2) !== round($subtotal + (float) $order->delivery - (float) $order->diskon, 2)) {
            throw new RuntimeException("Perhitungan total pesanan demo '{$definition['kode']}' tidak konsisten.");
        }
    }

    private function createStatusHistory(Order $order, array $definition): void
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
        $orderDate = $order->tanggal->copy();

        foreach ($steps as $index => $status) {
            OrderStatusHistory::query()->firstOrCreate(
                [
                    'order_id' => $order->id_order,
                    'to_status' => $status,
                    'source' => 'simulasi',
                ],
                [
                    'from_status' => $previousStatus,
                    'changed_by' => null,
                    'changed_at' => $orderDate->copy()->addMinutes($index * 20),
                ]
            );
            $previousStatus = $status;
        }
    }

    private function seedRatings(): void
    {
        $comments = [
            'BB-SIM00006' => 'Browniesnya lembut dan cocok dinikmati bersama keluarga.',
            'BB-SIM00007' => 'Bolen pisangnya harum dengan lapisan pastri yang renyah.',
            'BB-SIM00009' => 'Cheese roll terasa gurih dan pas untuk teman minum teh.',
        ];
        $orders = Order::query()
            ->whereIn('kode_order', ['BB-SIM00006', 'BB-SIM00007', 'BB-SIM00009'])
            ->where('status', 'completed')
            ->with(['customer', 'details.product'])
            ->orderBy('kode_order')
            ->get();

        foreach ($orders as $order) {
            $detail = $order->details->first();
            if (! $detail || ! $order->customer) {
                continue;
            }

            $legacyRating = Rating::query()
                ->where('product_id', $detail->product_id)
                ->where('nama_customer', 'Simulasi - '.$order->customer->nama)
                ->first();

            if ($legacyRating) {
                $legacyRating->nama_customer = $order->customer->nama;
                $legacyRating->save();

                continue;
            }

            Rating::query()->firstOrCreate(
                [
                    'product_id' => $detail->product_id,
                    'nama_customer' => $order->customer->nama,
                ],
                [
                    'rating' => 5,
                    'komentar' => $comments[$order->kode_order] ?? 'Produk dinikmati pelanggan.',
                ]
            );
        }
    }

    private function seedPageContents(): void
    {
        foreach (self::PAGES as $pageKey => $page) {
            PageContent::query()->firstOrCreate(
                ['page_key' => $pageKey],
                [
                    ...$page,
                    'status' => 'draft',
                ]
            );
        }
    }

    private function seedActivityLogs(array $staff, array $customerAccounts, array $orders): void
    {
        $activityDefinitions = [
            [
                'user' => $staff['admin'],
                'activity_type' => 'demo_seed',
                'description' => 'Data demonstrasi BENBEN CO. disiapkan.',
                'related_type' => 'User',
                'related_id' => $staff['admin']->id_user,
                'days_ago' => 2,
            ],
            [
                'user' => $staff['cashier'],
                'activity_type' => 'pos_transaction',
                'description' => 'Transaksi kasir demonstrasi '.$orders['BB-POS-SIM001']->kode_order.' tercatat.',
                'related_type' => 'Order',
                'related_id' => $orders['BB-POS-SIM001']->id_order,
                'days_ago' => 1,
            ],
            [
                'user' => $customerAccounts['nabila'],
                'activity_type' => 'order_placed',
                'description' => 'Pesanan demonstrasi '.$orders['BB-SIM00001']->kode_order.' dibuat.',
                'related_type' => 'Order',
                'related_id' => $orders['BB-SIM00001']->id_order,
                'days_ago' => 0,
            ],
        ];

        foreach ($activityDefinitions as $definition) {
            $user = $definition['user'];
            ActivityLog::query()->firstOrCreate(
                [
                    'user_id' => $user->id_user,
                    'activity_type' => $definition['activity_type'],
                    'description' => $definition['description'],
                    'related_type' => $definition['related_type'],
                    'related_id' => $definition['related_id'],
                ],
                [
                    'user_name' => $user->nama,
                    'user_email' => $user->email,
                    'role_name' => $user->role,
                    'status' => 'success',
                    'created_at' => now()->subDays($definition['days_ago']),
                ]
            );
        }
    }
}
