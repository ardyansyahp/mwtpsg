<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Prevent redirect loop
        if ($request->is('login') || $request->is('login/*')) {
            return $next($request);
        }

        // Check if user is logged in by checking session
        if (!session()->has('user_id')) {
            // Redirect to Portal Login (Root)
            $portalUrl = env('PORTAL_URL', 'https://portal.s2smfg.biz.id');
            return redirect()->away(env('URL_MASTER') . '/login');
        }

        return $next($request);
    }
}
