@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route(\'dashboard\') }}"\1>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            <span class="font-medium">Kembali</span>
        </a>
            <h2 class="text-3xl font-bold text-gray-800">Detail Box - Inject Out</h2>
            <p class="text-gray-600 mt-1">Daftar box untuk lot number: <span class="font-mono font-semibold">{{ $injectOut->lot_number ?? '-' }}</span></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Lot Number</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Lot Number</label>
                <div class="text-sm font-mono text-gray-800">{{ $injectOut->lot_number ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Waktu Scan Terakhir</label>
                <div class="text-sm text-gray-800">{{ optional($injectOut->waktu_scan)->format('Y-m-d H:i:s') }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Planning Run</label>
                <div class="text-sm text-gray-800">#{{ $injectOut->planning_run_id ?? '-' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Part</label>
                <div class="text-sm text-gray-800">
                    @if($injectOut->planningRun && $injectOut->planningRun->mold && $injectOut->planningRun->mold->part)
                        {{ $injectOut->planningRun->mold->part->nomor_part ?? '-' }}
                    @else
                        -
                    @endif
                </div>
            </div>
            @if($injectOut->injectIn && $injectOut->injectIn->mesin)
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Mesin</label>
                    <div class="text-sm text-gray-800">{{ $injectOut->injectIn->mesin->no_mesin ?? '-' }}</div>
                </div>
            @endif
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Total Box</label>
                <div class="text-sm font-semibold text-blue-600">{{ $injectOut->details->count() }} box</div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-800">Daftar Box</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Box Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waktu Scan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($injectOut->details as $index => $detail)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-3 py-1 rounded text-white bg-blue-600 font-medium">Box #{{ $detail->box_number ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ optional($detail->waktu_scan)->format('Y-m-d H:i:s') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $detail->catatan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data box</h3>
                                <p class="mt-1 text-sm text-gray-500">Belum ada box yang di-scan untuk lot number ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <a href="{{ route(\'dashboard\') }}"\1>
                Tutup
            </a>
    </div>
</div>
@endsection
