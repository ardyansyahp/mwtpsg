{{-- Smart Pagination Component: Dropdown + Info + Buttons --}}
<div class="flex flex-col md:flex-row justify-between items-center gap-4 py-3 w-full">
    {{-- Left Side: Per Page & Info --}}
    <div class="text-sm text-gray-600 flex flex-col sm:flex-row items-center gap-2 order-2 md:order-1">
        <div class="flex items-center gap-2">
            <span>Tampilkan</span>
            <select 
                class="px-2 py-1 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white text-xs font-medium cursor-pointer"
                onchange="window.location.href = updateQueryStringParameter(window.location.href, 'per_page', this.value)"
            >
                @foreach([10, 25, 50, 100] as $perPage)
                    <option value="{{ $perPage }}" {{ request('per_page', $paginator->perPage()) == $perPage ? 'selected' : '' }}>
                        {{ $perPage }}
                    </option>
                @endforeach
            </select>
            <span>data per halaman</span>
        </div>
        
        <span class="hidden sm:inline border-l border-gray-300 h-4 mx-2"></span>
        
        <div class="text-center sm:text-left">
            Menampilkan <span class="font-bold text-gray-900">{{ $paginator->firstItem() ?? 0 }}</span> - <span class="font-bold text-gray-900">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="font-bold text-gray-900">{{ $paginator->total() }}</span> data
        </div>
    </div>

    {{-- Right Side: Pagination Links (Custom Style) --}}
    <div class="order-1 md:order-2">
        @if ($paginator->hasPages())
            <div class="flex items-center justify-center gap-1">
                {{-- First Page Link (<<) --}}
                @if ($paginator->onFirstPage())
                    <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed text-xs">&lt;&lt;</span>
                @else
                    <a href="{{ $paginator->url(1) }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-xs" title="First Page">&lt;&lt;</a>
                @endif

                {{-- Previous Page Link (<) --}}
                @if ($paginator->onFirstPage())
                    <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed text-xs">&lt;</span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-xs" rel="prev" title="Previous Page">&lt;</a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="px-3 py-1 text-gray-500 bg-white border border-gray-300 rounded cursor-default text-xs">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-1 text-white bg-blue-600 border border-blue-600 rounded cursor-default text-xs">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-xs">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link (>) --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-xs" rel="next" title="Next Page">&gt;</a>
                @else
                    <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed text-xs">&gt;</span>
                @endif

                {{-- Last Page Link (>>) --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->url($paginator->lastPage()) }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-xs" title="Last Page">&gt;&gt;</a>
                @else
                    <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed text-xs">&gt;&gt;</span>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    function updateQueryStringParameter(uri, key, value) {
        var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
        var separator = uri.indexOf('?') !== -1 ? "&" : "?";
        // Also reset page to 1
        var newUri = uri;
        if (uri.match(re)) {
            newUri = uri.replace(re, '$1' + key + "=" + value + '$2');
        } else {
            newUri = uri + separator + key + "=" + value;
        }
        
        // Reset page parameter to 1
        var pageRe = new RegExp("([?&])page=.*?(&|$)", "i");
        if (newUri.match(pageRe)) {
            newUri = newUri.replace(pageRe, '$1page=1$2');
        } else {
             // If page param not present, no need to add it as 1 is default, 
             // but strictly speaking we should explicitly set it if we want to be sure.
             // Actually, if we change per_page, we MUST go to page 1 to avoid offset out of bounds.
             newUri = newUri + (newUri.indexOf('?') !== -1 ? "&" : "?") + "page=1";
        }
        return newUri;
    }
</script>
