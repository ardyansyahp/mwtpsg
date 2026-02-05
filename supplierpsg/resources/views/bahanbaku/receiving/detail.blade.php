@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Receiving Detail</h2>
            <p class="text-gray-600 mt-1">Detail receiving + download label</p>
        </div>
        <div class="flex items-center gap-2">
            <a
                href="{{ route('bahanbaku.receiving.labels', $receiving->id) }}"
                target="_blank"
                class="bg-gray-900 hover:bg-black text-white px-4 py-2 rounded-lg transition-colors text-sm"
            >
                Download Label (Print/PDF)
            </a>
            <a
                href="{{ route('bahanbaku.receiving.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors text-sm"
            >
                Tutup
            </a>
        </div>
    </div>

    {{-- Header info --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
            <div><span class="text-gray-500">Tanggal:</span> <span class="font-medium">{{ optional($receiving->tanggal_receiving)->format('Y-m-d') }}</span></div>
            <div><span class="text-gray-500">Supplier:</span> <span class="font-medium">{{ $receiving->supplier->nama_perusahaan ?? '-' }}</span></div>
            <div><span class="text-gray-500">Shift:</span> <span class="font-medium">{{ $receiving->shift ?? '-' }}</span></div>
            <div><span class="text-gray-500">No Surat Jalan:</span> <span class="font-medium">{{ $receiving->no_surat_jalan ?? '-' }}</span></div>
            <div><span class="text-gray-500">No PO:</span> <span class="font-medium">{{ $receiving->no_purchase_order ?? '-' }}</span></div>
            <div><span class="text-gray-500">Manpower:</span> <span class="font-medium">{{ $receiving->manpower ?? '-' }}</span></div>
        </div>
    </div>

    {{-- Detail table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nomor Bahan Baku</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lot Number</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">QRCode</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Label</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200" id="detailTableBody">
                    @forelse($receiving->details as $i => $d)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $i + 1 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $d->nomor_bahan_baku ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $d->bahanBaku->nama_bahan_baku ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">{{ $d->lot_number ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ $d->qty }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 font-mono">{{ $d->qrcode }}</td>
                            <td class="px-4 py-3 text-center">
                                <a
                                    href="{{ route('bahanbaku.receiving.labels', $receiving->id) }}#qr-{{ $d->id }}"
                                    target="_blank"
                                    class="text-blue-600 hover:text-blue-800 text-sm"
                                >
                                    Buka
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada detail.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
