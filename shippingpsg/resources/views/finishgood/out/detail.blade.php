@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a 
                href="{{ route('finishgood.out.index') }}" 
                class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
                title="Kembali"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Detail Finish Good Out</h2>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Info Label --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Label</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                    <div class="text-sm font-mono text-gray-800">{{ $finishGoodOut->lot_number ?? '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan Out</label>
                    <div class="text-sm text-gray-800">{{ optional($finishGoodOut->waktu_scan_out)->format('Y-m-d H:i:s') }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                    <div class="text-sm text-gray-800">
                        @if($finishGoodOut->part)
                            {{ $finishGoodOut->part->nomor_part }} - {{ $finishGoodOut->part->nama_part }}
                        @else
                            -
                        @endif
                    </div>
                </div>
                @if($finishGoodOut->finishGoodIn)
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan In</label>
                        <div class="text-sm text-gray-800">{{ optional($finishGoodOut->finishGoodIn->waktu_scan)->format('Y-m-d H:i:s') }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Manpower</label>
                        <div class="text-sm text-gray-800">{{ $finishGoodOut->finishGoodIn->manpower ?? '-' }}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info SPK --}}
        @if($finishGoodOut->spk)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi SPK (Surat Perintah Pengiriman)</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor SPK</label>
                    <div class="text-sm font-semibold text-gray-800">{{ $finishGoodOut->spk->nomor_spk }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Customer</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk->customer ? $finishGoodOut->spk->customer->nama_perusahaan : '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Plant Gate</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk->plantgate ? $finishGoodOut->spk->plantgate->nama_plantgate : '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk->tanggal->format('d/m/Y') }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Surat Jalan</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk->no_surat_jalan ?? '-' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Plat</label>
                    <div class="text-sm text-gray-800">{{ $finishGoodOut->spk->nomor_plat ?? '-' }}</div>
                </div>
            </div>

            {{-- Detail SPK Parts --}}
            @if($finishGoodOut->spk->details->count() > 0)
            <div class="mt-6 border-t border-gray-200 pt-4">
                <h4 class="text-md font-semibold text-gray-800 mb-3">Parts dalam SPK</h4>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Part</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Qty Packing Box</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Jadwal Delivery (pcs)</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-600 uppercase">Jumlah Pulling (box)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($finishGoodOut->spk->details as $detail)
                            <tr class="{{ $detail->part_id == $finishGoodOut->part_id ? 'bg-blue-50' : '' }}">
                                <td class="px-4 py-2">
                                    @if($detail->part)
                                        <div class="font-mono text-gray-800">{{ $detail->part->nomor_part }}</div>
                                        <div class="text-xs text-gray-600">{{ $detail->part->nama_part }}</div>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-800">{{ $detail->qty_packing_box ?? '-' }}</td>
                                <td class="px-4 py-2 text-gray-800">{{ number_format($detail->jadwal_delivery_pcs ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 text-gray-800">{{ number_format($detail->jumlah_pulling_box ?? 0, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Catatan --}}
        @if($finishGoodOut->catatan)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Catatan</h3>
            <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $finishGoodOut->catatan }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
