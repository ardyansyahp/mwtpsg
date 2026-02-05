@if ($paginator->hasPages())
    <div class="flex items-center justify-center gap-1">
        {{-- First Page Link (<<) --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed">
                &lt;&lt;
            </span>
        @else
            <a href="{{ $paginator->url(1) }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors" title="First Page">
                &lt;&lt;
            </a>
        @endif

        {{-- Previous Page Link (<) --}}
        @if ($paginator->onFirstPage())
            <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed">
                &lt;
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors" rel="prev" title="Previous Page">
                &lt;
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-3 py-1 text-gray-500 bg-white border border-gray-300 rounded cursor-default">
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-3 py-1 text-white bg-blue-600 border border-blue-600 rounded cursor-default">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link (>) --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors" rel="next" title="Next Page">
                &gt;
            </a>
        @else
            <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed">
                &gt;
            </span>
        @endif

        {{-- Last Page Link (>>) --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="px-3 py-1 text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors" title="Last Page">
                &gt;&gt;
            </a>
        @else
            <span class="px-3 py-1 text-gray-400 bg-white border border-gray-300 rounded cursor-not-allowed">
                &gt;&gt;
            </span>
        @endif
    </div>
@endif
