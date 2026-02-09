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

        <h2 class="text-xl font-bold text-gray-900 leading-none">Import Mold</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Import data mold dari file CSV</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Error Message --}}
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Import Errors --}}
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Beberapa baris gagal diimport:</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc pl-5 space-y-1 max-h-40 overflow-y-auto">
                            @foreach(session('import_errors') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <form action="{{ route('master.mold.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
        @csrf
        
        {{-- 1. Upload File --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">1. Upload File</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="file" class="block text-sm font-medium text-gray-700 mb-2">
                        File CSV
                    </label>
                    <input 
                        type="file" 
                        id="file" 
                        name="file" 
                        accept=".csv,.txt"
                        required
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none p-2"
                        onchange="handleFileUpload(event)"
                    >
                    <p class="mt-1 text-xs text-gray-500">Mendukung format .csv (Comma Delimited)</p>
                    <p class="text-xs text-gray-500">Jika dari Excel, silakan 'Save As' ke format CSV.</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Mulai dari Baris ke-
                    </label>
                    <input 
                        type="number" 
                        name="start_row" 
                        value="2" 
                        min="1"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                    <p class="mt-1 text-xs text-gray-500">Abaikan baris header (biasanya baris 1)</p>
                </div>
            </div>
        </div>

        {{-- 2. Mapping Kolom --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">2. Mapping Kolom (Huruf Kolom CSV)</h3>
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-6">
                <p class="text-sm text-blue-800">
                    Masukan huruf kolom untuk setiap field (contoh: <strong>A, B, AA</strong>). Kosongkan jika tidak ada di file.
                </p>
            </div>

            {{-- WAJIB DIISI --}}
            <div class="mb-6">
                <h4 class="text-sm font-bold text-gray-700 uppercase mb-3">Wajib Diisi</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kode Mold <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_kode_mold" 
                                placeholder="A"
                                required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Perusahaan <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_perusahaan" 
                                placeholder="B"
                                required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Part <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_part" 
                                placeholder="C"
                                required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Cavity <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_cavity" 
                                placeholder="D"
                                required
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>
                </div>
            </div>

            {{-- OPSIONAL / SPESIFIKASI TEKNIS --}}
            <div>
                <h4 class="text-sm font-bold text-gray-700 uppercase mb-3">Opsional / Spesifikasi Teknis</h4>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Mold
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_nomor_mold" 
                                placeholder="E"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Cycle Time
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_cycle_time" 
                                placeholder="F"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Capacity
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_capacity" 
                                placeholder="G"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Lokasi Mold
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_lokasi_mold" 
                                placeholder="H"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">INTERNAL / EXTERNAL</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tipe Mold
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_tipe_mold" 
                                placeholder="I"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                        <p class="mt-1 text-xs text-gray-500">SINGLE / FAMILY</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Material Resin
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_material_resin" 
                                placeholder="J"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Warna Produk
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500 text-sm">Kolom</span>
                            <input 
                                type="text" 
                                name="col_warna_produk" 
                                placeholder="K"
                                class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                            >
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit Button --}}
        <div class="flex items-center gap-4">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center gap-2 font-semibold">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m-4-4v12"/>
                </svg>
                <span>Import Data</span>
            </button>
            <a href="{{ route('master.mold.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg transition-colors font-semibold">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
function handleFileUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const fileName = file.name;
        const fileSize = (file.size / 1024).toFixed(2); // KB
        console.log(`File selected: ${fileName} (${fileSize} KB)`);
    }
}

// Save column mapping to localStorage
document.getElementById('importForm').addEventListener('submit', function() {
    const formData = new FormData(this);
    const mapping = {};
    
    // Save all column mappings
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('col_') && value) {
            mapping[key] = value;
        }
    }
    
    localStorage.setItem('mold_import_mapping', JSON.stringify(mapping));
});

// Restore column mapping from localStorage
window.addEventListener('DOMContentLoaded', function() {
    const savedMapping = localStorage.getItem('mold_import_mapping');
    if (savedMapping) {
        const mapping = JSON.parse(savedMapping);
        for (let [key, value] of Object.entries(mapping)) {
            const input = document.querySelector(`input[name="${key}"]`);
            if (input) {
                input.value = value;
            }
        }
    }
});
</script>
@endsection
