@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Back Button --}}
    <a href="{{ route('master.manpower.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-6 transition-colors">
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
                    {{ substr($manpower->nama, 0, 2) }}
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $manpower->nama }}</h1>
                    <div class="flex items-center gap-2 mt-1">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            {{ $manpower->nik ?? 'No NIK' }}
                        </span>
                        @if($manpower->status)
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
            
            <div class="flex gap-2">
                <a href="{{ route('master.manpower.idcard', $manpower->id) }}" target="_blank" class="flex items-center gap-2 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    Print ID Card
                </a>
                @if(userCan('master.manpower.edit'))
                <a href="{{ route('master.manpower.edit', $manpower->id) }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Data
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Info Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 h-fit">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Informasi Personal
            </h3>
            
            <div class="space-y-4">
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Departemen</label>
                    <p class="text-gray-900 font-medium">{{ $manpower->departemen ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Bagian</label>
                    <p class="text-gray-900 font-medium">{{ $manpower->bagian ?? '-' }}</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">QR Code</label>
                    <div class="flex items-center gap-2">
                         <p class="text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded">{{ $manpower->qrcode ?? '-' }}</p>
                    </div>
                </div>
                 <div>
                    <label class="text-xs font-semibold text-gray-500 uppercase">Terdaftar Sejak</label>
                    <p class="text-gray-900">{{ $manpower->created_at->format('d F Y') }}</p>
                </div>
            </div>
        </div>

        {{-- Stats / Dashboard Card (Placeholder) --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm font-medium text-gray-500">Total Jam Kerja</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total_hours'] ?? 0 }}</div>
                    <div class="text-xs text-gray-400 mt-1">Minggu Ini</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm font-medium text-gray-500">Kehadiran</div>
                    <div class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['attendance'] ?? '0%' }}</div>
                    <div class="text-xs text-gray-400 mt-1">Bulan Ini</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="text-sm font-medium text-gray-500">Aktivitas Terakhir</div>
                    <div class="text-lg font-bold text-gray-900 mt-1">{{ $stats['last_active'] }}</div>
                </div>
            </div>

            {{-- Placeholder for Activity Log --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 min-h-[300px] flex flex-col items-center justify-center text-gray-400">
                <svg class="w-16 h-16 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="font-medium">Histori aktivitas akan muncul di sini</p>
                <p class="text-sm">Menunggu integrasi modul absensi/produksi.</p>
            </div>
        </div>
    </div>
</div>
@endsection
