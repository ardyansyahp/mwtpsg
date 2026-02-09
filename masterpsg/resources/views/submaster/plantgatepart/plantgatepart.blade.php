@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 leading-none">Plant Gate Part</h2>
            <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Kelola relasi plant gate dengan part</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search & Filter Form --}}
            <form action="{{ route('submaster.plantgatepart.index') }}" method="GET" class="flex gap-2">
                {{-- Filter Plant Gate --}}
                <select name="plantgate_id" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Gate</option>
                    @foreach($plantgates as $pg)
                        <option value="{{ $pg->id }}" {{ request('plantgate_id') == $pg->id ? 'selected' : '' }}>
                            {{ $pg->nama_plantgate }}
                        </option>
                    @endforeach
                </select>

                {{-- Filter Status --}}
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>

                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari part..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 md:w-64"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                @if(request('search') || request('plantgate_id') || request('status'))
                    <a href="{{ route('submaster.plantgatepart.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                    </a>
                @endif
            </form>

            <div class="flex items-center gap-2">
                @if(userCan('submaster.plantgatepart.delete'))
                    {{-- Maintenance Group (Trash) --}}
                    <div class="flex items-center bg-white rounded-lg border border-gray-300 shadow-sm p-1 ml-2">
                        <a href="{{ route('submaster.plantgatepart.trash') }}" class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors" title="Recycle Bin">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </a>
                        
                        <div class="w-px h-6 bg-gray-200 mx-1"></div>

                        <a href="{{ route('submaster.plantgatepart.trash') }}" class="p-2 text-gray-600 hover:bg-gray-100 rounded transition-colors" title="View Deleted">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </a>
                    </div>
                @endif

                @if(userCan('submaster.plantgatepart.create'))
                    <div class="flex items-center gap-2 ml-2">
                        <a href="{{ route('submaster.plantgatepart.export', request()->all()) }}" target="_blank" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span class="hidden xl:inline">Export</span>
                        </a>

                        <a href="{{ route('submaster.plantgatepart.import.form') }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium">
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m-4-4v12"/></svg>
                            <span class="hidden xl:inline">Import</span>
                        </a>

                        <a href="{{ route('submaster.plantgatepart.create') }}" class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-md font-bold ml-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Plant Gate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor Part</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Part</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                    @forelse($plantgateParts as $index => $plantgatePart)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $plantgateParts->firstItem() + $index }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $plantgatePart->plantgate->nama_plantgate ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plantgatePart->plantgate->customer->nama_perusahaan ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $plantgatePart->part->nomor_part ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plantgatePart->part->nama_part ?? '-' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if(userCan('submaster.plantgatepart.edit'))
                                    <button
                                        onclick="toggleStatus({{ $plantgatePart->id }})"
                                        class="relative inline-flex items-center h-6 rounded-full w-11 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 {{ $plantgatePart->status ? 'bg-green-600' : 'bg-gray-200' }}"
                                        id="status-btn-{{ $plantgatePart->id }}"
                                        title="Klik untuk mengubah status"
                                    >
                                        <span
                                            class="inline-block w-4 h-4 transform bg-white rounded-full transition-transform {{ $plantgatePart->status ? 'translate-x-6' : 'translate-x-1' }}"
                                            id="status-dot-{{ $plantgatePart->id }}"
                                        ></span>
                                    </button>
                                @else
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $plantgatePart->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $plantgatePart->status ? 'Active' : 'Inactive' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('submaster.plantgatepart.detail', $plantgatePart->id) }}" class="text-green-600 hover:text-green-900 transition-colors" title="Detail">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    @if(userCan('submaster.plantgatepart.edit'))
                                    <a href="{{ route('submaster.plantgatepart.edit', $plantgatePart->id) }}" class="text-blue-600 hover:text-blue-900 transition-colors" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    @if(userCan('submaster.plantgatepart.delete'))
                                    <a href="{{ route('submaster.plantgatepart.delete', $plantgatePart->id) }}" class="text-red-600 hover:text-red-900 transition-colors" title="Hapus">
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
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan plant gate part baru.</p>
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
                    Menampilkan <span class="font-medium text-gray-900">{{ $plantgateParts->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $plantgateParts->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $plantgateParts->total() }}</span>️ data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $plantgateParts->appends(request()->all())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
</div>

<script>
// Toggle Status
function toggleStatus(id) {
    if(!confirm('Apakah Anda yakin ingin mengubah status data ini?')) return;

    const btn = document.getElementById(`status-btn-${id}`);
    const dot = document.getElementById(`status-dot-${id}`);
    
    const isCurrentlyActive = btn.classList.contains('bg-green-600');
    
    // Update UI immediately for better UX
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

    // Send request to server
    fetch(`{{ url('submaster/plantgatepart') }}/${id}/toggle-status`, {
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

function changePerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.set('page', 1); // Reset to page 1
    window.location.href = url.toString();
}
</script>
@endsection
