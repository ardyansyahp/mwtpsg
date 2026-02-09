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

        <h2 class="text-xl font-bold text-gray-900 leading-none">Hapus Mesin</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Konfirmasi penghapusan data mesin</p>
    </div>

    {{-- Confirmation Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">Apakah Anda yakin?</h3>
            <p class="text-sm text-gray-600 text-center mb-4">
                Data mesin <strong>{{ $mesin->no_mesin }}</strong> akan dipindahkan ke Recycle Bin.
            </p>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700"><strong>No Mesin:</strong> {{ $mesin->no_mesin }}</p>
                @if($mesin->merk_mesin)
                    <p class="text-sm text-gray-700 mt-1"><strong>Merk:</strong> {{ $mesin->merk_mesin }}</p>
                @endif
                @if(!is_null($mesin->tonase))
                    <p class="text-sm text-gray-700 mt-1"><strong>Tonase:</strong> {{ $mesin->tonase }}</p>
                @endif
            </div>
            <p class="text-xs text-gray-500 text-center">Anda dapat memulihkannya kembali jika diperlukan.</p>
        </div>

        <form id="deleteForm" class="flex items-center justify-center gap-4">
            @csrf
            @method('DELETE')
            <input type="hidden" id="mesin_id" value="{{ $mesin->id }}">

            <button 
                type="submit" 
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Ya, Hapus</span>
            </button>
            <a 
                href="{{ route('master.mesin.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors"
            >
                Batal
            </a>
        </form>
    </div>
</div>

<script>
document.getElementById('deleteForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }

    const mesinId = document.getElementById('mesin_id').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menghapus...</span>';

    try {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        // Route is defined as POST, so we don't strictly need _method DELETE unless we want to be semantic, 
        // but the route definition is Route::post('/{mesin}/destroy', ...).
        // Let's us POST directly.
        
        const response = await fetch(`{{ url('master/mesin') }}/${mesinId}/destroy`, {
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
            alert('Error: ' + (data.message || 'Gagal menghapus data'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus data');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
@endsection
