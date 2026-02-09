@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="mb-6">
        <a href="{{ route('submaster.part.index') }}" class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 mb-3 transition-colors">
            &larr; <span class="font-medium">Kembali</span>
        </a>
        <h2 class="text-xl font-bold text-gray-900 leading-none">Edit Part</h2>
        <p class="text-[10px] text-gray-500 mt-1.5 uppercase font-bold tracking-wider">Edit data part: {{ $part->nomor_part }}</p>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <form id="editForm" class="space-y-8">
            @csrf
            @method('PUT')
            
            {{-- Basic Info --}}
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Part *</label>
                        <input type="text" name="nomor_part" value="{{ $part->nomor_part }}" required maxlength="100" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 font-mono">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Part *</label>
                        <input type="text" name="nama_part" value="{{ $part->nama_part }}" required maxlength="255" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Customer *</label>
                        <div class="relative customer-autocomplete-wrapper">
                            <input type="text" id="customer_input" value="{{ $part->customer->nama_perusahaan ?? '' }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" autocomplete="off">
                            <input type="hidden" id="customer_id" name="customer_id" value="{{ $part->customer_id }}" required>
                            <div class="autocomplete-list hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Model Part *</label>
                        <select id="model_part" name="model_part" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="regular" {{ $part->model_part == 'regular' ? 'selected' : '' }}>Regular</option>
                            <option value="ckd" {{ $part->model_part == 'ckd' ? 'selected' : '' }}>CKD</option>
                            <option value="cbu" {{ $part->model_part == 'cbu' ? 'selected' : '' }}>CBU</option>
                            <option value="rempart" {{ $part->model_part == 'rempart' ? 'selected' : '' }}>Rempart</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Proses *</label>
                        <select id="proses" name="proses" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="inject" {{ $part->proses == 'inject' ? 'selected' : '' }}>INJECT</option>
                            <option value="assy" {{ $part->proses == 'assy' ? 'selected' : '' }}>ASSY</option>
                        </select>
                    </div>
                    <div>
                         <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                         <textarea name="keterangan" rows="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">{{ $part->keterangan }}</textarea>
                    </div>
                </div>
            </div>

            <div id="allFieldsSection">
                {{-- Cycle Time --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Cycle Time</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div id="ctInjectField">
                            <label class="block text-sm font-medium text-gray-700 mb-2">CT Inject</label>
                            <input type="number" name="CT_Inject" value="{{ $part->CT_Inject }}" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div id="ctAssyField">
                            <label class="block text-sm font-medium text-gray-700 mb-2">CT Assy</label>
                            <input type="number" name="CT_Assy" value="{{ $part->CT_Assy }}" step="0.01" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                </div>

                {{-- Material --}}
                <div id="materialSection" class="border-b border-gray-200 pb-4">
                     <h3 class="text-base font-semibold text-gray-900 mb-3">Material / Masterbatch</h3>
                     <div id="materialContainer" class="space-y-3"></div>
                     <button type="button" id="addMaterialBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">+ Tambah</button>
                </div>

                {{-- Box --}}
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Box</h3>
                    <div id="boxContainer" class="space-y-3"></div>
                    <button type="button" id="addBoxBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">+ Tambah Box</button>
                </div>

                {{-- Polybag --}}
                <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Polybag</h3>
                    <div id="polybagContainer" class="space-y-3"></div>
                    <button type="button" id="addPolybagBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">+ Tambah Polybag</button>
                </div>
                
                {{-- Subpart --}}
                 <div class="border-b border-gray-200 pb-4">
                    <h3 class="text-base font-semibold text-gray-900 mb-3">Subpart</h3>
                    <div id="subpartContainer" class="space-y-3"></div>
                    <button type="button" id="addSubpartBtn" class="mt-3 px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded text-xs">+ Tambah Subpart</button>
                </div>

                {{-- Specs --}}
                <div class="border-b border-gray-200 pb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Specs</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><label class="text-sm font-medium">Netto</label><input type="number" name="N_Cav1" value="{{ $part->N_Cav1 }}" step="0.001" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="text-sm font-medium">Runner</label><input type="number" name="Runner" value="{{ $part->Runner }}" step="0.001" class="w-full border rounded px-3 py-2"></div>
                        <div><label class="text-sm font-medium">Avg Brutto</label><input type="number" name="Avg_Brutto" value="{{ $part->Avg_Brutto }}" step="0.001" class="w-full border rounded px-3 py-2"></div>
                        <div>
                             <label class="text-sm font-medium">Warna Label</label>
                             <select name="Warna_Label_Packing" class="w-full border rounded px-3 py-2">
                                 <option value="">-</option>
                                 @foreach(['putih','kuning','merah','biru','hijau','hitam','buram'] as $w)
                                     <option value="{{ $w }}" {{ $part->Warna_Label_Packing == $w ? 'selected' : '' }}>{{ ucwords($w) }}</option>
                                 @endforeach
                             </select>
                        </div>
                        <div><label class="text-sm font-medium">QTY Packing</label><input type="number" name="QTY_Packing_Box" value="{{ $part->QTY_Packing_Box }}" class="w-full border rounded px-3 py-2"></div>
                    </div>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex items-center gap-4 pt-4 border-t border-gray-200">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Simpan Perubahan</button>
                <a href="{{ route('submaster.part.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">Batal</a>
            </div>
        </form>
    </div>
</div>

{{-- Templates for JS --}}
<template id="materialTemplate">
    <div class="material-item border border-gray-200 rounded p-3 bg-gray-50 flex gap-2 items-end">
        <div class="flex-1 grid grid-cols-3 gap-2">
             <select name="material_types[]" class="material-type-select border rounded px-2 py-1 text-xs"><option value="material">Material</option><option value="masterbatch">Masterbatch</option></select>
             <div class="relative material-autocomplete-wrapper"><input type="text" class="material-input w-full border rounded px-2 py-1 text-xs" placeholder="Cari..."><input type="hidden" name="material_ids[]" class="material-select"> <div class="autocomplete-list hidden absolute z-50 bg-white border shadow-lg w-full max-h-40 overflow-y-auto"></div></div>
             <input type="number" name="material_std_using[]" step="0.01" class="w-full border rounded px-2 py-1 text-xs" placeholder="%">
        </div>
        <button type="button" class="text-red-600 remove-btn">&times;</button>
    </div>
</template>
<template id="boxTemplate">
    <div class="box-item border border-gray-200 rounded p-3 bg-gray-50 flex gap-2 items-end">
        <div class="flex-1 grid grid-cols-4 gap-2">
             <div class="relative box-autocomplete-wrapper col-span-4 md:col-span-1"><input type="text" class="box-input w-full border rounded px-2 py-1 text-xs" placeholder="Cari Box..."><input type="hidden" name="box_ids[]" class="box-select"> <div class="autocomplete-list hidden absolute z-50 bg-white border shadow-lg w-full max-h-40 overflow-y-auto"></div></div>
             <input type="number" readonly class="box-panjang bg-gray-100 border rounded px-2 py-1 text-xs" placeholder="P">
             <input type="number" readonly class="box-lebar bg-gray-100 border rounded px-2 py-1 text-xs" placeholder="L">
             <input type="number" readonly class="box-tinggi bg-gray-100 border rounded px-2 py-1 text-xs" placeholder="T">
        </div>
        <button type="button" class="text-red-600 remove-btn">&times;</button>
    </div>
</template>
<template id="polybagTemplate">
    <div class="polybag-item border border-gray-200 rounded p-3 bg-gray-50 flex gap-2 items-end">
        <div class="flex-1 grid grid-cols-5 gap-2">
             <div class="relative polybag-autocomplete-wrapper col-span-5 md:col-span-1"><input type="text" class="polybag-input w-full border rounded px-2 py-1 text-xs" placeholder="Cari..."><input type="hidden" name="polybag_ids[]" class="polybag-select"> <div class="autocomplete-list hidden absolute z-50 bg-white border shadow-lg w-full max-h-40 overflow-y-auto"></div></div>
             <input type="number" readonly class="polybag-panjang bg-gray-100 border rounded px-2 py-1 text-xs" placeholder="P">
             <input type="number" readonly class="polybag-lebar bg-gray-100 border rounded px-2 py-1 text-xs" placeholder="L">
             <input type="number" readonly class="polybag-tinggi bg-gray-100 border rounded px-2 py-1 text-xs" placeholder="T">
             <input type="number" name="polybag_std_using[]" class="border rounded px-2 py-1 text-xs" placeholder="Std">
        </div>
        <button type="button" class="text-red-600 remove-btn">&times;</button>
    </div>
</template>
<template id="subpartTemplate">
    <div class="subpart-item border border-gray-200 rounded p-3 bg-gray-50 flex gap-2 items-end">
        <div class="flex-1 grid grid-cols-2 gap-2">
             <div class="relative subpart-autocomplete-wrapper"><input type="text" class="subpart-input w-full border rounded px-2 py-1 text-xs" placeholder="Cari..."><input type="hidden" name="subpart_ids[]" class="subpart-select"> <div class="autocomplete-list hidden absolute z-50 bg-white border shadow-lg w-full max-h-40 overflow-y-auto"></div></div>
             <input type="number" name="subpart_std_using[]" class="border rounded px-2 py-1 text-xs" placeholder="Std Using">
        </div>
        <button type="button" class="text-red-600 remove-btn">&times;</button>
    </div>
</template>

<script>
    const customersData = @json($customers);
    const materialsData = @json($materials);
    const masterbatchesData = @json($masterbatchesData);
    const boxesData = @json($boxes);
    const polybagsData = @json($polybags);
    const subpartsData = @json($subpartMaterials);
    
    // Existing Data
    const existingMaterials = @json($part->partMaterials);
    const existingBoxes = @json($part->partBoxes);
    const existingPolybags = @json($part->partPolybags);
    const existingSubparts = @json($part->partSubparts);

    // Simple Autocomplete Function
    function setupAutocomplete(wrapper, dataSrc, onSelect) {
        let input = wrapper.querySelector('input[type="text"]');
        let hidden = wrapper.querySelector('input[type="hidden"]');
        let list = wrapper.querySelector('.autocomplete-list');
        
        input.addEventListener('input', function() {
            const val = this.value.toLowerCase();
            const data = typeof dataSrc === 'function' ? dataSrc() : dataSrc;
            const matches = data.filter(item => {
                // Handle different data structures (label vs nama_bahan_baku mostly handled in controller by mapping to label or standardization)
                // In controller I mapped some but not all. Let's assume standardized in JS or handle here.
                let label = item.label || item.nama_bahan_baku || item.nama_perusahaan || (item.nomor_bahan_baku + ' ' + (item.nama_bahan_baku||''));
                if(item.box) label = item.nomor_bahan_baku + ' ' + (item.box.kode_box||'');
                if(item.polybag) label = item.nomor_bahan_baku;
                item._label = label; // cache it
                return label.toLowerCase().includes(val);
            }).slice(0, 15);
            
            list.innerHTML = '';
            matches.forEach(m => {
                const div = document.createElement('div');
                div.className = 'px-3 py-2 hover:bg-blue-50 cursor-pointer text-xs border-b';
                div.textContent = m._label;
                div.onclick = () => {
                    input.value = m._label;
                    hidden.value = m.id;
                    list.classList.add('hidden');
                    if(onSelect) onSelect(m);
                };
                list.appendChild(div);
            });
            list.classList.remove('hidden');
        });
        
        document.addEventListener('click', e => { if(!wrapper.contains(e.target)) list.classList.add('hidden'); });
    }

    // Init Logic
    document.addEventListener('DOMContentLoaded', () => {
        // Customer Auto
        setupAutocomplete(document.querySelector('.customer-autocomplete-wrapper'), customersData);
        
        // --- Materials ---
        const matContainer = document.getElementById('materialContainer');
        const matTmpl = document.getElementById('materialTemplate');
        
        function addMaterialRow(data = null) {
            const clone = matTmpl.content.cloneNode(true);
            const item = clone.querySelector('.material-item');
            matContainer.appendChild(item);
            
            const typeSelect = item.querySelector('.material-type-select');
            const wrapper = item.querySelector('.material-autocomplete-wrapper');
            const input = item.querySelector('.material-input');
            const hidden = item.querySelector('.material-select');
            const std = item.querySelector('input[name="material_std_using[]"]');
            
            if(data) {
                typeSelect.value = data.material_type;
                hidden.value = data.material_id;
                std.value = data.std_using;
                // Pre-fill input text
                const src = data.material_type === 'masterbatch' ? masterbatchesData : materialsData;
                const found = src.find(x => x.id == data.material_id);
                if(found) input.value = found.label || found.material?.nama_bahan_baku || '-';
            }
            
            setupAutocomplete(wrapper, () => typeSelect.value === 'masterbatch' ? masterbatchesData : materialsData, (sel) => {
                 // on select
            });
            
            item.querySelector('.remove-btn').onclick = () => item.remove();
        }
        
        document.getElementById('addMaterialBtn').onclick = () => addMaterialRow();
        if(existingMaterials.length) existingMaterials.forEach(addMaterialRow); else addMaterialRow(); 
        
        // --- Box ---
        const boxContainer = document.getElementById('boxContainer');
        const boxTmpl = document.getElementById('boxTemplate');
        
        function addBoxRow(data = null) {
            const clone = boxTmpl.content.cloneNode(true);
            const item = clone.querySelector('.box-item');
            boxContainer.appendChild(item);
            
            const wrapper = item.querySelector('.box-autocomplete-wrapper');
            const input = item.querySelector('.box-input');
            const hidden = item.querySelector('.box-select');
            const p = item.querySelector('.box-panjang');
            const l = item.querySelector('.box-lebar');
            const t = item.querySelector('.box-tinggi');
            
            if(data) {
                hidden.value = data.box_id;
                p.value = data.panjang; l.value = data.lebar; t.value = data.tinggi;
                 // Pre-fill input
                 const found = boxesData.find(x => x.id == data.box_id);
                 if(found) input.value = found.nomor_bahan_baku + (found.box?.kode_box ? ' ('+found.box.kode_box+')' : '');
            }
            
            setupAutocomplete(wrapper, boxesData, (s) => {
                 p.value = s.box?.panjang||''; l.value = s.box?.lebar||''; t.value = s.box?.tinggi||'';
            });
            item.querySelector('.remove-btn').onclick = () => item.remove();
        }
        document.getElementById('addBoxBtn').onclick = () => addBoxRow();
        if(existingBoxes.length) existingBoxes.forEach(addBoxRow); else addBoxRow();

        // --- Polybag ---
        // (Similar logic, abbreviated for space...)
         const polyContainer = document.getElementById('polybagContainer');
         const polyTmpl = document.getElementById('polybagTemplate');
         function addPolyRow(data=null){
             const clone = polyTmpl.content.cloneNode(true);
             const item = clone.querySelector('.polybag-item');
             polyContainer.appendChild(item);
             const wrapper = item.querySelector('.polybag-autocomplete-wrapper');
             const input = item.querySelector('.polybag-input');
             const hidden = item.querySelector('.polybag-select');
             const std = item.querySelector('input[name="polybag_std_using[]"]');
             const p = item.querySelector('.polybag-panjang');
             const l = item.querySelector('.polybag-lebar');
             const t = item.querySelector('.polybag-tinggi');
             
             if(data) {
                 hidden.value = data.polybag_id;
                 std.value = data.std_using;
                 const found = polybagsData.find(x => x.id == data.polybag_id);
                 if(found) {
                     input.value = found.nomor_bahan_baku;
                     p.value = found.polybag?.panjang; l.value = found.polybag?.lebar; t.value = found.polybag?.tinggi;
                 }
             }
             setupAutocomplete(wrapper, polybagsData, (s) => {
                 p.value = s.polybag?.panjang||''; l.value = s.polybag?.lebar||''; t.value = s.polybag?.tinggi||'';

             });
             item.querySelector('.remove-btn').onclick = () => item.remove();
         }
         document.getElementById('addPolybagBtn').onclick = () => addPolyRow();
         if(existingPolybags.length) existingPolybags.forEach(addPolyRow); else addPolyRow();

         // --- Subpart ---
         const subContainer = document.getElementById('subpartContainer');
         const subTmpl = document.getElementById('subpartTemplate');
         function addSubRow(data=null){
             const clone = subTmpl.content.cloneNode(true);
             const item = clone.querySelector('.subpart-item');
             subContainer.appendChild(item);
             const wrapper = item.querySelector('.subpart-autocomplete-wrapper');
             const input = item.querySelector('.subpart-input');
             const hidden = item.querySelector('.subpart-select');
             const std = item.querySelector('input[name="subpart_std_using[]"]');
             
             if(data) {
                 hidden.value = data.subpart_id;
                 std.value = data.std_using;
                 const found = subpartsData.find(x => x.id == data.subpart_id);
                 if(found) input.value = found.label || found.subpart?.nama_bahan_baku;
             }
             setupAutocomplete(wrapper, subpartsData, null);
             item.querySelector('.remove-btn').onclick = () => item.remove();
         }
         document.getElementById('addSubpartBtn').onclick = () => addSubRow();
         if(existingSubparts.length) existingSubparts.forEach(addSubRow); else addSubRow();

         // Toggle Logic
         const prosesSelect = document.getElementById('proses');
         function toggle() {
             const v = prosesSelect.value;
             document.getElementById('allFieldsSection').style.display = v ? 'block' : 'none';
             if(v === 'inject') {
                 document.getElementById('ctInjectField').style.display='block';
                 document.getElementById('ctAssyField').style.display='none';
                 document.getElementById('materialSection').style.display='block';
             } else {
                 document.getElementById('ctInjectField').style.display='none';
                 document.getElementById('ctAssyField').style.display='block';
                 document.getElementById('materialSection').style.display='none';
             }
         }
         prosesSelect.onchange = toggle;
         toggle();
         
         // Submit
         document.getElementById('editForm').onsubmit = async(e) => {
             e.preventDefault();
             // Validation here if needed
             const formData = new FormData(e.target);
             // Convert FormData to JSON or submit via fetch
             // Since we use method PUT, we can use fetch
             
             // Trick for Laravel PUT via FormData
             formData.append('_method', 'PUT'); 
             
             try {
                const res = await fetch(e.target.action, {
                    method: 'POST', // Use POST for FormData with _method=PUT
                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
                    body: formData
                });
                const json = await res.json();
                if(json.success) {
                    alert('Berhasil diperbarui');
                    window.location.href = "{{ route('submaster.part.index') }}";
                } else {
                    alert('Gagal: ' + json.message);
                }
             } catch(err) {
                 alert('Error: ' + err);
             }
         }
    });
</script>
@endsection
