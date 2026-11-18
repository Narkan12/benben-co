<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_login_records_activity_and_session_tracking(): void
    {
        $user = User::query()->create([
            'nama' => 'Pelanggan Uji',
            'username' => 'pelanggan-uji',
            'email' => 'pelanggan@example.com',
            'password' => 'secret123',
            'role' => 'customer',
        ]);

        $this->post(route('login.submit'), [
            'username' => 'pelanggan-uji',
            'password' => 'secret123',
        ])->assertRedirect(route('customer.dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id_user,
            'activity_type' => 'login_success',
        ]);

        $this->assertDatabaseHas('user_sessions', [
            'user_id' => $user->id_user,
            'status' => 'active',
        ]);
    }
}
