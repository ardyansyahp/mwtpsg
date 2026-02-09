@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Master Perusahaan</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Kelola data perusahaan</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search Form --}}
            <form action="{{ route('master.perusahaan.index') }}" method="GET" class="flex gap-2">
                {{-- Preserve sort params if any --}}
                @if(request('sort_by'))
                    <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                    <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                @endif
                
                {{-- Filter Jenis --}}
                <select name="jenis" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Jenis</option>
                    <option value="Supplier" {{ request('jenis') == 'Supplier' ? 'selected' : '' }}>Supplier</option>
                    <option value="Customer" {{ request('jenis') == 'Customer' ? 'selected' : '' }}>Customer</option>
                    <option value="Vendor" {{ request('jenis') == 'Vendor' ? 'selected' : '' }}>Vendor</option>
                    <option value="Maker" {{ request('jenis') == 'Maker' ? 'selected' : '' }}>Maker</option>
                </select>

                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if(request('search') || request('sort_by') || request('jenis'))
                    <a href="{{ route('master.perusahaan.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </form>

            {{-- Bulk Actions Toolbar (Absolute/Floating to prevent layout shift, or inline) --}}
            <div id="bulkActions" class="hidden flex items-center gap-3 bg-red-50 px-3 py-2 rounded-lg border border-red-100 mr-2">
                <span class="text-red-700 text-sm font-medium"><span id="selectedCount">0</span> selected</span>
                <button onclick="bulkDelete()" class="text-white bg-red-600 hover:bg-red-700 px-2 py-1 rounded text-xs font-bold transition-colors shadow-sm">
                    HAPUS
                </button>
            </div>

            {{-- Action Buttons Group --}}
            <div class="flex items-center gap-2">
                @if(userCan('master.perusahaan.delete'))
                    {{-- Maintenance Group --}}
                    <div class="flex items-center bg-white rounded-lg border border-gray-300 shadow-sm p-1 ml-2">
                        {{-- Reset Database --}}
                        <form action="{{ route('master.perusahaan.destroy.all') }}" method="POST" onsubmit="return confirm('EXTREME DANGER: Hapus SEMUA data?');" class="flex">
                            @csrf
                            <button type="submit" class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors" title="Reset Database (Hapus Semua)">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                        
                        <div class="w-px h-6 bg-gray-200 mx-1"></div>

                        {{-- Recycle Bin --}}
                        <a href="{{ route('master.perusahaan.trash') }}" class="p-2 text-gray-600 hover:bg-gray-100 rounded transition-colors" title="Recycle Bin">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </a>
                    </div>
                @endif

                @if(userCan('master.perusahaan.create'))
                    {{-- Data Tools --}}
                    <div class="flex items-center gap-2 ml-2">
                        <a href="{{ route('master.perusahaan.export') }}" target="_blank" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span class="hidden xl:inline">Export</span>
                        </a>

                        <a href="{{ route('master.perusahaan.import.form') }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m-4-4v12"/></svg>
                            <span class="hidden xl:inline">Import</span>
                        </a>

                        {{-- Primary Action --}}
                        <a href="{{ route('master.perusahaan.create') }}" class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-md font-bold ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Success/Warning Messages --}}
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded mb-4">
            {{ session('warning') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif
    
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
            
            $url = route('master.perusahaan.index', array_merge(request()->all(), ['sort_by' => $column, 'sort_order' => $direction]));
            
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('nama_perusahaan', 'Nama') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('inisial_perusahaan', 'Inisial') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('jenis_perusahaan', 'Jenis') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('kode_supplier', 'Kode') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('alamat', 'Alamat') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('created_at', 'Created At') !!}
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {!! sortLink('status', 'Status') !!}
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                    @forelse($perusahaans as $index => $perusahaan)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <input type="checkbox" name="selected_ids[]" value="{{ $perusahaan->id }}" class="row-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50" onchange="updateBulkActionState()">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $perusahaans->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $perusahaan->nama_perusahaan }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $perusahaan->inisial_perusahaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $perusahaan->jenis_perusahaan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $perusahaan->kode_supplier ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ Str::limit($perusahaan->alamat ?? '-', 50) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $perusahaan->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                <button
                                    onclick="toggleStatus({{ $perusahaan->id }})"
                                    class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $perusahaan->status ? 'bg-green-600' : 'bg-gray-200' }}"
                                    id="status-btn-{{ $perusahaan->id }}"
                                    title="Klik untuk mengubah status"
                                >
                                    <span
                                        class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $perusahaan->status ? 'translate-x-6' : 'translate-x-1' }}"
                                        id="status-dot-{{ $perusahaan->id }}"
                                    ></span>
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('master.perusahaan.show', $perusahaan->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors bg-blue-50 px-2 py-1 rounded-md border border-blue-200 hover:bg-blue-100" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if(userCan('master.perusahaan.edit'))
                                    <a 
                                        href="{{ route('master.perusahaan.edit', $perusahaan->id) }}"
                                        class="text-blue-600 hover:text-blue-900 transition-colors" 
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    @if(userCan('master.perusahaan.delete'))
                                    <a 
                                        href="{{ route('master.perusahaan.delete', $perusahaan->id) }}"
                                        class="text-red-600 hover:text-red-900 transition-colors" 
                                        title="Hapus"
                                    >
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
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan perusahaan baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Rows Per Page --}}
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4" id="paginationInfo">
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
                    Menampilkan <span class="font-medium text-gray-900">{{ $perusahaans->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $perusahaans->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $perusahaans->total() }}</span> data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $perusahaans->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleStatus(id) {
        if(!confirm('Apakah Anda yakin ingin mengubah status perusahaan ini?')) return;

        const btn = document.getElementById(`status-btn-${id}`);
        const dot = document.getElementById(`status-dot-${id}`);
        
        // Optimistic UI update
        const isCurrentlyActive = btn.classList.contains('bg-green-600');
        
        // Toggle visual state immediately
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

        fetch(`{{ url('master/perusahaan') }}/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                // Revert if failed
                alert('Gagal mengupdate status');
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
        
        // Update header checkbox state if not all selected
        const allCheckboxes = document.querySelectorAll('.row-checkbox');
        const selectAll = document.getElementById('selectAll');
        if (checkboxes.length > 0 && checkboxes.length < allCheckboxes.length) {
            selectAll.indeterminate = true;
        } else {
            selectAll.indeterminate = false;
            selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
        }
    }

    function bulkDelete() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) return;

        if (!confirm(`Apakah Anda yakin ingin menghapus ${checkboxes.length} data terpilih?`)) return;

        const ids = Array.from(checkboxes).map(cb => cb.value);

        fetch('{{ route('master.perusahaan.bulk.delete') }}', {
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
</script>
@endpush


