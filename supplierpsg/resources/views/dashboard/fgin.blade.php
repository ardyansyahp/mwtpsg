@extends('layout.app')

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">📦 Dashboard Finish Good In</h1>
            <p class="text-gray-600 mt-1">Monitoring hasil scan masuk (Produksi) per periode</p>
        </div>
        
        {{-- Filter & Auto-Refresh Section --}}
        <div class="w-full md:w-auto flex flex-col md:flex-row gap-2">
            <form id="filterForm" class="flex flex-col md:flex-row gap-2">
                <select name="part_id" id="filterPart" class="select2-part border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 min-w-[300px]">
                    <option value="">-- Semua Part --</option>
                    @foreach($allParts ?? [] as $part)
                        <option value="{{ $part->id }}" {{ (request('part_id') == $part->id) ? 'selected' : '' }}>
                            {{ $part->nomor_part }} - {{ $part->nama_part }}
                        </option>
                    @endforeach
                </select>
                @if(request('part_id'))
                    <a href="{{ route('finishgood.in.dashboard') }}" class="bg-gray-100 text-gray-600 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                        <i class="fas fa-times mr-2"></i>Reset
                    </a>
                @endif
            </form>
            
            {{-- Auto-Refresh Toggle --}}
            <button id="autoRefreshToggle" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600 transition-all flex items-center gap-2 shadow-sm">
                <i class="fas fa-sync-alt" id="refreshIcon"></i>
                <span id="refreshText">Auto-Refresh: ON</span>
                <span id="countdown" class="text-xs bg-white/20 px-2 py-0.5 rounded">10s</span>
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Hari Ini --}}
        <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-blue-500 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Hari Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($todayQty ?? 0) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">qty pcs</p>
                </div>
                <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Minggu Ini --}}
        <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-green-500 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Minggu Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($weekQty ?? 0) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">qty pcs</p>
                </div>
                <div class="p-2 bg-green-50 rounded-lg text-green-600">
                    <i class="fas fa-calendar-week text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Bulan Ini --}}
        <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-purple-500 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Bulan Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($monthQty ?? 0) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">qty pcs</p>
                </div>
                <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                    <i class="fas fa-calendar-alt text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Total Keseluruhan --}}
        <div class="bg-white rounded-xl shadow-sm border border-l-4 border-l-orange-500 p-4">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total All Time</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($totalQty ?? 0) }}</h3>
                    <p class="text-xs text-gray-400 mt-1">qty pcs</p>
                </div>
                <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                    <i class="fas fa-database text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart --}}
    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-line text-blue-500"></i>
            Trend Produksi (30 Hari Terakhir)
        </h3>
        <div class="h-[350px] w-full">
            <canvas id="trendChart"></canvas>
        </div>
    </div>
</div>

{{-- Select2 CSS & JS --}}
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

{{-- Select2 Custom Styling --}}
<style>
.select2-container--default .select2-selection--single {
    height: 42px !important;
    border: 1px solid #d1d5db !important;
    border-radius: 0.5rem !important;
    padding: 0.5rem 1rem !important;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px !important;
    padding-left: 0 !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 40px !important;
}
.select2-dropdown {
    border-radius: 0.5rem !important;
    border: 1px solid #d1d5db !important;
}
.select2-container--default .select2-results__option--highlighted[aria-selected] {
    background-color: #3b82f6 !important;
}
</style>

<script>
$(document).ready(function() {
    // Initialize Select2
    $('.select2-part').select2({
        placeholder: '-- Ketik untuk mencari part --',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        language: {
            noResults: function() {
                return "Part tidak ditemukan";
            },
            searching: function() {
                return "Mencari...";
            }
        }
    });

    // Submit form on change
    $('.select2-part').on('change', function() {
        $('#filterForm').submit();
    });
});
</script>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let chartInstance = null;
let autoRefreshEnabled = true;
let refreshInterval = null;
let countdownInterval = null;
let secondsLeft = 10;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Chart
    const ctx = document.getElementById('trendChart');
    if (ctx) {
        chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels'] ?? []) !!},
                datasets: [{
                    label: 'Qty In (Pcs)',
                    data: {!! json_encode($chartData['values'] ?? []) !!},
                    borderColor: '#3B82F6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end'
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#F3F4F6'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                interaction: {
                    mode: 'nearest',
                    axis: 'x',
                    intersect: false
                }
            }
        });
    }

    // Auto-Refresh Toggle Button
    const toggleBtn = document.getElementById('autoRefreshToggle');
    const refreshIcon = document.getElementById('refreshIcon');
    const refreshText = document.getElementById('refreshText');
    const countdownEl = document.getElementById('countdown');

    toggleBtn.addEventListener('click', function() {
        autoRefreshEnabled = !autoRefreshEnabled;
        
        if (autoRefreshEnabled) {
            toggleBtn.classList.remove('bg-gray-500', 'hover:bg-gray-600');
            toggleBtn.classList.add('bg-green-500', 'hover:bg-green-600');
            refreshText.textContent = 'Auto-Refresh: ON';
            startAutoRefresh();
        } else {
            toggleBtn.classList.remove('bg-green-500', 'hover:bg-green-600');
            toggleBtn.classList.add('bg-gray-500', 'hover:bg-gray-600');
            refreshText.textContent = 'Auto-Refresh: OFF';
            stopAutoRefresh();
        }
    });

    // Start auto-refresh on page load
    startAutoRefresh();

    function startAutoRefresh() {
        secondsLeft = 10;
        updateCountdown();
        
        // Clear existing intervals
        if (refreshInterval) clearInterval(refreshInterval);
        if (countdownInterval) clearInterval(countdownInterval);
        
        // Countdown timer
        countdownInterval = setInterval(function() {
            secondsLeft--;
            updateCountdown();
            
            if (secondsLeft <= 0) {
                secondsLeft = 10;
            }
        }, 1000);
        
        // Refresh data every 10 seconds
        refreshInterval = setInterval(function() {
            if (autoRefreshEnabled) {
                refreshData();
            }
        }, 10000);
    }

    function stopAutoRefresh() {
        if (refreshInterval) clearInterval(refreshInterval);
        if (countdownInterval) clearInterval(countdownInterval);
        countdownEl.textContent = '--';
    }

    function updateCountdown() {
        countdownEl.textContent = secondsLeft + 's';
    }

    function refreshData() {
        // Add spinning animation to icon
        refreshIcon.classList.add('fa-spin');
        
        // Get current filter
        const partId = new URLSearchParams(window.location.search).get('part_id') || '';
        const url = `{{ route('finishgood.in.dashboard') }}?part_id=${partId}&ajax=1`;
        
        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            // Update summary cards with animation
            updateCardValue('todayQty', data.todayQty);
            updateCardValue('weekQty', data.weekQty);
            updateCardValue('monthQty', data.monthQty);
            updateCardValue('totalQty', data.totalQty);
            
            // Update chart
            if (chartInstance && data.chartData) {
                chartInstance.data.labels = data.chartData.labels;
                chartInstance.data.datasets[0].data = data.chartData.values;
                chartInstance.update('none'); // Update without animation for smoother experience
            }
            
            // Remove spinning animation
            setTimeout(() => {
                refreshIcon.classList.remove('fa-spin');
            }, 500);
        })
        .catch(error => {
            console.error('Error refreshing data:', error);
            refreshIcon.classList.remove('fa-spin');
        });
    }

    function updateCardValue(id, newValue) {
        const cards = {
            'todayQty': document.querySelector('.border-l-blue-500 h3'),
            'weekQty': document.querySelector('.border-l-green-500 h3'),
            'monthQty': document.querySelector('.border-l-purple-500 h3'),
            'totalQty': document.querySelector('.border-l-orange-500 h3')
        };
        
        const element = cards[id];
        if (element) {
            const currentValue = element.textContent.replace(/,/g, '');
            const formattedValue = Number(newValue).toLocaleString('id-ID');
            
            if (currentValue !== formattedValue) {
                // Add pulse animation
                element.parentElement.parentElement.classList.add('animate-pulse');
                element.textContent = formattedValue;
                
                setTimeout(() => {
                    element.parentElement.parentElement.classList.remove('animate-pulse');
                }, 1000);
            }
        }
    }
});
</script>
@endsection

