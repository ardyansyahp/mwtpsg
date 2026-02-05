@extends('layout.app')

@push('styles')
<style>
    .scanner-active {
        border-color: #22c55e !important;
        box-shadow: 0 0 0 4px rgba(34, 197, 94, 0.2);
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <a href="{{ route('shipping.delivery.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center gap-1 mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Delivery List
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Loading Delivery Truck</h1>
                <p class="text-sm text-gray-500">Scan Truck untuk Surat Jalan <span class="font-bold text-indigo-600">{{ $spk->no_surat_jalan }}</span></p>
            </div>
            
            <div class="bg-indigo-50 px-4 py-3 rounded-xl border border-indigo-100">
                <p class="text-[10px] text-indigo-600 font-bold uppercase tracking-wider mb-1">Target Destination</p>
                <p class="font-bold text-indigo-900">{{ $spk->plantgate->nama_plantgate ?? $spk->customer->nama_perusahaan ?? '-' }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Main Scan Section -->
        <div class="md:col-span-2 space-y-6">
            <!-- Scanner Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 text-center relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-indigo-500 to-blue-600"></div>
                
                <div class="mb-6">
                    <div class="w-20 h-20 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-indigo-100 group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-10 h-10 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Scan Barcode Truck</h2>
                    <p class="text-sm text-gray-500 max-w-xs mx-auto mt-2">Pastikan kursor aktif di input bawah, lalu scan atau ketik Nopol kendaraan.</p>
                </div>
                
                <div class="relative max-w-md mx-auto">
                    <input type="text" 
                           id="truckInput" 
                           class="w-full px-4 py-4 bg-gray-50 border-2 border-gray-200 text-gray-900 placeholder-gray-400 focus:outline-none focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 rounded-xl text-center font-mono text-lg shadow-sm transition-all duration-300"
                           placeholder="B 1234 ABC" 
                           autofocus 
                           autocomplete="off">
                    
                    <div class="absolute inset-y-0 right-0 flex items-center pr-4 pointer-events-none">
                        <span id="typingIndicator" class="h-3 w-3 rounded-full bg-green-400 animate-pulse"></span>
                    </div>
                </div>
                
                <div id="scanStatus" class="mt-4 text-sm font-medium transition-all opacity-0 h-5">...</div>
            </div>

            <!-- SPK Details Info -->
            <div class="bg-indigo-900 rounded-2xl p-6 text-white shadow-lg overflow-hidden relative">
                <div class="absolute top-0 right-0 -mr-8 -mt-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <h3 class="text-lg font-bold mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Informasi Surat Jalan
                </h3>
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <p class="text-indigo-300 text-[10px] uppercase font-bold mb-1">Nomor SJ</p>
                        <p class="text-lg font-bold">{{ $spk->no_surat_jalan }}</p>
                    </div>
                    <div>
                        <p class="text-indigo-300 text-[10px] uppercase font-bold mb-1">Customer</p>
                        <p class="font-bold">{{ $spk->customer->nama_perusahaan ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-indigo-300 text-[10px] uppercase font-bold mb-1">Tanggal Scan Out</p>
                        <p class="font-bold">{{ $spk->updated_at->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-indigo-300 text-[10px] uppercase font-bold mb-1">Plant Gate</p>
                        <p class="font-bold">{{ $spk->plantgate->nama_plantgate ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Section -->
        <div class="space-y-6">
             <!-- Instructions -->
             <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h4 class="font-bold text-gray-900 mb-4 flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Cara Kerja
                </h4>
                <ul class="space-y-3 text-xs text-gray-600">
                    <li class="flex gap-2">
                        <span class="w-5 h-5 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0 font-bold">1</span>
                        <span>Daftar item untuk SJ <strong>{{ $spk->no_surat_jalan }}</strong> sedang disiapkan di dermaga loading.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-5 h-5 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0 font-bold">2</span>
                        <span>Scan Barcode yang ada di **Kaca Depan Truck** atau **Kuningan Nopol**.</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="w-5 h-5 bg-blue-50 text-blue-600 rounded-full flex items-center justify-center shrink-0 font-bold">3</span>
                        <span>Sistem akan membuat trip pengiriman (Delivery) dan mencatat status **OPEN**.</span>
                    </li>
                </ul>
             </div>

             <!-- Tip Card -->
             <div class="bg-emerald-50 rounded-2xl p-6 border border-emerald-100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-1.5 bg-emerald-500 rounded text-white font-bold text-[10px]">TIPS</div>
                    <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Fast Scan</span>
                </div>
                <p class="text-xs text-emerald-700 leading-relaxed">Gunakan scanner wireless agar bisa bebas bergerak di area dermaga tanpa terikat kabel.</p>
             </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const truckInput = document.getElementById('truckInput');
    const scanStatus = document.getElementById('scanStatus');
    const SPK_ID = {{ $spk->id }};

    // Auto Focus
    document.addEventListener('click', (e) => {
        if (e.target.tagName !== 'INPUT' && e.target.tagName !== 'BUTTON') truckInput.focus();
    });

    // Audio Context
    const audioContext = new (window.AudioContext || window.webkitAudioContext)();
    function playBeep(type) {
        if (audioContext.state === 'suspended') audioContext.resume();
        const osc = audioContext.createOscillator();
        const gain = audioContext.createGain();
        osc.connect(gain);
        gain.connect(audioContext.destination);
        if (type === 'success') {
            osc.frequency.setValueAtTime(1200, audioContext.currentTime);
            osc.frequency.exponentialRampToValueAtTime(600, audioContext.currentTime + 0.1);
            gain.gain.setValueAtTime(0.3, audioContext.currentTime);
            gain.gain.linearRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            osc.start();
            osc.stop(audioContext.currentTime + 0.1);
        } else {
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(150, audioContext.currentTime);
            gain.gain.setValueAtTime(0.5, audioContext.currentTime);
            gain.gain.linearRampToValueAtTime(0.01, audioContext.currentTime + 0.4);
            osc.start();
            osc.stop(audioContext.currentTime + 0.4);
        }
    }

    let typingTimer;
    truckInput.addEventListener('input', () => {
        clearTimeout(typingTimer);
        const val = truckInput.value.trim();
        if (val.length >= 4) { // Typical nopol length
            typingTimer = setTimeout(() => processScan(val), 500);
        }
    });

    truckInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            clearTimeout(typingTimer);
            processScan(truckInput.value.trim());
        }
    });

    async function processScan(barcode) {
        if (!barcode) return;
        
        truckInput.disabled = true;
        showStatus('Processing Truck Identification...', 'text-blue-500');
        
        try {
            const response = await fetch('{{ route("shipping.delivery.store-from-scan") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    spk_id: SPK_ID,
                    kendaraan_barcode: barcode
                })
            });

            const result = await response.json();

            if (result.success) {
                playBeep('success');
                showStatus(result.message, 'text-green-600');
                
                setTimeout(() => {
                    window.location.href = result.redirect;
                }, 1000);
                
            } else {
                playBeep('error');
                showStatus(result.message, 'text-red-600');
                truckInput.disabled = false;
                truckInput.value = '';
                truckInput.focus();
            }
        } catch (e) {
            console.error(e);
            playBeep('error');
            showStatus('System Error connecting to server', 'text-red-600');
            truckInput.disabled = false;
        }
    }

    function showStatus(msg, colorClass) {
        scanStatus.textContent = msg;
        scanStatus.className = `mt-4 text-sm font-bold transition-all opacity-100 h-5 ${colorClass}`;
    }
</script>
@endpush
