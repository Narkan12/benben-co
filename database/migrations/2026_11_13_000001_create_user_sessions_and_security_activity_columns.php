<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('user_email', 150)->nullable()->after('user_name');
            $table->string('role_name', 30)->nullable()->after('user_email');
            $table->string('ip_address', 45)->nullable()->after('related_id');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->text('device_info')->nullable()->after('user_agent');
            $table->string('session_reference', 128)->nullable()->after('device_info');
            $table->string('status', 25)->nullable()->after('session_reference');
        });

        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users', 'id_user')->cascadeOnUpdate()->nullOnDelete();
            $table->string('session_reference', 128)->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('device_info')->nullable();
            $table->timestamp('login_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('logout_at')->nullable();
            $table->string('status', 25)->default('active');
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sessions');

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropColumn([
                'user_email',
                'role_name',
                'ip_address',
                'user_agent',
                'device_info',
                'session_reference',
                'status',
            ]);
        });
    }
};
