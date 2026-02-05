@extends('layout.app')

@section('content')
<div class="p-6 space-y-6">
    {{-- Header --}}
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Dashboard WIP</h1>
            <p class="text-gray-600 mt-1">Monitoring pergerakan part dan pemakaian bahan baku di WIP</p>
        </div>
        <div class="text-sm text-gray-500">
            <i class="fas fa-calendar-alt mr-2"></i>
            <span>{{ now()->format('d F Y') }}</span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        {{-- Part di WIP Saat Ini --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Part di WIP</p>
                    <h3 class="text-3xl font-bold mt-2">{{ number_format($currentParts) }}</h3>
                    <p class="text-blue-100 text-xs mt-1">part</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-box-open text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- WIP In Hari Ini --}}
        <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">WIP In Hari Ini</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $todayWipIn }}</h3>
                    <p class="text-green-100 text-xs mt-1">part</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-sign-in-alt text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- WIP Out Hari Ini --}}
        <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">WIP Out Hari Ini</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $todayWipOut }}</h3>
                    <p class="text-orange-100 text-xs mt-1">part</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-sign-out-alt text-3xl"></i>
                </div>
            </div>
        </div>

        {{-- Confirmed vs Unconfirmed --}}
        <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Confirmed</p>
                    <h3 class="text-3xl font-bold mt-2">{{ $confirmedWipIn }}</h3>
                    <p class="text-purple-100 text-xs mt-1">{{ $unconfirmedWipIn }} belum</p>
                </div>
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <i class="fas fa-check-circle text-3xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Row 1 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart WIP In vs Out --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                Trend WIP In vs Out (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="wipInOutChart"></canvas>
            </div>
        </div>

        {{-- Chart Mesin Asal --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-industry text-indigo-500 mr-2"></i>
                WIP dari Mesin (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="mesinAsalChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 2 --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Chart Pemakaian Bahan Baku --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-box text-teal-500 mr-2"></i>
                Top 5 Pemakaian Bahan Baku (7 Hari Terakhir)
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="bahanBakuChart"></canvas>
            </div>
        </div>

        {{-- Chart Confirmed Status --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-pie-chart text-purple-500 mr-2"></i>
                Status Konfirmasi WIP In
            </h3>
            <div style="flex: 1; position: relative; min-height: 0; overflow: hidden;">
                <canvas id="confirmedChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Charts Row 3 --}}
    <div class="grid grid-cols-1 gap-6">
        {{-- Top Part --}}
        <div class="bg-white rounded-lg shadow-lg p-6" style="height: 400px; display: flex; flex-direction: column;">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" style="flex-shrink: 0;">
                <i class="fas fa-star text-yellow-500 mr-2"></i>
                Top 5 Part di WIP (7 Hari Terakhir)
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
    #wipInOutChart,
    #mesinAsalChart,
    #bahanBakuChart,
    #confirmedChart,
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
    const rawWipInData = {!! json_encode($wipInChartData ?? []) !!};
    const rawWipOutData = {!! json_encode($wipOutChartData ?? []) !!};
    const confirmedWipIn = {!! json_encode($confirmedWipIn ?? 0) !!};
    const unconfirmedWipIn = {!! json_encode($unconfirmedWipIn ?? 0) !!};
    const mesinAsal = {!! json_encode($mesinAsal ?? []) !!};
    const bahanBakuUsage = {!! json_encode($bahanBakuUsage ?? []) !!};
    const topParts = {!! json_encode($topParts ?? []) !!};

    // Pastikan data tidak kosong
    let dates = Array.isArray(rawDates) && rawDates.length > 0 ? rawDates : ['Hari 1', 'Hari 2', 'Hari 3', 'Hari 4', 'Hari 5', 'Hari 6', 'Hari 7'];
    let wipInData = Array.isArray(rawWipInData) && rawWipInData.length > 0 ? rawWipInData : [0, 0, 0, 0, 0, 0, 0];
    let wipOutData = Array.isArray(rawWipOutData) && rawWipOutData.length > 0 ? rawWipOutData : [0, 0, 0, 0, 0, 0, 0];

    // Chart 1: WIP In vs Out
    const canvas1 = document.getElementById('wipInOutChart');
    if (!canvas1) {
        console.error('Canvas wipInOutChart tidak ditemukan!');
        return;
    }
    
    const ctx1 = canvas1.getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'WIP In',
                data: wipInData,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'WIP Out',
                data: wipOutData,
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

    // Chart 2: Mesin Asal
    const mesinAsalLabels = Object.keys(mesinAsal).length > 0 ? Object.keys(mesinAsal) : ['Belum ada data'];
    const mesinAsalValues = Object.values(mesinAsal).length > 0 ? Object.values(mesinAsal) : [1];
    const canvas2 = document.getElementById('mesinAsalChart');
    if (canvas2) {
        const ctx2 = canvas2.getContext('2d');
        new Chart(ctx2, {
            type: 'doughnut',
            data: {
                labels: mesinAsalLabels,
                datasets: [{
                    data: mesinAsalValues,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(251, 146, 60, 0.8)',
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

    // Chart 3: Pemakaian Bahan Baku
    const bahanBakuLabels = Object.keys(bahanBakuUsage).length > 0 ? Object.keys(bahanBakuUsage) : ['Belum ada data'];
    const bahanBakuValues = Object.values(bahanBakuUsage).length > 0 ? Object.values(bahanBakuUsage) : [0];
    const canvas3 = document.getElementById('bahanBakuChart');
    if (canvas3) {
        const ctx3 = canvas3.getContext('2d');
        new Chart(ctx3, {
            type: 'bar',
            data: {
                labels: bahanBakuLabels,
                datasets: [{
                    label: 'Qty (kg)',
                    data: bahanBakuValues,
                    backgroundColor: 'rgba(20, 184, 166, 0.8)',
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

    // Chart 4: Confirmed Status
    const canvas4 = document.getElementById('confirmedChart');
    if (canvas4) {
        const ctx4 = canvas4.getContext('2d');
        new Chart(ctx4, {
            type: 'pie',
            data: {
                labels: ['Confirmed', 'Unconfirmed'],
                datasets: [{
                    data: [confirmedWipIn, unconfirmedWipIn],
                    backgroundColor: [
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
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
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: {
                                size: 11
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
