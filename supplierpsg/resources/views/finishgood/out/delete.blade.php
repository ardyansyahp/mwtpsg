@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a 
                href="{{ route('finishgood.out.index') }}" 
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
                title="Kembali"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Hapus Finish Good Out</h2>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <h3 class="text-lg font-semibold text-red-800">Konfirmasi Penghapusan</h3>
            </div>
            <p class="text-sm text-red-700">Anda yakin ingin menghapus data Finish Good Out ini? Tindakan ini tidak dapat dibatalkan.</p>
        </div>

        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 mb-6">
            <h4 class="text-md font-semibold text-gray-800 mb-4">Informasi Data</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                    <div class="text-sm font-mono text-gray-800">{{ $finishGoodOut->lot_number ?? '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->part ? $finishGoodOut->part->nomor_part . ' - ' . $finishGoodOut->part->nama_part : '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">SPK</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk ? $finishGoodOut->spk->nomor_spk : '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Customer</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk && $finishGoodOut->spk->customer ? $finishGoodOut->spk->customer->nama_perusahaan : '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan Out</label>
                    <div class="text-sm text-gray-800">{{ optional($finishGoodOut->waktu_scan_out)->format('Y-m-d H:i:s') }}</div>
                </div>
            </div>
        </div>

        <form id="deleteFinishGoodOutForm">
            @csrf
            @method('DELETE')
            
            <div class="flex items-center gap-3">
                <button 
                    type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Ya, Hapus
                </button>
                <a 
                    href="{{ route('finishgood.out.index') }}" 
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
    const form = document.getElementById('deleteFinishGoodOutForm');
    
    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!confirm('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.')) {
            return;
        }

        try {
            const response = await fetch('{{ route("finishgood.out.destroy", $finishGoodOut->id) }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-HTTP-Method-Override': 'DELETE',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                   document.querySelector('input[name="_token"]')?.value
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Data berhasil dihapus');
                window.location.href = '{{ route("finishgood.out.index") }}';
            } else {
                alert(data.message || 'Gagal menghapus data');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus data');
        }
    });
})();
</script>
@endsection
