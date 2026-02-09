@extends('layout.app')

@section('title', 'Dashboard - Master PSG')

@section('content')
<div class="bg-gray-50 flex flex-col min-h-full">
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-2">
        <div>
            <h1 class="text-xl font-bold text-gray-900 leading-none">Homepage Overview</h1>
            <p class="text-[9px] text-gray-500 mt-1 uppercase tracking-wider font-bold">Master Data Management System</p>
        </div>
        <div class="flex items-center gap-2 text-[10px] font-bold text-gray-600 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm transition-all hover:bg-gray-50">
            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ now()->format('l, d F Y') }}
        </div>
    </div>



    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-3 shrink-0">
        @php
            $kpis = [
                [
                    'label' => 'Total Perusahaan',
                    'value' => \App\Models\MPerusahaan::count(),
                    'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5',
                    'color' => 'blue',
                    'can' => 'master.perusahaan.view'
                ],
                [
                    'label' => 'Total Mesin',
                    'value' => \App\Models\MMesin::count(),
                    'icon' => 'M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z',
                    'color' => 'indigo',
                    'can' => 'master.mesin.view'
                ],
                [
                    'label' => 'Total Manpower',
                    'value' => \App\Models\MManpower::count(),
                    'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
                    'color' => 'violet',
                    'can' => 'master.manpower.view'
                ],
                [
                    'label' => 'Total Part Number',
                    'value' => \App\Models\SMPart::count(),
                    'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                    'color' => 'sky',
                    'can' => 'submaster.part.view'
                ]
            ];
        @endphp

        @foreach($kpis as $kpi)
            @if(userCan($kpi['can']))
            <div class="bg-white rounded-lg border border-gray-200 p-3 shadow-sm border-b-2 border-b-{{ $kpi['color'] }}-500 transition-all hover:bg-gray-50/50">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-{{ $kpi['color'] }}-50 text-{{ $kpi['color'] }}-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $kpi['icon'] }}" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-[9px] font-bold text-gray-400 uppercase tracking-widest leading-none">{{ $kpi['label'] }}</p>
                        <h3 class="text-base font-black text-gray-900 mt-1.5 leading-none">{{ number_format($kpi['value']) }}</h3>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    {{-- Main Content Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-3 flex-1 min-h-0">
        {{-- Operations Column --}}
        <div class="lg:col-span-3 flex flex-col min-h-0">
            {{-- Application Access Portals --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3 shrink-0">
                @if(auth()->user()->is_superadmin || userCan('controlsupplier.view') || userCan('controlsupplier.monitoring'))
                <a href="http://mwtpsg.test/supplierpsg/public/" class="group relative overflow-hidden bg-emerald-600 rounded-xl p-4 shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5">
                    <div class="absolute right-0 top-0 p-3 opacity-10">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
                        </svg>
                    </div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white leading-none tracking-tight">SUPPLIER PORTAL</h3>
                            <p class="text-[10px] text-emerald-100 font-bold mt-1 uppercase tracking-wider">Control Supplier & Monitoring</p>
                        </div>
                        <div class="ml-auto">
                           <div class="bg-white/20 p-1.5 rounded-lg backdrop-blur-sm group-hover:bg-white/30 transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                           </div>
                        </div>
                    </div>
                </a>
                @endif

                @if(auth()->user()->is_superadmin || userCan('shipping.delivery.view') || userCan('shipping.controltruck.view'))
                <a href="http://mwtpsg.test/shippingpsg/public/" class="group relative overflow-hidden bg-orange-600 rounded-xl p-4 shadow-md transition-all hover:shadow-lg hover:-translate-y-0.5">
                    <div class="absolute right-0 top-0 p-3 opacity-10">
                        <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4 m0 0l4 4m-4-4" />
                        </svg>
                    </div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-white/20 flex items-center justify-center text-white backdrop-blur-sm">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-black text-white leading-none tracking-tight">SHIPPING PORTAL</h3>
                            <p class="text-[10px] text-orange-100 font-bold mt-1 uppercase tracking-wider">Delivery & Truck Control</p>
                        </div>
                        <div class="ml-auto">
                           <div class="bg-white/20 p-1.5 rounded-lg backdrop-blur-sm group-hover:bg-white/30 transition-colors">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                </svg>
                           </div>
                        </div>
                    </div>
                </a>
                @endif
            </div>

            <div class="bg-white border border-gray-200 shadow-sm flex-1 flex flex-col overflow-hidden rounded-xl">
                <div class="px-4 py-2.5 border-b border-gray-100 bg-gray-50/50 flex-shrink-0">
                    <h2 class="text-[9px] font-bold text-gray-800 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Main Authority Hub
                    </h2>
                </div>
                <div class="p-4 flex-1 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 h-full">
                        @php
                            $ops = [
                                [
                                    'title' => 'Tambah Perusahaan',
                                    'desc' => 'Registrasi vendor atau entitas baru',
                                    'route' => 'master.perusahaan.create',
                                    'icon' => 'M12 4v16m8-8H4',
                                    'color' => 'blue',
                                    'can' => 'master.perusahaan.create'
                                ],
                                [
                                    'title' => 'Registrasi Mesin',
                                    'desc' => 'Daftarkan aset mesin produksi baru',
                                    'route' => 'master.mesin.create',
                                    'icon' => 'M12 4v16m8-8H4',
                                    'color' => 'indigo',
                                    'can' => 'master.mesin.create'
                                ],
                                [
                                    'title' => 'Input Manpower',
                                    'desc' => 'Tambah tim atau karyawan baru',
                                    'route' => 'master.manpower.create',
                                    'icon' => 'M12 4v16m8-8H4',
                                    'color' => 'violet',
                                    'can' => 'master.manpower.create'
                                ],
                                [
                                    'title' => 'Daftar Part Baru',
                                    'desc' => 'Input spek dan part number baru',
                                    'route' => 'submaster.part.create',
                                    'icon' => 'M12 4v16m8-8H4',
                                    'color' => 'sky',
                                    'can' => 'submaster.part.create'
                                ],
                                [
                                    'title' => 'Plant Gate Setup',
                                    'desc' => 'Konfigurasi gerbang kedatangan',
                                    'route' => 'master.plantgate.index',
                                    'icon' => 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4 m0 0l4 4m-4-4',
                                    'color' => 'slate',
                                    'can' => 'master.plantgate.view'
                                ],
                                [
                                    'title' => 'Asset Kendaraan',
                                    'desc' => 'Kelola armada logistik MWT',
                                    'route' => 'master.kendaraan.index',
                                    'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1',
                                    'color' => 'slate',
                                    'can' => 'master.kendaraan.view'
                                ],
                            ];
                        @endphp

                        @foreach($ops as $op)
                            @if(userCan($op['can']))
                            <a href="{{ route($op['route']) }}" class="group p-4 bg-white border border-gray-100 rounded-xl hover:bg-{{ $op['color'] }}-50 hover:border-{{ $op['color'] }}-400 transition-all duration-200 flex flex-col items-center justify-center text-center">
                                <div class="w-10 h-10 rounded-lg bg-{{ $op['color'] }}-100 text-{{ $op['color'] }}-600 flex items-center justify-center mb-2.5 group-hover:bg-{{ $op['color'] }}-600 group-hover:text-white group-hover:shadow-lg transition-all">
                                    <svg class="w-5.5 h-5.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $op['icon'] }}" />
                                    </svg>
                                </div>
                                <h4 class="text-[11px] font-black text-gray-900 group-hover:text-{{ $op['color'] }}-700 transition-colors uppercase tracking-tight leading-none">{{ $op['title'] }}</h4>
                                <p class="text-[9px] text-gray-400 mt-1.5 font-bold leading-tight">{{ $op['desc'] }}</p>
                            </a>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Column --}}
        <div class="space-y-3 flex flex-col min-h-0">
            {{-- User Profile Card --}}
            <div class="bg-blue-900 rounded-xl p-6 text-white shadow-sm relative overflow-hidden shrink-0">
                <div class="relative z-10">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-xl font-black border-2 border-blue-400/30">
                            {{ substr(session('user_name', 'U'), 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-white leading-tight uppercase tracking-tight">{{ session('user_name', 'User') }}</h3>
                            <p class="text-[8px] text-blue-300 font-bold uppercase tracking-[0.2em] mt-1">
                                {{ session('is_superadmin') ? 'Master System Admin' : 'Master Data Analyst' }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-blue-800 hover:bg-red-600 text-indigo-100 hover:text-white rounded-lg border border-blue-700 hover:border-red-600 transition-all text-[10px] font-black uppercase tracking-[0.2em]">
                            End Session
                        </button>
                    </form>
                </div>
            </div>

            {{-- Master Infrastructure Widget --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 pb-8 flex-1 flex flex-col min-h-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between mb-4 flex-shrink-0">
                    <h4 class="text-[9px] font-black text-gray-900 uppercase tracking-widest flex items-center gap-2">
                        <span class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></span>
                        Service Status
                    </h4>
                    <span class="text-[8px] font-bold text-blue-500 border border-blue-100 px-1.5 py-0.5 rounded uppercase">Active</span>
                </div>
                
                <div class="space-y-4 flex-1 overflow-hidden">
                    {{-- Status 1 --}}
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-tight">Main Database</span>
                            <span class="text-[9px] font-black text-emerald-600">STABLE</span>
                        </div>
                        <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-blue-500 h-full w-[96%]"></div>
                        </div>
                    </div>

                    {{-- Status 2 --}}
                    <div>
                        <div class="flex justify-between items-center mb-1.5">
                            <span class="text-[9px] font-black text-gray-400 uppercase tracking-tight">API Internal</span>
                            <span class="text-[9px] font-black text-indigo-600">LISTENING</span>
                        </div>
                        <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                            <div class="bg-indigo-500 h-full w-[88%]"></div>
                        </div>
                    </div>

                    {{-- Activity Log --}}
                    <div class="mt-4 pt-4 border-t border-gray-50 space-y-2 overflow-y-auto">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-1 bg-blue-500 rounded-full"></div>
                            <span class="text-[8px] font-bold text-gray-500 uppercase">Master Cache Cleared</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-1 bg-indigo-500 rounded-full"></div>
                            <span class="text-[8px] font-bold text-gray-500 uppercase">Schema Audit OK</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-1 bg-emerald-500 rounded-full"></div>
                            <span class="text-[8px] font-bold text-gray-500 uppercase">Index Optimization</span>
                        </div>
                    </div>
                </div>

                <div class="mt-auto pt-3 border-t border-gray-50 flex-shrink-0">
                    <p class="text-[8px] font-bold text-gray-400 text-center tracking-[0.3em]">VERSION 2.0.4</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
