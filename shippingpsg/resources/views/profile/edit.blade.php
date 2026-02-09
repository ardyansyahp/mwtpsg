@extends('layout.app')

@section('title', 'Profile Settings')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-slate-100 to-slate-200 p-6">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-3xl font-black text-slate-800 mb-2">Profile Settings</h1>
            <p class="text-sm text-slate-600">Manage your account credentials and security</p>
        </div>

        {{-- Success Message --}}
        @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-semibold">{{ session('success') }}</span>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Change Username Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-white">Change Username</h2>
                            <p class="text-xs text-blue-100">Update your login username</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('profile.update.username') }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Current Username
                        </label>
                        <input type="text" 
                               value="{{ $user->user_id }}" 
                               disabled
                               class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-lg text-slate-600 font-mono text-sm">
                    </div>

                    <div class="mb-6">
                        <label for="user_id" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            New Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="user_id"
                               name="user_id" 
                               value="{{ old('user_id', $user->user_id) }}"
                               required
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-200 transition-all font-mono text-sm @error('user_id') border-red-500 @enderror">
                        @error('user_id')
                        <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <button type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Update Username
                    </button>
                </form>
            </div>

            {{-- Change Password Card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-500 to-emerald-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-black text-white">Change Password</h2>
                            <p class="text-xs text-emerald-100">Update your account password</p>
                        </div>
                    </div>
                </div>

                <form action="{{ route('profile.update.password') }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Current Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="current_password"
                               name="current_password" 
                               required
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all @error('current_password') border-red-500 @enderror">
                        @error('current_password')
                        <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password"
                               name="password" 
                               required
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all @error('password') border-red-500 @enderror">
                        @error('password')
                        <p class="mt-2 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                            {{ $message }}
                        </p>
                        @enderror
                        <p class="mt-2 text-xs text-slate-500">Minimum 4 characters</p>
                    </div>

                    <div class="mb-6">
                        <label for="password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Confirm New Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               id="password_confirmation"
                               name="password_confirmation" 
                               required
                               class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 transition-all">
                    </div>

                    <button type="submit" 
                            class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Update Password
                    </button>
                </form>
            </div>
        </div>

        {{-- Security Notice --}}
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h3 class="text-sm font-bold text-amber-900 mb-1">Security Notice</h3>
                    <p class="text-xs text-amber-800">
                        • If you change your username, you'll need to use the new username for your next login<br>
                        • Make sure to remember your new password - there is no password recovery system<br>
                        • For security reasons, you'll be logged out after changing your password
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
