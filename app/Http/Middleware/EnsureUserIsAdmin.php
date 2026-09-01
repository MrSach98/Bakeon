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
            return redirect()->route('admin.login')->withErrors([
                'email' => 'Aapke paas admin panel access nahi hai.',
            ]);
        }

        return $next($request);
    }
}