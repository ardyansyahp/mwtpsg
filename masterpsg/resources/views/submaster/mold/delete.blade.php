@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('master.mold.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Hapus Mold</h2>
        <p class="text-gray-600 mt-1">Konfirmasi penghapusan data mold</p>
    </div>

    {{-- Confirmation Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-start gap-4 mb-6">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">Konfirmasi Penghapusan</h3>
                <p class="text-gray-600">Apakah Anda yakin ingin menghapus mold ini? Data akan dipindahkan ke recycle bin dan dapat dipulihkan kembali.</p>
            </div>
        </div>

        {{-- Mold Details --}}
        <div class="bg-gray-50 rounded-lg p-4 mb-6">
            <h4 class="text-sm font-semibold text-gray-700 mb-3">Detail Mold yang akan dihapus:</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <span class="text-gray-500">Kode Mold:</span>
                    <span class="ml-2 font-medium text-gray-900">{{ $mold->kode_mold ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Nomor Mold:</span>
                    <span class="ml-2 font-medium text-gray-900">{{ $mold->nomor_mold ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Perusahaan:</span>
                    <span class="ml-2 font-medium text-gray-900">{{ $mold->perusahaan ? $mold->perusahaan->nama_perusahaan : '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Part:</span>
                    <span class="ml-2 font-medium text-gray-900">
                        @if($mold->part)
                            {{ $mold->part->nomor_part }} - {{ $mold->part->nama_part }}
                        @else
                            -
                        @endif
                    </span>
                </div>
                <div>
                    <span class="text-gray-500">Cavity:</span>
                    <span class="ml-2 font-medium text-gray-900">{{ $mold->cavity ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Tipe Mold:</span>
                    <span class="ml-2 font-medium text-gray-900 capitalize">{{ $mold->tipe_mold ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <form action="{{ route('master.mold.destroy', $mold->id) }}" method="POST" class="flex items-center gap-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Ya, Hapus</span>
            </button>
            <a href="{{ route('master.mold.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                Batal
            </a>
        </form>
    </div>
</div>
@endsection
