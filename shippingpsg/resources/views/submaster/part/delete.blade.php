@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('submaster.part.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-indigo-600 gap-2 mb-2 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            <span class="font-medium">Kembali</span>
        </a>

        <h2 class="text-3xl font-bold text-gray-800">Hapus Part</h2>
        <p class="text-gray-600 mt-1">Konfirmasi penghapusan data part</p>
    </div>

    {{-- Confirmation Card --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="mb-6">
            <div class="flex items-center justify-center w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 text-center mb-2">
                Apakah Anda yakin?
            </h3>
            <p class="text-sm text-gray-600 text-center mb-4">
                Data part <strong>{{ $part->nomor_part }} - {{ $part->nama_part }}</strong> akan dihapus secara permanen.
            </p>
            @if(($part->childParts && $part->childParts->count() > 0) || ($molds && $molds->count() > 0) || ($childMolds && $childMolds->count() > 0))
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700 font-semibold">
                            ⚠️ Peringatan: Part ini memiliki data terkait yang juga akan ikut terhapus
                        </p>
                        <div class="mt-2 space-y-2">
                            @if($part->childParts && $part->childParts->count() > 0)
                            <div>
                                <p class="text-sm text-yellow-700 font-medium">
                                    • {{ $part->childParts->count() }} part ASSY terkait:
                                </p>
                                <ul class="mt-1 ml-4 list-disc list-inside text-sm text-yellow-700">
                                    @foreach($part->childParts as $childPart)
                                        <li><strong>{{ $childPart->nomor_part }}</strong> - {{ $childPart->nama_part }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if($molds && $molds->count() > 0)
                            <div>
                                <p class="text-sm text-yellow-700 font-medium">
                                    • {{ $molds->count() }} mold terkait dengan part ini:
                                </p>
                                <ul class="mt-1 ml-4 list-disc list-inside text-sm text-yellow-700">
                                    @foreach($molds as $mold)
                                        <li><strong>{{ $mold->kode_mold ?? $mold->mold_id ?? 'Mold #' . $mold->id }}</strong> - {{ $mold->nomor_mold ?? '-' }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                            @if($childMolds && $childMolds->count() > 0)
                            <div>
                                <p class="text-sm text-yellow-700 font-medium">
                                    • {{ $childMolds->count() }} mold terkait dengan part ASSY di atas
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <p class="text-sm text-gray-700"><strong>Nomor Part:</strong> {{ $part->nomor_part }}</p>
                <p class="text-sm text-gray-700 mt-1"><strong>Nama Part:</strong> {{ $part->nama_part }}</p>
                @if($part->customer)
                    <p class="text-sm text-gray-700 mt-1"><strong>Customer:</strong> {{ $part->customer->nama_perusahaan }}</p>
                @endif
                @if($part->tipe_part)
                    <p class="text-sm text-gray-700 mt-1"><strong>Tipe:</strong> {{ $part->tipe_part }}</p>
                @endif
                <p class="text-sm text-gray-700 mt-1"><strong>Model Part:</strong> {{ strtoupper($part->model_part) }}</p>
                <p class="text-sm text-gray-700 mt-1"><strong>Proses:</strong> {{ strtoupper($part->proses) }}</p>
                @if($part->created_at)
                    <p class="text-sm text-gray-700 mt-1"><strong>Created At:</strong> {{ $part->created_at->format('d M Y H:i') }}</p>
                @endif
            </div>
            <p class="text-xs text-red-600 text-center">
                Tindakan ini tidak dapat dibatalkan.
            </p>
        </div>

        <form id="deleteForm" class="flex items-center justify-center gap-4">
            @csrf
            @method('DELETE')
            <input type="hidden" id="part_id" value="{{ $part->id }}">
            
            <button 
                type="submit" 
                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors flex items-center gap-2"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Ya, Hapus</span>
            </button>
            <a href="{{ route('submaster.part.index') }}" class="text-gray-600 hover:text-gray-800 font-medium px-4 py-2 transition-colors">
                Batal
            </a>
        </form>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('deleteForm');
    if (!form) return;
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        @php
            $hasRelatedData = ($part->childParts && $part->childParts->count() > 0) || 
                              ($molds && $molds->count() > 0) || 
                              ($childMolds && $childMolds->count() > 0);
            
            $confirmMessage = '';
            if ($hasRelatedData) {
                $confirmMessage = '⚠️ PERINGATAN!\n\n' .
                    'Part ini memiliki data terkait yang juga akan ikut terhapus:\n\n';
                
                if ($part->childParts && $part->childParts->count() > 0) {
                    $confirmMessage .= '• ' . $part->childParts->count() . ' part ASSY terkait:\n';
                    foreach ($part->childParts as $childPart) {
                        $confirmMessage .= '  - ' . $childPart->nomor_part . ' - ' . $childPart->nama_part . '\n';
                    }
                    $confirmMessage .= '\n';
                }
                
                if ($molds && $molds->count() > 0) {
                    $confirmMessage .= '• ' . $molds->count() . ' mold terkait dengan part ini\n';
                }
                
                if ($childMolds && $childMolds->count() > 0) {
                    $confirmMessage .= '• ' . $childMolds->count() . ' mold terkait dengan part ASSY di atas\n';
                }
                
                $confirmMessage .= '\nSemua data di atas akan ikut terhapus.\n\n' .
                    'Apakah Anda yakin ingin melanjutkan?';
            } else {
                $confirmMessage = 'Apakah Anda yakin ingin menghapus data ini?';
            }
        @endphp
        
        @if($hasRelatedData)
        let confirmMessage = @json($confirmMessage);
        if (!confirm(confirmMessage)) {
            return;
        }
        @else
        if (!confirm(@json($confirmMessage))) {
            return;
        }
        @endif
        
        const partId = document.getElementById('part_id').value;
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span>Menghapus...</span>';
        
        try {
            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('_method', 'DELETE');
            
            const response = await fetch(`/submaster/part/${partId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                alert(data.message);
                window.location.href = '{{ route('submaster.part.index') }}';
            } else {
                alert('Error: ' + (data.message || 'Gagal menghapus data'));
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus data');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });
})();
</script>
@endsection
