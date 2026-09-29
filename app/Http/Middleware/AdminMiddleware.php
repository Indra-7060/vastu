<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin panel access: only users signed in through the separate "admin" guard with the
 * admin role. Storefront customers (the "web" guard) can never reach these routes.
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin || ! $admin->isAdmin() || ! $admin->is_active) {
            Auth::guard('admin')->logout();

            return redirect()->route('admin.login')->with('error', 'Please login as admin to continue.');
        }

        // Inside the admin panel, auth()->user() / $request->user() refer to the admin.
        Auth::shouldUse('admin');

        return $next($request);
    }
}
