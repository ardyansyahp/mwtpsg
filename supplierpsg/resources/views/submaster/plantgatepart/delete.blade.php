@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('submaster.plantgatepart.index') }}" 
            class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 mb-3 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>
        <h2 class="text-3xl font-bold text-gray-800">Hapus Plant Gate Part</h2>
        <p class="text-gray-600 mt-1">Konfirmasi penghapusan relasi plant gate dengan part</p>
    </div>

    {{-- Delete Confirmation Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-8">
            <div class="flex flex-col items-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Apakah Anda yakin?</h3>
                <p class="text-gray-600 mb-6 text-center max-w-md">Relasi plant gate part akan dihapus. Anda dapat menemukannya kembali di Recycle Bin.</p>

                <div class="w-full bg-gray-50 rounded-xl p-6 border border-gray-100 mb-8">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Plant Gate</span>
                            <p class="text-gray-800 font-medium">{{ $plantgatePart->plantgate->nama_plantgate ?? '-' }}</p>
                        </div>
                        <div>
                            <span class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">Part</span>
                            <p class="text-gray-800 font-medium">{{ $plantgatePart->part->nomor_part ?? '-' }} - {{ $plantgatePart->part->nama_part ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-red-500 font-medium mb-6">Tindakan ini akan memindahkan data ke sampah.</p>

                {{-- Action Buttons --}}
                <div class="inline-flex p-1.5 bg-red-600 rounded-xl shadow-lg shadow-red-200">
                    <form action="{{ route('submaster.plantgatepart.destroy', $plantgatePart->id) }}" method="POST" class="inline-flex m-0 p-0">
                        @csrf
                        @method('DELETE')
                        <button 
                            type="submit"
                            class="flex items-center gap-2 px-6 py-2.5 text-white hover:text-white/90 transition-all font-bold group"
                        >
                            <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            <span>Ya, Hapus</span>
                        </button>
                    </form>
                    <a 
                        href="{{ route('submaster.plantgatepart.index') }}" 
                        class="bg-white text-gray-700 px-8 py-2.5 rounded-lg font-bold hover:bg-gray-50 transition-all shadow-sm flex items-center"
                    >
                        Batal
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
