<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
            ->assertSeeText($product->nama);

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
            ->assertSeeText('Pesanan terbaru')
            ->assertSeeText($order->kode_order)
            ->assertSeeText('Total Pesanan');
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

    public function test_admin_pos_receipt_uses_saved_transaction_data(): void
    {
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

        $before = Order::query()->count();

        $response = $this->actingAs($admin)
            ->post(route('admin.pos.store'), [
                'nama' => 'Kasir POS',
                'no_whatsapp' => '081122334455',
                'payment_method' => 'cash',
                'amount_received' => 60000,
                'items' => [
                    ['product_id' => $product->id_product, 'qty' => 2],
                ],
            ]);

        $response->assertSessionHas('success');
        $this->assertEquals($before + 1, Order::query()->count());

        $createdOrder = Order::query()->latest('id_order')->first();

        $this->actingAs($admin)
            ->get(route('admin.pos.receipt', $createdOrder->id_order))
            ->assertOk()
            ->assertSeeText('Struk Transaksi')
            ->assertSeeText($createdOrder->kode_order)
            ->assertSeeText($product->nama)
            ->assertSeeText('Tunai');
    }
}
