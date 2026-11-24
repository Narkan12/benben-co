<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! in_array(config('app.env'), ['local', 'testing'], true) && ! config('app.demo_seed_enabled')) {
            return;
        }

        $this->call(DemoAdminPanelSeeder::class);
    }
}
