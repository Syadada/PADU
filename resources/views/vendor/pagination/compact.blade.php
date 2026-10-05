@if ($paginator->hasPages())
    @php
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();
        
        // Menampilkan daftar nomor halaman berjarak 5 (1-5, 6-10, 11-15, dst.)
        $startPage = floor(($currentPage - 1) / 5) * 5 + 1;
        $endPage = min($lastPage, $startPage + 4);
        
        $queryParams = request()->except('page');
        if (!empty($queryParams)) {
            $paginator->appends($queryParams);
        }
    @endphp

    <div class="flex items-center gap-2 flex-wrap text-xs font-sans">
        <nav role="navigation" aria-label="Navigasi Halaman" class="inline-flex items-center gap-1">
            
            {{-- Tombol Halaman Pertama (<<) --}}
            @if ($paginator->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-300 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Halaman Pertama">
                    &laquo;
                </span>
            @else
                <a href="{{ $paginator->url(1) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-2xs no-underline" title="Halaman Pertama (1)">
                    &laquo;
                </a>
            @endif

            {{-- Tombol Sebelumnya (<) --}}
            @if ($paginator->onFirstPage())
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-300 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Halaman Sebelumnya">
                    &lsaquo;
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-2xs no-underline" rel="prev" title="Halaman Sebelumnya">
                    &lsaquo;
                </a>
            @endif

            {{-- Daftar Nomor Halaman (Cukup 1-5 dan bertambah per rentang 5) --}}
            @for ($page = $startPage; $page <= $endPage; $page++)
                @if ($page == $currentPage)
                    <span class="min-w-8 h-8 px-2 flex items-center justify-center rounded-lg bg-blue-600 text-white font-extrabold shadow-sm border border-blue-600" aria-current="page">
                        {{ $page }}
                    </span>
                @else
                    <a href="{{ $paginator->url($page) }}" class="min-w-8 h-8 px-2 flex items-center justify-center rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-2xs no-underline">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Tombol Selanjutnya (>) --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-2xs no-underline" rel="next" title="Halaman Selanjutnya">
                    &rsaquo;
                </a>
            @else
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-300 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Halaman Selanjutnya">
                    &rsaquo;
                </span>
            @endif

            {{-- Tombol Halaman Terakhir (>>) --}}
            @if ($currentPage < $lastPage)
                <a href="{{ $paginator->url($lastPage) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-white hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold border border-slate-300 transition-colors shadow-2xs no-underline" title="Halaman Terakhir ({{ number_format($lastPage) }})">
                    &raquo;
                </a>
            @else
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-300 font-bold cursor-not-allowed border border-slate-200" aria-disabled="true" title="Halaman Terakhir">
                    &raquo;
                </span>
            @endif

        </nav>

        {{-- Form Cepat Lompat ke Halaman Tertentu --}}
        @if ($lastPage > 5)
            <form method="GET" action="{{ url()->current() }}" class="hidden sm:flex items-center gap-1.5 ml-1 pl-2 border-l border-slate-300 text-xs">
                @foreach($queryParams as $qKey => $qVal)
                    @if(is_array($qVal))
                        @foreach($qVal as $subKey => $subVal)
                            <input type="hidden" name="{{ $qKey }}[{{ $subKey }}]" value="{{ $subVal }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $qKey }}" value="{{ $qVal }}">
                    @endif
                @endforeach
                <span class="text-slate-500 font-medium">Lompat:</span>
                <input type="number" 
                       name="page" 
                       min="1" 
                       max="{{ $lastPage }}" 
                       placeholder="{{ $currentPage }}" 
                       class="w-16 h-8 px-2 text-center font-bold bg-white border border-slate-300 rounded-lg text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none shadow-2xs" 
                       title="Ketik nomor halaman lalu tekan Enter (1 - {{ number_format($lastPage) }})">
                <button type="submit" 
                        class="h-8 px-2.5 bg-slate-100 hover:bg-blue-600 hover:text-white text-slate-700 font-bold rounded-lg border border-slate-300 transition-all cursor-pointer shadow-2xs" 
                        title="Buka Halaman">
                    Go
                </button>
            </form>
        @endif
    </div>
@endif
