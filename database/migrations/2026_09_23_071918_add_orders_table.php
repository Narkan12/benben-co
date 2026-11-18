<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('id_order');
            $table->string('kode_order', 20)->unique();
            $table->foreignId('customer_id')
                ->constrained('customers', 'id_customer')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->dateTime('tanggal');
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('delivery', 10, 2)->default(0);
            $table->decimal('diskon', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'ready', 'completed', 'cancelled',
            ])->default('pending');
            $table->enum('payment_method', ['cash', 'transfer', 'other'])->nullable();
            $table->enum('delivery_method', ['pickup', 'delivery'])->nullable();
            $table->text('catatan')->nullable();
            $table->enum('source', ['online', 'pos'])->default('online');
            $table->foreignId('promo_id')
                ->nullable()
                ->constrained('promotions', 'id_promo')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('kode_promo_used', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};