<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InactiveAccountAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_deactivating_an_admin_closes_tracked_sessions_and_blocks_access(): void
    {
        $operator = User::query()->create([
            'nama' => 'Admin Pengelola',
            'username' => 'admin-pengelola',
            'email' => 'pengelola@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $target = User::query()->create([
            'nama' => 'Admin Dinonaktifkan',
            'username' => 'admin-dinonaktifkan',
            'email' => 'dinonaktifkan@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        UserSession::query()->create([
            'user_id' => $target->id_user,
            'session_reference' => str_repeat('a', 64),
            'login_at' => now(),
            'last_activity_at' => now(),
            'status' => 'active',
        ]);

        $this->actingAs($operator)
            ->patch(route('admin.users.toggle-status', $target->id_user))
            ->assertSessionHas('success');

        $this->assertFalse($target->fresh()->is_active);
        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $target->id_user,
            'status' => 'logged_out',
        ]);

        $this->actingAs($target->refresh())
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_inactive_customer_is_logged_out_before_accessing_customer_pages(): void
    {
        $customer = User::query()->create([
            'nama' => 'Pelanggan Dinonaktifkan',
            'username' => 'pelanggan-dinonaktifkan',
            'email' => 'pelanggan-dinonaktifkan@example.com',
            'password' => 'password123',
            'role' => 'customer',
            'is_active' => false,
        ]);

        $this->actingAs($customer)
            ->get(route('customer.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
