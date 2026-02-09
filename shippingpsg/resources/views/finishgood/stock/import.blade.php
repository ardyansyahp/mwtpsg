@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('finishgood.stock.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Import Stock Limits</h2>
        <p class="text-gray-600 mt-1">Import Min/Max stock untuk semua part dari file CSV</p>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Session Import Errors --}}
    @if(session('import_errors'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <h4 class="font-bold mb-2">Terjadi Kesalahan pada Baris Berikut:</h4>
            <div class="max-h-60 overflow-y-auto">
                <ul class="list-disc list-inside text-sm">
                    @foreach(session('import_errors') as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <form action="{{ route('finishgood.stock.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            
            {{-- Section 1: Upload File --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    1. Upload File
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">File CSV</label>
                        <input 
                            type="file" 
                            name="file" 
                            accept=".csv,.txt"
                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer"
                            required
                        >
                        <p class="mt-2 text-sm text-gray-500">Mendukung format .csv dan .txt (max 10MB)</p>
                        <p class="text-xs text-gray-400">Jika dari Excel, silakan 'Save As' ke format CSV.</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mulai dari Baris ke-</label>
                        <input 
                            type="number" 
                            name="start_row" 
                            value="2" 
                            min="1"
                            class="w-32 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                            required
                        >
                        <p class="mt-2 text-sm text-gray-500">Abaikan baris header (biasanya baris 1)</p>
                    </div>
                </div>
            </div>

            {{-- Section 2: Column Mapping --}}
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                    2. Mapping Kolom (Huruf Kolom CSV)
                </h3>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                    <p class="text-sm text-blue-800">
                        <strong>Instruksi:</strong> Masukkan huruf kolom untuk setiap field sesuai file Excel (contoh: <strong>A</strong>, <strong>B</strong>, <strong>AA</strong>). 
                        Setting ini akan tersimpan otomatis.
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Part</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-3 py-2 rounded-lg text-sm font-mono">Kolom</span>
                            <input type="text" name="col_nomor_part" placeholder="A" class="uppercase flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Min Stock</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-3 py-2 rounded-lg text-sm font-mono">Kolom</span>
                            <input type="text" name="col_min_stock" placeholder="B" class="uppercase flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono" required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Max Stock</label>
                        <div class="flex items-center gap-2">
                            <span class="bg-gray-100 text-gray-500 px-3 py-2 rounded-lg text-sm font-mono">Kolom</span>
                            <input type="text" name="col_max_stock" placeholder="C" class="uppercase flex-1 rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex items-center gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors flex items-center gap-2 font-semibold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Stock Limits
                </button>
                <a href="{{ route('finishgood.stock.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg transition-colors font-semibold">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Auto-save column mapping to localStorage
document.querySelectorAll('input[name^="col_"]').forEach(input => {
    // Load saved value
    const saved = localStorage.getItem(`stock_limits_${input.name}`);
    if (saved && !input.value) input.value = saved;
    
    // Save on change
    input.addEventListener('change', () => {
        localStorage.setItem(`stock_limits_${input.name}`, input.value.toUpperCase());
    });
});
</script>
@endsection
