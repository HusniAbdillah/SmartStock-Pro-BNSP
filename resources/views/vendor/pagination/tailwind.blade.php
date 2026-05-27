@if ($paginator->hasPages())
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
    <div class="hidden sm:block">
        <p style="font-size:13px; color:#64748D;">
            Menampilkan
            <span style="font-weight:500; color:#061B31;">{{ $paginator->firstItem() }}</span>–<span style="font-weight:500; color:#061B31;">{{ $paginator->lastItem() }}</span>
            dari
            <span style="font-weight:500; color:#061B31;">{{ $paginator->total() }}</span>
            data
        </p>
    </div>

    <div class="flex items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
        <span class="flex items-center justify-center w-8 h-8 rounded"
              style="background:#F8FAFC; border:1px solid #D4DEE9; color:#B8CCDB; cursor:not-allowed; font-size:13px;">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </span>
        @else
        <a href="{{ $paginator->previousPageUrl() }}"
           class="flex items-center justify-center w-8 h-8 rounded"
           style="background:#FFFFFF; border:1px solid #D4DEE9; color:#50617A; font-size:13px; text-decoration:none; transition:all 120ms ease;"
           onmouseover="this.style.borderColor='#533AFD'; this.style.color='#533AFD';"
           onmouseout="this.style.borderColor='#D4DEE9'; this.style.color='#50617A';">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        @endif

        {{-- Page links --}}
        @foreach ($elements as $element)
        @if (is_string($element))
        <span class="flex items-center justify-center w-8 h-8"
              style="font-size:13px; color:#B8CCDB;">{{ $element }}</span>
        @endif

        @if (is_array($element))
        @foreach ($element as $page => $url)
        @if ($page == $paginator->currentPage())
        <span class="flex items-center justify-center w-8 h-8 rounded"
              style="background:#533AFD; border:1px solid #533AFD; color:#FFFFFF; font-size:13px; font-weight:500;">
            {{ $page }}
        </span>
        @else
        <a href="{{ $url }}"
           class="flex items-center justify-center w-8 h-8 rounded"
           style="background:#FFFFFF; border:1px solid #D4DEE9; color:#50617A; font-size:13px; text-decoration:none; transition:all 120ms ease;"
           onmouseover="this.style.borderColor='#533AFD'; this.style.color='#533AFD';"
           onmouseout="this.style.borderColor='#D4DEE9'; this.style.color='#50617A';">
            {{ $page }}
        </a>
        @endif
        @endforeach
        @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}"
           class="flex items-center justify-center w-8 h-8 rounded"
           style="background:#FFFFFF; border:1px solid #D4DEE9; color:#50617A; font-size:13px; text-decoration:none; transition:all 120ms ease;"
           onmouseover="this.style.borderColor='#533AFD'; this.style.color='#533AFD';"
           onmouseout="this.style.borderColor='#D4DEE9'; this.style.color='#50617A';">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
        @else
        <span class="flex items-center justify-center w-8 h-8 rounded"
              style="background:#F8FAFC; border:1px solid #D4DEE9; color:#B8CCDB; cursor:not-allowed; font-size:13px;">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </span>
        @endif
    </div>
</nav>
@endif
