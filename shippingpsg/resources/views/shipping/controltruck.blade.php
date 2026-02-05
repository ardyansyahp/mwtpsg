@extends('layout.app')

@section('content')
<style>
    /* Custom Scrollbar for Truck Control Board */
    .custom-scrollbar::-webkit-scrollbar {
        height: 12px; /* Increased height for horizontal scrollbar */
        width: 12px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 6px;
        border: 2px solid #f1f1f1; /* Adds padding around thumb */
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    /* Firefox */
    .custom-scrollbar {
        scrollbar-width: auto; /* auto is wider than thin */
        scrollbar-color: #888 #f1f1f1;
    }
    [v-cloak] { display: none !important; }
    
    /* Fullscreen Mode */
    .fullscreen-mode {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 999999 !important;
        background: #fff !important;
        display: flex !important;
        flex-direction: column !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    .fullscreen-header {
        padding: 10px 20px;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
    }
</style>
<div id="app" v-cloak :class="['flex flex-col bg-white h-full', isFullscreen ? '' : 'shadow-sm rounded-lg overflow-hidden border border-gray-200']">
    {{-- Header Section --}}
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50" v-if="!isFullscreen">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <span>🚚</span> Control Truck
                </h1>
                <p class="text-gray-500 text-sm mt-1">Monitoring jadwal dan status truck</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 flex items-center gap-2 shadow-sm">
                    <span class="text-gray-500 text-sm font-medium">📅 Tanggal:</span>
                    <input type="date" 
                           v-model="tanggal" 
                           @change="loadData"
                           class="bg-transparent border-none text-sm font-medium text-gray-700 focus:ring-0 cursor-pointer p-0">
                </div>

                <div class="bg-white border border-gray-300 rounded-lg px-3 py-2 flex items-center gap-2 shadow-sm">
                    <span class="text-gray-500 text-sm font-medium">🔍 Plat:</span>
                    <input type="text" 
                           v-model="nomor_plat" 
                           @input="debounceLoad"
                           placeholder="Cari Plat..."
                           class="bg-transparent border-none text-sm font-medium text-gray-700 focus:ring-0 p-0 w-24">
                </div>
                
                <button @click="loadData" 
                        :disabled="loading" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 disabled:bg-blue-400 shadow-sm">
                    <span v-if="loading" class="animate-spin">⏳</span>
                    <span v-else>🔄</span>
                    <span>Refresh</span>
                </button>
                
                <button @click="toggleFullscreen" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center gap-2 shadow-sm"
                        title="Maximize Table">
                    <span>⤢</span>
                    <span>Maximize</span>
                </button>
             </div>
        </div>
    </div>
    
    {{-- Fullscreen Mode --}}
    <Teleport to="body">
        <div v-if="isFullscreen" class="fullscreen-mode">
            <div class="fullscreen-header">
                 <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                     <span>🚚</span> Control Truck
                 </h2>
                 
                 <div class="flex items-center gap-3 ml-auto">
                    {{-- Date Filter in Fullscreen --}}
                    <div class="bg-white border border-gray-300 rounded px-2 py-1 flex items-center gap-2 shadow-sm">
                        <span class="text-gray-500 text-sm font-medium">📅</span>
                        <input type="date" 
                               v-model="tanggal" 
                               @change="loadData"
                               class="bg-transparent border-none text-sm font-medium text-gray-700 focus:ring-0 cursor-pointer p-0 h-6">
                    </div>

                    <div class="bg-white border border-gray-300 rounded px-2 py-1 flex items-center gap-2 shadow-sm">
                        <span class="text-gray-500 text-[10px] font-medium">🔍</span>
                        <input type="text" 
                               v-model="nomor_plat" 
                               @input="debounceLoad"
                               placeholder="Plat..."
                               class="bg-transparent border-none text-xs font-medium text-gray-700 focus:ring-0 p-0 h-4 w-20">
                    </div>
                    
                    <button @click="loadData" 
                            :disabled="loading" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded text-sm font-medium transition-colors flex items-center gap-2 disabled:bg-blue-400">
                        <span v-if="loading" class="animate-spin">⏳</span>
                        <span v-else>🔄</span>
                        <span>Refresh</span>
                    </button>
                    
                    <button @click="toggleFullscreen" 
                            class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1.5 rounded text-sm font-medium transition-colors flex items-center gap-2 shadow-sm"
                            title="Exit Fullscreen (ESC)">
                        <span>⤢</span>
                        <span>Exit</span>
                    </button>
                 </div>
            </div>
            
            <div class="flex-1 overflow-auto custom-scrollbar bg-white" ref="tableContainer">
                <div id="tableWrapper" class="min-w-max h-full"> 
                    @include('shipping.partials.controltruck-table')
                </div>
            </div>
        </div>
    </Teleport>

    {{-- Normal Table Container --}}
    <div v-else class="relative bg-white flex-1">
         <div class="w-full overflow-auto custom-scrollbar" 
              style="max-height: calc(100vh - 250px); min-height: 500px;"
              ref="tableContainer">
            <div id="tableWrapper" class="min-w-max">
                @include('shipping.partials.controltruck-table')
            </div>
        </div>
    </div>
    
    {{-- Loading overlay (Toast style) --}}
    <div v-if="saving" class="fixed bottom-4 right-4 z-[10000] pointer-events-none">
        <div class="bg-gray-800 text-white px-4 py-2 rounded-lg shadow-xl flex items-center gap-3 animate-slide-up">
            <div class="text-xl animate-spin">⟳</div>
            <div class="font-medium text-sm">Menyimpan...</div>
        </div>
    </div>

    {{-- Error Toast --}}
    <div v-if="errorMessage" class="fixed bottom-4 right-4 z-[10000] pointer-events-none">
        <div class="bg-red-600 text-white px-4 py-2 rounded-lg shadow-xl flex items-center gap-3 animate-slide-up">
            <div class="text-xl">⚠️</div>
            <div class="font-medium text-sm">@{{ errorMessage }}</div>
        </div>
    </div>

    </div>
@endsection

<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
<script>
(function() {
    // Tunggu sampai Vue.js ter-load
    function initVueApp() {
        if (typeof Vue === 'undefined') {
            setTimeout(initVueApp, 50);
            return;
        }
        
        const { createApp } = Vue;

        const app = createApp({
    data() {
        return {
            tanggal: '{{ request("tanggal", date("Y-m-d")) }}',
            nomor_plat: '{{ request("nomor_plat", "") }}',
            trucks: @json($trucks ?? []),
            loading: false,
            saving: false,
            errorMessage: null, // Add error state
            isFullscreen: false,
            debounceTimer: null
        };
    },
    methods: {
        showError(msg) {
            this.errorMessage = msg;
            setTimeout(() => {
                this.errorMessage = null;
            }, 3000);
        },
        debounceLoad() {
            clearTimeout(this.debounceTimer);
            this.debounceTimer = setTimeout(() => {
                this.loadData();
            }, 500);
        },
        async loadData() {
            this.loading = true;
            
            try {
                // Update URL tanpa reload halaman
                const params = new URLSearchParams();
                params.set('tanggal', this.tanggal);
                if (this.nomor_plat) params.set('nomor_plat', this.nomor_plat);
                const newUrl = window.location.pathname + '?' + params.toString();
                window.history.pushState({ path: newUrl }, '', newUrl);
                
                // AJAX fetch untuk update tabel - gunakan full URL dengan query params
                const url = `/shipping/controltruck/monitoring?${params.toString()}`;
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                });
                
                if (!response.ok) {
                    throw new Error('Failed to load data: ' + response.status);
                }
                
                // Parse response sebagai HTML
                const html = await response.text();
                
                // Extract tabel dari HTML response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Cari wrapper tabel atau tabel itu sendiri
                const responseWrapper = doc.querySelector('#tableWrapper');
                let newTable = null;
                if (responseWrapper) {
                    newTable = responseWrapper.querySelector('.truck-control-table');
                } else {
                    newTable = doc.querySelector('.truck-control-table');
                }
                
                const tableContainer = this.isFullscreen 
                    ? this.$refs.tableContainer 
                    : document.querySelector('.overflow-auto');
                
                if (newTable && tableContainer) {
                    // Ganti tabel lama dengan yang baru
                    const tableWrapper = tableContainer.querySelector('#tableWrapper');
                    if (tableWrapper) {
                        // Ganti konten wrapper
                        const oldTable = tableWrapper.querySelector('.truck-control-table');
                        if (oldTable) {
                            oldTable.replaceWith(newTable.cloneNode(true));
                        } else {
                            tableWrapper.innerHTML = '';
                            tableWrapper.appendChild(newTable.cloneNode(true));
                        }
                    } else {
                        // Jika tidak ada wrapper, ganti langsung
                        const oldTable = tableContainer.querySelector('.truck-control-table');
                        if (oldTable) {
                            oldTable.replaceWith(newTable.cloneNode(true));
                        } else {
                            tableContainer.innerHTML = '';
                            tableContainer.appendChild(newTable.cloneNode(true));
                        }
                    }
                } else {
                    console.warn('Table not found in response or container not found');
                }
                
                // Setup frozen columns setelah tabel diupdate
                this.$nextTick(() => {
                    this.setupFrozenColumns();
                    if (window.updateControlTruckRedLine) {
                        window.updateControlTruckRedLine();
                    }
                });
                
            } catch (error) {
                console.error('Error loading data:', error);
                alert('Gagal memuat data: ' + error.message);
            } finally {
                this.loading = false;
            }
        },
        toggleFullscreen() {
            this.isFullscreen = !this.isFullscreen;
            if (this.isFullscreen) {
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
            this.$nextTick(() => {
                this.setupFrozenColumns();
            });
        },
        setupFrozenColumns() {
            this.$nextTick(() => {
                const table = document.querySelector('.truck-control-table');
                if (!table) return;
                
                // Define classes in order of sticky columns
                const stickyClasses = [
                    '.sticky-col-truck',
                    '.sticky-col-cyc',
                    '.sticky-col-sj',
                    '.sticky-col-driver',
                    '.sticky-col-customer',
                    '.sticky-col-activity',
                    '.sticky-col-plan'
                ];
                
                let currentLeft = 0;
                
                stickyClasses.forEach(selector => {
                    const cols = table.querySelectorAll(selector);
                    if (cols.length > 0) {
                        // All cells in this "column" should have the same left offset
                        cols.forEach(cell => {
                            cell.style.left = currentLeft + 'px';
                        });
                        
                        // Increase offset by ACTUAL width of this column (from the first th or td)
                        currentLeft += cols[0].offsetWidth;
                    }
                });

                // Also update the special multi-colspan header for Plan/Actual
                const planHeader = table.querySelector('.sticky-col-plan-header');
                if (planHeader) {
                    const activityCol = table.querySelector('.sticky-col-activity');
                    if (activityCol) {
                        planHeader.style.left = activityCol.style.left;
                    }
                }
            });
        }
    },
    mounted() {
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isFullscreen) {
                this.toggleFullscreen();
            }
        });
        
        this.setupFrozenColumns();
        
        window.addEventListener('resize', () => {
            this.setupFrozenColumns();
        });
    }
        }).mount('#app');
        window.controlTruckApp = app; 
    }
    
    // Initialize saat DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initVueApp);
    } else {
        initVueApp();
    }

    
    // RED LINE LOGIC
    function updateRedLine() {
        const redLine = document.getElementById('red-line');
        const timeLabel = document.getElementById('red-line-time');
        const table = document.querySelector('.truck-control-table');
        
        if (!redLine || !timeLabel || !table) return;
        
        const now = new Date();
        const hours = now.getHours();
        const minutes = now.getMinutes();
        const currentTime = `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`;
        
        // Show only if within timeline range (07:00 - 23:59)
        if (hours < 7 || hours >= 24) {
            redLine.classList.add('hidden');
            return;
        }
        
        redLine.classList.remove('hidden');
        timeLabel.textContent = currentTime;
        
        // Calculate Offset dynamically based on the 07.00 column position
        const headerRow2 = table.querySelector('thead tr:nth-child(2)');
        if (!headerRow2) return;
        
        // The time columns start after: Activity, Plan/Actual (indices 0, 1 in row 2)
        // Index 2 is "07.00" (the 3rd TH)
        const firstTimeCol = headerRow2.querySelector('th:nth-child(3)');
        if (!firstTimeCol) return;

        const baseLeft = firstTimeCol.offsetLeft;
        const colWidth = firstTimeCol.offsetWidth;
        
        const startHour = 7;
        const diffMinutes = ((hours - startHour) * 60) + minutes;
        
        // Pixels from start of 07:00 column
        const pixelsFromStart = (diffMinutes / 60) * colWidth;
        
        // Total position
        const position = baseLeft + pixelsFromStart;
        
        redLine.style.left = `${position}px`;
    }

    // Initialize Red Line
    setInterval(updateRedLine, 60000); // Every minute
    // Run immediately and after table load
    setTimeout(updateRedLine, 500); 
    
    // Make it global to call from Vue
    window.updateControlTruckRedLine = updateRedLine;
    
    // Restore cell function (original)
    function restoreCell(cell, content) {
        cell.classList.remove('editing');
        if (content) {
            cell.innerHTML = content;
        } else {
             // Try to recover content from attributes if not passed
             const name = cell.getAttribute('data-current-name') || '-';
             if (cell.classList.contains('editable-customer-cell')) {
                 cell.innerHTML = `<div style="font-weight: 600; font-size: 10px;">${name}</div>`;
             } else {
                 cell.innerHTML = name;
             }
        }
    }
})();
</script>
</div>
