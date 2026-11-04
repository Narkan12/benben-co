<?php

namespace App\Http\Controllers;

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
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
        $pendingOrders = Order::query()->whereIn('status', ['pending', 'confirmed', 'processing'])->count();
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
        $dateRange = [
            $validated['start_date'].' 00:00:00',
            $validated['end_date'].' 23:59:59',
        ];
        $dailySales = $this->paidSalesOrders()
            ->selectRaw('DATE(tanggal) as sales_date, COUNT(*) as transactions, SUM(total) as sales_total')
            ->whereBetween('tanggal', $dateRange)
            ->groupBy('sales_date')
            ->orderBy('sales_date')
            ->get();
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
        $reportTotal = (float) $dailySales->sum('sales_total');
        $reportTransactions = (int) $dailySales->sum('transactions');
        $reportUnits = (int) OrderDetail::query()
            ->whereHas('order', fn (Builder $query) => $this->applyPaidSalesConditions($query)
                ->whereBetween('tanggal', $dateRange))
            ->sum('qty');
        $paymentLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'ewallet' => 'E-Wallet',
            'other' => 'Metode lain (data lama)',
            'unknown' => 'Tidak diketahui',
        ];

        return response()->streamDownload(function () use (
            $dailySales,
            $paymentLabels,
            $paymentMethods,
            $reportTotal,
            $reportTransactions,
            $reportUnits,
            $validated,
            $reportOrders
        ) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['BENBEN CO. Bakery']);
            fputcsv($output, ['Laporan penjualan', $validated['start_date'].' - '.$validated['end_date']]);
            fputcsv($output, []);
            fputcsv($output, ['Ringkasan']);
            fputcsv($output, ['Pembayaran diterima', $reportTotal]);
            fputcsv($output, ['Transaksi terbayar', $reportTransactions]);
            fputcsv($output, ['Produk terjual', $reportUnits]);
            fputcsv($output, ['Rata-rata transaksi', $reportTransactions ? $reportTotal / $reportTransactions : 0]);
            fputcsv($output, []);
            fputcsv($output, ['Ringkasan metode pembayaran']);
            fputcsv($output, ['Metode pembayaran', 'Transaksi', 'Total diterima']);

            foreach ($paymentLabels as $method => $label) {
                $summary = $paymentMethods->get($method);
                fputcsv($output, [$label, $summary->transactions ?? 0, $summary->sales_total ?? 0]);
            }

            fputcsv($output, []);
            fputcsv($output, ['Ringkasan harian']);
            fputcsv($output, ['Tanggal', 'Transaksi terbayar', 'Total diterima']);

            foreach ($dailySales as $row) {
                fputcsv($output, [$row->sales_date, $row->transactions, $row->sales_total]);
            }
            fputcsv($output, []);
            fputcsv($output, ['Detail transaksi terbayar']);
            fputcsv($output, ['Kode transaksi', 'Tanggal', 'Nama customer', 'Nomor WhatsApp', 'Produk dibeli', 'Total item', 'Metode pembayaran', 'Total']);
            foreach ($reportOrders as $order) {
                fputcsv($output, [
                    $order->kode_order,
                    $order->tanggal?->format('Y-m-d H:i'),
                    $order->customer?->nama ?? 'Pelanggan umum',
                    $order->customer?->no_whatsapp ?? '-',
                    $order->details->map(fn (OrderDetail $detail) => ($detail->product?->nama ?? 'Produk dihapus').' x'.$detail->qty)->join(' | '),
                    $order->details->sum('qty'),
                    $paymentLabels[$order->payment_method] ?? 'Tidak diketahui',
                    $order->total,
                ]);
            }

            fclose($output);
        }, 'laporan-penjualan-'.$validated['start_date'].'-'.$validated['end_date'].'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

    public function saveProduct(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150', 'unique:products,nama'],
            'category_id' => ['required', 'exists:categories,id_category'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
            'stok' => ['required', 'integer', 'min:0'],
            'status' => ['required', 'in:active,inactive'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('products', 'public');
        }

        Product::query()->create($data);

        return back()->with('success', 'Produk berhasil ditambahkan.');
    }

    public function updateProduct(Request $request, int $productId)
    {
        $product = Product::query()->findOrFail($productId);
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:150', Rule::unique('products', 'nama')->ignore($product->id_product, 'id_product')],
            'category_id' => ['required', 'exists:categories,id_category'],
            'deskripsi' => ['nullable', 'string'],
            'harga' => ['required', 'numeric', 'min:0'],
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

        return back()->with('success', 'Produk berhasil diperbarui.');
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
        $product->delete();

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

        Category::query()->create($data);

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

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function deleteCategory(int $categoryId)
    {
        $category = Category::query()->findOrFail($categoryId);
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus selama masih memiliki produk.');
        }
        $category->delete();

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
            'nilai_diskon' => ['required', 'numeric', 'gt:0'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        $data['kode_promo'] = strtoupper($data['kode_promo']);
        if ($data['jenis_diskon'] === 'persen' && $data['nilai_diskon'] > 100) {
            return back()->withErrors(['nilai_diskon' => 'Diskon persentase tidak boleh lebih dari 100%.'])->withInput();
        }
        Promotion::query()->create($data);

        return back()->with('success', 'Promo berhasil ditambahkan.');
    }

    public function updatePromotion(Request $request, int $promotionId)
    {
        $promotion = Promotion::query()->findOrFail($promotionId);
        $data = $request->validate([
            'nama_promo' => ['required', 'string', 'max:100'],
            'kode_promo' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('promotions', 'kode_promo')->ignore($promotion->id_promo, 'id_promo')],
            'jenis_diskon' => ['required', 'in:persen,nominal'],
            'nilai_diskon' => ['required', 'numeric', 'gt:0'],
            'tanggal_mulai' => ['required', 'date_format:Y-m-d'],
            'tanggal_berakhir' => ['required', 'date_format:Y-m-d', 'after_or_equal:tanggal_mulai'],
            'status' => ['required', 'in:active,inactive'],
        ]);
        if ($data['jenis_diskon'] === 'persen' && $data['nilai_diskon'] > 100) {
            return back()->withErrors(['nilai_diskon' => 'Diskon persentase tidak boleh lebih dari 100%.'])->withInput();
        }
        $data['kode_promo'] = strtoupper($data['kode_promo']);
        $promotion->update($data);

        return back()->with('success', 'Promo berhasil diperbarui.');
    }

    public function deletePromotion(int $promotionId)
    {
        $promotion = Promotion::query()->findOrFail($promotionId);
        if ($promotion->orders()->exists()) {
            return back()->with('error', 'Promo tidak dapat dihapus karena sudah digunakan dalam pesanan.');
        }
        $promotion->delete();

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
