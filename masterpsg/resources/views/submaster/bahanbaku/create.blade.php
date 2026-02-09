@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <h2 class="text-xl font-bold text-gray-900 leading-none">Tambah Bahan Baku</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Isi data bahan baku baru</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="formCreateBahanBaku">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Baris 1: Kategori | Supplier | Nomor Bahan Baku -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori <span class="text-red-600">*</span></label>
                    <select name="kategori" id="kategoriSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="material">MATERIAL</option>
                        <option value="masterbatch">MASTERBATCH</option>
                        <option value="subpart">SUBPART</option>
                        <option value="box">BOX</option>
                        <option value="layer">LAYER</option>
                        <option value="polybag">POLYBAG</option>
                        <option value="rempart">REMPART</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                    <div class="relative">
                        <input type="text" 
                               id="supplierInput" 
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                               placeholder="Ketik nama supplier..." 
                               autocomplete="off">
                        <input type="hidden" name="supplier_id" id="supplierId">
                        
                        <!-- Custom Dropdown Container -->
                        <div id="supplierDropdown" class="absolute z-50 w-full bg-white border border-gray-200 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden">
                            <ul id="supplierList" class="py-1">
                                <!-- List items will be injected here -->
                            </ul>
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Bahan Baku</label>
                    <input type="text" name="nomor_bahan_baku" id="nomorBahanBaku" placeholder="Contoh: MAT-001 / MB-001" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                    <p class="text-xs text-gray-500 mt-1">Opsional. Auto-generate untuk BOX/LAYER/POLYBAG.</p>
                </div>

                <!-- Baris 2: Nama Bahan Baku (Group 1) ATAU Jenis (Group 2/Rempart/etc) -->
                <!-- Group 1: MATERIAL, MASTERBATCH, SUBPART -->
                <div class="md:col-span-3 field-group field-group-1" id="fieldGroup1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Bahan Baku <span class="text-red-600">*</span></label>
                    <input type="text" name="nama_bahan_baku" id="namaBahanBaku" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Jenis field untuk box, layer, polybag, rempart (Menggantikan posisi Nama Bahan Baku di Row 2 jika aktif) -->
                <div class="md:col-span-3 field-group field-group-jenis" id="fieldGroupJenis" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis <span class="text-red-600">*</span></label>
                    <select name="jenis" id="jenisSelect" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Jenis -</option>
                    </select>
                </div>

                <!-- Std Packing, UOM, Jenis Packing - untuk semua kategori kecuali BOX -->
                <div class="field-group field-group-packing" id="fieldGroupPacking1">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Std Packing</label>
                    <input type="number" step="0.01" min="0" name="std_packing" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="field-group field-group-packing" id="fieldGroupPacking2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">UOM</label>
                    <select name="uom" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih UOM -</option>
                        <option value="KG">KG</option>
                        <option value="PCS">PCS</option>
                        <option value="KARUNG">KARUNG</option>
                        <option value="PALLET">PALLET</option>
                        <option value="BOX">BOX</option>
                        <option value="ROLL">ROLL</option>
                        <option value="LITER">LITER</option>
                    </select>
                </div>

                <div class="field-group field-group-packing" id="fieldGroupPacking3">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Packing</label>
                    <select name="jenis_packing" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">- Pilih Jenis Packing -</option>
                        <option value="KARUNG">KARUNG</option>
                        <option value="PALLET">PALLET</option>
                        <option value="BOX">BOX</option>
                        <option value="BAG">BAG</option>
                        <option value="ROLL">ROLL</option>
                        <option value="PLASTIK">PLASTIK</option>
                    </select>
                </div>

                <!-- Baris 4: Dimensi (Box/Layer/Polybag Only) -->
                <!-- Group 2: BOX, LAYER, POLYBAG (Panjang, Lebar, Tinggi) -->
                <div class="field-group field-group-2" id="fieldGroup2" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Panjang (cm)</label>
                    <input type="number" step="0.01" min="0" name="panjang" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="field-group field-group-2" id="fieldGroup2" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Lebar (cm)</label>
                    <input type="number" step="0.01" min="0" name="lebar" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="field-group field-group-2" id="fieldGroup2" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi (cm)</label>
                    <input type="number" step="0.01" min="0" name="tinggi" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <!-- Kode Box untuk box (Baris 5) -->
                <div class="md:col-span-3 field-group field-group-kodebox" id="fieldGroupKodeBox" style="display: none;">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Box</label>
                    <input type="text" name="kode_box" maxlength="50" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: BOX-001" />
                </div>
                
                <!-- Keterangan (Full Width) -->
                <div class="md:col-span-3 mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="Tambahan informasi (opsional)..."></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 mt-6">
                <a href="{{ route('master.bahanbaku.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('formCreateBahanBaku');
    const kategoriSelect = document.getElementById('kategoriSelect');
    const namaBahanBaku = document.getElementById('namaBahanBaku');

    // Kategori groups
    const group1 = ['material', 'masterbatch', 'subpart']; // Nama Bahan Baku
    const group2 = ['box', 'layer', 'polybag']; // Panjang, Lebar, Tinggi + Jenis
    const kategoriWithKodeBox = ['box']; // Kode Box hanya untuk box
    
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
                jenisSelect.appendChild(opt);
            });
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
            document.getElementById('nomorBahanBaku').readOnly = false;
            document.getElementById('nomorBahanBaku').classList.remove('bg-gray-100');
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
            document.getElementById('nomorBahanBaku').readOnly = true;
            document.getElementById('nomorBahanBaku').classList.add('bg-gray-100', 'cursor-not-allowed');
            
            // Setup listeners for auto-generate
            setupAutoGenerateListeners();
            generateNomorBahanBaku();
        } else if (kategori === 'rempart') {
            // REMPART: Jenis + Std Packing, UOM, Jenis Packing
            document.querySelectorAll('.field-group-jenis').forEach(el => el.style.display = 'block');
            namaBahanBaku.required = false;
            updateJenisOptions();
            // Enable manual input for rempart
            document.getElementById('nomorBahanBaku').readOnly = false;
            document.getElementById('nomorBahanBaku').classList.remove('bg-gray-100');
        }
    }
    
    function setupAutoGenerateListeners() {
        const jenisSelect = document.getElementById('jenisSelect');
        const panjangInput = document.querySelector('input[name="panjang"]');
        const lebarInput = document.querySelector('input[name="lebar"]');
        const tinggiInput = document.querySelector('input[name="tinggi"]');
        const kodeBoxInput = document.querySelector('input[name="kode_box"]');
        
        // Remove existing listeners to avoid duplicates
        const newJenisSelect = jenisSelect.cloneNode(true);
        jenisSelect.parentNode.replaceChild(newJenisSelect, jenisSelect);
        
        // Add listeners
        document.getElementById('jenisSelect').addEventListener('change', generateNomorBahanBaku);
        if (panjangInput) {
            panjangInput.addEventListener('input', generateNomorBahanBaku);
        }
        if (lebarInput) {
            lebarInput.addEventListener('input', generateNomorBahanBaku);
        }
        if (tinggiInput) {
            tinggiInput.addEventListener('input', generateNomorBahanBaku);
        }
        if (kodeBoxInput) {
            kodeBoxInput.addEventListener('input', generateNomorBahanBaku);
        }
    }
    
    kategoriSelect.addEventListener('change', function() {
        toggleFields();
        generateNomorBahanBaku();
    });

    // Supplier Custom Autocomplete Logic
    const supplierInput = document.getElementById('supplierInput');
    const supplierDropdown = document.getElementById('supplierDropdown');
    const supplierList = document.getElementById('supplierList');
    const supplierHidden = document.getElementById('supplierId');
    
    // Inject data from server
    // Inject data from server
    const suppliersData = @json($suppliers->map(function($s) {
        return ['id' => $s->id, 'name' => $s->nama_perusahaan];
    }));

    if (supplierInput && supplierDropdown && supplierList && supplierHidden) {
        
        function filterSuppliers(query) {
            const lowerQuery = query.toLowerCase();
            return suppliersData.filter(s => s.name.toLowerCase().includes(lowerQuery));
        }

        function showDropdown(items) {
            supplierList.innerHTML = '';
            
            if (items.length === 0) {
                const li = document.createElement('li');
                li.className = 'px-4 py-2 text-sm text-gray-500 italic';
                li.textContent = 'Tidak ditemukan';
                supplierList.appendChild(li);
            } else {
                items.forEach(item => {
                    const li = document.createElement('li');
                    li.className = 'px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 cursor-pointer transition-colors border-b border-gray-100 last:border-0';
                    li.textContent = item.name;
                    li.onclick = function() {
                        supplierInput.value = item.name;
                        supplierHidden.value = item.id;
                        supplierDropdown.classList.add('hidden');
                    };
                    supplierList.appendChild(li);
                });
            }
            supplierDropdown.classList.remove('hidden');
        }

        supplierInput.addEventListener('input', function() {
            const val = this.value;
            if (!val) {
                supplierDropdown.classList.add('hidden');
                supplierHidden.value = '';
                return;
            }
            const filtered = filterSuppliers(val);
            showDropdown(filtered);
            
            // Clear ID if typed value doesn't strictly match selected (user is typing new stuff)
            // But we keep it empty until they select, or we could try to auto-match if exact match found
            const exactMatch = suppliersData.find(s => s.name.toLowerCase() === val.toLowerCase());
            supplierHidden.value = exactMatch ? exactMatch.id : '';
        });

        // Show full list on focus if empty or filter if has value
        supplierInput.addEventListener('focus', function() {
             const val = this.value;
             if (!val) {
                 // Show all (limit to 50 maybe if too many)
                 showDropdown(suppliersData.slice(0, 50)); 
             } else {
                 showDropdown(filterSuppliers(val));
             }
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!supplierInput.contains(e.target) && !supplierDropdown.contains(e.target)) {
                supplierDropdown.classList.add('hidden');
            }
        });
    }

    toggleFields(); // Initial call

    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Validate Supplier
            const supplierInput = document.getElementById('supplierInput');
            const supplierHidden = document.getElementById('supplierId');
            
            if (supplierInput && supplierHidden) {
                // Jika input ada text tapi hidden ID kosong, berarti user ngetik manual tapi ga pilih dari list (atau ga match)
                if (supplierInput.value.trim() !== '' && !supplierHidden.value) {
                    alert('Nama Supplier tidak valid atau belum dipilih dari rekomendasi. Silakan pilih supplier dari list yang muncul.');
                    supplierInput.focus();
                    return;
                }
            }

            const formData = new FormData(form);

            try {
                const response = await fetch('{{ route("master.bahanbaku.store") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok || !data.success) {
                    alert(data.message || 'Gagal menyimpan data');
                    return;
                }

                // Show success modal/alert
                alert(data.message || 'Data bahan baku berhasil ditambahkan');
                window.location.href = '{{ route("master.bahanbaku.index") }}';
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }
})();
</script>
@endsection

