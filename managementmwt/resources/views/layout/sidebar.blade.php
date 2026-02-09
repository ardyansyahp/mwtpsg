<aside id="sidebar" class="flex-col w-64 bg-slate-900 border-r border-slate-800 transition-all duration-300 md:relative fixed inset-y-0 left-0 z-50 h-full overflow-y-auto">
    {{-- Management Sidebar --}}
    <nav class="p-4 flex flex-col sidebar-scroll min-h-full">
        <ul class="space-y-2 flex-1 list-none">
            <li>
                <a href="{{ url('/') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition-colors {{ request()->is('/') ? 'bg-emerald-500/20 text-emerald-400 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span>Executive Overview</span>
                </a>
            </li>
            <li>
            <li>
                <a href="{{ route('dashboard.vendor.index') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition-colors {{ request()->routeIs('dashboard.vendor.*') ? 'bg-emerald-500/20 text-emerald-400 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
                    </svg>
                    <span>Dashboard Vendor</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard.delivery.index') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition-colors {{ request()->routeIs('dashboard.delivery.*') ? 'bg-emerald-500/20 text-emerald-400 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    <span>Delivery Dashboard</span>
                </a>
            </li>
            <li>
                <a href="{{ route('dashboard.stock.index') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-emerald-500/10 hover:text-emerald-400 transition-colors {{ request()->routeIs('dashboard.stock.*') ? 'bg-emerald-500/20 text-emerald-400 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Stock Status</span>
                </a>
            </li>

            {{-- Divider --}}
            <li class="my-4">
                <div class="border-t border-slate-800"></div>
            </li>

            {{-- Profile Settings --}}
            <li>
                <a href="{{ route('profile.edit') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-slate-300 hover:bg-blue-500/10 hover:text-blue-400 transition-colors {{ request()->routeIs('profile.*') ? 'bg-blue-500/20 text-blue-400 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Profile Settings</span>
                </a>
            </li>

        </ul>

        <div class="mt-auto pt-6 border-t border-slate-800">
             <div class="px-4 py-3 bg-slate-800/50 rounded-2xl border border-slate-700/50">
                <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mb-1">Executive Session</p>
                <p class="text-[10px] font-black text-white truncate">{{ session('user_name') }}</p>
             </div>
        </div>
    </nav>
</aside>
