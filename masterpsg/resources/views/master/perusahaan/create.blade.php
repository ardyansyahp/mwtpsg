@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('master.perusahaan.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Tambah Perusahaan</h2>
        <p class="text-gray-600 mt-1">Tambah data perusahaan baru</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createForm" class="space-y-6">
            @csrf
            <div>
                <label for="nama_perusahaan" class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Perusahaan <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="nama_perusahaan" 
                    name="nama_perusahaan" 
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Masukkan nama perusahaan"
                >
            </div>

            {{-- Grid untuk Inisial dan Jenis Perusahaan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="inisial_perusahaan" class="block text-sm font-medium text-gray-700 mb-2">
                        Inisial Perusahaan
                    </label>
                    <input 
                        type="text" 
                        id="inisial_perusahaan" 
                        name="inisial_perusahaan" 
                        maxlength="50"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Masukkan inisial perusahaan"
                    >
                </div>

                <div>
                    <label for="jenis_perusahaan" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Perusahaan
                    </label>
                    <select 
                        id="jenis_perusahaan" 
                        name="jenis_perusahaan" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Pilih Jenis Perusahaan</option>
                        <option value="Customer">Customer</option>
                        <option value="Vendor">Vendor</option>
                    </select>
                </div>
            </div>

            <div id="kode_supplier_wrapper" style="display: none;">
                <label for="kode_supplier" class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Supplier <span class="text-red-500">*</span>
                </label>
                <input 
                    type="text" 
                    id="kode_supplier" 
                    name="kode_supplier" 
                    maxlength="50"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Masukkan kode supplier"
                >
            </div>

            <div>
                <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                    Alamat
                </label>
                <textarea 
                    id="alamat" 
                    name="alamat" 
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Masukkan alamat perusahaan"
                ></textarea>
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
                    href="{{ route('master.perusahaan.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle kode_supplier field based on jenis_perusahaan
document.getElementById('jenis_perusahaan').addEventListener('change', function() {
    const kodeSupplierWrapper = document.getElementById('kode_supplier_wrapper');
    const kodeSupplierInput = document.getElementById('kode_supplier');
    
    if (this.value === 'Vendor') {
        kodeSupplierWrapper.style.display = 'block';
        kodeSupplierInput.required = true;
    } else {
        kodeSupplierWrapper.style.display = 'none';
        kodeSupplierInput.required = false;
        kodeSupplierInput.value = '';
    }
});

document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menyimpan...</span>';
    
    try {
        const response = await fetch('{{ route("master.perusahaan.store") }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("master.perusahaan.index") }}';
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


