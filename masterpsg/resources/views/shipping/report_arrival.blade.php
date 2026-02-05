@extends('layout.app')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .camera-container {
        position: relative;
        width: 100%;
        max-width: 500px;
        margin: 0 auto;
        aspect-ratio: 3/4;
        background: #000;
        border-radius: 1.5rem;
        overflow: hidden;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    #camera-feed {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .camera-overlay {
        position: absolute;
        inset: 0;
        border: 2px dashed rgba(255, 255, 255, 0.5);
        margin: 2rem;
        border-radius: 1rem;
        pointer-events: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .camera-overlay::after {
        content: 'ARAHKAN KE GERBANG TUJUAN';
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.75rem;
        font-weight: bold;
        letter-spacing: 0.1em;
    }
    #captured-image {
        display: none;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .capture-btn {
        width: 4rem;
        height: 4rem;
        background: white;
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
    }
    .capture-btn:active {
        transform: scale(0.9);
        background: #f3f4f6;
    }
    .capture-btn-inner {
        width: 3rem;
        height: 3rem;
        background: #ef4444;
        border-radius: 50%;
    }
</style>
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-8 flex items-center justify-between">
        <div>
            <a href="{{ route('shipping.delivery.index') }}" class="text-sm text-gray-500 hover:text-indigo-600 flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Lapor Kedatangan</h1>
            <p class="text-xs text-gray-500 mt-1">Sediakan bukti foto gerbang/lokasi tujuan untuk absen.</p>
        </div>
        <div class="bg-indigo-600 text-white px-3 py-1.5 rounded-full text-[10px] font-bold uppercase tracking-wider">
            {{ $delivery->no_surat_jalan }}
        </div>
    </div>

    <!-- Main Content -->
    <div class="space-y-6">
        <!-- Delivery Info Card -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center text-indigo-600 shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">Lokasi Tujuan</p>
                <p class="font-bold text-gray-900">{{ $delivery->destination }}</p>
            </div>
        </div>

        <!-- Maps Section -->
        <div class="bg-white rounded-2xl p-4 border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <p class="text-sm font-bold text-gray-900">Lokasi GPS Anda</p>
                </div>
                <div id="gps-status" class="text-xs text-gray-400">Mencari lokasi...</div>
            </div>
            <div id="map" class="w-full h-64 rounded-xl overflow-hidden bg-gray-100"></div>
            <div id="coordinates" class="mt-2 text-xs text-gray-500 text-center"></div>
        </div>

        <!-- Camera Interface -->
        <div id="camera-section">
            <div class="camera-container mb-6">
                <video id="camera-feed" autoplay playsinline></video>
                <canvas id="photo-canvas" style="display: none;"></canvas>
                <img id="captured-image" alt="Captured proof">
                <div class="camera-overlay" id="camera-overlay"></div>
            </div>

            <div class="flex flex-col items-center gap-4">
                <div id="capture-controls" class="flex items-center gap-8">
                    <button id="switch-camera" class="p-3 bg-gray-100 rounded-full text-gray-600 hover:bg-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    </button>
                    
                    <div class="capture-btn" id="take-photo">
                        <div class="capture-btn-inner"></div>
                    </div>
                </div>

                <div id="post-capture-controls" class="hidden flex items-center gap-4 w-full">
                    <button id="retake-photo" class="flex-1 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold text-sm transition-colors hover:bg-gray-200">
                        Ulangi Foto
                    </button>
                    <button id="submit-arrival" class="flex-1 py-3 bg-indigo-600 text-white rounded-xl font-bold text-sm shadow-lg shadow-indigo-200 transition-transform active:scale-95">
                        Kirim Laporan
                    </button>
                </div>
            </div>
        </div>

        <!-- Success Animation (Hidden) -->
        <div id="success-screen" class="hidden py-20 text-center">
            <div class="w-24 h-24 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900">Laporan Terkirim!</h2>
            <p class="text-sm text-gray-500 mt-2">Data kedatangan Anda telah tercatat di sistem.</p>
        </div>
    </div>
</div>

<form id="arrival-form" style="display:none;">
    @csrf
    <input type="hidden" name="lokasi" id="form-lokasi">
</form>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize Map
    let map = null;
    let marker = null;
    let currentPosition = null;

    function initMap(lat, lng) {
        if (!map) {
            map = L.map('map').setView([lat, lng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);
        }
        
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng]).addTo(map);
        }
        
        map.setView([lat, lng], 15);
        document.getElementById('coordinates').textContent = `Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}`;
        document.getElementById('gps-status').textContent = '✓ Lokasi ditemukan';
        document.getElementById('gps-status').classList.remove('text-gray-400');
        document.getElementById('gps-status').classList.add('text-green-600');
    }

    // Get GPS Location
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(
            (position) => {
                currentPosition = position;
                initMap(position.coords.latitude, position.coords.longitude);
            },
            (error) => {
                console.error('GPS Error:', error);
                document.getElementById('gps-status').textContent = '✗ Gagal mendapatkan lokasi';
                document.getElementById('gps-status').classList.add('text-red-600');
                // Default to Jakarta if GPS fails
                initMap(-6.2088, 106.8456);
            },
            { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
        );
    } else {
        document.getElementById('gps-status').textContent = '✗ GPS tidak tersedia';
        initMap(-6.2088, 106.8456);
    }

    const video = document.getElementById('camera-feed');
    const canvas = document.getElementById('photo-canvas');
    const capturedImg = document.getElementById('captured-image');
    const takePhotoBtn = document.getElementById('take-photo');
    const retakeBtn = document.getElementById('retake-photo');
    const switchBtn = document.getElementById('switch-camera');
    const submitBtn = document.getElementById('submit-arrival');
    const cameraOverlay = document.getElementById('camera-overlay');
    
    const captureControls = document.getElementById('capture-controls');
    const postCaptureControls = document.getElementById('post-capture-controls');
    
    let stream = null;
    let facingMode = 'environment';
    let blob = null;

    // Start Camera
    async function startCamera() {
        try {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: facingMode },
                audio: false
            });
            video.srcObject = stream;
        } catch (err) {
            console.error("Camera error:", err);
            alert("Tidak dapat mengakses kamera. Pastikan memberikan izin.");
        }
    }

    startCamera();

    // Switch Camera
    switchBtn.onclick = () => {
        facingMode = facingMode === 'user' ? 'environment' : 'user';
        startCamera();
    };

    // Take Photo
    takePhotoBtn.onclick = () => {
        const context = canvas.getContext('2d');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        context.drawImage(video, 0, 0, canvas.width, canvas.height);
        
        canvas.toBlob((b) => {
            blob = b;
            capturedImg.src = URL.createObjectURL(blob);
            showCaptured();
        }, 'image/jpeg', 0.8);
    };

    function showCaptured() {
        video.style.display = 'none';
        capturedImg.style.display = 'block';
        cameraOverlay.style.display = 'none';
        captureControls.classList.add('hidden');
        postCaptureControls.classList.remove('hidden');
        if (stream) stream.getTracks().forEach(track => track.stop());
    }

    retakeBtn.onclick = () => {
        video.style.display = 'block';
        capturedImg.style.display = 'none';
        cameraOverlay.style.display = 'flex';
        captureControls.classList.remove('hidden');
        postCaptureControls.classList.add('hidden');
        startCamera();
    };

    // Submit Logic
    submitBtn.onclick = async () => {
        if (!blob) return;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="animate-pulse">Mengirim...</span>';

        const formData = new FormData();
        formData.append('foto', blob, 'arrival_proof.jpg');
        formData.append('_token', '{{ csrf_token() }}');
        
        // Try getting geolocation
        if ("geolocation" in navigator) {
            const pos = await new Promise((resolve) => {
                navigator.geolocation.getCurrentPosition(resolve, () => resolve(null), {timeout: 5000});
            });
            if (pos) {
                formData.append('lokasi', `${pos.coords.latitude},${pos.coords.longitude}`);
            }
        }

        try {
            const response = await fetch('{{ route("shipping.delivery.report-arrival", $delivery->id) }}', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            if (result.success) {
                document.getElementById('camera-section').classList.add('hidden');
                document.getElementById('success-screen').classList.remove('hidden');
                setTimeout(() => window.location.href = result.redirect, 2000);
            } else {
                alert(result.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Kirim Laporan';
            }
        } catch (err) {
            console.error(err);
            alert("Terjadi kesalahan jaringan.");
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Kirim Laporan';
        }
    };
</script>
@endpush
