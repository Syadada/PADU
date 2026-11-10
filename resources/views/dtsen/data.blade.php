@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dtsenDataPageApp({{ json_encode(array_keys($activeColumnsMap)) }})">
    
    <!-- Header Banner Modul -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg font-bold flex items-center justify-center"><i class="{{ $activeDatasetScopeIcon ?? 'fi fi-rr-table' }}"></i></span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Tabel Master Data Terpadu
                </h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border flex items-center gap-1 {{ $activeDatasetScopeBadge ?? 'bg-indigo-50 text-indigo-700 border-indigo-200' }}">
                    <i class="{{ $activeDatasetScopeIcon ?? 'fi fi-rr-apps' }}"></i> {{ $activeDatasetScopeLabel ?? 'Set Terpadu (Keduanya)' }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold font-mono border border-slate-200">
                    {{ number_format($records->total()) }} Baris
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                {{ $activeDatasetScopeDesc ?? 'Pusat data terpadu: Relasi otomatis KK & Individu, sensor privasi NIK/KK, filter pivot Excel, dan transliterasi teks resmi BPS-Bappenas.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Atur Kolom -->
            <button type="button" 
                    @click="showColumnModal = true" 
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-settings-sliders"></i> <span>Atur Kolom</span> (<span x-text="visibleCols.length"></span>/{{ count($activeColumnsMap) }})
            </button>
            
            <!-- Unduh Hasil Pencarian (.CSV) -->
            <a href="{{ route('dtsen.export-search', request()->all()) }}" 
               class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
               title="Unduh hasil pencarian dan filter ke berkas CSV">
                <i class="fi fi-rr-file-download text-sm"></i> <span>Unduh Hasil (.CSV)</span>
            </a>

            <!-- Ekspor Data Modal Trigger (.ZIP) -->
            <button type="button" 
                    @click="showExportModal = true" 
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
                    @if($totalRows === 0) disabled title="Belum ada data untuk diekspor" @endif>
                <i class="fi fi-rr-upload"></i> <span>Ekspor Paket (.ZIP)</span>
            </button>

            <!-- Reset Filter -->
            <a href="{{ route('dtsen.data') }}" 
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-refresh"></i> <span>Reset Filter</span>
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong (Belum Ada Data) -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs"><i class="fi fi-rr-folder"></i></div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Silakan buka menu <strong><i class="fi fi-rr-folder"></i> 1. DATA</strong> di sidebar kiri untuk memilih dan mengimpor file CSV/Excel dari folder <code>src-dtsen/</code>.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <i class="fi fi-rr-bolt"></i> <span>Buka 1. DATA</span>
                </a>
            </div>
        </div>
    @else
        <!-- TABEL DATA MASTER CONTAINER -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5" id="tabel-data">
            
            <!-- SEARCH ENGINE & FILTER BAR -->
            <form method="GET" action="{{ route('dtsen.data') }}#tabel-data" id="dataTableSearchForm" class="space-y-3.5">
                <!-- Baris 1: Search By, Input Pencarian, Tombol Cari, Bulk Search, Unduh CSV -->
                <div class="flex flex-col md:flex-row items-stretch md:items-center gap-2">
                    <!-- Search By Selector -->
                    <div class="shrink-0">
                        <select name="search_by" 
                                aria-label="Target Pencarian Kolom"
                                class="w-full md:w-auto px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200/80 border border-slate-300 text-slate-900 font-black text-xs rounded-xl outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer transition-all shadow-xs">
                            <option value="all" {{ ($searchBy ?? 'all') === 'all' ? 'selected' : '' }}>Semua Kolom</option>
                            <option value="nik" {{ ($searchBy ?? '') === 'nik' ? 'selected' : '' }}>NIK (Kependudukan)</option>
                            <option value="kk" {{ ($searchBy ?? '') === 'kk' ? 'selected' : '' }}>No. Kartu Keluarga (KK)</option>
                            <option value="nama" {{ ($searchBy ?? '') === 'nama' ? 'selected' : '' }}>Nama Lengkap</option>
                            <option value="wilayah" {{ ($searchBy ?? '') === 'wilayah' ? 'selected' : '' }}>Wilayah Domisili</option>
                        </select>
                    </div>

                    <!-- Search Input Field -->
                    <div class="relative flex-1">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Ketik kata kunci pencarian (NIK, KK, Nama, atau Wilayah)..." 
                               class="w-full pl-10 pr-20 py-2.5 bg-slate-50 border border-slate-300 text-slate-900 font-bold text-xs rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all shadow-xs">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm"><i class="fi fi-rr-search"></i></span>
                        @if(!empty($search))
                            <a href="{{ route('dtsen.data', array_merge(request()->except('search'), ['page' => 1])) }}#tabel-data" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-700 font-extrabold text-xs"><i class="fi fi-rr-cross-small"></i> Clear</a>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 shrink-0">
                        <button type="submit" class="flex-1 md:flex-initial px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <i class="fi fi-rr-search"></i> <span>Cari Data</span>
                        </button>

                        <button type="button" 
                                @click="showBulkSearchModal = true" 
                                class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl border font-black text-xs flex items-center justify-center gap-1.5 transition-all shadow-xs cursor-pointer {{ !empty($bulkSearch) ? 'bg-indigo-600 text-white border-indigo-700 shadow-md shadow-indigo-600/20' : 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-indigo-200' }}">
                            <i class="fi fi-rr-users-alt"></i> <span>Bulk Search</span>
                            @if(!empty($bulkSearch))
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            @endif
                        </button>

                        <a href="{{ route('dtsen.export-search', request()->all()) }}" 
                           class="flex-1 md:flex-initial px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs shadow-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer"
                           title="Unduh hasil pencarian dan filter ke berkas CSV">
                            <i class="fi fi-rr-file-download text-sm"></i> <span>Unduh CSV</span>
                        </a>
                    </div>
                </div>

                <!-- Hidden inputs to retain current bulk search and column filters when submitting normal form -->
                @if(!empty($bulkSearch))
                    <input type="hidden" name="bulk_search" value="{{ $bulkSearch }}">
                    <input type="hidden" name="bulk_field" value="{{ $bulkField ?? 'nik' }}">
                @endif

                @foreach(request()->query() as $qk => $qv)
                    @if(!in_array($qk, ['page', 'search', 'search_by', 'bulk_search', 'bulk_field', 'desil', 'quality_status', 'order_by', 'per_page', '_token']) && !empty($qv))
                        <input type="hidden" name="{{ $qk }}" value="{{ is_array($qv) ? implode(',', $qv) : $qv }}">
                    @endif
                @endforeach

                <!-- Baris 2: Filter Cepat, Urutkan (Flexible Sorts), & Set Row Numbers -->
                <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-slate-100 text-xs">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-bold text-slate-500 text-[11px] uppercase tracking-wider">Filter Cepat:</span>
                        
                        <!-- Desil Filter -->
                        <select id="filter_desil" name="desil" onchange="document.getElementById('dataTableSearchForm').submit()" aria-label="Filter Berdasarkan Desil" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer outline-none focus:border-blue-500">
                            <option value="semua" {{ $desil === 'semua' ? 'selected' : '' }}>Semua Desil</option>
                            @for($d = 1; $d <= 10; $d++)
                                <option value="{{ $d }}" {{ (string)$desil === (string)$d ? 'selected' : '' }}>Desil {{ $d }}</option>
                            @endfor
                        </select>

                        <!-- QC Status Filter -->
                        <select id="filter_quality_status" name="quality_status" onchange="document.getElementById('dataTableSearchForm').submit()" aria-label="Filter Berdasarkan Status Quality Check" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer outline-none focus:border-blue-500">
                            <option value="semua" {{ $qualityStatus === 'semua' ? 'selected' : '' }}>Semua Status QC</option>
                            <option value="Valid" {{ $qualityStatus === 'Valid' ? 'selected' : '' }}>Hanya Valid</option>
                            <option value="Warning" {{ $qualityStatus === 'Warning' ? 'selected' : '' }}>Hanya Warning</option>
                            <option value="Critical" {{ $qualityStatus === 'Critical' ? 'selected' : '' }}>Hanya Critical</option>
                        </select>

                        <!-- Flexible Sorts Selector -->
                        <div class="flex items-center gap-1.5 pl-1 sm:pl-3 sm:border-l border-slate-200">
                            <span class="font-bold text-slate-500 text-[11px] uppercase tracking-wider"><i class="fi fi-rr-sort-alt"></i> Urutkan:</span>
                            <select name="order_by" onchange="document.getElementById('dataTableSearchForm').submit()" aria-label="Urutkan Data" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer outline-none focus:border-blue-500">
                                <option value="id_desc" {{ ($orderBy ?? '') === 'id_desc' ? 'selected' : '' }}>ID / Input Terbaru</option>
                                <option value="id_asc" {{ ($orderBy ?? '') === 'id_asc' ? 'selected' : '' }}>ID / Input Terlama</option>
                                <option value="nik_asc" {{ ($orderBy ?? '') === 'nik_asc' ? 'selected' : '' }}>NIK (0 - 9)</option>
                                <option value="nik_desc" {{ ($orderBy ?? '') === 'nik_desc' ? 'selected' : '' }}>NIK (9 - 0)</option>
                                <option value="nama_asc" {{ ($orderBy ?? '') === 'nama_asc' ? 'selected' : '' }}>Nama (A - Z)</option>
                                <option value="nama_desc" {{ ($orderBy ?? '') === 'nama_desc' ? 'selected' : '' }}>Nama (Z - A)</option>
                                <option value="gaji_desc" {{ ($orderBy ?? '') === 'gaji_desc' ? 'selected' : '' }}>Gaji Tertinggi</option>
                                <option value="gaji_asc" {{ ($orderBy ?? '') === 'gaji_asc' ? 'selected' : '' }}>Gaji Terendah</option>
                                <option value="tgl_asc" {{ ($orderBy ?? '') === 'tgl_asc' ? 'selected' : '' }}>Tanggal Lahir (Tertua)</option>
                                <option value="tgl_desc" {{ ($orderBy ?? '') === 'tgl_desc' ? 'selected' : '' }}>Tanggal Lahir (Termuda)</option>
                                <option value="desil_asc" {{ ($orderBy ?? '') === 'desil_asc' ? 'selected' : '' }}>Desil Terendah (1 - 10)</option>
                                <option value="desil_desc" {{ ($orderBy ?? '') === 'desil_desc' ? 'selected' : '' }}>Desil Tertinggi (10 - 1)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Set Row Numbers (Baris Per Halaman) -->
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-500 text-[11px] uppercase tracking-wider"><i class="fi fi-rr-list"></i> Baris:</span>
                        <select name="per_page" onchange="document.getElementById('dataTableSearchForm').submit()" aria-label="Jumlah Baris Per Halaman" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer outline-none focus:border-blue-500">
                            <option value="10" {{ ($perPage ?? 15) == 10 ? 'selected' : '' }}>10 baris</option>
                            <option value="15" {{ ($perPage ?? 15) == 15 ? 'selected' : '' }}>15 baris</option>
                            <option value="25" {{ ($perPage ?? 15) == 25 ? 'selected' : '' }}>25 baris</option>
                            <option value="50" {{ ($perPage ?? 15) == 50 ? 'selected' : '' }}>50 baris</option>
                            <option value="100" {{ ($perPage ?? 15) == 100 ? 'selected' : '' }}>100 baris</option>
                            <option value="250" {{ ($perPage ?? 15) == 250 ? 'selected' : '' }}>250 baris</option>
                            <option value="500" {{ ($perPage ?? 15) == 500 ? 'selected' : '' }}>500 baris</option>
                        </select>
                    </div>
                </div>
            </form>

            <!-- BULK SEARCH ACTIVE STATUS BANNER -->
            @if(!empty($bulkSearch))
                @php
                    $bulkItems = array_filter(preg_split('/[\r\n,;\s]+/', trim($bulkSearch)));
                    $bulkItemCount = count($bulkItems);
                @endphp
                <div class="flex items-center justify-between gap-3 p-3 bg-indigo-50 border border-indigo-200 rounded-xl text-xs font-bold text-indigo-900 shadow-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xs shrink-0"><i class="fi fi-rr-users-alt"></i></span>
                        <span>Bulk Search Aktif: Menyaring <strong>{{ number_format($bulkItemCount) }}</strong> nilai <strong>{{ strtoupper($bulkField ?? 'nik') }}</strong> spesifik.</span>
                    </div>
                    <button type="button" @click="clearBulkSearch()" class="px-2.5 py-1 rounded-lg bg-indigo-200 hover:bg-indigo-300 text-indigo-900 text-xs font-black cursor-pointer inline-flex items-center gap-1 transition-all">
                        <i class="fi fi-rr-cross-small"></i> <span>Hapus Bulk Search</span>
                    </button>
                </div>
            @endif

            <!-- COUNT RESULT & FILTER STATUS BANNER -->
            @php
                $hasCustomColFilters = false;
                foreach(request()->query() as $qk => $qv) {
                    if (!in_array($qk, ['page', 'search', 'search_by', 'bulk_search', 'bulk_field', 'desil', 'quality_status', 'order_by', 'per_page', '_token']) && !empty($qv) && $qv !== 'semua') {
                        $hasCustomColFilters = true;
                        break;
                    }
                }
                $isFiltered = !empty($search) || !empty($bulkSearch) || $desil !== 'semua' || $qualityStatus !== 'semua' || $hasCustomColFilters;
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3.5 rounded-xl bg-slate-50 border border-slate-200">
                <div class="flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-lg {{ $isFiltered ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-700' }} flex items-center justify-center font-black text-sm shrink-0">
                        <i class="fi fi-rr-stats"></i>
                    </span>
                    <div class="text-xs text-slate-700">
                        <span>Ditemukan <strong class="text-blue-700 text-sm font-black">{{ number_format($records->total()) }}</strong> data</span>
                        <span class="text-slate-500 font-normal"> (Menampilkan baris <strong>{{ number_format($records->firstItem() ?: 0) }}</strong> &ndash; <strong>{{ number_format($records->lastItem() ?: 0) }}</strong>)</span>
                        <span class="text-slate-400 font-normal"> dari total <strong>{{ number_format($totalSystemRows ?? $totalRows) }}</strong> data terdaftar di sistem.</span>
                    </div>
                </div>

                @if($isFiltered)
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-800 border border-blue-200 flex items-center gap-1">
                            <i class="fi fi-rr-filter"></i> Terfilter
                        </span>
                        <a href="{{ route('dtsen.data') }}#tabel-data" class="px-3 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-black text-xs hover:bg-rose-100 transition-colors flex items-center gap-1 shadow-2xs">
                            <i class="fi fi-rr-cross-small"></i> <span>Reset Semua Filter</span>
                        </a>
                    </div>
                @endif
            </div>

            <!-- MODAL BULK SEARCH BY NIK & KK -->
            <div x-show="showBulkSearchModal" 
                 x-cloak 
                 @keydown.escape.window="showBulkSearchModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-3 sm:p-6 flex items-center justify-center"
                 style="display: none;">
                <div @click.away="showBulkSearchModal = false" class="bg-white rounded-2xl max-w-xl w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200 my-auto flex flex-col">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-sm font-black shrink-0">
                                <i class="fi fi-rr-users-alt"></i>
                            </span>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Bulk Search (Pencarian Massal NIK / KK)</h3>
                                <p class="text-[11px] text-slate-500">Cari puluhan atau ratusan NIK/KK sekaligus dengan sekali tempel.</p>
                            </div>
                        </div>
                        <button type="button" @click="showBulkSearchModal = false" class="text-slate-400 hover:text-slate-600 text-base cursor-pointer">
                            <i class="fi fi-rr-cross"></i>
                        </button>
                    </div>

                    <form method="GET" action="{{ route('dtsen.data') }}#tabel-data" class="space-y-4">
                        <!-- Target Field Radio -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-black text-slate-700 uppercase tracking-wider">Pilih Target Identitas:</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition-all" 
                                       :class="bulkSearchField === 'nik' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 font-black ring-2 ring-indigo-500/30' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    <input type="radio" name="bulk_field" value="nik" x-model="bulkSearchField" class="accent-indigo-600">
                                    <span class="text-xs">NIK (Kependudukan)</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition-all" 
                                       :class="bulkSearchField === 'kk' ? 'bg-indigo-50 border-indigo-500 text-indigo-900 font-black ring-2 ring-indigo-500/30' : 'bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100'">
                                    <input type="radio" name="bulk_field" value="kk" x-model="bulkSearchField" class="accent-indigo-600">
                                    <span class="text-xs">Nomor Kartu Keluarga (KK)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Textarea -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <label for="bulkSearchTextarea" class="font-black text-slate-700 uppercase tracking-wider">Daftar Nomor Identitas:</label>
                                <span class="text-[11px] font-mono font-bold text-indigo-600" x-text="bulkSearchCount + ' nomor terdeteksi'"></span>
                            </div>
                            <textarea id="bulkSearchTextarea" 
                                      name="bulk_search" 
                                      x-model="bulkSearchInput"
                                      rows="7" 
                                      placeholder="Tempelkan daftar NIK atau No. KK di sini...&#10;Contoh:&#10;3201012010187820&#10;3201012010187819&#10;3201012010187818&#10;(Bisa dipisahkan baris baru, koma, titik koma, atau spasi)"
                                      class="w-full p-3 font-mono text-xs bg-slate-50 border border-slate-300 rounded-xl outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition-all"></textarea>
                        </div>

                        <!-- Retain other params -->
                        @if(!empty($search))
                            <input type="hidden" name="search" value="{{ $search }}">
                        @endif
                        @if(!empty($searchBy))
                            <input type="hidden" name="search_by" value="{{ $searchBy }}">
                        @endif
                        @if(!empty($perPage))
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                        @endif
                        @if(!empty($orderBy))
                            <input type="hidden" name="order_by" value="{{ $orderBy }}">
                        @endif

                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showBulkSearchModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-black text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                                <i class="fi fi-rr-check"></i> <span>Terapkan Bulk Search</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MODAL POPUP ATUR KOLOM TABEL (58 VARIABEL) -->
            <div x-show="showColumnModal" 
                 x-cloak 
                 @keydown.escape.window="showColumnModal = false"
                 class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-3 sm:p-6 flex items-center justify-center"
                 style="display: none;">
                <div @click.away="showColumnModal = false" class="bg-white rounded-2xl max-w-4xl w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200 my-auto flex flex-col max-h-[88vh]">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fi fi-rr-settings-sliders"></i> <span>Pengatur Tampilan Kolom Tabel ({{ count($activeColumnsMap) }} Variabel Terdeteksi)</span>
                            </h3>
                            <p class="text-xs text-slate-500">Centang atau hilangkan centang untuk memilih kolom yang ingin ditampilkan pada tabel data.</p>
                        </div>
                        <button type="button" @click="showColumnModal = false" class="text-slate-400 hover:text-slate-700 font-extrabold text-lg p-1 cursor-pointer">&times;</button>
                    </div>

                    <div class="space-y-4 overflow-y-auto pr-2 flex-1 scrollbar-thin max-h-[60vh]">
                        <!-- Group 1: Set Data Anggota Keluarga (Individu) -->
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                            <h4 class="text-xs font-black uppercase text-blue-900 flex items-center gap-1.5 border-b border-blue-200 pb-1.5 justify-between">
                                <span class="flex items-center gap-1.5"><i class="fi fi-rr-user"></i> Set Data Anggota Keluarga / Individu</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-100 text-blue-800 font-bold">{{ count($activeIndividuCols ?? []) }} Kolom Aktif</span>
                            </h4>
                            @if(!empty($activeIndividuCols) && count($activeIndividuCols) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($activeIndividuCols as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-white/80 rounded-lg border border-dashed border-blue-200 text-slate-500 text-xs flex items-center gap-2 font-medium">
                                    <i class="fi fi-rr-info text-blue-600"></i> Berkas dataset aktif tidak memuat variabel Set Individu (berfokus pada Set Rumah Tangga).
                                </div>
                            @endif
                        </div>

                        <!-- Group 2: Set Data Keluarga -->
                        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-2">
                            <h4 class="text-xs font-black uppercase text-emerald-900 flex items-center gap-1.5 border-b border-emerald-200 pb-1.5 justify-between">
                                <span class="flex items-center gap-1.5"><i class="fi fi-rr-home"></i> Set Data Keluarga / Rumah Tangga</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold">{{ count($activeKeluargaCols ?? []) }} Kolom Aktif</span>
                            </h4>
                            @if(!empty($activeKeluargaCols) && count($activeKeluargaCols) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($activeKeluargaCols as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-emerald-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-white/80 rounded-lg border border-dashed border-emerald-200 text-slate-500 text-xs flex items-center gap-2 font-medium">
                                    <i class="fi fi-rr-info text-emerald-600"></i> Berkas dataset aktif tidak memuat variabel Set Keluarga (berfokus pada Set Anggota Keluarga / Individu).
                                </div>
                            @endif
                        </div>

                        <!-- Group 3: Variabel Lainnya / Custom -->
                        @if(!empty($activeExtraCols) && count($activeExtraCols) > 0)
                            <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-purple-900 flex items-center gap-1.5 border-b border-purple-200 pb-1.5 justify-between">
                                    <span class="flex items-center gap-1.5"><i class="fi fi-rr-thumbtack"></i> Variabel Dataset Tambahan / Kustom</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-purple-100 text-purple-800 font-bold">{{ count($activeExtraCols) }} Kolom</span>
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($activeExtraCols as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-purple-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 flex items-center justify-between border-t border-slate-100 shrink-0">
                        <button type="button" @click="resetCols()" class="text-xs font-black hover:text-blue-800 transition-colors cursor-pointer text-blue-600">
                            <i class="fi fi-rr-refresh"></i> Tampilkan Semua Kolom
                        </button>
                        <button type="button" @click="showColumnModal = false" class="px-6 py-2.5 rounded-xl font-black text-xs shadow-md transition-all cursor-pointer bg-slate-900 text-white">
                            Selesai & Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal Filter Kolom Pivot Terpusat (Anti-Offscreen, 520px Max Width, & Pinned Footer) -->
            <div x-show="showFilterModal" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @keydown.escape.window="closeColumnFilter()"
                 class="padu-filter-overlay"
                 style="position: fixed; inset: 0; z-index: 99999; display: flex; align-items: center; justify-content: center; background-color: rgba(15, 23, 42, 0.65); padding: 1rem; backdrop-filter: blur(4px);" 
                 x-cloak>
                
                <!-- Backdrop Click to Close -->
                <div style="position: absolute; inset: 0;" @click="closeColumnFilter()"></div>

                <!-- Modal Dialog Container (Strictly 520px Width Max & Centered) -->
                <div @click.stop 
                     class="padu-filter-card" 
                     style="position: relative; z-index: 100000; width: 100%; max-width: 520px; max-height: 85vh; background: #ffffff; border-radius: 1rem; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); border: 1px solid #e2e8f0; display: flex; flex-direction: column; overflow: hidden; margin: auto;">
                    
                    <!-- Pinned Header -->
                    <div class="padu-filter-header" style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-black shrink-0">
                                <i class="fi fi-rr-filter"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-500">Filter Kolom Tabel</span>
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-mono" x-text="activeFilterType"></span>
                                </div>
                                <h3 class="text-sm font-black text-slate-900 truncate" x-text="activeFilterTitle"></h3>
                            </div>
                        </div>
                        <button type="button" @click="closeColumnFilter()" class="w-8 h-8 rounded-xl hover:bg-slate-200 text-slate-400 hover:text-slate-700 flex items-center justify-center text-lg font-black transition-colors cursor-pointer">
                            &times;
                        </button>
                    </div>

                    <!-- Scrollable Body with graceful height constraints -->
                    <div class="padu-filter-body" style="flex: 1 1 auto; overflow-y: auto; max-height: calc(85vh - 140px); padding: 1rem 1.25rem; display: flex; flex-direction: column; gap: 0.875rem;">
                        
                        <!-- QC Presets -->
                        <template x-if="activeFilterType === 'qc'">
                            <div class="space-y-1.5">
                                <span class="text-[11px] font-bold text-slate-600 block">Pilih Status Validitas:</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <button type="button" @click="setSingleFilterVal('Valid')" class="p-2.5 rounded-xl border border-emerald-300 bg-emerald-50/70 hover:bg-emerald-100 text-emerald-800 text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                                        <i class="fi fi-rr-check"></i> Valid
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('Warning')" class="p-2.5 rounded-xl border border-amber-300 bg-amber-50/70 hover:bg-amber-100 text-amber-800 text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                                        <i class="fi fi-rr-triangle-warning"></i> Warning
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('Critical')" class="p-2.5 rounded-xl border border-rose-300 bg-rose-50/70 hover:bg-rose-100 text-rose-800 text-xs font-bold flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                                        <i class="fi fi-rr-cross-circle"></i> Critical
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Age Presets & Range -->
                        <template x-if="activeFilterType === 'age'">
                            <div class="bg-blue-50/80 p-3 rounded-xl border border-blue-200 space-y-2 text-xs">
                                <span class="font-extrabold text-blue-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                    <i class="fi fi-rr-cake-birthday"></i> <span>Preset Rentang Usia Cepat</span>
                                </span>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <button type="button" @click="setSingleFilterVal('< 5')" class="px-2 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-800 transition-colors cursor-pointer text-center">
                                        &lt; 5 Thn (Balita)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('5 - 17')" class="px-2 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-800 transition-colors cursor-pointer text-center">
                                        5 - 17 Thn (Anak)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('18 - 40')" class="px-2 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-800 transition-colors cursor-pointer text-center">
                                        18 - 40 Thn (Pemuda)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('41 - 59')" class="px-2 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-800 transition-colors cursor-pointer text-center">
                                        41 - 59 Thn (Dewasa)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('>= 60')" class="col-span-2 px-2 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-800 transition-colors cursor-pointer text-center">
                                        &ge; 60 Thn (Lansia)
                                    </button>
                                </div>
                                <div class="pt-2 border-t border-blue-200">
                                    <span class="font-bold text-blue-900 text-[10px] block mb-1">Rentang Usia Kustom:</span>
                                    <div class="flex items-center gap-2">
                                        <input type="number" x-model="filterMinVal" min="0" max="120" placeholder="Min" class="w-1/2 text-xs bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500">
                                        <span class="text-slate-400 font-bold text-xs">s.d.</span>
                                        <input type="number" x-model="filterMaxVal" min="0" max="120" placeholder="Max" class="w-1/2 text-xs bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500">
                                        <button type="button" @click="applyFilterRange()" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer shrink-0">
                                            Set
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Salary Presets & Range -->
                        <template x-if="activeFilterType === 'salary'">
                            <div class="bg-emerald-50/80 p-3 rounded-xl border border-emerald-200 space-y-2 text-xs">
                                <span class="font-extrabold text-emerald-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                    <i class="fi fi-rr-dollar"></i> <span>Preset Rentang Gaji Cepat</span>
                                </span>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <button type="button" @click="setSingleFilterVal('< 1.5 Juta')" class="px-2 py-1.5 bg-white hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-800 transition-colors cursor-pointer text-center">
                                        &lt; Rp 1.5 Juta
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('1.5 - 3 Juta')" class="px-2 py-1.5 bg-white hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-800 transition-colors cursor-pointer text-center">
                                        Rp 1.5 - 3 Juta
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('3 - 5 Juta')" class="px-2 py-1.5 bg-white hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-800 transition-colors cursor-pointer text-center">
                                        Rp 3 - 5 Juta
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('5 - 10 Juta')" class="px-2 py-1.5 bg-white hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-800 transition-colors cursor-pointer text-center">
                                        Rp 5 - 10 Juta
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('> 10 Juta')" class="col-span-2 px-2 py-1.5 bg-white hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-800 transition-colors cursor-pointer text-center">
                                        &gt; Rp 10 Juta
                                    </button>
                                </div>
                                <div class="pt-2 border-t border-emerald-200">
                                    <span class="font-bold text-emerald-900 text-[10px] block mb-1">Rentang Nominal Kustom:</span>
                                    <div class="flex items-center gap-2">
                                        <input type="number" x-model="filterMinVal" placeholder="Min Rp" class="w-1/2 text-xs bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-emerald-500">
                                        <span class="text-slate-400 font-bold text-xs">-</span>
                                        <input type="number" x-model="filterMaxVal" placeholder="Max Rp" class="w-1/2 text-xs bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-emerald-500">
                                        <button type="button" @click="applyFilterRange()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors cursor-pointer shrink-0">
                                            Set
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Desil Presets -->
                        <template x-if="activeFilterType === 'desil'">
                            <div class="bg-amber-50/80 p-3 rounded-xl border border-amber-200 space-y-2 text-xs">
                                <span class="font-extrabold text-amber-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                    <i class="fi fi-rr-stats"></i> <span>Preset Desil Kesejahteraan</span>
                                </span>
                                <div class="grid grid-cols-2 gap-1.5">
                                    <button type="button" @click="setSingleFilterVal('1')" class="px-2 py-1.5 bg-white hover:bg-rose-100 border border-rose-300 rounded-lg text-xs font-bold text-rose-800 transition-colors cursor-pointer text-center">
                                        Desil 1 (Sangat Miskin)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('1 - 4')" class="px-2 py-1.5 bg-white hover:bg-amber-100 border border-amber-300 rounded-lg text-xs font-bold text-amber-800 transition-colors cursor-pointer text-center">
                                        Desil 1 - 4 (Bansos)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('5 - 7')" class="px-2 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-800 transition-colors cursor-pointer text-center">
                                        Desil 5 - 7 (Menengah)
                                    </button>
                                    <button type="button" @click="setSingleFilterVal('8 - 10')" class="px-2 py-1.5 bg-white hover:bg-emerald-100 border border-emerald-300 rounded-lg text-xs font-bold text-emerald-800 transition-colors cursor-pointer text-center">
                                        Desil 8 - 10 (Mampu)
                                    </button>
                                </div>
                                <div class="pt-2 border-t border-amber-200">
                                    <span class="font-bold text-amber-900 text-[10px] block mb-1">Pilih Angka Desil:</span>
                                    <div class="grid grid-cols-5 gap-1">
                                        <template x-for="d in [1,2,3,4,5,6,7,8,9,10]" :key="d">
                                            <button type="button" @click="toggleFilterVal(String(d))" 
                                                    class="py-1 rounded-lg text-xs font-black transition-colors cursor-pointer border"
                                                    :class="hasFilterVal(String(d)) ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white hover:bg-amber-100 text-slate-700 border-slate-200'"
                                                    x-text="'D' + d">
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Gender Presets -->
                        <template x-if="activeFilterType === 'gender'">
                            <div class="grid grid-cols-2 gap-2 text-xs">
                                <button type="button" @click="setSingleFilterVal('Laki-laki')" class="p-2.5 rounded-xl border border-blue-300 bg-blue-50/70 hover:bg-blue-100 text-blue-900 text-xs font-black flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                                    <i class="fi fi-rr-user"></i> Laki-laki
                                </button>
                                <button type="button" @click="setSingleFilterVal('Perempuan')" class="p-2.5 rounded-xl border border-pink-300 bg-pink-50/70 hover:bg-pink-100 text-pink-900 text-xs font-black flex items-center justify-center gap-1.5 cursor-pointer transition-all">
                                    <i class="fi fi-rr-user"></i> Perempuan
                                </button>
                            </div>
                        </template>

                        <!-- Search input box -->
                        <div class="relative">
                            <input type="text" 
                                   x-model="filterSearchVal" 
                                   @keydown.enter.prevent="applyActiveFilter()"
                                   :placeholder="'Cari / filter kata kunci ' + activeFilterTitle + '...'"
                                   class="w-full text-xs bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 pl-9 pr-8 outline-none focus:ring-2 focus:ring-blue-500 font-medium">
                            <i class="fi fi-rr-search absolute left-3 top-3 text-slate-400 text-xs"></i>
                            <button type="button" x-show="filterSearchVal" @click="filterSearchVal = ''" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-600 text-sm font-bold cursor-pointer">
                                &times;
                            </button>
                        </div>

                        <!-- Special Identity Notice & Bulk Search CTA for NIK / KK -->
                        <template x-if="activeFilterType === 'identity'">
                            <div class="p-3.5 bg-indigo-50/70 border border-indigo-200 rounded-xl space-y-2 text-xs">
                                <div class="flex items-center gap-2 font-extrabold text-indigo-900">
                                    <i class="fi fi-rr-id-badge text-indigo-600 text-sm"></i>
                                    <span>Pencarian Nomor Identitas</span>
                                </div>
                                <p class="text-slate-600 text-[11px] leading-relaxed">
                                    Ketik nomor lengkap atau awalan NIK/KK pada kolom pencarian di atas, lalu tekan <strong>Terapkan Filter</strong> atau tombol Enter.
                                </p>
                                <div class="pt-2 border-t border-indigo-100 flex items-center justify-between">
                                    <span class="text-[11px] text-indigo-700 font-bold">Punya banyak NIK / KK?</span>
                                    <button type="button" @click="closeColumnFilter(); showBulkSearchModal = true;" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-black text-xs transition-colors flex items-center gap-1 cursor-pointer shadow-xs">
                                        <i class="fi fi-rr-users-alt"></i> <span>Bulk Search NIK & KK</span>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Distinct Values Checkbox List (Strictly Contained with 220px max-height scroll) -->
                        <template x-if="activeFilterType !== 'identity'">
                            <div class="padu-filter-list" style="max-height: 220px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 0.75rem; background: #ffffff;">
                                <div class="p-2.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between text-xs font-bold text-slate-700 sticky top-0 z-10">
                                    <button type="button" @click="selectAllFilterVals()" class="text-blue-600 hover:text-blue-800 flex items-center gap-1.5 cursor-pointer">
                                        <i class="fi fi-rr-check-double"></i>
                                        <span x-text="filterSelectedVals.length >= filterDistinctValues.length && filterDistinctValues.length > 0 ? 'Batalkan Semua' : 'Pilih Semua (' + filterDistinctValues.length + ')'"></span>
                                    </button>
                                    <span class="text-[11px] font-mono text-slate-500" x-text="filterSelectedVals.length + ' dipilih'"></span>
                                </div>

                                <div style="padding: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem;">
                                    <template x-for="(item, idx) in filterDistinctValues.filter(v => !filterSearchVal || String(v).toLowerCase().includes(filterSearchVal.toLowerCase()))" :key="idx">
                                        <label class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg hover:bg-blue-50 cursor-pointer font-medium text-slate-800 transition-colors"
                                               :class="hasFilterVal(item) ? 'bg-blue-50/90 font-bold text-blue-900 border border-blue-200' : 'border border-transparent'">
                                            <input type="checkbox" 
                                                   :checked="hasFilterVal(item)" 
                                                   @change="toggleFilterVal(item)"
                                                   class="accent-blue-600 rounded cursor-pointer w-4 h-4">
                                            <span class="truncate text-xs" x-text="item"></span>
                                        </label>
                                    </template>

                                    <div x-show="filterDistinctValues.length === 0" class="py-6 text-center text-slate-400 text-xs">
                                        <i class="fi fi-rr-info text-base block mb-1"></i>
                                        Filter teks fleksibel aktif. Ketik nilai di kolom input di atas lalu tekan Terapkan.
                                    </div>
                                </div>
                            </div>
                        </template>

                    </div>

                    <!-- Pinned Footer: ALWAYS 100% visible -->
                    <div class="padu-filter-footer" style="padding: 0.875rem 1.25rem; background: #f8fafc; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-shrink: 0;">
                        <button type="button" @click="clearActiveFilter()" class="px-3.5 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 rounded-xl transition-colors cursor-pointer border border-rose-200 flex items-center gap-1.5">
                            <i class="fi fi-rr-trash"></i> <span>Reset Filter</span>
                        </button>
                        <div class="flex items-center gap-2">
                            <button type="button" @click="closeColumnFilter()" class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-200 rounded-xl transition-colors cursor-pointer">
                                Batal
                            </button>
                            <button type="button" @click="applyActiveFilter()" class="px-5 py-2 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                                <i class="fi fi-rr-check"></i>
                                <span>Terapkan Filter</span>
                                <span x-show="filterSelectedVals.length > 0" class="px-1.5 py-0.2 rounded-full bg-white text-emerald-800 font-extrabold text-[10px]" x-text="filterSelectedVals.length"></span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- TABEL MASTER ADAPTIF DENGAN STICKY HEADER -->
            <div class="overflow-x-auto border border-slate-200 rounded-xl relative shadow-xs scrollbar-thin min-h-[380px]">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead class="sticky top-0 z-20 bg-slate-100 shadow-xs">
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100">No</th>
                            
                            <!-- QC Status Header dengan Trigger Filter Modal -->
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100">
                                <div class="flex items-center justify-between gap-2">
                                    <span>QC Status</span>
                                    @php
                                        $qsParam = request('quality_status');
                                        $initialQs = array_values(array_filter(
                                            is_array($qsParam) ? $qsParam : explode(',', (string)$qsParam), 
                                            fn($v) => $v !== '' && $v !== 'semua'
                                        ));
                                    @endphp
                                    <button type="button" 
                                            @click="openColumnFilter('quality_status', 'QC Status', 'qc', ['Valid', 'Warning', 'Critical'], {{ json_encode($initialQs) }})" 
                                            class="px-1.5 py-0.5 rounded transition-colors flex items-center gap-1 cursor-pointer {{ count($initialQs) > 0 ? 'bg-blue-600 text-white font-extrabold text-[10px] shadow-xs' : 'text-slate-400 hover:bg-slate-200 hover:text-slate-700' }}"
                                            title="Filter QC Status">
                                        <i class="fi fi-rr-angle-small-down text-[11px]"></i>
                                        @if(count($initialQs) > 0)
                                            <span class="text-[10px] font-black">{{ count($initialQs) }}</span>
                                        @endif
                                    </button>
                                </div>
                            </th>

                            <!-- Header Kolom Dinamis dengan Trigger Filter Modal Pivot Excel -->
                            @foreach($activeColumnsMap as $colKey => $colTitle)
                                @php
                                    $reqVal = request($colKey);
                                    $synonymsMap = [
                                        'jenis_kelamin' => ['gender', 'jk', 'sex'],
                                        'usia' => ['umur', 'age'],
                                        'gaji_bulanan' => ['gaji', 'pendapatan', 'income', 'salary', 'penghasilan'],
                                        'desil_nasional' => ['desil', 'desil_kesejahteraan'],
                                        'status_bekerja' => ['pekerjaan', 'status_kerja'],
                                        'nomor_induk_kependudukan' => ['nik', 'no_nik'],
                                        'nomor_kartu_keluarga' => ['kk', 'no_kk'],
                                        'pendidikan' => ['pendidikan_terakhir', 'ijazah_tertinggi_yang_dimiliki'],
                                        'status_kawin' => ['status_pernikahan'],
                                    ];
                                    if (empty($reqVal) && isset($synonymsMap[$colKey])) {
                                        foreach ($synonymsMap[$colKey] as $syn) {
                                            if (request()->has($syn) && !empty(request($syn))) {
                                                $reqVal = request($syn);
                                                break;
                                            }
                                        }
                                    }
                                    $initialSelectedArr = array_values(array_filter(
                                        is_array($reqVal) ? $reqVal : explode(',', (string)$reqVal), 
                                        fn($v) => $v !== '' && $v !== 'semua'
                                    ));

                                    $lowerColKey = strtolower($colKey);
                                    $filterType = 'general';
                                    if (in_array($lowerColKey, ['usia', 'umur', 'age']) || str_contains($lowerColKey, 'usia') || str_contains($lowerColKey, 'umur')) {
                                        $filterType = 'age';
                                    } elseif (in_array($lowerColKey, ['gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income']) || str_contains($lowerColKey, 'gaji') || str_contains($lowerColKey, 'pendapatan')) {
                                        $filterType = 'salary';
                                    } elseif (str_contains($lowerColKey, 'desil')) {
                                        $filterType = 'desil';
                                    } elseif (in_array($lowerColKey, ['jenis_kelamin', 'gender', 'jk', 'sex'])) {
                                        $filterType = 'gender';
                                    } elseif (in_array($lowerColKey, ['nik', 'nomor_induk_kependudukan', 'kk', 'nomor_kartu_keluarga', 'no_nik', 'no_kk', 'id_pelanggan_pln', 'no_telepon', 'nomor_telepon'])) {
                                        $filterType = 'identity';
                                    }
                                @endphp
                                <th x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs font-bold text-slate-700 tracking-wider whitespace-nowrap relative group bg-slate-100">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="{{ count($initialSelectedArr) > 0 ? 'text-blue-700 font-extrabold' : '' }}">{{ $colTitle }}</span>

                                        <button type="button" 
                                                @click="openColumnFilter('{{ $colKey }}', '{{ addslashes($colTitle) }}', '{{ $filterType }}', {{ json_encode($importColumnDistinctValues[$colKey] ?? []) }}, {{ json_encode($initialSelectedArr) }})" 
                                                class="px-1.5 py-0.5 rounded transition-all flex items-center gap-1 cursor-pointer {{ count($initialSelectedArr) > 0 ? 'bg-blue-600 text-white font-extrabold text-[10px] shadow-sm' : 'text-slate-400 hover:bg-slate-200 hover:text-slate-700' }}"
                                                title="Filter Kolom {{ $colTitle }}">
                                            <i class="fi fi-rr-angle-small-down text-[11px]"></i>
                                            @if(count($initialSelectedArr) > 0)
                                                <span class="text-[10px] font-black bg-white text-blue-900 px-1 py-0.2 rounded-full">{{ count($initialSelectedArr) }}</span>
                                            @endif
                                        </button>
                                    </div>
                                </th>
                            @endforeach

                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider text-center bg-slate-100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($records as $index => $row)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3 text-xs text-slate-500 font-bold">{{ $records->firstItem() + $index }}</td>
                                <td class="px-4 py-3 text-xs">
                                    @php
                                        $errList = is_array($row->quality_issues) ? $row->quality_issues : [];
                                        $errCount = count($errList);
                                        $tooltipStr = !empty($errList) ? implode(' | ', $errList) : '';
                                    @endphp
                                    @if($row->quality_status === 'Valid' && $errCount === 0)
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] whitespace-nowrap flex items-center gap-1"><i class="fi fi-rr-check"></i> Valid</span>
                                    @elseif($errCount >= 3)
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-black text-[10px] whitespace-nowrap shadow-xs flex items-center gap-1" title="{{ $tooltipStr }}"><i class="fi fi-rr-cross-circle"></i> {{ $errCount }} EROR TERDETEKSI</span>
                                    @elseif($errCount == 2)
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500 text-white font-bold text-[10px] whitespace-nowrap flex items-center gap-1" title="{{ $tooltipStr }}"><i class="fi fi-rr-cross-circle"></i> 2 EROR</span>
                                    @elseif($row->quality_status === 'Warning')
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] whitespace-nowrap flex items-center gap-1" title="{{ $tooltipStr }}"><i class="fi fi-rr-triangle-warning"></i> Warning (1 Eror)</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px] whitespace-nowrap flex items-center gap-1" title="{{ $tooltipStr }}"><i class="fi fi-rr-cross-circle"></i> Critical (1 Eror)</span>
                                    @endif
                                </td>

                                <!-- Dynamic Cell Values -->
                                @foreach($activeColumnsMap as $colKey => $colTitle)
                                    <td x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs text-slate-800 whitespace-nowrap">
                                        @if($colKey === 'nomor_induk_kependudukan' || $colKey === 'nik')
                                            <span class="font-mono font-bold text-slate-900">
                                                <span x-show="isMasked">{{ $row->masked_nik ?? '****************' }}</span>
                                                <span x-show="!isMasked" x-cloak>{{ $row->$colKey ?? '-' }}</span>
                                            </span>
                                        @elseif($colKey === 'nomor_kartu_keluarga' || $colKey === 'no_kk' || $colKey === 'nomor_kartu_keluarga_kel')
                                            <span class="font-mono text-slate-800">
                                                <span x-show="isMasked">{{ $row->masked_kk ?? '****************' }}</span>
                                                <span x-show="!isMasked" x-cloak>{{ $row->$colKey ?? '-' }}</span>
                                            </span>
                                        @elseif($colKey === 'nama' || $colKey === 'nama_lengkap')
                                            <span class="font-bold text-slate-900">
                                                <span x-show="isMasked">{{ $row->masked_nama ?? '***' }}</span>
                                                <span x-show="!isMasked" x-cloak>{{ $row->$colKey ?? '-' }}</span>
                                            </span>
                                        @elseif($colKey === 'desil_nasional' || $colKey === 'desil')
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">Desil {{ $row->$colKey ?? '-' }}</span>
                                        @elseif($colKey === 'tanggal_lahir')
                                            <span>{{ !empty($row->tanggal_lahir) ? (is_object($row->tanggal_lahir) ? $row->tanggal_lahir->format('d/m/Y') : date('d/m/Y', strtotime($row->tanggal_lahir))) : '-' }}</span>
                                        @else
                                            @php
                                                $rawVal = $row->$colKey ?? null;
                                                if (is_numeric($rawVal) && strlen((string)$rawVal) < 15) {
                                                    $displayVal = number_format((float)$rawVal, (floor((float)$rawVal) == (float)$rawVal ? 0 : 2), ',', '.');
                                                } else {
                                                    $displayVal = $rawVal ?: '-';
                                                }
                                            @endphp
                                            <span class="font-semibold text-slate-900">{{ $displayVal }}</span>
                                        @endif
                                    </td>
                                @endforeach

                                <td class="px-4 py-3 text-xs text-center whitespace-nowrap">
                                    <button type="button" @click="openPreview({{ $row->id }})" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold text-[11px] rounded-lg shadow-sm cursor-pointer inline-flex items-center gap-1 transition-all">
                                        <i class="fi fi-rr-eye"></i> <span>Detail</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeColumnsMap) + 3 }}" class="px-4 py-16 text-center">
                                    <div class="space-y-3 max-w-md mx-auto py-6">
                                        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-bold mx-auto shadow-xs"><i class="fi fi-rr-search"></i></div>
                                        <div class="space-y-1">
                                            <h4 class="font-extrabold text-sm text-slate-800">Tidak ada data yang sesuai dengan kriteria</h4>
                                            <p class="text-xs text-slate-500 font-medium">Coba sesuaikan kata kunci pencarian atau bersihkan filter.</p>
                                        </div>
                                        <div class="pt-2">
                                            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                                <i class="fi fi-rr-refresh"></i> <span>Reset Semua Filter</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            <div class="flex flex-col lg:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-200/80">
                <div class="text-xs text-slate-600 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 border border-blue-200 font-extrabold shadow-2xs">
                        <i class="fi fi-rr-document"></i>
                        <span>Halaman {{ number_format($records->currentPage()) }} dari {{ number_format($records->lastPage()) }} Halaman</span>
                    </span>
                    <span class="text-slate-300 font-bold hidden sm:inline">&bull;</span>
                    <span class="font-medium text-slate-600">
                        Menampilkan <strong>{{ number_format($records->firstItem() ?: 0) }}</strong> s.d. <strong>{{ number_format($records->lastItem() ?: 0) }}</strong> dari <strong>{{ number_format($records->total()) }}</strong> total data terdaftar
                    </span>
                </div>
                <div>
                    {{ $records->links() }}
                </div>
            </div>

        </div>
    @endif

</div>

@push('scripts')
<script src="{{ asset('js/data-page.js') }}"></script>
@endpush
@endsection
