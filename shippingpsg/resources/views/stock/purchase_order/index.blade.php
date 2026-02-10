@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Purchase Order Customer</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Manajemen PO & Schedule Pengiriman</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search --}}
            <form action="{{ route('stock.po.index') }}" method="GET" class="flex gap-2">
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari PO/Part..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('stock.po.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300 flex items-center justify-center" title="Reset Filters">
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
                    <a href="{{ route('stock.po.export', request()->all()) }}" target="_blank" class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" title="Download Template / Export CSV">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </a>
                    
                    {{-- Import --}}
                    <a href="{{ route('stock.po.import.form') }}" class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" title="Import PO">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    </a>

                    <a href="{{ route('stock.po.create') }}" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-md font-bold ml-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah PO
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

    @if(session('import_warnings'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4 text-sm">
            <strong class="block mb-2">Warning: Import selesai tapi ada stok yang tidak mencukupi! Prioritaskan Produksi:</strong>
            <ul class="list-disc list-inside max-h-48 overflow-y-auto space-y-1">
                @foreach(session('import_warnings') as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">PO Number</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qty</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Freq Delivery</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($pos as $index => $po)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $pos->firstItem() + $index }}</td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $po->part->nomor_part }}</div>
                            <div class="text-xs text-gray-500">{{ $po->part->nama_part }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 font-bold">{{ $po->part->customer->inisial_perusahaan ?? '-' }}</div>
                            <div class="text-[10px] text-gray-500">{{ $po->part->customer->nama_perusahaan ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 text-sm font-mono text-gray-800">{{ $po->po_number }}</td>
                        <td class="px-6 py-4 text-center">
                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ number_format($po->qty) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ $po->delivery_frequency ?? '-' }}</td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600">
                            @if(is_numeric($po->month) && $po->month >= 1 && $po->month <= 12)
                                {{ DateTime::createFromFormat('!m', $po->month)->format('M') }}
                            @else
                                {{ $po->month }}
                            @endif
                            {{ $po->year }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('stock.po.edit', $po->id) }}" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded-md transition-colors" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form action="{{ route('stock.po.destroy', $po->id) }}" method="POST" onsubmit="return confirm('Hapus PO ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-md transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                            Tidak ada data PO
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-gray-200">
             <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                {{-- Left: Info & Per Page --}}
                <div class="text-sm text-gray-500 flex flex-col sm:flex-row items-center gap-2">
                    <div class="flex items-center gap-2">
                        <span>Tampilkan</span>
                        <select onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)" class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white text-xs font-medium cursor-pointer">
                            @foreach([10, 25, 50, 100] as $perPage)
                                <option value="{{ $perPage }}" {{ request('per_page', $pos->perPage()) == $perPage ? 'selected' : '' }}>{{ $perPage }}</option>
                            @endforeach
                        </select>
                        <span>data</span>
                    </div>
                    <span class="hidden sm:inline border-l border-gray-300 h-4 mx-2"></span>
                    <div>
                        Info: <span class="font-bold text-gray-900">{{ $pos->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-900">{{ $pos->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-900">{{ $pos->total() }}</span>
                    </div>
                </div>

                {{-- Right: Links --}}
                <div>
                     {{ $pos->appends(request()->all())->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
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
