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
<div class="w-full px-6">
    <!-- Header Section -->
    <div class="bg-white p-3 md:p-4 rounded-xl shadow-sm border border-gray-100 mb-4 md:mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-3 md:gap-4">
            <div>
                <a href="{{ route('spk.index') }}" class="text-[10px] text-gray-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to SPK List
                </a>
                <h1 class="text-base md:text-2xl font-bold text-gray-900 leading-tight">Scan Out - {{ $spk->nomor_spk }}</h1>
                <p class="text-[9px] md:text-sm text-gray-500 mt-0.5">{{ $spk->customer->nama_perusahaan ?? '-' }} | {{ $spk->plantgate->nama_plantgate ?? '-' }}</p>
            </div>
            
            <div class="flex items-center gap-2 md:gap-4 w-full md:w-auto">
                <div class="flex-1 md:flex-none">
                    <label for="noSuratJalan" class="block text-[9px] md:text-[10px] font-bold text-gray-400 uppercase mb-0.5">No Surat Jalan</label>
                    <input type="text" id="noSuratJalan" value="{{ $isNewCycle ? '' : $spk->no_surat_jalan }}" class="px-2 md:px-3 py-1.5 md:py-2 border rounded-lg text-xs md:text-sm focus:ring-2 focus:ring-indigo-500 outline-none w-full md:w-48" placeholder="Input No SJ...">
                </div>
                <div class="bg-indigo-50 px-3 md:px-4 py-1.5 md:py-2 rounded-lg text-center flex-1 md:flex-none">
                    <p class="text-[10px] md:text-xs text-indigo-600 font-bold uppercase">Shipment Status</p>
                    <p class="text-xs md:text-sm font-bold text-indigo-900 mt-0.5">Ready to Ship</p>
                </div>
            </div>
        </div>

        <!-- Progress Bar Summary -->
        <div class="mt-4 md:mt-6 grid grid-cols-3 gap-2 md:gap-4 text-center divide-x divide-gray-100">
            <div>
                <p class="text-[10px] md:text-xs text-gray-500 uppercase">Target Total</p>
                <p class="text-base md:text-xl font-bold text-gray-800">{{ number_format($totalTarget) }}</p>
            </div>
            <div>
                <p class="text-[10px] md:text-xs text-gray-500 uppercase">Scanned</p>
                <p class="text-base md:text-xl font-bold text-green-600" id="grandTotalScanned">{{ number_format($totalScanned) }}</p>
            </div>
            <div>
                <p class="text-[10px] md:text-xs text-gray-500 uppercase">Balance</p>
                <p class="text-base md:text-xl font-bold text-red-500" id="grandTotalBalance">{{ number_format($totalBalanceGlobal) }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
        <!-- Left Column: Scanner Section -->
        <div class="md:col-span-2 space-y-4 md:space-y-6">
            
            <!-- Scanner Card -->
            <div class="bg-white rounded-xl md:rounded-2xl shadow-sm border border-gray-100 p-4 md:p-8 text-center relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-indigo-600"></div>
                
                <h2 class="text-lg md:text-xl font-bold text-gray-900 mb-4">Ready to Scan</h2>
                
                <div class="relative max-w-xl mx-auto">
                    <input type="text" 
                           id="scannerInput" 
                           class="w-full pl-4 pr-10 py-3 md:py-4 bg-gray-50 border-2 border-gray-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-lg md:rounded-xl text-center font-mono text-sm md:text-lg shadow-sm transition-all duration-300"
                           placeholder="Scan barcode..." 
                           autofocus 
                           autocomplete="off">
                    
                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <span id="typingIndicator" class="h-2 w-2 md:h-3 md:w-3 rounded-full bg-green-400 animate-pulse"></span>
                    </div>
                </div>
                
                <div id="scanStatus" class="mt-2 text-xs md:text-sm font-medium transition-opacity opacity-0 h-5">...</div>
            </div>

            <!-- Part Progress List -->
            <div class="space-y-3">
                 <div class="flex justify-between items-center px-1">
                     <h3 class="font-bold text-gray-700 text-sm md:text-base">Progress per Part</h3>
                     <!-- Mobile Navigation Controls -->
                     <div class="md:hidden flex items-center gap-2" id="mobileNavControls" style="display: none;">
                         <button type="button" onclick="prevSlide()" class="p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                         </button>
                         <span class="text-xs font-medium text-gray-500" id="slideIndicator">1 / 1</span>
                         <button type="button" onclick="nextSlide()" class="p-1 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
                             <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                         </button>
                     </div>
                 </div>

                 <!-- Container -->
                 <div id="partProgressContainer" class="relative group">
                    <div id="partSlider" class="md:block md:space-y-3 flex md:flex-col overflow-x-hidden md:overflow-visible transition-all duration-300">
                        @foreach($progress as $id => $p)
                        <!-- Card Item -->
                        <div class="w-full md:w-auto flex-shrink-0 bg-white border border-gray-100 rounded-lg p-3 shadow-sm transition-transform duration-300 part-card" 
                             id="part-row-{{ $id }}" 
                             data-index="{{ $loop->index }}" 
                             data-previous="{{ $p['previous_scanned'] }}"
                             data-target="{{ $p['target'] }}">
                            <div class="flex justify-between items-start mb-2">
                                 <div>
                                     <p class="font-bold text-gray-800 text-xs md:text-sm">{{ $p['part_nomor'] }}</p>
                                     <p class="text-[10px] md:text-xs text-gray-500">{{ $p['part_nama'] }}</p>
                                 </div>
                                 <div class="text-right">
                                     <span class="text-[10px] md:text-xs font-mono bg-gray-100 px-2 py-1 rounded">Target: {{ $p['target'] }}</span>
                                 </div>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 md:h-2.5 mb-1">
                                <div class="bg-indigo-600 h-2 md:h-2.5 rounded-full transition-all duration-500" 
                                     id="progress-bar-{{ $id }}"
                                     style="width: {{ $p['target'] > 0 ? ($p['scanned'] / $p['target'] * 100) : 0 }}%"></div>
                            </div>
                            <div class="flex justify-between text-[10px] md:text-xs text-gray-600">
                                 <span>Scanned: <strong id="val-scanned-{{ $id }}" class="text-indigo-600">{{ $p['scanned'] }}</strong></span>
                                 <span>Balance: <strong id="val-balance-{{ $id }}" class="text-red-500">{{ $p['balance'] }}</strong></span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                 </div>
            </div>
        </div>

        {{-- Right Column: History --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 h-full flex flex-col max-h-[800px]">
                <div class="p-3 md:p-4 border-b border-gray-100 flex items-center justify-between bg-gray-50/50 rounded-t-xl">
                    <h3 class="font-semibold text-gray-800 text-sm md:text-base">Scan History</h3>
                    <span class="bg-blue-100 text-blue-700 text-[10px] md:text-xs px-2 py-0.5 rounded-full font-bold" id="scanCount">0</span>
                </div>
                
                <div class="flex-1 overflow-y-auto" id="scanHistoryContainer">
                    <ul id="scanList" class="divide-y divide-gray-100">
                        <!-- Loaded via JS or empty initially since cycle logic separates views -->
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Quality Checksheet & Submit Section (Bottom) -->
    <div class="mt-4 md:mt-6 bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-6">
        <div class="max-w-4xl mx-auto">
            <!-- Quality Checksheet -->
            <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border-2 border-yellow-300 rounded-xl p-4 md:p-6 mb-4">
                <div class="flex items-center gap-2 mb-3 md:mb-4">
                    <svg class="w-5 h-5 md:w-6 md:h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="font-bold text-gray-800 text-sm md:text-lg">Quality Checksheet</h3>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 md:gap-3">
                    <label class="flex items-center gap-2 md:gap-3 text-xs md:text-sm cursor-pointer hover:bg-yellow-100 p-2 md:p-3 rounded-lg transition-colors border border-transparent hover:border-yellow-300">
                        <input type="checkbox" class="quality-check w-4 h-4 md:w-5 md:h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500">
                        <span class="font-medium">✓ Label vs Part Sesuai</span>
                    </label>
                    <label class="flex items-center gap-2 md:gap-3 text-xs md:text-sm cursor-pointer hover:bg-yellow-100 p-2 md:p-3 rounded-lg transition-colors border border-transparent hover:border-yellow-300">
                        <input type="checkbox" class="quality-check w-4 h-4 md:w-5 md:h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500">
                        <span class="font-medium">✓ Label vs QTY Sesuai</span>
                    </label>
                    <label class="flex items-center gap-2 md:gap-3 text-xs md:text-sm cursor-pointer hover:bg-yellow-100 p-2 md:p-3 rounded-lg transition-colors border border-transparent hover:border-yellow-300">
                        <input type="checkbox" class="quality-check w-4 h-4 md:w-5 md:h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500">
                        <span class="font-medium">✓ STD Packing Sesuai</span>
                    </label>
                    <label class="flex items-center gap-2 md:gap-3 text-xs md:text-sm cursor-pointer hover:bg-yellow-100 p-2 md:p-3 rounded-lg transition-colors border border-transparent hover:border-yellow-300">
                        <input type="checkbox" class="quality-check w-4 h-4 md:w-5 md:h-5 text-indigo-600 rounded focus:ring-2 focus:ring-indigo-500">
                        <span class="font-medium">✓ IRD Sudah Dilampirkan</span>
                    </label>
                </div>
            </div>

            <!-- Submit & Print Button -->
            <div class="text-center">
                <button id="printBtn" onclick="submitAndPrint()" disabled class="inline-flex items-center gap-2 px-6 py-3 md:px-8 md:py-4 bg-green-600 hover:bg-green-700 text-white rounded-xl shadow-lg transition-all text-sm md:text-base font-bold disabled:bg-gray-300 disabled:cursor-not-allowed transform hover:scale-105 disabled:transform-none">
                    <svg class="w-4 h-4 md:w-5 md:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Selesai</span>
                </button>
                <p class="text-xs md:text-sm text-gray-500 mt-2 md:mt-3">*Pastikan semua checksheet tercentang dan No SJ terisi</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const MODEL_SPK_ID = {{ $spk->id }};
    let currentCycle = {{ $currentCycle }};
    let scanHistory = []; // Simple local storage for current session history

    const scannerInput = document.getElementById('scannerInput');
    const scanStatus = document.getElementById('scanStatus');
    const scanList = document.getElementById('scanList');
    const scanCount = document.getElementById('scanCount');
    
    // Audio Context
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    function playBeep(type) {
        if (audioContext.state === 'suspended') audioContext.resume();
        const osc = audioContext.createOscillator();
        const gain = audioContext.createGain();
        osc.connect(gain);
        gain.connect(audioContext.destination);
        
        if (type === 'success') {
            osc.frequency.setValueAtTime(1000, audioContext.currentTime);
            osc.frequency.exponentialRampToValueAtTime(500, audioContext.currentTime + 0.1);
            gain.gain.setValueAtTime(0.3, audioContext.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            osc.start();
            osc.stop(audioContext.currentTime + 0.1);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(200, audioContext.currentTime);
            osc.frequency.linearRampToValueAtTime(100, audioContext.currentTime + 0.3);
            gain.gain.setValueAtTime(0.5, audioContext.currentTime);
            gain.gain.linearRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
            osc.start();
            osc.stop(audioContext.currentTime + 0.3);
        }
    }

    // Input Handling
    let typingTimer;
    scannerInput.addEventListener('input', (e) => {
        clearTimeout(typingTimer);
        const val = scannerInput.value;
        if (val.length > 5 && val.includes('|')) {
            typingTimer = setTimeout(() => handleScan(val.trim()), 300);
        }
    });

    scannerInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(typingTimer);
            if (scannerInput.value.trim()) handleScan(scannerInput.value.trim());
        }
    });

    // Auto Focus (disabled when modal is active)
    let modalActive = false;
    document.addEventListener('click', (e) => {
        if (modalActive) return; // Don't auto-focus when modal is open
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON' && e.target.tagName !== 'SELECT') {
            scannerInput.focus();
        }
    });

    async function handleScan(barcode) {
        // Optimistic UI clear
        scannerInput.value = '';
        scannerInput.focus();
        
        try {
            showStatus('Processing...', 'text-blue-500');
            
            const response = await fetch('{{ route("finishgood.out.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    lot_number: barcode,
                    spk_id: MODEL_SPK_ID,
                    cycle: currentCycle
                })
            });

            const result = await response.json();

            if (result.success) {
                playBeep('success');
                showStatus('Success: ' + result.message, 'text-green-600');
                
                // Add to History
                addToHistory(result.data);
                
                // Refresh Page Data (Progress Bars & Totals)
                updateStats(); 
                
                // Auto-slide to the part (Mobile UX)
                if (result.data.part_id) {
                    slideToPart(result.data.part_id);
                }
                
            } else {
                playBeep('error');
                showStatus('Error: ' + result.message, 'text-red-600');
                alert('GAGAL: ' + result.message);
            }
        } catch (e) {
            console.error(e);
            playBeep('error');
            showStatus('System Error', 'text-red-600');
        }
    }

    function showStatus(msg, colorClass) {
        scanStatus.textContent = msg;
        scanStatus.className = `mt-2 text-xs md:text-sm font-medium transition-opacity opacity-100 h-5 ${colorClass}`;
        setTimeout(() => scanStatus.classList.add('opacity-0'), 3000);
    }

    function addToHistory(data) {
        const li = document.createElement('li');
        li.className = 'p-3 hover:bg-gray-50 flex justify-between items-center border-l-4 border-green-500 animate-pulse';
        li.innerHTML = `
            <div>
                <p class="font-bold text-gray-800 text-sm">${data.part_number}</p>
                <p class="text-[10px] text-gray-500">C${data.cycle}</p>
            </div>
            <div class="text-right">
                <span class="font-bold text-blue-600 text-lg">${data.qty}</span> <span class="text-xs">PCS</span>
            </div>
        `;
        scanList.insertBefore(li, scanList.firstChild);
        scanCount.textContent = parseInt(scanCount.textContent) + 1;
        setTimeout(() => li.classList.remove('animate-pulse'), 1000);
    }

    async function updateStats() {
        // Call helper API we created: getPartsBySpk
        try {
            const res = await fetch(`{{ url('/finishgood/out/api/parts') }}/${MODEL_SPK_ID}`);
            const json = await res.json();
            
            if (json.success) {
                let totalScan = 0;
                let totalBal = 0;

                json.data.forEach(part => {
                    const row = document.getElementById(`part-row-${part.part_id}`);
                    if (row) {
                       // Get Global Data
                       const prevScanned = parseInt(row.dataset.previous) || 0;
                       const target = parseInt(row.dataset.target) || 0;
                       const localScanned = part.qty_scanned;
                       
                       // Calculate Global Stats
                       const globalScannedTotal = prevScanned + localScanned;
                       const globalBalance = Math.max(0, target - globalScannedTotal);
                       
                       // Update Text
                       document.getElementById(`val-scanned-${part.part_id}`).textContent = localScanned;
                       document.getElementById(`val-balance-${part.part_id}`).textContent = globalBalance;
                       
                       // Update Bar (Progress based on Global Coverage against Original Target)
                       // Or Local Progress? User wants Global View likely.
                       // Let's visualize Global Progress: (Total Scanned / Target * 100)
                       const pct = target > 0 ? (globalScannedTotal / target * 100) : 0;
                       document.getElementById(`progress-bar-${part.part_id}`).style.width = `${pct}%`;

                       totalScan += localScanned;     // Header: Scanned (Local)
                       totalBal += globalBalance;     // Header: Balance (Global)
                    }
                });

                // Update Grand Totals
                document.getElementById('grandTotalScanned').textContent = totalScan.toLocaleString();
                document.getElementById('grandTotalBalance').textContent = totalBal.toLocaleString();
            }
        } catch (e) {
            console.error("Failed to update stats", e);
        }
    }

    // Cycle Logic
    window.finishCycle = async function() {
        const noSj = document.getElementById('noSuratJalan').value;
        if (!noSj) {
            alert('Silakan isi Nomor Surat Jalan terlebih dahulu.');
            document.getElementById('noSuratJalan').focus();
            return;
        }

        // Check if this is Non-Inoac and has balance
        const customerType = '{{ $spk->customer->customer_type ?? "non-inoac" }}';
        const totalTarget = {{ $totalTarget }};
        const totalScanned = parseInt(document.getElementById('grandTotalScanned').textContent.replace(/,/g, ''));
        const hasBalance = totalScanned < totalTarget;

        let splitReason = null;
        let nextDeadline = null;

        // Show modal for Non-Inoac with balance
        if (customerType === 'non-inoac' && hasBalance) {
            const balance = totalTarget - totalScanned;
            const result = await showSplitModal(balance);
            
            if (!result) return; // User cancelled
            
            splitReason = result.reason;
            nextDeadline = result.deadline;
        }

        if (!confirm(`Yakin ingin menyubmit pengiriman ini dan menerbitkan Surat Jalan?`)) return;

        try {
             const response = await fetch('{{ route("finishgood.out.close-cycle", $spk->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    no_surat_jalan: noSj,
                    split_reason: splitReason,
                    next_deadline_time: nextDeadline,
                    split_nomor_plat: result.nomorPlat
                })
            });
            
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                window.location.href = '{{ route("finishgood.out.index") }}';
            } else {
                alert('Gagal: ' + result.message);
            }

        } catch (e) {
            alert('Error closing cycle');
        }
    };

    // Modal for Split Reason & Next Deadline
    async function showSplitModal(balance) {
        return new Promise((resolve) => {
            modalActive = true; // Disable auto-focus
            
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 animate-fade-in">
                    <div class="mb-4">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-900">Pengiriman Kurang</h3>
                                <p class="text-sm text-gray-500">Sisa <strong class="text-red-600">${balance.toLocaleString()} PCS</strong> belum dikirim</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Kurang Kirim</label>
                            <select id="splitReason" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Pilih Alasan --</option>
                                <option value="Truk Penuh">Truk Penuh</option>
                                <option value="Produksi Belum Selesai">Produksi Belum Selesai</option>
                                <option value="Sopir Buru-buru">Sopir Buru-buru</option>
                                <option value="Barang Rusak/NG">Barang Rusak/NG</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Plat Truck (SPK Baru)</label>
                            <select id="splitNomorPlat" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Kosongkan jika belum tahu --</option>
                                @foreach($kendaraans as $k)
                                    <option value="{{ $k->nopol_kendaraan }}" {{ ($spk->nomor_plat == $k->nopol_kendaraan) ? 'selected' : '' }}>
                                        {{ $k->nopol_kendaraan }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Nomor plat untuk SPK sisa (bisa beda truck)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">Rencana Kirim Sisa (Jam)</label>
                            <input type="time" id="nextDeadline" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                            <p class="text-xs text-gray-500 mt-1">Estimasi jam pengiriman berikutnya (opsional)</p>
                        </div>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button id="cancelSplit" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium text-gray-700">
                            Batal
                        </button>
                        <button id="confirmSplit" class="flex-1 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-bold">
                            Lanjutkan
                        </button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Focus on first field
            setTimeout(() => document.getElementById('splitReason').focus(), 100);

            // Handle confirm
            document.getElementById('confirmSplit').onclick = () => {
                const reason = document.getElementById('splitReason').value;
                const deadline = document.getElementById('nextDeadline').value;
                const nomorPlat = document.getElementById('splitNomorPlat').value.trim();

                if (!reason) {
                    alert('Silakan pilih alasan kurang kirim.');
                    return;
                }

                modal.remove();
                modalActive = false; // Re-enable auto-focus
                resolve({ reason, deadline: deadline || null, nomorPlat: nomorPlat || null });
            };

            // Handle cancel
            document.getElementById('cancelSplit').onclick = () => {
                modal.remove();
                modalActive = false; // Re-enable auto-focus
                resolve(null);
            };

            // Prevent click outside from closing (removed this behavior)
        });
    }

    // Quality Checksheet Validation + No SJ Check
    const qualityChecks = document.querySelectorAll('.quality-check');
    const printBtn = document.getElementById('printBtn');
    const noSjInput = document.getElementById('noSuratJalan');

    function updatePrintButton() {
        const allChecked = Array.from(qualityChecks).every(check => check.checked);
        const noSjFilled = noSjInput.value.trim() !== '';
        const canPrint = allChecked && noSjFilled;
        
        printBtn.disabled = !canPrint;
        
        if (!allChecked && !noSjFilled) {
            printBtn.title = 'Lengkapi checksheet dan No SJ terlebih dahulu';
        } else if (!allChecked) {
            printBtn.title = 'Lengkapi checksheet terlebih dahulu';
        } else if (!noSjFilled) {
            printBtn.title = 'Input No Surat Jalan terlebih dahulu';
        } else {
            printBtn.title = 'Klik untuk submit dan cetak dokumen';
        }
    }

    qualityChecks.forEach(check => {
        check.addEventListener('change', updatePrintButton);
    });

    noSjInput.addEventListener('input', updatePrintButton);

    // Submit and Print Function (Combined)
    window.submitAndPrint = async function() {
        const noSuratJalan = document.getElementById('noSuratJalan').value.trim();
        
        if (!noSuratJalan) {
            alert('No Surat Jalan harus diisi!');
            return;
        }

        // Check if all quality checks are completed
        const allChecked = Array.from(qualityChecks).every(check => check.checked);
        if (!allChecked) {
            alert('Lengkapi semua checksheet terlebih dahulu!');
            return;
        }

        const customerType = '{{ $spk->customer->customer_type ?? "non-inoac" }}';
        
        // Use the displayed Global Balance (already formatted in UI)
        const globalBalance = parseInt(document.getElementById('grandTotalBalance').textContent.replace(/,/g, '')) || 0;
        const hasBalance = globalBalance > 0;

        let splitReason = null;
        let nextDeadline = null;
        let splitNomorPlat = null;

        // Handle Non-Inoac split modal if needed
        if (customerType === 'non-inoac' && hasBalance) {
            const balance = globalBalance;
            const result = await showSplitModal(balance);

            if (!result) return; // User cancelled

            splitReason = result.reason;
            nextDeadline = result.deadline;
            splitNomorPlat = result.nomorPlat;
        }

        // Disable button to prevent double submission
        printBtn.disabled = true;
        printBtn.textContent = '⏳ Processing...';

        try {
            const response = await fetch('{{ route("finishgood.out.close-cycle", $spk->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    no_surat_jalan: noSuratJalan,
                    split_reason: splitReason,
                    next_deadline_time: nextDeadline,
                    split_nomor_plat: splitNomorPlat
                })
            });

            const data = await response.json();

            if (data.success) {
                // Success! Now open print page
                const printUrl = '{{ route("finishgood.out.print", $spk->id) }}';
                const printWindow = window.open(printUrl, '_blank');
                
                // Wait a bit for print window to open, then redirect
                setTimeout(() => {
                    window.location.href = '{{ route("finishgood.out.index") }}';
                }, 1000);
            } else {
                alert('Error: ' + (data.message || 'Gagal menyimpan data'));
                printBtn.disabled = false;
                printBtn.textContent = '📄 Submit & Cetak Dokumen';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data');
            printBtn.disabled = false;
            printBtn.textContent = '📄 Submit & Cetak Dokumen';
        }
    };

    // Slider Logic for Mobile
    let currentSlide = 0;
    const cards = document.querySelectorAll('.part-card');
    const totalSlides = cards.length;
    const sliderContainer = document.getElementById('partSlider');
    const mobileNavControls = document.getElementById('mobileNavControls');
    const slideIndicator = document.getElementById('slideIndicator');

    function updateSlider() {
        if (window.innerWidth >= 768) {
            // Reset split desktop style
            sliderContainer.style.transform = 'none';
            mobileNavControls.style.display = 'none';
            return;
        }

        mobileNavControls.style.display = totalSlides > 1 ? 'flex' : 'none';
        
        // Slide Logic
        const offset = currentSlide * -100;
        // Apply transform to navigate (only on mobile flex container)
        // Since we used flex row on mobile, translateX works perfectly on the container if we wrap items properly or move the container.
        // Wait, my HTML structure: #partSlider is flex-row. Moving it moves all items.
        // Each item is w-full flex-shrink-0.
        sliderContainer.style.transform = `translateX(${offset}%)`;
        
        // Update Indicator
        slideIndicator.textContent = `${currentSlide + 1} / ${totalSlides}`;
        
        // Highlight active card slightly?
        cards.forEach((card, index) => {
            if(index === currentSlide) {
                card.classList.add('ring-2', 'ring-indigo-100');
            } else {
                card.classList.remove('ring-2', 'ring-indigo-100');
            }
        });
    }

    window.prevSlide = function() {
        if (currentSlide > 0) {
            currentSlide--;
            updateSlider();
        }
    }

    window.nextSlide = function() {
        if (currentSlide < totalSlides - 1) {
            currentSlide++;
            updateSlider();
        }
    }

    // Initialize & Resize Listener
    window.addEventListener('resize', updateSlider);
    updateSlider(); // Initial Call

    // Auto-slide to scanned part
    // Modify updateStats to slide to the scanned part
    const originalUpdateStats = updateStats;
    updateStats = async function() {
        // Call original logic first to update values
        await originalUpdateStats(); // This updates DOM values
        
        // Find the most recently updated or relevant part to slide to?
        // Actually updateStats fetches ALL parts.
        // We need to know WHICH part was just scanned.
        // The handleScan function has 'result.data' which contains the part info.
        // Let's modify handleScan to pass the part_id to a slider function.
    };
    
    // We can hook into handleScan directly instead of overriding updateStats
    // Let's modify handleScan below (actually I need to modify the block above, but easier to just add a helper here)
    
    function slideToPart(partId) {
        if (window.innerWidth >= 768) return;

        // Find index of the card with this partId
        const targetCard = document.getElementById(`part-row-${partId}`);
        if (targetCard) {
            const index = parseInt(targetCard.getAttribute('data-index'));
            if (!isNaN(index)) {
                currentSlide = index;
                updateSlider();
            }
        }
    }
</script>
@endpush
