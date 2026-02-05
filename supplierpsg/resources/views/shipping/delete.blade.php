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
            <h2 class="text-3xl font-bold text-gray-800">Hapus Delivery</h2>
            <p class="text-gray-600 mt-1">Konfirmasi penghapusan data delivery</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-sm font-medium text-red-800">Peringatan!</span>
                </div>
                <p class="text-sm text-red-700">Anda akan menghapus data delivery ini. Tindakan ini tidak dapat dibatalkan.</p>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Truck</label>
                    <div class="text-sm text-gray-800 font-mono">
                        {{ $delivery->kendaraan ? $delivery->kendaraan->nopol_kendaraan : '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Driver</label>
                    <div class="text-sm text-gray-800">
                        {{ $delivery->driver ? $delivery->driver->nama : '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Destination</label>
                    <div class="text-sm text-gray-800">
                        {{ $delivery->destination ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Berangkat</label>
                    <div class="text-sm text-gray-800">
                        {{ $delivery->tanggal_berangkat ? $delivery->tanggal_berangkat->format('d/m/Y H:i') : '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                    <div class="text-sm text-gray-800">
                        {{ str_replace('_', ' ', strtoupper($delivery->status ?? 'OPEN')) }}
                    </div>
                </div>

                @if($delivery->keterangan)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Keterangan</label>
                    <div class="text-sm text-gray-800">
                        {{ $delivery->keterangan }}
                    </div>
                </div>
                @endif
            </div>
        </div>

        <form id="deleteDeliveryForm">
            @csrf
            @method('DELETE')

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    id="btnSubmit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Hapus
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
    const form = document.getElementById('deleteDeliveryForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const deliveryId = {{ $delivery->id }};

    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!confirm('Apakah Anda yakin ingin menghapus data delivery ini?')) {
            return;
        }

        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Menghapus...';
        }

        const formData = new FormData(form);
        
        try {
            const response = await fetch(`/shipping/delivery/${deliveryId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-HTTP-Method-Override': 'DELETE'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert('Data delivery berhasil dihapus');
                window.location.href = '{{ route("shipping.delivery.index") }}';
            } else {
                alert('Gagal menghapus: ' + (data.message || 'Unknown error'));
                if (btnSubmit) {
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = 'Hapus';
                }
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Gagal menghapus: ' + error.message);
            if (btnSubmit) {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Hapus';
            }
        }
    });
})();
</script>
@endsection
