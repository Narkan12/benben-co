<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_profile_images', function (Blueprint $table) {
            $table->id();
            $table->string('image_key', 50)->unique();
            $table->string('label', 100);
            $table->string('default_path', 255);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        $images = [
            ['hero', 'Foto utama beranda', 'images/chesee-roll.png'],
            ['story_home', 'Foto cerita beranda', 'images/brownies-panggang-64sekat.png'],
            ['about_story', 'Foto cerita halaman Tentang', 'images/chesee-roll.png'],
            ['category_brownies', 'Kategori Brownies', 'images/brownies-panggang-64sekat.png'],
            ['category_cheese_roll', 'Kategori Cheese Roll', 'images/chesee-roll.png'],
            ['category_bolen_pisang', 'Kategori Bolen Pisang', 'images/bolen-lilit.png'],
            ['category_cookies', 'Kategori Cookies', 'images/products/cookies.jpg'],
            ['category_drinks', 'Kategori Drinks', 'images/yoghurt-250ml.png'],
            ['gallery_cheese_roll', 'Galeri Cheese Roll', 'images/chesee-roll.png'],
            ['gallery_brownies', 'Galeri Brownies Panggang', 'images/brownies-panggang-64sekat.png'],
            ['gallery_brownies_lumer', 'Galeri Brownies Lumer', 'images/brownies-lumer.png'],
            ['gallery_bolen_pisang', 'Galeri Bolen Pisang', 'images/bolen-lilit.png'],
            ['gallery_yoghurt', 'Galeri Yoghurt', 'images/yoghurt-250ml.png'],
        ];

        foreach ($images as [$key, $label, $path]) {
            DB::table('company_profile_images')->insert([
                'image_key' => $key,
                'label' => $label,
                'default_path' => $path,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_profile_images');
    }
};
