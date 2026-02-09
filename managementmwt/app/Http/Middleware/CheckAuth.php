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
        // Prevent redirect loop if already on login page
        if ($request->is('login') || $request->is('login/*')) {
            return $next($request);
        }

        // Check if user is logged in by checking session
        if (!session()->has('user_id')) {
            // Redirect to Portal Login (Root) - Only if not already coming from there
            return redirect()->away('http://mwtpsg.test/login');
        }

        // Check if user has management role (role = 2)
        $userRole = session('role');
        if ($userRole !== 2) {
            // Redirect non-management users back to masterpsg with error
            session()->flash('error', 'Access denied. Management portal is only accessible to management users.');
            return redirect()->away('http://mwtpsg.test/masterpsg/public/');
        }

        return $next($request);
    }
}
