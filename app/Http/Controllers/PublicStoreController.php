<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\CompanyProfileImage;
use App\Models\Order;
use App\Models\PageContent;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Rating;
use App\Services\WhatsAppCloudApi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicStoreController extends Controller
{
    public function home()
    {
        $homeContent = PageContent::publishedFor('home');
        $favoritesContent = PageContent::publishedFor('favorites');
        $promotionContent = PageContent::publishedFor('promotions');
        $profileImages = CompanyProfileImage::query()->get()->keyBy('image_key');
        $imagePath = fn (string $key): string => $profileImages->get($key)?->currentPath() ?? 'images/logo.png';
        $products = Product::query()
            ->with('category')
            ->withAvg('ratings', 'rating')
            ->where('status', 'active')
            ->orderBy('id_product')
            ->limit(4)
            ->get();

        $categoryHighlights = [
            [
                'name' => 'Brownies',
                'slug' => 'brownies',
                'image' => $imagePath('category_brownies'),
                'description' => 'Brownies panggang dan brownies lumer untuk teman berbagi.',
            ],
            [
                'name' => 'Cheese Roll',
                'slug' => 'cheese-roll',
                'image' => $imagePath('category_cheese_roll'),
                'description' => 'Pastry keju dengan rasa gurih dan tekstur renyah.',
            ],
            [
                'name' => 'Bolen Pisang',
                'slug' => 'bolen-pisang',
                'image' => $imagePath('category_bolen_pisang'),
                'description' => 'Bolen pisang untuk camilan keluarga dan buah tangan.',
            ],
            [
                'name' => 'Cookies',
                'slug' => 'cookies',
                'image' => $imagePath('category_cookies'),
                'description' => 'Pilihan cookies untuk menemani waktu santai.',
            ],
            [
                'name' => 'Drinks',
                'slug' => 'drinks',
                'image' => $imagePath('category_drinks'),
                'description' => 'Pilihan minuman untuk melengkapi sajian bakery.',
            ],
        ];

        $reviews = Rating::query()
            ->with('product')
            ->whereHas('product', fn ($query) => $query->where('status', 'active'))
            ->latest('id_rating')
            ->limit(6)
            ->get();

        $promos = Promotion::query()
            ->where('status', 'active')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->orderBy('tanggal_berakhir')
            ->get();

        $heroImage = $homeContent?->image_path ?: $imagePath('hero');
        $storyImage = $imagePath('story_home');

        return view('home', compact(
            'products',
            'categoryHighlights',
            'reviews',
            'promos',
            'heroImage',
            'storyImage',
            'homeContent',
            'favoritesContent',
            'promotionContent'
        ));
    }

    public function gallery()
    {
        $profileImages = CompanyProfileImage::query()->get()->keyBy('image_key');
        $imagePath = fn (string $key): string => $profileImages->get($key)?->currentPath() ?? 'images/logo.png';
        $galleryImages = [
            ['path' => $imagePath('gallery_cheese_roll'), 'label' => 'Cheese roll'],
            ['path' => $imagePath('gallery_brownies'), 'label' => 'Brownies panggang'],
            ['path' => $imagePath('gallery_brownies_lumer'), 'label' => 'Brownies lumer'],
            ['path' => $imagePath('gallery_bolen_pisang'), 'label' => 'Bolen pisang'],
            ['path' => $imagePath('gallery_yoghurt'), 'label' => 'Yoghurt'],
        ];

        return view('gallery', compact('galleryImages'));
    }

    public function about()
    {
        $aboutContent = PageContent::publishedFor('about');
        $aboutImage = $aboutContent?->image_path
            ?: (CompanyProfileImage::query()->where('image_key', 'about_story')->first()?->currentPath() ?? 'images/chesee-roll.png');

        return view('about', compact('aboutImage', 'aboutContent'));
    }

    public function products(Request $request)
    {
        $query = Product::query()->where('status', 'active');

        if ($request->filled('category')) {
            $categorySlug = $request->category;
            $category = Category::query()->where('slug', $categorySlug)->first();

            if ($category) {
                $query->where('category_id', $category->id_category);
            }
        }

        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where('nama', 'like', "%{$keyword}%");
        }

        $products = $query
            ->with('category')
            ->withAvg('ratings', 'rating')
            ->orderBy('id_product')
            ->paginate(8)
            ->withQueryString();
        $categories = Category::query()
            ->whereHas('products', fn ($productQuery) => $productQuery->where('status', 'active'))
            ->orderBy('nama_kategori')
            ->get();

        return view('products', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function productDetail(string $slug)
    {
        $product = ctype_digit($slug)
            ? Product::query()->with('category')->where('status', 'active')->find((int) $slug)
            : Product::query()
                ->with('category')
                ->where('status', 'active')
                ->get()
                ->first(fn ($item) => Str::slug($item->nama) === $slug);

        if (! $product) {
            abort(404);
        }

        $related = Product::query()
            ->with('category')
            ->withAvg('ratings', 'rating')
            ->where('status', 'active')
            ->where('id_product', '!=', $product->id_product)
            ->limit(4)
            ->get();

        $reviewAverage = round(($product->ratings()->avg('rating') ?? 0), 1);
        $reviews = $product->ratings()->orderByDesc('created_at')->get();

        return view('product-detail', [
            'product' => [
                'id' => $product->id_product,
                'name' => $product->nama,
                'category' => $product->category?->nama_kategori ?? 'Bakery',
                'price' => (float) $product->harga,
                'oldPrice' => null,
                'rating' => (float) $reviewAverage,
                'reviews' => $reviews->count(),
                'stock' => (int) $product->stok,
                'cartQty' => (int) (session('cart.'.$product->id_product.'.qty', 0)),
                'image' => $product->gambar ?? 'images/products/placeholder.jpg',
                'description' => $product->deskripsi ?? 'Produk premium bakery BenBen Co. dibuat fresh setiap hari.',
            ],
            'related' => $related,
            'reviewAverage' => $reviewAverage,
            'reviews' => $reviews,
        ]);
    }

    public function promos()
    {
        $pageContent = PageContent::publishedFor('promotions');
        $promos = Promotion::query()
            ->where('status', 'active')
            ->whereDate('tanggal_mulai', '<=', today())
            ->whereDate('tanggal_berakhir', '>=', today())
            ->orderBy('tanggal_berakhir')
            ->get();

        return view('promotions', compact('promos', 'pageContent'));
    }

    public function contact()
    {
        $pageContent = PageContent::publishedFor('contact');

        return view('contact', compact('pageContent'));
    }

    public function cart()
    {
        $cart = session('cart', []);
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::query()->find($productId);

            if (! $product) {
                continue;
            }

            $items[] = [
                'id_product' => $product->id_product,
                'name' => $product->nama,
                'price' => (float) $product->harga,
                'qty' => (int) ($item['qty'] ?? 1),
                'stock' => (int) $product->stok,
                'image' => $product->gambar ?? 'images/products/placeholder.jpg',
            ];
        }

        return view('cart', ['items' => $items]);
    }

    public function checkoutPage()
    {
        $cart = session('cart', []);
        $items = [];

        foreach ($cart as $productId => $item) {
            $product = Product::query()->find($productId);

            if ($product) {
                $items[] = [
                    'id_product' => $product->id_product,
                    'name' => $product->nama,
                    'price' => (float) $product->harga,
                    'qty' => (int) ($item['qty'] ?? 1),
                    'stock' => (int) $product->stok,
                ];
            }
        }

        return view('checkout', ['items' => $items]);
    }

    public function success(WhatsAppCloudApi $whatsApp, ?string $code = null)
    {
        $order = $code
            ? Order::query()->with(['customer', 'details.product'])
                ->where('customer_id', auth()->user()->customer_id)
                ->where('kode_order', $code)
                ->first()
            : null;
        $whatsappUrl = $order ? $whatsApp->customerConfirmationUrl($order) : null;

        return view('success', compact('order', 'whatsappUrl'));
    }
}
