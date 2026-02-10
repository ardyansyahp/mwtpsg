@extends('layout.app')

@push('styles')
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
@endpush

@section('content')
{{-- Native Loading Overlay (Before Vue Mounts) --}}
<div id="initial-loader" class="fixed inset-0 z-[99999] bg-white flex flex-col items-center justify-center gap-4 transition-opacity duration-500">
    <div class="animate-spin text-4xl">🚚</div>
    <div class="text-gray-500 font-medium animate-pulse">Memuat Control Truck...</div>
</div>

<div id="app">
    <Teleport to="body">
        <div class="fullscreen-mode flex flex-col h-full bg-white">
            
            {{-- Header --}}
            <div class="fullscreen-header shadow-sm z-[100] flex items-center px-4 py-2 border-b bg-gray-50">
                <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <span>🚚</span> Control Truck
                </h1>
                <div class="flex-1"></div>
                
                {{-- Controls --}}
                <div class="flex items-center gap-3">
                     {{-- Date Filter --}}
                     <div class="bg-white border border-gray-300 rounded px-2 py-1 flex items-center gap-2 text-sm">
                        <span class="text-gray-500">📅</span>
                        <input type="date" v-model="tanggal" @change="loadData" class="border-none p-0 text-sm focus:ring-0">
                    </div>
    
                    {{-- Search Plat --}}
                <div class="bg-white border border-gray-300 rounded px-2 py-1 flex items-center gap-2 text-sm shadow-sm relative group focus-within:ring-1 ring-blue-200">
                    <span class="text-gray-500 text-xs">🔍</span>
                    <input type="text" 
                           v-model="nomor_plat" 
                           @input="debounceLoad"
                           placeholder="Cari Plat..."
                           class="bg-transparent border-none text-sm font-medium text-gray-700 focus:ring-0 p-0 w-24">
                    
                    {{-- Clear Search Button --}}
                    <button v-if="nomor_plat" 
                            @click="nomor_plat = ''; loadData()" 
                            class="text-gray-400 hover:text-red-500 transition-colors"
                            title="Clear Search">
                        <i class="fa-solid fa-times text-xs"></i>
                    </button>
                </div>
    
                     <button @click="loadData" :disabled="loading" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition-colors flex items-center gap-2">
                        <span v-if="loading" class="animate-spin">⏳</span>
                        <span v-else>🔄</span>
                    </button>
                    
                    {{-- Close Button (Redirects to Dashboard/Home since normal view is gone) --}}
                    <a href="{{ url('/') }}" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm transition-colors flex items-center gap-2 decoration-0">
                        <span>❌</span> Close
                    </a>
                </div>
            </div>
    
            {{-- Scrollable Table Area --}}
            <div class="w-full overflow-auto custom-scrollbar flex-1 relative" 
                 style="max-height: 100vh !important; min-height: 0 !important;"
                 ref="tableContainer">
                <div id="tableWrapper" class="min-w-max h-full">
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
    </Teleport>
</div>

    <script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
    <script>
    (function() {
        console.log("Truck Monitoring Script Initializing...");
        
        function initVueApp() {
            if (typeof Vue === 'undefined') {
                console.log("Waiting for Vue CDN...");
                setTimeout(initVueApp, 50);
                return;
            }
            
            console.log("Vue.js loaded, mounting app...");
            const { createApp } = Vue;

            try {
                const app = createApp({
                    data() {
                        return {
                            tanggal: '{{ request("tanggal", date("Y-m-d")) }}',
                            nomor_plat: new URLSearchParams(window.location.search).get('nomor_plat') || '{{ request("nomor_plat", "") }}',
                            trucks: @json($trucks ?? []),
                            loading: false,
                            saving: false,
                            errorMessage: null,
                            isFullscreen: true,
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
                            }, 300);
                        },
                        async loadData() {
                            this.loading = true;
                            console.log("Loading data for date:", this.tanggal);
                            
                            try {
                                const params = new URLSearchParams();
                                params.set('tanggal', this.tanggal);
                                if (this.nomor_plat) params.set('nomor_plat', this.nomor_plat);
                                // Add timestamp to prevent caching
                                params.set('_t', new Date().getTime());
                                
                                const newUrl = window.location.pathname + '?' + params.toString();
                                // Only update history if not triggered by popstate (optional but good practice)
                                // actually pushState is fine here.
                                
                                // Clean params for history (remove _t) to keep URL clean
                                const historyParams = new URLSearchParams(params);
                                historyParams.delete('_t');
                                window.history.pushState({ path: newUrl }, '', window.location.pathname + '?' + historyParams.toString());
                                
                                const url = `{{ url('shipping/controltruck/monitoring') }}?${params.toString()}`;
                                const response = await fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'text/html'
                                    }
                                });
                                
                                if (!response.ok) throw new Error('Failed to load data: ' + response.status);
                                
                                const html = await response.text();
                                const parser = new DOMParser();
                                const doc = parser.parseFromString(html, 'text/html');
                                
                                const responseWrapper = doc.querySelector('#tableWrapper');
                                let newTable = responseWrapper ? responseWrapper.querySelector('.truck-control-table') : doc.querySelector('.truck-control-table');
                                
                                // Since we always use fullscreen teleport now, target the container inside it explicitly
                                // Note: $refs inside Teleport works, but let's be safe with selector if ref fails
                                const tableContainer = this.$refs.tableContainer || document.querySelector('.fullscreen-mode .custom-scrollbar');
                                
                                if (newTable && tableContainer) {
                                    const tableWrapper = tableContainer.querySelector('#tableWrapper');
                                    if (tableWrapper) {
                                        tableWrapper.innerHTML = ''; // Clear old content first
                                        tableWrapper.appendChild(newTable.cloneNode(true));
                                    } else {
                                        tableContainer.innerHTML = '<div id="tableWrapper" class="min-w-max h-full"></div>';
                                        tableContainer.querySelector('#tableWrapper').appendChild(newTable.cloneNode(true));
                                    }
                                }
                                
                                this.$nextTick(() => {
                                    this.setupFrozenColumns();
                                    if (window.updateControlTruckRedLine) window.updateControlTruckRedLine();
                                });
                                
                            } catch (error) {
                                console.error('Error loading data:', error);
                                this.showError('Gagal memuat data: ' + error.message);
                            } finally {
                                this.loading = false;
                            }
                        },
                        toggleFullscreen() {
                            // Ensure body overflow is handled
                            document.body.classList.toggle('overflow-hidden', this.isFullscreen);
                            this.$nextTick(() => this.setupFrozenColumns());
                        },
                        setupFrozenColumns() {
                            this.$nextTick(() => {
                                const table = document.querySelector('.truck-control-table');
                                if (!table) return;
                                
                                const stickyClasses = [
                                    '.sticky-col-truck', '.sticky-col-cyc', '.sticky-col-sj',
                                    '.sticky-col-driver', '.sticky-col-customer', '.sticky-col-activity',
                                    '.sticky-col-plan'
                                ];
                                
                                let currentLeft = 0;
                                stickyClasses.forEach(selector => {
                                    const cols = table.querySelectorAll(selector);
                                    if (cols.length > 0) {
                                        cols.forEach(cell => cell.style.left = currentLeft + 'px');
                                        currentLeft += cols[0].offsetWidth;
                                    }
                                });

                                const planHeader = table.querySelector('.sticky-col-plan-header');
                                if (planHeader) {
                                    const activityCol = table.querySelector('.sticky-col-activity');
                                    if (activityCol) planHeader.style.left = activityCol.style.left;
                                }
                            });
                        }
                    },
                    mounted() {
                        console.log("App mounted successfully.");
                        // Hide initial loader
                        const loader = document.getElementById('initial-loader');
                        if (loader) {
                            loader.style.opacity = '0';
                            setTimeout(() => loader.remove(), 500);
                        }

                        // ESC to Home
                        document.addEventListener('keydown', (e) => {
                            if (e.key === 'Escape') {
                                window.location.href = "{{ url('/') }}";
                            }
                        });
                        
                        this.setupFrozenColumns();
                        window.addEventListener('resize', this.setupFrozenColumns);
                    }
                }).mount('#app');
                
                window.controlTruckApp = app; 
            } catch (mountError) {
                console.error("Vue mount error:", mountError);
            }
        }
        
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
            
            if (hours < 7 || hours >= 24) {
                redLine.classList.add('hidden');
                return;
            }
            
            redLine.classList.remove('hidden');
            timeLabel.textContent = currentTime;
            
            const headerRow2 = table.querySelector('thead tr:nth-child(2)');
            if (!headerRow2) return;
            
            const firstTimeCol = headerRow2.querySelector('th:nth-child(3)');
            if (!firstTimeCol) return;

            const baseLeft = firstTimeCol.offsetLeft;
            const colWidth = firstTimeCol.offsetWidth;
            const startHour = 7;
            const diffMinutes = ((hours - startHour) * 60) + minutes;
            const pixelsFromStart = (diffMinutes / 60) * colWidth;
            const position = baseLeft + pixelsFromStart;
            
            redLine.style.left = `${position}px`;
        }

        setInterval(updateRedLine, 60000);
        setTimeout(updateRedLine, 500); 
        window.updateControlTruckRedLine = updateRedLine;
    })();
    </script>
</div>
@endsection
