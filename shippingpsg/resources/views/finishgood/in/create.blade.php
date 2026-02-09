@extends('layout.app')

@push('styles')
<style>
    .scanner-active {
        border-color: #22c55e !important;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
    }
    .last-scan-row {
        animation: highlight 2s ease-out;
    }
    @keyframes highlight {
        0% { background-color: #dcfce7; }
        100% { background-color: white; }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto">
    <!-- Header Section (Hidden on Mobile) -->
    <div class="hidden md:flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 md:mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('finishgood.in.index') }}" class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Scan Finish Good In</h1>
            </div>
            <p class="text-gray-500 ml-10">Scan barcode label (Format: Part|Customer|Qty|Lot)</p>
        </div>

        <!-- Operator Card (Hidden on Mobile) -->
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-blue-600 font-medium uppercase tracking-wider">Operator</p>
                <p class="font-bold text-gray-900">{{ session('mp_nama', auth()->user()->user_id ?? 'Unknown') }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        <!-- Left Column: Scanner Section -->
        <div class="md:col-span-2 space-y-4 md:space-y-6">
            
            <!-- Scanner Card -->
            <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-100 p-4 md:p-8 text-center relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                
                <div class="mb-4 md:mb-8 relative z-10">
                    <div class="bg-indigo-50 w-12 h-12 md:w-24 md:h-24 rounded-full flex items-center justify-center mx-auto mb-3 md:mb-6 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 md:w-10 md:h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                    </div>
                    
                    <h2 class="text-lg md:text-2xl font-bold text-gray-900 mb-1 md:mb-2 flex items-center justify-center gap-2">
                        Ready to Scan
                        <span id="networkStatus" class="flex h-3 w-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]" title="Online"></span>
                    </h2>
                    <p id="offlineBadge" class="hidden text-[10px] md:text-xs font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200 w-fit mx-auto mb-2 animate-pulse">
                        OFFLINE MODE: Data stored locally
                    </p>
                    <p class="text-xs md:text-base text-gray-500 max-w-lg mx-auto hidden md:block">
                        Pastikan kursor aktif di kolom input di bawah ini sebelum melakukan scanning.
                    </p>
                </div>

                <!-- Input Container -->
                <div class="relative max-w-xl mx-auto">
                    <div class="relative">
                        <input type="text" 
                               id="scannerInput" 
                               class="w-full pl-4 pr-10 py-3 md:py-4 bg-gray-50 border-2 border-gray-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-lg md:rounded-xl text-center font-mono text-sm md:text-lg shadow-sm transition-all duration-300"
                               placeholder="Scan barcode label here..." 
                               autofocus 
                               autocomplete="off">
                        
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                            <span id="typingIndicator" class="h-2 w-2 md:h-3 md:w-3 rounded-full bg-green-400 animate-pulse"></span>
                        </div>
                    </div>
                    <div class="mt-2 text-[10px] md:text-xs text-gray-400 flex justify-center items-center gap-1.5 md:gap-2">
                        <span class="bg-gray-100 px-1.5 py-0.5 rounded border border-gray-200">Tips</span>
                        <span>Auto-scan aktif. Ketik manual tekan <kbd class="font-sans border border-gray-200 rounded px-1 min-w-[20px] inline-block text-center">Enter</kbd></span>
                    </div>
                </div>
                
                <!-- Status Message -->
                <div id="scanStatus" class="mt-2 md:mt-4 h-5 md:h-6 text-xs md:text-sm font-medium transition-opacity opacity-0">
                    Menunggu input...
                </div>
                
                <!-- Debug Panel Removed -->
            </div>

            {{-- Processed Data Preview (Compact on Mobile) --}}
            <div id="previewCard" class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-100 p-4 md:p-6 hidden transition-all duration-500 transform translate-y-4"> <!-- Initially Hidden -->
                <div class="flex items-center justify-between mb-4 md:mb-6 pb-3 md:pb-4 border-b border-gray-100">
                    <h3 class="text-sm md:text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="w-1.5 h-4 md:w-2 md:h-6 bg-indigo-500 rounded-full"></span>
                        LAST SCANNED DATA
                    </h3>
                    <span class="text-[10px] md:text-xs font-mono text-gray-400" id="scanTime">--:--:--</span>
                </div>

                <div class="grid grid-cols-2 gap-3 md:gap-6 text-left">
                    <div class="bg-gray-50 p-2 md:p-4 rounded-lg md:rounded-xl">
                        <p class="text-[10px] md:text-xs text-gray-500 uppercase tracking-wider mb-0.5 md:mb-1">Part Number</p>
                        <p class="text-sm md:text-xl font-bold text-gray-900 truncate" id="prevPart">--</p>
                    </div>
                    <div class="bg-blue-50 p-2 md:p-4 rounded-lg md:rounded-xl">
                        <p class="text-[10px] md:text-xs text-blue-500 uppercase tracking-wider mb-0.5 md:mb-1">Qty</p>
                        <p class="text-sm md:text-xl font-bold text-blue-700" id="prevQty">--</p>
                    </div>
                    <div class="col-span-2 bg-gray-50 p-2 md:p-3 rounded-lg flex justify-between items-center">
                         <div>
                            <p class="text-[10px] text-gray-500 uppercase">Lot Number</p>
                            <p class="text-xs md:text-sm font-mono font-medium text-gray-700" id="prevLot">--</p>
                         </div>
                         <div class="text-right">
                             <p class="text-[10px] text-gray-500 uppercase">Customer</p>
                             <p class="text-xs md:text-sm font-medium text-gray-900" id="prevCust">--</p>
                         </div>
                    </div>
                    
                    <!-- Details Row -->
                    <div class="col-span-2 grid grid-cols-4 gap-2 bg-gray-50 p-2 md:p-3 rounded-lg text-center">
                        <div><p class="text-[10px] text-gray-400">Plan</p><p class="text-xs md:text-sm font-bold" id="prevPlan">-</p></div>
                        <div><p class="text-[10px] text-gray-400">Mesin</p><p class="text-xs md:text-sm font-bold" id="prevMesin">-</p></div>
                        <div><p class="text-[10px] text-gray-400">Shift</p><p class="text-xs md:text-sm font-bold" id="prevShift">-</p></div>
                        <div><p class="text-[10px] text-gray-400">Date</p><p class="text-xs md:text-sm font-bold" id="prevDate">-</p></div>
                    </div>
                </div>
            </div>
        </div>


        {{-- Right Column: History --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full flex flex-col max-h-[calc(100vh-200px)]">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-t-xl">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Recent Scans
                    </h3>
                    <div class="flex items-center gap-2">
                        <div id="syncIndicator" class="hidden items-center gap-1 text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full animate-pulse">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Syncing...
                        </div>
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-bold" id="scanCount">0</span>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto p-0" id="scanHistoryContainer">
                    {{-- Empty State --}}
                    <div id="emptyState" class="flex flex-col items-center justify-center h-48 text-gray-400">
                        <svg class="w-12 h-12 mb-2 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span class="text-sm">Belum ada data scan</span>
                    </div>

                    {{-- List Items will be injected here --}}
                    <ul id="scanList" class="divide-y divide-gray-100 hidden">
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ... existing defined variables ...
    const scannerInput = document.getElementById('scannerInput');
    const scanStatus = document.getElementById('scanStatus');
    const previewCard = document.getElementById('previewCard');
    
    // Preview Elements
    const prevPart = document.getElementById('prevPart');
    const prevQty = document.getElementById('prevQty');
    const prevLot = document.getElementById('prevLot');
    const prevCust = document.getElementById('prevCust');
    const prevPlan = document.getElementById('prevPlan');
    const prevMesin = document.getElementById('prevMesin');
    const prevShift = document.getElementById('prevShift');
    const prevDate = document.getElementById('prevDate');

    // History Elements
    const emptyState = document.getElementById('emptyState');
    const scanList = document.getElementById('scanList');
    const scanCount = document.getElementById('scanCount');
    let totalScans = 0;

    // Audio Base64 (No external files needed)
    // Simple Beep (Success)
    const audioSuccess = new Audio("data:audio/wav;base64,UklGRl9vT19XQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YU..."); // Truncated for brevity, I will use a reliable external URL logic here but wrapped to avoid errors
    
    // Since generating valid Base64 WAV string manually is tricky, I will use reliable Online URLs with error handling, 
    // OR create a simple oscillator beep using Web Audio API which is cleaner and file-less.
    
    const context = new (window.AudioContext || window.webkitAudioContext)();

    function playBeep(type) {
        if (context.state === 'suspended') {
            context.resume();
        }
        
        const oscillator = context.createOscillator();
        const gainNode = context.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(context.destination);

        if (type === 'success') {
            // High pitch short beep
            oscillator.type = 'sine';
            oscillator.frequency.setValueAtTime(1000, context.currentTime);
            oscillator.frequency.exponentialRampToValueAtTime(500, context.currentTime + 0.1);
            gainNode.gain.setValueAtTime(0.3, context.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.1);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.1);
        } else if (type === 'offline') {
            // Sweet double beep for offline success
            oscillator.type = 'triangle';
            oscillator.frequency.setValueAtTime(800, context.currentTime);
            gainNode.gain.setValueAtTime(0.2, context.currentTime);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.05);
            
            setTimeout(() => {
                const osc2 = context.createOscillator();
                const gain2 = context.createGain();
                osc2.connect(gain2);
                gain2.connect(context.destination);
                osc2.type = 'triangle';
                osc2.frequency.setValueAtTime(1200, context.currentTime);
                gain2.gain.setValueAtTime(0.2, context.currentTime);
                osc2.start();
                osc2.stop(context.currentTime + 0.05);
            }, 100);
        } else {
            // Low pitch long beep (Error)
            oscillator.type = 'sawtooth';
            oscillator.frequency.setValueAtTime(200, context.currentTime);
            oscillator.frequency.linearRampToValueAtTime(100, context.currentTime + 0.3);
            gainNode.gain.setValueAtTime(0.5, context.currentTime);
            gainNode.gain.linearRampToValueAtTime(0.01, context.currentTime + 0.3);
            oscillator.start();
            oscillator.stop(context.currentTime + 0.3);
        }
    }

    // Keep focus
    document.addEventListener('click', (e) => {
        // Prevent refocus if user is selecting text elsewhere (optional)
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'TEXTAREA') {
            scannerInput.focus();
        }
    });
    
    // ... rest of the code ...


    // Debug Helper (Console Only)
    function log(msg, type = 'info') {
        console.log(`[${type}] ${msg}`);
    }

    // Scanner Input Logic (Debounce)
    let typingTimer;
    const doneTypingInterval = 300; // 300ms wait after last char

    scannerInput.addEventListener('input', (e) => {
        clearTimeout(typingTimer);
        const val = scannerInput.value;
        log(`Input: "${val}" (len:${val.length})`);
        
        if (val.length > 5) { // Minimal length check
            typingTimer = setTimeout(() => {
                // Jika input mengandung separator, kita asumsikan scan selesai
                if (val.includes('|')) {
                    log('Auto-Detect Separator | -> Processing...');
                    handleScan(val.trim());
                }
            }, doneTypingInterval);
        }
    });

    scannerInput.addEventListener('keydown', async (e) => {
        // log(`Key: ${e.key}`); // Optional too noisy
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(typingTimer); // Clear auto timer if exists
            const barcode = scannerInput.value.trim();
            log('Enter Pressed. Barcode: ' + barcode);
            if (barcode) {
               handleScan(barcode);
            }
        }
    });

    async function handleScan(barcode) {
        log('Processing HandleScan: ' + barcode);
        const success = await processBarcode(barcode);
        
        if (success) {
            log('Scan Success. Clearing input.', 'success');
            scannerInput.value = ''; 
            scannerInput.focus();
        } else {
            log('Scan Failed or Error.', 'error');
            scannerInput.select();
        }
    }

    // Offline Queue Logic
    let offlineQueue = JSON.parse(localStorage.getItem('fg_scan_queue') || '[]');
    const networkStatus = document.getElementById('networkStatus');
    const offlineBadge = document.getElementById('offlineBadge');
    const syncIndicator = document.getElementById('syncIndicator');

    // Monitor Network
    function updateNetworkInfo() {
        if (navigator.onLine) {
            networkStatus.className = "flex h-3 w-3 rounded-full bg-green-500 shadow-[0_0_8px_rgba(34,197,94,0.6)]";
            networkStatus.title = "Online";
            offlineBadge.classList.add('hidden');
            if (typeof processGlobalQueue === 'function') processGlobalQueue();
        } else {
            networkStatus.className = "flex h-3 w-3 rounded-full bg-red-500 animate-pulse";
            networkStatus.title = "Offline";
            offlineBadge.classList.remove('hidden');
        }
    }
    
    async function processBarcode(barcode) {
        log('Parsing Barcode...');
        let parsedData = parseBarcode(barcode);
        
        if (!parsedData) {
            alert('Format salah! Barcode: ' + barcode);
            playSound('error');
            return false;
        }

        // 2. Kirim ke Server atau Simpan Offline
        if (navigator.onLine) {
            try {
                // Use the Global sync function for consistency
                const response = await syncDataToServer(parsedData);
                if (response.success) {
                    playSound('success');
                    updateUI(response.data, parsedData, false);
                    return true;
                } else {
                    alert('GAGAL: ' + response.message);
                    playSound('error');
                    return false;
                }
            } catch (error) {
                // Network error occurred while online (e.g. server down)
                return handleOfflineStorage(parsedData);
            }
        } else {
            // Navigator says we are offline
            return handleOfflineStorage(parsedData);
        }
    }

    function handleOfflineStorage(data) {
        const tempId = 'off_' + Date.now();
        const offlineItem = { ...data, id: tempId, is_offline: true, timestamp: new Date().toISOString() };
        
        offlineQueue.push(offlineItem);
        localStorage.setItem('fg_scan_queue', JSON.stringify(offlineQueue));
        
        // Sync the global reference
        if (typeof globalOfflineQueue !== 'undefined') {
            globalOfflineQueue = JSON.parse(localStorage.getItem('fg_scan_queue'));
        }

        playSound('offline');
        updateUI(offlineItem, data, true);
        log('Saved to Offline Queue: ' + data.lot_number, 'warning');
        return true;
    }

    // Remove the local sendToServer function as it will be handled globally
    // async function sendToServer(data) {
    //     const response = await fetch('{{ route("finishgood.in.store") }}', {
    //         method: 'POST',
    //         headers: {
    //             'Content-Type': 'application/json',
    //             'X-CSRF-TOKEN': '{{ csrf_token() }}',
    //             'Accept': 'application/json',
    //             'X-Requested-With': 'XMLHttpRequest'
    //         },
    //         body: JSON.stringify(data)
    //     });
    //     return await response.json();
    // }

    // Remove the local processQueue function as it will be handled globally
    // let isSyncing = false;
    // async function processQueue() {
    //     if (isSyncing || offlineQueue.length === 0 || !navigator.onLine) return;
        
    //     isSyncing = true;
    //     syncIndicator.classList.replace('hidden', 'flex');
    //     log('Starting Sync Process: ' + offlineQueue.length + ' items left', 'info');

    //     while (offlineQueue.length > 0 && navigator.onLine) {
    //         const item = offlineQueue[0];
    //         try {
    //             const res = await sendToServer(item);
    //             if (res.success) {
    //                 // Berhasil sync
    //                 offlineQueue.shift();
    //                 localStorage.setItem('fg_scan_queue', JSON.stringify(offlineQueue));
                    
    //                 // Update UI status di history list jika ada
    //                 const row = document.querySelector(`[data-temp-id="${item.id}"]`);
    //                 if (row) {
    //                     row.classList.remove('border-amber-400');
    //                     row.classList.add('border-green-500');
    //                     row.querySelector('.sync-badge').classList.add('hidden');
    //                 }
    //                 log('Synced: ' + item.lot_number, 'success');
    //             } else {
    //                 // Server error (e.g. validation), stop syncing to avoid infinite loop on bad data
    //                 log('Sync failed for item: ' + res.message, 'error');
    //                 break;
    //             }
    //         } catch (err) {
    //             log('Sync Network Error, pausing...', 'error');
    //             break;
    //         }
    //         // Small delay between syncs
    //         await new Promise(r => setTimeout(r, 500));
    //     }
        
    //     isSyncing = false;
    //     syncIndicator.classList.replace('flex', 'hidden');
    //     if (offlineQueue.length === 0) {
    //         log('All items synced successfully!', 'success');
    //     }
    // }

    // Run sync on start if items exist
    updateNetworkInfo();
    window.addEventListener('online', updateNetworkInfo);
    window.addEventListener('offline', updateNetworkInfo);
    
    // Listen to Global Sync Events to update UI if items are synced in background
    window.addEventListener('fg-item-synced', (e) => {
        const syncedId = e.detail.id;
        const row = document.querySelector(`[data-temp-id="${syncedId}"]`);
        if (row) {
            row.classList.remove('border-amber-400', 'bg-amber-50/30');
            row.classList.add('border-green-500');
            const badge = row.querySelector('.sync-badge');
            if (badge) badge.remove();
        }
    });

    // Remove the local call to processQueue on start
    // if (offlineQueue.length > 0) {
    //     processQueue();
    // }

    function parseBarcode(barcode) {
        // Format: Part|Customer|Qty|Lot
        // Contoh: 64521-K1Y -DC00|1200058|10|105-29-9-25-1
        // Contoh: 74252-VT010|PT TMMIN|6|106-25-1-26-3

        if (!barcode.includes('|')) {
            // Fallback: Jika polos, anggap Part Number, sisanya minta input manual?
            // Untuk sekarang return null agar user terpaksa standar
            return null; // Atau handle 'dumb' barcode
        }

        const parts = barcode.split('|');
        if (parts.length < 4) return null; // Minimal 4 segmen

        const partNumber = parts[0].trim();
        const customer = parts[1].trim();
        const qty = parseInt(parts[2].trim()) || 0;
        const fullLot = parts[3].trim(); // 106-25-1-26-3

        // Parse Lot Detail: 106-25-1-26-3
        // [Plan][Mesin] - [DD]-[MM]-[YY] - [Shift]
        let noPlanning = '';
        let noMesin = '';
        let tanggalProduksi = null;
        let shift = '';

        try {
            // Split by dash
            // Asumsi format: KKK-DD-MM-YY-S 
            // KKK = Kode Gabungan Plan & Mesin
            // DD-MM-YY = Tanggal
            // S = Shift

            const lotParts = fullLot.split('-');
            
            if (lotParts.length >= 5) {
                // Bagian 1: 106 (Plan + Mesin)
                const code = lotParts[0];
                if (code.length === 3) {
                    noPlanning = code.substring(0, 1);
                    noMesin = code.substring(1);
                } else if (code.length >= 4) {
                    noPlanning = code.substring(0, code.length - 2); // Asumsi mesin 2 digit terakhir
                    noMesin = code.substring(code.length - 2);
                } else {
                    noPlanning = '0'; 
                    noMesin = code;
                }

                // Bagian 2,3,4: Tanggal (25-1-26) -> DD-MM-YY
                const d = lotParts[1].padStart(2, '0');
                const m = lotParts[2].padStart(2, '0');
                let y = lotParts[3];
                if (y.length === 2) y = '20' + y; // 26 -> 2026
                
                tanggalProduksi = `${y}-${m}-${d}`;

                // Bagian 5: Shift
                shift = lotParts[4];
            }
        } catch (e) {
            console.warn("Failed parsing lot details", e);
        }

        return {
            part_number: partNumber,
            customer: customer,
            qty: qty,
            lot_number: fullLot,
            no_planning: noPlanning,
            no_mesin: noMesin,
            tanggal_produksi: tanggalProduksi,
            shift: shift,
            lot_produksi: barcode // Full raw barcode string
        };
    }

    function updateUI(savedData, parsedData, isOffline = false) {
        // Show Preview
        previewCard.classList.remove('hidden');
        prevPart.textContent = parsedData.part_number;
        prevQty.textContent = parsedData.qty + ' PCS';
        prevLot.textContent = parsedData.lot_number;
        prevLot.title = parsedData.lot_number;
        prevCust.textContent = parsedData.customer || '-';
        
        prevPlan.textContent = parsedData.no_planning || '-';
        prevMesin.textContent = parsedData.no_mesin || '-';
        prevDate.textContent = parsedData.tanggal_produksi || '-';
        prevShift.textContent = parsedData.shift || '-';

        // Add to History
        emptyState.classList.add('hidden');
        scanList.classList.remove('hidden');
        totalScans++;
        scanCount.textContent = totalScans;

        const li = document.createElement('li');
        li.dataset.tempId = isOffline ? savedData.id : '';
        li.className = `p-4 hover:bg-gray-50 transition-colors last-scan-row border-l-4 ${isOffline ? 'border-amber-400 bg-amber-50/30' : 'border-green-500'}`;
        
        li.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-2">
                        <h4 class="font-bold text-gray-800 text-sm">${parsedData.part_number}</h4>
                        ${isOffline ? `
                            <span class="sync-badge flex items-center gap-1 text-[9px] bg-amber-100 text-amber-700 px-1.5 py-0.5 rounded font-bold uppercase tracking-wider">
                                <svg class="w-2 h-2 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Pending
                            </span>
                        ` : ''}
                    </div>
                    <p class="text-xs text-gray-500 mt-1 font-mono">${parsedData.lot_number}</p>
                    <div class="flex gap-2 mt-1">
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 rounded">M${parsedData.no_mesin}</span>
                        <span class="text-[10px] bg-gray-100 text-gray-600 px-1.5 rounded">S${parsedData.shift}</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="block text-lg font-bold text-blue-600">${parsedData.qty}</span>
                    <span class="text-xs text-gray-400">PCS</span>
                </div>
            </div>
            <div class="mt-2 flex justify-between items-center text-[10px] text-gray-400">
                <span>${new Date().toLocaleTimeString()}</span>
                <span>${parsedData.customer || ''}</span>
            </div>
        `;
        scanList.insertBefore(li, scanList.firstChild);
    }

    function showStatus(msg, colorClass) {
        scanStatus.textContent = msg;
        scanStatus.className = `mt-4 h-6 text-sm font-medium transition-opacity opacity-100 ${colorClass}`;
        
        // Hide after 3s
        setTimeout(() => {
            scanStatus.classList.add('opacity-0');
        }, 3000);
    }

    function playSound(type) {
        // Audio dinonaktifkan sementara
        // playBeep(type);
    }
});
</script>
@endpush
