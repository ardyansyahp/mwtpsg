@extends('layout.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Custom Select2 Styling to match Tailwind */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border-color: #d1d5db !important; /* gray-300 */
        border-radius: 0.5rem !important; /* rounded-lg */
        display: flex !important;
        align-items: center !important;
        background-color: #ffffff !important;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
        top: 1px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #374151 !important; /* gray-700 */
        font-size: 0.875rem !important; /* text-sm */
        padding-left: 1rem !important; /* px-4 */
        line-height: normal !important;
    }

    .select2-search__field {
        border-radius: 0.375rem !important;
    }

    .select2-dropdown {
        border-color: #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
        background-color: #ffffff !important;
    }
    
    .select2-results__option {
        padding: 0.5rem 1rem !important;
        font-size: 0.875rem !important;
    }

    .select2-results__option--highlighted {
        background-color: #2563eb !important; /* blue-600 */
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('master.kendaraan.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Tambah Kendaraan</h2>
        <p class="text-gray-600 mt-1">Tambah data kendaraan operational</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('master.kendaraan.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Nopol --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Polisi (Nopol) <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="nopol_kendaraan" 
                        value="{{ old('nopol_kendaraan') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nopol_kendaraan') border-red-500 @enderror uppercase transition-shadow"
                        placeholder="Contoh: B 1234 CD"
                        required
                    >
                    @error('nopol_kendaraan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jenis --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Kendaraan <span class="text-red-500">*</span>
                    </label>
                    <select 
                        name="jenis_kendaraan" 
                        id="jenis_kendaraan"
                        class="w-full select2-enable"
                        required
                    >
                        <option value="">Pilih Jenis Kendaraan</option>
                        <option value="Truck Box" {{ old('jenis_kendaraan') == 'Truck Box' ? 'selected' : '' }}>Truck Box</option>
                        <option value="Truck Wing Box" {{ old('jenis_kendaraan') == 'Truck Wing Box' ? 'selected' : '' }}>Truck Wing Box</option>
                        <option value="Pick Up" {{ old('jenis_kendaraan') == 'Pick Up' ? 'selected' : '' }}>Pick Up</option>
                        <option value="Blind Van" {{ old('jenis_kendaraan') == 'Blind Van' ? 'selected' : '' }}>Blind Van</option>
                        <option value="Minibus" {{ old('jenis_kendaraan') == 'Minibus' ? 'selected' : '' }}>Minibus</option>
                    </select>
                    @error('jenis_kendaraan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Merk --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Merk Kendaraan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="merk_kendaraan" 
                        value="{{ old('merk_kendaraan') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow"
                        placeholder="Contoh: Isuzu, Hino"
                        required
                    >
                </div>

                {{-- Tahun --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun Pembuatan <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="tahun_kendaraan" 
                        value="{{ old('tahun_kendaraan', date('Y')) }}"
                        min="1990"
                        max="{{ date('Y') + 1 }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-shadow"
                        required
                    >
                </div>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-gray-100">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-sm"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan Data</span>
                </button>
                <a 
                    href="{{ route('master.kendaraan.index') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg transition-colors"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-enable').select2({
            placeholder: "Pilih Jenis Kendaraan",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
