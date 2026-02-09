<aside id="sidebar" class="flex-col w-64 bg-white border-r border-gray-200 transition-all duration-300 md:relative fixed inset-y-0 left-0 z-50 h-full overflow-y-auto">
    <nav class="p-4 flex flex-col sidebar-scroll min-h-full">
        @php
            // Dynamic URL Generation for Cross-App Navigation
            // This handles 'mwtpsg.test/appname/public' OR 'localhost/mwtpsg/appname/public' AND 'appname.test' scenarios automatically.
            
            $currentBase = url('/');
            $currentFolderName = basename(base_path()); // e.g., 'masterpsg'

            // URLs for other apps
            $masterUrl = str_replace($currentFolderName, 'masterpsg', $currentBase);
            $supplierUrl = str_replace($currentFolderName, 'supplierpsg', $currentBase);
            $shippingUrl = str_replace($currentFolderName, 'shippingpsg', $currentBase);
            $managementUrl = str_replace($currentFolderName, 'managementmwt', $currentBase);
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
                        <li><a href="{{ $masterUrl }}/submaster/bahanbaku" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm"><span>Bahan Baku</span></a></li>
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
<div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>