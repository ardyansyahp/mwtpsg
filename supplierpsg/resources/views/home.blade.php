@extends('layout.app')

@section('title', 'Supplier Command Center')

@section('content')
<div class="h-[calc(100vh-110px)] flex flex-col overflow-hidden px-4 pb-4 pt-1">
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
                this.$watch('query', (value) => {
                    if (value.length === 0) this.showResults = false;
                });
            }
         }">
        <div class="relative w-full z-50">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            
            <input type="text" 
                   x-model.debounce.300ms="query"
                   @input="search()"
                   @keydown.escape="showResults = false"
                   @click.outside="showResults = false"
                   class="block w-full pl-11 pr-4 py-3 bg-white border border-gray-200 rounded-xl text-sm placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 shadow-sm transition-all" 
                   placeholder="Search Supplier Data (e.g. 'PT. Maju Jaya', 'Resin PP', 'PO-2023-001')..."
                   autocomplete="off">
            
            {{-- Loading Indicator --}}
            <div x-show="loading" class="absolute inset-y-0 right-12 flex items-center">
                <svg class="animate-spin h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div class="absolute inset-y-0 right-0 pr-2 flex items-center">
                <kbd class="inline-flex items-center border border-gray-200 rounded px-2 text-xs font-sans font-medium text-gray-400">Ctrl K</kbd>
            </div>

            {{-- Search Results Dropdown --}}
            <div x-show="showResults && (results.length > 0 || query.length >= 2)"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute mt-2 w-full bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50 max-h-96 overflow-y-auto">
                 
                 <template x-if="results.length > 0">
                    <div>
                        <div class="bg-gray-50 px-4 py-2 text-[10px] font-bold text-gray-500 uppercase tracking-wider">
                            Top Matches
                        </div>
                        <ul class="divide-y divide-gray-50">
                            <template x-for="result in results">
                                <li>
                                    <a :href="result.url" class="block hover:bg-emerald-50/50 px-4 py-3 transition-colors">
                                        <div class="flex items-center">
                                            <div :class="`flex-shrink-0 h-8 w-8 rounded-lg bg-${result.icon === 'building' ? 'emerald' : (result.icon === 'cube' ? 'blue' : 'purple')}-100 text-${result.icon === 'building' ? 'emerald' : (result.icon === 'cube' ? 'blue' : 'purple')}-600 flex items-center justify-center`">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                     <path x-show="result.icon === 'building'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5"></path>
                                                     <path x-show="result.icon === 'cube'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                     <path x-show="result.icon === 'document'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900" x-text="result.title"></p>
                                                <div class="flex items-center gap-2">
                                                    <span class="text-xs text-emerald-600 font-bold bg-emerald-50 px-1.5 rounded" x-text="result.category"></span>
                                                    <span class="text-xs text-gray-500" x-text="result.subtitle"></span>
                                                </div>
                                            </div>
                                            <div class="ml-auto">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
                 </template>
                 
                 <div x-show="results.length === 0 && !loading" class="px-4 py-8 text-center text-gray-500">
                    <p class="text-sm">No results found for "<span x-text="query" class="font-bold text-gray-700"></span>"</p>
                    <p class="text-xs mt-1">Try a different keyword or supplier code.</p>
                 </div>
            </div>
        </div>
    </div>

    {{-- 2. TOP STATS: Compact KPI Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-2 shrink-0">
        @php
            $vendorCount = class_exists('\App\Models\MPerusahaan') ? \App\Models\MPerusahaan::count() : 0;
            $materialCount = class_exists('\App\Models\MBahanBaku') ? \App\Models\MBahanBaku::count() : 0;
            $receivingToday = class_exists('\App\Models\Receiving') ? \App\Models\Receiving::whereDate('tanggal_receiving', now())->count() : 0;
            $poCount = class_exists('\App\Models\TScheduleHeader') ? \App\Models\TScheduleHeader::whereMonth('created_at', now()->month)->count() : 0;

            $kpis = [
                ['label' => 'Total Vendor', 'value' => $vendorCount, 'color' => 'emerald', 'icon' => 'building', 'can' => 'controlsupplier.monitoring'],
                ['label' => 'Total Bahan Baku', 'value' => $materialCount, 'color' => 'blue', 'icon' => 'cube', 'can' => 'bahanbaku.receiving.view'],
                ['label' => 'Receiving Today', 'value' => $receivingToday, 'color' => 'amber', 'icon' => 'truck', 'can' => 'bahanbaku.receiving.view'],
                ['label' => 'PO This Month', 'value' => $poCount, 'color' => 'purple', 'icon' => 'document', 'can' => 'controlsupplier.monitoring']
            ];
        @endphp

        @foreach($kpis as $kpi)
            @if(function_exists('userCan') ? userCan($kpi['can']) : true)
            <div class="relative bg-white rounded-xl border border-{{ $kpi['color'] }}-100 p-3 flex items-center justify-between shadow-sm overflow-hidden group hover:border-{{ $kpi['color'] }}-300 transition-all hover:shadow-md">
                <div class="absolute right-0 top-0 h-full w-16 bg-gradient-to-l from-{{ $kpi['color'] }}-50/50 to-transparent transform skew-x-12"></div>
                <div class="absolute -right-2 -bottom-2 text-{{ $kpi['color'] }}-100 transform rotate-12 opacity-20 group-hover:scale-110 transition-transform duration-500 pointer-events-none">
                     @if($kpi['icon'] == 'building') <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" /></svg>
                     @elseif($kpi['icon'] == 'cube') <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M11 17a1 1 0 001.447.894l4-2A1 1 0 0017 15V9.236a1 1 0 00-1.447-.894l-4 2a1 1 0 00-.553.894V17zM15.211 6.276a1 1 0 000-1.788l-4.764-2.382a1 1 0 00-.894 0L4.789 4.488a1 1 0 000 1.788l4.764 2.382a1 1 0 00.894 0l4.764-2.382zM4.447 8.342A1 1 0 003 9.236V15a1 1 0 00.553.894l4 2A1 1 0 009 17v-5.764a1 1 0 00-.553-.894l-4-2z" /></svg>
                     @elseif($kpi['icon'] == 'truck') <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" /><path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1V5a1 1 0 00-1-1H3zM14 7a1 1 0 00-1 1v6.05A2.5 2.5 0 0115.95 16H17a1 1 0 001-1v-5a1 1 0 00-.293-.707l-2-2A1 1 0 0015 7h-1z" /></svg>
                     @elseif($kpi['icon'] == 'document') <svg class="w-16 h-16" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd" /></svg>
                     @endif
                </div>

                <div class="relative z-10">
                    <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400 group-hover:text-{{ $kpi['color'] }}-600 transition-colors">{{ $kpi['label'] }}</p>
                    <p class="text-xl font-black text-gray-800 leading-none mt-1 group-hover:text-{{ $kpi['color'] }}-700 transition-colors">{{ number_format($kpi['value']) }}</p>
                </div>
                <div class="relative z-10 w-8 h-8 rounded-lg bg-{{ $kpi['color'] }}-50 text-{{ $kpi['color'] }}-600 flex items-center justify-center border border-{{ $kpi['color'] }}-100 shadow-sm group-hover:scale-110 transition-transform">
                     @if($kpi['icon'] == 'building') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5" /></svg>
                     @elseif($kpi['icon'] == 'cube') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                     @elseif($kpi['icon'] == 'truck') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                     @elseif($kpi['icon'] == 'document') <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                     @endif
                </div>
            </div>
            @endif
        @endforeach
    </div>

    {{-- 3. MAIN CONTENT: Split Columns --}}
    <div class="flex-1 flex flex-col lg:flex-row gap-4 min-h-0 overflow-hidden">
        {{-- LEFT COLUMN (Dominant) --}}
        <div class="flex-1 flex flex-col gap-4 min-w-0 overflow-hidden">

            {{-- Live Activity Stream (Fixed Height) --}}
            <div class="flex-1 bg-white border border-gray-200 rounded-xl flex flex-col min-h-0 overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between shrink-0">
                    <h3 class="text-[10px] font-black uppercase tracking-widest text-gray-600">Supply Chain Stream</h3>
                    <div class="flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-[9px] font-bold text-emerald-600">LIVE UPDATES</span>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="bg-gray-50 sticky top-0 z-10 text-[9px] font-bold text-gray-400 uppercase tracking-wider">
                            <tr>
                                <th class="px-4 py-2">Time</th>
                                <th class="px-4 py-2">Activity</th>
                                <th class="px-4 py-2">Reference</th>
                                <th class="px-4 py-2">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 text-[10px]">
                            @php
                                // 1. Recent Receivings
                                $receivings = \App\Models\Receiving::with('supplier')
                                    ->latest('created_at')
                                    ->limit(5)
                                    ->get()
                                    ->map(function($item) {
                                        return [
                                            'time' => $item->created_at->diffForHumans(),
                                            'timestamp' => $item->created_at,
                                            'activity' => 'Goods Received',
                                            'ref' => $item->no_surat_jalan,
                                            'status' => 'RECEIVED',
                                            'color' => 'emerald'
                                        ];
                                    });

                                // 2. Recent POs (Schedules)
                                $pos = \App\Models\TScheduleHeader::with('supplier')
                                    ->latest('created_at')
                                    ->limit(5)
                                    ->get()
                                    ->map(function($item) {
                                        return [
                                            'time' => $item->created_at->diffForHumans(),
                                            'timestamp' => $item->created_at,
                                            'activity' => 'New PO Created',
                                            'ref' => substr($item->po_number, 0, 15),
                                            'status' => 'NEW',
                                            'color' => 'blue'
                                        ];
                                    });

                                // 3. Material Updates
                                $materials = \App\Models\MBahanBaku::latest('updated_at')
                                    ->limit(5)
                                    ->get()
                                    ->map(function($item) {
                                        return [
                                            'time' => $item->updated_at->diffForHumans(),
                                            'timestamp' => $item->updated_at,
                                            'activity' => 'Material Updated',
                                            'ref' => substr($item->nama_bahan_baku, 0, 15),
                                            'status' => 'UPDATED',
                                            'color' => 'amber'
                                        ];
                                    });

                                // Merge and Sort
                                $activities = $receivings->concat($pos)->concat($materials)
                                    ->sortByDesc('timestamp')
                                    ->take(10);
                            @endphp

                            @forelse($activities as $act)
                            <tr class="hover:bg-gray-50/80 transition-colors">
                                <td class="px-4 py-2.5 whitespace-nowrap text-gray-400 font-mono text-[9px]">{{ $act['time'] }}</td>
                                <td class="px-4 py-2.5 font-bold text-gray-700 truncate max-w-[150px]" title="{{ $act['activity'] }}">{{ $act['activity'] }}</td>
                                <td class="px-4 py-2.5 text-gray-600 font-mono truncate max-w-[100px]" title="{{ $act['ref'] }}">{{ $act['ref'] }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-1.5 py-0.5 rounded bg-{{ $act['color'] }}-50 text-{{ $act['color'] }}-600 font-bold text-[8px] border border-{{ $act['color'] }}-100 whitespace-nowrap">
                                        {{ $act['status'] }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-400 text-[10px] italic">
                                    No recent supply chain activities found.
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
            <div class="bg-emerald-950 rounded-xl p-5 text-white shadow-sm relative overflow-hidden shrink-0">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 bg-emerald-800 rounded-lg flex items-center justify-center text-lg font-black border border-emerald-700">
                            {{ substr(session('user_name', 'U'), 0, 1) }}
                        </div>
                        <div>
                            <h3 class="text-xs font-black text-white leading-tight uppercase tracking-tight truncate w-32">{{ session('user_name', 'Supplier User') }}</h3>
                            <p class="text-[8px] text-emerald-300 font-bold uppercase tracking-wider mt-0.5">
                                Vendor Control
                            </p>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-2 bg-emerald-900 hover:bg-emerald-800 text-emerald-200 hover:text-white rounded-lg border border-emerald-800 hover:border-emerald-700 transition-all text-[10px] font-black uppercase tracking-wider">
                            Logout
                        </button>
                    </form>
                </div>
                {{-- Decorative circles --}}
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-800 rounded-full blur-2xl opacity-20"></div>
            </div>

            {{-- Supply Chain Health Widget --}}
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex-1 flex flex-col min-h-0 overflow-hidden shadow-sm"
                 x-data="{
                    loading: true,
                    healthScore: 0,
                    metrics: [],
                    async runAnalysis() {
                        this.loading = true;
                        try {
                            const response = await fetch('{{ route('system.diagnostic') }}');
                            const data = await response.json();
                            this.healthScore = data.overall_health;
                            this.metrics = data.metrics.map(m => ({
                                label: m.label,
                                value: m.value,
                                color: m.color,
                                message: m.message
                            }));
                        } catch (e) {
                            console.error('Analysis error', e);
                        } finally {
                            this.loading = false;
                        }
                    }
                 }"
                 x-init="runAnalysis()">
                <div class="flex items-center justify-between mb-4 flex-shrink-0">
                    <h4 class="text-[10px] font-black text-gray-800 uppercase tracking-widest">Supply Health</h4>
                    <span class="text-[9px] font-black" 
                          :class="healthScore >= 90 ? 'text-emerald-500' : (healthScore >= 70 ? 'text-amber-500' : 'text-red-500')"
                          x-text="healthScore + '% OK'"></span>
                </div>
                
                <div class="space-y-4 flex-1 overflow-y-auto pr-1">
                    <template x-for="(metric, index) in metrics" :key="index">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-[9px] font-bold text-gray-500 uppercase" x-text="metric.label"></span>
                                <span class="text-[9px] font-bold" 
                                      :class="`text-${metric.color}-600`" 
                                      x-text="metric.value + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full w-full transition-all duration-1000 ease-out"
                                     :class="`bg-${metric.color}-500`"
                                     :style="`width: ${metric.value}%`"></div>
                            </div>
                            <p class="text-[8px] text-gray-400 mt-1 italic" x-text="metric.message"></p>
                        </div>
                    </template>
                    
                     {{-- Skeleton Loading --}}
                    <template x-if="loading">
                         <div class="space-y-4 animate-pulse">
                            <div class="h-8 bg-gray-100 rounded"></div>
                            <div class="h-8 bg-gray-100 rounded"></div>
                            <div class="h-8 bg-gray-100 rounded"></div>
                         </div>
                    </template>
                </div>

                <div class="mt-auto pt-3 border-t border-gray-50 flex-shrink-0 text-center">
                   <button @click="runAnalysis()" 
                           :disabled="loading"
                           class="text-[9px] text-emerald-600 hover:text-emerald-800 font-bold uppercase tracking-wider disabled:opacity-50 flex items-center justify-center gap-2 w-full">
                        <span x-show="loading" class="animate-spin h-3 w-3 border-2 border-emerald-600 border-t-transparent rounded-full"></span>
                        <span x-text="loading ? 'Analyzing Data...' : 'Analysis Report'"></span>
                   </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

