@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Stock Opname</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Manajemen Stok Finish Good - Periode {{ date('F Y') }}</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search Form --}}
            <form action="{{ route('stock.opname.index') }}" method="GET" class="flex gap-2">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari Part..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('stock.opname.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300 flex items-center justify-center" title="Reset Filters">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2">
                {{-- Data Tools --}}
                <div class="flex items-center gap-2 ml-2">
                    {{-- Export --}}
                    <a href="{{ route('stock.opname.export', request()->all()) }}" target="_blank" class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" title="Download Template / Export Current Stock">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    
                    {{-- Import --}}
                    <a href="{{ route('stock.opname.import.form') }}" class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" title="Import Stock Update">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-4 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('stock.opname.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-64">Part Number</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-72">Part Name</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Cust</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-700 uppercase tracking-wider bg-gray-100 border-x border-gray-200 w-24" title="Stok tercatat sistem">Stok Sistem<br><span class="text-[9px] font-normal text-gray-500">(Awal)</span></th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-blue-700 uppercase tracking-wider bg-blue-50 border-r border-blue-100 w-24" title="Masukkan hasil hitung">Stok Fisik<br><span class="text-[9px] font-normal text-blue-500">(Input)</span></th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-20">Selisih</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($stocks as $index => $stock)
                        @php
                            $systemQty = $stock->stockFg->qty ?? 0;
                            // Check if there is a recent opname (e.g. from seeder or manual input)
                            // Use latest opname actual qty if available, otherwise default to system qty
                            $defaultInput = $stock->latestStockOpname->qty_actual ?? $systemQty; 
                            $lastOpnameDate = $stock->latestStockOpname ? $stock->latestStockOpname->created_at->format('d M Y H:i') : null;
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $stocks->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-bold text-gray-900 block">{{ $stock->nomor_part }}</span>
                                <input type="hidden" name="opname_data[{{ $index }}][part_id]" value="{{ $stock->id }}">
                                
                                {{-- Last Opname Info --}}
                                @if($lastOpnameDate)
                                <span class="text-[9px] text-gray-400 block mt-1" title="Terakhir diopname pada tanggal ini">Last: {{ $lastOpnameDate }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $stock->nama_part }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                    {{ $stock->customer->inisial_perusahaan ?? substr($stock->customer->nama_perusahaan ?? 'GEN', 0, 3) }}
                                </span>
                            </td>
                            {{-- System Qty (Readonly) --}}
                            <td class="px-6 py-4 text-center bg-gray-50 border-x border-gray-100">
                                <span class="text-sm font-bold text-gray-700 system-qty-display">
                                    {{ number_format($systemQty) }}
                                </span>
                                <input type="hidden" class="system-qty-input" value="{{ $systemQty }}">
                            </td>
                            {{-- Actual Qty (Input) --}}
                            <td class="px-6 py-4 text-center bg-blue-50/30 border-r border-blue-100/50 p-0">
                                <input 
                                    type="number" 
                                    name="opname_data[{{ $index }}][qty_actual]" 
                                    value="{{ $defaultInput }}" 
                                    class="actual-qty-input w-full h-full px-2 py-3 border-none bg-transparent text-center focus:ring-0 focus:bg-white font-bold text-blue-700"
                                    min="0"
                                    oninput="calculateDiff(this)"
                                >
                            </td>
                            {{-- Diff (Auto Calculated) --}}
                            <td class="px-6 py-4 text-center align-middle">
                                <span class="diff-display text-xs font-black text-gray-300 inline-block py-1">-</span>
                            </td>
                            <td class="px-6 py-4">
                                <input 
                                    type="text" 
                                    name="opname_data[{{ $index }}][keterangan]" 
                                    placeholder="Ket..." 
                                    class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-xs"
                                >
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                                    <p class="text-sm font-medium">Tidak ada data part ditemukan</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-4">
                    {{-- Pagination Controls --}}
                    <div class="flex-1 w-full flex flex-col md:flex-row justify-between items-center gap-4">
                        {{-- Left: Info & Per Page --}}
                        <div class="text-sm text-gray-500 flex flex-col sm:flex-row items-center gap-2">
                            <div class="flex items-center gap-2">
                                <span>Show</span>
                                <select onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)" class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white text-xs font-medium cursor-pointer">
                                    @foreach([10, 25, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" {{ request('per_page', $stocks->perPage()) == $perPage ? 'selected' : '' }}>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <span class="hidden sm:inline border-l border-gray-300 h-4 mx-2"></span>
                            <div>
                                Record: <span class="font-bold text-gray-900">{{ $stocks->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-900">{{ $stocks->lastItem() ?? 0 }}</span> of <span class="font-bold text-gray-900">{{ $stocks->total() }}</span>
                            </div>
                        </div>

                        {{-- Right: Links --}}
                        <div>
                            {{ $stocks->appends(request()->all())->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                <div class="flex justify-end border-t border-gray-200 pt-4 mt-2 gap-3">
                     <button type="reset" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm transition-colors">
                        Reset Not saved
                     </button>
                     <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold shadow-md shadow-blue-200 transition-all flex items-center gap-2 hover:translate-y-[-1px]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2-2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Simpan Hasil Opname
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        var newUri = uri;
        if (uri.match(re)) {
            newUri = uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            newUri = uri + separator + key + "=" + value;
        }
        var pageRe = new RegExp("([?&])page=.*?(&|$)", "i");
        if (newUri.match(pageRe)) {
            newUri = newUri.replace(pageRe, '$1page=1$2');
        } else {
             newUri = newUri + (newUri.indexOf('?') !== -1 ? "&" : "?") + "page=1";
        }
        return newUri;
    }

    // Auto Calculate Difference
    function calculateDiff(input) {
        const row = input.closest('tr');
        const systemQtyInput = row.querySelector('.system-qty-input');
        const diffDisplay = row.querySelector('.diff-display');
        
        const systemQty = parseInt(systemQtyInput.value) || 0;
        const actualQty = parseInt(input.value) || 0;
        const diff = actualQty - systemQty;
        
        // Remove existing classes regarding colors (updated with bolder classes)
        diffDisplay.classList.remove(
            'text-green-600', 'bg-green-50', 'border-green-100', 
            'text-green-700', 'bg-green-100', 'border-green-300',
            'text-red-600', 'bg-red-50', 'border-red-100', 
            'text-red-700', 'bg-red-100', 'border-red-300',
            'text-gray-400', 'bg-gray-50', 'border-gray-100', 
            'text-gray-600', 'bg-gray-100', 'border-gray-300',
            'border', 'px-2', 'py-0.5', 'rounded', 'font-bold'
        );

        // Reset text color for empty state
        diffDisplay.classList.remove('text-gray-300', 'text-gray-400');
        
        if (input.value === '') {
             diffDisplay.textContent = '-';
             diffDisplay.classList.add('text-gray-400', 'font-bold');
             return;
        }

        let diffText = diff > 0 ? '+' + diff : diff;
        
        // Add common base classes
        diffDisplay.classList.add('border', 'px-2', 'py-0.5', 'rounded', 'font-bold');

        if (diff > 0) {
            // Positive: Darker Green
            diffDisplay.classList.add('text-green-700', 'bg-green-100', 'border-green-300');
        } else if (diff < 0) {
            // Negative: Darker Red
            diffDisplay.classList.add('text-red-700', 'bg-red-100', 'border-red-300');
        } else {
             // Zero: Darker Gray
             diffDisplay.classList.add('text-gray-600', 'bg-gray-100', 'border-gray-300');
        }

        diffDisplay.textContent = diffText;
    }

    // Calculate on load for all
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.actual-qty-input').forEach(input => calculateDiff(input));
    });
</script>
@endsection
