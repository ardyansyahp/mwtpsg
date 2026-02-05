@extends('layout.app')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .status-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 0.25rem;
        font-weight: 700;
        text-transform: uppercase;
        color: white;
    }
    .status-kritis { background-color: #ef4444; }
    .status-minim { background-color: #f97316; }
    .status-safe { background-color: #22c55e; }
    .status-over { background-color: #3b82f6; }
    .status-noorder { background-color: #1e293b; }
    .status-error { background-color: #eab308; }

    .level-stock-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2px;
        text-align: center;
        font-size: 0.6rem;
    }
    .level-header { color: #64748b; font-weight: 600; font-size: 0.55rem; text-transform: uppercase; }
    .level-value { font-weight: 700; color: #334155; }
    .schedule-info { font-size: 0.6rem; color: #64748b; margin-top: 2px; }
    
    .filter-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        transition: all 0.2s;
    }
    .filter-btn.active {
        background-color: #1e40af;
        color: white;
    }
    .filter-btn:not(.active) {
        background-color: white;
        color: #64748b;
        border: 1px solid #e2e8f0;
    }
</style>
@endpush

@section('content')
<div class="px-6 py-6 space-y-6 bg-slate-50 min-h-screen font-sans">
    
    {{-- Header & Filters --}}
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-2">
        <div class="flex items-center gap-3">
             <div class="p-2 bg-slate-800 rounded-lg text-white">
                <i class="fas fa-chart-line"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-800">Stock Finished Goods</h1>
        </div>
        
        <form action="" method="GET" class="flex flex-wrap items-center gap-2">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <i class="fas fa-search text-xs"></i>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Part ID/Name/Customer..." 
                    class="pl-9 pr-4 py-2 w-64 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
            </div>

            <select name="period" class="py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                <option value="month">Bulan Ini</option>
                <option value="today">Hari Ini</option>
            </select>

             <select name="customer" class="py-2 pl-3 pr-8 bg-white border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none cursor-pointer">
                <option value="">Semua Customer</option>
                 @foreach($parts->unique('customer_id') as $p) {{-- Assuming parts collection has customer info, otherwise adjust --}}
                    {{-- This is a placeholder as Controller didn't pass customers list separately yet --}}
                 @endforeach
            </select>
            
            <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 transition-colors">
                <i class="fas fa-file-excel"></i> Export
            </button>
        </form>
    </div>

    {{-- Info Bar --}}
    <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 flex items-center gap-2 text-sm text-blue-800 mb-6">
        <i class="fas fa-info-circle"></i>
        <span class="font-medium">Periode: {{ now()->startOfMonth()->format('d F Y') }} - {{ now()->format('d F Y') }}</span>
        <span class="text-blue-300">|</span>
        <span class="font-medium">Customer: Semua</span>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        {{-- Card 1: Total Item --}}
        <div class="glass-card p-4 rounded-xl bg-slate-800 border-none text-white relative overflow-hidden group">
            <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-boxes fa-3x"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-slate-300 mb-1">TOTAL ITEM</p>
            <h3 class="text-3xl font-bold">{{ $summary->total_items_count ?? count($stocks) }}</h3>
            <div class="mt-4 flex justify-end">
                <i class="fas fa-cubes text-slate-400"></i>
            </div>
        </div>

        {{-- Card 2: Stock Awal --}}
        <div class="glass-card p-4 rounded-xl bg-teal-700 border-none text-white relative overflow-hidden group">
             <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-warehouse fa-3x"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-teal-200 mb-1">STOCK AWAL</p>
            <h3 class="text-3xl font-bold">{{ number_format($summary->stock_awal ?? 0) }}</h3>
            <div class="mt-4 flex justify-end">
                 <i class="fas fa-door-open text-teal-300"></i>
            </div>
        </div>
        
        {{-- Card 3: Total Received --}}
        <div class="glass-card p-4 rounded-xl bg-emerald-500 border-none text-white relative overflow-hidden group">
             <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-download fa-3x"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-100 mb-1">TOTAL RECEIVED</p>
            <h3 class="text-3xl font-bold">{{ number_format($summary->total_in ?? 0) }}</h3>
            <div class="mt-4 flex justify-end">
                 <i class="fas fa-arrow-down text-emerald-200"></i>
            </div>
        </div>

        {{-- Card 4: Total Delivery --}}
        <div class="glass-card p-4 rounded-xl bg-orange-400 border-none text-white relative overflow-hidden group">
             <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-upload fa-3x"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-orange-100 mb-1">TOTAL DELIVERY</p>
            <h3 class="text-3xl font-bold">{{ number_format($summary->total_out ?? 0) }}</h3>
             <div class="mt-4 flex justify-end">
                 <i class="fas fa-arrow-up text-orange-200"></i>
            </div>
        </div>

        {{-- Card 5: Stock Akhir --}}
        <div class="glass-card p-4 rounded-xl bg-indigo-600 border-none text-white relative overflow-hidden group">
             <div class="absolute right-0 top-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-check-circle fa-3x"></i>
            </div>
            <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-200 mb-1">STOCK AKHIR</p>
            <h3 class="text-3xl font-bold">{{ number_format($summary->total_units ?? 0) }}</h3>
             <div class="mt-4 flex justify-end">
                 <i class="fas fa-cubes text-indigo-300"></i>
            </div>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-slate-200 pb-4">
        <button class="filter-btn active bg-slate-800 text-white border-none">ALL STOCK</button>
        
        <div class="flex items-center gap-2 pl-4 border-l border-slate-200">
            <div class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>
                <span class="text-xs font-bold text-slate-600 uppercase">KRITIS</span>
            </div>
             <div class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded">
                <span class="w-2.5 h-2.5 rounded-full bg-orange-500"></span>
                <span class="text-xs font-bold text-slate-600 uppercase">MINIM</span>
            </div>
             <div class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-slate-600 uppercase">SAFE</span>
            </div>
             <div class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                <span class="text-xs font-bold text-slate-600 uppercase">OVER</span>
            </div>
             <div class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded">
                <span class="w-2.5 h-2.5 rounded-full bg-slate-800"></span>
                <span class="text-xs font-bold text-slate-600 uppercase">NO ORDER</span>
            </div>
             <div class="flex items-center gap-1.5 cursor-pointer hover:bg-slate-100 px-2 py-1 rounded">
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span>
                <span class="text-xs font-bold text-slate-600 uppercase">ERROR</span>
            </div>
        </div>
    </div>

    {{-- Detailed Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-200 text-xs text-slate-500 font-bold uppercase tracking-wider">
                        <th class="px-4 py-4">Part Number</th>
                        <th class="px-4 py-4">Part Name</th>
                        <th class="px-4 py-4">Type</th>
                        <th class="px-4 py-4 text-center">Customer</th>
                        <th class="px-4 py-4">Model</th>
                        <th class="px-4 py-4 text-center">STO Awal</th>
                        <th class="px-4 py-4 text-center text-emerald-600">IN</th>
                        <th class="px-4 py-4 text-center text-orange-600">OUT</th>
                        <th class="px-4 py-4 text-center bg-slate-50">Level Stock</th>
                        <th class="px-4 py-4 text-center">Current</th>
                        <th class="px-4 py-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($stocks as $stock)
                    @php
                       // Mock logic for status based on levels (You can refine this with real MMax/MMin data)
                       $qty = $stock->qty;
                       $min = 1000; // Placeholder
                       $safe = 2500; // Placeholder
                       $max = 5000; // Placeholder
                       
                       $status = 'SAFE';
                       $badgeClass = 'status-safe';
                       
                       if($qty == 0) { $status = 'KRITIS'; $badgeClass = 'status-kritis'; }
                       elseif($qty < $min) { $status = 'MINIM'; $badgeClass = 'status-minim'; }
                       elseif($qty > $max) { $status = 'OVER'; $badgeClass = 'status-over'; }
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors text-sm text-slate-700">
                        <td class="px-4 py-3 font-bold">{{ $stock->part->nomor_part ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-800">{{ $stock->part->nama_part ?? '-' }}</div>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $stock->part->tipe_id ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded text-[10px] font-bold">
                                {{ $stock->part->customer->inisial_perusahaan ?? 'GEN' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">Regular</td> {{-- Placeholder for Model --}}
                        <td class="px-4 py-3 text-center font-bold text-slate-600">
                            {{ number_format($stock->opening_stock) }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-emerald-600">
                            +{{ number_format($stock->period_in) }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-orange-600">
                            -{{ number_format($stock->period_out) }}
                        </td>
                        
                        <td class="px-4 py-2 bg-slate-50/50">
                            <div class="level-stock-grid">
                                <span class="level-header">MIN</span>
                                <span class="level-header text-emerald-600">SAFE</span>
                                <span class="level-header">MAX</span>
                                
                                <span class="level-value text-red-500">{{ number_format($min) }}</span>
                                <span class="level-value text-emerald-600">{{ number_format($safe) }}</span>
                                <span class="level-value text-blue-500">{{ number_format($max) }}</span>
                            </div>
                            <div class="text-center schedule-info">Schedule: 700 Pcs/day</div>
                        </td>
                        
                        <td class="px-4 py-3 text-center font-black text-slate-800 text-base">
                            {{ number_format($stock->qty) }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="status-badge {{ $badgeClass }}">{{ $status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="px-6 py-12 text-center text-slate-400">
                            <i class="fas fa-box-open text-4xl mb-3 opacity-30"></i>
                            <p>No stock data available for current selection.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
