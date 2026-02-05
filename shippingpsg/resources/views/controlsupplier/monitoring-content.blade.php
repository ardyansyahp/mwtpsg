@extends('layout.app')

@section('content')
<div style="min-height: calc(100vh - 200px);">
<style>
    /* ===== BASE TABLE STYLES ===== */
    .item-matrix-table {
        border-collapse: separate;
        border-spacing: 0;
        table-layout: auto; /* Allow auto layout but adhere to min-widths */
        width: 100%;
        font-size: 10px;
        border: 1px solid #d1d5db; /* gray-300 */
    }
    
    .item-matrix-table th,
    .item-matrix-table td {
        border-right: 1px solid #e5e7eb; /* gray-200 */
        border-bottom: 1px solid #e5e7eb; /* gray-200 */
        padding: 4px;
        line-height: 1.2;
        font-size: 10px;
        vertical-align: middle;
    }

    .item-matrix-table th {
        border-right: 1px solid #3b82f6; /* blue-500 to match header bg */
        border-bottom: 1px solid #2563eb; /* blue-600 */
    }
    
    /* ===== STICKY HEADER ===== */
    /* General Sticky Header */
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 50;
        background-color: #2563eb; /* bg-blue-600 */
        color: white;
    }

    /* Weekend Header */
    .item-matrix-table th.weekend {
        background-color: #fef08a !important; /* yellow-200 */
        color: #854d0e !important; /* yellow-800 */
        border-right: 1px solid #eab308; /* yellow-500 */
        border-bottom: 1px solid #ca8a04; /* yellow-600 */
    }

    /* ===== STICKY COLUMNS (FROZEN) ===== */
    /* 
       Z-Index Hierarchy:
       - Header Frozen (Intersection): 60
       - Header Normal: 50
       - Body Frozen: 30 (Supplier), 29 (Item), 28 (Point)
       - Body Normal: 1
    */

    /* COLUMN 1: SUPPLIER */
    .sticky-col-supplier {
        position: sticky !important;
        left: 0 !important;
        width: 120px;
        min-width: 120px;
        max-width: 120px;
    }

    .item-matrix-table thead th.sticky-col-supplier {
        z-index: 60 !important;
        background-color: #2563eb; /* blue-600 */
        color: white;
        box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .item-matrix-table tbody td.sticky-col-supplier {
        z-index: 30 !important;
        background-color: #ffffff;
        box-shadow: 1px 0 2px rgba(0,0,0,0.05);
        color: #1f2937; /* gray-800 */
    }

    /* COLUMN 2: ITEM */
    .sticky-col-item {
        position: sticky !important;
        left: 120px !important; /* Width of Supplier */
        width: 180px;
        min-width: 180px;
        max-width: 180px;
    }

    .item-matrix-table thead th.sticky-col-item {
        z-index: 59 !important;
        background-color: #2563eb; /* blue-600 */
        color: white;
        box-shadow: 1px 1px 2px rgba(0,0,0,0.1);
    }

    .item-matrix-table tbody td.sticky-col-item {
        z-index: 29 !important;
        background-color: #ffffff;
        box-shadow: 1px 0 2px rgba(0,0,0,0.05);
        color: #1f2937; /* gray-800 */
    }

    /* COLUMN 3: POINT C */
    .sticky-col-point {
        position: sticky !important;
        left: 300px !important; /* 120 + 180 */
        width: 85px;
        min-width: 85px;
        max-width: 85px;
    }

    .item-matrix-table thead th.sticky-col-point {
        z-index: 58 !important;
        background-color: #2563eb; /* blue-600 */
        color: white;
        box-shadow: 2px 1px 2px rgba(0,0,0,0.1); /* Dropshadow to right */
    }

    .item-matrix-table tbody td.sticky-col-point {
        z-index: 28 !important;
        background-color: #ffffff; /* Will be overridden by inline styles for row colors, but important default */
        box-shadow: 2px 0 2px rgba(0,0,0,0.05);
    }

    /* ===== BADGES & EDITABLES ===== */
    .badge {
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 9px;
        font-weight: 700;
        display: inline-block;
        text-transform: uppercase;
    }
    
    .badge-open { background-color: #fef9c3; color: #a16207; border: 1px solid #fde047; }
    .badge-pending { background-color: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; }
    .badge-close { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }
    .badge-over { background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; }

    .editable-cell {
        cursor: pointer;
        background: #f0f9ff; /* sky-50 */
        transition: background 0.15s;
    }
    .editable-cell:hover {
        background: #e0f2fe; /* sky-100 */
    }
    .editable-cell.editing {
        background: #fef08a !important; /* yellow-200 */
        outline: 2px solid #3b82f6; /* blue-500 */
        z-index: 100;
    }
    .editable-cell input {
        width: 100%;
        border: none;
        background: transparent;
        outline: none;
        font-family: inherit;
        font-size: inherit;
        text-align: inherit;
    }

    .edit-icon {
        font-size: 9px;
        margin-right: 2px;
        opacity: 0.5;
    }
    .editable-cell:hover .edit-icon {
        opacity: 1;
    }

    .item-matrix-table .weekend {
        background-color: #fffbeb; /* amber-50 */
    }

    /* ===== LOADING OVERLAY ===== */
    .loading-overlay {
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(2px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999;
    }
    
    .loading-overlay.active { display: flex; }
    
    .loading-spinner {
        background: white;
        padding: 20px 40px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        text-align: center;
        border: 1px solid #e5e7eb;
    }

    /* ===== FULLSCREEN MODE ===== */
    /* Note: Fullscreen logic is handled by Teleporting to Body, removing margins/padding. */
    #controlSupplierApp.fullscreen-mode {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        background: white !important;
        z-index: 999999 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    .fullscreen-header {
        padding: 10px 20px;
        background: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
        display: none;
        align-items: center;
        gap: 12px;
        flex-shrink: 0;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* Footer cleanup */
    footer {
        display: none !important;
    }

    /* ===== TODAY HIGHLIGHT ===== */
    .today-highlight {
        background-color: #eff6ff !important; /* bg-blue-50 */
        box-shadow: inset 0 0 0 1px #3b82f6;
    }
    thead th.today-highlight {
        background-color: #1d4ed8 !important; /* blue-700 */
        color: white !important;
        box-shadow: inset 0 0 0 1px #3b82f6;
    }
</style>

<!-- Placeholder for restoring position after fullscreen -->
<div id="appPlaceholder" style="display: none;"></div>
<div id="controlSupplierApp">
    <!-- Normal mode header -->
    <div id="normalHeader" class="p-6">
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📊 Monitoring Delivery per Item</h1>
            </div>
            
            <div class="flex flex-col lg:flex-row gap-3 items-start lg:items-center">
                <!-- Filters Section -->
                <div class="flex flex-wrap gap-2 items-center">
                    <div class="flex items-center gap-2 bg-white px-2 py-1 rounded-lg border shadow-sm">
                        <span class="text-gray-500 text-sm">📅</span>
                        <input type="month" 
                               id="periodeInput"
                               value="{{ $periode }}"
                               class="border-none focus:ring-0 text-sm p-1 outline-none bg-transparent">
                    </div>

                    <select id="kategoriInput"
                            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm">
                        <option value="">Semua Kategori</option>
                        <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material & Masterbatch</option>
                        <option value="subpart" {{ request('kategori') == 'subpart' ? 'selected' : '' }}>Subpart</option>
                        <option value="layer" {{ request('kategori') == 'layer' ? 'selected' : '' }}>Layer</option>
                        <option value="polybag" {{ request('kategori') == 'polybag' ? 'selected' : '' }}>Polybag</option>
                        <option value="box" {{ request('kategori') == 'box' ? 'selected' : '' }}>Box</option>
                        <option value="rempart" {{ request('kategori') == 'rempart' ? 'selected' : '' }}>Rempart</option>
                    </select>

                    <div class="relative">
                        <input type="text" 
                               id="searchInput"
                               value="{{ request('search') }}"
                               placeholder="Cari Supplier / Item..." 
                               class="pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm w-48 shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Separator -->
                <div class="hidden lg:block h-8 w-px bg-gray-300 mx-1"></div>

                <!-- Actions Section -->
                <div class="flex flex-wrap gap-2 items-center">
                    <button id="btnRefresh"
                            class="bg-blue-50 text-blue-600 border border-blue-200 px-3 py-2 rounded-lg hover:bg-blue-100 transition-colors shadow-sm flex items-center gap-1.5 font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Refresh
                    </button>

                    <a href="{{ route('controlsupplier.import.form') }}"
                       class="bg-white text-gray-700 border border-gray-300 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-1.5 font-medium text-sm"
                       title="Import Custom Excel">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Excel
                    </a>

                    <button id="btnImportSAP"
                            class="bg-white text-gray-700 border border-gray-300 px-3 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm flex items-center gap-1.5 font-medium text-sm"
                            title="Import SAP">
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                        SAP
                    </button>
                    <input type="file" id="fileInputSAP" accept=".csv,.txt" style="display: none;">

                    <form action="{{ route('controlsupplier.reset') }}" method="POST" onsubmit="return confirm('PERINGATAN: Hapus SEMUA data periode {{ $periode }}?');" class="inline">
                        @csrf
                        <input type="hidden" name="periode" value="{{ $periode }}">
                        <button type="submit" class="bg-white text-red-600 border border-red-200 px-3 py-2 rounded-lg hover:bg-red-50 transition-colors shadow-sm flex items-center gap-1.5 font-medium text-sm" title="Reset Data">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            Reset
                        </button>
                    </form>

                    <button id="btnMaximize"
                            class="bg-gray-800 text-white border border-gray-800 px-3 py-2 rounded-lg hover:bg-gray-900 transition-colors shadow-sm flex items-center gap-1.5 font-medium text-sm"
                            onclick="window.toggleFullscreenMode(); return false;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
                        Full
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fullscreen mode header (hidden by default) -->
    <div id="fullscreenHeader" class="fullscreen-header" style="display: none;">
        <h2 style="margin: 0; font-size: 18px; color: #1976d2;">📊 Monitoring Delivery per Item</h2>
        <div>
            <label class="mr-2 font-semibold" style="font-size: 13px;">📅 Periode:</label>
            <input type="month" 
                   id="periodeInputFullscreen"
                   value="{{ $periode }}"
                   style="border: 1px solid #ccc; border-radius: 4px; padding: 2px 5px; font-size: 13px;">
                   
            <label class="ml-4 mr-2 font-semibold" style="font-size: 13px;">📦 Kategori:</label>
            <select id="kategoriInputFullscreen"
                    style="border: 1px solid #ccc; border-radius: 4px; padding: 2px 5px; font-size: 13px;">
                <option value="">Semua</option>
                <option value="material" {{ request('kategori') == 'material' ? 'selected' : '' }}>Material</option>
                <option value="subpart" {{ request('kategori') == 'subpart' ? 'selected' : '' }}>Subpart</option>
                <option value="layer" {{ request('kategori') == 'layer' ? 'selected' : '' }}>Layer</option>
                <option value="polybag" {{ request('kategori') == 'polybag' ? 'selected' : '' }}>Polybag</option>
                <option value="box" {{ request('kategori') == 'box' ? 'selected' : '' }}>Box</option>
                <option value="rempart" {{ request('kategori') == 'rempart' ? 'selected' : '' }}>Rempart</option>
            </select>
            
            <button id="btnRefreshFullscreen"
                    style="margin-left: 10px; background: #2196f3; color: white; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer;"
                    onclick="window.location.reload();">
                🔄
            </button>
            <button id="btnExitFullscreen"
                    style="margin-left: 5px; background: #f44336; color: white; border: none; border-radius: 4px; padding: 4px 10px; cursor: pointer;"
                    onclick="window.toggleFullscreenMode();">
                ✖ Exit
            </button>
        </div>
    </div>
    

    
    <div id="tableContainer" class="overflow-auto" style="max-height: calc(100vh - 200px);">
        <table class="item-matrix-table">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col-supplier sticky-header" style="vertical-align: middle; font-weight: 700;">SUPPLIER</th>
                    <th rowspan="2" class="sticky-col-item sticky-header" style="vertical-align: middle; font-weight: 700;">ITEM</th>
                    <th rowspan="2" class="sticky-col-point sticky-header" style="vertical-align: middle; font-weight: 700;">POINT C</th>
                    
                    @foreach($dates as $dateInfo)
                    @php
                        $carbonDate = \Carbon\Carbon::parse($dateInfo['date']);
                    @endphp
                    <th class="sticky-header {{ $dateInfo['is_weekend'] ? 'weekend' : '' }} {{ $carbonDate->isToday() ? 'today-highlight' : '' }}" 
                        data-date="{{ $carbonDate->format('Y-m-d') }}"
                        style="min-width: 70px; width: 70px; text-align: center; padding: 4px 2px; vertical-align: middle; font-size: 9px; line-height: 1.2;">
                        <div style="font-weight: 700; font-size: 10px;">
                            {{ $carbonDate->format('d-M') }}
                        </div>
                        <div style="font-size: 8px; margin-top: 1px; font-weight: 500; opacity: 0.85;">
                            {{ $carbonDate->format('D') }}
                        </div>
                    </th>
                    @endforeach
                    
                    <th rowspan="2" class="sticky-header" style="background: #1976d2; min-width: 70px; width: 70px; vertical-align: middle; font-weight: 700;">Total</th>
                    <th rowspan="2" class="sticky-header" style="background: #1976d2; min-width: 70px; width: 70px; vertical-align: middle; font-weight: 700;">Freq</th>
                    <th rowspan="2" class="sticky-header" style="background: #1976d2; min-width: 120px; width: 120px; vertical-align: middle; font-weight: 700;">Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        // Determine UOM based on kategori
                        $uom = in_array($item['kategori'], ['material', 'masterbatch']) ? 'KG' : 'PCS';
                        
                        $rows = [
                            ['label' => "PLAN({$uom})", 'field' => 'plan', 'editable' => true, 'color' => '#fff'],
                            ['label' => "ACT({$uom})", 'field' => 'act', 'editable' => false, 'color' => '#f5f5f5'],
                            ['label' => "BLC({$uom})", 'field' => 'blc', 'editable' => false, 'color' => '#fff'],
                            ['label' => 'STATUS', 'field' => 'status', 'editable' => false, 'color' => '#f5f5f5'],
                            ['label' => 'SR(%)', 'field' => 'sr', 'editable' => false, 'color' => '#fff'],
                            ['label' => 'PO NUMB', 'field' => 'ponumb', 'editable' => true, 'color' => '#f5f5f5'],
                        ];
                        
                        // Calculate frequency
                        $freqPlan = 0;
                        $freqAct = 0;
                        foreach($dates as $dateInfo) {
                            $dateStr = $dateInfo['date']->format('Y-m-d');
                            $daily = $item['daily_details'][$dateStr] ?? null;
                            if ($daily) {
                                if ($daily['plan'] > 0) $freqPlan++;
                                if ($daily['act'] > 0) $freqAct++;
                            }
                        }
                        $freqAr = ($freqPlan + $freqAct) > 0 ? ($freqPlan + $freqAct) / 2 : 0;
                        $freqBlc = $item['total_plan'] > 0 ? ($item['total_act'] / $item['total_plan']) * 100 : 0;
                        
                        // Grade berdasarkan freq balance (dalam decimal 0-1)
                        $freqBlcDecimal = $freqBlc / 100; // Konversi persen ke decimal
                        $freqGrade = '-';
                        if ($freqBlcDecimal == 0) $freqGrade = '-';
                        elseif ($freqBlcDecimal == 1) $freqGrade = 'A';
                        elseif ($freqBlcDecimal < 0.6) $freqGrade = 'D';
                        elseif ($freqBlcDecimal < 0.8) $freqGrade = 'C';
                        elseif ($freqBlcDecimal < 1) $freqGrade = 'B';
                        else $freqGrade = 'A';
                    @endphp
                    
                    @php
                        // Pre-calculate: Cari tanggal pertama ada PLAN dan tanggal CLOSE
                        $firstPlanDate = null;
                        $closedDate = null;
                        $cumulativePlanCheck = 0;
                        $cumulativeActCheck = 0;
                        
                        foreach($dates as $preCalcDateInfo) {
                            $preCalcDateStr = $preCalcDateInfo['date']->format('Y-m-d');
                            $preCalcDaily = $item['daily_details'][$preCalcDateStr] ?? null;
                            
                            if ($preCalcDaily) {
                                $preCalcPlan = $preCalcDaily['plan'] ?? 0;
                                $preCalcAct = $preCalcDaily['act'] ?? 0;
                                
                                // Cari tanggal pertama ada PLAN
                                if ($firstPlanDate === null && $preCalcPlan > 0) {
                                    $firstPlanDate = $preCalcDateStr;
                                }
                                
                                // Cari tanggal pertama dimana ACT >= PLAN
                                if ($firstPlanDate && $preCalcDateStr >= $firstPlanDate) {
                                    $cumulativePlanCheck += $preCalcPlan;
                                    $cumulativeActCheck += $preCalcAct;
                                    
                                    if ($closedDate === null && $cumulativePlanCheck > 0 && $cumulativeActCheck >= $cumulativePlanCheck) {
                                        $closedDate = $preCalcDateStr;
                                    }
                                }
                            }
                        }
                    @endphp
                    
                    @foreach($rows as $rowIdx => $row)
                    <tr>
                        @if($rowIdx === 0)
                        <td rowspan="6" class="sticky-col-supplier" style="vertical-align: middle; font-weight: 600; padding: 6px 4px;">
                            {{ $item['supplier_name'] }}
                        </td>
                    @endif
                    
                    @if($rowIdx === 0)
                        <td rowspan="6" class="sticky-col-item" style="vertical-align: middle; padding: 6px 4px;">
                            <div style="font-weight: 600; font-size: 10px; margin-bottom: 2px;">
                                {{ $item['nomor_bahan_baku'] }}
                            </div>
                            <div style="font-size: 9px; color: #666;">
                                {{ $item['nama_bahan_baku'] }}
                            </div>
                        </td>
                        @endif
                        
                        <td class="sticky-col-point" style="background: {{ $row['color'] }}; font-weight: 600; font-size: 9px; padding: 4px;">
                            {{ $row['label'] }}
                        </td>
                        @foreach($dates as $dateIdx => $dateInfo)
                            @php
                                $dateStr = $dateInfo['date']->format('Y-m-d');
                                $isToday = $dateInfo['date']->isToday();
                                $daily = $item['daily_details'][$dateStr] ?? null;
                                $cellKey = $item['bahan_baku_id'] . '-' . $dateStr . '-' . $row['field'];
                                
                                // Hitung kumulatif sampai tanggal saat ini
                                $cumulativePlan = 0;
                                $cumulativeAct = 0;
                                foreach(array_slice($dates, 0, $dateIdx + 1) as $calcDateInfo) {
                                    $calcDateStr = date('Y-m-d', strtotime($calcDateInfo['date']));
                                    $calcDaily = $item['daily_details'][$calcDateStr] ?? null;
                                    if ($calcDaily) {
                                        $cumulativePlan += $calcDaily['plan'] ?? 0;
                                        $cumulativeAct += $calcDaily['act'] ?? 0;
                                    }
                                }
                                $cumulativeBalance = $cumulativeAct - $cumulativePlan;
                                
                                // Tentukan apakah balance/status/SR harus ditampilkan
                                $shouldShow = false;
                                if ($firstPlanDate && $dateStr >= $firstPlanDate) {
                                    // Jika sudah CLOSE: Hanya tampilkan di tanggal CLOSE
                                    if ($closedDate) {
                                        $shouldShow = ($dateStr == $closedDate);
                                    }
                                    // Jika belum CLOSE: Tampilkan sampai akhir bulan
                                    else {
                                        $shouldShow = true;
                                    }
                                }
                            @endphp
                            
                            @if($row['editable'] && $row['field'] === 'plan')
                                <td id="{{ $cellKey }}" class="editable-cell {{ $dateInfo['is_weekend'] ? 'weekend' : '' }} {{ $isToday ? 'today-highlight' : '' }}" 
                                    style="text-align: right; background: #f0f8ff; cursor: pointer;"
                                    data-editable="plan"
                                    data-item='@json($item)'
                                    data-date="{{ $dateStr }}"
                                    data-value="{{ $daily['plan'] ?? 0 }}"
                                    data-ponumb="{{ $daily['ponumb'] ?? '' }}">
                                    <span class="edit-icon">✏️</span>
                                    <span class="cell-value">{{ $daily ? number_format($daily['plan'], 0, ',', '.') : '-' }}</span>
                                </td>
                            @elseif($row['editable'] && $row['field'] === 'ponumb')
                                <td id="{{ $cellKey }}" class="editable-cell {{ $dateInfo['is_weekend'] ? 'weekend' : '' }} {{ $isToday ? 'today-highlight' : '' }}" 
                                    style="text-align: left; background: #f0f8ff; cursor: pointer;"
                                    data-editable="ponumb"
                                    data-item='@json($item)'
                                    data-date="{{ $dateStr }}"
                                    data-value="{{ $daily['ponumb'] ?? '' }}">
                                    <span class="edit-icon">✏️</span>
                                    <span class="cell-value">{{ $daily['ponumb'] ?? '-' }}</span>
                                </td>
                            @else
                                <td class="{{ $dateInfo['is_weekend'] ? 'weekend' : '' }} {{ $isToday ? 'today-highlight' : '' }}" 
                                    style="text-align: right; background: {{ $row['color'] }}; padding: 4px;">
                                    @if($row['field'] === 'blc')
                                        {{-- Tampilkan balance sesuai kondisi --}}
                                        @if($shouldShow)
                                            <span style="color: {{ $cumulativeBalance < 0 ? '#d32f2f' : ($cumulativeBalance > 0 ? '#388e3c' : 'inherit') }}; font-weight: {{ $cumulativeBalance != 0 ? '600' : 'normal' }};">
                                                @if($cumulativeBalance != 0)
                                                    {{ $cumulativeBalance < 0 ? '' : '+' }}{{ number_format($cumulativeBalance, 0, ',', '.') }}
                                                @else
                                                    0
                                                @endif
                                            </span>
                                        @else
                                            -
                                        @endif
                                    @elseif($row['field'] === 'status')
                                        {{-- Tampilkan status sesuai kondisi --}}
                                        @if($shouldShow)
                                            @php
                                                // Tentukan status berdasarkan ACT vs PLAN
                                                $displayStatus = 'PENDING';
                                                if ($cumulativePlan > 0) {
                                                    if ($cumulativeAct > $cumulativePlan) {
                                                        $displayStatus = 'OVER';
                                                    } elseif ($cumulativeAct == $cumulativePlan) {
                                                        $displayStatus = 'CLOSE';
                                                    } else {
                                                        $displayStatus = 'PENDING';
                                                    }
                                                }
                                            @endphp
                                            <span class="badge badge-{{ strtolower($displayStatus) }}" style="display: block; text-align: center;">
                                                {{ $displayStatus }}
                                            </span>
                                        @else
                                            -
                                        @endif
                                    @elseif($row['field'] === 'sr')
                                        {{-- Hitung SR% kumulatif sebagai persentase ketercapaian total ACT vs total PLAN --}}
                                        @if($shouldShow)
                                            @php
                                                $srPercent = 0;
                                                if ($cumulativePlan > 0) {
                                                    $srPercent = ($cumulativeAct / $cumulativePlan) * 100;
                                                }
                                            @endphp
                                            @if($cumulativePlan > 0)
                                                <span style="color: {{ $srPercent < 100 ? '#d32f2f' : ($srPercent >= 100 ? '#388e3c' : 'inherit') }}; font-weight: {{ $srPercent != 100 ? '600' : 'normal' }};">
                                                    {{ number_format($srPercent, 1) }}%
                                                </span>
                                            @else
                                                -
                                            @endif
                                        @else
                                            -
                                        @endif
                                    @elseif($daily)
                                        @if($row['field'] === 'act')
                                            {{ $daily['act'] > 0 ? number_format($daily['act'], 0, ',', '.') : '-' }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif
                        @endforeach
                        
                        <td style="background: #e3f2fd; padding: 4px; text-align: right;">
                            @if($row['field'] === 'plan')
                                {{-- Total PLAN = SUM semua plan --}}
                                {{ number_format($item['total_plan'], 0, ',', '.') }}
                            @elseif($row['field'] === 'act')
                                {{-- Total ACT = SUM semua act --}}
                                {{ number_format($item['total_act'], 0, ',', '.') }}
                            @elseif($row['field'] === 'blc')
                                {{-- Total BALANCE = Total ACT - Total PLAN --}}
                                @php
                                    $totalBalance = $item['total_act'] - $item['total_plan'];
                                @endphp
                                <span style="color: {{ $totalBalance < 0 ? '#d32f2f' : ($totalBalance > 0 ? '#388e3c' : 'inherit') }}; font-weight: {{ $totalBalance != 0 ? '600' : 'normal' }};">
                                    @if($totalBalance != 0)
                                        {{ $totalBalance < 0 ? '' : '+' }}{{ number_format($totalBalance, 0, ',', '.') }}
                                    @else
                                        0
                                    @endif
                                </span>
                            @elseif($row['field'] === 'status')
                                {{-- Total STATUS = kosong --}}
                                -
                            @elseif($row['field'] === 'sr')
                                {{-- Total SR = (Total ACT / Total PLAN) * 100% --}}
                                @php
                                    $totalSrPercent = 0;
                                    if ($item['total_plan'] > 0) {
                                        $totalSrPercent = ($item['total_act'] / $item['total_plan']) * 100;
                                    }
                                @endphp
                                @if($item['total_plan'] > 0)
                                    <span style="color: {{ $totalSrPercent < 100 ? '#d32f2f' : ($totalSrPercent >= 100 ? '#388e3c' : 'inherit') }}; font-weight: {{ $totalSrPercent != 100 ? '600' : 'normal' }};">
                                        {{ number_format($totalSrPercent, 1) }}%
                                    </span>
                                @else
                                    -
                                @endif
                            @elseif($row['field'] === 'ponumb')
                                {{-- Total PO = COUNT berapa banyak PO (bukan sum) --}}
                                @php
                                    $poCount = count($item['po_numbers'] ?? []);
                                @endphp
                                {{ $poCount > 0 ? $poCount : '-' }}
                            @endif
                        </td>
                        
                        <td style="background: #fff3e0; padding: 4px; text-align: right;">
                            @if($row['field'] === 'plan')
                                {{ $freqPlan }}
                            @elseif($row['field'] === 'act')
                                {{ $freqAct }}
                            @elseif($row['field'] === 'blc')
                                {{ number_format($freqBlc, 1) }}%
                            @elseif($row['field'] === 'status')
                                <span style="font-size: 8px;">Result</span>
                            @elseif($row['field'] === 'sr')
                                -
                            @elseif($row['field'] === 'ponumb')
                                <span style="display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: 600; font-size: 9px;
                                             background: {{ $freqGrade === 'A' ? '#c8e6c9' : ($freqGrade === 'B' ? '#bbdefb' : ($freqGrade === 'C' ? '#fff9c4' : ($freqGrade === 'D' ? '#ffcdd2' : '#f5f5f5'))) }};
                                             color: {{ $freqGrade === 'A' ? '#2e7d32' : ($freqGrade === 'B' ? '#1565c0' : ($freqGrade === 'C' ? '#f57f17' : ($freqGrade === 'D' ? '#c62828' : '#666'))) }};">
                                    {{ $freqGrade }}
                                </span>
                            @endif
                        </td>
                        
                        @if($rowIdx === 0)
                        <td rowspan="6" style="vertical-align: middle; padding: 6px 4px; font-size: 9px; color: #666;">
                        </td>
                        @endif
                    </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
    
    <!-- Pagination & Rows Per Page -->
    <div class="px-6 py-4 bg-white border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4 text-sm text-gray-600 order-2 md:order-1">
            <div class="flex items-center gap-2">
                <span>Tampilkan</span>
                <select 
                    id="per_page_selector" 
                    class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-gray-50 text-xs font-medium cursor-pointer"
                    onchange="window.changePerPage(this.value)"
                >
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <span>data per halaman</span>
            </div>
            <div class="border-l border-gray-300 h-4 mx-2"></div>
            <div>
                Menampilkan <span class="font-medium text-gray-900">{{ $bahanBakuList->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $bahanBakuList->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $bahanBakuList->total() }}</span> data
            </div>
        </div>
        <div class="order-1 md:order-2">
            {{ $bahanBakuList->links('vendor.pagination.custom') }}
        </div>
    </div>
    
    <!-- Loading overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <div class="text-center">
                <div class="text-2xl mb-2">💾</div>
                <div class="font-semibold">Menyimpan...</div>
            </div>
        </div>
    </div>
</div>

<!-- Import Result Modal -->
<div id="importResultModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center" style="display: none; z-index: 9999;">
    <div class="bg-white rounded-lg shadow-xl p-6" style="max-width: 600px; width: 90%;">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Import Results</h3>
            <button onclick="closeImportModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="importResultContent" class="mb-4">
            <!-- Results will be inserted here -->
        </div>
        <div class="flex justify-end gap-2">
            <button onclick="closeImportModal()" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Close</button>
            <button onclick="closeImportModal(); window.location.reload();" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Refresh Page</button>
        </div>
    </div>
</div>

<script>
// Define toggleFullscreenMode globally FIRST
window.changePerPage = function(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1); // Reset to page 1
    window.location.href = url.toString();
};

window.toggleFullscreenMode = function() {
    const appDiv = document.getElementById('controlSupplierApp');
    const placeholder = document.getElementById('appPlaceholder');
    const normalHeader = document.getElementById('normalHeader');
    const fullscreenHeader = document.getElementById('fullscreenHeader');
    const tableContainer = document.getElementById('tableContainer');
    
    if (!appDiv) {
        console.error('App div not found');
        return;
    }

    // Initialize placeholder if it doesn't exist (fallback)
    if (!placeholder) {
        const p = document.createElement('div');
        p.id = 'appPlaceholder';
        p.style.display = 'none';
        appDiv.parentNode.insertBefore(p, appDiv);
    }
    
    const isFullscreen = appDiv.classList.contains('fullscreen-mode');
    
    if (isFullscreen) {
        // === EXIT FULLSCREEN ===
        
        // 1. Move back to original location
        const p = document.getElementById('appPlaceholder');
        if (p && p.parentNode) {
            p.parentNode.insertBefore(appDiv, p);
        }
        
        // 2. Remove classes
        appDiv.classList.remove('fullscreen-mode');
        document.body.classList.remove('fullscreen-active');
        
        // 3. Restore Layout Elements Visibility
        // We don't need to manually unhide sidebar/header because controlSupplierApp is now back inside the layout flow
        // The CSS rule: body.fullscreen-active .app-layout > div:first-child { display: none } takes care of it via the class removal
        
        // 4. Restore Headers
        if (normalHeader) normalHeader.style.display = 'block';
        if (fullscreenHeader) fullscreenHeader.style.display = 'none';
        
        // 5. Reset Table Container
        if (tableContainer) {
            tableContainer.style.maxHeight = 'calc(100vh - 200px)';
            tableContainer.style.padding = ''; // Clear inline padding if any
            tableContainer.style.margin = '';
            tableContainer.style.flex = '';
            tableContainer.style.height = '';
            tableContainer.style.minHeight = '';
        }
        
    } else {
        // === ENTER FULLSCREEN ===
        
        // 1. Move to Body (Teleport)
        document.body.appendChild(appDiv);
        
        // 2. Add classes
        appDiv.classList.add('fullscreen-mode');
        document.body.classList.add('fullscreen-active');
        
        // 3. Toggle Headers
        if (normalHeader) normalHeader.style.display = 'none';
        if (fullscreenHeader) fullscreenHeader.style.display = 'flex';
        
        // 4. Adjust Table Container to fill space
        if (tableContainer) {
            tableContainer.style.padding = '0';
            tableContainer.style.margin = '0';
            tableContainer.style.flex = '1';
            tableContainer.style.height = '100%'; 
            tableContainer.style.maxHeight = 'none';
        }
    }
};

console.log('toggleFullscreenMode function defined');

(function() {
    const app = {
        periode: '{{ $periode }}',
        editingCell: null,
        
        init() {
            this.setupEventListeners();
            
            // Check if should restore fullscreen from URL
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('fullscreen') === '1') {
                setTimeout(() => {
                    window.toggleFullscreenMode();
                    // Just to be safe, scroll again if toggleFullscreen's internal timeout was too fast or race condition
                    setTimeout(() => app.scrollToToday(), 150);
                }, 100);
            }

            // Restore scroll position if saved
            const container = document.getElementById('tableContainer');
            if (container) {
                const top = sessionStorage.getItem('tableScrollTop');
                const left = sessionStorage.getItem('tableScrollLeft');
                if (top) container.scrollTop = parseInt(top);
                if (left) container.scrollLeft = parseInt(left);
                sessionStorage.removeItem('tableScrollTop');
                sessionStorage.removeItem('tableScrollLeft');
            }
            
            // Setup import button
            this.setupImportButton();
            
            // Scroll to today in normal mode as well
            setTimeout(() => {
                this.scrollToToday();
            }, 500); // Slight delay to ensure table rendering is stable
        },
        
        setupEventListeners() {
            // Refresh button
            const btnRefresh = document.getElementById('btnRefresh');
            if (btnRefresh) {
                btnRefresh.addEventListener('click', () => this.reloadPage());
            }
            
            // Periode change (Normal)
            const periodeInput = document.getElementById('periodeInput');
            if (periodeInput) {
                periodeInput.addEventListener('change', () => {
                    this.periode = periodeInput.value;
                    this.reloadPage();
                });
            }

            // Periode change (Fullscreen)
            const periodeInputFullscreen = document.getElementById('periodeInputFullscreen');
            if (periodeInputFullscreen) {
                periodeInputFullscreen.addEventListener('change', () => {
                    this.periode = periodeInputFullscreen.value;
                    this.reloadPage();
                });
            }
            
            // Kategori change (Normal)
            const kategoriInput = document.getElementById('kategoriInput');
            if (kategoriInput) {
                kategoriInput.addEventListener('change', () => {
                    this.reloadPage();
                });
            }

            // Search input (Normal)
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') {
                        this.reloadPage();
                    }
                });
                
                // Optional: Debounce search input?
                // For now, let's keep it simple with Enter or Blur, or maybe just Enter.
            }

            // Kategori change (Fullscreen)
            const kategoriInputFullscreen = document.getElementById('kategoriInputFullscreen');
             if (kategoriInputFullscreen) {
                kategoriInputFullscreen.addEventListener('change', () => {
                    this.reloadPage();
                });
            }
            
            // Editable cells
            document.querySelectorAll('.editable-cell').forEach(cell => {
                cell.addEventListener('click', (e) => this.handleCellClick(e));
            });
        },
        
        async reloadPage() {
            const isFullscreen = document.getElementById('controlSupplierApp')?.classList.contains('fullscreen-mode');
            
            let periode = '{{ $periode }}';
            let kategori = '';
            let search = '';

            if (isFullscreen) {
                periode = document.getElementById('periodeInputFullscreen')?.value || periode;
                kategori = document.getElementById('kategoriInputFullscreen')?.value || '';
                // Fullscreen doesn't have search input in my updated design, or should I add it? 
                // For now, let's just grab it from normal header as fallback or URL
                search = new URLSearchParams(window.location.search).get('search') || '';
            } else {
                periode = document.getElementById('periodeInput')?.value || periode;
                kategori = document.getElementById('kategoriInput')?.value || '';
                search = document.getElementById('searchInput')?.value || '';
            }
            
            let url = '/controlsupplier/monitoring?periode=' + periode;
            if (kategori) {
                url += '&kategori=' + kategori;
            }
            if (search) {
                url += '&search=' + encodeURIComponent(search);
            }
            
            // Preserve fullscreen state
            if (isFullscreen) {
                url += '&fullscreen=1';
            }
            
            // Save scroll position before reload
            const container = document.getElementById('tableContainer');
            if (container) {
                sessionStorage.setItem('tableScrollTop', container.scrollTop);
                sessionStorage.setItem('tableScrollLeft', container.scrollLeft);
            }

            // Full page reload for MPA stability
            window.location.href = url;
        },
        
        handleCellClick(e) {
            const cell = e.currentTarget;
            if (cell.classList.contains('editing')) return;
            
            const type = cell.dataset.editable;
            const item = JSON.parse(cell.dataset.item);
            const date = cell.dataset.date;
            const currentValue = cell.dataset.value;
            let ponumb = cell.dataset.ponumb || '';
            
            // For PLAN, try to get live PO Number from the DOM (since we don't reload page)
            if (type === 'plan') {
                const poCellId = cell.id.replace('-plan', '-ponumb');
                const poCell = document.getElementById(poCellId);
                if (poCell && poCell.dataset.value) {
                    ponumb = poCell.dataset.value;
                }
            }
            
            if (type === 'ponumb') {
                this.showPOManager(cell, item, date, currentValue);
                return;
            }

            // Create input for Plan
            const input = document.createElement('input');
            input.type = 'number';
            input.value = currentValue;
            input.className = 'w-full border-none bg-transparent outline-none';
            input.style.padding = '2px';
            input.style.fontSize = '10px';
            
            // Replace content
            cell.innerHTML = '';
            cell.appendChild(input);
            cell.classList.add('editing');
            input.focus();
            input.select();
            
            // Save on blur
            input.addEventListener('blur', () => {
                const newValue = input.value;
                if (newValue !== currentValue) {
                    if (type === 'plan') {
                        this.updatePlan(item, date, newValue, ponumb, cell);
                    }
                } else {
                    this.restoreCell(cell, type, currentValue);
                }
            });
            
            // Save on Enter, cancel on Escape
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    input.blur();
                } else if (e.key === 'Escape') {
                    this.restoreCell(cell, type, currentValue);
                }
            });
        },
        
        restoreCell(cell, type, value) {
            if (!cell) return;
            
            const displayValue = type === 'plan' 
                ? (parseFloat(value) > 0 ? parseFloat(value).toLocaleString('id-ID') : '-')
                : (value || '-');
            
            cell.innerHTML = `<span class="edit-icon">✏️</span><span class="cell-value">${displayValue}</span>`;
            cell.classList.remove('editing');
        },

        showPOManager(cell, item, date, currentValue) {
            // Existing POs
            let poList = [];
            if (currentValue && currentValue !== '-') {
                poList = currentValue.split(',').map(s => s.trim()).filter(s => s);
            }

            // Create Popover
            const popover = document.createElement('div');
            popover.className = 'bg-white shadow-xl border border-gray-200 rounded p-3';
            popover.style.position = 'fixed';
            popover.style.zIndex = '1000001'; // Above fullscreen mode (999999)
            popover.style.width = '250px';
            popover.style.fontSize = '12px';
            
            // Position
            const rect = cell.getBoundingClientRect();
            popover.style.top = (rect.bottom + 5) + 'px';
            popover.style.left = rect.left + 'px';

            // Check if overflow bottom
            if (rect.bottom + 300 > window.innerHeight) {
                popover.style.top = (rect.top - 200) + 'px'; // Show above
            }

            // Header
            const header = document.createElement('div');
            header.className = 'flex justify-between items-center mb-2 border-b pb-1';
            header.innerHTML = '<span class="font-bold">Manage POs</span>';
            const closeBtn = document.createElement('button');
            closeBtn.className = 'text-red-500 hover:text-red-700 font-bold';
            closeBtn.innerHTML = '&times;';
            closeBtn.onclick = () => document.body.removeChild(popover);
            header.appendChild(closeBtn);
            popover.appendChild(header);

            // List Container
            const listContainer = document.createElement('div');
            listContainer.className = 'flex flex-col gap-2 mb-3';
            
            if (poList.length === 0) {
                listContainer.innerHTML = '<div class="text-gray-400 italic text-center text-xs py-2">No POs yet</div>';
            } else {
                poList.forEach(po => {
                    const row = document.createElement('div');
                    row.className = 'flex items-center gap-1';
                    
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.value = po;
                    input.readOnly = true;
                    input.className = 'border px-1 py-0.5 rounded flex-1 text-xs bg-gray-50';
                    
                    // Edit Button
                    const editBtn = document.createElement('button');
                    editBtn.innerHTML = '✏️';
                    editBtn.title = 'Edit PO';
                    editBtn.className = 'p-0.5 hover:bg-gray-100 rounded';
                    
                    // Save Button (hidden initially)
                    const saveBtn = document.createElement('button');
                    saveBtn.innerHTML = '✅';
                    saveBtn.title = 'Save';
                    saveBtn.className = 'p-0.5 hover:bg-green-100 rounded hidden';
                    
                    editBtn.onclick = () => {
                        input.readOnly = false;
                        input.classList.remove('bg-gray-50');
                        input.classList.add('bg-white', 'border-blue-500');
                        input.focus();
                        editBtn.classList.add('hidden');
                        saveBtn.classList.remove('hidden');
                    };
                    
                    saveBtn.onclick = () => {
                        const newVal = input.value.trim();
                        if (newVal && newVal !== po) {
                           this.updatePONumb(item, date, newVal, po, (success) => {
                               if (success) {
                                    // Update Local UI
                                    let currentPOs = cell.dataset.value ? cell.dataset.value.split(',').map(s=>s.trim()).filter(s=>s) : [];
                                    const idx = currentPOs.indexOf(po);
                                    if (idx !== -1) {
                                        currentPOs[idx] = newVal;
                                    }
                                    const finalValue = currentPOs.join(', ');
                                    cell.dataset.value = finalValue;
                                    this.restoreCell(cell, 'ponumb', finalValue);
                                    
                                    document.body.removeChild(popover);
                               }
                           });
                        } else {
                            // Cancel edit
                            input.value = po;
                            input.readOnly = true;
                            input.classList.add('bg-gray-50');
                            input.classList.remove('bg-white', 'border-blue-500');
                            saveBtn.classList.add('hidden');
                            editBtn.classList.remove('hidden');
                        }
                    };

                    // Delete Button
                    const delBtn = document.createElement('button');
                    delBtn.innerHTML = '🗑️';
                    delBtn.title = 'Delete PO';
                    delBtn.className = 'text-red-500 p-0.5 hover:bg-red-50 rounded';
                    delBtn.onclick = () => {
                        this.detachPONumb(item, date, po, (success) => {
                            if (success) {
                                // Update Local UI
                                let currentPOs = cell.dataset.value ? cell.dataset.value.split(',').map(s=>s.trim()).filter(s=>s) : [];
                                currentPOs = currentPOs.filter(p => p !== po);
                                const finalValue = currentPOs.join(', ');
                                cell.dataset.value = finalValue;
                                this.restoreCell(cell, 'ponumb', finalValue);

                                document.body.removeChild(popover);
                            }
                        });
                    };

                    row.appendChild(input);
                    row.appendChild(editBtn);
                    row.appendChild(saveBtn);
                    row.appendChild(delBtn);
                    listContainer.appendChild(row);
                });
            }
            popover.appendChild(listContainer);

            // Add New Section
            const addForm = document.createElement('div');
            addForm.className = 'border-t pt-2 flex gap-1';
            const newInput = document.createElement('input');
            newInput.type = 'text';
            newInput.placeholder = 'New PO...';
            newInput.className = 'border px-1 py-0.5 rounded flex-1 text-xs';
            const addBtn = document.createElement('button');
            addBtn.innerText = 'Add';
            addBtn.className = 'bg-blue-500 text-white px-2 py-0.5 rounded text-xs hover:bg-blue-600';
            
            const handleAdd = () => {
                const val = newInput.value.trim();
                if (val) {
                this.updatePONumb(item, date, val, null, (success) => {
                     if (success) {
                        // Update Local UI
                        let currentPOs = cell.dataset.value ? cell.dataset.value.split(',').map(s=>s.trim()).filter(s=>s) : [];
                        currentPOs.push(val);
                        const finalValue = currentPOs.join(', ');
                        cell.dataset.value = finalValue;
                        this.restoreCell(cell, 'ponumb', finalValue);
                        
                        document.body.removeChild(popover);
                     }
                });
                }
            };
            
            addBtn.onclick = handleAdd;
            newInput.onkeydown = (e) => { if (e.key === 'Enter') handleAdd(); };
            
            addForm.appendChild(newInput);
            addForm.appendChild(addBtn);
            popover.appendChild(addForm);

            // Close on click outside
            setTimeout(() => {
                const clickHandler = (e) => {
                    if (!popover.contains(e.target) && !cell.contains(e.target)) {
                        if (document.body.contains(popover)) {
                            document.body.removeChild(popover);
                        }
                        document.removeEventListener('click', clickHandler);
                    }
                };
                document.addEventListener('click', clickHandler);
            }, 100);

            document.body.appendChild(popover);
        },
        
        async updatePONumb(item, date, value, oldPonumb = null, callback = null) {
            if (!value || value.trim() === '') {
                alert('PO Number tidak boleh kosong!');
                if (callback) callback(false);
                return;
            }
            
            this.showLoading();
            
            try {
                const bodyJson = {
                    bahan_baku_id: item.bahan_baku_id,
                    supplier_id: item.supplier_id,
                    periode: this.periode,
                    tanggal: date,
                    ponumb: value.trim()
                };
                
                if (oldPonumb) {
                    bodyJson.old_ponumb = oldPonumb;
                }

                const response = await fetch('/controlsupplier/update-ponumb', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(bodyJson)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (callback) callback(true);
                    this.reloadPage(); 
                } else {
                    alert('Gagal menyimpan: ' + (data.error || 'Unknown error'));
                    if (callback) callback(false);
                    this.hideLoading();
                }
            } catch (error) {
                alert('Gagal menyimpan: ' + error.message);
                if (callback) callback(false);
                this.hideLoading();
            }
        },

        async detachPONumb(item, date, value, callback = null) {
            if (!confirm('Apakah anda yakin ingin menghapus PO ' + value + ' dari tanggal ini?')) return;

            this.showLoading();
            
            try {
                const response = await fetch('/controlsupplier/detach-ponumb', {
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
                    if (callback) callback(true);
                    this.reloadPage(); 
                } else {
                    alert('Gagal menghapus: ' + (data.error || 'Unknown error'));
                    this.hideLoading();
                }
            } catch (error) {
                alert('Gagal menghapus: ' + error.message);
                this.hideLoading();
                if (callback) callback(false);
            }
        },
        
        async updatePlan(item, date, value, ponumb, cell) {
            const qty = parseFloat(value) || 0;
            
            if (!ponumb || ponumb === '-' || ponumb === '') {
                alert('Silakan input PO Number terlebih dahulu sebelum input Plan!');
                this.restoreCell(cell, 'plan', cell.dataset.value);
                return;
            }
            
            this.showLoading();
            
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
                    this.reloadPage();
                } else {
                    alert('Gagal menyimpan: ' + (data.error || 'Unknown error'));
                    this.hideLoading();
                }
            } catch (error) {
                alert('Gagal menyimpan: ' + error.message);
                this.hideLoading();
            }
        },
        
        showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.add('active');
            }
        },
        
        hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.remove('active');
            }
        },
        
        setupImportButton() {
            const btnImport = document.getElementById('btnImportSAP');
            const fileInput = document.getElementById('fileInputSAP');
            
            if (btnImport && fileInput) {
                btnImport.addEventListener('click', () => {
                    // Open guidance modal first
                    openSAPGuidanceModal();
                });
                
                fileInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (file) {
                        this.uploadSAPFile(file);
                    }
                });
            }
        },
        
        async uploadSAPFile(file) {
            const formData = new FormData();
            formData.append('file', file);
            
            this.showLoading();
            
            try {
                const response = await fetch('/controlsupplier/import-sap', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: formData
                });
                
                const data = await response.json();
                
                this.hideLoading();
                
                if (data.success) {
                    this.showImportResults(data.data);
                } else {
                    alert('Import failed: ' + (data.error || 'Unknown error'));
                }
                
                // Reset file input
                document.getElementById('fileInputSAP').value = '';
                
            } catch (error) {
                this.hideLoading();
                alert('Import failed: ' + error.message);
                document.getElementById('fileInputSAP').value = '';
            }
        },
        
        showImportResults(results) {
            const modal = document.getElementById('importResultModal');
            const content = document.getElementById('importResultContent');
            
            let html = `
                <div class="space-y-3">
                    <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded">
                        <span class="text-2xl">✅</span>
                        <div>
                            <div class="font-semibold text-green-800">Successfully Imported</div>
                            <div class="text-green-600">${results.success} rows</div>
                        </div>
                    </div>
            `;
            
            if (results.skipped > 0) {
                html += `
                    <div class="flex items-center gap-2 p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <span class="text-2xl">⚠️</span>
                        <div>
                            <div class="font-semibold text-yellow-800">Skipped</div>
                            <div class="text-yellow-600">${results.skipped} rows</div>
                        </div>
                    </div>
                `;
            }
            
            if (results.errors && results.errors.length > 0) {
                html += `
                    <div class="p-3 bg-red-50 border border-red-200 rounded">
                        <div class="font-semibold text-red-800 mb-2">Errors:</div>
                        <div class="text-sm text-red-600 max-h-40 overflow-y-auto">
                            <ul class="list-disc list-inside space-y-1">
                `;
                
                results.errors.forEach(error => {
                    html += `<li>${error}</li>`;
                });
                
                html += `
                            </ul>
                        </div>
                    </div>
                `;
            }
            
            html += '</div>';
            
            content.innerHTML = html;
            modal.style.display = 'flex';
        },
        
        scrollToToday() {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const today = `${year}-${month}-${day}`;

            const container = document.getElementById('tableContainer');
            if (!container) return;
            
            const targetTh = container.querySelector(`th[data-date="${today}"]`);
            if (targetTh) {
                // Calculate width of sticky columns (Supplier + Item + Point C)
                // We can't rely just on offsetLeft of targetTh because sticky columns might be sticky.
                // But initially (scrollLeft = 0), offsetLeft is correct distance from start.
                // We want to scroll so that targetTh is adjacent to the sticky columns.
                
                // Estimate sticky width (Supplier 120 + Item 180 + Point 60 = 360) + margins/borders
                // Better: Measure the offsetLeft of the first date column!
                const firstDateTh = container.querySelector('th[data-date]');
                let stickyWidth = 0;
                if (firstDateTh) {
                    stickyWidth = firstDateTh.offsetLeft;
                } else {
                     // Fallback
                     stickyWidth = 360; 
                }
                
                // Validasi stickyWidth to be reasonable
                if (stickyWidth < 100) stickyWidth = 370; // Hardcode fallback matching CSS
                
                const targetPosition = targetTh.offsetLeft - stickyWidth;
                
                // Smooth scroll or instant? Instant is better for initial load.
                container.scrollTo({
                    left: targetPosition,
                    behavior: 'smooth'
                });
                console.log('Scrolled to today:', today, 'Position:', targetPosition);
            } else {
                console.log('Today column not found:', today);
            }
        },

        toggleFullscreen() {
            const appDiv = document.getElementById('controlSupplierApp');
            const placeholder = document.getElementById('appPlaceholder');
            const normalHeader = document.getElementById('normalHeader');
            const fullscreenHeader = document.getElementById('fullscreenHeader');
            const tableContainer = document.getElementById('tableContainer');
            
            if (appDiv.classList.contains('fullscreen-mode')) {
                // === EXIT FULLSCREEN ===
                appDiv.classList.remove('fullscreen-mode');
                document.body.classList.remove('fullscreen-active');
                
                // Move back to original position
                if (placeholder && placeholder.parentNode) {
                    placeholder.parentNode.insertBefore(appDiv, placeholder.nextSibling);
                }
                
                // Show normal header, hide fullscreen header
                if (normalHeader) normalHeader.style.display = 'block';
                if (fullscreenHeader) fullscreenHeader.style.display = 'none';
                
                // Reset table container
                if (tableContainer) {
                    tableContainer.style.maxHeight = 'calc(100vh - 250px)';
                    tableContainer.classList.add('px-6');
                    
                    // Reset inline styles forced during fullscreen
                    tableContainer.style.padding = '';
                    tableContainer.style.margin = '';
                    tableContainer.style.marginTop = '';
                    tableContainer.style.paddingTop = '';
                    tableContainer.style.flex = '';
                    tableContainer.style.height = '';
                    tableContainer.style.minHeight = '';
                }
            } else {
                // === ENTER FULLSCREEN ===
                // Move to body to bypass any overflow/z-index issues from layout
                document.body.appendChild(appDiv);
                
                appDiv.classList.add('fullscreen-mode');
                document.body.classList.add('fullscreen-active');
                
                // Hide normal header, show fullscreen header
                if (normalHeader) normalHeader.style.display = 'none';
                if (fullscreenHeader) fullscreenHeader.style.display = 'flex';
                
                // Expand table container
                if (tableContainer) {
                    tableContainer.classList.remove('px-6');
                    tableContainer.style.padding = '0';
                    tableContainer.style.margin = '0';
                    tableContainer.style.marginTop = '0';
                    tableContainer.style.paddingTop = '0';
                    tableContainer.style.flex = '1';
                    tableContainer.style.height = 'auto'; // allow flex to control
                    tableContainer.style.maxHeight = 'none';
                    tableContainer.style.minHeight = '0';
                }
                
                // Scroll to Today
                setTimeout(() => {
                    this.scrollToToday();
                }, 100);
            }
        }
    };
    
    // Expose globally for button onclicks
    window.toggleFullscreenMode = () => app.toggleFullscreen();
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => app.init());
    } else {
        app.init();
    }
    
    // Setup maximize and fullscreen buttons
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Setting up fullscreen buttons...');
        
        const btnMaximize = document.getElementById('btnMaximize');
        const btnExitFullscreen = document.getElementById('btnExitFullscreen');
        const btnRefreshFullscreen = document.getElementById('btnRefreshFullscreen');
        const periodeInputFullscreen = document.getElementById('periodeInputFullscreen');
        
        console.log('Buttons found:', {
            btnMaximize: !!btnMaximize,
            btnExitFullscreen: !!btnExitFullscreen,
            btnRefreshFullscreen: !!btnRefreshFullscreen
        });

        // Maximize button
        if (btnMaximize) {
            btnMaximize.onclick = function(e) {
                e.preventDefault();
                console.log('Maximize button clicked!');
                window.toggleFullscreenMode();
            };
            console.log('Maximize button handler attached');
        } else {
            console.error('btnMaximize not found!');
        }
        
        // Exit fullscreen button
        if (btnExitFullscreen) {
            btnExitFullscreen.onclick = function(e) {
                e.preventDefault();
                console.log('Exit fullscreen button clicked!');
                window.toggleFullscreenMode();
            };
            console.log('Exit fullscreen button handler attached');
        }
        
        // Refresh button in fullscreen
        if (btnRefreshFullscreen) {
            btnRefreshFullscreen.onclick = function(e) {
                e.preventDefault();
                app.reloadPage();
            };
        }
        
        // ESC key to exit fullscreen
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const appDiv = document.getElementById('controlSupplierApp');
                if (appDiv && appDiv.classList.contains('fullscreen-mode')) {
                    console.log('ESC pressed - exiting fullscreen');
                    window.toggleFullscreenMode();
                }
            }
        });
        
        console.log('Fullscreen handlers initialized successfully!');
    });
})();



function closeImportModal() {
    document.getElementById('importResultModal').style.display = 'none';
}

function openSAPGuidanceModal() {
    const modal = document.getElementById('sapGuidanceModal');
    if (modal) modal.style.display = 'flex';
}

function closeSAPGuidanceModal() {
    const modal = document.getElementById('sapGuidanceModal');
    if (modal) modal.style.display = 'none';
}

function triggerSAPUpload() {
    closeSAPGuidanceModal();
    const fileInput = document.getElementById('fileInputSAP');
    if (fileInput) fileInput.click();
}
</script>

<!-- Import Result Modal -->
<div id="importResultModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4 overflow-hidden animate-fade-in-up">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800">Import Result</h3>
            <button onclick="closeImportModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-4" id="importResultContent">
            <!-- Content will be injected here -->
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button onclick="closeImportModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium text-sm">
                Close
            </button>
        </div>
    </div>
</div>

<!-- SAP Import Guidance Modal -->
<div id="sapGuidanceModal" class="fixed inset-0 bg-black bg-opacity-50 z-[9999] hidden items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4 overflow-hidden animate-fade-in-up">
        <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <span class="text-purple-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                </span>
                SAP Excel Import Guidance
            </h3>
            <button onclick="closeSAPGuidanceModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="p-6">
            <div class="mb-4 text-sm text-gray-600">
                Silakan pastikan file CSV/Excel anda memiliki format kolom berikut agar data dapat terbaca dengan benar oleh sistem.
                <br>
                <span class="text-xs text-gray-500 italic">*Baris pertama (Header) akan dilewati oleh sistem.</span>
            </div>
            
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 w-16 text-center">Col</th>
                            <th class="px-4 py-2">Field Name</th>
                            <th class="px-4 py-2">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">B</td>
                            <td class="px-4 py-2 font-medium">PO Number</td>
                            <td class="px-4 py-2 text-gray-600">Nomor Purchase Order (Wajib)</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">C</td>
                            <td class="px-4 py-2 font-medium">Posting Date</td>
                            <td class="px-4 py-2 text-gray-600">Tanggal Penerimaan</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">F</td>
                            <td class="px-4 py-2 font-medium">Name 1</td>
                            <td class="px-4 py-2 text-gray-600">Nama Supplier (Harus sama dengan Master)</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">G</td>
                            <td class="px-4 py-2 font-medium">Material</td>
                            <td class="px-4 py-2 text-gray-600">Nomor Bahan Baku (Utama)</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">H</td>
                            <td class="px-4 py-2 font-medium">Description</td>
                            <td class="px-4 py-2 text-gray-600">Nama Bahan Baku (Opsional/Fallback jika G kosong)</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">I</td>
                            <td class="px-4 py-2 font-medium">Quantity</td>
                            <td class="px-4 py-2 text-gray-600">Jumlah Quantity (Angka)</td>
                        </tr>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 text-center font-mono bg-gray-50 text-gray-500">N</td>
                            <td class="px-4 py-2 font-medium">Reference</td>
                            <td class="px-4 py-2 text-gray-600">Nomor Surat Jalan</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 p-3 bg-blue-50 text-blue-800 text-xs rounded border border-blue-100 flex items-start gap-2">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>
                    <strong>Guide:</strong> Export data dari SAP ke format Spreadsheet, lalu Save As sebagai <strong>CSV (Comma delimited)</strong> sebelum upload. Pastikan tidak ada merged cell.
                </span>
            </div>
        </div>
        <div class="p-4 border-t border-gray-100 bg-gray-50 flex justify-end gap-2">
            <button onclick="closeSAPGuidanceModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium text-sm transition-colors">
                Cancel
            </button>
            <button onclick="triggerSAPUpload()" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors shadow-sm flex items-center gap-2 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Pilih File CSV
            </button>
        </div>
    </div>
</div>
@endsection
