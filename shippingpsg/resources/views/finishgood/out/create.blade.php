@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a 
                href="{{ route('finishgood.out.index') }}" 
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
                title="Kembali"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Scan Label Box - Finish Good Out</h2>
            <p class="text-gray-600 mt-1">Scan QR code label box dari Finish Good In untuk SPK: <span class="font-semibold">{{ $spk->nomor_spk }}</span></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createFinishGoodOutForm" class="space-y-6">
            @csrf

            {{-- Scan QR Code --}}
            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    <span class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        Scan QR Code / Masukkan Lot Number
                    </span>
                </label>
                <div class="flex gap-2">
                    <input 
                        type="text" 
                        id="lotNumberInput" 
                        class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Scan QR code atau ketik lot number dari label box yang sudah di-scan in"
                        autofocus
                    >
                    <button 
                        type="button" 
                        id="btnScanQR" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Scan
                    </button>
                    <button 
                        type="button" 
                        id="btnCariLabel" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Cari
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label box yang sudah di-scan in di Finish Good In</p>
            </div>

            {{-- Info Label (auto-fill setelah scan) --}}
            <div id="labelInfo" class="hidden border border-gray-200 rounded-lg p-4 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Label</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                        <div class="text-sm font-mono text-gray-800" id="displayLotNumber">-</div>
                        <input type="hidden" id="finishGoodInId" name="finish_good_in_id">
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
                        <input type="hidden" id="partId" name="part_id">
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

            {{-- Info SPK --}}
            <div class="border border-blue-200 rounded-lg p-4 bg-blue-50 mb-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-3">Informasi SPK</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nomor SPK</label>
                        <div class="text-sm font-semibold text-gray-800">{{ $spk->nomor_spk }}</div>
                        <input type="hidden" id="spkId" name="spk_id" value="{{ $spk->id }}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Customer</label>
                        <div class="text-sm text-gray-800">{{ $spk->customer->nama_perusahaan ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Plant Gate</label>
                        <div class="text-sm text-gray-800">{{ $spk->plantgate->nama_plantgate ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                        <div class="text-sm text-gray-800">{{ $spk->tanggal->format('d/m/Y') }}</div>
                    </div>
                </div>

                {{-- Info Parts SPK --}}
                @if($spk->details && $spk->details->count() > 0)
                <div class="border-t border-blue-300 pt-4 mt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-2">Parts dalam SPK ini:</h4>
                    <div id="spkPartsList" class="text-sm text-gray-700 space-y-1">
                        @foreach($spk->details as $detail)
                            <div>• {{ $detail->part ? $detail->part->nomor_part . ' - ' . $detail->part->nama_part : '-' }}</div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Warning jika part tidak sesuai dengan SPK --}}
            <div id="warningPartMismatch" class="hidden bg-red-50 border border-red-200 rounded-lg p-3 mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-sm font-medium text-red-800" id="warningPartMismatchText">Part tidak sesuai dengan SPK yang dipilih!</span>
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
                    Simpan Scan Out
                </button>
                <a 
                    href="{{ route('finishgood.out.index') }}" 
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
    const form = document.getElementById('createFinishGoodOutForm');
    const lotNumberInput = document.getElementById('lotNumberInput');
    const btnScanQR = document.getElementById('btnScanQR');
    const btnCariLabel = document.getElementById('btnCariLabel');
    const labelInfo = document.getElementById('labelInfo');
    const warningPartMismatch = document.getElementById('warningPartMismatch');
    const inputSection = document.getElementById('inputSection');
    const btnSubmit = document.getElementById('btnSubmit');

    let scannedPartId = null;
    let scannedLotNumber = null;
    const spkPartIds = @json($spk->details->pluck('part_id')->toArray());

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
            const lotNumberNormalized = lotNumber.trim();
            
            const response = await fetch(`{{ url('finishgood/out/api/finish-good-in') }}/${encodeURIComponent(lotNumberNormalized)}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Label tidak ditemukan');
                labelInfo.classList.add('hidden');
                inputSection.classList.add('hidden');
                btnSubmit.disabled = true;
                return;
            }

            const labelData = data.data;
            
            // Simpan part ID dan lot number
            scannedPartId = labelData.part_id;
            scannedLotNumber = labelData.lot_number;
            
            // Tampilkan info label
            document.getElementById('finishGoodInId').value = labelData.id;
            document.getElementById('partId').value = labelData.part_id;
            document.getElementById('displayLotNumber').textContent = labelData.lot_number;
            document.getElementById('displayWaktuScanIn').textContent = labelData.waktu_scan || '-';
            document.getElementById('displayManpower').textContent = labelData.manpower || '-';
            document.getElementById('displayPart').textContent = labelData.part ? 
                `${labelData.part.nomor_part} - ${labelData.part.nama_part}` : '-';

            // Warning jika sudah pernah di-scan out
            const warningAlreadyScanned = document.getElementById('warningAlreadyScanned');
            if (data.already_scanned) {
                warningAlreadyScanned.classList.remove('hidden');
                btnSubmit.disabled = true;
                inputSection.classList.add('hidden');
            } else {
                warningAlreadyScanned.classList.add('hidden');
                inputSection.classList.remove('hidden');
                checkPartWithSpk();
            }

            labelInfo.classList.remove('hidden');

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari label');
        }
    }

    function checkPartWithSpk() {
        if (!scannedPartId) {
            warningPartMismatch.classList.add('hidden');
            btnSubmit.disabled = true;
            return;
        }

        // Validasi part sesuai dengan SPK
        const partMatch = spkPartIds.includes(parseInt(scannedPartId));

        if (partMatch) {
            warningPartMismatch.classList.add('hidden');
            btnSubmit.disabled = false;
        } else {
            const scannedPart = document.getElementById('displayPart').textContent;
            document.getElementById('warningPartMismatchText').textContent = 
                `Part "${scannedPart}" tidak sesuai dengan SPK yang dipilih! Pastikan part ada di detail SPK.`;
            warningPartMismatch.classList.remove('hidden');
            btnSubmit.disabled = true;
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

        const spkId = document.getElementById('spkId')?.value;
        if (!spkId) {
            alert('SPK tidak ditemukan');
            return;
        }
        formData.append('spk_id', spkId);

        try {
            const response = await fetch('{{ route("finishgood.out.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Label berhasil di-scan out');
                window.location.href = '{{ route("finishgood.out.index") }}';
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
