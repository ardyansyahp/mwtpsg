@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Sampah Part</h2>
            <p class="text-gray-600 mt-1">Kelola data part yang telah dihapus</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('submaster.part.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                Kembali
            </a>
            @if(userCan('submaster.part.delete'))
            <form action="{{ route('submaster.part.restore.all') }}" method="POST" onsubmit="return confirm('Pulihkan semua data sampah?')">
                @csrf
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    Restore Semua
                </button>
            </form>
            <form action="{{ route('submaster.part.empty.trash') }}" method="POST" onsubmit="return confirm('PERINGATAN: Pasikan Anda yakin! Data akan hilang selamanya.')">
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dihapus Pada</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($trashed as $index => $part)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $trashed->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono">{{ $part->nomor_part }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $part->nama_part }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $part->customer ? $part->customer->nama_perusahaan : '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $part->deleted_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('submaster.part.restore', $part->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-900" title="Pulihkan">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>
                                    </form>
                                    <form action="{{ route('submaster.part.force.delete', $part->id) }}" method="POST" onsubmit="return confirm('Hapus permanen?')">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus Permanen">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">Tidak ada data sampah</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $trashed->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>
@endsection
