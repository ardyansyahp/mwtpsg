<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuperadmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }

        $user = \App\Models\User::where('user_id', session('user_id'))->first();

        if (!$user) {
            return redirect('/login')->with('error', 'User not found');
        }

        // Check if user is superadmin
        if (!$user->isSuperadmin()) {
            abort(403, 'Access denied. Superadmin only.');
        }

        return $next($request);
    }
}
