<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\MManpower;

class LoginController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLoginForm()
    {
        // Load all active manpower for autocomplete
        $manpowers = MManpower::where('status', true)->select('mp_id', 'nama')->get();
        return view('auth.login', compact('manpowers'));
    }

    /**
     * Handle login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'nullable|string',
        ]);

        $userId = $request->input('user_id');
        $password = $request->input('password');

        // 1. Check existing User account first (for Roles 1 & 2 which require password)
        $user = User::where('user_id', $userId)->first();

        // If user exists AND has privileged role (1=Superadmin or 2=Management)
        if ($user && in_array($user->role, [1, 2])) {
            // Privileged Role requires password
            if (!$password) {
                return back()->with('error', 'Password is required for this account');
            }

            if (!\Hash::check($password, $user->password)) {
                return back()->with('error', 'Invalid credentials');
            }

            // LOGIN USER USING LARAVEL AUTH
            \Auth::login($user);

            // Store user info in session
            session([
                'user_id' => $user->user_id,
                'role' => (int) $user->role,
                'user_name' => 'Administrator', // Or fetch from manpower if linked
                'mp_nama' => 'Administrator',
                'departemen' => 'SYSTEM ADMIN',
                'permissions' => $user->getPermissionSlugs(),
            ]);

            return $this->authenticated($request, $user) ?: redirect('/?ref=login');
        }

        // Regular user login (no password required)
        $manpower = MManpower::where('mp_id', $userId)
            ->orWhere('mp_id', 'LIKE', "%|$userId")
            ->first();

        if (!$manpower) {
            return back()->with('error', 'User ID not found.');
        }

        // Use the FULL ID from database for consistency
        $fullUserId = $manpower->mp_id;

        // Check if user exists, if not create one
        $user = User::firstOrCreate(
            ['user_id' => $fullUserId],
            [
                'password' => null,
                'role' => 0,
            ]
        );

        if (!$manpower->status) {
            return back()->with('error', 'Akun Anda dinonaktifkan/Inactive. Hubungi Admin.');
        }
        
        // Load permissions explicitly
        $user->load('permissions');
        
        // LOGIN USER USING LARAVEL AUTH
        \Auth::login($user);

        // Store user info in session
        session([
            'user_id' => $user->user_id,
            'role' => (int) $user->role,
            'user_name' => $manpower->nama,
            'mp_nama' => $manpower->nama,
            'departemen' => $manpower->departemen ?? 'STAFF',
            'permissions' => $user->getPermissionSlugs(),
        ]);

        return $this->authenticated($request, $user) ?: redirect('/');
    }

    /**
     * The user has been authenticated.
     */
    protected function authenticated(Request $request, $user)
    {
        // 0. Superadmin stays on Portal to choose application
        if ($user->role === 1) {
            return null;
        }

        // 1. Check for Executive/Management Access (Priority)
        // Ensure Superadmin is NOT redirected here automatically
        /* 
        MOVED TO ROUTES/WEB.PHP FOR BETTER CONTROL VIA PERMISSIONS
        
        if (!$user->is_superadmin && $user->hasPermission('management_dashboard')) {
            return redirect()->away('http://mwtpsg.test/managementmwt/public/');
        }

        // 2. Check for Supplier Access
        if ($user->hasPermission('controlsupplier.view') || $user->hasPermission('controlsupplier.monitoring')) {
            // Redirect to Supplier PSG via mwtpsg.test
            return redirect()->away('http://mwtpsg.test/supplierpsg/public/');
        }

        // 3. Check for Shipping Access
        if ($user->hasPermission('shipping.delivery.view') || $user->hasPermission('shipping.controltruck.view')) {
            // Redirect to Shipping PSG via mwtpsg.test
            return redirect()->away('http://mwtpsg.test/shippingpsg/public/');
        }
        */

        // Default: Stay on Master/Portal
        return null;
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        \Auth::logout();
        session()->flush();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
