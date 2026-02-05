@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
        <a href="{{ route(\'dashboard\') }}"\1>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

            <h2 class="text-3xl font-bold text-gray-800">WIP In - Informasi</h2>
            <p class="text-gray-600 mt-1">Data dari inject out otomatis masuk ke WIP In. Silakan konfirmasi di halaman utama.</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="border border-blue-200 rounded-lg p-6 bg-blue-50">
            <div class="flex items-start gap-4">
                <svg class="w-8 h-8 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Proses Otomatis</h3>
                    <p class="text-gray-700 mb-4">
                        Data dari <strong>Inject Out</strong> sekarang otomatis masuk ke <strong>WIP In</strong> dengan status <strong>Pending</strong>. 
                        Anda tidak perlu scan lagi. Cukup konfirmasi data yang sudah masuk di halaman utama WIP In.
                    </p>
                    <div class="bg-white rounded-lg p-4 border border-blue-200 mb-4">
                        <h4 class="font-medium text-gray-800 mb-2">Alur Baru:</h4>
                        <ol class="list-decimal list-inside space-y-1 text-sm text-gray-700">
                            <li>Scan label di <strong>Inject Out</strong></li>
                            <li>Data otomatis masuk ke <strong>WIP In</strong> (status: Pending)</li>
                            <li>Buka halaman <strong>WIP In</strong> dan klik tombol <strong>Confirm</strong></li>
                            <li>Data berubah status menjadi <strong>Confirmed</strong></li>
                        </ol>
                    </div>
                    <a href="{{ route(\'dashboard\') }}"\1>
                Tutup
            </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
