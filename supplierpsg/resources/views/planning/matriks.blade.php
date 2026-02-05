@extends('layout.app')

@section('content')
<div class="fade-in">
    {{-- Header --}}
    <div class="mb-4 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Matriks Planning Produksi</h2>
                <p class="text-gray-600 mt-1 text-sm">
                    Tanggal: {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }} 
                    @if(count($matriksDataInject) > 0 || count($matriksDataAssy) > 0)
                        | <span class="text-green-600 font-semibold">INJECT: {{ count($matriksDataInject) }} baris</span>
                        | <span class="text-blue-600 font-semibold">ASSY: {{ count($matriksDataAssy) }} baris</span>
                    @else
                        | <span class="text-red-600">Tidak ada data</span>
                    @endif
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-700">Pilih Tanggal:</label>
                    <input 
                        type="date" 
                        name="tanggal" 
                        id="tanggalFilter"
                        value="{{ \Carbon\Carbon::parse($tanggal)->format('Y-m-d') }}"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm"
                    >
                </div>
                <button 
                    onclick="window.print()"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors text-sm flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </button>
            </div>
        </div>
    </div>

    {{-- Matriks INJECT --}}
    @if(count($matriksDataInject) > 0)
    <div class="mb-6">
        <div class="bg-green-100 border-l-4 border-green-600 p-4 mb-3">
            <h3 class="text-lg font-bold text-green-800">PLANNING INJECT ({{ count($matriksDataInject) }} baris)</h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto overflow-y-auto" style="max-height: calc(50vh - 100px);">
            <table class="w-full border-collapse" style="min-width: 2800px; font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px;">
                <thead class="sticky top-0 z-30">
                    {{-- Header Row 1 - Main Headers --}}
                    <tr class="bg-gray-100">
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold sticky left-0 bg-gray-100 z-40" style="min-width: 60px; white-space: nowrap;">NO URUT<br>MOLD</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 90px; white-space: nowrap;">TANGGAL</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold sticky left-[60px] bg-gray-100 z-40" style="min-width: 110px; white-space: nowrap;">MESIN</th>
                    <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 120px; white-space: nowrap;">LOT<br>PRODUKSI</th>
                    <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 140px; white-space: nowrap;">KODE<br>PART</th>
                    <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 160px; white-space: nowrap;">NOMOR<br>BARANG</th>
                    <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 220px; white-space: nowrap;">NAMA<br>BARANG</th>
                    <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold bg-yellow-100" style="min-width: 110px; white-space: nowrap;">Qty PLANNING<br>PRODUKSI</th>
                    <th colspan="7" class="border border-gray-400 px-3 py-3 text-center font-bold bg-blue-50" style="white-space: nowrap;">SHIFT I</th>
                    <th colspan="7" class="border border-gray-400 px-3 py-3 text-center font-bold bg-green-50" style="white-space: nowrap;">SHIFT II</th>
                    <th colspan="7" class="border border-gray-400 px-3 py-3 text-center font-bold bg-yellow-50" style="white-space: nowrap;">SHIFT III</th>
                    <th colspan="4" class="border border-gray-400 px-3 py-3 text-center font-bold bg-gray-200" style="white-space: nowrap;">TARGET SHIFT</th>
                    <th colspan="4" class="border border-gray-400 px-3 py-3 text-center font-bold bg-gray-200" style="white-space: nowrap;">TOTAL SHIFT</th>
                </tr>
                {{-- Header Row 2 - Hour Labels --}}
                <tr class="bg-gray-50 sticky top-[40px] z-20">
                    @foreach($shift1Hours as $idx => $hour)
                        @php
                            $nextHour = $idx < count($shift1Hours) - 1 ? $shift1Hours[$idx + 1] : '14:00';
                            $label = str_replace(':00', '', $hour) . ' s/d ' . str_replace(':00', '', $nextHour);
                        @endphp
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-blue-50 sticky top-[50px]" style="min-width: 70px; white-space: nowrap;">{{ $label }}</th>
                    @endforeach
                    @foreach($shift2Hours as $idx => $hour)
                        @php
                            $nextHour = $idx < count($shift2Hours) - 1 ? $shift2Hours[$idx + 1] : '00:00';
                            $label = str_replace(':00', '', $hour) . ' s/d ' . str_replace(':00', '', $nextHour);
                        @endphp
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-green-50 sticky top-[50px]" style="min-width: 70px; white-space: nowrap;">{{ $label }}</th>
                    @endforeach
                    @foreach($shift3Hours as $idx => $hour)
                        @php
                            $nextHour = $idx < count($shift3Hours) - 1 ? $shift3Hours[$idx + 1] : '07:00';
                            $label = str_replace(':00', '', $hour) . ' s/d ' . str_replace(':00', '', $nextHour);
                        @endphp
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-yellow-50 sticky top-[50px]" style="min-width: 70px; white-space: nowrap;">{{ $label }}</th>
                    @endforeach
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">SHIFT I</th>
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">SHIFT II</th>
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">SHIFT III</th>
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 90px; white-space: nowrap;">TOTAL<br>SHIFT</th>
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 90px; white-space: nowrap;">PENCAPAIAN</th>
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">TRANSAKSI</th>
                    <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 90px; white-space: nowrap;">TOTAL<br>SHIFT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($matriksDataInject as $row)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="border border-gray-400 px-3 py-3 text-center font-semibold sticky left-0 bg-white z-10" style="white-space: nowrap;">{{ $row['no_urut'] }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center sticky left-[60px] bg-white z-10" style="white-space: nowrap;">
                            <div class="font-semibold">{{ $row['mesin']->no_mesin ?? '-' }}</div>
                            @if($row['mesin']->tonase ?? false)
                                <div class="text-xs text-gray-500">({{ $row['mesin']->tonase }}T)</div>
                            @endif
                        </td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-mono" style="white-space: nowrap;">{{ $row['lot_produksi'] ?? '-' }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-mono" style="white-space: nowrap;">{{ $row['kode_part'] ?? '-' }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-mono" style="white-space: nowrap;">{{ $row['nomor_barang'] ?? '-' }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-left" style="white-space: nowrap;">{{ $row['nama_barang'] ?? '-' }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['qty_planning'], 0, ',', '.') }}</td>
                        
                        {{-- Shift I --}}
                        @foreach($shift1Hours as $idx => $hour)
                            @php
                                $hourIndex = array_search($hour, $allHours ?? []);
                                $val = isset($row['hourly_targets']) && isset($row['hourly_targets'][$hourIndex]) ? (int)$row['hourly_targets'][$hourIndex] : 0;
                            @endphp
                            <td class="border border-gray-400 px-2 py-2 text-center bg-blue-50" style="white-space: nowrap;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}</td>
                        @endforeach
                        
                        {{-- Shift II --}}
                        @foreach($shift2Hours as $idx => $hour)
                            @php
                                $hourIndex = array_search($hour, $allHours ?? []);
                                $val = isset($row['hourly_targets']) && isset($row['hourly_targets'][$hourIndex]) ? (int)$row['hourly_targets'][$hourIndex] : 0;
                            @endphp
                            <td class="border border-gray-400 px-2 py-2 text-center bg-green-50" style="white-space: nowrap;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}</td>
                        @endforeach
                        
                        {{-- Shift III --}}
                        @foreach($shift3Hours as $idx => $hour)
                            @php
                                $hourIndex = array_search($hour, $allHours ?? []);
                                $val = isset($row['hourly_targets']) && isset($row['hourly_targets'][$hourIndex]) ? (int)$row['hourly_targets'][$hourIndex] : 0;
                            @endphp
                            <td class="border border-gray-400 px-2 py-2 text-center bg-yellow-50" style="white-space: nowrap;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}</td>
                        @endforeach
                        
                        {{-- Summary TARGET --}}
                        <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['target_shift1'], 0, ',', '.') }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['target_shift2'], 0, ',', '.') }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['target_shift3'], 0, ',', '.') }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['total_target'], 0, ',', '.') }}</td>
                        
                        {{-- Summary TOTAL --}}
                        <td class="border border-gray-400 px-3 py-3 text-center bg-yellow-100" style="white-space: nowrap;">{{ $row['total_actual'] > 0 ? number_format($row['total_actual'], 0, ',', '.') : '-' }}</td>
                        <td class="border border-gray-400 px-3 py-3 text-center bg-yellow-100" style="white-space: nowrap;">{{ $row['transaksi'] }}%</td>
                        <td class="border border-gray-400 px-3 py-3 text-center bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['total_target'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>
    @endif

    {{-- Matriks ASSY --}}
    @if(count($matriksDataAssy) > 0)
    <div class="mb-6">
        <div class="bg-blue-100 border-l-4 border-blue-600 p-4 mb-3">
            <h3 class="text-lg font-bold text-blue-800">PLANNING ASSY ({{ count($matriksDataAssy) }} baris)</h3>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-x-auto overflow-y-auto" style="max-height: calc(50vh - 100px);">
            <table class="w-full border-collapse" style="min-width: 2800px; font-family: 'Segoe UI', Arial, sans-serif; font-size: 11px;">
                <thead class="sticky top-0 z-30">
                    {{-- Header Row 1 - Main Headers --}}
                    <tr class="bg-gray-100">
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold sticky left-0 bg-gray-100 z-40" style="min-width: 60px; white-space: nowrap;">NO URUT</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 90px; white-space: nowrap;">TANGGAL</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold sticky left-[60px] bg-gray-100 z-40" style="min-width: 110px; white-space: nowrap;">MEJA</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 120px; white-space: nowrap;">LOT<br>PRODUKSI</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 140px; white-space: nowrap;">KODE<br>PART</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 160px; white-space: nowrap;">NOMOR<br>BARANG</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold" style="min-width: 220px; white-space: nowrap;">NAMA<br>BARANG</th>
                        <th rowspan="2" class="border border-gray-400 px-3 py-3 text-center font-bold bg-yellow-100" style="min-width: 110px; white-space: nowrap;">Qty PLANNING<br>PRODUKSI</th>
                        <th colspan="7" class="border border-gray-400 px-3 py-3 text-center font-bold bg-blue-50" style="white-space: nowrap;">SHIFT I</th>
                        <th colspan="7" class="border border-gray-400 px-3 py-3 text-center font-bold bg-green-50" style="white-space: nowrap;">SHIFT II</th>
                        <th colspan="7" class="border border-gray-400 px-3 py-3 text-center font-bold bg-yellow-50" style="white-space: nowrap;">SHIFT III</th>
                        <th colspan="4" class="border border-gray-400 px-3 py-3 text-center font-bold bg-gray-200" style="white-space: nowrap;">TARGET SHIFT</th>
                        <th colspan="4" class="border border-gray-400 px-3 py-3 text-center font-bold bg-gray-200" style="white-space: nowrap;">TOTAL SHIFT</th>
                    </tr>
                    {{-- Header Row 2 - Hour Labels --}}
                    <tr class="bg-gray-50 sticky top-[40px] z-20">
                        @foreach($shift1Hours as $idx => $hour)
                            @php
                                $nextHour = $idx < count($shift1Hours) - 1 ? $shift1Hours[$idx + 1] : '14:00';
                                $label = str_replace(':00', '', $hour) . ' s/d ' . str_replace(':00', '', $nextHour);
                            @endphp
                            <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-blue-50 sticky top-[50px]" style="min-width: 70px; white-space: nowrap;">{{ $label }}</th>
                        @endforeach
                        @foreach($shift2Hours as $idx => $hour)
                            @php
                                $nextHour = $idx < count($shift2Hours) - 1 ? $shift2Hours[$idx + 1] : '00:00';
                                $label = str_replace(':00', '', $hour) . ' s/d ' . str_replace(':00', '', $nextHour);
                            @endphp
                            <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-green-50 sticky top-[50px]" style="min-width: 70px; white-space: nowrap;">{{ $label }}</th>
                        @endforeach
                        @foreach($shift3Hours as $idx => $hour)
                            @php
                                $nextHour = $idx < count($shift3Hours) - 1 ? $shift3Hours[$idx + 1] : '07:00';
                                $label = str_replace(':00', '', $hour) . ' s/d ' . str_replace(':00', '', $nextHour);
                            @endphp
                            <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-yellow-50 sticky top-[50px]" style="min-width: 70px; white-space: nowrap;">{{ $label }}</th>
                        @endforeach
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">SHIFT I</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">SHIFT II</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">SHIFT III</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 90px; white-space: nowrap;">TOTAL<br>SHIFT</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 90px; white-space: nowrap;">PENCAPAIAN</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 80px; white-space: nowrap;">TRANSAKSI</th>
                        <th class="border border-gray-400 px-2 py-2 text-center font-semibold bg-gray-200 sticky top-[50px]" style="min-width: 90px; white-space: nowrap;">TOTAL<br>SHIFT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($matriksDataAssy as $row)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="border border-gray-400 px-3 py-3 text-center font-semibold sticky left-0 bg-white z-10" style="white-space: nowrap;">{{ $row['no_urut'] }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($row['tanggal'])->format('d/m/Y') }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center sticky left-[60px] bg-white z-10" style="white-space: nowrap;">
                                <div class="font-semibold">{{ $row['mesin'] ?? '-' }}</div>
                            </td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-mono" style="white-space: nowrap;">{{ $row['lot_produksi'] ?? '-' }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-mono" style="white-space: nowrap;">{{ $row['kode_part'] ?? '-' }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-mono" style="white-space: nowrap;">{{ $row['nomor_barang'] ?? '-' }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-left" style="white-space: nowrap;">{{ $row['nama_barang'] ?? '-' }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['qty_planning'], 0, ',', '.') }}</td>
                            
                            {{-- Shift I --}}
                            @foreach($shift1Hours as $idx => $hour)
                                @php
                                    $hourIndex = array_search($hour, $allHours ?? []);
                                    $val = isset($row['hourly_targets']) && isset($row['hourly_targets'][$hourIndex]) ? (int)$row['hourly_targets'][$hourIndex] : 0;
                                @endphp
                                <td class="border border-gray-400 px-2 py-2 text-center bg-blue-50" style="white-space: nowrap;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}</td>
                            @endforeach
                            
                            {{-- Shift II --}}
                            @foreach($shift2Hours as $idx => $hour)
                                @php
                                    $hourIndex = array_search($hour, $allHours ?? []);
                                    $val = isset($row['hourly_targets']) && isset($row['hourly_targets'][$hourIndex]) ? (int)$row['hourly_targets'][$hourIndex] : 0;
                                @endphp
                                <td class="border border-gray-400 px-2 py-2 text-center bg-green-50" style="white-space: nowrap;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}</td>
                            @endforeach
                            
                            {{-- Shift III --}}
                            @foreach($shift3Hours as $idx => $hour)
                                @php
                                    $hourIndex = array_search($hour, $allHours ?? []);
                                    $val = isset($row['hourly_targets']) && isset($row['hourly_targets'][$hourIndex]) ? (int)$row['hourly_targets'][$hourIndex] : 0;
                                @endphp
                                <td class="border border-gray-400 px-2 py-2 text-center bg-yellow-50" style="white-space: nowrap;">{{ $val > 0 ? number_format($val, 0, ',', '.') : '' }}</td>
                            @endforeach
                            
                            {{-- Summary TARGET --}}
                            <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['target_shift1'], 0, ',', '.') }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['target_shift2'], 0, ',', '.') }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['target_shift3'], 0, ',', '.') }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center font-semibold bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['total_target'], 0, ',', '.') }}</td>
                            
                            {{-- Summary TOTAL --}}
                            <td class="border border-gray-400 px-3 py-3 text-center bg-yellow-100" style="white-space: nowrap;">{{ $row['total_actual'] > 0 ? number_format($row['total_actual'], 0, ',', '.') : '-' }}</td>
                            <td class="border border-gray-400 px-3 py-3 text-center bg-yellow-100" style="white-space: nowrap;">{{ $row['transaksi'] }}%</td>
                            <td class="border border-gray-400 px-3 py-3 text-center bg-yellow-100" style="white-space: nowrap;">{{ number_format($row['total_target'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(count($matriksDataInject) == 0 && count($matriksDataAssy) == 0)
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <p class="text-sm font-medium text-gray-500">Tidak ada data planning untuk tanggal {{ \Carbon\Carbon::parse($tanggal)->format('d M Y') }}</p>
        <p class="text-xs text-gray-400 mt-2">Pastikan sudah ada planning yang dibuat untuk tanggal ini</p>
    </div>
    @endif
</div>

<style>
/* Spreadsheet-like styling */
table {
    border-collapse: collapse;
    width: 100%;
}

th, td {
    border: 1px solid #6b7280;
    padding: 6px 8px;
    font-size: 11px;
    line-height: 1.4;
}

th {
    background-color: #f3f4f6;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    color: #1f2937;
}

td {
    background-color: #ffffff;
    color: #111827;
}

tbody tr:nth-child(even) td {
    background-color: #f9fafb;
}

tbody tr:hover td {
    background-color: #f0f9ff;
}

tbody tr:hover td.sticky {
    background-color: #ffffff;
}

/* Sticky columns */
.sticky {
    position: sticky;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* Ensure proper z-index for sticky elements */
thead th.sticky {
    z-index: 50;
}

tbody td.sticky {
    z-index: 10;
}

/* Print styles */
@media print {
    .fade-in {
        padding: 0;
        margin: 0;
    }
    
    table {
        font-size: 8px;
        page-break-inside: auto;
    }
    
    th, td {
        padding: 2px 4px;
        border: 1px solid #000;
    }
    
    tr {
        page-break-inside: avoid;
        page-break-after: auto;
    }
    
    thead {
        display: table-header-group;
    }
    
    tfoot {
        display: table-footer-group;
    }
    
    /* Ensure sticky columns work in print */
    .sticky {
        position: relative;
    }
    
    /* Hide print button */
    button {
        display: none;
    }
}
</style>

<script>
(function() {
    // Handle tanggal filter change
    const tanggalFilter = document.getElementById('tanggalFilter');
    if (tanggalFilter) {
        tanggalFilter.addEventListener('change', function() {
            const selectedDate = this.value;
            if (selectedDate) {
                window.location.href = '{{ route("planning.matriks") }}?tanggal=' + encodeURIComponent(selectedDate);
            }
        });
    }
})();
</script>
@endsection
