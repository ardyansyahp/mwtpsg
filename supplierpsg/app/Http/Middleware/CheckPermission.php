<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The required permission slug
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Check if user is logged in
        if (!session()->has('user_id')) {
            return redirect('/login')->with('error', 'Please login to continue');
        }

        $user = \App\Models\User::where('user_id', session('user_id'))->first();

        if (!$user) {
            return redirect('/login')->with('error', 'User not found');
        }

        // Superadmin has all permissions
        if ($user->isSuperadmin()) {
            return $next($request);
        }

        // Check if user has the required permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'You do not have permission to access this page');
        }

        return $next($request);
    }
}
