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

        // Check if this is superadmin login (user exists in users table and is_superadmin = true)
        $user = User::where('user_id', $userId)->where('is_superadmin', true)->first();

        if ($user) {
            // Superadmin requires password
            if (!$password) {
                return back()->with('error', 'Password is required for superadmin');
            }

            if (!\Hash::check($password, $user->password)) {
                return back()->with('error', 'Invalid credentials');
            }

            // Store user info in session
            session([
                'user_id' => $user->user_id,
                'is_superadmin' => $user->is_superadmin,
                'permissions' => $user->getPermissionSlugs(),
            ]);

            return redirect('/')->with('success', 'Welcome back, Superadmin!');
        }

        // Regular user login (no password required)
        // user_id is the same as mp_id for regular users
        $manpower = MManpower::where('mp_id', $userId)->first();

        if (!$manpower) {
            return back()->with('error', 'User ID not found');
        }

        if (!$manpower->status) {
            return back()->with('error', 'Akun Anda dinonaktifkan/Inactive. Hubungi Admin.');
        }

        // Check if user exists, if not create one
        // For regular users, user_id = mp_id
        $user = User::firstOrCreate(
            ['user_id' => $userId],
            [
                'password' => null,
                'is_superadmin' => false,
            ]
        );

        // Load permissions explicitly
        $user->load('permissions');
        
        // Store user info in session
        session([
            'user_id' => $user->user_id,
            'is_superadmin' => $user->is_superadmin,
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
