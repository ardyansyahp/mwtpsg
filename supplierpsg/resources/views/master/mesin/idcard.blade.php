@extends('layout.app')

@section('content')
<div class="min-h-screen bg-gray-100 p-8 flex flex-col items-center">
    {{-- Controls --}}
    <div class="mb-8 flex gap-4 no-print w-full max-w-2xl justify-between items-center">
        <a href="{{ route('master.mesin.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        
        <div class="flex gap-3">
             <div class="bg-blue-50 text-blue-800 px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-info-circle mr-1"></i> Rekomendasi: Kertas A5 Landscape / A4 (2 Halaman)
            </div>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak ID Card
            </button>
        </div>
    </div>

    {{-- Printable Area (A5 Ratio) --}}
    <div id="printableArea" class="bg-white shadow-xl overflow-hidden relative">
        {{-- Border container for print margin safety --}}
        <div class="w-full h-full p-6 flex flex-col justify-between relative z-10">
            
            {{-- Header --}}
            <div class="flex justify-between items-end border-b-4 border-blue-600 pb-4 mb-4">
                <div>
                   <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">MESIN PRODUKSI</h1>
                   <p class="text-gray-500 font-medium uppercase tracking-widest mt-1 text-sm">Asset Identification Card</p>
                </div>
                {{-- Logo --}}
                 <img src="{{ asset('assets/images/logo.png') }}" onerror="this.style.display='none'" alt="Logo" class="h-12 object-contain grayscale opacity-80"> 
            </div>

            {{-- Body Content --}}
            <div class="flex-1 flex flex-row gap-8 items-center">
                {{-- Left: QR Code --}}
                <div class="w-1/3 flex flex-col items-center justify-center bg-gray-50 p-4 rounded-xl border border-gray-200">
                     <div id="qrBox" class="bg-white p-2" data-qrcode="{{ $mesin->qrcode }}"></div>
                     <p class="text-xs font-mono text-gray-400 mt-2 text-center break-all">{{ $mesin->qrcode }}</p>
                </div>

                {{-- Right: Details --}}
                <div class="w-2/3 flex flex-col justify-center space-y-5">
                    
                    {{-- No Mesin (Hero) --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nomor Mesin</label>
                        <div class="text-6xl font-black text-gray-900 leading-none">
                            {{ $mesin->no_mesin }}
                        </div>
                    </div>

                    {{-- Grid Spec --}}
                    <div class="grid grid-cols-2 gap-y-4 gap-x-8 pt-2">
                        <div>
                             <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Merk / Brand</label>
                             <div class="text-2xl font-bold text-gray-800">{{ $mesin->merk_mesin ?? '-' }}</div>
                        </div>
                        <div>
                             <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">Kapasitas (Ton)</label>
                             <div class="text-2xl font-bold text-gray-800">{{ $mesin->tonase ?? 0 }} <span class="text-sm font-medium text-gray-500">Ton</span></div>
                        </div>
                        <div class="col-span-2">
                             <label class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-0.5">System ID</label>
                             <div class="text-lg font-mono text-gray-600 bg-gray-100 inline-block px-2 py-1 rounded">{{ $mesin->mesin_id }}</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <div class="mt-6 pt-2 border-t border-gray-200 flex justify-between items-center text-xs text-gray-400">
                <span>Property of PT Mada Wikri Tunggal</span>
                <span class="font-mono">{{ date('d/m/Y H:i') }}</span>
            </div>
        </div>

        {{-- Background Decoration --}}
        <div class="absolute top-0 right-0 w-32 h-full bg-blue-50 skew-x-12 transform translate-x-16 -z-0"></div>
    </div>
</div>

{{-- Styles for Print --}}
<style>
    /* Screen Preview Style (Approximating A5) */
    #printableArea {
        width: 210mm;
        height: 148mm;
        margin: 0 auto;
        box-sizing: border-box;
    }

    @media print {
        @page {
            size: A5 landscape; /* Force A5 Landscape */
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
            min-height: auto;
        }

        body * {
            visibility: hidden;
        }

        #printableArea, #printableArea * {
            visibility: visible;
        }

        #printableArea {
            position: fixed;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            box-shadow: none;
            border-radius: 0;
            /* Center vertically/horizontally if paper is larger, but @page size usually handles it */
            display: flex;
            flex-direction: column;
        }

        .no-print {
            display: none !important;
        }
        
        /* Ensure background colors print */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const box = document.getElementById('qrBox');
        const text = box.getAttribute('data-qrcode');
        
        if (text) {
            box.innerHTML = '';
            // Generate bigger QR for print
            new QRCode(box, {
                text: text,
                width: 150,
                height: 150, // Bigger QR
                colorDark : "#111827", // Gray-900
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        }
    });
</script>
@endpush

