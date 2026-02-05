<footer class="bg-gray-800 border-t border-gray-700 py-2 px-4 flex-shrink-0">
    <div class="flex flex-row items-center justify-between gap-2 text-xs">
        {{-- Left --}}
        <div class="text-gray-300">
            © {{ date('Y') }} Mada Wikri Tunggal
        </div>

        {{-- Center --}}
        <div class="flex items-center gap-2">
            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
            <span class="text-gray-300">Sistem Online</span>
        </div>

        {{-- Right --}}
        <div class="flex items-center gap-3 text-gray-300">
            <span>Versi 1.0.0</span>
            <span class="hidden sm:inline">|</span>
            <span id="currentDateTime" class="hidden sm:inline"></span>
        </div>
    </div>
</footer>

