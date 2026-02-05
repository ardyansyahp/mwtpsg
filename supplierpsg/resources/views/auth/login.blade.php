<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-purple-600 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Welcome Back</h1>
            <p class="text-gray-600 mt-2">Sign in to continue</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">User ID</label>
                <div class="relative">
                    <input type="text" 
                        name="user_id" 
                        id="user_id" 
                        required 
                        autofocus
                        autocomplete="off"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Type to search..."
                        value="{{ old('user_id') }}">
                    
                    <!-- Autocomplete suggestions -->
                    <div id="suggestions" class="hidden absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                        <!-- Suggestions will be populated here -->
                    </div>
                </div>
                @error('user_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Password <span class="text-gray-400 text-xs">(for superadmin only)</span>
                </label>
                <input type="password" name="password" id="password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter password (if superadmin)">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition duration-200">
                Sign In
            </button>
        </form>

        <div class="mt-6 text-center text-sm text-gray-600">
            <p>Regular users: Enter your MP ID (no password required)</p>
            <p class="mt-1">Superadmin: Use superadmin credentials</p>
        </div>
    </div>

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
            <div class="suggestion-item px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100"
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
                // Focus on password field after selection
                document.getElementById('password').focus();
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

    // Show suggestions on focus if there's a value
    userIdInput.addEventListener('focus', function() {
        if (this.value) {
            showSuggestions(this.value);
        }
    });
    </script>
</body>
</html>
