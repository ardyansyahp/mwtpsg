@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Surat Perintah Pengiriman</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Manajemen SPK, Import/Export, dan Data Sampah</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search Form --}}
            <form action="{{ route('spk.index') }}" method="GET" class="flex gap-2">
                @if(request('per_page'))
                   <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari SPK / SJ..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                </div>

                @if(request('search'))
                    <a href="{{ route('spk.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300 flex items-center justify-center" title="Reset Filters">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </form>

            {{-- Bulk Actions Toolbar --}}
            <div id="bulkActions" class="hidden flex items-center gap-3 bg-red-50 px-3 py-2 rounded-lg border border-red-100 mr-2">
                <span class="text-red-700 text-sm font-medium"><span id="selectedCount">0</span> selected</span>
                @if(userCan('spk.delete'))
                    <button onclick="bulkDelete()" class="text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded text-xs font-bold transition-colors shadow-sm">
                        HAPUS
                    </button>
                @endif
            </div>

            {{-- Action Buttons Group --}}
            <div class="flex items-center gap-2">
                @if(userCan('spk.delete'))
                    {{-- Maintenance Group --}}
                    <div class="flex items-center bg-white rounded-lg border border-gray-300 shadow-sm p-1 ml-2">
                        {{-- Reset Database --}}
                        <form action="{{ route('spk.destroy_all') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA data SPK? Data akan masuk ke sampah.');" class="flex" title="Hapus Semua Data">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors" title="Hapus Semua Data (Soft Delete)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        
                        <div class="w-px h-6 bg-gray-200 mx-1"></div>

                        {{-- Recycle Bin --}}
                        <a href="{{ route('spk.trash') }}" class="p-2 text-gray-600 hover:bg-gray-100 rounded transition-colors" title="Tempat Sampah">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </a>
                    </div>
                @endif

                {{-- Data Tools --}}
                <div class="flex items-center gap-2 ml-2">
                    @if(userCan('spk.index'))
                        <a href="{{ route('spk.export', request()->all()) }}" target="_blank" class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" title="Export CSV">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </a>
                    @endif

                    @if(userCan('spk.create'))
                        <a href="{{ route('spk.import.form') }}" class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" title="Import CSV">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </a>
                        
                        {{-- Primary Action --}}
                        <a href="{{ route('spk.create') }}" class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-md font-bold ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Tambah</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full mobile-hide-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 w-10 text-center">
                            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'nomor_spk', 'sort_order' => (request('sort_by') == 'nomor_spk' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                            <div class="flex items-center gap-1">
                                Nomor SPK
                                @if(request('sort_by') == 'nomor_spk')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'tanggal', 'sort_order' => (request('sort_by') == 'tanggal' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                            <div class="flex items-center gap-1">
                                Deadline Pulling
                                @if(request('sort_by') == 'tanggal')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'jam_berangkat_plan', 'sort_order' => (request('sort_by') == 'jam_berangkat_plan' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                            <div class="flex items-center gap-1">
                                Jam Berangkat
                                @if(request('sort_by') == 'jam_berangkat_plan')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'cycle_number', 'sort_order' => (request('sort_by') == 'cycle_number' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                            <div class="flex items-center gap-1">
                                Cycle
                                @if(request('sort_by') == 'cycle_number')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'customer_id', 'sort_order' => (request('sort_by') == 'customer_id' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                             <div class="flex items-center gap-1">
                                Customer
                                @if(request('sort_by') == 'customer_id')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'model_part', 'sort_order' => (request('sort_by') == 'model_part' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                             <div class="flex items-center gap-1">
                                Model Part
                                @if(request('sort_by') == 'model_part')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider group cursor-pointer hover:bg-gray-100 transition-colors" onclick="window.location='{{ route('spk.index', array_merge(request()->all(), ['sort_by' => 'no_surat_jalan', 'sort_order' => (request('sort_by') == 'no_surat_jalan' && request('sort_order') == 'asc') ? 'desc' : 'asc'])) }}'">
                             <div class="flex items-center gap-1">
                                SJ
                                @if(request('sort_by') == 'no_surat_jalan')
                                    <svg class="w-3 h-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ request('sort_order') == 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/></svg>
                                @endif
                            </div>
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                    @forelse($spks as $index => $spk)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" class="select-item rounded border-gray-300 text-blue-600 focus:ring-blue-500" value="{{ $spk->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="No">{{ $index + 1 + ($spks->currentPage() - 1) * $spks->perPage() }}</td>
                           <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-blue-700" data-label="Nomor SPK">
                                {{ $spk->nomor_spk }}
                                @if($spk->parent_spk_id)
                                    <span class="ml-1 bg-orange-100 text-orange-600 text-[10px] px-1.5 py-0.5 rounded border border-orange-200 uppercase tracking-tighter">Split</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600" data-label="Deadline Pulling">
                                {{ optional($spk->tanggal)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900 group" data-label="Jam Berangkat">
                                {{ $spk->jam_berangkat_plan ?? '-' }}
                                @if($spk->parent_spk_id)
                                    <span class="inline-block w-2 h-2 bg-amber-400 rounded-full animate-pulse ml-1" title="Cek kembali jam berangkat untuk SPK Split"></span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" data-label="Cycle">
                                <span class="bg-gray-100 text-gray-800 px-2 py-0.5 rounded-full font-bold">C{{ $spk->cycle_number ?? 1 }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Customer">
                                <div class="font-medium text-gray-900">{{ $spk->customer->nama_perusahaan ?? '-' }}</div>
                                <div class="text-[10px] text-gray-400">{{ $spk->plantgate->nama_plantgate ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm" data-label="Model Part">
                                <span class="px-1.5 py-0.5 rounded text-white bg-blue-600 text-[10px] font-bold uppercase">{{ $spk->model_part }}</span>
                                <div class="text-[10px] text-gray-400 mt-0.5">{{ $spk->details_count ?? 0 }} item</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono" data-label="Surat Jalan">{{ $spk->no_surat_jalan ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center" data-label="Aksi">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('spk.detail', $spk->id) }}" class="p-1 hover:bg-green-50 rounded text-green-600 transition-colors" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    @if(userCan('spk.edit'))
                                    <a href="{{ route('spk.edit', $spk->id) }}" class="p-1 hover:bg-blue-50 rounded text-blue-600 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    @if(userCan('spk.delete'))
                                    <a href="{{ route('spk.delete', $spk->id) }}" class="p-1 hover:bg-red-50 rounded text-red-600 transition-colors" title="Hapus">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="11" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan SPK baru.</p>
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
                        <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
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
@endsection




<script>
function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1); // Reset to page 1
    window.location.href = url.toString();
}

// Bulk Selection
// Bulk Selection
const selectAll = document.getElementById('selectAll');
const selectItems = document.querySelectorAll('.select-item');
const bulkActions = document.getElementById('bulkActions');
const selectedCount = document.getElementById('selectedCount');

function updateBulkState() {
    const checked = Array.from(selectItems).filter(cb => cb.checked);
    if (bulkActions) {
        if (checked.length > 0) {
            bulkActions.classList.remove('hidden');
            if (selectedCount) selectedCount.textContent = checked.length;
        } else {
            bulkActions.classList.add('hidden');
        }
    }
}

if (selectAll) {
    selectAll.addEventListener('change', function() {
        selectItems.forEach(cb => cb.checked = this.checked);
        updateBulkState();
    });
}

if (selectItems) {
    selectItems.forEach(cb => {
        cb.addEventListener('change', updateBulkState);
    });
}




async function bulkDelete() {
    if (!confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')) return;
    
    const ids = Array.from(selectItems)
        .filter(cb => cb.checked)
        .map(cb => cb.value);
        
    try {
        const response = await fetch('{{ route("spk.bulk_delete") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert('Gagal: ' + data.message);
        }
    } catch (e) {
        alert('Terjadi kesalahan koneksi');
    }
}
</script>
