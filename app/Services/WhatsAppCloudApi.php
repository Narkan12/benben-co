<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use UnexpectedValueException;

class WhatsAppCloudApi
{
    public function adminNumber(): ?string
    {
        $number = preg_replace('/\D+/', '', (string) config('services.whatsapp.admin_number')) ?? '';

        return preg_match('/^[1-9]\d{7,14}$/', $number) ? $number : null;
    }

    public function missingNotificationConfiguration(): array
    {
        $api = config('services.whatsapp.cloud_api', []);
        $missing = [];

        if (! $this->adminNumber()) {
            $missing[] = 'WHATSAPP_ADMIN_NUMBER';
        }
        if (! filled($api['access_token'] ?? null)) {
            $missing[] = 'WHATSAPP_CLOUD_API_TOKEN';
        }
        if (! preg_match('/^\d+$/', (string) ($api['phone_number_id'] ?? ''))) {
            $missing[] = 'WHATSAPP_PHONE_NUMBER_ID';
        }
        if (! preg_match('/^v\d+\.\d+$/', (string) ($api['api_version'] ?? ''))) {
            $missing[] = 'WHATSAPP_CLOUD_API_VERSION';
        }
        if (! preg_match('/^[a-z0-9_]{1,512}$/', (string) ($api['order_template_name'] ?? ''))) {
            $missing[] = 'WHATSAPP_ORDER_TEMPLATE_NAME';
        }
        if (! preg_match('/^[a-z]{2}(?:_[A-Z]{2})?$/', (string) ($api['order_template_language'] ?? ''))) {
            $missing[] = 'WHATSAPP_ORDER_TEMPLATE_LANGUAGE';
        }

        return $missing;
    }

    public function customerConfirmationUrl(Order $order): ?string
    {
        $number = $this->adminNumber();
        if (! $number) {
            return null;
        }

        $order->loadMissing(['customer', 'details.product']);
        $productLines = $order->details->map(fn ($detail) => '- '.($detail->product?->nama ?? 'Produk')
            .' x '.$detail->qty)->implode("\n");
        $paymentLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer bank',
            'ewallet' => 'E-Wallet',
            'other' => 'Lainnya',
        ];
        $deliveryLabels = [
            'pickup' => 'Ambil di toko',
            'delivery' => 'Diantar ke alamat',
        ];

        $message = "Halo Admin BENBEN CO., saya ingin mengonfirmasi pesanan.\n\n"
            .'Nomor Pesanan: #'.$order->kode_order."\n"
            .'Nama Pelanggan: '.($order->customer?->nama ?? 'Pelanggan')."\n"
            ."Produk:\n".$productLines."\n"
            .'Total Pesanan: Rp'.number_format((float) $order->total, 0, ',', '.')."\n"
            .'Metode Pembayaran: '.($paymentLabels[$order->payment_method] ?? 'Lainnya')."\n"
            .'Metode Pengiriman: '.($deliveryLabels[$order->delivery_method] ?? 'Lainnya')."\n\n"
            .'Mohon informasi mengenai pesanan saya. Terima kasih.';

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    public function sendNewOrderNotification(Order $order): void
    {
        $missing = $this->missingNotificationConfiguration();
        if ($missing !== []) {
            throw new RuntimeException('WhatsApp Cloud API is not configured: '.implode(', ', $missing));
        }

        $order->loadMissing(['customer', 'details.product']);
        $api = config('services.whatsapp.cloud_api');
        $products = $order->details
            ->map(fn ($detail) => ($detail->product?->nama ?? 'Produk').' x '.$detail->qty)
            ->implode('; ');
        $products = Str::limit($products, 800, '…');
        $parameters = [
            '#'.$order->kode_order,
            $order->customer?->nama ?? 'Pelanggan',
            $this->internationalCustomerPhone($order->customer?->no_whatsapp),
            $products ?: 'Tidak ada rincian produk',
            (string) $order->details->sum('qty'),
            'Rp'.number_format((float) $order->total, 0, ',', '.'),
            $this->paymentMethodLabel($order->payment_method),
            $this->deliveryMethodLabel($order->delivery_method),
            $order->tanggal?->format('d/m/Y H:i') ?? '-',
        ];

        $response = Http::acceptJson()
            ->withToken($api['access_token'])
            ->connectTimeout(5)
            ->timeout(15)
            ->post(
                'https://graph.facebook.com/'.$api['api_version'].'/'.$api['phone_number_id'].'/messages',
                [
                    'messaging_product' => 'whatsapp',
                    'recipient_type' => 'individual',
                    'to' => $this->adminNumber(),
                    'type' => 'template',
                    'template' => [
                        'name' => $api['order_template_name'],
                        'language' => ['code' => $api['order_template_language']],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => array_map(
                                fn (string $text) => ['type' => 'text', 'text' => $text],
                                $parameters
                            ),
                        ]],
                    ],
                ]
            );

        $response->throw();

        if (! filled(data_get($response->json(), 'messages.0.id'))) {
            throw new UnexpectedValueException('WhatsApp Cloud API returned no message ID.');
        }
    }

    private function paymentMethodLabel(?string $method): string
    {
        return [
            'cash' => 'Tunai',
            'transfer' => 'Transfer bank',
            'ewallet' => 'E-Wallet',
            'other' => 'Lainnya',
        ][$method ?? ''] ?? 'Belum ditentukan';
    }

    private function deliveryMethodLabel(?string $method): string
    {
        return [
            'pickup' => 'Ambil di toko',
            'delivery' => 'Diantar ke alamat',
        ][$method ?? ''] ?? 'Belum ditentukan';
    }

    private function internationalCustomerPhone(?string $phone): string
    {
        $number = preg_replace('/\D+/', '', (string) $phone) ?? '';
        if (str_starts_with($number, '00')) {
            $number = substr($number, 2);
        }
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        } elseif (str_starts_with($number, '620')) {
            $number = '62'.substr($number, 3);
        }

        return preg_match('/^[1-9]\d{7,14}$/', $number) ? '+'.$number : 'Tidak tersedia';
    }
}
