@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route('produksi.assy.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            <span class="font-medium">Kembali</span>
        </a>
            <h2 class="text-3xl font-bold text-gray-800">Scan Label Box - ASSY Out</h2>
            <p class="text-gray-600 mt-1">Scan QR code label box dari ASSY In dan sistem akan otomatis mencatat waktu scan out</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createAssyOutForm" class="space-y-6">
            @csrf

            {{-- Scan QR Code --}}
            <div class="border border-green-200 rounded-lg p-4 bg-green-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Scan QR Code / Masukkan Lot Number
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="lotNumberInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        placeholder="Scan QR code atau ketik lot number dari label box yang sudah di-scan in"
                        autofocus
                    >
                    <button 
                        type="button" 
                        id="btnScanQR" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </button>
                    <button 
                        type="button" 
                        id="btnCariLabel" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Cari
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label box yang sudah di-scan in di ASSY</p>
            </div>

            {{-- Info Label (auto-fill setelah scan) --}}
            <div id="labelInfo" class="hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Label</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                        <div class="text-sm font-mono text-gray-800" id="displayLotNumber">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan In</label>
                        <div class="text-sm text-gray-800" id="displayWaktuScanIn">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Manpower</label>
                        <div class="text-sm text-gray-800" id="displayManpower">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                        <div class="text-sm text-gray-800" id="displayPart">-</div>
                    </div>
                </div>

                {{-- Warning jika sudah pernah di-scan out --}}
                <div id="warningAlreadyScanned" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span class="text-sm font-medium text-red-800">Label ini sudah pernah di-scan out sebelumnya!</span>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" 
                        placeholder="Catatan tambahan jika ada"
                    ></textarea>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    id="btnSubmit" 
                    class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors disabled:bg-gray-400 disabled:cursor-not-allowed"
                    disabled
                >
                    Simpan Scan Out
                </button>
                <a href="{{ route('produksi.assy.index') }}" 
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
    const form = document.getElementById('createAssyOutForm');
    const lotNumberInput = document.getElementById('lotNumberInput');
    const btnScanQR = document.getElementById('btnScanQR');
    const btnCariLabel = document.getElementById('btnCariLabel');
    const labelInfo = document.getElementById('labelInfo');
    const inputSection = document.getElementById('inputSection');
    const btnSubmit = document.getElementById('btnSubmit');

    // Scan QR Code (simulasi - bisa diintegrasikan dengan scanner)
    btnScanQR?.addEventListener('click', () => {
        const qrCode = prompt('Scan QR Code atau masukkan Lot Number:');
        if (qrCode && qrCode.trim()) {
            lotNumberInput.value = qrCode.trim();
            cariLabelByLotNumber(qrCode.trim());
        }
    });

    // Cari label berdasarkan lot number
    btnCariLabel?.addEventListener('click', () => {
        const lotNumber = lotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Masukkan lot number terlebih dahulu');
            return;
        }
        cariLabelByLotNumber(lotNumber);
    });

    // Enter key untuk search
    lotNumberInput?.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnCariLabel?.click();
        }
    });

    async function cariLabelByLotNumber(lotNumber) {
        try {
            // Normalisasi lot number: trim dan hapus spasi berlebih
            // Tapi jangan hapus semua spasi, karena mungkin memang ada spasi di part number
            const lotNumberNormalized = lotNumber.trim();
            
            console.log('Mencari ASSY In dengan lot number:', {
                original: lotNumber,
                normalized: lotNumberNormalized,
                length: lotNumberNormalized.length
            });

            const response = await fetch(`/produksi/assy/api/assy-in/${encodeURIComponent(lotNumberNormalized)}`);
            const data = await response.json();

            console.log('Response dari API:', data);
            
            // Jika tidak ditemukan, coba tanpa normalisasi (pakai asli)
            if (!data.success && lotNumber !== lotNumberNormalized) {
                console.log('Coba dengan lot number asli tanpa normalisasi');
                const response2 = await fetch(`/produksi/assy/api/assy-in/${encodeURIComponent(lotNumber)}`);
                const data2 = await response2.json();
                
                if (data2.success) {
                    // Update data dengan response yang berhasil
                    Object.assign(data, data2);
                }
            }

            if (!data.success) {
                alert(data.message || 'Label tidak ditemukan');
                console.error('ASSY In tidak ditemukan:', data.message);
                labelInfo.classList.add('hidden');
                inputSection.classList.add('hidden');
                btnSubmit.disabled = true;
                return;
            }

            const labelData = data.data;
            
            // Tampilkan info label
            document.getElementById('displayLotNumber').textContent = labelData.assy_in.lot_number;
            document.getElementById('displayWaktuScanIn').textContent = labelData.assy_in.waktu_scan;
            document.getElementById('displayManpower').textContent = labelData.assy_in.manpower || '-';
            document.getElementById('displayPart').textContent = labelData.part ? 
                `${labelData.part.nomor_part} - ${labelData.part.nama_part}` : '-';

            // Warning jika sudah pernah di-scan out
            const warningAlreadyScanned = document.getElementById('warningAlreadyScanned');
            if (labelData.already_scanned_out) {
                warningAlreadyScanned.classList.remove('hidden');
                btnSubmit.disabled = true;
            } else {
                warningAlreadyScanned.classList.add('hidden');
                btnSubmit.disabled = false;
            }

            labelInfo.classList.remove('hidden');
            inputSection.classList.remove('hidden');

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari label');
        }
    }

    // Submit form
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const lotNumber = lotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Scan atau masukkan lot number terlebih dahulu');
            return;
        }
        formData.append('lot_number', lotNumber);

        try {
            const response = await fetch('{{ route("produksi.assy.storeout") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Label berhasil di-scan out');
                window.location.href = "{{ route('produksi.assy.index') }}";
            } else {
                alert(data.message || 'Gagal menyimpan scan');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan scan');
        }
    });
})();
</script>
@endsection
