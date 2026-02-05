@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Back Button --}}
    <a href="{{ route('master.perusahaan.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-6 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        <span class="font-medium">Kembali ke Daftar</span>
    </a>

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 font-bold text-2xl">
                    {{ substr($perusahaan->nama_perusahaan, 0, 1) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $perusahaan->nama_perusahaan }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $perusahaan->jenis_perusahaan ?? 'Tidak ada jenis' }}
                        </span>
                        @if($perusahaan->status)
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Active
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>
            </div>
            
            @if(userCan('master.perusahaan.edit'))
            <a href="{{ route('master.perusahaan.edit', $perusahaan->id) }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Profil
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-fit">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Informasi Detail
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Inisial</label>
                    <p class="text-gray-900">{{ $perusahaan->inisial_perusahaan ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Kode Supplier/BP</label>
                    <p class="text-gray-900 font-mono bg-gray-50 inline-block px-2 py-1 rounded">{{ $perusahaan->kode_supplier ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Alamat</label>
                    <p class="text-gray-900">{{ $perusahaan->alamat ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Terdaftar Sejak</label>
                    <p class="text-gray-900">{{ $perusahaan->created_at->format('d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Stats / Dashboard Card (Placeholder) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm font-medium text-gray-500">Total Transaksi (PO)</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_po'] ?? 0 }}</div>
                    <div class="text-xs text-gray-400 mt-1">Semua waktu</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm font-medium text-gray-500">Total Nilai Pembelian</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">Rp {{ number_format($stats['total_spending'] ?? 0, 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-400 mt-1">Estimasi</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm font-medium text-gray-500">Transaksi Terakhir</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ $stats['last_transaction'] }}</div>
                </div>
            </div>

            {{-- Placeholder for Future Charts/Tables --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[300px] flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <p class="font-medium">Statistik & Grafik Detil akan muncul di sini</p>
                <p class="text-sm">Menunggu integrasi modul PO & Invoice.</p>
            </div>
        </div>
    </div>
</div>
@endsection
