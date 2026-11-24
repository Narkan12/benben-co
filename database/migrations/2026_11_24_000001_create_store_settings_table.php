<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('delivery_fee')->default(15000);
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 100)->nullable();
            $table->string('ewallet_provider', 100)->nullable();
            $table->string('ewallet_account_number', 50)->nullable();
            $table->string('ewallet_account_name', 100)->nullable();
            $table->string('qris_image_path')->nullable();
        });

        DB::table('store_settings')->insert([
            'id' => 1,
            'delivery_fee' => 15000,
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('store_settings');
    }
};
