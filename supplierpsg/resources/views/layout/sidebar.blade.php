<aside id="sidebar" class="w-64 bg-white border-r border-gray-200 transition-transform duration-300 ease-in-out fixed lg:static h-[calc(100vh-56px)] z-50 hidden lg:block overflow-y-auto sidebar-scroll">
    {{-- Menu Sidebar --}}
    <nav class="p-4 flex flex-col sidebar-scroll min-h-full">
        <ul class="space-y-2 flex-1">
            <li>
                <a href="{{ url('/') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->is('/') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span>Homepage</span>
                </a>
            </li>
            {{-- Dashboard Centralized Menu --}}
            @if(userCan('dashboard.view') || userCan('dashboard.rinci') || userCan('dashboard.bahanbaku.view') || userCan('dashboard.controlsupplier.view') || userCan('dashboard.inject.view') || userCan('dashboard.assy.view') || userCan('dashboard.wip.view') || userCan('dashboard.finishgood.in.view') || userCan('dashboard.finishgood.out.view') || userCan('dashboard.delivery.view'))
            <li>
                @php 
                    $isDashboardActive = request()->routeIs('*.dashboard'); 
                @endphp
                <div>
                    <a href="#" id="dashboardToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isDashboardActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </div>
                        <svg id="dashboardArrow" class="w-4 h-4 transition-transform duration-200 {{ $isDashboardActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    <ul id="dashboardSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isDashboardActive ? '' : 'hidden' }}">
                        @if(userCan('dashboard.bahanbaku.view'))
                        <li>
                            <a href="{{ route('bahanbaku.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('bahanbaku.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Bahan Baku</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.controlsupplier.view'))
                        <li>
                            <a href="{{ route('controlsupplier.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('controlsupplier.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Control Supplier</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.inject.view'))
                        <li>
                            <a href="{{ route('produksi.inject.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.inject.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Produksi Inject</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.assy.view'))
                        <li>
                            <a href="{{ route('produksi.assy.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.assy.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Produksi Assy</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.wip.view'))
                        <li>
                            <a href="{{ route('produksi.wip.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.wip.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>WIP (Barang ½ Jadi)</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.finishgood.in.view'))
                        <li>
                            <a href="{{ route('finishgood.in.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('finishgood.in.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Finish Good In</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.finishgood.out.view'))
                        <li>
                            <a href="{{ route('finishgood.out.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('finishgood.out.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Finish Good Out</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('dashboard.delivery.view'))
                        <li>
                            <a href="{{ route('shipping.delivery.dashboard') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('shipping.delivery.dashboard') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Shipping Delivery</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            {{-- Master Data Menu with Sub Menu --}}
            @if(userCan('master.perusahaan.view') || userCan('master.mesin.view') || userCan('master.manpower.view') || userCan('master.plantgate.view') || userCan('master.kendaraan.view'))
            <li>
                @php $isMasterActive = request()->is('master/*'); @endphp
                <div>
                    <a href="#" id="masterDataToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isMasterActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Master Data</span>
                        </div>
                        <svg id="masterDataArrow" class="w-4 h-4 transition-transform duration-200 {{ $isMasterActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    {{-- Sub Menu --}}
                    <ul id="masterDataSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isMasterActive ? '' : 'hidden' }}">
                        @if(userCan('master.perusahaan.view'))
                        <li>
                            <a href="{{ route('master.perusahaan.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.perusahaan.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Perusahaan</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('master.mesin.view'))
                        <li>
                            <a href="{{ route('master.mesin.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.mesin.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Mesin</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('master.manpower.view'))
                        <li>
                            <a href="{{ route('master.manpower.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.manpower.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Manpower</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('master.plantgate.view'))
                        <li>
                            <a href="{{ route('master.plantgate.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.plantgate.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Gate Customer</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('master.kendaraan.view'))
                        <li>
                            <a href="{{ route('master.kendaraan.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.kendaraan.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Kendaraan</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            {{-- Sub Master Menu with Sub Menu --}}
            @if(userCan('master.bahanbaku.view') || userCan('submaster.part.view') || userCan('master.mold.view') || userCan('submaster.plantgatepart.view'))
            <li>
                @php $isSubMasterActive = request()->is('submaster/*'); @endphp
                <div>
                    <a href="#" id="subMasterToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isSubMasterActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <span>Sub Master</span>
                        </div>
                        <svg id="subMasterArrow" class="w-4 h-4 transition-transform duration-200 {{ $isSubMasterActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    {{-- Sub Menu --}}
                    <ul id="subMasterSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isSubMasterActive ? '' : 'hidden' }}">
                        @if(userCan('master.bahanbaku.view'))
                        <li>
                            <a href="{{ route('master.bahanbaku.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.bahanbaku.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Bahan Baku</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('submaster.part.view'))
                        <li>
                            <a href="{{ route('submaster.part.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('submaster.part.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Part</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('master.mold.view'))
                        <li>
                            <a href="{{ route('master.mold.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('master.mold.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Mold</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('submaster.plantgatepart.view'))
                        <li>
                            <a href="{{ route('submaster.plantgatepart.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('submaster.plantgatepart.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Part -> Gate</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            {{-- Bahan Baku Menu with Sub Menu --}}
            @if(userCan('controlsupplier.monitoring') || userCan('controlsupplier.view') || userCan('bahanbaku.receiving.view') || userCan('planning.view') || userCan('planning.matriks.view') || userCan('bahanbaku.supply.view') || userCan('planning.input') || userCan('planning.matriks'))
            <li>
                @php 
                    $isBahanBakuActive = (request()->is('bahanbaku/*') || request()->is('controlsupplier/*') || request()->is('planning*')) && !request()->routeIs('*.dashboard'); 
                @endphp
                <div>
                    <a href="#" id="bahanBakuToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isBahanBakuActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0H4m16 0v6a2 2 0 01-2 2H6a2 2 0 01-2-2v-6" />
                            </svg>
                            <span>Bahan Baku</span>
                        </div>
                        <svg id="bahanBakuArrow" class="w-4 h-4 transition-transform duration-200 {{ $isBahanBakuActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    {{-- Sub Menu --}}
                    <ul id="bahanBakuSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isBahanBakuActive ? '' : 'hidden' }}">
                         @if(userCan('controlsupplier.monitoring') || userCan('controlsupplier.view'))
                         <li>
                            <a href="{{ route('controlsupplier.monitoring') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('controlsupplier.monitoring') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Control Supplier</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('bahanbaku.receiving.view'))
                        <li>
                            <a href="{{ route('bahanbaku.receiving.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('bahanbaku.receiving.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Receiving</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('planning.view'))
                         <li>
                            <a href="{{ route('planning.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('planning.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Input Planning</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('planning.matriks.view'))
                        <li>
                            <a href="{{ route('planning.matriks') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('planning.matriks') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Matriks Planning</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('bahanbaku.supply.view'))
                        <li>
                            <a href="{{ route('bahanbaku.supply.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('bahanbaku.supply.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Supply</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            {{-- Produksi Menu with Sub Menu --}}
            @if(userCan('produksi.inject.in.view') || userCan('produksi.inject.out.view') || userCan('produksi.wip.in.view') || userCan('produksi.wip.out.view') || userCan('produksi.assy.in.view') || userCan('produksi.assy.out.view'))
            <li>
                @php $isProduksiActive = request()->is('produksi*') && !request()->routeIs('*.dashboard'); @endphp
                <div>
                    <a href="#" id="produksiToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isProduksiActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                            </svg>
                            <span>Produksi</span>
                        </div>
                        <svg id="produksiArrow" class="w-4 h-4 transition-transform duration-200 {{ $isProduksiActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                {{-- Sub Menu --}}
                <ul id="produksiSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isProduksiActive ? '' : 'hidden' }}">
                    @if(userCan('produksi.inject.in.view'))
                    <li>
                        <a href="{{ route('produksi.inject.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.inject.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                            <span>Inject In</span>
                        </a>
                    </li>
                    @endif
                    @if(userCan('produksi.inject.out.view'))
                    <li>
                        <a href="{{ route('produksi.inject.indexout') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.inject.indexout') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                            <span>Inject Out</span>
                        </a>
                    </li>
                    @endif
                    @if(userCan('produksi.wip.in.view'))
                    <li>
                        <a href="{{ route('produksi.wip.indexin') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.wip.indexin') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                            <span>WIP In</span>
                        </a>
                    </li>
                    @endif
                    @if(userCan('produksi.wip.out.view'))
                    <li>
                        <a href="{{ route('produksi.wip.indexout') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.wip.indexout') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                            <span>WIP Out</span>
                        </a>
                    </li>
                    @endif
                    @if(userCan('produksi.assy.in.view'))
                    <li>
                        <a href="{{ route('produksi.assy.indexin') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.assy.indexin') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                            <span>Assy In</span>
                        </a>
                    </li>
                    @endif
                    @if(userCan('produksi.assy.out.view'))
                    <li>
                        <a href="{{ route('produksi.assy.indexout') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('produksi.assy.indexout') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                            <span>Assy Out</span>
                        </a>
                    </li>
                    @endif
                </ul>
                </div>
            </li>
            @endif
            {{-- Finish Good Menu with Sub Menu --}}
            @if(userCan('finishgood.in.view') || userCan('spk.view') || userCan('finishgood.out.view') || userCan('finishgood.stock.view'))
            <li>
                @php $isFinishGoodActive = (request()->is('finishgood*') || request()->is('spk*')) && !request()->routeIs('*.dashboard'); @endphp
                <div>
                    <a href="#" id="finishGoodToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isFinishGoodActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Finish Good</span>
                        </div>
                        <svg id="finishGoodArrow" class="w-4 h-4 transition-transform duration-200 {{ $isFinishGoodActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    {{-- Sub Menu --}}
                    <ul id="finishGoodSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isFinishGoodActive ? '' : 'hidden' }}">
                        @if(userCan('finishgood.in.view'))
                        <li>
                            <a href="{{ route('finishgood.in.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('finishgood.in.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>In</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('spk.view'))
                        <li>
                            <a href="{{ route('spk.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('spk.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>SPK</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('finishgood.out.view'))
                        <li>
                            <a href="{{ route('finishgood.out.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('finishgood.out.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Out</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('finishgood.stock.view'))
                        <li>
                            <a href="{{ route('finishgood.stock.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('finishgood.stock.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Stock FG</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            {{-- Shipping Menu with Sub Menu --}}
            @if(userCan('shipping.controltruck.view') || userCan('shipping.delivery.view') || userCan('shipping.dispatch.view') || userCan('shipping.status.view') || userCan('shipping.tracker.view'))
            <li>
                @php $isShippingActive = request()->is('shipping*') && !request()->routeIs('*.dashboard'); @endphp
                <div>
                    <a href="#" id="shippingToggle" class="menu-item flex items-center justify-between px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ $isShippingActive ? 'active' : '' }}">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span>Shipping</span>
                        </div>
                        <svg id="shippingArrow" class="w-4 h-4 transition-transform duration-200 {{ $isShippingActive ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </a>

                    {{-- Sub Menu --}}
                    <ul id="shippingSubmenu" class="submenu mt-1 ml-4 space-y-1 {{ $isShippingActive ? '' : 'hidden' }}">
                        @if(userCan('shipping.controltruck.view'))
                        <li>
                            <a href="{{ route('shipping.controltruck.monitoring') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('shipping.controltruck.monitoring') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Control Truck</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('shipping.dispatch.view'))
                        <li>
                            <a href="{{ route('shipping.dispatch.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('shipping.dispatch.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Penugasan Driver</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('shipping.delivery.view'))
                        <li>
                            <a href="{{ route('shipping.delivery.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('shipping.delivery.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Delivery</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('shipping.status.view'))
                        <li>
                            <a href="{{ route('shipping.status.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('shipping.status.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Status Dashboard</span>
                            </a>
                        </li>
                        @endif
                        @if(userCan('shipping.tracker.view'))
                        <li>
                            <a href="{{ route('shipping.tracker.index') }}" class="submenu-item flex items-center gap-3 px-4 py-2 rounded-lg text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors text-sm {{ request()->routeIs('shipping.tracker.index') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                                <span>Map Tracker (Beta)</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
            @if(userCan('tracer.view'))
            <li>
                <a href="{{ route('tracer.index') }}" class="menu-item flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors {{ request()->routeIs('tracer.*') ? 'bg-blue-50 text-blue-600 active' : '' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span>Part Tracer</span>
                </a>
            </li>
            @endif
        </ul>


    </nav>
</aside>

{{-- Overlay for mobile --}}
<div id="sidebarOverlay" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>