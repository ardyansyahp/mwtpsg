@extends('layout.app')

@section('content')
<div class="container mx-auto">
    <div class="mb-6">
        <a href="{{ route('superadmin.users.show', $user) }}" class="text-blue-600 hover:text-blue-800">← Back to User Details</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-2">Edit Permissions</h1>
        <p class="text-gray-600 mb-6">User: {{ $user->user_id }}</p>

        <form action="{{ route('superadmin.users.permissions.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            @php
                $groups = [
                    'Dashboard' => ['dashboard_rinci'],
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
