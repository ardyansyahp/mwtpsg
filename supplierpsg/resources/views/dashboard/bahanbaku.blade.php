@extends('layout.app')

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-xl font-bold text-gray-900 leading-none">Dashboard Bahan Baku</h1>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Monitoring pergerakan data bahan baku (Receiving & Supply)</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span>{{ now()->format('d F Y') }}</span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Total Stock Value --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Stock Value</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($totalStockValue ?? 0, 2) }}</h3>
                    <p class="text-blue-100 text-xs mt-1">kg ({{ $currentStock ?? 0 }} jenis)</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-boxes text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Bahan Baku Aktif --}}
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium">Bahan Baku Aktif</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($currentStock ?? 0) }}</h3>
                    <p class="text-indigo-100 text-xs mt-1">jenis dengan stock > 0</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-cube text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Receiving Hari Ini --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Receiving Hari Ini</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($todayReceiving ?? 0) }}</h3>
                    <p class="text-green-100 text-xs mt-1">transaksi masuk</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-arrow-down text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Supply Hari Ini --}}
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Supply Hari Ini</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($todaySupply ?? 0) }}</h3>
                    <p class="text-orange-100 text-xs mt-1">transaksi keluar</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-arrow-up text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Stock Level --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-chart-area text-blue-500 mr-2"></i>
                Level Stock (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="stockLevelChart"></canvas>
            </div>
        </div>

        {{-- Chart Receiving vs Supply (QTY) --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-chart-line text-green-500 mr-2"></i>
                Trend Receiving vs Supply (QTY) - 7 Hari
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="receivingSupplyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 1.5 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Schedule Plan vs Actual --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-calendar-check text-purple-500 mr-2"></i>
                Schedule Plan vs Actual (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="schedulePlanActualChart"></canvas>
            </div>
        </div>

        {{-- Chart Supply per Tujuan --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-chart-bar text-orange-500 mr-2"></i>
                Supply ke Inject vs Assy (QTY) - 7 Hari
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="supplyTujuanChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Supply ke Mesin --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-industry text-indigo-500 mr-2"></i>
                Supply ke Mesin (Inject)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="supplyMesinChart"></canvas>
            </div>
        </div>

        {{-- Chart Supply ke Meja --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-table text-teal-500 mr-2"></i>
                Supply ke Meja (Assy)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="supplyMejaChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 3 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Bahan Baku --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Top 5 Bahan Baku Paling Banyak Diterima
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="topBahanBakuChart"></canvas>
            </div>
        </div>

        {{-- Stock Per Kategori --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-layer-group text-pink-500 mr-2"></i>
                Stock Per Kategori Bahan Baku
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="stockKategoriChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    /* Pastikan canvas tidak melebihi container */
    #receivingSupplyChart,
    #supplyTujuanChart,
    #supplyMesinChart,
    #supplyMejaChart,
    #topBahanBakuChart,
    #stockKategoriChart,
    #stockLevelChart,
    #schedulePlanActualChart {
        max-width: 100% !important;
        max-height: 100% !important;
        width: 100% !important;
        height: 100% !important;
    }
</style>
<script>
// Wait for Chart.js to load
function initCharts() {
    // Pastikan Chart.js sudah ter-load
    if (typeof Chart === 'undefined') {
        console.error('Chart.js tidak ter-load! Menunggu...');
        setTimeout(initCharts, 100);
        return;
    }

    const rawDates = {!! json_encode($dates ?? []) !!};
    const rawReceivingData = {!! json_encode($receivingChartData ?? []) !!};
    const rawSupplyInjectData = {!! json_encode($supplyInjectChartData ?? []) !!};
    const rawSupplyAssyData = {!! json_encode($supplyAssyChartData ?? []) !!};
    const rawStockLevelData = {!! json_encode($stockLevelChartData ?? []) !!};
    const rawSchedulePlanData = {!! json_encode($schedulePlanChartData ?? []) !!};
    const rawScheduleActData = {!! json_encode($scheduleActChartData ?? []) !!};
    const supplyMesinData = {!! json_encode($supplyToMesin ?? []) !!};
    const supplyMejaData = {!! json_encode($supplyToMeja ?? []) !!};
    const topBahanBaku = {!! json_encode($topBahanBaku ?? []) !!};
    const stockKategoriLabels = {!! json_encode($stockKategoriLabels ?? []) !!};
    const stockKategoriValues = {!! json_encode($stockKategoriValues ?? []) !!};

    // Pastikan data tidak kosong
    let dates = Array.isArray(rawDates) && rawDates.length > 0 ? rawDates : ['Hari 1', 'Hari 2', 'Hari 3', 'Hari 4', 'Hari 5', 'Hari 6', 'Hari 7'];
    let receivingData = Array.isArray(rawReceivingData) && rawReceivingData.length > 0 ? rawReceivingData : [0, 0, 0, 0, 0, 0, 0];
    let supplyInjectData = Array.isArray(rawSupplyInjectData) && rawSupplyInjectData.length > 0 ? rawSupplyInjectData : [0, 0, 0, 0, 0, 0, 0];
    let supplyAssyData = Array.isArray(rawSupplyAssyData) && rawSupplyAssyData.length > 0 ? rawSupplyAssyData : [0, 0, 0, 0, 0, 0, 0];
    let stockLevelData = Array.isArray(rawStockLevelData) && rawStockLevelData.length > 0 ? rawStockLevelData : [0, 0, 0, 0, 0, 0, 0];
    let schedulePlanData = Array.isArray(rawSchedulePlanData) && rawSchedulePlanData.length > 0 ? rawSchedulePlanData : [0, 0, 0, 0, 0, 0, 0];
    let scheduleActData = Array.isArray(rawScheduleActData) && rawScheduleActData.length > 0 ? rawScheduleActData : [0, 0, 0, 0, 0, 0, 0];

    // Chart 0: Stock Level
    const canvas0 = document.getElementById('stockLevelChart');
    if (canvas0) {
        const ctx0 = canvas0.getContext('2d');
        new Chart(ctx0, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Level Stock (kg)',
                    data: stockLevelData,
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Stock: ' + context.parsed.y.toFixed(2) + ' kg';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                return value.toFixed(0) + ' kg';
                            }
                        }
                    },
                    x: {
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Chart 1: Receiving vs Supply (QTY)
    const canvas1 = document.getElementById('receivingSupplyChart');
    if (!canvas1) {
        console.error('Canvas receivingSupplyChart tidak ditemukan!');
        return;
    }
    const ctx1 = canvas1.getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Receiving (IN) - kg',
                data: receivingData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Supply (OUT) - kg',
                data: supplyInjectData.map((val, idx) => val + supplyAssyData[idx]),
                borderColor: 'rgb(249, 115, 22)',
                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 12,
                        padding: 10,
                        font: {
                            size: 11
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 10
                        }
                    }
                }
            }
        }
    });

    // Chart 1.5: Schedule Plan vs Actual
    const canvas15 = document.getElementById('schedulePlanActualChart');
    if (canvas15) {
        const ctx15 = canvas15.getContext('2d');
        new Chart(ctx15, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Schedule Plan',
                    data: schedulePlanData,
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                }, {
                    label: 'Schedule Actual',
                    data: scheduleActData,
                    backgroundColor: 'rgba(236, 72, 153, 0.8)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: { size: 10 }
                        }
                    },
                    x: {
                        ticks: { font: { size: 10 } }
                    }
                }
            }
        });
    }

    // Chart 2: Supply per Tujuan
    const canvas2 = document.getElementById('supplyTujuanChart');
    if (!canvas2) {
        console.error('Canvas supplyTujuanChart tidak ditemukan!');
    } else {
        const ctx2 = canvas2.getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Supply ke Inject (kg)',
                    data: supplyInjectData,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                }, {
                    label: 'Supply ke Assy (kg)',
                    data: supplyAssyData,
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    x: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }

    // Chart 3: Supply ke Mesin
    const supplyMesinLabels = Object.keys(supplyMesinData).length > 0 ? Object.keys(supplyMesinData) : ['Belum ada data'];
    const supplyMesinValues = Object.values(supplyMesinData).length > 0 ? Object.values(supplyMesinData) : [1];
    const canvas3 = document.getElementById('supplyMesinChart');
    if (!canvas3) {
        console.error('Canvas supplyMesinChart tidak ditemukan!');
    } else {
        const ctx3 = canvas3.getContext('2d');
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: supplyMesinLabels,
                datasets: [{
                    data: supplyMesinValues,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 8,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    // Chart 4: Supply ke Meja
    const supplyMejaLabels = Object.keys(supplyMejaData).length > 0 ? Object.keys(supplyMejaData) : ['Belum ada data'];
    const supplyMejaValues = Object.values(supplyMejaData).length > 0 ? Object.values(supplyMejaData) : [1];
    const canvas4 = document.getElementById('supplyMejaChart');
    if (!canvas4) {
        console.error('Canvas supplyMejaChart tidak ditemukan!');
    } else {
        const ctx4 = canvas4.getContext('2d');
        new Chart(ctx4, {
            type: 'pie',
            data: {
                labels: supplyMejaLabels,
                datasets: [{
                    data: supplyMejaValues,
                    backgroundColor: [
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 8,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    }

    // Chart 5: Top Bahan Baku
    const topBahanBakuLabels = Array.isArray(topBahanBaku) && topBahanBaku.length > 0 
        ? topBahanBaku.map(item => item.bahan_baku ? item.bahan_baku.nama_bahan_baku : 'Unknown')
        : ['Belum ada data'];
    const topBahanBakuValues = Array.isArray(topBahanBaku) && topBahanBaku.length > 0 
        ? topBahanBaku.map(item => item.total_qty)
        : [0];
    const canvas5 = document.getElementById('topBahanBakuChart');
    if (!canvas5) {
        console.error('Canvas topBahanBakuChart tidak ditemukan!');
    } else {
        const ctx5 = canvas5.getContext('2d');
        new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: topBahanBakuLabels,
                datasets: [{
                    label: 'Total Qty (kg)',
                    data: topBahanBakuValues,
                    backgroundColor: 'rgba(234, 179, 8, 0.8)',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    },
                    y: {
                        ticks: {
                            font: {
                                size: 10
                            }
                        }
                    }
                }
            }
        });
    }

    // Chart 6: Stock Per Kategori
    const stockKategoriLabelsArray = Array.isArray(stockKategoriLabels) && stockKategoriLabels.length > 0 
        ? stockKategoriLabels 
        : ['Belum ada data'];
    const stockKategoriValuesArray = Array.isArray(stockKategoriValues) && stockKategoriValues.length > 0 
        ? stockKategoriValues 
        : [0];
    const canvas6 = document.getElementById('stockKategoriChart');
    if (!canvas6) {
        console.error('Canvas stockKategoriChart tidak ditemukan!');
    } else {
        const ctx6 = canvas6.getContext('2d');
        new Chart(ctx6, {
            type: 'doughnut',
            data: {
                labels: stockKategoriLabelsArray,
                datasets: [{
                    data: stockKategoriValuesArray,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        top: 10,
                        bottom: 10,
                        left: 10,
                        right: 10
                    }
                },
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 8,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + ' jenis';
                            }
                        }
                    }
                }
            }
        });
    }
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
</script>

@endsection
