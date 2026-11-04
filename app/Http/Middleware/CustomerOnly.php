<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || Auth::user()->role !== 'customer') {
            return redirect()->route('login')->with('error', 'Silakan masuk sebagai pelanggan untuk melihat akun Anda.');
        }

        return $next($request);
    }
}
