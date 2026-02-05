@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('master.mold.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Edit Mold</h2>
        <p class="text-gray-600 mt-1">Edit data mold</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editForm" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="mold_id_hidden" value="{{ $mold->id }}">

            <div class="space-y-6">
                {{-- Row 1: Perusahaan & Part --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="perusahaan_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Perusahaan <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="perusahaan_id"
                            name="perusahaan_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Perusahaan</option>
                            @foreach($perusahaans as $p)
                                <option value="{{ $p->id }}" @selected($mold->perusahaan_id == $p->id)>
                                    {{ $p->nama_perusahaan }}{{ $p->inisial_perusahaan ? ' (' . $p->inisial_perusahaan . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="part_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Part <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="part_id"
                            name="part_id"
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Part</option>
                            @foreach($parts as $part)
                                <option value="{{ $part->id }}" @selected($mold->part_id == $part->id)>
                                    {{ $part->nomor_part }} - {{ $part->nama_part }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Row 2: Kode Mold, Nomor Mold, Cavity, Cycle Time --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="kode_mold" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Mold
                        </label>
                        <input
                            type="text"
                            id="kode_mold"
                            name="kode_mold"
                            maxlength="100"
                            value="{{ $mold->kode_mold }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Kode Mold"
                        >
                    </div>

                    <div>
                        <label for="nomor_mold" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Mold
                        </label>
                        <input
                            type="text"
                            id="nomor_mold"
                            name="nomor_mold"
                            maxlength="50"
                            value="{{ $mold->nomor_mold }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Nomor Mold"
                        >
                    </div>

                    <div>
                        <label for="cavity" class="block text-sm font-medium text-gray-700 mb-2">
                            Cavity <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="number"
                            id="cavity"
                            name="cavity"
                            required
                            min="1"
                            value="{{ $mold->cavity ?? 1 }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="4"
                        >
                    </div>

                    <div>
                        <label for="cycle_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Cycle Time (detik)
                        </label>
                        <input
                            type="number"
                            id="cycle_time"
                            name="cycle_time"
                            step="0.01"
                            min="0"
                            value="{{ $mold->cycle_time }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="30.5"
                        >
                    </div>
                </div>

                {{-- Row 3: Capacity, Lokasi Mold, Tipe Mold, Material Resin --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="capacity" class="block text-sm font-medium text-gray-700 mb-2">
                            Capacity
                        </label>
                        <input
                            type="number"
                            id="capacity"
                            name="capacity"
                            min="0"
                            value="{{ $mold->capacity }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="1000"
                        >
                    </div>

                    <div>
                        <label for="lokasi_mold" class="block text-sm font-medium text-gray-700 mb-2">
                            Lokasi Mold
                        </label>
                        <select
                            id="lokasi_mold"
                            name="lokasi_mold"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Lokasi Mold</option>
                            <option value="internal" @selected($mold->lokasi_mold == 'internal')>Internal</option>
                            <option value="external" @selected($mold->lokasi_mold == 'external')>External</option>
                        </select>
                    </div>

                    <div>
                        <label for="tipe_mold" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Mold
                        </label>
                        <select
                            id="tipe_mold"
                            name="tipe_mold"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Tipe Mold</option>
                            <option value="single" @selected($mold->tipe_mold == 'single')>Single</option>
                            <option value="family" @selected($mold->tipe_mold == 'family')>Family</option>
                        </select>
                    </div>

                    <div>
                        <label for="material_resin" class="block text-sm font-medium text-gray-700 mb-2">
                            Material Resin
                        </label>
                        <input
                            type="text"
                            id="material_resin"
                            name="material_resin"
                            maxlength="100"
                            value="{{ $mold->material_resin }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="PP, ABS, PC"
                        >
                    </div>
                </div>

                {{-- Row 4: Warna Produk --}}
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div>
                        <label for="warna_produk" class="block text-sm font-medium text-gray-700 mb-2">
                            Warna Produk
                        </label>
                        <select
                            id="warna_produk"
                            name="warna_produk"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Warna Produk</option>
                            <option value="putih" @selected($mold->warna_produk == 'putih')>Putih</option>
                            <option value="kuning" @selected($mold->warna_produk == 'kuning')>Kuning</option>
                            <option value="merah" @selected($mold->warna_produk == 'merah')>Merah</option>
                            <option value="biru" @selected($mold->warna_produk == 'biru')>Biru</option>
                            <option value="hijau" @selected($mold->warna_produk == 'hijau')>Hijau</option>
                            <option value="hitam" @selected($mold->warna_produk == 'hitam')>Hitam</option>
                            <option value="buram" @selected($mold->warna_produk == 'buram')>Buram</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Update</span>
                </button>
                <a href="{{ route('master.mold.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
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
    const moldId = document.getElementById('mold_id_hidden').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Mengupdate...</span>';

    try {
        formData.append('_method', 'PUT');
        const response = await fetch(`/submaster/mold/${moldId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });

        const data = await response.json();

        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("master.mold.index") }}';
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
