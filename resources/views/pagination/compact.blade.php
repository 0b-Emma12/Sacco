@if ($paginator->hasPages())
    @php
        $window = 1;
        $start = max(1, $paginator->currentPage() - $window);
        $end = min($paginator->lastPage(), $paginator->currentPage() + $window);
        $link = 'h-8 min-w-8 px-2 inline-flex items-center justify-center rounded-lg text-xs font-semibold text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white';
        $disabled = 'h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-300 dark:text-slate-600';
    @endphp
    <nav class="flex items-center gap-1" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="{{ $disabled }}" aria-disabled="true"><x-icon name="arrow-left" class="w-4 h-4"/></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous page" class="{{ $link }}"><x-icon name="arrow-left" class="w-4 h-4"/></a>
        @endif

        @if ($start > 1)
            <a href="{{ $paginator->url(1) }}" class="{{ $link }}">1</a>
            @if ($start > 2)
                <span class="px-1 text-xs text-slate-400">…</span>
            @endif
        @endif

        @foreach ($paginator->getUrlRange($start, $end) as $page => $url)
            @if ($page == $paginator->currentPage())
                <span aria-current="page" class="h-8 min-w-8 px-2 inline-flex items-center justify-center rounded-lg bg-[#181824] text-white text-xs font-semibold dark:bg-white dark:text-slate-900">{{ $page }}</span>
            @else
                <a href="{{ $url }}" class="{{ $link }}">{{ $page }}</a>
            @endif
        @endforeach

        @if ($end < $paginator->lastPage())
            @if ($end < $paginator->lastPage() - 1)
                <span class="px-1 text-xs text-slate-400">…</span>
            @endif
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="{{ $link }}">{{ $paginator->lastPage() }}</a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next page" class="{{ $link }}"><x-icon name="arrow-right" class="w-4 h-4"/></a>
        @else
            <span class="{{ $disabled }}" aria-disabled="true"><x-icon name="arrow-right" class="w-4 h-4"/></span>
        @endif
    </nav>
@endif
