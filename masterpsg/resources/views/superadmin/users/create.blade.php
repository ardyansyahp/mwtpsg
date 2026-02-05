@extends('layout.app')

@section('content')
<div class="container mx-auto max-w-2xl">
    <div class="mb-6">
        <a href="{{ route('superadmin.users.index') }}" class="text-blue-600 hover:text-blue-800">← Back to Users</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold mb-6">Create New User</h1>

        <form action="{{ route('superadmin.users.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User ID *</label>
                <div class="relative">
                    <input type="text" 
                        name="user_id" 
                        id="user_id" 
                        required
                        autocomplete="off"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Type to search mp_id..."
                        value="{{ old('user_id') }}">
                    
                    <!-- Autocomplete suggestions -->
                    <div id="suggestions" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto">
                        <!-- Suggestions will be populated here -->
                    </div>
                </div>
                @error('user_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">For regular users, use their mp_id from m_manpower table</p>
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password (Optional)</label>
                <input type="password" name="password" id="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Leave empty for regular users">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-sm text-gray-500 mt-1">Leave empty for passwordless login (regular users)</p>
            </div>

            <div class="flex gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Create User
                </button>
                <a href="{{ route('superadmin.users.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-6 py-2 rounded-lg">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Manpower data for autocomplete
const manpowers = @json($manpowers);

const userIdInput = document.getElementById('user_id');
const suggestionsDiv = document.getElementById('suggestions');

// Filter and show suggestions
function showSuggestions(searchTerm) {
    if (!searchTerm) {
        suggestionsDiv.classList.add('hidden');
        return;
    }

    const filtered = manpowers.filter(mp => 
        mp.mp_id.toLowerCase().includes(searchTerm.toLowerCase()) ||
        mp.nama.toLowerCase().includes(searchTerm.toLowerCase())
    );

    if (filtered.length === 0) {
        suggestionsDiv.classList.add('hidden');
        return;
    }

    suggestionsDiv.innerHTML = filtered.map(mp => `
        <div class="suggestion-item px-4 py-2 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
             data-mp-id="${mp.mp_id}">
            <div class="font-medium text-gray-900">${mp.mp_id}</div>
            <div class="text-sm text-gray-600">${mp.nama}</div>
        </div>
    `).join('');

    suggestionsDiv.classList.remove('hidden');

    // Add click handlers to suggestions
    document.querySelectorAll('.suggestion-item').forEach(item => {
        item.addEventListener('click', function() {
            userIdInput.value = this.dataset.mpId;
            suggestionsDiv.classList.add('hidden');
        });
    });
}

// Input event listener
userIdInput.addEventListener('input', function() {
    showSuggestions(this.value);
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if (!userIdInput.contains(e.target) && !suggestionsDiv.contains(e.target)) {
        suggestionsDiv.classList.add('hidden');
    }
});

// Show all suggestions on focus
userIdInput.addEventListener('focus', function() {
    if (this.value) {
        showSuggestions(this.value);
    }
});
</script>
@endpush
@endsection
