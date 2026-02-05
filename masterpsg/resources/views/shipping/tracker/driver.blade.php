@extends('layout.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-900 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden">
        <!-- Header -->
        <div class="bg-indigo-600 p-6 text-center">
            <h1 class="text-2xl font-bold text-white mb-1">Tracker Aktif</h1>
            <p class="text-indigo-200 text-sm">Experimental GPS Trace</p>
        </div>

        <!-- Info -->
        <div class="p-6">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4 animate-pulse">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">{{ $delivery->kendaraan->nopol_kendaraan ?? '-' }}</h3>
                <p class="text-gray-500 mb-2">{{ $delivery->destination ?? '-' }}</p>
                <div class="text-xs font-mono bg-gray-100 py-1 px-3 rounded inline-block text-gray-600">
                    ID: #{{ $delivery->id }}
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-sm font-medium text-blue-800">Status GPS</span>
                        <span id="gpsStatus" class="px-2 py-0.5 rounded textxs font-bold bg-gray-200 text-gray-600">Menunggu...</span>
                    </div>
                    <p id="gpsCoords" class="text-xs text-blue-600 font-mono">-</p>
                </div>

                <div class="text-center">
                    <p class="text-xs text-gray-400 mb-4">
                        Aplikasi akan mengirim lokasi Anda secara otomatis setiap 15 menit. 
                        <br><strong class="text-red-500">JANGAN TUTUP HALAMAN INI</strong>
                    </p>
                    
                    <button onclick="sendLocation()" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-bold transition-colors mb-2">
                        Kirim Manual Sekarang
                    </button>
                    
                    <a href="{{ route('shipping.delivery.index') }}" class="block text-center text-sm text-indigo-600 hover:text-indigo-800 py-2">
                        Kembali ke Menu Utama
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="bg-gray-50 p-4 text-center border-t border-gray-100">
             <p class="text-[10px] text-gray-400">Log terakhir: <span id="lastLog">-</span></p>
        </div>
    </div>
</div>

<script>
    const DELIVERY_ID = {{ $delivery->id }};
    const CSRF_TOKEN = '{{ csrf_token() }}';
    
    document.addEventListener('DOMContentLoaded', function() {
        requestPermission();
        
        // Send immediately on load
        sendLocation();
        
        // Schedule every 15 minutes (900,000 ms)
        setInterval(sendLocation, 900000); 
    });

    function requestPermission() {
        if ("geolocation" in navigator) {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    updateStatus('Ready', 'bg-green-100 text-green-700');
                },
                (error) => {
                    updateStatus('Error: ' + error.message, 'bg-red-100 text-red-700');
                }
            );
        } else {
             updateStatus('Not Supported', 'bg-red-100 text-red-700');
        }
    }

    function sendLocation() {
        updateStatus('Sending...', 'bg-yellow-100 text-yellow-700');
        
        if (!("geolocation" in navigator)) {
            return;
        }

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('gpsCoords').innerText = `${lat}, ${lng}`;

                try {
                    const response = await fetch('{{ route("shipping.tracker.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN
                        },
                        body: JSON.stringify({
                            delivery_id: DELIVERY_ID,
                            latitude: lat,
                            longitude: lng,
                            device_info: navigator.userAgent
                        })
                    });
                    
                    const result = await response.json();
                    
                    if(result.success) {
                        updateStatus('Active', 'bg-green-100 text-green-700');
                        document.getElementById('lastLog').innerText = new Date().toLocaleTimeString();
                    } else {
                        updateStatus('Server Error', 'bg-red-100 text-red-700');
                    }
                } catch (err) {
                     updateStatus('Net Error', 'bg-red-100 text-red-700');
                }
            },
            (error) => {
                updateStatus('GPS Failed', 'bg-red-100 text-red-700');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    }

    function updateStatus(text, classes) {
        const badge = document.getElementById('gpsStatus');
        badge.innerText = text;
        badge.className = `px-2 py-0.5 rounded text-xs font-bold ${classes}`;
    }
</script>
@endsection
