<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\User;
use App\Models\UserSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir'], true)) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    public function showCustomerLogin()
    {
        if (Auth::check() && Auth::user()->role === 'customer') {
            return redirect()->route('customer.dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('username', $credentials['username'])
            ->whereIn('role', ['admin', 'kasir'])
            ->first();

        if ($user && ! (bool) ($user->is_active ?? true)) {
            return back()->withErrors(['username' => 'Akun admin atau kasir tidak aktif.'])->onlyInput('username');
        }

        if ($user && Auth::attempt($credentials)) {
            $pending = $request->session()->pull('google_link_pending');
            if (is_array($pending)
                && ($pending['intent'] ?? null) === 'admin'
                && hash_equals(strtolower((string) ($pending['email'] ?? '')), strtolower((string) $user->email))
                && in_array($user->role, ['admin', 'kasir'], true)
                && ! User::query()->where('google_id', (string) ($pending['google_id'] ?? ''))->where('id_user', '!=', $user->id_user)->exists()) {
                $user->google_id = (string) $pending['google_id'];
                $user->save();
            }

            $request->session()->regenerate();
            UserSession::syncExpiredSessions($user->id_user);
            UserSession::registerActiveSession($user, $request);
            $this->recordAuthEvent($user, 'login_success', 'Admin atau kasir berhasil login', 'active', $request);

            return redirect()->intended(route('admin.dashboard'));
        }

        if ($user) {
            $this->recordAuthEvent($user, 'login_failed', 'Percobaan login gagal untuk akun admin atau kasir', 'failed', $request);
        }

        return back()->withErrors(['username' => 'Username atau password salah.'])->onlyInput('username');
    }

    public function customerLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $identifier = trim($credentials['username']);
        $user = User::query()
            ->where('role', 'customer')
            ->where(function ($query) use ($identifier) {
                $query->where('username', $identifier)->orWhere('email', $identifier);
            })
            ->first();

        if ($user && ! (bool) ($user->is_active ?? true)) {
            return back()->withErrors(['username' => 'Akun pelanggan tidak aktif.'])->onlyInput('username');
        }

        if ($user && Auth::attempt([
            'username' => $user->username,
            'password' => $credentials['password'],
            'role' => 'customer',
        ])) {
            $pending = $request->session()->pull('google_link_pending');
            if (is_array($pending)
                && ($pending['intent'] ?? null) === 'customer'
                && hash_equals(strtolower((string) ($pending['email'] ?? '')), strtolower((string) $user->email))
                && $user->role === 'customer'
                && ! User::query()->where('google_id', (string) ($pending['google_id'] ?? ''))->where('id_user', '!=', $user->id_user)->exists()) {
                $user->google_id = (string) $pending['google_id'];
                $user->save();
            }

            $request->session()->regenerate();
            UserSession::syncExpiredSessions($user->id_user);
            UserSession::registerActiveSession($user, $request);
            $this->recordAuthEvent($user, 'login_success', 'Pelanggan berhasil login', 'active', $request);

            return redirect()->intended(route('customer.dashboard'));
        }

        if ($user) {
            $this->recordAuthEvent($user, 'login_failed', 'Percobaan login gagal untuk akun pelanggan', 'failed', $request);
        }

        return back()->withErrors(['username' => 'Username atau password pelanggan salah.'])->onlyInput('username');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email:rfc', 'max:150', Rule::unique('users', 'email')],
            'no_whatsapp' => ['required', 'string', 'max:20'],
            'alamat' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($data) {
            $customer = Customer::query()->create([
                'nama' => $data['nama'],
                'no_whatsapp' => $data['no_whatsapp'],
                'alamat' => $data['alamat'] ?? null,
            ]);

            User::query()->create([
                'nama' => $data['nama'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'customer',
                'customer_id' => $customer->id_customer,
            ]);
        });

        return redirect()->route('login')->with('success', 'Akun berhasil dibuat. Silakan masuk.');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        $isStaff = $user && in_array($user->role, ['admin', 'kasir'], true);

        if ($user) {
            UserSession::syncExpiredSessions($user->id_user);
            UserSession::closeCurrentSession($user, $request);
            $this->recordAuthEvent($user, 'logout', 'Pengguna logout dari aplikasi', 'logged_out', $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isStaff ? 'admin.login' : 'login');
    }

    private function recordAuthEvent(User $user, string $activityType, string $description, string $status, Request $request): void
    {
        ActivityLog::query()->create([
            'user_id' => $user->id_user,
            'user_name' => $user->nama,
            'user_email' => $user->email,
            'role_name' => $user->role,
            'activity_type' => $activityType,
            'description' => $description,
            'metadata' => [
                'status' => $status,
            ],
            'ip_address' => $request->ip() ?: null,
            'user_agent' => $request->userAgent(),
            'device_info' => $request->header('User-Agent'),
            'session_reference' => UserSession::hashReference((int) $user->id_user, (string) $request->session()->getId()),
            'status' => $status,
        ]);
    }
}
