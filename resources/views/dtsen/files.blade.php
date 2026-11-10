@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg font-bold flex items-center justify-center"><i class="fi fi-rr-folder"></i></span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Manajemen Berkas Data & Ingesti Cepat
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 text-xs font-bold font-mono">
                    High-Speed Ingestion Engine
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Kelola berkas sumber dari folder <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-xs">src-dtsen/</code>, unduh paket arsip dari <code class="px-1.5 py-0.5 rounded bg-slate-100 text-slate-800 font-mono text-xs">src-export/</code>, dan unduh master template resmi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('dtsen.files') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-refresh"></i> <span>Refresh Berkas</span>
            </a>
            @if(isset($totalRows) && $totalRows > 0)
                <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fi fi-rr-table"></i> <span>Buka Tabel Master</span>
                </a>
            @endif
        </div>
    </div>

    <!-- 1. KARTU BERKAS HASIL EKSPOR DI FOLDER src-export/ (JIKA ADA) -->
    @if(!empty($srcExportFiles) && count($srcExportFiles) > 0)
        <div id="export-files-section" class="p-6 rounded-2xl space-y-4 shadow-sm bg-white text-slate-900 border border-emerald-200">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-base flex items-center justify-center"><i class="fi fi-rr-box"></i></span>
                    <div>
                        <h4 class="text-xs font-black uppercase tracking-wider text-emerald-950">
                            BERKAS HASIL EKSPOR TERSEDIA DI FOLDER <code class="px-2 py-0.5 rounded font-mono text-xs bg-emerald-50 text-emerald-800 border border-emerald-200">src-export/</code>
                        </h4>
                        <p class="text-[11px] text-slate-600">Paket arsip (.ZIP) resmi yang telah diekspor oleh sistem</p>
                    </div>
                </div>
                <span class="text-xs font-bold font-mono text-emerald-800 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200">
                    {{ count($srcExportFiles) }} Paket Terdeteksi
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($srcExportFiles as $exFile)
                    <div class="p-3.5 rounded-xl space-y-2 bg-slate-50 border border-slate-200 hover:border-emerald-300 hover:bg-emerald-50/30 transition-all">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold truncate font-mono text-slate-900" title="{{ $exFile['name'] }}">
                                <i class="fi fi-rr-box text-emerald-600"></i> {{ $exFile['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono pt-1.5 border-t border-slate-200 text-slate-600">
                            <span class="px-2 py-0.5 rounded font-bold bg-white text-emerald-800 border border-emerald-200">
                                {{ $exFile['size_formatted'] }}
                            </span>
                            <span class="text-slate-600 flex items-center gap-1"><i class="fi fi-rr-calendar"></i> {{ $exFile['modified_at'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- 2. UNGGAH DATASET BARU KE src-dtsen/ -->
    <div class="p-6 rounded-2xl shadow-sm bg-white text-slate-900 border border-emerald-200 space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-emerald-100 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="p-2 rounded-xl bg-emerald-100 text-emerald-800 font-bold text-base flex items-center justify-center"><i class="fi fi-rr-upload"></i></span>
                <div>
                    <h3 class="text-xs font-black uppercase tracking-wider text-emerald-950 flex items-center gap-1.5">
                        <span>UNGGAH DATASET BARU KE FOLDER</span>
                        <code class="px-2 py-0.5 rounded font-mono text-xs bg-emerald-50 text-emerald-800 border border-emerald-200">src-dtsen/</code>
                    </h3>
                    <p class="text-[11px] text-slate-600">
                        Unggah berkas data baru langsung dari peramban web. Berkas otomatis disimpan ke direktori <code class="font-mono text-emerald-900 font-bold">src-dtsen/</code> dan siap diproses.
                    </p>
                </div>
            </div>
            <span class="text-[10px] font-black uppercase tracking-wider text-emerald-800 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200 self-start sm:self-auto">
                CSV / XLSX / XLS / ZIP
            </span>
        </div>

        <form action="{{ route('dtsen.upload') }}" method="POST" enctype="multipart/form-data" class="space-y-3"
              x-data="{ fileName: '', uploading: false }" @submit="uploading = true">
            @csrf
            <div class="flex flex-col md:flex-row items-center gap-3">
                <div class="flex-1 w-full relative">
                    <label for="dataset_file" class="sr-only">Pilih Berkas Dataset Baru</label>
                    <input type="file" id="dataset_file" name="dataset_file" required accept=".csv,.xlsx,.xls,.zip"
                           aria-label="Pilih Berkas Dataset Baru"
                           @change="fileName = $event.target.files[0]?.name || ''"
                           class="w-full text-xs text-slate-700 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-emerald-600 file:text-white hover:file:bg-emerald-700 file:cursor-pointer p-1.5 border border-slate-300 rounded-xl bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <button type="submit" :disabled="uploading"
                        class="w-full md:w-auto px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-slate-400 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                    <span x-show="!uploading" class="flex items-center gap-1.5"><i class="fi fi-rr-upload"></i> Unggah ke src-dtsen/</span>
                    <span x-show="uploading">Mengunggah berkas...</span>
                </button>
            </div>
            <div class="flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <span class="flex items-center gap-1"><i class="fi fi-rr-bolt text-amber-500"></i> Mendukung file besar CSV jutaan baris & ZIP terkompresi. Nama berkas otomatis diamankan dari karakter berbahaya.</span>
                <span x-show="fileName" class="font-mono font-bold text-emerald-700" x-text="'File dipilih: ' + fileName"></span>
            </div>
        </form>
    </div>

    <!-- 3. PEMILIH & INGESTI BERKAS DARI FOLDER src-dtsen/ -->
    <div id="import-section" class="p-6 rounded-2xl shadow-sm bg-white text-slate-900 border border-slate-200 space-y-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg font-bold flex items-center justify-center"><i class="fi fi-rr-bolt"></i></span>
                    <h3 class="text-sm font-black uppercase tracking-wider text-slate-900">
                        PILIH & PROSES DATA DARI FOLDER <code class="px-2 py-0.5 rounded font-mono text-xs bg-blue-50 text-blue-800 border border-blue-200">src-dtsen/</code>
                    </h3>
                </div>
                <p class="text-xs text-slate-600 font-medium">
                    Pindahkan berkas dataset (CSV / XLSX) ke folder <code class="font-mono text-amber-800 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200">src-dtsen/</code> di root project. Pilih berkas di bawah ini untuk diproses ke database DuckDB.
                </p>
            </div>
            
            <a href="{{ route('dtsen.files') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-all shadow-sm flex items-center gap-1.5 shrink-0 self-start md:self-auto cursor-pointer border border-blue-600">
                <i class="fi fi-rr-refresh"></i> <span>Refresh Folder src-dtsen</span>
            </a>
        </div>

        @if(!empty($srcDtsenFiles) && count($srcDtsenFiles) > 0)
            <form action="{{ route('dtsen.import-local') }}" method="POST" class="space-y-4" x-data="{ selectedFile: '{{ !empty($activeDatasetFile) ? $activeDatasetFile : ($srcDtsenFiles[0]['name'] ?? '') }}', isProcessing: false }" @submit="isProcessing = true; startLocalImportSubmit($event)">
                @csrf
                <div class="space-y-2.5">
                    <div class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        <i class="fi fi-rr-folder"></i> Berkas Tersedia di Folder <code class="font-mono text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded border border-blue-200">src-dtsen/</code> ({{ count($srcDtsenFiles) }} Berkas Terdeteksi):
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($srcDtsenFiles as $f)
                            @php
                                $isActiveThisFile = (isset($totalRows) && $totalRows > 0 && ($f['name'] === ($activeDatasetFile ?? '')));
                            @endphp
                            <label for="selected_file_{{ $loop->index }}"
                                   @click="selectedFile = '{{ $f['name'] }}'" 
                                   :class="selectedFile === '{{ $f['name'] }}' ? '{{ $isActiveThisFile ? 'ring-3 ring-emerald-500 bg-emerald-50 border-emerald-500 shadow-md' : 'ring-2 ring-blue-600 bg-blue-50/90 border-blue-500 shadow-xs' }}' : '{{ $isActiveThisFile ? 'bg-emerald-50 border-emerald-500 ring-2 ring-emerald-500/30 shadow-sm' : 'bg-slate-50 border-slate-200 hover:bg-slate-100 hover:border-slate-300' }}'"
                                   class="flex items-start gap-3.5 p-4 rounded-xl border cursor-pointer transition-all relative {{ $isActiveThisFile ? 'border-2 border-emerald-500 bg-emerald-50 shadow-md shadow-emerald-500/10' : '' }}"
                                   style="{{ $isActiveThisFile ? 'background: #ecfdf5 !important; border: 2px solid #10b981 !important;' : '' }}">
                                
                                <input type="radio" id="selected_file_{{ $loop->index }}" name="selected_file" value="{{ $f['name'] }}" aria-label="Pilih berkas {{ $f['name'] }}" x-model="selectedFile" class="mt-1.5 {{ $isActiveThisFile ? 'accent-emerald-600' : 'accent-blue-600' }}">
                                
                                <div class="space-y-1.5 min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-base shrink-0">
                                                @if($f['extension'] === 'csv' || $f['extension'] === 'txt') <i class="fi fi-rr-file-spreadsheet text-blue-500"></i> @elseif($f['extension'] === 'xlsx' || $f['extension'] === 'xls') <i class="fi fi-rr-file-excel text-emerald-600"></i> @else <i class="fi fi-rr-folder text-amber-500"></i> @endif
                                            </span>
                                            <strong class="text-xs font-black truncate block {{ $isActiveThisFile ? 'text-emerald-950 font-black' : 'text-slate-900' }}" title="{{ $f['name'] }}">
                                                {{ $f['name'] }}
                                            </strong>
                                        </div>
                                        @if($isActiveThisFile)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase tracking-wider bg-emerald-600 text-white flex items-center gap-1.5 shadow-sm shrink-0">
                                                <span class="w-2 h-2 rounded-full bg-emerald-200 animate-pulse"></span>
                                                <i class="fi fi-rr-check-circle"></i> SEDANG DIGUNAKAN
                                            </span>
                                        @endif
                                    </div>

                                    @if($isActiveThisFile)
                                        <div class="text-[11px] font-bold text-emerald-800 flex items-center gap-1.5 pt-0.5">
                                            <i class="fi fi-rr-database text-emerald-600 font-bold"></i>
                                            <span>Dataset aktif di database &bull; <strong>{{ number_format($totalRows) }}</strong> baris data dimuat</span>
                                        </div>
                                    @endif

                                    <!-- Badges: Scope Set Data & Hash Kriptografis SHA-256 -->
                                    <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                        @if(!empty($f['scope_label']))
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border flex items-center gap-1 {{ $f['scope_badge'] }}">
                                                <i class="{{ $f['scope_icon'] }}"></i> {{ $f['scope_label'] }}
                                            </span>
                                        @endif
                                        @if(!empty($f['sha256']))
                                            <span class="px-2 py-0.5 rounded font-mono text-[10px] bg-slate-100 text-slate-600 border border-slate-200" title="Hash SHA-256 Lengkap: {{ $f['sha256'] }}">
                                                <i class="fi fi-rr-fingerprint text-[9px]"></i> SHA: {{ $f['sha256_short'] }}...
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Deteksi Duplikat Hash -->
                                    @if(!empty($f['is_duplicate']))
                                        <div class="p-2 rounded-lg bg-amber-50 border border-amber-300 text-amber-900 text-[10px] font-bold flex items-center gap-1.5">
                                            <i class="fi fi-rr-triangle-warning text-amber-600 shrink-0"></i>
                                            <span>Duplikat terdeteksi: Konten identik dengan <strong class="truncate">{{ $f['duplicate_of'] }}</strong></span>
                                        </div>
                                    @elseif(!empty($f['has_duplicates']))
                                        <div class="text-[10px] text-amber-700 font-semibold flex items-center gap-1">
                                            <i class="fi fi-rr-copy"></i> Memiliki salinan berkas duplikat di folder ini
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-3 text-[11px] font-mono pt-0.5 text-slate-500">
                                        <span class="px-2 py-0.5 rounded font-bold {{ $isActiveThisFile ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : 'bg-white text-slate-700 border border-slate-300' }} shadow-2xs">
                                            {{ $f['size_formatted'] }}
                                        </span>
                                        <span class="text-slate-600 flex items-center gap-1"><i class="fi fi-rr-calendar"></i> {{ $f['modified_at'] }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-slate-100">
                    <div class="text-xs flex items-center gap-2 text-slate-700 font-bold">
                        <span class="flex items-center gap-1"><i class="fi fi-rr-marker text-blue-600"></i> Berkas Terpilih:</span>
                        <code class="px-2.5 py-1 rounded font-mono text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-bold" x-text="'src-dtsen/' + selectedFile"></code>
                    </div>

                    <button type="submit" :disabled="isProcessing" class="w-full sm:w-auto px-8 py-3 rounded-xl text-white font-black text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50 bg-emerald-600 hover:bg-emerald-700">
                        <span x-show="!isProcessing">Mulai Impor & Injeksi Data</span>
                        <span x-show="isProcessing" class="flex items-center gap-2" style="display: none;">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses Data...
                        </span>
                    </button>
                </div>
            </form>
        @else
            <!-- Keadaan Kosong jika belum ada file di folder src-dtsen -->
            <div class="p-8 rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 flex items-center justify-center text-xl mx-auto font-bold">
                    <i class="fi fi-rr-folder"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold text-slate-900">Folder <code class="text-amber-800 bg-amber-50 px-1 rounded border border-amber-200 font-mono">src-dtsen/</code> Masih Kosong</h4>
                    <p class="text-xs text-slate-600 max-w-lg mx-auto">
                        Pindahkan berkas dataset Anda (seperti <code class="text-slate-800 font-mono bg-white px-1 rounded border border-slate-200">sample_1_juta_data.csv</code>) ke folder root project berikut:
                    </p>
                    <div class="py-2">
                        <code class="px-3 py-1.5 rounded-lg bg-white text-emerald-800 font-mono text-xs border border-slate-300 shadow-2xs inline-block font-bold">
                            aplikasi-cepat-analytics/src-dtsen/
                        </code>
                    </div>
                </div>
                <div>
                    <a href="{{ route('dtsen.files') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fi fi-rr-refresh"></i> <span>Cek Ulang Folder src-dtsen</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- 3. DOKUMEN MASTER FORMAT DATASET & TEMPLATE -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Template Download Card -->
        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
            <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm"><i class="fi fi-rr-download"></i></span>
                <div>
                    <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">Format Master 58 Variabel DTSEN 2026</h4>
                    <p class="text-[11px] text-slate-500">Unduh format acuan kolom resmi BPS & Bappenas</p>
                </div>
            </div>
            <p class="text-xs text-slate-600">
                Gunakan template standar ini untuk memastikan susunan header kolom 100% cocok dengan parser ingesti otomatis.
            </p>
            <div class="grid grid-cols-2 gap-2 pt-1">
                <a href="{{ route('dtsen.template', ['format' => 'xlsx']) }}" class="p-3 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-center text-xs font-bold transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-rr-file-excel text-emerald-600"></i> <span>Unduh Excel (.xlsx)</span>
                </a>
                <a href="{{ route('dtsen.template', ['format' => 'csv']) }}" class="p-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-center text-xs font-bold transition-all flex items-center justify-center gap-2">
                    <i class="fi fi-rr-file-spreadsheet text-blue-600"></i> <span>Unduh CSV (.csv)</span>
                </a>
            </div>
        </div>

        <!-- Danger Zone: Reset / Kosongkan Database -->
        <div class="p-5 bg-white rounded-2xl border border-rose-200 shadow-sm space-y-3">
            <div class="flex items-center gap-2.5 border-b border-rose-100 pb-3">
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm"><i class="fi fi-rr-trash"></i></span>
                <div>
                    <h4 class="font-extrabold text-rose-900 text-xs uppercase tracking-wider">Pembersihan Total Dataset</h4>
                    <p class="text-[11px] text-rose-600">Kosongkan database incase salah unggah berkas</p>
                </div>
            </div>
            <p class="text-xs text-slate-600">
                Menghapus seluruh baris data dan indeks di database SQLite/DuckDB agar sistem kembali bersih 0 record untuk berkas baru.
            </p>
            <div class="pt-1">
                <button type="button" 
                        @click="showClearModal = true" 
                        class="w-full p-3 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-bold transition-all flex items-center justify-center gap-2 cursor-pointer"
                        @if(($totalSystemRows ?? $totalRows) === 0) disabled title="Sistem sudah bersih" @endif>
                    <i class="fi fi-rr-trash"></i> <span>Kosongkan Seluruh Dataset</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
