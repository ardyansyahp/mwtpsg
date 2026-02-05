@php
    // Pre-calculate max cycles across all trucks to determine column headers
    $maxCycles = 4; // Min 4 as per original design
    foreach($trucks ?? [] as $t) {
        $maxCycles = max($maxCycles, count($t['customers'] ?? []));
    }
    // Cap at 10 for UI stability unless needed more
    $maxCycles = min(10, $maxCycles);
@endphp

<div class="inline-block min-w-full align-middle">
    <div id="truck-control-table-container" class="relative">
    <!-- Red Line Indicator - Z-index fixed to be below sticky columns (z-10+) -->
    <div id="red-line" class="absolute top-0 bottom-0 pointer-events-none z-[5] hidden" style="border-left: 2px solid red; width: 2px;">
         <div class="bg-red-600 text-white text-[9px] font-bold px-1 rounded-sm absolute -top-4 -left-3 whitespace-nowrap" id="red-line-time">
             00:00
         </div>
    </div>
    <table class="truck-control-table min-w-full border-separate border-spacing-0 text-[10px] border border-gray-300">
        <thead>
            <tr class="bg-blue-600 text-white">
                <th rowspan="2" class="sticky left-0 top-0 z-[60] bg-blue-600 border border-blue-500 px-1 py-1 w-[100px] min-w-[100px] shadow-sm sticky-col-truck">NOMOR TRUCK</th>
                <th rowspan="2" class="sticky left-[100px] top-0 z-[59] bg-blue-600 border border-blue-500 px-1 py-1 w-[30px] min-w-[30px] shadow-sm text-[9px] sticky-col-cyc">CYC</th>
                <th rowspan="2" class="sticky left-[130px] top-0 z-[59] bg-blue-600 border border-blue-500 px-1 py-1 w-[80px] min-w-[80px] shadow-sm text-[9px] sticky-col-sj">SJ</th>
                <th rowspan="2" class="sticky left-[210px] top-0 z-[59] bg-blue-600 border border-blue-500 px-1 py-1 w-[120px] min-w-[120px] shadow-sm sticky-col-driver">NAMA DRIVER</th>
                <th rowspan="2" class="sticky left-[330px] top-0 z-[58] bg-blue-600 border border-blue-500 px-1 py-1 w-[150px] min-w-[150px] shadow-sm sticky-col-customer">CUSTOMER</th>
                <th colspan="2" class="sticky left-[480px] top-0 z-[58] bg-blue-600 border border-blue-500 px-1 py-1 min-w-[160px] h-[30px] shadow-sm sticky-col-plan-header">PLAN/ACTUAL</th>
                
                {{-- SHIFT 1 --}}
                <th colspan="9" class="sticky top-0 z-50 bg-blue-700 border border-blue-600 px-1 py-1 h-[30px]">SHIFT 1</th>
                
                {{-- SHIFT 2 --}}
                <th colspan="8" class="sticky top-0 z-50 bg-blue-800 border border-blue-600 px-1 py-1 h-[30px]">SHIFT 2</th>
                
                <th rowspan="2" class="sticky top-0 right-0 z-[60] bg-blue-600 border border-blue-500 px-1 py-1 w-[80px] min-w-[80px] shadow-sm">STATUS TRIP</th>
            </tr>
            <tr class="bg-blue-600 text-white">
                {{-- Sub-header untuk PLAN/ACTUAL --}}
                <th class="sticky left-[480px] top-[30px] z-[57] bg-blue-600 border border-blue-500 px-1 py-1 min-w-[80px] text-[9px] shadow-sm sticky-col-activity">Activity</th>
                <th class="sticky left-[560px] top-[30px] z-[57] bg-blue-600 border border-blue-500 px-1 py-1 min-w-[80px] text-[9px] shadow-sm sticky-col-plan">PLAN/ACTUAL</th>
                
                {{-- SHIFT 1 Time slots --}}
                @foreach(['07.00', '08.00', '09.00', '10.00', '11.00', '12.00', '13.00', '14.00', '15.00'] as $time)
                    <th class="sticky top-[30px] z-50 bg-blue-600 border border-blue-500 px-1 py-1 w-[50px] min-w-[50px] text-[9px]">
                        {{ $time }}
                    </th>
                @endforeach
                
                {{-- SHIFT 2 Time slots --}}
                @foreach(['16.00', '17.00', '18.00', '19.00', '20.00', '21.00', '22.00', '23.00'] as $time)
                    <th class="sticky top-[30px] z-50 bg-blue-600 border border-blue-500 px-1 py-1 w-[50px] min-w-[50px] text-[9px]">
                        {{ $time }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
            @php
                // Pre-calculate max cycles again for body scope if needed or use from outer scope
            @endphp

            @forelse($trucks ?? [] as $truck)
                @php
                    $customers = $truck['customers'] ?? [];
                    $totalRows = count($customers);
                    $truckId = $truck['id'] ?? $truck['kendaraan_id'] ?? 0;
                @endphp
                
                @foreach($customers as $idx => $cycle)
                    @php
                        $targetCycle = $idx + 1;
                        $custId = $cycle['customer_id'] ?? 0;
                        
                        // Activity Label Patterns (Berangkat P/A, Datang P/A)
                        $patternIdx = $idx % 4;
                        $activity = ($patternIdx < 2) ? 'BERANGKAT' : 'DATANG';
                        $type = ($patternIdx % 2 == 0) ? 'PLAN' : 'ACTUAL';
                        $showActivityRowspan = ($patternIdx % 2 == 0);
                    @endphp
                    <tr class="hover:bg-gray-50 {{ isset($truck['is_pending']) && $truck['is_pending'] ? 'bg-orange-50' : '' }}">
                        {{-- NOMOR TRUCK (rowspan totalRows) --}}
                        @if($idx === 0)
                            <td rowspan="{{ $totalRows }}" class="sticky left-0 z-30 border border-gray-200 p-1 font-semibold align-middle shadow-sm sticky-col-truck"
                                :class="{{ isset($truck['is_pending']) && $truck['is_pending'] ? "'bg-orange-100 text-orange-800'" : "'bg-white'" }}">
                                {{ $truck['nopol_kendaraan'] ?? $truck['nopol'] ?? '-' }}
                                @if(isset($truck['is_pending']) && $truck['is_pending'])
                                    <div class="text-[8px] font-normal text-orange-600">Butuh Armada</div>
                                @endif
                            </td>
                        @endif
                        
                        
                        {{-- CYC - show ACTUAL cycle number (just the number, no 'C' prefix) --}}
                        <td class="sticky left-[100px] z-25 bg-gray-50 border border-gray-200 p-1 align-middle text-center font-bold text-[9px] shadow-sm sticky-col-cyc">
                            @if(isset($cycle['actual_cycle']) && $cycle['actual_cycle'])
                                {{ $cycle['actual_cycle'] }}
                            @else
                                -
                            @endif
                        </td>

                        {{-- SJ - show individual SJ per cycle --}}
                        <td class="sticky left-[130px] z-25 bg-gray-50 border border-gray-200 p-1 align-middle text-center text-[9px] shadow-sm text-xs sticky-col-sj">
                            {{ $cycle['surat_jalan'] ?? '-' }}
                        </td>

                        {{-- NAMA DRIVER - show individual driver per cycle --}}
                        <td class="sticky left-[210px] z-20 bg-gray-50 border border-gray-200 p-1 align-middle shadow-sm sticky-col-driver">
                            {{ $cycle['driver_name'] ?? '-' }}
                        </td>
                        
                        {{-- CUSTOMER - show individual customer per cycle --}}
                        <td class="sticky left-[330px] z-20 {{ $activity === 'BERANGKAT' ? 'bg-blue-50' : 'bg-gray-50' }} border border-gray-200 p-1 align-middle shadow-sm sticky-col-customer">
                            <div class="font-semibold text-[10px]">
                                {{ $cycle['customer_name'] ?? '-' }}
                            </div>
                        </td>
                        
                        {{-- Activity with rowspan 2 for Plan/Actual pair --}}
                        @if($showActivityRowspan)
                            <td rowspan="2" class="sticky z-20 bg-blue-50 border border-gray-200 p-1 font-semibold text-[9px] text-left align-middle pl-2 shadow-sm sticky-col-activity">
                                {{ $activity }}
                            </td>
                        @elseif($idx >= 4 && $idx % 2 == 0) {{-- For cycles beyond 4, if they don't fit the 4-pattern perfectly --}}
                             <td rowspan="2" class="sticky z-20 bg-blue-50 border border-gray-200 p-1 font-semibold text-[9px] text-left align-middle pl-2 shadow-sm sticky-col-activity">
                                {{ $activity }}
                            </td>
                        @endif
                        
                        {{-- PLAN/ACTUAL label --}}
                        <td class="sticky z-20 {{ $type === 'PLAN' ? 'bg-white' : 'bg-gray-50' }} border border-gray-200 p-1 font-semibold text-[9px] text-left align-middle pl-2 shadow-sm sticky-col-plan">
                            {{ $type }}
                        </td>
                        
                        {{-- TIME SLOTS Grid --}}
                        @foreach(['07.00', '08.00', '09.00', '10.00', '11.00', '12.00', '13.00', '14.00', '15.00', '16.00', '17.00', '18.00', '19.00', '20.00', '21.00', '22.00', '23.00'] as $timeSlot)
                            @php
                                // Note: $cycle['times'] actually contains the data for the WHOLE truck aggregated
                                $timeValues = (array)($cycle['times'][$activity][$type][$timeSlot] ?? []);
                            @endphp
                            <td class="bg-blue-50 border border-gray-200 p-[1px] min-w-[50px] w-[50px] relative text-center">
                                <div class="flex flex-wrap items-center justify-center gap-1 relative w-full h-full min-h-[20px]">
                                    @foreach($timeValues as $val)
                                        @php
                                            $timeValue = is_array($val) ? $val['time'] : $val;
                                            $parts = explode(' ', $timeValue);
                                            $cMarker = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '';
                                            $fotoUrl = is_array($val) ? $val['foto'] : null;
                                            $isActual = ($type === 'ACTUAL');
                                        @endphp
                                        <div class="relative group">
                                            @if($fotoUrl)
                                                <a href="{{ $fotoUrl }}" target="_blank" class="block cursor-pointer">
                                                    <img src="{{ asset('assets/images/' . ($isActual ? 'delivery.png' : 'delivery-truck.png')) }}" class="h-4 w-4" alt="Icon">
                                                </a>
                                            @else
                                                <img src="{{ asset('assets/images/' . ($isActual ? 'delivery.png' : 'delivery-truck.png')) }}" class="h-4 w-4" alt="Icon">
                                            @endif
                                            
                                            @if($cMarker)
                                                <span class="absolute -top-1 -right-1 {{ $isActual ? 'bg-green-500' : 'bg-red-500' }} text-white text-[6px] px-[2px] rounded-sm font-bold leading-none z-10">{{ $cMarker }}</span>
                                            @endif
                                            
                                            <div class="absolute bottom-full left-1/2 -translate-x-1/2 bg-black text-white px-1 rounded text-[8px] opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none mb-1 z-50 flex flex-col items-center">
                                                <span>{{ $timeValue }}</span>
                                                @if($fotoUrl)
                                                    <span class="text-[7px] text-blue-200 font-bold underline">(Klik Foto)</span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                        @endforeach

                        {{-- STATUS Column (Single Consolidated Column) --}}
                        @if($idx === 0)
                            <td rowspan="{{ $totalRows }}" class="sticky right-0 z-30 bg-white border border-gray-200 p-1 align-top shadow-sm w-[80px] min-w-[80px]">
                                <div class="flex flex-col gap-1 mt-1">
                                    @foreach($customers as $cIdx => $cData)
                                        @php
                                            $cStatus = strtoupper($cData['status'] ?? 'OPEN');
                                            if ($cStatus === '-' || $cStatus === '') $cStatus = 'OPEN';
                                            $cNum = $cData['actual_cycle'] ?? ($cIdx + 1);
                                            
                                            $colorMap = [
                                                'ADVANCED' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'NORMAL' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                'DELAY' => 'bg-red-100 text-red-700 border-red-200',
                                                'PENDING' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                'OPEN' => 'bg-gray-50 text-gray-400 border-gray-100',
                                                'IN_TRANSIT' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                                'ARRIVED' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                                'DELIVERED' => 'bg-green-100 text-green-700 border-green-200',
                                                'COMPLETED' => 'bg-gray-100 text-gray-700 border-gray-300',
                                            ];
                                            $colorClass = $colorMap[$cStatus] ?? 'bg-gray-50 text-gray-400 border-gray-100';
                                        @endphp
                                        
                                        @if($cData && ($cData['surat_jalan'] !== '-' || $cStatus !== 'OPEN'))
                                            <div class="flex items-center gap-1">
                                                <span class="font-bold text-[8px] text-gray-400 w-4">C{{ $cNum }}</span>
                                                <div class="flex-1 px-1 py-0.5 rounded border font-bold text-[8px] uppercase leading-tight text-center {{ $colorClass }}">
                                                    {{ str_replace('_', ' ', $cStatus) }}
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                    
                                    @if(count(array_filter($customers, fn($c) => $c['surat_jalan'] !== '-' || ($c['status'] ?? 'OPEN') !== 'OPEN')) === 0)
                                        <div class="text-center text-gray-300 italic pt-4">No Active Trip</div>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="{{ 21 + $maxCycles }}" class="p-8 text-center text-gray-400">
                        Tidak ada data truck untuk tanggal ini
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
