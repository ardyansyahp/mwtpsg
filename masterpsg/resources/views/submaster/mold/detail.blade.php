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
        <h2 class="text-3xl font-bold text-gray-800">Detail Mold</h2>
        <p class="text-gray-600 mt-1">Informasi lengkap mold</p>
    </div>

    {{-- Main Content --}}
    <div class="space-y-6">
        {{-- Informasi Dasar --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informasi Dasar
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Mold ID</label>
                    <p class="text-base font-semibold text-gray-900 font-mono">{{ $mold->mold_id ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Kode Mold</label>
                    <p class="text-base text-gray-900">{{ $mold->kode_mold ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Mold</label>
                    <p class="text-base text-gray-900">{{ $mold->nomor_mold ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Perusahaan</label>
                    <p class="text-base text-gray-900">{{ $mold->perusahaan ? $mold->perusahaan->nama_perusahaan : '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Part</label>
                    <p class="text-base text-gray-900">
                        @if($mold->part)
                            {{ $mold->part->nomor_part }} - {{ $mold->part->nama_part }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                    <p class="text-base text-gray-900">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $mold->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $mold->status ? 'Active' : 'Inactive' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Spesifikasi Mold --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Spesifikasi Mold
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Cavity</label>
                    <p class="text-base text-gray-900">{{ $mold->cavity ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Cycle Time</label>
                    <p class="text-base text-gray-900">{{ $mold->cycle_time ? number_format($mold->cycle_time, 2) . ' detik' : '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Capacity</label>
                    <p class="text-base text-gray-900">{{ $mold->capacity ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi Mold</label>
                    <p class="text-base text-gray-900">
                        @if($mold->lokasi_mold)
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-sm font-medium uppercase">
                                {{ $mold->lokasi_mold }}
                            </span>
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tipe Mold</label>
                    <p class="text-base text-gray-900">
                        @if($mold->tipe_mold)
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-md text-sm font-medium capitalize">
                                {{ $mold->tipe_mold }}
                            </span>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Material & Warna --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                Material & Warna
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Material Resin</label>
                    <p class="text-base text-gray-900">{{ $mold->material_resin ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Warna Produk</label>
                    <p class="text-base text-gray-900">
                        @if($mold->warna_produk)
                            @php
                                $warnaClasses = [
                                    'putih' => 'bg-gray-100 text-gray-800',
                                    'kuning' => 'bg-yellow-100 text-yellow-800',
                                    'merah' => 'bg-red-100 text-red-800',
                                    'biru' => 'bg-blue-100 text-blue-800',
                                    'hijau' => 'bg-green-100 text-green-800',
                                    'hitam' => 'bg-gray-800 text-white',
                                    'buram' => 'bg-gray-200 text-gray-700',
                                ];
                                $warnaClass = $warnaClasses[strtolower($mold->warna_produk)] ?? 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 py-1 {{ $warnaClass }} rounded-md text-sm font-medium capitalize">
                                {{ $mold->warna_produk }}
                            </span>
                        @else
                            -
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Metadata --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informasi Sistem
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Pada</label>
                    <p class="text-base text-gray-900">{{ $mold->created_at ? $mold->created_at->format('d M Y H:i:s') : '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Diupdate Pada</label>
                    <p class="text-base text-gray-900">{{ $mold->updated_at ? $mold->updated_at->format('d M Y H:i:s') : '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-4 pt-4">
            <a href="{{ route('master.mold.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors">
                Tutup
            </a>
        </div>
    </div>
</div>
@endsection
