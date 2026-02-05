@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <div class="flex items-center gap-4">
            <a href="{{ route('bahanbaku.supply.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium text-lg">Kembali</span>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Tambah Supply</h2>
                <p class="text-gray-600 mt-1">Supply per run untuk INJECT, atau supply langsung untuk ASSY</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="formCreateSupply">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Tipe Planning -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tipe Planning <span class="text-red-600">*</span>
                    </label>
                    <select name="tipe_planning" id="tipePlanningSelect" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Tipe -</option>
                        <option value="inject">INJECT</option>
                        <option value="assy">ASSY</option>
                    </select>
                </div>

                <!-- Planning Run - untuk INJECT dan ASSY -->
                <div id="planningRunDiv" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Planning Run <span class="text-red-600">*</span>
                    </label>
                    <select name="planning_run_id" id="planningRunSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Run -</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Pilih tanggal supply terlebih dahulu</p>
                </div>

                <!-- Mesin - untuk INJECT (dropdown, auto-populate dari planning) -->
                <div id="mesinDiv" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Mesin
                    </label>
                    <select name="mesin_id" id="mesinSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" disabled>
                        <option value="">- Pilih Mesin -</option>
                        @foreach($mesins ?? [] as $mesin)
                            <option value="{{ $mesin->id }}">{{ $mesin->no_mesin }}{{ $mesin->tonase ? ' - ' . $mesin->tonase . 'T' : '' }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Otomatis dari planning run yang dipilih</p>
                </div>

                <!-- Meja - untuk ASSY (dropdown, auto-populate dari planning) -->
                <div id="mejaDiv" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Meja
                    </label>
                    <select name="meja" id="mejaSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 bg-gray-100" disabled>
                        <option value="">- Pilih Meja -</option>
                        @foreach($mejas ?? [] as $meja)
                            <option value="{{ $meja }}">{{ $meja }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Otomatis dari planning run yang dipilih</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Supply <span class="text-red-600">*</span></label>
                    <input type="date" name="tanggal_supply" id="tanggalSupplyInput" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p class="text-xs text-gray-500 mt-1">Tanggal harus sesuai dengan tanggal planning</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Shift</label>
                    <select name="shift_no" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih -</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="DRAFT">DRAFT</option>
                        <option value="CONFIRMED">CONFIRMED</option>
                        <option value="CANCELLED">CANCELLED</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="opsional..."></textarea>
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
                                <p class="text-sm text-gray-500" id="subpartsListPlaceholder">Pilih planning run untuk INJECT atau part untuk ASSY untuk melihat kebutuhan subpart</p>
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
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Scan x Qty</th>
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
                <button type="button" id="btnCancel" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('formCreateSupply');
    const tipePlanningSelect = document.getElementById('tipePlanningSelect');
    const planningRunDiv = document.getElementById('planningRunDiv');
    const planningRunSelect = document.getElementById('planningRunSelect');
    const tanggalSupplyInput = document.getElementById('tanggalSupplyInput');
    const partDiv = document.getElementById('partDiv');
    const partSelect = document.getElementById('partSelect');
    const mesinDiv = document.getElementById('mesinDiv');
    const mesinSelect = document.getElementById('mesinSelect');
    const mejaDiv = document.getElementById('mejaDiv');
    const mejaSelect = document.getElementById('mejaSelect');
    const kategoriInfo = document.getElementById('kategoriInfo');
    const btnScanQR = document.getElementById('btnScanQR');
    const btnAddDetail = document.getElementById('btnAddDetail');
    const btnCancel = document.getElementById('btnCancel');
    const detailBody = document.getElementById('detailBody');
    const planningRequirementsDiv = document.getElementById('planningRequirementsDiv');
    const planningInfo = document.getElementById('planningInfo');
    const materialsList = document.getElementById('materialsList');
    const subpartsList = document.getElementById('subpartsList');

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
    
    // Track material yang dipilih untuk menghitung masterbatch (3% dari material)
    let selectedMaterialStdPacking = 0;

    // Filter receiving details berdasarkan tipe planning
    function getFilteredReceivingDetails() {
        const tipe = tipePlanningSelect.value;
        if (tipe === 'assy') {
            // ASSY: hanya subpart
            return receivingDetails.filter(d => d.kategori === 'subpart');
        } else {
            // INJECT: material, masterbatch, subpart (semua)
            return receivingDetails;
        }
    }

    // Load subparts dari part yang dipilih (untuk ASSY)
    async function loadPartSubparts(partId) {
        if (!subpartsList || !planningRequirementsDiv || !planningInfo || !materialsList) {
            console.error('Required elements not found for loadPartSubparts');
            return;
        }
        
        if (!partId) {
            subpartsList.innerHTML = '<p class="text-sm text-gray-500" id="subpartsListPlaceholder">Pilih part untuk melihat subpart</p>';
            planningRequirementsDiv.classList.add('hidden');
            return;
        }

        try {
            // Show loading state
            planningRequirementsDiv.classList.remove('hidden');
            planningInfo.innerHTML = '<p class="text-sm text-gray-500">Memuat data...</p>';
            materialsList.innerHTML = '<p class="text-sm text-gray-500">ASSY: Tidak menggunakan material</p>';
            subpartsList.innerHTML = '<p class="text-sm text-gray-500">Memuat...</p>';

            const url = `{{ route('bahanbaku.supply.api.partSubparts') }}?part_id=${encodeURIComponent(partId)}&tipe=assy`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success && data.subparts && data.subparts.length > 0) {
                // Update planning info untuk ASSY
                planningInfo.innerHTML = `
                    <p class="text-sm text-gray-700"><strong>Part:</strong> ${data.part.nomor_part} - ${data.part.nama_part}</p>
                    <p class="text-xs text-gray-500 mt-1">ASSY: Supply subpart sesuai kebutuhan</p>
                `;

                // Materials list: ASSY tidak menggunakan material
                materialsList.innerHTML = '<p class="text-sm text-gray-500">ASSY: Tidak menggunakan material/masterbatch</p>';

                // Tampilkan subparts
                subpartsList.innerHTML = data.subparts.map(s => `
                    <div class="bg-white border border-gray-200 rounded p-2 text-sm">
                        <div class="font-medium text-gray-800">${s.urutan}. ${s.subpart.nama || '-'}</div>
                        <div class="text-xs text-gray-500 mt-1">
                            Nomor: ${s.subpart.nomor || '-'} | Std: ${s.std_using} ${s.subpart.uom || ''}
                        </div>
                    </div>
                `).join('');

                // Tampilkan planning requirements div untuk ASSY
                planningRequirementsDiv.classList.remove('hidden');
            } else {
                planningInfo.innerHTML = `
                    <p class="text-sm text-gray-700"><strong>Part:</strong> ${data.part ? (data.part.nomor_part + ' - ' + data.part.nama_part) : '-'}</p>
                    <p class="text-xs text-gray-500 mt-1">ASSY: Supply subpart sesuai kebutuhan</p>
                `;
                materialsList.innerHTML = '<p class="text-sm text-gray-500">ASSY: Tidak menggunakan material/masterbatch</p>';
                subpartsList.innerHTML = '<p class="text-sm text-gray-500">Tidak ada subpart untuk part ini</p>';
                planningRequirementsDiv.classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error loading part subparts:', error);
            planningInfo.innerHTML = '<p class="text-sm text-red-500">Error memuat data part</p>';
            materialsList.innerHTML = '<p class="text-sm text-gray-500">-</p>';
            subpartsList.innerHTML = '<p class="text-sm text-red-500">Error memuat subpart</p>';
        }
    }

    // Load planning runs berdasarkan tipe dan tanggal
    async function loadPlanningRuns() {
        const tipe = tipePlanningSelect.value;
        const tanggal = tanggalSupplyInput.value;
        
        console.log('loadPlanningRuns called with:', { tipe, tanggal });
        
        if (!tipe || !tanggal) {
            console.log('Missing tipe or tanggal, clearing options');
            planningRunSelect.innerHTML = '<option value="">- Pilih Run -</option>';
            return;
        }
        
        planningRunSelect.innerHTML = '<option value="">Memuat...</option>';
        planningRunSelect.disabled = true;
        
        try {
            const url = `{{ route('bahanbaku.supply.api.planningRuns') }}?tipe=${encodeURIComponent(tipe)}&tanggal=${encodeURIComponent(tanggal)}`;
            console.log('Fetching URL:', url);
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);
            
            if (data.success && data.runs) {
                console.log('Found runs:', data.runs.length);
                let html = '<option value="">- Pilih Run -</option>';
                data.runs.forEach(run => {
                    const mesin = run.mesin || '-';
                    const part = run.part || '-';
                    const lot = run.lot_produksi || '-';
                    const mejaValue = (run.meja && run.meja !== '-') ? run.meja : '';
                    const mesinIdValue = run.mesin_id || '';
                    const partIdValue = run.part_id || '';
                    html += `<option value="${run.id}" data-tanggal="${run.tanggal}" data-mesin-id="${mesinIdValue}" data-meja="${mejaValue}" data-part-id="${partIdValue}">
                        #${run.id} | ${run.tanggal} | ${mesin} | ${part} | Lot: ${lot}
                    </option>`;
                    console.log('Adding option for run:', run.id, 'meja:', mejaValue, 'part_id:', partIdValue);
                });
                planningRunSelect.innerHTML = html;
            } else {
                console.log('No runs found or error:', data);
                planningRunSelect.innerHTML = '<option value="">- Tidak ada run untuk tanggal ini -</option>';
            }
        } catch (error) {
            console.error('Error loading planning runs:', error);
            planningRunSelect.innerHTML = '<option value="">- Error memuat data -</option>';
        } finally {
            planningRunSelect.disabled = false;
        }
    }

    // Load parts berdasarkan tipe planning
    async function loadPartsByTipe() {
        const tipe = tipePlanningSelect.value;
        
        if (!tipe) {
            partSelect.innerHTML = '<option value="">- Pilih Part -</option>';
            return;
        }
        
        partSelect.innerHTML = '<option value="">Memuat...</option>';
        partSelect.disabled = true;
        
        try {
            const url = `{{ route('bahanbaku.supply.api.partsByTipe') }}?tipe=${encodeURIComponent(tipe)}`;
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success && data.parts) {
                let html = '<option value="">- Pilih Part -</option>';
                data.parts.forEach(part => {
                    html += `<option value="${part.id}">${part.nomor_part} - ${part.nama_part}</option>`;
                });
                partSelect.innerHTML = html;
            } else {
                partSelect.innerHTML = '<option value="">- Tidak ada part untuk tipe ini -</option>';
            }
        } catch (error) {
            console.error('Error loading parts:', error);
            partSelect.innerHTML = '<option value="">- Error memuat data -</option>';
        } finally {
            partSelect.disabled = false;
        }
    }

    // Update UI berdasarkan tipe planning
    function updateUIByTipe() {
        const tipe = tipePlanningSelect?.value || '';
        
        // Pastikan semua elemen ada sebelum mengakses propertinya
        if (!planningRunDiv || !planningRunSelect || !mesinDiv || !mesinSelect || !mejaDiv || !mejaSelect || !kategoriInfo || !tanggalSupplyInput) {
            console.error('Some required elements are missing');
            return;
        }
        
        if (tipe === 'assy') {
            // ASSY: tampilkan planning run dan meja dropdown, sembunyikan part dan mesin
            planningRunDiv.style.display = 'block';
            planningRunSelect.required = true;
            if (partDiv) {
                partDiv.style.display = 'none';
            }
            if (partSelect) {
                partSelect.required = false;
                partSelect.value = '';
            }
            mesinDiv.style.display = 'none';
            mesinSelect.disabled = true;
            mesinSelect.value = '';
            mejaDiv.style.display = 'block';
            mejaSelect.disabled = false; // Akan di-enable setelah planning dipilih
            mejaSelect.value = '';
            kategoriInfo.innerHTML = '<p class="text-sm text-blue-800"><strong>ASSY:</strong> Hanya bisa supply subpart</p>';
            
            // Load planning runs jika tanggal sudah ada
            if (tanggalSupplyInput.value) {
                loadPlanningRuns();
            }
        } else if (tipe === 'inject') {
            // INJECT: tampilkan planning run dan mesin dropdown, sembunyikan part dan meja
            planningRunDiv.style.display = 'block';
            planningRunSelect.required = true;
            if (partDiv) {
                partDiv.style.display = 'none';
            }
            if (partSelect) {
                partSelect.required = false;
                partSelect.value = '';
            }
            mesinDiv.style.display = 'block';
            mesinSelect.disabled = false; // Akan di-enable setelah planning dipilih
            mesinSelect.value = '';
            mejaDiv.style.display = 'none';
            mejaSelect.disabled = true;
            mejaSelect.value = '';
            kategoriInfo.innerHTML = '<p class="text-sm text-blue-800"><strong>INJECT:</strong> Bisa supply material/masterbatch (hasil mixing) + subpart, atau subpart saja</p>';
            
            // Load planning runs jika tanggal sudah ada
            if (tanggalSupplyInput.value) {
                loadPlanningRuns();
            }
        } else {
            // Belum pilih tipe
            planningRunDiv.style.display = 'none';
            if (partDiv) {
                partDiv.style.display = 'none';
            }
            mesinDiv.style.display = 'none';
            mesinSelect.disabled = true;
            mesinSelect.value = '';
            mejaDiv.style.display = 'none';
            mejaSelect.disabled = true;
            mejaSelect.value = '';
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
            }
        });
    }

    // Cek apakah sudah ada material atau subpart di form (untuk validasi masterbatch)
    function hasMaterialOrSubpart() {
        const rows = Array.from(detailBody.querySelectorAll('tr'));
        return rows.some(tr => {
            const select = tr.querySelector('[data-row-receiving]');
            if (!select || !select.value) return false;
            const option = select.options[select.selectedIndex];
            const kategori = option?.dataset.kategori || '';
            return kategori === 'material' || kategori === 'subpart';
        });
    }

    // Cek apakah nomor_bahan_baku ada di planning requirements
    function isInPlanningRequirements(nomorBahanBaku, kategori) {
        const tipe = tipePlanningSelect.value;
        if ((tipe !== 'inject' && tipe !== 'assy') || !planningRunSelect.value) {
            return true; // Jika belum pilih planning, skip validasi
        }

        if (kategori === 'material') {
            // Material hanya untuk INJECT
            return tipe === 'inject' && planningRequirements.materials.some(m => m.nomor_bahan_baku === nomorBahanBaku);
        } else if (kategori === 'subpart') {
            // Subpart untuk INJECT dan ASSY
            return planningRequirements.subparts.some(s => s.nomor_bahan_baku === nomorBahanBaku);
        } else if (kategori === 'masterbatch') {
            // Masterbatch hanya untuk INJECT
            return tipe === 'inject' && true; // Masterbatch selalu OK jika ada material (akan divalidasi di qty)
        }
        return false;
    }

    // Cek qty vs planning requirements
    function validateQty(row, nomorBahanBaku, kategori, qty, stdPacking) {
        const tipe = tipePlanningSelect.value;
        if ((tipe !== 'inject' && tipe !== 'assy') || !planningRunSelect.value) {
            return { valid: true, message: '' };
        }

        let planningQty = 0;
        if (kategori === 'material') {
            const material = planningRequirements.materials.find(m => m.nomor_bahan_baku === nomorBahanBaku);
            if (material) {
                planningQty = parseFloat(material.qty_total) || 0;
            } else {
                return { valid: true, message: '' }; // Tidak ada di planning, skip validasi
            }
        } else if (kategori === 'subpart') {
            const subpart = planningRequirements.subparts.find(s => s.nomor_bahan_baku === nomorBahanBaku);
            if (subpart) {
                planningQty = parseFloat(subpart.qty_total) || 0;
            } else {
                return { valid: true, message: '' }; // Tidak ada di planning, skip validasi
            }
        } else if (kategori === 'masterbatch') {
            // Masterbatch: 3% dari material yang dipilih
            // Cari material yang dipilih di form untuk validasi
            const materialRows = Array.from(detailBody.querySelectorAll('tr')).filter(tr => {
                const sel = tr.querySelector('[data-row-receiving]');
                if (!sel || !sel.value) return false;
                const opt = sel.options[sel.selectedIndex];
                return opt && opt.dataset.kategori === 'material';
            });
            
            if (materialRows.length === 0) {
                // Belum ada material, skip validasi untuk sekarang
                return { valid: true, message: '' };
            }
            
            // Ambil material pertama dan cari di planning requirements
            const materialSelect = materialRows[0].querySelector('[data-row-receiving]');
            const materialOption = materialSelect.options[materialSelect.selectedIndex];
            const materialNomorBahanBaku = materialOption.dataset.nomor || '';
            
            const material = planningRequirements.materials.find(m => m.nomor_bahan_baku === materialNomorBahanBaku);
            if (!material) {
                // Material tidak ada di planning, skip validasi
                return { valid: true, message: '' };
            }
            
            // Planning qty untuk masterbatch = 3% dari material planning qty
            const materialPlanningQty = parseFloat(material.qty_total) || 0;
            planningQty = materialPlanningQty * 0.03;
            
            // Validasi untuk INJECT
            if (tipe === 'inject') {
                // 1. Qty tidak boleh kurang dari planning
                if (qty < planningQty) {
                    return { 
                        valid: false, 
                        message: `Qty masterbatch (${qty}) kurang dari planning requirement (${planningQty.toFixed(3)} = 3% dari material ${materialPlanningQty}). Minimal harus ${planningQty.toFixed(3)}.` 
                    };
                }

                // 2. Qty tidak boleh lebih dari planning
                if (qty > planningQty) {
                    // 3. Apabila lebih, lihat std_packing masterbatch
                    const materialStdPacking = parseFloat(materialOption.dataset.stdPacking || 0);
                    const masterbatchStdPacking = materialStdPacking * 0.03; // 3% dari material std_packing
                    const scanCount = parseInt(row.querySelector('[data-field="scan_count"]')?.value || 1);
                    
                    // 4. Jika std_packing > planning, boleh tapi hanya 1 kali scan
                    if (masterbatchStdPacking > planningQty) {
                        // Hanya boleh jika scan_count = 1 dan qty = masterbatchStdPacking
                        if (scanCount === 1 && qty === masterbatchStdPacking) {
                            return { 
                                valid: true, 
                                message: `Peringatan: Qty masterbatch (${qty}) melebihi planning (${planningQty.toFixed(3)}), tapi std_packing (${masterbatchStdPacking.toFixed(3)}) lebih besar, jadi diperbolehkan dengan 1 kali scan.` 
                            };
                        } else {
                            return { 
                                valid: false, 
                                message: `Qty masterbatch (${qty}) melebihi planning (${planningQty.toFixed(3)}). Jika std_packing (${masterbatchStdPacking.toFixed(3)}) lebih besar dari planning, hanya boleh scan 1 kali dengan qty = std_packing.` 
                            };
                        }
                    } else {
                        // std_packing <= planning, tidak boleh lebih dari planning
                        return { 
                            valid: false, 
                            message: `Qty masterbatch (${qty}) melebihi planning requirement (${planningQty.toFixed(3)}). Tidak boleh melebihi planning.` 
                        };
                    }
                }

                // Qty sama dengan planning, valid
                return { valid: true, message: '' };
            }
            
            // Validasi untuk ASSY masterbatch (tetap menggunakan logika lama jika diperlukan)
            // Validasi: qty tidak boleh kurang dari planning
            if (qty < planningQty) {
                return { 
                    valid: false, 
                    message: `Qty masterbatch (${qty}) kurang dari planning requirement (${planningQty.toFixed(3)} = 3% dari material ${materialPlanningQty}). Minimal harus ${planningQty.toFixed(3)}.` 
                };
            }
            
            // Validasi: qty tidak boleh melebihi std_packing masterbatch
            const materialStdPacking = parseFloat(materialOption.dataset.stdPacking || 0);
            const masterbatchStdPacking = materialStdPacking * 0.03; // 3% dari material std_packing
            
            if (qty > masterbatchStdPacking) {
                return { 
                    valid: false, 
                    message: `Qty masterbatch (${qty}) melebihi std_packing (${masterbatchStdPacking.toFixed(3)}). Tidak boleh melebihi std_packing.` 
                };
            }
            
            // Validasi: qty bisa lebih dari planning, tapi dengan exception
            if (qty > planningQty) {
                const scanCount = parseInt(row.querySelector('[data-field="scan_count"]')?.value || 1);
                
                if (masterbatchStdPacking > planningQty) {
                    return { valid: true, message: `Peringatan: Qty masterbatch (${qty}) melebihi planning (${planningQty.toFixed(3)}), tapi std_packing (${masterbatchStdPacking.toFixed(3)}) lebih besar, jadi diperbolehkan.` };
                } else if (masterbatchStdPacking === planningQty) {
                    if (scanCount > 1) {
                        return { 
                            valid: false, 
                            message: `Qty masterbatch (${qty}) melebihi planning (${planningQty.toFixed(3)}). Tidak boleh scan berkali-kali jika std_packing (${masterbatchStdPacking.toFixed(3)}) sama dengan planning requirement.` 
                        };
                    } else {
                        return { valid: true, message: `Peringatan: Qty masterbatch (${qty}) melebihi planning requirement (${planningQty.toFixed(3)}).` };
                    }
                } else {
                    if (scanCount > 1) {
                        return { 
                            valid: false, 
                            message: `Qty masterbatch (${qty}) melebihi planning (${planningQty.toFixed(3)}). Tidak boleh scan berkali-kali jika std_packing (${masterbatchStdPacking.toFixed(3)}) <= planning requirement.` 
                        };
                    } else {
                        return { valid: true, message: `Peringatan: Qty masterbatch (${qty}) melebihi planning requirement (${planningQty.toFixed(3)}).` };
                    }
                }
            }
            
            return { valid: true, message: '' };
        }

        if (planningQty === 0) {
            return { valid: true, message: '' }; // Tidak ada planning qty, skip
        }

        // Validasi untuk INJECT
        if (tipe === 'inject') {
            // 1. Qty tidak boleh kurang dari planning
            if (qty < planningQty) {
                return { 
                    valid: false, 
                    message: `Qty (${qty}) kurang dari planning requirement (${planningQty}). Minimal harus ${planningQty}.` 
                };
            }

            // 2. Qty tidak boleh lebih dari planning
            if (qty > planningQty) {
                // 3. Apabila lebih, lihat std_packing
                const scanCount = parseInt(row.querySelector('[data-field="scan_count"]')?.value || 1);
                
                // 4. Jika std_packing > planning, boleh tapi hanya 1 kali scan
                if (stdPacking > planningQty) {
                    // Hanya boleh jika scan_count = 1 dan qty = std_packing
                    if (scanCount === 1 && qty === stdPacking) {
                        return { 
                            valid: true, 
                            message: `Peringatan: Qty (${qty}) melebihi planning (${planningQty}), tapi std_packing (${stdPacking}) lebih besar, jadi diperbolehkan dengan 1 kali scan.` 
                        };
                    } else {
                        return { 
                            valid: false, 
                            message: `Qty (${qty}) melebihi planning (${planningQty}). Jika std_packing (${stdPacking}) lebih besar dari planning, hanya boleh scan 1 kali dengan qty = std_packing.` 
                        };
                    }
                } else {
                    // std_packing <= planning, tidak boleh lebih dari planning
                    return { 
                        valid: false, 
                        message: `Qty (${qty}) melebihi planning requirement (${planningQty}). Tidak boleh melebihi planning.` 
                    };
                }
            }

            // Qty sama dengan planning, valid
            return { valid: true, message: '' };
        }

        // Validasi untuk ASSY (tetap menggunakan logika lama)
        // Validasi: qty tidak boleh kurang dari planning
        if (qty < planningQty) {
            return { 
                valid: false, 
                message: `Qty (${qty}) kurang dari planning requirement (${planningQty}). Minimal harus ${planningQty}.` 
            };
        }

        // Validasi: qty tidak boleh melebihi std_packing
        if (qty > stdPacking) {
            return { 
                valid: false, 
                message: `Qty (${qty}) melebihi std_packing (${stdPacking}). Tidak boleh melebihi std_packing.` 
            };
        }

        // Validasi: qty bisa lebih dari planning, tapi dengan exception
        if (qty > planningQty) {
            const scanCount = parseInt(row.querySelector('[data-field="scan_count"]')?.value || 1);
            
            // Exception: Jika std_packing > planning qty, gapapa (misal planning 50, std_packing 150, itu OK)
            // Tapi qty tetap tidak boleh melebihi std_packing (sudah dicek di atas)
            if (stdPacking > planningQty) {
                return { valid: true, message: `Peringatan: Qty (${qty}) melebihi planning (${planningQty}), tapi std_packing (${stdPacking}) lebih besar, jadi diperbolehkan.` };
            } else if (stdPacking === planningQty) {
                // Jika std_packing sama dengan planning dan scan > 1, itu tidak boleh
                // (misal std_packing 50, planning 50, scan 2x jadi 100)
                if (scanCount > 1) {
                    return { 
                        valid: false, 
                        message: `Qty (${qty}) melebihi planning (${planningQty}). Tidak boleh scan berkali-kali jika std_packing (${stdPacking}) sama dengan planning requirement.` 
                    };
                } else {
                    return { valid: true, message: `Peringatan: Qty (${qty}) melebihi planning requirement (${planningQty}).` };
                }
            } else {
                // stdPacking < planningQty
                // Jika scan berkali-kali sehingga qty melebihi planning, itu tidak boleh
                if (scanCount > 1) {
                    return { 
                        valid: false, 
                        message: `Qty (${qty}) melebihi planning (${planningQty}). Tidak boleh scan berkali-kali jika std_packing (${stdPacking}) <= planning requirement.` 
                    };
                } else {
                    return { valid: true, message: `Peringatan: Qty (${qty}) melebihi planning requirement (${planningQty}).` };
                }
            }
        }

        return { valid: true, message: '' };
    }

    // Scan QR code (simulasi - bisa diintegrasikan dengan scanner)
    function scanQRCode() {
        const qrCode = prompt('Scan QR Code atau masukkan QR Code:');
        if (!qrCode || !qrCode.trim()) return;

        const tipe = tipePlanningSelect.value;

        // Untuk INJECT: prioritas material dulu, baru masterbatch, lalu subpart
        let found = null;
        if (tipe === 'inject') {
            // Cari semua yang cocok dengan QR code
            const matches = receivingDetails.filter(d => 
                d.qrcode.toLowerCase() === qrCode.trim().toLowerCase()
            );
            
            if (matches.length === 0) {
                alert('QR Code tidak ditemukan di receiving detail');
                return;
            }
            
            // Prioritaskan material dulu, baru masterbatch, lalu subpart
            found = matches.find(d => d.kategori === 'material') ||
                   matches.find(d => d.kategori === 'masterbatch') ||
                   matches.find(d => d.kategori === 'subpart') ||
                   matches[0];
        } else {
            // ASSY: hanya cari subpart
            found = receivingDetails.find(d => 
                d.qrcode.toLowerCase() === qrCode.trim().toLowerCase() &&
                d.kategori === 'subpart'
            );
            
            if (!found) {
                const anyMatch = receivingDetails.find(d => 
                    d.qrcode.toLowerCase() === qrCode.trim().toLowerCase()
                );
                if (anyMatch) {
                    alert('ASSY hanya bisa supply subpart. QR Code ini bukan subpart.');
                } else {
                    alert('QR Code tidak ditemukan di receiving detail');
                }
                return;
            }
        }

        // Validasi untuk INJECT: harus ada material atau subpart sebelum masterbatch
        if (tipe === 'inject' && found.kategori === 'masterbatch' && !hasMaterialOrSubpart()) {
            alert('INJECT: Harus input material atau subpart terlebih dahulu sebelum masterbatch.');
            return;
        }

        // Validasi untuk INJECT dan ASSY: item harus ada di planning requirements
        if ((tipe === 'inject' || tipe === 'assy') && planningRunSelect.value) {
            if (!isInPlanningRequirements(found.nomor_bahan_baku, found.kategori)) {
                alert(`Item ini (${found.nomor_bahan_baku} - ${found.nama_bahan_baku}) tidak ada di planning requirement yang dipilih.`);
                return;
            }
        }

        // Cek apakah sudah ada di detail
        const existingRow = Array.from(detailBody.querySelectorAll('tr')).find(tr => {
            const select = tr.querySelector('select[data-field="receiving_detail_id"]');
            return select && select.value == found.id;
        });

        if (existingRow) {
            // Jika sudah ada, akumulasikan scan count
            const scanCountInput = existingRow.querySelector('[data-field="scan_count"]');
            if (scanCountInput) {
                const currentScanCount = parseInt(scanCountInput.value) || 1;
                scanCountInput.value = currentScanCount + 1;
                calculateQtyFromScan(existingRow);
            }
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
                <div class="flex items-center gap-2">
                    <input type="number" step="1" min="1" value="1"
                           class="w-20 border border-gray-300 rounded-lg px-2 py-2 text-sm text-center" 
                           data-field="scan_count" placeholder="1" required />
                    <span class="text-xs text-gray-500">x</span>
                    <input type="number" step="0.001" min="0" 
                           class="flex-1 border border-gray-300 rounded-lg px-2 py-2 text-sm bg-gray-50" 
                           data-field="qty" placeholder="25" readonly />
                    <span class="text-xs text-gray-500" data-field="qty-label">kg</span>
                </div>
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
        if (initial.receiving_detail_id) {
            tr.querySelector('[data-field="receiving_detail_id"]').value = initial.receiving_detail_id;
            updateRowFromReceiving(tr);
        }
        tr.querySelector('[data-field="nomor_bahan_baku"]').value = initial.nomor_bahan_baku || '';
        tr.querySelector('[data-field="lot_number"]').value = initial.lot_number || '';

        // Handler ketika select receiving detail berubah
        tr.querySelector('[data-row-receiving]').addEventListener('change', function() {
            const select = this;
            const selectedOption = select.options[select.selectedIndex];
            const kategori = selectedOption?.dataset.kategori || '';
            const nomorBahanBaku = selectedOption?.dataset.nomor || '';
            
            // Validasi untuk INJECT: masterbatch harus ada material/subpart dulu
            if (tipePlanningSelect.value === 'inject' && kategori === 'masterbatch' && !hasMaterialOrSubpart()) {
                alert('INJECT: Harus input material atau subpart terlebih dahulu sebelum masterbatch.');
                select.value = '';
                updateRowFromReceiving(tr);
                return;
            }
            
            // Validasi untuk INJECT: item harus ada di planning requirements
            if (tipePlanningSelect.value === 'inject' && planningRunSelect.value && nomorBahanBaku) {
                if (!isInPlanningRequirements(nomorBahanBaku, kategori)) {
                    alert(`Item ini (${nomorBahanBaku} - ${selectedOption?.dataset.nama || '-'}) tidak ada di planning requirement yang dipilih.`);
                    select.value = '';
                    updateRowFromReceiving(tr);
                    return;
                }
            }
            
            updateRowFromReceiving(tr);
        });
        
        // Handler ketika scan count berubah
        const scanCountInput = tr.querySelector('[data-field="scan_count"]');
        scanCountInput.addEventListener('change', function() {
            calculateQtyFromScan(tr);
        });
        scanCountInput.addEventListener('input', function() {
            calculateQtyFromScan(tr);
        });

        detailBody.appendChild(tr);
    }
    
    // Hitung qty berdasarkan scan count
    function calculateQtyFromScan(row) {
        const select = row.querySelector('[data-row-receiving]');
        if (!select) return;
        
        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;
        
        const scanCountInput = row.querySelector('[data-field="scan_count"]');
        const qtyInput = row.querySelector('[data-field="qty"]');
        const qtyLabel = row.querySelector('[data-field="qty-label"]');
        
        if (!scanCountInput || !qtyInput || !scanCountInput.value) {
            if (qtyInput) qtyInput.value = '';
            return;
        }
        
        const kategori = selectedOption.dataset.kategori || '';
        const stdPacking = parseFloat(selectedOption.dataset.stdPacking || 0);
        const scanCount = parseInt(scanCountInput.value) || 1;
        
        let qty = 0;
        
        if (kategori === 'masterbatch') {
            // Masterbatch: 3% dari std_packing material yang dipilih
            // Cari material yang dipilih di form
            const materialRows = Array.from(detailBody.querySelectorAll('tr')).filter(tr => {
                const sel = tr.querySelector('[data-row-receiving]');
                if (!sel || !sel.value) return false;
                const opt = sel.options[sel.selectedIndex];
                return opt && opt.dataset.kategori === 'material';
            });
            
            if (materialRows.length > 0) {
                // Ambil material pertama yang dipilih
                const materialSelect = materialRows[0].querySelector('[data-row-receiving]');
                const materialOption = materialSelect.options[materialSelect.selectedIndex];
                const materialStdPacking = parseFloat(materialOption.dataset.stdPacking || 0);
                
                // Masterbatch = 3% dari material * scan count
                qty = (materialStdPacking * 0.03) * scanCount;
            } else {
                // Jika belum ada material, gunakan std_packing masterbatch sendiri
                qty = stdPacking * scanCount;
            }
        } else {
            // Material dan Subpart: scan_count * std_packing
            qty = stdPacking * scanCount;
        }
        
        qtyInput.value = qty.toFixed(3);
        
        // Update label UOM jika ada
        const uom = selectedOption.dataset.uom || 'kg';
        if (qtyLabel) {
            qtyLabel.textContent = uom;
        }
        
        // Validasi qty vs planning requirements hanya akan dilakukan saat submit
        // Bukan di sini, jadi user bisa bebas input/scan dulu
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
            const stdPacking = parseFloat(selectedOption.dataset.stdPacking || 0);
            
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
            
            // Reset scan count ke 1 dan hitung qty
            const scanCountInput = row.querySelector('[data-field="scan_count"]');
            if (scanCountInput) {
                scanCountInput.value = 1;
                calculateQtyFromScan(row);
            }
            
            // Track material std_packing untuk masterbatch
            if (kategori === 'material') {
                selectedMaterialStdPacking = stdPacking;
                // Update semua masterbatch rows
                updateAllMasterbatchRows();
            }
            
            // Validasi qty vs planning requirements hanya akan dilakukan saat submit
            // Bukan di sini, jadi user bisa bebas input/scan dulu
        } else {
            row.querySelector('.bahan-nama').textContent = '-';
            row.querySelector('.kategori-badge').textContent = '-';
            row.querySelector('.kategori-badge').className = 'text-xs px-2 py-1 rounded kategori-badge';
            row.querySelector('[data-field="nomor_bahan_baku"]').value = '';
            row.querySelector('[data-field="lot_number"]').value = '';
            row.querySelector('[data-field="qty"]').value = '';
        }
    }
    
    // Update semua row masterbatch dengan material yang baru dipilih
    function updateAllMasterbatchRows() {
        Array.from(detailBody.querySelectorAll('tr')).forEach(row => {
            const select = row.querySelector('[data-row-receiving]');
            if (!select || !select.value) return;
            
            const selectedOption = select.options[select.selectedIndex];
            const kategori = selectedOption.dataset.kategori || '';
            
            if (kategori === 'masterbatch') {
                calculateQtyFromScan(row);
            }
        });
    }

    function collectDetails() {
        const rows = Array.from(detailBody.querySelectorAll('tr'));
        return rows.map((tr) => {
            const receivingDetailId = tr.querySelector('[data-field="receiving_detail_id"]').value;
            if (!receivingDetailId) return null;
            
            const qty = tr.querySelector('[data-field="qty"]').value;
            if (!qty || parseFloat(qty) <= 0) return null;
            
            return {
                receiving_detail_id: receivingDetailId,
                qty: qty,
            };
        }).filter(item => item !== null);
    }

    // Load kebutuhan material dari planning run
    // planningRunSelect, planningRequirementsDiv, planningInfo, materialsList, subpartsList sudah dideklarasikan di atas
    
    // Simpan planning requirements untuk validasi
    let planningRequirements = {
        materials: [],
        masterbatch: [],
        subparts: []
    };

    async function loadPlanningRequirements(planningRunId) {
        if (!planningRunId) {
            planningRequirementsDiv.classList.add('hidden');
            return;
        }

        console.log('Loading planning requirements for ID:', planningRunId);
        
        // Show loading state
        if (planningRequirementsDiv) {
            planningRequirementsDiv.classList.remove('hidden');
            planningInfo.innerHTML = '<p class="text-sm text-gray-500">Memuat data...</p>';
            materialsList.innerHTML = '<p class="text-sm text-gray-500">Memuat...</p>';
            subpartsList.innerHTML = '<p class="text-sm text-gray-500">Memuat...</p>';
        }

        try {
            const url = `{{ route('bahanbaku.supply.api.planningRequirements') }}?planning_run_id=${encodeURIComponent(planningRunId)}`;
            console.log('Fetching URL:', url);
            
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            console.log('Response status:', response.status);
            const data = await response.json();
            console.log('Response data:', data);

            if (!response.ok || !data.success) {
                console.error('Error loading planning requirements:', data.message);
                planningRequirementsDiv.classList.add('hidden');
                return;
            }

            const req = data.data;
            const tipe = tipePlanningSelect.value;
            
            // Simpan planning requirements untuk validasi
            planningRequirements = {
                materials: req.materials || [],
                masterbatch: [], // Masterbatch dianggap 3% dari material, tidak ada di planning requirements
                subparts: req.subparts || []
            };
            
            // Auto-populate mesin untuk INJECT
            if (tipe === 'inject' && req.planning_run.mesin_id && mesinSelect) {
                mesinSelect.value = req.planning_run.mesin_id;
                mesinSelect.disabled = false; // Enable setelah diisi
                console.log('Auto-populated mesin_id:', req.planning_run.mesin_id);
            } else if (mesinSelect) {
                mesinSelect.value = '';
                mesinSelect.disabled = true;
            }
            
            // Auto-populate meja untuk ASSY
            if (tipe === 'assy' && req.planning_run.meja && mejaSelect) {
                const mejaValue = req.planning_run.meja;
                // Pastikan meja bukan '-' atau empty
                if (mejaValue && mejaValue !== '-' && mejaValue.trim() !== '') {
                    console.log('Auto-populating meja from planning requirements:', mejaValue);
                    
                    // Pastikan option meja ada di dropdown, jika tidak ada, tambahkan
                    let optionExists = false;
                    for (let i = 0; i < mejaSelect.options.length; i++) {
                        if (mejaSelect.options[i].value === mejaValue) {
                            optionExists = true;
                            break;
                        }
                    }
                    
                    if (!optionExists) {
                        // Tambahkan option baru jika belum ada
                        const newOption = document.createElement('option');
                        newOption.value = mejaValue;
                        newOption.textContent = mejaValue;
                        mejaSelect.appendChild(newOption);
                        console.log('Added new meja option:', mejaValue);
                    }
                    
                    mejaSelect.value = mejaValue;
                    mejaSelect.disabled = false; // Enable setelah diisi
                    console.log('Meja value after setting:', mejaSelect.value);
                } else {
                    console.log('Meja value is empty or invalid:', mejaValue);
                    mejaSelect.value = '';
                    mejaSelect.disabled = true;
                }
            } else if (mejaSelect) {
                console.log('Meja not populated - tipe:', tipe, 'meja:', req.planning_run?.meja);
                mejaSelect.value = '';
                mejaSelect.disabled = true;
            }
            
            // Tampilkan info planning
            console.log('Planning run data:', req.planning_run);
            console.log('Planning requirements:', planningRequirements);
            const lotProduksi = req.planning_run.lot_produksi && req.planning_run.lot_produksi.trim() !== '' 
                ? req.planning_run.lot_produksi 
                : '-';
            
            const mesinOrMejaLabel = tipe === 'assy' ? 'Meja' : 'Mesin';
            const mesinOrMejaValue = req.planning_run.mesin || '-';
            
            planningInfo.innerHTML = `
                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm">
                    <div><span class="font-semibold">Lot Produksi:</span> ${lotProduksi}</div>
                    <div><span class="font-semibold">${mesinOrMejaLabel}:</span> ${mesinOrMejaValue}</div>
                    <div><span class="font-semibold">Part:</span> ${req.planning_run.part || '-'}</div>
                    <div><span class="font-semibold">Qty Target:</span> ${req.planning_run.qty_target || 0}</div>
                </div>
            `;

            // Tampilkan materials (hanya untuk INJECT, ASSY tidak menggunakan material)
            if (tipe === 'assy') {
                materialsList.innerHTML = '<p class="text-sm text-gray-500">ASSY: Tidak menggunakan material/masterbatch</p>';
            } else if (req.materials && req.materials.length > 0) {
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

    // Event listener untuk planning run select
    if (planningRunSelect) {
        planningRunSelect.addEventListener('change', function() {
            const planningRunId = this.value;
            console.log('Planning Run ID selected:', planningRunId);
            
            // Reset mesin dan meja
            if (mesinSelect) {
                mesinSelect.value = '';
                mesinSelect.disabled = true;
            }
            if (mejaSelect) {
                mejaSelect.value = '';
                mejaSelect.disabled = true;
            }
            
            if (planningRunId) {
                // Ambil data dari option yang dipilih untuk auto-populate mesin/meja
                const selectedOption = this.options[this.selectedIndex];
                const tipe = tipePlanningSelect.value;
                
                if (tipe === 'inject' && selectedOption?.dataset?.mesinId && mesinSelect) {
                    mesinSelect.value = selectedOption.dataset.mesinId;
                    mesinSelect.disabled = false;
                    console.log('Auto-populated mesin_id from option:', selectedOption.dataset.mesinId);
                } else if (tipe === 'assy' && selectedOption?.dataset?.meja && mejaSelect) {
                    const mejaValue = selectedOption.dataset.meja;
                    if (mejaValue && mejaValue !== '-') {
                        console.log('Auto-populated meja from option:', mejaValue);
                        
                        // Pastikan option meja ada di dropdown, jika tidak ada, tambahkan
                        let optionExists = false;
                        for (let i = 0; i < mejaSelect.options.length; i++) {
                            if (mejaSelect.options[i].value === mejaValue) {
                                optionExists = true;
                                break;
                            }
                        }
                        
                        if (!optionExists) {
                            // Tambahkan option baru jika belum ada
                            const newOption = document.createElement('option');
                            newOption.value = mejaValue;
                            newOption.textContent = mejaValue;
                            mejaSelect.appendChild(newOption);
                            console.log('Added new meja option from dataset:', mejaValue);
                        }
                        
                        mejaSelect.value = mejaValue;
                        mejaSelect.disabled = false;
                    } else {
                        console.log('Meja value from option is invalid:', mejaValue);
                    }
                }
                
                loadPlanningRequirements(planningRunId);
            } else {
                if (planningRequirementsDiv) {
                    planningRequirementsDiv.classList.add('hidden');
                }
            }
        });
    } else {
        console.error('planningRunSelect element not found!');
    }

    // Event listener untuk part select (ASSY)
    if (partSelect) {
        partSelect.addEventListener('change', function() {
            const partId = this.value;
            if (partId && tipePlanningSelect.value === 'assy') {
                loadPartSubparts(partId);
            } else {
                if (planningRequirementsDiv && tipePlanningSelect.value === 'assy') {
                    planningRequirementsDiv.classList.add('hidden');
                }
            }
        });
    }

    // Event listeners
    tipePlanningSelect.addEventListener('change', function() {
        updateUIByTipe();
        if (planningRequirementsDiv) {
            planningRequirementsDiv.classList.add('hidden');
        }
        // Reload planning runs jika tanggal sudah ada
        if (this.value === 'inject' && tanggalSupplyInput.value) {
            loadPlanningRuns();
        } else if (this.value === 'assy' && partSelect && partSelect.value) {
            // Load subparts jika part sudah dipilih
            loadPartSubparts(partSelect.value);
        }
    });
    
    tanggalSupplyInput.addEventListener('change', function() {
        // Load planning runs saat tanggal berubah (hanya untuk INJECT)
        if (tipePlanningSelect.value === 'inject' && this.value) {
            loadPlanningRuns();
        }
        // Reset planning run selection
        planningRunSelect.value = '';
        if (planningRequirementsDiv) {
            planningRequirementsDiv.classList.add('hidden');
        }
    });
    btnScanQR.addEventListener('click', scanQRCode);
    btnAddDetail.addEventListener('click', () => addRow());
    
    detailBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('btnRemoveRow')) {
            e.target.closest('tr')?.remove();
        }
    });


    // Default values
    if (form) {
        const tgl = form.querySelector('[name="tanggal_supply"]');
        if (tgl && !tgl.value) {
            tgl.value = new Date().toISOString().slice(0, 10);
        }
    }

    // Initialize
    updateUIByTipe();
    // Baris detail akan muncul setelah scan QR code, tidak otomatis saat halaman dimuat
    
    // Load planning requirements jika planning run sudah dipilih saat halaman dimuat
    if (planningRunSelect && planningRunSelect.value) {
        console.log('Initial planning run value:', planningRunSelect.value);
        loadPlanningRequirements(planningRunSelect.value);
    }

    // Form submit handler
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validasi: untuk INJECT dan ASSY, pastikan planning run dipilih dan tanggal match
            const tipe = tipePlanningSelect.value;
            if (tipe === 'inject' || tipe === 'assy') {
                if (!planningRunSelect.value) {
                    alert(`Planning Run harus dipilih untuk ${tipe.toUpperCase()}`);
                    return;
                }
                
                // Validasi tanggal supply harus match dengan tanggal planning
                const selectedRun = planningRunSelect.options[planningRunSelect.selectedIndex];
                if (selectedRun && selectedRun.dataset.tanggal) {
                    const tanggalPlanning = selectedRun.dataset.tanggal;
                    const tanggalSupply = tanggalSupplyInput.value;
                    if (tanggalPlanning !== tanggalSupply) {
                        alert(`Tanggal supply (${tanggalSupply}) harus sesuai dengan tanggal planning (${tanggalPlanning})`);
                        return;
                    }
                }
                
                // Untuk INJECT, pastikan mesin sudah terisi (auto-populate dari planning)
                if (tipe === 'inject' && !mesinSelect.value) {
                    alert('Mesin harus terisi (otomatis dari planning run)');
                    return;
                }
                
                // Untuk ASSY, pastikan meja sudah terisi (auto-populate dari planning)
                if (tipe === 'assy' && !mejaSelect.value) {
                    alert('Meja harus terisi (otomatis dari planning run)');
                    return;
                }
            }

            // Validasi final qty vs planning requirements (untuk INJECT dan ASSY)
            if ((tipe === 'inject' || tipe === 'assy') && planningRunSelect.value) {
                const rows = Array.from(detailBody.querySelectorAll('tr'));
                let validationErrors = [];
                let validationWarnings = [];
                
                for (const row of rows) {
                    const select = row.querySelector('[data-row-receiving]');
                    if (!select || !select.value) continue;
                    
                    const option = select.options[select.selectedIndex];
                    const nomorBahanBaku = option?.dataset.nomor || '';
                    const kategori = option?.dataset.kategori || '';
                    const qty = parseFloat(row.querySelector('[data-field="qty"]')?.value || 0);
                    const stdPacking = parseFloat(option?.dataset.stdPacking || 0);
                    
                    if (!nomorBahanBaku || qty <= 0) continue;
                    
                    const validation = validateQty(row, nomorBahanBaku, kategori, qty, stdPacking);
                    
                    if (!validation.valid) {
                        validationErrors.push(`${nomorBahanBaku}: ${validation.message}`);
                    } else if (validation.message && validation.message.includes('Peringatan')) {
                        validationWarnings.push(`${nomorBahanBaku}: ${validation.message}`);
                    }
                }
                
                if (validationErrors.length > 0) {
                    alert('Terdapat kesalahan validasi:\n\n' + validationErrors.join('\n'));
                    return;
                }
                
                if (validationWarnings.length > 0) {
                    const shouldContinue = confirm('Peringatan:\n\n' + validationWarnings.join('\n') + '\n\nLanjutkan submit?');
                    if (!shouldContinue) {
                        return;
                    }
                }
            }

            const details = collectDetails();
            console.log('Collected details:', details);
            
            if (details.length === 0) {
                alert('Minimal harus ada 1 detail supply');
                return;
            }

            // Pastikan planning_run_id tidak null untuk INJECT dan ASSY
            let planningRunId = planningRunSelect.value || null;
            if ((tipe === 'inject' || tipe === 'assy') && !planningRunId) {
                alert(`Planning Run harus dipilih untuk ${tipe.toUpperCase()}`);
                return;
            }
            
            // Untuk ASSY, ambil part_id dan meja dari planning run yang dipilih
            let partId = null;
            if (tipe === 'assy' && planningRunSelect.value) {
                const selectedOption = planningRunSelect.options[planningRunSelect.selectedIndex];
                partId = selectedOption?.dataset?.partId || null;
                console.log('Part ID from planning run:', partId);
            }
            let meja = tipe === 'assy' ? mejaSelect.value : null;

            const payload = {
                planning_run_id: planningRunId,
                part_id: partId,
                meja: meja, // Untuk ASSY (mesin_id tidak perlu disimpan, sudah ada di planning)
                tujuan: tipe, // Gunakan tipe planning sebagai tujuan
                tanggal_supply: form.querySelector('[name="tanggal_supply"]').value,
                shift_no: form.querySelector('[name="shift_no"]').value || null,
                status: form.querySelector('[name="status"]').value || 'DRAFT',
                catatan: form.querySelector('[name="catatan"]').value || null,
                details: details,
            };

            console.log('Payload to send:', payload);

            try {
                const response = await fetch('{{ route("bahanbaku.supply.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json().catch(async (err) => {
                    // Jika response bukan JSON, coba ambil text
                    const text = await response.text();
                    console.error('Response bukan JSON:', text);
                    return { success: false, message: text || 'Terjadi kesalahan' };
                });
                
                console.log('Response data:', data);
                console.log('Response status:', response.status);
                
                if (!response.ok || !data.success) {
                    const errorMsg = data.message || data.errors || 'Gagal menyimpan data';
                    console.error('Error:', errorMsg);
                    alert(typeof errorMsg === 'string' ? errorMsg : JSON.stringify(errorMsg));
                    return;
                }

                alert(data.message || 'Berhasil menyimpan');
                window.location.href = '{{ route("bahanbaku.supply.index") }}';
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }

    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            window.location.href = '{{ route("bahanbaku.supply.index") }}';
        });
    }
})();
</script>
@endsection

