@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Finish Good - Scan In</h2>
            <p class="text-gray-600 mt-1">Scan label box yang sudah di-scan out di ASSY</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search & Filter Form --}}
            <form action="{{ route('finishgood.in.index') }}" method="GET" class="flex flex-col md:flex-row gap-2">
                @if(request('sort_by'))
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif

                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" title="Tanggal Mulai">
                    <span class="text-gray-400 text-xs">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" title="Tanggal Akhir">
                </div>
                
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 w-48 md:w-64 text-sm"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 flex items-center gap-1 text-sm font-medium">
                        Filter
                    </button>

                    @if(request('search') || request('start_date') || request('end_date'))
                        <a href="{{ route('finishgood.in.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('finishgood.in.export', request()->all()) }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium text-sm">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden xl:inline">Export</span>
                </a>

                @if(userCan('finishgood.in.create'))
                    <a href="{{ route('finishgood.in.create') }}" class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-md font-bold text-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full mobile-hide-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Part Info</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lot & Produksi</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Qty Total</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Operator</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="tableBody">
                    @forelse($finishGoodIns as $index => $item)
                        @php
                            $finishGoodIn = $item; // Item itu sendiri sudah object TFinishGoodIn
                            $qtyBox = $item->total_box ?? 1;
                            $qtyPcs = $item->total_pcs ?? ($finishGoodIn->qty * $qtyBox);
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono" data-label="No">{{ $finishGoodIns->firstItem() + $index }}</td>
                            
                            {{-- Part Info --}}
                            <td class="px-6 py-4" data-label="Part Info">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-900">
                                        {{ $finishGoodIn->part->nomor_part ?? ($finishGoodIn->assyOut->part->nomor_part ?? '-') }}
                                    </span>
                                    <span class="text-xs text-gray-500 truncate max-w-[200px]">
                                        {{ $finishGoodIn->part->nama_part ?? ($finishGoodIn->assyOut->part->nama_part ?? 'Unknown Part') }}
                                    </span>
                                </div>
                            </td>

                            {{-- Customer --}}
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Customer">
                                @if($finishGoodIn->customer)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        {{ $finishGoodIn->customer }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>

                            {{-- Lot & Prod Info --}}
                            <td class="px-6 py-4" data-label="Lot & Produksi">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-mono font-semibold text-gray-700 bg-gray-100 px-2 py-0.5 rounded w-fit">
                                        {{ $finishGoodIn->lot_number ?? '-' }}
                                    </span>
                                    <div class="text-[10px] text-gray-500 flex gap-2 mt-1">
                                        <span title="Mesin">M: <b>{{ $finishGoodIn->mesin->no_mesin ?? ($finishGoodIn->no_mesin ?? '-') }}</b></span>
                                        <span class="text-gray-300">|</span>
                                        <span title="Shift">S: <b>{{ $finishGoodIn->shift ?? '-' }}</b></span>
                                        <span class="text-gray-300">|</span>
                                        <span title="Tgl Prod">{{ $finishGoodIn->tanggal_produksi ? \Carbon\Carbon::parse($finishGoodIn->tanggal_produksi)->format('Y-m-d') : '-' }}</span>
                                   </div>
                                </div>
                            </td>

                            {{-- Qty --}}
                            <td class="px-6 py-4 text-center" data-label="Qty Total">
                                <div class="flex flex-col items-center">
                                    <span class="text-lg font-bold text-green-600">{{ number_format($qtyPcs) }} <span class="text-xs font-normal text-gray-500">pcs</span></span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full mt-1">
                                        {{ $qtyBox }} Box @ {{ $finishGoodIn->qty }}
                                    </span>
                                </div>
                            </td>

                            {{-- Operator / Waktu --}}
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Operator">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-xs font-bold" title="{{ $finishGoodIn->manpower->nama ?? 'Unknown' }}">
                                        {{ substr($finishGoodIn->manpower->nama ?? ($finishGoodIn->manpower ?? 'U'), 0, 2) }}
                                    </div>
                                    <div class="flex flex-col">
                                        <div class="flex items-center gap-1">
                                            <span class="text-xs font-medium text-gray-900 max-w-[80px] truncate">{{ $finishGoodIn->manpower->nama ?? ($finishGoodIn->manpower ?? '-') }}</span>
                                            @if(($item->total_mp ?? 1) > 1)
                                                <span class="text-[10px] text-indigo-600 bg-indigo-50 px-1.5 py-0.5 rounded-full font-medium" title="{{ $item->total_mp }} operators contributed">+{{ $item->total_mp - 1 }}</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-gray-400">
                                            {{ $finishGoodIn->waktu_scan ? $finishGoodIn->waktu_scan->timezone('Asia/Jakarta')->format('d M H:i') : '-' }}
                                        </span>
                                    </div>
                                </div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-6 py-4 whitespace-nowrap text-center" data-label="Aksi">
                                <div class="flex items-center justify-center gap-2 opacity-100"> <!-- Always visible or group-hover:opacity-100 -->
                                    <button onclick="window.location='{{ route('finishgood.in.detail', $finishGoodIn->id) }}'" class="p-1.5 hover:bg-green-50 text-green-600 rounded-lg transition-colors" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    
                                    @if(userCan('finishgood.in.delete'))
                                    <button onclick="deleteLot({{ $finishGoodIn->id }}, '{{ $finishGoodIn->lot_number }}', {{ $qtyBox }})" class="p-1.5 hover:bg-red-50 text-red-600 rounded-lg transition-colors" title="Hapus Semua Lot Ini">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                    @endif
                                    
                                    <form id="delete-lot-form-{{ $finishGoodIn->id }}" action="{{ route('finishgood.in.destroyLot', $finishGoodIn->id) }}" method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="7" class="px-6 py-16 text-center bg-gray-50/50">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-gray-100 p-4 rounded-full mb-4">
                                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Belum ada data Finish Good</h3>
                                    <p class="mt-1 text-sm text-gray-500 max-w-sm">Data scan masuk akan muncul di sini. Silakan lakukan scanning.</p>
                                    <a href="{{ route('finishgood.in.create') }}" class="mt-4 text-sm font-medium text-green-600 hover:text-green-700">
                                        Mulai Scanning &rarr;
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Rows Per Page --}}
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4 text-sm text-gray-600 order-2 md:order-1">
                <div class="flex items-center gap-2">
                    <span>Tampilkan</span>
                    <select 
                        id="per_page_selector" 
                        class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-green-500 bg-gray-50 text-xs font-medium cursor-pointer"
                        onchange="changePerPage(this.value)"
                    >
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data per halaman</span>
                </div>
                <div class="border-l border-gray-300 h-4 mx-2"></div>
                <div>
                    Menampilkan <span class="font-medium text-gray-900">{{ $finishGoodIns->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $finishGoodIns->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $finishGoodIns->total() }}</span>️ data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $finishGoodIns->appends(request()->all())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

<script>
    function deleteLot(id, lotNumber, qtyBox) {
        if (confirm(`PERINGATAN: Anda akan menghapus Lot Number: ${lotNumber}\n\nLot ini berisi ${qtyBox} box/scan.\nSemua data box dalam lot ini akan terhapus permanen.\n\nLanjutkan hapus?`)) {
            const form = document.getElementById('delete-lot-form-' + id);
            
            // Tampilkan loading state jika perlu, atau langsung submit
            // Gunakan fetch untuk handle response JSON dari destroyLot (opsional, tapi form submit standard reload page)
            // Karena destroyLot return JSON, lebih baik pakai Fetch API biar smooth, tpi form submit biasa jg ok asal redirect/reload logic ada.
            // Oh, destroyLot return JSON. Berarti kalau form submit biasa browser akan nampilin JSON mentah.
            // SAYA HARUS UBAH destroyLot agar redirect back() atau handle via JS Fetch. 
            // Mari kita pakai Fetch saja biar keren.
            
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'DELETE'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Berhasil menghapus Lot!');
                    window.location.reload();
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan sistem.');
            });
        }
    }
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1); // Reset to page 1
        window.location.href = url.toString();
    }
</script>
@endsection
