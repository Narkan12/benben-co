<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nama_kategori' => 'Brownies', 'slug' => 'brownies'],
            ['nama_kategori' => 'Cheese Roll', 'slug' => 'cheese-roll'],
            ['nama_kategori' => 'Bolen Pisang', 'slug' => 'bolen-pisang'],
            ['nama_kategori' => 'Cookies', 'slug' => 'cookies'],
            ['nama_kategori' => 'Drinks', 'slug' => 'drinks'],
            ['nama_kategori' => 'Cake', 'slug' => 'cake'],
            ['nama_kategori' => 'Cheesecake', 'slug' => 'cheesecake'],
            ['nama_kategori' => 'Bread', 'slug' => 'bread'],
            ['nama_kategori' => 'Pastry', 'slug' => 'pastry'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }

        $categoryMap = Category::query()->pluck('id_category', 'slug');

        $products = [
            ['nama' => 'Cheese Roll', 'category_id' => $categoryMap['cheese-roll'], 'harga' => 45000, 'gambar' => 'images/chesee-roll.png', 'stok' => 14],
            ['nama' => 'Cheese Milkcake', 'category_id' => $categoryMap['cake'], 'harga' => 68000, 'gambar' => 'images/chesee-milkcake.png', 'stok' => 4],
            ['nama' => 'Brownies Panggang 64 Sekat', 'category_id' => $categoryMap['brownies'], 'harga' => 38000, 'gambar' => 'images/brownies-panggang-64sekat.png', 'stok' => 20],
            ['nama' => 'Brownies Panggang 20 Sekat', 'category_id' => $categoryMap['brownies'], 'harga' => 52000, 'gambar' => 'images/brownies-panggang-20sekat.png', 'stok' => 9],
            ['nama' => 'Brownies Lumer', 'category_id' => $categoryMap['brownies'], 'harga' => 42000, 'gambar' => 'images/brownies-lumer.png', 'stok' => 11],
            ['nama' => 'Bolen Lilit', 'category_id' => $categoryMap['bolen-pisang'], 'harga' => 35000, 'gambar' => 'images/bolen-lilit.png', 'stok' => 3],
            ['nama' => 'Yoghurt 250 ml', 'category_id' => $categoryMap['drinks'], 'harga' => 30000, 'gambar' => 'images/yoghurt-250ml.png', 'stok' => 16],
            ['nama' => 'Yoghurt 1 L', 'category_id' => $categoryMap['drinks'], 'harga' => 28000, 'gambar' => 'images/yoghurt-1l.png', 'stok' => 25],
            ['nama' => 'Choco Chip Cookies', 'category_id' => $categoryMap['cookies'], 'harga' => 32000, 'gambar' => 'images/products/cookies.jpg', 'stok' => 18],
            ['nama' => 'Butter Cookies', 'category_id' => $categoryMap['cookies'], 'harga' => 29000, 'gambar' => 'images/products/cookies2.jpg', 'stok' => 15],
            ['nama' => 'Bolen Pisang', 'category_id' => $categoryMap['bolen-pisang'], 'harga' => 38000, 'gambar' => 'images/bolen-lilit.png', 'stok' => 12],
        ];

        foreach ($products as $productData) {
            $productData['deskripsi'] = 'Produk BenBen Co.';
            $productData['status'] = 'active';

            Product::query()->updateOrCreate(
                ['nama' => $productData['nama']],
                $productData
            );
        }
    }
}
