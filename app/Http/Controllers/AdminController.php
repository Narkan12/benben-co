<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\CompanyProfileImage;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Rating;
use App\Models\StockLog;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function settings()
    {
        return view('admin.settings');
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->nama = $data['nama'];
        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }
        $user->save();

        return back()->with('success', 'Pengaturan akun berhasil diperbarui.');
    }

    public function companyProfile()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $images = CompanyProfileImage::query()->orderBy('id')->get();

        return view('admin.company-profile', compact('images'));
    }

    public function updateCompanyProfileImage(Request $request, string $imageKey)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'gambar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $image = CompanyProfileImage::query()->where('image_key', $imageKey)->firstOrFail();
        $oldPath = $image->image_path;
        $newPath = $request->file('gambar')->store('company-profile', 'public');
        $image->update(['image_path' => $newPath]);

        if ($oldPath && str_starts_with($oldPath, 'company-profile/')) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('success', 'Gambar '.$image->label.' berhasil diperbarui.');
    }

    public function toggleOrderNotifications(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        Auth::user()->update([
            'order_notifications_enabled' => (bool) $data['enabled'],
        ]);

        return back()->with('success', 'Pengaturan notifikasi pesanan berhasil diperbarui.');
    }

    public function orderNotifications()
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $user = Auth::user();
        $pendingOrders = Order::query()
            ->where('source', 'online')
            ->where('status', 'pending')
            ->count();

        $newOrders = collect();
        if ($user->order_notifications_enabled) {
            $newOrders = Order::query()
                ->with('customer')
                ->where('source', 'online')
                ->where('status', 'pending')
                ->whereNull('admin_viewed_at')
                ->orderBy('id_order')
                ->limit(10)
                ->get();
        }

        return response()->json([
            'enabled' => (bool) $user->order_notifications_enabled,
            'pending_count' => $pendingOrders,
            'orders' => $newOrders->map(fn (Order $order) => [
                'id' => $order->id_order,
                'code' => $order->kode_order,
                'customer' => $order->customer?->nama ?? 'Pelanggan',
                'total' => (float) $order->total,
            ])->values(),
        ]);
    }

    public function markOrderNotificationsSeen(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1', 'max:10'],
            'order_ids.*' => ['required', 'integer', 'distinct'],
        ]);

        Order::query()
            ->whereIn('id_order', $data['order_ids'])
            ->where('source', 'online')
            ->where('status', 'pending')
            ->whereNull('admin_viewed_at')
            ->update(['admin_viewed_at' => now()]);

        return response()->json(['saved' => true]);
    }

    public function dashboard(Request $request)
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['today', '7d', 'month', 'custom'])],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $period = $validated['period'] ?? '7d';
        [$periodStart, $periodEnd] = match ($period) {
            'today' => [today()->startOfDay(), today()->endOfDay()],
            'month' => [today()->startOfMonth(), today()->endOfDay()],
            'custom' => [
                Carbon::parse($validated['start_date'] ?? today()->startOfMonth()->toDateString())->startOfDay(),
                Carbon::parse($validated['end_date'] ?? today()->toDateString())->endOfDay(),
            ],
            default => [today()->subDays(6)->startOfDay(), today()->endOfDay()],
        };
        $activeProducts = Product::query()->where('status', 'active')->count();
        $pendingOrders = Order::query()->whereIn('status', ['pending', 'confirmed', 'processing', 'ready'])->count();
        $pendingPaymentOrders = Order::query()
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'pending')
            ->count();
        $periodSales = $this->paidSalesOrders()
            ->whereBetween('tanggal', [$periodStart, $periodEnd])
            ->sum('total');
        $totalTransactions = Order::query()->count();
        $completedOrders = Order::query()->where('status', 'completed')->count();
        $totalRevenue = $this->paidSalesOrders()->sum('total');

        $salesByDate = $this->paidSalesOrders()
            ->selectRaw('DATE(tanggal) as sales_date, SUM(total) as sales_total')
            ->whereBetween('tanggal', [$periodStart, $periodEnd])
            ->groupBy('sales_date')
            ->pluck('sales_total', 'sales_date');
        $chartStart = $periodStart->copy();
        if ($chartStart->diffInDays($periodEnd) > 6) {
            $chartStart = $periodEnd->copy()->subDays(6)->startOfDay();
        }
        $salesLabels = collect(range(0, $chartStart->diffInDays($periodEnd)))->map(fn ($days) => $chartStart->copy()->addDays($days));
        $salesChartLabels = $salesLabels->map(fn ($date) => $date->translatedFormat('D'))->implode(',');
        $salesChartValues = $salesLabels->map(fn ($date) => (float) ($salesByDate[$date->toDateString()] ?? 0))->implode(',');
        $topProductSales = OrderDetail::query()
            ->whereHas('order', fn (Builder $query) => $this->applyPaidSalesConditions($query)
                ->whereBetween('tanggal', [$periodStart, $periodEnd]))
            ->select('product_id')
            ->selectRaw('SUM(qty) as units_sold')
            ->groupBy('product_id')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();
        $topProducts = Product::query()->whereIn('id_product', $topProductSales->pluck('product_id'))->get()
            ->sortBy(fn ($product) => $topProductSales->search(fn ($sale) => $sale->product_id === $product->id_product));
        $topProductLabels = $topProducts->pluck('nama')->implode(',');
        $topProductValues = $topProducts->map(function ($product) use ($topProductSales) {
            return (int) $topProductSales->firstWhere('product_id', $product->id_product)->units_sold;
        })->implode(',');
        $outOfStock = Product::query()->where('status', 'active')->where('stok', '<=', 5)->orderBy('stok')->orderBy('nama')->get();
        $latestOrders = Order::query()->with('customer')->latest('id_order')->limit(5)->get();
        $pendingOnlineOrders = Order::query()
            ->where('source', 'online')
            ->where('status', 'pending')
            ->count();
        $orderNotificationsEnabled = (bool) Auth::user()->order_notifications_enabled;
        $ratingCount = Rating::query()->count();
        $ratingAverage = round((float) (Rating::query()->avg('rating') ?? 0), 1);
        $recentRatings = Rating::query()
            ->with('product')
            ->latest('created_at')
            ->latest('id_rating')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'activeProducts',
            'pendingOrders',
            'pendingPaymentOrders',
            'periodSales',
            'period',
            'periodStart',
            'periodEnd',
            'completedOrders',
            'totalRevenue',
            'totalTransactions',
            'topProducts',
            'salesChartLabels',
            'salesChartValues',
            'topProductLabels',
            'topProductValues',
            'outOfStock',
            'latestOrders',
            'pendingOnlineOrders',
            'orderNotificationsEnabled',
            'ratingCount',
            'ratingAverage',
            'recentRatings'
        ));
    }

    public function products(Request $request)
    {
        $products = Product::query()->with('category')
            ->when($request->filled('search'), fn (Builder $query) => $query->where('nama', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category'), fn (Builder $query) => $query->where('category_id', $request->integer('category')))
            ->latest('id_product')->paginate(10)->withQueryString();
        $categories = Category::query()->orderBy('nama_kategori')->get();

        return view('admin.products', compact('products', 'categories'));
    }

    public function categories()
    {
        $categories = Category::query()->withCount('products')->latest('id_category')->paginate(10);

        return view('admin.categories', compact('categories'));
    }

    public function orders(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'])],
        ]);
        $orders = Order::query()->with(['customer', 'details.product', 'statusHistories.user'])
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('kode_order', 'like', $search)
                        ->orWhereHas('customer', fn (Builder $customer) => $customer->where('nama', 'like', $search));
                });
            })
            ->latest('id_order')->paginate(10)->withQueryString();
        $statusOptions = $orders->getCollection()->mapWithKeys(function (Order $order) {
            $nextStatuses = $this->orderStatusTransitions()[$order->status] ?? [];

            if ($order->payment_method !== 'cash' && $order->payment_status !== 'verified') {
                $nextStatuses = array_values(array_diff($nextStatuses, ['confirmed']));
            }

            return [$order->id_order => $nextStatuses];
        });

        return view('admin.orders', compact('orders', 'statusOptions'));
    }

    public function promotions()
    {
        $promos = Promotion::query()->latest('id_promo')->paginate(10);

        return view('admin.promotions', compact('promos'));
    }

    public function payments()
    {
        $orders = Order::query()->with('customer')
            ->where('payment_status', 'pending')
            ->where('status', 'pending')
            ->where(fn (Builder $query) => $query->whereNull('payment_method')->orWhere('payment_method', '!=', 'cash'))
            ->latest('id_order')->paginate(10)->withQueryString();

        return view('admin.payments', compact('orders'));
    }

    public function customers(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $customers = Customer::query()->withCount('orders')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(fn (Builder $builder) => $builder->where('nama', 'like', $search)
                    ->orWhere('no_whatsapp', 'like', $search));
            })
            ->latest('id_customer')->paginate(10)->withQueryString();

        return view('admin.customers', compact('customers'));
    }

    public function users(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:admin,kasir'],
        ]);

        $users = User::query()
            ->whereIn('role', ['admin', 'kasir'])
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('nama', 'like', $search)
                        ->orWhere('username', 'like', $search)
                        ->orWhere('email', 'like', $search);
                });
            })
            ->when($request->filled('role'), fn (Builder $query) => $query->where('role', $request->string('role')))
            ->orderByDesc('id_user')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function storeUser(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email:rfc', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:admin,kasir'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $user = User::query()->create([
            'nama' => $data['nama'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'role' => $data['role'],
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        $this->logActivity('user_created', 'Admin menambahkan akun '.$user->nama.' sebagai '.ucfirst($user->role), 'user', $user->id_user, ['role' => $user->role]);

        return back()->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function updateUser(Request $request, int $userId)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $user = User::query()->findOrFail($userId);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', Rule::unique('users', 'username')->ignore($user->id_user, 'id_user')],
            'email' => ['nullable', 'email:rfc', 'max:150', Rule::unique('users', 'email')->ignore($user->id_user, 'id_user')],
            'role' => ['required', 'in:admin,kasir'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->fill([
            'nama' => $data['nama'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'role' => $data['role'],
        ]);

        if (filled($data['password'] ?? null)) {
            $user->password = $data['password'];
        }

        $user->save();

        $this->logActivity('user_updated', 'Admin memperbarui data akun '.$user->nama, 'user', $user->id_user, ['role' => $user->role]);

        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function toggleUserStatus(Request $request, int $userId)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $user = User::query()->findOrFail($userId);
        if ((int) Auth::id() === (int) $user->id_user) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = ! (bool) $user->is_active;
        $user->save();

        $this->logActivity('user_status_updated', 'Admin '.((bool) $user->is_active ? 'mengaktifkan' : 'menonaktifkan').' akun '.$user->nama, 'user', $user->id_user, ['status' => $user->is_active ? 'active' : 'inactive']);

        return back()->with('success', 'Status akun berhasil diperbarui.');
    }

    public function deleteUser(Request $request, int $userId)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $user = User::query()->findOrFail($userId);
        if ((int) Auth::id() === (int) $user->id_user) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->nama;
        $user->delete();

        $this->logActivity('user_deleted', 'Admin menghapus akun '.$name, 'user', $userId, ['deleted_name' => $name]);

        return back()->with('success', 'Akun berhasil dihapus.');
    }

    public function history(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', 'string', 'max:80'],
            'role' => ['nullable', 'in:admin,kasir,customer'],
            'status' => ['nullable', 'in:active,logged_out,expired,failed,success'],
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        UserSession::syncExpiredSessions();

        $sessionTableExists = Schema::hasTable('user_sessions');

        $logs = ActivityLog::query()->with('user')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('description', 'like', $search)
                        ->orWhere('activity_type', 'like', $search)
                        ->orWhere('user_name', 'like', $search)
                        ->orWhere('user_email', 'like', $search);
                });
            })
            ->when($request->filled('type'), fn (Builder $query) => $query->where('activity_type', $request->string('type')))
            ->when($request->filled('role'), fn (Builder $query) => $query->where('role_name', $request->string('role')))
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('start_date'), fn (Builder $query) => $query->whereDate('created_at', '>=', $request->string('start_date')))
            ->when($request->filled('end_date'), fn (Builder $query) => $query->whereDate('created_at', '<=', $request->string('end_date')))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $summary = [
            'total_activity' => ActivityLog::query()->count(),
            'admin_activity' => ActivityLog::query()->where('role_name', 'admin')->count(),
            'customer_activity' => ActivityLog::query()->where('role_name', 'customer')->count(),
            'active_sessions' => $sessionTableExists ? UserSession::query()->where('status', 'active')->count() : 0,
            'ended_sessions' => $sessionTableExists ? UserSession::query()->whereIn('status', ['logged_out', 'expired'])->count() : 0,
        ];

        $activityTypes = ActivityLog::query()->select('activity_type')->distinct()->pluck('activity_type');

        return view('admin.history', compact('logs', 'activityTypes', 'summary'));
    }

    public function sessions(Request $request)
    {
        abort_unless(Auth::user()->role === 'admin', 403);

        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,logged_out,expired'],
            'role' => ['nullable', 'in:admin,kasir,customer'],
        ]);

        UserSession::syncExpiredSessions();

        if (! Schema::hasTable('user_sessions')) {
            return view('admin.sessions', ['sessions' => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15, 1)]);
        }

        $sessions = UserSession::query()->with('user')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function (Builder $builder) use ($search) {
                    $builder->whereHas('user', fn (Builder $userQuery) => $userQuery->where('nama', 'like', $search)->orWhere('email', 'like', $search))
                        ->orWhere('ip_address', 'like', $search)
                        ->orWhere('user_agent', 'like', $search);
                });
            })
            ->when($request->filled('status'), fn (Builder $query) => $query->where('status', $request->string('status')))
            ->when($request->filled('role'), fn (Builder $query) => $query->whereHas('user', fn (Builder $userQuery) => $userQuery->where('role', $request->string('role'))))
            ->orderByDesc('last_activity_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sessions', compact('sessions'));
    }

    public function ratings(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'rating' => ['nullable', 'integer', 'between:1,5'],
        ]);

        $ratings = Rating::query()
            ->with('product')
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = '%'.$request->string('search').'%';
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('nama_customer', 'like', $search)
                        ->orWhere('komentar', 'like', $search)
                        ->orWhereHas('product', fn (Builder $productQuery) => $productQuery->where('nama', 'like', $search));
                });
            })
            ->when($request->filled('rating'), fn (Builder $query) => $query->where('rating', $request->integer('rating')))
            ->latest('created_at')
            ->latest('id_rating')
            ->paginate(10)
            ->withQueryString();
        $ratingCount = Rating::query()->count();
        $ratingAverage = round((float) (Rating::query()->avg('rating') ?? 0), 1);

        return view('admin.ratings', compact('ratings', 'ratingCount', 'ratingAverage'));
    }

    public function inventory()
    {
        $products = Product::query()
            ->where('status', 'active')
            ->orderBy('stok')
            ->paginate(10);

        return view('admin.inventory', compact('products'));
    }

    public function reportTransactions(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
            'payment_status' => ['nullable', 'in:pending,verified,rejected'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'])],
        ]);

        $startDate = $validated['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();
        $dateRange = [$startDate.' 00:00:00', $endDate.' 23:59:59'];

        $query = Order::query()->with(['customer', 'details.product'])
            ->whereBetween('tanggal', $dateRange)
            ->when($request->filled('payment_status'), fn (Builder $builder) => $builder->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('status'), fn (Builder $builder) => $builder->where('status', $request->string('status')))
            ->when($request->filled('search'), function (Builder $builder) use ($request) {
                $search = '%'.$request->string('search').'%';
                $builder->where(function (Builder $cond) use ($search) {
                    $cond->where('kode_order', 'like', $search)
                        ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery->where('nama', 'like', $search));
                });
            });

        $rows = $query->latest('tanggal')->latest('id_order')->get();
        $summary = [
            'label' => 'Transaksi',
            'count' => $rows->count(),
            'revenue' => (float) $rows->where('status', '!=', 'cancelled')->where('payment_status', 'verified')->sum('total'),
            'paid' => $rows->where('status', '!=', 'cancelled')->where('payment_status', 'verified')->count(),
            'unpaid' => $rows->where('status', '!=', 'cancelled')->where('payment_status', 'pending')->count(),
        ];

        return view('admin.report-page', [
            'reportType' => 'transactions',
            'title' => 'Laporan Transaksi',
            'rows' => $rows,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'searchLabel' => 'Nomor transaksi / pelanggan',
        ]);
    }

    public function reportSales(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();
        $dateRange = [$startDate.' 00:00:00', $endDate.' 23:59:59'];
        $validOrders = $this->paidSalesOrders()->whereBetween('tanggal', $dateRange)->get();
        $productTotals = OrderDetail::query()
            ->whereHas('order', fn (Builder $query) => $this->applyPaidSalesConditions($query)->whereBetween('tanggal', $dateRange))
            ->with('product')
            ->select('product_id', DB::raw('SUM(qty) as sold_qty'))
            ->addSelect(DB::raw('SUM(subtotal) as sold_subtotal'))
            ->groupBy('product_id')
            ->orderByDesc('sold_qty')
            ->get();
        $totalQty = (int) $productTotals->sum('sold_qty');
        $rows = $productTotals->map(function (OrderDetail $item) {
            return [
                'product' => $item->product?->nama ?? 'Produk tidak tersedia',
                'qty' => (int) $item->sold_qty,
                'subtotal' => (float) $item->sold_subtotal,
            ];
        });

        $summary = [
            'label' => 'Penjualan',
            'count' => $validOrders->count(),
            'revenue' => (float) $validOrders->sum('total'),
            'qty' => $totalQty,
            'bestSeller' => $productTotals->first() ? (Product::query()->find($productTotals->first()->product_id)?->nama ?? 'Produk tidak tersedia') : 'Belum ada penjualan',
        ];

        return view('admin.report-page', [
            'reportType' => 'sales',
            'title' => 'Laporan Penjualan',
            'rows' => $rows,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'searchLabel' => 'Cari produk',
        ]);
    }

    public function reportStock(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'category' => ['nullable', 'integer', 'exists:categories,id_category'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $products = Product::query()->with('category')
            ->when($request->filled('category'), fn (Builder $query) => $query->where('category_id', $request->integer('category')))
            ->when($request->filled('search'), fn (Builder $query) => $query->where('nama', 'like', '%'.$request->string('search').'%'))
            ->orderBy('nama')
            ->get();

        $summary = [
            'label' => 'Stok',
            'count' => $products->count(),
            'low_stock' => $products->where('stok', '>', 0)->where('stok', '<=', 5)->count(),
            'out_of_stock' => $products->where('stok', '<=', 0)->count(),
            'healthy' => $products->where('stok', '>', 5)->count(),
        ];

        return view('admin.report-page', [
            'reportType' => 'stock',
            'title' => 'Laporan Stok Barang',
            'rows' => $products,
            'summary' => $summary,
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
            'startDate' => $validated['start_date'] ?? today()->startOfMonth()->toDateString(),
            'endDate' => $validated['end_date'] ?? today()->toDateString(),
            'searchLabel' => 'Cari nama produk',
        ]);
    }

    public function reportProducts(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id_category'],
        ]);

        $products = Product::query()->with('category')
            ->when($request->filled('search'), fn (Builder $query) => $query->where('nama', 'like', '%'.$request->string('search').'%'))
            ->when($request->filled('category'), fn (Builder $query) => $query->where('category_id', $request->integer('category')))
            ->orderBy('nama')
            ->get();

        $summary = [
            'label' => 'Produk',
            'count' => $products->count(),
            'categories' => $products->pluck('category_id')->filter()->unique()->count(),
            'active' => $products->where('status', 'active')->count(),
            'inactive' => $products->where('status', 'inactive')->count(),
        ];

        return view('admin.report-page', [
            'reportType' => 'products',
            'title' => 'Laporan Produk',
            'rows' => $products,
            'summary' => $summary,
            'categories' => Category::query()->orderBy('nama_kategori')->get(),
            'startDate' => today()->startOfMonth()->toDateString(),
            'endDate' => today()->toDateString(),
            'searchLabel' => 'Cari produk',
        ]);
    }

    public function reportFinance(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        $startDate = $validated['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();
        $dateRange = [$startDate.' 00:00:00', $endDate.' 23:59:59'];
        $orders = Order::query()
            ->whereBetween('tanggal', $dateRange)
            ->when($request->filled('search'), fn (Builder $query) => $query->where('kode_order', 'like', '%'.$request->string('search').'%'))
            ->latest('tanggal')->latest('id_order')->get();
        $summary = [
            'label' => 'Keuangan',
            'paid' => (float) $orders->where('status', '!=', 'cancelled')->where('payment_status', 'verified')->sum('total'),
            'unpaid' => (float) $orders->where('status', '!=', 'cancelled')->where('payment_status', 'pending')->sum('total'),
            'cancelled' => (float) $orders->where('status', 'cancelled')->sum('total'),
        ];

        return view('admin.report-page', [
            'reportType' => 'finance',
            'title' => 'Laporan Keuangan',
            'rows' => $orders,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'searchLabel' => 'Cari nomor pesanan',
        ]);
    }

    public function reports()
    {
        $validated = request()->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $startDate = $validated['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();
        $dateRange = [$startDate.' 00:00:00', $endDate.' 23:59:59'];
        $dailySales = $this->paidSalesOrders()
            ->selectRaw('DATE(tanggal) as sales_date, COUNT(*) as transactions, SUM(total) as sales_total')
            ->whereBetween('tanggal', $dateRange)
            ->groupBy('sales_date')->orderBy('sales_date')->get();
        $reportTotal = (float) $dailySales->sum('sales_total');
        $reportTransactions = (int) $dailySales->sum('transactions');
        $reportUnits = (int) OrderDetail::query()
            ->whereHas('order', fn (Builder $query) => $this->applyPaidSalesConditions($query)
                ->whereBetween('tanggal', $dateRange))
            ->sum('qty');
        $paymentMethods = $this->paidSalesOrders()
            ->selectRaw('payment_method, COUNT(*) as transactions, SUM(total) as sales_total')
            ->whereBetween('tanggal', $dateRange)
            ->groupBy('payment_method')
            ->get()
            ->keyBy(fn ($row) => in_array($row->payment_method, ['cash', 'transfer', 'ewallet', 'other'], true)
                ? $row->payment_method
                : 'unknown');
        $reportOrders = $this->paidSalesOrders()
            ->with(['customer', 'details.product'])
            ->whereBetween('tanggal', $dateRange)
            ->latest('tanggal')
            ->latest('id_order')
            ->get();

        return view('admin.reports', compact(
            'dailySales',
            'paymentMethods',
            'reportTotal',
            'reportTransactions',
            'reportUnits',
            'startDate',
            'endDate',
            'reportOrders'
        ));
    }

    public function exportReport(Request $request, string $type, string $format)
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date_format:Y-m-d'],
            'end_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:start_date'],
            'search' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'integer', 'exists:categories,id_category'],
            'payment_status' => ['nullable', 'in:pending,verified,rejected'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled'])],
        ]);

        $startDate = $validated['start_date'] ?? today()->startOfMonth()->toDateString();
        $endDate = $validated['end_date'] ?? today()->toDateString();
        $dateRange = [$startDate.' 00:00:00', $endDate.' 23:59:59'];
        if (! in_array($format, ['csv', 'excel', 'pdf'], true)) {
            abort(404, 'Format export tidak didukung.');
        }

        $report = $this->reportExportData($type, $validated, $dateRange);
        $snapshot = in_array($type, ['stock', 'products'], true);
        $filename = str_replace(' ', '_', $report['title']).'_'
            .($snapshot
                ? Carbon::parse($endDate)->format('d-m-Y')
                : Carbon::parse($startDate)->format('d-m-Y').'_sampai_'.Carbon::parse($endDate)->format('d-m-Y'))
            .($format === 'excel' ? '.xlsx' : '.'.$format);

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($report) {
                $output = fopen('php://output', 'w');
                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, $report['headers']);
                foreach ($report['rows'] as $row) {
                    fputcsv($output, array_map(fn ($value) => is_float($value) ? number_format($value, 2, '.', '') : $value, array_values($row)));
                }
                fclose($output);
            }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        if ($format === 'excel') {
            $xlsx = $this->buildStyledXlsxSpreadsheet(
                $report['title'],
                $startDate,
                $endDate,
                $report['summary'],
                $report['headers'],
                $report['rows'],
                $report['landscape']
            );

            return response($xlsx, 200)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="'.$filename.'"');
        }

        $pdf = $this->buildPaginatedReportPdf(
            $report['title'],
            $startDate,
            $endDate,
            $report['summary'],
            $report['headers'],
            $report['rows'],
            $report['landscape']
        );

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Content-Length', (string) strlen($pdf))
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function reportExportData(string $type, array $filters, array $dateRange): array
    {
        $search = $filters['search'] ?? null;
        $category = $filters['category'] ?? null;

        return match ($type) {
            'transactions' => $this->transactionExportData($filters, $dateRange, $search),
            'sales' => $this->salesExportData($dateRange),
            'legacy-sales' => $this->legacySalesExportData($dateRange),
            'stock' => $this->stockExportData($search, $category),
            'products' => $this->productExportData($search, $category),
            'finance' => $this->financeExportData($dateRange, $search),
            default => abort(404, 'Jenis laporan tidak ditemukan.'),
        };
    }

    private function transactionExportData(array $filters, array $dateRange, ?string $search): array
    {
        $orders = Order::query()->with(['customer', 'details.product'])
            ->whereBetween('tanggal', $dateRange)
            ->when(isset($filters['payment_status']), fn (Builder $query) => $query->where('payment_status', $filters['payment_status']))
            ->when(isset($filters['status']), fn (Builder $query) => $query->where('status', $filters['status']))
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $builder) use ($search) {
                    $builder->where('kode_order', 'like', '%'.$search.'%')
                        ->orWhereHas('customer', fn (Builder $customerQuery) => $customerQuery->where('nama', 'like', '%'.$search.'%'));
                });
            })
            ->latest('tanggal')->latest('id_order')->get();

        $rows = $orders->map(fn (Order $order) => [
            'Nomor Transaksi' => $order->kode_order,
            'Tanggal' => $order->tanggal?->format('Y-m-d H:i'),
            'Pelanggan' => $order->customer?->nama ?? 'Pelanggan umum',
            'Produk' => $order->details->map(fn (OrderDetail $detail) => ($detail->product?->nama ?? 'Produk dihapus').' x'.$detail->qty)->join(', '),
            'Jumlah Item' => (int) $order->details->sum('qty'),
            'Total' => (float) $order->total,
            'Metode Pembayaran' => $order->payment_method ?? '-',
            'Status Pembayaran' => $order->payment_status ?? '-',
            'Status Pesanan' => $order->status,
        ])->all();

        return [
            'title' => 'Laporan Transaksi',
            'headers' => ['Nomor Transaksi', 'Tanggal', 'Pelanggan', 'Produk', 'Jumlah Item', 'Total', 'Metode Pembayaran', 'Status Pembayaran', 'Status Pesanan'],
            'rows' => $rows,
            'summary' => [
                'Jumlah transaksi' => $orders->count(),
                'Transaksi dibayar' => $orders->where('status', '!=', 'cancelled')->where('payment_status', 'verified')->count(),
                'Transaksi belum dibayar' => $orders->where('status', '!=', 'cancelled')->where('payment_status', 'pending')->count(),
                'Pembayaran diterima' => (float) $orders->where('status', '!=', 'cancelled')->where('payment_status', 'verified')->sum('total'),
            ],
            'landscape' => true,
        ];
    }

    private function salesExportData(array $dateRange): array
    {
        $orders = $this->paidSalesOrders()->whereBetween('tanggal', $dateRange)->get();
        $totals = OrderDetail::query()
            ->whereHas('order', fn (Builder $query) => $this->applyPaidSalesConditions($query)->whereBetween('tanggal', $dateRange))
            ->with('product')
            ->select('product_id', DB::raw('SUM(qty) as sold_qty'), DB::raw('SUM(subtotal) as sold_subtotal'))
            ->groupBy('product_id')
            ->orderByDesc('sold_qty')
            ->get();
        $rows = $totals->map(fn (OrderDetail $item) => [
            'Produk' => $item->product?->nama ?? 'Produk tidak tersedia',
            'Jumlah Terjual' => (int) $item->sold_qty,
            'Subtotal' => (float) $item->sold_subtotal,
        ])->all();

        return [
            'title' => 'Laporan Penjualan',
            'headers' => ['Produk', 'Jumlah Terjual', 'Subtotal'],
            'rows' => $rows,
            'summary' => [
                'Jumlah transaksi berhasil' => $orders->count(),
                'Total produk terjual' => (int) $totals->sum('sold_qty'),
                'Total pendapatan' => (float) $orders->sum('total'),
                'Produk terlaris' => $totals->first()?->product?->nama ?? 'Belum ada penjualan',
            ],
            'landscape' => false,
        ];
    }

    private function legacySalesExportData(array $dateRange): array
    {
        $orders = $this->paidSalesOrders()
            ->with(['customer', 'details.product'])
            ->whereBetween('tanggal', $dateRange)
            ->latest('tanggal')->latest('id_order')->get();
        $quantity = (int) $orders->sum(fn (Order $order) => $order->details->sum('qty'));
        $total = (float) $orders->sum('total');
        $paymentLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
            'other' => 'Metode lain (data lama)',
        ];

        return [
            'title' => 'Laporan Penjualan',
            'headers' => ['Kode Transaksi', 'Tanggal', 'Customer', 'Nomor WhatsApp', 'Detail Barang', 'Pembayaran', 'Total'],
            'rows' => $orders->map(fn (Order $order) => [
                'Kode Transaksi' => $order->kode_order,
                'Tanggal' => $order->tanggal?->format('Y-m-d H:i'),
                'Customer' => $order->customer?->nama ?? 'Pelanggan umum',
                'Nomor WhatsApp' => $order->customer?->no_whatsapp ?? '-',
                'Detail Barang' => $order->details->map(fn (OrderDetail $detail) => ($detail->product?->nama ?? 'Produk dihapus').' x'.$detail->qty)->join(', '),
                'Pembayaran' => $paymentLabels[$order->payment_method] ?? 'Tidak diketahui',
                'Total' => (float) $order->total,
            ])->all(),
            'summary' => [
                'Pembayaran diterima' => $total,
                'Transaksi terbayar' => $orders->count(),
                'Produk terjual' => $quantity,
                'Rata-rata transaksi' => $orders->count() ? $total / $orders->count() : 0,
            ],
            'landscape' => true,
        ];
    }

    private function stockExportData(?string $search, ?string $category): array
    {
        $products = Product::query()->with('category')
            ->when($category, fn (Builder $query) => $query->where('category_id', $category))
            ->when($search, fn (Builder $query) => $query->where('nama', 'like', '%'.$search.'%'))
            ->orderBy('nama')->get();

        return [
            'title' => 'Laporan Stok Barang',
            'headers' => ['Produk', 'Kategori', 'Stok', 'Harga', 'Status Stok'],
            'rows' => $products->map(fn (Product $product) => [
                'Produk' => $product->nama,
                'Kategori' => $product->category?->nama_kategori ?? '-',
                'Stok' => (int) $product->stok,
                'Harga' => (float) $product->harga,
                'Status Stok' => $product->stok > 5 ? 'Aman' : ($product->stok > 0 ? 'Menipis' : 'Habis'),
            ])->all(),
            'summary' => [
                'Jumlah produk' => $products->count(),
                'Total stok tersedia' => (int) $products->sum('stok'),
                'Produk stok menipis' => $products->where('stok', '>', 0)->where('stok', '<=', 5)->count(),
                'Produk stok habis' => $products->where('stok', '<=', 0)->count(),
            ],
            'landscape' => false,
        ];
    }

    private function productExportData(?string $search, ?string $category): array
    {
        $products = Product::query()->with('category')
            ->when($category, fn (Builder $query) => $query->where('category_id', $category))
            ->when($search, fn (Builder $query) => $query->where('nama', 'like', '%'.$search.'%'))
            ->orderBy('nama')->get();

        return [
            'title' => 'Laporan Produk',
            'headers' => ['Produk', 'Kategori', 'Harga', 'Stok', 'Status'],
            'rows' => $products->map(fn (Product $product) => [
                'Produk' => $product->nama,
                'Kategori' => $product->category?->nama_kategori ?? '-',
                'Harga' => (float) $product->harga,
                'Stok' => (int) $product->stok,
                'Status' => $product->status,
            ])->all(),
            'summary' => [
                'Jumlah produk' => $products->count(),
                'Jumlah kategori' => $products->pluck('category_id')->filter()->unique()->count(),
                'Produk aktif' => $products->where('status', 'active')->count(),
                'Produk nonaktif' => $products->where('status', 'inactive')->count(),
            ],
            'landscape' => false,
        ];
    }

    private function financeExportData(array $dateRange, ?string $search): array
    {
        $orders = Order::query()->whereBetween('tanggal', $dateRange)
            ->when($search, fn (Builder $query) => $query->where('kode_order', 'like', '%'.$search.'%'))
            ->latest('tanggal')->latest('id_order')->get();

        return [
            'title' => 'Laporan Keuangan',
            'headers' => ['Nomor Transaksi', 'Tanggal', 'Status Pembayaran', 'Status Pesanan', 'Total'],
            'rows' => $orders->map(fn (Order $order) => [
                'Nomor Transaksi' => $order->kode_order,
                'Tanggal' => $order->tanggal?->format('Y-m-d H:i'),
                'Status Pembayaran' => $order->payment_status ?? '-',
                'Status Pesanan' => $order->status,
                'Total' => (float) $order->total,
            ])->all(),
            'summary' => [
                'Pembayaran diterima' => (float) $orders->where('status', '!=', 'cancelled')->where('payment_status', 'verified')->sum('total'),
                'Nilai belum dibayar' => (float) $orders->where('status', '!=', 'cancelled')->where('payment_status', 'pending')->sum('total'),
                'Nilai dibatalkan' => (float) $orders->where('status', 'cancelled')->sum('total'),
            ],
            'landscape' => false,
        ];
    }

    public function exportReports(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $dailySales = $this->paidSalesOrders()
            ->selectRaw('DATE(tanggal) as sales_date, COUNT(*) as transactions, SUM(total) as sales_total')
            ->whereBetween('tanggal', [$validated['start_date'].' 00:00:00', $validated['end_date'].' 23:59:59'])
            ->groupBy('sales_date')->orderBy('sales_date')->get();

        return response()->streamDownload(function () use ($dailySales) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Tanggal', 'Transaksi Terbayar', 'Pembayaran Diterima']);
            foreach ($dailySales as $row) {
                fputcsv($output, [$row->sales_date, $row->transactions, $row->sales_total]);
            }
            fclose($output);
        }, 'laporan-penjualan.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportReportsExcel(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date_format:Y-m-d'],
            'end_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:start_date'],
        ]);
        $report = $this->legacySalesExportData([
            $validated['start_date'].' 00:00:00',
            $validated['end_date'].' 23:59:59',
        ]);
        $xlsx = $this->buildStyledXlsxSpreadsheet(
            $report['title'],
            $validated['start_date'],
            $validated['end_date'],
            $report['summary'],
            $report['headers'],
            $report['rows'],
            $report['landscape']
        );
        $filename = 'Laporan_Penjualan_'.Carbon::parse($validated['start_date'])->format('d-m-Y')
            .'_sampai_'.Carbon::parse($validated['end_date'])->format('d-m-Y').'.xlsx';

        return response($xlsx, 200)
            ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'"')
            ->header('Content-Length', (string) strlen($xlsx))
            ->header('X-Content-Type-Options', 'nosniff');
    }

    private function buildXlsxSpreadsheet(array $headers, array $rows, string $title): string
    {
        $zip = new \ZipArchive;
        $temp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip->open($temp, \ZipArchive::OVERWRITE | \ZipArchive::CREATE);

        $sheetRows = [];
        $sheetRows[] = $headers;
        foreach ($rows as $row) {
            $sheetRows[] = array_values($row);
        }

        $sheetXml = '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData>';
        foreach ($sheetRows as $rowIndex => $rowValues) {
            $sheetXml .= '<row r="'.($rowIndex + 1).'">';
            foreach ($rowValues as $cellIndex => $cellValue) {
                $cellLetter = $this->excelColumnName($cellIndex + 1);
                $cellXml = '<c r="'.$cellLetter.($rowIndex + 1).'" t="inlineStr"><is><t>'.htmlspecialchars((string) $cellValue, ENT_XML1).'</t></is></c>';
                $sheetXml .= $cellXml;
            }
            $sheetXml .= '</row>';
        }
        $sheetXml .= '</sheetData></worksheet>';

        $zip->addFromString('[Content_Types].xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>
  <Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>
  <Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>
</Relationships>
XML);

        $zip->addFromString('docProps/core.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <dc:creator>BENBEN CO. Bakery</dc:creator>
  <cp:lastModifiedBy>BENBEN CO. Bakery</cp:lastModifiedBy>
  <dcterms:created xsi:type="dcterms:W3CDTF">2026-11-11T00:00:00Z</dcterms:created>
  <dcterms:modified xsi:type="dcterms:W3CDTF">2026-11-11T00:00:00Z</dcterms:modified>
</cp:coreProperties>
XML);

        $zip->addFromString('docProps/app.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">
  <Application>Microsoft Excel</Application>
</Properties>
XML);

        $zip->addFromString('xl/workbook.xml', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Sheet1" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        $xlsx = file_get_contents($temp);
        unlink($temp);

        return (string) $xlsx;
    }

    private function excelColumnName(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $index--;
            $letter = chr(65 + ($index % 26)).$letter;
            $index = intdiv($index, 26);
        }

        return $letter;
    }

    private function buildStyledXlsxSpreadsheet(
        string $title,
        string $startDate,
        string $endDate,
        array $summary,
        array $headers,
        array $rows,
        bool $landscape
    ): string {
        $zip = new \ZipArchive;
        $temp = tempnam(sys_get_temp_dir(), 'benben-report-');
        if ($temp === false) {
            throw new \RuntimeException('Tidak dapat menyiapkan file sementara untuk laporan Excel.');
        }

        try {
            if ($zip->open($temp, \ZipArchive::OVERWRITE | \ZipArchive::CREATE) !== true) {
                throw new \RuntimeException('Tidak dapat membuka file sementara untuk laporan Excel.');
            }

            $columnCount = max(1, count($headers));
            $lastColumn = $this->excelColumnName($columnCount);
            $tableHeaderRow = 8 + count($summary);
            $lastRow = $tableHeaderRow + count($rows);
            $period = in_array($title, ['Laporan Stok Barang', 'Laporan Produk'], true)
                ? 'Periode data: Snapshot per '.Carbon::parse($endDate)->format('d-m-Y')
                : 'Periode: '.Carbon::parse($startDate)->format('d-m-Y').' - '.Carbon::parse($endDate)->format('d-m-Y');
            $sheetRows = [
                1 => [['value' => $title, 'style' => 1]],
                2 => [['value' => 'BENBEN CO. Bakery', 'style' => 2]],
                3 => [['value' => $period, 'style' => 3]],
                4 => [['value' => 'Dicetak: '.now()->format('d-m-Y H:i'), 'style' => 3]],
                6 => [['value' => 'RINGKASAN', 'style' => 4]],
            ];
            $summaryRow = 7;
            foreach ($summary as $label => $value) {
                $sheetRows[$summaryRow] = [
                    ['value' => $label, 'style' => 5],
                    ['value' => $value, 'style' => $this->isCurrencyReportField((string) $label) ? 9 : 8],
                ];
                $summaryRow++;
            }
            $sheetRows[$tableHeaderRow] = array_map(fn ($header) => ['value' => $header, 'style' => 6], $headers);
            foreach ($rows as $rowIndex => $row) {
                $sheetRow = $tableHeaderRow + $rowIndex + 1;
                $sheetRows[$sheetRow] = [];
                foreach (array_values($row) as $columnIndex => $value) {
                    $isNumeric = is_int($value) || is_float($value);
                    $isCurrency = $this->isCurrencyReportField((string) ($headers[$columnIndex] ?? ''));
                    $sheetRows[$sheetRow][] = [
                        'value' => $value,
                        'style' => $isNumeric
                            ? ($isCurrency ? ($rowIndex % 2 ? 12 : 9) : ($rowIndex % 2 ? 11 : 8))
                            : ($rowIndex % 2 ? 10 : 7),
                    ];
                }
            }

            $widths = [];
            foreach ($headers as $index => $header) {
                $maxLength = mb_strlen((string) $header);
                foreach ($rows as $row) {
                    $maxLength = max($maxLength, mb_strlen((string) (array_values($row)[$index] ?? '')));
                }
                $widths[] = min(50, max(12, $maxLength + 2));
            }

            $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                .'<sheetPr><pageSetUpPr fitToPage="1"/></sheetPr>'
                .'<dimension ref="A1:'.$lastColumn.$lastRow.'"/>'
                .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="'.$tableHeaderRow.'" topLeftCell="A'.($tableHeaderRow + 1).'" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A'.($tableHeaderRow + 1).'" sqref="A'.($tableHeaderRow + 1).'"/></sheetView></sheetViews>'
                .'<cols>';
            foreach ($widths as $index => $width) {
                $column = $index + 1;
                $sheetXml .= '<col min="'.$column.'" max="'.$column.'" width="'.$width.'" customWidth="1"/>';
            }
            $sheetXml .= '</cols><sheetData>';
            foreach ($sheetRows as $rowNumber => $cells) {
                $sheetXml .= '<row r="'.$rowNumber.'"'.($rowNumber <= 4 ? ' ht="24" customHeight="1"' : '').'>';
                foreach ($cells as $columnIndex => $cell) {
                    $reference = $this->excelColumnName($columnIndex + 1).$rowNumber;
                    $value = $cell['value'];
                    if (is_int($value) || is_float($value)) {
                        $number = is_finite((float) $value) ? (string) $value : '0';
                        $sheetXml .= '<c r="'.$reference.'" s="'.$cell['style'].'"><v>'.$number.'</v></c>';
                    } else {
                        $text = htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                        $sheetXml .= '<c r="'.$reference.'" s="'.$cell['style'].'" t="inlineStr"><is><t xml:space="preserve">'.$text.'</t></is></c>';
                    }
                }
                $sheetXml .= '</row>';
            }
            $sheetXml .= '</sheetData><autoFilter ref="A'.$tableHeaderRow.':'.$lastColumn.$lastRow.'"/><mergeCells count="5">'
                .'<mergeCell ref="A1:'.$lastColumn.'1"/><mergeCell ref="A2:'.$lastColumn.'2"/>'
                .'<mergeCell ref="A3:'.$lastColumn.'3"/><mergeCell ref="A4:'.$lastColumn.'4"/>'
                .'<mergeCell ref="A6:B6"/></mergeCells>'
                .'<printOptions horizontalCentered="1"/>'
                .'<pageMargins left="0.3" right="0.3" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
                .'<pageSetup paperSize="9" orientation="'.($landscape ? 'landscape' : 'portrait').'" fitToWidth="1" fitToHeight="0"/>'
                .'<headerFooter><oddFooter>&amp;CPage &amp;P / &amp;N</oddFooter></headerFooter></worksheet>';

            $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                .'<sheets><sheet name="Laporan" sheetId="1" r:id="rId1"/></sheets><definedNames>'
                .'<definedName name="_xlnm.Print_Area">\'Laporan\'!$A$1:$'.$lastColumn.'$'.$lastRow.'</definedName>'
                .'<definedName name="_xlnm.Print_Titles">\'Laporan\'!$A$'.$tableHeaderRow.':$'.$lastColumn.'$'.$tableHeaderRow.'</definedName>'
                .'</definedNames></workbook>';

            $files = [
                '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
                    .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
                    .'<Default Extension="xml" ContentType="application/xml"/>'
                    .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
                    .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
                    .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
                    .'<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
                    .'<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
                    .'</Types>',
                '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
                    .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
                    .'<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
                    .'</Relationships>',
                'docProps/core.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" xmlns:dc="http://purl.org/dc/elements/1.1/">'
                    .'<dc:creator>BENBEN CO. Bakery</dc:creator><dc:title>'.htmlspecialchars($title, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</dc:title></cp:coreProperties>',
                'docProps/app.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties"><Application>BENBEN CO. Bakery</Application></Properties>',
                'xl/workbook.xml' => $workbookXml,
                'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                    .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                    .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                    .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                    .'</Relationships>',
                'xl/worksheets/sheet1.xml' => $sheetXml,
                'xl/styles.xml' => $this->spreadsheetStyles(),
            ];
            foreach ($files as $path => $contents) {
                if (! $zip->addFromString($path, $contents)) {
                    throw new \RuntimeException('Tidak dapat menulis bagian '.$path.' pada laporan Excel.');
                }
            }
            if (! $zip->close()) {
                throw new \RuntimeException('Tidak dapat menyelesaikan arsip laporan Excel.');
            }

            $xlsx = file_get_contents($temp);
            if ($xlsx === false || $xlsx === '') {
                throw new \RuntimeException('File laporan Excel kosong atau tidak dapat dibaca.');
            }

            return $xlsx;
        } finally {
            if (is_file($temp)) {
                unlink($temp);
            }
        }
    }

    private function spreadsheetStyles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="2"><numFmt numFmtId="164" formatCode="#,##0"/><numFmt numFmtId="165" formatCode="&quot;Rp&quot; #,##0"/></numFmts>'
            .'<fonts count="4"><font><sz val="10"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="18"/><color rgb="FF3A2118"/><name val="Calibri"/></font>'
            .'<font><b/><sz val="11"/><color rgb="FF65402D"/><name val="Calibri"/></font>'
            .'<font><sz val="10"/><color rgb="FF65402D"/><name val="Calibri"/></font></fonts>'
            .'<fills count="5"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF65402D"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF3E7D7"/><bgColor indexed="64"/></patternFill></fill>'
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFFAF6F0"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
            .'<border><left style="thin"><color rgb="FFE3D8CC"/></left><right style="thin"><color rgb="FFE3D8CC"/></right><top style="thin"><color rgb="FFE3D8CC"/></top><bottom style="thin"><color rgb="FFE3D8CC"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="13">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="165" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="164" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="165" fontId="0" fillId="4" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1"/>'
            .'</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function isCurrencyReportField(string $label): bool
    {
        return preg_match('/total|subtotal|harga|pendapatan|pembayaran diterima|nilai belum dibayar|nilai dibatalkan|rata-rata transaksi/i', $label) === 1;
    }

    private function buildPaginatedReportPdf(
        string $title,
        string $startDate,
        string $endDate,
        array $summary,
        array $headers,
        array $rows,
        bool $landscape
    ): string {
        $pdf = new class('P', 'mm', 'A4', true, 'UTF-8', false) extends \TCPDF
        {
            public function Footer(): void
            {
                $this->SetY(-10);
                $this->SetFont('helvetica', '', 8);
                $this->SetTextColor(110, 93, 79);
                $this->Cell(0, 5, 'BENBEN CO. Bakery - Halaman '.$this->getAliasNumPage().' / '.$this->getAliasNbPages(), 0, 0, 'C');
            }
        };
        $pdf->SetCreator('BENBEN CO. Bakery');
        $pdf->SetAuthor('BENBEN CO. Bakery');
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(12, 13, 12);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->SetFont('helvetica', '', 8);
        $pdf->AddPage($landscape ? 'L' : 'P', 'A4');

        $pdf->SetTextColor(58, 33, 24);
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 9, $title, 0, 1, 'L');
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(101, 64, 45);
        $pdf->Cell(0, 6, 'BENBEN CO. Bakery', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 8);
        $pdf->SetTextColor(90, 77, 66);
        $period = in_array($title, ['Laporan Stok Barang', 'Laporan Produk'], true)
            ? 'Periode data: Snapshot per '.Carbon::parse($endDate)->format('d-m-Y')
            : 'Periode: '.Carbon::parse($startDate)->format('d-m-Y').' s.d. '.Carbon::parse($endDate)->format('d-m-Y');
        $pdf->Cell(0, 5, $period, 0, 1, 'L');
        $pdf->Cell(0, 5, 'Dicetak: '.now()->format('d-m-Y H:i'), 0, 1, 'L');
        $pdf->Ln(3);

        $summaryHtml = '<table cellpadding="4" cellspacing="0" border="1" style="border-color:#e3d8cc;">'
            .'<thead><tr style="background-color:#f3e7d7;color:#3a2118;font-weight:bold;"><th colspan="2">Ringkasan</th></tr></thead><tbody>';
        foreach ($summary as $label => $value) {
            $formatted = is_numeric($value) && $this->isCurrencyReportField((string) $label)
                ? 'Rp '.number_format((float) $value, 0, ',', '.')
                : (string) $value;
            $summaryHtml .= '<tr><td width="55%">'.htmlspecialchars((string) $label, ENT_QUOTES, 'UTF-8').'</td>'
                .'<td width="45%">'.htmlspecialchars($formatted, ENT_QUOTES, 'UTF-8').'</td></tr>';
        }
        $summaryHtml .= '</tbody></table><br />';
        $pdf->writeHTML($summaryHtml, true, false, true, false, '');

        $columnWidth = 100 / max(1, count($headers));
        $tableHtml = '<table cellpadding="4" cellspacing="0" border="1" style="border-color:#e3d8cc;width:100%;">'
            .'<thead><tr style="background-color:#65402d;color:#ffffff;font-weight:bold;">';
        foreach ($headers as $header) {
            $tableHtml .= '<th width="'.$columnWidth.'%">'.htmlspecialchars((string) $header, ENT_QUOTES, 'UTF-8').'</th>';
        }
        $tableHtml .= '</tr></thead><tbody>';
        foreach ($rows as $rowIndex => $row) {
            $tableHtml .= '<tr style="background-color:'.($rowIndex % 2 ? '#faf6f0' : '#ffffff').';">';
            foreach (array_values($row) as $columnIndex => $value) {
                $header = (string) ($headers[$columnIndex] ?? '');
                $text = is_numeric($value) && $this->isCurrencyReportField($header)
                    ? 'Rp '.number_format((float) $value, 0, ',', '.')
                    : (string) $value;
                $tableHtml .= '<td width="'.$columnWidth.'%">'.nl2br(htmlspecialchars($text, ENT_QUOTES, 'UTF-8')).'</td>';
            }
            $tableHtml .= '</tr>';
        }
        $tableHtml .= '</tbody></table>';
        $pdf->writeHTML($tableHtml, true, false, true, false, '');

        return $pdf->Output('', 'S');
    }

    private function paidSalesOrders(): Builder
    {
        return Order::query()->where(function (Builder $query) {
            $this->applyPaidSalesConditions($query);
        });
    }

    private function applyPaidSalesConditions(Builder $query): Builder
    {
        return $query
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', 'verified');
    }

    private function logActivity(string $activityType, string $description, ?string $relatedType = null, ?int $relatedId = null, array $metadata = []): void
    {
        $user = Auth::user();

        ActivityLog::query()->create([
            'user_id' => Auth::id(),
            'user_name' => $user?->nama,
            'user_email' => $user?->email,
            'role_name' => $user?->role,
            'activity_type' => $activityType,
            'description' => $description,
            'related_type' => $relatedType,
            'related_id' => $relatedId,
            'metadata' => $metadata,
            'ip_address' => request()->ip() ?: null,
            'user_agent' => request()->userAgent(),
            'device_info' => request()->header('User-Agent'),
            'session_reference' => $user ? UserSession::hashReference((int) $user->id_user, (string) request()->session()->getId()) : null,
            'status' => 'success',
        ]);
    }

    public function saveProduct(Request $request)
    {
        $this->normalizeProductPrice($request);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150', 'unique:products,nama'],
            'category_id' => ['required', 'exists:categories,id_category'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'regex:/^\d+$/', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $product = Product::query()->create($data);
        $this->logActivity('product_created', 'Admin menambahkan produk '.$product->nama, 'product', $product->id_product, ['category_id' => $product->category_id]);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, int $productId)
    {
        $product = Product::query()->findOrFail($productId);
        $this->normalizeProductPrice($request);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150', Rule::unique('products', 'nama')->ignore($product->id_product, 'id_product')],
            'category_id' => ['required', 'exists:categories,id_category'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'regex:/^\d+$/', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if ($request->hasFile('gambar')) {
            if ($product->gambar && ! str_starts_with($product->gambar, 'images/')) {
                Storage::disk('public')->delete($product->gambar);
            }
            $data['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        $product->update($data);
        $this->logActivity('product_updated', 'Admin memperbarui produk '.$product->nama, 'product', $product->id_product, ['category_id' => $product->category_id]);

        return back()->with('success', 'Produk berhasil diperbarui.');
    }

    private function normalizeProductPrice(Request $request): void
    {
        $price = $request->input('harga');
        if (is_string($price) && preg_match('/^\s*(?:Rp\s*)?(\d{1,3}(?:\.\d{3})+|\d+)\s*$/i', $price, $matches)) {
            $request->merge(['harga' => str_replace('.', '', $matches[1])]);
        }
    }

    public function deleteProduct(int $productId)
    {
        $product = Product::query()->findOrFail($productId);
        if ($product->orderDetails()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah tercatat dalam pesanan.');
        }
        if ($product->gambar && ! str_starts_with($product->gambar, 'images/')) {
            Storage::disk('public')->delete($product->gambar);
        }
        $productName = $product->nama;
        $product->delete();
        $this->logActivity('product_deleted', 'Admin menghapus produk '.$productName, 'product', $productId, ['name' => $productName]);

        return back()->with('success', 'Produk berhasil dihapus.');
    }

    public function saveCategory(Request $request)
    {
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
        ]);

        $data['slug'] = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['nama_kategori']);
        $data['slug'] = $this->uniqueCategorySlug($data['slug']);

        $category = Category::query()->create($data);
        $this->logActivity('category_created', 'Admin menambahkan kategori '.$category->nama_kategori, 'category', $category->id_category, ['slug' => $category->slug]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, int $categoryId)
    {
        $category = Category::query()->findOrFail($categoryId);
        $data = $request->validate([
            'nama_kategori' => ['required', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:100'],
        ]);
        $slug = filled($data['slug'] ?? null) ? Str::slug($data['slug']) : Str::slug($data['nama_kategori']);
        $data['slug'] = $this->uniqueCategorySlug($slug, $category->id_category);
        $category->update($data);
        $this->logActivity('category_updated', 'Admin memperbarui kategori '.$category->nama_kategori, 'category', $category->id_category, ['slug' => $category->slug]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function deleteCategory(int $categoryId)
    {
        $category = Category::query()->findOrFail($categoryId);
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus selama masih memiliki produk.');
        }
        $categoryName = $category->nama_kategori;
        $category->delete();
        $this->logActivity('category_deleted', 'Admin menghapus kategori '.$categoryName, 'category', $categoryId, ['name' => $categoryName]);

        return back()->with('success', 'Kategori berhasil dihapus.');
    }

    private function uniqueCategorySlug(string $slug, ?int $ignoreId = null): string
    {
        $baseSlug = substr($slug, 0, 100);
        $slug = $baseSlug;
        $suffix = 2;
        while (Category::query()->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->where('id_category', '!=', $ignoreId))
            ->exists()) {
            $suffixText = '-'.$suffix++;
            $slug = substr($baseSlug, 0, 100 - strlen($suffixText)).$suffixText;
        }

        return $slug;
    }

    public function updateStock(Request $request, int $productId)
    {
        $data = $request->validate([
            'stok' => ['required', 'integer', 'min:0'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);
        DB::transaction(function () use ($productId, $data) {
            $product = Product::query()->lockForUpdate()->findOrFail($productId);
            $before = (int) $product->stok;
            $after = (int) $data['stok'];
            if ($before !== $after) {
                $product->update(['stok' => $after]);
                StockLog::query()->create([
                    'product_id' => $product->id_product,
                    'jenis' => $after > $before ? 'in' : 'out',
                    'jumlah' => abs($after - $before),
                    'stok_sebelum' => $before,
                    'stok_sesudah' => $after,
                    'keterangan' => $data['keterangan'] ?? 'Penyesuaian stok oleh admin',
                ]);
                $this->logActivity('stock_updated', 'Admin menyesuaikan stok produk '.$product->nama.' dari '.$before.' menjadi '.$after, 'product', $product->id_product, ['before' => $before, 'after' => $after]);
            }
        });

        return back()->with('success', 'Stok produk berhasil diperbarui.');
    }

    public function savePromotion(Request $request)
    {
        $data = $request->validate([
            'nama_promo' => ['required', 'string', 'max:100'],
            'kode_promo' => ['required', 'string', 'max:50', 'alpha_dash', 'unique:promotions,kode_promo'],
            'jenis_diskon' => ['required', 'in:persen,nominal'],
            'nilai_diskon' => ['required', 'numeric', 'regex:/^\d+$/', 'gt:0'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $data['kode_promo'] = strtoupper($data['kode_promo']);
        if ($data['jenis_diskon'] === 'persen' && $data['nilai_diskon'] > 100) {
            return back()->withErrors(['nilai_diskon' => 'Diskon persentase tidak boleh lebih dari 100%.'])->withInput();
        }
        $promotion = Promotion::query()->create($data);
        $this->logActivity('promo_created', 'Admin menambahkan promo '.$promotion->nama_promo, 'promotion', $promotion->id_promo, ['kode' => $promotion->kode_promo]);

        return back()->with('success', 'Promo berhasil ditambahkan.');
    }

    public function updatePromotion(Request $request, int $promotionId)
    {
        $promotion = Promotion::query()->findOrFail($promotionId);
        $data = $request->validate([
            'nama_promo' => ['required', 'string', 'max:100'],
            'kode_promo' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('promotions', 'kode_promo')->ignore($promotion->id_promo, 'id_promo')],
            'jenis_diskon' => ['required', 'in:persen,nominal'],
            'nilai_diskon' => ['required', 'numeric', 'regex:/^\d+$/', 'gt:0'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        if ($data['jenis_diskon'] === 'persen' && $data['nilai_diskon'] > 100) {
            return back()->withErrors(['nilai_diskon' => 'Diskon persentase tidak boleh lebih dari 100%.'])->withInput();
        }
        $data['kode_promo'] = strtoupper($data['kode_promo']);
        $promotion->update($data);
        $this->logActivity('promo_updated', 'Admin memperbarui promo '.$promotion->nama_promo, 'promotion', $promotion->id_promo, ['kode' => $promotion->kode_promo]);

        return back()->with('success', 'Promo berhasil diperbarui.');
    }

    public function deletePromotion(int $promotionId)
    {
        $promotion = Promotion::query()->findOrFail($promotionId);
        if ($promotion->orders()->exists()) {
            return back()->with('error', 'Promo tidak dapat dihapus karena sudah digunakan dalam pesanan.');
        }
        $promotionName = $promotion->nama_promo;
        $promotion->delete();
        $this->logActivity('promo_deleted', 'Admin menghapus promo '.$promotionName, 'promotion', $promotionId, ['name' => $promotionName]);

        return back()->with('success', 'Promo berhasil dihapus.');
    }

    public function updateOrderStatus(Request $request, int $orderId)
    {
        $request->validate([
            'status' => ['required', 'in:pending,confirmed,processing,ready,completed,cancelled'],
        ]);

        DB::transaction(function () use ($orderId, $request) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            if ($order->status === $request->status) {
                return;
            }
            if (! in_array($request->status, $this->orderStatusTransitions()[$order->status] ?? [], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Status ini tidak dapat dipilih dari status '.$this->orderStatusLabel($order->status).'. Ikuti urutan status pesanan.',
                ]);
            }
            if ($request->status === 'confirmed'
                && $order->payment_method !== 'cash'
                && $order->payment_status !== 'verified') {
                throw ValidationException::withMessages([
                    'status' => 'Pembayaran harus diverifikasi sebelum pesanan dikonfirmasi.',
                ]);
            }
            if ($request->status === 'cancelled') {
                $this->restockCancelledOrder($order);
                if ($order->payment_method !== 'cash' && $order->payment_status === 'pending') {
                    $order->payment_status = 'rejected';
                }
            }
            $previousStatus = $order->status;
            $order->status = $request->status;
            if ($request->status === 'completed' && $order->payment_method === 'cash') {
                $order->payment_status = 'verified';
            }
            $order->save();
            $this->recordOrderStatusChange($order, $previousStatus, $request->status, 'admin');
        });

        return back()->with('success', 'Status pesanan berhasil diubah.');
    }

    public function markCashPaymentReceived(int $orderId)
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);

            if ($order->payment_method !== 'cash' || $order->status !== 'ready') {
                throw ValidationException::withMessages([
                    'payment' => 'Pembayaran tunai hanya dapat ditandai diterima untuk pesanan siap diambil.',
                ]);
            }

            if ($order->payment_status === 'verified') {
                return;
            }

            if ($order->payment_status !== 'pending') {
                throw ValidationException::withMessages([
                    'payment' => 'Status pembayaran tunai ini tidak dapat diubah.',
                ]);
            }

            $order->update(['payment_status' => 'verified']);
        });

        return back()->with('success', 'Pembayaran tunai berhasil ditandai sudah diterima.');
    }

    private function orderStatusTransitions(): array
    {
        return [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'cancelled'],
            'processing' => ['ready', 'cancelled'],
            'ready' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => [],
        ];
    }

    private function orderStatusLabel(string $status): string
    {
        return [
            'pending' => 'Menunggu',
            'confirmed' => 'Dikonfirmasi',
            'processing' => 'Diproses',
            'ready' => 'Siap Diambil',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ][$status] ?? $status;
    }

    public function verifyPayment(int $orderId)
    {
        DB::transaction(function () use ($orderId) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            if ($order->payment_method === 'cash') {
                abort(422, 'Pembayaran tunai diverifikasi saat pesanan selesai.');
            }
            if (! $order->payment_proof) {
                abort(422, 'Bukti pembayaran belum diunggah.');
            }
            if ($order->payment_status !== 'pending' || $order->status !== 'pending') {
                abort(422, 'Hanya pembayaran yang masih menunggu untuk pesanan menunggu yang dapat diverifikasi.');
            }
            $order->update([
                'payment_status' => 'verified',
                'status' => 'confirmed',
            ]);
            $this->recordOrderStatusChange($order, 'pending', 'confirmed', 'payment_verification');
        });

        return back()->with('success', 'Pembayaran dikonfirmasi dan pesanan otomatis dikonfirmasi.');
    }

    public function rejectPayment(int $orderId)
    {
        $order = Order::query()->findOrFail($orderId);
        if ($order->payment_method === 'cash') {
            return back()->with('error', 'Pembayaran tunai tidak dapat ditolak dari halaman ini.');
        }
        if (! $order->payment_proof) {
            return back()->with('error', 'Bukti pembayaran belum diunggah.');
        }
        if ($order->payment_status !== 'pending' || $order->status !== 'pending') {
            return back()->with('error', 'Hanya pembayaran yang masih menunggu untuk pesanan menunggu yang dapat ditolak.');
        }
        DB::transaction(function () use ($orderId) {
            $order = Order::query()->lockForUpdate()->findOrFail($orderId);
            if ($order->payment_status !== 'pending' || $order->status !== 'pending') {
                abort(422, 'Status pembayaran sudah berubah.');
            }
            $this->restockCancelledOrder($order);
            $previousStatus = $order->status;
            $order->update(['payment_status' => 'rejected', 'status' => 'cancelled']);
            $this->recordOrderStatusChange($order, $previousStatus, 'cancelled', 'payment_rejection');
        });

        return back()->with('success', 'Pesanan dibatalkan karena pembayaran ditolak.');
    }

    private function recordOrderStatusChange(
        Order $order,
        string $fromStatus,
        string $toStatus,
        string $source
    ): void {
        OrderStatusHistory::query()->create([
            'order_id' => $order->id_order,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'changed_by' => Auth::id(),
            'source' => $source,
        ]);
    }

    private function restockCancelledOrder(Order $order): void
    {
        foreach ($order->details()->with('product')->get() as $detail) {
            $product = Product::query()->lockForUpdate()->find($detail->product_id);
            if (! $product) {
                continue;
            }

            $before = (int) $product->stok;
            $after = $before + (int) $detail->qty;
            $product->update(['stok' => $after]);
            StockLog::query()->create([
                'product_id' => $product->id_product,
                'jenis' => 'in',
                'jumlah' => (int) $detail->qty,
                'stok_sebelum' => $before,
                'stok_sesudah' => $after,
                'keterangan' => 'Restock pembatalan '.$order->kode_order,
            ]);
        }
    }
}
