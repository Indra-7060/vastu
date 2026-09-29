@if ($paginator->hasPages())
    <nav class="admin-pagination" role="navigation" aria-label="Pagination">
        {{-- Page numbers --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-btn page-disabled">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-btn page-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-btn" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next » --}}
        @if ($paginator->hasMorePages())
            <a class="page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">»</a>
        @else
            <span class="page-btn page-disabled">»</span>
        @endif
    </nav>
@endif
