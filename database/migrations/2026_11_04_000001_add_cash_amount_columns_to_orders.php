<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'cash_received') && Schema::hasColumn('orders', 'cash_change')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'cash_received')) {
                $table->decimal('cash_received', 10, 2)->nullable()->after('payment_status');
            }

            if (! Schema::hasColumn('orders', 'cash_change')) {
                $table->decimal('cash_change', 10, 2)->nullable()->after('cash_received');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'cash_received')) {
                $table->dropColumn('cash_received');
            }

            if (Schema::hasColumn('orders', 'cash_change')) {
                $table->dropColumn('cash_change');
            }
        });
    }
};
