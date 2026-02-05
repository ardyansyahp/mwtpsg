@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Import Bahan Baku</h2>
            <p class="text-gray-600 mt-1">Import data bahan baku dari file CSV dengan mapping kolom dinamis</p>
        </div>
        <a 
            href="{{ route('master.bahanbaku.index') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
        >
            Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form id="importForm" action="{{ route('master.bahanbaku.import') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- File Upload Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">1. Upload File</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File CSV</label>
                        <input 
                            type="file" 
                            name="file" 
                            accept=".csv, .txt"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            required
                        >
                        <p class="mt-1 text-sm text-gray-500">Mendukung format .csv (Comma Delimited)</p>
                        <p class="text-xs text-gray-400">Jika dari Excel, silakan 'Save As' ke format CSV.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mulai dari Baris ke-</label>
                        <input 
                            type="number" 
                            name="start_row" 
                            value="2" 
                            min="1"
                            class="w-24 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                            required
                        >
                        <p class="mt-1 text-sm text-gray-500">Abaikan baris header (biasanya baris 1)</p>
                    </div>
                </div>
            </div>

            <!-- Column Mapping Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">2. Mapping Kolom (Huruf Kolom CSV)</h3>
                <p class="mb-4 text-sm text-gray-600 bg-blue-50 p-3 rounded">
                    Masukan huruf kolom untuk setiap field (contoh: <strong>A</strong>, <strong>B</strong>, <strong>AA</strong>). 
                    Kosongkan jika tidak ada di file.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Mandatory Fields -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Wajib Diisi</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_kategori" 
                                placeholder="A" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">material, subpart, box, dll</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nomor Bahan Baku <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_nomor" 
                                placeholder="B" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Nama / Deskripsi <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_nama" 
                                placeholder="C" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                        </div>
                    </div>

                    <!-- Optional Fields -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 mt-4">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Opsional / Kondisional</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Supplier (Nama)</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_supplier" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis (untuk Box/Layer)</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_jenis" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">polybox, impraboard, dll</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Satuan (UOM)</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_uom" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Std Packing</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_std_packing" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Packing</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_jenis_packing" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <!-- Dimensions -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 mt-4">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Dimensi (Jika Ada)</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Panjang</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_panjang" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lebar</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_lebar" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tinggi</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_tinggi" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Box</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_kode_box" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>

                    <!-- Status & Keterangan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_status" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                         <p class="text-xs text-gray-400 mt-1">AKTIF / DISCONTINUE</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_keterangan" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a 
                    href="{{ route('master.bahanbaku.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded-lg transition-colors"
                >
                    Batal
                </a>
                <button 
                    type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    <span>Import Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('importForm');
        if (!form) return;

        const inputs = [
            'start_row',
            'col_kategori', 
            'col_nomor', 
            'col_nama', 
            'col_supplier', 
            'col_jenis',
            'col_uom',
            'col_std_packing',
            'col_jenis_packing',
            'col_panjang',
            'col_lebar',
            'col_tinggi',
            'col_kode_box',
            'col_status',
            'col_keterangan'
        ];
        
        // Restore from localStorage
        inputs.forEach(name => {
            const savedValue = localStorage.getItem('bb_import_' + name);
            if (savedValue) {
                const input = form.querySelector(`[name="${name}"]`);
                if (input) input.value = savedValue;
            }
        });
        
        // Save to localStorage on submit
        form.addEventListener('submit', function() {
            inputs.forEach(name => {
                const input = form.querySelector(`[name="${name}"]`);
                if (input) {
                    localStorage.setItem('bb_import_' + name, input.value);
                }
            });
        });
    });
</script>
@endsection
