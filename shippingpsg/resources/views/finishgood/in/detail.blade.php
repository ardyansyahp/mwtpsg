@extends('layout.app')

@section('content')
<div class="fade-in max-w-[95%] mx-auto px-4 sm:px-6 lg:px-8 py-4">
    {{-- Header Section --}}
    <div class="mb-4"> {{-- Reduced from mb-8 --}}
        <div class="flex items-center gap-4 mb-4">
            <a href="{{ route('finishgood.in.index') }}" class="p-2 rounded-full hover:bg-gray-100 text-gray-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Lot Number</h1>
                <p class="text-sm text-gray-500">Kelola item dalam lot produksi ini</p>
            </div>
        </div>

        {{-- Summary Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Lot Number</span>
                    <span class="text-xl font-mono font-bold text-gray-900 bg-gray-50 px-3 py-1 rounded inline-block">
                        {{ $finishGoodIn->lot_number ?? '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Part Number</span>
                    <span class="text-lg font-bold text-gray-800 break-words">
                        {{ $finishGoodIn->part->nomor_part ?? '-' }}
                    </span>
                    <span class="text-xs text-gray-500 block truncate">{{ $finishGoodIn->part->nama_part ?? '' }}</span>
                </div>
                <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Total Quantity</span>
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-green-600">{{ $items->sum('qty') }}</span>
                        <span class="text-sm text-gray-500">pcs <span class="text-xs">({{ $items->count() }} Boxes)</span></span>
                    </div>
                </div>
                 <div>
                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Info Produksi</span>
                    <div class="text-sm text-gray-700">
                        <p>Customer: <b>{{ $finishGoodIn->customer ?? '-' }}</b></p>
                        <p>Mesin: <b>{{ $finishGoodIn->mesin->no_mesin ?? ($finishGoodIn->no_mesin ?? '-') }}</b></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Items Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden flex flex-col h-[600px]">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex-none flex justify-between items-center">
            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Daftar Scan Box ({{ $items->count() }})</h3>
        </div>
        
        <div class="overflow-y-auto flex-1 p-0">
            <table class="w-full relative">
                <thead class="bg-gray-50 border-b border-gray-200 sticky top-0 z-10 shadow-sm">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase w-16 bg-gray-50">No</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase bg-gray-50">Waktu Scan</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase bg-gray-50">Qty (Pcs)</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase bg-gray-50">Operator</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase bg-gray-50">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($items as $index => $item)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-700">
                                {{ $item->waktu_scan ? $item->waktu_scan->timezone('Asia/Jakarta')->format('d M Y, H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded-full">
                                    {{ $item->qty }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold flex-shrink-0">
                                    {{ substr($item->manpower->nama ?? 'U', 0, 1) }}
                                </div>
                                <span class="truncate max-w-[150px]">{{ $item->manpower->nama ?? 'Unknown' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center"> {{-- Centered action column --}}
                                <div onclick="deleteItem({{ $item->id }})" class="cursor-pointer text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-lg transition-all inline-block" title="Hapus Box Ini">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </div>
                                
                                <form id="delete-form-{{ $item->id }}" action="{{ route('finishgood.in.destroy', $item->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada item.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function deleteItem(id) {
        if (confirm('Apakah Anda yakin ingin menghapus box ini?')) {
            document.getElementById('delete-form-' + id).submit();
        }
    }
</script>
@endsection
