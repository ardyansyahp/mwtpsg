@extends('layout.app')

@section('content')
<div class="container-fluid px-4">
    <div class="flex justify-between items-center mt-4 mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-shipping-fast text-blue-600 mr-2"></i>Status Shipping Dashboard
        </h1>
        <form action="{{ route('shipping.status.index') }}" method="GET" class="flex items-center space-x-2">
            <input type="date" name="date" value="{{ $date }}" class="border rounded px-3 py-2 text-sm" onchange="this.form.submit()">
        </form>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SPK Info</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pulling (FG Out)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Loading (Assignment)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Delivery (On Road)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Arrival (Customer)</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Summary</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($dashboardData as $item)
                        <tr class="hover:bg-gray-50">
                            <!-- 1. SPK INFO -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-bold text-gray-900">{{ $item->surat_jalan }}</div>
                                <div class="text-xs text-gray-500">SPK: {{ $item->spk_no }}</div>
                                <div class="text-xs text-blue-600 font-semibold mt-1">{{ $item->customer }}</div>
                                @if($item->plantgate)<div class="text-[10px] text-gray-400">Gate: {{ $item->plantgate }}</div>@endif
                                <div class="mt-2 text-xs text-gray-500">
                                    <i class="far fa-clock mr-1"></i>Plan: {{ $item->plan_time ? $item->plan_time->format('H:i') : '-' }}
                                </div>
                            </td>

                            <!-- 2. PULLING -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->pulling_time)
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Done
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $item->pulling_time->format('H:i') }}
                                    </div>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                        Pending
                                    </span>
                                @endif
                            </td>

                            <!-- 3. ASSIGNMENT -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->driver_name !== '-')
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                            <i class="fas fa-steering-wheel"></i>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->driver_name }}</div>
                                            <div class="text-xs text-gray-500">{{ $item->truck_no }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400 italic">Waiting Assignment</span>
                                @endif
                            </td>

                            <!-- 4. DELIVERY (DEPARTURE) -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->departure_time)
                                    @php
                                        $dStatus = $item->departure_status_label; // ADVANCED, DELAY, NORMAL
                                        $dColor = match($dStatus) {
                                            'ADVANCED' => 'text-green-600 bg-green-50 border-green-200',
                                            'DELAY' => 'text-red-600 bg-red-50 border-red-200',
                                            'NORMAL' => 'text-blue-600 bg-blue-50 border-blue-200',
                                            default => 'text-gray-600 bg-gray-50 border-gray-200'
                                        };
                                    @endphp
                                    <div class="flex flex-col">
                                        <span class="px-2 py-0.5 text-[10px] font-bold uppercase border rounded w-fit mb-1 {{ $dColor }}">
                                            {{ $dStatus }}
                                        </span>
                                        <div class="text-sm text-gray-900 font-semibold">
                                            {{ $item->departure_time->format('H:i') }}
                                        </div>
                                        
                                        <!-- Duration Hint if arrived -->
                                        @if($item->arrival_time)
                                            <div class="text-[10px] text-gray-400 mt-1" title="Duration Trip">
                                                <i class="fas fa-hourglass-half mr-1"></i>{{ $item->duration_trip }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif

                                <!-- START: INCIDENTS SECTION -->
                                @if($item->incidents && $item->incidents->count() > 0)
                                    <div class="mt-3 bg-red-50 p-2 rounded-lg border border-red-100 max-w-[200px]">
                                        <div class="text-[10px] font-bold text-red-600 uppercase mb-1 flex items-center gap-1">
                                            <i class="fas fa-exclamation-triangle"></i> Terjadi Kendala:
                                        </div>
                                        @foreach($item->incidents as $inc)
                                            <div class="mb-2 last:mb-0">
                                                <div class="text-[10px] text-gray-700 leading-tight">
                                                    {{ $inc->keterangan }}
                                                </div>
                                                @if($inc->foto)
                                                    <a href="{{ asset('storage/' . $inc->foto) }}" target="_blank" class="block mt-1">
                                                        <img src="{{ asset('storage/' . $inc->foto) }}" class="w-full h-12 object-cover rounded shadow-sm hover:opacity-80 transition-opacity border border-red-200" title="Klik untuk perbesar">
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                                <!-- END: INCIDENTS SECTION -->
                            </td>

                            <!-- 5. ARRIVAL -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->arrival_time)
                                    <div class="text-sm text-green-700 font-bold">
                                        <i class="fas fa-check-circle mr-1"></i>Arrived
                                    </div>
                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $item->arrival_time->format('H:i') }}
                                    </div>
                                    @if($item->arrival_proof)
                                        <div class="mt-3">
                                            <a href="{{ asset('storage/' . $item->arrival_proof) }}" target="_blank" class="block">
                                                <img src="{{ asset('storage/' . $item->arrival_proof) }}" class="w-24 h-16 object-cover rounded-lg shadow-sm border border-gray-200 hover:border-blue-400 transition-colors" title="Lihat Foto Bukti Tiba">
                                            </a>
                                            <div class="text-[10px] text-gray-400 mt-1 italic text-center">Bukti Tiba</div>
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>

                            <!-- 6. SUMMARY / FINISH -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if($item->delivery_status === 'COMPLETED')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-800 text-white">
                                        Trip Completed
                                    </span>
                                @elseif($item->delivery_status === 'ARRIVED')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        At Customer
                                    </span>
                                @elseif($item->delivery_status !== 'OPEN')
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                        Status: {{ $item->delivery_status }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Waiting...</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data pengiriman untuk tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
