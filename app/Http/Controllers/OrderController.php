<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Rating;
use App\Models\StoreSetting;
use App\Models\UserSession;
use App\Services\WhatsAppCloudApi;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $paymentDetails = $order->payment_details
            ?? StoreSetting::current()->paymentInstructions((string) $order->payment_method);

        return view('customer.order-detail', compact('order', 'paymentDetails'));
    }

    public function proof(int $id, WhatsAppCloudApi $whatsApp)
    {
        $order = Order::query()->with(['customer', 'details.product', 'statusHistories.user'])
            ->where('customer_id', auth()->user()->customer_id)
            ->findOrFail($id);
        $paymentDetails = $order->payment_details
            ?? StoreSetting::current()->paymentInstructions((string) $order->payment_method);
        $whatsappUrl = $whatsApp->customerConfirmationUrl($order);

        return view('customer.order-proof', compact('order', 'paymentDetails', 'whatsappUrl'));
    }

    public function submitPaymentProof(Request $request, int $id)
    {
        $data = $request->validate([
            'payment_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);
        $oldProof = null;
        DB::transaction(function () use ($id, $data, &$oldProof) {
            $order = Order::query()
                ->where('customer_id', auth()->user()->customer_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($order->status !== 'pending'
                || $order->payment_method === 'cash'
                || $order->payment_status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment_proof' => 'Bukti hanya dapat dikirim untuk pembayaran non-tunai yang masih menunggu.',
                ]);
            }

            $oldProof = $order->payment_proof;
            $newProof = $data['payment_proof']->store('payment-proofs', 'public');
            $order->update(['payment_proof' => $newProof]);
        });

        if ($oldProof && str_starts_with($oldProof, 'payment-proofs/')) {
            Storage::disk('public')->delete($oldProof);
        }

        return back()->with('success', 'Bukti pembayaran berhasil dikirim dan menunggu verifikasi admin.');
    }

    public function confirmReceived(int $id)
    {
        DB::transaction(function () use ($id) {
            $order = Order::query()
                ->where('customer_id', auth()->user()->customer_id)
                ->lockForUpdate()
                ->findOrFail($id);

            if ($order->status !== 'ready') {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan hanya dapat dikonfirmasi setelah siap diterima.',
                ]);
            }

            if ($order->payment_status !== 'verified') {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran harus diterima atau diverifikasi terlebih dahulu.',
                ]);
            }

            $order->update(['status' => 'completed']);
            OrderStatusHistory::query()->create([
                'order_id' => $order->id_order,
                'from_status' => 'ready',
                'to_status' => 'completed',
                'changed_by' => auth()->id(),
                'source' => 'customer_confirmation',
            ]);
        });

        return redirect()->route('customer.order.detail', $id)
            ->with('success', 'Penerimaan pesanan berhasil dikonfirmasi.');
    }

    public function dashboard()
    {
        $user = auth()->user();
        $customer = $user?->customer;
        $customerId = $customer?->id_customer;

        $orders = $customerId
            ? Order::query()->where('customer_id', $customerId)->with('details.product')->latest('id_order')->limit(5)->get()
            : collect();
        $customerOrders = fn () => Order::query()->where('customer_id', $customerId);

        return view('customer.dashboard', [
            'orders' => $orders,
            'totalOrders' => $customerId ? $customerOrders()->count() : 0,
            'processingOrders' => $customerId ? $customerOrders()->whereIn('status', ['pending', 'confirmed', 'processing', 'ready'])->count() : 0,
            'waitingOrders' => $customerId ? $customerOrders()->where(function ($query) {
                $query->where('status', 'pending')->orWhere('payment_status', 'pending');
            })->count() : 0,
            'completedOrders' => $customerId ? $customerOrders()->where('status', 'completed')->count() : 0,
            'totalSpent' => $customerId ? (float) $customerOrders()->where('payment_status', 'verified')->where('status', '!=', 'cancelled')->sum('total') : 0,
            'lastOrder' => $orders->first(),
            'totalItems' => $customerId ? (int) Order::query()->where('customer_id', $customerId)->withSum('details', 'qty')->get()->sum('details_sum_qty') : 0,
            'customer' => $customer,
        ]);
    }

    public function security()
    {
        UserSession::syncExpiredSessions(auth()->id());

        if (! Schema::hasTable('user_sessions')) {
            return view('customer.security', ['sessions' => new LengthAwarePaginator([], 0, 10, 1)]);
        }

        $sessions = UserSession::query()
            ->where('user_id', auth()->id())
            ->orderByDesc('last_activity_at')
            ->orderByDesc('login_at')
            ->paginate(10)
            ->withQueryString();

        return view('customer.security', ['sessions' => $sessions]);
    }

    public function profile()
    {
        return view('customer.profile', ['customer' => auth()->user()->customer]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($user->id_user)],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($user, $data) {
            $customer = $user->customer;
            if (! $customer) {
                $customer = Customer::query()->create([
                    'nama' => $data['nama'],
                    'no_whatsapp' => $data['no_whatsapp'],
                    'alamat' => $data['alamat'] ?? null,
                ]);
                $user->customer()->associate($customer);
            }

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
            'komentar' => ['nullable', 'string', 'max:2000'],
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
