@extends('layout.app')

@push('styles')
<style>
    .glass-card {
        background: white;
        border-radius: 6px;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e2e8f0;
    }
    .metric-card {
        padding: 1rem;
        border-radius: 6px;
        background: white;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        position: relative;
        overflow: hidden;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .metric-title {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        margin-bottom: 0.25rem;
    }
    .metric-value {
        font-size: 1.5rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.2;
    }
    .metric-icon {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        font-size: 2rem;
        opacity: 0.1;
    }
    .status-badge {
        font-size: 0.65rem;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 700;
        display: inline-block;
        text-align: center;
        min-width: 60px;
    }
    .status-kritis { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
    .status-minim { background-color: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .status-safe { background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; }
    .status-over { background-color: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
    .status-noorder { background-color: #f8fafc; color: #334155; border: 1px solid #e2e8f0; }

    .level-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        font-size: 0.65rem;
        text-align: center;
    }
    .level-col { display: flex; flex-direction: column; }
    .level-label { font-size: 0.55rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; }
    .level-val { font-weight: 700; color: #475569; }

    .border-l-4-slate { border-left: 4px solid #475569; }
    .border-l-4-teal { border-left: 4px solid #0d9488; }
    .border-l-4-emerald { border-left: 4px solid #10b981; }
    .border-l-4-orange { border-left: 4px solid #f97316; }
    .border-l-4-indigo { border-left: 4px solid #6366f1; }
</style>
@endpush

@section('content')
<div x-data="{ 
    limitModalOpen: false, 
    limitPartId: null, 
    limitPartName: '', 
    limitMin: 0, 
    limitMax: 0,
    openLimitModal(id, name, min, max) {
        this.limitPartId = id;
        this.limitPartName = name;
        this.limitMin = min;
        this.limitMax = max;
        this.limitModalOpen = true;
    }
}" class="space-y-4 bg-slate-50 min-h-screen font-sans">
    
    {{-- Header & Filters --}}
    <div class="glass-card p-3 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
             <div class="w-1.5 h-10 bg-slate-800 rounded-full"></div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-cubes text-slate-800"></i> Stock Finished Goods
                </h1>
                <p class="text-[10px] text-slate-500 font-bold uppercase tracking-wide">
                    Live Inventory Monitoring
                </p>
            </div>
        </div>
        
        <form action="" method="GET" class="flex flex-wrap items-center gap-3">
            <div class="relative group">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-emerald-500 transition-colors">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Part Number ..." 
                    class="pl-9 pr-4 py-2 w-64 bg-slate-50 border border-slate-200 rounded-md text-xs font-semibold focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all">
            </div>

            <select name="period" class="py-2 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-md text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer">
                <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>Bulan Ini</option>
                <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>Hari Ini</option>
            </select>

             <select name="customer" class="py-2 pl-3 pr-8 bg-slate-50 border border-slate-200 rounded-md text-xs font-bold text-slate-700 focus:ring-2 focus:ring-emerald-500 outline-none cursor-pointer w-40">
                <option value="">Semua Customer</option>
                 @foreach($parts->unique('customer_id') as $p)
                    @if($p->customer)
                        <option value="{{ $p->customer->id }}" {{ request('customer') == $p->customer->id ? 'selected' : '' }}>
                            {{ $p->customer->nama_perusahaan }}
                        </option>
                    @endif
                 @endforeach
            </select>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('finishgood.stock.export', request()->all()) }}" target="_blank"
                   class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" 
                   title="Export Data">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </a>
                <a href="{{ route('finishgood.stock.import.form') }}" 
                   class="flex items-center justify-center bg-white border border-gray-300 text-gray-700 p-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm" 
                   title="Import Stock Limits">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
            </div>
        </form>
    </div>

    {{-- Info Bar --}}
    <div class="bg-blue-50 border border-blue-100 rounded-md px-4 py-2.5 flex items-center gap-2 text-xs text-blue-800">
        <i class="fas fa-info-circle"></i>
        <span class="font-bold">Periode:</span> 
        @if(request('period') == 'today')
            {{ now()->format('d F Y') }}
        @else
            {{ now()->startOfMonth()->format('d F Y') }} - {{ now()->format('d F Y') }}
        @endif
        <span class="text-blue-300 mx-2">|</span>
        <span class="font-bold">Customer:</span> Semua
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        {{-- Card 1: Total Item --}}
        <div class="metric-card border-l-4-slate">
            <div>
                <div class="metric-title text-slate-600">TOTAL ITEM</div>
                <div class="metric-value">{{ $summary->total_items_count ?? count($stocks) }}</div>
            </div>
             <i class="fas fa-boxes metric-icon text-slate-400"></i>
        </div>

        {{-- Card 2: Stock Awal --}}
        <div class="metric-card border-l-4-teal">
            <div>
                 <div class="metric-title text-teal-600">STOCK AWAL</div>
                <div class="metric-value">{{ number_format($summary->stock_awal ?? 0) }}</div>
            </div>
             <i class="fas fa-warehouse metric-icon text-teal-400"></i>
        </div>
        
        {{-- Card 3: Total Received --}}
        <div class="metric-card border-l-4-emerald">
            <div>
                 <div class="metric-title text-emerald-600">TOTAL RECEIVED</div>
                <div class="metric-value">{{ number_format($summary->total_in ?? 0) }}</div>
            </div>
             <i class="fas fa-download metric-icon text-emerald-400"></i>
        </div>

        {{-- Card 4: Total Delivery --}}
        <div class="metric-card border-l-4-orange">
            <div>
                 <div class="metric-title text-orange-600">TOTAL DELIVERY</div>
                <div class="metric-value">{{ number_format($summary->total_out ?? 0) }}</div>
            </div>
             <i class="fas fa-upload metric-icon text-orange-400"></i>
        </div>

        {{-- Card 5: Stock Akhir --}}
        <div class="metric-card border-l-4-indigo">
             <div>
                 <div class="metric-title text-indigo-600">STOCK AKHIR</div>
                <div class="metric-value">{{ number_format($summary->total_units ?? 0) }}</div>
            </div>
             <i class="fas fa-check-circle metric-icon text-indigo-400"></i>
        </div>
    </div>

    {{-- Stock Level Filter Tabs --}}
    <div class="flex items-stretch gap-0 border border-gray-200 rounded-lg overflow-hidden bg-white shadow-sm mb-6">
        <button type="button" onclick="filterByStatus('ALL')" class="status-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[10px] font-bold text-gray-600 bg-white hover:bg-gray-50 transition-colors active-filter border-r border-gray-200">
            ALL STOCK
        </button>
        <button type="button" onclick="filterByStatus('KRITIS')" class="status-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[10px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors border-r border-gray-200">
            <span class="w-2 h-2 rounded-full bg-red-500"></span> KRITIS
        </button>
        <button type="button" onclick="filterByStatus('MINIM')" class="status-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[10px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors border-r border-gray-200">
            <span class="w-2 h-2 rounded-full bg-orange-500"></span> MINIM
        </button>
        <button type="button" onclick="filterByStatus('SAFE')" class="status-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[10px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors border-r border-gray-200">
            <span class="w-2 h-2 rounded-full bg-emerald-500"></span> SAFE
        </button>
        <button type="button" onclick="filterByStatus('OVER')" class="status-filter flex-1 flex items-center justify-center gap-1.5 px-3 py-2 text-[10px] font-semibold text-gray-600 bg-white hover:bg-gray-50 transition-colors">
            <span class="w-2 h-2 rounded-full bg-blue-500"></span> OVER
        </button>
    </div>


    {{-- Detailed Table --}}
    <div class="bg-white rounded-md shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-[10px] text-slate-500 font-extrabold uppercase tracking-widest">
                        <th class="px-6 py-4">Part Details</th>
                        <th class="px-3 py-4 text-center">Cust</th>
                        <th class="px-3 py-4 text-center">Type/Model</th>
                        <th class="px-4 py-4 text-right text-slate-400">STO Awal</th>
                        <th class="px-4 py-4 text-right text-emerald-600">IN</th>
                        <th class="px-4 py-4 text-right text-orange-600">OUT</th>
                        <th class="px-4 py-4 text-center">Level Stock</th>
                        <th class="px-6 py-4 text-right">Current Stock</th>
                        <th class="px-6 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-xs font-medium text-slate-700">
                    @forelse($stocks as $stock)
                    @php
                       // Real logic for status
                       $qty = $stock->qty;
                       $min = $stock->part->min_stock ?? 0;
                       $max = $stock->part->max_stock ?? 0;
                       
                       // Default Safe
                       $status = 'SAFE';
                       $badgeClass = 'status-safe';
                       
                       if($qty == 0) { $status = 'KRITIS'; $badgeClass = 'status-kritis'; }
                       elseif($min > 0 && $qty < $min) { $status = 'MINIM'; $badgeClass = 'status-minim'; }
                       elseif($max > 0 && $qty > $max) { $status = 'OVER'; $badgeClass = 'status-over'; }
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors stock-row" data-status="{{ $status }}">
                        <td class="px-6 py-3">
                            <div class="font-bold text-slate-800">{{ $stock->part->nomor_part ?? '-' }}</div>
                            <div class="text-[10px] text-slate-500">{{ $stock->part->nama_part ?? '-' }}</div>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                {{ $stock->part->customer->inisial_perusahaan ?? substr($stock->part->customer->nama_perusahaan ?? 'GEN', 0, 3) }}
                            </span>
                        </td>
                         <td class="px-3 py-3 text-center text-[10px] text-slate-500">
                            <div>{{ $stock->part->tipe_id ?? '-' }}</div>
                            <div class="text-[9px]">Regular</div>
                        </td>
                        <td class="px-4 py-3 text-right text-slate-400 font-mono">
                            {{ number_format($stock->opening_stock) }}
                        </td>
                        <td class="px-4 py-3 text-right text-emerald-600 font-bold font-mono">
                            @if($stock->period_in > 0)+@endif{{ number_format($stock->period_in) }}
                        </td>
                        <td class="px-4 py-3 text-right text-orange-600 font-bold font-mono">
                            @if($stock->period_out > 0)-@endif{{ number_format($stock->period_out) }}
                        </td>
                        
                        <td class="px-4 py-2">
                            <div class="space-y-1">
                                <div class="bg-slate-50 rounded border border-slate-100 p-1">
                                    <div class="level-grid">
                                        <div class="level-col border-r border-slate-200">
                                            <span class="level-label">Min</span>
                                            <span class="level-val {{ $min > 0 ? 'text-slate-600' : 'text-slate-300' }}">{{ $min > 0 ? number_format($min) : '-' }}</span>
                                        </div>
                                        <div class="level-col border-r border-slate-200">
                                            <span class="level-label text-emerald-500">Safe</span>
                                            <span class="level-val text-emerald-600">
                                                @if($min > 0 && $max > 0)
                                                    {{ number_format($min) }}-{{ number_format($max) }}
                                                @else
                                                    -
                                                @endif
                                            </span>
                                        </div>
                                        <div class="level-col">
                                            <span class="level-label">Max</span>
                                            <span class="level-val {{ $max > 0 ? 'text-blue-500' : 'text-slate-300' }}">{{ $max > 0 ? number_format($max) : '-' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <button @click="openLimitModal({{ $stock->part_id }}, '{{ $stock->part->nomor_part }}', {{ $min }}, {{ $max }})" 
                                    class="w-full bg-blue-500 hover:bg-blue-600 text-white text-[10px] font-bold py-1 px-2 rounded flex items-center justify-center gap-1 transition-colors" 
                                    title="Edit Stock Limits">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </button>
                            </div>
                        </td>
                        
                        <td class="px-6 py-3 text-right font-black text-slate-800 text-sm font-mono">
                            {{ number_format($stock->qty) }}
                        </td>
                        <td class="px-6 py-3 text-center">
                            <span class="status-badge {{ $badgeClass }}">{{ $status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-slate-400">
                            <div class="flex flex-col items-center justify-center gap-2">
                                <i class="fas fa-box-open text-4xl opacity-20"></i>
                                <span class="text-xs font-medium">No stock data available</span>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Update Limits Modal --}}
    <div x-show="limitModalOpen" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div x-show="limitModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="limitModalOpen = false"></div>

            <div x-show="limitModalOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="relative bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full max-w-lg z-10">
                <form :action="`{{ url('finishgood/stock/update-limits') }}/${limitPartId}`" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-sliders-h text-blue-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Update Stock Limits
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 mb-4">Set minimum and maximum stock levels for part <strong x-text="limitPartName"></strong>.</p>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Min Stock</label>
                                            <input type="number" name="min_stock" x-model="limitMin" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required min="0">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Max Stock</label>
                                            <input type="number" name="max_stock" x-model="limitMax" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm" required min="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Changes
                        </button>
                        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="limitModalOpen = false">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.active-filter {
    background-color: #1e293b !important; /* Slate 800 */
    color: white !important;
    font-weight: bold !important;
}
.active-filter span {
    background-color: white !important;
}
.active-filter i {
    color: white !important;
}
</style>

<script>
function filterByStatus(status) {
    // Remove active class from all filters
    document.querySelectorAll('.status-filter').forEach(btn => {
        btn.classList.remove('active-filter');
    });
    
    // Add active class to clicked filter
    event.target.closest('.status-filter').classList.add('active-filter');
    
    // Get all stock rows
    const rows = document.querySelectorAll('.stock-row');
    
    if (status === 'ALL') {
        // Show all rows
        rows.forEach(row => {
            row.style.display = '';
        });
    } else {
        // Filter by status
        rows.forEach(row => {
            if (row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
}
</script>
@endsection
