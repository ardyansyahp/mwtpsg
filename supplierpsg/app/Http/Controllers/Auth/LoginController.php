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
            if (!$password) {
                return back()->with('error', 'Password is required for this account');
            }

            if (!\Hash::check($password, $user->password)) {
                return back()->with('error', 'Invalid credentials');
            }

            // Store user info in session
            session([
                'user_id' => $user->user_id,
                'role' => (int) $user->role,
                'permissions' => $user->getPermissionSlugs(),
                'mp_nama' => 'Administrator', // Or fetch if linked
            ]);

            return redirect('/')->with('success', 'Welcome back!');
        }

        // Regular user login (no password required)
        $manpower = MManpower::where('mp_id', $userId)->first();

        if (!$manpower) {
            return back()->with('error', 'User ID not found');
        }

        if (!$manpower->status) {
            return back()->with('error', 'Akun Anda dinonaktifkan/Inactive. Hubungi Admin.');
        }

        // Check if user exists, if not create one
        $user = User::firstOrCreate(
            ['user_id' => $userId],
            [
                'password' => null,
                'role' => 0,
            ]
        );

        // Load permissions explicitly
        $user->load('permissions');
        
        // Store user info in session
        session([
            'user_id' => $user->user_id,
            'role' => (int) $user->role,
            'permissions' => $user->getPermissionSlugs(),
            'mp_nama' => $manpower->nama,
        ]);

        return redirect('/')->with('success', 'Welcome, ' . $manpower->nama . '!');
    }

    /**
     * Handle logout request.
     */
    public function logout(Request $request)
    {
        session()->flush();
        return redirect('/login')->with('success', 'Logged out successfully');
    }
}
