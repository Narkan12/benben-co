<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $request->validate([
            'product_id' => ['required', 'exists:products,id_product'],
            'qty' => ['nullable', 'integer', 'min:1'],
        ]);

        $product = Product::query()->findOrFail($request->product_id);

        if ($product->status !== 'active') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Produk tidak tersedia saat ini.'], 422);
            }

            return back()->with('error', 'Produk tidak tersedia saat ini.');
        }

        $cart = session('cart', []);
        $qty = max(1, (int) ($request->qty ?? 1));

        if (isset($cart[$product->id_product])) {
            $qty += (int) $cart[$product->id_product]['qty'];
        }

        if ($qty > $product->stok) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Stok tidak mencukupi untuk jumlah yang diminta.'], 422);
            }

            return back()->with('error', 'Stok tidak mencukupi untuk jumlah yang diminta.');
        }

        $cart[$product->id_product] = [
            'product_id' => $product->id_product,
            'qty' => $qty,
        ];

        session(['cart' => $cart]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $product->nama.' ditambahkan ke keranjang.',
                'product_id' => $product->id_product,
                'quantity' => $qty,
                'stock_remaining' => (int) $product->stok - $qty,
                'cart_item_count' => collect($cart)->sum('qty'),
            ]);
        }

        if ($request->boolean('go_to_checkout')) {
            return redirect()->route('checkout')->with('success', $product->nama.' ditambahkan ke keranjang.');
        }

        return back()->with('success', $product->nama.' ditambahkan ke keranjang.');
    }

    public function update(Request $request, int $productId)
    {
        $request->validate([
            'qty' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::query()->findOrFail($productId);
        $qty = (int) $request->qty;

        if ($product->status !== 'active' || $qty > $product->stok) {
            return back()->with('error', 'Stok tidak mencukupi untuk jumlah yang diminta.');
        }

        $cart = session('cart', []);

        if (! isset($cart[$productId])) {
            return back()->with('error', 'Produk tidak ada di keranjang.');
        }

        $cart[$productId]['qty'] = $qty;
        session(['cart' => $cart]);

        return back()->with('success', 'Jumlah produk berhasil diperbarui.');
    }

    public function remove(int $productId)
    {
        $cart = session('cart', []);

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session(['cart' => $cart]);
        }

        return back()->with('success', 'Item berhasil dihapus dari keranjang.');
    }

    public function clear()
    {
        session()->forget('cart');

        return back()->with('success', 'Keranjang berhasil dibersihkan.');
    }
}
