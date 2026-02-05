@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('master.mold.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Tambah Mold</h2>
        <p class="text-gray-600 mt-1">Tambah data mold baru</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createForm" class="space-y-6">
            @csrf

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
                                <option value="{{ $p->id }}">{{ $p->nama_perusahaan }}{{ $p->inisial_perusahaan ? ' (' . $p->inisial_perusahaan . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="part_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Part <span class="text-red-500">*</span>
                        </label>
                        <div class="relative part-autocomplete-wrapper">
                            <input 
                                type="text" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                placeholder="Ketik nomor part atau nama part..." 
                                autocomplete="off"
                            >
                            <input type="hidden" name="part_id" required>
                            <div class="autocomplete-list hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
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
                            value="1"
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
                            <option value="">Pilih Lokasi </option>
                            <option value="internal">Internal</option>
                            <option value="external">External</option>
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
                            <option value="">Pilih Tipe</option>
                            <option value="single">Single</option>
                            <option value="family">Family</option>
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
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Contoh: PP"
                        >
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan</span>
                </button>
                <a href="{{ route('master.mold.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Data for autocomplete
const partsData = @json($partsData);

// Reusable Autocomplete Function (same as in part/create.blade.php)
function setupAutocomplete(wrapper, dataSource, onSelect) {
    let input = wrapper.querySelector('input[type="text"]');
    let hidden = wrapper.querySelector('input[type="hidden"]');
    let list = wrapper.querySelector('.autocomplete-list');
    
    // Safety check
    if (!input || !hidden || !list) return;

    // Reset state helper
    const resetState = () => {
        list.classList.add('hidden');
        list.innerHTML = '';
    };

    // Filter and show options
    const showSuggestions = (query) => {
        const data = typeof dataSource === 'function' ? dataSource() : dataSource;
        
        const filtered = data.filter(item => 
            item.label.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);

        list.innerHTML = '';
        if (filtered.length === 0) {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 text-gray-500 text-xs italic';
            div.textContent = 'Tidak ditemukan';
            list.appendChild(div);
        } else {
            filtered.forEach(item => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 text-sm';
                div.textContent = item.label;
                div.addEventListener('click', () => {
                    input.value = item.label;
                    hidden.value = item.id;
                    resetState();
                    if (onSelect) onSelect(item);
                });
                list.appendChild(div);
            });
        }
        list.classList.remove('hidden');
    };

    // Input Event
    input.addEventListener('input', function() {
        const query = this.value.trim();
        const data = typeof dataSource === 'function' ? dataSource() : dataSource;
        
        // Clear hidden if text mismatch (simple check)
        // Note: For strict matching, you'd compare against current selection, but clearing on mismatch is safer
        if (hidden.value && query !== (data.find(i => i.id == hidden.value)?.label || '')) {
             hidden.value = ''; 
             if (onSelect) onSelect(null);
        }
        
        if (query.length === 0) {
            resetState();
            return;
        }
        showSuggestions(query);
    });

    // Focus Event
    input.addEventListener('focus', function() {
        const query = this.value.trim();
        showSuggestions(query);
    });
    
    // Click outside to close
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            resetState();
             if (hidden.value === '' && input.value !== '') {
                  input.value = '';
            }
        }
    });
}

// Initialize Autocomplete
(function() {
    const wrapper = document.querySelector('.part-autocomplete-wrapper');
    if (wrapper) {
        setupAutocomplete(wrapper, partsData, (selected) => {
            // Optional: Do something when a part is selected
        });
    }
})();

document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menyimpan...</span>';

    try {
        const response = await fetch('{{ route("master.mold.store") }}', {
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

