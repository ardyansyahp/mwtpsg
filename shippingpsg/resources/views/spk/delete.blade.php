@extends('layout.app')

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
            <h2 class="text-3xl font-bold text-gray-800">Hapus Surat Perintah Pengiriman</h2>
            <p class="text-gray-600 mt-1">Konfirmasi penghapusan SPK</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-red-200 p-6">
        <div class="flex items-start gap-4 mb-6">
            <div class="flex-shrink-0">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Penghapusan</h3>
                <p class="text-sm text-gray-600 mb-4">
                    Anda yakin ingin menghapus SPK <strong>{{ $spk->nomor_spk }}</strong>? 
                    Tindakan ini tidak dapat dibatalkan dan akan menghapus semua detail part yang terkait.
                </p>
            </div>
        </div>

        {{-- Info SPK yang akan dihapus --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-medium text-gray-700 mb-3">Informasi SPK:</h4>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-600">Nomor SPK:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $spk->nomor_spk }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Tanggal:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ optional($spk->tanggal)->format('d/m/Y') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Customer:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $spk->customer->nama_perusahaan ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Jumlah Detail:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $spk->details->count() }} item</span>
                </div>
            </div>
        </div>

        <form id="deleteSPKForm">
            @csrf
            @method('DELETE')

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('spk.index') }}" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg"
                >
                    Batal
                </a>
                <button 
                    type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Hapus SPK
                </button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('deleteSPKForm');
    
    form?.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (!confirm('Apakah Anda yakin ingin menghapus SPK ini? Tindakan ini tidak dapat dibatalkan.')) {
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'DELETE');
            
            const response = await fetch('{{ route('spk.destroy', $spk->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message || 'SPK berhasil dihapus');
                window.location.href = "{{ route('spk.index') }}";
            } else {
                alert(data.message || 'Gagal menghapus SPK');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus SPK');
        }
    });
})();
</script>
@endsection
