@extends('layout.app')

@section('content')
<div class="min-h-screen bg-gray-100 p-4 fade-in">
    {{-- Header Section --}}
    <div class="bg-white rounded-lg shadow-sm p-4 mb-4">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Control Supplier - {{ $viewMode === 'daily' ? 'Harian' : 'Bulanan' }}</h1>
                <p class="text-gray-600">{{ $formattedDate }}</p>
            </div>
            <div class="flex items-center gap-4">
                {{-- View Mode Toggle --}}
                <div class="flex bg-gray-200 rounded-md p-1">
                    <button type="button" 
                        onclick="loadControlSupplierDashboard(document.querySelector('[name=date]').value, document.querySelector('[name=category]').value, 'daily')"
                        class="px-3 py-1.5 text-sm rounded {{ $viewMode === 'daily' ? 'bg-white shadow-sm font-semibold' : 'text-gray-600' }}">
                        Harian
                    </button>
                    <button type="button" 
                        onclick="loadControlSupplierDashboard(document.querySelector('[name=date]').value, document.querySelector('[name=category]').value, 'monthly')"
                        class="px-3 py-1.5 text-sm rounded {{ $viewMode === 'monthly' ? 'bg-white shadow-sm font-semibold' : 'text-gray-600' }}">
                        Bulanan
                    </button>
                </div>

                {{-- Date & Category Filter --}}
                <form id="controlSupplierFilterForm" class="flex items-center gap-2">
                    <select name="category" 
                        class="border rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 capitalize"
                        onchange="loadControlSupplierDashboard(this.form.date.value, this.value, '{{ $viewMode }}')">
                        <option value="all" {{ (request('category') == 'all' || !request('category')) ? 'selected' : '' }}>All Categories</option>
                        <option value="material" {{ request('category') == 'material' ? 'selected' : '' }}>Material/Masterbatch</option>
                        <option value="subpart" {{ request('category') == 'subpart' ? 'selected' : '' }}>Subpart</option>
                        <option value="layer" {{ request('category') == 'layer' ? 'selected' : '' }}>Layer</option>
                        <option value="box" {{ request('category') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="polybag" {{ request('category') == 'polybag' ? 'selected' : '' }}>Polybag</option>
                        <option value="rempart" {{ request('category') == 'rempart' ? 'selected' : '' }}>Rempart</option>
                    </select>

                    <input type="date" name="date" value="{{ $dateStr }}" 
                        class="border rounded-md px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onchange="loadControlSupplierDashboard(this.value, this.form.category.value, '{{ $viewMode }}')">
                    <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                </form>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
        {{-- Trend Chart --}}
        <div class="bg-white rounded-lg shadow-sm p-4 h-80 flex flex-col" style="height: 320px;">
            <h3 class="text-sm font-bold text-gray-700 mb-2 flex-shrink-0">
                {{ $viewMode === 'daily' ? 'Daily Trend - Last 30 Days (Service Rate %)' : 'Monthly Achievement Trend (Service Rate %)' }}
            </h3>
            <div class="flex-1 relative min-h-0" style="position: relative; overflow: hidden;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        {{-- Category Chart --}}
        <div class="bg-white rounded-lg shadow-sm p-4 h-80 flex flex-col" style="height: 320px;">
            <h3 class="text-sm font-bold text-gray-700 mb-2 flex-shrink-0">
                {{ $viewMode === 'daily' ? 'Category Achievement - Selected Date (SR %)' : 'Category Achievement (Current Month SR %)' }}
            </h3>
            <div class="flex-1 relative min-h-0" style="position: relative; overflow: hidden;">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
        
        {{-- Supplier Chart (Full Width) --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-sm p-4 h-80 flex flex-col" style="height: 320px;">
             <h3 class="text-sm font-bold text-gray-700 mb-2 flex-shrink-0">
                {{ $viewMode === 'daily' ? 'Supplier Achievement - Selected Date (SR %)' : 'Supplier Achievement (Current Month SR %)' }}
             </h3>
             <div class="flex-1 relative min-h-0" style="position: relative; overflow: hidden;">
                <canvas id="supplierChart"></canvas>
            </div>
        </div>
    </div>
    
    
    @php
        $hasData = !$supplierStats->isEmpty() || !$categoryStats->isEmpty();
        // Check if trend data has any non-zero values
        $hasTrend = collect($trendData)->sum('plan') > 0;
        
        // Calculate summary statistics
        $lowSRItems = $items->filter(function($item) {
            return $item['delivery_sr'] < 90 && $item['delivery_po'] > 0;
        })->sortBy('delivery_sr')->take(5);
        
        $highOutstandingItems = $items->filter(function($item) {
            return abs($item['delivery_balance']) > 0;
        })->sortByDesc(function($item) {
            return abs($item['delivery_balance']);
        })->take(5);
        
        $totalPO = $items->sum('delivery_po');
        $totalActual = $items->sum('delivery_act');
        $totalOutstanding = $items->sum('delivery_balance');
        $overallSR = $totalPO > 0 ? round(($totalActual / $totalPO) * 100, 1) : 0;
    @endphp

    {{-- Management Summary Cards --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
        {{-- Overall Statistics --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-sm p-4 text-white">
            <h3 class="text-sm font-semibold mb-3 opacity-90">📊 Ringkasan {{ $viewMode === 'daily' ? 'Harian' : 'Bulanan' }}</h3>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs opacity-90">Total Plan:</span>
                    <span class="font-bold text-lg">{{ number_format($totalPO) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs opacity-90">Total Actual:</span>
                    <span class="font-bold text-lg">{{ number_format($totalActual) }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs opacity-90">Outstanding:</span>
                    <span class="font-bold text-lg {{ $totalOutstanding < 0 ? 'text-red-200' : 'text-green-200' }}">
                        {{ number_format($totalOutstanding) }}
                    </span>
                </div>
                <div class="border-t border-white/20 pt-2 mt-2">
                    <div class="flex justify-between items-center">
                        <span class="text-xs opacity-90">Overall SR:</span>
                        <span class="font-bold text-2xl {{ $overallSR >= 90 ? 'text-green-200' : 'text-red-200' }}">
                            {{ $overallSR }}%
                        </span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Low Service Rate Items --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-bold text-red-600 mb-3">⚠️ Item SR Rendah (< 90%)</h3>
            @if($lowSRItems->isEmpty())
                <p class="text-xs text-gray-500 italic">Semua item memiliki SR ≥ 90% 👍</p>
            @else
                <div class="space-y-2">
                    @foreach($lowSRItems as $item)
                        <div class="border-l-4 border-red-400 pl-2 py-1 bg-red-50">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate">{{ $item['nama_material'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $item['supplier_name'] }}</p>
                                </div>
                                <div class="text-right ml-2">
                                    <p class="text-sm font-bold text-red-600">{{ $item['delivery_sr'] }}%</p>
                                    <p class="text-xs text-gray-500">{{ number_format(abs($item['delivery_balance'])) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- High Outstanding PO --}}
        <div class="bg-white rounded-lg shadow-sm p-4">
            <h3 class="text-sm font-bold text-orange-600 mb-3">📦 Outstanding PO Tertinggi</h3>
            @if($highOutstandingItems->isEmpty())
                <p class="text-xs text-gray-500 italic">Tidak ada outstanding PO</p>
            @else
                <div class="space-y-2">
                    @foreach($highOutstandingItems as $item)
                        <div class="border-l-4 {{ $item['delivery_balance'] < 0 ? 'border-red-400 bg-red-50' : 'border-green-400 bg-green-50' }} pl-2 py-1">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate">{{ $item['nama_material'] }}</p>
                                    <p class="text-xs text-gray-600">{{ $item['supplier_name'] }}</p>
                                </div>
                                <div class="text-right ml-2">
                                    <p class="text-sm font-bold {{ $item['delivery_balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ number_format($item['delivery_balance']) }}
                                    </p>
                                    <p class="text-xs text-gray-500">SR: {{ $item['delivery_sr'] }}%</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>


    @if(!$hasData && !$hasTrend)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded mb-4 text-sm">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Data Grafik Kosong:</strong> Belum ada data "Schedule Header" di database. Grafik akan muncul mendatar (nol) sampai data diinput.
        </div>
    @endif

    {{-- Supplier Summary Table --}}
    @php
        // Group items by supplier and calculate totals
        $supplierSummary = $items->groupBy('supplier_name')->map(function($supplierItems, $supplierName) {
            $totalPO = $supplierItems->sum('delivery_po');
            $totalActual = $supplierItems->sum('delivery_act');
            $totalBalance = $supplierItems->sum('delivery_balance');
            $overallSR = $totalPO > 0 ? round(($totalActual / $totalPO) * 100, 1) : 0;
            
            return [
                'supplier_name' => $supplierName,
                'total_po' => $totalPO,
                'total_actual' => $totalActual,
                'total_balance' => $totalBalance,
                'overall_sr' => $overallSR,
                'item_count' => $supplierItems->count(),
            ];
        })->sortByDesc('total_po')->values();
    @endphp

    <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6 border-2 border-indigo-200">
        <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 text-white text-center py-3 font-bold text-xl border-b-4 border-indigo-800">
            📊 RINGKASAN PER SUPPLIER
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-200">
                        <th class="p-4 border-2 border-gray-400 text-left font-bold text-sm uppercase tracking-wide">Supplier</th>
                        <th class="p-4 border-2 border-gray-400 text-center font-bold text-sm uppercase tracking-wide">Jumlah<br>Item</th>
                        <th class="p-4 border-2 border-gray-400 text-right font-bold text-sm uppercase tracking-wide bg-blue-50">Total Plan</th>
                        <th class="p-4 border-2 border-gray-400 text-right font-bold text-sm uppercase tracking-wide bg-blue-50">Total Actual</th>
                        <th class="p-4 border-2 border-gray-400 text-right font-bold text-sm uppercase tracking-wide bg-blue-50">Balance</th>
                        <th class="p-4 border-2 border-gray-400 text-center font-bold text-sm uppercase tracking-wide bg-green-50">Service<br>Rate</th>
                        <th class="p-4 border-2 border-gray-400 text-center font-bold text-sm uppercase tracking-wide">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($supplierSummary as $index => $supplier)
                        <tr class="hover:bg-blue-50 transition-colors {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="p-4 border-2 border-gray-300 font-bold text-base text-gray-800">{{ $supplier['supplier_name'] }}</td>
                            <td class="p-4 border-2 border-gray-300 text-center text-lg font-semibold">{{ $supplier['item_count'] }}</td>
                            <td class="p-4 border-2 border-gray-300 text-right bg-blue-50 text-base font-semibold">{{ number_format($supplier['total_po']) }}</td>
                            <td class="p-4 border-2 border-gray-300 text-right bg-blue-50 text-base font-semibold">{{ number_format($supplier['total_actual']) }}</td>
                            <td class="p-4 border-2 border-gray-300 text-right bg-blue-50 font-bold text-lg {{ $supplier['total_balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($supplier['total_balance']) }}
                            </td>
                            <td class="p-4 border-2 border-gray-300 text-center bg-green-50 font-bold text-2xl {{ $supplier['overall_sr'] < 90 ? 'text-red-600' : ($supplier['overall_sr'] >= 100 ? 'text-green-600' : 'text-orange-500') }}">
                                {{ $supplier['overall_sr'] }}%
                            </td>
                            <td class="p-4 border-2 border-gray-300 text-center font-bold text-base {{ $supplier['overall_sr'] >= 100 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-orange-600' }}">
                                <span class="px-3 py-1 rounded-full">{{ $supplier['overall_sr'] >= 100 ? 'CLOSE' : 'PENDING' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500 text-lg">
                                Tidak ada data supplier
                            </td>
                        </tr>
                    @endforelse
                    
                    {{-- Total Row --}}
                    @if($supplierSummary->isNotEmpty())
                        @php
                            $grandTotalPO = $supplierSummary->sum('total_po');
                            $grandTotalActual = $supplierSummary->sum('total_actual');
                            $grandTotalBalance = $supplierSummary->sum('total_balance');
                            $grandTotalSR = $grandTotalPO > 0 ? round(($grandTotalActual / $grandTotalPO) * 100, 1) : 0;
                            $totalItems = $supplierSummary->sum('item_count');
                        @endphp
                        <tr class="bg-gradient-to-r from-gray-200 to-gray-300 border-t-4 border-gray-600">
                            <td class="p-4 border-2 border-gray-400 text-left font-bold text-lg uppercase">🏆 TOTAL KESELURUHAN</td>
                            <td class="p-4 border-2 border-gray-400 text-center font-bold text-xl">{{ $totalItems }}</td>
                            <td class="p-4 border-2 border-gray-400 text-right bg-blue-100 font-bold text-lg">{{ number_format($grandTotalPO) }}</td>
                            <td class="p-4 border-2 border-gray-400 text-right bg-blue-100 font-bold text-lg">{{ number_format($grandTotalActual) }}</td>
                            <td class="p-4 border-2 border-gray-400 text-right bg-blue-100 font-bold text-xl {{ $grandTotalBalance < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($grandTotalBalance) }}
                            </td>
                            <td class="p-4 border-2 border-gray-400 text-center bg-green-100 font-bold text-3xl {{ $grandTotalSR < 90 ? 'text-red-600' : ($grandTotalSR >= 100 ? 'text-green-600' : 'text-orange-500') }}">
                                {{ $grandTotalSR }}%
                            </td>
                            <td class="p-4 border-2 border-gray-400 text-center font-bold text-xl {{ $grandTotalSR >= 100 ? 'bg-green-200 text-green-700' : 'bg-yellow-200 text-orange-600' }}">
                                <span class="px-4 py-2 rounded-full">{{ $grandTotalSR >= 100 ? 'CLOSE' : 'PENDING' }}</span>
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- Main Detail Table Section --}}
    <div class="bg-white rounded-lg shadow-lg overflow-hidden border-2 border-green-200">
        {{-- Custom Header Style matching the image --}}
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white text-center py-3 font-bold text-xl border-b-4 border-green-800">
            📋 DETAIL PER ITEM
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    {{-- Top Header Row --}}
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-200">
                        <th colspan="2" class="p-4 border-2 border-gray-400 text-left font-bold text-base">{{ $formattedDate }}</th>
                        <th colspan="4" class="p-4 border-2 border-gray-400 bg-blue-100 font-bold text-center text-base uppercase tracking-wide">DELIVERY</th>
                        <th rowspan="2" class="p-4 border-2 border-gray-400 bg-green-100 font-bold text-center text-base uppercase tracking-wide">STATUS</th>
                    </tr>
                    
                    {{-- Column Headers --}}
                    <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                        <th class="p-3 border-2 border-gray-400 text-left font-bold text-sm uppercase tracking-wide">Supplier</th>
                        <th class="p-3 border-2 border-gray-400 text-left font-bold text-sm uppercase tracking-wide">
                            @if($category == 'all')
                                Nama Item (All)
                            @elseif($category == 'material' || !$category)
                                Nama Material
                            @else
                                Nama {{ ucfirst($category) }}
                            @endif
                        </th>
                        
                        {{-- Delivery --}}
                        <th class="p-3 border-2 border-gray-400 text-right font-bold text-sm uppercase tracking-wide bg-blue-50">Plan</th>
                        <th class="p-3 border-2 border-gray-400 text-right font-bold text-sm uppercase tracking-wide bg-blue-50">Act Del</th>
                        <th class="p-3 border-2 border-gray-400 text-right font-bold text-sm uppercase tracking-wide bg-blue-50">+/-</th>
                        <th class="p-3 border-2 border-gray-400 text-center font-bold text-sm uppercase tracking-wide bg-blue-50">SR (%)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $index => $item)
                        @php
                            $uom = in_array($item['kategori'], ['material', 'masterbatch']) ? 'KG' : 'PCS';
                        @endphp
                        <tr class="hover:bg-blue-50 transition-colors {{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="p-3 border-2 border-gray-300 font-semibold text-sm">{{ $item['supplier_name'] }}</td>
                            <td class="p-3 border-2 border-gray-300 text-sm">
                                <div class="font-semibold">{{ $item['nama_material'] }}</div>
                                <div class="text-xs text-gray-500">{{ $uom }}</div>
                            </td>
                            
                            {{-- Delivery --}}
                            <td class="p-3 border-2 border-gray-300 text-right bg-blue-50 font-semibold text-base">{{ number_format($item['delivery_po']) }}</td>
                            <td class="p-3 border-2 border-gray-300 text-right bg-blue-50 font-semibold text-base">{{ number_format($item['delivery_act']) }}</td>
                            <td class="p-3 border-2 border-gray-300 text-right bg-blue-50 font-bold text-lg {{ $item['delivery_balance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ number_format($item['delivery_balance']) }}
                            </td>
                            <td class="p-3 border-2 border-gray-300 text-center bg-blue-50 font-bold text-xl {{ $item['delivery_sr'] < 100 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $item['delivery_sr'] }}%
                            </td>
                            
                            {{-- Status --}}
                            <td class="p-3 border-2 border-gray-300 text-center font-bold text-sm {{ $item['delivery_sr'] >= 100 ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-orange-600' }}">
                                <span class="px-3 py-1 rounded-full">{{ $item['delivery_sr'] >= 100 ? 'CLOSE' : 'PENDING' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500 text-lg">
                                Tidak ada data untuk tanggal ini
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Script for Charts --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    function initSupplierCharts() {
        if (typeof Chart === 'undefined') {
            setTimeout(initSupplierCharts, 100);
            return;
        }

        // Data from Controller
        const trendData = @json($trendData ?? []);
        const supplierData = @json($supplierStats ?? []);
        const categoryData = @json($categoryStats ?? []);
        
        // Helper to destroy chart
        const destroyChart = (id) => {
            const chart = Chart.getChart(id);
            if (chart) chart.destroy();
        };

        // Common Options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
        };

        // 1. Trend Chart (Plan vs Act + SR Line)
        const canvasTrend = document.getElementById('trendChart');
        if (canvasTrend) {
            destroyChart('trendChart');
            new Chart(canvasTrend.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: trendData.map(d => d.month),
                    datasets: [
                        {
                            label: 'Plan',
                            data: trendData.map(d => d.plan),
                            backgroundColor: '#93c5fd', // Light Blue
                            order: 2,
                            yAxisID: 'y'
                        },
                        {
                            label: 'Actual',
                            data: trendData.map(d => d.act),
                            backgroundColor: '#2563eb', // Dark Blue
                            order: 3,
                            yAxisID: 'y'
                        },
                        {
                            type: 'line',
                            label: 'SR (%)',
                            data: trendData.map(d => d.sr),
                            borderColor: '#16a34a', // Green
                            borderWidth: 2,
                            pointRadius: 2,
                            fill: false,
                            order: 1,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        y: { 
                            beginAtZero: true,
                            position: 'left',
                            title: { display: true, text: 'Quantity' }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            max: 120,
                            title: { display: true, text: 'SR %' },
                            grid: { drawOnChartArea: false }
                        }
                    }
                }
            });
        }

        // 2. Category Chart (Plan vs Act)
        const canvasCat = document.getElementById('categoryChart');
        if (canvasCat) {
            destroyChart('categoryChart');
            if (categoryData.length > 0) {
                new Chart(canvasCat.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: categoryData.map(d => d.category),
                        datasets: [
                            {
                                label: 'Plan',
                                data: categoryData.map(d => d.plan),
                                backgroundColor: '#fbbf24' // Amber
                            },
                            {
                                label: 'Actual',
                                data: categoryData.map(d => d.act),
                                backgroundColor: '#f59e0b' // Dark Amber
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'Quantity' } } },
                        plugins: { 
                            title: { display: true, text: 'Plan vs Actual by Category' },
                            tooltip: {
                                callbacks: {
                                    afterBody: function(context) {
                                        const idx = context[0].dataIndex;
                                        const sr = categoryData[idx].sr;
                                        return `SR: ${sr}%`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                 canvasCat.parentNode.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-sm">No category data available</div>';
            }
        }

        // 3. Supplier Chart (Plan vs Act)
        const canvasSup = document.getElementById('supplierChart');
        if (canvasSup) {
            destroyChart('supplierChart');
            if (supplierData.length > 0) {
                new Chart(canvasSup.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: supplierData.map(d => d.name),
                        datasets: [
                            {
                                label: 'Plan',
                                data: supplierData.map(d => d.plan || 0), // Handle potential undefined in mapping if new
                                backgroundColor: '#a5b4fc' // Indigo light
                            },
                            {
                                label: 'Actual',
                                data: supplierData.map(d => d.act),
                                backgroundColor: '#6366f1' // Indigo
                            }
                        ]
                    },
                    options: {
                        ...commonOptions,
                        scales: { y: { beginAtZero: true, title: { display: true, text: 'Quantity' } } },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    afterBody: function(context) {
                                        const idx = context[0].dataIndex;
                                        const sr = supplierData[idx].sr;
                                        return `SR: ${sr}%`;
                                    }
                                }
                            }
                        }
                    }
                });
            } else {
                 canvasSup.parentNode.innerHTML = '<div class="flex items-center justify-center h-full text-gray-400 text-sm">No supplier data available</div>';
            }
        }
    }

    // Call init immediately
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSupplierCharts);
    } else {
        initSupplierCharts();
    }


function loadControlSupplierDashboard(date, category, viewMode) {
    const url = '{{ route("controlsupplier.dashboard") }}?date=' + date + '&category=' + category + '&view_mode=' + viewMode;
    window.location.href = url;
}
</script>
@endsection
