@extends('layout.app')

@section('content')
<div class="fade-in" id="dispatchApp">
    {{-- Header & Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Penugasan Driver</h2>
            <p class="text-gray-600 mt-1">Tentukan Sopir & Truk untuk Surat Jalan yang siap dikirim.</p>
        </div>
        
        <div class="flex flex-col md:flex-row gap-2 items-start md:items-center">
            {{-- Search & Filter Form --}}
            <form action="{{ route('shipping.dispatch.index') }}" method="GET" class="flex flex-col md:flex-row gap-2">
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" title="Tanggal Mulai">
                    <span class="text-gray-400 text-xs">s/d</span>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm" title="Tanggal Akhir">
                </div>
                
                <div class="relative">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Cari SJ / SPK / Customer..." 
                        class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 w-48 md:w-64 text-sm"
                    >
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 flex items-center gap-1 text-sm font-medium">
                        Filter
                    </button>

                    @if(request('search') || request('start_date') || request('end_date'))
                        <a href="{{ route('shipping.dispatch.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg transition-colors border border-gray-300" title="Reset Filters">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Action Buttons --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('shipping.dispatch.index', array_merge(request()->all(), ['export' => 1])) }}" class="flex items-center gap-2 bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium text-sm">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export</span>
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-blue-50 text-blue-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total SJ Siap</p>
                <p class="text-xl font-bold text-gray-900">{{ $readySjs->total() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-amber-50 text-amber-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Belum Ditugaskan</p>
                <p class="text-xl font-bold text-gray-900">{{ $readySjs->filter(fn($sj) => !$sj->driver_id)->count() }}</p>
            </div>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm flex items-center gap-4">
            <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Siap Muat</p>
                <p class="text-xl font-bold text-gray-900">{{ $readySjs->filter(fn($sj) => $sj->driver_id)->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Main Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
        <div class="overflow-x-auto">
            <table class="w-full text-left mobile-hide-table">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Info Surat Jalan</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Pelanggan & Pabrik</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Sopir Saat Ini</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Truk Saat Ini</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">Update Terakhir</th>
                        <th class="px-6 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($readySjs as $sj)
                    <tr class="hover:bg-gray-50 transition-colors group">
                        <td class="px-6 py-4" data-label="Info Surat Jalan">
                            <div class="flex flex-col">
                                <span class="text-sm font-black text-indigo-900 group-hover:text-indigo-600 transition-colors">{{ $sj->no_surat_jalan }}</span>
                                <span class="text-[10px] text-gray-400 font-mono mt-0.5">SPK: {{ $sj->nomor_spk }}</span>
                                <div class="mt-2 flex items-center gap-2">
                                     <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 text-[10px] font-bold rounded border border-blue-100" title="Rencana Berangkat">
                                        OUT: {{ $sj->jam_berangkat_plan ? substr($sj->jam_berangkat_plan, 0, 5) : '-' }}
                                     </span>
                                     @if($sj->jam_datang_plan)
                                        <span class="px-1.5 py-0.5 bg-emerald-50 text-emerald-600 text-[10px] font-bold rounded border border-emerald-100" title="Rencana Kembali">
                                            IN: {{ substr($sj->jam_datang_plan, 0, 5) }}
                                        </span>
                                     @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4" data-label="Pelanggan & Pabrik">
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-gray-900">{{ $sj->customer->nama_perusahaan ?? '-' }}</span>
                                <div class="flex items-center gap-1 text-[10px] text-gray-400 font-medium">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    {{ $sj->plantgate->nama_plantgate ?? '-' }}
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4" data-label="Sopir Saat Ini">
                            @if($sj->driver)
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs">
                                        {{ substr($sj->driver->nama, 0, 1) }}
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ $sj->driver->nama }}</span>
                                </div>
                            @else
                                <span class="px-2 py-1 bg-amber-50 text-amber-600 text-[10px] font-bold rounded-lg border border-amber-100 uppercase">Menunggu Penugasan</span>
                            @endif
                        </td>
                        <td class="px-6 py-4" data-label="Truk Saat Ini">
                            @if($sj->nomor_plat)
                                <span class="text-xs font-black text-gray-900 font-mono bg-gray-100 px-2 py-1 rounded-md border border-gray-200">{{ $sj->nomor_plat }}</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-400 font-medium italic" data-label="Update Terakhir">
                            {{ $sj->updated_at->format('d M, H:i') }}
                        </td>
                        <td class="px-6 py-4 text-center" data-label="Aksi">
                            @if(userCan('shipping.dispatch.assign'))
                            <button 
                                @click="openModal({{ json_encode($sj) }})"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-xl transition-all shadow-md active:scale-95"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                Tugaskan
                            </button>
                            @else
                            <span class="text-xs text-gray-400 italic">Tidak Ada Akses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="p-4 bg-gray-50 rounded-full text-gray-300">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                </div>
                                <p class="text-gray-400 font-medium italic text-sm">Tidak ada Surat Jalan yang siap kirim.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & Rows Per Page --}}
        <div class="bg-white px-6 py-4 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-4 text-sm text-gray-600 order-2 md:order-1">
                <div class="flex items-center gap-2">
                    <span>Tampilkan</span>
                    <select 
                        id="per_page_selector" 
                        class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-indigo-500 bg-gray-50 text-xs font-medium cursor-pointer"
                        onchange="changePerPage(this.value)"
                    >
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span>data per halaman</span>
                </div>
                <div class="border-l border-gray-300 h-4 mx-2"></div>
                <div>
                     Menampilkan <span class="font-medium text-gray-900">{{ $readySjs->firstItem() ?? 0 }}</span> - <span class="font-medium text-gray-900">{{ $readySjs->lastItem() ?? 0 }}</span> dari <span class="font-medium text-gray-900">{{ $readySjs->total() }}</span>️ data
                </div>
            </div>
            <div class="order-1 md:order-2">
                {{ $readySjs->appends(request()->all())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    {{-- Dispatch Modal --}}
    <div 
        v-cloak
        v-if="modal.show" 
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
        @click.self="closeModal"
    >
        <div class="bg-white rounded-xl w-full max-w-lg shadow-xl overflow-hidden border border-gray-100 animate-slide-up">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Penugasan Pengiriman</h3>
                    <p class="text-xs text-gray-500 font-medium uppercase tracking-widest mt-1">Portal Penugasan</p>
                </div>
                <button @click="closeModal" class="p-2 hover:bg-gray-200 rounded-xl transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l18 18"/></svg>
                </button>
            </div>

            <div class="p-8">
                <div class="mb-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100 flex items-start gap-4">
                     <div class="p-2 bg-indigo-600 rounded-lg text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                     </div>
                     <div>
                        <p class="text-xs font-bold text-indigo-700 uppercase">Surat Jalan Terpilih</p>
                        <p class="text-lg font-black text-indigo-900 font-mono mt-0.5">@{{ modal.sj.no_surat_jalan }}</p>
                        <p class="text-[10px] text-indigo-400 mt-1">@{{ modal.sj.customer?.nama_perusahaan }} — @{{ modal.sj.plantgate?.nama_plantgate }}</p>
                     </div>
                </div>

                <div class="space-y-5">
                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Pilih Sopir</label>
                        <div class="relative group">
                            <select 
                                v-model="form.driver_id"
                                class="w-full h-12 pl-12 pr-4 bg-white border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:bg-white focus:outline-none transition-all appearance-none"
                            >
                                <option value="" disabled>Cari atau Pilih Sopir</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver->id }}">
                                        {{ $driver->nama }} [{{ $driver->bagian }}]
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-black text-gray-400 uppercase tracking-widest mb-2">Armada (Truk)</label>
                        
                        <!-- Info plat jika sudah ada -->
                        <div v-if="modal.sj.nomor_plat" class="flex items-center justify-between p-4 bg-indigo-50/50 border border-indigo-100 rounded-xl">
                             <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center text-indigo-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-tighter">Plat Nomor (Otomatis)</p>
                                    <p class="text-base font-black text-indigo-900 font-mono tracking-wider">@{{ modal.sj.nomor_plat }}</p>
                                </div>
                             </div>
                             <button @click="modal.sj.nomor_plat = ''; form.nomor_plat = ''" class="px-3 py-1.5 bg-white border border-indigo-200 text-[10px] font-bold text-indigo-600 rounded-lg hover:bg-indigo-50 transition-colors">
                                Ubah
                             </button>
                        </div>

                        <!-- Dropdown jika masih kosong -->
                        <div v-else class="relative group">
                            <select 
                                v-model="form.nomor_plat"
                                class="w-full h-12 pl-12 pr-4 bg-white border border-gray-300 rounded-lg text-sm focus:border-indigo-500 focus:bg-white focus:outline-none transition-all appearance-none"
                            >
                                <option value="" disabled>Pilih Plat Nomor Truk</option>
                                @foreach($trucks as $truck)
                                    <option value="{{ $truck->nopol_kendaraan }}">
                                        {{ $truck->nopol_kendaraan }} {{ $truck->nama_kendaraan ? '[' . $truck->nama_kendaraan . ']' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex gap-4">
                    <button 
                        @click="closeModal"
                        class="flex-1 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-lg transition-all"
                    >
                        Batal
                    </button>
                    <button 
                        @click="submitAssignment"
                        :disabled="submitting || !form.driver_id || !form.nomor_plat"
                        class="flex-[2] py-2 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-lg shadow-sm shadow-indigo-200 transition-all flex items-center justify-center gap-2"
                    >
                        <span v-if="submitting" class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span v-else>Konfirmasi Penugasan</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes slide-up {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-slide-up {
    animation: slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
[v-cloak] { display: none !important; }
</style>


<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    function initDispatchApp() {
        if (!document.getElementById('dispatchApp')) return;
        const { createApp, ref, reactive } = Vue;
        
        createApp({
            setup() {
                const modal = reactive({
                    show: false,
                    sj: {}
                });

                const form = reactive({
                    spk_id: null,
                    driver_id: '',
                    nomor_plat: ''
                });

                const submitting = ref(false);

                const openModal = (sj) => {
                    modal.sj = { ...sj };
                    form.spk_id = sj.id;
                    form.driver_id = sj.driver_id || '';
                    form.nomor_plat = sj.nomor_plat || '';
                    modal.show = true;
                    document.body.style.overflow = 'hidden';
                };

                const closeModal = () => {
                    modal.show = false;
                    document.body.style.overflow = 'auto';
                };

                const submitAssignment = async () => {
                    if(submitting.value) return;
                    submitting.value = true;

                    try {
                        const response = await fetch("{{ route('shipping.dispatch.assign') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(form)
                        });

                        const result = await response.json();

                        if(result.success) {
                            // Toast alert or immediate reload
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'Penugasan berhasil dikonfirmasi.',
                                timer: 2000,
                                showConfirmButton: false,
                                background: '#F8FAFC',
                                customClass: {
                                    title: 'font-bold text-gray-900',
                                    popup: 'rounded-xl border-none shadow-xl'
                                }
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(result.message);
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Penugasan Gagal',
                            text: error.message,
                            background: '#FFF5F5'
                        });
                    } finally {
                        submitting.value = false;
                    }
                };

                return {
                    modal,
                    form,
                    submitting,
                    openModal,
                    closeModal,
                    submitAssignment
                };
            }
        }).mount('#dispatchApp');
    }

    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initDispatchApp);
    } else {
        initDispatchApp();
    }

    function changePerPage(value) {
        const url = new URL(window.location.href);
        url.searchParams.set('per_page', value);
        url.searchParams.set('page', 1); // Reset to page 1
        window.location.href = url.toString();
    }
</script>
@endsection
