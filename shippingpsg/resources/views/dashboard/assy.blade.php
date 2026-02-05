@extends('layout.app')

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard Assy</h1>
            <p class="text-gray-600 mt-1">Monitoring pergerakan part, pemakaian bahan baku, dan manpower</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span>{{ now()->format('d F Y') }}</span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Part di Assy Saat Ini --}}
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm font-medium">Part di Assy</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($currentParts) }}</h3>
                    <p class="text-indigo-100 text-xs mt-1">part (In - Out)</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-puzzle-piece text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Assy In Hari Ini --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Assy In Hari Ini</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $todayAssyIn }}</h3>
                    <p class="text-green-100 text-xs mt-1">part masuk</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-sign-in-alt text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Assy Out Hari Ini --}}
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Assy Out Hari Ini</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $todayAssyOut }}</h3>
                    <p class="text-orange-100 text-xs mt-1">part keluar</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-sign-out-alt text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Assy In vs Out --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                Trend Assy In vs Out (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="assyInOutChart"></canvas>
            </div>
        </div>

        {{-- Chart Manpower --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-users text-pink-500 mr-2"></i>
                Manpower Assy (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="manpowerChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Per Meja --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-table text-teal-500 mr-2"></i>
                Assy per Meja (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="assyPerMejaChart"></canvas>
            </div>
        </div>

        {{-- Chart Pemakaian Bahan Baku --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-box text-purple-500 mr-2"></i>
                Top 5 Pemakaian Subpart (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="bahanBakuChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 3 --}}
    <div class="grid grid-cols-1 gap-6">
        {{-- Top Part --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Top 5 Part Paling Banyak di Assy (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="topPartsChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Script --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<style>
    /* Pastikan canvas tidak melebihi container */
    #assyInOutChart,
    #manpowerChart,
    #assyPerMejaChart,
    #bahanBakuChart,
    #topPartsChart {
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
    const rawAssyInData = {!! json_encode($assyInChartData ?? []) !!};
    const rawAssyOutData = {!! json_encode($assyOutChartData ?? []) !!};
    const rawManpowerData = {!! json_encode($manpowerChartData ?? []) !!};
    const assyPerMeja = {!! json_encode($assyPerMeja ?? []) !!};
    const bahanBakuUsage = {!! json_encode($bahanBakuUsage ?? []) !!};
    const topParts = {!! json_encode($topParts ?? []) !!};

    // Pastikan data tidak kosong
    let dates = Array.isArray(rawDates) && rawDates.length > 0 ? rawDates : ['Hari 1', 'Hari 2', 'Hari 3', 'Hari 4', 'Hari 5', 'Hari 6', 'Hari 7'];
    let assyInData = Array.isArray(rawAssyInData) && rawAssyInData.length > 0 ? rawAssyInData : [0, 0, 0, 0, 0, 0, 0];
    let assyOutData = Array.isArray(rawAssyOutData) && rawAssyOutData.length > 0 ? rawAssyOutData : [0, 0, 0, 0, 0, 0, 0];
    let manpowerData = Array.isArray(rawManpowerData) && rawManpowerData.length > 0 ? rawManpowerData : [0, 0, 0, 0, 0, 0, 0];

    // Chart 1: Assy In vs Out
    const canvas1 = document.getElementById('assyInOutChart');
    if (!canvas1) {
        console.error('Canvas assyInOutChart tidak ditemukan!');
        return;
    }
    const ctx1 = canvas1.getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Assy In',
                data: assyInData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Assy Out',
                data: assyOutData,
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

    // Chart 2: Manpower
    const canvas2 = document.getElementById('manpowerChart');
    if (canvas2) {
        const ctx2 = canvas2.getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Jumlah Manpower',
                    data: manpowerData,
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
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
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

    // Chart 3: Assy per Meja
    const assyPerMejaLabels = Object.keys(assyPerMeja).length > 0 ? Object.keys(assyPerMeja) : ['Belum ada data'];
    const assyPerMejaValues = Object.values(assyPerMeja).length > 0 ? Object.values(assyPerMeja) : [1];
    const canvas3 = document.getElementById('assyPerMejaChart');
    if (canvas3) {
        const ctx3 = canvas3.getContext('2d');
        new Chart(ctx3, {
            type: 'doughnut',
            data: {
                labels: assyPerMejaLabels,
                datasets: [{
                    data: assyPerMejaValues,
                    backgroundColor: [
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
                        'rgba(168, 85, 247, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
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

    // Chart 4: Pemakaian Bahan Baku (Subpart)
    const bahanBakuLabels = Object.keys(bahanBakuUsage).length > 0 ? Object.keys(bahanBakuUsage) : ['Belum ada data'];
    const bahanBakuValues = Object.values(bahanBakuUsage).length > 0 ? Object.values(bahanBakuUsage) : [0];
    const canvas4 = document.getElementById('bahanBakuChart');
    if (canvas4) {
        const ctx4 = canvas4.getContext('2d');
        new Chart(ctx4, {
            type: 'bar',
            data: {
                labels: bahanBakuLabels,
                datasets: [{
                    label: 'Qty',
                    data: bahanBakuValues,
                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
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

    // Chart 5: Top Parts
    const topPartsLabels = Array.isArray(topParts) && topParts.length > 0 ? topParts.map(item => item.nomor_part) : ['Belum ada data'];
    const topPartsValues = Array.isArray(topParts) && topParts.length > 0 ? topParts.map(item => item.total) : [0];
    const canvas5 = document.getElementById('topPartsChart');
    if (canvas5) {
        const ctx5 = canvas5.getContext('2d');
        new Chart(ctx5, {
            type: 'bar',
            data: {
                labels: topPartsLabels,
                datasets: [{
                    label: 'Jumlah Part',
                    data: topPartsValues,
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
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                if (Array.isArray(topParts) && topParts.length > 0 && topParts[index]) {
                                    return topParts[index].nama_part;
                                }
                                return 'Belum ada data';
                            }
                        }
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
}

// Start initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCharts);
} else {
    initCharts();
}
</script>

@endsection
