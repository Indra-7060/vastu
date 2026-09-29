<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login', ['account' => 'required']));
        }

        // Admins use the separate admin panel login; never treat them as a storefront customer.
        if ($user->isAdmin()) {
            auth()->guard('web')->logout();

            return redirect()->route('login', ['account' => 'required']);
        }

        if (! $user->isCustomer() || ! $user->is_active) {
            auth()->logout();

            return redirect()->route('login')->withErrors([
                'email' => 'Your account is not available.',
            ]);
        }

        return $next($request);
    }
}
