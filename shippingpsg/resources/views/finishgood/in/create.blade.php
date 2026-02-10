@extends('layout.app')

@push('styles')
<style>
    .scanner-active {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
        transform: scale(1.01);
    }
    .last-scan-row {
        animation: slideIn 0.3s ease-out;
    }
    @keyframes slideIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    /* Custom Scrollbar for History */
    #scanHistoryContainer::-webkit-scrollbar {
        width: 6px;
    }
    #scanHistoryContainer::-webkit-scrollbar-track {
        background: transparent;
    }
    #scanHistoryContainer::-webkit-scrollbar-thumb {
        background: #e2e8f0;
        border-radius: 10px;
    }
    #scanHistoryContainer::-webkit-scrollbar-thumb:hover {
        background: #cbd5e1;
    }
</style>
@endpush

@section('content')
<div class="w-full px-2 md:px-4 lg:px-6">
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

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 md:gap-6">
        <!-- Left Column: Scanner Section -->
        <div class="lg:col-span-3 space-y-4 md:space-y-6">
            
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

            {{-- Processed Data Preview (Premium Design) --}}
            <div id="previewCard" class="bg-white rounded-2xl shadow-xl border border-blue-50/50 hidden transition-all duration-500 overflow-hidden">
                <!-- Header with indicator -->
                <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-5 py-3 flex items-center justify-between">
                    <h3 class="text-xs font-bold text-white/90 uppercase tracking-widest flex items-center gap-2">
                        <svg class="w-4 h-4 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Last Scanned Data
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="text-[10px] font-mono text-blue-100/70" id="scanTime">--:--:--</span>
                    </div>
                </div>

                <div class="p-5 md:p-6 grid grid-cols-1 md:grid-cols-12 gap-5 items-center">
                    <!-- Left: Part & Main Info -->
                    <div class="md:col-span-8 flex flex-col gap-4">
                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mb-1">Part Number</span>
                            <div class="flex items-center gap-3">
                                <span class="text-lg md:text-3xl font-black text-gray-900 leading-none" id="prevPart">--</span>
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold" id="prevCust">--</span>
                            </div>
                        </div>

                        <div class="flex flex-col">
                            <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mb-1">Lot Number / ID Label</span>
                            <span class="text-xs font-mono text-gray-600 bg-gray-50 border border-gray-100 px-3 py-1.5 rounded-lg w-fit" id="prevLot">--</span>
                        </div>
                    </div>

                    <!-- Middle: Qty (Big) -->
                    <div class="md:col-span-4 flex flex-col items-center md:items-end justify-center pt-4 md:pt-0 border-t md:border-t-0 md:border-l border-gray-100">
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-tight mb-1">Total Quantity</span>
                        <div class="flex items-baseline gap-1">
                            <span class="text-4xl font-black text-indigo-600 leading-none" id="prevQty">0</span>
                            <span class="text-sm font-bold text-gray-400 uppercase">Pcs</span>
                        </div>
                    </div>

                    <!-- Bottom: Meta Badges -->
                    <div class="md:col-span-12 mt-2 pt-4 border-t border-gray-50 grid grid-cols-4 gap-2">
                        <div class="bg-gray-50/50 p-2 rounded-xl flex flex-col items-center">
                            <span class="text-[9px] text-gray-400 uppercase font-bold">Plan</span>
                            <span class="text-xs font-black text-gray-700" id="prevPlan">-</span>
                        </div>
                        <div class="bg-gray-50/50 p-2 rounded-xl flex flex-col items-center">
                            <span class="text-[9px] text-gray-400 uppercase font-bold">Mesin</span>
                            <span class="text-xs font-black text-gray-700" id="prevMesin">-</span>
                        </div>
                        <div class="bg-gray-50/50 p-2 rounded-xl flex flex-col items-center">
                            <span class="text-[9px] text-gray-400 uppercase font-bold">Shift</span>
                            <span class="text-xs font-black text-gray-700" id="prevShift">-</span>
                        </div>
                        <div class="bg-gray-50/50 p-2 rounded-xl flex flex-col items-center">
                            <span class="text-[9px] text-gray-400 uppercase font-bold">Prod. Date</span>
                            <span class="text-xs font-black text-gray-700" id="prevDate">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: History --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full flex flex-col max-h-[calc(100vh-140px)]">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-t-xl">
                    <h3 class="font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Recent Scans
                    </h3>
                    <div class="flex items-center gap-3">
                        <div id="syncIndicator" class="hidden items-center gap-1 text-[10px] text-indigo-600 font-bold bg-indigo-50 px-2 py-0.5 rounded-full animate-pulse">
                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Syncing...
                        </div>
                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full font-bold" id="scanCount">0</span>
                        <button type="button" id="finishBatchBtn" class="bg-indigo-600 text-white text-[10px] font-black uppercase tracking-widest px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm">
                            Selesai
                        </button>
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

<!-- Navigation Confirmation Modal (Summary) -->
<div id="exitConfirmationModal" class="fixed inset-0 z-[9999] hidden">
    <!-- Backdrop -->
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    
    <!-- Modal Content -->
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg animate-slide-up">
                <!-- Header -->
                <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        Ringkasan Hasil Scan
                    </h3>
                    <button id="closeModalBtn" class="text-white/80 hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-6 font-sans">
                    <div class="flex flex-col gap-4">
                        <div class="text-sm text-gray-600 font-medium">
                            Anda baru saja menscan item berikut. Apakah Anda yakin ingin meninggalkan halaman ini?
                        </div>
                        
                        <!-- Summary List -->
                        <div id="modalSummaryContainer" class="max-h-[300px] overflow-y-auto pr-2 custom-scrollbar">
                            <div class="space-y-3" id="modalSummaryList">
                                <!-- Injected via JS -->
                            </div>
                        </div>

                        <!-- Total Footer -->
                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center px-2">
                            <div class="text-xs text-gray-400 uppercase font-bold">Total Terhitung</div>
                            <div class="flex gap-4">
                                <div class="text-right">
                                    <span class="block text-lg font-black text-indigo-600" id="modalTotalPcs">0</span>
                                    <span class="text-[9px] text-gray-500 uppercase font-bold">Total Pcs</span>
                                </div>
                                <div class="text-right">
                                    <span class="block text-lg font-black text-blue-600" id="modalTotalBox">0</span>
                                    <span class="text-[9px] text-gray-500 uppercase font-bold">Total Box</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="bg-gray-50 px-6 py-4 flex flex-col sm:flex-row-reverse gap-3">
                    <button type="button" id="confirmExitBtn" class="w-full inline-flex justify-center rounded-xl bg-indigo-600 px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-indigo-700 transition-all focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 sm:w-auto">
                        Selesai & Keluar
                    </button>
                    <button type="button" id="cancelExitBtn" class="w-full inline-flex justify-center rounded-xl bg-white px-6 py-3 text-sm font-bold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-all sm:w-auto">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- STATE ---
    let sessionScans = {}; 
    let allowNavigation = false;
    let pendingTargetUrl = null;
    let totalScans = 0;

    // --- ELEMENTS ---
    const modal = document.getElementById('exitConfirmationModal');
    const modalList = document.getElementById('modalSummaryList');
    const modalTotalPcs = document.getElementById('modalTotalPcs');
    const modalTotalBox = document.getElementById('modalTotalBox');
    const finishBatchBtn = document.getElementById('finishBatchBtn');
    const scannerInput = document.getElementById('scannerInput');
    const scanCount = document.getElementById('scanCount');
    const emptyState = document.getElementById('emptyState');
    const scanList = document.getElementById('scanList');
    const previewCard = document.getElementById('previewCard');

    // Focus input on start
    if (scannerInput) scannerInput.focus();

    // --- AUDIO ---
    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    function playBeep(type) {
        try {
            if (audioCtx.state === 'suspended') audioCtx.resume();
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.connect(gain); gain.connect(audioCtx.destination);
            if (type === 'success') {
                osc.frequency.setValueAtTime(1200, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.2, audioCtx.currentTime);
                osc.start(); osc.stop(audioCtx.currentTime + 0.1);
            } else if (type === 'error') {
                osc.type = 'sawtooth';
                osc.frequency.setValueAtTime(150, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.3, audioCtx.currentTime);
                osc.start(); osc.stop(audioCtx.currentTime + 0.4);
            } else {
                osc.type = 'triangle';
                osc.frequency.setValueAtTime(800, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
                osc.start(); osc.stop(audioCtx.currentTime + 0.1);
            }
        } catch (e) { console.error('Audio error:', e); }
    }

    // --- UTILS ---
    function updateSessionSummary(parsedData) {
        const partNo = parsedData.part_number;
        if (!sessionScans[partNo]) {
            sessionScans[partNo] = { pcs: 0, boxes: 0 };
        }
        sessionScans[partNo].pcs += parseInt(parsedData.qty || 0);
        sessionScans[partNo].boxes += 1;
    }

    function showExitModal(targetUrl = null) {
        pendingTargetUrl = targetUrl;
        if (!modalList) return;
        
        modalList.innerHTML = '';
        let totalPcsSum = 0, totalBoxSum = 0;
        Object.keys(sessionScans).forEach(partNo => {
            const data = sessionScans[partNo];
            totalPcsSum += data.pcs; totalBoxSum += data.boxes;
            const div = document.createElement('div');
            div.className = "bg-gray-50 border border-gray-100 rounded-2xl p-4 flex justify-between items-center shadow-sm mb-3";
            div.innerHTML = `
                <div class="flex-1 min-w-0 pr-3">
                    <div class="text-[10px] text-indigo-500 font-bold uppercase tracking-widest mb-1">Part Number</div>
                    <div class="text-sm md:text-base font-black text-gray-900 truncate">${partNo}</div>
                </div>
                <div class="flex gap-6">
                    <div class="text-right">
                        <div class="text-[9px] text-gray-400 font-bold uppercase mb-1">Pcs</div>
                        <div class="text-base font-black text-gray-800">${data.pcs.toLocaleString()}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] text-gray-400 font-bold uppercase mb-1">Box</div>
                        <div class="text-base font-black text-indigo-600">${data.boxes.toLocaleString()}</div>
                    </div>
                </div>
            `;
            modalList.appendChild(div);
        });
        if (modalTotalPcs) modalTotalPcs.textContent = totalPcsSum.toLocaleString();
        if (modalTotalBox) modalTotalBox.textContent = totalBoxSum.toLocaleString();
        if (modal) modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    // --- EVENTS ---
    if (finishBatchBtn) {
        finishBatchBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (Object.keys(sessionScans).length > 0) showExitModal();
            else showToast('Belum ada data yang di-scan untuk sesi ini.', 'warning');
        });
    }

    const confirmExitBtn = document.getElementById('confirmExitBtn');
    if (confirmExitBtn) {
        confirmExitBtn.onclick = () => {
            allowNavigation = true;
            window.location.href = pendingTargetUrl || "{{ route('finishgood.in.index') }}";
        };
    }

    const cancelExitBtn = document.getElementById('cancelExitBtn');
    if (cancelExitBtn) {
        cancelExitBtn.onclick = () => {
            if (modal) modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };
    }

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (link && !allowNavigation) {
            const url = link.getAttribute('href');
            if (url && url !== '#' && !url.startsWith('javascript:') && Object.keys(sessionScans).length > 0) {
                e.preventDefault();
                showExitModal(url);
            }
        }
        if (!['INPUT', 'TEXTAREA', 'BUTTON'].includes(e.target.tagName)) {
            if (modal && modal.contains(e.target)) return;
            scannerInput?.focus();
        }
    });

    // --- SCANNER INPUT LOGIC ---
    let typingTimer;
    let isProcessing = false;
    const doneTypingInterval = 80; // Faster response (80ms)

    if (scannerInput) {
        scannerInput.addEventListener('input', () => {
            clearTimeout(typingTimer);
            if (scannerInput.value.trim().length > 5) {
                typingTimer = setTimeout(() => {
                    const val = scannerInput.value.trim();
                    if (val && !isProcessing) handleScan(val);
                }, doneTypingInterval);
            }
        });

        scannerInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(typingTimer);
                const val = scannerInput.value.trim();
                if (val && !isProcessing) handleScan(val);
            }
        });
    }

    async function handleScan(barcode) {
        if (isProcessing) return;
        isProcessing = true;
        
        try {
            const parsedData = await parseBarcode(barcode);
            
            if (!parsedData) {
                showToast('Format barcode tidak dikenal!', 'error');
                playBeep('error');
                scannerInput.value = ''; // Clear bad input
                return;
            }

            if (parsedData.qty <= 0) {
                showToast('⚠️ Gagal: Jumlah (Qty) tidak boleh kosong.', 'error');
                playBeep('error');
                scannerInput.value = '';
                return;
            }

            // Priority: Send to server if online
            if (navigator.onLine && window.syncDataToServer) {
                const response = await window.syncDataToServer(parsedData);
                if (response.success) {
                    playBeep('success');
                    updateSessionSummary(parsedData);
                    updateUI(response.data, parsedData, false);
                    scannerInput.value = '';
                    showToast('Scan Berhasil: ' + parsedData.part_number, 'success', 2000);
                } else {
                    let msg = response.message || 'Error simpan data';
                    if (response.errors) msg += ': ' + Object.values(response.errors).flat().join(', ');
                    showToast(msg, 'error');
                    playBeep('error');
                    scannerInput.select();
                }
            } else {
                handleOfflineStorage(parsedData);
            }
        } catch (error) {
            console.error('Scan error:', error);
            handleOfflineStorage(parsedData);
        } finally {
            isProcessing = false;
            scannerInput.focus();
        }
    }

    function handleOfflineStorage(data) {
        const tempId = 'off_' + Date.now();
        const offlineItem = { ...data, id: tempId, is_offline: true, timestamp: new Date().toISOString() };
        let queue = JSON.parse(localStorage.getItem('fg_scan_queue') || '[]');
        queue.push(offlineItem);
        localStorage.setItem('fg_scan_queue', JSON.stringify(queue));
        
        playBeep('offline');
        updateSessionSummary(data);
        updateUI(offlineItem, data, true);
        scannerInput.value = '';
        scannerInput.focus();
        showToast('Disimpan Offline: ' + data.part_number, 'warning', 2000);
    }

    async function parseBarcode(barcode) {
        // Mode 1: Standard Pipe Format (Part|Cust|Qty|Lot)
        if (barcode.includes('|')) {
            const parts = barcode.split('|');
            if (parts.length < 4) return null;
            
            const fullLot = parts[3].trim();
            let plan = '', machine = '', date = null, shift = '';
            
            const lotParts = fullLot.split('-');
            if (lotParts.length >= 5) {
                const code = lotParts[0];
                plan = code.length >= 4 ? code.substring(0, code.length-2) : code.substring(0,1);
                machine = code.length >= 4 ? code.substring(code.length-2) : code.substring(1);
                date = `${lotParts[3].length === 2 ? '20'+lotParts[3] : lotParts[3]}-${lotParts[2].padStart(2, '0')}-${lotParts[1].padStart(2, '0')}`;
                shift = lotParts[4];
            }

            return {
                part_number: parts[0].trim(), customer: parts[1].trim(), qty: parseInt(parts[2].trim()) || 0,
                lot_number: fullLot, lot_produksi: barcode, no_planning: plan, no_mesin: machine,
                tanggal_produksi: date, shift: shift
            };
        }
        
        // Mode 2: Inoac / Single Label (API Check)
        try {
            const res = await fetch(`{{ url('/api/part/check') }}/${encodeURIComponent(barcode.trim())}`);
            if (res.ok) {
                const info = await res.json();
                if (info.customer?.toUpperCase().includes('INOAC')) {
                    return {
                        part_number: info.nomor_part, customer: info.customer, qty: info.qty_packing_box || 24,
                        lot_number: null, lot_produksi: barcode, no_planning: null, no_mesin: null,
                        tanggal_produksi: null, shift: null
                    };
                }
            }
        } catch (e) { console.error('API Parse error:', e); }
        
        return null;
    }

    function updateUI(savedData, parsedData, isOffline) {
        if (previewCard) previewCard.classList.remove('hidden');
        
        // Update Last Scanned Card
        const els = {
            scanTime: document.getElementById('scanTime'),
            prevPart: document.getElementById('prevPart'),
            prevQty: document.getElementById('prevQty'),
            prevLot: document.getElementById('prevLot'),
            prevCust: document.getElementById('prevCust'),
            prevPlan: document.getElementById('prevPlan'),
            prevMesin: document.getElementById('prevMesin'),
            prevShift: document.getElementById('prevShift'),
            prevDate: document.getElementById('prevDate')
        };

        if (els.scanTime) els.scanTime.textContent = new Date().toLocaleTimeString();
        if (els.prevPart) els.prevPart.textContent = parsedData.part_number;
        if (els.prevQty) els.prevQty.textContent = parsedData.qty.toLocaleString();
        if (els.prevLot) els.prevLot.textContent = parsedData.lot_number || '-';
        if (els.prevCust) els.prevCust.textContent = parsedData.customer || '-';
        if (els.prevPlan) els.prevPlan.textContent = parsedData.no_planning || '-';
        if (els.prevMesin) els.prevMesin.textContent = parsedData.no_mesin || '-';
        if (els.prevShift) els.prevShift.textContent = parsedData.shift || '-';
        if (els.prevDate) els.prevDate.textContent = parsedData.tanggal_produksi || '-';

        // Update Empty State & Counter
        if (emptyState) emptyState.classList.add('hidden');
        if (scanList) scanList.classList.remove('hidden');
        if (scanCount) scanCount.textContent = ++totalScans;

        // Add to History
        const li = document.createElement('li');
        li.dataset.tempId = isOffline ? savedData.id : '';
        li.className = `p-4 hover:bg-gray-50 transition-all border-l-4 last-scan-row animate-fade-in-down ${isOffline ? 'border-amber-400 bg-amber-50/20' : 'border-indigo-600 bg-white'}`;
        li.innerHTML = `
            <div class="flex items-center justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-black text-gray-900 truncate">${parsedData.part_number}</span>
                        <span class="text-[8px] px-1.5 py-0.5 rounded-full font-bold uppercase ${isOffline ? 'bg-amber-100 text-amber-700' : 'bg-indigo-50 text-indigo-600'}">${isOffline ? 'Pending' : 'Synced'}</span>
                    </div>
                    <div class="flex flex-wrap gap-2 text-[10px] text-gray-400 font-medium tracking-tight">
                        <span class="bg-gray-100 px-1 rounded">Lot: ${parsedData.lot_number || '-'}</span>
                        <span class="bg-gray-100 px-1 rounded">M${parsedData.no_mesin || '-'} S${parsedData.shift || '-'}</span>
                    </div>
                </div>
                <div class="text-right">
                    <span class="text-xl font-black text-indigo-600">${parsedData.qty}</span>
                    <span class="text-[9px] font-bold text-gray-400 block uppercase">PCS</span>
                </div>
            </div>
        `;
        
        if (scanList) {
            scanList.insertBefore(li, scanList.firstChild);
            // Limit history to last 50 items to prevent DOM bloat
            if (scanList.children.length > 50) {
                scanList.lastElementChild.remove();
            }
        }
    }
});
</script>
@endpush
