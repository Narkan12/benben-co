<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->uuid('checkout_token')->nullable();
            $table->unique('checkout_token');
            $table->timestamp('whatsapp_notified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_checkout_token_unique');
            $table->dropColumn(['checkout_token', 'whatsapp_notified_at']);
        });
    }
};
