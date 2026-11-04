<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer', 'ewallet', 'other'])
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')->where('payment_method', 'ewallet')->update([
            'payment_method' => 'other',
        ]);

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('payment_method', ['cash', 'transfer', 'other'])
                ->nullable()
                ->change();
        });
    }
};
