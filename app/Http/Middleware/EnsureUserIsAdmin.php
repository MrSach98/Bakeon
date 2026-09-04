<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isAdmin()) {
            auth()->logout();

            return redirect()->route('admin.login')->with('error', 'Your session has expired. Please login again.');
        }

        return $next($request);
    }
}