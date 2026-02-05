@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Supply</h2>
        <p class="text-gray-600 mt-1">Perbarui supply (tujuan inject/assy) untuk planning run</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="formEditSupply">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Planning Run - hanya untuk INJECT -->
                <div id="planningRunDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Planning Run <span class="text-red-600">*</span>
                    </label>
                    <select name="planning_run_id" id="planningRunSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Run -</option>
                        @foreach($planningRuns as $r)
                            <option value="{{ $r->id }}" {{ (string)$supply->planning_run_id === (string)$r->id ? 'selected' : '' }}>
                                #{{ $r->id }} | {{ optional($r->start_at)->format('Y-m-d H:i') }} - {{ optional($r->end_at)->format('H:i') }} | {{ $r->day->mesin->no_mesin ?? '-' }} | {{ $r->mold->part->nomor_part ?? '-' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Part - hanya untuk ASSY -->
                <div id="partDiv" style="display: {{ $supply->tujuan === 'assy' ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Part <span class="text-red-600">*</span>
                    </label>
                    <select name="part_id" id="partSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Part -</option>
                        @if($supply->tujuan === 'assy')
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}" {{ (string)$supply->part_id === (string)$part->id ? 'selected' : '' }}>{{ $part->nomor_part }} - {{ $part->nama_part }}</option>
                            @endforeach
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Parts yang memiliki subpart untuk ASSY</p>
                </div>

                <!-- Meja - hanya untuk ASSY -->
                <div id="mejaDiv" style="display: {{ $supply->tujuan === 'assy' ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Meja <span class="text-red-600">*</span>
                    </label>
                    <input type="text" name="meja" id="mejaInput" value="{{ $supply->meja ?? '' }}" maxlength="50" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: MEJA-1" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipe Planning <span class="text-red-600">*</span>
                    </label>
                    <select name="tujuan" required id="tujuanSelect" disabled class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-100">
                        <option value="inject" {{ $supply->tujuan === 'inject' ? 'selected' : '' }}>INJECT</option>
                        <option value="assy" {{ $supply->tujuan === 'assy' ? 'selected' : '' }}>ASSY</option>
                    </select>
                    <input type="hidden" name="tujuan" value="{{ $supply->tujuan }}">
                    <p class="text-xs text-gray-500 mt-1" id="tujuanHint">
                        Tipe planning tidak dapat diubah setelah dibuat
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Supply <span class="text-red-600">*</span></label>
                    <input type="date" name="tanggal_supply" value="{{ optional($supply->tanggal_supply)->format('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                    <select name="shift_no" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih -</option>
                        <option value="1" {{ (string)$supply->shift_no === '1' ? 'selected' : '' }}>1</option>
                        <option value="2" {{ (string)$supply->shift_no === '2' ? 'selected' : '' }}>2</option>
                        <option value="3" {{ (string)$supply->shift_no === '3' ? 'selected' : '' }}>3</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="DRAFT" {{ $supply->status === 'DRAFT' ? 'selected' : '' }}>DRAFT</option>
                        <option value="CONFIRMED" {{ $supply->status === 'CONFIRMED' ? 'selected' : '' }}>CONFIRMED</option>
                        <option value="CANCELLED" {{ $supply->status === 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="opsional...">{{ $supply->catatan }}</textarea>
                </div>
            </div>

            {{-- Kebutuhan Material dari Planning --}}
            <div id="planningRequirementsDiv" class="mt-6 hidden">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Kebutuhan Material dari Planning
                    </h3>
                    <div id="planningInfo" class="mb-3 text-sm text-gray-600"></div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Materials --}}
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Material & Masterbatch</h4>
                            <div id="materialsList" class="space-y-2">
                                <p class="text-sm text-gray-500">Pilih planning run untuk melihat kebutuhan material</p>
                            </div>
                        </div>
                        
                        {{-- Subparts --}}
                        <div>
                            <h4 class="font-semibold text-gray-700 mb-2">Subpart</h4>
                            <div id="subpartsList" class="space-y-2">
                                <p class="text-sm text-gray-500">Pilih planning run untuk melihat kebutuhan subpart</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail --}}
            <div class="mt-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-800">Supply Detail</h3>
                    <div class="flex gap-2">
                        <!-- Tombol Scan QR -->
                        <button type="button" id="btnScanQR" class="px-3 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700 text-sm">
                            📷 Scan QR
                        </button>
                        <button type="button" id="btnAddDetail" class="px-3 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-900 text-sm">
                            Tambah Baris
                        </button>
                    </div>
                </div>

                <!-- Info kategori yang diperbolehkan -->
                <div id="kategoriInfo" class="mb-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <p class="text-sm text-blue-800">
                        <strong>INJECT:</strong> Bisa supply material/masterbatch (hasil mixing) + subpart, atau subpart saja
                    </p>
                </div>

                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b border-gray-200">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">QR Code / Receiving Sack</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bahan Baku</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nomor BB</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Lot</th>
                                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="detailBody" class="divide-y divide-gray-200"></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 mt-6">
                <a href="{{ route('bahanbaku.supply.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script type="application/json" id="initialDetailsJson">{!! $supply->details->load('receivingDetail.bahanBaku')->map(function($d) {
    return [
        'receiving_detail_id' => $d->receiving_detail_id,
        'qty' => $d->qty,
        'nomor_bahan_baku' => $d->nomor_bahan_baku,
        'lot_number' => $d->lot_number,
        'nama_bahan_baku' => optional($d->receivingDetail)->bahanBaku->nama_bahan_baku ?? '-',
        'kategori' => optional($d->receivingDetail)->bahanBaku->kategori ?? '',
    ];
})->values()->toJson() !!}</script>

<script>
(function() {
    const form = document.getElementById('formEditSupply');
    const tujuanSelect = document.getElementById('tujuanSelect');
    const planningRunDiv = document.getElementById('planningRunDiv');
    const planningRunSelect = document.getElementById('planningRunSelect');
    const partDiv = document.getElementById('partDiv');
    const partSelect = document.getElementById('partSelect');
    const mejaDiv = document.getElementById('mejaDiv');
    const mejaInput = document.getElementById('mejaInput');
    const kategoriInfo = document.getElementById('kategoriInfo');
    const tujuanHint = document.getElementById('tujuanHint');
    const btnScanQR = document.getElementById('btnScanQR');
    const btnAddDetail = document.getElementById('btnAddDetail');
    const btnCancel = document.getElementById('btnCancel');
    const detailBody = document.getElementById('detailBody');

    // Data receiving details dengan kategori
    @php
        $receivingDetailsJson = $receivingDetails->map(function($d) {
            $bahanBaku = $d->bahanBaku ?? null;
            return [
                'id' => $d->id ?? null,
                'qrcode' => $d->qrcode ?? '',
                'nomor_bahan_baku' => $d->nomor_bahan_baku ?? '',
                'nama_bahan_baku' => ($bahanBaku && isset($bahanBaku->nama_bahan_baku)) ? $bahanBaku->nama_bahan_baku : '-',
                'kategori' => ($bahanBaku && isset($bahanBaku->kategori)) ? strtolower(trim($bahanBaku->kategori)) : '',
                'lot_number' => $d->lot_number ?? '',
                'qty' => $d->qty ?? 0,
                'std_packing' => ($bahanBaku && isset($bahanBaku->std_packing)) ? $bahanBaku->std_packing : 0,
                'uom' => ($bahanBaku && isset($bahanBaku->uom)) ? $bahanBaku->uom : 'kg',
            ];
        })->values()->toArray();
    @endphp
    const receivingDetails = @json($receivingDetailsJson);

    const initialDetailsJson = document.getElementById('initialDetailsJson');
    let initialDetails = [];
    if (initialDetailsJson) {
        try {
            initialDetails = JSON.parse(initialDetailsJson.textContent || '[]');
            console.log('Initial details loaded:', initialDetails);
        } catch (e) {
            console.error('Error parsing initial details:', e);
            initialDetails = [];
        }
    }
    console.log('Receiving details available:', receivingDetails.length);
    console.log('Receiving detail IDs:', receivingDetails.map(rd => rd.id));

    // Filter receiving details berdasarkan tujuan
    function getFilteredReceivingDetails() {
        const tujuan = tujuanSelect.value;
        if (tujuan === 'assy') {
            // ASSY: hanya subpart
            return receivingDetails.filter(d => d.kategori === 'subpart');
        } else {
            // INJECT: material, masterbatch, subpart (semua)
            return receivingDetails;
        }
    }

    // Update UI berdasarkan tujuan
    function updateUIByTujuan() {
        const tujuan = tujuanSelect.value;
        
        if (tujuan === 'assy') {
            // ASSY: sembunyikan planning run, tampilkan part dan meja
            planningRunDiv.style.display = 'none';
            planningRunSelect.required = false;
            planningRunSelect.value = '';
            partDiv.style.display = 'block';
            partSelect.required = true;
            mejaDiv.style.display = 'block';
            mejaInput.required = true;
            kategoriInfo.innerHTML = '<p class="text-sm text-blue-800"><strong>ASSY:</strong> Hanya bisa supply subpart</p>';
            tujuanHint.textContent = 'ASSY: hanya subpart saja';
        } else {
            // INJECT: tampilkan planning run, sembunyikan part dan meja
            planningRunDiv.style.display = 'block';
            planningRunSelect.required = true;
            partDiv.style.display = 'none';
            partSelect.required = false;
            partSelect.value = '';
            mejaDiv.style.display = 'none';
            mejaInput.required = false;
            mejaInput.value = '';
            kategoriInfo.innerHTML = '<p class="text-sm text-blue-800"><strong>INJECT:</strong> Bisa supply material/masterbatch (hasil mixing) + subpart, atau subpart saja</p>';
            tujuanHint.textContent = 'INJECT: material/masterbatch + subpart atau subpart saja';
        }
        
        // Refresh dropdown receiving detail
        refreshDetailReceivingOptions();
    }

    // Generate options HTML untuk receiving detail
    function getReceivingOptionsHtml() {
        const filtered = getFilteredReceivingDetails();
        let html = '<option value="">- Pilih Receiving Sack / Scan QR -</option>';
        filtered.forEach(d => {
            const kategoriLabel = d.kategori.toUpperCase();
            const stdPacking = d.std_packing || 0;
            html += `<option value="${d.id}" 
                data-kategori="${d.kategori}" 
                data-nomor="${d.nomor_bahan_baku}" 
                data-lot="${d.lot_number}" 
                data-nama="${d.nama_bahan_baku}" 
                data-qty="${d.qty}"
                data-std-packing="${stdPacking}"
                data-uom="${d.uom || 'kg'}">
                ${d.qrcode} | ${d.nomor_bahan_baku} | ${d.nama_bahan_baku} | ${kategoriLabel} | Std: ${stdPacking} ${d.uom || 'kg'} | Lot: ${d.lot_number}
            </option>`;
        });
        return html;
    }

    // Refresh options di semua select receiving detail
    function refreshDetailReceivingOptions() {
        detailBody.querySelectorAll('select[data-field="receiving_detail_id"]').forEach(select => {
            const currentValue = select.value;
            select.innerHTML = getReceivingOptionsHtml();
            select.value = currentValue;
            // Update row jika value masih ada tapi tidak ada di filtered
            if (currentValue && !select.querySelector(`option[value="${currentValue}"]`)) {
                const row = select.closest('tr');
                if (row) {
                    select.value = '';
                    updateRowFromReceiving(row);
                }
            } else if (currentValue) {
                // Update row info jika value masih valid
                updateRowFromReceiving(select.closest('tr'));
            }
        });
    }

    // Scan QR code (simulasi - bisa diintegrasikan dengan scanner)
    function scanQRCode() {
        const qrCode = prompt('Scan QR Code atau masukkan QR Code:');
        if (!qrCode || !qrCode.trim()) return;

        const found = receivingDetails.find(d => 
            d.qrcode.toLowerCase() === qrCode.trim().toLowerCase()
        );

        if (!found) {
            alert('QR Code tidak ditemukan di receiving detail');
            return;
        }

        // Cek kategori sesuai tujuan
        const tujuan = tujuanSelect.value;
        if (tujuan === 'assy' && found.kategori !== 'subpart') {
            alert('ASSY hanya bisa supply subpart. QR Code ini bukan subpart.');
            return;
        }

        // Cek apakah sudah ada di detail
        const existingRow = Array.from(detailBody.querySelectorAll('tr')).find(tr => {
            const select = tr.querySelector('select[data-field="receiving_detail_id"]');
            return select && select.value == found.id;
        });

        if (existingRow) {
            alert('Receiving detail ini sudah ditambahkan');
            return;
        }

        // Tambah baris dengan data dari QR
        addRow({
            receiving_detail_id: found.id,
            nomor_bahan_baku: found.nomor_bahan_baku,
            lot_number: found.lot_number,
        });
    }

    function addRow(initial = {}) {
        const tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        
        const receivingOptionsHtml = getReceivingOptionsHtml();
        
        tr.innerHTML = `
            <td class="px-4 py-2">
                <select class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" 
                        data-field="receiving_detail_id" 
                        data-row-receiving>
                    ${receivingOptionsHtml}
                </select>
            </td>
            <td class="px-4 py-2">
                <span class="text-sm text-gray-600 bahan-nama">-</span>
            </td>
            <td class="px-4 py-2">
                <span class="text-xs px-2 py-1 rounded kategori-badge">-</span>
            </td>
            <td class="px-4 py-2">
                <input type="number" step="0.001" min="0" 
                       class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm" 
                       data-field="qty" placeholder="25" required />
            </td>
            <td class="px-4 py-2">
                <input type="text" maxlength="100" 
                       class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm bg-gray-50" 
                       data-field="nomor_bahan_baku" placeholder="MAT-0001" readonly />
            </td>
            <td class="px-4 py-2">
                <input type="text" maxlength="100" 
                       class="w-full border border-gray-300 rounded-lg px-2 py-2 text-sm bg-gray-50" 
                       data-field="lot_number" placeholder="LOT-..." readonly />
            </td>
            <td class="px-4 py-2 text-center">
                <button type="button" class="btnRemoveRow text-red-600 hover:text-red-900">Hapus</button>
            </td>
        `;

        // Set initial values
        const receivingSelect = tr.querySelector('[data-field="receiving_detail_id"]');
        if (initial.receiving_detail_id) {
            console.log('Setting initial receiving_detail_id:', initial.receiving_detail_id, 'Type:', typeof initial.receiving_detail_id);
            // Pastikan option ada di select, jika tidak ada, tambahkan dari receivingDetails
            const existingOption = receivingSelect.querySelector(`option[value="${initial.receiving_detail_id}"]`);
            if (!existingOption) {
                console.log('Option not found in select, searching in receivingDetails...');
                // Coba dengan perbandingan yang lebih fleksibel (string vs number)
                const receivingDetail = receivingDetails.find(rd => {
                    return rd.id == initial.receiving_detail_id || 
                           String(rd.id) === String(initial.receiving_detail_id) ||
                           Number(rd.id) === Number(initial.receiving_detail_id);
                });
                console.log('Found receiving detail:', receivingDetail ? 'Yes' : 'No');
                if (receivingDetail) {
                    const option = document.createElement('option');
                    option.value = receivingDetail.id;
                    const kategoriLabel = (receivingDetail.kategori || '').toUpperCase();
                    const stdPacking = receivingDetail.std_packing || 0;
                    option.textContent = `${receivingDetail.qrcode} | ${receivingDetail.nomor_bahan_baku} | ${receivingDetail.nama_bahan_baku} | ${kategoriLabel} | Std: ${stdPacking} ${receivingDetail.uom || 'kg'} | Lot: ${receivingDetail.lot_number}`;
                    option.dataset.kategori = receivingDetail.kategori || '';
                    option.dataset.nomor = receivingDetail.nomor_bahan_baku || '';
                    option.dataset.lot = receivingDetail.lot_number || '';
                    option.dataset.nama = receivingDetail.nama_bahan_baku || '-';
                    option.dataset.qty = receivingDetail.qty || 0;
                    option.dataset.stdPacking = receivingDetail.std_packing || 0;
                    option.dataset.uom = receivingDetail.uom || 'kg';
                    receivingSelect.appendChild(option);
                }
            }
            receivingSelect.value = initial.receiving_detail_id;
            // Update row dari receiving detail yang dipilih
            updateRowFromReceiving(tr);
        }
        tr.querySelector('[data-field="qty"]').value = initial.qty || '';
        tr.querySelector('[data-field="nomor_bahan_baku"]').value = initial.nomor_bahan_baku || '';
        tr.querySelector('[data-field="lot_number"]').value = initial.lot_number || '';

        // Handler ketika select receiving detail berubah
        tr.querySelector('[data-row-receiving]').addEventListener('change', function() {
            updateRowFromReceiving(tr);
        });

        detailBody.appendChild(tr);
        return tr;
    }

    // Update row dari receiving detail yang dipilih
    function updateRowFromReceiving(row) {
        const select = row.querySelector('[data-row-receiving]');
        const selectedOption = select.options[select.selectedIndex];
        
        if (selectedOption.value) {
            const kategori = selectedOption.dataset.kategori || '';
            const nomor = selectedOption.dataset.nomor || '';
            const lot = selectedOption.dataset.lot || '';
            const nama = selectedOption.dataset.nama || '';
            
            row.querySelector('[data-field="nomor_bahan_baku"]').value = nomor;
            row.querySelector('[data-field="lot_number"]').value = lot;
            row.querySelector('.bahan-nama').textContent = nama || '-';
            
            const badge = row.querySelector('.kategori-badge');
            badge.textContent = kategori.toUpperCase();
            badge.className = `text-xs px-2 py-1 rounded kategori-badge ${
                kategori === 'subpart' ? 'bg-blue-100 text-blue-800' :
                kategori === 'material' ? 'bg-green-100 text-green-800' :
                kategori === 'masterbatch' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'
            }`;
        } else {
            row.querySelector('.bahan-nama').textContent = '-';
            row.querySelector('.kategori-badge').textContent = '-';
            row.querySelector('.kategori-badge').className = 'text-xs px-2 py-1 rounded kategori-badge';
            row.querySelector('[data-field="nomor_bahan_baku"]').value = '';
            row.querySelector('[data-field="lot_number"]').value = '';
        }
    }

    function collectDetails() {
        const rows = Array.from(detailBody.querySelectorAll('tr'));
        return rows.map((tr) => {
            const receivingDetailId = tr.querySelector('[data-field="receiving_detail_id"]').value;
            if (!receivingDetailId) return null;
            
            return {
                receiving_detail_id: receivingDetailId,
                qty: tr.querySelector('[data-field="qty"]').value,
            };
        }).filter(item => item !== null);
    }

    // Load kebutuhan material dari planning run
    const planningRequirementsDiv = document.getElementById('planningRequirementsDiv');
    const planningInfo = document.getElementById('planningInfo');
    const materialsList = document.getElementById('materialsList');
    const subpartsList = document.getElementById('subpartsList');

    async function loadPlanningRequirements(planningRunId) {
        if (!planningRunId) {
            planningRequirementsDiv.classList.add('hidden');
            return;
        }

        try {
            const response = await fetch(`{{ route('bahanbaku.supply.api.planningRequirements') }}?planning_run_id=${encodeURIComponent(planningRunId)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                console.error('Error loading planning requirements:', data.message);
                planningRequirementsDiv.classList.add('hidden');
                return;
            }

            const req = data.data;
            
            // Tampilkan info planning
            planningInfo.innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                    <div><span class="font-semibold">Lot Produksi:</span> ${req.planning_run.lot_produksi || '-'}</div>
                    <div><span class="font-semibold">Mesin:</span> ${req.planning_run.mesin}</div>
                    <div><span class="font-semibold">Part:</span> ${req.planning_run.part}</div>
                    <div><span class="font-semibold">Qty Target:</span> ${req.planning_run.qty_target || 0}</div>
                </div>
            `;

            // Tampilkan materials
            if (req.materials && req.materials.length > 0) {
                materialsList.innerHTML = req.materials.map(m => `
                    <div class="bg-white border border-gray-200 rounded p-2 text-sm">
                        <div class="font-semibold text-gray-800">${m.nama_bahan_baku}</div>
                        <div class="text-gray-600 text-xs mt-1">
                            <span>Qty: ${m.qty_total} ${m.uom || ''}</span>
                            ${m.nomor_bahan_baku ? `<span class="ml-2">| ${m.nomor_bahan_baku}</span>` : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                materialsList.innerHTML = '<p class="text-sm text-gray-500">Tidak ada material</p>';
            }

            // Tampilkan subparts
            if (req.subparts && req.subparts.length > 0) {
                subpartsList.innerHTML = req.subparts.map(s => `
                    <div class="bg-white border border-gray-200 rounded p-2 text-sm">
                        <div class="font-semibold text-gray-800">${s.nama_bahan_baku}</div>
                        <div class="text-gray-600 text-xs mt-1">
                            <span>Qty: ${s.qty_total} ${s.uom || ''}</span>
                            ${s.nomor_bahan_baku ? `<span class="ml-2">| ${s.nomor_bahan_baku}</span>` : ''}
                        </div>
                    </div>
                `).join('');
            } else {
                subpartsList.innerHTML = '<p class="text-sm text-gray-500">Tidak ada subpart</p>';
            }

            planningRequirementsDiv.classList.remove('hidden');

        } catch (error) {
            console.error('Error loading planning requirements:', error);
            planningRequirementsDiv.classList.add('hidden');
        }
    }

    if (planningRunSelect) {
        planningRunSelect.addEventListener('change', function() {
            const planningRunId = this.value;
            loadPlanningRequirements(planningRunId);
        });
        
        // Load requirements saat halaman dimuat jika planning run sudah dipilih
        if (planningRunSelect.value) {
            loadPlanningRequirements(planningRunSelect.value);
        }
    }

    // Event listeners
    // Tujuan tidak bisa diubah (disabled), jadi tidak perlu event listener
    // tujuanSelect.addEventListener('change', updateUIByTujuan);
    btnScanQR.addEventListener('click', scanQRCode);
    btnAddDetail.addEventListener('click', () => addRow());
    
    detailBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btnRemoveRow')) {
            e.target.closest('tr')?.remove();
        }
    });

    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            window.location.href = '{{ route("bahanbaku.supply.index") }}';
        });
    }

    // Initialize - update UI berdasarkan tujuan yang sudah ada
    updateUIByTujuan();
    
    // Load initial details setelah semua elemen siap
    setTimeout(() => {
        console.log('Loading initial details, count:', initialDetails ? initialDetails.length : 0);
        console.log('Initial details:', initialDetails);
        console.log('Receiving details available:', receivingDetails.length);
        console.log('Receiving detail IDs:', receivingDetails.map(rd => rd.id));
        
        if (initialDetails && initialDetails.length > 0) {
            initialDetails.forEach((d, index) => {
                console.log(`Adding row ${index + 1}:`, d);
                console.log('Looking for receiving_detail_id:', d.receiving_detail_id);
                const row = addRow(d);
                console.log('Row added:', row);
                
                // Double check setelah row ditambahkan
                setTimeout(() => {
                    const select = row.querySelector('[data-field="receiving_detail_id"]');
                    if (select && d.receiving_detail_id && !select.value) {
                        console.warn('Receiving detail ID not set, trying again...');
                        // Coba lagi dengan delay lebih lama
                        const receivingDetail = receivingDetails.find(rd => {
                            return rd.id == d.receiving_detail_id || 
                                   String(rd.id) === String(d.receiving_detail_id);
                        });
                        if (receivingDetail) {
                            console.log('Found receiving detail, setting value...');
                            select.value = receivingDetail.id;
                            updateRowFromReceiving(row);
                        }
                    }
                }, 50);
            });
        } else {
            console.log('No initial details, adding empty row');
            addRow();
        }
    }, 200);

    // Form submit handler
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                planning_run_id: planningRunSelect.value || null,
                part_id: partSelect.value || null,
                meja: mejaInput.value || null,
                tujuan: tujuanSelect.value,
                tanggal_supply: form.querySelector('[name="tanggal_supply"]').value,
                shift_no: form.querySelector('[name="shift_no"]').value || null,
                status: form.querySelector('[name="status"]').value || 'DRAFT',
                catatan: form.querySelector('[name="catatan"]').value || null,
                details: collectDetails(),
            };

            try {
                const response = await fetch(`/bahanbaku/supply/{{ $supply->id }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ ...payload, _method: 'PUT' })
                });

                const data = await response.json().catch(() => ({}));
                
                if (!response.ok || !data.success) {
                    alert(data.message || 'Gagal update data');
                    return;
                }

                alert(data.message || 'Berhasil update');
                window.location.href = '{{ route("bahanbaku.supply.index") }}';
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }
})();
</script>
@endsection
