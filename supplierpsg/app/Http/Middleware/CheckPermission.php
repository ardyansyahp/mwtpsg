<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Middleware untuk cek permission user (support multi-project)
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Get user from session
        $userId = session('user_id');
        
        if (!$userId) {
            return redirect()->away('http://mwtpsg.test/login');
        }
        
        // Load User model from masterpsg (shared database)
        $user = \App\Models\User::find($userId);
        
        if (!$user) {
            return redirect()->away('http://mwtpsg.test/login');
        }
        
        // Superadmin bisa akses semua
        if ($user->is_superadmin) {
            return $next($request);
        }
        
        // Check permission
        if (!$user->hasPermission($permission)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        
        return $next($request);
    }
}
