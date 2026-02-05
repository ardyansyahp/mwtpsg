@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Sampah Kendaraan
            </h2>
            <p class="text-gray-600 mt-1">Pulihkan data yang terhapus atau hapus permanen.</p>
        </div>
        <a href="{{ route('master.kendaraan.index') }}" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors">
            Kembali ke Index
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <span class="text-sm text-gray-600">Menampilkan items yang dihapus</span>
            
            @if($trashed->count() > 0)
                <div class="flex gap-2">
                     <form action="{{ route('master.kendaraan.restore.all') }}" method="POST" onsubmit="return confirm('Pulihkan SEMUA data?')">
                        @csrf
                        <button type="submit" class="text-sm bg-green-100 text-green-700 px-3 py-1.5 rounded hover:bg-green-200 transition font-medium">
                            Restore All
                        </button>
                    </form>
                    <form action="{{ route('master.kendaraan.empty.trash') }}" method="POST" onsubmit="return confirm('HAPUS PERMANEN semua data di sampah? Tindakan ini tidak bisa dibatalkan!')">
                        @csrf
                        <button type="submit" class="text-sm bg-red-100 text-red-700 px-3 py-1.5 rounded hover:bg-red-200 transition font-medium">
                            Empty Trash
                        </button>
                    </form>
                </div>
            @endif
        </div>

        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nopol</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Merk</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($trashed as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $item->nopol_kendaraan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->jenis_kendaraan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->merk_kendaraan }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->deleted_at->format('d M Y H:i') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                            <div class="flex justify-center gap-2">
                                <form action="{{ route('master.kendaraan.restore', $item->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-900 bg-green-50 px-2 py-1 rounded" title="Restore">
                                        Pulihkan
                                    </button>
                                </form>
                                <form action="{{ route('master.kendaraan.force.delete', $item->id) }}" method="POST" onsubmit="return confirm('Hapus permanen?')">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded" title="Force Delete">
                                        Hapus Permanen
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <p>Tempat sampah kosong.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
         <div class="p-4 border-t border-gray-200">
            {{ $trashed->links() }}
        </div>
    </div>
</div>
@endsection
