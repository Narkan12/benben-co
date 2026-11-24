<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || ! in_array(Auth::user()->role ?? '', ['admin', 'kasir'], true)) {
            return redirect()->route('admin.login')->with('error', 'Anda harus login sebagai admin atau kasir.');
        }

        $user = Auth::user();
        if (! (bool) ($user->is_active ?? true)) {
            UserSession::closeCurrentSession($user, $request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        return $next($request);
    }
}
