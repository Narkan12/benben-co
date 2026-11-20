<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(Request $request, string $intent)
    {
        if (! in_array($intent, ['customer', 'admin'], true)) {
            abort(404);
        }

        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()->route($intent === 'admin' ? 'admin.login' : 'login')
                ->withErrors(['google' => 'Google Login belum dikonfigurasi oleh administrator.']);
        }

        $request->session()->put('google_oauth_intent', $intent);

        return Socialite::driver('google')
            ->redirect();
    }

    public function callback(Request $request)
    {
        $intent = $request->session()->get('google_oauth_intent', 'customer');

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            report($exception);

            $request->session()->forget('google_oauth_intent');

            return redirect()->route($intent === 'admin' ? 'admin.login' : 'login')
                ->withErrors(['google' => 'Google Login dibatalkan atau gagal. Silakan coba lagi.']);
        }

        $googleId = (string) $googleUser->getId();
        $email = strtolower(trim((string) $googleUser->getEmail()));
        $rawData = (array) ($googleUser->user ?? []);
        $verified = (bool) ($rawData['email_verified'] ?? $rawData['verified_email'] ?? false);

        if ($googleId === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || ! $verified) {
            return redirect()->route($intent === 'admin' ? 'admin.login' : 'login')
                ->withErrors(['google' => 'Akun Google harus memiliki email valid yang sudah terverifikasi.']);
        }

        $request->session()->forget('google_oauth_intent');

        if ($intent === 'admin') {
            $user = User::query()
                ->where('google_id', $googleId)
                ->whereIn('role', ['admin', 'kasir'])
                ->first();

            if (! $user) {
                $existing = User::query()->where('email', $email)->whereIn('role', ['admin', 'kasir'])->first();

                if ($existing) {
                    $request->session()->put('google_link_pending', [
                        'google_id' => $googleId,
                        'email' => $email,
                        'intent' => 'admin',
                    ]);

                    return redirect()->route('admin.login')
                        ->withErrors(['google' => 'Akun Google terdeteksi. Masuk sekali dengan username dan password admin untuk mengaitkan akun secara aman.']);
                }

                return redirect()->route('admin.login')
                    ->withErrors(['google' => 'Akun Google ini tidak terdaftar sebagai admin atau kasir.']);
            }
        } else {
            $user = User::query()->where('google_id', $googleId)->where('role', 'customer')->first();

            if (! $user) {
                $existing = User::query()->where('email', $email)->first();

                if ($existing) {
                    $request->session()->put('google_link_pending', [
                        'google_id' => $googleId,
                        'email' => $email,
                        'intent' => 'customer',
                    ]);

                    return redirect()->route('login')
                        ->withErrors(['google' => 'Akun email sudah terdaftar. Masuk dengan username/password sekali untuk mengaitkan Google secara aman.']);
                }

                $displayName = trim((string) ($googleUser->getName() ?: 'Pengguna Google'));

                $user = DB::transaction(function () use ($email, $googleId, $displayName) {
                    $customer = Customer::query()->create([
                        'nama' => $displayName ?: 'Pengguna Google',
                        'no_whatsapp' => '-',
                        'alamat' => null,
                    ]);

                    $baseUsername = Str::slug(Str::before($email, '@'), '_');
                    $baseUsername = $baseUsername ?: 'customer';
                    $username = $baseUsername;
                    $suffix = 1;

                    while (User::query()->where('username', $username)->exists()) {
                        $username = $baseUsername.'_'.$suffix;
                        $suffix++;
                    }

                    return User::query()->create([
                        'nama' => $displayName ?: 'Pengguna Google',
                        'username' => $username,
                        'email' => $email,
                        'google_id' => $googleId,
                        'password' => Hash::make(Str::random(64)),
                        'role' => 'customer',
                        'customer_id' => $customer->id_customer,
                        'is_active' => true,
                    ]);
                });
            }
        }

        if (! (bool) ($user->is_active ?? true)) {
            return redirect()->route($intent === 'admin' ? 'admin.login' : 'login')
                ->withErrors(['google' => 'Akun Anda tidak aktif. Silakan hubungi administrator.']);
        }

        Auth::login($user);
        $request->session()->regenerate();
        UserSession::syncExpiredSessions($user->id_user);
        UserSession::registerActiveSession($user, $request);
        $this->recordLogin($user, $request, $intent);

        return redirect()->intended($intent === 'admin'
            ? route('admin.dashboard')
            : route('customer.dashboard'));
    }

    private function recordLogin(User $user, Request $request, string $intent): void
    {
        ActivityLog::query()->create([
            'user_id' => $user->id_user,
            'user_name' => $user->nama,
            'user_email' => $user->email,
            'role_name' => $user->role,
            'activity_type' => 'login_success',
            'description' => $intent === 'admin'
                ? 'Login Google berhasil untuk akun admin atau kasir'
                : 'Login Google berhasil untuk akun pelanggan',
            'metadata' => ['provider' => 'google', 'status' => 'active'],
            'ip_address' => $request->ip() ?: null,
            'user_agent' => $request->userAgent(),
            'device_info' => $request->header('User-Agent'),
            'session_reference' => UserSession::hashReference((int) $user->id_user, (string) $request->session()->getId()),
            'status' => 'active',
        ]);
    }
}
