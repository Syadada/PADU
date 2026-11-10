@extends('layouts.app')

@section('content')
@php
    $selectableColKeys = array_values(array_filter(
        array_keys($activeColumnsMap), 
        fn($k) => !in_array(strtolower($k), ['id', 'nik', 'nomor_induk_kependudukan', 'no_kk', 'kk', 'nomor_kartu_keluarga', 'nama', 'nama_lengkap', 'gaji', 'gaji_bulanan', 'val', 'quality_status', 'quality_issues'])
    ));
    $defaultColKeys = array_values(array_filter(
        array_keys($displayMetadataCols ?? []),
        fn($k) => in_array($k, $selectableColKeys)
    ));
    if (empty($defaultColKeys)) {
        $defaultColKeys = array_slice($selectableColKeys, 0, 6);
    }

    $allFilterableKeys = array_keys($importFilterableColumns ?? []);
    $requestedFilterKeys = [];
    foreach ($allFilterableKeys as $cK) {
        if (request()->has('salary_' . $cK) && request('salary_' . $cK) !== 'semua' && request('salary_' . $cK) !== '') {
            $requestedFilterKeys[] = $cK;
        }
    }
    $defaultFilterKeys = array_values(array_unique(array_merge(
        array_keys($primaryFilterColumns ?? []),
        $requestedFilterKeys
    )));
    if (empty($defaultFilterKeys)) {
        $defaultFilterKeys = array_slice($allFilterableKeys, 0, 6);
    }
@endphp

<div class="space-y-6" x-data="salaryPageApp({{ json_encode($selectableColKeys) }}, {{ json_encode($defaultColKeys) }}, {{ json_encode($allFilterableKeys) }}, {{ json_encode($defaultFilterKeys) }})">
    
    <!-- Header Banner Modul -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 text-lg font-bold flex items-center justify-center"><i class="fi fi-rr-dollar"></i></span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Analisis Finansial & Pencarian Gaji Subjek
                </h2>
                @if(!empty($activeDatasetScopeLabel))
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border flex items-center gap-1 {{ $activeDatasetScopeBadge ?? 'bg-indigo-50 text-indigo-700 border-indigo-200' }}">
                        <i class="{{ $activeDatasetScopeIcon ?? 'fi fi-rr-apps' }}"></i> {{ $activeDatasetScopeLabel }}
                    </span>
                @endif
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold font-mono border border-slate-200">
                    {{ number_format($records->total()) }} Baris
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Sistem pencarian & perbandingan gaji fleksibel yang menyesuaikan secara otomatis dengan seluruh header berkas dataset yang dimuat.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Tombol Atur Filter Header (Atas) -->
            <button type="button" 
                    @click="showFilterModal = true" 
                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-settings-sliders"></i> <span>Atur Header Filter</span> (<span x-text="activeFilterCols.length"></span>/{{ count($importFilterableColumns) }})
            </button>
            <!-- Tombol Atur Kolom Tabel (Bawah) -->
            <button type="button" 
                    @click="showColumnModal = true" 
                    class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-table"></i> <span>Atur Kolom Tabel</span> (<span x-text="visibleCols.length"></span>/{{ count($selectableColKeys) }})
            </button>
            <a href="{{ route('dtsen.kpi') }}" class="px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs rounded-xl border border-indigo-200 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-trophy"></i> <span>Ranking (KPI)</span>
            </a>
            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-table"></i> <span>Master Data</span>
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs"><i class="fi fi-rr-dollar"></i></div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Impor berkas dataset CSV/Excel melalui menu Manajemen Berkas untuk menganalisis statistik finansial gaji.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <i class="fi fi-rr-bolt"></i> <span>Buka Manajemen Berkas</span>
                </a>
            </div>
        </div>
    @elseif(empty($hasSalaryColumn))
        <!-- Keadaan Dataset Tidak Memiliki Kolom Gaji -->
        <div class="p-8 bg-white rounded-2xl border border-amber-200 text-center space-y-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-2xl mx-auto font-bold">ℹ️</div>
            <div class="space-y-1">
                <h4 class="font-extrabold text-slate-900 text-sm">Kolom Gaji Tidak Terdeteksi pada Berkas Ini</h4>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Berkas dataset yang dimuat tidak menyertakan variabel gaji (seperti <code>gaji_bulanan</code> atau <code>gaji</code>).
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.kpi') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm">
                    <i class="fi fi-rr-chart-pie-alt"></i> <span>Buka Diagram KPI Variabel Lainnya</span>
                </a>
            </div>
        </div>
    @else
        <!-- MODUL UTAMA ANALISIS & PENCARIAN MULTIDIMENSI GAJI -->
        <div class="space-y-6">
            
            <!-- 1. FORM FILTER KONTROL FLEKSIBEL (ADAPTIF MENYESUAIKAN HEADER DATASET) -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <form method="GET" action="{{ route('dtsen.salary') }}" id="salaryCohortFilterForm" @submit="beforeFilterSubmit($event)" class="space-y-4">
                    @php
                        $activeFiltersList = [];
                        foreach ($importFilterableColumns as $cK => $cT) {
                            $userVal = request('salary_' . $cK);
                            if (!empty($userVal) && $userVal !== 'semua') {
                                $activeFiltersList[$cK] = [
                                    'label' => $cT,
                                    'value' => $userVal,
                                    'param' => 'salary_' . $cK
                                ];
                            }
                        }
                        if (request('salary_search')) {
                            $activeFiltersList['search'] = [
                                'label' => 'Nama/NIK',
                                'value' => request('salary_search'),
                                'param' => 'salary_search'
                            ];
                        }
                    @endphp

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div class="space-y-0.5">
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fi fi-rr-search"></i> <span>Filter Multidimensi Bebas Sesuai Dataset</span>
                            </h3>
                            <p class="text-xs text-slate-500">
                                Seluruh pilihan filter di bawah ini dibaca langsung dari kolom & data unik berkas Anda. Klik <strong>Atur Header Filter</strong> untuk menambah header lain.
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            <!-- Tombol Buka Modal Atur Header Filter di Atas -->
                            <button type="button" 
                                    @click="showFilterModal = true" 
                                    class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 font-extrabold text-xs rounded-xl border border-blue-200 shadow-2xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <i class="fi fi-rr-settings-sliders"></i> <span>Atur Header Filter</span> (<span x-text="activeFilterCols.length"></span>/{{ count($importFilterableColumns) }})
                            </button>

                            @if(!empty($activeFiltersList))
                                <a href="{{ route('dtsen.salary') }}" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 transition-colors flex items-center gap-1">
                                    <i class="fi fi-rr-cross-small"></i> <span>Reset Semua Filter</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    @if(!empty($activeFiltersList))
                        <!-- Badges Kriteria Aktif dengan Tombol Hapus Satuan -->
                        <div class="flex flex-wrap items-center gap-1.5 bg-emerald-50/80 p-2.5 rounded-xl border border-emerald-200">
                            <span class="text-xs font-black text-emerald-950 uppercase tracking-wider mr-1">Filter Aktif:</span>
                            @foreach($activeFiltersList as $afKey => $afInfo)
                                @php
                                    $removeUrl = request()->fullUrlWithQuery([$afInfo['param'] => null]);
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-600 text-white rounded-lg text-xs font-bold shadow-2xs">
                                    <span>{{ $afInfo['label'] }}: <em>{{ $afInfo['value'] }}</em></span>
                                    <a href="{{ $removeUrl }}" class="ml-1 text-emerald-200 hover:text-white font-black" title="Hapus kriteria ini"><i class="fi fi-rr-cross-small"></i></a>
                                </span>
                            @endforeach
                            <a href="{{ route('dtsen.salary') }}" class="text-xs font-bold text-rose-600 hover:text-rose-800 transition-colors ml-auto">
                                Bersihkan Semua
                            </a>
                        </div>
                    @endif

                    <!-- GRID FILTER DINAMIS: MENAMPILKAN SELURUH HEADER YANG DIAKTIFKAN USER -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3.5">
                        
                        <!-- Loop Seluruh Filterable Columns (Tampil jika isFilterActive(colKey)) -->
                        @foreach($importFilterableColumns as $colKey => $colTitle)
                            <div x-show="isFilterActive('{{ $colKey }}')" class="relative">
                                <div class="flex items-center justify-between mb-1">
                                    <label for="salary_{{ $colKey }}" class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider truncate cursor-pointer" title="{{ $colTitle }}">
                                        @if(str_contains($colKey, 'kelamin') || str_contains($colKey, 'gender')) <i class="fi fi-rr-restroom"></i>
                                        @elseif(str_contains($colKey, 'provinsi') || str_contains($colKey, 'wilayah') || str_contains($colKey, 'daerah')) <i class="fi fi-rr-marker"></i>
                                        @elseif(str_contains($colKey, 'desil')) <i class="fi fi-rr-gem"></i>
                                        @elseif(str_contains($colKey, 'usia') || str_contains($colKey, 'umur')) <i class="fi fi-rr-cake-birthday"></i>
                                        @elseif(str_contains($colKey, 'kerja') || str_contains($colKey, 'pekerjaan')) <i class="fi fi-rr-briefcase"></i>
                                        @elseif(str_contains($colKey, 'kawin') || str_contains($colKey, 'nikah')) <i class="fi fi-rr-ring"></i>
                                        @elseif(str_contains($colKey, 'pendidikan') || str_contains($colKey, 'ijazah')) <i class="fi fi-rr-graduation-cap"></i>
                                        @elseif(str_contains($colKey, 'kabupaten') || str_contains($colKey, 'kota')) <i class="fi fi-rr-building"></i>
                                        @elseif(str_contains($colKey, 'kecamatan') || str_contains($colKey, 'desa')) <i class="fi fi-rr-home"></i>
                                        @else <i class="fi fi-rr-thumbtack"></i>
                                        @endif
                                        {{ $colTitle }}
                                    </label>
                                    <button type="button" @click="removeFilter('{{ $colKey }}')" class="text-slate-300 hover:text-rose-600 text-xs px-1" title="Sembunyikan filter header ini">&times;</button>
                                </div>

                                @if(in_array($colKey, ['usia', 'umur', 'age']))
                                    <!-- Khusus Kolom Usia / Umur -->
                                    <select id="salary_{{ $colKey }}" name="salary_{{ $colKey }}" aria-label="Filter {{ $colTitle }}" @change="submitFilterForm()" class="w-full text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                        <option value="semua">Semua {{ $colTitle }}</option>
                                        <option value="balita" {{ (request('salary_' . $colKey) == 'balita') ? 'selected' : '' }}>Balita (< 6 Thn)</option>
                                        <option value="anak" {{ (request('salary_' . $colKey) == 'anak') ? 'selected' : '' }}>Usia Sekolah (6-17 Thn)</option>
                                        <option value="produktif" {{ (request('salary_' . $colKey) == 'produktif') ? 'selected' : '' }}>Usia Kerja (18-59 Thn)</option>
                                        <option value="lansia" {{ (request('salary_' . $colKey) == 'lansia') ? 'selected' : '' }}>Lansia (≥ 60 Thn)</option>
                                        <option value="< 30" {{ (request('salary_' . $colKey) == '< 30') ? 'selected' : '' }}>Di Bawah 30 Tahun</option>
                                        <option value="30-50" {{ (request('salary_' . $colKey) == '30-50') ? 'selected' : '' }}>30 - 50 Tahun</option>
                                        <option value="> 50" {{ (request('salary_' . $colKey) == '> 50') ? 'selected' : '' }}>Di Atas 50 Tahun</option>
                                    </select>
                                @elseif(isset($importColumnDistinctValues[$colKey]) && count($importColumnDistinctValues[$colKey]) > 0 && count($importColumnDistinctValues[$colKey]) <= 100)
                                    <!-- Kolom dengan Nilai Unik dari Dataset -->
                                    <select id="salary_{{ $colKey }}" name="salary_{{ $colKey }}" aria-label="Filter {{ $colTitle }}" @change="submitFilterForm()" class="w-full text-xs font-bold bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                        <option value="semua">Semua {{ $colTitle }}</option>
                                        @foreach($importColumnDistinctValues[$colKey] as $vItem)
                                            <option value="{{ $vItem }}" {{ (request('salary_' . $colKey) == $vItem) ? 'selected' : '' }}>
                                                {{ $vItem }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <!-- Input Bebas untuk Kolom Teks / Wilayah -->
                                    <input type="text" id="salary_{{ $colKey }}" name="salary_{{ $colKey }}" aria-label="Cari {{ $colTitle }}" value="{{ request('salary_' . $colKey) }}" placeholder="Cari {{ $colTitle }}..." class="w-full text-xs font-medium bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                                @endif
                            </div>
                        @endforeach

                        <!-- Kolom Urutkan Hasil (Sort By) -->
                        <div>
                            <label for="salary_sort" class="block text-[11px] font-extrabold text-emerald-900 uppercase tracking-wider mb-1">
                                <i class="fi fi-rr-sort-alt"></i> Urutkan Hasil
                            </label>
                            <select id="salary_sort" name="salary_sort" aria-label="Urutkan Hasil" @change="submitFilterForm()" class="w-full text-xs font-extrabold bg-emerald-50 border-2 border-emerald-400 text-emerald-950 rounded-xl px-2.5 py-2 outline-none focus:ring-2 focus:ring-emerald-500 cursor-pointer">
                                <option value="gaji_desc" {{ (request('salary_sort', 'gaji_desc') == 'gaji_desc') ? 'selected' : '' }}>Gaji Tertinggi (MAX ↓)</option>
                                <option value="gaji_asc" {{ (request('salary_sort') == 'gaji_asc') ? 'selected' : '' }}>Gaji Terendah (MIN ↑)</option>
                                <option value="nama_asc" {{ (request('salary_sort') == 'nama_asc') ? 'selected' : '' }}>Nama Subjek (A-Z)</option>
                                <option value="usia_desc" {{ (request('salary_sort') == 'usia_desc') ? 'selected' : '' }}>Usia Tertua</option>
                                <option value="usia_asc" {{ (request('salary_sort') == 'usia_asc') ? 'selected' : '' }}>Usia Termuda</option>
                            </select>
                        </div>

                    </div>

                    <!-- Row 2: QUICK ADD HEADER DROPDOWN & SUBMIT BUTTON -->
                    <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200 flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2 flex-1">
                            <label for="salary_quick_add_header" class="text-xs font-bold text-slate-700 whitespace-nowrap cursor-pointer flex items-center gap-1"><i class="fi fi-rr-plus text-blue-600"></i> Tambah Header Filter:</label>
                            <!-- Dropdown Pilih Header Apa Saja untuk Ditambahkan ke Filter di Atas -->
                            <select id="salary_quick_add_header" name="salary_quick_add_header" aria-label="Tambah Header Filter" @change="addFilterFromSelect($event.target.value); $event.target.value = ''" 
                                    class="text-xs font-bold bg-white border border-slate-300 text-blue-700 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer min-w-[200px]">
                                <option value="">-- Pilih Header untuk Ditambahkan --</option>
                                @foreach($importFilterableColumns as $optKey => $optTitle)
                                    <option value="{{ $optKey }}" :disabled="isFilterActive('{{ $optKey }}')">
                                        {{ $optTitle }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="text-[11px] text-slate-400 italic">Pilih header di atas untuk memunculkan dropdown filternya ke grid.</span>
                        </div>

                        <!-- Pencarian Nama / NIK & Tombol Cari -->
                        <div class="flex items-center gap-2 shrink-0">
                            <label for="salary_search_input" class="sr-only">Cari nama atau NIK</label>
                            <input type="text" id="salary_search_input" name="salary_search" value="{{ request('salary_search') }}" autocomplete="off" aria-label="Cari nama atau NIK" placeholder="Cari nama atau NIK..." class="text-xs font-medium bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500 w-48">
                            <a href="{{ route('dtsen.salary') }}" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                                <i class="fi fi-rr-refresh"></i> Reset
                            </a>
                            <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                                <i class="fi fi-rr-search"></i> <span>Cari & Bedah</span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>

            <!-- MODAL POPUP ATUR HEADER FILTER DI ATAS (DARI SELURUH HEADER DATASET) -->
            <div x-show="showFilterModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-3 sm:p-6 flex items-center justify-center">
                <div @click.away="showFilterModal = false" class="bg-white rounded-2xl max-w-4xl w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200 my-auto flex flex-col max-h-[88vh]">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fi fi-rr-settings-sliders"></i> <span>Pengatur Tampilan Dropdown Header Filter di Atas</span>
                            </h3>
                            <p class="text-xs text-slate-500">
                                Centang header kolom yang ingin Anda tampilkan sebagai kotak filter di bagian atas halaman analisis gaji.
                            </p>
                        </div>
                        <button type="button" @click="showFilterModal = false" class="text-slate-400 hover:text-slate-700 font-extrabold text-lg p-1 cursor-pointer">&times;</button>
                    </div>

                    <div class="space-y-4 overflow-y-auto pr-2 flex-1 scrollbar-thin max-h-[60vh]">
                        <!-- Group 1: Set Data Individu -->
                        @if(!empty($officialIndividuVars))
                            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-blue-900 flex items-center gap-1.5 border-b border-blue-200 pb-1.5">
                                    <i class="fi fi-rr-user"></i> Set Data Anggota Keluarga / Individu ({{ count($officialIndividuVars) }} Variabel)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($officialIndividuVars as $cKey => $cTitle)
                                        @if(isset($importFilterableColumns[$cKey]))
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                                <input type="checkbox" id="toggle_filter_{{ $cKey }}" name="toggle_filter_{{ $cKey }}" aria-label="Aktifkan filter {{ $cTitle }}" :checked="isFilterActive('{{ $cKey }}')" @change="toggleFilter('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                                <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Group 2: Set Data Keluarga -->
                        @if(!empty($officialKeluargaVars))
                            <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-emerald-900 flex items-center gap-1.5 border-b border-emerald-200 pb-1.5">
                                    <i class="fi fi-rr-home"></i> Set Data Keluarga ({{ count($officialKeluargaVars) }} Variabel)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($officialKeluargaVars as $cKey => $cTitle)
                                        @if(isset($importFilterableColumns[$cKey]))
                                            <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-emerald-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                                <input type="checkbox" id="toggle_filter_{{ $cKey }}" name="toggle_filter_{{ $cKey }}" aria-label="Aktifkan filter {{ $cTitle }}" :checked="isFilterActive('{{ $cKey }}')" @change="toggleFilter('{{ $cKey }}')" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                                <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Group 3: Variabel Tambahan Dataset Lainnya -->
                        @php
                            $extraCols = array_diff_key($importFilterableColumns, $officialIndividuVars ?? [], $officialKeluargaVars ?? []);
                        @endphp
                        @if(!empty($extraCols))
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-slate-700 flex items-center gap-1.5 border-b border-slate-200 pb-1.5">
                                    <i class="fi fi-rr-thumbtack"></i> Variabel Tambahan Dataset Lainnya ({{ count($extraCols) }} Kolom)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($extraCols as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_filter_{{ $cKey }}" name="toggle_filter_{{ $cKey }}" aria-label="Aktifkan filter {{ $cTitle }}" :checked="isFilterActive('{{ $cKey }}')" @change="toggleFilter('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 shrink-0">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showAllFilters()" class="px-3.5 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-50 rounded-xl transition-colors cursor-pointer">
                                <i class="fi fi-rr-check"></i> Tampilkan Semua Filter
                            </button>
                            <button type="button" @click="resetFilters()" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">
                                <i class="fi fi-rr-refresh"></i> Kembalikan Standar
                            </button>
                        </div>
                        <button type="button" @click="showFilterModal = false" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all cursor-pointer">
                            Selesai (Tutup)
                        </button>
                    </div>
                </div>
            </div>

            <!-- MODAL POPUP ATUR KOLOM TABEL (UNTUK TABEL BAWAH) -->
            <div x-show="showColumnModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-3 sm:p-6 flex items-center justify-center">
                <div @click.away="showColumnModal = false" class="bg-white rounded-2xl max-w-4xl w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200 my-auto flex flex-col max-h-[88vh]">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fi fi-rr-settings-sliders"></i> <span>Pengatur Tampilan Kolom Tabel Data Gaji</span>
                            </h3>
                            <p class="text-xs text-slate-500">
                                Centang atau hilangkan centang untuk memilih variabel yang ingin ditampilkan di kolom tabel.
                            </p>
                        </div>
                        <button type="button" @click="showColumnModal = false" class="text-slate-400 hover:text-slate-700 font-extrabold text-lg p-1 cursor-pointer">&times;</button>
                    </div>

                    <div class="space-y-4 overflow-y-auto pr-2 flex-1 scrollbar-thin max-h-[60vh]">
                        <!-- Group 1: Set Data Individu -->
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                            <h4 class="text-xs font-black uppercase text-blue-900 flex items-center gap-1.5 border-b border-blue-200 pb-1.5 justify-between">
                                <span class="flex items-center gap-1.5"><i class="fi fi-rr-user"></i> Set Data Anggota Keluarga / Individu</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-blue-100 text-blue-800 font-bold">{{ count($activeIndividuCols ?? []) }} Kolom Aktif</span>
                            </h4>
                            @php
                                $individuSelectables = array_filter($selectableColKeys, fn($k) => isset($officialIndividuVars[$k]));
                            @endphp
                            @if(!empty($individuSelectables))
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($individuSelectables as $cKey)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_salary_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $officialIndividuVars[$cKey] ?? $cKey }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                            <span class="truncate" title="{{ $officialIndividuVars[$cKey] ?? $cKey }}">{{ $officialIndividuVars[$cKey] ?? $cKey }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-white/80 rounded-lg border border-dashed border-blue-200 text-slate-500 text-xs flex items-center gap-2 font-medium">
                                    <i class="fi fi-rr-info text-blue-600"></i> Berkas dataset aktif tidak memuat variabel Set Individu.
                                </div>
                            @endif
                        </div>

                        <!-- Group 2: Set Data Keluarga -->
                        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-2">
                            <h4 class="text-xs font-black uppercase text-emerald-900 flex items-center gap-1.5 border-b border-emerald-200 pb-1.5 justify-between">
                                <span class="flex items-center gap-1.5"><i class="fi fi-rr-home"></i> Set Data Keluarga / Rumah Tangga</span>
                                <span class="px-2 py-0.5 rounded text-[10px] font-mono bg-emerald-100 text-emerald-800 font-bold">{{ count($activeKeluargaCols ?? []) }} Kolom Aktif</span>
                            </h4>
                            @php
                                $keluargaSelectables = array_filter($selectableColKeys, fn($k) => isset($officialKeluargaVars[$k]));
                            @endphp
                            @if(!empty($keluargaSelectables))
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($keluargaSelectables as $cKey)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-emerald-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_salary_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $officialKeluargaVars[$cKey] ?? $cKey }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                            <span class="truncate" title="{{ $officialKeluargaVars[$cKey] ?? $cKey }}">{{ $officialKeluargaVars[$cKey] ?? $cKey }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @else
                                <div class="p-3 bg-white/80 rounded-lg border border-dashed border-emerald-200 text-slate-500 text-xs flex items-center gap-2 font-medium">
                                    <i class="fi fi-rr-info text-emerald-600"></i> Berkas dataset aktif tidak memuat variabel Set Keluarga.
                                </div>
                            @endif
                        </div>

                        <!-- Group 3: Variabel Tambahan Dataset Lainnya -->
                        @php
                            $extraTableCols = array_diff($selectableColKeys, array_keys($officialIndividuVars ?? []), array_keys($officialKeluargaVars ?? []));
                        @endphp
                        @if(!empty($extraTableCols))
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-slate-700 flex items-center gap-1.5 border-b border-slate-200 pb-1.5">
                                    <i class="fi fi-rr-thumbtack"></i> Variabel Tambahan Dataset Lainnya ({{ count($extraTableCols) }} Kolom)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($extraTableCols as $cKey)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_salary_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $activeColumnsMap[$cKey] ?? $cKey }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                            <span class="truncate" title="{{ $activeColumnsMap[$cKey] ?? $cKey }}">{{ $activeColumnsMap[$cKey] ?? $cKey }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex items-center justify-between border-t border-slate-100 pt-3 shrink-0">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="showAllCols()" class="px-3.5 py-1.5 text-xs font-bold text-blue-600 hover:bg-blue-50 rounded-xl transition-colors cursor-pointer">
                                <i class="fi fi-rr-check"></i> Tampilkan Semua Kolom
                            </button>
                            <button type="button" @click="resetCols()" class="px-3.5 py-1.5 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl transition-colors cursor-pointer">
                                <i class="fi fi-rr-refresh"></i> Kembalikan Standar
                            </button>
                        </div>
                        <button type="button" @click="showColumnModal = false" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all cursor-pointer">
                            Selesai (Tutup)
                        </button>
                    </div>
                </div>
            </div>

            <!-- 2. KARTU STATISTIK RINGKASAN FINANSIAL KELOMPOK TERFILTER -->
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-2">
                    <div class="space-y-0.5">
                        <h3 class="text-xs font-black uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                            <i class="fi fi-rr-stats"></i> <span>Ringkasan Statistik Finansial Hasil Pencarian</span>
                        </h3>
                        @if(!empty($activeFiltersList))
                            <p class="text-[11px] text-emerald-800 font-bold flex items-center gap-1">
                                <i class="fi fi-rr-bullseye text-blue-600"></i> Dihitung khusus dari <strong>{{ number_format($filteredTotal ?? $records->total() ?? 0) }} subjek</strong> yang memenuhi seluruh filter aktif Anda.
                            </p>
                        @else
                            <p class="text-[11px] text-slate-500 font-medium">
                                Menampilkan agregat makro dari seluruh <strong>{{ number_format($totalSystemRows) }} subjek</strong> pada dataset.
                            </p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!empty($activeFiltersList))
                            <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-900 text-xs font-extrabold border border-emerald-300 shadow-2xs">
                                <i class="fi fi-rr-bullseye"></i> {{ number_format($filteredTotal ?? $records->total() ?? 0) }} Subjek Terfilter
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold border border-slate-200">
                                <i class="fi fi-rr-globe"></i> {{ number_format($totalSystemRows) }} Total Subjek
                            </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <!-- Gaji Tertinggi di Kelompok Ini -->
                    <div class="p-4 rounded-2xl shadow-xs space-y-1.5" style="background: #ecfdf5 !important; border: 1px solid #a7f3d0 !important;">
                        <div class="flex items-center justify-between text-xs font-black uppercase text-emerald-900">
                            <span class="flex items-center gap-1.5"><i class="fi fi-rr-chart-line-up text-emerald-600"></i> Gaji Tertinggi (MAX)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-200 text-emerald-950">TERTINGGI</span>
                        </div>
                        <div class="text-slate-900 font-mono font-black text-xl">
                            Rp {{ number_format($gajiMax, 0, ',', '.') }}
                        </div>
                        <div class="text-xs font-medium text-emerald-900 truncate">
                            @if($gajiMaxSubjek)
                                Subjek: <strong class="font-extrabold text-slate-900"><span x-show="isMasked">{{ $gajiMaxSubjek->masked_nama }}</span><span x-show="!isMasked" style="display:none;">{{ $gajiMaxSubjek->nama }}</span></strong>
                            @else
                                Subjek: <em class="text-slate-400">Tidak ada</em>
                            @endif
                        </div>
                    </div>

                    <!-- Gaji Terendah di Kelompok Ini -->
                    <div class="p-4 rounded-2xl shadow-xs space-y-1.5" style="background: #fffbeb !important; border: 1px solid #fde68a !important;">
                        <div class="flex items-center justify-between text-xs font-black uppercase text-amber-900">
                            <span class="flex items-center gap-1.5"><i class="fi fi-rr-chart-line-down text-rose-600"></i> Gaji Terendah (MIN)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-200 text-amber-950">TERENDAH</span>
                        </div>
                        <div class="text-slate-900 font-mono font-black text-xl">
                            Rp {{ number_format($gajiMin, 0, ',', '.') }}
                        </div>
                        <div class="text-xs font-medium text-amber-900 truncate">
                            @if($gajiMinSubjek)
                                Subjek: <strong class="font-extrabold text-slate-900"><span x-show="isMasked">{{ $gajiMinSubjek->masked_nama }}</span><span x-show="!isMasked" style="display:none;">{{ $gajiMinSubjek->nama }}</span></strong>
                            @else
                                Subjek: <em class="text-slate-400">Tidak ada</em>
                            @endif
                        </div>
                    </div>

                    <!-- Rata-Rata Gaji di Kelompok Ini -->
                    <div class="p-4 rounded-2xl shadow-xs space-y-1.5" style="background: #eff6ff !important; border: 1px solid #bfdbfe !important;">
                        <div class="flex items-center justify-between text-xs font-black uppercase text-blue-900">
                            <span class="flex items-center gap-1.5"><i class="fi fi-rr-stats text-blue-600"></i> Gaji Rata-Rata (AVG)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-200 text-blue-950">RATA-RATA</span>
                        </div>
                        <div class="text-slate-900 font-mono font-black text-xl">
                            Rp {{ number_format($gajiAvg, 0, ',', '.') }}
                        </div>
                        <p class="text-xs font-medium text-blue-700 truncate">
                            Dihitung dari {{ number_format($gajiCount) }} subjek
                        </p>
                    </div>

                    <!-- Total Agregat Gaji di Kelompok Ini -->
                    <div class="p-4 rounded-2xl shadow-xs space-y-1.5" style="background: #faf5ff !important; border: 1px solid #e9d5ff !important;">
                        <div class="flex items-center justify-between text-xs font-black uppercase text-purple-900">
                            <span class="flex items-center gap-1.5"><i class="fi fi-rr-dollar text-purple-600"></i> Total Kumulatif (SUM)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-200 text-purple-950">TOTAL</span>
                        </div>
                        <div class="text-slate-900 font-mono font-black text-xl">
                            Rp {{ number_format($gajiSum, 0, ',', '.') }}
                        </div>
                        <p class="text-xs font-medium text-purple-700 truncate">
                            Total perputaran gaji kelompok
                        </p>
                    </div>
                </div>

                <!-- Smart Response Finansial & Disparitas Upah -->
                @php
                    $finMax = (float)($gajiMax ?? 0);
                    $finMin = (float)($gajiMin ?? 0);
                    $finAvg = (float)($gajiAvg ?? 0);
                    $finGap = max(0, $finMax - $finMin);
                    $finRatioAvg = $finAvg > 0 ? round($finMax / $finAvg, 1) : 1;
                    $nSubjekGaji = (int)($gajiCount ?? 0);
                @endphp
                <div class="p-4 rounded-xl border border-emerald-200 bg-gradient-to-r from-emerald-50/70 via-teal-50/40 to-slate-50 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-emerald-600 text-white text-xs font-black shadow-xs"><i class="fi fi-rr-dollar"></i></span>
                            <span class="text-xs font-black uppercase tracking-wider text-emerald-950">
                                Keterangan Ringkasan Gaji & Pendapatan Warga
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-mono font-bold text-emerald-900">
                            <span class="px-2.5 py-0.5 rounded bg-white border border-emerald-300">Selisih Gaji Tertinggi - Terendah: Rp {{ number_format($finGap, 0, ',', '.') }}</span>
                            <span class="px-2.5 py-0.5 rounded bg-white border border-emerald-300">Gaji Teratas vs Rerata: {{ $finRatioAvg }}x lipat</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <div class="p-2.5 rounded-lg bg-white/95 border border-emerald-200 shadow-2xs space-y-0.5">
                            <span class="text-[10px] text-emerald-800 font-bold block">Gaji Paling Tinggi</span>
                            <strong class="font-mono text-emerald-700 text-sm block">Rp {{ number_format($finMax, 0, ',', '.') }}</strong>
                            <p class="text-[10px] text-slate-500 truncate">{{ $gajiMaxSubjek->nama ?? '-' }} (NIK: {{ $gajiMaxSubjek->masked_nik ?? '-' }})</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-white/95 border border-blue-200 shadow-2xs space-y-0.5">
                            <span class="text-[10px] text-blue-800 font-bold block">Rata-Rata Penghasilan Warga</span>
                            <strong class="font-mono text-blue-700 text-sm block">Rp {{ number_format($finAvg, 0, ',', '.') }}</strong>
                            <p class="text-[10px] text-slate-500">Dihitung dari {{ number_format($nSubjekGaji) }} pekerja tercatat</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-white/95 border border-amber-200 shadow-2xs space-y-0.5">
                            <span class="text-[10px] text-amber-800 font-bold block">Gaji Paling Rendah</span>
                            <strong class="font-mono text-amber-700 text-sm block">Rp {{ number_format($finMin, 0, ',', '.') }}</strong>
                            <p class="text-[10px] text-slate-500 truncate">{{ $gajiMinSubjek->nama ?? '-' }} (NIK: {{ $gajiMinSubjek->masked_nik ?? '-' }})</p>
                        </div>
                    </div>

                    <div class="p-3 bg-white/95 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-2 leading-relaxed">
                        <p class="flex items-start gap-1.5">
                            <span class="text-emerald-600 font-bold shrink-0"><i class="fi fi-rr-thumbtack"></i></span>
                            <span>
                                <strong>Kondisi Gaji:</strong> Rata-rata penghasilan warga yang bekerja berada di angka <strong>Rp {{ number_format($finAvg, 0, ',', '.') }} per bulan</strong>. 
                                @if($finRatioAvg >= 5)
                                    Perbedaan pendapatan antara gaji tertinggi dan terendah tergolong sangat jauh (jomplang).
                                @else
                                    Tingkat pendapatan antar pekerja tergolong wajar dan relatif seimbang.
                                @endif
                                @if(!empty($activeFiltersList))
                                    (Dihitung khusus dari <strong>{{ number_format($filteredTotal ?? 0) }} subjek terfilter</strong>).
                                @endif
                            </span>
                        </p>
                        <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs space-y-1">
                            <div class="font-extrabold flex items-center gap-1.5 text-emerald-900">
                                <i class="fi fi-rr-bulb text-amber-500"></i> <span>Solusi & Saran Kebijakan Kesejahteraan:</span>
                            </div>
                            <ul class="list-disc list-inside space-y-0.5 text-[11px] text-emerald-900 font-medium pl-1">
                                <li><strong>Bantuan Pekerja Bergaji Rendah:</strong> Warga dengan gaji di bawah rata-rata (Rp {{ number_format($finMin, 0, ',', '.') }} s/d Rp {{ number_format($finAvg, 0, ',', '.') }}) perlu dibantu dengan subsidi sembako atau jaring pengaman agar kebutuhan dapur aman.</li>
                                <li><strong>Pastikan Upah Layak:</strong> Koordinasikan dengan dinas ketenagakerjaan setempat agar pengusaha membayarkan gaji yang layak sesuai standar upah minimum wilayah (UMR/UMK).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. TABEL DATA HASIL PENCARIAN SUBJEK (DENGAN HEADER ADAPTIF) -->
            <div id="tabel-data" class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="space-y-0.5">
                        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                            <i class="fi fi-rr-table"></i> <span>Tabel Data Subjek Hasil Pencarian</span>
                            @if(request('salary_sort', 'gaji_desc') == 'gaji_desc')
                                <span class="px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-black">
                                    <i class="fi fi-rr-sort-amount-down text-emerald-600"></i> Diurutkan dari Gaji Tertinggi
                                </span>
                            @elseif(request('salary_sort') == 'gaji_asc')
                                <span class="px-2 py-0.5 rounded bg-amber-100 text-amber-800 text-[10px] font-black">
                                    <i class="fi fi-rr-sort-amount-up text-rose-600"></i> Diurutkan dari Gaji Terendah
                                </span>
                            @endif
                        </h3>
                        <p class="text-xs text-slate-500">
                            Menampilkan daftar subjek individual yang memenuhi seluruh kriteria filter di atas. Klik <strong>Atur Kolom</strong> untuk memilih header tabel.
                        </p>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-bold text-slate-400 font-mono">
                            {{ number_format($records->total()) }} Baris Data
                        </span>
                        
                        <!-- Tombol Atur Kolom Tabel -->
                        <button type="button" 
                                @click="showColumnModal = true" 
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="fi fi-rr-table"></i> <span>Atur Kolom</span> (<span x-text="visibleCols.length"></span>/{{ count($selectableColKeys) }})
                        </button>

                        <!-- Tombol Masking NIK/Nama -->
                        <button type="button" @click="isMasked = !isMasked" class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                            <span x-text="isMasked ? 'Buka Sensor' : 'Sensor NIK/Nama'">Sensor NIK/Nama</span>
                        </button>
                    </div>
                </div>

                @if($records->isEmpty())
                    <div class="p-12 text-center text-slate-400 space-y-2">
                        <div class="text-3xl text-slate-400"><i class="fi fi-rr-search"></i></div>
                        <h4 class="font-bold text-slate-800 text-sm">Tidak Ada Data Subjek yang Cocok</h4>
                        <p class="text-xs text-slate-500 max-w-md mx-auto">
                            Kombinasi kriteria filter yang Anda tentukan tidak menemukan kecocokan pada dataset. Silakan ubah atau kurangi filter untuk memperluas pencarian.
                        </p>
                        <div class="pt-2">
                            <a href="{{ route('dtsen.salary') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all inline-flex items-center gap-1.5">
                                <i class="fi fi-rr-refresh"></i> Bersihkan Semua Filter
                            </a>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-2xs scrollbar-thin min-h-[380px]">
                        <table class="w-full text-left border-collapse min-w-max text-xs" style="min-width: 100%; border-collapse: collapse;">
                            <thead class="sticky top-0 z-20 bg-slate-100 shadow-2xs">
                                <tr class="bg-slate-100 border-b border-slate-200 text-[11px] font-extrabold text-slate-700 uppercase tracking-wider">
                                    <th class="py-3 px-3 text-center bg-slate-100" style="width: 52px; min-width: 52px; padding: 12px 8px; text-align: center;">#</th>
                                    <th class="py-3 px-3.5 bg-slate-100" style="min-width: 170px; padding: 12px 14px; text-align: left; border-left: 1px solid #cbd5e1;">Nomor Identitas (NIK)</th>
                                    <th class="py-3 px-4 bg-slate-100" style="min-width: 180px; padding: 12px 16px; text-align: left; border-left: 1px solid #cbd5e1;">Nama Lengkap</th>
                                    <th class="py-3 px-4 text-right bg-emerald-50 text-emerald-950 font-black" style="min-width: 165px; padding: 12px 16px; text-align: right; background-color: #ecfdf5; color: #064e3b; border-left: 1px solid #a7f3d0; border-right: 1px solid #a7f3d0;">
                                        Gaji Bulanan (Rp)
                                    </th>
                                    
                                    <!-- Dynamic Dataset Headers (Dengan Batas Kolom & Label Jelas) -->
                                    @foreach($selectableColKeys as $colKey)
                                        @php
                                            $colTitle = $activeColumnsMap[$colKey] ?? ucwords(str_replace('_', ' ', $colKey));
                                            $numPrefix = '';
                                            $cleanTitle = $colTitle;
                                            if (preg_match('/^(\d+)\.\s*(.*)$/', $colTitle, $m)) {
                                                $numPrefix = $m[1];
                                                $cleanTitle = $m[2];
                                            }
                                        @endphp
                                        <th x-show="isColVisible('{{ $colKey }}')" 
                                            class="py-3 px-4 text-center whitespace-nowrap bg-slate-100 text-slate-800"
                                            style="min-width: 175px; padding: 12px 16px; text-align: center; border-left: 1px solid #cbd5e1;">
                                            <div class="inline-flex items-center gap-1.5 justify-center">
                                                @if($numPrefix)
                                                    <span class="px-1.5 py-0.5 rounded-md bg-slate-200 text-slate-700 font-mono text-[9px] font-black border border-slate-300">
                                                        #{{ $numPrefix }}
                                                    </span>
                                                @endif
                                                <span class="font-extrabold text-slate-800 text-[11px] uppercase tracking-wider">
                                                    {{ $cleanTitle }}
                                                </span>
                                            </div>
                                        </th>
                                    @endforeach

                                    <th class="py-3 px-3 text-center bg-slate-100" style="width: 90px; min-width: 90px; padding: 12px 8px; text-align: center; border-left: 1px solid #cbd5e1;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                                @foreach($records as $index => $row)
                                    @php
                                        $rowSalary = (float)($row->gaji_bulanan ?? $row->gaji ?? 0);
                                        $rowRank = (($records->currentPage() - 1) * $records->perPage()) + ($index + 1);
                                    @endphp
                                    <tr class="hover:bg-blue-50/40 transition-colors">
                                        <!-- No Peringkat -->
                                        <td class="py-2.5 px-3 text-center font-bold text-slate-400" style="width: 52px; min-width: 52px; padding: 10px 8px; text-align: center;">
                                            @if($rowRank <= 3 && request('salary_sort', 'gaji_desc') == 'gaji_desc')
                                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-black shadow-2xs">
                                                    #{{ $rowRank }}
                                                </span>
                                            @else
                                                #{{ $rowRank }}
                                            @endif
                                        </td>

                                        <!-- NIK -->
                                        <td class="py-2.5 px-3.5 font-mono text-slate-600 font-semibold text-[11px] whitespace-nowrap" style="min-width: 170px; padding: 10px 14px; text-align: left; border-left: 1px solid #f1f5f9;">
                                            <span x-show="isMasked">{{ $row->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $row->nomor_induk_kependudukan ?? $row->nik ?? '-' }}</span>
                                        </td>

                                        <!-- Nama Lengkap -->
                                        <td class="py-2.5 px-4 font-bold text-slate-900 whitespace-nowrap" style="min-width: 180px; padding: 10px 16px; text-align: left; border-left: 1px solid #f1f5f9;">
                                            <span x-show="isMasked">{{ $row->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $row->nama ?? $row->nama_lengkap ?? '-' }}</span>
                                        </td>

                                        <!-- Gaji Bulanan (Highlighted) -->
                                        <td class="py-2.5 px-4 text-right font-mono font-black text-emerald-700 text-sm whitespace-nowrap bg-emerald-50/50" style="min-width: 165px; padding: 10px 16px; text-align: right; background-color: #ecfdf5; border-left: 1px solid #d1fae5; border-right: 1px solid #d1fae5;">
                                            Rp {{ number_format($rowSalary, 0, ',', '.') }}
                                        </td>

                                        <!-- Dynamic Columns Sesuai Seleksi Pengguna -->
                                        @foreach($selectableColKeys as $colKey)
                                            @php $cellVal = $row->$colKey ?? '-'; @endphp
                                            <td x-show="isColVisible('{{ $colKey }}')" class="py-2.5 px-4 text-center whitespace-nowrap" style="min-width: 175px; padding: 10px 16px; text-align: center; border-left: 1px solid #f1f5f9;">
                                                @if(str_contains(strtolower($colKey), 'kelamin') || str_contains(strtolower($colKey), 'gender'))
                                                    @if(str_contains(strtolower((string)$cellVal), 'perempuan') || strtolower((string)$cellVal) === 'p' || strtolower((string)$cellVal) === 'wanita')
                                                        <span class="px-2.5 py-0.5 rounded-full bg-pink-100 text-pink-700 text-[10px] font-bold border border-pink-200">Wanita</span>
                                                    @elseif(str_contains(strtolower((string)$cellVal), 'laki') || strtolower((string)$cellVal) === 'l' || strtolower((string)$cellVal) === 'pria')
                                                        <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold border border-blue-200">Pria</span>
                                                    @else
                                                        <span class="text-slate-500">{{ $cellVal }}</span>
                                                    @endif
                                                @elseif(str_contains(strtolower($colKey), 'desil'))
                                                    <span class="px-2.5 py-0.5 rounded-lg bg-blue-50 text-blue-800 font-mono text-[10px] font-black border border-blue-200">
                                                        Desil {{ $cellVal }}
                                                    </span>
                                                @elseif(str_contains(strtolower($colKey), 'usia') || str_contains(strtolower($colKey), 'umur'))
                                                    <span class="font-mono font-bold text-slate-800">{{ $cellVal }}</span>
                                                    <span class="text-[9px] text-slate-400 font-normal">thn</span>
                                                @else
                                                    @php
                                                        $rawVal = $row->$colKey ?? null;
                                                        if (is_numeric($rawVal) && strlen((string)$rawVal) < 15) {
                                                            $displayVal = number_format((float)$rawVal, (floor((float)$rawVal) == (float)$rawVal ? 0 : 2), ',', '.');
                                                        } else {
                                                            $displayVal = $rawVal ?: '-';
                                                        }
                                                    @endphp
                                                    <span class="text-slate-700 font-medium">{{ $displayVal }}</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <!-- Aksi Detail Preview -->
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap" style="width: 90px; min-width: 90px; padding: 10px 8px; text-align: center; border-left: 1px solid #f1f5f9;">
                                            <button type="button" 
                                                    @click="openPreview({{ $row->id ?? 0 }})"
                                                    class="px-2.5 py-1 bg-white hover:bg-blue-600 hover:text-white text-slate-700 font-bold rounded-lg transition-all text-[11px] border border-slate-300 shadow-2xs cursor-pointer inline-flex items-center gap-1"
                                                    title="Buka detail subjek">
                                                <i class="fi fi-rr-eye"></i> <span>Detail</span>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- PAGINATION FOOTER -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs">
                        <div class="text-slate-500 font-medium">
                            Menampilkan <strong class="text-slate-800 font-bold">{{ $records->firstItem() ?? 0 }}</strong> s/d <strong class="text-slate-800 font-bold">{{ $records->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-800 font-bold">{{ number_format($records->total()) }}</strong> subjek terfilter
                        </div>

                        <div>
                            {{ $records->onEachSide(1)->links() }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- 4. BANNER SHORTCUT KE HALAMAN RANKING & DIAGRAM KPI (HIGH-CONTRAST SOLID COLOR, NO TRANSPARENCY) -->
            <div class="p-5 rounded-2xl shadow-md flex flex-col sm:flex-row sm:items-center justify-between gap-4"
                 style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #1e293b !important;">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xl text-amber-500"><i class="fi fi-rr-trophy"></i></span>
                        <h4 class="font-extrabold text-sm" style="color: #ffffff !important;">Ingin Melihat Peringkat & Top / Bottom Ekstrem Seluruh Variabel?</h4>
                    </div>
                    <p class="text-xs max-w-xl" style="color: #cbd5e1 !important;">
                        Seluruh pemeringkatan makro Top 5 dan Bottom 5 telah disatukan di <strong style="color: #38bdf8 !important;">Halaman Diagram KPI & Ranking</strong> agar analisis komparasi antar variabel kependudukan dan finansial tersaji lebih fokus dan komprehensif.
                    </p>
                </div>
                <div class="shrink-0">
                    <a href="{{ route('dtsen.kpi') }}" 
                       class="px-5 py-2.5 font-extrabold text-xs rounded-xl shadow-md transition-all inline-flex items-center gap-1.5 cursor-pointer"
                       style="background-color: #4f46e5 !important; color: #ffffff !important; border: none !important;">
                        <i class="fi fi-rr-chart-pie-alt"></i> <span>Buka Halaman Ranking (KPI)</span>
                    </a>
                </div>
            </div>

        </div>
    @endif

</div>

@push('scripts')
<script>
function salaryPageApp(allSelectableColKeys = [], defaultTableColKeys = [], allFilterableKeys = [], defaultFilterKeys = []) {
    let savedTableCols = null;
    let savedFilterCols = null;
    try {
        savedTableCols = JSON.parse(localStorage.getItem('padu_salary_visible_columns'));
        savedFilterCols = JSON.parse(localStorage.getItem('padu_salary_filter_columns'));
    } catch (e) {}

    const validTableSaved = (savedTableCols && Array.isArray(savedTableCols)) 
        ? savedTableCols.filter(c => allSelectableColKeys.includes(c)) 
        : [];

    const validFilterSaved = (savedFilterCols && Array.isArray(savedFilterCols)) 
        ? savedFilterCols.filter(c => allFilterableKeys.includes(c)) 
        : [];

    const initialFilterCols = validFilterSaved.length > 0
        ? Array.from(new Set([...validFilterSaved, ...defaultFilterKeys]))
        : (defaultFilterKeys.length > 0 ? defaultFilterKeys : allFilterableKeys.slice(0, 6));

    return {
        // Table Column Manager
        showColumnModal: false,
        visibleCols: validTableSaved.length > 0 ? validTableSaved : (defaultTableColKeys.length > 0 ? defaultTableColKeys : allSelectableColKeys.slice(0, 6)),

        // Top Filter Header Manager
        showFilterModal: false,
        activeFilterCols: initialFilterCols,

        isColVisible(colKey) {
            return this.visibleCols.includes(colKey);
        },

        toggleCol(colKey) {
            if (this.visibleCols.includes(colKey)) {
                if (this.visibleCols.length > 1) {
                    this.visibleCols = this.visibleCols.filter(c => c !== colKey);
                }
            } else {
                this.visibleCols.push(colKey);
            }
            localStorage.setItem('padu_salary_visible_columns', JSON.stringify(this.visibleCols));
        },

        showAllCols() {
            this.visibleCols = [...allSelectableColKeys];
            localStorage.setItem('padu_salary_visible_columns', JSON.stringify(this.visibleCols));
        },

        resetCols() {
            this.visibleCols = defaultTableColKeys.length > 0 ? [...defaultTableColKeys] : allSelectableColKeys.slice(0, 6);
            localStorage.setItem('padu_salary_visible_columns', JSON.stringify(this.visibleCols));
        },

        // Top Filter Header Methods
        isFilterActive(colKey) {
            return this.activeFilterCols.includes(colKey);
        },

        toggleFilter(colKey) {
            if (this.activeFilterCols.includes(colKey)) {
                if (this.activeFilterCols.length > 1) {
                    this.activeFilterCols = this.activeFilterCols.filter(c => c !== colKey);
                }
            } else {
                this.activeFilterCols.push(colKey);
            }
            localStorage.setItem('padu_salary_filter_columns', JSON.stringify(this.activeFilterCols));
        },

        addFilterFromSelect(colKey) {
            if (colKey && !this.activeFilterCols.includes(colKey)) {
                this.activeFilterCols.push(colKey);
                localStorage.setItem('padu_salary_filter_columns', JSON.stringify(this.activeFilterCols));
            }
        },

        removeFilter(colKey) {
            if (this.activeFilterCols.length > 1) {
                this.activeFilterCols = this.activeFilterCols.filter(c => c !== colKey);
                localStorage.setItem('padu_salary_filter_columns', JSON.stringify(this.activeFilterCols));
            }
        },

        showAllFilters() {
            this.activeFilterCols = [...allFilterableKeys];
            localStorage.setItem('padu_salary_filter_columns', JSON.stringify(this.activeFilterCols));
        },

        resetFilters() {
            this.activeFilterCols = defaultFilterKeys.length > 0 ? [...defaultFilterKeys] : allFilterableKeys.slice(0, 6);
            localStorage.setItem('padu_salary_filter_columns', JSON.stringify(this.activeFilterCols));
        },

        openPreview(id) {
            if (typeof window.openPreview === 'function') {
                window.openPreview(id);
            } else {
                window.dispatchEvent(new CustomEvent('open-preview', { detail: { id: id } }));
            }
        },

        beforeFilterSubmit(e) {
            const form = e.target;
            Array.from(form.elements).forEach(el => {
                if (!el.name) return;
                if (el.value === '' || el.value === 'semua') {
                    el.disabled = true;
                }
            });
        },

        submitFilterForm() {
            const form = document.getElementById('salaryCohortFilterForm');
            if (form) {
                Array.from(form.elements).forEach(el => {
                    if (el.name && (el.value === '' || el.value === 'semua')) {
                        el.disabled = true;
                    }
                });
                form.submit();
            }
        }
    };
}
</script>
@endpush
@endsection
