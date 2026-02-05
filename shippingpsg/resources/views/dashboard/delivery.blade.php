@extends('layout.app')

@push('styles')
<style>
    .glass-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
    }
    .metric-card {
        border-radius: 10px;
        padding: 1.25rem;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .metric-card-icon {
        position: absolute;
        right: 15px;
        bottom: 15px;
        font-size: 2.5rem;
        opacity: 0.2;
    }
    .metric-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
    }
    .metric-value {
        font-size: 1.8rem;
        font-weight: 800;
        line-height: 1.2;
    }
    .metric-sub {
        font-size: 0.65rem;
        opacity: 0.9;
    }
    .status-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
        display: inline-block;
        min-width: 60px;
        text-align: center;
    }
</style>
@endpush

@section('content')
<div class="px-6 py-6 space-y-6 bg-slate-50 min-h-screen font-sans">
    
    {{-- Header & Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-2">
        <div class="flex items-center gap-3">
             <i class="fas fa-truck-loading text-slate-800 text-lg"></i>
            <h1 class="text-xl font-bold text-slate-800">Delivery Performance</h1>
        </div>
        
        <form action="" method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Part ID/Name/Customer..." 
                    class="pl-9 pr-4 py-2 w-64 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
            </div>

            <select name="month" class="py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                <option value="{{ date('Y-m') }}">Bulan Ini</option>
                <option value="{{ date('Y-m', strtotime('-1 month')) }}">Bulan Lalu</option>
            </select>

             <select name="customer" class="py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                <option value="">Semua Customer</option>
                {{-- Options populated by backend --}}
            </select>
            
            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors">
                <i class="fas fa-file-excel"></i> Export
            </button>
        </form>
    </div>

    {{-- Info Bar --}}
    <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 flex items-center gap-2 text-sm text-blue-800 mb-6">
        <i class="fas fa-info-circle"></i>
        <span class="font-medium">Periode: {{ now()->startOfMonth()->format('d F Y') }} - {{ now()->format('d F Y') }}</span>
        <span class="text-blue-300">|</span>
        <span class="font-medium">Customer: Semua</span>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        {{-- Card 1: Purchase Order --}}
        <div class="metric-card bg-blue-600">
            <div class="metric-title">PURCHASE ORDER</div>
            <div class="metric-value">{{ number_format(4880358) }}</div>
             <i class="fas fa-file-invoice-dollar metric-card-icon"></i>
        </div>

        {{-- Card 2: Delivery Plan --}}
        <div class="metric-card bg-emerald-500">
             <div class="metric-title">DELIVERY PLAN</div>
            <div class="metric-value">{{ number_format(309324) }}</div>
             <i class="fas fa-calendar-check metric-card-icon"></i>
        </div>
        
        {{-- Card 3: Aktual Delivery --}}
        <div class="metric-card bg-yellow-600">
             <div class="metric-title">AKTUAL DELIVERY</div>
            <div class="metric-value">{{ number_format(73245) }}</div>
             <i class="fas fa-truck metric-card-icon"></i>
        </div>

        {{-- Card 4: Pending Delivery --}}
        <div class="metric-card bg-red-600">
             <div class="metric-title">PENDING DELIVERY</div>
            <div class="metric-value">{{ number_format(236079) }}</div>
             <i class="fas fa-clock metric-card-icon"></i>
        </div>

        {{-- Card 5: Service Rate By PO --}}
        <div class="metric-card bg-purple-600">
             <div class="metric-title">SERVICE RATE BY PO</div>
            <div class="metric-value">1.5%</div>
            <div class="metric-sub text-purple-200">↓ 98.5% dari target</div>
             <i class="fas fa-chart-line metric-card-icon"></i>
        </div>

        {{-- Card 6: Service Rate By Plan --}}
        <div class="metric-card bg-indigo-600">
             <div class="metric-title">SERVICE RATE BY DAILY PLAN</div>
            <div class="metric-value">23.7%</div>
             <div class="metric-sub text-indigo-200">↓ 76.3% dari target</div>
             <i class="fas fa-chart-line metric-card-icon"></i>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Monthly Trend --}}
        <div class="glass-card p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">
                    <i class="fas fa-chart-line text-slate-400"></i> Monthly Service Rate Trend
                </h3>
                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded">by PO</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartMonthTrend"></canvas>
            </div>
        </div>

        {{-- Daily Trend --}}
        <div class="glass-card p-6">
             <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-slate-700 text-sm flex items-center gap-2">
                    <i class="fas fa-chart-bar text-slate-400"></i> Daily Service Rate Trend
                </h3>
                <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded">by Daily Schedule Only</span>
            </div>
            <div class="relative h-64 w-full">
                <canvas id="chartDailyTrend"></canvas>
            </div>
        </div>
    </div>

    {{-- Service Filter Bar --}}
    <div class="glass-card px-4 py-3 flex flex-col md:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-2 w-full md:w-auto">
            <i class="fas fa-filter text-slate-400 text-xs"></i>
            <span class="text-xs font-bold text-slate-600">Service Rate by:</span>
            <select class="text-xs font-semibold text-slate-700 bg-slate-50 border-none rounded focus:ring-0 cursor-pointer py-1">
                <option>Purchase Order (PO)</option>
                <option>Daily Plan</option>
            </select>
        </div>
        
        <div class="flex bg-white border border-slate-200 rounded-md overflow-hidden text-[10px] font-bold w-full md:w-auto">
            <button class="bg-blue-900 text-white px-4 py-2">ALL DELIVERY</button>
            <button class="bg-white text-slate-500 hover:bg-slate-50 px-4 py-2 border-l border-slate-100">POOR (<90%)</button>
            <button class="bg-white text-slate-500 hover:bg-slate-50 px-4 py-2 border-l border-slate-100">GOOD (90-99%)</button>
            <button class="bg-white text-slate-500 hover:bg-slate-50 px-4 py-2 border-l border-slate-100">EXCELLENT (100%)</button>
            <button class="bg-white text-slate-500 hover:bg-slate-50 px-4 py-2 border-l border-slate-100">OVER (>100%)</button>
        </div>
    </div>

    {{-- Detailed Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] text-slate-500 font-extrabold uppercase tracking-widest">
                        <th class="px-4 py-3">Part Name</th>
                        <th class="px-4 py-3">Part Number</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-center">Customer</th>
                        <th class="px-4 py-3 text-right">PO</th>
                        <th class="px-4 py-3 text-right">DI</th>
                        <th class="px-4 py-3 text-right text-blue-600">Actual Del...</th>
                        <th class="px-4 py-3 text-right">Service Rate</th>
                        <th class="px-4 py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    {{-- Mock Data Rows matching image --}}
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-800">HOUSING SUB ASSY</td>
                        <td class="px-4 py-3">90-AD01BSY</td>
                        <td class="px-4 py-3">K0JA</td>
                        <td class="px-4 py-3 text-center">AJI</td>
                        <td class="px-4 py-3 text-right">9,814</td>
                        <td class="px-4 py-3 text-right">0</td>
                        <td class="px-4 py-3 text-right text-blue-600 font-bold">504</td>
                        <td class="px-4 py-3 text-right">5.1%</td>
                        <td class="px-4 py-3 text-center"><span class="status-badge bg-red-600">POOR</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-800">HOUSING RH</td>
                        <td class="px-4 py-3">11-0G43BV1</td>
                        <td class="px-4 py-3">D06A</td>
                        <td class="px-4 py-3 text-center">AJI</td>
                        <td class="px-4 py-3 text-right">140</td>
                        <td class="px-4 py-3 text-right">0</td>
                        <td class="px-4 py-3 text-right text-blue-600 font-bold">10</td>
                        <td class="px-4 py-3 text-right">7.1%</td>
                        <td class="px-4 py-3 text-center"><span class="status-badge bg-red-600">POOR</span></td>
                    </tr>
                     <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-800">HOUSING LH</td>
                        <td class="px-4 py-3">11-0G44BV1</td>
                        <td class="px-4 py-3">D06A</td>
                        <td class="px-4 py-3 text-center">AJI</td>
                        <td class="px-4 py-3 text-right">140</td>
                        <td class="px-4 py-3 text-right">0</td>
                        <td class="px-4 py-3 text-right text-blue-600 font-bold">0</td>
                        <td class="px-4 py-3 text-right">0.0%</td>
                        <td class="px-4 py-3 text-center"><span class="status-badge bg-red-600">POOR</span></td>
                    </tr>
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3 font-bold text-slate-800">REFLECTOR RH</td>
                        <td class="px-4 py-3">11-0G43RV1I</td>
                        <td class="px-4 py-3">D06A</td>
                        <td class="px-4 py-3 text-center">AJI</td>
                        <td class="px-4 py-3 text-right">220</td>
                        <td class="px-4 py-3 text-right">0</td>
                        <td class="px-4 py-3 text-right text-blue-600 font-bold">0</td>
                        <td class="px-4 py-3 text-right">0.0%</td>
                        <td class="px-4 py-3 text-center"><span class="status-badge bg-red-600">POOR</span></td>
                    </tr>
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
    
    // --- Chart 1: Monthly Trend ---
    new Chart(document.getElementById('chartMonthTrend'), {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
            datasets: [
                {
                    label: 'Target (100%)',
                    data: Array(12).fill(100),
                    borderColor: '#10b981', // Emerald 500
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0,
                    fill: false
                },
                {
                    label: 'Service Rate (by PO)',
                    data: [2, null, null, null, null, null, null, null, null, null, null, null], // Mock data point at Jan
                    borderColor: '#3b82f6', // Blue 500
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderWidth: 2,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 4,
                    fill: false
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
                    max: 110,
                    ticks: { callback: function(value) { return value + "%" }, stepSize: 20, font: {size: 10} },
                    grid: { color: '#f1f5f9' }
                },
                x: { grid: { display: false }, ticks: { font: {size: 10} } }
            }
        }
    });

    // --- Chart 2: Daily Trend ---
    // Generate days for Jan (31)
    const days = Array.from({length: 31}, (_, i) => i + 1);
    const dailyData = Array(31).fill(null);
    dailyData[5] = 100; dailyData[6] = 100; dailyData[7] = 100; dailyData[8] = 100; // Mock full bars
    dailyData[11] = 5; // Mock small bar (Sun)

    new Chart(document.getElementById('chartDailyTrend'), {
        type: 'bar',
        data: {
            labels: days,
            datasets: [
                {
                    type: 'line',
                    label: 'Target (100%)',
                    data: Array(31).fill(100),
                    borderColor: '#10b981',
                    borderWidth: 2,
                    borderDash: [5, 5],
                    pointRadius: 0
                },
                {
                    label: 'Service Rate',
                    data: dailyData,
                    backgroundColor: function(context) {
                        const val = context.raw;
                        if(val >= 90) return '#34d399'; // Emerald
                        if(val < 90) return '#f87171'; // Red
                        return '#cbd5e1';
                    },
                    borderRadius: 4,
                    barThickness: 6
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
                    max: 110,
                    ticks: { callback: function(value) { return value + "%" }, stepSize: 20, font: {size: 10} },
                    grid: { color: '#f1f5f9' }
                },
                x: { 
                    grid: { display: false }, 
                    ticks: { font: {size: 9}, maxRotation: 0, autoSkip: false },
                    title: { display: true, text: 'Tanggal (Januari 2026)', font: {size: 10} }
                }
            }
        }
    });

</script>
@endpush
