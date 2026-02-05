@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route(\'dashboard\') }}"\1>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

            <h2 class="text-3xl font-bold text-gray-800">Scan ASSY In</h2>
            <p class="text-gray-600 mt-1">Scan QR code supply assy, WIP out lot, dan operator</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createAssyInForm" class="space-y-6">
            @csrf

            {{-- 1. Scan QR Code Supply ASSY --}}
            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        1. Scan QR Supply ASSY *
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="supplyLotNumberInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Scan QR code atau ketik lot number supply (contoh: SUP-20241218-000001)"
                        autofocus
                    >
                    <button 
                        type="button" 
                        id="btnScanSupplyQR" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </a>
                    <button 
                        type="button" 
                        id="btnCariSupply" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Cari
                    </a>
                </div>
                <div id="supplyInfo" class="mt-2 text-sm text-gray-700 hidden">
                    <span class="font-medium">Supply: </span><span id="supplyDisplay">-</span>
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label supply assy atau ketik lot number secara manual</p>
            </div>

            {{-- Info Supply (auto-fill setelah scan) --}}
            <div id="supplyInfoDetail" class="hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Supply</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                        <div class="text-sm font-mono text-gray-800" id="displaySupplyLotNumber">-</div>
                        <input type="hidden" id="supplyDetailId" name="supply_detail_id">
                        <input type="hidden" id="partId" name="part_id">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Supply</label>
                        <div class="text-sm text-gray-800" id="displayTanggalSupply">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                        <div class="text-sm text-gray-800" id="displayPart">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Meja</label>
                        <div class="text-sm text-gray-800" id="displayMeja">-</div>
                    </div>
                </div>
            </div>

            {{-- 2. Scan QR Code WIP Out --}}
            <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        2. Scan QR Lot WIP Out * (Bisa Multiple Box)
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="wipOutLotNumberInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        placeholder="Scan QR code atau ketik lot number dari label wipout"
                    >
                    <button 
                        type="button" 
                        id="btnScanWipOutQR" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </a>
                    <button 
                        type="button" 
                        id="btnCariWipOut" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Tambah Box
                    </a>
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label wipout (yang berasal dari inject out). Bisa scan beberapa box untuk meja yang sama.</p>
            </div>

            {{-- List WIP Out yang sudah ditambahkan --}}
            <div id="wipOutList" class="hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Box WIP Out</h3>
                <div id="wipOutItems" class="space-y-2">
                    <!-- Items akan ditambahkan via JavaScript -->
                </div>
                <p class="text-xs text-gray-500 mt-2">Total: <span id="wipOutCount">0</span> box</p>
            </div>

            {{-- 3. Scan QR Code Operator --}}
            <div class="border border-yellow-200 rounded-lg p-4 bg-yellow-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        3. Scan QR Operator *
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
                    </a>
                </div>
                <div id="operatorInfo" class="mt-2 text-sm text-gray-700 hidden">
                    <span class="font-medium">Operator: </span><span id="operatorDisplay">-</span>
                    <input type="hidden" id="operatorInput" name="operator">
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari badge operator</p>
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
                </a>
                <a href="{{ route(\'dashboard\') }}" 
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
    const form = document.getElementById('createAssyInForm');
    const supplyLotNumberInput = document.getElementById('supplyLotNumberInput');
    const btnScanSupplyQR = document.getElementById('btnScanSupplyQR');
    const btnCariSupply = document.getElementById('btnCariSupply');
    const supplyInfo = document.getElementById('supplyInfo');
    const supplyInfoDetail = document.getElementById('supplyInfoDetail');
    
    const wipOutLotNumberInput = document.getElementById('wipOutLotNumberInput');
    const btnScanWipOutQR = document.getElementById('btnScanWipOutQR');
    const btnCariWipOut = document.getElementById('btnCariWipOut');
    const wipOutList = document.getElementById('wipOutList');
    const wipOutItems = document.getElementById('wipOutItems');
    const wipOutCount = document.getElementById('wipOutCount');
    
    const operatorQRInput = document.getElementById('operatorQRInput');
    const btnScanOperator = document.getElementById('btnScanOperator');
    const operatorInfo = document.getElementById('operatorInfo');
    
    const inputSection = document.getElementById('inputSection');
    const btnSubmit = document.getElementById('btnSubmit');

    let supplyScanned = false;
    let operatorScanned = false;
    let wipOuts = []; // Array untuk menyimpan multiple WIP Out

    // Scan QR Code Supply (simulasi - bisa diintegrasikan dengan scanner)
    btnScanSupplyQR?.addEventListener('click', () => {
        const qrCode = prompt('Scan QR Code atau masukkan Lot Number Supply:');
        if (qrCode && qrCode.trim()) {
            supplyLotNumberInput.value = qrCode.trim();
            cariSupplyByLotNumber(qrCode.trim());
        }
    });

    // Cari supply berdasarkan lot number
    btnCariSupply?.addEventListener('click', () => {
        const lotNumber = supplyLotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Masukkan lot number supply terlebih dahulu');
            return;
        }
        cariSupplyByLotNumber(lotNumber);
    });

    // Enter key untuk search supply
    supplyLotNumberInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnCariSupply?.click();
        }
    });

    async function cariSupplyByLotNumber(lotNumber) {
        try {
            const response = await fetch(`/produksi/assy/api/supply-detail/${encodeURIComponent(lotNumber)}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Supply tidak ditemukan atau bukan untuk assy');
                if (supplyInfo) supplyInfo.classList.add('hidden');
                if (supplyInfoDetail) supplyInfoDetail.classList.add('hidden');
                supplyScanned = false;
                updateSubmitButton();
                return;
            }

            const supplyDetail = data.data;
            
            // Tampilkan info supply di display kecil
            if (document.getElementById('supplyDisplay')) {
                document.getElementById('supplyDisplay').textContent = supplyDetail.lot_number;
            }
            if (supplyInfo) supplyInfo.classList.remove('hidden');
            
            // Tampilkan info supply detail
            document.getElementById('supplyDetailId').value = supplyDetail.id;
            document.getElementById('displaySupplyLotNumber').textContent = supplyDetail.lot_number;
            document.getElementById('displayTanggalSupply').textContent = supplyDetail.supply?.tanggal_supply || '-';
            
            if (supplyDetail.supply?.part_id) {
                document.getElementById('partId').value = supplyDetail.supply.part_id;
                document.getElementById('displayPart').textContent = supplyDetail.supply.part ? 
                    `${supplyDetail.supply.part.nomor_part} - ${supplyDetail.supply.part.nama_part}` : '-';
            } else {
                document.getElementById('partId').value = '';
                document.getElementById('displayPart').textContent = '-';
            }
            
            document.getElementById('displayMeja').textContent = supplyDetail.supply?.meja || '-';
            if (supplyInfoDetail) supplyInfoDetail.classList.remove('hidden');

            supplyScanned = true;
            updateSubmitButton();

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari supply');
        }
    }

    // Scan QR Code WIP Out (simulasi - bisa diintegrasikan dengan scanner)
    btnScanWipOutQR?.addEventListener('click', () => {
        const qrCode = prompt('Scan QR Code atau masukkan Lot Number WIP Out:');
        if (qrCode && qrCode.trim()) {
            wipOutLotNumberInput.value = qrCode.trim();
            cariDanTambahWipOut(qrCode.trim());
        }
    });

    // Cari wip out berdasarkan lot number dan tambahkan ke list
    btnCariWipOut?.addEventListener('click', () => {
        const lotNumber = wipOutLotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Masukkan lot number wipout terlebih dahulu');
            return;
        }
        cariDanTambahWipOut(lotNumber);
    });

    // Enter key untuk search wipout
    wipOutLotNumberInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnCariWipOut?.click();
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

    // Fungsi untuk mencari operator berdasarkan QR code
    async function cariOperatorByQR(qrCode) {
        if (!qrCode || !qrCode.trim()) {
            alert('Masukkan QR code operator terlebih dahulu');
            if (operatorInfo) operatorInfo.classList.add('hidden');
            operatorScanned = false;
            updateSubmitButton();
            return;
        }
        
        try {
            console.log('Mencari operator dengan QR code:', qrCode);
            const response = await fetch(`/produksi/assy/api/operator/${encodeURIComponent(qrCode.trim())}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Operator tidak ditemukan');
                if (operatorInfo) operatorInfo.classList.add('hidden');
                operatorScanned = false;
                updateSubmitButton();
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
            if (operatorInfo) operatorInfo.classList.add('hidden');
            operatorScanned = false;
            updateSubmitButton();
            return;
        }
        
        const operatorInput = document.getElementById('operatorInput');
        const operatorDisplay = document.getElementById('operatorDisplay');
        
        if (operatorInput) {
            operatorInput.value = operatorName.trim();
        }
        if (operatorDisplay) {
            operatorDisplay.textContent = operatorName.trim();
        }
        if (operatorInfo) {
            operatorInfo.classList.remove('hidden');
        }
        operatorScanned = true;
        updateSubmitButton();
        console.log('Operator berhasil di-set:', operatorName);
    }

    async function cariDanTambahWipOut(lotNumber) {
        try {
            // Normalisasi lot number: trim dan hapus spasi berlebih
            const lotNumberNormalized = lotNumber.trim().replace(/\s+/g, ' ');
            
            console.log('Mencari WIP Out dengan lot number:', {
                original: lotNumber,
                normalized: lotNumberNormalized
            });

            const response = await fetch(`/produksi/assy/api/wip-out/${encodeURIComponent(lotNumberNormalized)}`);
            const data = await response.json();

            console.log('Response dari API:', data);

            if (!data.success) {
                alert(data.message || 'Label wipout tidak ditemukan');
                console.error('WIP Out tidak ditemukan:', data.message);
                return;
            }

            const wipOutData = data.data;

            // Cek apakah sudah pernah di-scan in
            if (wipOutData.already_scanned_in) {
                if (!confirm('Label wipout ini sudah pernah di-scan in sebelumnya. Tetap tambahkan?')) {
                    return;
                }
            }

            // Cek duplikat: apakah lot number dengan id yang sama sudah ada di list
            const isDuplicate = wipOuts.some(wo => wo.id === wipOutData.wip_out.id);
            if (isDuplicate) {
                alert('Box ini sudah ditambahkan ke list');
                return;
            }

            // Tambahkan ke array (boleh duplikat lot number, karena qty/box number yang bertambah)
            wipOuts.push({
                id: wipOutData.wip_out.id,
                lot_number: wipOutData.wip_out.lot_number,
                box_number: wipOutData.wip_out.box_number,
                waktu_scan_out: wipOutData.wip_out.waktu_scan_out,
                part: wipOutData.part,
                already_scanned_in: wipOutData.already_scanned_in
            });

            // Update tampilan
            updateWipOutList();
            
            // Clear input
            wipOutLotNumberInput.value = '';
            wipOutLotNumberInput.focus();

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari label wipout: ' + error.message);
        }
    }

    function updateWipOutList() {
        if (wipOuts.length === 0) {
            wipOutList.classList.add('hidden');
            updateSubmitButton();
            return;
        }

        wipOutList.classList.remove('hidden');
        wipOutCount.textContent = wipOuts.length;

        // Clear dan rebuild list
        wipOutItems.innerHTML = '';

        wipOuts.forEach((wipOut, index) => {
            const item = document.createElement('div');
            item.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded-lg';
            
            const warningClass = wipOut.already_scanned_in ? 'border-red-300 bg-red-50' : '';
            item.className += ' ' + warningClass;

            item.innerHTML = `
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-semibold text-gray-800">Box #${wipOut.box_number || '-'}</span>
                        ${wipOut.already_scanned_in ? '<span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">Sudah pernah di-scan</span>' : ''}
                    </div>
                    <div class="text-xs font-mono text-gray-600 mt-1">${wipOut.lot_number}</div>
                    ${wipOut.part ? `<div class="text-xs text-gray-500 mt-1">${wipOut.part.nomor_part} - ${wipOut.part.nama_part}</div>` : ''}
                </div>
                <button 
                    type="button" 
                    class="text-red-600 hover:text-red-800 p-2 rounded-lg hover:bg-red-50 transition-colors"
                    onclick="hapusWipOut(${index})"
                    title="Hapus"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </a>
            `;

            wipOutItems.appendChild(item);
        });

        updateSubmitButton();
    }

    // Function untuk hapus WIP Out dari list (harus global untuk bisa dipanggil dari onclick)
    window.hapusWipOut = function(index) {
        if (confirm('Hapus box ini dari list?')) {
            wipOuts.splice(index, 1);
            updateWipOutList();
        }
    };

    function updateSubmitButton() {
        if (supplyScanned && wipOuts.length > 0 && operatorScanned) {
            inputSection.classList.remove('hidden');
            btnSubmit.disabled = false;
        } else {
            inputSection.classList.add('hidden');
            btnSubmit.disabled = true;
        }
    }

    // Submit form
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!supplyScanned) {
            alert('Silakan scan QR supply assy terlebih dahulu');
            supplyLotNumberInput.focus();
            return;
        }

        if (wipOuts.length === 0) {
            alert('Harus tambahkan minimal 1 box WIP Out');
            wipOutLotNumberInput.focus();
            return;
        }

        if (!operatorScanned) {
            alert('Silakan scan QR operator terlebih dahulu');
            operatorQRInput.focus();
            return;
        }

        const catatan = form.querySelector('[name="catatan"]').value.trim();
        const supplyDetailId = document.getElementById('supplyDetailId').value;
        const partId = document.getElementById('partId').value;

        // Disable submit button
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Menyimpan...';

        try {
            // Submit setiap WIP Out sebagai record ASSY In terpisah
            let successCount = 0;
            let errorCount = 0;
            const errors = [];

            for (const wipOut of wipOuts) {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('supply_detail_id', supplyDetailId);
                formData.append('wip_out_id', wipOut.id);
                formData.append('part_id', partId);
                formData.append('manpower', document.getElementById('operatorInput').value);
                formData.append('catatan', catatan);

                try {
                    const response = await fetch('{{ route("produksi.assy.storein") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const data = await response.json();

                    if (data.success) {
                        successCount++;
                    } else {
                        errorCount++;
                        errors.push(`Box ${wipOut.lot_number}: ${data.message || 'Gagal menyimpan'}`);
                    }
                } catch (error) {
                    errorCount++;
                    errors.push(`Box ${wipOut.lot_number}: ${error.message}`);
                }
            }

            // Tampilkan hasil
            if (errorCount === 0) {
                alert(`Berhasil menyimpan ${successCount} box!`);
                window.location.reload();
            } else {
                let message = `Berhasil: ${successCount} box, Gagal: ${errorCount} box`;
                if (errors.length > 0) {
                    message += '\n\nDetail error:\n' + errors.join('\n');
                }
                alert(message);
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Simpan';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data');
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Simpan';
        }
    });
})();
</script>
@endsection
