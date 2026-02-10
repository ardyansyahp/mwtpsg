<aside id="sidebar" class="flex-col w-64 bg-white border-r border-gray-200 transition-all duration-300 md:relative fixed inset-y-0 left-0 z-50 h-full overflow-y-auto">
    <nav class="p-4 flex flex-col sidebar-scroll min-h-full">
        @php
            // URLs from .env (Works for both Local & Production)
            $masterUrl = env('URL_MASTER');
            $supplierUrl = env('URL_SUPPLIER');
            $shippingUrl = env('URL_SHIPPING');
            $managementUrl = env('URL_MANAGEMENT');
        @endphp

        <div class="px-4 pb-4 mb-4 border-b border-gray-100">
             <h3 class="text-[10px] font-black text-slate-400 uppercase tracking-widest leading-none">Main Menu</h3>
        </div>
        <ul class="space-y-2 flex-1 list-none">
            {{-- Homepage --}}
            <li>
                <a href="{{ url('/') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->is('/') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                    <i class="fa-solid fa-house w-5 h-5 text-center"></i>
                    <span>Homepage</span>
                </a>
            </li>

            {{-- USER MANAGEMENT (SUPERADMIN ONLY) --}}
            @if(session('role') === 1)
            <li>
                <a href="{{ route('superadmin.users.index') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('superadmin.users.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                    <i class="fa-solid fa-users-gear w-5 h-5 text-center"></i>
                    <span>User Management</span>
                </a>
            </li>
            @endif
            
            {{-- APP SWITCHER (SUPERADMIN ONLY) --}}
            @if(session('is_superadmin'))
            <li class="mb-4 mt-2">
                <div class="px-3 py-2 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg border border-blue-100 shadow-sm relative overflow-hidden group/card hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 right-0 w-16 h-16 bg-blue-100 rounded-full blur-xl opacity-50 -mr-4 -mt-4 transition-transform group-hover/card:scale-150 duration-500"></div>
                    
                    <div class="flex items-center justify-between mb-2 relative z-10">
                        <p class="text-[10px] font-black text-blue-900 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fa-solid fa-cube text-blue-600"></i>
                            App Switcher
                        </p>
                        <span class="bg-blue-600 text-white text-[9px] font-bold px-1.5 py-0.5 rounded shadow-sm">ADMIN</span>
                    </div>

                    <div class="space-y-1 relative z-10">
                        <a href="{{ $supplierUrl }}" class="flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold text-gray-700 bg-white/60 hover:bg-white hover:text-blue-700 rounded border border-transparent hover:border-blue-200 transition-all duration-200 group shadow-sm hover:shadow">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-boxes-packing text-blue-500 w-4"></i> Supplier
                            </span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                        </a>
                        <a href="{{ $shippingUrl }}" class="flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold text-gray-700 bg-white/60 hover:bg-white hover:text-indigo-700 rounded border border-transparent hover:border-indigo-200 transition-all duration-200 group shadow-sm hover:shadow">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-truck-fast text-indigo-500 w-4"></i> Shipping
                            </span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 group-hover:text-indigo-500 transition-colors"></i>
                        </a>
                         <a href="{{ $managementUrl }}" class="flex items-center justify-between px-2.5 py-1.5 text-xs font-semibold text-gray-700 bg-white/60 hover:bg-white hover:text-purple-700 rounded border border-transparent hover:border-purple-200 transition-all duration-200 group shadow-sm hover:shadow">
                            <span class="flex items-center gap-2">
                                <i class="fa-solid fa-chart-pie text-purple-500 w-4"></i> Management
                            </span>
                            <i class="fa-solid fa-chevron-right text-[10px] text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                        </a>
                    </div>
                </div>
            </li>
            @endif

            {{-- MASTER DATA SECTION --}}
            @if(userCan('master.perusahaan.view') || userCan('master.mesin.view') || userCan('master.manpower.view') || userCan('master.plantgate.view') || userCan('master.kendaraan.view'))
            <li>
                @php $isMasterActive = request()->is('master/*'); @endphp
                <div>
                    <a href="#" id="masterDataToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isMasterActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-database w-5 h-5 text-center"></i>
                            <span>Master Data</span>
                        </div>
                        <i id="masterDataArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isMasterActive ? 'rotate-180' : '' }}"></i>
                    </a>
                    <ul id="masterDataSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isMasterActive ? '' : 'hidden' }}">
                        @if(userCan('master.perusahaan.view'))
                        <li><a href="{{ $masterUrl }}/master/perusahaan" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Perusahaan</span></a></li>
                        @endif
                        @if(userCan('master.mesin.view'))
                        <li><a href="{{ $masterUrl }}/master/mesin" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Mesin</span></a></li>
                        @endif
                        @if(userCan('master.manpower.view'))
                        <li><a href="{{ $masterUrl }}/master/manpower" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Manpower</span></a></li>
                        @endif
                        @if(userCan('master.plantgate.view'))
                        <li><a href="{{ $masterUrl }}/master/plantgate" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Gate Customer</span></a></li>
                        @endif
                        @if(userCan('master.kendaraan.view'))
                        <li><a href="{{ $masterUrl }}/master/kendaraan" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Kendaraan</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- SUB MASTER SECTION --}}
            @if(userCan('master.bahanbaku.view') || userCan('submaster.part.view') || userCan('master.mold.view') || userCan('submaster.plantgatepart.view'))
            <li>
                @php $isSubMasterActive = request()->is('submaster/*'); @endphp
                <div>
                     <a href="#" id="subMasterToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isSubMasterActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-layer-group w-5 h-5 text-center"></i>
                            <span>Sub Master</span>
                        </div>
                        <i id="subMasterArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isSubMasterActive ? 'rotate-180' : '' }}"></i>
                    </a>
                    <ul id="subMasterSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isSubMasterActive ? '' : 'hidden' }}">
                        @if(userCan('master.bahanbaku.view'))
                        <li><a href="{{ $masterUrl }}/submaster/bahanbaku" onclick="console.log('Bahan Baku Link:', this.href); return true;" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Bahan Baku</span></a></li>
                        @endif
                        @if(userCan('submaster.part.view'))
                        <li><a href="{{ $masterUrl }}/submaster/part" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Part</span></a></li>
                        @endif
                        @if(userCan('master.mold.view'))
                        <li><a href="{{ $masterUrl }}/submaster/mold" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Mold</span></a></li>
                        @endif
                        @if(userCan('submaster.plantgatepart.view'))
                        <li><a href="{{ $masterUrl }}/submaster/plantgatepart" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Part -> Gate</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- SUPPLIER PSG SECTION --}}
             @php
                $hasSupplierAccess = userCan('dashboard.controlsupplier.view') || userCan('controlsupplier.monitoring') || userCan('controlsupplier.view') || userCan('bahanbaku.receiving.view');
            @endphp
            @if($hasSupplierAccess)
            <li>
                 @php $isSupplierActive = (request()->is('controlsupplier*') || request()->is('bahanbaku*')); @endphp
                 <div>
                    <a href="#" id="bahanBakuToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isSupplierActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-boxes-packing w-5 h-5 text-center"></i>
                            <span>Supplier Portal</span>
                        </div>
                        <i id="bahanBakuArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isSupplierActive ? 'rotate-180' : '' }}"></i>
                    </a>
                    <ul id="bahanBakuSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isSupplierActive ? '' : 'hidden' }}">
                         @if(userCan('dashboard.controlsupplier.view'))
                         <li><a href="{{ $supplierUrl }}/controlsupplier/dashboard" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Dashboard</span></a></li>
                         @endif
                         @if(userCan('controlsupplier.monitoring') || userCan('controlsupplier.view'))
                         <li><a href="{{ $supplierUrl }}/controlsupplier/monitoring" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Control Supplier</span></a></li>
                         @endif
                         @if(userCan('bahanbaku.receiving.view'))
                         <li><a href="{{ $supplierUrl }}/bahanbaku/receiving" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Receiving</span></a></li>
                         @endif
                    </ul>
                 </div>
            </li>
            @endif

            {{-- SHIPPING PSG SECTION --}}
            {{-- STOCK --}}
             @if(userCan('finishgood.in.view') || userCan('finishgood.stock.view'))
            <li>
                @php $isStockActive = (request()->is('shipping/stock*') || request()->is('finishgood/in*')); @endphp
                <div>
                    <a href="#" id="stockToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isStockActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                             <i class="fa-solid fa-cubes-stacked w-5 h-5 text-center"></i>
                            <span>Stock FG</span>
                        </div>
                        <i id="stockArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isStockActive ? 'rotate-180' : '' }}"></i>
                    </a>
                    <ul id="stockSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isStockActive ? '' : 'hidden' }}">
                        <li><a href="{{ $shippingUrl }}/shipping/stock/opname" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Stock Opname</span></a></li>
                        <li><a href="{{ $shippingUrl }}/shipping/stock/po" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Purchase Order</span></a></li>
                        @if(userCan('finishgood.in.view'))
                        <li><a href="{{ $shippingUrl }}/finishgood/in" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>In (Scan)</span></a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif

            {{-- LOADING --}}
            @if(userCan('spk.view') || userCan('finishgood.out.view'))
            <li>
                 @php $isLoadingActive = (request()->is('spk*') || request()->is('finishgood/out*')); @endphp
                 <div>
                    <a href="#" id="loadingToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isLoadingActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-truck-ramp-box w-5 h-5 text-center"></i>
                            <span>Loading</span>
                        </div>
                        <i id="loadingArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isLoadingActive ? 'rotate-180' : '' }}"></i>
                    </a>
                    <ul id="loadingSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isLoadingActive ? '' : 'hidden' }}">
                         @if(userCan('spk.view'))
                         <li><a href="{{ $shippingUrl }}/spk" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>SPK</span></a></li>
                         @endif
                         @if(userCan('finishgood.out.view'))
                         <li><a href="{{ $shippingUrl }}/finishgood/out" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Out (Scan)</span></a></li>
                         @endif
                    </ul>
                 </div>
            </li>
            @endif

            {{-- SHIPPING --}}
             @if(userCan('shipping.dispatch.view') || userCan('shipping.delivery.view'))
             <li>
                @php $isShippingActive = (request()->is('shipping/dispatch*') || (request()->is('shipping/delivery*') && !request()->routeIs('shipping.delivery.dashboard'))); @endphp
                <div>
                     <a href="#" id="shippingToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isShippingActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                             <i class="fa-solid fa-truck-fast w-5 h-5 text-center"></i>
                            <span>Shipping</span>
                        </div>
                        <i id="shippingArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isShippingActive ? 'rotate-180' : '' }}"></i>
                    </a>
                     <ul id="shippingSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isShippingActive ? '' : 'hidden' }}">
                         @if(userCan('shipping.dispatch.view'))
                         <li><a href="{{ $shippingUrl }}/shipping/dispatch" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Penugasan</span></a></li>
                         @endif
                         @if(userCan('shipping.delivery.view'))
                         <li><a href="{{ $shippingUrl }}/shipping/delivery" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Delivery List</span></a></li>
                         @endif
                     </ul>
                </div>
            </li>
             @endif

             {{-- CONTROL --}}
             @if(userCan('shipping.controltruck.view') || userCan('shipping.tracker.view'))
             <li>
                @php $isControlActive = (request()->is('shipping/controltruck*') || request()->is('shipping/tracker*')); @endphp
                <div>
                     <a href="#" id="controlToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isControlActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-tower-broadcast w-5 h-5 text-center"></i>
                            <span>Control Truck</span>
                        </div>
                        <i id="controlArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isControlActive ? 'rotate-180' : '' }}"></i>
                    </a>
                     <ul id="controlSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isControlActive ? '' : 'hidden' }}">
                         @if(userCan('shipping.controltruck.view'))
                         <li><a href="{{ $shippingUrl }}/shipping/controltruck/monitoring" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Monitoring</span></a></li>
                         @endif
                          @if(userCan('shipping.tracker.view'))
                         <li><a href="{{ $shippingUrl }}/shipping/tracker" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Driver Map</span></a></li>
                         @endif
                     </ul>
                </div>
             </li>
            @endif

             {{-- DASHBOARD --}}
              @if(userCan('finishgood.stock.view') || userCan('dashboard.delivery.view'))
             <li>
                 @php $isDashboardActive = (request()->is('finishgood/stock*') || request()->routeIs('shipping.delivery.dashboard')); @endphp
                  <div>
                     <a href="#" id="dashboardToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isDashboardActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-chart-line w-5 h-5 text-center"></i>
                            <span>Analytic Board</span>
                        </div>
                        <i id="dashboardArrow" class="fa-solid fa-chevron-down w-4 h-4 transition-transform duration-200 {{ $isDashboardActive ? 'rotate-180' : '' }}"></i>
                    </a>
                     <ul id="dashboardSubmenu" class="submenu mt-1 ml-4 space-y-1 list-none {{ $isDashboardActive ? '' : 'hidden' }}">
                          @if(userCan('finishgood.stock.view'))
                         <li><a href="{{ $shippingUrl }}/finishgood/stock" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>FG Stock</span></a></li>
                         @endif
                          @if(userCan('dashboard.delivery.view'))
                         <li><a href="{{ $shippingUrl }}/shipping/delivery/dashboard" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Delivery Perf.</span></a></li>
                         @endif
                     </ul>
                  </div>
             </li>
              @endif

            {{-- PROFILE SETTINGS (for Kabag users) --}}
            @php
                $userId = session('user_id');
                $user = $userId ? \App\Models\User::where('user_id', $userId)->with('manpower')->first() : null;
                $isKabag = $user && $user->isKabag();
            @endphp
            @if($isKabag)
            <li class="mt-4">
                <div class="border-t border-gray-200 mb-4"></div>
                <a href="{{ route('profile.edit') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-purple-50 hover:text-purple-600 transition-colors {{ request()->routeIs('profile.*') ? 'bg-purple-50 text-purple-600 active' : '' }}">
                    <i class="fa-solid fa-user-gear w-5 h-5 text-center"></i>
                    <span>Profile Settings</span>
                </a>
            </li>
            @endif

        </ul>
    </nav>
</aside>