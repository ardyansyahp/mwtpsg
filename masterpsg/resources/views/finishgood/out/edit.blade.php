@extends('layout.app')

@section('content')
<div class="fade-in max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <div class="flex items-center gap-3">
                <a href="{{ route('finishgood.out.index') }}" class="p-2 hover:bg-gray-100 rounded-lg transition-colors text-gray-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div>
                     <h2 class="text-3xl font-black text-gray-900 tracking-tight">Edit Finish Good Out</h2>
                     <p class="text-gray-500 font-medium">Manage Surat Jalan & Scanning Data</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-3">
             <span class="px-3 py-1 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold font-mono">
                {{ $spk->nomor_spk }}
             </span>
        </div>
    </div>

    {{-- Alert Messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl flex items-center gap-3 font-bold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-3 font-bold">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        {{-- SPK Info Card --}}
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-sm font-black text-gray-400 uppercase tracking-widest mb-4">SPK Information</h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="text-xs text-gray-500 font-bold uppercase">Customer</label>
                        <p class="text-gray-900 font-bold">{{ $spk->customer->nama_perusahaan ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold uppercase">Plant / Gate</label>
                        <p class="text-gray-900 font-bold">{{ $spk->plantgate->nama_plantgate ?? '-' }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold uppercase">Date</label>
                        <p class="text-gray-900 font-bold">{{ \Carbon\Carbon::parse($spk->tanggal)->format('d M Y') }}</p>
                    </div>
                    <div>
                        <label class="text-xs text-gray-500 font-bold uppercase">Status</label>
                        <div class="mt-1">
                            @if($spk->no_surat_jalan)
                                <span class="px-2 py-1 bg-green-100 text-green-700 rounded-lg text-xs font-bold">CLOSED (Surat Jalan Issued)</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded-lg text-xs font-bold">OPEN (Scanning)</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-indigo-50 p-6 rounded-2xl border border-indigo-100">
                <h3 class="text-sm font-black text-indigo-400 uppercase tracking-widest mb-4">Scanning Summary</h3>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-indigo-700 font-medium">Total Scanned</span>
                    <span class="text-2xl font-black text-indigo-900">{{ number_format($totalScanned) }}</span>
                </div>
                <div class="flex justify-between items-center text-sm mb-4">
                    <span class="text-indigo-600">Boxes</span>
                    <span class="font-bold text-indigo-800">{{ $totalBoxes }} Box</span>
                </div>
                <div class="w-full bg-indigo-200 rounded-full h-2">
                    @php $percent = $totalTarget > 0 ? min(100, ($totalScanned / $totalTarget) * 100) : 0; @endphp
                    <div class="bg-indigo-600 h-2 rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                </div>
                <div class="mt-2 text-right text-xs font-bold text-indigo-500">{{ number_format($percent, 1) }}% Completed</div>
            </div>
        </div>

        {{-- Main Forms --}}
        <div class="md:col-span-2 space-y-6">
            {{-- 1. Edit Surat Jalan Form --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="p-2 bg-blue-100 text-blue-600 rounded-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Update Surat Jalan</h3>
                </div>

                <form action="{{ route('finishgood.out.update', $spk->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nomor Surat Jalan</label>
                        <input type="text" name="no_surat_jalan" value="{{ old('no_surat_jalan', $spk->no_surat_jalan) }}" 
                            class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 font-mono font-bold text-gray-900 transition-all uppercase"
                            placeholder="NO SURAT JALAN" required>
                        <p class="text-xs text-gray-500 mt-2">Perubahan nomor surat jalan akan otomatis terupdate di data Control Truck dan History Scan.</p>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all active:scale-95">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

            {{-- 2. Reset Scanning Zone --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-red-100 relative overflow-hidden">
                <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-bl-full -mr-16 -mt-16 z-0"></div>
                
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="p-2 bg-red-100 text-red-600 rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-red-900">Reset Scanning Data</h3>
                    </div>

                    <div class="mb-6">
                        <p class="text-sm text-gray-600 mb-2">Fitur ini akan melakukan:</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 space-y-1 ml-2">
                             <li>Menghapus <strong class="text-red-600 font-bold">SEMUA ({{ $totalBoxes }})</strong> data scan kardus untuk SPK ini.</li>
                             <li>Mengembalikan status SPK menjadi <strong>OPEN</strong>.</li>
                             <li>Menghapus jadwal truck di Control Truck (jika ada).</li>
                        </ul>
                        <div class="mt-4 p-3 bg-red-50 text-red-700 text-sm font-bold border border-red-200 rounded-lg">
                            ⚠️ Warning: Data yang dihapus tidak dapat dikembalikan!
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button @click="showResetModal = true" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-200 transition-all active:scale-95 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            RESET SEMUA SCAN
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- Reset Confirmation Modal --}}
    <div v-if="showResetModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" @click.self="showResetModal = false">
        <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate-bounce-in">
            <form action="{{ route('finishgood.out.reset', $spk->id) }}" method="POST">
                @csrf
                
                <div class="p-8 text-center">
                    <div class="mx-auto w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77-1.333.192 3 1.732 3z"/></svg>
                    </div>
                    
                    <h3 class="text-2xl font-black text-gray-900 mb-2">Konfirmasi Reset</h3>
                    <p class="text-gray-500 mb-6">Apakah Anda yakin ingin menghapus semua data scanning? Tindakan ini tidak dapat dibatalkan.</p>
                    
                    <div class="text-left mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Alasan Reset (Wajib)</label>
                        <textarea name="reason" rows="3" class="w-full px-4 py-3 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-red-500 focus:ring-0 text-sm" placeholder="Contoh: Salah scan barang, mau ulang dari awal..." required minlength="5"></textarea>
                    </div>

                    <div class="flex gap-4">
                        <button type="button" @click="showResetModal = false" class="flex-1 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" class="flex-1 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl shadow-lg shadow-red-200 transition-colors">
                            Ya, Reset Semua
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div> <!-- End of .fade-in -->

<script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
<script>
    const { createApp, ref } = Vue;

    createApp({
        setup() {
            const showResetModal = ref(false);
            return { showResetModal };
        }
    }).mount('.fade-in');
</script>

<style>
    @keyframes bounce-in {
        0% { transform: scale(0.9); opacity: 0; }
        50% { transform: scale(1.05); opacity: 1; }
        100% { transform: scale(1); }
    }
    .animate-bounce-in {
        animation: bounce-in 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
</style>
@endsection
