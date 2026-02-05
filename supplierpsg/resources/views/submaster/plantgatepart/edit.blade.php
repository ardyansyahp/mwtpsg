@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <a href="{{ route('submaster.plantgatepart.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors" title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Edit Plant Gate Part</h2>
        <p class="text-gray-600 mt-1">Kelola relasi plant gate dengan part</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editForm" class="space-y-6">
            @csrf
            <input type="hidden" id="plantgate_part_id" value="{{ $plantgatePart->id }}">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Gate <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-60 overflow-y-auto bg-gray-50">
                        <div class="space-y-2">
                            @foreach($plantgates as $plantgate)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input 
                                        id="gate_{{ $plantgate->id }}" 
                                        name="plantgate_ids[]" 
                                        type="checkbox" 
                                        value="{{ $plantgate->id }}"
                                        {{ in_array($plantgate->id, $currentGateIds) ? 'checked' : '' }}
                                        class="gate-checkbox focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="gate_{{ $plantgate->id }}" class="font-medium text-gray-700 cursor-pointer select-none">
                                        {{ $plantgate->nama_plantgate }} 
                                        <span class="text-gray-500 font-normal">- {{ $plantgate->customer->nama_perusahaan ?? '-' }}</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Centang semua gate yang sesuai untuk part ini</p>
                </div>

                <div>
                    <label for="part_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Part <span class="text-red-500">*</span>
                    </label>
                    <div class="relative part-autocomplete-wrapper">
                        <input 
                            type="text" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent opacity-75 cursor-not-allowed" 
                            placeholder="Ketik nomor part atau nama part..." 
                            value="{{ $plantgatePart->part->nomor_part }} - {{ $plantgatePart->part->nama_part }}"
                            readonly
                        >
                        <input type="hidden" name="part_id" value="{{ $plantgatePart->part_id }}" required>
                        <p class="mt-2 text-xs text-amber-600 italic">Nomor part tidak dapat diubah pada mode edit relasi</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Perubahan</span>
                </button>
                <a href="{{ route('submaster.plantgatepart.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const plantgatePartId = document.getElementById('plantgate_part_id').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    // Validasi minimal 1 gate
    const checkedGates = this.querySelectorAll('input[name="plantgate_ids[]"]:checked');
    if (checkedGates.length === 0) {
        alert('Pilih minimal satu gate!');
        return;
    }

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menyimpan...</span>';

    try {
        formData.append('_method', 'PUT');
        const response = await fetch(`/submaster/plantgatepart/${plantgatePartId}`, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("submaster.plantgatepart.index") }}';
        } else {
            alert('Error: ' + (data.message || 'Gagal mengupdate data'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat mengupdate data');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
@endsection
