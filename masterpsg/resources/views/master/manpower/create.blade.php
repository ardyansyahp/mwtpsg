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

        <h2 class="text-xl font-bold text-gray-900 leading-none">Tambah Manpower</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Tambah data manpower baru</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createForm" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">


                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700 mb-2">
                        NIK
                    </label>
                    <input
                        type="text"
                        id="nik"
                        name="nik"
                        maxlength="50"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Masukkan NIK"
                    >
                </div>

                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="nama"
                        name="nama"
                        required
                        maxlength="255"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Masukkan nama"
                    >
                </div>

                <div>
                    <label for="departemen" class="block text-sm font-medium text-gray-700 mb-2">
                        Departemen
                    </label>
                    <select
                        id="departemen"
                        name="departemen"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="">Pilih Departemen</option>
                        <option value="Produksi">Produksi</option>
                        <option value="PPIC">PPIC</option>
                        <option value="GA">GA</option>
                        <option value="QC">QC</option>
                        <option value="Purchasing">Purchasing</option>
                    </select>
                </div>

                <div>
                    <label for="bagian" class="block text-sm font-medium text-gray-700 mb-2">
                        Bagian
                    </label>
                    <select
                        id="bagian"
                        name="bagian"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        disabled
                    >
                        <option value="">Pilih Departemen terlebih dahulu</option>
                    </select>
                </div>

                {{-- User Account Section --}}
                <div class="col-span-1 md:col-span-2 mt-4">
                    <h3 class="text-sm font-semibold text-gray-800 border-b pb-2 mb-4">Akun Pengguna (Optional)</h3>
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">
                        Role
                    </label>
                    <select
                        id="role"
                        name="role"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                        <option value="0">User (Operator/Regular)</option>
                        <option value="2">Management</option>
                        <option value="1">Superadmin</option>
                    </select>
                </div>

                <div id="password-container" class="hidden">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="4"
                        placeholder="Minimal 4 karakter (Wajib untuk Role Superadmin/Management)"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    >
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan</span>
                </button>
                <a href="{{ route('master.manpower.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Toggle bagian dropdown based on departemen
document.getElementById('departemen').addEventListener('change', function() {
    const bagianSelect = document.getElementById('bagian');
    const selectedDepartemen = this.value;
    
    // Clear existing options
    bagianSelect.innerHTML = '';
    
    if (selectedDepartemen === 'Produksi') {
        bagianSelect.disabled = false;
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option><option value="Assy">Assy</option><option value="Inject">Inject</option><option value="Kabag">Kabag</option>';
    } else if (selectedDepartemen === 'PPIC') {
        bagianSelect.disabled = false;
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option><option value="Bahan Baku">Bahan Baku</option><option value="Finish Good">Finish Good</option><option value="Kabag">Kabag</option>';
    } else if (selectedDepartemen === 'GA') {
        bagianSelect.disabled = false;
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option><option value="Driver">Driver</option><option value="Admin">Admin</option><option value="Kabag">Kabag</option>';
    } else if (selectedDepartemen === 'QC') {
        bagianSelect.disabled = false;
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option><option value="Admin">Admin</option><option value="Operator">Operator</option><option value="Kabag">Kabag</option>';
    } else if (selectedDepartemen === 'Purchasing') {
        bagianSelect.disabled = false;
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option><option value="Staff">Staff</option><option value="Kabag">Kabag</option>';
    } else {
        bagianSelect.disabled = true;
        bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
    }
});

// Role & Password Interaction
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    const passContainer = document.getElementById('password-container');
    const passInput = document.getElementById('password');
    
    if (role === '1' || role === '2') {
        passContainer.classList.remove('hidden');
        passInput.setAttribute('required', 'required');
    } else {
        passContainer.classList.add('hidden');
        passInput.removeAttribute('required');
        passInput.value = ''; // Reset password
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
        const response = await fetch('{{ route("master.manpower.store") }}', {
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


