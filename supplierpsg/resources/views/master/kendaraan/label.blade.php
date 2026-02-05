<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Label Kendaraan - {{ $kendaraan->nopol_kendaraan }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printableArea, #printableArea * {
                visibility: visible;
            }
            #printableArea {
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 100%;
                margin: 0;
                padding: 0;
                box-shadow: none !important;
                border: none !important;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: auto;
                margin: 0mm;
            }
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div id="printableArea" class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 w-full max-w-sm">
        <div class="text-center mb-4">
            <h1 class="text-2xl font-bold text-gray-800 tracking-wider border-b-2 border-gray-800 pb-2 inline-block">
                {{ $kendaraan->nopol_kendaraan }}
            </h1>
            <p class="text-sm text-gray-500 mt-2  uppercase tracking-wide font-semibold">{{ $kendaraan->jenis_kendaraan }} - {{ $kendaraan->merk_kendaraan }}</p>
        </div>

        <div class="flex flex-col items-center justify-center p-4 bg-gray-50 rounded-lg border border-gray-100">
            <div id="qrBox" data-qrcode="{{ $kendaraan->qrcode ?? $kendaraan->nopol_kendaraan }}"></div>
            <p class="mt-2 text-xs text-gray-400 font-mono">{{ $kendaraan->qrcode ?? $kendaraan->nopol_kendaraan }}</p>
        </div>

        <div class="mt-6 border-t border-gray-100 pt-4">
            <div class="flex justify-between text-sm">
                <span class="text-gray-500">Tahun:</span>
                <span class="font-semibold text-gray-700">{{ $kendaraan->tahun_kendaraan }}</span>
            </div>
            <div class="flex justify-between text-sm mt-1">
                <span class="text-gray-500">Status:</span>
                <span class="font-semibold text-gray-700">{{ $kendaraan->status ? 'Aktif' : 'Non-Aktif' }}</span>
            </div>
        </div>

        <div class="mt-8 flex justify-center no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2 shadow-sm font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Print Label
            </button>
        </div>
    </div>

    <!-- Script QR Code -->
    <script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
    <script>
    (function(){
      function generateQR() {
        const box = document.getElementById('qrBox');
        if (!box) {
          setTimeout(generateQR, 50);
          return;
        }
        
        // Wait for library to load
        if (typeof qrcode === 'undefined' || typeof qrcode !== 'function') {
          setTimeout(generateQR, 100);
          return;
        }
        
        const text = box.getAttribute('data-qrcode') || '';
        if (!text) {
            box.innerHTML = '<span class="text-gray-400 text-xs text-center">QR Code<br>Tidak Tersedia</span>';
            return;
        }
        
        try {
          // TypeNumber 0 (Auto), ErrorCorrectionLevel 'M' (Medium)
          const qr = qrcode(0, 'M');
          qr.addData(String(text));
          qr.make();
          // Scale 6, Margin 0 (kita pakai padding div)
          box.innerHTML = qr.createImgTag(6, 0);
        } catch (error) {
          console.error('Error generating QR:', error);
          box.innerHTML = '<span class="text-red-400 text-xs">Error QR</span>';
        }
      }
      
      // Start checking
      generateQR();
    })();
    </script>
</body>
</html>
