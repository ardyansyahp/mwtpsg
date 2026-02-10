@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Pending Surat Jalan (Siap Kirim) --}}
    <div class="mb-8 p-6 bg-gradient-to-br from-indigo-50 to-blue-50 rounded-2xl border border-indigo-100 shadow-sm">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-2 bg-indigo-600 rounded-lg text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900">Surat Jalan Siap Kirim</h3>
                <p class="text-xs text-gray-500">Daftar item yang sudah scan-out dan siap masuk truck</p>
            </div>
        </div>

        @if(count($pendingSjs) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($pendingSjs as $sj)
                    <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex flex-col justify-between hover:border-indigo-300 transition-colors">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <span class="bg-indigo-100 text-indigo-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase">Ready</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $sj->updated_at->diffForHumans() }}</span>
                            </div>
                            <h4 class="font-bold text-gray-900 text-sm mb-1">{{ $sj->no_surat_jalan }}</h4>
                            <p class="text-xs text-gray-500 mb-2 truncate" title="{{ $sj->customer->nama_perusahaan ?? '-' }}">
                                {{ $sj->customer->nama_perusahaan ?? '-' }}
                            </p>
                            <div class="flex items-center gap-1 text-[10px] text-indigo-600 font-medium mb-4">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $sj->plantgate->nama_plantgate ?? '-' }}
                            </div>
                        </div>
                        <a href="{{ route('shipping.delivery.scan', $sj->id) }}" class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-colors flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            Scan Truck
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-6 bg-white/50 rounded-xl border border-dashed border-gray-300">
                <p class="text-sm text-gray-500 italic">Tidak ada Surat Jalan yang menunggu pengiriman.</p>
            </div>
        @endif
    </div>

    {{-- History Header --}}
    <div class="flex items-center justify-between mb-6 mobile-stack-header">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">History Delivery</h2>
            <p class="text-gray-600 mt-1">Status pengiriman barang di truck</p>
        </div>
        @if(userCan('shipping.delivery.create'))
        <a 
            href="{{ route('shipping.delivery.create') }}" 
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors flex items-center gap-2"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Delivery Manual</span>
        </a>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full mobile-hide-table">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            No
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Truck
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Driver
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Surat Jalan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Destination
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Berangkat
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Status
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                    @forelse($deliveries as $index => $delivery)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="No">
                                {{ $deliveries->firstItem() + $index }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-mono" data-label="Truck">
                                {{ $delivery->kendaraan ? $delivery->kendaraan->nopol_kendaraan : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Driver">
                                {{ $delivery->driver ? $delivery->driver->nama : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" data-label="Surat Jalan">
                                {{ $delivery->no_surat_jalan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" data-label="Destination">
                                {{ $delivery->destination ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" data-label="Tanggal Berangkat">
                                {{ $delivery->waktu_berangkat ? $delivery->waktu_berangkat->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap" data-label="Status">
                                @php
                                    $statusColors = [
                                        'OPEN' => 'bg-yellow-100 text-yellow-800',
                                        'IN_TRANSIT' => 'bg-blue-100 text-blue-800',
                                        'ARRIVED' => 'bg-green-100 text-green-800',
                                        'DELIVERED' => 'bg-green-100 text-green-800',
                                        'PENDING' => 'bg-gray-100 text-gray-800',
                                        'DELAY' => 'bg-red-100 text-red-800',
                                        'CANCELLED' => 'bg-red-100 text-red-800',
                                    ];
                                    $colorClass = $statusColors[$delivery->status ?? 'OPEN'] ?? 'bg-gray-100 text-gray-800';
                                @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $colorClass }}">
                                    {{ str_replace('_', ' ', strtoupper($delivery->status ?? 'OPEN')) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center" data-label="Aksi">
                                <div class="flex items-center justify-center gap-2">
                                    @php
                                        $activeStatuses = ['IN_TRANSIT', 'ADVANCED', 'NORMAL', 'PENDING', 'DELAY'];
                                        $isArrivedOrDelivered = in_array($delivery->status, ['ARRIVED', 'DELIVERED']);
                                    @endphp
                                    
                                    
                                    @if(in_array($delivery->status, $activeStatuses) && userCan('shipping.delivery.create'))
                                        <a 
                                            href="{{ route('shipping.delivery.arrive', $delivery->id) }}" 
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1 rounded-lg text-xs font-bold transition-colors flex items-center gap-1" 
                                            title="Lapor Kedatangan (Tiba)"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            <span>Tiba</span>
                                        </a>

                                        @if(userCan('shipping.tracker.view') || userCan('shipping.delivery.create'))
                                        <a 
                                            href="{{ route('shipping.tracker.track', $delivery->id) }}" 
                                            class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded-lg text-xs font-bold transition-colors flex items-center gap-1" 
                                            title="Aktifkan Tracking"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </a>
                                        @endif

                                        {{-- NEW: Lapor Kendala Button --}}
                                        <button 
                                            onclick="openIncidentModal({{ $delivery->id }}, '{{ $delivery->no_surat_jalan }}', '{{ $delivery->kendaraan->nopol_kendaraan ?? '' }}')"
                                            class="bg-orange-600 hover:bg-orange-700 text-white px-3 py-1 rounded-lg text-xs font-bold transition-colors flex items-center gap-1" 
                                            title="Lapor Kendala (Macet/Rusak/Dll)"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                            <span>Kendala</span>
                                        </button>
                                    @endif

                                    @if(in_array($delivery->status, ['ARRIVED', 'DELIVERED']) && userCan('shipping.delivery.create'))
                                        <button 
                                            onclick="finishTrip({{ $delivery->id }})" 
                                            class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded-lg text-xs font-bold transition-colors flex items-center gap-1" 
                                            title="Selesai (Kembali ke PT)"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            <span>Selesai</span>
                                        </button>
                                    @endif

                                    @if(userCan('shipping.delivery.edit'))
                                    <a 
                                        href="{{ route('shipping.delivery.edit', $delivery->id) }}" 
                                        class="text-blue-600 hover:text-blue-900 transition-colors" 
                                        title="Edit"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    @endif
                                    @if(userCan('shipping.delivery.delete'))
                                    <a 
                                        href="{{ route('shipping.delivery.delete', $delivery->id) }}" 
                                        class="text-red-600 hover:text-red-900 transition-colors" 
                                        title="Hapus"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr id="emptyState">
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
                                <p class="mt-1 text-sm text-gray-500">Mulai dengan menambahkan delivery baru.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $deliveries->links() }}
        </div>
    </div>

    {{-- Incident Report Modal --}}
    <div id="incidentModal" class="hidden fixed inset-0 z-[60] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4 text-center sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-50 blur-sm transition-opacity" aria-hidden="true" onclick="closeIncidentModal()"></div>
            
            <div class="relative inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:max-w-lg sm:w-full">
                <form id="incidentForm" action="{{ route('shipping.delivery.incident.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="delivery_header_id" id="modal_delivery_id">
                    <input type="hidden" name="latitude" id="modal_lat">
                    <input type="hidden" name="longitude" id="modal_lng">
                    
                    <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-orange-100 sm:mx-0 sm:h-10 sm:w-10">
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-xl leading-6 font-bold text-gray-900" id="modal-title">
                                    Lapor Kendala Pengiriman
                                </h3>
                                <div class="mt-2 text-sm text-gray-500" id="modal_subtitle">
                                    Surat Jalan: - | Plat: -
                                </div>
                                
                                <div class="mt-6 space-y-4">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Pilih Kendala / Alasan:</label>
                                        <select name="keterangan" id="incident_reason" required class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5">
                                            <option value="">-- Pilih Kendala --</option>
                                            <option value="MACET TOTAL">Macet Total / Stuck</option>
                                            <option value="BAN PECAH">Ban Pecah / Masalah Ban</option>
                                            <option value="MOGOK">Mesin Mogok / Trouble</option>
                                            <option value="KECELAKAAN">Kecelakaan Lalu Lintas</option>
                                            <option value="DICEGAT / ADA MASALAH DI JALAN">Ada Masalah / Dicegat</option>
                                            <option value="LAINNYA">Lainnya (Ketik di bawah)</option>
                                        </select>
                                    </div>
                                    
                                    <div id="other_reason_container" class="hidden">
                                        <label class="block text-sm font-semibold text-gray-700 mb-1">Detail Kendala:</label>
                                        <textarea id="custom_reason" name="custom_keterangan" rows="3" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-xl focus:ring-orange-500 focus:border-orange-500 block p-2.5" placeholder="Jelaskan detail kendala..."></textarea>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Bukti (Keadaan di Lokasi):</label>
                                        <div class="flex flex-col items-center gap-3 p-4 border-2 border-dashed border-gray-300 rounded-2xl bg-gray-50 hover:bg-gray-100 transition-colors relative min-h-[150px]">
                                            
                                            <!-- Placeholder (Initial State) -->
                                            <div id="photo_placeholder" class="flex flex-col items-center py-4 cursor-pointer" onclick="initiateCamera()">
                                                <svg class="w-12 h-12 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span class="text-xs font-bold text-orange-600 uppercase tracking-wider">Klik Untuk Buka Kamera</span>
                                                <p class="text-[10px] text-gray-400 mt-1">Gunakan foto asli keadaan di jalan</p>
                                            </div>

                                            <!-- Live Camera View -->
                                            <div id="camera_active_view" class="hidden w-full flex flex-col items-center gap-2">
                                                <video id="video_feed" autoplay playsinline class="w-full h-64 object-cover rounded-xl shadow-md border border-gray-900 bg-black"></video>
                                                <button type="button" onclick="takeSnapshot()" class="w-full py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold shadow-lg flex items-center justify-center gap-2">
                                                    <div class="w-3 h-3 bg-white rounded-full animate-pulse"></div>
                                                    AMBIL FOTO
                                                </button>
                                            </div>

                                            <!-- Captured Preview -->
                                            <div id="photo_preview_container" class="hidden w-full relative">
                                                <img id="photo_preview" class="w-full h-64 object-cover rounded-xl shadow-md border border-gray-200">
                                                <button type="button" onclick="initiateCamera()" class="absolute bottom-4 left-1/2 -translate-x-1/2 bg-gray-900/80 text-white px-4 py-2 rounded-full text-[10px] font-bold backdrop-blur-sm flex items-center gap-1 hover:bg-gray-900">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                    FOTO ULANG
                                                </button>
                                            </div>

                                            <!-- Hidden Inputs -->
                                            <canvas id="photo_canvas" class="hidden"></canvas>
                                            <input type="file" name="foto" id="incident_photo" accept="image/*" capture="environment" class="hidden">
                                        </div>
                                    </div>

                                    <div id="location_status" class="text-[10px] text-gray-400 italic">
                                        Detecting location...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:flex sm:flex-row-reverse gap-3">
                        <button type="submit" id="submitIncidentBtn" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2 bg-orange-600 text-base font-bold text-white hover:bg-orange-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                            Kirim & Buka WhatsApp
                        </button>
                        <button type="button" onclick="closeIncidentModal()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-6 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function finishTrip(deliveryId) {
    if (!confirm('Apakah Anda yakin sudah kembali ke PT? Trip ini akan diselesaikan.')) {
        return;
    }

    fetch(`{{ url('shipping/delivery') }}/${deliveryId}/finish`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                window.location.reload();
            }
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan jaringan. Silakan coba lagi.');
    });
}

function openIncidentModal(deliveryId, sj, plat) {
    document.getElementById('modal_delivery_id').value = deliveryId;
    document.getElementById('modal_subtitle').innerText = `Surat Jalan: ${sj} | Plat: ${plat}`;
    document.getElementById('incidentModal').classList.remove('hidden');
    
    // Get geolocation
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                document.getElementById('modal_lat').value = pos.coords.latitude;
                document.getElementById('modal_lng').value = pos.coords.longitude;
                document.getElementById('location_status').innerText = '📍 Lokasi terdeteksi';
                document.getElementById('location_status').classList.add('text-green-600');
            },
            (err) => {
                document.getElementById('location_status').innerText = '⚠️ Gagal mendeteksi lokasi';
                document.getElementById('location_status').classList.add('text-red-600');
            }
        );
    } else {
        document.getElementById('location_status').innerText = '⚠️ Browser tidak mendukung lokasi';
    }
}

let cameraStream = null;

async function initiateCamera() {
    const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
    
    // Reset view
    document.getElementById('photo_placeholder').classList.add('hidden');
    document.getElementById('photo_preview_container').classList.add('hidden');
    document.getElementById('camera_active_view').classList.remove('hidden');

    try {
        const constraints = {
            video: {
                facingMode: isMobile ? "environment" : "user",
                width: { ideal: 1280 },
                height: { ideal: 720 }
            },
            audio: false
        };

        stopCameraStream(); // Stop existing if any
        cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('video_feed');
        video.srcObject = cameraStream;
    } catch (err) {
        console.error("Error accessing camera:", err);
        alert("Gagal mengakses kamera. Pastikan izin kamera diizinkan atau gunakan upload file.");
        closeIncidentModal();
        document.getElementById('incident_photo').click(); // Fallback to file picker
    }
}

function stopCameraStream() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

function takeSnapshot() {
    const video = document.getElementById('video_feed');
    const canvas = document.getElementById('photo_canvas');
    const preview = document.getElementById('photo_preview');
    
    // Set canvas dimensions to match video stream
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw directly from video
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    // Stop stream immediately to save battery/resource
    stopCameraStream();
    
    // Show preview
    preview.src = canvas.toDataURL('image/jpeg', 0.8);
    document.getElementById('camera_active_view').classList.add('hidden');
    document.getElementById('photo_preview_container').classList.remove('hidden');
}

function closeIncidentModal() {
    stopCameraStream();
    document.getElementById('incidentModal').classList.add('hidden');
    document.getElementById('incidentForm').reset();
    document.getElementById('other_reason_container').classList.add('hidden');
    
    // Reset views
    document.getElementById('photo_placeholder').classList.remove('hidden');
    document.getElementById('camera_active_view').classList.add('hidden');
    document.getElementById('photo_preview_container').classList.add('hidden');
}

// Handle "Other" reason display
document.getElementById('incident_reason').addEventListener('change', function() {
    const container = document.getElementById('other_reason_container');
    if (this.value === 'LAINNYA') {
        container.classList.remove('hidden');
        document.getElementById('custom_reason').setAttribute('required', 'required');
    } else {
        container.classList.add('hidden');
        document.getElementById('custom_reason').removeAttribute('required');
    }
});

// Intercept form submission to handle WhatsApp redirect if needed AFTER success
// But easiest is to let backend return the WA link in the response and window.location hack
document.getElementById('incidentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitIncidentBtn');
    const originalText = btn.innerText;
    btn.innerText = 'Mengirim...';
    btn.disabled = true;

    try {
        const formData = new FormData(this);
        
        // If we have a captured photo on canvas, convert to blob
        const preview = document.getElementById('photo_preview');
        if (preview.src && preview.src.startsWith('data:image')) {
            const blob = await (await fetch(preview.src)).blob();
            formData.set('foto', blob, 'incident_photo.jpg');
        }

        const response = await fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (data.success) {
            // Open WA link if provided
            if (data.wa_link) {
                window.open(data.wa_link, '_blank');
            }
            alert('Laporan kendala berhasil dikirim.');
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    } catch (err) {
        console.error(err);
        alert('Gagal mengirim laporan.');
    } finally {
        btn.innerText = originalText;
        btn.disabled = false;
    }
});
</script>
@endpush

