@extends('layout.app')

@section('content')
<div class="fade-in" x-data="userManagement()">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">User Management</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Kelola akun pengguna dan hak akses sistem</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search Form --}}
            <form action="{{ route('superadmin.users.index') }}" method="GET" class="flex gap-2">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Search User / MP ID..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('superadmin.users.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </form>

            {{-- Bulk Actions Toolbar --}}
            <div x-show="selectedUsers.length > 0" x-transition.opacity class="flex items-center gap-3 bg-red-50 px-3 py-2 rounded-lg border border-red-100 mr-2">
                <span class="text-red-700 text-sm font-medium"><span x-text="selectedUsers.length">0</span> selected</span>
                <button @click="window.location.href = '{{ route('superadmin.users.bulk_permissions') }}?ids=' + selectedUsers.join(',')" class="text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded text-xs font-bold transition-colors shadow-sm">
                    ASSIGN PERMISSIONS
                </button>
            </div>
        </div>
    </div>

    {{-- Success/Warning Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                            <input type="checkbox" @change="toggleAll($event)" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            NIK
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            NAMA
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            DEPARTEMEN
                        </th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            QUICK ROLE
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($users as $index => $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if(!$user->is_superadmin)
                                    <input type="checkbox" value="{{ $user->id }}" x-model="selectedUsers" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $users->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $user->manpower->nik ?? $user->user_id }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 uppercase">
                                {{ $user->manpower->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <span class="uppercase font-bold">{{ $user->manpower->departemen ?? '-' }}</span>
                                @if(isset($user->manpower->bagian))
                                    <span class="text-gray-400 uppercase">({{ $user->manpower->bagian }})</span>
                                @endif
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($user->is_superadmin)
                                    <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold tracking-wide rounded-full bg-purple-100 text-purple-700 uppercase">
                                        Superadmin
                                    </span>
                                @else
                                   <span class="{{ $user->permissions->count() > 0 ? 'text-green-600 font-semibold' : 'text-gray-400' }}">
                                        {{ $user->permissions->count() }} permissions
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                     @if(!$user->is_superadmin)
                                        <a href="{{ route('superadmin.users.permissions.edit', $user) }}" class="text-blue-600 hover:text-blue-900 transition-colors bg-blue-50 px-2 py-1 rounded-md border border-blue-200 hover:bg-blue-100" title="Manage Permissions">
                                           Permissions
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                 <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                 <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Rows Per Page --}}
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
             <div class="flex items-center gap-4 text-sm text-gray-600 order-2 md:order-1">
                <div class="flex items-center gap-2">
                    <span>Tampilkan</span>
                    <select onchange="window.location.href = this.value" class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-gray-50 text-xs font-medium cursor-pointer">
                        <option value="{{ $users->url(1) }}&per_page=10" {{ $users->perPage() == 10 ? 'selected' : '' }}>10</option>
                        <option value="{{ $users->url(1) }}&per_page=25" {{ $users->perPage() == 25 ? 'selected' : '' }}>25</option>
                        <option value="{{ $users->url(1) }}&per_page=50" {{ $users->perPage() == 50 ? 'selected' : '' }}>50</option>
                    </select>
                    <span>data per halaman</span>
                </div>
                 <div class="border-l border-gray-300 h-4 mx-2"></div>
                <div>
                     Menampilkan <span class="font-medium text-gray-900">{{ $users->firstItem() }}</span> - <span class="font-medium text-gray-900">{{ $users->lastItem() }}</span> dari <span class="font-medium text-gray-900">{{ $users->total() }}</span> data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $users->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

@push('scripts')
<script>
    function userManagement() {
        return {
            selectedUsers: [],
            
            toggleAll(e) {
                if (e.target.checked) {
                    const checkboxes = document.querySelectorAll('tbody input[type="checkbox"]');
                    this.selectedUsers = Array.from(checkboxes).map(cb => cb.value);
                } else {
                    this.selectedUsers = [];
                }
            }
        }
    }
</script>
@endpush
@endsection
