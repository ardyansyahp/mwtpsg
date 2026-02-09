@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Map Tracker (Experimental)</h2>
            <p class="text-gray-600 mt-1">Real-time monitoring posisi truck aktif via GPS Driver</p>
        </div>
        <div class="flex gap-2">
            <button onclick="fetchLocations()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Refresh Map</span>
            </button>
        </div>
    </div>

    <!-- Map Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div id="map" class="w-full h-[600px] z-0"></div>
    </div>

    <!-- Active List -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4" id="truckList">
        <!-- Filled by JS -->
    </div>
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
    let map;
    let markers = {};

    function initMap() {
        if (document.getElementById('map') && !map) {
            // Initialize Map (Center Indonesia/Jakarta)
            map = L.map('map').setView([-6.2088, 106.8456], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Fetch Initial Data
            fetchLocations();

            // Auto Refresh every 60s
            setInterval(fetchLocations, 60000);
        }
    }

    document.addEventListener('DOMContentLoaded', initMap);

    async function fetchLocations() {
        try {
            const response = await fetch('{{ route("shipping.tracker.locations") }}');
            const trucks = await response.json();
            
            updateMap(trucks);
            updateList(trucks);
        } catch (error) {
            console.error('Error fetching locations:', error);
        }
    }

    function updateMap(trucks) {
        // Clear old markers if truck not in list (optional, or just update)
        // Simple approach: Update existing, add new
        
        trucks.forEach(truck => {
            if (markers[truck.id]) {
                markers[truck.id].setLatLng([truck.lat, truck.lng]);
                markers[truck.id].getPopup().setContent(getPopupContent(truck));
            } else {
                const marker = L.marker([truck.lat, truck.lng])
                    .addTo(map)
                    .bindPopup(getPopupContent(truck));
                markers[truck.id] = marker;
            }
        });
    }

    function getPopupContent(truck) {
        return `
            <div class="font-sans">
                <h3 class="font-bold text-sm">${truck.nopol}</h3>
                <p class="text-xs text-gray-600">Driver: ${truck.driver}</p>
                 <p class="text-xs text-gray-500 mt-1">Status: ${truck.status}</p>
                <p class="text-[10px] text-gray-400 mt-1">Updated ${truck.last_update}</p>
            </div>
        `;
    }

    function updateList(trucks) {
        const container = document.getElementById('truckList');
        if(trucks.length === 0) {
            container.innerHTML = '<div class="col-span-3 text-center text-gray-400 italic">Tidak ada truck aktif saat ini.</div>';
            return;
        }

        container.innerHTML = trucks.map(truck => `
            <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm flex items-center justify-between">
                <div>
                   <h4 class="font-bold text-gray-800">${truck.nopol}</h4> 
                   <p class="text-sm text-gray-600">${truck.driver}</p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full mb-1">${truck.status}</span>
                    <p class="text-[10px] text-gray-400">${truck.last_update}</p>
                </div>
            </div>
        `).join('');
    }
</script>
@endsection
