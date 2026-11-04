<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('order_notifications_enabled')->default(true);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('admin_viewed_at')->nullable()->index();
        });

        DB::table('orders')->whereNull('admin_viewed_at')->update([
            'admin_viewed_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['admin_viewed_at']);
            $table->dropColumn('admin_viewed_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('order_notifications_enabled');
        });
    }
};
