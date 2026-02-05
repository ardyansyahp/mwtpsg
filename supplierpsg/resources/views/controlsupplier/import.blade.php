@extends('layout.app')

@section('content')
<div class="fade-in w-full px-6">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Import Control Supplier</h2>
            <p class="text-gray-600 mt-1">Import data delivery plan dari Excel/CSV dengan mapping kolom dinamis</p>
        </div>
        <a 
            href="{{ route('controlsupplier.monitoring') }}"
            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors"
        >
            Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <p class="font-bold">Error!</p>
            <p>{{ session('error') }}</p>
            
            @if(session('import_errors'))
            <div class="mt-2 pl-4 border-l-4 border-red-500 bg-red-50 p-2">
                <p class="font-semibold text-sm">Detail Error:</p>
                <ul class="list-disc pl-5 text-sm mt-1 max-h-60 overflow-y-auto">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="importForm" action="{{ route('controlsupplier.import.process') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- File Upload Section -->
            <div class="mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">1. Upload File</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File Excel/CSV</label>
                        <input 
                            type="file" 
                            name="file" 
                            accept=".csv, .txt"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                            required
                        >
                        <p class="mt-1 text-sm text-gray-500">Mohon upload file .CSV (Comma Separated)</p>
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
                <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">2. Mapping Kolom (Huruf Kolom Excel)</h3>
                <p class="mb-4 text-sm text-gray-600 bg-blue-50 p-3 rounded">
                    Masukan huruf kolom Excel untuk setiap field (contoh: <strong>A</strong>, <strong>B</strong>, <strong>AA</strong>). 
                    Kosongkan jika tidak ada di file.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- General Information -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Informasi Umum</h4>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                             Periode <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_periode" 
                                value="B"
                                placeholder="A" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Contoh isi: 2024-01 atau Jan-24</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Supplier (Nama) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_supplier" 
                                value="E"
                                placeholder="B" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bahan Baku (Nomor) <span class="text-xs text-gray-400">(Opsional)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_bahan_baku" 
                                value="G"
                                placeholder="C" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Kode / Nomor Part</p>
                    </div>

                    <div class="col-span-1 md:col-span-2 lg:col-span-3 -mt-4 mb-2">
                       <p class="text-xs text-amber-600 italic bg-amber-50 p-2 rounded inline-block">
                           * Isi minimal salah satu dari Kolom Nomor atau Kolom Nama Bahan Baku.
                       </p>
                    </div>

                    <div>
                         <!-- Empty space -->
                    </div>
                </div>

                <!-- Revised Grid for Material and PO -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 -mt-6 mb-6">
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Bahan Baku (Nama) <span class="text-xs text-gray-400">(Opsional)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_nama_bahan_baku" 
                                value="H"
                                placeholder="D" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                            >
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Deskripsi / Nama Material</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            PO Number <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-2 py-1 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_po_number" 
                                value="A"
                                placeholder="E" 
                                class="uppercase w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Daily Data -->
                    <div class="col-span-1 md:col-span-2 lg:col-span-3 mt-4">
                        <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider">Data Harian (Qty)</h4>
                    </div>

                    <div class="col-span-1 md:col-span-2 lg:col-span-3 bg-blue-50 p-4 rounded border border-blue-100">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kolom Tanggal 1 (Mulai) <span class="text-red-500">*</span>
                        </label>
                        <div class="flex items-center gap-3">
                            <span class="bg-white text-gray-500 px-3 py-2 border border-gray-300 rounded text-sm font-mono">Kolom</span>
                            <input 
                                type="text" 
                                name="col_start_date" 
                                value="M"
                                placeholder="E" 
                                class="uppercase w-32 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm font-mono"
                                required
                            >
                            <span class="text-sm text-gray-600">
                                <svg class="w-4 h-4 inline mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Sistem akan otomatis membaca kolom berikutnya untuk tanggal 2, 3, dst (sampai akhir bulan).
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a 
                    href="{{ route('controlsupplier.monitoring') }}"
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
            'col_periode', 
            'col_supplier', 
            'col_bahan_baku', 
            'col_nama_bahan_baku', 
            'col_po_number', 
            'col_start_date',
            'start_row'
        ];
        
        // Restore from localStorage
        inputs.forEach(name => {
            const savedValue = localStorage.getItem('cs_import_' + name);
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
                    localStorage.setItem('cs_import_' + name, input.value);
                }
            });
        });
    });
</script>
@endsection
