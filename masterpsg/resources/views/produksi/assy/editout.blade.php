@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route('produksi.assy.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            <span class="font-medium">Kembali</span>
        </a>
            <h2 class="text-3xl font-bold text-gray-800">Edit ASSY Out</h2>
            <p class="text-gray-600 mt-1">Edit data ASSY scan out</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editAssyOutForm" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lot Number</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono text-gray-700">
                        {{ $assyOut->lot_number ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Scan Out</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                        {{ optional($assyOut->waktu_scan)->format('Y-m-d H:i:s') }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Part</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                        @if($assyOut->part)
                            {{ $assyOut->part->nomor_part }} - {{ $assyOut->part->nama_part }}
                        @elseif($assyOut->assyIn && $assyOut->assyIn->part)
                            {{ $assyOut->assyIn->part->nomor_part }} - {{ $assyOut->assyIn->part->nama_part }}
                        @else
                            -
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Scan In</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                        {{ $assyOut->assyIn ? optional($assyOut->assyIn->waktu_scan)->format('Y-m-d H:i:s') : '-' }}
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea 
                    name="catatan" 
                    rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >{{ $assyOut->catatan ?? '' }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Simpan Perubahan
                </button>
                <a href="{{ route('produksi.assy.index') }}" 
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('editAssyOutForm');

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('_method', 'PUT');

        try {
            const response = await fetch('{{ route("produksi.assy.updateout", $assyOut->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Data berhasil diperbarui');
                window.location.href = "{{ route('produksi.assy.index') }}";
            } else {
                alert(data.message || 'Gagal memperbarui data');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat memperbarui data');
        }
    });
})();
</script>
@endsection
