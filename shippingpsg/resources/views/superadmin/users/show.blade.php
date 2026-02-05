@extends('layout.app')

@section('content')
<div class="container mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.users.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Users</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold">User Details</h1>
            <p class="text-gray-600">{{ $user->user_id }}</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">User ID</label>
                <p class="mt-1 text-gray-900">{{ $user->user_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <p class="mt-1 text-gray-900">{{ $user->manpower->nama ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <p class="mt-1">
                    @if($user->is_superadmin)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            Superadmin
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            User
                        </span>
                    @endif
                </p>
            </div>
        </div>

        <div class="border-t pt-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Permissions</h2>
                @if(!$user->is_superadmin)
                    <a href="{{ route('superadmin.users.permissions.edit', $user) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Edit Permissions
                    </a>
                @endif
            </div>

            @if($user->is_superadmin)
                <p class="text-gray-600">Superadmin has access to all permissions</p>
            @else
                @if($user->permissions->count() > 0)
                    <div class="space-y-4">
                        @foreach($allPermissions as $category => $permissions)
                            @php
                                $userPermissionsInCategory = $user->permissions->where('category', $category);
                            @endphp
                            @if($userPermissionsInCategory->count() > 0)
                                <div class="border rounded-lg p-4">
                                    <h3 class="font-semibold text-lg mb-2 capitalize">{{ str_replace('_', ' ', $category) }}</h3>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($userPermissionsInCategory as $permission)
                                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                                {{ $permission->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600">No permissions assigned yet</p>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
