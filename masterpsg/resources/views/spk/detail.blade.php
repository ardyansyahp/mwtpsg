@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('spk.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Detail Surat Perintah Pengiriman</h2>
            <p class="text-gray-600 mt-1">SPK: {{ $spk->nomor_spk }}</p>
        </div>
    </div>

    <div class="space-y-6">
        {{-- Header Info --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi SPK</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor SPK</label>
                    <p class="text-sm font-medium text-gray-900">{{ $spk->nomor_spk }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal & Jam (Deadline)</label>
                    <p class="text-sm text-gray-900">{{ optional($spk->tanggal)->format('d/m/Y H:i') }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Customer</label>
                    <p class="text-sm text-gray-900">{{ $spk->customer->nama_perusahaan ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Plant Gate</label>
                    <p class="text-sm text-gray-900">{{ $spk->plantgate->nama_plantgate ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Model Part</label>
                    <p class="text-sm">
                        <span class="px-2 py-1 rounded text-white bg-blue-600 text-xs font-medium uppercase">{{ $spk->model_part }}</span>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Manpower Pembuat</label>
                    <p class="text-sm text-gray-900">{{ $spk->manpower_pembuat ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Cycle</label>
                    <p class="text-sm">
                        <span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full font-bold text-xs">C{{ $spk->cycle_number ?? $spk->cycle ?? 1 }}</span>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">No. Surat Jalan</label>
                    <p class="text-sm text-gray-900">{{ $spk->no_surat_jalan ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nomor Plat</label>
                    <p class="text-sm text-gray-900">{{ $spk->nomor_plat ?? '-' }}</p>
                </div>
                @if($spk->catatan)
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <p class="text-sm text-gray-900">{{ $spk->catatan }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Detail Part --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Part</h3>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Part</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Part</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Packing Box</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal Delivery (pcs)</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Pulling Box</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($spk->details as $index => $detail)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $detail->part->nomor_part ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $detail->part->nama_part ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($detail->qty_packing_box, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($detail->jadwal_delivery_pcs, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format($detail->jumlah_pulling_box, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">{{ $detail->catatan ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    Tidak ada detail part
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('spk.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">
                Tutup
            </a>
        </div>
    </div>
</div>
@endsection
