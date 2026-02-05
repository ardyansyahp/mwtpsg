@extends('layout.app')

@section('content')
<div class="fade-in max-w-lg mx-auto">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 bg-red-50 flex justify-between items-center">
            <h2 class="text-xl font-bold text-red-800">Hapus Kendaraan</h2>
            <a href="{{ route('master.kendaraan.index') }}" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
        </div>
        
        <div class="p-6">
            <div class="flex items-center gap-4 mb-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600 flex-shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <p class="text-gray-800 font-medium">Apakah Anda yakin ingin menghapus data kendaraan ini?</p>
                    <p class="text-gray-500 text-sm mt-1">Data yang dihapus akan masuk ke sampah (recycle bin) dan dapat dipulihkan nanti.</p>
                </div>
            </div>

            <div class="bg-gray-50 rounded p-4 mb-6 border border-gray-200">
                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                    <dt class="text-gray-500">Nomor Polisi</dt>
                    <dd class="font-bold text-gray-800">{{ $kendaraan->nopol_kendaraan }}</dd>
                    
                    <dt class="text-gray-500">Jenis</dt>
                    <dd class="text-gray-800">{{ $kendaraan->jenis_kendaraan }}</dd>
                    
                    <dt class="text-gray-500">Merk</dt>
                    <dd class="text-gray-800">{{ $kendaraan->merk_kendaraan }}</dd>
                </dl>
            </div>

            <form action="{{ route('master.kendaraan.destroy', $kendaraan->id) }}" method="POST" class="flex justify-end gap-3">
                @csrf
                
                <a href="{{ route('master.kendaraan.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors font-medium">Batal</a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium shadow-sm">
                    Ya, Hapus
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
