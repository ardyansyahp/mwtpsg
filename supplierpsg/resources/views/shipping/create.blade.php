@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('shipping.delivery.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Tambah Delivery</h2>
            <p class="text-gray-600 mt-1">Scan QR Truck dan Driver untuk membuat delivery baru</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createDeliveryForm" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Truck Scan --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Scan QR Truck <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="truckScanInput"
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono" 
                            placeholder="Scan QR Kendaraan..."
                            autofocus
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                            </svg>
                        </div>
                        <input type="hidden" name="kendaraan_id" id="kendaraanId">
                    </div>
                    {{-- Selected Truck Info --}}
                    <div id="truckInfo" class="mt-2 hidden p-3 bg-blue-50 rounded-lg border border-blue-100 flex items-start gap-3">
                        <div class="bg-blue-100 p-2 rounded-full text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800" id="truckNopolText"></p>
                            <p class="text-sm text-gray-600" id="truckDescText"></p>
                        </div>
                        <button type="button" id="clearTruck" class="ml-auto text-gray-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Driver Scan --}}
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Scan ID Card Driver
                    </label>
                    <div class="relative">
                        <input 
                            type="text" 
                            id="driverScanInput"
                            class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono" 
                            placeholder="Scan ID/Barcode Driver..."
                            autocomplete="off"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0c0 .884.39 1.676 1 2.222V19" />
                            </svg>
                        </div>
                        <input type="hidden" name="driver_id" id="driverId">
                    </div>
                    {{-- Selected Driver Info --}}
                    <div id="driverInfo" class="mt-2 hidden p-3 bg-green-50 rounded-lg border border-green-100 flex items-start gap-3">
                        <div class="bg-green-100 p-2 rounded-full text-green-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <div>
                            <p class="font-semibold text-gray-800" id="driverNameText"></p>
                            <p class="text-sm text-gray-600" id="driverDescText"></p>
                        </div>
                        <button type="button" id="clearDriver" class="ml-auto text-gray-400 hover:text-red-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                {{-- No Surat Jalan --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        No Surat Jalan
                    </label>
                    <input 
                        type="text" 
                        name="no_surat_jalan" 
                        id="noSuratJalan"
                        class="w-full px-4 py-2 border border-blue-200 bg-blue-50 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-medium" 
                        placeholder="Scan/Input Nomor Surat Jalan"
                    >
                    <p class="text-xs text-gray-500 mt-1">Input dari Control Truck</p>
                </div>
            </div>

            {{-- Keterangan --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Keterangan
                </label>
                <textarea 
                    name="keterangan" 
                    id="keterangan"
                    rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                    placeholder="Keterangan tambahan (opsional)"
                ></textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    id="btnSubmit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors font-medium shadow-sm w-full md:w-auto flex justify-center"
                >
                    Simpan Delivery
                </button>
                <a href="{{ route('shipping.delivery.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg w-full md:w-auto text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const trucks = @json($trucks);
    const drivers = @json($drivers);
    
    // Truck Scanning Logic
    const truckInput = document.getElementById('truckScanInput');
    const truckIdField = document.getElementById('kendaraanId');
    const truckInfo = document.getElementById('truckInfo');
    const truckNopolText = document.getElementById('truckNopolText');
    const truckDescText = document.getElementById('truckDescText');
    const clearTruckBtn = document.getElementById('clearTruck');

    function selectTruck(truck) {
        if (!truck) return;
        truckIdField.value = truck.id;
        truckNopolText.textContent = truck.nopol_kendaraan;
        truckDescText.textContent = truck.jenis_kendaraan;
        truckInfo.classList.remove('hidden');
        truckInput.value = ''; // Clear input for next scan if needed, or keep it? usually clear.
        truckInput.parentElement.classList.add('hidden'); // Hide input when selected? Or just hide input
        
        // Focus next field
        document.getElementById('driverScanInput')?.focus();
    }

    function resetTruck() {
        truckIdField.value = '';
        truckInfo.classList.add('hidden');
        truckInput.parentElement.classList.remove('hidden');
        truckInput.value = '';
        truckInput.focus();
    }

    truckInput?.addEventListener('change', function(e) {
        const val = e.target.value.trim().toUpperCase();
        if (!val) return;

        // Try to find by ID or Nopol
        const truck = trucks.find(t => 
            t.id.toString() === val || 
            t.nopol_kendaraan.replace(/\s/g, '').toUpperCase().includes(val.replace(/\s/g, ''))
        );

        if (truck) {
            selectTruck(truck);
        } else {
            alert('Truck tidak ditemukan!');
            e.target.value = '';
        }
    });

    truckInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            truckInput.blur(); // Trigger change
        }
    });

    clearTruckBtn?.addEventListener('click', resetTruck);


    // Driver Scanning Logic
    const driverInput = document.getElementById('driverScanInput');
    const driverIdField = document.getElementById('driverId');
    const driverInfo = document.getElementById('driverInfo');
    const driverNameText = document.getElementById('driverNameText');
    const driverDescText = document.getElementById('driverDescText');
    const clearDriverBtn = document.getElementById('clearDriver');

    function selectDriver(driver) {
        if (!driver) return;
        driverIdField.value = driver.id;
        driverNameText.textContent = driver.nama;
        // driverDescText.textContent = driver.bagian; // Assuming 'bagian' exists or just empty
        driverInfo.classList.remove('hidden');
        driverInput.value = '';
        driverInput.parentElement.classList.add('hidden');

        // Focus next field
        document.getElementById('noSuratJalan')?.focus();
    }

    function resetDriver() {
        driverIdField.value = '';
        driverInfo.classList.add('hidden');
        driverInput.parentElement.classList.remove('hidden');
        driverInput.value = '';
        driverInput.focus();
    }

    driverInput?.addEventListener('change', function(e) {
        const val = e.target.value.trim().toUpperCase();
        if (!val) return;

        // Try to find by ID, Name, NIK, or QRCode
        const driver = drivers.find(d => 
            d.id.toString() === val || 
            d.nama.toUpperCase().includes(val) ||
            (d.nik && d.nik.toString() === val) ||
            (d.qrcode && d.qrcode.toString() === val)
        );

        if (driver) {
            selectDriver(driver);
        } else {
            alert('Driver tidak ditemukan!');
            e.target.value = '';
        }
    });

    driverInput?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            driverInput.blur();
        }
    });

    clearDriverBtn?.addEventListener('click', resetDriver);


    // Form Submission
    const form = document.getElementById('createDeliveryForm');
    const btnSubmit = document.getElementById('btnSubmit');

    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!truckIdField.value) {
            alert('Silakan scan/pilih Truck terlebih dahulu');
            resetTruck();
            return;
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Menyimpan...';
        }

        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("shipping.delivery.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Data delivery berhasil ditambahkan');
                window.location.href = '{{ route("shipping.delivery.index") }}';
            } else {
                alert('Gagal menyimpan: ' + (data.message || 'Unknown error'));
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Simpan Delivery';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal menyimpan: ' + error.message);
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Simpan Delivery';
            }
        }
    });
})();
</script>
@endsection

