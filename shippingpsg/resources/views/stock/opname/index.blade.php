@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Stock Opname</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Manajemen Stok Finish Good</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part Number</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part Name</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">System Qty</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Actual Qty</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($stocks as $index => $stock)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $stocks->firstItem() + $index }}</td>
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm font-medium text-gray-900">{{ $stock->nomor_part }}</span>
                                <input type="hidden" name="opname_data[{{ $index }}][part_id]" value="{{ $stock->id }}">
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $stock->nama_part }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                    {{ $stock->customer->inisial_perusahaan ?? substr($stock->customer->nama_perusahaan ?? 'GEN', 0, 3) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ number_format($stock->stockFg->qty ?? 0) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <input 
                                    type="number" 
                                    name="opname_data[{{ $index }}][qty_actual]" 
                                    value="{{ $stock->stockFg->qty ?? 0 }}" 
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-center focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                    min="0"
                                >
                            </td>
                            <td class="px-6 py-4">
                                <input 
                                    type="text" 
                                    name="opname_data[{{ $index }}][keterangan]" 
                                    placeholder="Alasan perubahan..." 
                                    class="w-full px-2 py-1 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500 text-sm"
                                >
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Tidak ada data part
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
                                <span>Tampilkan</span>
                                <select onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)" class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white text-xs font-medium cursor-pointer">
                                    @foreach([10, 25, 50, 100] as $perPage)
                                        <option value="{{ $perPage }}" {{ request('per_page', $stocks->perPage()) == $perPage ? 'selected' : '' }}>{{ $perPage }}</option>
                                    @endforeach
                                </select>
                                <span>data</span>
                            </div>
                            <span class="hidden sm:inline border-l border-gray-300 h-4 mx-2"></span>
                            <div>
                                Info: <span class="font-bold text-gray-900">{{ $stocks->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-900">{{ $stocks->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-900">{{ $stocks->total() }}</span>
                            </div>
                        </div>

                        {{-- Right: Links --}}
                        <div>
                            {{ $stocks->appends(request()->all())->links('vendor.pagination.custom') }}
                        </div>
                    </div>
                </div>

                {{-- Action Button (Separate Row or Float) --}}
                <div class="flex justify-end border-t border-gray-200 pt-4 mt-2">
                     <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium shadow-sm transition-colors flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Simpan Perubahan
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
</script>
@endsection
