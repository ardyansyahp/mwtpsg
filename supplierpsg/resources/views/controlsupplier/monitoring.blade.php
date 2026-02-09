@extends('layout.app')

@push('styles')
<style>
    /* ===== TABLE MATRIX STYLES ===== */
    .item-matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        margin: 0;
        font-size: 10px;
    }
    
    .item-matrix-table th,
    .item-matrix-table td {
        border: 1px solid #ddd;
        padding: 4px;
        line-height: 1.2;
        font-size: 10px;
        vertical-align: middle;
        background-color: #fff; /* Default background */
    }
    
    .item-matrix-table thead th {
        padding: 6px 4px;
        font-weight: 700;
        color: white;
        background-color: #2196f3;
    }
    
    /* ===== BADGE STYLES ===== */
    .badge {
        padding: 3px 6px;
        border-radius: 3px;
        font-size: 9px;
        font-weight: 600;
        display: inline-block;
    }
    
    .badge-open { background-color: #fff9c4; color: #f57f17; }
    .badge-pending { background-color: #ffcdd2; color: #c62828; }
    .badge-close { background-color: #c8e6c9; color: #2e7d32; }
    .weekend { background-color: #fff9c4 !important; }
    
    /* ===== STICKY / FROZEN COLUMNS ===== */
    /* Z-Index Hierarchy:
       Header Frozen > Header Normal
       Body Frozen > Body Normal
    */
    
    /* Sticky Header (Vertical Freeze) */
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    /* Sticky Columns (Horizontal Freeze) */
    .sticky-col-supplier {
        position: sticky;
        left: 0;
        z-index: 5;
        min-width: 120px;
        width: 120px;
        max-width: 120px;
        border-right: 2px solid #1976d2 !important;
        background-color: #fff !important;
    }
    
    .sticky-col-item {
        position: sticky;
        left: 120px; /* Width of Supplier */
        z-index: 5;
        min-width: 180px;
        width: 180px;
        max-width: 180px;
        border-right: 2px solid #1976d2 !important;
        background-color: #fff !important;
    }
    
    .sticky-col-point {
        position: sticky;
        left: 300px; /* 120 + 180 */
        z-index: 5;
        min-width: 85px;
        width: 85px;
        max-width: 85px;
        border-right: 2px solid #1976d2 !important;
        background-color: #fff !important;
    }
    
    /* Intersection: Header + Frozen Column (Paling Atas) */
    th.sticky-col-supplier.sticky-header,
    th.sticky-col-item.sticky-header,
    th.sticky-col-point.sticky-header {
        z-index: 20; /* Higher than normal header(10) and normal frozen col(5) */
        background-color: #1976d2 !important; /* Darker blue for corner headers */
    }
    
    /* Fix: Ensure alternating row colors apply to sticky columns too */
    .item-matrix-table tbody tr:nth-child(even) td {
        background-color: #fff;
    }
    .item-matrix-table tbody tr:nth-child(even) .sticky-col-supplier,
    .item-matrix-table tbody tr:nth-child(even) .sticky-col-item,
    .item-matrix-table tbody tr:nth-child(even) .sticky-col-point {
        background-color: #fff;
    }

    .item-matrix-table tbody tr:nth-child(odd) td {
        background-color: #f9fafb;
    }
    .item-matrix-table tbody tr:nth-child(odd) .sticky-col-supplier,
    .item-matrix-table tbody tr:nth-child(odd) .sticky-col-item,
    .item-matrix-table tbody tr:nth-child(odd) .sticky-col-point {
        background-color: #f9fafb;
    }
    
    /* Point Column Specific Coloring Logic Override */
    /* Kita perlu memastikan background color Point C konsisten dengan rules PHP, 
       tapi karena ini sticky, kita set default row color dulu.
       Warna spesifik cell akan di-override inline style dari PHP view.
    */
    
    /* ===== SCROLL CONTAINER ===== */
    /* Container ini membungkus tabel dan meng-handle overflow */
    .table-container {
        overflow: auto;
        width: 100%;
        height: 100%;
        position: relative;
        /* Scrollbar styles */
        scrollbar-width: thin;
        scrollbar-color: #888 #f1f1f1;
    }
    
    .table-container::-webkit-scrollbar {
        height: 10px;
        width: 10px;
    }
    .table-container::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    .table-container::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    /* ===== FULLSCREEN MODE ===== */
    .fullscreen-mode {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: white !important;
        z-index: 2147483647 !important; /* Max Z-Index agar di atas segalanya (sidebar/header) */
        display: flex;
        flex-direction: column;
        padding: 0;
        margin: 0;
    }

    .fullscreen-header {
        padding: 10px 20px;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 15px;
        flex-shrink: 0;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    
    /* ===== EDITABLE CELLS ===== */
    .editable-cell {
        cursor: pointer;
        background: #f0f8ff;
        position: relative;
    }
    
    .editable-cell:hover {
        background: #e3f2fd;
    }
    
    .editable-cell.editing {
        background: #fff9c4;
        border: 2px solid #2196f3 !important;
        padding: 2px;
    }
    
    .editable-cell input {
        width: 100%;
        border: none;
        padding: 2px;
        font-size: 10px;
        box-sizing: border-box;
        background: transparent;
        outline: none;
    }
    
    .edit-icon {
        font-size: 10px;
        margin-right: 4px;
        color: #2196f3;
    }
    
    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    }
    
    .loading-spinner {
        background: white;
        padding: 20px 40px;
        border-radius: 8px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Hide body scroll when fullscreen */
    body.fullscreen-active {
        overflow: hidden !important;
    }
    
    /* Hide sidebar and header when fullscreen */
    body.fullscreen-active .sidebar,
    body.fullscreen-active .header,
    body.fullscreen-active nav,
    body.fullscreen-active aside {
        display: none !important;
    }

</style>
@endpush

@section('content')
<div id="app">
    <!-- Normal Mode Header -->
    <div v-if="!isFullscreen" class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between gap-4">
            <!-- Title -->
            <h1 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chart-bar text-blue-600"></i>
                <span>Monitoring Delivery per Item</span>
            </h1>
            
            <!-- Filters and Actions -->
            <div class="flex items-center gap-3">
                <!-- Filter Periode -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-gray-700 flex items-center gap-1">
                        <i class="fas fa-calendar text-blue-600"></i>
                        <span>Periode:</span>
                    </label>
                    <input 
                        type="month" 
                        v-model="periode" 
                        @change="loadData"
                        class="border border-gray-300 px-3 py-1.5 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                
                <!-- Filter Kategori -->
                <div class="flex items-center gap-2">
                    <label class="text-sm font-semibold text-gray-700 flex items-center gap-1">
                        <i class="fas fa-box text-orange-600"></i>
                        <span>Kategori:</span>
                    </label>
                    <select 
                        v-model="kategori" 
                        @change="loadData"
                        class="border border-gray-300 px-3 py-1.5 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Semua Kategori</option>
                        <option value="material">Material & Masterbatch</option>
                        <option value="subpart">Subpart</option>
                        <option value="layer">Layer</option>
                        <option value="polybag">Polybag</option>
                        <option value="box">Box</option>
                        <option value="rempart">Rempart</option>
                    </select>
                </div>
                
                <!-- Tombol Refresh -->
                <button 
                    @click="loadData" 
                    :disabled="loading" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 rounded-lg text-sm transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center gap-1.5"
                >
                    <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
                    <span v-if="loading">Memuat...</span>
                    <span v-else>Refresh</span>
                </button>
                
                <!-- Tombol Maximize -->
                <button 
                    @click="toggleFullscreen" 
                    :class="isFullscreen ? 'bg-orange-500 hover:bg-orange-600' : 'bg-green-600 hover:bg-green-700'"
                    class="text-white px-4 py-1.5 rounded-lg text-sm transition-colors flex items-center gap-1.5"
                    :title="isFullscreen ? 'Exit Fullscreen (ESC)' : 'Maximize Table'"
                >
                    <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                    <span v-if="isFullscreen">Exit</span>
                    <span v-else>Maximize</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Fullscreen Mode -->
    <div v-if="isFullscreen" class="fullscreen-mode">
        <div class="fullscreen-header">
            <h2 class="text-lg font-bold text-blue-700 m-0">
                <i class="fas fa-chart-bar"></i> Monitoring Delivery per Item
            </h2>
            
            <!-- Filter Periode (Fullscreen) -->
            <div class="flex items-center gap-2">
                <label class="font-semibold text-sm">
                    <i class="fas fa-calendar"></i> Periode:
                </label>
                <input 
                    type="month" 
                    v-model="periode" 
                    @change="loadData"
                    class="border px-2 py-1 rounded text-sm"
                >
            </div>
            
            <!-- Filter Kategori (Fullscreen) -->
            <div class="flex items-center gap-2">
                <label class="font-semibold text-sm">
                    <i class="fas fa-box"></i> Kategori:
                </label>
                <select 
                    v-model="kategori" 
                    @change="loadData"
                    class="border px-2 py-1 rounded text-sm"
                >
                    <option value="">Semua Kategori</option>
                    <option value="material">Material & Masterbatch</option>
                    <option value="subpart">Subpart</option>
                    <option value="layer">Layer</option>
                    <option value="polybag">Polybag</option>
                    <option value="box">Box</option>
                    <option value="rempart">Rempart</option>
                </select>
            </div>
            
            <!-- Tombol Refresh (Fullscreen) -->
            <button 
                @click="loadData" 
                :disabled="loading" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm disabled:bg-gray-400"
            >
                <i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'"></i>
                <span v-if="loading"> Memuat...</span>
                <span v-else> Refresh</span>
            </button>
            
            <!-- Tombol Exit Fullscreen -->
            <button 
                @click="toggleFullscreen" 
                class="bg-orange-500 hover:bg-orange-600 text-white px-3 py-1 rounded text-sm"
            >
                <i class="fas fa-compress"></i> Exit (ESC)
            </button>
            
            <div class="ml-auto text-xs text-gray-600 italic">
                Tekan <strong>ESC</strong> untuk keluar
            </div>
        </div>
        
        <div class="table-container" ref="tableContainer">
            @include('controlsupplier.partials.table')
        </div>
    </div>
    
    <!-- Normal Mode Table -->
    <div v-else class="overflow-auto" style="max-height: calc(100vh - 200px);" ref="tableContainer">
        @include('controlsupplier.partials.table')
    </div>
    
    <!-- Loading Overlay -->
    <div v-if="saving" class="loading-overlay">
        <div class="loading-spinner">
            <div class="text-center">
                <div class="text-2xl mb-2">
                    <i class="fas fa-save text-blue-600"></i>
                </div>
                <div class="font-semibold">Menyimpan...</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/vue@3/dist/vue.global.js"></script>
<script>
    let appInstance = null;

    function initMatrixApp() {
        if (!document.getElementById('app')) return;
        
        const { createApp } = Vue;
        
        if (appInstance) {
            appInstance = null;
        }

        appInstance = createApp({
            data() {
                return {
                    periode: '{{ $periode }}',
                    kategori: '{{ request('kategori', '') }}',
                    items: @json($items),
                    dates: @json($dates),
                    loading: false,
                    saving: false,
                    isFullscreen: false,
                    editingCell: null,
                    scrollPosition: { x: 0, y: 0 }
                };
            },
            
            methods: {
                async loadData() {
                    this.loading = true;
                    
                    // Save scroll position
                    if (this.$refs.tableContainer) {
                        this.scrollPosition = {
                            x: this.$refs.tableContainer.scrollLeft,
                            y: this.$refs.tableContainer.scrollTop
                        };
                    }
                    
                    try {
                        const params = new URLSearchParams();
                        params.set('periode', this.periode);
                        if (this.kategori) {
                            params.set('kategori', this.kategori);
                        }
                        window.location.href = `?${params.toString()}`;
                    } catch (error) {
                        console.error('Error loading data:', error);
                        alert('Gagal memuat data');
                    } finally {
                        this.loading = false;
                        this.$nextTick(() => {
                            this.setupFrozenColumns();
                        });
                    }
                },
                
                toggleFullscreen() {
                    this.isFullscreen = !this.isFullscreen;
                    
                    if (this.isFullscreen) {
                        document.body.classList.add('overflow-hidden');
                        document.body.classList.add('fullscreen-active');
                    } else {
                        document.body.classList.remove('overflow-hidden');
                        document.body.classList.remove('fullscreen-active');
                    }
                    
                    this.$nextTick(() => {
                        this.setupFrozenColumns();
                    });
                },
                
                async updatePONumb(item, date, value) {
                    if (!value || value.trim() === '') {
                        alert('PO Number tidak boleh kosong!');
                        return;
                    }
                    
                    this.saving = true;
                    
                    try {
                        const response = await fetch('/controlsupplier/update-ponumb', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                bahan_baku_id: item.bahan_baku_id,
                                supplier_id: item.supplier_id,
                                periode: this.periode,
                                tanggal: date,
                                ponumb: value.trim()
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            await this.loadData();
                        } else {
                            alert('Gagal menyimpan: ' + (data.error || 'Unknown error'));
                        }
                    } catch (error) {
                        alert('Gagal menyimpan: ' + error.message);
                    } finally {
                        this.saving = false;
                        this.editingCell = null;
                    }
                },
                
                async updatePlan(item, date, value, ponumb) {
                    const qty = parseFloat(value) || 0;
                    
                    if (!ponumb || ponumb === '-') {
                        alert('Silakan input PO Number terlebih dahulu sebelum input Plan!');
                        this.editingCell = null;
                        return;
                    }
                    
                    this.saving = true;
                    
                    try {
                        const response = await fetch('/controlsupplier/update-plan', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                bahan_baku_id: item.bahan_baku_id,
                                supplier_id: item.supplier_id,
                                periode: this.periode,
                                tanggal: date,
                                qty: qty,
                                ponumb: ponumb
                            })
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            await this.loadData();
                        } else {
                            alert('Gagal menyimpan: ' + (data.error || 'Unknown error'));
                        }
                    } catch (error) {
                        alert('Gagal menyimpan: ' + error.message);
                    } finally {
                        this.saving = false;
                        this.editingCell = null;
                    }
                },
                
                startEdit(cellKey) {
                    this.editingCell = cellKey;
                    this.$nextTick(() => {
                        const input = document.querySelector(`input[data-cell="${cellKey}"]`);
                        if (input) {
                            input.focus();
                            input.select();
                        }
                    });
                },
                
                formatNumber(value) {
                    if (!value || value === 0) return '-';
                    return parseFloat(value).toLocaleString('id-ID');
                },
                
                calculateFrequency(item) {
                    let freqPlan = 0;
                    let freqAct = 0;
                    
                    this.dates.forEach(dateObj => {
                        const dateStr = dateObj.date.split('T')[0];
                        const daily = item.daily_details[dateStr];
                        if (daily) {
                            if (daily.plan > 0) freqPlan++;
                            if (daily.act > 0) freqAct++;
                        }
                    });
                    
                    const freqAr = (freqPlan + freqAct) > 0 ? (freqPlan + freqAct) / 2 : 0;
                    const freqBlc = item.total_plan > 0 ? (item.total_blc / item.total_plan) * 100 : 0;
                    
                    let freqGrade = '-';
                    if (freqAr === 0) {
                        freqGrade = '-';
                    } else if (freqAr === 1) {
                        freqGrade = 'A';
                    } else if (freqAr < 0.6) {
                        freqGrade = 'D';
                    } else if (freqAr < 0.8) {
                        freqGrade = 'C';
                    } else if (freqAr < 1) {
                        freqGrade = 'B';
                    } else {
                        freqGrade = 'A';
                    }
                    
                    return { freqPlan, freqAct, freqBlc, freqAr, freqGrade };
                },
                
                setupFrozenColumns() {
                    this.$nextTick(() => {
                        const table = document.querySelector('.item-matrix-table');
                        if (!table) return;
                        
                        const supplierCols = table.querySelectorAll('.sticky-col-supplier');
                        const itemCols = table.querySelectorAll('.sticky-col-item');
                        const pointCols = table.querySelectorAll('.sticky-col-point');
                        
                        if (supplierCols.length > 0) {
                            const supplierWidth = supplierCols[0].offsetWidth;
                            
                            itemCols.forEach(cell => {
                                cell.style.left = supplierWidth + 'px';
                            });
                            
                            if (itemCols.length > 0) {
                                const itemWidth = itemCols[0].offsetWidth;
                                pointCols.forEach(cell => {
                                    cell.style.left = (supplierWidth + itemWidth) + 'px';
                                });
                            }
                        }
                    });
                }
            },
            
            mounted() {
                // ESC key handler
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && this.isFullscreen) {
                        this.toggleFullscreen();
                    }
                });
                
                // Setup frozen columns
                this.setupFrozenColumns();
                
                // Resize handler
                window.addEventListener('resize', () => {
                    this.setupFrozenColumns();
                });
                
                // Restore scroll position
                this.$nextTick(() => {
                    if (this.$refs.tableContainer && this.scrollPosition.x > 0) {
                        this.$refs.tableContainer.scrollLeft = Math.max(0, this.scrollPosition.x);
                        this.$refs.tableContainer.scrollTop = this.scrollPosition.y;
                    }
                    
                    // Prevent scrolling to the left of frozen columns
                    if (this.$refs.tableContainer) {
                        // Initial reset
                        this.$refs.tableContainer.scrollLeft = Math.max(0, this.$refs.tableContainer.scrollLeft);
                        
                        // Continuous monitoring
                        this.$refs.tableContainer.addEventListener('scroll', (e) => {
                            if (e.target.scrollLeft < 0) {
                                requestAnimationFrame(() => {
                                    e.target.scrollLeft = 0;
                                });
                            }
                        }, { passive: false });
                        
                        // Also prevent on wheel event
                        this.$refs.tableContainer.addEventListener('wheel', (e) => {
                            const container = e.currentTarget;
                            if (container.scrollLeft === 0 && e.deltaX < 0) {
                                e.preventDefault();
                            }
                        }, { passive: false });
                    }
                });
            }
        }).mount('#app');
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMatrixApp);
    } else {
        initMatrixApp();
    }
</script>
@endpush
