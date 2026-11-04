<?php

namespace App\Http\Middleware;

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

        return $next($request);
    }
}
