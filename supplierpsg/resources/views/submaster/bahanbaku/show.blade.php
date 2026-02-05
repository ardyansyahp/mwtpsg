@extends('layout.app')

@section('content')
<div class="fade-in max-w-4xl mx-auto mt-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Detail Bahan Baku</h2>
            <div class="space-x-2">
                @if(userCan('master.bahanbaku.edit'))
                    <a href="{{ route('master.bahanbaku.edit', $bahanbaku->id) }}" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700 transition">Edit</a>
                @endif
                <a href="{{ route('master.bahanbaku.index') }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200 transition">Kembali</a>
            </div>
        </div>

        <div class="p-6">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-1 space-y-6">
                    {{-- Basic Info --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">Informasi Utama</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                            <div>
                                <dt class="text-xs text-gray-500">Kategori</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $bahanbaku->kategori_label }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Nomor Bahan Baku</dt>
                                <dd class="mt-1 text-sm font-bold text-gray-900 font-mono">{{ $bahanbaku->nomor_bahan_baku ?? '-' }}</dd>
                            </div>
                            <div class="col-span-2">
                                <dt class="text-xs text-gray-500">Nama Bahan Baku</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $bahanbaku->nama_bahan_baku }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Supplier</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $bahanbaku->supplier?->nama_perusahaan ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $bahanbaku->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $bahanbaku->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    {{-- Detail Info --}}
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3 border-t pt-4">Detail Spesifikasi</h3>
                        <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-4">
                            @php $detail = $bahanbaku->detail; @endphp
                            
                            @if(in_array($bahanbaku->kategori, ['box', 'layer', 'polybag', 'rempart']))
                                <div>
                                    <dt class="text-xs text-gray-500">Jenis</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ strtoupper(str_replace('_', ' ', $detail->jenis ?? '-')) }}</dd>
                                </div>
                            @endif

                            @if($bahanbaku->kategori === 'box' && $detail?->kode_box)
                                <div>
                                    <dt class="text-xs text-gray-500">Kode Box</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $detail->kode_box }}</dd>
                                </div>
                            @endif

                            @if(isset($detail->panjang))
                                <div>
                                    <dt class="text-xs text-gray-500">Dimensi (PxLxT)</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ $detail->panjang ?? '-' }} x 
                                        {{ $detail->lebar ?? '-' }} x 
                                        {{ $detail->tinggi ?? '-' }} cm
                                    </dd>
                                </div>
                            @endif

                            @if(isset($detail->std_packing))
                                <div>
                                    <dt class="text-xs text-gray-500">Standard Packing</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        {{ number_format($detail->std_packing, 2) }} {{ $detail->uom }} 
                                        @if($detail->jenis_packing)
                                            <span class="text-gray-500">({{ $detail->jenis_packing }})</span>
                                        @endif
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </div>
                </div>

                {{-- QR Code Section --}}
                <div class="w-full md:w-1/3 flex flex-col items-center justify-center border-l border-gray-200 pl-0 md:pl-8">
                     <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">QR Code</h3>
                     <div class="bg-white p-4 border border-blue-100 rounded-lg shadow-sm flex flex-col items-center justify-center">
                        <div id="qrcode"></div>
                     </div>
                     <p class="text-xs text-gray-400 mt-2 text-center">{{ $qrCode }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var qrText = "{{ $qrCode }}";
        if (qrText && qrText !== '-') {
            new QRCode(document.getElementById("qrcode"), {
                text: qrText,
                width: 150,
                height: 150,
                colorDark : "#000000",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.H
            });
        } else {
             document.getElementById("qrcode").innerHTML = '<span class="text-gray-400 text-xs italic">No QR Data</span>';
        }
    });
</script>
@endpush
