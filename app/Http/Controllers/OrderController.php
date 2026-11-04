<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::query()->with(['customer', 'details.product'])
            ->where('customer_id', auth()->user()->customer_id)->latest('id_order')->paginate(10);

        return view('customer.orders', ['orders' => $orders]);
    }

    public function show(int $id)
    {
        $order = Order::query()->with(['customer', 'details.product', 'statusHistories.user'])
            ->where('customer_id', auth()->user()->customer_id)->findOrFail($id);

        return view('customer.order-detail', ['order' => $order]);
    }

    public function proof(int $id)
    {
        $order = Order::query()->with(['customer', 'details.product', 'statusHistories.user'])
            ->where('customer_id', auth()->user()->customer_id)
            ->findOrFail($id);

        return view('customer.order-proof', ['order' => $order]);
    }

    public function dashboard()
    {
        $user = auth()->user();
        $customer = $user?->customer;
        $customerId = $customer?->id_customer;

        $baseOrders = Order::query()->where('customer_id', $customerId);
        $orders = $customerId ? $baseOrders->with('details.product')->latest('id_order')->limit(5)->get() : collect();

        return view('customer.dashboard', [
            'orders' => $orders,
            'totalOrders' => $customerId ? $baseOrders->count() : 0,
            'processingOrders' => $customerId ? $baseOrders->whereIn('status', ['pending', 'confirmed', 'processing', 'ready'])->count() : 0,
            'waitingOrders' => $customerId ? $baseOrders->where(function ($query) {
                $query->where('status', 'pending')->orWhere('payment_status', 'pending');
            })->count() : 0,
            'completedOrders' => $customerId ? $baseOrders->where('status', 'completed')->count() : 0,
            'totalSpent' => $customerId ? (float) $baseOrders->where('payment_status', 'verified')->where('status', '!=', 'cancelled')->sum('total') : 0,
            'lastOrder' => $orders->first(),
            'totalItems' => $customerId ? (int) Order::query()->where('customer_id', $customerId)->withSum('details', 'qty')->get()->sum('details_sum_qty') : 0,
            'customer' => $customer,
        ]);
    }

    public function profile()
    {
        return view('customer.profile', ['customer' => auth()->user()->customer]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $customer = $user->customer ?? Customer::query()->create([
            'nama' => $user->nama,
            'no_whatsapp' => '',
            'alamat' => null,
        ]);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($user->id_user)],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($user, $customer, $data) {
            $user->update([
                'nama' => $data['nama'],
                'email' => $data['email'],
            ]);

            $customer->update([
                'nama' => $data['nama'],
                'no_whatsapp' => $data['no_whatsapp'],
                'alamat' => $data['alamat'],
            ]);
        });

        return redirect()->route('customer.profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function submitReview(Request $request, int $productId)
    {
        $request->validate([
            'nama_customer' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string'],
        ]);

        Product::query()->findOrFail($productId);

        Rating::query()->create([
            'product_id' => $productId,
            'nama_customer' => $request->nama_customer,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        return back()->with('success', 'Ulasan berhasil dikirim.');
    }
}
