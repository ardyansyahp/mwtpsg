@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div class="flex flex-col">
            <h2 class="text-xl font-bold text-gray-900 leading-none">Master Manpower</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Kelola data manpower</p>
        </div>

        {{-- Actions --}}
        <div class="flex flex-col md:flex-row gap-2 w-full md:w-auto items-center">
            
            <form action="{{ route('master.manpower.index') }}" method="GET" class="flex flex-col md:flex-row gap-2 w-full md:w-auto">
                {{-- Filter & Search --}}
                <select name="departemen_filter" onchange="this.form.submit()" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 text-gray-600 bg-white min-w-[140px]">
                    <option value="">Semua Dept</option>
                    <option value="Produksi" {{ request('departemen_filter') == 'Produksi' ? 'selected' : '' }}>Produksi</option>
                    <option value="Quality" {{ request('departemen_filter') == 'Quality' ? 'selected' : '' }}>Quality</option>
                    <option value="Maintenance" {{ request('departemen_filter') == 'Maintenance' ? 'selected' : '' }}>Maintenance</option>
                    <option value="Logistik" {{ request('departemen_filter') == 'Logistik' ? 'selected' : '' }}>Logistik</option>
                    <option value="HRD" {{ request('departemen_filter') == 'HRD' ? 'selected' : '' }}>HRD</option>
                </select>

                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari NIK/Nama/Dept..." 
                        class="w-full md:w-48 pl-9 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                    >
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </form>

            {{-- Bulk Actions (Hidden by default) --}}
            <div id="bulkActions" class="hidden flex items-center gap-2">
                <button type="button" onclick="bulkDelete()" class="px-3 py-2 bg-red-100 text-red-600 rounded-lg hover:bg-red-200 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            
            {{-- Maintenance Group (Trash & Reset) --}}
            @if(userCan('master.manpower.delete'))
            <div class="flex items-center bg-white border border-gray-300 rounded-lg overflow-hidden shadow-sm h-[38px]">
                <form action="{{ route('master.manpower.destroy.all') }}" method="POST" onsubmit="return confirm('Hapus SEMUA data manpower?');" class="h-full">
                    @csrf
                    <button type="submit" class="h-full px-3 text-red-500 hover:bg-red-50 transition-colors border-r border-gray-200" title="Hapus Semua Data">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </form>
                <a href="{{ route('master.manpower.trash') }}" class="h-full px-3 text-gray-500 hover:bg-gray-50 transition-colors flex items-center" title="Recycle Bin">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </a>
            </div>
            @endif

            {{-- Exports & Imports --}}
            <a href="{{ route('master.manpower.export') }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm h-[38px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Export</span>
            </a>
            
            @if(userCan('master.manpower.create'))
            <a href="{{ route('master.manpower.import.form') }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-50 transition-colors shadow-sm h-[38px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                <span>Import</span>
            </a>
            <a href="{{ route('master.manpower.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-sm h-[38px]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah</span>
            </a>
            @endif
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
            
            $url = route('master.manpower.index', array_merge(request()->all(), ['sort_by' => $column, 'sort_order' => $direction]));
            
            return '<a href="'.$url.'" class="group flex items-center gap-1 cursor-pointer select-none hover:text-blue-600">'.$label.' '.$icon.'</a>';
        }
    @endphp

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-center w-12">
                            <input type="checkbox" id="selectAll" onchange="toggleAllCheckboxes()" class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{!! sortLink('nik', 'NIK') !!}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{!! sortLink('nama', 'Nama') !!}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{!! sortLink('departemen', 'Departemen') !!}</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">QR Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{!! sortLink('status', 'Status') !!}</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($manpowers as $index => $manpower)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-4 text-center">
                                <input type="checkbox" name="ids[]" value="{{ $manpower->id }}" onchange="updateBulkActionState()" class="row-checkbox rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $manpowers->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $manpower->nik ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <a href="{{ route('master.manpower.show', $manpower->id) }}" class="hover:text-blue-600 font-medium">
                                    {{ $manpower->nama }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $manpower->departemen ?? '-' }} <span class="text-xs text-gray-400">({{ $manpower->bagian ?? '-' }})</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-xs font-mono text-gray-500">
                                {{ $manpower->qrcode }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                    <input type="checkbox" name="toggle" id="toggle_{{ $manpower->id }}" 
                                        {{ $manpower->status ? 'checked' : '' }}
                                        onchange="toggleStatus({{ $manpower->id }})"
                                        class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer" style="top: 0; left: 0;"/>
                                    <label for="toggle_{{ $manpower->id }}" class="toggle-label block overflow-hidden h-5 rounded-full bg-gray-300 cursor-pointer"></label>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('master.manpower.show', $manpower->id) }}" class="text-gray-500 hover:text-gray-700 bg-gray-100 p-1.5 rounded-md transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if(userCan('master.manpower.edit'))
                                    <a href="{{ route('master.manpower.edit', $manpower->id) }}" class="text-blue-500 hover:text-blue-700 bg-blue-50 p-1.5 rounded-md transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    @endif
                                    <a href="{{ route('master.manpower.idcard', $manpower->id) }}" target="_blank" class="text-green-500 hover:text-green-700 bg-green-50 p-1.5 rounded-md transition-colors" title="ID Card">
                                         <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    </a>
                                    @if(userCan('master.manpower.delete'))
                                    <a href="{{ route('master.manpower.delete', $manpower->id) }}" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-md transition-colors" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                <p>Tidak ada data manpower ditemukan.</p>
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
                    Menampilkan <span class="font-medium text-gray-900">{{ $manpowers->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $manpowers->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $manpowers->total() }}</span> data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $manpowers->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

<style>

    /* Toggle Checkbox Style */
    .toggle-checkbox {
        right: 0;
        z-index: 1;
        border-color: #cbd5e0;
        transition: all 0.2s ease-in-out;
    }

    .toggle-checkbox:checked {
        right: 0;
        border-color: #10B981; /* green-500 */
        transform: translateX(100%);
    }

    .toggle-checkbox:checked + .toggle-label {
        background-color: #10B981; /* green-500 */
    }

</style>

<script>
    function toggleStatus(id) {
        fetch(`/master/manpower/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Optional: Toast notification
                console.log(data.message);
            } else {
                alert('Gagal mengupdate status');
                // Revert checkbox state
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function toggleAllCheckboxes() {
        const selectAll = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = selectAll.checked);
        updateBulkActionState();
    }

    function updateBulkActionState() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const bulkDiv = document.getElementById('bulkActions');
        if (checkboxes.length > 0) {
            bulkDiv.classList.remove('hidden');
        } else {
            bulkDiv.classList.add('hidden');
        }
    }

    function bulkDelete() {
        if (!confirm('Apakah Anda yakin ingin menghapus data yang dipilih?')) return;

        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);

        fetch('{{ route("master.manpower.bulk.delete") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ ids: ids })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Gagal menghapus data');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1); // Reset to page 1
        window.location.href = url.toString();
    }
</script>
@endsection
