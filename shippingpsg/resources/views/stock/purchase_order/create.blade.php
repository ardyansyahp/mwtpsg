@extends('layout.app')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Styling Select2 agar mirip Tailwind input */
    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #d1d5db !important; /* border-gray-300 */
        border-radius: 0.5rem !important; /* rounded-lg */
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        top: 8px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding-left: 16px !important; /* px-4 */
        color: #111827 !important; /* text-gray-900 */
        line-height: normal !important;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('stock.po.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors" title="Kembali">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span class="font-medium">Kembali</span>
            </a>
            <h2 class="text-3xl font-bold text-gray-800">Tambah Purchase Order</h2>
            <p class="text-gray-600 mt-1">Buat PO baru secara manual</p>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form action="{{ route('stock.po.store') }}" method="POST" class="space-y-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Part --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Part <span class="text-red-500">*</span>
                    </label>
                    <select name="part_id" class="w-full select2-part" required>
                        <option value="">Pilih Part</option>
                        @foreach($parts as $part)
                            <option value="{{ $part->id }}" {{ old('part_id') == $part->id ? 'selected' : '' }}>
                                {{ $part->nomor_part }} - {{ $part->nama_part }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PO Number --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor PO <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="po_number" 
                        value="{{ old('po_number') }}" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contoh: PO-2026-001"
                    >
                </div>

                {{-- Qty --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Qty <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="qty" 
                        value="{{ old('qty') }}" 
                        min="1" 
                        required 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0"
                    >
                </div>

                {{-- Month --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Bulan <span class="text-red-500">*</span>
                    </label>
                    <select name="month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" required>
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ old('month') == $m ? 'selected' : '' }}>
                                {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                            </option>
                        @endfor
                    </select>
                </div>

                {{-- Year --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tahun <span class="text-red-500">*</span>
                    </label>
                    <input 
                        type="number" 
                        name="year" 
                        value="{{ old('year', date('Y')) }}" 
                        required 
                        min="2020" 
                        max="2099"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                {{-- Delivery Frequency --}}
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Delivery Frequency (Opsional)
                    </label>
                    <input 
                        type="text" 
                        name="delivery_frequency" 
                        value="{{ old('delivery_frequency') }}" 
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="Contoh: 4x/Month"
                    >
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 mt-6">
                <a href="{{ route('stock.po.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition-colors font-medium">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors shadow-sm font-medium">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-part').select2({
            placeholder: "Pilih Part",
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endpush
@endsection
