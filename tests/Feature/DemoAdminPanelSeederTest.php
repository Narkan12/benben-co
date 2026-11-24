<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Database\Seeders\DemoAdminPanelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAdminPanelSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_admin_reports_idempotently_without_changing_existing_products(): void
    {
        $category = Category::query()->create([
            'nama_kategori' => 'Kategori Asli',
            'slug' => 'kategori-asli',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Produk Asli',
            'deskripsi' => 'Produk yang sudah tersedia.',
            'harga' => 51000,
            'stok' => 7,
            'status' => 'active',
        ]);
        $categoryCount = Category::query()->count();
        $admin = User::query()->create([
            'nama' => 'Admin Pengujian',
            'username' => 'admin-pengujian@example.com',
            'password' => 'password-lama',
            'role' => 'admin',
        ]);

        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertSame(12, Product::query()->count());
        $this->assertGreaterThan($categoryCount, Category::query()->count());
        $this->assertEquals(51000, (float) $product->fresh()->harga);
        $this->assertSame(7, $product->fresh()->stok);
        $this->assertSame(6, Customer::query()->where('no_whatsapp', 'like', '0812345678%')->count());
        $this->assertSame(11, Order::query()->where('catatan', 'like', '[SIMULASI]%')->count());
        $this->assertSame(3, Rating::query()->whereIn('nama_customer', ['Nabila Putri', 'Rizky Ramadhan', 'Siti Aulia', 'Fajar Maulana', 'Dinda Maharani', 'Rafi Akbar'])->count());
        $this->assertDatabaseCount('page_contents', 5);
        $this->assertDatabaseCount('activity_logs', 3);
        $this->assertDatabaseCount('user_sessions', 0);
        $this->assertDatabaseCount('stock_logs', 0);
        $this->assertDatabaseCount('promotions', 2);
        $statusHistoryCount = OrderStatusHistory::query()->count();

        foreach (Order::query()->where('catatan', 'like', '[SIMULASI]%')->with('details')->get() as $order) {
            $this->assertContains($order->status, ['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled']);
            $this->assertContains($order->payment_status, ['pending', 'verified', 'rejected']);
            $this->assertEquals(
                (float) $order->subtotal,
                (float) $order->details->sum(fn ($detail) => (float) $detail->harga * $detail->qty)
            );
            $this->assertEquals(
                (float) $order->total,
                (float) $order->subtotal + (float) $order->delivery - (float) $order->diskon
            );

            if ($order->status === 'cancelled') {
                $this->assertSame('rejected', $order->payment_status);
            }

            if ($order->source === 'pos') {
                $this->assertSame('completed', $order->status);
                $this->assertSame('verified', $order->payment_status);
                $this->assertSame('pickup', $order->delivery_method);
                if ($order->payment_method === 'cash') {
                    $this->assertEquals((float) $order->total + (float) $order->cash_change, (float) $order->cash_received);
                }
            }
        }

        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertSame(11, Order::query()->where('catatan', 'like', '[SIMULASI]%')->count());
        $this->assertSame(6, Customer::query()->where('no_whatsapp', 'like', '0812345678%')->count());
        $this->assertSame(3, Rating::query()->whereIn('nama_customer', ['Nabila Putri', 'Rizky Ramadhan', 'Siti Aulia', 'Fajar Maulana', 'Dinda Maharani', 'Rafi Akbar'])->count());
        $this->assertDatabaseCount('page_contents', 5);
        $this->assertDatabaseCount('activity_logs', 3);
        $this->assertDatabaseCount('promotions', 2);
        $this->assertSame($statusHistoryCount, OrderStatusHistory::query()->count());

        $startDate = today()->subDays(40)->toDateString();
        $endDate = today()->toDateString();
        $this->actingAs($admin)
            ->get(route('admin.dashboard', ['period' => 'custom', 'start_date' => $startDate, 'end_date' => $endDate]))
            ->assertOk();
        $this->get(route('admin.reports.transactions', ['start_date' => $startDate, 'end_date' => $endDate]))
            ->assertOk()
            ->assertSee('BB-SIM00001');
        $this->get(route('admin.reports.sales', ['start_date' => $startDate, 'end_date' => $endDate]))
            ->assertOk()
            ->assertSee('Cheese Roll');
        $this->get(route('admin.reports.finance', ['start_date' => $startDate, 'end_date' => $endDate]))
            ->assertOk()
            ->assertSee('BB-SIM00001');
        $export = $this->get(route('admin.reports.export.dynamic', [
            'type' => 'transactions',
            'format' => 'csv',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]));
        $export->assertOk();
        $this->assertStringContainsString('BB-SIM00001', $export->streamedContent());
    }

    public function test_seeder_creates_a_realistic_demo_catalog_and_orders(): void
    {
        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertSame(11, Product::query()->count());
        $this->assertGreaterThanOrEqual(9, Category::query()->count());
        $this->assertSame(11, Order::query()->count());
        $this->assertSame(10, Product::query()->where('stok', '>', 5)->count());
        $this->assertSame(1, Product::query()->whereBetween('stok', [1, 5])->count());
        $this->assertSame(0, Product::query()->where('stok', 0)->count());
        $this->assertSame(11, Order::query()->whereHas('details')->count());
    }

    public function test_seeder_is_disabled_in_production_unless_explicitly_enabled(): void
    {
        config(['app.env' => 'production']);

        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customers', 0);

        config(['app.demo_seed_enabled' => true]);
        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertDatabaseCount('orders', 11);
        $this->assertDatabaseHas('users', [
            'username' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->assertDatabaseMissing('users', [
            'username' => 'nadia.pramesti',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'kasir@example.com',
            'role' => 'kasir',
        ]);

        $this->assertDatabaseMissing('users', [
            'username' => 'aditya.wijaya',
        ]);
    }

    public function test_seeded_accounts_can_log_in_with_the_documented_credentials(): void
    {
        $this->seed(DemoAdminPanelSeeder::class);

        $admin = User::query()->where('username', 'admin@example.com')->firstOrFail();
        $cashier = User::query()->where('username', 'kasir@example.com')->firstOrFail();
        $customer = User::query()->where('username', 'nabila.putri')->firstOrFail();

        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('admin123', $admin->password));
        $this->assertSame('kasir', $cashier->role);
        $this->assertTrue(Hash::check('kasir123', $cashier->password));
        $this->assertSame('customer', $customer->role);
        $this->assertTrue(Hash::check('BenbenPelanggan!2025', $customer->password));
        $this->assertNotNull($customer->customer_id);

        $this->post(route('admin.login.submit'), [
            'username' => 'admin@example.com',
            'password' => 'admin123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'))->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.submit'), [
            'username' => 'kasir@example.com',
            'password' => 'kasir123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->post(route('logout'))->assertRedirect(route('admin.login'));

        $this->post(route('login.submit'), [
            'username' => 'nabila.putri',
            'password' => 'BenbenPelanggan!2025',
        ])->assertRedirect(route('customer.dashboard'));
    }

    public function test_legacy_seeded_admin_and_customer_are_upgraded_without_losing_order_relations(): void
    {
        $legacyCustomer = Customer::query()->create([
            'nama' => 'Simulasi - Nabila Putri',
            'no_whatsapp' => 'SIMULASI-01',
            'alamat' => 'Alamat simulasi - Jl. Melati, Antapani, Bandung.',
        ]);
        $legacyAdmin = User::query()->create([
            'nama' => 'Admin01',
            'username' => 'admin@example.com',
            'email' => 'admin@example.com',
            'password' => 'password-lama',
            'role' => 'admin',
        ]);

        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertSame(
            $legacyCustomer->id_customer,
            Customer::query()->where('no_whatsapp', '081234567801')->value('id_customer')
        );
        $this->assertSame(
            $legacyCustomer->id_customer,
            Order::query()->where('kode_order', 'BB-SIM00001')->value('customer_id')
        );
        $this->assertSame($legacyAdmin->id_user, User::query()->where('username', 'admin@example.com')->value('id_user'));
        $this->assertTrue(Hash::check('admin123', $legacyAdmin->fresh()->password));
        $this->assertDatabaseMissing('customers', ['no_whatsapp' => 'SIMULASI-01']);
        $this->assertDatabaseMissing('users', ['username' => 'nadia.pramesti']);
    }

    public function test_user_factory_uses_the_application_user_columns(): void
    {
        $user = User::factory()->create();

        $this->assertNotEmpty($user->nama);
        $this->assertNotEmpty($user->username);
        $this->assertSame('kasir', $user->role);
        $this->assertTrue(Hash::check('password', $user->password));
    }
}
