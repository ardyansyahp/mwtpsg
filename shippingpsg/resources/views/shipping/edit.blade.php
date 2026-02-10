@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a 
                href="{{ route('shipping.delivery.index') }}" 
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
                title="Kembali"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Edit Delivery</h2>
            <p class="text-gray-600 mt-1">Edit data delivery</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editDeliveryForm" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Truck --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Truck <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="kendaraan_id" 
                        id="kendaraanId"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Pilih Truck --</option>
                        @foreach($trucks as $truck)
                            <option value="{{ $truck->id }}" {{ $delivery->kendaraan_id == $truck->id ? 'selected' : '' }}>
                                {{ $truck->nopol_kendaraan }} - {{ $truck->jenis_kendaraan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Driver --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Driver
                    </label>
                    <select 
                        name="driver_id" 
                        id="driverId"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="">-- Pilih Driver --</option>
                        @foreach($drivers as $driver)
                            <option value="{{ $driver->id }}" {{ $delivery->driver_id == $driver->id ? 'selected' : '' }}>
                                {{ $driver->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Destination --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Destination <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="destination" 
                        id="destination"
                        required
                        value="{{ $delivery->destination }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Masukkan tujuan delivery"
                    >
                </div>

                {{-- No Surat Jalan --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        No Surat Jalan
                    </label>
                    <input 
                        type="text" 
                        name="no_surat_jalan" 
                        id="noSuratJalan"
                        value="{{ $delivery->no_surat_jalan }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" 
                        placeholder="Nomor surat jalan (opsional)"
                    >
                </div>

                {{-- Tanggal Berangkat --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Berangkat <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="tanggal_berangkat" 
                        id="tanggalBerangkat"
                        required
                        value="{{ $delivery->tanggal_berangkat ? $delivery->tanggal_berangkat->format('Y-m-d') : '' }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                {{-- Waktu Berangkat --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Berangkat
                    </label>
                    <input 
                        type="datetime-local" 
                        name="waktu_berangkat" 
                        id="waktuBerangkat"
                        value="{{ $delivery->waktu_berangkat ? $delivery->waktu_berangkat->format('Y-m-d\TH:i') : '' }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                {{-- Waktu Tiba --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Tiba
                    </label>
                    <input 
                        type="datetime-local" 
                        name="waktu_tiba" 
                        id="waktuTiba"
                        value="{{ $delivery->waktu_tiba ? $delivery->waktu_tiba->format('Y-m-d\TH:i') : '' }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="status" 
                        id="status"
                        required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="OPEN" {{ $delivery->status == 'OPEN' ? 'selected' : '' }}>OPEN</option>
                        <option value="IN_TRANSIT" {{ $delivery->status == 'IN_TRANSIT' ? 'selected' : '' }}>IN_TRANSIT</option>
                        <option value="ARRIVED" {{ $delivery->status == 'ARRIVED' ? 'selected' : '' }}>ARRIVED</option>
                        <option value="DELIVERED" {{ $delivery->status == 'DELIVERED' ? 'selected' : '' }}>DELIVERED</option>
                        <option value="CANCELLED" {{ $delivery->status == 'CANCELLED' ? 'selected' : '' }}>CANCELLED</option>
                    </select>
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
                >{{ $delivery->keterangan }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    id="btnSubmit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Update
                </button>
                <a 
                    href="{{ route('shipping.delivery.index') }}" 
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
    const form = document.getElementById('editDeliveryForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const deliveryId = {{ $delivery->id }};

    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Mengupdate...';
        }

        const formData = new FormData(form);
        
        try {
            const response = await fetch(`{{ url('shipping/delivery') }}/${deliveryId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-HTTP-Method-Override': 'PUT'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Data delivery berhasil diupdate');
                window.location.href = '{{ route("shipping.delivery.index") }}';
            } else {
                alert('Gagal mengupdate: ' + (data.message || 'Unknown error'));
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Update';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal mengupdate: ' + error.message);
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Update';
            }
        }
    });
})();
</script>
@endsection
