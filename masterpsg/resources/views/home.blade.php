@extends('layout.app')

@section('title', 'Master Command Center')

@section('content')
<div class="h-[calc(100vh-110px)] flex flex-col overflow-hidden px-4 pb-4 pt-1">
    {{-- Error Message --}}
    @if(session('error'))
    <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center gap-3 shrink-0">
        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-semibold">{{ session('error') }}</span>
    </div>
    @endif

    {{-- 1. HEADER: Global Search Center --}}
    <div class="mb-2 shrink-0" 
         x-data="{
            query: '',
            results: [],
            loading: false,
            showResults: false,
            async search() {
                if (this.query.length < 2) { 
                    this.results = []; 
                    this.showResults = false; 
                    return; 
                }
                this.loading = true;
                this.showResults = true;
                try {
                    let response = await fetch('{{ route('global.search') }}?q=' + this.query);
                    this.results = await response.json();
                } catch (error) {
                    console.error('Search error:', error);
                } finally {
                    this.loading = false;
                }
            },
            init() {
                this.$watch('query', () => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => this.search(), 300);
                });
            }
         }"
         @click.away="showResults = false">
         
        <div class="relative w-full">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <input type="text" 
                   x-model="query"
                   @focus="showResults = true"
                   class="block w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 shadow-sm transition-all" 
                   placeholder="Search Master Data (e.g. 'Astra Honda', 'Mesin Injection', 'Budi Santoso')..."
                   autocomplete="off">
                   
            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                <div x-show="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500 mr-2"></div>
                <kbd x-show="!loading" class="inline-flex items-center border border-gray-200 rounded px-2 text-xs font-sans font-medium text-gray-400">Ctrl K</kbd>
            </div>

            {{-- Search Results Dropdown --}}
            <div x-show="showResults && results.length > 0" 
                 x-transition
                 class="absolute mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-100 py-2 overflow-hidden z-50 max-h-[60vh] overflow-y-auto">
                
                <template x-for="(result, index) in results" :key="index">
                    <a :href="result.url" class="block px-4 py-3 hover:bg-gray-50 transition-colors flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800" x-text="result.title"></h4>
                            <p class="text-xs text-gray-400 flex items-center gap-1">
                                <span class="uppercase font-bold tracking-wider" x-text="result.category"></span>
                                <span>&bull;</span>
                                <span x-text="result.subtitle"></span>
                            </p>
                        </div>
                    </a>
                </template>
            </div>
        </div>
    </div>

    {{-- 2. TOP STATS: Compact KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-2 shrink-0">
        @php
            $kpis = [
                ['label' => 'Total Perusahaan', 'value' => \App\Models\MPerusahaan::count(), 'color' => 'blue', 'icon' => 'building'],
                ['label' => 'Total Mesin', 'value' => \App\Models\MMesin::count(), 'color' => 'indigo', 'icon' => 'cog'],
                ['label' => 'Total Manpower', 'value' => \App\Models\MManpower::count(), 'color' => 'violet', 'icon' => 'users'],
                ['label' => 'Total Part', 'value' => \App\Models\SMPart::count(), 'color' => 'sky', 'icon' => 'cube']
            ];
        @endphp

        @foreach($kpis as $kpi)
        <div class="relative bg-white rounded-xl border border-{{ $kpi['color'] }}-100 p-3 flex items-center justify-between shadow-sm overflow-hidden group hover:border-{{ $kpi['color'] }}-300 transition-all hover:shadow-md">
            {{-- Decorative Background --}}
            <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-{{ $kpi['color'] }}-50/50 to-transparent transform skew-x-12"></div>
            <div class="absolute -right-2 -bottom-2 text-{{ $kpi['color'] }}-100 transform rotate-12 opacity-20 group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                 <i class="fas fa-{{ $kpi['icon'] }} text-6xl"></i>
            </div>

            <div class="relative z-10">
                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400 group-hover:text-{{ $kpi['color'] }}-600 transition-colors">{{ $kpi['label'] }}</p>
                <p class="text-xl font-black text-gray-800 leading-none mt-1 group-hover:text-{{ $kpi['color'] }}-700 transition-colors">{{ number_format($kpi['value']) }}</p>
            </div>
            <div class="relative z-10 w-8 h-8 rounded-lg bg-{{ $kpi['color'] }}-50 text-{{ $kpi['color'] }}-600 flex items-center justify-center border border-{{ $kpi['color'] }}-100 shadow-sm group-hover:scale-110 transition-transform">
                <i class="fas fa-{{ $kpi['icon'] }} text-sm"></i>
            </div>
        </div>
        @endforeach
    </div>

    {{-- 3. MAIN CONTENT: Split Columns --}}
    <div class="flex-1 flex flex-col lg:flex-row gap-4 min-h-0 overflow-hidden">
        {{-- LEFT COLUMN (Dominant) --}}
        <div class="flex-1 flex flex-col gap-4 min-w-0 overflow-hidden">

            {{-- Live Activity Stream (Fixed Height) --}}
            <div class="flex-1 bg-white border border-gray-200 rounded-xl flex flex-col min-h-0 overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between shrink-0">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-600">Live Data Stream</h3>
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[9px] font-bold text-emerald-600">REALTIME</span>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0 z-10 text-[9px] font-bold text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-2">Time</th>
                                <th class="px-4 py-2">User</th>
                                <th class="px-4 py-2">Action</th>
                                <th class="px-4 py-2">Module</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-[10px]">
                            @php
                                $companies = \App\Models\MPerusahaan::latest('updated_at')->limit(5)->get()->map(function($item) {
                                    return [
                                        'time' => $item->updated_at->diffForHumans(),
                                        'timestamp' => $item->updated_at,
                                        'user' => 'System',
                                        'action' => 'Company Updated: ' . $item->nama_perusahaan,
                                        'module' => 'VENDOR',
                                        'color' => 'blue'
                                    ];
                                });

                                $parts = \App\Models\SMPart::latest('updated_at')->limit(5)->get()->map(function($item) {
                                    return [
                                        'time' => $item->updated_at->diffForHumans(),
                                        'timestamp' => $item->updated_at,
                                        'user' => 'System',
                                        'action' => 'Part Spec Updated: ' . $item->nomor_part,
                                        'module' => 'PART',
                                        'color' => 'sky'
                                    ];
                                });

                                $manpower = \App\Models\MManpower::latest('updated_at')->limit(5)->get()->map(function($item) {
                                    return [
                                        'time' => $item->updated_at->diffForHumans(),
                                        'timestamp' => $item->updated_at,
                                        'user' => 'HR',
                                        'action' => 'Personnel Updated: ' . $item->nama,
                                        'module' => 'MANPOWER',
                                        'color' => 'violet'
                                    ];
                                });

                                $activities = $companies->concat($parts)->concat($manpower)
                                    ->sortByDesc('timestamp')
                                    ->take(10);
                            @endphp

                            @forelse($activities as $act)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-400 font-mono text-[9px]">{{ $act['time'] }}</td>
                                <td class="px-4 py-2.5 font-bold text-gray-700">{{ $act['user'] }}</td>
                                <td class="px-4 py-2.5 text-gray-600 truncate max-w-[200px]" title="{{ $act['action'] }}">{{ $act['action'] }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded bg-{{ $act['color'] }}-50 text-{{ $act['color'] }}-600 font-bold text-[8px] border border-{{ $act['color'] }}-100 whitespace-nowrap">
                                        {{ $act['module'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                             <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-[10px] italic">
                                    No recent master data activities found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN (Panel) --}}
        <div class="w-full lg:w-72 flex flex-col gap-4 shrink-0">
            {{-- User Profile Card --}}
            <div class="bg-slate-900 rounded-xl p-5 text-white shadow-sm relative overflow-hidden shrink-0">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-slate-700 rounded-lg flex items-center justify-center text-lg font-black border border-slate-600">
                            {{ substr(session('user_name', 'U'), 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-white leading-tight uppercase tracking-tight truncate w-32">{{ session('user_name', 'User') }}</h3>
                            <p class="text-[8px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">
                                {{ session('is_superadmin') ? 'Super Administrator' : 'Master Operator' }}
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-slate-800 hover:bg-red-600 text-slate-300 hover:text-white rounded-lg border border-slate-700 hover:border-red-600 transition-all text-[10px] font-black uppercase tracking-wider">
                            Logout
                        </button>
                    </form>
                </div>
            </div>

            {{-- Data Summary Widget --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex-1 flex flex-col min-h-0 overflow-hidden shadow-sm">
                <div class="flex items-center justify-between mb-4 flex-shrink-0">
                    <h4 class="text-[10px] font-black text-gray-800 uppercase tracking-widest">Data Summary</h4>
                </div>
                
                <div class="space-y-3 flex-1 overflow-y-auto pr-1">
                    @php
                        $summary = [
                            ['label' => 'Companies', 'value' => \App\Models\MPerusahaan::count(), 'color' => 'blue'],
                            ['label' => 'Machines', 'value' => \App\Models\MMesin::count(), 'color' => 'indigo'],
                            ['label' => 'Manpower', 'value' => \App\Models\MManpower::count(), 'color' => 'violet'],
                            ['label' => 'Parts', 'value' => \App\Models\SMPart::count(), 'color' => 'sky'],
                            ['label' => 'Vehicles', 'value' => \App\Models\MKendaraan::count(), 'color' => 'cyan'],
                            ['label' => 'Plant Gates', 'value' => \App\Models\MPlantGate::count(), 'color' => 'teal'],
                        ];
                    @endphp

                    @foreach($summary as $item)
                    <div class="flex justify-between items-center p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <span class="text-[9px] font-bold text-gray-500 uppercase">{{ $item['label'] }}</span>
                        <span class="text-sm font-black text-{{ $item['color'] }}-600">{{ number_format($item['value']) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
