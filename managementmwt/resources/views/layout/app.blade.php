<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin">
    <title>Master PSG</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/images/logoico.ico') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    

    
    <style>
        /* Critical CSS to prevent FOUC (Flash of Unstyled Content) */
        .hidden { display: none !important; }
        .invisible { visibility: hidden; }
        [x-cloak] { display: none !important; }

        #sidebar   { view-transition-name: sidebar; }
        #appHeader { view-transition-name: header; }
        #mainContent { view-transition-name: content; }

        /* Anti-Flicker for Sidebar State */
        .sidebar-is-closed #sidebar {
            display: none !important;
        }

        /* Smooth View Transitions */
        ::view-transition-old(content) {
            animation: 90ms cubic-bezier(0.4, 0, 1, 1) both fade-out;
        }
        ::view-transition-new(content) {
            animation: 210ms cubic-bezier(0, 0, 0.2, 1) 90ms both fade-in;
        }
    </style>
    
    <!-- Custom Styles -->
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
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
        /* Injected by Setup Script for Management Portal */
        /* Executive Theme: Deep Emerald & Slate */
        header.bg-white { 
            background: linear-gradient(to right, #064e3b, #065f46) !important; 
            border-bottom: 2px solid #059669 !important; 
        }
        
        /* Sidebar Customization */
        #sidebar { 
            background-color: #0f172a !important; /* Deep Slate */
            border-right: 1px solid #1e293b !important;
        }
        
        #sidebar .text-gray-700, #sidebar .text-gray-600 {
            color: #94a3b8 !important;
        }
        
        #sidebar .hover\:bg-blue-50:hover {
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
        }

        #sidebar .active {
            background-color: rgba(16, 185, 129, 0.2) !important;
            color: #34d399 !important;
            border-left: 4px solid #10b981 !important;
        }

        /* Override Text Colors in Header */
        header .text-gray-800, header .text-gray-700, header .text-gray-900, header .text-black {
            color: #ffffff !important;
        }
        
        /* Dropdown compatibility */
        header .absolute .text-gray-900, header .absolute .text-black { color: #1e293b !important; }
        
        /* Override Icons */
        header svg {
            color: #ffffff !important; 
        }

        /* Title for Management */
        title { content: "Management View - MWT"; }
    </style>

    {{-- Anti-Flicker / State Restore (Run before paint) --}}
    <script>
        (function() {
            const state = localStorage.getItem('sidebarState');
            if (state === 'closed') {
                document.documentElement.classList.add('sidebar-is-closed');
            }
        })();
    </script>
</head>
<body>
    <div class="flex h-screen bg-gray-50 overflow-hidden">
        {{-- Sidebar --}}
        @include('layout.sidebar')

        {{-- Main Content Wrapper --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            {{-- Header --}}
            @include('layout.header')

            {{-- Main Content Scroll Area --}}
            <main id="mainContent" class="flex-1 flex flex-col overflow-y-auto p-4 md:p-6">
                {{-- Global Flash Messages --}}
                @if(session('success'))
                    <div class="mb-4 bg-green-50 border-l-4 border-green-500 p-4 rounded shadow-sm">
                        <p class="text-sm text-green-700 font-medium font-bold">SUCCESS: {{ session('success') }}</p>
                    </div>
                @endif
                
                <div class="flex-1">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Sidebar Toggle Script (With Persistence & Transitions) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('sidebarToggle');
            const side = document.getElementById('sidebar');
            
            // Function to apply sidebar state
            const applySidebarState = (state) => {
                if (state === 'open') {
                    side.classList.remove('hidden');
                    side.classList.add('flex');
                    document.documentElement.classList.remove('sidebar-is-closed');
                } else if (state === 'closed') {
                    side.classList.add('hidden');
                    side.classList.remove('flex');
                    document.documentElement.classList.add('sidebar-is-closed');
                }
            };

            // Initial State from LocalStorage (Sync with Head Script)
            const storedState = localStorage.getItem('sidebarState');
            if (storedState) {
                applySidebarState(storedState);
            } else {
                if (window.innerWidth >= 768) {
                    applySidebarState('open');
                } else {
                    applySidebarState('closed');
                }
            }
            
            // Restore Sidebar Scroll Position
            const scrollPos = localStorage.getItem('sidebarScrollPos');
            if (scrollPos && side) {
                side.scrollTop = parseInt(scrollPos);
            }

            if (side) {
                side.addEventListener('scroll', () => {
                    localStorage.setItem('sidebarScrollPos', side.scrollTop);
                });
            }

            if (btn && side) {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    // Check effective state (either has .hidden or root has .sidebar-is-closed)
                    const isClosed = side.classList.contains('hidden') || document.documentElement.classList.contains('sidebar-is-closed');
                    const newState = isClosed ? 'open' : 'closed';
                    
                    applySidebarState(newState);
                    localStorage.setItem('sidebarState', newState);
                });
                
                document.addEventListener('click', function(e) {
                    if (window.innerWidth < 768 && !side.contains(e.target) && !btn.contains(e.target) && !side.classList.contains('hidden')) {
                        applySidebarState('closed');
                    }
                });
            }

            // Submenu Toggles (Accordion Behavior)
            const toggles = ['masterDataToggle', 'subMasterToggle', 'bahanBakuToggle'];
            toggles.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('click', function(e) {
                        e.preventDefault();
                        const subId = id.replace('Toggle', 'Submenu');
                        const sub = document.getElementById(subId);
                        const arrowId = id.replace('Toggle', 'Arrow');
                        const arrow = document.getElementById(arrowId);
                        
                        if (sub) {
                            const isOpening = sub.classList.contains('hidden');
                            
                            // Close ALL other submenus first
                            toggles.forEach(otherId => {
                                if (otherId !== id) {
                                    const otherSub = document.getElementById(otherId.replace('Toggle', 'Submenu'));
                                    const otherArrow = document.getElementById(otherId.replace('Toggle', 'Arrow'));
                                    if (otherSub) otherSub.classList.add('hidden');
                                    if (otherArrow) otherArrow.classList.remove('rotate-180');
                                }
                            });

                            // Then toggle the current one
                            sub.classList.toggle('hidden');
                            if (arrow) arrow.classList.toggle('rotate-180');
                        }
                    });
                }
            });
        });
    </script>
    
    <!-- Scripts -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    
    @stack('scripts')

    {{-- Global Sync & Toast Manager --}}
    <div id="globalToastContainer" class="fixed bottom-5 right-5 z-[9999] flex flex-col gap-2 pointer-events-none"></div>

    <script>
        // --- TOAST MANAGER ---
        function showToast(message, type = 'success', duration = 4000) {
            const container = document.getElementById('globalToastContainer');
            if(!container) return;
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

        // --- EXPOSE TOAST GLOBALLY ---
        window.showToast = showToast;
    </script>
</body>
</html>
