@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a 
            href="{{ route('submaster.part.index') }}" 
            class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors"
            title="Kembali"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-xl font-bold text-gray-900 leading-none">Tambah Part</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Tambah data part baru</p>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="createForm" class="space-y-8">
            @csrf
            
            {{-- Basic Info Section --}}
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nomor_part" class="block text-sm font-medium text-gray-700 mb-2">
                            Nomor Part <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nomor_part" 
                            name="nomor_part" 
                            required
                            maxlength="100"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent font-mono"
                            placeholder="Contoh: PART-001"
                        >
                    </div>
                    <div>
                        <label for="nama_part" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Part <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="nama_part" 
                            name="nama_part" 
                            required
                            maxlength="255"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Masukkan nama part"
                        >
                    </div>
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Customer <span class="text-red-500">*</span>
                        </label>
                        <div class="relative customer-autocomplete-wrapper">
                            <input 
                                type="text" 
                                id="customer_input" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ketik nama customer..." 
                                autocomplete="off"
                            >
                            <input type="hidden" id="customer_id" name="customer_id" required>
                            <div class="autocomplete-list hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                    <div>
                        <label for="tipe_part" class="block text-sm font-medium text-gray-700 mb-2">
                            Tipe Part
                        </label>
                        <input 
                            type="text" 
                            id="tipe_part" 
                            name="tipe_part" 
                            maxlength="255"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="Masukkan tipe part (opsional)"
                        >
                    </div>
                    <div>
                        <label for="model_part" class="block text-sm font-medium text-gray-700 mb-2">
                            Model Part <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="model_part" 
                            name="model_part" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Model Part</option>
                            <option value="regular">Regular</option>
                            <option value="ckd">CKD</option>
                            <option value="cbu">CBU</option>
                            <option value="rempart">Rempart</option>
                        </select>
                    </div>
                    <div>
                        <label for="proses" class="block text-sm font-medium text-gray-700 mb-2">
                            Proses <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="proses" 
                            name="proses" 
                            required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Proses</option>
                            <option value="inject">INJECT</option>
                            <option value="assy">ASSY</option>
                        </select>
                    </div>
                    <div id="parentPartField" style="display: none;">
                        <label for="parent_part_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Source Part (Part INJECT)
                        </label>
                        <div class="relative parent-part-autocomplete-wrapper">
                            <input 
                                type="text" 
                                id="parent_part_id_input" 
                                autocomplete="off"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                placeholder="Ketik nomor part atau nama part INJECT..."
                            >
                            <input type="hidden" id="parent_part_id" name="parent_part_id" value="">
                            <div class="autocomplete-list hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Ketik untuk mencari part INJECT jika part ini merupakan lanjutan dari part INJECT
                        </p>
                    </div>
                </div>
            </div>

            {{-- All Fields Section (Hidden until Proses is selected) --}}
            <div id="allFieldsSection" style="display: none;">

            {{-- Cycle Time Section --}}
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Cycle Time</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div id="ctInjectField" style="display: none;">
                        <label for="CT_Inject" class="block text-sm font-medium text-gray-700 mb-2">
                            CT Inject
                        </label>
                        <input 
                            type="number" 
                            id="CT_Inject" 
                            name="CT_Inject" 
                            step="0.01"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.00"
                        >
                    </div>
                    <div id="ctAssyField" style="display: none;">
                        <label for="CT_Assy" class="block text-sm font-medium text-gray-700 mb-2">
                            CT Assy
                        </label>
                        <input 
                            type="number" 
                            id="CT_Assy" 
                            name="CT_Assy" 
                            step="0.01"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.00"
                        >
                    </div>
                </div>
            </div>

            {{-- Material/Masterbatch Section (INJECT Only) --}}
            <div id="materialSection" class="border-b border-gray-200 pb-4" style="display: none;">
                <div class="flex justify-between items-center mb-3">
                    <h3 class="text-base font-semibold text-gray-900">Material / Masterbatch</h3>
                    <div class="text-sm">
                        <span class="text-gray-600">Total Persentase: </span>
                        <span id="totalPercentage" class="font-semibold text-blue-600">0%</span>
                    </div>
                </div>
                <div id="materialContainer" class="space-y-3">
                    <div class="material-item border border-gray-200 rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Tipe</label>
                        <select 
                                    name="material_types[]"
                                    class="material-type-select w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="material">Material</option>
                                    <option value="masterbatch">Masterbatch</option>
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Material/Masterbatch</label>
                                <div class="relative material-autocomplete-wrapper w-full">
                                    <input type="text" 
                                           class="material-input w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                           placeholder="Pilih Material/Masterbatch" 
                                           autocomplete="off">
                                    <input type="hidden" name="material_ids[]" class="material-select">
                                    <div class="autocomplete-list hidden absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto"></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Persentase (%)</label>
                        <input 
                            type="number" 
                                    name="material_std_using[]" 
                            step="0.01"
                            min="0"
                                    max="100"
                                    class="material-percentage w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="0.00"
                                    onchange="validateMaterialPercentage()"
                        >
                                <p class="text-xs text-gray-500 mt-0.5">0-100%</p>
                    </div>
                        </div>
                        <button type="button" class="mt-2 text-xs text-red-600 hover:text-red-800 remove-material-btn" style="display: none;">Hapus</button>
                    </div>
                </div>
                <button type="button" id="addMaterialBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">
                    + Tambah Material/Masterbatch
                </button>
            </div>

            {{-- Box Section --}}
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Box</h3>
                <div id="boxContainer" class="space-y-3">
                    <div class="box-item border border-gray-200 rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Box</label>
                        <select 
                                    name="box_ids[]"
                                    class="box-select w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih Box (Opsional)</option>
                                    @foreach($boxes as $box)
                                        <option value="{{ $box->id }}"
                                            data-panjang="{{ $box->box?->panjang ?? '' }}"
                                            data-lebar="{{ $box->box?->lebar ?? '' }}"
                                            data-tinggi="{{ $box->box?->tinggi ?? '' }}">
                                            {{ $box->nomor_bahan_baku ?? '-' }} @if($box->box?->kode_box)({{ $box->box->kode_box }})@endif
                                        </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">P Box</label>
                        <input 
                            type="number" 
                                    name="box_panjang[]" 
                            step="0.01"
                            min="0"
                                    readonly
                                    class="box-panjang w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                            placeholder="0.00"
                        >
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">L Box</label>
                                <input 
                                    type="number" 
                                    name="box_lebar[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="box-lebar w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">T Box</label>
                        <input 
                            type="number" 
                                    name="box_tinggi[]" 
                            step="0.01"
                            min="0"
                                    readonly
                                    class="box-tinggi w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                            placeholder="0.00"
                        >
                    </div>
                </div>
                        <button type="button" class="mt-2 text-xs text-red-600 hover:text-red-800 remove-box-btn" style="display: none;">Hapus</button>
            </div>
                    </div>
                <button type="button" id="addBoxBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">
                    + Tambah Box
                </button>
            </div>

            {{-- Polybag Section --}}
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Polybag</h3>
                <div id="polybagContainer" class="space-y-3">
                    <div class="polybag-item border border-gray-200 rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Pilih Polybag</label>
                        <select 
                                    name="polybag_ids[]"
                                    class="polybag-select w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                        >
                            <option value="">Pilih Polybag (Opsional)</option>
                            @forelse($polybags as $polybag)
                                        <option value="{{ $polybag->id }}"
                                            data-panjang="{{ $polybag->polybag?->panjang ?? '' }}"
                                            data-lebar="{{ $polybag->polybag?->lebar ?? '' }}"
                                            data-tinggi="{{ $polybag->polybag?->tinggi ?? '' }}">
                                            {{ $polybag->nomor_bahan_baku ?? '-' }}
                                </option>
                            @empty
                                        <option value="" disabled>Tidak ada data Polybag</option>
                            @endforelse
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">P Polybag</label>
                        <input 
                            type="number" 
                                    name="polybag_panjang[]" 
                            step="0.01"
                            min="0"
                                    readonly
                                    class="polybag-panjang w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                            placeholder="0.00"
                        >
                    </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">L Polybag</label>
                                <input 
                                    type="number" 
                                    name="polybag_lebar[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="polybag-lebar w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">T Polybag</label>
                                <input 
                                    type="number" 
                                    name="polybag_tinggi[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="polybag-tinggi w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Std Using</label>
                                <input 
                                    type="number" 
                                    name="polybag_std_using[]" 
                                    step="0.01"
                                    min="0"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-xs text-red-600 hover:text-red-800 remove-polybag-btn" style="display: none;">Hapus</button>
                    </div>
                </div>
                <button type="button" id="addPolybagBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">
                    + Tambah Polybag
                </button>
            </div>

            {{-- Layer Section --}}
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Layer</h3>
                <div id="layerContainer" class="space-y-3">
                    <div class="layer-item border border-gray-200 rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Pilih Layer
                                </label>
                                <div class="relative layer-autocomplete-wrapper w-full">
                                    <input type="text" 
                                           class="layer-input w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                           placeholder="Pilih Layer (Opsional)" 
                                           autocomplete="off">
                                    <input type="hidden" name="layer_jenis[]" class="layer-jenis-select">
                                    <div class="autocomplete-list hidden absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto"></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    P Layer
                                </label>
                                <input 
                                    type="number" 
                                    name="layer_panjang[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="layer-panjang w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    L Layer
                                </label>
                                <input 
                                    type="number" 
                                    name="layer_lebar[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="layer-lebar w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    T Layer
                                </label>
                                <input 
                                    type="number" 
                                    name="layer_tinggi[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="layer-tinggi w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Std Using
                                </label>
                                <input 
                                    type="number" 
                                    name="layer_std_using[]" 
                                    step="0.01"
                                    min="0"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>
                        <input type="hidden" name="layer_materials[]" class="layer-material-id">
                        <button type="button" class="mt-2 text-xs text-red-600 hover:text-red-800 remove-layer-btn" style="display: none;">Hapus</button>
                    </div>
                </div>
                <button type="button" id="addLayerBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">
                    + Tambah Layer
                </button>
            </div>

            {{-- Subpart Section --}}
            <div class="border-b border-gray-200 pb-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Subpart</h3>
                <div id="subpartContainer" class="space-y-3">
                    <div class="subpart-item border border-gray-200 rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Pilih Subpart
                                </label>
                                <div class="relative subpart-autocomplete-wrapper w-full">
                                    <input type="text" 
                                           class="subpart-input w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500" 
                                           placeholder="Pilih Subpart (Opsional)" 
                                           autocomplete="off">
                                    <input type="hidden" name="subpart_ids[]" class="subpart-select">
                                    <div class="autocomplete-list hidden absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto"></div>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Nama
                                </label>
                                <input 
                                    type="text" 
                                    name="subpart_nama[]"
                                    class="subpart-nama w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="Auto dari pilihan"
                                    readonly
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Std Packing
                                </label>
                                <input 
                                    type="number" 
                                    name="subpart_std_packing[]" 
                                    step="0.01"
                                    min="0"
                                    readonly
                                    class="subpart-std-packing w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="0.00"
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    UOM
                                </label>
                                <input 
                                    type="text" 
                                    name="subpart_uom[]"
                                    class="subpart-uom w-full px-2 py-1.5 text-xs border border-gray-300 rounded bg-gray-100 cursor-not-allowed"
                                    placeholder="Auto dari pilihan"
                                    readonly
                                >
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">
                                    Std Using
                                </label>
                                <input 
                                    type="number" 
                                    name="subpart_std_using[]" 
                                    step="0.01"
                                    min="0"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                    placeholder="0.00"
                                >
                            </div>
                        </div>
                        <button type="button" class="mt-2 text-xs text-red-600 hover:text-red-800 remove-subpart-btn" style="display: none;">Hapus</button>
                    </div>
                </div>
                <button type="button" id="addSubpartBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">
                    + Tambah Subpart
                </button>
            </div>

            {{-- Label & Packing Section --}}
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Label & Packing</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="Warna_Label_Packing" class="block text-sm font-medium text-gray-700 mb-2">
                            Warna Label Packing
                        </label>
                        <select 
                            id="Warna_Label_Packing" 
                            name="Warna_Label_Packing" 
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        >
                            <option value="">Pilih Warna Label Packing (Opsional)</option>
                            <option value="putih">Putih</option>
                            <option value="kuning">Kuning</option>
                            <option value="merah">Merah</option>
                            <option value="biru">Biru</option>
                            <option value="hijau">Hijau</option>
                            <option value="hitam">Hitam</option>
                            <option value="buram">Buram</option>
                        </select>
                    </div>
                    <div>
                        <label for="QTY_Packing_Box" class="block text-sm font-medium text-gray-700 mb-2">
                            QTY Packing Box
                        </label>
                        <input 
                            type="number" 
                            id="QTY_Packing_Box" 
                            name="QTY_Packing_Box" 
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0"
                        >
                    </div>
                </div>
            </div>

            {{-- Rempart Section --}}
            <div id="rempartSection" class="border-b border-gray-200 pb-4" style="display: none;">
                <h3 class="text-base font-semibold text-gray-900 mb-3">Rempart</h3>
                <div id="rempartContainer" class="space-y-3">
                    <div class="rempart-item border border-gray-200 rounded p-3 bg-gray-50">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Karton Box (P0-D0)</label>
                        <select 
                                    name="rempart_r_karton_box_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'karton_box_p0_d0')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Polybag (P0-P0)</label>
                        <select 
                                    name="rempart_r_polybag_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'polybag_p0_p0')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Gasket Duplex (P0-LD)</label>
                        <select 
                                    name="rempart_r_gasket_duplex_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'gasket_duplex_p0_ld')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Foam Sheet (P0-S0)</label>
                        <select 
                                    name="rempart_r_foam_sheet_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'foam_sheet_p0_s0')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Hologram (P0-H0)</label>
                        <select 
                                    name="rempart_r_hologram_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'hologram_p0_h0')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Label A</label>
                        <select 
                                    name="rempart_r_labela_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'label_a')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Label B</label>
                        <select 
                                    name="rempart_r_labelb_id[]"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                                >
                                    <option value="">Pilih (Opsional)</option>
                                    @foreach($rempartMaterials as $item)
                                        @if($item->rempart?->jenis === 'label_b')
                                            <option value="{{ $item->id }}">{{ $item->nomor_bahan_baku ?? '-' }}</option>
                                        @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Qty Pcs</label>
                        <input 
                            type="number" 
                                    name="rempart_r_qty_pcs[]" 
                            min="0"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
                            placeholder="0"
                        >
                    </div>
                </div>
                        <button type="button" class="mt-2 text-xs text-red-600 hover:text-red-800 remove-rempart-btn" style="display: none;">Hapus</button>
            </div>
                    </div>
                <button type="button" id="addRempartBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">
                    + Tambah Rempart
                </button>
                    </div>

            {{-- Weight Section (INJECT Only) --}}
            <div id="weightSection" class="pb-6" style="display: none;">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Weight</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="netto" class="block text-sm font-medium text-gray-700 mb-2">
                            Netto
                        </label>
                        <input 
                            type="number" 
                            id="netto" 
                            name="netto" 
                            step="0.001"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.000"
                        >
                    </div>
                    <div>
                        <label for="brutto" class="block text-sm font-medium text-gray-700 mb-2">
                            Brutto
                        </label>
                        <input 
                            type="number" 
                            id="brutto" 
                            name="brutto" 
                            step="0.001"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.000"
                        >
                    </div>
                    <div>
                        <label for="runner" class="block text-sm font-medium text-gray-700 mb-2">
                            Runner
                        </label>
                        <input 
                            type="number" 
                            id="runner" 
                            name="runner" 
                            step="0.001"
                            min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="0.000"
                        >
                    </div>
                </div>
            </div>

            </div>{{-- End allFieldsSection --}}

            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button 
                    type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Simpan</span>
                </button>
                <a 
                    href="{{ route('submaster.part.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
// Data Sources
const customersData = @json($customersData);
const materialsData = @json($materialsData);
const masterbatchesData = @json($masterbatchesData);
const boxesData = @json($boxesData);
const polybagsData = @json($polybagsData);
const layersData = @json($layersData);
const subpartsData = @json($subpartsData);
// Part Data for Source Part (INJECT)
const partsRaw = @json($parts);
const partsData = partsRaw.map(p => ({
    id: p.id,
    label: (p.nomor_part || '-') + ' - ' + (p.nama_part || '') + (p.proses ? ` (${p.proses})` : '')
}));

// Reusable Autocomplete Setup Function
// Enhanced Reusable Autocomplete Setup Function
function setupAutocomplete(wrapper, dataSource, onSelect) {
    let input = wrapper.querySelector('input[type="text"]');
    let hidden = wrapper.querySelector('input[type="hidden"]');
    let list = wrapper.querySelector('.autocomplete-list');
    
    // Convert SELECT to AUTOCOMPLETE if needed
    const select = wrapper.querySelector('select');
    if (select && !select.classList.contains('hidden')) {
        // Prevent duplicate conversion
        if (wrapper.querySelector('input[type="text"].w-full')) return;

        // Create elements - preserve crucial classes for logic selectors
        const idClasses = ['material-select', 'box-select', 'polybag-select', 'layer-jenis-select', 'subpart-select'];
        
        input = document.createElement('input');
        input.type = 'text';
        
        // Copy visual classes but exclude ID classes from the textbox
        const classList = select.className.split(/\s+/);
        input.className = classList
            .filter(c => c !== 'hidden' && !idClasses.includes(c))
            .join(' ');

        // Add default styling if none exists
        if (!input.className || input.className.trim() === '') {
            input.className = 'w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500';
        }

        input.placeholder = 'Ketik untuk mencari...';
        input.autocomplete = 'off';
        
        // Create hidden input for the actual value
        const newHidden = document.createElement('input');
        newHidden.type = 'hidden';
        newHidden.name = select.name;
        
        // Add ID classes to hidden input so logic can find it
        idClasses.forEach(cls => {
            if (select.classList.contains(cls)) newHidden.classList.add(cls);
        });
        
        // Create list container
        const newList = document.createElement('div');
        newList.className = 'autocomplete-list hidden absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg max-h-40 overflow-y-auto';
        
        // Remove old select
        select.remove();
        
        // Add new elements
        wrapper.classList.add('relative');
        wrapper.appendChild(input);
        wrapper.appendChild(newHidden);
        wrapper.appendChild(newList);
        
        // Update references
        hidden = newHidden;
        list = newList;
    }
    
    // Safety check
    if (!input || !hidden || !list) {
        // Try one more time to find elements (for manual inputs like customer)
        input = wrapper.querySelector('input[type="text"]');
        hidden = wrapper.querySelector('input[type="hidden"]');
        list = wrapper.querySelector('.autocomplete-list');
        if (!input || !hidden || !list) return;
    }

    // Reset state helper
    const resetState = () => {
        list.classList.add('hidden');
        list.innerHTML = '';
    };

    // Filter and show options
    const showSuggestions = (query) => {
        // Resolve data source (can be array or function)
        const data = typeof dataSource === 'function' ? dataSource() : dataSource;
        
        const filtered = data.filter(item => 
            item.label.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 20);

        list.innerHTML = '';
        if (filtered.length === 0) {
            const div = document.createElement('div');
            div.className = 'px-3 py-2 text-gray-500 text-xs italic';
            div.textContent = 'Tidak ditemukan';
            list.appendChild(div);
        } else {
            filtered.forEach(item => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100 text-xs';
                div.textContent = item.label;
                div.addEventListener('click', () => {
                    input.value = item.label;
                    hidden.value = item.id;
                    resetState();
                    if (onSelect) onSelect(item);
                });
                list.appendChild(div);
            });
        }
        list.classList.remove('hidden');
    };

    // Clear previous listeners (clone replacement to handle re-init scenarios)
    const newInput = input.cloneNode(true);
    if(input.parentNode) input.parentNode.replaceChild(newInput, input);
    input = newInput;
    
    // Input Event
    input.addEventListener('input', function() {
        const query = this.value.trim();
        const data = typeof dataSource === 'function' ? dataSource() : dataSource;
        
        // Clear hidden if text mismatch
        if (hidden.value && query !== (data.find(i => i.id == hidden.value)?.label || '')) {
             hidden.value = ''; 
             if (onSelect) onSelect(null);
        }
        
        if (query.length === 0) {
            resetState();
            return;
        }
        showSuggestions(query);
    });

    // Focus Event
    input.addEventListener('focus', function() {
        const query = this.value.trim();
        showSuggestions(query);
    });
    
    // Click outside to close
    document.addEventListener('click', function(e) {
        if (!wrapper.contains(e.target)) {
            resetState();
        }
    });

} // END setupAutocomplete

// 7. Handle Customer Autocomplete
(function() {
    const wrapper = document.querySelector('.customer-autocomplete-wrapper');
    if (!wrapper) return;
    
    setupAutocomplete(wrapper, customersData, (selected) => {
        // Optional: do something when customer selected
    });
})();

// Toggle all fields based on Proses selection
(function() {
    function initProsesToggle() {
        const prosesSelect = document.getElementById('proses');
        const parentPartField = document.getElementById('parentPartField');
        const allFieldsSection = document.getElementById('allFieldsSection');
        const ctInjectField = document.getElementById('ctInjectField');
        const ctAssyField = document.getElementById('ctAssyField');
        const materialSection = document.getElementById('materialSection');
        const weightSection = document.getElementById('weightSection');

        if (!prosesSelect) return;

        function toggle() {
             const val = prosesSelect.value;
             if (val) {
                 allFieldsSection.style.display = 'block';
                 if (val === 'inject') {
                     if(parentPartField) parentPartField.style.display = 'none';
                     if(ctInjectField) ctInjectField.style.display = 'block';
                     if(ctAssyField) ctAssyField.style.display = 'none';
                     if(materialSection) materialSection.style.display = 'block';
                     if(weightSection) weightSection.style.display = 'block';
                 } else if (val === 'assy') {
                     if(parentPartField) parentPartField.style.display = 'block';
                     if(ctInjectField) ctInjectField.style.display = 'none';
                     if(ctAssyField) ctAssyField.style.display = 'block';
                     if(materialSection) materialSection.style.display = 'none';
                     if(weightSection) weightSection.style.display = 'none';
                 }
             } else {
                 allFieldsSection.style.display = 'none';
                 if(parentPartField) parentPartField.style.display = 'none';
             }
        }
        
        prosesSelect.addEventListener('change', toggle);
        toggle(); // Initial run
    }

    initProsesToggle();
})();

// --------------------------------------------------------------------------
// 1. Handle Dynamic Material/Masterbatch
// --------------------------------------------------------------------------
(function() {
    const materialContainer = document.getElementById('materialContainer');
    const addMaterialBtn = document.getElementById('addMaterialBtn');
    
    function initItem(item) {
        const wrapper = item.querySelector('.material-autocomplete-wrapper') || item;
        const typeSelect = item.querySelector('.material-type-select');
        
        if (!wrapper) return;
        
        // Define data getter based on type
        const getData = () => {
             return typeSelect.value === 'masterbatch' ? masterbatchesData : materialsData;
        };
        
        setupAutocomplete(wrapper, getData, (selectedItem) => {
            // No extra action needed on select for material
        });
    }

    function updateRemoveButtons() {
        const items = materialContainer.querySelectorAll('.material-item');
        items.forEach(item => {
            const removeBtn = item.querySelector('.remove-material-btn');
            removeBtn.style.display = items.length > 1 ? 'block' : 'none';
        });
    }
    
    // Add Button
    addMaterialBtn.addEventListener('click', function() {
        const firstItem = materialContainer.querySelector('.material-item');
        const newItem = firstItem.cloneNode(true);
        
        // Reset values
        newItem.querySelector('.material-input').value = '';
        newItem.querySelector('.material-select').value = ''; // hidden
        newItem.querySelector('input[name="material_std_using[]"]').value = '';
        newItem.querySelector('.material-type-select').value = 'material';
        
        materialContainer.appendChild(newItem);
        initItem(newItem);
        updateRemoveButtons();
    });
    
    // Remove Button
    materialContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-material-btn')) {
            e.target.closest('.material-item').remove();
            updateRemoveButtons();
        }
    });

    // Type Change Listener (Delegate)
    materialContainer.addEventListener('change', function(e) {
        if (e.target.classList.contains('material-type-select')) {
            // Clear input when type changes
            const item = e.target.closest('.material-item');
            item.querySelector('.material-input').value = '';
            item.querySelector('.material-select').value = '';
        }
    });
    
    // Init existing
    materialContainer.querySelectorAll('.material-item').forEach(initItem);
    updateRemoveButtons();
})();


// --------------------------------------------------------------------------
// 2. Handle Dynamic Box (Convert Select to Autocomplete)
// --------------------------------------------------------------------------
(function() {
    const boxContainer = document.getElementById('boxContainer');
    const addBoxBtn = document.getElementById('addBoxBtn');

    function initItem(item) {
        // We look for wrapper. If wrapper doesn't exist, we look for select parent div
        let wrapper = item.querySelector('.box-autocomplete-wrapper');
        // If HTML replacement failed, wrapper might be missing, so we use select parent
        if (!wrapper) {
             const select = item.querySelector('.box-select');
             if (select) wrapper = select.parentNode;
        }

        if (!wrapper) return;

        setupAutocomplete(wrapper, boxesData, (selected) => {
            if (selected) {
                 item.querySelector('.box-panjang').value = selected.panjang;
                 item.querySelector('.box-lebar').value = selected.lebar;
                 item.querySelector('.box-tinggi').value = selected.tinggi;
            } else {
                 item.querySelector('.box-panjang').value = '';
                 item.querySelector('.box-lebar').value = '';
                 item.querySelector('.box-tinggi').value = '';
            }
        });
    }

    function updateRemoveButtons() {
        const items = boxContainer.querySelectorAll('.box-item');
        items.forEach(item => {
            const removeBtn = item.querySelector('.remove-box-btn');
            removeBtn.style.display = items.length > 1 ? 'block' : 'none';
        });
    }

    addBoxBtn.addEventListener('click', function() {
        const firstItem = boxContainer.querySelector('.box-item');
        const newItem = firstItem.cloneNode(true);
        
        // Reset
        const input = newItem.querySelector('.box-input') || newItem.querySelector('input[type="text"]');
        if (input) input.value = '';
        const hidden = newItem.querySelector('.box-select') || newItem.querySelector('input[type="hidden"]');
        if (hidden) hidden.value = '';
        
        newItem.querySelector('.box-panjang').value = '';
        newItem.querySelector('.box-lebar').value = '';
        newItem.querySelector('.box-tinggi').value = '';
        
        boxContainer.appendChild(newItem);
        initItem(newItem);
        updateRemoveButtons();
    });

    boxContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-box-btn')) {
            e.target.closest('.box-item').remove();
            updateRemoveButtons();
        }
    });

    boxContainer.querySelectorAll('.box-item').forEach(initItem);
    updateRemoveButtons();
})();


// --------------------------------------------------------------------------
// 3. Handle Dynamic Polybag
// --------------------------------------------------------------------------
(function() {
    const polybagContainer = document.getElementById('polybagContainer');
    const addPolybagBtn = document.getElementById('addPolybagBtn');

    function initItem(item) {
        let wrapper = item.querySelector('.polybag-autocomplete-wrapper');
        if (!wrapper) {
             const select = item.querySelector('.polybag-select');
             if (select) wrapper = select.parentNode;
        }
        if (!wrapper) return;

        setupAutocomplete(wrapper, polybagsData, (selected) => {
             if (selected) {
                 item.querySelector('.polybag-panjang').value = selected.panjang;
                 item.querySelector('.polybag-lebar').value = selected.lebar;
                 item.querySelector('.polybag-tinggi').value = selected.tinggi;
            } else {
                 item.querySelector('.polybag-panjang').value = '';
                 item.querySelector('.polybag-lebar').value = '';
                 item.querySelector('.polybag-tinggi').value = '';
            }
        });
    }

    function updateRemoveButtons() {
        const items = polybagContainer.querySelectorAll('.polybag-item');
        items.forEach(item => {
            const removeBtn = item.querySelector('.remove-polybag-btn');
            removeBtn.style.display = items.length > 1 ? 'block' : 'none';
        });
    }

    addPolybagBtn.addEventListener('click', function() {
        const firstItem = polybagContainer.querySelector('.polybag-item');
        const newItem = firstItem.cloneNode(true);
        
        const input = newItem.querySelector('.polybag-input') || newItem.querySelector('input[type="text"]');
        if (input) input.value = '';
        const hidden = newItem.querySelector('.polybag-select') || newItem.querySelector('input[type="hidden"]');
        if (hidden) hidden.value = '';
        
        newItem.querySelector('input[name="polybag_std_using[]"]').value = '';
        newItem.querySelector('.polybag-panjang').value = '';
        newItem.querySelector('.polybag-lebar').value = '';
        newItem.querySelector('.polybag-tinggi').value = '';

        polybagContainer.appendChild(newItem);
        initItem(newItem);
        updateRemoveButtons();
    });

    polybagContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-polybag-btn')) {
            e.target.closest('.polybag-item').remove();
            updateRemoveButtons();
        }
    });

    polybagContainer.querySelectorAll('.polybag-item').forEach(initItem);
    updateRemoveButtons();
})();


// --------------------------------------------------------------------------
// 4. Handle Dynamic Layer
// --------------------------------------------------------------------------
(function() {
    const layerContainer = document.getElementById('layerContainer');
    const addLayerBtn = document.getElementById('addLayerBtn');

    function initItem(item) {
        let wrapper = item.querySelector('.layer-autocomplete-wrapper') || item;
        
        setupAutocomplete(wrapper, layersData, (selected) => {
             // Layer logic stores ID in .layer-material-id hidden input
             // And label in layer-jenis-select name (via hidden input inside wrapper)
             item.querySelector('.layer-material-id').value = selected ? selected.id : '';
             
             if (selected) {
                 item.querySelector('.layer-panjang').value = selected.panjang;
                 item.querySelector('.layer-lebar').value = selected.lebar;
                 item.querySelector('.layer-tinggi').value = selected.tinggi;
             } else {
                 item.querySelector('.layer-panjang').value = '';
                 item.querySelector('.layer-lebar').value = '';
                 item.querySelector('.layer-tinggi').value = '';
             }
        });
    }

    function updateRemoveButtons() {
        const items = layerContainer.querySelectorAll('.layer-item');
        items.forEach(item => {
            const removeBtn = item.querySelector('.remove-layer-btn');
            removeBtn.style.display = items.length > 1 ? 'block' : 'none';
        });
    }

    addLayerBtn.addEventListener('click', function() {
        const firstItem = layerContainer.querySelector('.layer-item');
        const newItem = firstItem.cloneNode(true);
        
        const input = newItem.querySelector('.layer-input');
        if (input) input.value = '';
        const hidden = newItem.querySelector('.layer-jenis-select');
        if (hidden) hidden.value = '';
        
        newItem.querySelector('.layer-material-id').value = '';
        newItem.querySelector('.layer-panjang').value = '';
        newItem.querySelector('.layer-lebar').value = '';
        newItem.querySelector('.layer-tinggi').value = '';
        newItem.querySelector('input[name="layer_std_using[]"]').value = '';
        
        layerContainer.appendChild(newItem);
        initItem(newItem);
        updateRemoveButtons();
    });

    layerContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-layer-btn')) {
            e.target.closest('.layer-item').remove();
            updateRemoveButtons();
        }
    });

    layerContainer.querySelectorAll('.layer-item').forEach(initItem);
    updateRemoveButtons();
})();


// --------------------------------------------------------------------------
// 5. Handle Dynamic Subpart
// --------------------------------------------------------------------------
(function() {
    const subpartContainer = document.getElementById('subpartContainer');
    const addSubpartBtn = document.getElementById('addSubpartBtn');

    function initItem(item) {
        let wrapper = item.querySelector('.subpart-autocomplete-wrapper') || item;
        
        setupAutocomplete(wrapper, subpartsData, (selected) => {
             if (selected) {
                 item.querySelector('.subpart-nama').value = selected.nama;
                 item.querySelector('.subpart-std-packing').value = selected.std_packing;
                 item.querySelector('.subpart-uom').value = selected.uom;
             } else {
                 item.querySelector('.subpart-nama').value = '';
                 item.querySelector('.subpart-std-packing').value = '';
                 item.querySelector('.subpart-uom').value = '';
             }
        });
    }

    function updateRemoveButtons() {
        const items = subpartContainer.querySelectorAll('.subpart-item');
        items.forEach(item => {
            const removeBtn = item.querySelector('.remove-subpart-btn');
            removeBtn.style.display = items.length > 1 ? 'block' : 'none';
        });
    }

    addSubpartBtn.addEventListener('click', function() {
        const firstItem = subpartContainer.querySelector('.subpart-item');
        const newItem = firstItem.cloneNode(true);
        
        const input = newItem.querySelector('.subpart-input');
        if (input) input.value = '';
        const hidden = newItem.querySelector('.subpart-select');
        if (hidden) hidden.value = '';
        
        newItem.querySelector('.subpart-nama').value = '';
        newItem.querySelector('.subpart-std-packing').value = '';
        newItem.querySelector('.subpart-uom').value = '';
        newItem.querySelector('input[name="subpart_std_using[]"]').value = '';
        
        subpartContainer.appendChild(newItem);
        initItem(newItem);
        updateRemoveButtons();
    });

    subpartContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-subpart-btn')) {
            e.target.closest('.subpart-item').remove();
            updateRemoveButtons();
        }
    });

    subpartContainer.querySelectorAll('.subpart-item').forEach(initItem);
    updateRemoveButtons();
})();


// --------------------------------------------------------------------------
// 6. Handle Dynamic Source Part (Parent Part)
// --------------------------------------------------------------------------
(function() {
    const wrapper = document.querySelector('.parent-part-autocomplete-wrapper');
    if (!wrapper) return;

    setupAutocomplete(wrapper, partsData, (selected) => {
        // No extra fields to populate for now
    });
})();

// Validate Material Percentage
function validateMaterialPercentage() {
    let total = 0;
    
    document.querySelectorAll('.material-item').forEach(item => {
        const percentageInput = item.querySelector('.material-percentage');
        if (percentageInput) {
            const percentage = parseFloat(percentageInput.value) || 0;
            total += percentage;
        }
    });
    
    // Round to 2 decimal places to avoid floating point issues
    total = Math.round(total * 100) / 100;
    
    const totalPercentageEl = document.getElementById('totalPercentage');
    
    if (totalPercentageEl) {
        totalPercentageEl.textContent = total.toFixed(2) + '%';
        
        // Use tolerance for floating point comparison (0.01)
        if (total > 100.01) {
            totalPercentageEl.classList.remove('text-blue-600', 'text-green-600');
            totalPercentageEl.classList.add('text-red-600');
            return false;
        } else if (Math.abs(total - 100) <= 0.01) {
            totalPercentageEl.classList.remove('text-red-600', 'text-blue-600');
            totalPercentageEl.classList.add('text-green-600');
            return true;
        } else {
            totalPercentageEl.classList.remove('text-red-600', 'text-green-600');
            totalPercentageEl.classList.add('text-blue-600');
            return true;
        }
    }
    
    return true;
}

// Toggle Rempart Section based on Model Part selection
(function() {
    const modelPartSelect = document.getElementById('model_part');
    const rempartSection = document.getElementById('rempartSection');
    
    function toggleRempartSection() {
        if (modelPartSelect.value === 'rempart') {
            rempartSection.style.display = 'block';
        } else {
            rempartSection.style.display = 'none';
        }
    }
    
    // Initial state
    toggleRempartSection();
    
    // On change
    modelPartSelect.addEventListener('change', toggleRempartSection);
})();



// Handle Dynamic Rempart
(function() {
    const rempartContainer = document.getElementById('rempartContainer');
    const addRempartBtn = document.getElementById('addRempartBtn');
    
    function updateRemoveButtons() {
        const items = rempartContainer.querySelectorAll('.rempart-item');
        items.forEach((item, index) => {
            const removeBtn = item.querySelector('.remove-rempart-btn');
            if (items.length > 1) {
                removeBtn.style.display = 'block';
            } else {
                removeBtn.style.display = 'none';
            }
        });
    }
    
    addRempartBtn.addEventListener('click', function() {
        const firstItem = rempartContainer.querySelector('.rempart-item');
        const newItem = firstItem.cloneNode(true);
        newItem.querySelectorAll('select').forEach(select => select.value = '');
        newItem.querySelector('input[name="rempart_r_qty_pcs[]"]').value = '';
        rempartContainer.appendChild(newItem);
        updateRemoveButtons();
    });
    
    rempartContainer.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-rempart-btn')) {
            e.target.closest('.rempart-item').remove();
            updateRemoveButtons();
        }
    });
    
    updateRemoveButtons();
})();



document.getElementById('createForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validate material percentage - only if material section is visible
    const materialSection = document.getElementById('materialSection');
    if (materialSection && materialSection.style.display !== 'none') {
        // Use the same calculation as validateMaterialPercentage
        let totalPercent = 0;
        const materialItems = document.querySelectorAll('.material-item');
        
        materialItems.forEach(item => {
            const percentageInput = item.querySelector('.material-percentage');
            if (percentageInput && percentageInput.value) {
                const percentage = parseFloat(percentageInput.value) || 0;
                totalPercent += percentage;
            }
        });
        
        // Round to 2 decimal places to avoid floating point precision issues
        totalPercent = Math.round(totalPercent * 100) / 100;
        
        // Debug: log the total
        console.log('Total persentase:', totalPercent);
        
        // Check if total exceeds 100% (use tolerance for floating point - allow up to 100.01)
        // Only block if clearly over 100% (more than 0.01 tolerance)
        // Allow exactly 100% (with tolerance)
        if (totalPercent > 100.01) {
            alert('Total persentase material dan masterbatch tidak boleh melebihi 100% (Saat ini: ' + totalPercent.toFixed(2) + '%)');
            return;
        }
        
        // Update display (this will update the visual indicator)
        validateMaterialPercentage();
        
        // Optional: warn if not exactly 100% (but allow submission, use tolerance)
        // Only show confirm if total is significantly different from 100%
        if (totalPercent > 0 && Math.abs(totalPercent - 100) > 0.01) {
            if (!confirm('Total persentase adalah ' + totalPercent.toFixed(2) + '%. Apakah Anda yakin ingin melanjutkan? (Rekomendasi: 100%)')) {
                return;
            }
        }
    }
    
    const formData = new FormData(this);
    
    // Create new FormData and copy all fields except material/masterbatch (we'll add them manually)
    const newFormData = new FormData();
    for (let [key, value] of formData.entries()) {
        // Skip material fields - we'll add them manually to avoid duplication
        if (!key.startsWith('material_') && !key.startsWith('layer_') && 
            !key.startsWith('box_') && !key.startsWith('polybag_') && 
            !key.startsWith('rempart_') && !key.startsWith('subpart_')) {
            newFormData.append(key, value);
        }
    }
    
    // Collect layer data with P/L/T
    const layerMaterials = [];
    const layerJenis = [];
    const layerPanjang = [];
    const layerLebar = [];
    const layerTinggi = [];
    const layerStdUsing = [];
    
    document.querySelectorAll('.layer-item').forEach(item => {
        const materialId = item.querySelector('.layer-material-id').value;
        const jenis = item.querySelector('.layer-jenis-select').value || '';
        const panjang = item.querySelector('.layer-panjang').value || 0;
        const lebar = item.querySelector('.layer-lebar').value || 0;
        const tinggi = item.querySelector('.layer-tinggi').value || 0;
        const stdUsing = item.querySelector('input[name="layer_std_using[]"]').value || 0;
        
        if (materialId) {
            layerMaterials.push(materialId);
            layerJenis.push(jenis);
            layerPanjang.push(panjang);
            layerLebar.push(lebar);
            layerTinggi.push(tinggi);
            layerStdUsing.push(stdUsing);
        }
    });
    
    // newFormData already created above, just skip layer fields when copying
    // (layer fields will be added manually below)
    
    // Add layer data to formData
    layerMaterials.forEach((id, index) => {
        newFormData.append('layer_materials[]', id);
        newFormData.append('layer_jenis[]', layerJenis[index] || '');
        newFormData.append('layer_panjang[]', layerPanjang[index] || 0);
        newFormData.append('layer_lebar[]', layerLebar[index] || 0);
        newFormData.append('layer_tinggi[]', layerTinggi[index] || 0);
        newFormData.append('layer_std_using[]', layerStdUsing[index] || 0);
    });

    // Collect Material/Masterbatch data (combined)
    const materialIds = [];
    const materialTypes = [];
    const materialStdUsing = [];
    document.querySelectorAll('.material-item').forEach(item => {
        const materialId = item.querySelector('.material-select').value;
        const materialType = item.querySelector('.material-type-select').value || 'material';
        const stdUsing = item.querySelector('input[name="material_std_using[]"]').value || 0;
        if (materialId) {
            materialIds.push(materialId);
            materialTypes.push(materialType);
            materialStdUsing.push(stdUsing);
        }
    });
    
    // Remove ALL existing material_* fields from newFormData to prevent duplication
    // FormData doesn't support deleteAll, so we need to delete each one
    const materialKeysToDelete = [];
    for (let [key, value] of newFormData.entries()) {
        if (key.startsWith('material_')) {
            materialKeysToDelete.push(key);
        }
    }
    // Delete all material_ keys (note: FormData.delete() removes ALL entries with that key)
    materialKeysToDelete.forEach(key => {
        newFormData.delete(key);
    });
    
    // Debug: log material data before adding
    console.log('Material data to send:', {
        ids: materialIds,
        types: materialTypes,
        stdUsing: materialStdUsing,
        total: materialStdUsing.reduce((sum, val) => sum + parseFloat(val || 0), 0)
    });
    
    // Now add material data (should be clean, no duplicates)
    materialIds.forEach((id, index) => {
        newFormData.append('material_ids[]', id);
        newFormData.append('material_types[]', materialTypes[index] || 'material');
        newFormData.append('material_std_using[]', materialStdUsing[index] || 0);
    });
    
    // Collect Box data
    const boxIds = [];
    const boxPanjang = [];
    const boxLebar = [];
    const boxTinggi = [];
    document.querySelectorAll('.box-item').forEach(item => {
        const boxId = item.querySelector('.box-select').value;
        const panjang = item.querySelector('.box-panjang').value || '';
        const lebar = item.querySelector('.box-lebar').value || '';
        const tinggi = item.querySelector('.box-tinggi').value || '';
        if (boxId) {
            boxIds.push(boxId);
            boxPanjang.push(panjang);
            boxLebar.push(lebar);
            boxTinggi.push(tinggi);
        }
    });
    
    boxIds.forEach((id, index) => {
        newFormData.append('box_ids[]', id);
        newFormData.append('box_panjang[]', boxPanjang[index] || '');
        newFormData.append('box_lebar[]', boxLebar[index] || '');
        newFormData.append('box_tinggi[]', boxTinggi[index] || '');
    });
    
    // Collect Polybag data
    const polybagIds = [];
    const polybagPanjang = [];
    const polybagLebar = [];
    const polybagTinggi = [];
    const polybagStdUsing = [];
    document.querySelectorAll('.polybag-item').forEach(item => {
        const polybagId = item.querySelector('.polybag-select').value;
        const panjang = item.querySelector('.polybag-panjang').value || '';
        const lebar = item.querySelector('.polybag-lebar').value || '';
        const tinggi = item.querySelector('.polybag-tinggi').value || '';
        const stdUsing = item.querySelector('input[name="polybag_std_using[]"]').value || '';
        if (polybagId) {
            polybagIds.push(polybagId);
            polybagPanjang.push(panjang);
            polybagLebar.push(lebar);
            polybagTinggi.push(tinggi);
            polybagStdUsing.push(stdUsing);
        }
    });
    
    polybagIds.forEach((id, index) => {
        newFormData.append('polybag_ids[]', id);
        newFormData.append('polybag_panjang[]', polybagPanjang[index] || '');
        newFormData.append('polybag_lebar[]', polybagLebar[index] || '');
        newFormData.append('polybag_tinggi[]', polybagTinggi[index] || '');
        newFormData.append('polybag_std_using[]', polybagStdUsing[index] || '');
    });
    
    // Collect Rempart data
    const rempartKartonBox = [];
    const rempartPolybag = [];
    const rempartGasketDuplex = [];
    const rempartFoamSheet = [];
    const rempartHologram = [];
    const rempartLabelA = [];
    const rempartLabelB = [];
    const rempartQtyPcs = [];
    document.querySelectorAll('.rempart-item').forEach(item => {
        const kartonBox = item.querySelector('select[name="rempart_r_karton_box_id[]"]').value || '';
        const polybag = item.querySelector('select[name="rempart_r_polybag_id[]"]').value || '';
        const gasketDuplex = item.querySelector('select[name="rempart_r_gasket_duplex_id[]"]').value || '';
        const foamSheet = item.querySelector('select[name="rempart_r_foam_sheet_id[]"]').value || '';
        const hologram = item.querySelector('select[name="rempart_r_hologram_id[]"]').value || '';
        const labelA = item.querySelector('select[name="rempart_r_labela_id[]"]').value || '';
        const labelB = item.querySelector('select[name="rempart_r_labelb_id[]"]').value || '';
        const qtyPcs = item.querySelector('input[name="rempart_r_qty_pcs[]"]').value || '';
        
        // Check if at least one field has value
        if (kartonBox || polybag || gasketDuplex || foamSheet || hologram || labelA || labelB) {
            rempartKartonBox.push(kartonBox);
            rempartPolybag.push(polybag);
            rempartGasketDuplex.push(gasketDuplex);
            rempartFoamSheet.push(foamSheet);
            rempartHologram.push(hologram);
            rempartLabelA.push(labelA);
            rempartLabelB.push(labelB);
            rempartQtyPcs.push(qtyPcs);
        }
    });
    
    rempartKartonBox.forEach((val, index) => {
        newFormData.append('rempart_r_karton_box_id[]', val);
        newFormData.append('rempart_r_polybag_id[]', rempartPolybag[index] || '');
        newFormData.append('rempart_r_gasket_duplex_id[]', rempartGasketDuplex[index] || '');
        newFormData.append('rempart_r_foam_sheet_id[]', rempartFoamSheet[index] || '');
        newFormData.append('rempart_r_hologram_id[]', rempartHologram[index] || '');
        newFormData.append('rempart_r_labela_id[]', rempartLabelA[index] || '');
        newFormData.append('rempart_r_labelb_id[]', rempartLabelB[index] || '');
        newFormData.append('rempart_r_qty_pcs[]', rempartQtyPcs[index] || '');
    });

    // Collect subpart data
    const subpartIds = [];
    const subpartNama = [];
    const subpartStdPacking = [];
    const subpartUom = [];
    const subpartStdUsing = [];
    
    document.querySelectorAll('.subpart-item').forEach(item => {
        const subpartId = item.querySelector('.subpart-select').value;
        const nama = item.querySelector('.subpart-nama').value || '';
        const stdPacking = item.querySelector('.subpart-std-packing').value || '';
        const uom = item.querySelector('.subpart-uom').value || '';
        const stdUsing = item.querySelector('input[name="subpart_std_using[]"]').value || 0;
        
        if (subpartId) {
            subpartIds.push(subpartId);
            subpartNama.push(nama);
            subpartStdPacking.push(stdPacking);
            subpartUom.push(uom);
            subpartStdUsing.push(stdUsing);
        }
    });
    
    // Add subpart data to formData
    subpartIds.forEach((id, index) => {
        newFormData.append('subpart_ids[]', id);
        newFormData.append('subpart_nama[]', subpartNama[index] || '');
        newFormData.append('subpart_std_packing[]', subpartStdPacking[index] || '');
        newFormData.append('subpart_uom[]', subpartUom[index] || '');
        newFormData.append('subpart_std_using[]', subpartStdUsing[index] || 0);
    });
    
    // Map netto, brutto, runner ke database fields
    const netto = newFormData.get('netto');
    const brutto = newFormData.get('brutto');
    const runner = newFormData.get('runner');
    
    if (netto) {
        newFormData.append('N_Cav1', netto); // Map netto ke N_Cav1
    }
    if (brutto) {
        newFormData.append('Avg_Brutto', brutto); // Map brutto ke Avg_Brutto
    }
    if (runner) {
        newFormData.append('Runner', runner);
    }
    
    // Remove temporary fields
    newFormData.delete('netto');
    newFormData.delete('brutto');
    
    // Debug: log all material_std_using values before sending
    const allMaterialStdUsing = [];
    for (let [key, value] of newFormData.entries()) {
        if (key === 'material_std_using[]') {
            allMaterialStdUsing.push(value);
        }
    }
    console.log('All material_std_using[] values being sent:', allMaterialStdUsing);
    const totalSent = allMaterialStdUsing.reduce((sum, val) => sum + parseFloat(val || 0), 0);
    console.log('Total being sent:', totalSent);
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span>Menyimpan...</span>';
    
    try {
        const response = await fetch('{{ route("submaster.part.store") }}', {
            method: 'POST',
            body: newFormData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(data.message);
            window.location.href = '{{ route("submaster.part.index") }}';
        } else {
            alert('Error: ' + (data.message || 'Gagal menyimpan data'));
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan data');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});
</script>
@endsection
