@extends('layout.app')

@section('content')
<div class="h-full flex items-center justify-center bg-gradient-to-br from-blue-50 via-white to-purple-50">
    {{-- Hero Section - Full Height --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-600 to-purple-600 text-white shadow-2xl rounded-2xl mx-6 w-full max-w-6xl">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute transform rotate-45 -left-1/4 -top-1/4 w-96 h-96 bg-white rounded-full"></div>
            <div class="absolute transform -rotate-45 -right-1/4 -bottom-1/4 w-96 h-96 bg-white rounded-full"></div>
        </div>
        <div class="relative px-6 py-16 md:py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-extrabold mb-6 tracking-tight">Sistem Traceability Manufacturing</h1>
                <p class="text-xl md:text-2xl text-blue-100 mb-2 font-light">PT Mada Wikri Tunggal</p>
                <p class="text-base md:text-lg text-blue-200 mb-10">Monitoring Real-time Produksi & Inventory Management</p>
                
                <div class="mt-8 flex flex-wrap justify-center gap-4 md:gap-6">
                    <div class="bg-white/20 backdrop-blur-md rounded-xl px-6 md:px-8 py-4 border border-white/30 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-2xl md:text-3xl font-bold">24/7</div>
                        <div class="text-xs md:text-sm text-blue-100 font-medium tracking-wide">Monitoring</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-md rounded-xl px-6 md:px-8 py-4 border border-white/30 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-2xl md:text-3xl font-bold">100%</div>
                        <div class="text-xs md:text-sm text-blue-100 font-medium tracking-wide">Traceable</div>
                    </div>
                    <div class="bg-white/20 backdrop-blur-md rounded-xl px-6 md:px-8 py-4 border border-white/30 transform hover:scale-105 transition-transform duration-300 shadow-lg">
                        <div class="text-2xl md:text-3xl font-bold">Real-time</div>
                        <div class="text-xs md:text-sm text-blue-100 font-medium tracking-wide">Data</div>
                    </div>
                </div>

                {{-- Quick Stats --}}
                <div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-left">
                                <div class="text-sm text-blue-200">Status</div>
                                <div class="text-lg font-semibold">Online</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div class="text-left">
                                <div class="text-sm text-blue-200">Uptime</div>
                                <div class="text-lg font-semibold">99.9%</div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-4 border border-white/20">
                        <div class="flex items-center justify-center gap-3">
                            <svg class="w-8 h-8 text-blue-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <div class="text-left">
                                <div class="text-sm text-blue-200">Performance</div>
                                <div class="text-lg font-semibold">Optimal</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Font Awesome --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection
