@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center gap-1.5 font-sans flex-wrap text-xs">
        
        {{-- First Page Link << --}}
        @if ($paginator->onFirstPage())
            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-400 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Halaman Pertama">
                &lt;&lt;
            </span>
        @else
            <a href="{{ $paginator->url(1) }}" class="px-2 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-sm" title="Halaman Pertama">
                &lt;&lt;
            </a>
        @endif

        {{-- Previous Page Link < --}}
        @if ($paginator->onFirstPage())
            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-400 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Sebelumnya">
                &lt;
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="px-2 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-sm" rel="prev" title="Sebelumnya">
                &lt;
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span class="px-1 text-slate-400 font-bold" aria-disabled="true">{{ $element }}</span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="px-2.5 py-1 rounded-lg bg-blue-600 text-white font-extrabold shadow-sm border border-blue-600" aria-current="page">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $url }}" class="px-2.5 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-sm">
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link > --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="px-2 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-sm" rel="next" title="Selanjutnya">
                &gt;
            </a>
        @else
            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-400 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Selanjutnya">
                &gt;
            </span>
        @endif

        {{-- Last Page Link >> --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->url($paginator->lastPage()) }}" class="px-2 py-1 rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-sm" title="Halaman Terakhir ({{ $paginator->lastPage() }})">
                &gt;&gt;
            </a>
        @else
            <span class="px-2 py-1 rounded-lg bg-slate-100 text-slate-400 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Halaman Terakhir">
                &gt;&gt;
            </span>
        @endif

    </nav>
@endif
