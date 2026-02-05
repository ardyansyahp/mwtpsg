@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Produksi Inject - Scan Out</h2>
            <p class="text-gray-600 mt-1">Scan label box yang sudah di-print dari inject in</p>
        </div>
        @if(userCan('produksi.inject.create'))
        <a 
            href="{{ route('produksi.inject.createout') }}" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Scan Label Box</span>
        </a>
        @endif
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Scan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Lot Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Planning Run</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress Box</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                    @forelse($injectOuts as $index => $out)
                        @php
                            $planningRun = $out->planningRun;
                            $part = $planningRun && $planningRun->mold ? $planningRun->mold->part : null;
                            $qtyPackingBox = $part ? ($part->QTY_Packing_Box ?? 0) : 0;
                            $targetTotal = $planningRun ? ($planningRun->qty_target_total ?? 0) : 0;
                            $targetBoxCount = $qtyPackingBox > 0 ? (int) ceil($targetTotal / $qtyPackingBox) : 0;
                            
                            // Hitung box yang sudah di-scan dari details (bukan dari count record)
                            $scannedBoxCount = $out->details->count();
                            $percentage = $targetBoxCount > 0 ? ($scannedBoxCount / $targetBoxCount) * 100 : 0;
                            $widthPercentage = min(100, max(0, $percentage));
                            $widthStyle = 'width: ' . $widthPercentage . '%';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $injectOuts->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($out->waktu_scan)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-700">{{ $out->lot_number ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">#{{ $out->planning_run_id ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $part->nomor_part ?? '-' }}</td>
                            <td class="px-6 py-4 text-sm">
                                @if($planningRun && $targetBoxCount > 0)
                                    <div class="flex flex-col gap-1">
                                        <div class="flex items-center gap-2">
                                            <div class="flex-1 bg-gray-200 rounded-full h-2.5">
                                                @php
                                                    $progressColor = $percentage >= 100 ? 'bg-green-600' : ($percentage >= 80 ? 'bg-yellow-500' : 'bg-blue-600');
                                                @endphp
                                                <div 
                                                    class="h-2.5 rounded-full transition-all {{ $progressColor }}"
                                                    style="width: <?php echo $widthPercentage; ?>%"
                                                ></div>
                                            </div>
                                            <span class="text-xs font-medium text-gray-700 whitespace-nowrap">
                                                {{ $scannedBoxCount }}/{{ $targetBoxCount }}
                                            </span>
                                        </div>
                                        <div class="text-xs text-gray-600">
                                            Target: {{ number_format($targetTotal, 0, ',', '.') }} pcs 
                                            ({{ $qtyPackingBox }} pcs/box)
                                        </div>
                                        @if($scannedBoxCount > 0)
                                            <div class="text-xs text-blue-600 mt-1">
                                                Box: 
                                                @foreach($out->details->sortBy('box_number') as $detail)
                                                    <span class="px-1.5 py-0.5 bg-blue-100 rounded">#{{ $detail->box_number }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('produksi.inject.detailout', $out->id) }}" class="text-green-600 hover:text-green-900 transition-colors" title="Detail Box">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>
                                    @if(userCan('produksi.inject.edit'))
                                    <a href="{{ route('produksi.inject.editout', $out->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    @if(userCan('produksi.inject.delete'))
                                    <a href="{{ route('produksi.inject.deleteout', $out->id) }}" class="text-red-600 hover:text-red-900 transition-colors" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data scan out</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan scan label box.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $injectOuts->links() }}
    </div>
</div>

@endsection
