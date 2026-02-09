@extends('layout.app')

@section('content')
<div class="container mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.users.show', $user) }}" class="text-blue-600 hover:text-blue-800">← Back to User Details</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-2">Edit Permissions</h1>
        <p class="text-gray-600 mb-6">User: {{ $user->user_id }}</p>

        @if($user->is_superadmin)
            <div class="bg-gradient-to-r from-purple-50 to-blue-50 border-l-4 border-purple-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-start gap-3">
                    <i class="fas fa-crown text-purple-600 text-2xl mt-1"></i>
                    <div>
                        <h3 class="font-bold text-purple-900 text-lg mb-1">🎯 Superadmin Access</h3>
                        <p class="text-purple-800 mb-2">
                            User ini adalah <strong>Superadmin</strong> dan otomatis memiliki akses ke <strong>SEMUA halaman</strong> di <strong>SEMUA project</strong>:
                        </p>
                        <ul class="list-disc list-inside text-purple-700 text-sm space-y-1 ml-4">
                            <li><strong>Master PSG</strong> - Portal utama & Master Data</li>
                            <li><strong>Supplier PSG</strong> - Control Supplier & Bahan Baku</li>
                            <li><strong>Shipping PSG</strong> - Finish Good & Shipping</li>
                        </ul>
                        <p class="text-purple-600 text-sm mt-3 italic">
                            💡 Checkbox di bawah tidak berpengaruh untuk Superadmin. Permissions hanya berlaku untuk user biasa.
                        </p>
                    </div>
                </div>
            </div>
        @else
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                <div class="flex items-start gap-3">
                    <i class="fas fa-info-circle text-blue-600 text-xl mt-1"></i>
                    <div>
                        <h3 class="font-bold text-blue-900 mb-1">Permission Assignment</h3>
                        <p class="text-blue-800 text-sm">
                            Pilih permissions yang ingin diberikan ke user ini. User dapat memiliki akses ke multiple projects.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('superadmin.users.permissions.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            @php
                $groups = [
                    'Management Portal' => ['management'],
                    'Dashboard' => ['dashboard', 'dashboard_rinci'],
                    'Master Data' => ['master_perusahaan', 'master_mesin', 'master_manpower', 'master_plantgate', 'master_kendaraan'],
                    'Sub Master' => ['master_bahanbaku', 'submaster_part', 'master_mold', 'submaster_plantgatepart'],
                    'Bahan Baku & Supply' => ['bahanbaku_receiving', 'bahanbaku_supply', 'controlsupplier'],
                    'Produksi & Planning' => ['planning', 'planning_matriks', 'produksi_inject_in', 'produksi_inject_out', 'produksi_wip_in', 'produksi_wip_out', 'produksi_assy_in', 'produksi_assy_out'],
                    'Finish Good' => ['finishgood_in', 'finishgood_out', 'spk'],
                    'Shipping' => ['shipping_controltruck', 'shipping_dispatch', 'shipping_delivery'],
                    'Other' => ['tracer']
                ];
                
                $groupedPermissions = [];
                // Initialize groups order
                foreach(array_keys($groups) as $gName) $groupedPermissions[$gName] = [];
                
                foreach($allPermissions as $cat => $perms) {
                    $found = false;
                    foreach($groups as $groupName => $cats) {
                        if(in_array($cat, $cats)) {
                            $groupedPermissions[$groupName][$cat] = $perms;
                            $found = true;
                            break;
                        }
                    }
                    if(!$found) {
                        $groupedPermissions['Other'][$cat] = $perms;
                    }
                }
            @endphp

            <div class="space-y-8">
                {{-- Homepage Redirection Section --}}
                @php
                    $masterPerm = \DB::table('permissions')->where('slug', 'homepage.master.view')->first();
                    $supplierPerm = \DB::table('permissions')->where('slug', 'homepage.supplier.view')->first();
                    $shippingPerm = \DB::table('permissions')->where('slug', 'homepage.shipping.view')->first();
                    
                    $masterPermId = $masterPerm->id ?? null;
                    $supplierPermId = $supplierPerm->id ?? null;
                    $shippingPermId = $shippingPerm->id ?? null;
                @endphp
                
                @if($masterPermId || $supplierPermId || $shippingPermId || true)
                <div>
                    <h2 class="text-lg font-bold mb-3 pb-1 border-b border-gray-200 text-purple-800 flex items-center gap-2">
                        <i class="fas fa-home text-sm"></i> Homepage Redirection
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @if($masterPermId)
                        <div class="border border-purple-200 rounded-lg p-3 bg-purple-50 hover:shadow-md transition-shadow h-full cursor-pointer" onclick="this.querySelector('input').click()">
                            <div class="flex items-start gap-2 pointer-events-none">
                                <input type="checkbox" name="permissions[]" value="{{ $masterPermId }}" 
                                    class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 pointer-events-auto"
                                    {{ in_array('homepage.master.view', $permissions ?? []) ? 'checked' : '' }}>
                                <div>
                                    <label class="font-bold text-gray-800 text-xs uppercase tracking-wider block">Master Homepage</label>
                                    <p class="text-[10px] text-gray-600 mt-1 leading-tight">Force stay on Master Portal (Default).</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($supplierPermId)
                        <div class="border border-purple-200 rounded-lg p-3 bg-purple-50 hover:shadow-md transition-shadow h-full cursor-pointer" onclick="this.querySelector('input').click()">
                            <div class="flex items-start gap-2 pointer-events-none">
                                <input type="checkbox" name="permissions[]" value="{{ $supplierPermId }}" 
                                    class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 pointer-events-auto"
                                    {{ in_array('homepage.supplier.view', $permissions ?? []) ? 'checked' : '' }}>
                                <div>
                                    <label class="font-bold text-gray-800 text-xs uppercase tracking-wider block">Supplier Homepage</label>
                                    <p class="text-[10px] text-gray-600 mt-1 leading-tight">Force redirect login ke Supplier Portal.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($shippingPermId)
                        <div class="border border-purple-200 rounded-lg p-3 bg-purple-50 hover:shadow-md transition-shadow h-full cursor-pointer" onclick="this.querySelector('input').click()">
                            <div class="flex items-start gap-2 pointer-events-none">
                                <input type="checkbox" name="permissions[]" value="{{ $shippingPermId }}" 
                                    class="mt-1 rounded border-gray-300 text-purple-600 shadow-sm focus:border-purple-300 focus:ring focus:ring-purple-200 focus:ring-opacity-50 pointer-events-auto"
                                    {{ in_array('homepage.shipping.view', $permissions ?? []) ? 'checked' : '' }}>
                                <div>
                                    <label class="font-bold text-gray-800 text-xs uppercase tracking-wider block">Shipping Homepage</label>
                                    <p class="text-[10px] text-gray-600 mt-1 leading-tight">Force redirect login ke Shipping Portal.</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif
                @foreach($groupedPermissions as $groupName => $categories)
                    @if(count($categories) > 0)
                        <div>
                            <h2 class="text-lg font-bold mb-3 pb-1 border-b border-gray-200 text-blue-800 flex items-center gap-2">
                                <i class="fas fa-layer-group text-sm"></i> {{ $groupName }}
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                @foreach($categories as $catName => $perms)
                                    <div class="border border-gray-200 rounded-lg p-3 bg-gray-50 hover:shadow-md transition-shadow h-full category-card">
                                        <div class="flex justify-between items-start mb-2">
                                            <h3 class="font-bold text-xs uppercase tracking-wider text-gray-700">
                                                {{ str_replace('_', ' ', $catName) }}
                                            </h3>
                                            <div class="flex gap-1">
                                                <button type="button" class="text-[10px] text-blue-600 hover:text-blue-800 font-medium px-1" onclick="selectAll(this)">All</button>
                                                <button type="button" class="text-[10px] text-gray-500 hover:text-gray-700 font-medium px-1" onclick="selectNone(this)">None</button>
                                            </div>
                                        </div>
                                        
                                        <div class="space-y-1">
                                            @foreach($perms as $permission)
                                                <label class="flex items-start gap-2 cursor-pointer hover:bg-white p-1 rounded -ml-1 transition-colors">
                                                    <input type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $permission->id }}"
                                                        {{ $user->permissions->contains($permission->id) ? 'checked' : '' }}
                                                        class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-3 h-3">
                                                    <span class="text-xs text-gray-600 leading-tight select-none">{{ $permission->name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Save Permissions
                </button>
                <a href="{{ route('superadmin.users.show', $user) }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function selectAll(btn) {
    const category = btn.closest('.category-card');
    if(category) {
        category.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = true);
    }
}

function selectNone(btn) {
    const category = btn.closest('.category-card');
    if(category) {
        category.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
    }
}
</script>
@endpush
@endsection
