@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('bahanbaku.receiving.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium text-lg">Kembali</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Tambah Receiving</h2>
                <p class="text-gray-600 mt-1">Isi data receiving bahan baku</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="formCreateReceiving">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Receiving <span class="text-red-600">*</span></label>
                    <input type="date" name="tanggal_receiving" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Supplier -</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}">{{ $s->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No Surat Jalan</label>
                    <input type="text" name="no_surat_jalan" maxlength="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No Purchase Order</label>
                    <input type="text" name="no_purchase_order" maxlength="100" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Manpower <span class="text-red-600">*</span></label>
                    <div class="flex gap-2">
                        <input 
                            type="text" 
                            id="manpowerInput" 
                            name="manpower" 
                            maxlength="100" 
                            placeholder="Scan QR Code Karyawan" 
                            class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                            readonly
                        />
                        <button 
                            type="button" 
                            id="btnScanManpower" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors text-sm flex items-center gap-2"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            Scan QR
                        </button>
                    </div>
                    <p id="manpowerInfo" class="text-xs text-gray-500 mt-1"></p>
                </div>

                <div class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                    <select 
                        name="shift" 
                        id="shiftSelect"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100" 
                    >
                        <option value="1">Shift 1</option>
                        <option value="2">Shift 2</option>
                        <option value="3">Shift 3</option>
                    </select>
                    <input type="hidden" name="shift" id="shiftHidden" />
                </div>
            </div>

            {{-- Detail --}}
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Receiving Detail</h3>
                    <button type="button" id="btnAddDetail" class="px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 text-sm">Tambah Baris</button>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Bahan Baku</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lot Number</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Internal Lot No</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">UOM</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">QRCode</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailBody" class="divide-y divide-gray-200">
                                {{-- rows injected by JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
                <p class="text-xs text-gray-500 mt-2">Isi minimal 1 baris detail (qty & qrcode) kalau mau simpan detail. Kalau kosong semua, header tetap bisa disimpan.</p>
            </div>

            <div class="flex items-center justify-end gap-2 mt-6">
                <button type="button" id="btnCancel" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
<script>
(function() {
    const form = document.getElementById('formCreateReceiving');
    const btnCancel = document.getElementById('btnCancel');
    const btnAddDetail = document.getElementById('btnAddDetail');
    const detailBody = document.getElementById('detailBody');
    const supplierSelect = form.querySelector('[name="supplier_id"]');

    // Variable untuk menyimpan bahan baku berdasarkan supplier
    let bahanBakuList = [];

    // Fungsi untuk mendapatkan options HTML bahan baku
    function getBahanBakuOptionsHtml() {
        if (bahanBakuList.length === 0) {
            return '<option value="">- Pilih Supplier terlebih dahulu -</option>';
        }
        let html = '<option value="">- Pilih Bahan Baku -</option>';
        bahanBakuList.forEach(bb => {
            html += `<option value="${bb.nomor_bahan_baku}">${bb.nama_bahan_baku}</option>`;
        });
        return html;
    }

    // Fungsi untuk load bahan baku berdasarkan supplier
    async function loadBahanBakuBySupplier(supplierId) {
        if (!supplierId) {
            bahanBakuList = [];
            refreshBahanBakuOptions();
            return;
        }

        try {
            const response = await fetch(`{{ route('bahanbaku.receiving.api.bahanbaku.supplier') }}?supplier_id=${encodeURIComponent(supplierId)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                alert(data.message || 'Gagal memuat bahan baku');
                bahanBakuList = [];
                refreshBahanBakuOptions();
                return;
            }

            bahanBakuList = data.data || [];
            refreshBahanBakuOptions();

        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi error saat memuat bahan baku');
            bahanBakuList = [];
            refreshBahanBakuOptions();
        }
    }

    // Fungsi untuk refresh options bahan baku di semua row
    function refreshBahanBakuOptions() {
        const optionsHtml = getBahanBakuOptionsHtml();
        detailBody.querySelectorAll('select[data-field="nomor_bahan_baku"]').forEach(select => {
            const currentValue = select.value;
            select.innerHTML = optionsHtml;
            select.value = currentValue;
        });
    }

    // Listen perubahan supplier
    if (supplierSelect) {
        supplierSelect.addEventListener('change', function() {
            const supplierId = this.value;
            loadBahanBakuBySupplier(supplierId);
        });
    }

    function generateInternalLotStr(itemName, qty, uom) {
        if(!qty || !itemName) return '';
        const today = new Date();
        const dd = String(today.getDate()).padStart(2, '0');
        const mm = String(today.getMonth() + 1).padStart(2, '0');
        const yyyy = today.getFullYear();
        const dateStr = `${dd}${mm}${yyyy}`;
        
        const isBox = (itemName || '').toUpperCase().includes('BOX');
        const cleanItemName = (itemName || '').replace(/\s+/g, '');
        const cleanUom = (uom || '').replace(/\s+/g, '');

        let suffix = qty;
        if (!isBox && cleanUom) {
            suffix = `${qty}${cleanUom}`;
        }
        return `${dateStr}|${cleanItemName}|${suffix}`;
    }

    function buildQrcode(tanggalReceiving, nomorBahanBaku, lotNumber, seq = 1) {
        // Fallback for old qrcode logic if needed, but we prefer internal lot
        const date = String(tanggalReceiving || '').replace(/[^0-9]/g, '') || new Date().toISOString().slice(0,10).replace(/-/g, '');
        const nbb = String(nomorBahanBaku || 'NA').trim().toUpperCase().replace(/[^A-Z0-9]/g, '') || 'NA';
        const lot = String(lotNumber || 'LOT').trim().toUpperCase().replace(/[^A-Z0-9]/g, '') || 'LOT';
        return `RCV-${date}-${nbb}-${lot}-${String(seq).padStart(2,'0')}`;
    }

    function addRow(initial = {}) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML = `
            <td class="px-4 py-2">
                <select class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" data-field="nomor_bahan_baku">${getBahanBakuOptionsHtml()}</select>
            </td>
            <td class="px-4 py-2"><input type="text" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" data-field="lot_number" maxlength="100" /></td>
            <td class="px-4 py-2"><input type="number" step="0.001" min="0" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" data-field="qty" /></td>
            <td class="px-4 py-2"><input type="text" class="w-full border border-gray-200 bg-gray-50 rounded-lg px-2 py-2 text-sm font-mono text-gray-500" data-field="internal_lot_number" readonly placeholder="Auto" /></td>
            <td class="px-4 py-2">
                <select class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" data-field="uom">
                    <option value="">- Pilih UOM -</option>
                    <option value="kg">kg</option>
                    <option value="karung">karung</option>
                    <option value="tong">tong</option>
                    <option value="palet">palet</option>
                    <option value="pcs">pcs</option>
                    <option value="box">box</option>
                    <option value="sak">sak</option>
                    <option value="drum">drum</option>
                    <option value="liter">liter</option>
                    <option value="meter">meter</option>
                    <option value="roll">roll</option>
                </select>
            </td>
            <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                    <input type="text" class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm bg-gray-50" data-field="qrcode" maxlength="255" readonly />
                    <button type="button" class="btnDownloadQr px-2 py-2 text-xs rounded-md border border-gray-300 hover:bg-gray-50">Download</button>
                </div>
            </td>
            <td class="px-4 py-2 text-center">
                <button type="button" class="btnRemoveRow text-red-600 hover:text-red-900">Hapus</button>
            </td>
        `;

        tr.querySelector('[data-field="nomor_bahan_baku"]').value = initial.nomor_bahan_baku || '';
        tr.querySelector('[data-field="lot_number"]').value = initial.lot_number || '';
        tr.querySelector('[data-field="qty"]').value = initial.qty ?? '';
        tr.querySelector('[data-field="uom"]').value = initial.uom || '';
        tr.querySelector('[data-field="qrcode"]').value = initial.qrcode || '';

        // auto-generate qrcode dari internal_lot_number
        const syncQr = () => {
            const rowSelect = tr.querySelector('[data-field="nomor_bahan_baku"]');
            const itemName = rowSelect.options[rowSelect.selectedIndex]?.text || '';
            const qty = tr.querySelector('[data-field="qty"]')?.value || '';
            const uom = tr.querySelector('[data-field="uom"]')?.value || '';
            
            const internalLot = generateInternalLotStr(itemName, qty, uom);
            tr.querySelector('[data-field="internal_lot_number"]').value = internalLot;
            tr.querySelector('[data-field="qrcode"]').value = internalLot;
        };
        tr.querySelector('[data-field="qty"]')?.addEventListener('input', syncQr);
        tr.querySelector('[data-field="uom"]')?.addEventListener('change', syncQr);
        tr.querySelector('[data-field="nomor_bahan_baku"]')?.addEventListener('change', syncQr);
        
        syncQr();

        detailBody.appendChild(tr);
    }

    function downloadQrPng(text, filename) {
        if (!text) {
            alert('QRCode masih kosong. Isi Lot Number dulu.');
            return;
        }
        if (typeof qrcode !== 'function') {
            alert('QR generator belum siap. Coba refresh halaman.');
            return;
        }

        // typeNumber 0 = auto, error correction = M (sedang)
        const qr = qrcode(0, 'M');
        qr.addData(String(text));
        qr.make();

        const imgTag = qr.createImgTag(6, 2);
        const m = imgTag.match(/src=\"([^\"]+)\"/);
        const src = m ? m[1] : null;
        if (!src) {
            alert('Gagal generate QR image.');
            return;
        }
        const a = document.createElement('a');
        a.href = src;
        a.download = filename || 'qrcode.png';
        document.body.appendChild(a);
        a.click();
        a.remove();
    }

    function collectDetails() {
        const rows = Array.from(detailBody.querySelectorAll('tr'));
        return rows.map((tr) => ({
            nomor_bahan_baku: tr.querySelector('[data-field="nomor_bahan_baku"]').value || null,
            lot_number: tr.querySelector('[data-field="lot_number"]').value || null,
            internal_lot_number: tr.querySelector('[data-field="internal_lot_number"]').value || null,
            qty: tr.querySelector('[data-field="qty"]').value,
            uom: tr.querySelector('[data-field="uom"]').value || null,
            qrcode: tr.querySelector('[data-field="qrcode"]').value,
        }));
    }

    if (btnAddDetail && !btnAddDetail.hasAttribute('data-handler-attached')) {
        btnAddDetail.setAttribute('data-handler-attached', 'true');
        btnAddDetail.addEventListener('click', function() {
            addRow();
        });
    }

    if (detailBody && !detailBody.hasAttribute('data-handler-attached')) {
        detailBody.setAttribute('data-handler-attached', 'true');
        detailBody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnRemoveRow');
            if (!btn) return;
            e.preventDefault();
            btn.closest('tr')?.remove();
        });
    }

    // download qrcode per row
    if (detailBody && !detailBody.hasAttribute('data-handler-download-attached')) {
        detailBody.setAttribute('data-handler-download-attached', 'true');
        detailBody.addEventListener('click', function(e) {
            const btn = e.target.closest('.btnDownloadQr');
            if (!btn) return;
            e.preventDefault();
            const tr = btn.closest('tr');
            const qrText = tr?.querySelector('[data-field=\"qrcode\"]')?.value || '';
            const lot = tr?.querySelector('[data-field=\"lot_number\"]')?.value || '';
            const filename = (lot ? String(lot).replace(/[^A-Za-z0-9_-]/g, '_') : 'qrcode') + '.png';
            downloadQrPng(qrText, filename);
        });
    }

    // default 1 row
    if (detailBody && detailBody.children.length === 0) {
        addRow();
    }

    // Fungsi untuk menentukan shift berdasarkan waktu komputer
    function getCurrentShift() {
        const now = new Date();
        const hour = now.getHours();
        
        if (hour >= 7 && hour < 15) {
            return '1'; // Shift 1: 07:00 - 15:00
        } else if (hour >= 15 && hour < 23) {
            return '2'; // Shift 2: 15:00 - 23:00
        } else {
            return '3'; // Shift 3: 23:00 - 07:00
        }
    }

    // Set shift otomatis saat halaman dimuat
    const shiftSelect = document.getElementById('shiftSelect');
    const shiftHidden = document.getElementById('shiftHidden');
    if (shiftSelect && shiftHidden) {
        const currentShift = getCurrentShift();
        shiftSelect.value = currentShift;
        shiftHidden.value = currentShift;
    }

    // Scan QR Code untuk Manpower
    const btnScanManpower = document.getElementById('btnScanManpower');
    const manpowerInput = document.getElementById('manpowerInput');
    const manpowerInfo = document.getElementById('manpowerInfo');

    if (btnScanManpower && manpowerInput) {
        btnScanManpower.addEventListener('click', async function() {
            const qrCode = prompt('Scan QR Code Karyawan atau masukkan QR Code:');
            if (!qrCode || !qrCode.trim()) return;

            try {
                const response = await fetch(`{{ route('bahanbaku.receiving.api.manpower.qr') }}?qrcode=${encodeURIComponent(qrCode.trim())}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok || !data.success) {
                    alert(data.message || 'Karyawan tidak ditemukan');
                    manpowerInput.value = '';
                    manpowerInfo.textContent = '';
                    return;
                }

                // Set nilai manpower dengan nama karyawan
                const manpowerName = data.data.nama;
                if (data.data.nik) {
                    manpowerInput.value = `${manpowerName} (${data.data.nik})`;
                } else {
                    manpowerInput.value = manpowerName;
                }
                
                // Tampilkan info karyawan
                let infoText = `Nama: ${data.data.nama}`;
                if (data.data.departemen) {
                    infoText += ` | Departemen: ${data.data.departemen}`;
                }
                if (data.data.bagian) {
                    infoText += ` | Bagian: ${data.data.bagian}`;
                }
                manpowerInfo.textContent = infoText;
                manpowerInfo.className = 'text-xs text-green-600 mt-1';

            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi error saat mencari karyawan');
                manpowerInput.value = '';
                manpowerInfo.textContent = '';
            }
        });
    }

    if (btnCancel && !btnCancel.hasAttribute('data-handler-attached')) {
        btnCancel.setAttribute('data-handler-attached', 'true');
        btnCancel.addEventListener('click', function() {
            window.location.href = '{{ route("bahanbaku.receiving.index") }}';
        });
    }

    if (form && !form.hasAttribute('data-handler-attached')) {
        form.setAttribute('data-handler-attached', 'true');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validasi manpower
            if (!manpowerInput.value || !manpowerInput.value.trim()) {
                alert('Silakan scan QR Code karyawan terlebih dahulu');
                return;
            }

            const payload = {
                tanggal_receiving: form.querySelector('[name="tanggal_receiving"]').value,
                supplier_id: form.querySelector('[name="supplier_id"]').value || null,
                no_surat_jalan: form.querySelector('[name="no_surat_jalan"]').value || null,
                no_purchase_order: form.querySelector('[name="no_purchase_order"]').value || null,
                manpower: manpowerInput.value || null,
                shift: shiftHidden.value || null,
                details: collectDetails(),
            };

            try {
                const response = await fetch('{{ route("bahanbaku.receiving.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    alert(data.message || 'Gagal menyimpan data');
                    return;
                }

                alert(data.message || 'Berhasil menyimpan');
                window.location.href = '{{ route("bahanbaku.receiving.index") }}';
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }
})();
</script>
@endsection

