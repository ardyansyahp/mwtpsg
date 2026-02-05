@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-3xl font-bold text-gray-800">Detail Part</h2>
        <p class="text-gray-600 mt-1">Informasi lengkap part</p>
    </div>

    {{-- Main Content --}}
    <div class="space-y-6">
        {{-- Informasi Dasar --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informasi Dasar
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Part</label>
                    <p class="text-base font-semibold text-gray-900 font-mono">{{ $part->nomor_part }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Nama Part</label>
                    <p class="text-base font-semibold text-gray-900">{{ $part->nama_part }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Customer</label>
                    <p class="text-base text-gray-900">{{ $part->customer ? $part->customer->nama_perusahaan : '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tipe Part</label>
                    @php
                        $tipePart = $part->tipe_id;
                        // Jika tipe_id null pada part ASSY, tapi punya relasi ke part INJECT, pakai dari part INJECT
                        if (empty($tipePart) && $part->parentPart) {
                            $tipePart = $part->parentPart->tipe_id;
                        }
                    @endphp
                    <p class="text-base text-gray-900">{{ $tipePart ?: '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Model Part</label>
                    <p class="text-base text-gray-900">
                        <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-sm font-medium uppercase">
                            {{ $part->model_part }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Proses</label>
                    <p class="text-base text-gray-900">
                        @if($part->parentPart)
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-md text-sm font-medium uppercase mr-1">INJECT</span>
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-md text-sm font-medium uppercase">ASSY</span>
                        @else
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-md text-sm font-medium uppercase">
                                {{ strtoupper($part->proses) }}
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Cycle Time --}}
        @php
            // Jika part punya parent (ASSY), ambil CT_Inject dari parent (INJECT) dan CT_Assy dari part ini (ASSY)
            // Jika part tidak punya parent, hanya tampilkan CT dari part ini
            $ctInject = $part->CT_Inject;
            $ctAssy = $part->CT_Assy;
            if($part->parentPart) {
                $ctInject = $part->parentPart->CT_Inject ?? $part->CT_Inject;
                $ctAssy = $part->CT_Assy;
            }
        @endphp
        @if($ctInject || $ctAssy)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Cycle Time
                @if($part->parentPart)
                    <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                @endif
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                @if($ctInject)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">CT Inject</label>
                    <p class="text-base text-gray-900">{{ number_format($ctInject, 2) . ' detik' }}</p>
                </div>
                @endif
                @if($ctAssy)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">CT Assy</label>
                    <p class="text-base text-gray-900">{{ number_format($ctAssy, 2) . ' detik' }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Material / Masterbatch --}}
        @php
            // Logika baru: Jika ASSY punya data, pakai ASSY. Jika ASSY kosong, pakai INJECT (fallback)
            // Jika kedua-duanya punya data, tampilkan semua
            $allMaterials = collect($part->partMaterials ?? []);
            if($part->parentPart) {
                // Jika part ini (ASSY) tidak punya data, pakai data dari parent (INJECT)
                if($allMaterials->isEmpty() && $part->parentPart->partMaterials) {
                    $allMaterials = collect($part->parentPart->partMaterials);
                } 
                // Jika kedua-duanya punya data, gabungkan
                elseif(!$allMaterials->isEmpty() && $part->parentPart->partMaterials) {
                    $allMaterials = $allMaterials->merge($part->parentPart->partMaterials);
                }
            }
        @endphp
        @if($allMaterials->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Material / Masterbatch
                @if($part->parentPart)
                    @php
                        $hasAssyMaterial = $part->partMaterials && $part->partMaterials->count() > 0;
                        $hasInjectMaterial = $part->parentPart->partMaterials && $part->parentPart->partMaterials->count() > 0;
                    @endphp
                    @if($hasAssyMaterial && $hasInjectMaterial)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                    @elseif($hasAssyMaterial)
                        <span class="ml-2 text-sm font-normal text-gray-500">(ASSY)</span>
                    @elseif($hasInjectMaterial)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT)</span>
                    @endif
                @endif
            </h3>
            <div class="mt-4 space-y-4">
                @foreach($allMaterials as $partMaterial)
                @php
                    // Tentukan dari part mana (INJECT atau ASSY)
                    $isFromParent = false;
                    if ($part->parentPart) {
                        // Jika part punya parent, cek apakah material ini dari parent
                        $isFromParent = $part->parentPart->partMaterials->contains('id', $partMaterial->id);
                        $sourcePart = $isFromParent ? 'INJECT' : 'ASSY';
                    } else {
                        // Jika part tidak punya parent, sesuai proses part itu sendiri
                        $sourcePart = strtoupper($part->proses);
                    }
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tipe</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 {{ $partMaterial->material_type == 'material' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }} rounded-md text-sm font-medium uppercase">
                                    {{ $partMaterial->material_type }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Material/Masterbatch</label>
                            <p class="text-base text-gray-900 font-semibold">
                                {{ $partMaterial->material ? ($partMaterial->material->material->nama_bahan_baku ?? $partMaterial->material->nama_bahan_baku ?? '-') : '-' }}
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dari Part</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 {{ $sourcePart == 'INJECT' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }} rounded-md text-sm font-medium uppercase">
                                    {{ $sourcePart }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Std Using (%)</label>
                            <p class="text-base text-gray-900">{{ $partMaterial->std_using ? number_format($partMaterial->std_using, 2) . '%' : '-' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Box --}}
        @php
            // Logika baru: Jika ASSY punya data, pakai ASSY. Jika ASSY kosong, pakai INJECT (fallback)
            // Jika kedua-duanya punya data, tampilkan semua
            $allBoxes = collect($part->partBoxes ?? []);
            if($part->parentPart) {
                // Jika part ini (ASSY) tidak punya data, pakai data dari parent (INJECT)
                if($allBoxes->isEmpty() && $part->parentPart->partBoxes) {
                    $allBoxes = collect($part->parentPart->partBoxes);
                } 
                // Jika kedua-duanya punya data, gabungkan
                elseif(!$allBoxes->isEmpty() && $part->parentPart->partBoxes) {
                    $allBoxes = $allBoxes->merge($part->parentPart->partBoxes);
                }
            }
        @endphp
        @if($allBoxes->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Box
                @if($part->parentPart)
                    <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                @endif
            </h3>
            <div class="mt-4 space-y-4">
                @foreach($allBoxes as $partBox)
                @php
                    // Tentukan dari part mana (INJECT atau ASSY)
                    $isFromParent = false;
                    if ($part->parentPart) {
                        // Jika part punya parent, cek apakah box ini dari parent
                        $isFromParent = $part->parentPart->partBoxes->contains('id', $partBox->id);
                        $sourcePart = $isFromParent ? 'INJECT' : 'ASSY';
                    } else {
                        // Jika part tidak punya parent, sesuai proses part itu sendiri
                        $sourcePart = strtoupper($part->proses);
                    }
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
                        @if($partBox->box)
                        @php
                            $boxDetail = $partBox->box->box ?? null;
                        @endphp
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jenis Box</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-md text-sm font-medium uppercase">
                                    {{ $boxDetail->jenis ?? $partBox->jenis_box ?? '-' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Kode Box</label>
                            <p class="text-base text-gray-900 font-mono">{{ $boxDetail->kode_box ?? $partBox->kode_box ?? '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dimensi</label>
                            <p class="text-base text-gray-900">
                                @php
                                    $panjang = $boxDetail->panjang ?? $partBox->panjang ?? null;
                                    $lebar = $boxDetail->lebar ?? $partBox->lebar ?? null;
                                    $tinggi = $boxDetail->tinggi ?? $partBox->tinggi ?? null;
                                @endphp
                                @if($panjang && $lebar && $tinggi)
                                    {{ number_format($panjang, 2) }} x {{ number_format($lebar, 2) }} x {{ number_format($tinggi, 2) }} cm
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @else
                        <div></div>
                        <div></div>
                        <div></div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dari Part</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 {{ $isFromParent ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }} rounded-md text-sm font-medium uppercase">
                                    {{ $sourcePart }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Polybag --}}
        @php
            // Logika baru: Jika ASSY punya data, pakai ASSY. Jika ASSY kosong, pakai INJECT (fallback)
            // Jika kedua-duanya punya data, tampilkan semua
            $allPolybags = collect($part->partPolybags ?? []);
            if($part->parentPart) {
                // Jika part ini (ASSY) tidak punya data, pakai data dari parent (INJECT)
                if($allPolybags->isEmpty() && $part->parentPart->partPolybags) {
                    $allPolybags = collect($part->parentPart->partPolybags);
                } 
                // Jika kedua-duanya punya data, gabungkan
                elseif(!$allPolybags->isEmpty() && $part->parentPart->partPolybags) {
                    $allPolybags = $allPolybags->merge($part->parentPart->partPolybags);
                }
            }
        @endphp
        @if($allPolybags->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                Polybag
                @if($part->parentPart)
                    @php
                        $hasAssyPolybag = $part->partPolybags && $part->partPolybags->count() > 0;
                        $hasInjectPolybag = $part->parentPart->partPolybags && $part->parentPart->partPolybags->count() > 0;
                    @endphp
                    @if($hasAssyPolybag && $hasInjectPolybag)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                    @elseif($hasAssyPolybag)
                        <span class="ml-2 text-sm font-normal text-gray-500">(ASSY)</span>
                    @elseif($hasInjectPolybag)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT)</span>
                    @endif
                @endif
            </h3>
            <div class="mt-4 space-y-4">
                @foreach($allPolybags as $partPolybag)
                @php
                    // Tentukan dari part mana (INJECT atau ASSY)
                    $isFromParent = false;
                    if ($part->parentPart) {
                        // Jika part punya parent, cek apakah polybag ini dari parent
                        $isFromParent = $part->parentPart->partPolybags->contains('id', $partPolybag->id);
                        $sourcePart = $isFromParent ? 'INJECT' : 'ASSY';
                    } else {
                        // Jika part tidak punya parent, sesuai proses part itu sendiri
                        $sourcePart = strtoupper($part->proses);
                    }
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">

                        @php
                            $polybagDetail = $partPolybag->bahanBaku ? $partPolybag->bahanBaku->polybag : ($partPolybag->polybag ? $partPolybag->polybag->polybag : null);
                        @endphp
                        @if($polybagDetail)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jenis</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-md text-sm font-medium uppercase">
                                    {{ $polybagDetail->jenis ?? 'LDPE' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dimensi</label>
                            <p class="text-base text-gray-900">
                                @if($polybagDetail->panjang && $polybagDetail->lebar && $polybagDetail->tinggi)
                                    {{ number_format($polybagDetail->panjang, 2) }} x {{ number_format($polybagDetail->lebar, 2) }} x {{ number_format($polybagDetail->tinggi, 2) }} cm
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        @else
                        <div></div>
                        <div></div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dari Part</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 {{ $isFromParent ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }} rounded-md text-sm font-medium uppercase">
                                    {{ $sourcePart }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Std Using</label>
                            <p class="text-base text-gray-900">{{ $partPolybag->std_using ? number_format($partPolybag->std_using, 2) : '-' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Layer --}}
        @php
            // Logika baru: Jika ASSY punya data, pakai ASSY. Jika ASSY kosong, pakai INJECT (fallback)
            // Jika kedua-duanya punya data, tampilkan semua
            $allLayers = collect($part->partLayers ?? []);
            if($part->parentPart) {
                // Jika part ini (ASSY) tidak punya data, pakai data dari parent (INJECT)
                if($allLayers->isEmpty() && $part->parentPart->partLayers) {
                    $allLayers = collect($part->parentPart->partLayers);
                } 
                // Jika kedua-duanya punya data, gabungkan
                elseif(!$allLayers->isEmpty() && $part->parentPart->partLayers) {
                    $allLayers = $allLayers->merge($part->parentPart->partLayers);
                }
            }
        @endphp
        @if($allLayers->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Layer
                @if($part->parentPart)
                    @php
                        $hasAssyLayer = $part->partLayers && $part->partLayers->count() > 0;
                        $hasInjectLayer = $part->parentPart->partLayers && $part->parentPart->partLayers->count() > 0;
                    @endphp
                    @if($hasAssyLayer && $hasInjectLayer)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                    @elseif($hasAssyLayer)
                        <span class="ml-2 text-sm font-normal text-gray-500">(ASSY)</span>
                    @elseif($hasInjectLayer)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT)</span>
                    @endif
                @endif
            </h3>
            <div class="mt-4 space-y-4">
                @foreach($allLayers as $layer)
                @php
                    // Tentukan dari part mana (INJECT atau ASSY)
                    $isFromParent = false;
                    if ($part->parentPart) {
                        // Jika part punya parent, cek apakah layer ini dari parent
                        $isFromParent = $part->parentPart->partLayers->contains('id', $layer->id);
                        $sourcePart = $isFromParent ? 'INJECT' : 'ASSY';
                    } else {
                        // Jika part tidak punya parent, sesuai proses part itu sendiri
                        $sourcePart = strtoupper($part->proses);
                    }
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Layer {{ $layer->layer_number }}</label>
                            <p class="text-base text-gray-900 font-semibold">
                                {{ $layer->bahanBaku ? $layer->bahanBaku->nama_bahan_baku : '-' }}
                            </p>
                            @if($layer->bahanBaku && $layer->bahanBaku->layer)
                            <p class="text-sm text-gray-600 mt-1">
                                <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-md text-xs font-medium">
                                    {{ $layer->bahanBaku->layer->jenis ?? '-' }}
                                </span>
                            </p>
                            @if($layer->bahanBaku->layer->panjang && $layer->bahanBaku->layer->lebar && $layer->bahanBaku->layer->tinggi)
                            <p class="text-sm text-gray-600 mt-1">
                                {{ number_format($layer->bahanBaku->layer->panjang, 2) }} x {{ number_format($layer->bahanBaku->layer->lebar, 2) }} x {{ number_format($layer->bahanBaku->layer->tinggi, 2) }} cm
                            </p>
                            @endif
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dari Part</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 {{ $isFromParent ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }} rounded-md text-sm font-medium uppercase">
                                    {{ $sourcePart }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Std Using</label>
                            <p class="text-base text-gray-900">{{ $layer->std_using ? number_format($layer->std_using, 2) : '-' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Subpart --}}
        @php
            // Logika baru: Jika ASSY punya data, pakai ASSY. Jika ASSY kosong, pakai INJECT (fallback)
            // Jika kedua-duanya punya data, tampilkan semua
            $allSubparts = collect($part->partSubparts ?? []);
            if($part->parentPart) {
                // Jika part ini (ASSY) tidak punya data, pakai data dari parent (INJECT)
                if($allSubparts->isEmpty() && $part->parentPart->partSubparts) {
                    $allSubparts = collect($part->parentPart->partSubparts);
                } 
                // Jika kedua-duanya punya data, gabungkan
                elseif(!$allSubparts->isEmpty() && $part->parentPart->partSubparts) {
                    $allSubparts = $allSubparts->merge($part->parentPart->partSubparts);
                }
            }
        @endphp
        @if($allSubparts->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Subpart
                @if($part->parentPart)
                    @php
                        $hasAssySubpart = $part->partSubparts && $part->partSubparts->count() > 0;
                        $hasInjectSubpart = $part->parentPart->partSubparts && $part->parentPart->partSubparts->count() > 0;
                    @endphp
                    @if($hasAssySubpart && $hasInjectSubpart)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                    @elseif($hasAssySubpart)
                        <span class="ml-2 text-sm font-normal text-gray-500">(ASSY)</span>
                    @elseif($hasInjectSubpart)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT)</span>
                    @endif
                @endif
            </h3>
            <div class="mt-4 space-y-4">
                @foreach($allSubparts as $subpart)
                @php
                    // Tentukan dari part mana (INJECT atau ASSY)
                    $isFromParent = false;
                    if ($part->parentPart) {
                        // Jika part punya parent, cek apakah subpart ini dari parent
                        $isFromParent = $part->parentPart->partSubparts->contains('id', $subpart->id);
                        $sourcePart = $isFromParent ? 'INJECT' : 'ASSY';
                    } else {
                        // Jika part tidak punya parent, sesuai proses part itu sendiri
                        $sourcePart = strtoupper($part->proses);
                    }
                @endphp
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Subpart</label>
                            <p class="text-base text-gray-900 font-semibold">
                                {{ $subpart->nama ?? ($subpart->subpart ? $subpart->subpart->nama_bahan_baku : '-') }}
                            </p>
                        </div>
                        @if($subpart->subpart && $subpart->subpart->nomor_bahan_baku)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nomor Bahan Baku</label>
                            <p class="text-base text-gray-900 font-mono">{{ $subpart->subpart->nomor_bahan_baku }}</p>
                        </div>
                        @else
                        <div></div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dari Part</label>
                            <p class="text-base text-gray-900">
                                <span class="px-2 py-1 {{ $sourcePart == 'INJECT' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }} rounded-md text-sm font-medium uppercase">
                                    {{ $sourcePart }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Std Using</label>
                            <p class="text-base text-gray-900">{{ $subpart->std_using ? number_format($subpart->std_using, 2) : '-' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Label & Packing --}}
        @php
            $labelPackingItems = [];

            // 1. Data dari Part ini
            if (!empty($part->Warna_Label_Packing) || !empty($part->QTY_Packing_Box)) {
                $labelPackingItems[] = [
                    'source' => strtoupper($part->proses),
                    'warna' => $part->Warna_Label_Packing,
                    'qty' => $part->QTY_Packing_Box,
                    'is_current' => true
                ];
            }

            // 2. Data dari Parent Part (INJECT) jika ada
            if ($part->parentPart) {
                if (!empty($part->parentPart->Warna_Label_Packing) || !empty($part->parentPart->QTY_Packing_Box)) {
                    $labelPackingItems[] = [
                        'source' => 'INJECT',
                        'warna' => $part->parentPart->Warna_Label_Packing,
                        'qty' => $part->parentPart->QTY_Packing_Box,
                        'is_current' => false
                    ];
                }
            }
        @endphp

        @if(count($labelPackingItems) > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                Label & Packing
                @if(count($labelPackingItems) > 1)
                    <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                @elseif(count($labelPackingItems) === 1 && isset($labelPackingItems[0]['source']))
                    <span class="ml-2 text-sm font-normal text-gray-500">({{ $labelPackingItems[0]['source'] }})</span>
                @endif
            </h3>
            
            <div class="space-y-6">
                @foreach($labelPackingItems as $item)
                <div class="border border-gray-200 rounded-lg p-4 {{ $item['source'] == 'ASSY' ? 'bg-green-50' : 'bg-blue-50' }}">
                    <div class="mb-3">
                        <span class="px-2 py-1 {{ $item['source'] == 'INJECT' ? 'bg-blue-200 text-blue-800' : 'bg-green-200 text-green-800' }} rounded-md text-xs font-bold uppercase tracking-wider">
                            {{ $item['source'] }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Warna Label Packing</label>
                            <p class="text-base text-gray-900">
                                @if($item['warna'])
                                    @php
                                        $warnaClasses = [
                                            'putih' => 'bg-white text-gray-800 border border-gray-300',
                                            'kuning' => 'bg-yellow-100 text-yellow-800',
                                            'merah' => 'bg-red-100 text-red-800',
                                            'biru' => 'bg-blue-100 text-blue-800',
                                            'hijau' => 'bg-green-100 text-green-800',
                                            'hitam' => 'bg-gray-800 text-white',
                                            'buram' => 'bg-gray-300 text-gray-700',
                                        ];
                                        $warnaClass = $warnaClasses[strtolower($item['warna'])] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="px-3 py-1.5 {{ $warnaClass }} rounded-md text-sm font-medium capitalize shadow-sm">
                                        {{ $item['warna'] }}
                                    </span>
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">QTY Packing Box</label>
                            <p class="text-base text-gray-900 font-semibold">{{ $item['qty'] ?: '-' }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Rempart --}}
        @php
            // Logika baru: Jika ASSY punya data, pakai ASSY. Jika ASSY kosong, pakai INJECT (fallback)
            // Jika kedua-duanya punya data, tampilkan semua
            $allRemparts = collect($part->partRemparts ?? []);
            if($part->parentPart) {
                // Jika part ini (ASSY) tidak punya data, pakai data dari parent (INJECT)
                if($allRemparts->isEmpty() && $part->parentPart->partRemparts) {
                    $allRemparts = collect($part->parentPart->partRemparts);
                } 
                // Jika kedua-duanya punya data, gabungkan
                elseif(!$allRemparts->isEmpty() && $part->parentPart->partRemparts) {
                    $allRemparts = $allRemparts->merge($part->parentPart->partRemparts);
                }
            }
        @endphp
        @if($allRemparts->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Rempart
                @if($part->parentPart)
                    @php
                        $hasAssyRempart = $part->partRemparts && $part->partRemparts->count() > 0;
                        $hasInjectRempart = $part->parentPart->partRemparts && $part->parentPart->partRemparts->count() > 0;
                    @endphp
                    @if($hasAssyRempart && $hasInjectRempart)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                    @elseif($hasAssyRempart)
                        <span class="ml-2 text-sm font-normal text-gray-500">(ASSY)</span>
                    @elseif($hasInjectRempart)
                        <span class="ml-2 text-sm font-normal text-gray-500">(INJECT)</span>
                    @endif
                @endif
            </h3>
            <div class="mt-4 space-y-4">
                @foreach($allRemparts as $partRempart)
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @if($partRempart->rempartKartonBox)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Karton Box (P0-D0)</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartKartonBox->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->rempartPolybag)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Polybag (P0-P0)</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartPolybag->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->rempartGasketDuplex)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Gasket Duplex (P0-LD)</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartGasketDuplex->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->rempartFoamSheet)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Foam Sheet (P0-S0)</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartFoamSheet->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->rempartHologram)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Hologram (P0-H0)</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartHologram->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->rempartLabelA)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Label A</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartLabelA->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->rempartLabelB)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Label B</label>
                            <p class="text-base text-gray-900">{{ $partRempart->rempartLabelB->nama_bahan_baku }}</p>
                        </div>
                        @endif
                        @if($partRempart->R_Qty_Pcs)
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Qty Pcs</label>
                            <p class="text-base text-gray-900">{{ $partRempart->R_Qty_Pcs }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Weight --}}
        @php
            // Ambil weight dari parent (INJECT) jika ada, atau dari part ini
            $netto = $part->N_Cav1;
            $brutto = $part->Avg_Brutto;
            $runner = $part->Runner;
            if($part->parentPart) {
                $netto = $part->parentPart->N_Cav1 ?? $part->N_Cav1;
                $brutto = $part->parentPart->Avg_Brutto ?? $part->Avg_Brutto;
                $runner = $part->parentPart->Runner ?? $part->Runner;
            }
        @endphp
        @if($netto || $brutto || $runner)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                </svg>
                Weight
                @if($part->parentPart)
                    <span class="ml-2 text-sm font-normal text-gray-500">(INJECT + ASSY)</span>
                @endif
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                @if($netto)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Netto</label>
                    <p class="text-base text-gray-900 font-semibold">{{ number_format($netto, 3) . ' g' }}</p>
                </div>
                @endif
                @if($brutto)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Brutto</label>
                    <p class="text-base text-gray-900 font-semibold">{{ number_format($brutto, 3) . ' g' }}</p>
                </div>
                @endif
                @if($runner)
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Runner</label>
                    <p class="text-base text-gray-900 font-semibold">{{ number_format($runner, 3) . ' g' }}</p>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- Metadata --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
                <svg class="w-5 h-5 inline-block mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Informasi Sistem
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Pada</label>
                    <p class="text-base text-gray-900">{{ $part->created_at ? $part->created_at->format('d M Y H:i:s') : '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Diupdate Pada</label>
                    <p class="text-base text-gray-900">{{ $part->updated_at ? $part->updated_at->format('d M Y H:i:s') : '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-end gap-4 pt-4">
            <a href="{{ route('submaster.part.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors"
            >
                Tutup
            </a>
        </div>
    </div>
</div>
@endsection
