@extends('layout.app')

@section('content')
<div class="fade-in max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Edit Kendaraan</h2>
            <a href="{{ route('master.kendaraan.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>
        
        <form action="{{ route('master.kendaraan.update', $kendaraan->id) }}" method="POST" class="p-6">
            @csrf
            
            <div class="space-y-4">
                {{-- Nopol --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Polisi (Nopol)</label>
                    <input 
                        type="text" 
                        name="nopol_kendaraan" 
                        value="{{ old('nopol_kendaraan', $kendaraan->nopol_kendaraan) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('nopol_kendaraan') border-red-500 @enderror uppercase"
                        required
                    >
                    @error('nopol_kendaraan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Jenis --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kendaraan</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            name="jenis_kendaraan" 
                            list="jenis_list"
                            value="{{ old('jenis_kendaraan', $kendaraan->jenis_kendaraan) }}"
                            class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 @error('jenis_kendaraan') border-red-500 @enderror"
                            required
                        >
                        <datalist id="jenis_list">
                            <option value="Truck Box">
                            <option value="Truck Wing Box">
                            <option value="Pick Up">
                            <option value="Blind Van">
                            <option value="Minibus">
                        </datalist>
                    </div>
                </div>

                {{-- Merk --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Merk Kendaraan</label>
                    <input 
                        type="text" 
                        name="merk_kendaraan" 
                        value="{{ old('merk_kendaraan', $kendaraan->merk_kendaraan) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                </div>

                {{-- Tahun --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Pembuatan</label>
                    <input 
                        type="number" 
                        name="tahun_kendaraan" 
                        value="{{ old('tahun_kendaraan', $kendaraan->tahun_kendaraan) }}"
                        min="1990"
                        max="{{ date('Y') + 1 }}"
                        class="w-24 rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"
                        required
                    >
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('master.kendaraan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium shadow-sm">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
