<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppOrderNotification;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\User;
use App\Services\WhatsAppCloudApi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_click_to_chat_is_unavailable_without_an_admin_number(): void
    {
        config(['services.whatsapp.admin_number' => null]);

        $this->assertNull(app(WhatsAppCloudApi::class)->customerConfirmationUrl(new Order()));

        $this->get(route('contact'))
            ->assertOk()
            ->assertDontSee('wa.me/628139494009')
            ->assertDontSee('data-whatsapp-contact-form', false)
            ->assertSee('mailto:hello@benbenco.id', false);
    }

    public function test_contact_page_uses_the_configured_number_for_its_message_form(): void
    {
        config(['services.whatsapp.admin_number' => '+62 813-9494-009']);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('action="https://wa.me/628139494009"', false)
            ->assertSee('data-whatsapp-contact-form', false)
            ->assertSee('Pesan baru terkirim setelah Anda menekan tombol kirim di aplikasi WhatsApp.');
    }

    public function test_order_proof_explains_when_whatsapp_is_not_configured(): void
    {
        config(['services.whatsapp.admin_number' => null]);
        [$order] = $this->createStoredOrder();
        $customer = User::query()->create([
            'nama' => 'Pelanggan Uji',
            'username' => 'pelanggan-uji',
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $order->customer_id,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.order.proof', $order->id_order))
            ->assertOk()
            ->assertDontSee('Hubungi Admin via WhatsApp')
            ->assertSee('WhatsApp admin belum dikonfigurasi.')
            ->assertSee('mailto:hello@benbenco.id', false);
    }

    protected function tearDown(): void
    {
        Cache::flush();

        parent::tearDown();
    }

    public function test_checkout_redirects_to_persisted_order_with_a_prefilled_whatsapp_summary_and_is_idempotent(): void
    {
        Cache::flush();
        Queue::fake();
        $this->configureWhatsAppApi();
        config(['services.whatsapp.admin_number' => '+62 813-9494-009']);
        [$customerUser, $product] = $this->createCheckoutContext();

        $checkout = $this->actingAs($customerUser)
            ->withSession([
                'cart' => [$product->id_product => ['qty' => 2]],
                'cart_customer_id' => $customerUser->customer_id,
            ])
            ->get(route('checkout'))
            ->assertOk()
            ->assertSee('name="checkout_token"', false);

        preg_match('/name="checkout_token" value="([^"]+)"/', $checkout->getContent(), $tokenMatch);
        $this->assertNotEmpty($tokenMatch[1] ?? null);

        $checkoutData = [
            'checkout_token' => $tokenMatch[1],
            'nama' => 'Pelanggan Pesanan',
            'no_whatsapp' => '081234567890',
            'alamat' => '',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
        ];

        $response = $this->post(route('checkout.store'), $checkoutData);
        $order = Order::query()->where('checkout_token', $checkoutData['checkout_token'])->firstOrFail();
        $response->assertRedirect(route('customer.order.proof', $order->id_order));
        $this->assertSame(20000.0, (float) $order->total);

        $proof = $this->get(route('customer.order.proof', $order->id_order))
            ->assertOk()
            ->assertSee('Hubungi Admin via WhatsApp');
        $this->get(route('checkout.success', ['code' => $order->kode_order]))
            ->assertOk()
            ->assertSee('Hubungi Admin via WhatsApp');

        preg_match('/href="([^"]*wa\.me[^"]*)"/', $proof->getContent(), $linkMatch);
        $this->assertNotEmpty($linkMatch[1] ?? null);
        $whatsAppUrl = html_entity_decode($linkMatch[1], ENT_QUOTES);
        $this->assertSame('628139494009', basename(parse_url($whatsAppUrl, PHP_URL_PATH)));
        parse_str((string) parse_url($whatsAppUrl, PHP_URL_QUERY), $query);
        $message = $query['text'] ?? '';

        $this->assertStringContainsString('#'.$order->kode_order, $message);
        $this->assertStringContainsString('Pelanggan Pesanan', $message);
        $this->assertStringContainsString('- Cheese Roll Tes x 2', $message);
        $this->assertStringContainsString('Total Pesanan: Rp20.000', $message);
        $this->assertStringContainsString('Metode Pembayaran: Tunai', $message);
        $this->assertStringContainsString('Metode Pengiriman: Ambil di toko', $message);

        $this->withSession([
            'cart' => [$product->id_product => ['qty' => 2]],
            'cart_customer_id' => $customerUser->customer_id,
        ])->post(route('checkout.store'), $checkoutData)
            ->assertRedirect(route('customer.order.proof', $order->id_order));

        $this->assertDatabaseCount('orders', 1);
        Queue::assertPushedTimes(SendWhatsAppOrderNotification::class, 1);
    }

    public function test_checkout_succeeds_and_logs_when_cloud_api_credentials_are_missing(): void
    {
        Queue::fake();
        Log::spy();
        config([
            'services.whatsapp.admin_number' => '628139494009',
            'services.whatsapp.cloud_api' => [
                'access_token' => null,
                'phone_number_id' => null,
                'api_version' => null,
                'order_template_name' => null,
                'order_template_language' => 'id',
            ],
        ]);
        [$customerUser, $product] = $this->createCheckoutContext();
        $token = (string) Str::uuid();

        $this->actingAs($customerUser)
            ->withSession([
                'cart' => [$product->id_product => ['qty' => 1]],
                'cart_customer_id' => $customerUser->customer_id,
            ])
            ->post(route('checkout.store'), $this->checkoutData($token))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['checkout_token' => $token]);
        Queue::assertNothingPushed();
        Log::shouldHaveReceived('notice')->once();
    }

    public function test_checkout_remains_successful_when_synchronous_whatsapp_delivery_fails(): void
    {
        Log::spy();
        $this->configureWhatsAppApi();
        config(['queue.default' => 'sync']);
        Http::fake([
            'https://graph.facebook.com/v23.0/123456789/messages' => Http::response([
                'error' => ['message' => 'Simulated provider error'],
            ], 503),
        ]);
        [$customerUser, $product] = $this->createCheckoutContext();
        $token = (string) Str::uuid();

        $this->actingAs($customerUser)
            ->withSession([
                'cart' => [$product->id_product => ['qty' => 1]],
                'cart_customer_id' => $customerUser->customer_id,
            ])
            ->post(route('checkout.store'), $this->checkoutData($token))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', ['checkout_token' => $token]);
        Http::assertSentCount(1);
        Log::shouldHaveReceived('error');
    }

    public function test_cloud_api_sends_one_order_template_and_marks_the_order_as_notified(): void
    {
        $this->configureWhatsAppApi();
        [$order] = $this->createStoredOrder();
        Http::fake([
            'https://graph.facebook.com/v23.0/123456789/messages' => Http::response([
                'messages' => [['id' => 'wamid.test-order']],
            ]),
        ]);
        $job = new SendWhatsAppOrderNotification($order->id_order);
        $gateway = app(WhatsAppCloudApi::class);

        $job->handle($gateway);
        $job->handle($gateway);

        $this->assertNotNull($order->fresh()->whatsapp_notified_at);
        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
            && $request['messaging_product'] === 'whatsapp'
            && $request['to'] === '628139494009'
            && $request['template']['name'] === 'benben_order_notification'
            && $request['template']['components'][0]['parameters'][0]['text'] === '#'.$order->kode_order
            && $request['template']['components'][0]['parameters'][2]['text'] === '+6281234567890'
            && $request['template']['components'][0]['parameters'][3]['text'] === 'Cheese Roll Tes x 2'
            && $request['template']['components'][0]['parameters'][4]['text'] === '2'
            && $request['template']['components'][0]['parameters'][5]['text'] === 'Rp20.000');
    }

    public function test_cloud_api_failure_keeps_order_and_is_logged_after_job_failure(): void
    {
        $this->configureWhatsAppApi();
        [$order] = $this->createStoredOrder();
        Http::fake([
            'https://graph.facebook.com/v23.0/123456789/messages' => Http::response([
                'error' => ['message' => 'Simulated provider error'],
            ], 503),
        ]);
        Log::shouldReceive('error')->once()->withArgs(function (string $message, array $context) use ($order) {
            return $message === 'WhatsApp order notification failed after retries.'
                && $context['order_id'] === $order->id_order
                && str_contains($context['exception'], '503');
        });

        try {
            (new SendWhatsAppOrderNotification($order->id_order))
                ->handle(app(WhatsAppCloudApi::class));
            $this->fail('The failed API response should throw an HTTP exception.');
        } catch (RequestException $exception) {
            (new SendWhatsAppOrderNotification($order->id_order))->failed($exception);
        }

        $this->assertDatabaseHas('orders', [
            'id_order' => $order->id_order,
            'total' => 20000,
            'whatsapp_notified_at' => null,
        ]);
    }

    private function configureWhatsAppApi(): void
    {
        config([
            'services.whatsapp.admin_number' => '628139494009',
            'services.whatsapp.cloud_api' => [
                'access_token' => 'test-access-token',
                'phone_number_id' => '123456789',
                'api_version' => 'v23.0',
                'order_template_name' => 'benben_order_notification',
                'order_template_language' => 'id',
            ],
        ]);
    }

    private function createCheckoutContext(): array
    {
        $customer = Customer::query()->create([
            'nama' => 'Nama Profil',
            'no_whatsapp' => '081111111111',
        ]);
        $user = User::query()->create([
            'nama' => 'Nama Profil',
            'username' => 'customer-'.Str::random(8),
            'password' => 'password123',
            'role' => 'customer',
            'customer_id' => $customer->id_customer,
        ]);
        $category = Category::query()->create([
            'nama_kategori' => 'Roti Tes',
            'slug' => 'roti-tes',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Cheese Roll Tes',
            'deskripsi' => 'Produk untuk pengujian WhatsApp',
            'harga' => 10000,
            'stok' => 10,
            'status' => 'active',
        ]);

        return [$user, $product];
    }

    private function checkoutData(string $token): array
    {
        return [
            'checkout_token' => $token,
            'nama' => 'Pelanggan Pesanan',
            'no_whatsapp' => '081234567890',
            'alamat' => '',
            'payment_method' => 'cash',
            'delivery_method' => 'pickup',
        ];
    }

    private function createStoredOrder(): array
    {
        $customer = Customer::query()->create([
            'nama' => 'Pelanggan API',
            'no_whatsapp' => '081234567890',
        ]);
        $category = Category::query()->create([
            'nama_kategori' => 'Roti Tes',
            'slug' => 'roti-tes',
        ]);
        $product = Product::query()->create([
            'category_id' => $category->id_category,
            'nama' => 'Cheese Roll Tes',
            'deskripsi' => 'Produk untuk pengujian WhatsApp',
            'harga' => 10000,
            'stok' => 8,
            'status' => 'active',
        ]);
        $order = Order::query()->create([
            'kode_order' => 'BB-WA123456',
            'customer_id' => $customer->id_customer,
            'tanggal' => now(),
            'subtotal' => 20000,
            'delivery' => 0,
            'diskon' => 0,
            'total' => 20000,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'delivery_method' => 'pickup',
            'source' => 'online',
        ]);
        OrderDetail::query()->create([
            'order_id' => $order->id_order,
            'product_id' => $product->id_product,
            'harga' => 10000,
            'qty' => 2,
            'subtotal' => 20000,
        ]);

        return [$order, $product];
    }
}
