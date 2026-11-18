<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $products = [
        ['Brownies Fudge Klasik', 'Cheese Roll', 'images/chesee-roll.png', 'pastry', 'Brownies Fudge Klasik', 'pastry'],
        ['Cheesecake Blueberry', 'Cheese Milkcake', 'images/chesee-milkcake.png', 'cake', 'Cheesecake Blueberry', 'cheesecake'],
        ['Red Velvet Cake Slice', 'Brownies Panggang 64 Sekat', 'images/brownies-panggang-64sekat.png', 'brownies', 'Red Velvet Cake Slice', 'cake'],
        ['Butter Cookies Toples', 'Brownies Panggang 20 Sekat', 'images/brownies-panggang-20sekat.png', 'brownies', 'Butter Cookies Toples', 'cookies'],
        ['Sourdough Bread', 'Brownies Lumer', 'images/brownies-lumer.png', 'brownies', 'Sourdough Bread', 'bread'],
        ['Bolen Pisang Keju', 'Bolen Lilit', 'images/bolen-lilit.png', 'pastry', 'Bolen Pisang Keju', 'pastry'],
        ['Tiramisu Cup', 'Yoghurt 250 ml', 'images/yoghurt-250ml.png', 'minuman', 'Tiramisu Cup', 'cheesecake'],
        ['Choco Chip Cookies', 'Yoghurt 1 L', 'images/yoghurt-1l.png', 'minuman', 'Choco Chip Cookies', 'cookies'],
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $beverageCategoryId = DB::table('categories')->where('slug', 'minuman')->value('id_category');

            if (! $beverageCategoryId) {
                $beverageCategoryId = DB::table('categories')->insertGetId([
                    'nama_kategori' => 'Minuman',
                    'slug' => 'minuman',
                    'created_at' => now(),
                ]);
            }

            $categoryIds = DB::table('categories')->pluck('id_category', 'slug');
            $categoryIds['minuman'] = $beverageCategoryId;

            foreach ($this->products as [$legacyName, $name, $image, $categorySlug]) {
                $categoryId = $categoryIds[$categorySlug] ?? null;

                if (! $categoryId) {
                    continue;
                }

                DB::table('products')->where('nama', $legacyName)->update([
                    'nama' => $name,
                    'gambar' => $image,
                    'category_id' => $categoryId,
                    'deskripsi' => 'Produk BenBen Co.',
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $categoryIds = DB::table('categories')->pluck('id_category', 'slug');

            foreach ($this->products as [, $name, , , $legacyName, $legacyCategorySlug]) {
                $categoryId = $categoryIds[$legacyCategorySlug] ?? null;

                if (! $categoryId) {
                    continue;
                }

                DB::table('products')->where('nama', $name)->update([
                    'nama' => $legacyName,
                    'gambar' => 'images/products/placeholder.jpg',
                    'category_id' => $categoryId,
                    'deskripsi' => 'Produk premium bakery BenBen Co. dibuat fresh setiap hari.',
                ]);
            }

            $beverageCategoryId = $categoryIds['minuman'] ?? null;

            if ($beverageCategoryId && ! DB::table('products')->where('category_id', $beverageCategoryId)->exists()) {
                DB::table('categories')->where('id_category', $beverageCategoryId)->delete();
            }
        });
    }
};
