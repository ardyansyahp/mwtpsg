@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Master Part</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Kelola data part (komponen, finish good, dll)</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search Form --}}
            <form action="{{ route('submaster.part.index') }}" method="GET" class="flex gap-2">
                @if(request('sort_by'))
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif
                
                {{-- Filter Proses --}}
                <select name="proses" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Proses</option>
                    <option value="inject" {{ request('proses') == 'inject' ? 'selected' : '' }}>Inject</option>
                    <option value="assy" {{ request('proses') == 'assy' ? 'selected' : '' }}>Assy</option>
                </select>
                
                {{-- Filter Customer --}}
                <div class="relative" id="customer_filter_container">
                    <input 
                        type="text" 
                        id="customer_search"
                        value="{{ request('customer_id') ? ($customers->where('id', request('customer_id'))->first()->nama_perusahaan ?? '') : '' }}"
                        placeholder="Filter Customer..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-56 text-sm"
                        autocomplete="off"
                    >
                    <input type="hidden" name="customer_id" id="customer_id_hidden" value="{{ request('customer_id') }}">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div id="customer_results" class="absolute z-[60] mt-1 w-64 bg-white border border-gray-200 rounded-lg shadow-xl hidden max-h-60 overflow-y-auto">
                        {{-- Results injected via JS --}}
                    </div>
                </div>
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari Nama/Nomor/Model..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if(request('search') || request('sort_by') || request('proses') || request('customer_id'))
                    <a href="{{ route('submaster.part.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </form>

            {{-- Bulk Actions --}}
            <div id="bulkActions" class="hidden flex items-center gap-3 bg-red-50 px-3 py-2 rounded-lg border border-red-100 mr-2">
                <span class="text-red-700 text-sm font-medium"><span id="selectedCount">0</span> selected</span>
                <button onclick="bulkDelete()" class="text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded text-xs font-bold transition-colors shadow-sm">
                    HAPUS
                </button>
            </div>

            <div class="flex items-center gap-2">
                @if(userCan('submaster.part.delete'))
                    {{-- Maintenance Group (Reset & Trash) --}}
                    <div class="flex items-center bg-white rounded-lg border border-gray-300 shadow-sm p-1 ml-2">
                         {{-- Reset Database --}}
                        <form action="{{ route('submaster.part.destroy.all') }}" method="POST" onsubmit="return confirm('BAHAYA: Apakah Anda yakin ingin menghapus SEMUA data part yang aktif? Data akan masuk ke sampah.');" class="flex items-center">
                            @csrf
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors" title="Reset Database (Hapus Semua)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        
                        <div class="w-px h-6 bg-gray-200 mx-1"></div>

                        {{-- Recycle Bin --}}
                        <a href="{{ route('submaster.part.trash') }}" class="p-2 text-gray-600 hover:bg-gray-100 rounded transition-colors" title="Recycle Bin">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </a>
                    </div>
                @endif

                @if(userCan('submaster.part.create'))
                    <div class="flex items-center gap-2 ml-2">
                        <a href="{{ route('submaster.part.export') }}" target="_blank" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span class="hidden xl:inline">Export</span>
                        </a>

                        <a href="{{ route('submaster.part.import.form') }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m-4-4v12"/></svg>
                            <span class="hidden xl:inline">Import</span>
                        </a>

                        <a href="{{ route('submaster.part.create') }}" class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-md font-bold ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @php
        function sortLink($column, $label) {
            $direction = 'asc';
            $icon = '<svg class="w-3 h-3 text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>';
            
            if (request('sort_by') === $column) {
                $direction = request('sort_order') === 'asc' ? 'desc' : 'asc';
                if (request('sort_order') === 'asc') {
                    $icon = '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>';
                } else {
                    $icon = '<svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>';
                }
            }
            
            $url = route('submaster.part.index', array_merge(request()->all(), ['sort_by' => $column, 'sort_order' => $direction]));
            return '<a href="'.$url.'" class="group flex items-center gap-1 cursor-pointer select-none hover:text-blue-600">'.$label.' '.$icon.'</a>';
        }
    @endphp

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                            <input type="checkbox" id="selectAll" onclick="toggleAllCheckboxes()" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('nomor_part', 'Nomor Part') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('nama_part', 'Nama Part') !!}
                        </th>
                         <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                             {!! sortLink('model_part', 'Model') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('proses', 'Proses') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('status', 'Status') !!}
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                    @forelse($parts as $index => $part)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <input type="checkbox" name="selected_ids[]" value="{{ $part->id }}" class="row-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="updateBulkActionState()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $parts->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono font-bold">
                                {{ $part->nomor_part }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $part->nama_part }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $part->customer ? $part->customer->nama_perusahaan : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    {{ strtoupper($part->model_part) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full 
                                    @if($part->proses === 'inject') bg-purple-100 text-purple-800
                                    @elseif($part->proses === 'assy') bg-green-100 text-green-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ strtoupper($part->proses) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                 <button
                                     onclick="toggleStatus({{ $part->id }})"
                                     class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $part->status ? 'bg-green-600' : 'bg-gray-200' }}"
                                     id="status-btn-{{ $part->id }}"
                                     title="Klik untuk mengubah status"
                                 >
                                     <span
                                         class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $part->status ? 'translate-x-6' : 'translate-x-1' }}"
                                         id="status-dot-{{ $part->id }}"
                                     ></span>
                                 </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a 
                                        href="{{ route('submaster.part.detail', $part->id) }}"
                                        class="text-green-600 hover:text-green-900 transition-colors bg-green-50 px-2 py-1 rounded-md border border-green-200 hover:bg-green-100" 
                                        title="Detail"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>

                                    @if(userCan('submaster.part.edit'))
                                    <a 
                                        href="{{ route('submaster.part.edit', $part->id) }}"
                                        class="text-blue-600 hover:text-blue-900 transition-colors" 
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @endif

                                    @if(userCan('submaster.part.delete'))
                                    <a 
                                        href="{{ route('submaster.part.delete', $part->id) }}"
                                        class="text-red-600 hover:text-red-900 transition-colors" 
                                        title="Hapus"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="9" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan part baru.</p>
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
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data per halaman</span>
                </div>
                <div class="border-l border-gray-300 h-4 mx-2"></div>
                <div>
                    Menampilkan <span class="font-medium text-gray-900">{{ $parts->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $parts->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $parts->total() }}</span>️ data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $parts->appends(request()->all())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(id) {
        if(!confirm('Apakah Anda yakin ingin mengubah status data ini?')) return;

        const btn = document.getElementById(`status-btn-${id}`);
        const dot = document.getElementById(`status-dot-${id}`);
        
        const isCurrentlyActive = btn.classList.contains('bg-green-600');
        
        if (isCurrentlyActive) {
            btn.classList.remove('bg-green-600');
            btn.classList.add('bg-gray-200');
            dot.classList.remove('translate-x-6');
            dot.classList.add('translate-x-1');
        } else {
            btn.classList.remove('bg-gray-200');
            btn.classList.add('bg-green-600');
            dot.classList.remove('translate-x-1');
            dot.classList.add('translate-x-6');
        }

        fetch(`{{ url('submaster/part') }}/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Gagal mengupdate status: ' + data.message);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
            location.reload();
        });
    }

    function toggleAllCheckboxes() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActionState();
    }

    function updateBulkActionState() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const bulkActions = document.getElementById('bulkActions');
        const countSpan = document.getElementById('selectedCount');
        countSpan.textContent = checkboxes.length;
        if (checkboxes.length > 0) {
            bulkActions.classList.remove('hidden');
        } else {
            bulkActions.classList.add('hidden');
        }
    }

    function bulkDelete() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) return;
        if (!confirm(`Apakah Anda yakin ingin menghapus ${checkboxes.length} data terpilih?`)) return;

        const ids = Array.from(checkboxes).map(cb => cb.value);

        fetch('{{ route('submaster.part.bulk.delete') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus data: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan sistem');
        });
    }

    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1); // Reset to page 1
        window.location.href = url.toString();
    }

    // Custom Customer Autocomplete Logic
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('customer_filter_container');
        const searchInput = document.getElementById('customer_search');
        const hiddenInput = document.getElementById('customer_id_hidden');
        const resultsDiv = document.getElementById('customer_results');
        
        const customers = @json($customers->map(fn($c) => ['id' => $c->id, 'name' => $c->nama_perusahaan]));
        let selectedIndex = -1;

        function renderResults(filtered) {
            if (filtered.length === 0) {
                resultsDiv.innerHTML = '<div class="px-4 py-2 text-xs text-gray-500">Tidak ada hasil</div>';
            } else {
                resultsDiv.innerHTML = filtered.map((c, i) => `
                    <div class="result-item px-4 py-2 cursor-pointer hover:bg-blue-50 text-sm transition-colors border-b border-gray-50 last:border-0" data-id="${c.id}" data-name="${c.name}">
                        <div class="font-medium text-gray-800">${c.name}</div>
                    </div>
                `).join('');
            }
            resultsDiv.classList.remove('hidden');
        }

        searchInput.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            if (val === '') {
                resultsDiv.classList.add('hidden');
                if (hiddenInput.value !== '') {
                    hiddenInput.value = '';
                    this.form.submit();
                }
                return;
            }

            const filtered = customers.filter(c => c.name.toLowerCase().includes(val)).slice(0, 10);
            renderResults(filtered);
        });

        // Focus event to show all or current
        searchInput.addEventListener('focus', function() {
            const val = this.value.toLowerCase();
            if (val === '') {
                 // Show top 10 as recommendations
                 renderResults(customers.slice(0, 10));
            } else {
                 const filtered = customers.filter(c => c.name.toLowerCase().includes(val)).slice(0, 10);
                 renderResults(filtered);
            }
        });

        // Click selection
        resultsDiv.addEventListener('click', function(e) {
            const item = e.target.closest('.result-item');
            if (item) {
                const id = item.dataset.id;
                const name = item.dataset.name;
                
                searchInput.value = name;
                hiddenInput.value = id;
                resultsDiv.classList.add('hidden');
                searchInput.form.submit();
            }
        });

        // Close when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
                resultsDiv.classList.add('hidden');
            }
        });

        // Basic Keyboard support
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                resultsDiv.classList.add('hidden');
            }
            if (e.key === 'Enter' && !resultsDiv.classList.contains('hidden')) {
                const items = resultsDiv.querySelectorAll('.result-item');
                if (items.length === 1) { // Auto select if only one result
                    const id = items[0].dataset.id;
                    const name = items[0].dataset.name;
                    searchInput.value = name;
                    hiddenInput.value = id;
                    resultsDiv.classList.add('hidden');
                    this.form.submit();
                }
            }
        });
    });
</script>
@endpush
