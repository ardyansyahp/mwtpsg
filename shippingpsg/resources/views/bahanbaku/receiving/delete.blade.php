@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Hapus Receiving</h2>
        <p class="text-gray-600 mt-1">Konfirmasi penghapusan data</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-4">
            <p class="text-gray-700">Yakin mau hapus receiving ini?</p>
        </div>

        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                <div><span class="text-gray-500">Tanggal:</span> <span class="text-gray-900 font-medium">{{ optional($receiving->tanggal_receiving)->format('Y-m-d') }}</span></div>
                <div><span class="text-gray-500">Supplier:</span> <span class="text-gray-900 font-medium">{{ $receiving->supplier->nama_perusahaan ?? '-' }}</span></div>
                <div><span class="text-gray-500">No Surat Jalan:</span> <span class="text-gray-900 font-medium">{{ $receiving->no_surat_jalan ?? '-' }}</span></div>
                <div><span class="text-gray-500">No PO:</span> <span class="text-gray-900 font-medium">{{ $receiving->no_purchase_order ?? '-' }}</span></div>
                <div><span class="text-gray-500">Manpower:</span> <span class="text-gray-900 font-medium">{{ $receiving->manpower ?? '-' }}</span></div>
                <div><span class="text-gray-500">Shift:</span> <span class="text-gray-900 font-medium">{{ $receiving->shift ?? '-' }}</span></div>
                <div class="md:col-span-2"><span class="text-gray-500">Jumlah Detail:</span> <span class="text-gray-900 font-medium">{{ $receiving->details_count ?? 0 }}</span></div>
            </div>
        </div>

        <form id="formDeleteReceiving" class="mt-6">
            @csrf
            @method('DELETE')

            <div class="flex items-center justify-end gap-2">
                <a href="{{ route('bahanbaku.receiving.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Batal</a>
                <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 text-white hover:bg-red-700">Hapus</button>
            </div>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('formDeleteReceiving');
    const btnCancel = document.getElementById('btnCancel');

    if (btnCancel && !btnCancel.hasAttribute('data-handler-attached')) {
        btnCancel.setAttribute('data-handler-attached', 'true');
        btnCancel.addEventListener('click', function() {
            window.location.href = '{{ route("bahanbaku.receiving.index") }}';
        });
    }

    if (form && !form.hasAttribute('data-handler-attached')) {
        form.setAttribute('data-handler-attached', 'true');
        form.addEventListener('submit', async function(e) {
            e.preventDefault();

            try {
                const response = await fetch(`/bahanbaku/receiving/{{ $receiving->id }}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: new URLSearchParams({ _method: 'DELETE' })
                });

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    alert(data.message || 'Gagal hapus data');
                    return;
                }

                alert(data.message || 'Berhasil hapus');
                window.location.href = '{{ route("bahanbaku.receiving.index") }}';
            } catch (error) {
                console.error(error);
                alert('Terjadi error: ' + error.message);
            }
        });
    }
})();
</script>
@endsection
