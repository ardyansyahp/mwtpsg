@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route('produksi.inject.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

            <h2 class="text-3xl font-bold text-gray-800">Scan Inject In</h2>
            <p class="text-gray-600 mt-1">Scan QR code mesin, operator, dan supply inject</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createInjectInForm" class="space-y-6">
            @csrf

            {{-- Scan QR Code Mesin --}}
            <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        1. Scan QR Mesin *
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="mesinQRInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        placeholder="Scan QR code mesin atau ketik no mesin (contoh: MC-16)"
                        autofocus
                    >
                    <button 
                        type="button" 
                        id="btnScanMesin" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </button>
                </div>
                <div id="mesinInfo" class="mt-2 text-sm text-gray-700 hidden">
                    <span class="font-medium">Mesin: </span><span id="mesinDisplay">-</span>
                    <input type="hidden" id="mesinIdInput" name="mesin_id">
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label mesin</p>
            </div>

            {{-- Scan QR Code Operator --}}
            <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        2. Scan QR Operator *
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="operatorQRInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500" 
                        placeholder="Scan QR code operator atau ketik nama operator"
                    >
                    <button 
                        type="button" 
                        id="btnScanOperator" 
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </button>
                </div>
                <div id="operatorInfo" class="mt-2 text-sm text-gray-700 hidden">
                    <span class="font-medium">Operator: </span><span id="operatorDisplay">-</span>
                    <input type="hidden" id="operatorInput" name="operator">
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari badge operator</p>
            </div>

            {{-- Scan QR Code Supply Inject --}}
            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        3. Scan QR Supply Inject *
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="lotNumberInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Scan QR code atau ketik lot number (contoh: SUP-20241218-000001)"
                    >
                    <button 
                        type="button" 
                        id="btnScanSupply" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </button>
                    <button 
                        type="button" 
                        id="btnCariSupply" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Cari
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label supply atau ketik lot number secara manual</p>
            </div>

            {{-- Info Supply (auto-fill setelah scan) --}}
            <div id="supplyInfo" class="hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Supply</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                        <div class="text-sm font-mono text-gray-800" id="displayLotNumber">-</div>
                        <input type="hidden" id="supplyDetailId" name="supply_detail_id">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Supply</label>
                        <div class="text-sm text-gray-800" id="displayTanggalSupply">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Planning Run</label>
                        <div class="text-sm text-gray-800" id="displayPlanningRun">-</div>
                        <input type="hidden" id="planningRunId" name="planning_run_id">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                        <div class="text-sm text-gray-800" id="displayPart">-</div>
                    </div>
                </div>

                {{-- Info Planning Target Qty --}}
                <div id="planningTargetInfo" class="hidden border-t border-gray-300 pt-4 mt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Target Produksi dari Planning</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <label class="block text-xs font-medium text-blue-600 mb-1">Target Total</label>
                            <div class="text-lg font-bold text-blue-800" id="displayTargetTotal">-</div>
                            <div class="text-xs text-blue-600 mt-1" id="displayTargetActual">Actual: -</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <label class="block text-xs font-medium text-green-600 mb-1">Waktu Start</label>
                            <div class="text-sm font-semibold text-green-800" id="displayStartAt">-</div>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <label class="block text-xs font-medium text-red-600 mb-1">Waktu End</label>
                            <div class="text-sm font-semibold text-red-800" id="displayEndAt">-</div>
                        </div>
                    </div>

                    {{-- Target Per Jam (ringkas, hanya jam aktif/jam berikutnya) --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Target per Jam (untuk monitoring)</label>
                        <div class="max-h-40 overflow-auto border border-gray-200 rounded-lg bg-white">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-100 sticky top-0">
                                    <tr>
                                        <th class="p-2 text-left">Jam</th>
                                        <th class="p-2 text-right">Target Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="hourlyTargetsTable">
                                    <tr>
                                        <td colspan="2" class="p-2 text-center text-gray-500">Tidak ada data</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Input Data --}}
            <div id="inputSection" class="hidden space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan (opsional)</label>
                    <textarea 
                        name="catatan" 
                        rows="3" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Catatan tambahan jika ada"
                    ></textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    id="btnSubmit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled
                >
                    Simpan
                </button>
                <a href="{{ route('produksi.inject.index') }}" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('createInjectInForm');
    const mesinQRInput = document.getElementById('mesinQRInput');
    const operatorQRInput = document.getElementById('operatorQRInput');
    const lotNumberInput = document.getElementById('lotNumberInput');
    const btnScanMesin = document.getElementById('btnScanMesin');
    const btnScanOperator = document.getElementById('btnScanOperator');
    const btnScanSupply = document.getElementById('btnScanSupply');
    const btnCariSupply = document.getElementById('btnCariSupply');
    const supplyInfo = document.getElementById('supplyInfo');
    const inputSection = document.getElementById('inputSection');
    const btnSubmit = document.getElementById('btnSubmit');
    const mesinInfo = document.getElementById('mesinInfo');
    const operatorInfo = document.getElementById('operatorInfo');
    
    // State untuk tracking scan yang sudah dilakukan
    let mesinScanned = false;
    let operatorScanned = false;
    let supplyScanned = false;

    // Scan QR Mesin
    btnScanMesin?.addEventListener('click', () => {
        const qrCode = prompt('Scan QR Code Mesin atau masukkan No Mesin:');
        if (qrCode && qrCode.trim()) {
            mesinQRInput.value = qrCode.trim();
            cariMesinByQR(qrCode.trim());
        }
    });

    mesinQRInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            cariMesinByQR(mesinQRInput.value.trim());
        }
    });

    // Scan QR Operator
    btnScanOperator?.addEventListener('click', () => {
        const qrCode = prompt('Scan QR Code Operator atau masukkan QR Code Operator:');
        if (qrCode && qrCode.trim()) {
            operatorQRInput.value = qrCode.trim();
            cariOperatorByQR(qrCode.trim());
        }
    });

    operatorQRInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            cariOperatorByQR(operatorQRInput.value.trim());
        }
    });

    // Scan QR Supply
    btnScanSupply?.addEventListener('click', () => {
        const qrCode = prompt('Scan QR Code Supply atau masukkan Lot Number:');
        if (qrCode && qrCode.trim()) {
            lotNumberInput.value = qrCode.trim();
            cariSupplyByLotNumber(qrCode.trim());
        }
    });

    // Cari supply berdasarkan lot number
    btnCariSupply?.addEventListener('click', () => {
        const lotNumber = lotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Masukkan lot number terlebih dahulu');
            return;
        }
        cariSupplyByLotNumber(lotNumber);
    });

    // Enter key untuk search supply
    lotNumberInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnCariSupply?.click();
        }
    });

    // Fungsi untuk mencari mesin berdasarkan QR code
    async function cariMesinByQR(qrCode) {
        if (!qrCode || !qrCode.trim()) {
            alert('Masukkan QR code mesin terlebih dahulu');
            mesinInfo.classList.add('hidden');
            mesinScanned = false;
            checkAllScanned();
            return;
        }
        
        try {
            console.log('Mencari mesin dengan QR code:', qrCode);
            const response = await fetch(`/produksi/inject/api/mesin/${encodeURIComponent(qrCode.trim())}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Mesin tidak ditemukan');
                mesinInfo.classList.add('hidden');
                mesinScanned = false;
                checkAllScanned();
                return;
            }

            const mesin = data.data;
            console.log('Mesin ditemukan:', mesin);
            document.getElementById('mesinIdInput').value = mesin.id;
            document.getElementById('mesinDisplay').textContent = `${mesin.no_mesin}${mesin.tonase ? ' - ' + mesin.tonase + 'T' : ''}`;
            mesinInfo.classList.remove('hidden');
            mesinScanned = true;
            checkAllScanned();
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari mesin: ' + error.message);
        }
    }

    // Fungsi untuk mencari operator berdasarkan QR code
    async function cariOperatorByQR(qrCode) {
        if (!qrCode || !qrCode.trim()) {
            alert('Masukkan QR code operator terlebih dahulu');
            operatorInfo.classList.add('hidden');
            operatorScanned = false;
            checkAllScanned();
            return;
        }
        
        try {
            console.log('Mencari operator dengan QR code:', qrCode);
            const response = await fetch(`/produksi/inject/api/operator/${encodeURIComponent(qrCode.trim())}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Operator tidak ditemukan');
                operatorInfo.classList.add('hidden');
                operatorScanned = false;
                checkAllScanned();
                return;
            }

            const operator = data.data;
            console.log('Operator ditemukan:', operator);
            setOperator(operator.nama, operator);
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari operator: ' + error.message);
        }
    }

    // Fungsi untuk set operator setelah data ditemukan
    function setOperator(operatorName, operatorData = null) {
        console.log('Setting operator:', operatorName, operatorData);
        if (!operatorName || !operatorName.trim()) {
            alert('Nama operator tidak valid');
            operatorInfo.classList.add('hidden');
            operatorScanned = false;
            checkAllScanned();
            return;
        }
        
        const operatorInput = document.getElementById('operatorInput');
        const operatorDisplay = document.getElementById('operatorDisplay');
        
        if (operatorInput) {
            // Simpan nama operator ke field hidden
            operatorInput.value = operatorName.trim();
        }
        if (operatorDisplay) {
            // Tampilkan nama operator dalam bahasa Indonesia
            operatorDisplay.textContent = operatorName.trim();
        }
        if (operatorInfo) {
            operatorInfo.classList.remove('hidden');
        }
        operatorScanned = true;
        checkAllScanned();
        console.log('Operator berhasil di-set:', operatorName);
    }

    async function cariSupplyByLotNumber(lotNumber) {
        try {
            const response = await fetch(`/produksi/inject/api/supply-detail/${encodeURIComponent(lotNumber)}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Supply tidak ditemukan atau tidak untuk inject');
                supplyInfo.classList.add('hidden');
                inputSection.classList.add('hidden');
                supplyScanned = false;
                checkAllScanned();
                return;
            }

            const supplyDetail = data.data;
            
            // Tampilkan info supply
            document.getElementById('supplyDetailId').value = supplyDetail.id;
            document.getElementById('displayLotNumber').textContent = supplyDetail.lot_number;
            document.getElementById('displayTanggalSupply').textContent = supplyDetail.supply?.tanggal_supply || '-';
            
            if (supplyDetail.supply?.planning_run_id) {
                document.getElementById('planningRunId').value = supplyDetail.supply.planning_run_id;
                document.getElementById('displayPlanningRun').textContent = `#${supplyDetail.supply.planning_run_id}`;
                
                document.getElementById('displayPart').textContent = supplyDetail.supply.planning_run?.mold?.part?.nomor_part || '-';
                
                // Tampilkan info planning target qty
                const planningRun = supplyDetail.supply.planning_run;
                if (planningRun) {
                    const planningTargetInfo = document.getElementById('planningTargetInfo');
                    planningTargetInfo.classList.remove('hidden');
                    
                    // Target Total
                    const targetTotal = planningRun.qty_target_total || 0;
                    const actualTotal = planningRun.qty_actual_total || 0;
                    document.getElementById('displayTargetTotal').textContent = targetTotal.toLocaleString('id-ID') + ' pcs';
                    document.getElementById('displayTargetActual').textContent = `Actual: ${actualTotal.toLocaleString('id-ID')} pcs`;
                    
                    // Waktu Start & End
                    document.getElementById('displayStartAt').textContent = planningRun.start_at ? 
                        new Date(planningRun.start_at).toLocaleString('id-ID') : '-';
                    document.getElementById('displayEndAt').textContent = planningRun.end_at ? 
                        new Date(planningRun.end_at).toLocaleString('id-ID') : '-';
                    
                    // Hourly Targets
                    const hourlyTargetsTable = document.getElementById('hourlyTargetsTable');
                    if (planningRun.hourly_targets && planningRun.hourly_targets.length > 0) {
                        // Sort by hour_start
                        const sortedTargets = [...planningRun.hourly_targets].sort((a, b) => {
                            return new Date(a.hour_start) - new Date(b.hour_start);
                        });
                        
                        // Ambil waktu sekarang untuk highlight jam yang sedang berjalan
                        const now = new Date();
                        const currentHour = now.getHours();
                        
                        hourlyTargetsTable.innerHTML = sortedTargets.map(target => {
                            const hourStart = new Date(target.hour_start);
                            const hourStartHour = hourStart.getHours();
                            const isCurrentHour = hourStartHour === currentHour || (hourStartHour === currentHour - 1);
                            const rowClass = isCurrentHour ? 'bg-yellow-50 font-semibold' : '';
                            
                            return `
                                <tr class="${rowClass}">
                                    <td class="p-2">${target.hour_start_time} - ${target.hour_end_time}</td>
                                    <td class="p-2 text-right font-medium">${target.qty_target.toLocaleString('id-ID')} pcs</td>
                                </tr>
                            `;
                        }).join('');
                    } else {
                        hourlyTargetsTable.innerHTML = '<tr><td colspan="2" class="p-2 text-center text-gray-500">Tidak ada target per jam</td></tr>';
                    }
                }
            } else {
                document.getElementById('planningRunId').value = '';
                document.getElementById('displayPlanningRun').textContent = '- (Tidak ada planning)';
                document.getElementById('displayPart').textContent = '-';
                document.getElementById('planningTargetInfo').classList.add('hidden');
            }

            supplyInfo.classList.remove('hidden');
            inputSection.classList.remove('hidden');
            supplyScanned = true;
            checkAllScanned();

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari supply');
        }
    }

    // Fungsi untuk check apakah semua scan sudah dilakukan
    function checkAllScanned() {
        if (mesinScanned && operatorScanned && supplyScanned) {
            btnSubmit.disabled = false;
        } else {
            btnSubmit.disabled = true;
        }
    }

    // Submit form
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Validasi semua scan sudah dilakukan
        if (!mesinScanned) {
            alert('Silakan scan QR mesin terlebih dahulu');
            mesinQRInput.focus();
            return;
        }
        if (!operatorScanned) {
            alert('Silakan scan QR operator terlebih dahulu');
            operatorQRInput.focus();
            return;
        }
        if (!supplyScanned) {
            alert('Silakan scan QR supply inject terlebih dahulu');
            lotNumberInput.focus();
            return;
        }
        
        const formData = new FormData(form);
        const lotNumber = lotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Scan atau masukkan lot number terlebih dahulu');
            return;
        }
        formData.append('lot_number', lotNumber);
        // Set operator ke manpower field
        formData.append('manpower', document.getElementById('operatorInput').value);

        try {
            const response = await fetch('{{ route("produksi.inject.storein") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Data berhasil disimpan');
                window.location.href = "{{ route('produksi.inject.index') }}";
            } else {
                alert(data.message || 'Gagal menyimpan data');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data');
        }
    });
})();
</script>
@endsection
