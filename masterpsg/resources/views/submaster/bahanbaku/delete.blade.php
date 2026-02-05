@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Hapus Bahan Baku</h2>
        <p class="text-gray-600 mt-1">Konfirmasi penghapusan data</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-4">
            <p class="text-gray-700">Yakin mau hapus bahan baku ini?</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <div><span class="text-gray-500">Kategori:</span> <span class="text-gray-900 font-medium">{{ strtoupper($bahanbaku->kategori) }}</span></div>
                <div><span class="text-gray-500">Supplier:</span> <span class="text-gray-900 font-medium">{{ $bahanbaku->supplier->nama_perusahaan ?? '-' }}</span></div>
                <div><span class="text-gray-500">Nomor BB:</span> <span class="text-gray-900 font-medium">{{ $bahanbaku->nomor_bahan_baku ?? '-' }}</span></div>
                <div class="md:col-span-2"><span class="text-gray-500">Nama:</span> <span class="text-gray-900 font-medium">{{ $bahanbaku->nama_bahan_baku }}</span></div>
            </div>
        </div>

        <form id="formDeleteBahanBaku" class="mt-6">
            @csrf
            @method('DELETE')

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('master.bahanbaku.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 flex items-center justify-center">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('formDeleteBahanBaku');
    const btnCancel = document.getElementById('btnCancel');

    if (btnCancel && !btnCancel.hasAttribute('data-handler-attached')) {
        btnCancel.setAttribute('data-handler-attached', 'true');
        
    }

    if (form && !form.hasAttribute('data-handler-attached')) {
        form.setAttribute('data-handler-attached', 'true');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const response = await fetch(`/submaster/bahanbaku/{{ $bahanbaku->id }}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    alert(data.message || 'Gagal hapus data');
                    return;
                }

                alert(data.message || 'Berhasil hapus');

                window.location.href = "{{ route('master.bahanbaku.index') }}";
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }
})();
</script>
@endsection
