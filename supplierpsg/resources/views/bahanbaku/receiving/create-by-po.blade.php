@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Receiving Berbasis PO Number</h2>
        <p class="text-gray-600 mt-1">Input PO Number untuk fetch schedule kedatangan material</p>
    </div>

    <!-- Step 1: Input PO Number -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-4">
        <h3 class="text-lg font-semibold mb-4">Step 1: Input PO Number</h3>
        
        <div class="flex gap-3">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-1">PO Number <span class="text-red-600">*</span></label>
                <input 
                    type="text" 
                    id="poNumberInput" 
                    placeholder="Masukkan PO Number" 
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div class="flex items-end">
                <button 
                    type="button" 
                    id="btnFetchSchedule" 
                    onclick="fetchScheduleHandler(event)"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition">
                    Fetch Schedule
                </button>
            </div>
        </div>

        <div id="supplierInfo" class="mt-4 hidden">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                <p class="text-sm text-gray-700"><strong>Supplier:</strong> <span id="supplierName"></span></p>
            </div>
        </div>
    </div>

    <!-- Step 2: Schedule List & Input Receiving -->
    <div id="scheduleSection" class="hidden">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold mb-4">Step 2: Input Qty Actual Receiving</h3>

            <form id="formReceivingByPO" onsubmit="return false;">
                <input type="hidden" id="poNumberHidden" name="po_number">
                <input type="hidden" id="supplierIdHidden" name="supplier_id">

                <!-- Header Info -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No Surat Jalan</label>
                        <input 
                            type="text" 
                            name="no_surat_jalan" 
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Opsional"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Manpower <span class="text-red-600">*</span></label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                id="manpowerInput" 
                                name="manpower" 
                                required
                                maxlength="100" 
                                placeholder="Scan QR Code atau input manual" 
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            />
                            <button 
                                type="button" 
                                id="btnScanManpower" 
                                onclick="scanManpowerQR(event)"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors whitespace-nowrap flex items-center justify-center"
                                title="Scan QR Code Karyawan"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                            </button>
                        </div>
                        <p id="manpowerInfo" class="text-xs mt-0.5 px-1"></p>
                    </div>
                    <div class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Shift <span class="text-red-600">*</span></label>
                        <select 
                            name="shift" 
                            required
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="1">Shift 1</option>
                            <option value="2">Shift 2</option>
                            <option value="3">Shift 3</option>
                        </select>
                    </div>
                </div>

                <!-- Schedule Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-300">
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">No</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nomor Material</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Nama Material</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Tanggal Schedule</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Plan</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Already Received</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Sisa</th>
                                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">Qty Actual <span class="text-red-600">*</span></th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Internal Lot No</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Lot Number</th>
                            </tr>
                        </thead>
                        <tbody id="scheduleTableBody">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex gap-3">
                    <button 
                        type="button" 
                        id="btnSubmitReceiving"
                        onclick="handleSubmitReceiving(event)"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition">
                        <i class="fas fa-save mr-2"></i>Save Receiving
                    </button>
                    <button 
                        type="button" 
                        onclick="window.location.href='{{ route('bahanbaku.receiving.index') }}'"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg font-medium transition">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
console.log('=== SCRIPT LOADED AT:', new Date().toLocaleTimeString(), '===');
console.log('Page URL:', window.location.href);

// Global function untuk handle submit receiving
async function handleSubmitReceiving(e) {
    console.log('=== handleSubmitReceiving CALLED! ===');
    console.log('Event:', e);
    
    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    const formReceivingByPO = document.getElementById('formReceivingByPO');
    const btnSubmitReceiving = document.getElementById('btnSubmitReceiving');
    
    console.log('Form element:', formReceivingByPO);
    console.log('Button element:', btnSubmitReceiving);
    
    if (!formReceivingByPO) {
        console.error('Form not found!');
        alert('Error: Form tidak ditemukan');
        return;
    }
    
    const shouldSubmit = confirm('Simpan data receiving ini?');
    console.log('User confirm result:', shouldSubmit);
    
    if (!shouldSubmit) {
        console.log('User cancelled');
        return;
    }
    
    console.log('Preparing form data...');
    const formData = new FormData(formReceivingByPO);
    
    // Debug: Log form data
    console.log('=== FORM DATA ===');
    let itemCount = 0;
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
        if (pair[0].startsWith('items[')) itemCount++;
    }
    console.log('Total items:', itemCount / 4);
    
    // Disable button
    if (btnSubmitReceiving) {
        btnSubmitReceiving.disabled = true;
        btnSubmitReceiving.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    }
    
    console.log('=== SENDING REQUEST ===');
    
    try {
        const url = '{{ route('bahanbaku.receiving.storeByPO') }}';
        console.log('URL:', url);
        
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: formData
        });
        
        console.log('=== RESPONSE RECEIVED ===');
        console.log('Status:', response.status);
        console.log('OK:', response.ok);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let result;
        try {
            result = JSON.parse(responseText);
            console.log('Parsed result:', result);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid JSON response');
        }
        
        if (result.success) {
            console.log('=== SUCCESS! ID:', result.receiving_id, '===');
            
            // Hide form
            formReceivingByPO.style.display = 'none';
            
            // Show success message
            const successDiv = document.createElement('div');
            successDiv.className = 'bg-green-50 border-2 border-green-500 rounded-lg p-6 text-center';
            successDiv.innerHTML = `
                <div class="mb-4">
                    <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-green-700 mb-2">Receiving Berhasil Disimpan!</h3>
                <p class="text-gray-700 mb-6">Receiving ID: <strong>#${result.receiving_id}</strong></p>
                
                <div class="flex gap-3 justify-center">
                    <a href="/bahanbaku/receiving/${result.receiving_id}/labels-by-po" 
                       target="_blank"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Print Labels
                    </a>
                    <button 
                        onclick="window.location.reload()"
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Input Receiving Baru
                    </button>
                    <button 
                        onclick="window.location.href='{{ route('bahanbaku.receiving.index') }}'"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition inline-flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Tutup
                    </button>
                </div>
            `;
            
            formReceivingByPO.parentElement.appendChild(successDiv);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } else {
            console.error('Server returned error:', result.message);
            alert('Error: ' + (result.message || 'Gagal menyimpan data'));
        }
    } catch (error) {
        console.error('=== ERROR ===', error);
        alert('Terjadi kesalahan: ' + error.message);
    } finally {
        if (btnSubmitReceiving) {
            btnSubmitReceiving.disabled = false;
            btnSubmitReceiving.innerHTML = '<i class="fas fa-save mr-2"></i>Save Receiving';
        }
    }
}

// Global function untuk scan manpower QR
async function scanManpowerQR(e) {
    e.preventDefault();
    e.stopPropagation();
    console.log('scanManpowerQR called!');
    
    const manpowerInput = document.getElementById('manpowerInput');
    const manpowerInfo = document.getElementById('manpowerInfo');
    
    if (!manpowerInput) {
        console.error('manpowerInput not found!');
        return;
    }
    
    // Tampilkan loading
    if (manpowerInfo) {
        manpowerInfo.textContent = 'Mencari karyawan...';
        manpowerInfo.className = 'text-xs text-blue-600 mt-0.5 px-1';
    }
    
    const qrCode = prompt('Scan QR Code Karyawan atau masukkan QR Code:');
    if (!qrCode || !qrCode.trim()) {
        console.log('User cancelled or empty QR code');
        if (manpowerInfo) {
            manpowerInfo.textContent = '';
            manpowerInfo.className = 'text-xs mt-0.5 px-1';
        }
        return;
    }

    // Update loading message
    if (manpowerInfo) {
        manpowerInfo.textContent = 'Mencari karyawan...';
        manpowerInfo.className = 'text-xs text-blue-600 mt-0.5 px-1';
    }

    try {
        const url = `{{ route('bahanbaku.receiving.api.manpower.qr') }}?qrcode=${encodeURIComponent(qrCode.trim())}`;
        console.log('Fetching:', url);
        
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
            // Tampilkan error di bawah input
            if (manpowerInfo) {
                manpowerInfo.textContent = '❌ ' + (data.message || 'Karyawan tidak ditemukan');
                manpowerInfo.className = 'text-xs text-red-600 mt-0.5 px-1 font-medium';
            }
            manpowerInput.value = '';
            return;
        }

        // Set nilai manpower dengan nama karyawan
        const manpowerName = data.data.nama;
        if (data.data.nik) {
            manpowerInput.value = `${manpowerName} (${data.data.nik})`;
        } else {
            manpowerInput.value = manpowerName;
        }
        
        // Tampilkan info karyawan di bawah input
        if (manpowerInfo) {
            let infoText = `✓ ${data.data.nama}`;
            if (data.data.departemen) {
                infoText += ` | ${data.data.departemen}`;
            }
            if (data.data.bagian) {
                infoText += ` | ${data.data.bagian}`;
            }
            manpowerInfo.textContent = infoText;
            manpowerInfo.className = 'text-xs text-green-600 mt-0.5 px-1 font-medium';
        }

    } catch (error) {
        console.error('Error:', error);
        // Tampilkan error di bawah input
        if (manpowerInfo) {
            manpowerInfo.textContent = '❌ Terjadi error saat mencari karyawan: ' + error.message;
            manpowerInfo.className = 'text-xs text-red-600 mt-0.5 px-1 font-medium';
        }
        manpowerInput.value = '';
    }
}

// Format number (global function)
function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Helper to generate Internal Lot string
function generateInternalLotStr(itemName, qty, uom) {
    if(!qty) return '';
    const today = new Date();
    const dd = String(today.getDate()).padStart(2, '0');
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const yyyy = today.getFullYear();
    const dateStr = `${dd}${mm}${yyyy}`;
    
    // Heuristic: Check if name implies Box
    const isBox = (itemName || '').toUpperCase().includes('BOX');
    // Remove spaces from item name for cleaner lot
    const cleanItemName = (itemName || '').replace(/\s+/g, '');
    const cleanUom = (uom || '').replace(/\s+/g, '');

    let suffix = qty;
    if (!isBox && cleanUom) {
        suffix = `${qty}${cleanUom}`;
    }
    return `${dateStr}|${cleanItemName}|${suffix}`;
}

// Handler for Qty Change
function updateRowInternalLot(qtyInput, index, itemName, uom) {
   const row = qtyInput.closest('tr');
   const internalLotInput = row.querySelector(`input[name="items[${index}][internal_lot_number]"]`);
   const qty = qtyInput.value;
   if(internalLotInput) {
       internalLotInput.value = generateInternalLotStr(itemName, qty, uom);
   }
}

// Render schedule table (global function)
function renderScheduleTable(items) {
    const scheduleTableBody = document.getElementById('scheduleTableBody');
    
    if (!scheduleTableBody) {
        console.error('scheduleTableBody element not found!');
        return;
    }
    
    scheduleTableBody.innerHTML = '';
    
    items.forEach((item, index) => {
        const row = document.createElement('tr');
        row.className = 'border-b hover:bg-gray-50';
        
        row.innerHTML = `
            <td class="px-4 py-3 text-sm">${index + 1}</td>
            <td class="px-4 py-3 text-sm font-mono">${item.nomor_bahan_baku}</td>
            <td class="px-4 py-3 text-sm">${item.nama_bahan_baku}</td>
            <td class="px-4 py-3 text-sm">${item.tanggal_schedule_formatted}</td>
            <td class="px-4 py-3 text-sm text-right">${formatNumber(item.pc_plan)}</td>
            <td class="px-4 py-3 text-sm text-right">${formatNumber(item.pc_act)}</td>
            <td class="px-4 py-3 text-sm text-right font-semibold ${item.sisa > 0 ? 'text-orange-600' : 'text-green-600'}">
                ${formatNumber(item.sisa)}
            </td>
            <td class="px-4 py-3">
                <input 
                    type="number" 
                    name="items[${index}][qty]" 
                    step="0.001"
                    min="0"
                    max="${item.sisa}"
                    required
                    placeholder="0"
                    class="w-24 border border-gray-300 rounded px-2 py-1 text-right text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    oninput="updateRowInternalLot(this, ${index}, '${item.nama_bahan_baku}', '${item.uom || ''}')"
                />
                <input type="hidden" name="items[${index}][schedule_detail_id]" value="${item.schedule_detail_id}">
                <input type="hidden" name="items[${index}][nomor_bahan_baku]" value="${item.nomor_bahan_baku}">
                <input type="hidden" name="items[${index}][uom]" value="${item.uom}">
            </td>
            <td class="px-4 py-3">
                 <input 
                    type="text" 
                    name="items[${index}][internal_lot_number]" 
                    readonly
                    placeholder="Auto-generated"
                    class="w-48 border border-gray-200 bg-gray-50 text-gray-700 font-mono rounded px-2 py-1 text-xs"
                />
            </td>
            <td class="px-4 py-3">
                <input 
                    type="text" 
                    name="items[${index}][lot_number]" 
                    placeholder="LOT-XXX (Opsional)"
                    class="w-32 border border-gray-300 rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </td>
        `;
        
        scheduleTableBody.appendChild(row);
    });
}

// Global function untuk fetch schedule
async function fetchScheduleHandler(e) {
    e.preventDefault();
    console.log('fetchScheduleHandler called!');
    
    const poNumberInput = document.getElementById('poNumberInput');
    const btnFetchSchedule = document.getElementById('btnFetchSchedule');
    const scheduleSection = document.getElementById('scheduleSection');
    const supplierInfo = document.getElementById('supplierInfo');
    const supplierName = document.getElementById('supplierName');
    const scheduleTableBody = document.getElementById('scheduleTableBody');
    
    if (!poNumberInput) {
        alert('Error: Input PO Number tidak ditemukan');
        return;
    }
    
    const poNumber = poNumberInput.value.trim();
    
    if (!poNumber) {
        alert('Silakan masukkan PO Number');
        return;
    }

    btnFetchSchedule.disabled = true;
    btnFetchSchedule.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';

    try {
        const url = `{{ route('bahanbaku.receiving.api.scheduleByPO') }}?po_number=${encodeURIComponent(poNumber)}`;
        console.log('Fetching:', url);
        
        const response = await fetch(url);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Result:', result);

        if (result.success) {
            const scheduleData = result.data.items;
            console.log('Schedule items:', scheduleData);
            
            if (scheduleData.length === 0) {
                alert('Tidak ada schedule untuk PO Number ini');
                return;
            }
            
            // Show supplier info
            supplierInfo.classList.remove('hidden');
            supplierName.textContent = result.data.supplier.nama;
            
            // Set hidden fields
            document.getElementById('poNumberHidden').value = result.data.po_number;
            document.getElementById('supplierIdHidden').value = result.data.supplier.id;

            // Populate table
            renderScheduleTable(scheduleData);
            
            // Show schedule section
            scheduleSection.classList.remove('hidden');
            
        } else {
            alert(result.message || 'PO Number tidak ditemukan');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat fetch schedule: ' + error.message);
    } finally {
        btnFetchSchedule.disabled = false;
        btnFetchSchedule.innerHTML = 'Fetch Schedule';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing...');
    
    const btnFetchSchedule = document.getElementById('btnFetchSchedule');
    const poNumberInput = document.getElementById('poNumberInput');
    const scheduleSection = document.getElementById('scheduleSection');
    const supplierInfo = document.getElementById('supplierInfo');
    const supplierName = document.getElementById('supplierName');
    const scheduleTableBody = document.getElementById('scheduleTableBody');
    const formReceivingByPO = document.getElementById('formReceivingByPO');

    // Debug: Check if elements exist
    console.log('btnFetchSchedule:', btnFetchSchedule);
    console.log('poNumberInput:', poNumberInput);
    
    if (!btnFetchSchedule) {
        console.error('ERROR: btnFetchSchedule element not found!');
        alert('Error: Button tidak ditemukan. Silakan refresh halaman.');
        return;
    }
    
    if (!poNumberInput) {
        console.error('ERROR: poNumberInput element not found!');
        return;
    }

    let scheduleData = [];

    // Backup event listener (jika onclick tidak jalan)
    console.log('Attaching backup event listener to btnFetchSchedule...');
    btnFetchSchedule.addEventListener('click', async function(e) {
        e.preventDefault();
        console.log('Button clicked via addEventListener!');
        await fetchScheduleHandler(e);
    });
    
    console.log('Event listener attached successfully!');

    // Scan QR Code untuk Manpower
    const btnScanManpower = document.getElementById('btnScanManpower');
    const manpowerInput = document.getElementById('manpowerInput');
    const manpowerInfo = document.getElementById('manpowerInfo');

    // Clear info saat user input manual
    if (manpowerInput) {
        manpowerInput.addEventListener('input', function() {
            if (manpowerInfo.textContent && !manpowerInfo.classList.contains('text-gray-500')) {
                manpowerInfo.textContent = '';
                manpowerInfo.className = 'text-xs mt-0.5 px-1';
            }
        });
    }

    // Backup event listener (jika onclick tidak jalan)
    if (btnScanManpower && manpowerInput) {
        console.log('Attaching backup event listener for scan manpower...');
        btnScanManpower.addEventListener('click', async function(e) {
            e.preventDefault();
            console.log('Button clicked via addEventListener!');
            await scanManpowerQR(e);
        });
        console.log('Backup event listener attached!');
    } else {
        console.warn('btnScanManpower or manpowerInput not found!', {
            btnScanManpower: btnScanManpower,
            manpowerInput: manpowerInput
        });
    }

    // Submit form - BACKUP event listener
    const btnSubmitReceiving = document.getElementById('btnSubmitReceiving');
    
    console.log('=== SETTING UP SUBMIT BUTTON ===');
    console.log('btnSubmitReceiving element:', btnSubmitReceiving);
    
    if (btnSubmitReceiving) {
        btnSubmitReceiving.addEventListener('click', async function(e) {
            console.log('BACKUP: Button clicked via addEventListener');
            await handleSubmitReceiving(e);
        });
        console.log('Backup event listener attached to submit button!');
    } else {
        console.error('btnSubmitReceiving not found!');
    }
    
    // Auto-select Shift based on Time (WIB/Server Time approx)
    const shiftSelect = document.querySelector('select[name="shift"]');
    if (shiftSelect) {
        const hour = new Date().getHours();
        // Shift 1: 07:00 - 15:00
        // Shift 2: 15:00 - 23:00
        // Shift 3: 23:00 - 07:00
        if (hour >= 7 && hour < 15) {
            shiftSelect.value = '1';
        } else if (hour >= 15 && hour < 23) {
            shiftSelect.value = '2';
        } else {
            shiftSelect.value = '3';
        }
        console.log('Auto-selected shift:', shiftSelect.value, 'Hour:', hour);
    }

    console.log('=== ALL EVENT LISTENERS ATTACHED SUCCESSFULLY ===');
    console.log('formReceivingByPO:', formReceivingByPO);
    console.log('btnFetchSchedule:', btnFetchSchedule);
    console.log('btnScanManpower:', btnScanManpower);
});
</script>
@endsection
