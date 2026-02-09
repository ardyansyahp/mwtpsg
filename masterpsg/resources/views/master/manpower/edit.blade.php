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

        <h2 class="text-xl font-bold text-gray-900 leading-none">Edit Manpower</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Edit data manpower</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editForm" class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" id="manpower_id_hidden" value="{{ $manpower->id }}">

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
                        value="{{ $manpower->nik ?? '' }}"
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
                        value="{{ $manpower->nama }}"
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
                        <option value="Produksi" {{ ($manpower->departemen ?? '') === 'Produksi' ? 'selected' : '' }}>Produksi</option>
                        <option value="PPIC" {{ ($manpower->departemen ?? '') === 'PPIC' ? 'selected' : '' }}>PPIC</option>
                        <option value="GA" {{ ($manpower->departemen ?? '') === 'GA' ? 'selected' : '' }}>GA</option>
                        <option value="QC" {{ ($manpower->departemen ?? '') === 'QC' ? 'selected' : '' }}>QC</option>
                        <option value="Purchasing" {{ ($manpower->departemen ?? '') === 'Purchasing' ? 'selected' : '' }}>Purchasing</option>
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
                        data-current-bagian="{{ $manpower->bagian ?? '' }}"
                    >
                        <option value="">Pilih Bagian</option>
                        @if(($manpower->departemen ?? '') === 'Produksi')
                            <option value="Assy" {{ ($manpower->bagian ?? '') === 'Assy' ? 'selected' : '' }}>Assy</option>
                            <option value="Inject" {{ ($manpower->bagian ?? '') === 'Inject' ? 'selected' : '' }}>Inject</option>
                            <option value="Kabag" {{ ($manpower->bagian ?? '') === 'Kabag' ? 'selected' : '' }}>Kabag</option>
                        @elseif(($manpower->departemen ?? '') === 'PPIC')
                            <option value="Bahan Baku" {{ ($manpower->bagian ?? '') === 'Bahan Baku' ? 'selected' : '' }}>Bahan Baku</option>
                            <option value="Finish Good" {{ ($manpower->bagian ?? '') === 'Finish Good' ? 'selected' : '' }}>Finish Good</option>
                            <option value="Kabag" {{ ($manpower->bagian ?? '') === 'Kabag' ? 'selected' : '' }}>Kabag</option>
                        @elseif(($manpower->departemen ?? '') === 'GA')
                            <option value="Driver" {{ ($manpower->bagian ?? '') === 'Driver' ? 'selected' : '' }}>Driver</option>
                            <option value="Admin" {{ ($manpower->bagian ?? '') === 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Kabag" {{ ($manpower->bagian ?? '') === 'Kabag' ? 'selected' : '' }}>Kabag</option>
                        @elseif(($manpower->departemen ?? '') === 'QC')
                            <option value="Admin" {{ ($manpower->bagian ?? '') === 'Admin' ? 'selected' : '' }}>Admin</option>
                            <option value="Operator" {{ ($manpower->bagian ?? '') === 'Operator' ? 'selected' : '' }}>Operator</option>
                            <option value="Kabag" {{ ($manpower->bagian ?? '') === 'Kabag' ? 'selected' : '' }}>Kabag</option>
                        @elseif(($manpower->departemen ?? '') === 'Purchasing')
                            <option value="Kabag" {{ ($manpower->bagian ?? '') === 'Kabag' ? 'selected' : '' }}>Kabag</option>
                        @endif
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Update</span>
                </button>
                <a href="{{ route('master.manpower.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Function to update bagian options based on departemen
function updateBagianOptions() {
    const departemenSelect = document.getElementById('departemen');
    const bagianSelect = document.getElementById('bagian');
    const selectedDepartemen = departemenSelect.value;
    const currentBagian = bagianSelect.getAttribute('data-current-bagian') || '';
    
    // Clear existing options
    const options = '<option value="">Pilih Bagian</option>';
    
    if (selectedDepartemen === 'Produksi') {
        bagianSelect.innerHTML = options + 
            '<option value="Assy"' + (currentBagian === 'Assy' ? ' selected' : '') + '>Assy</option>' +
            '<option value="Inject"' + (currentBagian === 'Inject' ? ' selected' : '') + '>Inject</option>' +
            '<option value="Kabag"' + (currentBagian === 'Kabag' ? ' selected' : '') + '>Kabag</option>';
        bagianSelect.disabled = false;
    } else if (selectedDepartemen === 'PPIC') {
        bagianSelect.innerHTML = options + 
            '<option value="Bahan Baku"' + (currentBagian === 'Bahan Baku' ? ' selected' : '') + '>Bahan Baku</option>' +
            '<option value="Finish Good"' + (currentBagian === 'Finish Good' ? ' selected' : '') + '>Finish Good</option>' +
            '<option value="Kabag"' + (currentBagian === 'Kabag' ? ' selected' : '') + '>Kabag</option>';
        bagianSelect.disabled = false;
    } else if (selectedDepartemen === 'GA') {
        bagianSelect.innerHTML = options + 
            '<option value="Driver"' + (currentBagian === 'Driver' ? ' selected' : '') + '>Driver</option>' +
            '<option value="Admin"' + (currentBagian === 'Admin' ? ' selected' : '') + '>Admin</option>' +
            '<option value="Kabag"' + (currentBagian === 'Kabag' ? ' selected' : '') + '>Kabag</option>';
        bagianSelect.disabled = false;
    } else if (selectedDepartemen === 'QC') {
        bagianSelect.innerHTML = options + 
            '<option value="Admin"' + (currentBagian === 'Admin' ? ' selected' : '') + '>Admin</option>' +
            '<option value="Operator"' + (currentBagian === 'Operator' ? ' selected' : '') + '>Operator</option>' +
            '<option value="Kabag"' + (currentBagian === 'Kabag' ? ' selected' : '') + '>Kabag</option>';
        bagianSelect.disabled = false;
    } else if (selectedDepartemen === 'Purchasing') {
        bagianSelect.innerHTML = options + 
            '<option value="Kabag"' + (currentBagian === 'Kabag' ? ' selected' : '') + '>Kabag</option>';
        bagianSelect.disabled = false;
    } else {
        bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
        bagianSelect.disabled = true;
    }
}

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
        bagianSelect.innerHTML = '<option value="">Pilih Bagian</option><option value="Kabag">Kabag</option>';
    } else {
        bagianSelect.disabled = true;
        bagianSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
    }
});

// Initialize bagian options on page load
updateBagianOptions();

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const manpowerId = document.getElementById('manpower_id_hidden').value;
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Mengupdate...</span>';

    try {
        formData.append('_method', 'PUT');
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

