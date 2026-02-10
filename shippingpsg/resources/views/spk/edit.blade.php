@extends('layout.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
@endpush

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route('spk.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            <span class="font-medium">Kembali</span>
        </a>
            <h2 class="text-3xl font-bold text-gray-800">Edit Surat Perintah Pengiriman</h2>
            <p class="text-gray-600 mt-1">Edit SPK: {{ $spk->nomor_spk }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editSPKForm" class="space-y-6">
            @csrf
            @method('PUT')
            
            {{-- Hidden fields but kept for form data if needed --}}
            <input type="hidden" name="nomor_spk" value="{{ $spk->nomor_spk }}">
            <input type="hidden" name="manpower_pembuat" value="{{ $spk->manpower_pembuat }}">
            <input type="hidden" name="no_surat_jalan" value="{{ $spk->no_surat_jalan }}">

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
                        value="{{ optional($spk->tanggal)->format('Y-m-d H:i') }}"
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
                        value="{{ $spk->jam_berangkat_plan ? date('H:i', strtotime($spk->jam_berangkat_plan)) : '07:00' }}"
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
                        value="{{ $spk->jam_datang_plan ? date('H:i', strtotime($spk->jam_datang_plan)) : '' }}"
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
                        value="{{ $spk->cycle_number ?? $spk->cycle ?? 1 }}"
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Pilih Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $spk->customer_id == $customer->id ? 'selected' : '' }}>{{ $customer->nama_perusahaan }}</option>
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
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Pilih Plant Gate</option>
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
                        <option value="regular" {{ $spk->model_part == 'regular' ? 'selected' : '' }}>Regular</option>
                        <option value="ckd" {{ $spk->model_part == 'ckd' ? 'selected' : '' }}>CKD</option>
                        <option value="cbu" {{ $spk->model_part == 'cbu' ? 'selected' : '' }}>CBU</option>
                        <option value="rempart" {{ $spk->model_part == 'rempart' ? 'selected' : '' }}>Rempart</option>
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
                            <option value="{{ $kendaraan->nopol_kendaraan }}" {{ $spk->nomor_plat == $kendaraan->nopol_kendaraan ? 'selected' : '' }}>{{ $kendaraan->nopol_kendaraan }}</option>
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
                >{{ $spk->catatan }}</textarea>
            </div>

            {{-- Detail SPK --}}
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">Detail Part</h3>
                    <button 
                        type="button" 
                        id="btnAddDetail"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Tambah Part</span>
                    </button>
                </div>

                <div id="detailsContainer" class="space-y-4">
                    {{-- Existing details will be loaded here --}}
                </div>

                <div id="emptyDetailState" class="text-center py-8 text-gray-500 border-2 border-dashed border-gray-300 rounded-lg" style="display: none;">
                    <p>Belum ada detail part. Klik "Tambah Part" untuk menambahkan.</p>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('spk.index') }}" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Update SPK
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    // Initialize Flatpickr immediately (before other scripts)
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr(".datepicker-complete", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            time_24hr: true,
        });

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
    const form = document.getElementById('editSPKForm');

    const customerSelect = document.getElementById('customerSelect');
    const plantgateSelect = document.getElementById('plantgateSelect');
    const btnAddDetail = document.getElementById('btnAddDetail');
    const detailsContainer = document.getElementById('detailsContainer');
    const emptyDetailState = document.getElementById('emptyDetailState');
    
    let detailIndex = @json($spk->details->count());
    let parts = @json($parts);
    const existingDetails = @json($spk->details);

    // Load plantgates when customer changes
    async function loadPlantgates(customerId, selectedPlantgateId = null) {
        if (!customerId) {
            plantgateSelect.innerHTML = '<option value="">Pilih Customer terlebih dahulu</option>';
            plantgateSelect.disabled = true;
            return;
        }

        try {
            const response = await fetch(`{{ route('spk.api.plantgates') }}?customer_id=${customerId}`);
            const data = await response.json();
            
            if (data.success) {
                plantgateSelect.innerHTML = '<option value="">Pilih Plant Gate</option>';
                data.data.forEach(pg => {
                    const option = document.createElement('option');
                    option.value = pg.id;
                    option.textContent = pg.nama_plantgate;
                    if (selectedPlantgateId && pg.id == selectedPlantgateId) {
                        option.selected = true;
                    }
                    plantgateSelect.appendChild(option);
                });
                plantgateSelect.disabled = false;
            } else {
                alert(data.message || 'Gagal memuat plant gates');
                plantgateSelect.disabled = true;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memuat plant gates');
            plantgateSelect.disabled = true;
        }
    }

    // Initial load plantgates
    const currentCustomerId = customerSelect?.value;
    const currentPlantgateId = @json($spk->plantgate_id ?? null);
    let lastCustomerId = currentCustomerId; // Set initial value

    if (currentCustomerId) {
        loadPlantgates(currentCustomerId, currentPlantgateId);
    }

    customerSelect?.addEventListener('change', async function() {
        const customerId = this.value;
        if (customerId === lastCustomerId) return;
        lastCustomerId = customerId;
        
        await loadPlantgates(customerId);
    });

    // Load parts when plantgate changes
    plantgateSelect?.addEventListener('change', async function() {
        const plantgateId = this.value;
        if (!plantgateId) return;

        try {
            const response = await fetch(`{{ route('spk.api.parts') }}?plantgate_id=${plantgateId}`);
            const data = await response.json();
            
            if (data.success) {
                parts = data.data;
                document.querySelectorAll('[data-part-select]').forEach(select => {
                    updatePartSelect(select, parts);
                });
            }
        } catch (error) {
            console.error('Error:', error);
        }
    });

    // Load existing details
    existingDetails.forEach((detail, idx) => {
        detailIndex = idx;
        addDetailRow(detail);
    });

    // Add detail row
    btnAddDetail?.addEventListener('click', function() {
        detailIndex++;
        addDetailRow();
    });

    function addDetailRow(existingDetail = null) {
        emptyDetailState.style.display = 'none';
        const currentIdx = existingDetail ? existingDetail.id : detailIndex;
        
        const row = document.createElement('div');
        row.className = 'bg-gray-50 rounded-lg p-4 border border-gray-200';
        row.dataset.index = currentIdx;
        
        row.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Part <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="details[${currentIdx}][part_id]" 
                        data-part-select
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">Pilih Part</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Qty Packing Box <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="details[${currentIdx}][qty_packing_box]" 
                        required
                        min="0"
                        value="${existingDetail?.qty_packing_box ?? 0}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jadwal Delivery (pcs) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="details[${currentIdx}][jadwal_delivery_pcs]" 
                        required
                        min="0"
                        value="${existingDetail?.jadwal_delivery_pcs ?? 0}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Pulling Box <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="details[${currentIdx}][jumlah_pulling_box]" 
                        required
                        min="0"
                        value="${existingDetail?.jumlah_pulling_box ?? 0}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan
                </label>
                <textarea 
                    name="details[${currentIdx}][catatan]"
                    rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Catatan (opsional)"
                >${existingDetail?.catatan ?? ''}</textarea>
            </div>
            <div class="mt-4 flex justify-end">
                    <button 
                        type="button" 
                        class="btnRemoveDetail text-red-600 hover:text-red-900 transition-colors"
                        data-index="${currentIdx}"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Hapus
                    </button>
            </div>
        `;
        
        detailsContainer.appendChild(row);
        
        // Update part select
        const partSelect = row.querySelector('[data-part-select]');
        updatePartSelect(partSelect, parts);
        
        // Set selected part if existing
        if (existingDetail?.part_id) {
            setTimeout(() => {
                partSelect.value = existingDetail.part_id;
            }, 100);
        }
        
        // Remove detail row handler
        row.querySelector('.btnRemoveDetail')?.addEventListener('click', function() {
            row.remove();
            if (detailsContainer.children.length === 0) {
                emptyDetailState.style.display = 'block';
            }
        });

        // Auto-Calculation Logic
        const qtyPackingInput = row.querySelector(`[name="details[${currentIdx}][qty_packing_box]"]`);
        const jadwalInput = row.querySelector(`[name="details[${currentIdx}][jadwal_delivery_pcs]"]`);
        const pullingBoxInput = row.querySelector(`[name="details[${currentIdx}][jumlah_pulling_box]"]`);
        const catatanInput = row.querySelector(`[name="details[${currentIdx}][catatan]"]`);

        const calculate = () => {
            const stdQty = parseFloat(qtyPackingInput.value) || 0;
            const jadwalQty = parseFloat(jadwalInput.value) || 0;
            
            // 1. Calculate Pulling Box
            if (stdQty > 0) {
                pullingBoxInput.value = Math.ceil(jadwalQty / stdQty);
            } else {
                pullingBoxInput.value = 0;
            }

            // 2. Handle Additional Note (Non-Standard Qty)
            if (stdQty > 0 && jadwalQty > 0) {
                const remainder = jadwalQty % stdQty;
                const boxes = Math.floor(jadwalQty / stdQty);
                
                // Template for auto-generated note
                // "additional | Non-std qty: X box (XxY) + Z pcs (packing khusus)"
                let noteText = "";
                if (remainder > 0) {
                     noteText = `additional | Non-std qty: ${boxes} box (${boxes}x${stdQty}) + ${remainder} pcs (packing khusus)`;
                }

                const currentNote = catatanInput.value.trim();
                
                // Update only if:
                // - There is a remainder AND (Note is empty OR Note is auto-generated type)
                // - OR There is NO remainder AND Note is auto-generated (Clear it)
                
                if (remainder > 0) {
                    if (currentNote === "" || currentNote.startsWith("additional |")) {
                        catatanInput.value = noteText;
                    }
                } else {
                    // Valid standard quantity. Clean up the note if it was auto-generated.
                    if (currentNote.startsWith("additional |")) {
                        catatanInput.value = ""; 
                    }
                }
            }
        };
        
        // Attach listeners
        qtyPackingInput?.addEventListener('input', calculate);
        jadwalInput?.addEventListener('input', calculate);
    }

    function updatePartSelect(select, partsList) {
        if (!select || !partsList) return;
        
        const currentValue = select.value;
        select.innerHTML = '<option value="">Pilih Part</option>';
        
        partsList.forEach(part => {
            const option = document.createElement('option');
            option.value = part.id;
            option.textContent = `${part.nomor_part} - ${part.nama_part}`;
            select.appendChild(option);
        });
        
        if (currentValue) {
            select.value = currentValue;
        }
    }

    // Form submit
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const detailRows = detailsContainer.querySelectorAll('[data-index]');
        if (detailRows.length === 0) {
            alert('Minimal harus ada 1 detail part');
            return;
        }
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route('spk.update', $spk->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-HTTP-Method-Override': 'PUT'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message || 'SPK berhasil diperbarui');
                window.location.href = "{{ route('spk.index') }}";
            } else {
                alert(data.message || 'Gagal memperbarui SPK');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memperbarui SPK');
        }
    });
})();
</script>
@endsection
