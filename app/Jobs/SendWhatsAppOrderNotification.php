<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppCloudApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWhatsAppOrderNotification implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [30, 120];

    public int $timeout = 20;

    public int $uniqueFor = 900;

    public function __construct(public int $orderId) {}

    public function uniqueId(): string
    {
        return 'whatsapp-order-'.$this->orderId;
    }

    public function handle(WhatsAppCloudApi $whatsApp): void
    {
        $order = Order::query()->with(['customer', 'details.product'])->findOrFail($this->orderId);
        if ($order->whatsapp_notified_at) {
            return;
        }

        $whatsApp->sendNewOrderNotification($order);

        $order->whatsapp_notified_at = now();
        $order->save();
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('WhatsApp order notification failed after retries.', [
            'order_id' => $this->orderId,
            'order_code' => Order::query()->whereKey($this->orderId)->value('kode_order'),
            'exception' => $exception?->getMessage(),
        ]);
    }
}
