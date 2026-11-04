<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\StockLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Keranjang Anda masih kosong.');
        }

        $items = [];
        $subtotal = 0;

        foreach ($cart as $productId => $row) {
            $product = Product::query()->find($productId);

            if (! $product || $product->status !== 'active') {
                continue;
            }

            $qty = (int) ($row['qty'] ?? 1);
            $lineTotal = $product->harga * $qty;
            $subtotal += $lineTotal;

            $items[] = [
                'product' => $product,
                'qty' => $qty,
                'line_total' => $lineTotal,
            ];
        }

        if ($items === []) {
            return redirect()->route('cart')->with('error', 'Produk di keranjang sudah tidak tersedia.');
        }

        return view('checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery' => 15000,
            'customer' => auth()->user()?->customer,
        ]);
    }

    public function previewPromotion(Request $request)
    {
        $request->merge([
            'promo_code' => strtoupper(trim((string) $request->input('promo_code'))),
        ]);
        $data = $request->validate([
            'promo_code' => ['required', 'string', 'max:50', 'alpha_dash'],
            'delivery_method' => ['required', 'in:pickup,delivery'],
        ]);

        $cart = session('cart', []);
        if (empty($cart)) {
            return response()->json(['message' => 'Keranjang Anda masih kosong.'], 422);
        }

        $products = Product::query()
            ->whereIn('id_product', array_keys($cart))
            ->where('status', 'active')
            ->get()
            ->keyBy('id_product');
        $subtotal = 0;

        foreach ($cart as $productId => $row) {
            $product = $products->get($productId);
            $qty = (int) ($row['qty'] ?? 1);

            if (! $product || $qty < 1 || $qty > $product->stok) {
                return response()->json([
                    'message' => 'Produk dalam keranjang tidak tersedia atau stoknya tidak cukup.',
                ], 422);
            }

            $subtotal += (float) $product->harga * $qty;
        }

        $promotion = Promotion::query()
            ->where('kode_promo', $data['promo_code'])
            ->where('status', 'active')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->first();

        if (! $promotion) {
            return response()->json([
                'message' => 'Kode promo tidak valid atau sudah di luar masa berlaku.',
            ], 422);
        }

        $discount = $promotion->jenis_diskon === 'persen'
            ? round(($subtotal * $promotion->nilai_diskon) / 100, 2)
            : (float) $promotion->nilai_diskon;
        $discount = min($discount, $subtotal);
        $delivery = $data['delivery_method'] === 'delivery' ? 15000 : 0;

        return response()->json([
            'promo_code' => $promotion->kode_promo,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery' => $delivery,
            'total' => max(0, $subtotal - $discount + $delivery),
            'message' => 'Kode promo berhasil digunakan.',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'alamat' => [Rule::requiredIf($request->input('delivery_method') === 'delivery'), 'nullable', 'string'],
            'payment_method' => ['required', 'in:transfer,ewallet,cash'],
            'delivery_method' => ['required', 'in:pickup,delivery'],
            'promo_code' => ['nullable', 'string', 'max:50', 'alpha_dash'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'payment_proof' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart')->with('error', 'Keranjang masih kosong.');
        }

        $promoCode = strtoupper(trim((string) $request->input('promo_code')));

        $proofPath = $request->hasFile('payment_proof')
            ? $request->file('payment_proof')->store('payment-proofs', 'public')
            : null;

        $order = DB::transaction(function () use ($request, $cart, $promoCode, $proofPath) {
            $promo = null;
            if ($promoCode !== '') {
                $promo = Promotion::query()
                    ->where('kode_promo', $promoCode)
                    ->where('status', 'active')
                    ->whereDate('tanggal_mulai', '<=', today())
                    ->whereDate('tanggal_berakhir', '>=', today())
                    ->lockForUpdate()
                    ->first();

                if (! $promo) {
                    throw ValidationException::withMessages([
                        'promo_code' => 'Kode promo tidak valid atau sudah di luar masa berlaku.',
                    ]);
                }
            }

            $products = Product::query()
                ->whereIn('id_product', array_keys($cart))
                ->orderBy('id_product')
                ->lockForUpdate()
                ->get()
                ->keyBy('id_product');
            $items = [];
            $subtotal = 0;

            foreach ($cart as $productId => $row) {
                $product = $products->get($productId);
                $qty = (int) ($row['qty'] ?? 1);

                if (! $product || $product->status !== 'active') {
                    throw ValidationException::withMessages([
                        'cart' => 'Salah satu produk di keranjang sudah tidak tersedia. Silakan periksa kembali keranjang Anda.',
                    ]);
                }

                if ($qty < 1 || $qty > $product->stok) {
                    throw ValidationException::withMessages([
                        'cart' => 'Stok produk '.$product->nama.' tidak cukup.',
                    ]);
                }

                $lineTotal = (float) $product->harga * $qty;
                $subtotal += $lineTotal;
                $items[] = [
                    'product' => $product,
                    'qty' => $qty,
                    'subtotal' => $lineTotal,
                ];
            }

            if ($items === []) {
                throw ValidationException::withMessages([
                    'cart' => 'Tidak ada produk yang tersedia untuk dipesan.',
                ]);
            }

            $discount = 0;
            if ($promo) {
                $discount = $promo->jenis_diskon === 'persen'
                    ? round(($subtotal * $promo->nilai_diskon) / 100, 2)
                    : (float) $promo->nilai_diskon;
                $discount = min($discount, $subtotal);
            }

            $customer = auth()->check() && auth()->user()->role === 'customer'
                ? auth()->user()->customer
                : null;
            if ($customer) {
                $customer->update([
                    'nama' => $request->nama,
                    'no_whatsapp' => $request->no_whatsapp,
                    'alamat' => $request->alamat,
                ]);
            } else {
                $customer = Customer::query()->create([
                    'nama' => $request->nama,
                    'no_whatsapp' => $request->no_whatsapp,
                    'alamat' => $request->alamat,
                ]);
            }

            $delivery = $request->delivery_method === 'delivery' ? 15000 : 0;
            $total = max(0, $subtotal - $discount + $delivery);

            $order = Order::query()->create([
                'kode_order' => 'BB-'.strtoupper(Str::random(8)),
                'customer_id' => $customer->id_customer,
                'tanggal' => now(),
                'subtotal' => $subtotal,
                'delivery' => $delivery,
                'diskon' => $discount,
                'total' => $total,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'payment_status' => 'pending',
                'payment_proof' => $proofPath,
                'delivery_method' => $request->delivery_method,
                'catatan' => $request->catatan ?? null,
                'source' => 'online',
                'promo_id' => $promo?->id_promo,
                'kode_promo_used' => $promo?->kode_promo,
            ]);
            OrderStatusHistory::query()->create([
                'order_id' => $order->id_order,
                'from_status' => null,
                'to_status' => 'pending',
                'changed_by' => null,
                'source' => 'checkout',
            ]);

            foreach ($items as $item) {
                $product = $item['product'];
                $stockBefore = (int) $product->stok;

                OrderDetail::query()->create([
                    'order_id' => $order->id_order,
                    'product_id' => $product->id_product,
                    'harga' => $product->harga,
                    'qty' => $item['qty'],
                    'subtotal' => $item['subtotal'],
                ]);

                $product->stok = $stockBefore - $item['qty'];
                $product->save();

                StockLog::query()->create([
                    'product_id' => $product->id_product,
                    'jenis' => 'out',
                    'jumlah' => $item['qty'],
                    'stok_sebelum' => $stockBefore,
                    'stok_sesudah' => $product->stok,
                    'keterangan' => 'Order '.$order->kode_order,
                ]);
            }

            return $order;
        });

        session()->forget('cart');

        if (auth()->check() && auth()->user()->role === 'customer') {
            return redirect()->route('customer.order.proof', ['id' => $order->id_order])
                ->with('success', 'Pesanan berhasil dibuat.');
        }

        return redirect()->route('checkout.success', ['code' => $order->kode_order])
            ->with('success', 'Pesanan berhasil dibuat.');
    }
}
