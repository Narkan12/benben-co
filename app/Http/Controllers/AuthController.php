<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
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

        $staffAccountExists = User::query()
            ->where('username', $credentials['username'])
            ->whereIn('role', ['admin', 'kasir'])
            ->exists();

        if ($staffAccountExists && Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
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

        if ($user && Auth::attempt([
            'username' => $user->username,
            'password' => $credentials['password'],
            'role' => 'customer',
        ])) {
            $request->session()->regenerate();

            return redirect()->intended(route('customer.dashboard'));
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
        $isStaff = Auth::check() && in_array(Auth::user()->role, ['admin', 'kasir'], true);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isStaff ? 'admin.login' : 'login');
    }
}
