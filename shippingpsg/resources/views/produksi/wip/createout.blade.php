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

            <h2 class="text-3xl font-bold text-gray-800">Scan Label Box - WIP Out</h2>
            <p class="text-gray-600 mt-1">Scan QR code label box yang sudah di-scan in dan sistem akan otomatis mencatat waktu scan out</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createWipOutForm" class="space-y-6">
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
                    </a>
                    <button 
                        type="button" 
                        id="btnCariLabel" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                    >
                        Cari
                    </a>
                </div>
                <p class="text-xs text-gray-500 mt-2">Scan QR code dari label box yang sudah di-scan in di WIP</p>
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
                        <label class="block text-xs font-medium text-gray-600 mb-1">Box Number</label>
                        <div class="text-sm text-gray-800" id="displayBoxNumber">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan In</label>
                        <div class="text-sm text-gray-800" id="displayWaktuScanIn">-</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                        <div class="text-sm text-gray-800" id="displayPart">-</div>
                    </div>
                </div>

                {{-- Info Target dan Progress Box --}}
                <div id="targetInfo" class="hidden border-t border-gray-300 pt-4 mt-4">
                    <h4 class="text-md font-semibold text-gray-800 mb-3">Target dan Progress Box</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                            <label class="block text-xs font-medium text-blue-600 mb-1">Target Total</label>
                            <div class="text-lg font-bold text-blue-800" id="displayTargetTotal">-</div>
                            <div class="text-xs text-blue-600 mt-1" id="displayQtyPacking">Qty/Box: -</div>
                        </div>
                        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
                            <label class="block text-xs font-medium text-green-600 mb-1">Target Box</label>
                            <div class="text-lg font-bold text-green-800" id="displayTargetBox">-</div>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <label class="block text-xs font-medium text-yellow-600 mb-1">Sudah Di-scan</label>
                            <div class="text-lg font-bold text-yellow-800" id="displayScannedBox">-</div>
                            <div class="text-xs text-yellow-600 mt-1" id="displayRemainingBox">Sisa: -</div>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mb-4">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Progress Box</label>
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-4">
                                <div 
                                    id="progressBar" 
                                    class="h-4 rounded-full transition-all bg-green-600"
                                    style="width: 0%"
                                ></div>
                            </div>
                            <span class="text-sm font-medium text-gray-700 whitespace-nowrap" id="progressText">0/0</span>
                        </div>
                    </div>

                    {{-- Warning jika sudah mencapai target --}}
                    <div id="warningTargetReached" class="hidden bg-orange-50 border border-orange-200 rounded-lg p-3 mb-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm font-medium text-orange-800">Semua box sudah di-scan out! Target sudah tercapai.</span>
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
    const form = document.getElementById('createWipOutForm');
    const lotNumberInput = document.getElementById('lotNumberInput');
    const btnScanQR = document.getElementById('btnScanQR');
    const btnCariLabel = document.getElementById('btnCariLabel');
    const labelInfo = document.getElementById('labelInfo');
    const inputSection = document.getElementById('inputSection');
    const btnSubmit = document.getElementById('btnSubmit');
    
    // Variabel untuk auto-submit
    let autoSubmitTimer = null;
    let cancelAutoSubmitHandler = null;

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
            const response = await fetch(`/produksi/wip/api/wip-in/${encodeURIComponent(lotNumber)}`);
            const data = await response.json();

            if (!data.success) {
                alert(data.message || 'Label tidak ditemukan');
                labelInfo.classList.add('hidden');
                inputSection.classList.add('hidden');
                btnSubmit.disabled = true;
                return;
            }

            const labelData = data.data;
            
            // Tampilkan info label
            document.getElementById('displayLotNumber').textContent = labelData.wip_in.lot_number;
            document.getElementById('displayBoxNumber').textContent = `Box #${labelData.wip_in.box_number || '-'}`;
            document.getElementById('displayWaktuScanIn').textContent = labelData.wip_in.waktu_scan_in;
            document.getElementById('displayPart').textContent = labelData.part ? `${labelData.part.nomor_part} - ${labelData.part.nama_part}` : '-';

            // Tampilkan info target dan progress
            if (labelData.planning_run && labelData.part) {
                const targetInfo = document.getElementById('targetInfo');
                targetInfo.classList.remove('hidden');
                
                const targetTotal = labelData.planning_run.qty_target_total || 0;
                const qtyPackingBox = labelData.part?.qty_packing_box || 0;
                const targetBoxCount = labelData.target_box_count || 0;
                const scannedBoxCount = labelData.scanned_box_count || 0;
                const remainingBoxCount = labelData.remaining_box_count || 0;

                document.getElementById('displayTargetTotal').textContent = targetTotal.toLocaleString('id-ID') + ' pcs';
                document.getElementById('displayQtyPacking').textContent = `Qty/Box: ${qtyPackingBox} pcs`;
                document.getElementById('displayTargetBox').textContent = targetBoxCount + ' box';
                document.getElementById('displayScannedBox').textContent = scannedBoxCount + ' box';
                document.getElementById('displayRemainingBox').textContent = `Sisa: ${remainingBoxCount} box`;

                // Update progress bar
                const percentage = targetBoxCount > 0 ? (scannedBoxCount / targetBoxCount) * 100 : 0;
                const progressBar = document.getElementById('progressBar');
                const progressText = document.getElementById('progressText');
                progressBar.style.width = Math.min(100, percentage) + '%';
                progressText.textContent = `${scannedBoxCount}/${targetBoxCount}`;
                
                // Warna progress bar
                if (percentage >= 100) {
                    progressBar.classList.remove('bg-green-600', 'bg-yellow-500');
                    progressBar.classList.add('bg-green-600');
                } else if (percentage >= 80) {
                    progressBar.classList.remove('bg-green-600', 'bg-green-600');
                    progressBar.classList.add('bg-yellow-500');
                } else {
                    progressBar.classList.remove('bg-yellow-500', 'bg-green-600');
                    progressBar.classList.add('bg-green-600');
                }

                // Warning jika sudah mencapai target
                const warningTargetReached = document.getElementById('warningTargetReached');
                if (remainingBoxCount <= 0) {
                    warningTargetReached.classList.remove('hidden');
                    btnSubmit.disabled = true;
                } else {
                    warningTargetReached.classList.add('hidden');
                    btnSubmit.disabled = false;
                    
                    // Clear timer sebelumnya jika ada
                    if (autoSubmitTimer) {
                        clearTimeout(autoSubmitTimer);
                        autoSubmitTimer = null;
                    }
                    if (cancelAutoSubmitHandler) {
                        document.removeEventListener('keydown', cancelAutoSubmitHandler);
                        cancelAutoSubmitHandler = null;
                    }
                    
                    // Auto-submit setelah 1 detik (untuk mempercepat proses scan berulang)
                    // User bisa cancel dengan menekan ESC sebelum auto-submit
                    autoSubmitTimer = setTimeout(() => {
                        if (btnSubmit && !btnSubmit.disabled) {
                            form.dispatchEvent(new Event('submit'));
                        }
                        autoSubmitTimer = null;
                    }, 1000);
                    
                    // Cancel auto-submit jika user tekan ESC
                    cancelAutoSubmitHandler = function(e) {
                        if (e.key === 'Escape') {
                            if (autoSubmitTimer) {
                                clearTimeout(autoSubmitTimer);
                                autoSubmitTimer = null;
                            }
                            document.removeEventListener('keydown', cancelAutoSubmitHandler);
                            cancelAutoSubmitHandler = null;
                        }
                    };
                    document.addEventListener('keydown', cancelAutoSubmitHandler, { once: true });
                }
            } else {
                document.getElementById('targetInfo').classList.add('hidden');
                btnSubmit.disabled = false;
            }

            labelInfo.classList.remove('hidden');
            inputSection.classList.remove('hidden');

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mencari label');
        }
    }

    // Fungsi untuk reset form
    function resetForm() {
        // Clear auto-submit timer jika ada
        if (autoSubmitTimer) {
            clearTimeout(autoSubmitTimer);
            autoSubmitTimer = null;
        }
        if (cancelAutoSubmitHandler) {
            document.removeEventListener('keydown', cancelAutoSubmitHandler);
            cancelAutoSubmitHandler = null;
        }
        
        // Clear input lot number
        lotNumberInput.value = '';
        
        // Hide info label dan input section
        labelInfo.classList.add('hidden');
        inputSection.classList.add('hidden');
        document.getElementById('targetInfo').classList.add('hidden');
        
        // Disable submit button
        btnSubmit.disabled = true;
        
        // Reset form data
        form.reset();
        
        // Reset textarea catatan
        const catatanTextarea = form.querySelector('textarea[name="catatan"]');
        if (catatanTextarea) {
            catatanTextarea.value = '';
        }
        
        // Focus kembali ke input untuk scan berikutnya
        setTimeout(() => {
            lotNumberInput.focus();
        }, 100);
    }

    // Submit form
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Clear auto-submit timer jika ada
        if (autoSubmitTimer) {
            clearTimeout(autoSubmitTimer);
            autoSubmitTimer = null;
        }
        if (cancelAutoSubmitHandler) {
            document.removeEventListener('keydown', cancelAutoSubmitHandler);
            cancelAutoSubmitHandler = null;
        }
        
        const formData = new FormData(form);
        const lotNumber = lotNumberInput.value.trim();
        if (!lotNumber) {
            alert('Scan atau masukkan lot number terlebih dahulu');
            return;
        }
        formData.append('lot_number', lotNumber);

        // Disable submit button saat proses
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Menyimpan...';

        try {
            const response = await fetch('{{ route("produksi.wip.storeout") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Simpan lot number yang baru saja di-scan
                const scannedLotNumber = lotNumber;
                
                // Tampilkan notifikasi sukses
                const successMsg = document.createElement('div');
                successMsg.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                successMsg.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>${data.message || 'Label berhasil di-scan out'}</span>
                `;
                document.body.appendChild(successMsg);
                
                // Hapus notifikasi setelah 2 detik
                setTimeout(() => {
                    successMsg.remove();
                }, 2000);
                
                // Reset form
                resetForm();
                
                // Jika user ingin scan box berikutnya dengan lot number yang sama,
                // otomatis isi dan cari ulang data terbaru
                // (optional: bisa diaktifkan jika diperlukan)
                // setTimeout(() => {
                //     lotNumberInput.value = scannedLotNumber;
                //     cariLabelByLotNumber(scannedLotNumber);
                // }, 300);
                
                // Atau biarkan kosong untuk scan lot number baru (default)
                // Auto-focus sudah dilakukan di resetForm()
            } else {
                // Tampilkan error
                const errorMsg = document.createElement('div');
                errorMsg.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                errorMsg.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>${data.message || 'Gagal menyimpan scan'}</span>
                `;
                document.body.appendChild(errorMsg);
                
                setTimeout(() => {
                    errorMsg.remove();
                }, 3000);
                
                // Enable kembali submit button
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Simpan Scan Out';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan scan');
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Simpan Scan Out';
        }
    });
    
    // Auto-focus ke input saat halaman dimuat
    if (lotNumberInput) {
        lotNumberInput.focus();
    }
})();
</script>
@endsection
