@extends('layout.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 to-blue-50 p-6">
    {{-- Header Section --}}
    <div class="max-w-7xl mx-auto mb-8">
        <div class="bg-white rounded-2xl shadow-xl p-8 border border-blue-100">
            <div class="flex items-center gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-4 rounded-xl shadow-lg">
                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Part Tracer</h1>
                    <p class="text-gray-600">Lacak perjalanan part dari Receiving hingga Finish Good</p>
                </div>
            </div>

            {{-- Search Form --}}
            <div class="mt-6">
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor LOT / Barcode</label>
                        <input 
                            type="text" 
                            id="lotNumber" 
                            placeholder="Masukkan nomor LOT atau scan barcode..."
                            onkeypress="if(event.key==='Enter'){event.preventDefault();window.performTrace();}"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all outline-none text-lg"
                        >
                    </div>
                    <div class="flex items-end">
                        <button 
                            type="button"
                            onclick="window.performTrace()"
                            class="px-8 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-indigo-700 transition-all shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                        >
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <span>Trace</span>
                            </div>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading State --}}
    <div id="loadingState" class="hidden max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
            <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-200 border-t-blue-600 mb-4"></div>
            <p class="text-gray-600 font-medium">Melacak perjalanan part...</p>
        </div>
    </div>

    {{-- Results Section --}}
    <div id="resultsSection" class="hidden max-w-7xl mx-auto">
        
        {{-- Part Information Card --}}
        <div id="partInfoCard" class="bg-white rounded-2xl shadow-xl p-8 mb-6 border border-blue-100">
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-4 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-semibold">LOT INFO</span>
                        <span id="lotStatus" class="px-4 py-1 rounded-full text-sm font-semibold"></span>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800" id="partName"></h2>
                    <p class="text-gray-600" id="partNumber"></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total Journey Time</p>
                    <p class="text-3xl font-bold text-blue-600" id="totalTime">-</p>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4" id="partDetails">
                <!-- Will be filled dynamically -->
            </div>
        </div>

        {{-- Timeline Visualization --}}
        <div class="bg-white rounded-2xl shadow-xl p-8 mb-6 border border-blue-100">
            <h3 class="text-xl font-bold text-gray-800 mb-6 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Timeline Perjalanan Part
            </h3>
            <div id="timeline" class="relative">
                <!-- Timeline will be generated here -->
            </div>
        </div>

        {{-- Detailed Information Sections --}}
        <div class="space-y-6" id="detailSections">
            <!-- Detail cards will be generated here -->
        </div>
    </div>

    {{-- No Results State --}}
    <div id="noResultsState" class="hidden max-w-7xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-red-100 rounded-full mb-4">
                <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Data Tidak Ditemukan</h3>
            <p class="text-gray-600">Nomor LOT yang Anda cari tidak ditemukan dalam sistem.</p>
        </div>
    </div>
</div>

<style>
/* Timeline Styles */
.timeline-item {
    position: relative;
    padding-left: 60px;
    padding-bottom: 40px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: -10px;
    width: 2px;
    background: linear-gradient(to bottom, #3b82f6, #e5e7eb);
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-icon {
    position: absolute;
    left: 0;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    z-index: 10;
}

.detail-card {
    transition: all 0.3s ease;
}

.detail-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in {
    animation: slideIn 0.6s ease-out;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<script>
console.log('=== TRACER SCRIPT LOADED ===');

// Make performTrace global so it can be called from onclick
window.performTrace = function() {
    console.log('=== PERFORM TRACE CALLED ===');
    const lotInput = document.getElementById('lotNumber');
    const loadingState = document.getElementById('loadingState');
    const resultsSection = document.getElementById('resultsSection');
    const noResultsState = document.getElementById('noResultsState');
    
    const lotNumber = lotInput ? lotInput.value.trim() : '';
    
    console.log('Lot Number:', lotNumber);
    
    if (!lotNumber) {
        alert('Mohon masukkan nomor LOT');
        return;
    }

    console.log('=== STARTING TRACE ===');

    // Show loading
    if (loadingState) loadingState.classList.remove('hidden');
    if (resultsSection) resultsSection.classList.add('hidden');
    if (noResultsState) noResultsState.classList.add('hidden');

    // Fetch data
    const url = `{{ url('tracer/trace') }}/${encodeURIComponent(lotNumber)}`;
    console.log('Fetching from:', url);

    fetch(url)
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            if (loadingState) loadingState.classList.add('hidden');
            
            if (data.success && data.data) {
                displayResults(data.data);
                if (resultsSection) resultsSection.classList.remove('hidden');
            } else {
                if (noResultsState) noResultsState.classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            if (loadingState) loadingState.classList.add('hidden');
            if (noResultsState) noResultsState.classList.remove('hidden');
            alert('Terjadi kesalahan: ' + error.message);
        });
};

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CONTENT LOADED ===');
    const lotInput = document.getElementById('lotNumber');
    if (lotInput) {
        setTimeout(() => {
            lotInput.focus();
            console.log('Input focused');
        }, 100);
    }
});

function displayResults(traceData) {
    console.log('=== DISPLAYING RESULTS ===');
        // Update Part Info
        document.getElementById('partName').textContent = traceData.part?.nama_part || '-';
        document.getElementById('partNumber').textContent = traceData.part?.nomor_part || '-';
        
        // Set status badge
        const statusBadge = document.getElementById('lotStatus');
        statusBadge.textContent = traceData.current_status || 'Unknown';
        statusBadge.className = `px-4 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-700`;

        // Calculate total time
        if (traceData.timeline && traceData.timeline.length > 0) {
            const firstEvent = traceData.timeline[traceData.timeline.length - 1];
            const lastEvent = traceData.timeline[0];
            const totalHours = calculateTimeDiff(firstEvent.timestamp, lastEvent.timestamp);
            document.getElementById('totalTime').textContent = formatDuration(totalHours);
        }

        // Render part details
        renderPartDetails(traceData);

        // Render timeline
        renderTimeline(traceData.timeline || []);

        // Render detail sections
        renderDetailSections(traceData);
    }

    function renderPartDetails(data) {
        const detailsContainer = document.getElementById('partDetails');
        const details = [
            { label: 'Tipe', value: data.part?.tipe?.nama_part || '-' },
            { label: 'Perusahaan', value: data.part?.perusahaan?.nama_perusahaan || '-' },
            { label: 'Quantity', value: data.quantity || '-' },
            { label: 'Batch', value: data.batch_number || '-' }
        ];

        detailsContainer.innerHTML = details.map(item => `
            <div class="p-4 bg-gray-50 rounded-xl">
                <p class="text-xs text-gray-500 font-medium">${item.label}</p>
                <p class="text-sm font-semibold text-gray-800 truncate">${item.value}</p>
            </div>
        `).join('');
    }

    function renderTimeline(timeline) {
        const timelineContainer = document.getElementById('timeline');
        
        if (!timeline || timeline.length === 0) {
            timelineContainer.innerHTML = '<p class="text-gray-500 text-center py-8">Tidak ada data timeline</p>';
            return;
        }

        timelineContainer.innerHTML = timeline.map((item, index) => {
            const config = getStageConfig(item.stage);
            
            return `
                <div class="timeline-item slide-in">
                    <div class="timeline-icon" style="background: ${config.gradient}">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${config.icon}"/>
                        </svg>
                    </div>
                    <div class="bg-gradient-to-br ${config.bgGradient} border-2 ${config.border} rounded-xl p-5 shadow-md">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h4 class="text-lg font-bold ${config.textColor}">${item.stage_name}</h4>
                                <p class="text-sm text-gray-600">${item.action}</p>
                            </div>
                            <span class="px-3 py-1 ${config.badge} rounded-full text-xs font-bold">
                                ${item.status || 'Completed'}
                            </span>
                        </div>
                        <div class="text-sm text-gray-700">
                            <strong>Waktu:</strong> ${formatDateTime(item.timestamp)}
                            ${item.operator ? `<br><strong>Operator:</strong> ${item.operator}` : ''}
                            ${item.duration ? `<br><strong>Durasi:</strong> ${formatDuration(item.duration)}` : ''}
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function renderDetailSections(traceData) {
        const container = document.getElementById('detailSections');
        const sections = [];

        if (traceData.finishgood) {
            sections.push(createDetailCard('Finish Good', traceData.finishgood, 'from-green-500 to-emerald-600'));
        }
        if (traceData.assy) {
            sections.push(createDetailCard('Assembly', traceData.assy, 'from-purple-500 to-pink-600'));
        }
        if (traceData.wip) {
            sections.push(createDetailCard('Work In Progress', traceData.wip, 'from-yellow-500 to-orange-600'));
        }
        if (traceData.inject) {
            sections.push(createDetailCard('Injection', traceData.inject, 'from-blue-500 to-indigo-600'));
        }
        if (traceData.receiving) {
            sections.push(createDetailCard('Receiving & Supply', traceData.receiving, 'from-teal-500 to-cyan-600'));
        }

        container.innerHTML = sections.join('');
    }

    function createDetailCard(title, data, gradient) {
        const fields = Object.entries(data).filter(([key]) => key !== 'materials' && key !== 'subparts' && key !== 'receivings');
        
        let materialsHTML = '';
        let subpartsHTML = '';
        let receivingsHTML = '';
        
        // Handle materials (untuk inject)
        if (data.materials && Array.isArray(data.materials) && data.materials.length > 0) {
            materialsHTML = `
                <div class="col-span-full mt-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Bahan Baku yang Digunakan (${data.materials.length} jenis)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        ${data.materials.map(mat => `
                            <div class="p-3 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg">
                                <div class="flex items-start justify-between mb-2">
                                    <span class="px-2 py-1 bg-blue-600 text-white text-xs font-bold rounded">${mat.kategori}</span>
                                    <span class="text-xs text-blue-600 font-semibold">${mat.qty}</span>
                                </div>
                                <p class="text-xs text-gray-500 font-medium">${mat.nomor}</p>
                                <p class="text-sm font-bold text-gray-800">${mat.nama}</p>
                                <p class="text-xs text-gray-600 mt-1">LOT: ${mat.lot}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }
        
        // Handle subparts (untuk assy)
        if (data.subparts && Array.isArray(data.subparts) && data.subparts.length > 0) {
            subpartsHTML = `
                <div class="col-span-full mt-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Subpart yang Digunakan untuk Assembly (${data.subparts.length} jenis)
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        ${data.subparts.map((sub, idx) => {
                            let supplierInfo = '';
                            if (data.subpart_suppliers && data.subpart_suppliers[idx]) {
                                supplierInfo = `<p class="text-xs text-purple-600 font-semibold mt-1">📦 ${data.subpart_suppliers[idx].inisial}</p>`;
                            }
                            return `
                                <div class="p-3 bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-lg">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="text-xs text-purple-600 font-semibold">${sub.qty}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 font-medium">${sub.nomor}</p>
                                    <p class="text-sm font-bold text-gray-800">${sub.nama}</p>
                                    <p class="text-xs text-gray-600 mt-1">LOT: ${sub.lot}</p>
                                    ${supplierInfo}
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }
        
        // Handle inject info (LOT inject yang masuk ke assy)
        if (data.inject_info) {
            let injectInfoHTML = `
                <div class="col-span-full mt-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Part dari Inject yang Digunakan untuk Assembly
                    </h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                        ${Object.entries(data.inject_info).map(([key, value]) => `
                            <div class="p-3 bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-lg">
                                <p class="text-xs text-gray-500 font-medium uppercase">${formatLabel(key)}</p>
                                <p class="text-sm font-bold text-gray-800">${value}</p>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
            subpartsHTML += injectInfoHTML;
        }
        
        // Handle receivings (multiple receiving dari supplier berbeda)
        if (data.receivings && Array.isArray(data.receivings) && data.receivings.length > 0) {
            receivingsHTML = `
                <div class="col-span-full mt-4">
                    <h4 class="text-sm font-bold text-gray-700 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                        </svg>
                        Receiving dari Supplier (${data.receivings.length} receiving)
                    </h4>
                    ${data.receivings.map(rcv => `
                        <div class="mb-3 p-4 bg-gradient-to-br from-teal-50 to-cyan-50 border-2 border-teal-200 rounded-lg">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <span class="px-3 py-1 bg-teal-600 text-white text-xs font-bold rounded-full">${rcv.inisial}</span>
                                    <span class="ml-2 text-sm font-bold text-gray-800">${rcv.supplier}</span>
                                </div>
                                <span class="text-xs text-gray-600">${rcv.tanggal}</span>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                                ${rcv.items.map(item => `
                                    <div class="p-2 bg-white rounded border border-teal-200">
                                        <p class="text-xs font-bold text-gray-800">${item.bahan_baku}</p>
                                        <p class="text-xs text-gray-600">Qty: ${item.qty}</p>
                                        <p class="text-xs text-gray-500">LOT: ${item.lot}</p>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }
        
        return `
            <div class="detail-card bg-white rounded-2xl shadow-xl p-8 border border-gray-100 fade-in">
                <h3 class="text-xl font-bold text-gray-800 mb-4 bg-gradient-to-r ${gradient} text-transparent bg-clip-text">${title}</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${fields.map(([key, value]) => {
                        // Skip array fields
                        if (Array.isArray(value)) return '';
                        return `
                            <div class="p-4 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl">
                                <p class="text-xs text-gray-500 font-medium uppercase mb-1">${formatLabel(key)}</p>
                                <p class="text-sm font-bold text-gray-800">${value || '-'}</p>
                            </div>
                        `;
                    }).join('')}
                    ${materialsHTML}
                    ${subpartsHTML}
                    ${receivingsHTML}
                </div>
            </div>
        `;
    }

    function getStageConfig(stage) {
        const configs = {
            'receiving': { gradient: 'linear-gradient(135deg, #14b8a6 0%, #0891b2 100%)', bgGradient: 'from-teal-50 to-cyan-50', border: 'border-teal-200', textColor: 'text-teal-700', badge: 'bg-teal-100 text-teal-700', icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4' },
            'supply': { gradient: 'linear-gradient(135deg, #10b981 0%, #059669 100%)', bgGradient: 'from-emerald-50 to-green-50', border: 'border-emerald-200', textColor: 'text-emerald-700', badge: 'bg-emerald-100 text-emerald-700', icon: 'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4' },
            'inject': { gradient: 'linear-gradient(135deg, #3b82f6 0%, #6366f1 100%)', bgGradient: 'from-blue-50 to-indigo-50', border: 'border-blue-200', textColor: 'text-blue-700', badge: 'bg-blue-100 text-blue-700', icon: 'M13 10V3L4 14h7v7l9-11h-7z' },
            'wip': { gradient: 'linear-gradient(135deg, #f59e0b 0%, #f97316 100%)', bgGradient: 'from-yellow-50 to-orange-50', border: 'border-yellow-200', textColor: 'text-orange-700', badge: 'bg-yellow-100 text-yellow-700', icon: 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z' },
            'assy': { gradient: 'linear-gradient(135deg, #a855f7 0%, #ec4899 100%)', bgGradient: 'from-purple-50 to-pink-50', border: 'border-purple-200', textColor: 'text-purple-700', badge: 'bg-purple-100 text-purple-700', icon: 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10' },
            'finishgood': { gradient: 'linear-gradient(135deg, #22c55e 0%, #10b981 100%)', bgGradient: 'from-green-50 to-emerald-50', border: 'border-green-200', textColor: 'text-green-700', badge: 'bg-green-100 text-green-700', icon: 'M5 13l4 4L19 7' }
        };
        return configs[stage] || configs['receiving'];
    }

    function formatDateTime(timestamp) {
        if (!timestamp) return '-';
        const date = new Date(timestamp);
        return date.toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    }

    function formatDuration(hours) {
        if (!hours || hours <= 0) return '-';
        if (hours < 1) return `${Math.round(hours * 60)} menit`;
        if (hours < 24) return `${Math.round(hours)} jam`;
        const days = Math.floor(hours / 24);
        const remainingHours = Math.round(hours % 24);
        return `${days} hari ${remainingHours} jam`;
    }

    function calculateTimeDiff(start, end) {
        if (!start || !end) return 0;
        const startDate = new Date(start);
        const endDate = new Date(end);
        return Math.abs(endDate - startDate) / (1000 * 60 * 60);
    }

    function formatLabel(key) {
        return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

console.log('=== ALL FUNCTIONS DEFINED ===');
console.log('window.performTrace:', typeof window.performTrace);
</script>

@endsection
