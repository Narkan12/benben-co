<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use App\Models\User;
use Database\Seeders\DemoAdminPanelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
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

        $this->assertSame(1, Product::query()->count());
        $this->assertSame($categoryCount, Category::query()->count());
        $this->assertEquals(51000, (float) $product->fresh()->harga);
        $this->assertSame(7, $product->fresh()->stok);
        $this->assertSame(6, Customer::query()->where('no_whatsapp', 'like', 'SIMULASI-%')->count());
        $this->assertSame(11, Order::query()->where('catatan', 'like', '[SIMULASI]%')->count());
        $this->assertSame(3, Rating::query()->where('nama_customer', 'like', 'Simulasi - %')->count());
        $this->assertDatabaseCount('page_contents', 5);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertDatabaseCount('user_sessions', 0);
        $this->assertDatabaseCount('stock_logs', 0);

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
        $this->assertSame(6, Customer::query()->where('no_whatsapp', 'like', 'SIMULASI-%')->count());
        $this->assertSame(3, Rating::query()->where('nama_customer', 'like', 'Simulasi - %')->count());
        $this->assertDatabaseCount('page_contents', 5);

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
            ->assertSee('Produk Asli');
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

    public function test_seeder_creates_a_demo_catalog_only_when_the_product_table_is_empty(): void
    {
        $this->seed(DemoAdminPanelSeeder::class);

        $this->assertSame(5, Product::query()->count());
        $this->assertGreaterThanOrEqual(5, Category::query()->count());
        $this->assertSame(11, Order::query()->count());
        $this->assertSame(2, Product::query()->where('stok', '>', 5)->count());
        $this->assertSame(2, Product::query()->whereBetween('stok', [1, 5])->count());
        $this->assertSame(1, Product::query()->where('stok', 0)->count());
    }

    public function test_seeder_refuses_to_run_outside_local_or_testing(): void
    {
        config(['app.env' => 'production']);

        try {
            $this->seed(DemoAdminPanelSeeder::class);
            $this->fail('Seeder seharusnya menolak lingkungan non-lokal.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('local atau testing', $exception->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('customers', 0);
    }
}
