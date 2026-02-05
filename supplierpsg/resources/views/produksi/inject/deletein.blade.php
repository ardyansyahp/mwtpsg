@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 max-w-md mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <div class="flex-shrink-0">
                <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Hapus Scan In</h2>
                <p class="text-gray-600 mt-1">Apakah Anda yakin ingin menghapus data ini?</p>
            </div>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Lot Number:</span>
                    <span class="font-mono font-medium text-gray-800">{{ $injectIn->lot_number ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Waktu Scan:</span>
                    <span class="font-medium text-gray-800">{{ optional($injectIn->waktu_scan)->format('Y-m-d H:i:s') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Mesin:</span>
                    <span class="font-medium text-gray-800">{{ $injectIn->mesin->no_mesin ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Manpower:</span>
                    <span class="font-medium text-gray-800">{{ $injectIn->manpower ?? '-' }}</span>
                </div>
            </div>
        </div>

        <form id="deleteInjectInForm">
            @csrf
            @method('DELETE')

            <div class="flex items-center gap-3">
                <button 
                    type="submit" 
                    class="flex-1 bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Ya, Hapus
                </button>
                <a href="{{ route('produksi.inject.index') }}" 
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-center"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('deleteInjectInForm');

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (!confirm('Data yang dihapus tidak dapat dikembalikan. Yakin ingin melanjutkan?')) {
            return;
        }

        const formData = new FormData(form);
        formData.append('_method', 'DELETE');

        try {
            const response = await fetch('{{ route("produksi.inject.destroyin", $injectIn->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Data berhasil dihapus');
                window.location.href = "{{ route('produksi.inject.index') }}";
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
