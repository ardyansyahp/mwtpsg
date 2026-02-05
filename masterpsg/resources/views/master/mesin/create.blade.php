@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('master.mesin.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Tambah Mesin</h2>
        <p class="text-gray-600 mt-1">Tambah data mesin baru</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createForm" class="space-y-6">
            @csrf

            {{-- Grid untuk semua field --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="no_mesin" class="block text-sm font-medium text-gray-700 mb-2">
                        No Mesin <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        id="no_mesin" 
                        name="no_mesin" 
                        required
                        maxlength="50"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: MC-01"
                    >
                </div>

                <div>
                    <label for="merk_mesin" class="block text-sm font-medium text-gray-700 mb-2">
                        Merk Mesin
                    </label>
                    <input 
                        type="text" 
                        id="merk_mesin" 
                        name="merk_mesin" 
                        maxlength="100"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: Sumitomo"
                    >
                </div>

                <div>
                    <label for="tonase" class="block text-sm font-medium text-gray-700 mb-2">
                        Tonase
                    </label>
                    <input 
                        type="number" 
                        id="tonase" 
                        name="tonase" 
                        min="0"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Contoh: 180"
                    >
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan</span>
                </button>
                <a 
                    href="{{ route('master.mesin.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menyimpan...</span>';

    try {
        const response = await fetch('{{ route("master.mesin.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("master.mesin.index") }}';
        } else {
            alert('Error: ' + (data.message || 'Gagal menyimpan data'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
@endsection

