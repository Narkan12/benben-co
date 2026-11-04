<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdvanceOrderStatuses extends Command
{
    protected $signature = 'orders:advance-statuses';

    protected $description = 'Memajukan status pesanan berdasarkan pembayaran dan waktu operasional';

    public function handle(): int
    {
        $advanced = 0;

        Order::query()
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) {
                $query->where(function ($query) {
                    $query->where('status', 'pending')->where('payment_status', 'verified');
                })->orWhereIn('status', ['confirmed', 'processing']);
            })
            ->with('statusHistories')
            ->chunkById(100, function ($orders) use (&$advanced) {
                foreach ($orders as $order) {
                    $nextStatus = $this->nextStatus($order);
                    if (! $nextStatus) {
                        continue;
                    }

                    DB::transaction(function () use ($order, $nextStatus) {
                        $lockedOrder = Order::query()->lockForUpdate()->find($order->id_order);
                        if (! $lockedOrder || $lockedOrder->status !== $order->status) {
                            return;
                        }

                        $previousStatus = $lockedOrder->status;
                        $lockedOrder->status = $nextStatus;
                        $lockedOrder->save();
                        OrderStatusHistory::query()->create([
                            'order_id' => $lockedOrder->id_order,
                            'from_status' => $previousStatus,
                            'to_status' => $nextStatus,
                            'changed_by' => null,
                            'source' => 'system_automation',
                            'changed_at' => now(),
                        ]);
                    });
                    $advanced++;
                }
            }, 'id_order');

        $this->info("{$advanced} status pesanan diperbarui otomatis.");

        return self::SUCCESS;
    }

    private function nextStatus(Order $order): ?string
    {
        if ($order->status === 'pending' && $order->payment_status === 'verified') {
            return 'confirmed';
        }

        $lastChange = $order->statusHistories->last()?->changed_at ?? $order->tanggal;
        if (! $lastChange) {
            return null;
        }

        if ($order->status === 'confirmed' && $lastChange->lte(now()->subMinutes(15))) {
            return 'processing';
        }

        if ($order->status === 'processing' && $lastChange->lte(now()->subHour())) {
            return 'ready';
        }

        // Pickup orders require a real handover; delivery orders may complete after a day.
        if ($order->status === 'ready'
            && $order->delivery_method === 'delivery'
            && $lastChange->lte(now()->subDay())) {
            return 'completed';
        }

        return null;
    }
}
