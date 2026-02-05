@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit Bahan Baku</h2>
        <p class="text-gray-600 mt-1">Perbarui data bahan baku</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="formEditBahanBaku">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Baris 1: Kategori | Supplier | Nomor Bahan Baku -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-600">*</span></label>
                    <select name="kategori" id="kategoriSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="material" {{ $bahanbaku->kategori === 'material' ? 'selected' : '' }}>MATERIAL</option>
                        <option value="masterbatch" {{ $bahanbaku->kategori === 'masterbatch' ? 'selected' : '' }}>MASTERBATCH</option>
                        <option value="subpart" {{ $bahanbaku->kategori === 'subpart' ? 'selected' : '' }}>SUBPART</option>
                        <option value="box" {{ $bahanbaku->kategori === 'box' ? 'selected' : '' }}>BOX</option>
                        <option value="layer" {{ $bahanbaku->kategori === 'layer' ? 'selected' : '' }}>LAYER</option>
                        <option value="polybag" {{ $bahanbaku->kategori === 'polybag' ? 'selected' : '' }}>POLYBAG</option>
                        <option value="rempart" {{ $bahanbaku->kategori === 'rempart' ? 'selected' : '' }}>REMPART</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <select name="supplier_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Supplier -</option>
                        @foreach($suppliers as $s)
                            <option value="{{ $s->id }}" {{ (string) $bahanbaku->supplier_id === (string) $s->id ? 'selected' : '' }}>{{ $s->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Bahan Baku</label>
                    <input type="text" name="nomor_bahan_baku" id="nomorBahanBaku" value="{{ $bahanbaku->nomor_bahan_baku }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 {{ in_array($bahanbaku->kategori, ['box', 'layer', 'polybag']) ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ in_array($bahanbaku->kategori, ['box', 'layer', 'polybag']) ? 'readonly' : '' }} />
                    <p class="text-xs text-gray-500 mt-1">Auto-generate untuk BOX/LAYER/POLYBAG.</p>
                </div>

                <!-- Baris 2: Nama Bahan Baku (Group 1) ATAU Jenis (Group 2) -->
                <!-- Group 1: MATERIAL, MASTERBATCH, SUBPART -->
                <div class="md:col-span-3 field-group field-group-1" id="fieldGroup1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan Baku <span class="text-red-600">*</span></label>
                    <input type="text" name="nama_bahan_baku" id="namaBahanBaku" value="{{ $bahanbaku->nama_bahan_baku }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Jenis field untuk box, layer, polybag, rempart -->
                <div class="md:col-span-3 field-group field-group-jenis" id="fieldGroupJenis" style="display: {{ in_array($bahanbaku->kategori, ['box', 'layer', 'polybag', 'rempart']) ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis <span class="text-red-600">*</span></label>
                    <select name="jenis" id="jenisSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Jenis -</option>
                    </select>
                </div>

                <!-- Std Packing, UOM, Jenis Packing - untuk semua kategori KECUALI BOX -->
                <div class="field-group field-group-packing" id="fieldGroupPacking1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Std Packing</label>
                    <input type="number" step="0.01" min="0" name="std_packing" value="{{ $bahanbaku->detail?->std_packing ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="field-group field-group-packing" id="fieldGroupPacking2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">UOM</label>
                    <select name="uom" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih UOM -</option>
                        <option value="KG" {{ ($bahanbaku->detail?->uom ?? '') === 'KG' ? 'selected' : '' }}>KG</option>
                        <option value="PCS" {{ ($bahanbaku->detail?->uom ?? '') === 'PCS' ? 'selected' : '' }}>PCS</option>
                        <option value="KARUNG" {{ ($bahanbaku->detail?->uom ?? '') === 'KARUNG' ? 'selected' : '' }}>KARUNG</option>
                        <option value="PALLET" {{ ($bahanbaku->detail?->uom ?? '') === 'PALLET' ? 'selected' : '' }}>PALLET</option>
                        <option value="BOX" {{ ($bahanbaku->detail?->uom ?? '') === 'BOX' ? 'selected' : '' }}>BOX</option>
                        <option value="ROLL" {{ ($bahanbaku->detail?->uom ?? '') === 'ROLL' ? 'selected' : '' }}>ROLL</option>
                        <option value="LITER" {{ ($bahanbaku->detail?->uom ?? '') === 'LITER' ? 'selected' : '' }}>LITER</option>
                    </select>
                </div>

                <div class="field-group field-group-packing" id="fieldGroupPacking3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Packing</label>
                    <select name="jenis_packing" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Jenis Packing -</option>
                        <option value="KARUNG" {{ ($bahanbaku->detail?->jenis_packing ?? '') === 'KARUNG' ? 'selected' : '' }}>KARUNG</option>
                        <option value="PALLET" {{ ($bahanbaku->detail?->jenis_packing ?? '') === 'PALLET' ? 'selected' : '' }}>PALLET</option>
                        <option value="BOX" {{ ($bahanbaku->detail?->jenis_packing ?? '') === 'BOX' ? 'selected' : '' }}>BOX</option>
                        <option value="BAG" {{ ($bahanbaku->detail?->jenis_packing ?? '') === 'BAG' ? 'selected' : '' }}>BAG</option>
                        <option value="ROLL" {{ ($bahanbaku->detail?->jenis_packing ?? '') === 'ROLL' ? 'selected' : '' }}>ROLL</option>
                        <option value="PLASTIK" {{ ($bahanbaku->detail?->jenis_packing ?? '') === 'PLASTIK' ? 'selected' : '' }}>PLASTIK</option>
                    </select>
                </div>

                <!-- Group 2: BOX, LAYER, POLYBAG (Panjang, Lebar, Tinggi) -->
                <div class="field-group field-group-2" id="fieldGroup2" style="display: {{ in_array($bahanbaku->kategori, ['box', 'layer', 'polybag']) ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Panjang (cm)</label>
                    <input type="number" step="0.01" min="0" name="panjang" value="{{ $bahanbaku->box?->panjang ?? $bahanbaku->layer?->panjang ?? $bahanbaku->polybag?->panjang ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="field-group field-group-2" id="fieldGroup2" style="display: {{ in_array($bahanbaku->kategori, ['box', 'layer', 'polybag']) ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lebar (cm)</label>
                    <input type="number" step="0.01" min="0" name="lebar" value="{{ $bahanbaku->box?->lebar ?? $bahanbaku->layer?->lebar ?? $bahanbaku->polybag?->lebar ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="field-group field-group-2" id="fieldGroup2" style="display: {{ in_array($bahanbaku->kategori, ['box', 'layer', 'polybag']) ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi (cm)</label>
                    <input type="number" step="0.01" min="0" name="tinggi" value="{{ $bahanbaku->box?->tinggi ?? $bahanbaku->layer?->tinggi ?? $bahanbaku->polybag?->tinggi ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Kode Box untuk box (Baris 5) -->
                <div class="md:col-span-3 field-group field-group-kodebox" id="fieldGroupKodeBox" style="display: {{ $bahanbaku->kategori === 'box' ? 'block' : 'none' }};">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Box</label>
                    <input type="text" name="kode_box" maxlength="50" value="{{ $bahanbaku->box?->kode_box ?? '' }}" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: BOX-001" />
                </div>
                
                <!-- Keterangan (Full Width) -->
                <div class="md:col-span-3 mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tambahan informasi (opsional)...">{{ $bahanbaku->keterangan }}</textarea>
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
    const form = document.getElementById('formEditBahanBaku');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const btnCancel = document.getElementById('btnCancel');
    const namaBahanBaku = document.getElementById('namaBahanBaku');

    // Kategori groups
    const group1 = ['material', 'masterbatch', 'subpart']; // Nama Bahan Baku
    const group2 = ['box', 'layer', 'polybag']; // Panjang, Lebar, Tinggi + Jenis
    const kategoriWithKodeBox = ['box']; // Kode Box hanya untuk box
    
    @php
        $currentJenisPHP = '';
        if ($bahanbaku->kategori == 'box' && $bahanbaku->box) {
            $currentJenisPHP = $bahanbaku->box->jenis;
        } elseif ($bahanbaku->kategori == 'layer' && $bahanbaku->layer) {
            $currentJenisPHP = $bahanbaku->layer->jenis;
        } elseif ($bahanbaku->kategori == 'polybag' && $bahanbaku->polybag) {
            $currentJenisPHP = $bahanbaku->polybag->jenis;
        } elseif ($bahanbaku->kategori == 'rempart' && $bahanbaku->rempart) {
            $currentJenisPHP = $bahanbaku->rempart->jenis;
        } elseif ($bahanbaku->detail) {
            $currentJenisPHP = $bahanbaku->detail->jenis ?? '';
        }
    @endphp

    // Current values from server (Passed safely from PHP)
    const currentJenis = "{{ $currentJenisPHP }}".trim().toLowerCase();
    
    // Jenis options berdasarkan kategori
    const jenisOptions = {
        'box': [
            { value: 'polybox', label: 'POLYBOX' },
            { value: 'impraboard', label: 'IMPRABOARD' },
            { value: 'karton', label: 'KARTON' }
        ],
        'layer': [
            { value: 'ldpe', label: 'LDPE' },
            { value: 'polyfoam_sheet', label: 'POLYFOAM SHEET' },
            { value: 'polyfoam_bag', label: 'POLYFOAM BAG' },
            { value: 'layer_sheet', label: 'LAYER SHEET' },
            { value: 'karton', label: 'KARTON' },
            { value: 'foam', label: 'FOAM' },
            { value: 'foam_sheet', label: 'FOAM SHEET' },
            { value: 'foam_bag', label: 'FOAM BAG' }
        ],
        'polybag': [
            { value: 'ldpe', label: 'LDPE' },
            { value: 'hdpe', label: 'HDPE' },
            { value: 'pe', label: 'PE' }
        ],
        'rempart': [
            { value: 'karton_box_p0_d0', label: 'KARTON BOX (P0-D0)' },
            { value: 'polybag_p0_p0', label: 'POLYBAG (P0-P0)' },
            { value: 'gasket_duplex_p0_ld', label: 'GASKET DUPLEX (P0-LD)' },
            { value: 'foam_sheet_p0_s0', label: 'FOAM SHEET (P0-S0)' },
            { value: 'hologram_p0_h0', label: 'HOLOGRAM (P0-H0)' },
            { value: 'label_a', label: 'LABEL A' },
            { value: 'label_b', label: 'LABEL B' }
        ]
    };

    function updateJenisOptions() {
        const kategori = kategoriSelect.value;
        const jenisSelect = document.getElementById('jenisSelect');
        
        // Clear existing options
        jenisSelect.innerHTML = '<option value="">- Pilih Jenis -</option>';
        
        if (jenisOptions[kategori]) {
            jenisOptions[kategori].forEach(option => {
                const opt = document.createElement('option');
                opt.value = option.value;
                opt.textContent = option.label;
                
                // Loose matching logic
                if (currentJenis && (currentJenis === option.value.toLowerCase())) {
                    opt.selected = true;
                }
                jenisSelect.appendChild(opt);
            });
            
            // Force value update with timeout to ensure DOM is ready
            setTimeout(() => {
                if (currentJenis && kategori === '{{ $bahanbaku->kategori }}') {
                    // Coba cari match case-insensitive
                    const match = jenisOptions[kategori].find(o => o.value.toLowerCase() === currentJenis);
                    if (match) {
                        jenisSelect.value = match.value;
                    }
                }
            }, 50);
        }
    }

    function generateNomorBahanBaku() {
        const kategori = kategoriSelect.value;
        const nomorBahanBakuInput = document.getElementById('nomorBahanBaku');
        
        // Only auto-generate for box, layer, polybag
        if (!['box', 'layer', 'polybag'].includes(kategori)) {
            return;
        }
        
        const jenisSelect = document.getElementById('jenisSelect');
        const jenis = jenisSelect.value;
        
        if (!jenis) {
            nomorBahanBakuInput.value = '';
            return;
        }
        
        // Get jenis label and format it
        const jenisOption = jenisOptions[kategori]?.find(opt => opt.value === jenis);
        let jenisLabel = jenisOption ? jenisOption.label : jenis.toUpperCase();
        
        // Format: Convert uppercase to title case for readability
        // Box: Title Case (e.g., POLYBOX -> Polybox, IMPRABOARD -> Impraboard)
        // Layer/Polybag: Title Case with spaces (e.g., POLYFOAM SHEET -> Polyfoam Sheet)
        if (kategori === 'box') {
            // Box: Convert to Title Case
            jenisLabel = jenisLabel.charAt(0).toUpperCase() + jenisLabel.slice(1).toLowerCase();
        } else {
            // Layer/Polybag: Title Case with spaces (e.g., POLYFOAM SHEET -> Polyfoam Sheet)
            jenisLabel = jenisLabel.split(' ').map(word => 
                word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
            ).join(' ');
        }
        
        // Get dimensions
        const panjang = document.querySelector('input[name="panjang"]')?.value || '';
        const lebar = document.querySelector('input[name="lebar"]')?.value || '';
        const tinggi = document.querySelector('input[name="tinggi"]')?.value || '';
        
        let nomor = jenisLabel;
        
        // Box: jenis + kodebox + ukuran (e.g., Impraboard-A14-20x20x30cm)
        if (kategori === 'box') {
            const kodeBox = document.querySelector('input[name="kode_box"]')?.value || '';
            if (kodeBox) {
                nomor += '-' + kodeBox;
            }
            // Add dimensions if all are filled
            if (panjang && lebar && tinggi) {
                nomor += '-' + panjang + 'x' + lebar + 'x' + tinggi + 'cm';
            } else if (panjang && lebar) {
                nomor += '-' + panjang + 'x' + lebar + 'cm';
            } else if (panjang) {
                nomor += '-' + panjang + 'cm';
            }
        } else {
            // Layer/Polybag: jenis + ukuran (e.g., Polyfoam sheet-10x30x40cm)
            if (panjang && lebar && tinggi) {
                nomor += '-' + panjang + 'x' + lebar + 'x' + tinggi + 'cm';
            } else if (panjang && lebar) {
                nomor += '-' + panjang + 'x' + lebar + 'cm';
            } else if (panjang) {
                nomor += '-' + panjang + 'cm';
            }
        }
        
        nomorBahanBakuInput.value = nomor;
    }

    function toggleFields() {
        const kategori = kategoriSelect.value;
        
        // Hide all groups first
        document.querySelectorAll('.field-group-1').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.field-group-2').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.field-group-kodebox').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.field-group-jenis').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.field-group-packing').forEach(el => el.style.display = 'none');
        
        // Show packing fields if NOT box
        if (kategori !== 'box') {
            document.querySelectorAll('.field-group-packing').forEach(el => el.style.display = 'block');
        }
        
        // Show appropriate fields
        if (group1.includes(kategori)) {
            // Group 1: Nama Bahan Baku, Std Packing, UOM, Jenis Packing
            document.querySelectorAll('.field-group-1').forEach(el => el.style.display = 'block');
            namaBahanBaku.required = true;
            // Enable manual input for nomor_bahan_baku
            const nomorInput = document.getElementById('nomorBahanBaku');
            nomorInput.readOnly = false;
            nomorInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        } else if (group2.includes(kategori)) {
            // Group 2: Panjang, Lebar, Tinggi + Jenis + Std Packing, UOM, Jenis Packing
            document.querySelectorAll('.field-group-2').forEach(el => el.style.display = 'block');
            document.querySelectorAll('.field-group-jenis').forEach(el => el.style.display = 'block');
            namaBahanBaku.required = false;
            updateJenisOptions();
            
            // Show Kode Box untuk box
            if (kategoriWithKodeBox.includes(kategori)) {
                document.querySelectorAll('.field-group-kodebox').forEach(el => el.style.display = 'block');
            }
            
            // Make nomor_bahan_baku readonly and auto-generate
            const nomorInput = document.getElementById('nomorBahanBaku');
            nomorInput.readOnly = true;
            nomorInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            
            // Setup listeners for auto-generate
            setupAutoGenerateListeners();
            // REMOVED: generateNomorBahanBaku(); - Do not auto-generate on init/toggle to preserve DB value
        } else if (kategori === 'rempart') {
            // REMPART: Jenis + Std Packing, UOM, Jenis Packing
            document.querySelectorAll('.field-group-jenis').forEach(el => el.style.display = 'block');
            namaBahanBaku.required = false;
            updateJenisOptions();
            // Enable manual input for rempart
            const nomorInput = document.getElementById('nomorBahanBaku');
            nomorInput.readOnly = false;
            nomorInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
        }
    }
    
    function setupAutoGenerateListeners() {
        const jenisSelect = document.getElementById('jenisSelect');
        const panjangInput = document.querySelector('input[name="panjang"]');
        const lebarInput = document.querySelector('input[name="lebar"]');
        const tinggiInput = document.querySelector('input[name="tinggi"]');
        const kodeBoxInput = document.querySelector('input[name="kode_box"]');
        
        // Prevent duplicate listeners logic without cloning node
        if (jenisSelect && !jenisSelect.hasAttribute('data-auto-gen-listener')) {
            jenisSelect.addEventListener('change', generateNomorBahanBaku);
            jenisSelect.setAttribute('data-auto-gen-listener', 'true');
        }
        
        // Helper to add listener safely
        const addListenerSafe = (el) => {
            if (el && !el.hasAttribute('data-auto-gen-listener')) {
                el.addEventListener('input', generateNomorBahanBaku);
                el.setAttribute('data-auto-gen-listener', 'true');
            }
        };

        addListenerSafe(panjangInput);
        addListenerSafe(lebarInput);
        addListenerSafe(tinggiInput);
        addListenerSafe(kodeBoxInput);
    }
    
    kategoriSelect.addEventListener('change', function() {
        toggleFields();
        generateNomorBahanBaku();
    });

    toggleFields(); // Initial call
    updateJenisOptions(); // Initial call to populate jenis options
    
    // Set initial value for jenis explicitly if exists
    const jenisSelect = document.getElementById('jenisSelect');
    if (currentJenis && jenisSelect) {
        jenisSelect.value = currentJenis;
    }
    
    // Setup auto-generate listeners but DO NOT trigger generation immediately
    // to preserve the existing nomad bahan baku from database
    if (['box', 'layer', 'polybag'].includes('{{ $bahanbaku->kategori }}')) {
        setupAutoGenerateListeners();
    }

    if (btnCancel) {
        btnCancel.addEventListener('click', function() {
            window.location.href = "{{ route('master.bahanbaku.index') }}";
        });
    }

    if (form && !form.hasAttribute('data-handler-attached')) {
        form.setAttribute('data-handler-attached', 'true');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);
            // Append _method PUT explicity just in case, though blade directive usually handles it
            formData.append('_method', 'PUT'); 

            try {
                const response = await fetch("{{ route('master.bahanbaku.update', $bahanbaku->id) }}", {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                    alert(data.message || 'Gagal update data');
                    return;
                }

                alert(data.message || 'Data bahan baku berhasil diperbarui');
                window.location.href = "{{ route('master.bahanbaku.index') }}";
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }
})();
</script>
@endsection
