@extends('layout.app')

@push('styles')
<style>
    .glass-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
    }
    .metric-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
    }
    .metric-val {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .pill-badge {
        font-size: 0.6rem;
        padding: 2px 6px;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
    }
</style>
@endpush

@section('content')
<div class="space-y-6" x-data="dashboardManager()">
    {{-- Page Header --}}
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <h2 class="text-2xl font-black text-slate-900 tracking-tight">Executive Overview</h2>
            <p class="text-xs font-bold text-slate-500 mt-1 uppercase tracking-wide">
                {{ \Carbon\Carbon::now()->format('l, d F Y') }}
            </p>
        </div>

        <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-sm">
            <span class="text-[0.65rem] font-black text-slate-400 uppercase tracking-widest border-r border-slate-100 pr-2">Period</span>
            <input type="month" name="periode" value="{{ $periode }}" 
                   class="border-none focus:ring-0 text-xs font-bold text-slate-700 bg-transparent p-0 cursor-pointer"
                   onchange="this.form.submit()">
        </form>
    </div>

    {{-- 3 Main Pillars Summary --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- 1. Vendor / Supplier --}}
        <div class="glass-card p-4 flex flex-col justify-between h-full relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-5">
                <i class="fas fa-handshake text-6xl text-slate-800"></i>
            </div>
            
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-truck-loading text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 leading-tight">Vendor Perform.</h3>
                    <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Supply Chain</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- KPI Row --}}
                <div class="flex justify-between items-end">
                    <div>
                        <p class="metric-label">Service Rate</p>
                        <h4 class="text-2xl font-black {{ $supplierStats['service_rate'] >= 90 ? 'text-emerald-600' : 'text-red-500' }}">
                            {{ number_format($supplierStats['service_rate'], 1) }}%
                        </h4>
                    </div>
                    <div class="text-right">
                         <p class="metric-label">Active Suppliers</p>
                         <p class="font-bold text-slate-700">{{ number_format($supplierStats['active_suppliers']) }}</p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-emerald-500 h-full" style="width: {{ $supplierStats['service_rate'] }}%"></div>
                </div>

                {{-- Mini Stats --}}
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-50">
                    <div>
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Plan vs Act</p>
                        <p class="text-xs font-bold text-slate-700">
                            {{ number_format($supplierStats['total_act']) }} <span class="text-[0.6rem] text-slate-400">/ {{ number_format($supplierStats['total_plan']) }}</span>
                        </p>
                    </div>
                    <div class="text-right">
                         <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Top Vol. Supplier</p>
                         <p class="text-[0.65rem] font-bold text-emerald-600 truncate max-w-[100px] ml-auto" title="{{ $supplierStats['top_supplier'] }}">
                             {{ Str::limit($supplierStats['top_supplier'], 15) }}
                         </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Finished Goods / Stock --}}
        <div class="glass-card p-4 flex flex-col justify-between h-full relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-5">
                <i class="fas fa-cubes text-6xl text-slate-800"></i>
            </div>
            
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i class="fas fa-warehouse text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 leading-tight">Stock Status</h3>
                    <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Inventory Control</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- KPI Row --}}
                <div class="flex justify-between items-end">
                    <div>
                        <p class="metric-label">Total Inventory</p>
                        <h4 class="text-2xl font-black text-slate-800">
                            {{ number_format($fgStats['current_inventory']) }}
                        </h4>
                    </div>
                    <div class="text-right">
                         <p class="metric-label">Unique Items</p>
                         <p class="font-bold text-slate-700">{{ number_format($fgStats['total_items']) }}</p>
                    </div>
                </div>

                {{-- Status Pills --}}
                <div class="flex gap-2">
                    <span class="pill-badge bg-red-100 text-red-600 border border-red-200 flex-1 text-center">
                        <i class="fas fa-exclamation-circle mr-1"></i> {{ $fgStats['critical_items'] }} Critical
                    </span>
                    <span class="pill-badge bg-blue-100 text-blue-600 border border-blue-200 flex-1 text-center">
                        <i class="fas fa-layer-group mr-1"></i> {{ $fgStats['over_items'] }} Over
                    </span>
                </div>

                {{-- Flow Stats --}}
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-50">
                    <div>
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Inbound (Month)</p>
                        <p class="text-xs font-bold text-blue-600">+ {{ number_format($fgStats['total_in']) }}</p>
                    </div>
                    <div class="text-right">
                         <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Outbound (Month)</p>
                         <p class="text-xs font-bold text-orange-600">- {{ number_format($fgStats['total_out']) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Delivery / Customer --}}
        <div class="glass-card p-4 flex flex-col justify-between h-full relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-3 opacity-5">
                <i class="fas fa-shipping-fast text-6xl text-slate-800"></i>
            </div>
            
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded bg-orange-50 text-orange-600 flex items-center justify-center">
                    <i class="fas fa-truck text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-800 leading-tight">Delivery Perf.</h3>
                    <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Logistics</p>
                </div>
            </div>

            <div class="space-y-4">
                {{-- KPI Row --}}
                <div class="flex justify-between items-end">
                    <div>
                        <p class="metric-label">Service Rate</p>
                        <h4 class="text-2xl font-black {{ $deliveryStats['service_rate'] >= 90 ? 'text-emerald-600' : 'text-orange-500' }}">
                            {{ number_format($deliveryStats['service_rate'], 1) }}%
                        </h4>
                    </div>
                    <div class="text-right">
                         <p class="metric-label">Pending Del.</p>
                         <p class="font-bold {{ $deliveryStats['pending'] > 0 ? 'text-red-500' : 'text-emerald-500' }}">
                             {{ number_format($deliveryStats['pending']) }}
                         </p>
                    </div>
                </div>

                {{-- Progress Bar --}}
                <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                    <div class="bg-orange-500 h-full" style="width: {{ $deliveryStats['service_rate'] }}%"></div>
                </div>

                {{-- Mini Stats --}}
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-50">
                    <div>
                        <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Target Plan</p>
                        <p class="text-xs font-bold text-slate-700">{{ number_format($deliveryStats['total_plan']) }}</p>
                    </div>
                    <div class="text-right">
                         <p class="text-[0.6rem] text-slate-400 font-bold uppercase">Actual Deliv.</p>
                         <p class="text-xs font-bold text-orange-600">{{ number_format($deliveryStats['total_actual']) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts and Live Monitor --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Trend Chart (Span 2 Cols) --}}
        <div class="lg:col-span-2 glass-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800">Performance Trend (6 Months)</h3>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-[0.6rem] font-bold text-slate-500 uppercase">Vendor</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        <span class="text-[0.6rem] font-bold text-slate-500 uppercase">Delivery</span>
                    </div>
                </div>
            </div>
            <div id="trendChart" class="-ml-2 h-[220px]"></div>
        </div>

        {{-- Live Feed (Span 1 Col) --}}
        <div class="glass-card p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-bold text-slate-800">Live Pulse</h3>
                <span class="pill-badge bg-emerald-50 text-emerald-600 border border-emerald-100 animate-pulse">LIVE</span>
            </div>

            <div class="space-y-3">
                {{-- Live Item 1 --}}
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded border border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-truck-loading text-emerald-500 text-sm"></i>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-700 uppercase">Receiving Today</p>
                        </div>
                    </div>
                    <p class="font-black text-slate-800" x-text="liveStats.receiving_today">0</p>
                </div>
                {{-- Live Item 2 --}}
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded border border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-box-open text-blue-500 text-sm"></i>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-700 uppercase">FG Scan In</p>
                        </div>
                    </div>
                    <p class="font-black text-slate-800" x-text="liveStats.fg_in_today.toLocaleString()">0</p>
                </div>
                {{-- Live Item 3 --}}
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded border border-slate-100">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-shipping-fast text-orange-500 text-sm"></i>
                        <div>
                            <p class="text-[0.65rem] font-bold text-slate-700 uppercase">Delivered Today</p>
                        </div>
                    </div>
                    <p class="font-black text-slate-800" x-text="liveStats.delivery_today.toLocaleString()">0</p>
                </div>
            </div>
            
            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                 <p class="text-[0.6rem] text-slate-400">Data updates automatically every 5s</p>
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    function dashboardManager() {
        return {
            liveStats: {
                receiving_today: 0,
                fg_in_today: 0,
                fg_out_today: 0,
                delivery_today: 0
            },
            init() {
                this.initTrendChart();
                this.startPolling();
            },
            initTrendChart() {
                const options = {
                    series: [{
                        name: 'Vendor',
                        data: @json(collect($monthlyTrend)->pluck('supplier_sr'))
                    }, {
                        name: 'Delivery',
                        data: @json(collect($monthlyTrend)->pluck('delivery_sr'))
                    }],
                    chart: {
                        type: 'area',
                        height: 220,
                        toolbar: { show: false },
                        fontFamily: 'Inter, sans-serif',
                        zoom: { enabled: false }
                    },
                    colors: ['#10b981', '#f97316'],
                    dataLabels: { enabled: false },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: @json(collect($monthlyTrend)->pluck('month')),
                        axisBorder: { show: false },
                        axisTicks: { show: false },
                        labels: {
                            style: { fontSize: '10px', fontWeight: 600, colors: '#94a3b8' }
                        }
                    },
                    yaxis: {
                        max: 100,
                        labels: {
                            formatter: (val) => val.toFixed(0) + '%',
                            style: { fontSize: '10px', fontWeight: 600, colors: '#94a3b8' }
                        }
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.3,
                            opacityTo: 0.05,
                            stops: [0, 100]
                        }
                    },
                    grid: {
                        borderColor: '#f1f5f9',
                        strokeDashArray: 4,
                        padding: {
                            left: 10,
                            right: 0
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '11px',
                        fontWeight: 600,
                        itemMargin: { horizontal: 10, vertical: 0 }
                    },
                    tooltip: {
                        y: { formatter: function (val) { return val + "%" } }
                    }
                };

                const chart = new ApexCharts(document.querySelector("#trendChart"), options);
                chart.render();
            },
            async startPolling() {
                const fetchStats = async () => {
                    try {
                        const response = await fetch('{{ route('api.stats') }}');
                        const data = await response.json();
                        this.liveStats = data;
                    } catch (e) {
                        console.error('Polling failed', e);
                    }
                };

                fetchStats(); 
                setInterval(fetchStats, 5000); 
            }
        }
    }
</script>
@endpush
