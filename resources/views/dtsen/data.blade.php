@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dtsenDataPageApp({{ json_encode(array_keys($activeColumnsMap)) }})">
    
    <!-- Header Banner Modul -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg font-bold">📋</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Tabel Master Data Terpadu (58 Variabel DTSEN 2026)
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-bold font-mono">
                    {{ number_format($records->total()) }} Baris
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Pusat data terpadu: Relasi otomatis KK & Individu, sensor privasi NIK/KK, filter pivot Excel, dan transliterasi teks resmi BPS-Bappenas.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Atur Kolom -->
            <button type="button" 
                    @click="showColumnModal = true" 
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>🎛️</span> Atur Kolom (<span x-text="visibleCols.length"></span>/{{ count($activeColumnsMap) }})
            </button>
            
            <!-- Ekspor Data Modal Trigger -->
            <button type="button" 
                    @click="showExportModal = true" 
                    class="px-3.5 py-2 bg-emerald-50 hover:bg-emerald-100 text-emerald-800 font-bold text-xs rounded-xl border border-emerald-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer"
                    @if($totalRows === 0) disabled title="Belum ada data untuk diekspor" @endif>
                <span>📤</span> Ekspor (.ZIP)
            </button>

            <!-- Reset Filter -->
            <a href="{{ route('dtsen.data') }}" 
               class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>🔄</span> Reset Filter
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong (Belum Ada Data) -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">📂</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Silakan buka menu <strong>📁 Manajemen Berkas</strong> di sidebar kiri untuk memilih dan mengimpor file CSV/Excel dari folder <code>src-dtsen/</code>.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Buka Manajemen Berkas
                </a>
            </div>
        </div>
    @else
        <!-- TABEL DATA MASTER CONTAINER -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5" id="tabel-data">
            
            <!-- SEARCH ENGINE & FILTER BAR -->
            <form method="GET" action="{{ route('dtsen.data') }}#tabel-data" id="dataTableSearchForm" class="space-y-3">
                <div class="flex flex-col sm:flex-row items-center gap-2">
                    <div class="relative w-full">
                        <input type="text" 
                               name="search" 
                               value="{{ $search }}" 
                               placeholder="Cari NIK, KK, Nama Lengkap, atau Wilayah domisili..." 
                               class="w-full pl-10 pr-20 py-2.5 bg-slate-50 border border-slate-300 text-slate-900 font-bold text-xs rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all shadow-xs">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 text-sm">🔍</span>
                        @if(!empty($search))
                            <a href="{{ route('dtsen.data') }}#tabel-data" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-700 font-extrabold text-xs">✕ Clear</a>
                        @endif
                    </div>

                    <button type="submit" class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all flex items-center justify-center gap-1.5 shrink-0 cursor-pointer">
                        <span>⚡</span> Cari Data
                    </button>
                </div>

                <!-- FILTER QUICK PRESETS -->
                <div class="flex flex-wrap items-center gap-2 pt-1 text-xs">
                    <span class="font-bold text-slate-500 text-[11px] uppercase tracking-wider">Filter Cepat:</span>
                    
                    <!-- Desil Filter -->
                    <select id="filter_desil" name="desil" aria-label="Filter Berdasarkan Desil" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer outline-none focus:border-blue-500">
                        <option value="semua" {{ $desil === 'semua' ? 'selected' : '' }}>Semua Desil</option>
                        @for($d = 1; $d <= 10; $d++)
                            <option value="{{ $d }}" {{ (string)$desil === (string)$d ? 'selected' : '' }}>Desil {{ $d }}</option>
                        @endfor
                    </select>

                    <!-- QC Status Filter -->
                    <select id="filter_quality_status" name="quality_status" aria-label="Filter Berdasarkan Status Quality Check" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-bold text-xs cursor-pointer outline-none focus:border-blue-500">
                        <option value="semua" {{ $qualityStatus === 'semua' ? 'selected' : '' }}>Semua Status QC</option>
                        <option value="Valid" {{ $qualityStatus === 'Valid' ? 'selected' : '' }}>🟢 Hanya Valid</option>
                        <option value="Warning" {{ $qualityStatus === 'Warning' ? 'selected' : '' }}>🟡 Hanya Warning</option>
                        <option value="Critical" {{ $qualityStatus === 'Critical' ? 'selected' : '' }}>🔴 Hanya Critical</option>
                    </select>

                    @php
                        $hasCustomColFilters = false;
                        foreach(request()->query() as $qk => $qv) {
                            if (!in_array($qk, ['page', 'search', 'desil', 'quality_status', '_token']) && !empty($qv) && $qv !== 'semua') {
                                $hasCustomColFilters = true;
                                break;
                            }
                        }
                    @endphp

                    @if(!empty($search) || $desil !== 'semua' || $qualityStatus !== 'semua' || $hasCustomColFilters)
                        <a href="{{ route('dtsen.data') }}#tabel-data" class="px-2.5 py-1.5 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-bold text-[11px] hover:bg-rose-100 transition-colors flex items-center gap-1 shadow-2xs">
                            <span>✕</span> Hapus Semua Filter
                        </a>
                    @endif
                </div>
            </form>

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
                                <span>⚙️</span> Pengatur Tampilan Kolom Tabel (58 Variabel DTSEN)
                            </h3>
                            <p class="text-xs text-slate-500">Centang atau hilangkan centang untuk memilih kolom yang ingin ditampilkan pada tabel.</p>
                        </div>
                        <button type="button" @click="showColumnModal = false" class="text-slate-400 hover:text-slate-700 font-extrabold text-lg p-1 cursor-pointer">&times;</button>
                    </div>

                    <div class="space-y-4 overflow-y-auto pr-2 flex-1 scrollbar-thin max-h-[60vh]">
                        <!-- Group 1: Set Data Anggota Keluarga (Individu) -->
                        @if(!empty($officialIndividuVars))
                            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-blue-900 flex items-center gap-1.5 border-b border-blue-200 pb-1.5">
                                    👤 Set Data Anggota Keluarga / Individu ({{ count($officialIndividuVars) }} Variabel)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($officialIndividuVars as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Group 2: Set Data Keluarga -->
                        @if(!empty($officialKeluargaVars))
                            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-emerald-900 flex items-center gap-1.5 border-b border-emerald-200 pb-1.5">
                                    🏠 Set Data Keluarga ({{ count($officialKeluargaVars) }} Variabel)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($officialKeluargaVars as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-emerald-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Group 3: Variabel Lainnya / Custom -->
                        @php
                            $extraCols = array_diff_key($activeColumnsMap, $officialIndividuVars, $officialKeluargaVars);
                        @endphp
                        @if(!empty($extraCols))
                            <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-purple-900 flex items-center gap-1.5 border-b border-purple-200 pb-1.5">
                                    📌 Variabel Berkas CSV / Tambahan ({{ count($extraCols) }} Variabel)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($extraCols as $cKey => $cTitle)
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
                            🔄 Tampilkan Semua Kolom
                        </button>
                        <button type="button" @click="showColumnModal = false" class="px-6 py-2.5 rounded-xl font-black text-xs shadow-md transition-all cursor-pointer bg-slate-900 text-white">
                            Selesai & Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- TABEL MASTER ADAPTIF DENGAN STICKY HEADER -->
            <div class="overflow-x-auto overflow-y-auto max-h-[70vh] min-h-[420px] border border-slate-200 rounded-xl relative shadow-xs scrollbar-thin">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead class="sticky top-0 z-20 bg-slate-100 shadow-xs">
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100">No</th>
                            
                            <!-- QC Status Header dengan Filter Pivot Dropdown -->
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100 relative group">
                                <div class="flex items-center justify-between gap-2">
                                    <span>QC Status</span>
                                    @php
                                        $qsParam = request('quality_status');
                                        $initialQs = array_values(array_filter(
                                            is_array($qsParam) ? $qsParam : explode(',', (string)$qsParam), 
                                            fn($v) => $v !== '' && $v !== 'semua'
                                        ));
                                    @endphp
                                    <div class="relative" x-data="{ 
                                        open: false, 
                                        searchVal: '',
                                        selectedVals: {{ json_encode($initialQs) }},
                                        hasVal(val) {
                                            if (val === null || val === undefined) return false;
                                            const target = String(val).trim().toLowerCase();
                                            return this.selectedVals.some(v => String(v).trim().toLowerCase() === target);
                                        },
                                        toggleVal(val) {
                                            if (this.hasVal(val)) {
                                                const target = String(val).trim().toLowerCase();
                                                this.selectedVals = this.selectedVals.filter(v => String(v).trim().toLowerCase() !== target);
                                            } else {
                                                this.selectedVals.push(val);
                                            }
                                        },
                                        applyFilter() {
                                            const url = new URL(window.location.href);
                                            if (this.selectedVals.length > 0) {
                                                url.searchParams.set('quality_status', this.selectedVals.join(','));
                                            } else {
                                                url.searchParams.delete('quality_status');
                                            }
                                            url.searchParams.set('page', '1');
                                            url.hash = 'tabel-data';
                                            this.open = false;
                                            window.location.href = url.toString();
                                        },
                                        clearFilter() {
                                            this.selectedVals = [];
                                            this.searchVal = '';
                                            const url = new URL(window.location.href);
                                            url.searchParams.delete('quality_status');
                                            url.searchParams.set('page', '1');
                                            url.hash = 'tabel-data';
                                            this.open = false;
                                            window.location.href = url.toString();
                                        }
                                    }">
                                        <button type="button" @click.stop="open = !open" 
                                                class="px-1.5 py-0.5 rounded transition-colors flex items-center gap-1 cursor-pointer"
                                                :class="selectedVals.length > 0 ? 'bg-blue-600 text-white font-extrabold text-[10px] shadow-xs' : 'text-slate-400 hover:bg-slate-200 hover:text-slate-700'"
                                                title="Filter QC Status">
                                            <span class="text-[10px]">🔽</span>
                                            <span x-show="selectedVals.length > 0" class="text-[10px] font-black" x-text="selectedVals.length"></span>
                                        </button>

                                        <div x-show="open" @click.outside="open = false" x-cloak 
                                             class="absolute left-0 top-full mt-1.5 w-56 bg-white rounded-xl shadow-2xl border border-slate-200 p-3 z-50 space-y-2.5 text-slate-800 normal-case tracking-normal">
                                            <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                                <span class="font-extrabold text-[11px] text-blue-900 uppercase">Filter: QC Status</span>
                                                <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">&times;</button>
                                            </div>
                                            @php $qcItems = ['Valid', 'Warning', 'Critical']; @endphp
                                            <div class="space-y-1 text-xs border border-slate-100 rounded-lg p-1">
                                                @foreach($qcItems as $qItem)
                                                    <label class="flex items-center gap-2 px-2 py-1 rounded hover:bg-blue-50 cursor-pointer font-medium text-slate-700 transition-colors"
                                                           :class="hasVal('{{ $qItem }}') ? 'bg-blue-50 font-bold text-blue-900' : ''">
                                                        <input type="checkbox" 
                                                               :checked="hasVal('{{ $qItem }}')" 
                                                               @change="toggleVal('{{ $qItem }}')"
                                                               class="accent-blue-600 rounded cursor-pointer">
                                                        <span class="truncate text-xs">
                                                            @if($qItem === 'Valid') 🟢 Valid @elseif($qItem === 'Warning') 🟡 Warning @else 🔴 Critical @endif
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                            <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                                <button type="button" @click.prevent.stop="clearFilter()" class="px-2.5 py-1.5 text-[11px] font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                                                    Reset
                                                </button>
                                                <button type="button" @click.prevent.stop="applyFilter()" class="px-3.5 py-1.5 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                                                    <span>✓</span> Terapkan <span x-show="selectedVals.length > 0" x-text="'(' + selectedVals.length + ')'"></span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </th>

                            <!-- Header Kolom Dinamis dengan Popover Filter Pivot Excel (Tersedia untuk Seluruh Variabel) -->
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
                                    $isAge = in_array($lowerColKey, ['usia', 'umur', 'age']) || str_contains($lowerColKey, 'usia') || str_contains($lowerColKey, 'umur');
                                    $isSalary = in_array($lowerColKey, ['gaji', 'gaji_bulanan', 'pendapatan', 'penghasilan', 'salary', 'income']) || str_contains($lowerColKey, 'gaji') || str_contains($lowerColKey, 'pendapatan');
                                    $isDesil = str_contains($lowerColKey, 'desil');
                                    $isGender = in_array($lowerColKey, ['jenis_kelamin', 'gender', 'jk', 'sex']);
                                    $isIdentity = in_array($lowerColKey, ['nik', 'nomor_induk_kependudukan', 'kk', 'nomor_kartu_keluarga', 'no_nik', 'no_kk', 'id_pelanggan_pln', 'no_telepon', 'nomor_telepon']);
                                    $isFreeText = in_array($lowerColKey, ['nama', 'nama_lengkap', 'alamat', 'nama_ibu_kandung', 'catatan', 'keterangan']);
                                @endphp
                                <th x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs font-bold text-slate-700 tracking-wider whitespace-nowrap relative group bg-slate-100">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="{{ count($initialSelectedArr) > 0 ? 'text-blue-700 font-extrabold' : '' }}">{{ $colTitle }}</span>

                                        <!-- Tombol Filter Pivot AutoFilter Excel Fleksibel Sesuai Header -->
                                        <div class="relative" x-data="{ 
                                            open: false, 
                                            searchVal: {{ json_encode(!empty($initialSelectedArr) && count($initialSelectedArr) === 1 ? (string)$initialSelectedArr[0] : '') }},
                                            minVal: '',
                                            maxVal: '',
                                            selectedVals: {{ json_encode($initialSelectedArr) }},
                                            hasVal(val) {
                                                if (val === null || val === undefined) return false;
                                                const target = String(val).trim().toLowerCase();
                                                return this.selectedVals.some(v => String(v).trim().toLowerCase() === target);
                                            },
                                            toggleVal(val) {
                                                if (this.hasVal(val)) {
                                                    const target = String(val).trim().toLowerCase();
                                                    this.selectedVals = this.selectedVals.filter(v => String(v).trim().toLowerCase() !== target);
                                                } else {
                                                    this.selectedVals.push(val);
                                                }
                                            },
                                            setSingle(val) {
                                                this.selectedVals = [val];
                                                this.applyFilter();
                                            },
                                            applyRange() {
                                                let min = parseInt(this.minVal);
                                                let max = parseInt(this.maxVal);
                                                let rangeStr = '';
                                                if (!isNaN(min) && !isNaN(max)) {
                                                    rangeStr = min + ' - ' + max;
                                                } else if (!isNaN(min)) {
                                                    rangeStr = '> ' + (min - 1);
                                                } else if (!isNaN(max)) {
                                                    rangeStr = '< ' + (max + 1);
                                                }
                                                if (rangeStr) {
                                                    this.selectedVals = [rangeStr];
                                                    this.applyFilter();
                                                }
                                            },
                                            selectAll(items) {
                                                if (this.selectedVals.length >= items.length) {
                                                    this.selectedVals = [];
                                                } else {
                                                    this.selectedVals = [...items];
                                                }
                                            },
                                            applyFilter(allItems = []) {
                                                const textQuery = this.searchVal.trim();
                                                if (textQuery !== '') {
                                                    if (this.selectedVals.length === 0) {
                                                        this.selectedVals = [textQuery];
                                                    } else if (!this.selectedVals.some(v => v.toLowerCase() === textQuery.toLowerCase())) {
                                                        this.selectedVals.push(textQuery);
                                                    }
                                                }
                                                const url = new URL(window.location.href);
                                                if (this.selectedVals.length > 0) {
                                                    url.searchParams.set('{{ $colKey }}', this.selectedVals.join(','));
                                                } else {
                                                    url.searchParams.delete('{{ $colKey }}');
                                                }
                                                url.searchParams.set('page', '1');
                                                url.hash = 'tabel-data';
                                                this.open = false;
                                                window.location.href = url.toString();
                                            },
                                            clearFilter() {
                                                this.selectedVals = [];
                                                this.searchVal = '';
                                                this.minVal = '';
                                                this.maxVal = '';
                                                const url = new URL(window.location.href);
                                                url.searchParams.delete('{{ $colKey }}');
                                                @if(isset($synonymsMap[$colKey]))
                                                    @foreach($synonymsMap[$colKey] as $syn)
                                                        url.searchParams.delete('{{ $syn }}');
                                                    @endforeach
                                                @endif
                                                url.searchParams.set('page', '1');
                                                url.hash = 'tabel-data';
                                                this.open = false;
                                                window.location.href = url.toString();
                                            }
                                        }">
                                            <button type="button" @click.stop="open = !open" 
                                                    class="px-1.5 py-0.5 rounded transition-all flex items-center gap-1 cursor-pointer"
                                                    :class="selectedVals.length > 0 ? 'bg-blue-600 text-white font-extrabold text-[10px] shadow-sm' : 'text-slate-400 hover:bg-slate-200 hover:text-slate-700'"
                                                    title="Filter Kolom {{ $colTitle }}">
                                                <span class="text-[10px]">🔽</span>
                                                <span x-show="selectedVals.length > 0" class="text-[10px] font-black bg-white text-blue-900 px-1 py-0.2 rounded-full" x-text="selectedVals.length"></span>
                                            </button>

                                            <!-- Popover Dropdown Filter Pivot Excel Multi-Select Fleksibel Sesuai Header -->
                                            <div x-show="open" @click.outside="open = false" x-cloak 
                                                 class="absolute top-full mt-1.5 w-80 bg-white rounded-xl shadow-2xl border border-slate-200 p-3.5 z-50 space-y-2.5 text-slate-800 normal-case tracking-normal {{ $loop->index > 3 ? 'right-0' : 'left-0' }}">
                                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                                    <div>
                                                        <span class="font-extrabold text-xs text-blue-900 uppercase">Filter: {{ $colTitle }}</span>
                                                        <p class="text-[10px] text-slate-400 font-medium">
                                                            @if($isAge)
                                                                Rentang Usia / Umur
                                                            @elseif($isSalary)
                                                                Rentang Finansial / Pendapatan
                                                            @elseif($isDesil)
                                                                Tingkat Kesejahteraan (Desil 1 - 10)
                                                            @elseif($isGender)
                                                                Kategori Jenis Kelamin
                                                            @elseif($isIdentity)
                                                                Nomor / Digit Identitas Unik
                                                            @elseif($isFreeText)
                                                                Pencarian Teks Bebas
                                                            @else
                                                                Pilihan Kategori Multi-Select
                                                            @endif
                                                        </p>
                                                    </div>
                                                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer p-0.5">&times;</button>
                                                </div>

                                                <!-- FILTER SPESIFIK: USIA / UMUR -->
                                                @if($isAge)
                                                    <div class="bg-blue-50/80 p-2.5 rounded-lg border border-blue-200 space-y-2 text-xs">
                                                        <span class="font-extrabold text-blue-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                                            <span>🎂</span> Preset Rentang Usia Cepat
                                                        </span>
                                                        <div class="grid grid-cols-2 gap-1.5">
                                                            <button type="button" @click.prevent.stop="setSingle('< 5')" class="px-2 py-1 bg-white hover:bg-blue-100 border border-blue-300 rounded text-[11px] font-semibold text-blue-800 transition-colors text-center cursor-pointer">
                                                                &lt; 5 Thn (Balita)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('5 - 17')" class="px-2 py-1 bg-white hover:bg-blue-100 border border-blue-300 rounded text-[11px] font-semibold text-blue-800 transition-colors text-center cursor-pointer">
                                                                5 - 17 Thn (Anak)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('18 - 40')" class="px-2 py-1 bg-white hover:bg-blue-100 border border-blue-300 rounded text-[11px] font-semibold text-blue-800 transition-colors text-center cursor-pointer">
                                                                18 - 40 Thn (Pemuda)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('41 - 59')" class="px-2 py-1 bg-white hover:bg-blue-100 border border-blue-300 rounded text-[11px] font-semibold text-blue-800 transition-colors text-center cursor-pointer">
                                                                41 - 59 Thn (Dewasa)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('>= 60')" class="col-span-2 px-2 py-1 bg-white hover:bg-blue-100 border border-blue-300 rounded text-[11px] font-semibold text-blue-800 transition-colors text-center cursor-pointer">
                                                                &ge; 60 Thn (Lansia)
                                                            </button>
                                                        </div>

                                                        <div class="pt-1.5 border-t border-blue-200">
                                                            <span class="font-bold text-blue-900 text-[10px] block mb-1">Rentang Usia Kustom:</span>
                                                            <div class="flex items-center gap-1.5">
                                                                <input type="number" id="min_age_{{ $colKey }}" name="min_age_{{ $colKey }}" aria-label="Usia Minimum" x-model="minVal" min="0" max="120" placeholder="Min" class="w-1/2 text-xs bg-white border border-slate-300 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-blue-500">
                                                                <span class="text-slate-400 font-bold text-xs">s.d.</span>
                                                                <input type="number" id="max_age_{{ $colKey }}" name="max_age_{{ $colKey }}" aria-label="Usia Maksimum" x-model="maxVal" min="0" max="120" placeholder="Max" class="w-1/2 text-xs bg-white border border-slate-300 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-blue-500">
                                                            </div>
                                                            <button type="button" @click.prevent.stop="applyRange()" class="mt-1.5 w-full py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm cursor-pointer flex items-center justify-center leading-normal">
                                                                + Terapkan Rentang Usia
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- FILTER SPESIFIK: GAJI / PENDAPATAN / PENGHASILAN -->
                                                @if($isSalary)
                                                    <div class="bg-emerald-50/80 p-2.5 rounded-lg border border-emerald-200 space-y-2 text-xs">
                                                        <span class="font-extrabold text-emerald-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                                            <span>💵</span> Preset Rentang Gaji Cepat
                                                        </span>
                                                        <div class="grid grid-cols-2 gap-1.5">
                                                            <button type="button" @click.prevent.stop="setSingle('< 1.5 Juta')" class="px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 rounded text-[11px] font-semibold text-emerald-800 transition-colors text-center cursor-pointer">
                                                                &lt; Rp 1.5 Juta
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('1.5 - 3 Juta')" class="px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 rounded text-[11px] font-semibold text-emerald-800 transition-colors text-center cursor-pointer">
                                                                Rp 1.5 - 3 Juta
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('3 - 5 Juta')" class="px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 rounded text-[11px] font-semibold text-emerald-800 transition-colors text-center cursor-pointer">
                                                                Rp 3 - 5 Juta
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('5 - 10 Juta')" class="px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 rounded text-[11px] font-semibold text-emerald-800 transition-colors text-center cursor-pointer">
                                                                Rp 5 - 10 Juta
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('> 10 Juta')" class="col-span-2 px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 rounded text-[11px] font-semibold text-emerald-800 transition-colors text-center cursor-pointer">
                                                                &gt; Rp 10 Juta
                                                            </button>
                                                        </div>

                                                        <div class="pt-1.5 border-t border-emerald-200">
                                                            <span class="font-bold text-emerald-900 text-[10px] block mb-1">Rentang Nominal Kustom:</span>
                                                            <div class="flex items-center gap-1.5">
                                                                <input type="number" id="min_sal_{{ $colKey }}" name="min_sal_{{ $colKey }}" aria-label="Gaji Minimum" x-model="minVal" placeholder="Min Rp" class="w-1/2 text-xs bg-white border border-slate-300 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-emerald-500">
                                                                <span class="text-slate-400 font-bold text-xs">-</span>
                                                                <input type="number" id="max_sal_{{ $colKey }}" name="max_sal_{{ $colKey }}" aria-label="Gaji Maksimum" x-model="maxVal" placeholder="Max Rp" class="w-1/2 text-xs bg-white border border-slate-300 rounded px-2 py-1 outline-none focus:ring-2 focus:ring-emerald-500">
                                                            </div>
                                                            <button type="button" @click.prevent.stop="applyRange()" class="mt-1.5 w-full py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-lg transition-colors shadow-sm cursor-pointer flex items-center justify-center leading-normal">
                                                                + Terapkan Rentang Gaji
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- FILTER SPESIFIK: DESIL KESEJAHTERAAN -->
                                                @if($isDesil)
                                                    <div class="bg-amber-50/80 p-2.5 rounded-lg border border-amber-200 space-y-2 text-xs">
                                                        <span class="font-extrabold text-amber-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                                            <span>📊</span> Preset Tingkat Desil
                                                        </span>
                                                        <div class="grid grid-cols-2 gap-1.5">
                                                            <button type="button" @click.prevent.stop="setSingle('1')" class="px-2 py-1 bg-white hover:bg-rose-100 border border-rose-300 rounded text-[11px] font-semibold text-rose-800 transition-colors text-center cursor-pointer">
                                                                🔴 Desil 1 (Sangat Miskin)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('1 - 4')" class="px-2 py-1 bg-white hover:bg-amber-100 border border-amber-300 rounded text-[11px] font-semibold text-amber-800 transition-colors text-center cursor-pointer">
                                                                🟠 Desil 1-4 (Bansos)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('5 - 7')" class="px-2 py-1 bg-white hover:bg-blue-100 border border-blue-300 rounded text-[11px] font-semibold text-blue-800 transition-colors text-center cursor-pointer">
                                                                🔵 Desil 5-7 (Menengah)
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('8 - 10')" class="px-2 py-1 bg-white hover:bg-emerald-100 border border-emerald-300 rounded text-[11px] font-semibold text-emerald-800 transition-colors text-center cursor-pointer">
                                                                🟢 Desil 8-10 (Mampu)
                                                            </button>
                                                        </div>
                                                        <div class="pt-1.5 border-t border-amber-200">
                                                            <span class="font-bold text-amber-900 text-[10px] block mb-1">Pilih Angka Desil:</span>
                                                            <div class="grid grid-cols-5 gap-1">
                                                                @for($d = 1; $d <= 10; $d++)
                                                                    <button type="button" @click.prevent.stop="toggleVal('{{ $d }}')" 
                                                                            class="py-1 rounded text-xs font-black transition-colors cursor-pointer border"
                                                                            :class="hasVal('{{ $d }}') ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-white hover:bg-amber-100 text-slate-700 border-slate-200'">
                                                                        D{{ $d }}
                                                                    </button>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- FILTER SPESIFIK: JENIS KELAMIN -->
                                                @if($isGender)
                                                    <div class="bg-indigo-50/80 p-2.5 rounded-lg border border-indigo-200 space-y-1.5 text-xs">
                                                        <span class="font-extrabold text-indigo-900 text-[11px] uppercase tracking-wide flex items-center gap-1">
                                                            <span>🚻</span> Pilihan Cepat Jenis Kelamin
                                                        </span>
                                                        <div class="grid grid-cols-2 gap-2">
                                                            <button type="button" @click.prevent.stop="setSingle('Laki-laki')" class="px-2.5 py-1.5 bg-white hover:bg-blue-100 border border-blue-300 rounded-lg text-xs font-bold text-blue-900 transition-colors text-center cursor-pointer">
                                                                👨 Laki-laki
                                                            </button>
                                                            <button type="button" @click.prevent.stop="setSingle('Perempuan')" class="px-2.5 py-1.5 bg-white hover:bg-pink-100 border border-pink-300 rounded-lg text-xs font-bold text-pink-900 transition-colors text-center cursor-pointer">
                                                                👩 Perempuan
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endif

                                                <!-- KOTAK PENCARIAN TEKS FLEKSIBEL -->
                                                <div>
                                                    <input type="text" id="search_val_{{ $colKey }}" name="search_val_{{ $colKey }}" aria-label="Cari pilihan {{ $colTitle }}" x-model="searchVal" 
                                                           @keydown.enter.prevent.stop="applyFilter({{ json_encode($importColumnDistinctValues[$colKey] ?? []) }})" 
                                                           placeholder="@if($isIdentity)Ketik digit {{ strtolower($colTitle) }}...@elseif($isFreeText)Ketik {{ strtolower($colTitle) }}...@elseCari pilihan {{ strtolower($colTitle) }}...@endif" 
                                                           class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500">
                                                    @if($isFreeText || $isIdentity)
                                                        <p class="text-[10px] text-slate-400 mt-1 italic">💡 Tekan Enter atau klik Terapkan untuk memfilter baris yang memuat teks/digit ini.</p>
                                                    @endif
                                                </div>

                                                <!-- DAFTAR PILIHAN CHECKBOX NILAI UNIK (DISTINCT VALUES) -->
                                                <div class="max-h-52 overflow-y-auto space-y-1 text-xs pr-1 border border-slate-100 rounded-lg p-1">
                                                    @if(isset($importColumnDistinctValues[$colKey]) && count($importColumnDistinctValues[$colKey]) > 0)
                                                        <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer font-bold text-blue-600 border-b border-slate-100 pb-1.5 mb-1">
                                                            <input type="checkbox" 
                                                                   id="select_all_{{ $colKey }}"
                                                                   name="select_all_{{ $colKey }}"
                                                                   aria-label="Pilih atau hapus semua {{ $colTitle }}"
                                                                   :checked="selectedVals.length >= {{ count($importColumnDistinctValues[$colKey]) }}" 
                                                                   @change="selectAll({{ json_encode($importColumnDistinctValues[$colKey]) }})"
                                                                   class="accent-blue-600 rounded cursor-pointer">
                                                            <span>(Pilih / Hapus Semua)</span>
                                                        </label>

                                                        @foreach($importColumnDistinctValues[$colKey] as $dIndex => $dItem)
                                                            <label x-show="!searchVal || {{ json_encode(strtolower($dItem)) }}.includes(searchVal.toLowerCase())"
                                                                   class="flex items-center gap-2 px-2 py-1 rounded hover:bg-blue-50 cursor-pointer font-medium text-slate-700 transition-colors"
                                                                   :class="hasVal({{ json_encode($dItem) }}) ? 'bg-blue-50 font-bold text-blue-900' : ''">
                                                                <input type="checkbox" 
                                                                       id="item_{{ $colKey }}_{{ $dIndex }}"
                                                                       name="filter_{{ $colKey }}[]"
                                                                       aria-label="{{ $dItem }}"
                                                                       :checked="hasVal({{ json_encode($dItem) }})" 
                                                                       @change="toggleVal({{ json_encode($dItem) }})"
                                                                       class="accent-blue-600 rounded cursor-pointer">
                                                                <span class="truncate text-xs">{{ $dItem }}</span>
                                                            </label>
                                                        @endforeach

                                                        <!-- Tombol jika mengetik teks yang tidak ada di daftar checkbox -->
                                                        <div x-show="searchVal.trim() !== '' && !{{ json_encode(array_map('strtolower', $importColumnDistinctValues[$colKey])) }}.includes(searchVal.trim().toLowerCase())" class="pt-1 border-t border-slate-100">
                                                            <button type="button" @click.prevent.stop="applyFilter()" class="w-full text-left px-2 py-1 rounded bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-[11px] transition-colors cursor-pointer flex items-center gap-1">
                                                                <span>🔍</span> Terapkan Filter Teks: "<span x-text="searchVal.trim()"></span>"
                                                            </button>
                                                        </div>
                                                    @else
                                                        <div class="px-2 py-3 text-center text-slate-400 text-xs">
                                                            <p class="font-medium">Filter teks fleksibel aktif.</p>
                                                            <p class="text-[10px] mt-0.5">Ketik nilai pencarian pada kolom input di atas lalu tekan Terapkan.</p>
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- TOMBOL AKSI BAWAH: RESET & TERAPKAN -->
                                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                                    <button type="button" @click.prevent.stop="clearFilter()" class="px-2.5 py-1.5 text-[11px] font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer">
                                                        Reset
                                                    </button>
                                                    <button type="button" @click.prevent.stop="applyFilter({{ json_encode($importColumnDistinctValues[$colKey] ?? []) }})" class="px-3.5 py-1.5 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-all flex items-center gap-1 cursor-pointer">
                                                        <span>✓</span> Terapkan <span x-show="selectedVals.length > 0" x-text="'(' + selectedVals.length + ')'"></span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
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
                                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] whitespace-nowrap">🟢 Valid</span>
                                    @elseif($errCount >= 3)
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-black text-[10px] whitespace-nowrap shadow-xs" title="{{ $tooltipStr }}">🔴 {{ $errCount }} EROR TERDETEKSI</span>
                                    @elseif($errCount == 2)
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-500 text-white font-bold text-[10px] whitespace-nowrap" title="{{ $tooltipStr }}">🔴 2 EROR</span>
                                    @elseif($row->quality_status === 'Warning')
                                        <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] whitespace-nowrap" title="{{ $tooltipStr }}">🟡 Warning (1 Eror)</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px] whitespace-nowrap" title="{{ $tooltipStr }}">🔴 Critical (1 Eror)</span>
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
                                        <span>👁</span> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeColumnsMap) + 3 }}" class="px-4 py-16 text-center">
                                    <div class="space-y-3 max-w-md mx-auto py-6">
                                        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-bold mx-auto shadow-xs">🔍</div>
                                        <div class="space-y-1">
                                            <h4 class="font-extrabold text-sm text-slate-800">Tidak ada data yang sesuai dengan kriteria</h4>
                                            <p class="text-xs text-slate-500 font-medium">Coba sesuaikan kata kunci pencarian atau bersihkan filter.</p>
                                        </div>
                                        <div class="pt-2">
                                            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
                                                <span>🔄</span> Reset Semua Filter
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
                        <span>📄</span>
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
