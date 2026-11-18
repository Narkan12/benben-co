<?php

namespace Database\Seeders;

use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['username' => 'admin@example.com'],
            [
                'nama' => 'Admin01',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
            ]
        );

        User::query()->updateOrCreate(
            ['username' => 'kasir@example.com'],
            [
                'nama' => 'Kasir01',
                'password' => bcrypt('kasir123'),
                'role' => 'kasir',
            ]
        );

        $this->call(ProductSeeder::class);

        Promotion::query()->updateOrCreate(
            ['kode_promo' => 'WELCOME10'],
            [
                'nama_promo' => 'Welcome Promo',
                'jenis_diskon' => 'persen',
                'nilai_diskon' => 10,
                'tanggal_mulai' => now()->format('Y-m-d'),
                'tanggal_berakhir' => now()->addDays(30)->format('Y-m-d'),
                'status' => 'active',
            ]
        );

        Promotion::query()->updateOrCreate(
            ['kode_promo' => 'BAKERY20'],
            [
                'nama_promo' => 'Weekend Special',
                'jenis_diskon' => 'nominal',
                'nilai_diskon' => 20000,
                'tanggal_mulai' => now()->format('Y-m-d'),
                'tanggal_berakhir' => now()->addDays(20)->format('Y-m-d'),
                'status' => 'active',
            ]
        );
    }
}
