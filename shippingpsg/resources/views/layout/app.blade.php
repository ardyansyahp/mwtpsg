<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PSG</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/logoico.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    
    <style>
        /* Critical CSS to prevent FOUC (Flash of Unstyled Content) */
        .hidden { display: none !important; }
        .invisible { visibility: hidden; }
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Instrument Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    @stack('styles')
    <style>
        /* Injected by Setup Script for Shipping PSG */
        header.bg-white { 
            background-color: #1e3a8a !important; 
            border-bottom-color: #1e40af !important; 
        }
        header .text-gray-800, header .text-gray-700, header .text-gray-900, header svg { color: #ffffff !important; }
        header button:hover { background-color: rgba(255,255,255,0.1) !important; }
        title { content: "Shipping PSG"; }
    </style>
</head>
<body>
    <div class="app-layout">
        {{-- Header --}}
        @include('layout.header')

        <div class="app-main-wrapper">
            {{-- Sidebar --}}
            @include('layout.sidebar')

            {{-- Main Content Area --}}
            <div class="app-content-area">
                {{-- Main Content --}}
                <main id="mainContent" class="app-main-content {{ (request()->is('/') || request()->routeIs('*.dashboard')) ? '' : 'p-6' }}">
                    {{-- Global Flash Messages --}}
                    @if(session('success'))
                        <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded-md shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-md shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="mb-4 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-md shadow-sm">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm text-yellow-700 font-medium">{{ session('warning') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </main>

                {{-- Footer --}}
                @include('layout.footer')
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    
    @stack('scripts')

    {{-- Global Sync & Toast Manager --}}
    <div id="globalToastContainer" class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

    <script>
        // --- TOAST MANAGER ---
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('globalToastContainer');
            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 px-4 py-3 rounded-xl shadow-2xl border pointer-events-auto transform transition-all duration-300 translate-x-full opacity-0 max-w-sm`;
            
            const themes = {
                success: 'bg-green-600 border-green-500 text-white',
                warning: 'bg-amber-500 border-amber-400 text-white',
                error: 'bg-red-600 border-red-500 text-white',
                info: 'bg-indigo-600 border-indigo-500 text-white'
            };
            
            toast.className += ' ' + (themes[type] || themes.info);
            
            const icon = {
                success: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                warning: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
                error: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                info: '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
            };

            toast.innerHTML = `
                <div class="flex-shrink-0">${icon[type]}</div>
                <div class="flex-1 text-sm font-bold tracking-tight">${message}</div>
                <button onclick="this.parentElement.remove()" class="flex-shrink-0 opacity-70 hover:opacity-100 transition-opacity">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            `;

            container.appendChild(toast);
            
            // Trigger animation
            requestAnimationFrame(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            });

            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => toast.remove(), 300);
            }, duration);
        }

        // --- GLOBAL SYNC MANAGER ---
        let globalOfflineQueue = JSON.parse(localStorage.getItem('fg_scan_queue') || '[]');
        let isGlobalSyncing = false;

        async function syncDataToServer(data) {
            const response = await fetch('{{ route("finishgood.in.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });
            return await response.json();
        }

        async function processGlobalQueue() {
            if (isGlobalSyncing || globalOfflineQueue.length === 0 || !navigator.onLine) return;
            
            isGlobalSyncing = true;
            console.log('Global Sync: Starting to process ' + globalOfflineQueue.length + ' items');
            
            let successCount = 0;
            const originalCount = globalOfflineQueue.length;

            while (globalOfflineQueue.length > 0 && navigator.onLine) {
                const item = globalOfflineQueue[0];
                try {
                    const res = await syncDataToServer(item);
                    if (res.success) {
                        globalOfflineQueue.shift();
                        localStorage.setItem('fg_scan_queue', JSON.stringify(globalOfflineQueue));
                        successCount++;
                        
                        // Dispatch event for UI updates in finishgood.in.create if current page
                        window.dispatchEvent(new CustomEvent('fg-item-synced', { detail: { id: item.id } }));
                    } else {
                        console.error('Global Sync: Item failed', res.message);
                        break; 
                    }
                } catch (err) {
                    console.error('Global Sync: Network error');
                    break;
                }
                await new Promise(r => setTimeout(r, 500));
            }

            if (successCount > 0) {
                showToast(`Sinkronisasi Berhasil: ${successCount} data scan terkirim ke server.`, 'success');
            }

            isGlobalSyncing = false;
        }

        // Sync Event Listeners
        window.addEventListener('online', processGlobalQueue);
        window.addEventListener('load', () => {
            if (globalOfflineQueue.length > 0) {
                setTimeout(processGlobalQueue, 2000); // Wait a bit after load
            }
        });

        // --- SERVICE WORKER REGISTRATION ---
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then(reg => console.log('Service Worker: Registered'))
                    .catch(err => console.log(`Service Worker: Error: ${err}`));
            });
        }
    </script>
</body>
</html>
