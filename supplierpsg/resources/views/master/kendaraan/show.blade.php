@extends('layout.app')

@section('content')
<div class="fade-in max-w-2xl mx-auto mt-10">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Detail Kendaraan</h2>
            <div class="space-x-2">
                @if(userCan('master.kendaraan.edit'))
                    <a href="{{ route('master.kendaraan.edit', $kendaraan->id) }}" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700 transition">Edit</a>
                @endif
                <a href="{{ route('master.kendaraan.index') }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200 transition">Kembali</a>
            </div>
        </div>

        <div class="p-6">
            <div class="flex flex-col md:flex-row gap-8">
                <div class="flex-1">
                     <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Informasi Kendaraan</h3>
                     <dl class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm text-gray-500">Nomor Polisi</dt>
                            <dd class="text-sm font-bold text-gray-900 border p-2 rounded bg-gray-50 border-gray-200 text-center">{{ $kendaraan->nopol_kendaraan }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm text-gray-500">Jenis</dt>
                            <dd class="text-sm text-gray-900">{{ $kendaraan->jenis_kendaraan }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm text-gray-500">Merk</dt>
                            <dd class="text-sm text-gray-900">{{ $kendaraan->merk_kendaraan }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <dt class="text-sm text-gray-500">Tahun Pembuatan</dt>
                            <dd class="text-sm text-gray-900">{{ $kendaraan->tahun_kendaraan }}</dd>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                             <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $kendaraan->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $kendaraan->status ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                     </dl>
                </div>

                {{-- QR Code Section --}}
                <div class="w-full md:w-1/3 flex flex-col items-center justify-center border-l border-gray-200 pl-0 md:pl-8">
                     <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">QR Code</h3>
                     <div class="bg-white p-4 border border-blue-100 rounded-lg shadow-sm flex flex-col items-center justify-center">
                        <div id="qrcode"></div>
                     </div>
                     <p class="text-xs text-gray-400 mt-2 text-center">{{ $kendaraan->qrcode }}</p>
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
        new QRCode(document.getElementById("qrcode"), {
            text: "{{ $kendaraan->qrcode }}",
            width: 150,
            height: 150,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    });
</script>
@endpush
