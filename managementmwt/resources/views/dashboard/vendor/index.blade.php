@extends('layout.app')

@section('title', 'Control Supplier Dashboard')

@section('content')
<div class="h-[calc(100vh-112px)] bg-gray-50 flex flex-col overflow-hidden">
    {{-- Top Navigation & Filters --}}
    <div class="bg-white border-b border-gray-200 px-4 py-2 flex flex-col md:flex-row justify-between items-center gap-4 flex-shrink-0">
        <div>
            <h1 class="text-lg font-bold text-gray-900 flex items-center gap-2 leading-none">
                <span class="w-1.5 h-5 bg-emerald-600 rounded-full"></span>
                Control Supplier Performance
            </h1>
            <p class="text-[10px] text-gray-500 mt-1 uppercase font-bold tracking-wider">{{ $formattedDate }} • Mode: {{ ucfirst($viewMode) }}</p>
        </div>
        
        <div class="flex items-center gap-3">
            {{-- Mode Toggle --}}
            <div class="flex bg-gray-100 p-0.5 rounded-lg border border-gray-200">
                <button onclick="loadControlDashboard('{{ $dateStr }}', '{{ $category }}', 'daily')" 
                    class="px-3 py-1 text-[10px] font-bold rounded-md transition-all {{ $viewMode === 'daily' ? 'bg-white shadow-sm text-emerald-700' : 'text-gray-500 hover:text-gray-700' }}">
                    DAILY
                </button>
                <button onclick="loadControlDashboard('{{ $dateStr }}', '{{ $category }}', 'monthly')" 
                    class="px-3 py-1 text-[10px] font-bold rounded-md transition-all {{ $viewMode === 'monthly' ? 'bg-white shadow-sm text-emerald-700' : 'text-gray-500 hover:text-gray-700' }}">
                    MONTHLY
                </button>
            </div>

            {{-- Category & Date --}}
            <div class="flex items-center gap-2">
                <select id="catSelect" onchange="updateFilters()" class="text-[10px] font-bold border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 px-2 py-1 capitalize">
                    <option value="all" {{ $category == 'all' ? 'selected' : '' }}>All Components</option>
                    <option value="material" {{ $category == 'material' ? 'selected' : '' }}>Material</option>
                    <option value="subpart" {{ $category == 'subpart' ? 'selected' : '' }}>Subpart</option>
                    <option value="layer" {{ $category == 'layer' ? 'selected' : '' }}>Layer</option>
                    <option value="box" {{ $category == 'box' ? 'selected' : '' }}>Box</option>
                    <option value="polybag" {{ $category == 'polybag' ? 'selected' : '' }}>Polybag</option>
                    <option value="rempart" {{ $category == 'rempart' ? 'selected' : '' }}>Rempart</option>
                </select>
                <input type="date" id="dateInput" value="{{ $dateStr }}" onchange="updateFilters()" 
                    class="text-[10px] font-bold border-gray-200 rounded-lg focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50 px-2 py-1">
            </div>
        </div>
    </div>

    {{-- Main Dashboard Grid --}}
    <div class="flex-1 p-3 grid grid-cols-1 lg:grid-cols-12 gap-3 overflow-hidden">
        
        {{-- Left Column: Summary & Trend (7/12) --}}
        <div class="lg:col-span-7 flex flex-col gap-3 overflow-hidden">
            {{-- Quick KPI Row --}}
            @php
                $totalPO = $items->sum('delivery_po');
                $totalAct = $items->sum('delivery_act');
                $overallSR = $totalPO > 0 ? round(($totalAct / $totalPO) * 100, 1) : 0;
            @endphp
            <div class="grid grid-cols-4 gap-2">
                <div class="bg-white border border-gray-200 p-3 rounded-xl shadow-sm">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Plan</p>
                    <h3 class="text-lg font-black text-gray-900 mt-0.5">{{ number_format($totalPO) }}</h3>
                </div>
                <div class="bg-white border border-gray-200 p-3 rounded-xl shadow-sm">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Total Arrival</p>
                    <h3 class="text-lg font-black text-emerald-600 mt-0.5">{{ number_format($totalAct) }}</h3>
                </div>
                <div class="bg-white border border-gray-200 p-3 rounded-xl shadow-sm">
                    <p class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Balance</p>
                    <h3 class="text-lg font-black {{ ($totalAct - $totalPO) < 0 ? 'text-red-600' : 'text-green-600' }} mt-0.5">{{ number_format($totalAct - $totalPO) }}</h3>
                </div>
                <div class="bg-white border-2 border-emerald-500 p-3 rounded-xl shadow-sm relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-1 opacity-10">
                        <svg class="w-10 h-10 text-emerald-900" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/></svg>
                    </div>
                    <p class="text-[9px] font-black text-emerald-700 uppercase tracking-widest relative z-10">Service Rate</p>
                    <h3 class="text-xl font-black text-emerald-800 mt-0.5 relative z-10">{{ $overallSR }}%</h3>
                </div>
            </div>

            {{-- Trend Chart --}}
            <div class="flex-1 bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col">
                <h3 class="text-[10px] font-black text-gray-800 uppercase tracking-wider mb-2 border-b border-gray-50 pb-1 flex items-center gap-2">
                    <svg class="w-3 h-3 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                    Delivery Achievement Trend (Last 30 Days)
                </h3>
                <div class="flex-1 relative">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Right Column: Category & Vendor Ranking (5/12) --}}
        <div class="lg:col-span-5 flex flex-col gap-3 overflow-hidden">
            
            {{-- Category Achievement --}}
            <div class="h-1/3 bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex flex-col">
                <h3 class="text-[10px] font-black text-gray-800 uppercase tracking-wider mb-1 border-b border-gray-50 pb-1">Category Performance</h3>
                <div class="flex-1 relative">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            {{-- Vendor SR Rank (The "Resume" requested by user) --}}
            <div class="flex-1 bg-white border border-gray-200 rounded-xl shadow-sm flex flex-col overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/30 flex justify-between items-center">
                    <h3 class="text-xs font-black text-gray-800 uppercase tracking-wider">Vendor Service Rate Resume</h3>
                    <span class="text-[10px] font-bold text-emerald-600">Active Vendors: {{ count($supplierStats) }}</span>
                </div>
                <div class="flex-1 overflow-y-auto p-4 custom-scrollbar">
                    <div class="space-y-4">
                        @foreach($supplierStats->sortByDesc('sr') as $sup)
                        <div class="group">
                            <div class="flex justify-between items-center mb-1.5 px-1">
                                <span class="text-xs font-bold text-gray-700 truncate max-w-[200px]" title="{{ $sup['name'] }}">{{ $sup['name'] }}</span>
                                <span class="text-xs font-black {{ $sup['sr'] < 90 ? 'text-red-600' : 'text-emerald-700' }}">{{ $sup['sr'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden border border-gray-50">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out {{ $sup['sr'] < 90 ? 'bg-red-500' : 'bg-emerald-500' }}" 
                                     style="width: 0%;" 
                                     data-width="{{ min($sup['sr'], 100) }}%">
                                </div>
                            </div>
                            <div class="flex justify-between mt-1 px-1">
                                <p class="text-[9px] font-bold text-gray-400">Total Arrived: <span class="text-gray-600">{{ number_format($sup['act']) }}</span></p>
                                <p class="text-[9px] font-bold {{ $sup['sr'] >= 100 ? 'text-emerald-500' : 'text-amber-500' }} uppercase tracking-tighter">
                                    {{ $sup['sr'] >= 100 ? 'Fully Fulfilled' : 'In Progress' }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 20px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #d1d5db; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    function loadControlDashboard(date, cat, mode) {
        window.location.href = `{{ route('dashboard.vendor.index') }}?date=${date}&category=${cat}&view_mode=${mode}`;
    }

    function updateFilters() {
        const date = document.getElementById('dateInput').value;
        const cat = document.getElementById('catSelect').value;
        const mode = '{{ $viewMode }}';
        loadControlDashboard(date, cat, mode);
    }

    document.addEventListener('DOMContentLoaded', function() {
        // --- Animate Progress Bars ---
        setTimeout(() => {
            document.querySelectorAll('[data-width]').forEach(bar => {
                bar.style.width = bar.getAttribute('data-width');
            });
        }, 300);

        // --- Charts Initialization ---
        if (typeof Chart === 'undefined') return;

        const trendData = @json($trendData ?? []);
        const categoryData = @json($categoryStats ?? []);

        // 1. Trend Chart
        new Chart(document.getElementById('trendChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: trendData.map(d => d.month),
                datasets: [
                    {
                        label: 'Service Rate (%)',
                        type: 'line',
                        data: trendData.map(d => d.sr),
                        borderColor: '#10b981',
                        borderWidth: 2,
                        pointRadius: 2,
                        yAxisID: 'ySR',
                        order: 1
                    },
                    {
                        label: 'Actual Volume',
                        data: trendData.map(d => d.act),
                        backgroundColor: '#334155', // Slate 700
                        borderRadius: 4,
                        yAxisID: 'yVol',
                        order: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    ySR: {
                        beginAtZero: true,
                        max: 120,
                        position: 'right',
                        ticks: { font: { size: 9 }, color: '#10b981' },
                        grid: { display: false }
                    },
                    yVol: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: { font: { size: 9 }, color: '#64748b' },
                        grid: { color: '#f1f5f9' }
                    },
                    x: { ticks: { font: { size: 9 }, color: '#64748b' }, grid: { display: false } }
                }
            }
        });

        // 2. Category Chart
        new Chart(document.getElementById('categoryChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: categoryData.map(d => d.category),
                datasets: [{
                    data: categoryData.map(d => d.sr),
                    backgroundColor: categoryData.map(d => d.sr < 90 ? '#ef4444' : '#10b981'),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { 
                        beginAtZero: true, 
                        max: 100, 
                        ticks: { font: { size: 9 }, color: '#94a3b8' },
                        grid: { display: false }
                    },
                    y: { 
                        ticks: { font: { size: 9, weight: 'bold' }, color: '#334155' },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>
@endsection
