@php
    /** @var \Illuminate\Pagination\LengthAwarePaginator $planningDays */
    /** @var \Illuminate\Support\Collection $mesins */
    /** @var \Illuminate\Support\Collection $molds */
    /** @var \Illuminate\Support\Collection $materials */
    /** @var array $hourSlots */

    $isEdit = isset($planningDay);

    // Kalau edit: mapping hourly untuk prefill
    $existingRuns = $isEdit ? $planningDay->runs : collect();

    $hourKey = function($dt) {
        try {
            return \Carbon\Carbon::parse($dt)->format('H:i');
        } catch (\Throwable $e) {
            return null;
        }
    };

    $mapHourly = function($rows) use ($hourKey) {
        $map = [];
        foreach ($rows as $r) {
            $k = $hourKey($r->hour_start);
            if ($k) $map[$k] = $r;
        }
        return $map;
    };

@endphp

@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">Planning Produksi</h2>
            <p class="text-gray-600 mt-1">1 halaman: header + maksimal 3 run + input 24 jam</p>
        </div>
        @if($isEdit)
            <button
                id="btnBackToList"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg transition-colors"
            >
                Kembali
            </a>
        @endif
    </div>

    @if(!$isEdit)
        {{-- LIST (mode index) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex items-center justify-between">
                <div class="text-sm text-gray-600">Buat planning baru atau edit planning existing.</div>
                @if(userCan('planning.create'))
                <button id="btnCreatePlanning" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">Tambah Planning</button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mesin/Meja</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Run</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($planningDays as $pd)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ \Carbon\Carbon::parse($pd->tanggal)->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($pd->tipe === 'assy')
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">ASSY</span>
                                    @else
                                        <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">INJECT</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($pd->tipe === 'assy')
                                        {{ $pd->meja ?? '-' }}
                                    @else
                                        {{ $pd->mesin->no_mesin ?? '-' }}
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">{{ $pd->runs->count() }} run</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <button class="btnDetailPlanning text-green-600 hover:text-green-900 mr-3" data-id="{{ $pd->id }}" title="Detail">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                    </button>
                                    @if(userCan('planning.edit'))
                                    <button class="btnEditPlanning text-blue-600 hover:text-blue-900 mr-3" data-id="{{ $pd->id }}">Edit</button>
                                    @endif
                                    @if(userCan('planning.delete'))
                                    <button class="btnDeletePlanning text-red-600 hover:text-red-900" data-id="{{ $pd->id }}">Hapus</button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">Belum ada planning.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($planningDays->hasPages())
                <div class="bg-white px-6 py-4 border-t border-gray-200 flex items-center justify-between" id="paginationInfo">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span class="font-medium">{{ $planningDays->firstItem() }}</span> sampai <span class="font-medium">{{ $planningDays->lastItem() }}</span> dari <span class="font-medium">{{ $planningDays->total() }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($planningDays->onFirstPage())
                            <button class="px-3 py-1 text-sm text-gray-500 bg-white border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed" disabled>Previous</a>
                        @else
                            <button class="pagination-btn px-3 py-1 text-sm text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50" data-url="{{ $planningDays->previousPageUrl() }}">Previous</a>
                        @endif

                        @if($planningDays->hasMorePages())
                            <button class="pagination-btn px-3 py-1 text-sm text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50" data-url="{{ $planningDays->nextPageUrl() }}">Next</a>
                        @else
                            <button class="px-3 py-1 text-sm text-gray-500 bg-white border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed" disabled>Next</a>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        {{-- Modal create quick --}}
        <div id="createModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
            <div class="bg-white rounded-lg w-full max-w-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold">Buat Planning</h3>
                    <button id="btnCloseModal" class="text-gray-500 hover:text-gray-800">X</a>
                </div>

                <form id="createPlanningForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Planning</label>
                        <select name="tipe" id="tipePlanning" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="inject">INJECT</option>
                            <option value="assy">ASSY</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Produksi (mulai 07:00)</label>
                        <input type="date" name="tanggal" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div id="mesinField">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mesin</label>
                        <select name="mesin_id" id="mesinSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih Mesin</option>
                            @foreach($mesins as $m)
                                <option value="{{ $m->id }}">{{ $m->no_mesin }}{{ $m->tonase ? ' - ' . $m->tonase . 'T' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="mejaField" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meja</label>
                        <select name="meja" id="mejaSelect" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih Meja</option>
                            @foreach($mejas ?? [] as $meja)
                                <option value="{{ $meja }}">{{ $meja }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">Lanjut Input Run</a>
                        <button type="button" id="btnCancelModal" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg">Batal</a>
                    </div>
                </form>
            </div>
        </div>

        <script>
        (function() {
            const modal = document.getElementById('createModal');
            const openBtn = document.getElementById('btnCreatePlanning');
            const closeBtn = document.getElementById('btnCloseModal');
            const cancelBtn = document.getElementById('btnCancelModal');
            const form = document.getElementById('createPlanningForm');
            const tipeSelect = document.getElementById('tipePlanning');
            const mesinField = document.getElementById('mesinField');
            const mejaField = document.getElementById('mejaField');
            const mesinSelect = document.getElementById('mesinSelect');
            const mejaSelect = document.getElementById('mejaSelect');

            // Toggle fields berdasarkan tipe
            if (tipeSelect) {
                function toggleFields() {
                    if (tipeSelect.value === 'assy') {
                        mesinField.classList.add('hidden');
                        mejaField.classList.remove('hidden');
                        mesinSelect.removeAttribute('required');
                        mesinSelect.value = ''; // Clear value
                        mejaSelect.setAttribute('required', 'required');
                    } else {
                        mesinField.classList.remove('hidden');
                        mejaField.classList.add('hidden');
                        mesinSelect.setAttribute('required', 'required');
                        mejaSelect.removeAttribute('required');
                        mejaSelect.value = ''; // Clear value
                    }
                }
                
                tipeSelect.addEventListener('change', toggleFields);
                // Initialize on page load
                toggleFields();
            }

            function open() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
            function close() { modal.classList.add('hidden'); modal.classList.remove('flex'); }

            openBtn?.addEventListener('click', open);
            closeBtn?.addEventListener('click', close);
            cancelBtn?.addEventListener('click', close);

            document.querySelectorAll('.btnDetailPlanning').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const id = btn.getAttribute('data-id');
                    const response = await fetch(`/planning/${id}/detail`);
                    const html = await response.text();
                    window.location.href = '{{ route('planning.index') }}';
                });
            });

            document.querySelectorAll('.btnEditPlanning').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const id = btn.getAttribute('data-id');
                    const response = await fetch(`/planning/${id}/edit`);
                    const html = await response.text();
                    window.location.href = '{{ route('planning.index') }}';
                });
            });

            // Delete planning
            document.querySelectorAll('.btnDeletePlanning').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    e.preventDefault();
                    const id = btn.getAttribute('data-id');
                    
                    if (!confirm('Apakah Anda yakin ingin menghapus planning ini?')) {
                        return;
                    }

                    try {
                        const formData = new FormData();
                        formData.append('_token', '{{ csrf_token() }}');
                        formData.append('_method', 'DELETE');
                        
                        const response = await fetch(`/planning/${id}`, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        const data = await response.json();
                        
                        if (data.success) {
                            alert(data.message || 'Planning berhasil dihapus');
                            // Reload halaman untuk refresh list
                            window.location.reload();
                        } else {
                            alert(data.message || 'Gagal menghapus planning');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menghapus planning');
                    }
                });
            });

            form?.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Pastikan lot_produksi terkirim dengan mengupdate hidden input sebelum submit
                for (let i = 0; i < 3; i++) {
                    const lotInput = document.getElementById(`lot-produksi-${i}`);
                    const hiddenInput = document.getElementById(`lot-produksi-hidden-${i}`);
                    if (lotInput && hiddenInput && lotInput.value) {
                        hiddenInput.value = lotInput.value;
                    }
                }
                
                const fd = new FormData(form);
                
                // Jika tipe ASSY, hapus mesin_id dari form data
                const tipe = tipeSelect?.value;
                if (tipe === 'assy') {
                    fd.delete('mesin_id');
                } else {
                    fd.delete('meja');
                }
                
                try {
                const res = await fetch('{{ route("planning.store") }}', {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                    
                    if (!res.ok) {
                        const errorText = await res.text();
                        console.error('Response error:', errorText);
                        alert('Error: ' + res.status + ' ' + res.statusText);
                        return;
                    }
                    
                const data = await res.json();
                    
                if (!data.success) {
                        console.error('Server error:', data);
                        alert(data.message || 'Gagal membuat planning: ' + (data.errors ? JSON.stringify(data.errors) : 'Unknown error'));
                    return;
                }
                    
                    console.log('Planning created successfully:', data);
                close();
                const id = data.planning_day_id;
                const response = await fetch(`/planning/${id}/edit`);
                const html = await response.text();
                window.location.href = '{{ route('planning.index') }}';
                } catch (error) {
                    console.error('Error creating planning:', error);
                    alert('Terjadi kesalahan: ' + error.message);
                }
            });

            // Handle pagination
            document.querySelectorAll('.pagination-btn').forEach(btn => {
                btn.addEventListener('click', async function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('data-url');
                    if (!url) return;

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                        const html = await response.text();
                        
                        // Parse HTML response
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        
                        // Update table body
                        const newTableBody = doc.querySelector('#tableBody');
                        if (newTableBody) {
                            document.querySelector('#tableBody').innerHTML = newTableBody.innerHTML;
                        }
                        
                        // Update pagination
                        const newPagination = doc.querySelector('#paginationInfo');
                        if (newPagination) {
                            document.querySelector('#paginationInfo').outerHTML = newPagination.outerHTML;
                        }
                        
                        // Re-attach pagination handlers
                        document.querySelectorAll('.pagination-btn').forEach(btn => {
                            btn.addEventListener('click', arguments.callee);
                        });
                    } catch (error) {
                        console.error('Error loading page:', error);
                        alert('Gagal memuat halaman: ' + error.message);
                    }
                });
            });
        })();
        </script>
    @else
        {{-- EDITOR (mode edit) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <form id="planningEditorForm" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" id="planning_day_id" value="{{ $planningDay->id }}">
                <template id="material_options_template">
                    <option value="">Pilih Material</option>
                    @foreach($materials as $bb)
                        <option value="{{ $bb->id }}">{{ $bb->nama_bahan_baku }}{{ $bb->uom ? ' (' . $bb->uom . ')' : '' }}</option>
                    @endforeach
                </template>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Planning</label>
                        <select id="tipe-planning-edit" name="tipe" required class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="inject" @selected($planningDay->tipe === 'inject')>INJECT</option>
                            <option value="assy" @selected($planningDay->tipe === 'assy')>ASSY</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Produksi (mulai 07:00)</label>
                        <input type="date" id="tanggal-produksi" name="tanggal" required class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ \Carbon\Carbon::parse($planningDay->tanggal)->format('Y-m-d') }}">
                    </div>
                    <div id="mesin-field-edit">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Mesin</label>
                        <select id="mesin-select" name="mesin_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih Mesin</option>
                            @foreach($mesins as $m)
                                <option value="{{ $m->id }}" data-no-mesin="{{ $m->no_mesin }}" @selected($planningDay->mesin_id == $m->id)>{{ $m->no_mesin }}{{ $m->tonase ? ' - ' . $m->tonase . 'T' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="meja-field-edit" class="hidden">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Meja</label>
                        <select id="meja-select-edit" name="meja" class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">Pilih Meja</option>
                            @foreach($mejas ?? [] as $meja)
                                <option value="{{ $meja }}" {{ $planningDay->meja == $meja ? 'selected' : '' }}>{{ $meja }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <input type="text" name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg" value="{{ $planningDay->status }}" placeholder="draft/approved/closed">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label>
                    <textarea name="catatan" rows="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg">{{ $planningDay->catatan }}</textarea>
                </div>

                @php
                    $maxRuns = $planningDay->tipe === 'assy' ? 1 : 3;
                @endphp
                @for($r=0; $r<$maxRuns; $r++)
                    @php
                        $run = $existingRuns->get($r);
                        $runHourlyTargetMap = $run ? $mapHourly($run->hourlyTargets) : [];
                        $runHourlyActualMap = $run ? $mapHourly($run->hourlyActuals) : [];
                    @endphp

                    <div class="border border-gray-200 rounded-lg p-4 run-container" data-run-index="{{ $r }}" data-tipe="{{ $planningDay->tipe }}">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">Run {{ $r+1 }}</h3>
                            @if($planningDay->tipe === 'inject')
                            <span class="text-xs text-gray-500">Maks 3 run per hari</span>
                            @else
                                <span class="text-xs text-gray-500">ASSY hanya 1 run</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            @if($planningDay->tipe === 'inject')
                                <div class="md:col-span-2 inject-field">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Mold</label>
                                <select class="run-mold w-full px-4 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][mold_id]" data-run-index="{{ $r }}">
                                    <option value="">- Pilih Mold -</option>
                                    @foreach($molds as $mold)
                                        <option value="{{ $mold->id }}" @selected($run && $run->mold_id == $mold->id)>
                                            {{ $mold->kode_mold ?? ('MOLD#' . $mold->id) }} | {{ $mold->part->nomor_part ?? '-' }} | {{ $mold->perusahaan->inisial_perusahaan ?? '-' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                                <div class="inject-field">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Lot Produksi</label>
                                    <input type="text" id="lot-produksi-{{ $r }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50" name="runs[{{ $r }}][lot_produksi]" value="{{ $run->lot_produksi ?? '' }}" readonly>
                                    <input type="hidden" name="runs[{{ $r }}][lot_produksi]" id="lot-produksi-hidden-{{ $r }}" value="{{ $run->lot_produksi ?? '' }}">
                            </div>
                                <div class="inject-field">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cavity (info)</label>
                                <input type="text" class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50" value="{{ $run?->mold?->cavity ?? '' }}" readonly>
                            </div>
                            @else
                                <div class="md:col-span-2 assy-field">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Part</label>
                                    <select class="run-part w-full px-4 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][part_id]" data-run-index="{{ $r }}" id="part-select-{{ $r }}" data-current-part-id="{{ $run && $run->part_id ? $run->part_id : '' }}">
                                        <option value="">- Pilih Part -</option>
                                        @if($run && $run->part_id && $run->part)
                                            <option value="{{ $run->part_id }}" selected data-part-data="{{ json_encode([
                                                'id' => $run->part->id,
                                                'nomor_part' => $run->part->nomor_part,
                                                'nama_part' => $run->part->nama_part,
                                                'box' => $run->part->box ? [
                                                    'id' => $run->part->box->id,
                                                    'nama' => $run->part->box->nama_bahan_baku,
                                                    'kode' => $run->part->box->kode_bahan_baku ?? $run->part->box->nomor_bahan_baku,
                                                    'std_using' => (string) $run->part->std_using_box,
                                                ] : null,
                                                'polybag' => $run->part->polybag ? [
                                                    'id' => $run->part->polybag->id,
                                                    'nama' => $run->part->polybag->nama_bahan_baku,
                                                    'std_using' => (string) $run->part->Std_Using_Polybag,
                                                ] : null,
                                            ]) }}">
                                                {{ $run->part->nomor_part }} - {{ $run->part->nama_part }}
                                            </option>
                                        @endif
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">Parts yang memiliki subpart untuk ASSY</p>
                                </div>
                                <div class="assy-field">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lot Produksi (Manual)</label>
                                    <input type="text" id="lot-produksi-{{ $r }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][lot_produksi]" value="{{ $run->lot_produksi ?? '' }}" placeholder="Input lot produksi">
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Start</label>
                                <input type="datetime-local" class="w-full px-4 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][start_at]" value="{{ $run?->start_at ? \Carbon\Carbon::parse($run->start_at)->format('Y-m-d\\TH:i') : '' }}">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">End</label>
                                <input type="datetime-local" class="w-full px-4 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][end_at]" value="{{ $run?->end_at ? \Carbon\Carbon::parse($run->end_at)->format('Y-m-d\\TH:i') : '' }}">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Catatan Run</label>
                            <input type="text" class="w-full px-4 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][catatan]" value="{{ $run->catatan ?? '' }}">
                        </div>

                        {{-- Box & Polybag (untuk INJECT dan ASSY) --}}
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-semibold text-gray-800 mb-3">Box</div>
                                <div class="text-xs text-gray-500 mb-2">Pilih kode box yang digunakan (opsional)</div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode Box</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][box_id]" id="box-select-{{ $r }}" data-run-index="{{ $r }}">
                                            <option value="">- Pilih Box (Opsional) -</option>
                                            @foreach($boxes as $box)
                                                <option value="{{ $box->id }}" @selected($run && $run->box_id == $box->id)>
                                                    {{ $box->nomor_bahan_baku }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Qty Box</label>
                                        <input type="number" step="0.001" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][qty_box]" id="qty-box-{{ $r }}" value="{{ $run->qty_box ?? '' }}" placeholder="Qty (opsional)">
                                        <p class="text-xs text-gray-500 mt-1">Dari std_using_box part, bisa diubah manual</p>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-semibold text-gray-800 mb-3">Polybag</div>
                                <div class="text-xs text-gray-500 mb-2">Pilih polybag yang digunakan (opsional)</div>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Polybag (LDPE)</label>
                                        <select class="w-full px-3 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][polybag_id]" id="polybag-select-{{ $r }}" data-run-index="{{ $r }}">
                                            <option value="">- Pilih Polybag (Opsional) -</option>
                                            @foreach($polybags as $polybag)
                                                <option value="{{ $polybag->id }}" @selected($run && $run->polybag_id == $polybag->id)>
                                                    {{ $polybag->nomor_bahan_baku }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Qty Polybag</label>
                                        <input type="number" step="0.001" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg" name="runs[{{ $r }}][qty_polybag]" id="qty-polybag-{{ $r }}" value="{{ $run->qty_polybag ?? '' }}" placeholder="Qty (opsional)">
                                        <p class="text-xs text-gray-500 mt-1">Dari Std_Using_Polybag part, bisa diubah manual</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($planningDay->tipe === 'inject')
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6 inject-field">
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-semibold text-gray-800 mb-2">Target per Jam (24 baris)</div>
                                <div class="max-h-[420px] overflow-auto">
                                    <table class="w-full text-sm">
                                        <thead class="sticky top-0 bg-gray-50">
                                            <tr>
                                                <th class="p-2 text-left">Jam</th>
                                                <th class="p-2 text-left">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hourSlots as $slot)
                                                @php
                                                    $val = 0;
                                                    if ($run && isset($runHourlyTargetMap[$slot['start']])) {
                                                        $val = (int) $runHourlyTargetMap[$slot['start']]->qty_target;
                                                    }
                                                @endphp
                                                <tr class="border-t">
                                                    <td class="p-2">{{ $slot['label'] }}</td>
                                                    <td class="p-2">
                                                        <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][hourly_target][{{ $slot['index'] }}]" value="{{ $val }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-semibold text-gray-800 mb-2">Actual per Jam (24 baris)</div>
                                <div class="max-h-[420px] overflow-auto">
                                    <table class="w-full text-sm">
                                        <thead class="sticky top-0 bg-gray-50">
                                            <tr>
                                                <th class="p-2 text-left">Jam</th>
                                                <th class="p-2 text-left">Qty</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($hourSlots as $slot)
                                                @php
                                                    $val = 0;
                                                    if ($run && isset($runHourlyActualMap[$slot['start']])) {
                                                        $val = (int) $runHourlyActualMap[$slot['start']]->qty_actual;
                                                    }
                                                @endphp
                                                <tr class="border-t">
                                                    <td class="p-2">{{ $slot['label'] }}</td>
                                                    <td class="p-2">
                                                        <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][hourly_actual][{{ $slot['index'] }}]" value="{{ $val }}">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6 inject-field">
                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-semibold text-gray-800 mb-3">Material</div>
                                <div class="text-xs text-gray-500 mb-2">Pilih mold dulu, material dan masterbatch akan ter-load otomatis dari SM_Part. Tinggal input qty-nya saja.</div>
                                <div class="space-y-2" id="materials-wrap-{{ $r }}">
                                    @php
                                        $existingMaterials = $run ? $run->materials : collect();
                                    @endphp
                                    @forelse($existingMaterials as $mi => $mat)
                                        <div class="grid grid-cols-12 gap-2">
                                            <div class="col-span-5">
                                                <select class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][materials][{{ $mi }}][material_id]">
                                                    <option value="">Pilih Material</option>
                                                    @foreach($materials as $bb)
                                                        <option value="{{ $bb->id }}" @selected($bb->id == $mat->material_id)>{{ $bb->nama_bahan_baku }}{{ $bb->uom ? ' (' . $bb->uom . ')' : '' }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-span-3">
                                                <input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][materials][{{ $mi }}][qty_total]" value="{{ $mat->qty_total }}" placeholder="Qty">
                                            </div>
                                            <div class="col-span-2">
                                                <input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][materials][{{ $mi }}][uom]" value="{{ $mat->uom }}" placeholder="UOM">
                                            </div>
                                            <div class="col-span-2 flex items-center">
                                                <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-xs text-gray-500">Belum ada material. Klik "Tambah".</div>
                                    @endforelse
                                </div>
                                <button type="button" class="btnAddMaterial mt-3 bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded" data-run-index="{{ $r }}">Tambah Material</a>
                            </div>

                            <div class="border border-gray-200 rounded-lg p-3">
                                <div class="font-semibold text-gray-800 mb-3">Subpart</div>
                                <div class="text-xs text-gray-500 mb-2">Pilih mold dulu, subpart akan ter-load otomatis dari SM_Part_Subpart. Tinggal input qty-nya saja.</div>

                                <div class="space-y-2" id="subparts-wrap-{{ $r }}">
                                    {{-- Tampilkan subpart yang sudah ada di database (edit mode) --}}
                                    @php
                                        $existingSubpartsInject = $run ? $run->subparts : collect();
                                    @endphp
                                    @forelse($existingSubpartsInject as $si => $sub)
                                        @php
                                            $partsubpart = $sub->partsubpart ?? null;
                                            $subpart = $partsubpart->subpart ?? null;
                                        @endphp
                                        <div class="grid grid-cols-12 gap-2 border border-gray-100 rounded p-2" data-existing-subpart>
                                            <div class="col-span-5">
                                                <div class="text-sm text-gray-800">{{ $partsubpart->urutan ?? '-' }}. {{ $subpart->nama_bahan_baku ?? '-' }}</div>
                                                <div class="text-xs text-gray-500">Std Using: <span class="font-semibold">{{ number_format($partsubpart->std_using ?? 0, 2) }}</span> {{ $subpart->uom ?? '' }}</div>
                                                <input type="hidden" name="runs[{{ $r }}][subparts][{{ $si }}][partsubpart_id]" value="{{ $partsubpart->id ?? '' }}">
                                            </div>
                                            <div class="col-span-3">
                                                <input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][subparts][{{ $si }}][qty_total]" value="{{ $sub->qty_total ?? '' }}" placeholder="Qty">
                                            </div>
                                            <div class="col-span-2">
                                                <input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][subparts][{{ $si }}][uom]" value="{{ $sub->uom ?? ($subpart->uom ?? '') }}" placeholder="UOM">
                                            </div>
                                            <div class="col-span-2 flex items-center">
                                                <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                                            </div>
                                        </div>
                                    @empty
                                        {{-- Jika tidak ada subpart yang sudah ada, tampilkan pesan atau kosong --}}
                                        @if($run && $run->mold_id)
                                            <div class="text-xs text-gray-500">Memuat subpart...</div>
                                        @endif
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- Subpart untuk ASSY --}}
                        @if($planningDay->tipe === 'assy')
                        <div class="border border-gray-200 rounded-lg p-3 mt-6 assy-field">
                            <div class="font-semibold text-gray-800 mb-3">Subpart</div>
                            <div class="text-xs text-gray-500 mb-2">Pilih part dulu, subpart akan ter-load otomatis dari SM_Part_Subpart (tipe ASSY). Tinggal input qty-nya saja.</div>
                            
                            <div class="space-y-2" id="subparts-wrap-{{ $r }}">
                                {{-- Subpart akan di-load otomatis via JavaScript saat part dipilih --}}
                                @php
                                    $existingSubparts = $run ? $run->subparts : collect();
                                @endphp
                                @forelse($existingSubparts as $si => $sub)
                                    @php
                                        $partsubpart = $sub->partsubpart ?? null;
                                        $subpart = $partsubpart->subpart ?? null;
                                    @endphp
                                    <div class="grid grid-cols-12 gap-2 border border-gray-100 rounded p-2">
                                        <div class="col-span-5">
                                            <div class="text-sm text-gray-800">{{ $partsubpart->urutan ?? '-' }}. {{ $subpart->nama_bahan_baku ?? '-' }}</div>
                                            <div class="text-xs text-gray-500">Std Using: <span class="font-semibold">{{ $partsubpart->std_using ?? '-' }}</span> {{ $subpart->uom ?? '' }}</div>
                                            <input type="hidden" name="runs[{{ $r }}][subparts][{{ $si }}][partsubpart_id]" value="{{ $partsubpart->id ?? '' }}">
                            </div>
                                        <div class="col-span-3">
                                            <input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][subparts][{{ $si }}][qty_total]" value="{{ $sub->qty_total }}" placeholder="Qty">
                        </div>
                                        <div class="col-span-2">
                                            <input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][subparts][{{ $si }}][uom]" value="{{ $sub->uom ?? ($subpart->uom ?? '') }}" placeholder="UOM">
                                        </div>
                                        <div class="col-span-2 flex items-center">
                                            <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-xs text-gray-500">Pilih part dulu untuk melihat subpart</div>
                                @endforelse
                            </div>
                        </div>
                        @endif

                        <div class="border border-gray-200 rounded-lg p-3 mt-6">
                            <div class="font-semibold text-gray-800 mb-2">Kebutuhan (opsional)</div>
                            @php $k = $run?->kebutuhan; @endphp
                            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Polybox</label>
                                    <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][kebutuhan][qty_polybox]" value="{{ $k->qty_polybox ?? 0 }}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Partisi</label>
                                    <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][kebutuhan][qty_partisi]" value="{{ $k->qty_partisi ?? 0 }}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Imfrabolt</label>
                                    <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][kebutuhan][qty_imfrabolt]" value="{{ $k->qty_imfrabolt ?? 0 }}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Karton</label>
                                    <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][kebutuhan][qty_karton]" value="{{ $k->qty_karton ?? 0 }}">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-600 mb-1">Troly</label>
                                    <input type="number" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[{{ $r }}][kebutuhan][qty_troly]" value="{{ $k->qty_troly ?? 0 }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endfor

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Simpan</button>
                    <a href="{{ route('planning.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg">Tutup</a>
                </div>
            </form>
        </div>

        <script>
        (function() {
            const planningDayId = document.getElementById('planning_day_id')?.value;
            const materialOptionsHtml = document.getElementById('material_options_template')?.innerHTML || '';

            document.getElementById('btnBackToList')?.addEventListener('click', async () => {
                const response = await fetch('/planning');
                const html = await response.text();
                window.location.href = '{{ route('planning.index') }}';
                window.location.href = '{{ route('planning.index') }}';
            });

            // add/remove row helpers
            function attachRemoveHandlers(scope) {
                (scope || document).querySelectorAll('.btnRemoveRow').forEach(btn => {
                    if (btn.dataset.bound) return;
                    btn.dataset.bound = '1';
                    btn.addEventListener('click', () => {
                        const row = btn.closest('.grid');
                        if (row) row.remove();
                    });
                });
            }
            attachRemoveHandlers();

            // Load parts berdasarkan tipe planning
            async function loadPartsByTipe() {
                const tipe = tipePlanningEdit?.value;
                
                if (!tipe || tipe !== 'assy') {
                    return; // Hanya filter untuk ASSY
                }
                
                // Filter semua part select untuk ASSY
                document.querySelectorAll('.run-part').forEach(async (select) => {
                    const runIndex = select.getAttribute('data-run-index');
                    const currentValue = select.value; // Simpan value yang sudah dipilih
                    const currentPartId = select.getAttribute('data-current-part-id') || currentValue;
                    
                    // Jangan reload jika sudah ada part yang dipilih (edit mode)
                    // Simpan current part data sebelum di-clear (untuk edit mode)
                    let savedPartData = null;
                    if (currentValue && currentValue !== '' && currentPartId && currentPartId !== '') {
                        // Cek apakah option yang dipilih masih ada di dropdown dengan value yang valid
                        const existingOption = select.querySelector(`option[value="${currentValue}"]`);
                        if (existingOption && existingOption.value !== '' && existingOption.value !== 'Memuat...' && existingOption.value !== '- Pilih Part -') {
                            // Simpan part data sebelum di-clear
                            if (existingOption.dataset.partData) {
                                savedPartData = existingOption.dataset.partData;
                                select.setAttribute('data-saved-part-data', savedPartData);
                            }
                            // Part sudah ada dan valid, tidak perlu reload (edit mode)
                            console.log('Part already selected in edit mode, skipping reload:', currentValue);
                            return;
                        }
                        // Simpan part data jika ada meskipun akan di-reload
                        if (existingOption && existingOption.dataset.partData) {
                            savedPartData = existingOption.dataset.partData;
                            select.setAttribute('data-saved-part-data', savedPartData);
                        }
                    }
                    
                    select.innerHTML = '<option value="">Memuat...</option>';
                    select.disabled = true;
                    
                    try {
                        const url = `{{ route('planning.api.partsByTipe') }}?tipe=${encodeURIComponent(tipe)}`;
                        const response = await fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });
                        
                        const data = await response.json();
                        
                        if (data.success && data.parts) {
                            let html = '<option value="">- Pilih Part -</option>';
                            
                            // Cek apakah current part ada di hasil filter
                            const currentPartId = select.getAttribute('data-current-part-id') || currentValue;
                            const currentPartInResults = data.parts.find(p => p.id == currentPartId);
                            
                            
                            data.parts.forEach(part => {
                                const selected = (currentPartId && currentPartId == part.id) ? 'selected' : '';
                                const partData = JSON.stringify(part); // Store part data in data attribute
                                html += `<option value="${part.id}" ${selected} data-part-data='${partData.replace(/'/g, "&apos;")}'>${part.nomor_part} - ${part.nama_part}</option>`;
                            });
                            
                            // Tambahkan current part jika tidak ada di hasil filter (edit mode)
                            if (currentPartId && !currentPartInResults) {
                                const savedPartData = select.getAttribute('data-saved-part-data');
                                if (savedPartData) {
                                    try {
                                        const partData = JSON.parse(savedPartData.replace(/&apos;/g, "'"));
                                        const selected = currentPartId == partData.id ? 'selected' : '';
                                        html += `<option value="${partData.id}" ${selected} data-part-data='${savedPartData.replace(/'/g, "&apos;")}'>${partData.nomor_part} - ${partData.nama_part}</option>`;
                                    } catch (e) {
                                        console.error('Error parsing saved part data:', e);
                                    }
                                }
                            }
                            
                            select.innerHTML = html;
                        } else {
                            select.innerHTML = '<option value="">- Tidak ada part untuk ASSY -</option>';
                        }
                    } catch (error) {
                        console.error('Error loading parts:', error);
                        select.innerHTML = '<option value="">- Error memuat data -</option>';
                    } finally {
                        select.disabled = false;
                    }
                });
            }

            // Toggle fields berdasarkan tipe planning
            const tipePlanningEdit = document.getElementById('tipe-planning-edit');
            const mesinFieldEdit = document.getElementById('mesin-field-edit');
            const mejaFieldEdit = document.getElementById('meja-field-edit');
            const mesinSelectEdit = document.getElementById('mesin-select');
            const mejaSelectEdit = document.getElementById('meja-select-edit');

            function toggleFieldsByTipe() {
                const tipe = tipePlanningEdit?.value;
                if (tipe === 'assy') {
                    mesinFieldEdit?.classList.add('hidden');
                    mejaFieldEdit?.classList.remove('hidden');
                    mesinSelectEdit?.removeAttribute('required');
                    mejaSelectEdit?.setAttribute('required', 'required');
                    // Hide inject fields, show assy fields
                    document.querySelectorAll('.inject-field').forEach(el => el.classList.add('hidden'));
                    document.querySelectorAll('.assy-field').forEach(el => el.classList.remove('hidden'));
                    
                    // Load parts untuk ASSY (hanya jika belum ada part yang dipilih)
                    // Jangan reload jika sudah ada part yang dipilih (edit mode)
                    const hasPartInRuns = Array.from(document.querySelectorAll('.run-part')).some(select => {
                        const currentValue = select.value;
                        return currentValue && currentValue !== '';
                    });
                    
                    if (!hasPartInRuns) {
                        loadPartsByTipe();
                    }
                } else {
                    mesinFieldEdit?.classList.remove('hidden');
                    mejaFieldEdit?.classList.add('hidden');
                    mesinSelectEdit?.setAttribute('required', 'required');
                    mejaSelectEdit?.removeAttribute('required');
                    mejaSelectEdit.value = '';
                    // Show inject fields, hide assy fields
                    document.querySelectorAll('.inject-field').forEach(el => el.classList.remove('hidden'));
                    document.querySelectorAll('.assy-field').forEach(el => el.classList.add('hidden'));
                }
            }

            tipePlanningEdit?.addEventListener('change', toggleFieldsByTipe);
            // Initialize on page load
            toggleFieldsByTipe();
            
            // Load parts saat halaman dimuat jika tipe ASSY (hanya jika belum ada part yang dipilih)
            // Jangan load jika sudah ada part yang dipilih (edit mode)
            if (tipePlanningEdit?.value === 'assy') {
                // Cek apakah sudah ada part yang dipilih
                const hasPartSelected = Array.from(document.querySelectorAll('.run-part')).some(select => {
                    const currentValue = select.value;
                    const currentPartId = select.getAttribute('data-current-part-id') || currentValue;
                    // Cek juga apakah option dengan value tersebut valid (bukan empty atau placeholder)
                    const existingOption = select.querySelector(`option[value="${currentValue}"]`);
                    return currentPartId && currentPartId !== '' && existingOption && existingOption.value !== '' && existingOption.value !== '- Pilih Part -';
                });
                
                // Hanya load jika belum ada part yang dipilih (untuk create mode)
                if (!hasPartSelected) {
                    loadPartsByTipe();
                } else {
                    console.log('Edit mode detected: Part already selected, skipping loadPartsByTipe');
                }
            }

            // Function to generate lot produksi (hanya untuk INJECT)
            function generateLotProduksi(runIndex) {
                const tipe = tipePlanningEdit?.value;
                if (tipe === 'assy') {
                    return; // ASSY tidak auto-generate lot
                }

                const tanggalInput = document.getElementById('tanggal-produksi');
                const mesinSelect = document.getElementById('mesin-select');
                const lotInput = document.getElementById(`lot-produksi-${runIndex}`);
                
                if (!tanggalInput || !mesinSelect || !lotInput) {
                    return;
                }

                const tanggal = tanggalInput.value;
                const mesinOption = mesinSelect.options[mesinSelect.selectedIndex];
                const noMesin = mesinOption?.getAttribute('data-no-mesin') || '';

                if (!tanggal || !noMesin) {
                    lotInput.value = '';
                    return;
                }

                // Parse tanggal
                const date = new Date(tanggal);
                const tanggalStr = String(date.getDate()).padStart(2, '0');
                const bulanStr = String(date.getMonth() + 1).padStart(2, '0');
                const tahunStr = String(date.getFullYear()).slice(-2);

                // Extract nomor mesin (ambil angka dari no_mesin, contoh: "MC-16" -> "16")
                const noMesinMatch = noMesin.match(/\d+/);
                const noMesinNum = noMesinMatch ? noMesinMatch[0] : '';
                const noMesinFormatted = noMesinNum.padStart(2, '0');

                // No planning = urutan run (1, 2, 3)
                const noPlanning = (runIndex + 1).toString();

                // Format: noplanning-nomesin-tanggal-bulan-tahun
                // Contoh: 116-08-01-26 (1 = no planning, 16 = no mesin, 08 = tanggal, 01 = bulan, 26 = tahun)
                const lotProduksi = `${noPlanning}${noMesinFormatted}-${tanggalStr}-${bulanStr}-${tahunStr}`;
                lotInput.value = lotProduksi;
                // Update hidden input juga agar terkirim saat submit
                const hiddenInput = document.getElementById(`lot-produksi-hidden-${runIndex}`);
                if (hiddenInput) {
                    hiddenInput.value = lotProduksi;
                }
            }

            // Update lot produksi when tanggal or mesin changes
            const tanggalInput = document.getElementById('tanggal-produksi');
            const mesinSelect = document.getElementById('mesin-select');
            
            if (tanggalInput) {
                tanggalInput.addEventListener('change', () => {
                    for (let i = 0; i < 3; i++) {
                        generateLotProduksi(i);
                    }
                });
            }

            if (mesinSelect) {
                mesinSelect.addEventListener('change', () => {
                    for (let i = 0; i < 3; i++) {
                        generateLotProduksi(i);
                    }
                });
            }

            // Generate lot produksi on page load
            setTimeout(() => {
                for (let i = 0; i < 3; i++) {
                    generateLotProduksi(i);
                }
            }, 100);

            // Auto-load materials, masterbatch, and subparts from part when mold is selected
            document.querySelectorAll('.run-mold').forEach(moldSelect => {
                moldSelect.addEventListener('change', async function() {
                    const runIndex = this.getAttribute('data-run-index');
                    const moldId = this.value;
                    
                    console.log('Mold changed:', { runIndex, moldId });
                    
                    if (!moldId) {
                        // Clear materials and subparts if mold is cleared
                        const materialsWrap = document.getElementById(`materials-wrap-${runIndex}`);
                        const subpartsWrap = document.getElementById(`subparts-wrap-${runIndex}`);
                        if (materialsWrap) materialsWrap.innerHTML = '<div class="text-xs text-gray-500">Belum ada material. Klik "Tambah".</div>';
                        if (subpartsWrap) subpartsWrap.innerHTML = '';
                        return;
                    }

                    try {
                        console.log('Fetching part data for mold:', moldId);
                        // Get tipe planning (inject/assy)
                        const tipePlanningSelect = document.querySelector('select[name="tipe"]');
                        const tipe = tipePlanningSelect ? tipePlanningSelect.value : 'inject';
                        const res = await fetch(`/planning/api/mold/${moldId}/part-data?tipe=${tipe}`);
                        const json = await res.json();
                        console.log('Part data received:', json);
                        const partData = json.data;

                        if (!partData) {
                            console.warn('No part data found');
                            return;
                        }

                        // Load Materials (Material 1, Material 2, Masterbatch)
                        const materialsWrap = document.getElementById(`materials-wrap-${runIndex}`);
                        console.log('Materials to load:', partData.materials);
                        if (partData.materials && partData.materials.length > 0) {
                        // Clear empty placeholder if exists
                            const placeholder = materialsWrap.querySelector('.text-xs.text-gray-500');
                        if (placeholder && placeholder.textContent.includes('Belum ada material')) {
                            placeholder.remove();
                        }

                        // Add materials from part
                        partData.materials.forEach((mat, idx) => {
                            // Check if material already exists
                                const existingRows = materialsWrap.querySelectorAll('.grid');
                            let materialExists = false;
                            existingRows.forEach(row => {
                                const select = row.querySelector('select[name*="[material_id]"]');
                                if (select && select.value == mat.id) {
                                    materialExists = true;
                                }
                            });

                            if (materialExists) {
                                return; // Skip if already exists
                            }

                                const currentIdx = materialsWrap.querySelectorAll('.grid').length;
                            const html = `
                                <div class="grid grid-cols-12 gap-2">
                                    <div class="col-span-5">
                                        <select class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][materials][${currentIdx}][material_id]">
                                            ${materialOptionsHtml}
                                        </select>
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][materials][${currentIdx}][qty_total]" placeholder="Qty">
                                    </div>
                                    <div class="col-span-2">
                                        <input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][materials][${currentIdx}][uom]" value="${mat.uom || ''}" placeholder="UOM">
                                    </div>
                                    <div class="col-span-2 flex items-center">
                                        <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                                    </div>
                                </div>
                            `;
                                materialsWrap.insertAdjacentHTML('beforeend', html);
                            
                            // Set selected material
                                const newRow = materialsWrap.lastElementChild;
                            const select = newRow.querySelector('select[name*="[material_id]"]');
                            if (select) {
                                select.value = mat.id;
                            }
                        });

                            attachRemoveHandlers(materialsWrap);
                        }

                        // Load Subparts - Preserve existing subparts with data (edit mode)
                        const subpartsWrap = document.getElementById(`subparts-wrap-${runIndex}`);
                        
                        // Jangan hapus subpart yang sudah ada di database (edit mode)
                        // Hanya hapus placeholder atau empty message
                        const placeholder = subpartsWrap.querySelector('.text-xs.text-gray-500');
                        if (placeholder && (placeholder.textContent.includes('Memuat subpart') || placeholder.textContent.includes('Pilih mold'))) {
                            placeholder.remove();
                        }
                        
                        if (partData.subparts && partData.subparts.length > 0) {
                            partData.subparts.forEach((row, idx) => {
                                // Cek apakah subpart ini sudah ada di form (edit mode)
                                const existingSubparts = subpartsWrap.querySelectorAll('[data-existing-subpart]');
                                let subpartExists = false;
                                existingSubparts.forEach(existing => {
                                    const partsubpartIdInput = existing.querySelector('input[name*="[partsubpart_id]"]');
                                    if (partsubpartIdInput && partsubpartIdInput.value == row.id) {
                                        subpartExists = true;
                                    }
                                });
                                
                                // Skip jika subpart sudah ada (edit mode)
                                if (subpartExists) {
                                    return;
                                }
                                
                                // Hitung index baru berdasarkan subpart yang sudah ada
                                const currentIdx = subpartsWrap.querySelectorAll('[data-existing-subpart], .grid').length;
                                const name = row.subpart?.nama || '-';
                                const uom = row.subpart?.uom || '';
                                const std = row.std_using || '-';
                    const html = `
                                    <div class="grid grid-cols-12 gap-2 border border-gray-100 rounded p-2">
                            <div class="col-span-5">
                                            <div class="text-sm text-gray-800">${row.urutan || '-'}. ${name}</div>
                                            <div class="text-xs text-gray-500">Std Using: <span class="font-semibold">${std}</span> ${uom}</div>
                                            <input type="hidden" name="runs[${runIndex}][subparts][${currentIdx}][partsubpart_id]" value="${row.id}">
                            </div>
                                        <div class="col-span-3"><input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][subparts][${currentIdx}][qty_total]" placeholder="Qty"></div>
                                        <div class="col-span-2"><input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][subparts][${currentIdx}][uom]" value="${uom}" placeholder="UOM"></div>
                            <div class="col-span-2 flex items-center">
                                <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                            </div>
                        </div>
                    `;
                                subpartsWrap.insertAdjacentHTML('beforeend', html);
                            });

                            attachRemoveHandlers(subpartsWrap);
                        } else {
                            // Jangan hapus subpart yang sudah ada jika tidak ada subpart baru
                            const existingSubparts = subpartsWrap.querySelectorAll('[data-existing-subpart]');
                            if (existingSubparts.length === 0) {
                                // Hanya tampilkan pesan jika benar-benar tidak ada subpart
                                const placeholder = subpartsWrap.querySelector('.text-xs.text-gray-500');
                                if (!placeholder || (!placeholder.textContent.includes('Tidak ada') && !placeholder.textContent.includes('Pilih mold'))) {
                                    subpartsWrap.insertAdjacentHTML('beforeend', '<div class="text-xs text-gray-500">Tidak ada subpart standard untuk part ini.</div>');
                                }
                            }
                        }

                        // Load Box & Polybag info
                        if (partData.box) {
                            const boxSelect = document.getElementById(`box-select-${runIndex}`);
                            const qtyBoxInput = document.getElementById(`qty-box-${runIndex}`);
                            if (boxSelect) {
                                boxSelect.value = partData.box.id;
                            }
                            if (qtyBoxInput && !qtyBoxInput.value) {
                                qtyBoxInput.value = partData.box.std_using || '';
                            }
                        }
                        if (partData.polybag) {
                            const polybagSelect = document.getElementById(`polybag-select-${runIndex}`);
                            const qtyPolybagInput = document.getElementById(`qty-polybag-${runIndex}`);
                            if (polybagSelect) {
                                polybagSelect.value = partData.polybag.id;
                            }
                            if (qtyPolybagInput && !qtyPolybagInput.value) {
                                qtyPolybagInput.value = partData.polybag.std_using || '';
                            }
                        }
                    } catch (error) {
                        console.error('Error loading part data:', error);
                        alert('Error loading part data: ' + error.message);
                    }
                });
            });

            // Auto-load subparts from part when part is selected (for ASSY)
            document.querySelectorAll('.run-part').forEach(partSelect => {
                partSelect.addEventListener('change', async function() {
                    const runIndex = this.getAttribute('data-run-index');
                    const partId = this.value;
                    const tipe = tipePlanningEdit?.value;
                    
                    console.log('Part changed for ASSY:', { runIndex, partId, tipe });
                    
                    // Hanya proses jika tipe ASSY
                    if (tipe !== 'assy') {
                        return;
                    }

                    if (!partId) {
                        // Clear subparts if part is cleared
                        const subpartsWrap = document.getElementById(`subparts-wrap-${runIndex}`);
                        if (subpartsWrap) subpartsWrap.innerHTML = '<div class="text-xs text-gray-500">Pilih part dulu untuk melihat subpart</div>';
                        return;
                    }

                    try {
                        console.log('Fetching subparts for part:', partId);
                        const res = await fetch(`/planning/api/part-subparts?part_id=${partId}&tipe=assy`);
                    const json = await res.json();
                        console.log('Subparts data received:', json);
                        
                        if (!json.success || !json.subparts) {
                            console.warn('No subparts data found');
                            const subpartsWrap = document.getElementById(`subparts-wrap-${runIndex}`);
                            if (subpartsWrap) subpartsWrap.innerHTML = '<div class="text-xs text-gray-500">Tidak ada subpart untuk part ini.</div>';
                        return;
                    }

                        // Load Subparts - Always clear and reload
                        const subpartsWrap = document.getElementById(`subparts-wrap-${runIndex}`);
                        if (!subpartsWrap) return;
                        
                        subpartsWrap.innerHTML = ''; // Always clear existing first
                        
                        if (json.subparts && json.subparts.length > 0) {
                            json.subparts.forEach((row, idx) => {
                                // Gunakan idx sebagai currentIdx untuk form index
                                const currentIdx = idx;
                                const name = row.subpart?.nama || '-';
                                const uom = row.subpart?.uom || '';
                                const std = row.std_using || '-';
                                const html = `
                            <div class="grid grid-cols-12 gap-2 border border-gray-100 rounded p-2">
                                <div class="col-span-5">
                                            <div class="text-sm text-gray-800">${row.urutan || '-'}. ${name}</div>
                                    <div class="text-xs text-gray-500">Std Using: <span class="font-semibold">${std}</span> ${uom}</div>
                                            <input type="hidden" name="runs[${runIndex}][subparts][${currentIdx}][partsubpart_id]" value="${row.id}">
                                </div>
                                        <div class="col-span-3"><input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][subparts][${currentIdx}][qty_total]" placeholder="Qty"></div>
                                        <div class="col-span-2"><input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][subparts][${currentIdx}][uom]" value="${uom}" placeholder="UOM"></div>
                                        <div class="col-span-2 flex items-center">
                                            <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                                        </div>
                            </div>
                        `;
                                subpartsWrap.insertAdjacentHTML('beforeend', html);
                            });

                            attachRemoveHandlers(subpartsWrap);
                        } else {
                            subpartsWrap.innerHTML = '<div class="text-xs text-gray-500">Tidak ada subpart standard untuk part ini.</div>';
                        }

                        // Load Box & Polybag info from selected part
                        // Get part info from parts list (loaded via getPartsByTipe)
                        const partOption = partSelect.options[partSelect.selectedIndex];
                        const partDataStr = partOption?.dataset?.partData;
                        if (partDataStr) {
                            try {
                                const partData = JSON.parse(partDataStr);
                                if (partData.box) {
                                    const boxSelect = document.getElementById(`box-select-${runIndex}`);
                                    const qtyBoxInput = document.getElementById(`qty-box-${runIndex}`);
                                    if (boxSelect) {
                                        boxSelect.value = partData.box.id;
                                    }
                                    if (qtyBoxInput && !qtyBoxInput.value) {
                                        qtyBoxInput.value = partData.box.std_using || '';
                                    }
                                }
                                if (partData.polybag) {
                                    const polybagSelect = document.getElementById(`polybag-select-${runIndex}`);
                                    const qtyPolybagInput = document.getElementById(`qty-polybag-${runIndex}`);
                                    if (polybagSelect) {
                                        polybagSelect.value = partData.polybag.id;
                                    }
                                    if (qtyPolybagInput && !qtyPolybagInput.value) {
                                        qtyPolybagInput.value = partData.polybag.std_using || '';
                                    }
                                }
                            } catch (e) {
                                console.error('Error parsing part data:', e);
                            }
                        }
                    } catch (error) {
                        console.error('Error loading subparts:', error);
                        alert('Error loading subparts: ' + error.message);
                    }
                });
            });

            // Add material row
            document.querySelectorAll('.btnAddMaterial').forEach(btn => {
                btn.addEventListener('click', () => {
                    const runIndex = btn.getAttribute('data-run-index');
                    const wrap = document.getElementById(`materials-wrap-${runIndex}`);
                    const idx = wrap.querySelectorAll('.grid').length;

                    const html = `
                        <div class="grid grid-cols-12 gap-2">
                            <div class="col-span-5">
                                <select class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][materials][${idx}][material_id]">
                                    ${materialOptionsHtml}
                                </select>
                            </div>
                            <div class="col-span-3">
                                <input type="number" step="0.001" min="0" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][materials][${idx}][qty_total]" placeholder="Qty">
                            </div>
                            <div class="col-span-2">
                                <input type="text" class="w-full px-2 py-1 border border-gray-300 rounded" name="runs[${runIndex}][materials][${idx}][uom]" placeholder="UOM">
                            </div>
                            <div class="col-span-2 flex items-center">
                                <button type="button" class="btnRemoveRow text-red-600 text-xs">Hapus</a>
                            </div>
                        </div>
                    `;
                    wrap.insertAdjacentHTML('beforeend', html);
                    attachRemoveHandlers(wrap);
                });
            });


            // Auto-load data for existing molds in edit mode (after all event listeners are set up)
            setTimeout(() => {
                document.querySelectorAll('.run-mold').forEach(moldSelect => {
                    if (moldSelect.value) {
                        console.log('Auto-triggering for existing mold:', moldSelect.value);
                        // Trigger change event to auto-load materials and subparts
                        const event = new Event('change', { bubbles: true });
                        moldSelect.dispatchEvent(event);
                    }
                });
            }, 300);

            // Save editor
            const form = document.getElementById('planningEditorForm');
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                // Pastikan lot_produksi terkirim dengan mengupdate hidden input sebelum submit (hanya untuk INJECT)
                const tipe = tipePlanningEdit?.value;
                if (tipe === 'inject') {
                    const maxRuns = document.querySelectorAll('.run-container').length;
                    for (let i = 0; i < maxRuns; i++) {
                        const lotInput = document.getElementById(`lot-produksi-${i}`);
                        const hiddenInput = document.getElementById(`lot-produksi-hidden-${i}`);
                        if (lotInput && hiddenInput && lotInput.value) {
                            hiddenInput.value = lotInput.value;
                        }
                    }
                }
                
                const fd = new FormData(form);
                
                // Jika tipe ASSY, hapus mesin_id dari form data
                if (tipe === 'assy') {
                    fd.delete('mesin_id');
                } else {
                    fd.delete('meja');
                }
                
                fd.append('_method', 'PUT');

                const res = await fetch(`/planning/${planningDayId}`, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!res.ok) {
                    const errorText = await res.text();
                    console.error('Response error:', errorText);
                    alert('Error: ' + res.status + ' ' + res.statusText);
                    return;
                }

                const data = await res.json();
                
                if (!data.success) {
                    console.error('Server error:', data);
                    alert(data.message || 'Gagal update planning: ' + (data.errors ? JSON.stringify(data.errors) : 'Unknown error'));
                    return;
                }
                
                console.log('Planning updated successfully:', data);
                alert(data.message || 'Planning berhasil diupdate');
            });
        })();
        </script>
    @endif
</div>
@endsection
