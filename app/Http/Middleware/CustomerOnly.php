<?php

namespace App\Http\Middleware;

use App\Models\UserSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerOnly
{
    public function handle(Request $request, Closure $next)
    {
        if (! Auth::check() || Auth::user()->role !== 'customer') {
            if (! Auth::check()) {
                $request->session()->flash('error', 'Silakan masuk atau daftar sebagai pelanggan sebelum berbelanja.');
                $request->session()->put('url.intended', $this->intendedUrl($request));

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Silakan masuk atau daftar sebagai pelanggan sebelum berbelanja.',
                        'login_url' => route('login'),
                    ], Response::HTTP_UNAUTHORIZED);
                }
            }

            return redirect()->route('login')->with('error', Auth::check()
                ? 'Silakan gunakan akun pelanggan untuk berbelanja.'
                : 'Silakan masuk atau daftar sebagai pelanggan sebelum berbelanja.');
        }

        $user = Auth::user();
        if (! (bool) ($user->is_active ?? true)) {
            UserSession::closeCurrentSession($user, $request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Akun Anda tidak aktif. Hubungi administrator.');
        }

        if ($request->routeIs('cart*', 'checkout*')) {
            $customerId = Auth::user()->customer_id;
            abort_unless($customerId, 403, 'Akun pelanggan belum terhubung ke profil pelanggan.');

            if ((int) $request->session()->get('cart_customer_id') !== (int) $customerId) {
                $request->session()->forget('cart');
                $request->session()->put('cart_customer_id', $customerId);
            }
        }

        return $next($request);
    }

    private function intendedUrl(Request $request): string
    {
        if ($request->isMethod('GET')) {
            return $request->fullUrl();
        }

        if ($request->routeIs('checkout.*', 'checkout')) {
            return route('checkout');
        }

        if ($request->routeIs('cart.add') && $request->boolean('go_to_checkout')) {
            return route('checkout');
        }

        if ($request->routeIs('cart.add')) {
            $referer = $request->headers->get('referer');
            $parts = $referer ? parse_url($referer) : false;
            if (is_array($parts) && isset($parts['host']) && strcasecmp($parts['host'], $request->getHost()) === 0) {
                return $request->getSchemeAndHttpHost().($parts['path'] ?? '/')
                    .(isset($parts['query']) ? '?'.$parts['query'] : '');
            }

            return route('products');
        }

        if ($request->routeIs('cart.*')) {
            return route('cart');
        }

        if ($request->routeIs('customer.order.*')) {
            return route('customer.orders');
        }

        return route('products');
    }
}
