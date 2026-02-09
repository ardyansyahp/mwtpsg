@extends('layout.app')

@push('styles')
<style>
    .glass-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
    }
    .metric-card {
        padding: 1rem;
        border-radius: 6px;
        background: white;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .metric-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .metric-icon {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        font-size: 2.5rem;
        opacity: 0.1;
        color: #10b981; /* Default Green */
    }
    .trend-badge {
        font-size: 0.7rem;
        font-weight: 600;
        padding: 2px 6px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        gap: 2px;
    }
    /* Theme Colors for specific cards if needed, but keeping unified green/white per request */
    /* If user wants accents: */
    .border-l-4-green { border-left: 4px solid #10b981; }
    .border-l-4-blue { border-left: 4px solid #3b82f6; }
    .border-l-4-orange { border-left: 4px solid #f59e0b; }
    .border-l-4-red { border-left: 4px solid #ef4444; }
    .active-service-filter {
        background-color: #1e293b !important; /* slate-800 */
        color: white !important;
        font-weight: bold !important;
    }
    .active-service-filter span {
        background-color: white !important;
    }
    .active-service-filter i {
        color: white !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-4 bg-slate-50 min-h-screen font-sans">
    
    {{-- Header & Filters --}}
    <div class="glass-card p-3 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
             <div class="w-1.5 h-10 bg-emerald-600 rounded-full"></div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-truck-loading text-emerald-600"></i> Delivery Performance
                </h1>
                 <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wide">
                    {{ \Carbon\Carbon::parse(request('month', now()))->isoFormat('MMMM YYYY') }}
                </p>
            </div>
        </div>
        
        <form action="" method="GET" class="flex flex-wrap items-center gap-3">
             <div class="relative group">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Part ID/Name..." 
                    class="pl-9 pr-4 py-2 w-64 bg-slate-50 border border-slate-200 rounded-md text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
            </div>

            <select name="month" onchange="this.form.submit()" class="py-2 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-md text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer">
                 @for($i=0; $i<12; $i++)
                    @php $m = now()->subMonths($i); @endphp
                    <option value="{{ $m->format('Y-m') }}" {{ request('month', now()->format('Y-m')) == $m->format('Y-m') ? 'selected' : '' }}>
                        {{ $m->isoFormat('MMMM YYYY') }}
                    </option>
                @endfor
            </select>

             <select name="customer" onchange="this.form.submit()" class="py-2 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-md text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer w-48">
                <option value="">Semua Customer</option>
                 @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer') == $c->id ? 'selected' : '' }}>{{ $c->nama_perusahaan }}</option>
                @endforeach
            </select>
            
            <a href="{{ route('shipping.delivery.dashboard.export', request()->all()) }}" target="_blank"
                class="flex items-center justify-center bg-white border border-slate-200 text-slate-500 p-2.5 rounded-xl hover:bg-slate-50 transition-colors shadow-sm" 
                title="Export Data">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
            </a>
        </form>
    </div>

    {{-- Info Bar --}}
    <div class="bg-emerald-50 border border-emerald-100 rounded-md px-4 py-2.5 flex items-center gap-2 text-xs text-emerald-800">
        <i class="fas fa-info-circle"></i>
        <span class="font-bold">Periode:</span> {{ now()->startOfMonth()->format('d F Y') }} - {{ now()->format('d F Y') }}
        <span class="text-emerald-300 mx-2">|</span>
        <span class="font-bold">Customer:</span> {{ $customers->find(request('customer'))?->nama_perusahaan ?? 'Semua' }}
    </div>

    {{-- 6 Metric Cards Row --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Card 1: Purchase Order --}}
        <div class="metric-card border-l-4-blue">
            <div>
                <div class="metric-title text-blue-600">PURCHASE ORDER</div>
                <div class="metric-value">{{ number_format($totalPO) }}</div>
            </div>
             <i class="fas fa-file-invoice-dollar metric-icon text-blue-500"></i>
        </div>

        {{-- Card 2: Delivery Plan --}}
        <div class="metric-card border-l-4-green">
            <div>
                 <div class="metric-title text-emerald-600">DELIVERY PLAN (DI)</div>
                <div class="metric-value">{{ number_format($totalPlan) }}</div>
            </div>
             <i class="fas fa-calendar-check metric-icon text-emerald-500"></i>
        </div>
        
        {{-- Card 3: Aktual Delivery --}}
        <div class="metric-card border-l-4-orange">
            <div>
                 <div class="metric-title text-orange-600">AKTUAL DELIVERY</div>
                <div class="metric-value">{{ number_format($totalActual) }}</div>
            </div>
             <i class="fas fa-truck metric-icon text-orange-500"></i>
        </div>

        {{-- Card 4: Pending Delivery --}}
        <div class="metric-card border-l-4-red">
            <div>
                 <div class="metric-title text-red-600">PENDING DELIVERY</div>
                <div class="metric-value {{ $pendingDelivery > 0 ? 'text-red-600' : '' }}">{{ number_format($pendingDelivery) }}</div>
            </div>
             <i class="fas fa-clock metric-icon text-red-500"></i>
        </div>

        {{-- Card 5: Service Rate By PO --}}
        <div class="metric-card bg-slate-800 text-white border-none">
            <div>
                 <div class="metric-title text-slate-400">SERVICE RATE (PO)</div>
                 <div class="flex items-end gap-2">
                    <div class="metric-value text-white">{{ number_format($serviceRatePO, 1) }}%</div>
                    @if($serviceRatePO < 100)
                        <span class="text-[10px] text-red-400 mb-1.5"><i class="fas fa-arrow-down"></i></span>
                    @endif
                 </div>
                 <div class="w-full bg-slate-700 h-1.5 mt-2 rounded-full overflow-hidden">
                     <div class="bg-emerald-500 h-full" style="width: {{ min($serviceRatePO, 100) }}%"></div>
                 </div>
            </div>
        </div>

         {{-- Card 6: Service Rate By Plan --}}
        <div class="metric-card bg-emerald-600 text-white border-none">
             <div>
                 <div class="metric-title text-emerald-100">SERVICE RATE (DI)</div>
                <div class="flex items-end gap-2">
                    <div class="metric-value text-white">{{ number_format($serviceRatePlan, 1) }}%</div>
                </div>
                 <div class="w-full bg-emerald-800 h-1.5 mt-2 rounded-full overflow-hidden">
                     <div class="bg-white h-full" style="width: {{ min($serviceRatePlan, 100) }}%"></div>
                 </div>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Monthly Trend --}}
        <div class="glass-card p-6">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-700 text-xs uppercase flex items-center gap-2">
                    <i class="fas fa-chart-line text-emerald-500"></i> Monthly Service Rate Trend
                </h3>
                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded font-bold">BY PO</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartMonthTrend"></canvas>
            </div>
        </div>

        {{-- Daily Trend --}}
        <div class="glass-card p-6">
             <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-slate-700 text-xs uppercase flex items-center gap-2">
                    <i class="fas fa-chart-bar text-emerald-500"></i> Daily Service Rate Trend
                </h3>
                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded font-bold">BY DAILY SCHEDULE</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartDailyTrend"></canvas>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="glass-card px-4 py-3 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 w-full md:w-auto">
            <i class="fas fa-filter text-slate-400 text-xs"></i>
            <span class="text-xs font-bold text-slate-600 uppercase">Service Rate by:</span>
            <div class="flex bg-slate-100 rounded p-0.5">
                <button class="px-3 py-1 bg-white shadow-sm rounded-sm text-[10px] font-bold text-slate-700">Purchase Order (PO)</button>
            </div>
        </div>
        
        <div class="flex items-stretch gap-0 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm w-full md:w-auto">
            <button type="button" onclick="filterByServiceRate('ALL')" class="service-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[9px] font-bold text-gray-600 bg-white hover:bg-gray-50 transition-colors active-service-filter border-r border-gray-200">
                <i class="fas fa-layer-group"></i> ALL DELIVERY
            </button>
            <button type="button" onclick="filterByServiceRate('POOR')" class="service-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[9px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors border-r border-gray-200">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> POOR (&lt;90%)
            </button>
            <button type="button" onclick="filterByServiceRate('GOOD')" class="service-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[9px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors border-r border-gray-200">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> GOOD (90-99%)
            </button>
            <button type="button" onclick="filterByServiceRate('EXCELLENT')" class="service-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[9px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> EXCELLENT (100%)
            </button>
        </div>
    </div>

    {{-- Detailed Table --}}
    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] text-slate-500 font-extrabold uppercase tracking-widest">
                        <th class="px-6 py-4">Part Name</th>
                        <th class="px-6 py-4">Part Number</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4 text-right">PO (Month)</th>
                        <th class="px-6 py-4 text-right">DI (Plan)</th>
                        <th class="px-6 py-4 text-right text-emerald-600">Actual Del...</th>
                        <th class="px-6 py-4 text-right">Service Rate</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($performanceData as $item)
                        @php
                            $rate = $item['rate'];
                            $status = $rate >= 90 ? 'GOOD' : 'POOR';
                            $statusClass = $rate >= 90 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700';
                            if($rate >= 100) {
                                $status = 'EXCELLENT';
                                $statusClass = 'bg-blue-100 text-blue-700';
                            }
                        @endphp
                        <tr class="hover:bg-slate-50 transition-colors delivery-row" data-status="{{ $status }}">
                        <td class="px-6 py-3 font-bold text-slate-800">{{ $item['part_name'] }}</td>
                        <td class="px-6 py-3 font-mono text-slate-500">{{ $item['part_number'] }}</td>
                        <td class="px-6 py-3">{{ $item['model'] }}</td>
                        <td class="px-6 py-3 font-bold">{{ $item['customer_name'] }}</td>
                        <td class="px-6 py-3 text-right text-slate-500">{{ number_format($item['po']) }}</td>
                        <td class="px-6 py-3 text-right font-bold">{{ number_format($item['plan']) }}</td>
                        <td class="px-6 py-3 text-right text-emerald-600 font-extrabold">{{ number_format($item['actual']) }}</td>
                        <td class="px-6 py-3 text-right font-mono">{{ number_format($rate, 1) }}%</td>
                        <td class="px-6 py-3 text-center">
                            <span class="inline-block px-2 py-1 rounded text-[10px] font-bold {{ $statusClass }}">
                                {{ $status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                         <td colspan="9" class="px-6 py-8 text-center text-slate-400">Tidak ada data untuk periode ini</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';
    
    // --- Chart 1: Monthly Trend ---
    new Chart(document.getElementById('chartMonthTrend'), {
        type: 'line',
        data: {
            labels: @json($monthlyLabels),
            datasets: [
                {
                    label: 'Target (100%)',
                    data: Array(12).fill(100),
                    borderColor: '#10b981', // Emerald 500
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    fill: false,
                    order: 0
                },
                {
                    label: 'Service Rate',
                    data: @json($monthlyRate),
                    borderColor: '#3b82f6', // Blue 500
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3b82f6',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    fill: true,
                    tension: 0.3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, usePointStyle: true, font: {size: 10} } } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 120,
                    ticks: { callback: function(value) { return value + "%" }, stepSize: 20, font: {size: 10} },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false }, ticks: { font: {size: 10} } }
            }
        }
    });

    // --- Chart 2: Daily Trend ---
    new Chart(document.getElementById('chartDailyTrend'), {
        type: 'bar',
        data: {
            labels: @json($daysLabel),
            datasets: [
                {
                    type: 'line',
                    label: 'Target (100%)',
                    data: Array({{ count($daysLabel) }}).fill(100),
                    borderColor: '#10b981',
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    order: 0
                },
                {
                    label: 'Service Rate',
                    data: @json($chartRate),
                    backgroundColor: function(context) {
                        const val = context.raw;
                        if(val >= 90) return '#10b981'; // Emerald
                        if(val < 90) return '#ef4444'; // Red
                        return '#cbd5e1';
                    },
                    borderRadius: 2,
                    barPercentage: 0.7,
                    order: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 10, usePointStyle: true, font: {size: 10} } } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    max: 120,
                    ticks: { callback: function(value) { return value + "%" }, stepSize: 20, font: {size: 10} },
                    grid: { color: '#f1f5f9' }
                },
                x: { 
                    grid: { display: false }, 
                    ticks: { font: {size: 9}, maxRotation: 0, autoSkip: true, maxTicksLimit: 15 },
                    title: { display: true, text: 'Tanggal', font: {size: 10} }
                }
            }
        }
    });

    function filterByServiceRate(status) {
        // Remove active class from all filters
        document.querySelectorAll('.service-filter').forEach(btn => {
            btn.classList.remove('active-service-filter');
        });
        
        // Add active class to clicked filter
        event.target.closest('.service-filter').classList.add('active-service-filter');
        
        // Get all delivery rows
        const rows = document.querySelectorAll('.delivery-row');
        
        if (status === 'ALL') {
            // Show all rows
            rows.forEach(row => {
                row.style.display = '';
            });
        } else {
            // Filter by status
            rows.forEach(row => {
                if (row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    }
</script>
@endpush
