@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route(\'dashboard\') }}"\1>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

            <h2 class="text-3xl font-bold text-gray-800">Edit Scan In</h2>
            <p class="text-gray-600 mt-1">Edit data scan in label box</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editWipInForm" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lot Number</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm font-mono text-gray-700">
                        {{ $wipIn->lot_number ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Waktu Scan In</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                        {{ optional($wipIn->waktu_scan_in)->format('Y-m-d H:i:s') }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Box Number</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                        Box #{{ $wipIn->box_number ?? '-' }}
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Planning Run</label>
                    <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                        #{{ $wipIn->planning_run_id ?? '-' }}
                    </div>
                </div>

                @if($wipIn->planningRun && $wipIn->planningRun->mold && $wipIn->planningRun->mold->part)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Part</label>
                        <div class="px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-sm text-gray-700">
                            {{ $wipIn->planningRun->mold->part->nomor_part ?? '-' }}
                        </div>
                    </div>
                @endif
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                <textarea 
                    name="catatan" 
                    rows="3" 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >{{ $wipIn->catatan ?? '' }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors"
                >
                    Simpan Perubahan
                </a>
                <a href="{{ route(\'dashboard\') }}" 
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
    const form = document.getElementById('editWipInForm');

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        formData.append('_method', 'PUT');

        try {
            const response = await fetch('{{ route("produksi.wip.updatein", $wipIn->id) }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                alert(data.message || 'Data berhasil diperbarui');
                window.location.reload();
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
