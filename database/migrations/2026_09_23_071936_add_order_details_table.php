<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id('id_detail');
            $table->foreignId('order_id')
                ->constrained('orders', 'id_order')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('product_id')
                ->constrained('products', 'id_product')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->decimal('harga', 10, 2);
            $table->integer('qty');
            $table->decimal('subtotal', 10, 2);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};