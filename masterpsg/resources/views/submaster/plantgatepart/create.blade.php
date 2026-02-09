@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <a href="{{ route('submaster.plantgatepart.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors" title="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>
        <h2 class="text-xl font-bold text-gray-900 leading-none">Tambah Plant Gate Part</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Tambah relasi plant gate dengan part</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createForm" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Pilih Gate <span class="text-red-500">*</span>
                    </label>
                    <div class="border border-gray-300 rounded-lg p-4 max-h-60 overflow-y-auto bg-gray-50">
                        <div class="space-y-2">
                            @foreach($plantgates as $plantgate)
                            <div class="flex items-start">
                                <div class="flex items-center h-5">
                                    <input 
                                        id="gate_{{ $plantgate->id }}" 
                                        name="plantgate_ids[]" 
                                        type="checkbox" 
                                        value="{{ $plantgate->id }}"
                                        class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded cursor-pointer"
                                    >
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="gate_{{ $plantgate->id }}" class="font-medium text-gray-700 cursor-pointer select-none">
                                        {{ $plantgate->nama_plantgate }} 
                                        <span class="text-gray-500 font-normal">- {{ $plantgate->customer->nama_perusahaan ?? '-' }}</span>
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Centang gate yang sesuai untuk part ini (bisa lebih dari satu)</p>
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

            <div class="flex items-center gap-4 pt-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan</span>
                </button>
                <a href="{{ route('submaster.plantgatepart.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Data for autocomplete
const partsData = @json($partsData);

// Reusable Autocomplete Function
function setupAutocomplete(wrapper, dataSource, onSelect) {
    let input = wrapper.querySelector('input[type="text"]');
    let hidden = wrapper.querySelector('input[type="hidden"]');
    let list = wrapper.querySelector('.autocomplete-list');
    
    if (!input || !hidden || !list) return;

    const resetState = () => {
        list.classList.add('hidden');
        list.innerHTML = '';
    };

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

    input.addEventListener('input', function() {
        const query = this.value.trim();
        const data = typeof dataSource === 'function' ? dataSource() : dataSource;
        
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

    input.addEventListener('focus', function() {
        showSuggestions(this.value.trim());
    });
    
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
        setupAutocomplete(wrapper, partsData);
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
        const response = await fetch('{{ route("submaster.plantgatepart.store") }}', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await response.json();
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("submaster.plantgatepart.index") }}';
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


