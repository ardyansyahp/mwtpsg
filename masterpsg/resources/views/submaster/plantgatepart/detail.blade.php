@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('submaster.plantgatepart.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>
        <h2 class="text-xl font-bold text-gray-900 leading-none">Detail Plant Gate Part</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Informasi lengkap relasi plant gate dengan part</p>
    </div>

    {{-- Detail Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Informasi Plant Gate Part</h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Plant Gate Info --}}
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-700 border-b pb-2">Informasi Plant Gate</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nama Plant Gate</label>
                        <p class="text-gray-900 font-medium">{{ $plantgatePart->plantgate->nama_plantgate ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Customer</label>
                        <p class="text-gray-900">{{ $plantgatePart->plantgate->customer->nama_perusahaan ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Alamat Customer</label>
                        <p class="text-gray-900">{{ $plantgatePart->plantgate->customer->alamat ?? '-' }}</p>
                    </div>
                </div>

                {{-- Part Info --}}
                <div class="space-y-4">
                    <h4 class="font-semibold text-gray-700 border-b pb-2">Informasi Part</h4>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Part</label>
                        <p class="text-gray-900 font-mono font-medium">{{ $plantgatePart->part->nomor_part ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Nama Part</label>
                        <p class="text-gray-900">{{ $plantgatePart->part->nama_part ?? '-' }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-500 mb-1">Proses</label>
                        <p class="text-gray-900 uppercase">{{ $plantgatePart->part->proses ?? '-' }}</p>
                    </div>
                </div>

                {{-- Status & Timestamps --}}
                <div class="space-y-4 md:col-span-2">
                    <h4 class="font-semibold text-gray-700 border-b pb-2">Status & Informasi Lainnya</h4>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $plantgatePart->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $plantgatePart->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Pada</label>
                            <p class="text-gray-900">{{ $plantgatePart->created_at->format('d M Y H:i') }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Diupdate Pada</label>
                            <p class="text-gray-900">{{ $plantgatePart->updated_at->format('d M Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
            <a 
                href="{{ route('submaster.plantgatepart.index') }}" 
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors font-semibold"
            >
                Tutup
            </a>

            <div class="flex gap-2">
                @if(userCan('submaster.plantgatepart.edit'))
                <a 
                    href="{{ route('submaster.plantgatepart.edit', $plantgatePart->id) }}" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors font-semibold flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
                @endif

                @if(userCan('submaster.plantgatepart.delete'))
                <a 
                    href="{{ route('submaster.plantgatepart.delete', $plantgatePart->id) }}" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors font-semibold flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
