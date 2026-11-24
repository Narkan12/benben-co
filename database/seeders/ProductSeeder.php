<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categoryDefinitions = [
            'brownies' => ['nama_kategori' => 'Brownies'],
            'cheese-roll' => ['nama_kategori' => 'Cheese Roll'],
            'bolen-pisang' => ['nama_kategori' => 'Bolen Pisang'],
            'cookies' => ['nama_kategori' => 'Cookies'],
            'minuman' => ['nama_kategori' => 'Minuman', 'aliases' => ['drinks']],
            'cake' => ['nama_kategori' => 'Kue', 'aliases' => []],
            'cheesecake' => ['nama_kategori' => 'Cheesecake'],
            'bread' => ['nama_kategori' => 'Roti', 'aliases' => []],
            'pastry' => ['nama_kategori' => 'Pastri', 'aliases' => []],
        ];
        $categoryIds = [];

        foreach ($categoryDefinitions as $slug => $definition) {
            $category = Category::query()->where('slug', $slug)->first();

            if (! $category) {
                foreach ($definition['aliases'] ?? [] as $alias) {
                    $category = Category::query()->where('slug', $alias)->first();
                    if ($category) {
                        break;
                    }
                }
            }

            $category ??= Category::query()->firstOrCreate(
                ['slug' => $slug],
                ['nama_kategori' => $definition['nama_kategori']]
            );

            $legacyNames = [
                'Drinks' => 'Minuman',
                'Cake' => 'Kue',
                'Bread' => 'Roti',
                'Pastry' => 'Pastri',
            ];
            if (isset($legacyNames[$category->nama_kategori])
                && $legacyNames[$category->nama_kategori] === $definition['nama_kategori']) {
                $category->nama_kategori = $definition['nama_kategori'];
                $category->save();
            }

            $categoryIds[$slug] = $category->id_category;
            foreach ($definition['aliases'] ?? [] as $alias) {
                $categoryIds[$alias] = $category->id_category;
            }
        }

        $products = [
            [
                'nama' => 'Cheese Roll',
                'category' => 'cheese-roll',
                'deskripsi' => 'Roti gulung lembut dengan isian keju gurih, dipanggang hingga keemasan.',
                'harga' => 45000,
                'gambar' => 'images/chesee-roll.png',
                'stok' => 14,
            ],
            [
                'nama' => 'Cheese Milkcake',
                'category' => 'cake',
                'deskripsi' => 'Milk cake bertekstur lembut dengan lapisan krim dan sentuhan keju.',
                'harga' => 68000,
                'gambar' => 'images/chesee-milkcake.png',
                'stok' => 4,
            ],
            [
                'nama' => 'Brownies Panggang 64 Sekat',
                'category' => 'brownies',
                'deskripsi' => 'Brownies panggang cokelat dengan potongan rapi untuk sajian bersama.',
                'harga' => 38000,
                'gambar' => 'images/brownies-panggang-64sekat.png',
                'stok' => 20,
            ],
            [
                'nama' => 'Brownies Panggang 20 Sekat',
                'category' => 'brownies',
                'deskripsi' => 'Brownies panggang cokelat dalam ukuran praktis untuk dinikmati di rumah.',
                'harga' => 52000,
                'gambar' => 'images/brownies-panggang-20sekat.png',
                'stok' => 9,
            ],
            [
                'nama' => 'Brownies Lumer',
                'category' => 'brownies',
                'deskripsi' => 'Brownies cokelat lembut dengan topping cokelat lumer.',
                'harga' => 42000,
                'gambar' => 'images/brownies-lumer.png',
                'stok' => 11,
            ],
            [
                'nama' => 'Bolen Lilit',
                'category' => 'bolen-pisang',
                'deskripsi' => 'Pastri berlapis dengan isian pisang dan keju, dipanggang sampai harum.',
                'harga' => 35000,
                'gambar' => 'images/bolen-lilit.png',
                'stok' => 8,
            ],
            [
                'nama' => 'Yoghurt 250 ml',
                'category' => 'minuman',
                'deskripsi' => 'Yoghurt dingin dalam kemasan 250 ml untuk menemani kudapan.',
                'harga' => 30000,
                'gambar' => 'images/yoghurt-250ml.png',
                'stok' => 16,
            ],
            [
                'nama' => 'Yoghurt 1 L',
                'category' => 'minuman',
                'deskripsi' => 'Yoghurt dalam kemasan 1 liter, cocok disajikan bersama keluarga.',
                'harga' => 28000,
                'gambar' => 'images/yoghurt-1l.png',
                'stok' => 25,
            ],
            [
                'nama' => 'Choco Chip Cookies',
                'category' => 'cookies',
                'deskripsi' => 'Cookies renyah dengan taburan choco chip di setiap gigitan.',
                'harga' => 32000,
                'gambar' => 'images/products/cookies.jpg',
                'stok' => 18,
            ],
            [
                'nama' => 'Butter Cookies',
                'category' => 'cookies',
                'deskripsi' => 'Butter cookies beraroma mentega dengan tekstur renyah.',
                'harga' => 29000,
                'gambar' => 'images/products/cookies2.jpg',
                'stok' => 15,
            ],
            [
                'nama' => 'Bolen Pisang',
                'category' => 'bolen-pisang',
                'deskripsi' => 'Bolen berlapis dengan isian pisang matang dan cokelat.',
                'harga' => 38000,
                'gambar' => 'images/bolen-lilit.png',
                'stok' => 12,
            ],
        ];

        foreach ($products as $definition) {
            $image = Storage::disk('public')->exists($definition['gambar'])
                ? $definition['gambar']
                : 'images/products/placeholder.jpg';

            $product = Product::query()->firstOrCreate(
                ['nama' => $definition['nama']],
                [
                    'category_id' => $categoryIds[$definition['category']],
                    'deskripsi' => $definition['deskripsi'],
                    'harga' => $definition['harga'],
                    'gambar' => $image,
                    'stok' => $definition['stok'],
                    'status' => 'active',
                ]
            );

            $changes = [];
            if (in_array(trim((string) $product->deskripsi), ['', 'Produk BenBen Co.'], true)) {
                $changes['deskripsi'] = $definition['deskripsi'];
            }
            if (! $product->gambar || ! Storage::disk('public')->exists($product->gambar)) {
                $changes['gambar'] = $image;
            }

            if ($changes !== []) {
                $product->forceFill($changes)->save();
            }
        }
    }
}
