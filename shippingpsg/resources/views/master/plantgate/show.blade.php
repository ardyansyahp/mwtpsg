@extends('layout.app')

@section('content')
<div class="fade-in max-w-2xl mx-auto mt-10">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-800">Detail Plant Gate</h2>
            <div class="space-x-2">
                @if(userCan('master.plantgate.edit'))
                    <a href="{{ route('master.plantgate.edit', $plantgate->id) }}" class="bg-blue-600 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-700 transition">Edit</a>
                @endif
                <a href="{{ route('master.plantgate.index') }}" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded text-sm hover:bg-gray-200 transition">Kembali</a>
            </div>
        </div>

        <div class="p-6">
            {{-- Info Table --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Informasi Dasar</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500">Nama Plant Gate</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $plantgate->nama_plantgate }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Customer</dt>
                            <dd class="text-base text-gray-900">{{ $plantgate->customer->nama_perusahaan ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Status</dt>
                            <dd>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $plantgate->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $plantgate->status ? 'Active' : 'Inactive' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Statistik</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-xs text-gray-500">Total Part Terkait</dt>
                            <dd class="text-lg font-medium text-blue-600">{{ $stats['total_parts'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-gray-500">Terakhir Diupdate</dt>
                            <dd class="text-base text-GRAY-900">{{ $stats['last_updated'] }}</dd>
                        </div>
                         <div>
                            <dt class="text-xs text-gray-500">Dibuat Pada</dt>
                            <dd class="text-base text-gray-900">{{ $plantgate->created_at->format('d F Y H:i') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Relations (Optional) --}}
            @if($plantgate->parts->count() > 0)
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Part yang Terhubung</h3>
                <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Part</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Part</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($plantgate->parts as $part)
                                <tr>
                                    <td class="px-6 py-3 text-sm text-gray-900">{{ $part->nomor_part }}</td>
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $part->nama_part }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
