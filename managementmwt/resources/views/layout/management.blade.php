<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Management Portal - MWT</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#10b981', // Emerald Bright
                            600: '#00df82', // Custom Bright Green for MWT
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    },
                    fontFamily: {
                        sans: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        [x-cloak] { display: none !important; }
        
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }

        .premium-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.04);
            border-radius: 1.5rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .premium-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.06);
        }

        .gradient-green {
            background: linear-gradient(135deg, #10b981 0%, #00df82 100%);
        }

        .glass-nav {
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased min-h-screen">
    {{-- Header / Nav --}}
    <nav class="glass-nav sticky top-0 z-[100] w-full">
        <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                {{-- Logo & Brand --}}
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 gradient-green rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-xl font-black text-slate-900 leading-none">MANAGEMENT PORTAL</h1>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-[0.2em] mt-1">PT Mada Wikri Tunggal</p>
                    </div>
                </div>

                {{-- Center Nav (Optional for Quick Links) --}}
                <div class="hidden md:flex items-center gap-8">
                    <a href="/" class="text-sm font-bold text-emerald-600 border-b-2 border-emerald-500 pb-1">Dashboard</a>
                    <a href="#" class="text-sm font-bold text-slate-500 hover:text-emerald-600 transition-colors">Supplier</a>
                    <a href="#" class="text-sm font-bold text-slate-500 hover:text-emerald-600 transition-colors">Production</a>
                    <a href="#" class="text-sm font-bold text-slate-500 hover:text-emerald-600 transition-colors">Logistics</a>
                </div>

                {{-- Right Profile --}}
                <div class="flex items-center gap-6">
                    <div class="hidden lg:flex flex-col text-right">
                        <span class="text-sm font-black text-slate-900 capitalize">{{ session('user_name', 'Management Name') }}</span>
                        <span class="text-[9px] font-bold text-emerald-600 uppercase tracking-widest">{{ session('is_superadmin') ? 'Executive Director' : 'General Manager' }}</span>
                    </div>
                    <div class="w-12 h-12 bg-white border-2 border-slate-100 rounded-2xl flex items-center justify-center text-emerald-600 font-bold shadow-sm overflow-hidden">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(session('user_name', 'User')) }}&background=f0fdf4&color=10b981&bold=true" alt="Avatar">
                    </div>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main Content --}}
    <main class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 pb-8">
        <div class="pt-8 border-t border-slate-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">© 2026 PT MADA WIKRI TUNGGAL - S2S Ecosystem</p>
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-tighter">System Pulse Stable - Real-time Enabled</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
