@extends('layout.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Styling Select2 agar mirip Tailwind input */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d1d5db !important; /* border-gray-300 */
        border-radius: 0.5rem !important; /* rounded-lg */
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 8px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 16px !important; /* px-4 */
        color: #111827 !important; /* text-gray-900 */
        line-height: normal !important;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('spk.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Tambah Surat Perintah Pengiriman</h2>
            <p class="text-gray-600 mt-1">Buat SPK baru untuk pengiriman</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createSPKForm" class="space-y-6">
            @csrf
            
            {{-- Hidden fields but kept for form data if needed --}}
            <input type="hidden" name="nomor_spk" value="Auto Generated">
            <input type="hidden" name="manpower_pembuat" value="{{ $manpowerName ?? 'Unknown' }}">
            <input type="hidden" name="no_surat_jalan" value="">

            {{-- Row 1: 4 Columns --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Deadline Persiapan Barang (FG) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="tanggal" 
                        required
                        value="{{ date('Y-m-d H:i') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 datepicker-complete"
                        placeholder="Pilih Tanggal & Jam"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Rencana Berangkat <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="jam_berangkat_plan" 
                        required
                        value="07:00"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 timepicker"
                        placeholder="00:00"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jam Rencana Datang (Kembali)
                    </label>
                    <input 
                        type="text" 
                        name="jam_datang_plan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 timepicker"
                        placeholder="--:--"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Cycle <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="cycle" 
                        required
                        min="1"
                        value="1"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="1"
                    >
                </div>
            </div>

            {{-- Row 2: 4 Columns --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Customer <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="customer_id" 
                        id="customerSelect"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 select2-customer"
                    >
                        <option value="">Pilih Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->nama_perusahaan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Plant Gate <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="plantgate_id" 
                        id="plantgateSelect"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 cursor-not-allowed"
                        disabled
                    >
                        <option value="">Pilih Customer terlebih dahulu</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Model Part <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="model_part" 
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="regular">Regular</option>
                        <option value="ckd">CKD</option>
                        <option value="cbu">CBU</option>
                        <option value="rempart">Rempart</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Plat
                    </label>
                    <select 
                        name="nomor_plat" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Pilih Nomor Plat</option>
                        @foreach($kendaraans as $kendaraan)
                            <option value="{{ $kendaraan->nopol_kendaraan }}">{{ $kendaraan->nopol_kendaraan }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan
                </label>
                <textarea 
                    name="catatan" 
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Catatan tambahan jika ada"
                ></textarea>
            </div>

            {{-- Detail SPK --}}
            <div class="border-t border-gray-200 pt-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Part</h3>
                    <div id="partSearchContainer" class="relative hidden w-full sm:w-64">
                        <input 
                            type="text" 
                            id="partSearchInput" 
                            placeholder="Cari Nomor/Nama Part..." 
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 text-sm"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div id="partsTableContainer" class="hidden">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        {{-- Added max-height and overflow-y-auto for vertical scroll --}}
                        <div class="overflow-x-auto max-h-[600px] overflow-y-auto relative">
                            <table class="w-full border-collapse">
                                <thead class="bg-blue-600 text-white sticky top-0 z-20">
                                    <tr>
                                        <!-- Added bg-blue-600 to th to ensure opacity when scrolling mainly for sticky effect -->
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">NO</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">NOMOR PART</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">NAMA PART</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">JENIS PART</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">TIPE PART</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">STD QTY PACKING (PCS/BOX)</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">JADWAL DELIVERY (PCS)</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider bg-blue-600">JUMLAH PULLING (BOX)</th>
                                    </tr>
                                </thead>
                                <tbody id="partsTableBody" class="bg-white divide-y divide-gray-200">
                                    {{-- Parts rows will be generated here --}}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div id="emptyDetailState" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg">
                    <p>Pilih Plant Gate untuk menampilkan detail part.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('spk.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Simpan SPK
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Init Select2 and retain formatting
    $(document).ready(function() {
        $('.select2-customer').select2({
            placeholder: "Pilih Customer",
            allowClear: true,
            width: '100%'
        });

        // Trigger change event for vanilla JS listener when Select2 changes
        // Removed recursive manual dispatch
        

        // Init Flatpickr for Date & Time (Complete)
        flatpickr(".datepicker-complete", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
            defaultDate: new Date(),
        });

        // Init Flatpickr for Time Only (24h)
        flatpickr(".timepicker", {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
        });
    });
</script>
<script>
(function() {
    const form = document.getElementById('createSPKForm');
    // Changed: use select directly
    const customerSelect = document.getElementById('customerSelect');
    
    // Removed datalist related elements logic
    
    const plantgateSelect = document.getElementById('plantgateSelect');
    const partsTableContainer = document.getElementById('partsTableContainer');
    const partsTableBody = document.getElementById('partsTableBody');
    const emptyDetailState = document.getElementById('emptyDetailState');
    const partSearchContainer = document.getElementById('partSearchContainer');
    const partSearchInput = document.getElementById('partSearchInput');
    
    let parts = [];
    let lastCustomerId = null;

    // Search Logic
    partSearchInput?.addEventListener('input', function() {
        const filter = this.value.toLowerCase();
        const rows = partsTableBody.getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = rows[i];
            const nomorPart = row.cells[1]?.textContent.toLowerCase() || '';
            const namaPart = row.cells[2]?.textContent.toLowerCase() || '';
            
            if (nomorPart.includes(filter) || namaPart.includes(filter)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    });

    // Load plantgates when customer changes using jQuery listener (since Select2 uses jQuery events)
    $(customerSelect).on('change', async function() {
        const customerId = this.value;
        
        // Prevent redundant fetches if the customer hasn't changed
        if (customerId === lastCustomerId) return;
        lastCustomerId = customerId;
        
        if (!customerId) {
            plantgateSelect.innerHTML = '<option value="">Pilih Customer terlebih dahulu</option>';
            plantgateSelect.disabled = true;
            plantgateSelect.classList.add('cursor-not-allowed', 'bg-gray-50');
            
            // Clear parts
            partsTableContainer.classList.add('hidden');
            partSearchContainer.classList.add('hidden');
            emptyDetailState.classList.remove('hidden');
            partsTableBody.innerHTML = '';
            partSearchInput.value = ''; // Clear search
            return;
        }

        try {
            const response = await fetch(`/spk/api/plantgates?customer_id=${customerId}`);
            const data = await response.json();
            
            if (data.success) {
                plantgateSelect.innerHTML = '<option value="">Pilih Plant Gate</option>';
                data.data.forEach(pg => {
                    const option = document.createElement('option');
                    option.value = pg.id;
                    option.textContent = pg.nama_plantgate;
                    plantgateSelect.appendChild(option);
                });
                plantgateSelect.disabled = false;
                plantgateSelect.classList.remove('cursor-not-allowed', 'bg-gray-50');
            } else {
                alert(data.message || 'Gagal memuat plant gates');
                plantgateSelect.disabled = true;
                plantgateSelect.classList.add('cursor-not-allowed', 'bg-gray-50');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat plant gates');
            plantgateSelect.disabled = true;
            plantgateSelect.classList.add('cursor-not-allowed', 'bg-gray-50');
        }
    });

    // Load parts when plantgate changes
    plantgateSelect?.addEventListener('change', async function() {
        const plantgateId = this.value;
        if (!plantgateId) {
            partsTableContainer.classList.add('hidden');
            emptyDetailState.classList.remove('hidden');
            partsTableBody.innerHTML = '';
            return;
        }

        try {
            const response = await fetch(`/spk/api/parts?plantgate_id=${plantgateId}`);
            const data = await response.json();
            
            if (data.success && data.data.length > 0) {
                parts = data.data;
                generatePartsTable(parts);
                partsTableContainer.classList.remove('hidden');
                partSearchContainer.classList.remove('hidden');
                emptyDetailState.classList.add('hidden');
            } else {
                partsTableContainer.classList.add('hidden');
                partSearchContainer.classList.add('hidden');
                emptyDetailState.classList.remove('hidden');
                emptyDetailState.innerHTML = '<p>Tidak ada part untuk plant gate ini.</p>';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat parts');
            partsTableContainer.classList.add('hidden');
            partSearchContainer.classList.add('hidden');
            emptyDetailState.classList.remove('hidden');
        }
    });

    function generatePartsTable(partsList) {
        partsTableBody.innerHTML = '';
        
        partsList.forEach((part, index) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-gray-50';
            
            const stdQtyPacking = part.QTY_Packing_Box || 0;
            // Format model_part: regular -> Reguler, ckd -> CKD, cbu -> CBU, rempart -> Rempart
            const modelPartMap = {
                'regular': 'Reguler',
                'ckd': 'CKD',
                'cbu': 'CBU',
                'rempart': 'Rempart'
            };
            const modelPart = modelPartMap[part.model_part?.toLowerCase()] || (part.model_part ? part.model_part.charAt(0).toUpperCase() + part.model_part.slice(1) : 'Reguler');
            
            row.innerHTML = `
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">${index + 1}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 font-mono">${part.nomor_part}</td>
                <td class="px-4 py-3 text-sm text-gray-900">${part.nama_part}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${modelPart}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${part.tipe_id || '-'}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">${stdQtyPacking}</td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <input 
                        type="hidden" 
                        name="details[${index}][part_id]" 
                        value="${part.id}"
                    >
                    <input 
                        type="hidden" 
                        name="details[${index}][qty_packing_box]" 
                        value="${stdQtyPacking}"
                    >
                    <input 
                        type="number" 
                        name="details[${index}][jadwal_delivery_pcs]" 
                        data-part-index="${index}"
                        data-std-qty="${stdQtyPacking}"
                        min="0"
                        value="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50"
                        placeholder="0"
                    >
                </td>
                <td class="px-4 py-3 whitespace-nowrap">
                    <input 
                        type="number" 
                        name="details[${index}][jumlah_pulling_box]" 
                        data-pulling-box="${index}"
                        readonly
                        value="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-700"
                        placeholder="0"
                    >
                    <input 
                        type="hidden" 
                        name="details[${index}][catatan]" 
                        value=""
                    >
                </td>
            `;
            
            partsTableBody.appendChild(row);
            
            // Add event listener for auto-calculation and validation
            const jadwalInput = row.querySelector('[data-part-index]');
            const pullingBoxInput = row.querySelector('[data-pulling-box]');
            
            if (jadwalInput && pullingBoxInput) {
                jadwalInput.addEventListener('change', function() {
                     validateQuantity(this, pullingBoxInput);
                });
                
                jadwalInput.addEventListener('input', function() {
                     // Recalculate usage while typing, but validate strict on change
                     const stdQty = parseFloat(this.getAttribute('data-std-qty')) || 0;
                     const jadwalDelivery = parseFloat(this.value) || 0;
                     
                     // Calculate jumlah pulling box: ceil(jadwal_delivery / std_qty)
                     const jumlahPullingBox = (stdQty > 0 && jadwalDelivery > 0) ? Math.ceil(jadwalDelivery / stdQty) : 0;
                     pullingBoxInput.value = jumlahPullingBox;

                     // UX: Highlight row if value > 0
                     if (jadwalDelivery > 0) {
                         row.classList.add('bg-blue-50');
                         row.classList.remove('hover:bg-gray-50');
                     } else {
                         row.classList.remove('bg-blue-50');
                         row.classList.add('hover:bg-gray-50');
                     }
                });
            }
        });
    }

    function validateQuantity(input, pullingBoxInput) {
        const stdQty = parseFloat(input.getAttribute('data-std-qty')) || 0;
        const value = parseFloat(input.value) || 0;
        
        if (value > 0 && stdQty > 0) {
            if (value % stdQty !== 0) {
                alert(`Jadwal Delivery untuk item ini harus kelipatan Standard Packing (${stdQty} Pcs)!\nContoh: ${stdQty}, ${stdQty*2}, ${stdQty*3}...`);
                
                // Reset to nearest multiple or previous valid? 
                // Let's just reset to 0 or focus
                input.value = 0;
                pullingBoxInput.value = 0;
                input.focus();
                return false;
            }
        }
        return true;
    }

    // Form submit
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Validate at least one part with jadwal delivery > 0
        const jadwalInputs = document.querySelectorAll('[name*="[jadwal_delivery_pcs]"]');
        let hasValidDelivery = false;
        let isAllQuantitiesValid = true;

        jadwalInputs.forEach(input => {
            const val = parseFloat(input.value) || 0;
            const stdQty = parseFloat(input.getAttribute('data-std-qty')) || 0;

            if (val > 0) {
                hasValidDelivery = true;
                if (stdQty > 0 && val % stdQty !== 0) {
                    isAllQuantitiesValid = false;
                    input.classList.add('border-red-500', 'ring-red-500');
                } else {
                    input.classList.remove('border-red-500', 'ring-red-500');
                }
            }
        });
        
        if (!hasValidDelivery) {
            alert('Minimal harus ada 1 part dengan jadwal delivery > 0');
            return;
        }

        if (!isAllQuantitiesValid) {
            alert('Ada input Jadwal Delivery yang tidak sesuai kelipatan Standard Packing. Mohon periksa kembali input yang berwarna merah.');
            return;
        }
        
        const formData = new FormData(form);
        
        // Filter out parts with jadwal_delivery_pcs = 0 before submitting
        let detailIndex = 0;
        const filteredFormData = new FormData();
        
        // Copy all non-details fields
        for (const [key, value] of formData.entries()) {
            if (!key.startsWith('details[')) {
                filteredFormData.append(key, value);
            }
        }
        
        // Only include details with jadwal_delivery_pcs > 0
        document.querySelectorAll('[name*="[jadwal_delivery_pcs]"]').forEach((input, idx) => {
            const jadwalDelivery = parseFloat(input.value) || 0;
            if (jadwalDelivery > 0) {
                const row = input.closest('tr');
                const partId = row.querySelector('[name*="[part_id]"]').value;
                const qtyPackingBox = row.querySelector('[name*="[qty_packing_box]"]').value;
                const jumlahPullingBox = row.querySelector('[name*="[jumlah_pulling_box]"]').value;
                
                filteredFormData.append(`details[${detailIndex}][part_id]`, partId);
                filteredFormData.append(`details[${detailIndex}][qty_packing_box]`, qtyPackingBox);
                filteredFormData.append(`details[${detailIndex}][jadwal_delivery_pcs]`, jadwalDelivery);
                filteredFormData.append(`details[${detailIndex}][jumlah_pulling_box]`, jumlahPullingBox);
                
                // Catatan (opsional)
                const catatanInput = row.querySelector('[name*="[catatan]"]');
                filteredFormData.append(`details[${detailIndex}][catatan]`, catatanInput ? (catatanInput.value || '') : '');
                
                detailIndex++;
            }
        });
        
        try {
            const response = await fetch('{{ route("spk.store") }}', {
                method: 'POST',
                body: filteredFormData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message || 'SPK berhasil dibuat');
                window.location.href = '{{ route("spk.index") }}';
            } else {
                alert(data.message || 'Gagal menyimpan SPK');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan SPK');
        }
    });
})();
</script>
@endpush
@endsection
