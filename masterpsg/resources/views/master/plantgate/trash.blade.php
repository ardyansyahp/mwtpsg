@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Back Button --}}
    <a href="{{ route('master.plantgate.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-6 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="font-medium">Kembali ke Daftar</span>
    </a>

    {{-- Header --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Recycle Bin Plant Gate</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Pulihkan data plant gate yang dihapus</p>
        </div>
        
        @if($trashed->count() > 0 && userCan('master.plantgate.delete'))
        <form action="{{ route('master.plantgate.empty.trash') }}" method="POST" onsubmit="return confirm('APAKAH ANDA YAKIN? Semua data di tong sampah akan dihapus PERMANEN dan tidak bisa dikembalikan.');">
            @csrf
            <button 
                type="submit" 
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Kosongkan Sampah</span>
            </button>
        </form>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Restore All Button --}}
    @if($trashed->count() > 0 && userCan('master.plantgate.delete'))
    <div class="mb-4">
         <form action="{{ route('master.plantgate.restore.all') }}" method="POST" onsubmit="return confirm('Pulihkan SEMUA data di tong sampah?');">
            @csrf
            <button type="submit" class="text-green-600 hover:text-green-800 font-medium flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Pulihkan Semua
            </button>
        </form>
    </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plant Gate</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dihapus Pada</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($trashed as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                             {{ $trashed->firstItem() + $index }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $item->nama_plantgate }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $item->customer->nama_perusahaan ?? '-' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $item->deleted_at->format('d M Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center gap-2">
                                {{-- Restore --}}
                                <form action="{{ route('master.plantgate.restore', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-100 text-green-700 hover:bg-green-200 px-3 py-1 rounded transition-colors text-xs font-semibold" title="Pulihkan">
                                        Restore
                                    </button>
                                </form>

                                {{-- Force Delete --}}
                                <form action="{{ route('master.plantgate.force.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus permanen? Data tidak bisa kembali.');">
                                    @csrf
                                    <button type="submit" class="bg-red-100 text-red-700 hover:bg-red-200 px-3 py-1 rounded transition-colors text-xs font-semibold" title="Hapus Permanen">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <p>Tong sampah kosong.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
         <div class="bg-white px-6 py-4 border-t border-gray-200">
            {{ $trashed->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
