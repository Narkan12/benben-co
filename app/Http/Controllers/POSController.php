<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class POSController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->where('status', 'active')
            ->where('stok', '>', 0)
            ->orderBy('nama')
            ->get();

        return view('pos.index', compact('products'));
    }

    public function receipt(Order $order)
    {
        $order->load(['customer', 'details.product', 'statusHistories.user']);

        return view('pos.receipt', compact('order'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_whatsapp' => ['nullable', 'string', 'max:20'],
            'payment_method' => ['required', 'in:cash,transfer,ewallet'],
            'amount_received' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id_product'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
        ]);

        $order = DB::transaction(function () use ($data) {
            $items = [];
            $total = 0;

            foreach ($data['items'] as $item) {
                $product = Product::query()
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);
                $qty = (int) $item['qty'];

                if ($product->status !== 'active' || $qty > $product->stok) {
                    throw ValidationException::withMessages([
                        'items' => $product->nama.' tidak memiliki stok yang cukup.',
                    ]);
                }

                $lineTotal = (float) $product->harga * $qty;
                $total += $lineTotal;
                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'subtotal' => $lineTotal,
                ];
            }

            if ((float) $data['amount_received'] < $total) {
                throw ValidationException::withMessages([
                    'amount_received' => 'Jumlah pembayaran masih kurang dari total transaksi.',
                ]);
            }

            $customer = Customer::query()->create([
                'nama' => $data['nama'],
                'no_whatsapp' => $data['no_whatsapp'] ?? '-',
                'alamat' => null,
            ]);

            $cashReceived = (float) $data['amount_received'];
            $cashChange = $data['payment_method'] === 'cash' ? max(0, $cashReceived - $total) : 0;

            $orderData = [
                'kode_order' => 'BB-POS-'.strtoupper(Str::random(6)),
                'customer_id' => $customer->id_customer,
                'tanggal' => now(),
                'subtotal' => $total,
                'delivery' => 0,
                'diskon' => 0,
                'total' => $total,
                'status' => 'completed',
                'payment_method' => $data['payment_method'],
                'payment_status' => 'verified',
                'delivery_method' => 'pickup',
                'source' => 'pos',
            ];

            if ($data['payment_method'] === 'cash' && Schema::hasColumn('orders', 'cash_received')) {
                $orderData['cash_received'] = $cashReceived;
                $orderData['cash_change'] = $cashChange;
            }

            $order = Order::query()->create($orderData);
            OrderStatusHistory::query()->create([
                'order_id' => $order->id_order,
                'from_status' => null,
                'to_status' => 'completed',
                'changed_by' => auth()->id(),
                'source' => 'pos',
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                $stockBefore = (int) $product->stok;
                $product->stok = $stockBefore - $item['qty'];
                $product->save();

                OrderDetail::query()->create([
                    'order_id' => $order->id_order,
                    'product_id' => $product->id_product,
                    'harga' => $product->harga,
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                ]);

                StockLog::query()->create([
                    'product_id' => $product->id_product,
                    'jenis' => 'out',
                    'jumlah' => $item['qty'],
                    'stok_sebelum' => $stockBefore,
                    'stok_sesudah' => $product->stok,
                    'keterangan' => 'Transaksi POS '.$order->kode_order,
                ]);
            }

            return $order;
        });

        return redirect()->route('admin.pos.receipt', ['order' => $order->id_order])
            ->with('success', 'Transaksi '.$order->kode_order.' berhasil disimpan.');
    }
}
