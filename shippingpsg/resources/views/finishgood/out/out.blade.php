@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Finish Good - Scan Out</h2>
            <p class="text-gray-600 mt-1">Pilih SPK untuk melakukan scan label box</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search & Filter Form --}}
            <form action="{{ route('finishgood.out.index') }}" method="GET" class="flex flex-col md:flex-row gap-2">
                @if(request('sort_by'))
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif

                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" title="Tanggal Mulai">
                    <span class="text-gray-400 text-xs">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" title="Tanggal Akhir">
                </div>
                
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari SPK / Customer..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64 text-sm"
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
                        <a href="{{ route('finishgood.out.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('finishgood.out.export', request()->all()) }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium text-sm">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span class="hidden xl:inline">Export</span>
                </a>
                
                {{-- Create Button (Not suitable here as SPK creation is elsewhere, but keeping slot empty or removing) --}}
                {{-- If user wants "samain persis", maybe they just mean the toolbar style. --}}
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full mobile-hide-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-16">No</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nomor SPK</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Cycle</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No Surat Jalan</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Plant Gate</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Model Part</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Progress</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100" id="tableBody">
                    @forelse($spks as $index => $spk)
                        <tr class="hover:bg-gray-50 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="No">{{ $spks->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $spk->parent_spk_id ? 'text-yellow-600' : 'text-blue-800' }}" data-label="Nomor SPK">
                                {{ $spk->nomor_spk }}
                            </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="Cycle">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-gray-100 text-gray-600 border border-gray-200">
                                    C{{ $spk->cycle_number ?? 1 }}
                                </span>
                             </td>
                             <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="No Surat Jalan">
                                @if($spk->no_surat_jalan)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 border border-indigo-200">
                                        {{ $spk->no_surat_jalan }}
                                    </span>
                                @else
                                    <span class="text-gray-400 italic text-xs">Belum dikirim</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Tanggal">{{ optional($spk->tanggal)->format('Y-m-d') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" data-label="Customer">
                                @if($spk->customer)
                                    <div class="flex flex-col">
                                        <span>{{ $spk->customer->nama_perusahaan ?? '-' }}</span>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Plant Gate">{{ $spk->plantgate->nama_plantgate ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" data-label="Model Part">
                                <span class="px-2 py-1 rounded text-white bg-blue-600 text-xs font-bold uppercase">{{ $spk->model_part }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Progress">
                                <div class="flex flex-col">
                                    @php
                                        $displayTarget = ($spk->total_original_target > $spk->total_target) 
                                            ? $spk->total_original_target 
                                            : $spk->total_target;
                                        $scanned = $spk->total_scanned ?? 0;
                                        $percent = $displayTarget > 0 ? min(100, round(($scanned / $displayTarget) * 100)) : 0;
                                    @endphp
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-gray-900 text-xs">{{ number_format($scanned) }} / {{ number_format($displayTarget) }}</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1 max-w-[100px]">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" data-label="Status">
                                @php
                                    $hasSuratJalan = !empty($spk->no_surat_jalan);
                                    $scannedQty = $spk->total_scanned ?? 0;
                                @endphp
                                @if($hasSuratJalan)
                                    <span class="px-2.5 py-0.5 rounded-full text-white bg-green-500 text-xs font-bold">Close</span>
                                @elseif($scannedQty > 0)
                                    <span class="px-2.5 py-0.5 rounded-full text-white bg-blue-500 text-xs font-bold">Progress</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full text-white bg-yellow-400 text-xs font-bold">Open</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center" data-label="Aksi">
                                <div class="flex items-center justify-center gap-2">
                                    @if($spk->no_surat_jalan)
                                        <div class="text-green-500 cursor-default p-1.5 border border-transparent" title="Sudah Dikirim">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                    @else
                                        @if(userCan('finishgood.out.create'))
                                        <a href="{{ route('finishgood.out.scan', ['spk' => $spk->id]) }}" class="p-1.5 hover:bg-green-50 text-green-600 rounded-lg transition-colors border border-green-200 hover:border-green-300" title="Scan Label Box">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                            </svg>
                                        </a>
                                        @endif
                                    @endif
                                    
                                    <a href="{{ route('finishgood.out.print', ['spk' => $spk->id]) }}" target="_blank" class="p-1.5 hover:bg-blue-50 text-blue-600 rounded-lg transition-colors border border-blue-200 hover:border-blue-300" title="Cetak PDF">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                        </svg>
                                    </a>
                                    
                                    @if(userCan('finishgood.out.edit'))
                                    <a href="{{ route('finishgood.out.edit', ['spk' => $spk->id]) }}" class="p-1.5 hover:bg-orange-50 text-orange-600 rounded-lg transition-colors border border-orange-200 hover:border-orange-300" title="Edit Data">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="11" class="px-6 py-16 text-center bg-gray-50/50">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-gray-100 p-4 rounded-full mb-4">
                                        <svg class="h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-lg font-medium text-gray-900">Tidak ada data SPK</h3>
                                    <p class="mt-1 text-sm text-gray-500">Mulai dengan membuat SPK baru untuk melakukan shipping.</p>
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
                        class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-gray-50 text-xs font-medium cursor-pointer"
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
                     Menampilkan <span class="font-medium text-gray-900">{{ $spks->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $spks->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $spks->total() }}</span>️ data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $spks->appends(request()->all())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

<script>
    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1); // Reset to page 1
        window.location.href = url.toString();
    }
</script>
@endsection
