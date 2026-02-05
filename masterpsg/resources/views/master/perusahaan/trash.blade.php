@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('master.perusahaan.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">Recycle Bin Perusahaan</h2>
                <p class="text-gray-600 mt-1">Pulihkan data perusahaan yang dihapus</p>
            </div>
            
            <div class="flex gap-2">
                @if($perusahaans->count() > 0 && userCan('master.perusahaan.delete'))
                <form action="{{ route('master.perusahaan.empty.trash') }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin mengosongkan sampah? Data akan hilang SELAMANYA dan tidak bisa kembali.');">
                    @csrf
                    <button 
                        type="submit" 
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Empty Trash</span>
                    </button>
                </form>

                <form action="{{ route('master.perusahaan.restore.all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin memulihkan SEMUA data?');">
                    @csrf
                    <button 
                        type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <span>Restore All</span>
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Success Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($perusahaans as $index => $perusahaan)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $perusahaans->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $perusahaan->nama_perusahaan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $perusahaan->deleted_at->format('d M Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                @if(userCan('master.perusahaan.delete'))
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('master.perusahaan.restore', $perusahaan->id) }}" method="POST">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-green-600 hover:text-green-900 transition-colors bg-green-50 px-3 py-1 rounded-md border border-green-200 hover:bg-green-100"
                                            title="Pulihkan"
                                        >
                                            Restore
                                        </button>
                                    </form>

                                    <form action="{{ route('master.perusahaan.force.delete', $perusahaan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus permanen data ini?');">
                                        @csrf
                                        <button 
                                            type="submit" 
                                            class="text-red-600 hover:text-red-900 transition-colors bg-red-50 px-3 py-1 rounded-md border border-red-200 hover:bg-red-100"
                                            title="Hapus Permanen"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tong Sampah Kosong</h3>
                                <p class="mt-1 text-sm text-gray-500">Tidak ada data perusahaan yang dihapus.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="bg-white px-6 py-4 border-t border-gray-200" id="paginationInfo">
            {{ $perusahaans->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
