<header id="appHeader" class="bg-white border-b border-gray-200 sticky top-0 z-[100]">
    <div class="flex items-center justify-between px-3 sm:px-6 py-2">
        {{-- Left Side --}}
        <div class="flex items-center gap-2 sm:gap-4">
            {{-- Hamburger Menu --}}
            <button id="sidebarToggle" class="p-1.5 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM9 4v16"/>
                </svg>
            </button>

            {{-- App Title --}}
            <div class="flex items-center gap-2">
                <h2 class="text-sm sm:text-base font-semibold text-gray-800 truncate max-w-[150px] sm:max-w-none">Supplier PSG</h2>
            </div>
        </div>

        {{-- Right Side --}}
        <div class="flex items-center gap-2 sm:gap-4">
            {{-- User Profile Dropdown --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center gap-2 cursor-pointer hover:bg-gray-50 rounded-lg px-3 py-2 transition-colors">
                    {{-- User Avatar with Initial --}}
                    <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center text-emerald-900 text-sm font-semibold shadow-sm">
                        @if(session('is_superadmin'))
                            SA
                        @else
                            {{ strtoupper(substr(session('mp_nama', session('user_id', 'U')), 0, 1)) }}
                        @endif
                    </div>
                    
                    {{-- User Name & Role --}}
                    <div class="hidden sm:block text-left">
                        <div class="text-sm font-medium text-black">
                            @if(session('is_superadmin'))
                                Superadmin
                            @else
                                {{ session('mp_nama', session('user_id', 'User')) }}
                            @endif
                        </div>
                        <div class="text-xs text-black">
                            @if(session('is_superadmin'))
                                Administrator
                            @else
                                {{ session('user_id') }}
                            @endif
                        </div>
                    </div>
                    
                    {{-- Dropdown Icon --}}
                    <svg class="w-4 h-4 text-gray-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                {{-- Dropdown Menu --}}
                <div x-show="open" 
                     @click.away="open = false"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                     style="display: none;">
                    
                    {{-- User Info --}}
                    <div class="px-4 py-3 border-b border-gray-100">
                        <div class="text-sm font-bold text-gray-900 truncate">
                            @if(session('is_superadmin'))
                                Superadmin
                            @else
                                {{ session('mp_nama', session('user_id', 'User')) }}
                            @endif
                        </div>
                        <div class="text-[10px] text-gray-400 mt-1 uppercase tracking-wider font-bold truncate">
                            ID: {{ session('user_id') }}
                        </div>
                        
                        <div class="mt-2.5">
                            @if(session('is_superadmin'))
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-purple-100 text-purple-700 border border-purple-200">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Superadmin
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-emerald-100 text-emerald-700 border border-emerald-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    {{ session('departemen', 'PURCHASING STAFF') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Menu Items --}}

                    <div class="border-t border-gray-100 my-1"></div>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                            <i class="fa-solid fa-right-from-bracket mr-3 text-red-500"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

{{-- Alpine.js for dropdown --}}
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
// Header button handlers
(function() {

})();
</script>
