@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('master.manpower.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Hapus Manpower</h2>
        <p class="text-gray-600 mt-1">Konfirmasi penghapusan data manpower</p>
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
                Data manpower <strong>{{ $manpower->nama }}</strong> akan dihapus secara permanen.
            </p>
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700"><strong>MP ID:</strong> {{ $manpower->mp_id ?? '-' }}</p>
                <p class="text-sm text-gray-700 mt-1"><strong>NIK:</strong> {{ $manpower->nik ?? '-' }}</p>
                <p class="text-sm text-gray-700 mt-1"><strong>Nama:</strong> {{ $manpower->nama }}</p>
                <p class="text-sm text-gray-700 mt-1"><strong>Departemen:</strong> {{ $manpower->departemen ?? '-' }}</p>
                <p class="text-sm text-gray-700 mt-1"><strong>Bagian:</strong> {{ $manpower->bagian ?? '-' }}</p>
            </div>
            <p class="text-xs text-red-600 text-center">Tindakan ini tidak dapat dibatalkan.</p>
        </div>

        <form id="deleteForm" class="flex items-center justify-center gap-4">
            @csrf
            @method('DELETE')
            <input type="hidden" id="manpower_id" value="{{ $manpower->id }}">

            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Ya, Hapus</span>
            </button>
            <a href="{{ route('master.manpower.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">Batal</a>
        </form>
    </div>
</div>

<script>
document.getElementById('deleteForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    if (!confirm('Apakah Anda yakin ingin menghapus data ini?')) {
        return;
    }

    const manpowerId = document.getElementById('manpower_id').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menghapus...</span>';

    try {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('_method', 'DELETE');

        const response = await fetch(`/master/manpower/${manpowerId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("master.manpower.index") }}';
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

