<table class="item-matrix-table">
    <thead>
        <!-- First header row: Dates -->
        <tr>
            <th rowspan="2" class="sticky-col-supplier sticky-header" style="vertical-align: middle; font-weight: 700;">SUPPLIER</th>
            <th rowspan="2" class="sticky-col-item sticky-header" style="vertical-align: middle; font-weight: 700;">ITEM</th>
            <th rowspan="2" class="sticky-col-point sticky-header" style="vertical-align: middle; font-weight: 700;">POINT C</th>
            
            @foreach($dates as $dateInfo)
            <th class="sticky-header {{ $dateInfo['is_weekend'] ? 'weekend' : '' }}" 
                style="min-width: 70px; width: 70px; text-align: center; padding: 4px 2px; vertical-align: middle; font-size: 9px; line-height: 1.2;">
                <div style="font-weight: 700; font-size: 10px;">
                    {{ \Carbon\Carbon::parse($dateInfo['date'])->format('d-M') }}
                </div>
                <div style="font-size: 8px; margin-top: 1px; font-weight: 500; opacity: 0.85;">
                    {{ \Carbon\Carbon::parse($dateInfo['date'])->format('D') }}
                </div>
            </th>
            @endforeach
            
            <th rowspan="2" class="sticky-header" style="background: #1976d2; min-width: 70px; width: 70px; vertical-align: middle; font-weight: 700;">Total</th>
            <th rowspan="2" class="sticky-header" style="background: #1976d2; min-width: 70px; width: 70px; vertical-align: middle; font-weight: 700;">Freq</th>
            <th rowspan="2" class="sticky-header" style="background: #1976d2; min-width: 120px; width: 120px; vertical-align: middle; font-weight: 700;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
            @php
                // Determine UOM based on kategori
                $uom = in_array($item['kategori'], ['material', 'masterbatch']) ? 'KG' : 'PCS';
                
                $rows = [
                    ['label' => "PLAN({$uom})", 'field' => 'plan', 'editable' => true, 'color' => '#fff'],
                    ['label' => "ACT({$uom})", 'field' => 'act', 'editable' => false, 'color' => '#f5f5f5'],
                    ['label' => "BLC({$uom})", 'field' => 'blc', 'editable' => false, 'color' => '#fff'],
                    ['label' => 'STATUS', 'field' => 'status', 'editable' => false, 'color' => '#f5f5f5'],
                    ['label' => 'SR(%)', 'field' => 'sr', 'editable' => false, 'color' => '#fff'],
                    ['label' => 'PO NUMB', 'field' => 'ponumb', 'editable' => true, 'color' => '#f5f5f5'],
                ];
                
                // Calculate frequency
                $freqPlan = 0;
                $freqAct = 0;
                foreach($dates as $dateInfo) {
                    $dateStr = date('Y-m-d', strtotime($dateInfo['date']));
                    $daily = $item['daily_details'][$dateStr] ?? null;
                    if ($daily) {
                        if ($daily['plan'] > 0) $freqPlan++;
                        if ($daily['act'] > 0) $freqAct++;
                    }
                }
                $freqAr = ($freqPlan + $freqAct) > 0 ? ($freqPlan + $freqAct) / 2 : 0;
                $freqBlc = $item['total_plan'] > 0 ? ($item['total_act'] / $item['total_plan']) * 100 : 0;
                
                // Grade berdasarkan freq balance (dalam decimal 0-1)
                $freqBlcDecimal = $freqBlc / 100; // Konversi persen ke decimal
                $freqGrade = '-';
                if ($freqBlcDecimal == 0) $freqGrade = '-';
                elseif ($freqBlcDecimal == 1) $freqGrade = 'A';
                elseif ($freqBlcDecimal < 0.6) $freqGrade = 'D';
                elseif ($freqBlcDecimal < 0.8) $freqGrade = 'C';
                elseif ($freqBlcDecimal < 1) $freqGrade = 'B';
                else $freqGrade = 'A';
            @endphp
            
            @foreach($rows as $rowIdx => $row)
            <tr>
                {{-- Supplier (rowspan 6) --}}
                @if($rowIdx === 0)
                <td rowspan="6" class="sticky-col-supplier" style="vertical-align: middle; font-weight: 600; padding: 6px 4px;">
                    {{ $item['supplier_name'] }}
                </td>
                @endif
                
                {{-- Item (rowspan 6) --}}
                @if($rowIdx === 0)
                <td rowspan="6" class="sticky-col-item" style="vertical-align: middle; padding: 6px 4px;">
                    <div style="font-weight: 600; font-size: 10px; margin-bottom: 2px;">
                        {{ $item['nomor_bahan_baku'] }}
                    </div>
                    <div style="font-size: 9px; color: #666;">
                        {{ $item['nama_bahan_baku'] }}
                    </div>
                </td>
                @endif
                
                {{-- Point C (Row Label) --}}
                <td class="sticky-col-point" style="background: {{ $row['color'] }}; font-weight: 600; font-size: 9px; padding: 4px;">
                    {{ $row['label'] }}
                </td>
                
                {{-- Data per tanggal --}}
                @php
                    // Hitung balance dan SR kumulatif untuk item ini
                    $runningBalance = 0;
                    $totalPlan = 0;
                    $totalAct = 0;
                    $hasStarted = false;
                    $isClosed = false;
                @endphp
                @foreach($dates as $dateInfo)
                    @php
                        $dateStr = date('Y-m-d', strtotime($dateInfo['date']));
                        $daily = $item['daily_details'][$dateStr] ?? null;
                        $cellKey = $item['bahan_baku_id'] . '-' . $dateStr . '-' . $row['field'];
                        
                        // Jika ada plan di tanggal ini, mulai tracking balance
                        if ($daily && $daily['plan'] > 0) {
                            $hasStarted = true;
                        }
                        
                        // Update running balance, total plan dan total act jika sudah dimulai
                        if ($hasStarted && !$isClosed) {
                            if ($daily) {
                                // Rumus balance: ACT - PLAN (minus jika kurang, plus jika lebih)
                                $runningBalance += $daily['act'] - $daily['plan'];
                                
                                // Akumulasi total plan dan act untuk SR%
                                $totalPlan += $daily['plan'];
                                $totalAct += $daily['act'];
                                
                                // Jika status CLOSE, stop tracking setelah tanggal ini
                                if ($daily['status'] === 'CLOSE') {
                                    $isClosed = true;
                                }
                            }
                        }
                    @endphp
                    
                    @if($row['editable'] && $row['field'] === 'plan')
                        {{-- Editable PLAN cell --}}
                        <td class="editable-cell {{ $dateInfo['is_weekend'] ? 'weekend' : '' }}" 
                            style="text-align: right; background: #f0f8ff; cursor: pointer;"
                            @click="startEdit('{{ $cellKey }}')">
                            
                            <div v-if="editingCell !== '{{ $cellKey }}'">
                                <span class="edit-icon">✏️</span>
                                <span>{{ $daily ? number_format($daily['plan'], 0, ',', '.') : '-' }}</span>
                            </div>
                            <input v-else
                                   type="number"
                                   data-cell="{{ $cellKey }}"
                                   value="{{ $daily['plan'] ?? 0 }}"
                                   @blur="updatePlan(@json($item), '{{ $dateStr }}', $event.target.value, '{{ $daily['ponumb'] ?? '' }}')"
                                   @keydown.enter="$event.target.blur()"
                                   @keydown.escape="editingCell = null">
                        </td>
                    @elseif($row['editable'] && $row['field'] === 'ponumb')
                        {{-- Editable PO NUMB cell --}}
                        <td class="editable-cell {{ $dateInfo['is_weekend'] ? 'weekend' : '' }}" 
                            style="text-align: left; background: #f0f8ff; cursor: pointer;"
                            @click="startEdit('{{ $cellKey }}')">
                            
                            <div v-if="editingCell !== '{{ $cellKey }}'">
                                <span class="edit-icon">✏️</span>
                                <span>{{ $daily['ponumb'] ?? '-' }}</span>
                            </div>
                            <input v-else
                                   type="text"
                                   data-cell="{{ $cellKey }}"
                                   value="{{ $daily['ponumb'] ?? '' }}"
                                   @blur="updatePONumb(@json($item), '{{ $dateStr }}', $event.target.value)"
                                   @keydown.enter="$event.target.blur()"
                                   @keydown.escape="editingCell = null">
                        </td>
                    @else
                        {{-- Readonly cells --}}
                        <td class="{{ $dateInfo['is_weekend'] ? 'weekend' : '' }}" 
                            style="text-align: right; background: {{ $row['color'] }}; padding: 4px;">
                            @if($row['field'] === 'blc')
                                {{-- Tampilkan balance jika sudah dimulai dan belum CLOSE --}}
                                @if($hasStarted && !$isClosed)
                                    <span style="color: {{ $runningBalance < 0 ? '#d32f2f' : ($runningBalance > 0 ? '#388e3c' : 'inherit') }}; font-weight: {{ $runningBalance != 0 ? '600' : 'normal' }};">
                                        @if($runningBalance != 0)
                                            {{ $runningBalance < 0 ? '' : '+' }}{{ number_format($runningBalance, 0, ',', '.') }}
                                        @else
                                            0
                                        @endif
                                    </span>
                                @else
                                    -
                                @endif
                            @elseif($row['field'] === 'status')
                                {{-- Tampilkan status jika sudah dimulai dan belum CLOSE --}}
                                @if($hasStarted && !$isClosed)
                                    @php
                                        // Ambil status dari data, default PENDING jika tidak ada
                                        $displayStatus = ($daily && $daily['status']) ? $daily['status'] : 'PENDING';
                                    @endphp
                                    <span class="badge badge-{{ strtolower($displayStatus) }}" style="display: block; text-align: center;">
                                        {{ $displayStatus }}
                                    </span>
                                @else
                                    -
                                @endif
                            @elseif($row['field'] === 'sr')
                                {{-- Hitung SR% kumulatif sebagai persentase ketercapaian total ACT vs total PLAN --}}
                                @if($hasStarted && !$isClosed)
                                    @php
                                        $srPercent = 0;
                                        if ($totalPlan > 0) {
                                            $srPercent = ($totalAct / $totalPlan) * 100;
                                        }
                                    @endphp
                                    @if($totalPlan > 0)
                                        <span style="color: {{ $srPercent < 100 ? '#d32f2f' : ($srPercent >= 100 ? '#388e3c' : 'inherit') }}; font-weight: {{ $srPercent != 100 ? '600' : 'normal' }};">
                                            {{ number_format($srPercent, 1) }}%
                                        </span>
                                    @else
                                        -
                                    @endif
                                @else
                                    -
                                @endif
                            @elseif($daily)
                                @if($row['field'] === 'act')
                                    {{ $daily['act'] > 0 ? number_format($daily['act'], 0, ',', '.') : '-' }}
                                @endif
                            @else
                                -
                            @endif
                        </td>
                    @endif
                @endforeach
                
                {{-- Total column --}}
                <td style="background: #e3f2fd; padding: 4px; text-align: right;">
                    @if($row['field'] === 'plan')
                        {{-- Total PLAN = SUM semua plan --}}
                        {{ number_format($item['total_plan'], 0, ',', '.') }}
                    @elseif($row['field'] === 'act')
                        {{-- Total ACT = SUM semua act --}}
                        {{ number_format($item['total_act'], 0, ',', '.') }}
                    @elseif($row['field'] === 'blc')
                        {{-- Total BALANCE = Total ACT - Total PLAN --}}
                        @php
                            $totalBalance = $item['total_act'] - $item['total_plan'];
                        @endphp
                        <span style="color: {{ $totalBalance < 0 ? '#d32f2f' : ($totalBalance > 0 ? '#388e3c' : 'inherit') }}; font-weight: {{ $totalBalance != 0 ? '600' : 'normal' }};">
                            @if($totalBalance != 0)
                                {{ $totalBalance < 0 ? '' : '+' }}{{ number_format($totalBalance, 0, ',', '.') }}
                            @else
                                0
                            @endif
                        </span>
                    @elseif($row['field'] === 'status')
                        {{-- Total STATUS = kosong --}}
                        -
                    @elseif($row['field'] === 'sr')
                        {{-- Total SR = (Total ACT / Total PLAN) * 100% --}}
                        @php
                            $totalSrPercent = 0;
                            if ($item['total_plan'] > 0) {
                                $totalSrPercent = ($item['total_act'] / $item['total_plan']) * 100;
                            }
                        @endphp
                        @if($item['total_plan'] > 0)
                            <span style="color: {{ $totalSrPercent < 100 ? '#d32f2f' : ($totalSrPercent >= 100 ? '#388e3c' : 'inherit') }}; font-weight: {{ $totalSrPercent != 100 ? '600' : 'normal' }};">
                                {{ number_format($totalSrPercent, 1) }}%
                            </span>
                        @else
                            -
                        @endif
                    @elseif($row['field'] === 'ponumb')
                        {{-- Total PO = COUNT berapa banyak PO (bukan sum) --}}
                        @php
                            $poCount = count($item['po_numbers'] ?? []);
                        @endphp
                        {{ $poCount > 0 ? $poCount : '-' }}
                    @endif
                </td>
                
                {{-- Freq column --}}
                <td style="background: #fff3e0; padding: 4px; text-align: right;">
                    @if($row['field'] === 'plan')
                        {{ $freqPlan }}
                    @elseif($row['field'] === 'act')
                        {{ $freqAct }}
                    @elseif($row['field'] === 'blc')
                        {{ number_format($freqBlc, 1) }}%
                    @elseif($row['field'] === 'status')
                        <span style="font-size: 8px;">Result</span>
                    @elseif($row['field'] === 'sr')
                        -
                    @elseif($row['field'] === 'ponumb')
                        <span style="display: inline-block; padding: 2px 6px; border-radius: 3px; font-weight: 600; font-size: 9px;
                                     background: {{ $freqGrade === 'A' ? '#c8e6c9' : ($freqGrade === 'B' ? '#bbdefb' : ($freqGrade === 'C' ? '#fff9c4' : ($freqGrade === 'D' ? '#ffcdd2' : '#f5f5f5'))) }};
                                     color: {{ $freqGrade === 'A' ? '#2e7d32' : ($freqGrade === 'B' ? '#1565c0' : ($freqGrade === 'C' ? '#f57f17' : ($freqGrade === 'D' ? '#c62828' : '#666'))) }};">
                            {{ $freqGrade }}
                        </span>
                    @endif
                </td>
                
                {{-- Keterangan (rowspan 6) --}}
                @if($rowIdx === 0)
                <td rowspan="6" style="vertical-align: middle; padding: 6px 4px; font-size: 9px; color: #666;">
                    {{-- Kosong untuk sekarang --}}
                </td>
                @endif
            </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
