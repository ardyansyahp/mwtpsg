@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Detail ASSY In</h2>
            <p class="text-gray-600 mt-1">Detail data ASSY In dengan informasi subpart dan box</p>
        </div>
    </div>

    @forelse($detailData as $index => $data)
        @php
            $percentage = $data['target_box_count'] > 0 ? ($data['scanned_box_count'] / $data['target_box_count']) * 100 : 0;
            $widthPercentage = min(100, max(0, $percentage));
            $progressColor = $percentage >= 100 ? 'bg-green-600' : ($percentage >= 80 ? 'bg-yellow-500' : 'bg-blue-600');
        @endphp
        
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-white">Lot Number: {{ $data['lot_number'] }}</h3>
                        <p class="text-blue-100 text-sm mt-1">
                            Waktu Scan: {{ optional($data['waktu_scan'])->format('d/m/Y H:i:s') }}
                            @if($data['manpower'])
                                • Manpower: {{ $data['manpower'] }}
                            @endif
                            @if($data['meja'])
                                • Meja: {{ $data['meja'] }}
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        @if($data['planning_run'])
                            <span class="inline-block px-3 py-1 bg-white/20 rounded-full text-white text-sm font-medium">
                                Planning Run #{{ $data['planning_run']->id }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="p-6 space-y-6">
                {{-- Part yang Dihasilkan --}}
                @if($data['part'])
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                        <h4 class="text-sm font-semibold text-green-900 mb-1">🎯 Part yang Dihasilkan</h4>
                        <p class="text-green-800 font-medium">{{ $data['part']->nomor_part }} - {{ $data['part']->nama_part }}</p>
                        @if($data['qty_packing_box'] > 0)
                            <p class="text-green-700 text-sm mt-1">Qty Packing: {{ $data['qty_packing_box'] }} pcs/box</p>
                        @endif
                    </div>
                @endif

                {{-- Progress Box --}}
                @if($data['target_box_count'] > 0)
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 mb-3">📦 Progress Box</h4>
                        <div class="flex items-center gap-3 mb-2">
                            <div class="flex-1 bg-gray-200 rounded-full h-4">
                                <div 
                                    class="h-4 rounded-full transition-all flex items-center justify-center text-white text-xs font-bold {{ $progressColor }}"
                                    style="width: <?php echo $widthPercentage; ?>%"
                                >
                                    @if($widthPercentage > 20)
                                        {{ number_format($percentage, 0) }}%
                                    @endif
                                </div>
                            </div>
                            <span class="text-sm font-bold text-gray-700 whitespace-nowrap min-w-[80px] text-right">
                                {{ $data['scanned_box_count'] }}/{{ $data['target_box_count'] }} box
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mb-3">
                            Target Total: {{ number_format($data['target_total'], 0, ',', '.') }} pcs
                        </p>
                        
                        {{-- Box Numbers --}}
                        @if($data['scanned_box_count'] > 0)
                            <div class="flex flex-wrap gap-2">
                                @for($i = 1; $i <= $data['target_box_count']; $i++)
                                    @php
                                        $isScanned = in_array($i, $data['box_numbers']) || $i <= $data['scanned_box_count'];
                                        $boxClass = $isScanned 
                                            ? 'bg-blue-500 text-white border-blue-600' 
                                            : 'bg-gray-100 text-gray-400 border-gray-300';
                                    @endphp
                                    <span class="px-3 py-1.5 text-sm font-medium rounded border-2 {{ $boxClass }}">
                                        Box #{{ $i }}
                                    </span>
                                @endfor
                            </div>
                        @endif
                    </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Subpart yang Digunakan (dari Supply) --}}
                    @if(count($data['subparts']) > 0)
                        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                            <h4 class="text-sm font-semibold text-yellow-900 mb-3">🔧 Subpart yang Digunakan (Supply)</h4>
                            <div class="space-y-2">
                                @foreach($data['subparts'] as $subpart)
                                    <div class="bg-white rounded p-3 border border-yellow-200">
                                        <p class="font-medium text-gray-900">{{ $subpart['nama'] }}</p>
                                        <p class="text-sm text-gray-600">
                                            Qty: {{ $subpart['qty'] }} {{ $subpart['satuan'] }}
                                        </p>
                                        @if($subpart['lot_number'])
                                            <p class="text-xs text-gray-500 font-mono mt-1">
                                                Lot: {{ $subpart['lot_number'] }}
                                            </p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(count($data['supplied_lot_numbers']) > 0)
                                <div class="mt-3 pt-3 border-t border-yellow-300">
                                    <p class="text-xs text-yellow-800 font-medium mb-1">Lot Number Supply:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($data['supplied_lot_numbers'] as $lotNum)
                                            <span class="px-2 py-0.5 bg-yellow-200 text-yellow-900 rounded text-xs font-mono">
                                                {{ $lotNum }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- WIP Out Boxes yang Di-scan --}}
                    @if(count($data['wip_out_boxes']) > 0)
                        <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                            <h4 class="text-sm font-semibold text-purple-900 mb-3">📦 Box dari WIP Out</h4>
                            <div class="space-y-2 max-h-64 overflow-y-auto">
                                @foreach($data['wip_out_boxes'] as $box)
                                    <div class="bg-white rounded p-3 border border-purple-200">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900">Box #{{ $box['box_number'] }}</p>
                                                <p class="text-xs text-gray-500 font-mono mt-0.5">
                                                    {{ $box['lot_number'] }}
                                                </p>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs text-gray-600">
                                                    {{ optional($box['waktu_scan'])->format('H:i:s') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            
                            @if(count($data['wip_out_lot_numbers']) > 0)
                                <div class="mt-3 pt-3 border-t border-purple-300">
                                    <p class="text-xs text-purple-800 font-medium mb-1">Lot Number WIP Out:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($data['wip_out_lot_numbers'] as $lotNum)
                                            <span class="px-2 py-0.5 bg-purple-200 text-purple-900 rounded text-xs font-mono">
                                                {{ $lotNum }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            
                            <div class="mt-3 pt-3 border-t border-purple-300">
                                <p class="text-sm text-purple-900">
                                    <span class="font-semibold">Total:</span> {{ count($data['wip_out_boxes']) }} box di-scan
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200">
                    <a 
                        href="{{ route('produksi.assy.editin', $data['id']) }}"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors flex items-center gap-2" 
                        title="Edit" 
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        <span class="text-sm font-medium">Edit</span>
                    </a>
                    <a 
                        href="{{ route('produksi.assy.deletein', $data['id']) }}"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors flex items-center gap-2" 
                        title="Hapus" 
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span class="text-sm font-medium">Hapus</span>
                    </a>
                </div>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="mt-4 text-lg font-medium text-gray-900">Tidak ada data</h3>
            <p class="mt-2 text-gray-500">Belum ada data ASSY In yang di-scan.</p>
        </div>
    @endforelse
</div>

@endsection
