<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerAccountFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_login_with_email(): void
    {
        $this->post(route('register.submit'), [
            'nama' => 'Pelanggan Uji',
            'username' => 'pelanggan-uji',
            'email' => 'pelanggan@example.com',
            'no_whatsapp' => '08123456789',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseHas('users', [
            'username' => 'pelanggan-uji',
            'email' => 'pelanggan@example.com',
            'role' => 'customer',
        ]);

        $this->post(route('login.submit'), [
            'username' => 'pelanggan@example.com',
            'password' => 'password123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->post(route('logout'))->assertRedirect(route('login'));

        $this->post(route('login.submit'), [
            'username' => 'pelanggan-uji',
            'password' => 'password123',
        ])->assertRedirect(route('customer.dashboard'));
    }

    public function test_duplicate_customer_username_and_email_are_rejected(): void
    {
        User::query()->create([
            'nama' => 'Pelanggan Lama',
            'username' => 'pelanggan-lama',
            'email' => 'lama@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => Customer::query()->create([
                'nama' => 'Pelanggan Lama',
                'no_whatsapp' => '08123456789',
            ])->id_customer,
        ]);

        $this->from(route('register'))
            ->post(route('register.submit'), [
                'nama' => 'Pelanggan Baru',
                'username' => 'pelanggan-lama',
                'email' => 'lama@example.com',
                'no_whatsapp' => '08111111111',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['username', 'email']);
    }

    public function test_customer_cannot_view_another_customers_order(): void
    {
        $owner = Customer::query()->create(['nama' => 'Pemilik', 'no_whatsapp' => '08123456789']);
        $other = Customer::query()->create(['nama' => 'Pelanggan Lain', 'no_whatsapp' => '08987654321']);
        $user = User::query()->create([
            'nama' => 'Pemilik',
            'username' => 'pemilik',
            'email' => 'pemilik@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $owner->id_customer,
        ]);
        $order = Order::query()->create([
            'kode_order' => 'BB-OWNERSHIP',
            'customer_id' => $other->id_customer,
            'tanggal' => now(),
            'status' => 'pending',
            'payment_status' => 'pending',
            'source' => 'online',
        ]);

        $this->actingAs($user)
            ->get(route('customer.order.detail', $order->id_order))
            ->assertNotFound();
    }

    public function test_customer_order_history_shows_guidance_when_there_are_no_orders(): void
    {
        $customer = Customer::query()->create([
            'nama' => 'Pelanggan Baru',
            'no_whatsapp' => '08123456789',
        ]);
        $user = User::query()->create([
            'nama' => 'Pelanggan Baru',
            'username' => 'pelanggan-baru-orders',
            'email' => 'pelanggan-baru-orders@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $customer->id_customer,
        ]);

        $this->actingAs($user)
            ->get(route('customer.orders'))
            ->assertOk()
            ->assertSeeText('Belum ada pesanan')
            ->assertSeeText('Pilih produk')
            ->assertSeeText('Atur pesanan')
            ->assertSeeText('Pantau status')
            ->assertSee(route('products'), false)
            ->assertDontSee('<table class="bb-table-bb">', false);
    }

    public function test_customer_can_confirm_receipt_only_for_own_ready_paid_order(): void
    {
        $customer = Customer::query()->create(['nama' => 'Pemilik', 'no_whatsapp' => '08123456789']);
        $otherCustomer = Customer::query()->create(['nama' => 'Pelanggan Lain', 'no_whatsapp' => '08987654321']);
        $user = User::query()->create([
            'nama' => 'Pemilik',
            'username' => 'pemilik-confirm',
            'email' => 'pemilik-confirm@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $customer->id_customer,
        ]);
        $admin = User::query()->create([
            'nama' => 'Admin Uji',
            'username' => 'admin-confirm',
            'email' => 'admin-confirm@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $ownOrder = Order::query()->create([
            'kode_order' => 'BB-READY-OWN',
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'total' => 50000,
            'status' => 'ready',
            'payment_method' => 'transfer',
            'payment_status' => 'verified',
            'source' => 'online',
        ]);
        $otherOrder = Order::query()->create([
            'kode_order' => 'BB-READY-OTHER',
            'customer_id' => $otherCustomer->id_customer,
            'tanggal' => now(),
            'status' => 'ready',
            'payment_method' => 'transfer',
            'payment_status' => 'verified',
            'source' => 'online',
        ]);
        $unpaidOrder = Order::query()->create([
            'kode_order' => 'BB-READY-UNPAID',
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'status' => 'ready',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'source' => 'online',
        ]);

        $this->actingAs($user)
            ->patch(route('customer.order.confirm-received', $otherOrder->id_order))
            ->assertNotFound();

        $this->patch(route('customer.order.confirm-received', $unpaidOrder->id_order))
            ->assertSessionHasErrors('payment');
        $this->assertDatabaseHas('orders', [
            'id_order' => $unpaidOrder->id_order,
            'status' => 'ready',
            'payment_status' => 'pending',
        ]);

        $this->patch(route('customer.order.confirm-received', $ownOrder->id_order))
            ->assertRedirect(route('customer.order.detail', $ownOrder->id_order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id_order' => $ownOrder->id_order,
            'status' => 'completed',
            'payment_status' => 'verified',
        ]);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $ownOrder->id_order,
            'from_status' => 'ready',
            'to_status' => 'completed',
            'changed_by' => $user->id_user,
            'source' => 'customer_confirmation',
        ]);

        $this->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSeeText('Pesanan Selesai')
            ->assertSeeText('BB-READY-OWN')
            ->assertViewHas('completedOrders', 1)
            ->assertViewHas('processingOrders', 1)
            ->assertViewHas('waitingOrders', 1);

        $this->actingAs($admin)
            ->get(route('admin.orders'))
            ->assertOk()
            ->assertSeeText('BB-READY-OWN')
            ->assertSeeText('Selesai')
            ->assertSeeText('Pemilik');
    }

    public function test_customer_can_submit_payment_proof_for_own_pending_order(): void
    {
        Storage::fake('public');
        $customer = Customer::query()->create(['nama' => 'Pemilik', 'no_whatsapp' => '08123456789']);
        $user = User::query()->create([
            'nama' => 'Pemilik',
            'username' => 'pemilik-payment-proof',
            'email' => 'pemilik-payment-proof@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $customer->id_customer,
        ]);
        $admin = User::query()->create([
            'nama' => 'Admin Uji',
            'username' => 'admin-payment-proof',
            'email' => 'admin-payment-proof@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $order = Order::query()->create([
            'kode_order' => 'BB-PROOF-001',
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'status' => 'pending',
            'payment_method' => 'transfer',
            'payment_status' => 'pending',
            'source' => 'online',
        ]);
        $otherCustomer = Customer::query()->create(['nama' => 'Pelanggan Lain', 'no_whatsapp' => '08987654321']);
        $otherOrder = Order::query()->create([
            'kode_order' => 'BB-PROOF-OTHER',
            'customer_id' => $otherCustomer->id_customer,
            'tanggal' => now(),
            'status' => 'pending',
            'payment_method' => 'transfer',
            'payment_status' => 'pending',
            'source' => 'online',
        ]);
        $imageContent = file_get_contents(public_path('images/hero.jpg'));
        $this->assertNotFalse($imageContent);

        $this->actingAs($user)
            ->patch(route('customer.order.payment-proof', $otherOrder->id_order), [
                'payment_proof' => UploadedFile::fake()->createWithContent('other-receipt.jpg', $imageContent),
            ])
            ->assertNotFound();

        $this->patch(route('customer.order.payment-proof', $order->id_order), [
            'payment_proof' => UploadedFile::fake()->createWithContent('receipt.jpg', $imageContent),
        ])
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertStringStartsWith('payment-proofs/', $order->payment_proof);
        $this->assertTrue(Storage::disk('public')->exists($order->payment_proof));

        $this->actingAs($admin)
            ->patch(route('admin.payments.verify', $order->id_order))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'id_order' => $order->id_order,
            'status' => 'confirmed',
            'payment_status' => 'verified',
        ]);
    }

    public function test_customer_can_view_order_proof_for_own_orders_only(): void
    {
        $owner = Customer::query()->create(['nama' => 'Pemilik', 'no_whatsapp' => '08123456789']);
        $other = Customer::query()->create(['nama' => 'Pelanggan Lain', 'no_whatsapp' => '08987654321']);
        $user = User::query()->create([
            'nama' => 'Pemilik',
            'username' => 'pemilik-proof',
            'email' => 'pemilik-proof@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $owner->id_customer,
        ]);
        $category = Category::query()->create([
            'nama_kategori' => 'Brownies',
            'slug' => 'brownies',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Cokelat',
            'harga' => 25000,
            'stok' => 10,
            'status' => 'active',
            'deskripsi' => 'Brownies untuk uji bukti pemesanan',
        ]);

        $ownOrder = Order::query()->create([
            'kode_order' => 'BB-OWN-ORDER',
            'customer_id' => $owner->id_customer,
            'tanggal' => now(),
            'subtotal' => 50000,
            'delivery' => 0,
            'diskon' => 0,
            'total' => 50000,
            'status' => 'confirmed',
            'payment_method' => 'transfer',
            'payment_status' => 'verified',
            'delivery_method' => 'pickup',
            'source' => 'online',
        ]);
        OrderDetail::query()->create([
            'order_id' => $ownOrder->id_order,
            'product_id' => $product->id_product,
            'harga' => 25000,
            'qty' => 2,
            'subtotal' => 50000,
        ]);

        $otherOrder = Order::query()->create([
            'kode_order' => 'BB-OTHER-ORDER',
            'customer_id' => $other->id_customer,
            'tanggal' => now(),
            'subtotal' => 15000,
            'delivery' => 0,
            'diskon' => 0,
            'total' => 15000,
            'status' => 'pending',
            'payment_method' => 'transfer',
            'payment_status' => 'pending',
            'delivery_method' => 'pickup',
            'source' => 'online',
        ]);

        $this->actingAs($user)
            ->get(route('customer.order.proof', $ownOrder->id_order))
            ->assertOk()
            ->assertSeeText('Bukti Pemesanan')
            ->assertSeeText($ownOrder->kode_order)
            ->assertSeeText($product->nama)
            ->assertSee('margin: 0;', false);

        $this->actingAs($user)
            ->get(route('customer.order.proof', $otherOrder->id_order))
            ->assertNotFound();
    }

    public function test_customer_dashboard_shows_recent_orders_and_summary(): void
    {
        $customer = Customer::query()->create([
            'nama' => 'Pelanggan Dashboard',
            'no_whatsapp' => '08129997777',
            'alamat' => 'Jl. Dashboard No. 1',
        ]);
        $user = User::query()->create([
            'nama' => 'Pelanggan Dashboard',
            'username' => 'dashboard-cust',
            'email' => 'dashboard-cust@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $customer->id_customer,
        ]);
        $category = Category::query()->create([
            'nama_kategori' => 'Kue',
            'slug' => 'kue',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Kue Cokelat',
            'harga' => 25000,
            'stok' => 8,
            'status' => 'active',
            'deskripsi' => 'Kue untuk uji dashboard pelanggan',
        ]);

        $order = Order::query()->create([
            'kode_order' => 'BB-DASH-001',
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'subtotal' => 50000,
            'delivery' => 0,
            'diskon' => 0,
            'total' => 50000,
            'status' => 'processing',
            'payment_method' => 'transfer',
            'payment_status' => 'verified',
            'delivery_method' => 'pickup',
            'source' => 'online',
        ]);
        OrderDetail::query()->create([
            'order_id' => $order->id_order,
            'product_id' => $product->id_product,
            'harga' => 25000,
            'qty' => 2,
            'subtotal' => 50000,
        ]);

        $this->actingAs($user)
            ->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee('data-theme-toggle', false)
            ->assertSeeText('Pesanan terbaru')
            ->assertSeeText('Belanja Anda di BenBen Co.')
            ->assertSeeText('Jelajahi katalog')
            ->assertSeeText($order->kode_order)
            ->assertSee(route('customer.order.detail', $order->id_order), false)
            ->assertSeeText('Pembayaran')
            ->assertSeeText('Diterima')
            ->assertSeeText('Total Pesanan');
    }

    public function test_public_home_explains_customer_login_before_ordering(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSeeText('Masuk untuk memesan')
            ->assertSeeText('Masuk atau daftar')
            ->assertSeeText('Checkout dan bayar')
            ->assertSeeText('Pantau status pesanan');
    }

    public function test_customer_can_update_profile_details(): void
    {
        $customer = Customer::query()->create([
            'nama' => 'Pelanggan Lama',
            'no_whatsapp' => '08123456789',
            'alamat' => 'Jl. Lama No. 1',
        ]);
        $user = User::query()->create([
            'nama' => 'Pelanggan Lama',
            'username' => 'pelanggan-lama',
            'email' => 'lama@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $customer->id_customer,
        ]);

        $this->actingAs($user)
            ->patch(route('customer.profile.update'), [
                'nama' => 'Pelanggan Baru',
                'email' => 'baru@example.com',
                'no_whatsapp' => '08129998888',
                'alamat' => 'Jl. Baru No. 99',
            ])
            ->assertRedirect(route('customer.profile'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('customers', [
            'id_customer' => $customer->id_customer,
            'nama' => 'Pelanggan Baru',
            'no_whatsapp' => '08129998888',
            'alamat' => 'Jl. Baru No. 99',
        ]);

        $this->assertDatabaseHas('users', [
            'id_user' => $user->id_user,
            'nama' => 'Pelanggan Baru',
            'email' => 'baru@example.com',
        ]);
    }

    public function test_profile_validation_does_not_create_orphan_customer_and_repairs_missing_link(): void
    {
        $user = User::query()->create([
            'nama' => 'Profil Belum Terhubung',
            'username' => 'profil-belum-terhubung',
            'email' => 'profil-belum-terhubung@example.com',
            'password' => 'password123',
            'role' => 'customer',
        ]);

        $this->actingAs($user)
            ->from(route('customer.profile'))
            ->patch(route('customer.profile.update'), [
                'nama' => '',
                'email' => 'email-tidak-valid',
                'no_whatsapp' => '',
            ])
            ->assertRedirect(route('customer.profile'))
            ->assertSessionHasErrors(['nama', 'email', 'no_whatsapp']);

        $this->assertDatabaseCount('customers', 0);

        $this->patch(route('customer.profile.update'), [
            'nama' => 'Profil Terhubung',
            'email' => 'profil-terhubung@example.com',
            'no_whatsapp' => '08123456789',
            'alamat' => 'Jl. Contoh',
        ])->assertRedirect(route('customer.profile'));

        $this->assertNotNull($user->fresh()->customer_id);
        $this->assertDatabaseHas('customers', [
            'id_customer' => $user->fresh()->customer_id,
            'nama' => 'Profil Terhubung',
        ]);
    }

    public function test_admin_pos_receipt_uses_saved_transaction_data(): void
    {
        config(['services.whatsapp.admin_number' => '+62 813-9494-009']);

        $admin = User::query()->create([
            'nama' => 'Admin Uji',
            'username' => 'admin-pos',
            'email' => 'admin-pos@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $category = Category::query()->create([
            'nama_kategori' => 'Kue',
            'slug' => 'kue',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Kue Keju',
            'harga' => 30000,
            'stok' => 15,
            'status' => 'active',
            'deskripsi' => 'Kue keju untuk presensi POS',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.pos'))
            ->assertOk()
            ->assertSee('data-money-input', false)
            ->assertSee('Nominal cepat')
            ->assertSee('data-pos-payment-presets', false)
            ->assertSee('name="checkout_token"', false)
            ->assertSee('js/pos.js', false);

        $before = Order::query()->count();
        $transactionToken = (string) Str::uuid();
        $transactionData = [
            'checkout_token' => $transactionToken,
            'nama' => 'Kasir POS',
            'no_whatsapp' => '081122334455',
            'payment_method' => 'cash',
            'amount_received' => 60000,
            'items' => [
                ['product_id' => $product->id_product, 'qty' => 2],
            ],
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.pos.store'), $transactionData);

        $response->assertSessionHas('success');
        $this->assertEquals($before + 1, Order::query()->count());

        $createdOrder = Order::query()->latest('id_order')->first();
        $this->actingAs($admin)
            ->post(route('admin.pos.store'), $transactionData)
            ->assertRedirect(route('admin.pos.receipt', ['order' => $createdOrder->id_order]));

        $this->assertEquals($before + 1, Order::query()->count());
        $this->assertDatabaseCount('stock_logs', 1);
        $this->assertDatabaseHas('orders', ['checkout_token' => $transactionToken]);
        $this->assertEquals(60000, $createdOrder->cash_received);
        $this->assertEquals(0, $createdOrder->cash_change);
        $this->assertEquals(13, $product->fresh()->stok);

        $longReceiptProductNames = [];
        for ($item = 1; $item <= 8; $item++) {
            $name = 'Paket Roti Keju Susu Manis untuk Dibagikan Nomor '.$item;
            $longReceiptProductNames[] = $name;
            $longReceiptProduct = Product::query()->create([
                'category_id' => $category->id_category,
                'nama' => $name,
                'harga' => 10000,
                'stok' => 5,
                'status' => 'active',
            ]);

            OrderDetail::query()->create([
                'order_id' => $createdOrder->id_order,
                'product_id' => $longReceiptProduct->id_product,
                'harga' => 10000,
                'qty' => 1,
                'subtotal' => 10000,
            ]);
        }

        $receiptResponse = $this->actingAs($admin)
            ->get(route('admin.pos.receipt', $createdOrder->id_order))
            ->assertOk()
            ->assertSeeText('BENBEN CO.')
            ->assertSeeText($createdOrder->kode_order)
            ->assertSeeText('Antapani, Kota Bandung')
            ->assertSeeText('Headers and footers')
            ->assertSeeText($product->nama)
            ->assertSeeText('Tunai')
            ->assertSeeText('Telp: +628139494009')
            ->assertSeeText('2 × Rp30.000 / unit')
            ->assertSeeText('Total pembayaran')
            ->assertSeeText('Uang diterima')
            ->assertSeeText('Uang kembalian')
            ->assertSee('data-print-receipt', false)
            ->assertSee('receipt-paper-width', false)
            ->assertSee('value="80"', false)
            ->assertSee('value="58"', false)
            ->assertSee('size: 80mm 180mm', false)
            ->assertSee('body * {', false)
            ->assertSee('.printable-receipt,', false)
            ->assertSee('visibility: hidden !important;', false)
            ->assertDontSee('<nav class="bb-navbar">', false)
            ->assertDontSee('<footer class="bb-footer">', false)
            ->assertDontSee('bb-admin-shell', false)
            ->assertSee('js/receipt.js', false);

        foreach ($longReceiptProductNames as $productName) {
            $receiptResponse->assertSeeText($productName);
        }
    }

    public function test_pos_rejects_thousand_separators_in_amount_received(): void
    {
        $admin = User::query()->create([
            'nama' => 'Admin Uji',
            'username' => 'admin-pos-invalid-amount',
            'email' => 'admin-pos-invalid-amount@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $this->actingAs($admin)
            ->post(route('admin.pos.store'), [
                'nama' => 'Kasir POS',
                'payment_method' => 'cash',
                'checkout_token' => (string) Str::uuid(),
                'amount_received' => '60.000',
                'items' => [['product_id' => 1, 'qty' => 1]],
            ])
            ->assertSessionHasErrors('amount_received');
    }

    public function test_admin_product_price_accepts_rupiah_formatted_thousands(): void
    {
        $admin = User::query()->create([
            'nama' => 'Admin Uji',
            'username' => 'admin-price-validation',
            'email' => 'admin-price-validation@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $category = Category::query()->create([
            'nama_kategori' => 'Kue',
            'slug' => 'kue-validasi-harga',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.products'))
            ->assertOk()
            ->assertSee('input-group-text', false)
            ->assertSee('56.000');

        $this->actingAs($admin)
            ->post(route('admin.products.store'), [
                'nama' => 'Produk Harga Terformat',
                'category_id' => $category->id_category,
                'harga' => 'Rp 15.000',
                'stok' => '10',
                'status' => 'active',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'nama' => 'Produk Harga Terformat',
            'harga' => 15000,
        ]);
    }

    public function test_admin_users_page_formats_user_registration_date(): void
    {
        $admin = User::query()->create([
            'nama' => 'Admin Pengelola',
            'username' => 'admin-user-list',
            'email' => 'admin-user-list@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $staff = User::query()->create([
            'nama' => 'Kasir Pengelola',
            'username' => 'kasir-user-list',
            'email' => 'kasir-user-list@example.com',
            'password' => 'password123',
            'role' => 'kasir',
        ]);
        $staff->forceFill(['created_at' => '2026-11-11 12:00:00'])->save();

        $this->actingAs($admin)
            ->get(route('admin.users'))
            ->assertOk()
            ->assertSeeText('11 Nov 2026');
    }
}
