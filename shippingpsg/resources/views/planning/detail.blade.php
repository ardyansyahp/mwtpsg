<div class="p-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">Detail Planning Produksi</h1>
        <button href="{{ route(\'planning.index\') }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg">
            Tutup
        </a>
    </div>

    @if($planningDay)
    <div class="bg-white rounded-lg shadow-md p-6">
        {{-- Informasi Dasar Planning --}}
        <div class="mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Informasi Dasar Planning</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal Produksi</label>
                    <p class="text-gray-800">{{ \Carbon\Carbon::parse($planningDay->tanggal)->format('d/m/Y') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Tipe Planning</label>
                    <p class="text-gray-800">
                        @if($planningDay->tipe === 'assy')
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">ASSY</span>
                        @else
                            <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">INJECT</span>
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">
                        {{ $planningDay->tipe === 'assy' ? 'Meja' : 'Mesin' }}
                    </label>
                    <p class="text-gray-800">
                        @if($planningDay->tipe === 'assy')
                            {{ $planningDay->meja ?? '-' }}
                        @else
                            {{ $planningDay->mesin->no_mesin ?? '-' }}
                        @endif
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Status</label>
                    <p class="text-gray-800">{{ $planningDay->status ?? '-' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600 mb-1">Jumlah Run</label>
                    <p class="text-gray-800">{{ $planningDay->runs->count() }} run</p>
                </div>
                @if($planningDay->catatan)
                <div class="md:col-span-3">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Catatan</label>
                    <p class="text-gray-800">{{ $planningDay->catatan }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Detail Run --}}
        @foreach($planningDay->runs as $runIndex => $run)
        <div class="mb-8 border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">
                Run {{ $run->urutan_run ?? ($runIndex + 1) }}
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Informasi Run --}}
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-3">Informasi Run</h3>
                    <div class="space-y-3">
                        @if($planningDay->tipe === 'inject' && $run->mold)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Mold</label>
                                <p class="text-gray-800">{{ $run->mold->kode_mold ?? ('MOLD#' . $run->mold->id) }}</p>
                                <p class="text-xs text-gray-500">{{ $run->mold->perusahaan->inisial_perusahaan ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Cavity</label>
                                <p class="text-gray-800">{{ $run->mold->cavity ?? '-' }}</p>
                            </div>
                        @elseif($planningDay->tipe === 'assy' && $run->part)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Part</label>
                                <p class="text-gray-800">{{ $run->part->nomor_part }} - {{ $run->part->nama_part }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Lot Produksi</label>
                            <p class="text-gray-800">{{ $run->lot_produksi ?? '-' }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Start</label>
                                <p class="text-gray-800 text-sm">{{ $run->start_at ? \Carbon\Carbon::parse($run->start_at)->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">End</label>
                                <p class="text-gray-800 text-sm">{{ $run->end_at ? \Carbon\Carbon::parse($run->end_at)->format('d/m/Y H:i') : '-' }}</p>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Qty Target Total</label>
                            <p class="text-gray-800">{{ number_format($run->qty_target_total ?? 0) }}</p>
                        </div>

                        @if($run->catatan)
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Catatan Run</label>
                            <p class="text-gray-800">{{ $run->catatan }}</p>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Box & Polybag --}}
                <div>
                    <h3 class="text-md font-semibold text-gray-700 mb-3">Box & Polybag</h3>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Box</label>
                            @if($run->box)
                                <p class="text-gray-800">{{ $run->box->nomor_bahan_baku }} - {{ $run->box->nama_bahan_baku }}</p>
                                <p class="text-gray-800 font-semibold">Qty: {{ number_format($run->qty_box ?? 0, 2) }}</p>
                            @else
                                <p class="text-gray-500">-</p>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Polybag</label>
                            @if($run->polybag)
                                <p class="text-gray-800">{{ $run->polybag->nomor_bahan_baku }} - {{ $run->polybag->nama_bahan_baku }}</p>
                                <p class="text-gray-800 font-semibold">Qty: {{ number_format($run->qty_polybag ?? 0, 2) }}</p>
                            @else
                                <p class="text-gray-500">-</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Material (INJECT only) --}}
            @if($planningDay->tipe === 'inject' && $run->materials->count() > 0)
            <div class="mt-6">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Material</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left">No</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Material</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Qty Total</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">UOM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($run->materials as $matIndex => $mat)
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">{{ $matIndex + 1 }}</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $mat->material->nama_bahan_baku ?? '-' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format($mat->qty_total ?? 0, 3) }}</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $mat->uom ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Subpart --}}
            @if($run->subparts->count() > 0)
            <div class="mt-6">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Subpart</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="border border-gray-300 px-3 py-2 text-left">Urutan</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">Subpart</th>
                                <th class="border border-gray-300 px-3 py-2 text-right">Qty Total</th>
                                <th class="border border-gray-300 px-3 py-2 text-left">UOM</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($run->subparts as $sub)
                            @php
                                $partsubpart = $sub->partsubpart ?? null;
                                $subpart = $partsubpart->subpart ?? null;
                            @endphp
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">{{ $partsubpart->urutan ?? '-' }}</td>
                                <td class="border border-gray-300 px-3 py-2">
                                    {{ $subpart->nama_bahan_baku ?? '-' }}
                                    @if($partsubpart)
                                        <span class="text-xs text-gray-500">(Std: {{ number_format($partsubpart->std_using ?? 0, 2) }})</span>
                                    @endif
                                </td>
                                <td class="border border-gray-300 px-3 py-2 text-right">{{ number_format($sub->qty_total ?? 0, 3) }}</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $sub->uom ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Kebutuhan --}}
            @if($run->kebutuhan)
            <div class="mt-6">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Kebutuhan</h3>
                <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Polybox</label>
                        <p class="text-gray-800">{{ number_format($run->kebutuhan->qty_polybox ?? 0) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Partisi</label>
                        <p class="text-gray-800">{{ number_format($run->kebutuhan->qty_partisi ?? 0) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Imfrabolt</label>
                        <p class="text-gray-800">{{ number_format($run->kebutuhan->qty_imfrabolt ?? 0) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Karton</label>
                        <p class="text-gray-800">{{ number_format($run->kebutuhan->qty_karton ?? 0) }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Troly</label>
                        <p class="text-gray-800">{{ number_format($run->kebutuhan->qty_troly ?? 0) }}</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Target & Actual per Jam (INJECT only) --}}
            @if($planningDay->tipe === 'inject' && $run->hourlyTargets->count() > 0)
            <div class="mt-6">
                <h3 class="text-md font-semibold text-gray-700 mb-3">Target & Actual per Jam</h3>
                <div class="overflow-x-auto max-h-96">
                    <table class="w-full text-sm border-collapse border border-gray-300">
                        <thead class="bg-gray-100 sticky top-0">
                            <tr>
                                <th class="border border-gray-300 px-2 py-2 text-left">Jam</th>
                                <th class="border border-gray-300 px-2 py-2 text-right">Target</th>
                                <th class="border border-gray-300 px-2 py-2 text-right">Actual</th>
                                <th class="border border-gray-300 px-2 py-2 text-right">%</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $totalTarget = 0;
                                $totalActual = 0;
                            @endphp
                            @foreach($run->hourlyTargets->sortBy('hour_start') as $target)
                            @php
                                $actual = $run->hourlyActuals->firstWhere('hour_start', $target->hour_start);
                                $targetQty = $target->qty_target ?? 0;
                                $actualQty = $actual->qty_actual ?? 0;
                                $percentage = $targetQty > 0 ? ($actualQty / $targetQty) * 100 : 0;
                                $totalTarget += $targetQty;
                                $totalActual += $actualQty;
                            @endphp
                            <tr>
                                <td class="border border-gray-300 px-2 py-1">
                                    {{ \Carbon\Carbon::parse($target->hour_start)->format('H:i') }} - 
                                    {{ \Carbon\Carbon::parse($target->hour_end)->format('H:i') }}
                                </td>
                                <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($targetQty) }}</td>
                                <td class="border border-gray-300 px-2 py-1 text-right">{{ number_format($actualQty) }}</td>
                                <td class="border border-gray-300 px-2 py-1 text-right">
                                    <span class="{{ $percentage >= 100 ? 'text-green-600' : ($percentage >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($percentage, 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                            <tr class="bg-gray-50 font-semibold">
                                <td class="border border-gray-300 px-2 py-2">Total</td>
                                <td class="border border-gray-300 px-2 py-2 text-right">{{ number_format($totalTarget) }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right">{{ number_format($totalActual) }}</td>
                                <td class="border border-gray-300 px-2 py-2 text-right">
                                    @php
                                        $totalPercentage = $totalTarget > 0 ? ($totalActual / $totalTarget) * 100 : 0;
                                    @endphp
                                    <span class="{{ $totalPercentage >= 100 ? 'text-green-600' : ($totalPercentage >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format($totalPercentage, 1) }}%
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-lg shadow-md p-6 text-center text-gray-500">
        Planning tidak ditemukan.
    </div>
    @endif
</div>
@endsection
