<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                // An admin left in the customer session (older logins) is signed out of the storefront.
                if ($guard !== 'admin' && Auth::guard($guard)->user()->isAdmin()) {
                    Auth::guard($guard)->logout();

                    return $next($request);
                }

                return $guard === 'admin'
                    ? redirect(RouteServiceProvider::HOME)
                    : redirect()->route('account', 'overview');
            }
        }

        return $next($request);
    }
}
