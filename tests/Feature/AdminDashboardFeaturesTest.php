<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CompanyProfileImage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Rating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AdminDashboardFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_shows_active_promo_modal_with_code_and_usage_period(): void
    {
        $promo = Promotion::query()->create([
            'nama_promo' => 'Promo Uji',
            'kode_promo' => 'UJI10',
            'jenis_diskon' => 'persen',
            'nilai_diskon' => 10,
            'tanggal_mulai' => today()->toDateString(),
            'tanggal_berakhir' => today()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('data-promo-modal', false)
            ->assertSee('data-copy-promo="UJI10"', false)
            ->assertSee('Promo Uji')
            ->assertSee($promo->tanggal_mulai->translatedFormat('d M Y'))
            ->assertSee($promo->tanggal_berakhir->translatedFormat('d M Y'))
            ->assertSee('Batas pemakaian mengikuti periode promo di atas.');
    }

    public function test_admin_can_update_name_without_changing_username_or_password(): void
    {
        $admin = $this->createUser();

        $response = $this->actingAs($admin)->patch(route('admin.settings.update'), [
            'nama' => 'Admin Baru',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertRedirect();
        $this->assertSame('Admin Baru', $admin->fresh()->nama);
        $this->assertSame('admin@example.com', $admin->fresh()->username);
        $this->assertTrue(Hash::check('password-lama', $admin->fresh()->password));
    }

    public function test_admin_can_change_password_and_must_confirm_it(): void
    {
        $admin = $this->createUser();

        $this->actingAs($admin)
            ->patch(route('admin.settings.update'), [
                'nama' => $admin->nama,
                'password' => 'password-baru',
                'password_confirmation' => 'password-baru',
            ])
            ->assertRedirect();

        $this->assertTrue(Hash::check('password-baru', $admin->fresh()->password));

        $this->from(route('admin.settings'))
            ->patch(route('admin.settings.update'), [
                'nama' => $admin->nama,
                'password' => 'password-lain',
                'password_confirmation' => 'tidak-sama',
            ])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHasErrors('password');
    }

    public function test_cashier_can_update_only_their_own_profile(): void
    {
        $cashier = $this->createUser('kasir');
        $otherStaff = User::query()->create([
            'nama' => 'Admin Lain',
            'username' => 'admin-lain@example.com',
            'password' => 'password-lama',
            'role' => 'admin',
        ]);

        $this->actingAs($cashier)
            ->patch(route('admin.settings.update'), [
                'nama' => 'Kasir Diperbarui',
            ])
            ->assertRedirect();

        $this->assertSame('Kasir Diperbarui', $cashier->fresh()->nama);
        $this->assertSame('admin-lain@example.com', $otherStaff->fresh()->username);
    }

    public function test_admin_can_update_a_company_profile_image_and_see_it_on_the_home_page(): void
    {
        Storage::fake('public');
        $admin = $this->createUser();
        $image = CompanyProfileImage::query()->where('image_key', 'hero')->firstOrFail();

        $this->actingAs($admin)
            ->put(route('admin.company-profile.images.update', $image->image_key), [
                'gambar' => new UploadedFile(
                    storage_path('app/public/images/hero.jpg'),
                    'hero-baru.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $updatedImage = $image->fresh();
        $this->assertNotNull($updatedImage->image_path);
        Storage::disk('public')->assertExists($updatedImage->image_path);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('media.show', ['path' => $updatedImage->image_path]), false);

        $this->actingAs($admin)
            ->get(route('admin.company-profile'))
            ->assertOk()
            ->assertSee('Gambar Company Profile')
            ->assertSee('Kategori Cheese Roll');
    }

    public function test_cashier_cannot_manage_company_profile_images(): void
    {
        $cashier = $this->createUser('kasir');

        $this->actingAs($cashier)
            ->get(route('admin.company-profile'))
            ->assertForbidden();

        $this->actingAs($cashier)
            ->put(route('admin.company-profile.images.update', 'hero'), [
                'gambar' => new UploadedFile(
                    storage_path('app/public/images/hero.jpg'),
                    'hero-baru.jpg',
                    'image/jpeg',
                    null,
                    true
                ),
            ])
            ->assertForbidden();
    }

    public function test_admin_can_toggle_and_poll_new_online_order_notifications(): void
    {
        $admin = $this->createUser();
        $order = $this->createOnlineOrder();

        $this->actingAs($admin)
            ->getJson(route('admin.order-notifications'))
            ->assertOk()
            ->assertJsonPath('pending_count', 1)
            ->assertJsonPath('orders.0.id', $order->id_order)
            ->assertJsonPath('orders.0.code', $order->kode_order);

        $this->postJson(route('admin.order-notifications.seen'), [
            'order_ids' => [$order->id_order],
        ])->assertOk()->assertJsonPath('saved', true);

        $this->getJson(route('admin.order-notifications'))
            ->assertOk()
            ->assertJsonPath('pending_count', 1)
            ->assertJsonCount(0, 'orders');

        $this->post(route('admin.order-notifications.toggle'), ['enabled' => 0])
            ->assertRedirect();

        $this->getJson(route('admin.order-notifications'))
            ->assertOk()
            ->assertJsonPath('enabled', false)
            ->assertJsonCount(0, 'orders');
    }

    public function test_cashier_cannot_use_admin_order_notification_endpoints(): void
    {
        $cashier = $this->createUser('kasir');

        $this->actingAs($cashier)
            ->getJson(route('admin.order-notifications'))
            ->assertForbidden();

        $this->postJson(route('admin.order-notifications.seen'), ['order_ids' => [1]])
            ->assertForbidden();
    }

    public function test_adding_cart_items_returns_current_remaining_stock_without_exceeding_stock(): void
    {
        $category = Category::query()->create(['nama_kategori' => 'Brownies', 'slug' => 'brownies']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Stok Tes',
            'harga' => 12000,
            'stok' => 3,
            'status' => 'active',
        ]);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id_product,
            'qty' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('quantity', 1)
            ->assertJsonPath('stock_remaining', 2)
            ->assertJsonPath('cart_item_count', 1);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id_product,
            'qty' => 1,
        ])
            ->assertOk()
            ->assertJsonPath('quantity', 2)
            ->assertJsonPath('stock_remaining', 1)
            ->assertJsonPath('cart_item_count', 2);

        $this->postJson(route('cart.add'), [
            'product_id' => $product->id_product,
            'qty' => 2,
        ])->assertUnprocessable();

        $this->assertSame(2, session('cart')[$product->id_product]['qty']);
    }

    public function test_order_status_must_follow_the_workflow_and_invalid_choice_returns_to_orders(): void
    {
        $admin = $this->createUser();
        $order = $this->createOnlineOrder();

        $this->actingAs($admin)
            ->from(route('admin.orders'))
            ->patch(route('admin.orders.update-status', $order->id_order), ['status' => 'processing'])
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);

        foreach (['confirmed', 'processing', 'ready', 'completed'] as $status) {
            $this->patch(route('admin.orders.update-status', $order->id_order), ['status' => $status])
                ->assertRedirect()
                ->assertSessionHasNoErrors();
            $this->assertSame($status, $order->fresh()->status);
        }
        $histories = $order->statusHistories()->get();
        $this->assertCount(5, $histories);
        $this->assertSame('pending', $histories->first()->to_status);
        $this->assertSame('completed', $histories->last()->to_status);
        $this->get(route('admin.orders'))
            ->assertOk()
            ->assertSee('Riwayat status')
            ->assertSee('Admin Demo');

        $this->from(route('admin.orders'))
            ->patch(route('admin.orders.update-status', $order->id_order), ['status' => 'cancelled'])
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHasErrors('status');
    }

    public function test_unverified_transfer_order_cannot_be_confirmed(): void
    {
        $admin = $this->createUser();
        $order = $this->createOnlineOrder();
        $order->update(['payment_method' => 'transfer', 'payment_status' => 'pending']);

        $this->actingAs($admin)
            ->from(route('admin.orders'))
            ->patch(route('admin.orders.update-status', $order->id_order), ['status' => 'confirmed'])
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_verifying_online_payment_automatically_confirms_the_order_and_records_history(): void
    {
        $admin = $this->createUser();
        $order = $this->createOnlineOrder();
        $order->update([
            'payment_method' => 'transfer',
            'payment_status' => 'pending',
            'payment_proof' => 'payment-proofs/proof.png',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.payments.verify', $order->id_order))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('verified', $order->fresh()->payment_status);
        $this->assertSame('confirmed', $order->fresh()->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id_order,
            'from_status' => 'pending',
            'to_status' => 'confirmed',
            'changed_by' => $admin->id_user,
            'source' => 'payment_verification',
        ]);
    }

    public function test_cash_payment_can_be_marked_received_when_order_is_ready(): void
    {
        $admin = $this->createUser();
        $order = $this->createPaidOrder('BB-CASHREADY1', 18000, 'ready', 'cash', 'pending');

        $this->actingAs($admin)
            ->patch(route('admin.orders.receive-cash', $order->id_order))
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame('ready', $order->fresh()->status);
        $this->assertSame('verified', $order->fresh()->payment_status);

        $this->actingAs($admin)
            ->get(route('admin.reports', [
                'start_date' => today()->toDateString(),
                'end_date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Rp18.000')
            ->assertSee('Tunai');
    }

    public function test_cash_payment_cannot_be_marked_received_before_pickup_ready(): void
    {
        $admin = $this->createUser();
        $order = $this->createPaidOrder('BB-CASHPEND02', 18000, 'pending', 'cash', 'pending');

        $this->actingAs($admin)
            ->from(route('admin.orders'))
            ->patch(route('admin.orders.receive-cash', $order->id_order))
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHasErrors('payment');

        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_cancelling_reserved_order_restocks_once_only(): void
    {
        $admin = $this->createUser();
        $category = Category::query()->create(['nama_kategori' => 'Bolen', 'slug' => 'bolen']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Bolen Tes Stok',
            'harga' => 10000,
            'stok' => 3,
            'status' => 'active',
        ]);
        $order = $this->createPaidOrder('BB-CANCELSTOCK', 20000, 'pending', 'cash', 'pending');
        OrderDetail::query()->create([
            'order_id' => $order->id_order,
            'product_id' => $product->id_product,
            'harga' => 10000,
            'qty' => 2,
            'subtotal' => 20000,
        ]);
        $product->update(['stok' => 1]);

        $this->actingAs($admin)
            ->patch(route('admin.orders.update-status', $order->id_order), ['status' => 'cancelled'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $product->fresh()->stok);
        $this->assertSame('cancelled', $order->statusHistories()->get()->last()->to_status);

        $this->from(route('admin.orders'))
            ->patch(route('admin.orders.update-status', $order->id_order), ['status' => 'pending'])
            ->assertRedirect(route('admin.orders'))
            ->assertSessionHasErrors('status');

        $this->assertSame(3, $product->fresh()->stok);
    }

    public function test_dashboard_only_labels_zero_stock_as_out_of_stock(): void
    {
        $admin = $this->createUser();
        $category = Category::query()->create(['nama_kategori' => 'Cookies', 'slug' => 'cookies']);
        Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Cookies Habis',
            'harga' => 10000,
            'stok' => 0,
            'status' => 'active',
        ]);
        Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Cookies Nonaktif',
            'harga' => 10000,
            'stok' => 0,
            'status' => 'inactive',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Stok Habis')
            ->assertSee('Cookies Habis')
            ->assertDontSee('Cookies Nonaktif')
            ->assertDontSee('Stok Menipis');
    }

    public function test_product_detail_displays_customer_reviews_and_accepts_a_new_rating(): void
    {
        $category = Category::query()->create(['nama_kategori' => 'Cheese Roll', 'slug' => 'cheese-roll']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Cheese Roll Ulasan',
            'deskripsi' => 'Pastry keju favorit.',
            'harga' => 25000,
            'stok' => 8,
            'status' => 'active',
        ]);

        Rating::query()->create([
            'product_id' => $product->id_product,
            'nama_customer' => 'Siti Nurhaliza',
            'rating' => 5,
            'komentar' => 'Renyah dan enak.',
        ]);

        $this->get(route('product.detail', $product->slug))
            ->assertOk()
            ->assertSee('Ulasan Pelanggan')
            ->assertSee('Siti Nurhaliza')
            ->assertSee('Renyah dan enak.')
            ->assertSee('Tulis Ulasan');

        $this->from(route('product.detail', $product->slug))
            ->post(route('product.review', $product->id_product), [
                'nama_customer' => 'Budi Santoso',
                'rating' => 4,
                'komentar' => 'Rasanya pas untuk teman minum teh.',
            ])
            ->assertRedirect(route('product.detail', $product->slug))
            ->assertSessionHas('success', 'Ulasan berhasil dikirim.');

        $this->assertSame(2, $product->ratings()->count());
        $this->assertSame('Budi Santoso', $product->ratings()->latest('id_rating')->first()->nama_customer);
    }

    public function test_admin_dashboard_shows_overall_rating_and_recent_customer_feedback(): void
    {
        $admin = $this->createUser();
        $category = Category::query()->create(['nama_kategori' => 'Brownies', 'slug' => 'brownies']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Admin Rating',
            'harga' => 30000,
            'stok' => 5,
            'status' => 'active',
        ]);

        Rating::query()->create([
            'product_id' => $product->id_product,
            'nama_customer' => 'Dewi Pelanggan',
            'rating' => 4,
            'komentar' => 'Teksturnya lembut.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Rating & ulasan')
            ->assertSee('RATING KESELURUHAN')
            ->assertSee('4.0')
            ->assertSee('Dari 1 ulasan pelanggan')
            ->assertSee('Ulasan Terbaru')
            ->assertSee('Dewi Pelanggan')
            ->assertSee('Brownies Admin Rating')
            ->assertSee('Teksturnya lembut.');
    }

    public function test_admin_can_view_search_and_filter_customer_ratings(): void
    {
        $admin = $this->createUser();
        $category = Category::query()->create(['nama_kategori' => 'Brownies', 'slug' => 'brownies']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Rating Tes',
            'harga' => 30000,
            'stok' => 5,
            'status' => 'active',
        ]);

        Rating::query()->create([
            'product_id' => $product->id_product,
            'nama_customer' => 'Dewi Pelanggan',
            'rating' => 5,
            'komentar' => 'Sangat lezat.',
        ]);
        Rating::query()->create([
            'product_id' => $product->id_product,
            'nama_customer' => 'Rina Pelanggan',
            'rating' => 2,
            'komentar' => 'Kurang cocok.',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.ratings', ['rating' => 5, 'search' => 'Dewi']))
            ->assertOk()
            ->assertSee('Rating & Ulasan')
            ->assertSee('Dewi Pelanggan')
            ->assertSee('Brownies Rating Tes')
            ->assertSee('Sangat lezat.')
            ->assertDontSee('Rina Pelanggan')
            ->assertDontSee('Kurang cocok.');
    }

    public function test_sales_reports_include_paid_ready_orders_and_break_down_payment_methods(): void
    {
        $admin = $this->createUser();
        $this->createPaidOrder('BB-TRANSFER1', 20000, 'ready', 'transfer', 'verified');
        $this->createPaidOrder('BB-EWALLET01', 12000, 'ready', 'other', 'verified');
        $this->createPaidOrder('BB-EWALLET02', 8000, 'ready', 'ewallet', 'verified');
        $this->createPaidOrder('BB-CASHDONE1', 5000, 'completed', 'cash', 'verified');
        $this->createPaidOrder('BB-CASHPEND1', 10000, 'ready', 'cash', 'pending');
        $this->createPaidOrder('BB-CANCEL001', 50000, 'cancelled', 'transfer', 'verified');
        $this->createPaidOrder('BB-UNKNOWN01', 3000, 'completed', null, 'verified');
        $this->createPaidOrder('BB-CASHOLD001', 60000, 'completed', 'cash', 'pending');

        $this->actingAs($admin)
            ->get(route('admin.reports', [
                'start_date' => today()->toDateString(),
                'end_date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Unduh Excel')
            ->assertSee('Simpan PDF')
            ->assertSee('Cetak')
            ->assertSee('data-print-report', false)
            ->assertSee('Pembayaran Diterima')
            ->assertSee('Rp48.000')
            ->assertSee('E-Wallet')
            ->assertSee('Metode lain (data lama)')
            ->assertSee('Tidak diketahui')
            ->assertSee('Rp20.000')
            ->assertSee('Rp5.000')
            ->assertDontSee('Rp98.000')
            ->assertDontSee('Rp60.000');

        $this->actingAs($admin)
            ->get(route('admin.reports.export', [
                'start_date' => today()->toDateString(),
                'end_date' => today()->toDateString(),
            ]))
            ->assertOk()
            ->assertStreamedContent("Tanggal,\"Transaksi Terbayar\",\"Pembayaran Diterima\"\n".today()->toDateString().",5,48000\n");

        $excelResponse = $this->actingAs($admin)
            ->get(route('admin.reports.excel', [
                'start_date' => today()->toDateString(),
                'end_date' => today()->toDateString(),
            ]));

        $excelResponse->assertDownload(
            'laporan-penjualan-'.today()->toDateString().'-'.today()->toDateString().'.csv'
        );
        $excelContent = $excelResponse->streamedContent();
        $this->assertStringContainsString('Ringkasan metode pembayaran', $excelContent);
        $this->assertStringContainsString('Ringkasan harian', $excelContent);
        $this->assertStringContainsString('Tunai', $excelContent);
        $this->assertStringContainsString('48000', $excelContent);
    }

    public function test_public_pages_only_display_promotions_currently_in_period(): void
    {
        $this->createPromotion('NOW10', today()->toDateString(), today()->addDay()->toDateString());
        $this->createPromotion('LATER10', today()->addDay()->toDateString(), today()->addDays(2)->toDateString());
        $this->createPromotion('ENDED10', today()->subDays(2)->toDateString(), today()->subDay()->toDateString());

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('NOW10')
            ->assertDontSee('LATER10')
            ->assertDontSee('ENDED10');

        $this->get(route('promotions'))
            ->assertOk()
            ->assertSee('NOW10')
            ->assertDontSee('LATER10')
            ->assertDontSee('ENDED10');
    }

    public function test_checkout_revalidates_promo_and_calculates_discount_from_database_price(): void
    {
        $category = Category::query()->create(['nama_kategori' => 'Brownies', 'slug' => 'brownies']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Test',
            'deskripsi' => 'Produk untuk tes checkout',
            'harga' => 10000,
            'stok' => 10,
            'status' => 'active',
        ]);
        $this->createPromotion('FUTURE10', today()->addDay()->toDateString(), today()->addDays(2)->toDateString());
        $this->createPromotion('VALID50', today()->toDateString(), today()->addDay()->toDateString(), 50);

        $checkoutData = [
            'nama' => 'Pelanggan Tes',
            'no_whatsapp' => '081234567890',
            'alamat' => '',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
        ];

        $this->from(route('checkout'))
            ->withSession(['cart' => [$product->id_product => ['qty' => 2]]])
            ->post(route('checkout.store'), $checkoutData + ['promo_code' => 'FUTURE10'])
            ->assertSessionHasErrors('promo_code');

        $checkoutPage = $this->get(route('checkout'))->assertOk();
        $this->assertSame(
            1,
            substr_count($checkoutPage->getContent(), 'Kode promo tidak valid atau sudah di luar masa berlaku.')
        );

        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(10, $product->fresh()->stok);

        $response = $this->withSession(['cart' => [$product->id_product => ['qty' => 2]]])
            ->post(route('checkout.store'), $checkoutData + ['promo_code' => 'VALID50'])
            ->assertRedirect();

        $order = Order::query()->firstOrFail();
        $response->assertRedirect(route('checkout.success', ['code' => $order->kode_order]));
        $this->assertSame(20000.0, (float) $order->subtotal);
        $this->assertSame(10000.0, (float) $order->diskon);
        $this->assertSame(10000.0, (float) $order->total);
        $this->assertSame(8, $product->fresh()->stok);
        $this->assertSame(1, $order->statusHistories()->count());
        $this->assertSame('checkout', $order->statusHistories()->first()->source);
    }

    public function test_checkout_promo_preview_uses_current_cart_prices_and_case_insensitive_code(): void
    {
        $category = Category::query()->create(['nama_kategori' => 'Brownies', 'slug' => 'brownies']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Promo',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
        ]);
        Promotion::query()->create([
            'nama_promo' => 'Weekend Special',
            'kode_promo' => 'BAKERY20',
            'jenis_diskon' => 'nominal',
            'nilai_diskon' => 20000,
            'tanggal_mulai' => today(),
            'tanggal_berakhir' => today()->addDay(),
            'status' => 'active',
        ]);

        $this->withSession(['cart' => [$product->id_product => ['qty' => 4]]])
            ->postJson(route('checkout.promo-preview'), [
                'promo_code' => 'bakery20',
                'delivery_method' => 'delivery',
            ])
            ->assertOk()
            ->assertJsonPath('promo_code', 'BAKERY20')
            ->assertJsonPath('subtotal', 40000)
            ->assertJsonPath('discount', 20000)
            ->assertJsonPath('delivery', 15000)
            ->assertJsonPath('total', 35000);
    }

    public function test_checkout_promo_preview_caps_nominal_discount_and_rejects_invalid_codes(): void
    {
        $category = Category::query()->create(['nama_kategori' => 'Brownies', 'slug' => 'brownies']);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Brownies Murah',
            'harga' => 10000,
            'stok' => 5,
            'status' => 'active',
        ]);
        Promotion::query()->create([
            'nama_promo' => 'Potongan Nominal',
            'kode_promo' => 'POTONG20000',
            'jenis_diskon' => 'nominal',
            'nilai_diskon' => 20000,
            'tanggal_mulai' => today(),
            'tanggal_berakhir' => today()->addDay(),
            'status' => 'active',
        ]);

        $this->withSession(['cart' => [$product->id_product => ['qty' => 1]]])
            ->postJson(route('checkout.promo-preview'), [
                'promo_code' => 'potong20000',
                'delivery_method' => 'pickup',
            ])
            ->assertOk()
            ->assertJsonPath('discount', 10000)
            ->assertJsonPath('total', 0);

        $this->postJson(route('checkout.promo-preview'), [
            'promo_code' => 'KODESALAH',
            'delivery_method' => 'pickup',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('message', 'Kode promo tidak valid atau sudah di luar masa berlaku.');
    }

    private function createUser(string $role = 'admin'): User
    {
        return User::query()->create([
            'nama' => $role === 'admin' ? 'Admin Demo' : 'Kasir Demo',
            'username' => $role.'@example.com',
            'password' => 'password-lama',
            'role' => $role,
            'order_notifications_enabled' => true,
        ]);
    }

    private function createOnlineOrder(): Order
    {
        $customer = Customer::query()->create([
            'nama' => 'Pelanggan Demo',
            'no_whatsapp' => '081234567890',
        ]);

        $order = Order::query()->create([
            'kode_order' => 'BB-TEST1234',
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'subtotal' => 20000,
            'total' => 20000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'delivery_method' => 'pickup',
            'source' => 'online',
        ]);

        OrderStatusHistory::query()->create([
            'order_id' => $order->id_order,
            'from_status' => null,
            'to_status' => 'pending',
            'source' => 'checkout',
        ]);

        return $order;
    }

    private function createPaidOrder(
        string $code,
        int $total,
        string $status,
        ?string $paymentMethod,
        string $paymentStatus
    ): Order {
        $customer = Customer::query()->create([
            'nama' => 'Pelanggan '.$code,
            'no_whatsapp' => '081234567890',
        ]);

        return Order::query()->create([
            'kode_order' => $code,
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'subtotal' => $total,
            'total' => $total,
            'status' => $status,
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'delivery_method' => 'pickup',
            'source' => 'online',
        ]);
    }

    private function createPromotion(string $code, string $start, string $end, int $discount = 10): Promotion
    {
        return Promotion::query()->create([
            'nama_promo' => 'Promo '.$code,
            'kode_promo' => $code,
            'jenis_diskon' => 'persen',
            'nilai_diskon' => $discount,
            'tanggal_mulai' => $start,
            'tanggal_berakhir' => $end,
            'status' => 'active',
        ]);
    }
}
