@extends('layout.app')

@section('content')
<div class="min-h-screen bg-gray-100 p-8 flex flex-col items-center">
    {{-- Controls --}}
    <div class="mb-8 flex gap-4 no-print w-full max-w-2xl justify-between items-center">
        <a href="{{ route('master.manpower.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center gap-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        
        <div class="flex gap-3">
             <div class="bg-blue-50 text-blue-800 px-4 py-2 rounded-lg text-sm">
                <i class="fas fa-info-circle mr-1"></i> Ukuran Standard Kartu ID (CR-80 / ATM)
            </div>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-bold shadow-sm transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak ID Card
            </button>
        </div>
    </div>

    {{-- Printable Area (ID-1 Size: 85.6mm x 53.98mm) --}}
    <!-- We use slightly larger wrapper for shadow/preview, inner div is exact size -->
    <div class="bg-white shadow-2xl rounded-lg overflow-hidden relative" style="width: 85.6mm; height: 53.98mm;" id="printableArea">
        
        {{-- Card Background / Design --}}
        <div class="w-full h-full relative z-10 flex flex-row overflow-hidden border border-gray-200" style="border-radius: 3mm;">
            
            {{-- Left Strip (Accent) --}}
            <div class="w-[6mm] h-full bg-blue-600 flex flex-col items-center justify-end pb-2">
                 <div class="text-[6px] text-white font-bold tracking-widest writing-vertical transform -rotate-180 opacity-80 whitespace-nowrap">
                    PT MADA WIKRI TUNGGAL
                 </div>
            </div>

            {{-- Main Content --}}
            <div class="flex-1 p-[4mm] flex flex-col justify-between relative bg-white">
                
                {{-- Header --}}
                <div class="flex justify-between items-start mb-2">
                    <div>
                         <img src="{{ asset('assets/images/logo.png') }}" onerror="this.style.display='none'" alt="Logo" class="h-[6mm] object-contain"> 
                    </div>
                    <div class="text-right">
                        <span class="block text-[6px] font-bold text-gray-400 uppercase tracking-wider">EMPLOYEE ID CARD</span>
                    </div>
                </div>

                {{-- Body: Photo + Info --}}
                <div class="flex gap-[3mm] items-center">
                    {{-- Photo Placeholder / Avatar --}}
                    <div class="w-[20mm] h-[25mm] bg-gray-100 rounded border border-gray-200 flex items-center justify-center overflow-hidden">
                        {{-- Determine Initials --}}
                        <span class="text-gray-400 text-xs font-bold">{{ substr($manpower->nama, 0, 2) }}</span>
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 space-y-[2px]">
                        <div>
                            <span class="block text-[10px] font-bold text-gray-900 leading-tight">{{ \Illuminate\Support\Str::limit($manpower->nama, 20) }}</span>
                            <span class="block text-[6px] text-gray-500 font-semibold">{{ $manpower->nik ?? '-' }}</span>
                        </div>
                        
                        <div class="pt-[2px]">
                            <span class="block text-[6px] text-gray-400 uppercase">DEPARTEMEN</span>
                            <span class="block text-[7px] text-blue-800 font-bold uppercase leading-none">{{ $manpower->departemen ?? '-' }}</span>
                        </div>

                         <div class="pt-[1px]">
                             <span class="block text-[6px] text-gray-400 uppercase">BAGIAN</span>
                            <span class="block text-[7px] text-gray-700 font-semibold uppercase leading-none">{{ $manpower->bagian ?? '-' }}</span>
                        </div>
                    </div>
                    
                    {{-- QR Code (Compact) --}}
                     <div class="w-[18mm] flex flex-col items-center justify-center">
                         <div id="qrBox" class="bg-white" data-qrcode="{{ $manpower->qrcode }}"></div>
                    </div>
                </div>

                {{-- Footer Strip --}}
                <div class="mt-auto pt-1 border-t border-gray-100 flex justify-between items-center">
                     <span class="text-[5px] text-gray-400 font-mono">{{ $manpower->qrcode }}</span>
                     <span class="text-[5px] text-blue-400 font-bold">MWT GROUP</span>
                </div>

            </div>
             {{-- Background Watermark/Decoration --}}
            <div class="absolute -bottom-4 -right-4 w-20 h-20 bg-blue-50 rounded-full opacity-50 z-0 pointer-events-none"></div>
        </div>
    </div>
</div>

<style>
    .writing-vertical {
        writing-mode: vertical-rl;
    }

    #printableArea {
        box-sizing: border-box;
        /* Exact CR-80 Dimensions */
        width: 85.6mm; 
        height: 53.98mm; 
        margin: 0 auto;
    }

    @media print {
        @page {
            size: auto; /* Let content dictate or use user settings if they have card printer */
             /* Or specific: size: 85.6mm 53.98mm; if supported */
            margin: 0;
        }
        
        body {
            margin: 0;
            padding: 0;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        body * {
            visibility: hidden;
        }

        #printableArea, #printableArea * {
            visibility: visible;
        }

        #printableArea {
            position: fixed;
            left: 50%;
            top: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            padding: 0;
            box-shadow: none !important;
            border: none !important;
        }

        .no-print {
            display: none !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            print-color-adjust: exact;
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
            // Generate QR optimized for small size (approx 15-18mm)
            // 1mm ~ 3.78px. 18mm ~ 68px
            new QRCode(box, {
                text: text,
                width: 64, // Small pixels
                height: 64, 
                colorDark : "#1f2937", // Gray-800
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M // Medium correction for density
            });
        }
    });
</script>
@endpush

