<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id('id_rating');
            $table->foreignId('product_id')
                ->constrained('products', 'id_product')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('nama_customer', 100);
            $table->tinyInteger('rating');
            $table->text('komentar')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};