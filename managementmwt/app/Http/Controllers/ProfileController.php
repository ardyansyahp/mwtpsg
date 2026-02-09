<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\User;

class ProfileController extends Controller
{
    /**
     * Show the profile edit form.
     */
    public function edit()
    {
        $userId = session('user_id');
        $user = User::where('user_id', $userId)->firstOrFail();
        
        return view('profile.edit', compact('user'));
    }

    /**
     * Update the user's username.
     */
    public function updateUsername(Request $request)
    {
        $userId = session('user_id');
        $user = User::where('user_id', $userId)->firstOrFail();

        $request->validate([
            'user_id' => [
                'required',
                'string',
                'max:255',
                'unique:users,user_id,' . $user->id,
            ],
        ], [
            'user_id.required' => 'Username is required',
            'user_id.unique' => 'This username is already taken',
        ]);

        $oldUserId = $user->user_id;
        $user->user_id = $request->user_id;
        $user->save();

        // Update session
        session(['user_id' => $request->user_id]);

        return redirect()->route('profile.edit')
            ->with('success', "Username successfully changed from '{$oldUserId}' to '{$request->user_id}'");
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        $userId = session('user_id');
        $user = User::where('user_id', $userId)->firstOrFail();

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(4)],
        ], [
            'current_password.required' => 'Current password is required',
            'current_password.current_password' => 'The current password is incorrect',
            'password.required' => 'New password is required',
            'password.confirmed' => 'Password confirmation does not match',
            'password.min' => 'Password must be at least 4 characters',
        ]);

        $user->password = Hash::make($request->password);
        $user->save();

        // Logout user for security - they must login with new password
        session()->flush();

        return redirect()->route('login')
            ->with('success', 'Password successfully changed. Please login with your new password.');
    }
}
