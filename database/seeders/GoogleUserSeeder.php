<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class GoogleUserSeeder extends Seeder
{
    public function run(): void
    {
        $customer = Customer::query()->firstOrCreate(
            ['nama' => 'Google Customer Demo'],
            ['no_whatsapp' => '-', 'alamat' => null]
        );

        User::query()->firstOrCreate(
            ['google_id' => 'google-demo-customer-001'],
            [
                'nama' => 'Google Customer Demo',
                'username' => 'google.customer.demo',
                'email' => 'google.customer.demo@example.com',
                'password' => Hash::make(bin2hex(random_bytes(32))),
                'role' => 'customer',
                'customer_id' => $customer->id_customer,
                'is_active' => true,
            ]
        );

        User::query()->firstOrCreate(
            ['google_id' => 'google-demo-admin-001'],
            [
                'nama' => 'Google Admin Demo',
                'username' => 'google.admin.demo',
                'email' => 'google.admin.demo@example.com',
                'password' => Hash::make(bin2hex(random_bytes(32))),
                'role' => 'admin',
                'customer_id' => null,
                'is_active' => true,
            ]
        );
    }
}
