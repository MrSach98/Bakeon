<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerIsLoggedIn
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check()) {
           
            return redirect('/cart')->with('open_login_modal', true);
        }

        return $next($request);
    }
}