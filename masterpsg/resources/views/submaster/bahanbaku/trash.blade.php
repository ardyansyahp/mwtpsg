@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Sampah Bahan Baku</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Kelola data bahan baku yang telah dihapus (soft delete)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('master.bahanbaku.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors">
                Kembali ke Index
            </a>
            @if(userCan('master.bahanbaku.delete'))
                 <form action="{{ route('master.bahanbaku.restore.all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan SEMUA data?')">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Pulihkan Semua
                    </button>
                </form>
                <form action="{{ route('master.bahanbaku.empty.trash') }}" method="POST" onsubmit="return confirm('PERINGATAN: Tindakan ini akan menghapus SEMUA data di sampah secara permanen dan TIDAK BISA dibatalkan. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Kosongkan Sampah
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor BB</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama/Deskripsi</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dihapus Pada</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($trashed as $index => $bb)
                        <tr class="hover:bg-gray-50 transition-colors">
                             <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-900">{{ $trashed->firstItem() + $index }}</td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $bb->kategori_label }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-700 font-mono font-bold">
                                {{ $bb->nomor_bahan_baku ?? '-' }}
                            </td>
                            <td class="px-4 py-2 text-xs text-gray-900">
                                {{ $bb->nama_bahan_baku }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs text-gray-500">
                                {{ $bb->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-xs font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if(userCan('master.bahanbaku.delete'))
                                        <form action="{{ route('master.bahanbaku.restore', $bb->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-600 hover:text-green-900" title="Pulihkan">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('master.bahanbaku.force.delete', $bb->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus permanen data ini?')">
                                            @csrf
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus Permanen">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                Tidak ada data di sampah.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white px-6 py-4 border-t border-gray-200">
             {{ $trashed->appends(request()->all())->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
