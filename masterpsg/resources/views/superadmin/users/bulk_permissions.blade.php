@extends('layout.app')

@section('content')
<div class="fade-in">
    <div class="flex flex-col gap-6 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('superadmin.users.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <h2 class="text-xl font-bold text-gray-900 leading-none">Bulk Assign Permissions</h2>
            </div>
            <p class="text-[10px] text-gray-500 uppercase font-bold tracking-wider ml-7">Add permissions to selected users</p>
        </div>

        <form action="{{ route('superadmin.users.bulk_update_permissions') }}" method="POST" class="w-full">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Selected Users List -->
                <div class="lg:col-span-1 space-y-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 sticky top-24">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="font-bold text-gray-900">Selected Users</h3>
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $users->count() }} Users</span>
                        </div>
                        <div class="space-y-2 max-h-[calc(100vh-300px)] overflow-y-auto pr-1">
                            @foreach($users as $user)
                                <input type="hidden" name="user_ids[]" value="{{ $user->id }}">
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold text-xs">
                                        {{ strtoupper(substr($user->manpower->nama ?? $user->user_id, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-bold text-gray-900 truncate">{{ $user->manpower->nama ?? '-' }}</div>
                                        <div class="text-xs text-gray-500 truncate">{{ $user->user_id }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Role Templates (Moved to sidebar) -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                        <h3 class="font-bold text-gray-900 mb-2">Quick Templates</h3>
                        <p class="text-xs text-gray-500 mb-3">Select a role to check common permissions automatically.</p>
                        <select id="roleTemplate" class="w-full text-sm border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" onchange="applyTemplate(this.value)">
                            <option value="">-- Select Template --</option>
                            <option value="all_read">All Read Only (View)</option>
                            <option value="warehouse_staff">Warehouse Staff (Logistik)</option>
                            <option value="purchasing">Purchasing Staff (Supplier)</option>
                        </select>
                    </div>
                </div>

                <!-- Permissions Grid -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="font-bold text-gray-900">Permissions to Add</h3>
                            <button type="button" onclick="resetPermissions()" class="text-xs text-red-500 hover:text-red-700 font-medium">Reset Selection</button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($allPermissions as $category => $permissions)
                                <div class="border rounded-xl p-4 bg-gray-50/50 hover:bg-white hover:shadow-sm transition-all duration-200">
                                    <div class="flex items-center gap-2 mb-3 border-b border-gray-200 pb-2">
                                        <input type="checkbox" id="cat_{{ $category }}" 
                                               onchange="toggleCategory('{{ $category }}', this)"
                                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                        <label for="cat_{{ $category }}" class="text-xs font-black uppercase text-gray-700 tracking-wider cursor-pointer">
                                            {{ str_replace('_', ' ', $category) }}
                                        </label>
                                    </div>
                                    
                                    <div class="grid grid-cols-1 gap-2 pl-6">
                                        @foreach($permissions as $perm)
                                            <div class="flex items-start gap-2 group">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm->id }}" 
                                                       id="perm_{{ $perm->id }}"
                                                       class="perm-checkbox cat-{{ $category }} mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                                                <label for="perm_{{ $perm->id }}" class="text-xs text-gray-600 cursor-pointer group-hover:text-gray-900 transition-colors lh-sm">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-gray-100">
                            <a href="{{ route('superadmin.users.index') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 font-medium text-sm hover:bg-gray-50 transition-colors">
                                Cancel
                            </a>
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 shadow-md hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                                Apply Permissions
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function toggleCategory(category, checkbox) {
        const checkboxes = document.querySelectorAll(`.cat-${category}`);
        checkboxes.forEach(cb => {
            cb.checked = checkbox.checked;
        });
    }

    function resetPermissions() {
        document.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
        document.getElementById('roleTemplate').value = "";
    }

    function applyTemplate(template) {
        // Reset permissions first
        document.querySelectorAll('input[name="permissions[]"]').forEach(cb => cb.checked = false);
        document.querySelectorAll('input[id^="cat_"]').forEach(cb => cb.checked = false);

        if (!template) return;

        if (template === 'all_read') {
            document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`).textContent.toLowerCase();
                if (label.includes('view') || label.includes('index') || label.includes('list') || label.includes('show')) {
                    cb.checked = true;
                }
            });
        } else if (template === 'warehouse_staff') {
             document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`).textContent.toLowerCase();
                const parentCat = cb.className; // contains cat-categoryname
                if (label.includes('finishgood') || label.includes('stock') || label.includes('scan') || parentCat.includes('finishgood') || parentCat.includes('receiving')) {
                    cb.checked = true;
                }
            });
        }
         else if (template === 'purchasing') {
             document.querySelectorAll('input[name="permissions[]"]').forEach(cb => {
                const label = document.querySelector(`label[for="${cb.id}"]`).textContent.toLowerCase();
                const parentCat = cb.className;
                 if (label.includes('supplier') || label.includes('po') || label.includes('bahan') || parentCat.includes('controlsupplier')) {
                    cb.checked = true;
                }
            });
        }
    }
</script>
@endpush
@endsection
