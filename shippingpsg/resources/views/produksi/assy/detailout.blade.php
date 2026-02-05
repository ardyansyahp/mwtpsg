@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('produksi.assy.index') }}" class="flex items-center gap-2 text-gray-500 hover:text-gray-700 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Detail ASSY Out</h2>
            <p class="text-gray-600 mt-1">Daftar box ASSY In untuk lot number: <span class="font-mono font-semibold">{{ $assyOut->lot_number ?? '-' }}</span></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Lot Number</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                <div class="text-sm font-mono text-gray-800">{{ $assyOut->lot_number ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan Out</label>
                <div class="text-sm text-gray-800">{{ optional($assyOut->waktu_scan)->format('Y-m-d H:i:s') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                <div class="text-sm text-gray-800">
                    @if($assyOut->part)
                        {{ $assyOut->part->nomor_part }} - {{ $assyOut->part->nama_part }}
                    @elseif($assyOut->assyIn && $assyOut->assyIn->part)
                        {{ $assyOut->assyIn->part->nomor_part }} - {{ $assyOut->assyIn->part->nama_part }}
                    @else
                        -
                    @endif
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Total Box Scan Out</label>
                <div class="text-sm font-semibold text-blue-600">{{ $relatedAssyOuts->count() }} box</div>
            </div>
            @if($assyOut->assyIn && $assyOut->assyIn->manpower)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Manpower</label>
                    <div class="text-sm text-gray-800">{{ $assyOut->assyIn->manpower }}</div>
                </div>
            @endif
            @if($assyOut->assyIn && $assyOut->assyIn->supplyDetail && $assyOut->assyIn->supplyDetail->supply && $assyOut->assyIn->supplyDetail->supply->meja)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Meja</label>
                    <div class="text-sm text-gray-800">
                        <span class="px-2 py-1 rounded text-white bg-blue-600 font-medium">
                            {{ $assyOut->assyIn->supplyDetail->supply->meja }}
                        </span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Box yang Di-scan Out</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Box</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Scan Out</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Scan In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sumber ASSY In</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($relatedAssyOuts as $index => $out)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-3 py-1 rounded text-white bg-green-600 font-medium">Box #{{ $index + 1 }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ optional($out->waktu_scan)->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $out->assyIn ? optional($out->assyIn->waktu_scan)->format('Y-m-d H:i:s') : '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($out->assyIn)
                                    @if($out->assyIn->supplyDetail)
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-medium">
                                                Supply
                                            </span>
                                            @if($out->assyIn->supplyDetail->receivingDetail && $out->assyIn->supplyDetail->receivingDetail->bahanBaku)
                                                <span class="text-xs text-gray-600">
                                                    {{ $out->assyIn->supplyDetail->receivingDetail->bahanBaku->nama_bahan_baku }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                    
                                    @if($out->assyIn->wipOut)
                                        <div class="flex items-center gap-2 {{ $out->assyIn->supplyDetail ? 'mt-1' : '' }}">
                                            <span class="px-2 py-1 bg-purple-100 text-purple-800 rounded text-xs font-medium">
                                                WIP Out
                                            </span>
                                            <span class="text-xs text-gray-600">
                                                Box #{{ $out->assyIn->wipOut->box_number ?? '-' }}
                                            </span>
                                        </div>
                                    @endif
                                    
                                    @if(!$out->assyIn->supplyDetail && !$out->assyIn->wipOut)
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $out->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data box</h3>
                                <p class="mt-1 text-sm text-gray-500">Belum ada box yang di-scan out untuk lot number ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route('produksi.assy.index') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
            Tutup
        </a>
    </div>
</div>
@endsection
