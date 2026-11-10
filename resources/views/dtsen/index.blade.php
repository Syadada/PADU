@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dtsenApp()">
    <style>
    @media print {
        nav, header, button, form, .no-print, [x-show="showColumnModal"], [x-show="showPreviewModal"], [x-show="showImportProgressModal"] {
            display: none !important;
        }
        body {
            background: #ffffff !important;
            color: #0f172a !important;
            font-family: sans-serif !important;
        }
        #tabel-data {
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .overflow-x-auto, .max-h-\[70vh\] {
            max-height: none !important;
            overflow: visible !important;
        }
        table {
            width: 100% !important;
            min-width: 100% !important;
            border-collapse: collapse !important;
            page-break-inside: auto;
        }
        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        th, td {
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            font-size: 11px !important;
            color: #0f172a !important;
        }
        th {
            background-color: #f1f5f9 !important;
            font-weight: 800 !important;
        }
        th div .relative {
            display: none !important;
        }
    }
    </style>

    <!-- Header Banner Dashboard -->
    <div class="p-6 bg-white rounded-2xl border border-slate-300 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex flex-wrap items-center gap-2">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg flex items-center justify-center"><i class="{{ $activeDatasetScopeIcon ?? 'fi fi-rr-apps' }}"></i></span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Dashboard Analitik & Quality Test (DTSEN 2026)
                </h2>
                @if(!empty($activeDatasetScopeLabel))
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border flex items-center gap-1 {{ $activeDatasetScopeBadge ?? 'bg-indigo-50 text-indigo-700 border-indigo-200' }}">
                        <i class="{{ $activeDatasetScopeIcon ?? 'fi fi-rr-apps' }}"></i> {{ $activeDatasetScopeLabel }}
                    </span>
                @endif
                @if($totalRows > 0)
                    <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 text-xs font-bold font-mono border border-slate-200">
                        {{ number_format($totalRows) }} Baris
                    </span>
                @endif
            </div>
            <p class="text-xs text-slate-500 font-medium">
                {{ $activeDatasetScopeDesc ?? 'Pengolahan Data Adaptif File CSV/XLSX, Relasi Otomatis KK-Individu, & Pengecekan Kualitas Standar Validasi Data.' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5 shrink-0" x-data="{ showExportModal: false, showTemplateModal: false, showClearModal: false }">
            <a href="{{ route('dtsen.activity-logs') }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2 border border-slate-700 whitespace-nowrap shrink-0 cursor-pointer text-decoration-none">
                <i class="fi fi-rr-list-check"></i> <span>Log Aktivitas System</span> <span x-show="logCount > 0" class="px-2 py-0.5 rounded-full bg-blue-600 text-white text-[10px] font-bold" x-text="logCount"></span>
            </a>

            @if($totalRows > 0)
                <button @click="showExportModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0 cursor-pointer">
                    <i class="fi fi-rr-upload"></i> <span>Ekspor Data Paket (.ZIP)</span>
                </button>
                <button @click="showClearModal = true" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 whitespace-nowrap shrink-0 cursor-pointer">
                    <i class="fi fi-rr-trash"></i> <span>Kosongkan Data</span>
                </button>
            @endif

            <!-- Dropdown Format Template Data -->
            <div class="relative shrink-0">
                <button @click="showTemplateModal = !showTemplateModal" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all flex items-center gap-1.5 border border-slate-200 whitespace-nowrap cursor-pointer">
                    <i class="fi fi-rr-download"></i> <span>Template Data</span> <i class="fi fi-rr-angle-small-down"></i>
                </button>
                <div x-show="showTemplateModal" 
                     @click.away="showTemplateModal = false" 
                     x-transition 
                     class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 py-1.5 text-xs font-bold text-slate-700 space-y-0.5" 
                     style="display: none;">
                    <a href="{{ route('dtsen.template', ['format' => 'xlsx']) }}" class="block px-4 py-2.5 hover:bg-emerald-50 text-slate-900 flex items-center gap-2 transition-colors">
                        <i class="fi fi-rr-file-excel text-emerald-600"></i> <span>Template Excel (.xlsx)</span>
                    </a>
                    <a href="{{ route('dtsen.template', ['format' => 'csv']) }}" class="block px-4 py-2.5 hover:bg-blue-50 text-slate-900 flex items-center gap-2 transition-colors">
                        <i class="fi fi-rr-file-spreadsheet text-blue-600"></i> <span>Template CSV (.csv)</span>
                    </a>
                </div>
            </div>

            <!-- Modal Konfirmasi Kosongkan Data (Incase user salah upload file) -->
            <div x-show="showClearModal" 
                 x-transition 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" 
                 style="display: none;">
                <div @click.away="showClearModal = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md p-6 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-3">
                        <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg"><i class="fi fi-rr-trash"></i></span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm">Konfirmasi Kosongkan Data</h3>
                            <p class="text-[11px] text-slate-500">Hapus seluruh data di sistem incase salah upload file</p>
                        </div>
                    </div>

                    <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-1">
                        <p><i class="fi fi-rr-triangle-warning text-rose-600"></i> <strong>Perhatian:</strong> Tindakan ini akan menghapus <strong>{{ number_format($totalRows) }} baris data</strong> yang saat ini ada di sistem. Sistem akan dikosongkan secara total agar Anda bisa mengunggah file yang benar.</p>
                    </div>

                    <form action="{{ route('dtsen.clear') }}" method="POST" class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                        @csrf
                        <button type="button" @click="showClearModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                            <i class="fi fi-rr-trash"></i> <span>Ya, Kosongkan Sekarang</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Modal Opsi Ekspor Data (2 Mode Utama & Solusi Rekonsiliasi Original_Row_Index) -->
            <div x-show="showExportModal" 
                 x-transition 
                 class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" 
                 style="display: none;">
                <div @click.away="showExportModal = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-2">
                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold"><i class="fi fi-rr-upload"></i></span>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Opsi Ekspor Data DTSEN 2026</h3>
                                <p class="text-[11px] text-slate-500">Pilih mode ekspor dan format berkas yang diinginkan</p>
                            </div>
                        </div>
                        <button type="button" @click="showExportModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base flex items-center"><i class="fi fi-rr-cross"></i></button>
                    </div>

                    <form action="{{ route('dtsen.export') }}" method="GET" class="space-y-4">
                        <input type="hidden" name="search" value="{{ $search }}">
                        <input type="hidden" name="desil" value="{{ $desil }}">
                        <input type="hidden" name="jenis_kelamin" value="{{ $jenisKelamin }}">
                        <input type="hidden" name="pendidikan" value="{{ $pendidikan }}">
                        <input type="hidden" name="status_bekerja" value="{{ $statusBekerja }}">
                        <input type="hidden" name="status_kawin" value="{{ $statusKawin }}">

                        <!-- 1. Mode Ekspor Data -->
                        <div class="space-y-2">
                            <div class="text-xs font-bold text-slate-800 uppercase">1. Mode Ekspor Data</div>
                            <div class="space-y-2">
                                <label for="dtsen_export_mode_as_is" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" id="dtsen_export_mode_as_is" name="mode" value="as_is" aria-label="Mode A: Ekspor As-Is (+ Catatan Error Audit)" checked class="mt-0.5 accent-blue-600">
                                    <div>
                                        <strong class="block text-xs text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-document text-blue-600"></i> Mode A: Ekspor As-Is (+ Catatan Error Audit)</strong>
                                        <p class="text-[11px] text-slate-500">Mengekspor seluruh baris data apa adanya ditambah 1 kolom rincian temuan error. Urutan & indeks baris tetap presisi 100%.</p>
                                    </div>
                                </label>
                                <label for="dtsen_export_mode_cleaned_only" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" id="dtsen_export_mode_cleaned_only" name="mode" value="cleaned_only" aria-label="Mode B: Ekspor Cleaned Only (Data Cacat Dibuang)" class="mt-0.5 accent-emerald-600">
                                    <div>
                                        <strong class="block text-xs text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-check-circle text-emerald-600"></i> Mode B: Ekspor Cleaned Only (Data Cacat Dibuang)</strong>
                                        <p class="text-[11px] text-slate-500">Mengekspor hanya baris data yang Valid. Disertai kolom <code>Original_Row_Index</code> untuk rekonsiliasi ke master file.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Format File -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-100">
                            <div class="text-xs font-bold text-slate-800 uppercase">2. Format Berkas Paket (.ZIP)</div>
                            <div class="grid grid-cols-2 gap-3">
                                <label for="dtsen_export_format_xlsx" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex flex-col items-center justify-center text-center">
                                    <input type="radio" id="dtsen_export_format_xlsx" name="format" value="xlsx" aria-label="Format Excel Terbaru (.xlsx)" checked class="mb-1 accent-emerald-600">
                                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-file-excel text-emerald-600"></i> Excel Terbaru (.xlsx)</span>
                                </label>
                                <label for="dtsen_export_format_csv" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex flex-col items-center justify-center text-center">
                                    <input type="radio" id="dtsen_export_format_csv" name="format" value="csv" aria-label="Format CSV File (.csv)" class="mb-1 accent-blue-600">
                                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-file-spreadsheet text-blue-600"></i> CSV File (.csv)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Catatan Penting Paket Dual File & Auto Reset -->
                        <div class="p-3 bg-amber-500 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                            <p><i class="fi fi-rr-folder"></i> <strong>Lokasi Hasil Ekspor:</strong> Paket berkas (.ZIP) akan diekspor & disimpan langsung di folder <code class="text-blue-900 font-mono font-bold">src-export/</code> di root project (bebas memori & anti crash browser).</p>
                            <p><i class="fi fi-rr-broom"></i> <strong>Auto-Purge Reset:</strong> Setelah ekspor selesai, data sistem dikosongkan agar bersih untuk pemrosesan file berikutnya.</p>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="showExportModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" @click="showExportModal = false" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                                <i class="fi fi-rr-download"></i> <span>Ekspor Langsung ke Folder src-export/</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if(!empty($srcExportFiles) && count($srcExportFiles) > 0)
        <!-- Kartu Berkas Hasil Ekspor di Folder src-export/ -->
        <div class="p-5 rounded-2xl space-y-3 shadow-md card-dark-slate" style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #065f46 !important;">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="p-1.5 rounded-xl text-emerald-400 font-bold text-sm flex items-center justify-center" style="background-color: rgba(16, 185, 129, 0.2) !important;"><i class="fi fi-rr-box"></i></span>
                    <h4 class="text-xs font-black uppercase tracking-wider" style="color: #6ee7b7 !important;">
                        BERKAS HASIL EKSPOR TERSEDIA DI FOLDER <code class="px-2 py-0.5 rounded font-mono text-xs" style="background-color: #064e3b !important; color: #a7f3d0 !important; border: 1px solid #047857 !important;">src-export/</code>
                    </h4>
                </div>
                <span class="text-[11px] font-bold font-mono" style="color: #34d399 !important;">
                    {{ count($srcExportFiles) }} Paket Ekspor Terdeteksi
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3">
                @foreach($srcExportFiles as $exFile)
                    <div class="p-3.5 rounded-xl space-y-1.5" style="background-color: #1e293b !important; border: 1px solid #334155 !important;">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold truncate font-mono" style="color: #a7f3d0 !important;" title="{{ $exFile['name'] }}">
                                <i class="fi fi-rr-box text-emerald-400"></i> {{ $exFile['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono pt-1 border-t" style="border-color: #334155 !important; color: #94a3b8 !important;">
                            <span class="px-2 py-0.5 rounded font-bold" style="background-color: #020617 !important; color: #f59e0b !important; border: 1px solid #334155 !important;">
                                {{ $exFile['size_formatted'] }}
                            </span>
                            <span style="color: #cbd5e1 !important;"><i class="fi fi-rr-calendar"></i> {{ $exFile['modified_at'] }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Selector & Impor Berkas Data dari Folder src-dtsen (High-Speed Python Engine) -->
    <div class="p-6 rounded-2xl shadow-xl card-dark-slate space-y-4 font-sans" style="background-color: #0f172a !important; color: #ffffff !important; border: 1px solid #1e293b !important;">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b pb-4" style="border-color: #334155 !important;">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="p-2 rounded-xl text-blue-400 text-lg flex items-center justify-center" style="background-color: rgba(59, 130, 246, 0.2);"><i class="fi fi-rr-bolt"></i></span>
                    <h3 class="text-sm font-black uppercase tracking-wider" style="color: #ffffff !important;">
                        PILIH & PROSES DATA DARI FOLDER <code class="px-2 py-0.5 rounded font-mono text-xs" style="background-color: #1e3a8a !important; color: #93c5fd !important; border: 1px solid #1d4ed8 !important;">src-dtsen/</code>
                    </h3>
                </div>
                <p class="text-xs font-medium" style="color: #cbd5e1 !important;">
                    Pindahkan berkas dataset (CSV / XLSX) ke folder <code class="font-mono" style="color: #fde047 !important;">src-dtsen/</code> di root project. Pilih berkas di bawah ini untuk diproses.
                </p>
            </div>
            
            <a href="{{ route('dtsen.index') }}" class="btn-refresh-folder px-4 py-2 rounded-xl text-white text-xs font-bold transition-all shadow-md flex items-center gap-1.5 shrink-0 self-start md:self-auto cursor-pointer" style="background-color: #0284c7 !important; color: #ffffff !important; border: 1px solid #0369a1 !important;">
                <i class="fi fi-rr-refresh"></i> <span>Refresh Folder src-dtsen</span>
            </a>
        </div>

        @if(!empty($srcDtsenFiles) && count($srcDtsenFiles) > 0)
            <form action="{{ route('dtsen.import-local') }}" method="POST" class="space-y-4" x-data="{ selectedFile: '{{ !empty($activeDatasetFile) ? $activeDatasetFile : ($srcDtsenFiles[0]['name'] ?? '') }}', isProcessing: false }" @submit="isProcessing = true; startLocalImportSubmit($event)">
                @csrf
                <div class="space-y-2.5">
                    <div class="block text-xs font-bold uppercase tracking-wider" style="color: #cbd5e1 !important;">
                        <i class="fi fi-rr-folder"></i> Berkas Tersedia di Folder <code class="font-mono" style="color: #93c5fd !important;">src-dtsen/</code> ({{ count($srcDtsenFiles) }} Berkas Terdeteksi):
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($srcDtsenFiles as $f)
                            @php
                                $isActiveThisFile = (isset($totalRows) && $totalRows > 0 && ($f['name'] === ($activeDatasetFile ?? '')));
                            @endphp
                            <label for="dtsen_selected_file_{{ $loop->index }}"
                                   @click="selectedFile = '{{ $f['name'] }}'" 
                                   :class="selectedFile === '{{ $f['name'] }}' ? 'selected-card' : ''"
                                   class="file-option-card flex items-start gap-3 relative overflow-hidden group {{ $isActiveThisFile ? 'ring-2 ring-emerald-400 border-emerald-500' : '' }}"
                                   style="background-color: {{ $isActiveThisFile ? '#064e3b' : '#1e293b' }} !important; color: #ffffff !important; border: 1px solid {{ $isActiveThisFile ? '#10b981' : '#334155' }} !important;">
                                
                                <input type="radio" id="dtsen_selected_file_{{ $loop->index }}" name="selected_file" value="{{ $f['name'] }}" aria-label="Pilih berkas {{ $f['name'] }}" x-model="selectedFile" class="mt-1 accent-blue-500">
                                
                                <div class="space-y-1 min-w-0 flex-1">
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="text-base shrink-0">
                                                @if($f['extension'] === 'csv' || $f['extension'] === 'txt') <i class="fi fi-rr-file-spreadsheet text-blue-400"></i> @elseif($f['extension'] === 'xlsx' || $f['extension'] === 'xls') <i class="fi fi-rr-file-excel text-emerald-400"></i> @else <i class="fi fi-rr-folder text-amber-400"></i> @endif
                                            </span>
                                            <strong class="text-xs font-bold truncate block group-hover:text-blue-300 transition-colors" style="color: #ffffff !important;" title="{{ $f['name'] }}">
                                                {{ $f['name'] }}
                                            </strong>
                                        </div>
                                        @if($isActiveThisFile)
                                            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-emerald-500 text-slate-950 flex items-center gap-1 shadow-2xs shrink-0">
                                                <i class="fi fi-rr-check-circle"></i> SEDANG DIGUNAKAN
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 text-[11px] font-mono pt-1" style="color: #94a3b8 !important;">
                                        <span class="file-size-badge" style="background-color: #020617 !important; color: #f59e0b !important; border: 1px solid #334155 !important;">
                                            {{ $f['size_formatted'] }}
                                        </span>
                                        <span><i class="fi fi-rr-calendar"></i> {{ $f['modified_at'] }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3 border-t" style="border-color: #334155 !important;">
                    <div class="text-xs flex items-center gap-2" style="color: #cbd5e1 !important;">
                        <span><i class="fi fi-rr-marker"></i> Berkas Terpilih:</span>
                        <code class="px-2.5 py-1 rounded font-mono text-xs" style="background-color: #020617 !important; color: #34d399 !important; border: 1px solid #334155 !important;" x-text="'src-dtsen/' + selectedFile"></code>
                    </div>

                    <button type="submit" :disabled="isProcessing" class="btn-process-data w-full sm:w-auto px-8 py-3 rounded-xl text-white font-bold text-xs shadow-lg transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50" style="background-color: #059669 !important; color: #ffffff !important;">
                        <span x-show="!isProcessing">Mulai Impor Data</span>
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
            <div class="p-8 rounded-xl border border-dashed border-slate-700 bg-slate-800/40 text-center space-y-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 flex items-center justify-center text-xl mx-auto font-bold">
                    <i class="fi fi-rr-folder"></i>
                </div>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold text-white">Folder <code class="text-amber-300 font-mono">src-dtsen/</code> Masih Kosong</h4>
                    <p class="text-xs text-slate-400 max-w-lg mx-auto">
                        Pindahkan berkas dataset Anda (seperti <code class="text-slate-200">data_dtsen.csv</code>) ke folder root project berikut:
                    </p>
                    <div class="py-2">
                        <code class="px-3 py-1.5 rounded-lg bg-slate-900 text-emerald-400 font-mono text-xs border border-slate-700 inline-block">
                            aplikasi-cepat-analytics/src-dtsen/
                        </code>
                    </div>
                </div>
                <div>
                    <a href="{{ route('dtsen.index') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-1.5 cursor-pointer">
                        <i class="fi fi-rr-refresh"></i> <span>Refresh Folder src-dtsen</span>
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Alert Notifikasi Flash Session -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold text-xs flex items-center gap-2">
            <i class="fi fi-rr-check-circle text-emerald-600"></i> <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-900 font-bold text-xs flex items-center gap-2">
            <i class="fi fi-rr-cross-circle text-rose-600"></i> <span>{{ session('error') }}</span>
        </div>
    @endif

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- KETIKA SISTEM KOSONG (0 BARIS DATA) -> BERSIH TOTAL TANPA MENGHASILKAN HEADER KOSONG APA PUN -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-3">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mx-auto font-bold"><i class="fi fi-rr-folder"></i></div>
            <h3 class="font-extrabold text-slate-900 text-base">Sistem Dalam Keadaan Bersih (Belum Ada File Data)</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">Silakan unggah berkas berformat <strong>.csv</strong> atau <strong>.xlsx</strong> menggunakan form di atas, atau klik tombol <strong><i class="fi fi-rr-bolt"></i> Demo Data (100 Baris / 30 Error)</strong> untuk memulai pengujian.</p>
        </div>
    @else
        <!-- KARTU WARNA KUNING: METRIK STATISTIK & STANDAR VALIDASI DATA -->
        <div id="summary-stats-section" class="p-6 rounded-2xl bg-amber-50/90 border-2 border-amber-300 text-amber-950 shadow-sm space-y-4">
            <div>
                <div class="flex items-center justify-between border-b border-amber-200 pb-3 mb-3">
                    <h3 class="font-black text-amber-900 text-sm flex items-center gap-2 uppercase tracking-wide">
                        <i class="fi fi-rr-shield-check"></i> <span>Ringkasan Metrik Kualitas Data & Evaluasi Adaptif</span>
                    </h3>
                    <span class="px-3 py-1 bg-amber-200/80 text-amber-900 rounded-full font-bold text-xs">
                        Metrik Adaptif File Upload
                    </span>
                </div>

                <!-- Tabel Ringkasan Metrik Kuning -->
                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                    <div class="p-3 bg-white/90 rounded-xl border border-amber-200 text-center">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase">Total Baris Data</span>
                        <strong class="text-xl font-black text-slate-900">{{ number_format($totalRows) }}</strong>
                    </div>
                    <div class="p-3 bg-white/90 rounded-xl border border-amber-200 text-center">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase">Kolom Terbaca</span>
                        <strong class="text-xl font-black text-slate-900">{{ count($activeColumnsMap) }}</strong>
                    </div>
                    <div class="p-3 bg-white/90 rounded-xl border border-amber-200 text-center">
                        <span class="block text-[11px] font-bold text-slate-500 uppercase">KK Terelasi</span>
                        <strong class="text-xl font-black text-blue-900">{{ number_format($totalKk) }}</strong>
                    </div>
                    <div class="p-3 bg-emerald-100/90 rounded-xl border border-emerald-300 text-center">
                        <span class="block text-[11px] font-bold text-emerald-800 uppercase flex items-center gap-1.5"><i class="fi fi-rr-check-circle text-emerald-600"></i> Data Valid</span>
                        <strong class="text-xl font-black text-emerald-950">{{ number_format($validCount) }}</strong>
                    </div>
                    <div class="p-3 bg-amber-100/90 rounded-xl border border-amber-300 text-center">
                        <span class="block text-[11px] font-bold text-amber-900 uppercase flex items-center gap-1.5"><i class="fi fi-rr-info text-amber-600"></i> Missing Value</span>
                        <strong class="text-xl font-black text-amber-950">{{ number_format($warningCount) }}</strong>
                    </div>
                    <div class="p-3 bg-rose-100/90 rounded-xl border border-rose-300 text-center">
                        <span class="block text-[11px] font-bold text-rose-800 uppercase flex items-center gap-1.5"><i class="fi fi-rr-cross-circle text-rose-600"></i> Critical Error</span>
                        <strong class="text-xl font-black text-rose-950">{{ number_format($criticalCount) }}</strong>
                    </div>
                    <div class="p-3 bg-purple-100/90 rounded-xl border border-purple-300 text-center">
                        <span class="block text-[11px] font-bold text-purple-900 uppercase flex items-center gap-1.5"><i class="fi fi-rr-triangle-warning text-purple-600"></i> Multi-Error (3+)</span>
                        <strong class="text-xl font-black text-purple-950">{{ number_format($multiErrorCount ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-amber-200/80 text-xs leading-relaxed text-amber-900/90 space-y-1.5 font-medium">
                <p>
                    <i class="fi fi-rr-thumbtack text-blue-600"></i> <strong>Catatan Validasi & Relasi Standar Data:</strong> Berdasarkan standar pemeriksaan kualitas data DTSEN 2026, data diolah melalui pengecekan format (NIK & KK 16 digit), keabsahan nilai desil (1 s.d. 10), serta kelengkapan isian.
                </p>
            </div>
        </div>

        <!-- SEKSI ANALISIS METRIK GAJI & PENDAPATAN SUBJEK (TAMPIL HANYA JIKA FILE MEMILIKI VARIABEL GAJI) -->
        @if(!empty($hasSalaryColumn))
        <div id="salary-analytics-section" class="space-y-5">
            
            <!-- 1. FORM FILTER KONTROL METRIK GAJI (TEPAT DI ATAS KARTU METRIK GAJI) -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <form method="GET" action="{{ route('dtsen.index') }}#salary-analytics-section" id="salaryFilterForm" class="space-y-4">
                    <input type="hidden" name="kpi_var" value="{{ $kpiTargetVar }}">

                    @php
                        $activeSalaryFilters = [];
                        if (request('salary_search')) {
                            $activeSalaryFilters['search'] = request('salary_search');
                        }
                        foreach ($importFilterableColumns as $cK => $cT) {
                            if (request('salary_' . $cK) && request('salary_' . $cK) !== 'semua') {
                                $activeSalaryFilters[$cK] = $cT . ': ' . request('salary_' . $cK);
                            }
                        }
                    @endphp

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fi fi-rr-search"></i> <span>Filter Khusus Analisis Metrik Gaji Kelompok Subjek</span>
                            </h3>
                            <p class="text-xs text-slate-500">Tentukan kriteria kelompok (misal: Perempuan, SMA, Desil Kesejahteraan, Status Pekerjaan) untuk menghitung statistik gaji kelompok tersebut secara terpisah.</p>
                        </div>

                        @if(!empty($activeSalaryFilters))
                            <div class="flex flex-wrap items-center gap-1.5 bg-blue-50/80 p-2 rounded-xl border border-blue-200 shrink-0">
                                <span class="text-[11px] font-bold text-blue-900">Kelompok Terfilter Gaji:</span>
                                @foreach($activeSalaryFilters as $sFKey => $sFBadge)
                                    <span class="px-2 py-0.5 bg-blue-600 text-white rounded-md text-[10px] font-bold">{{ $sFBadge }}</span>
                                @endforeach
                                <a href="{{ request()->fullUrlWithQuery(array_fill_keys(array_merge(['salary_search'], array_map(fn($k) => 'salary_' . $k, array_keys($importFilterableColumns))), null)) }}" class="text-[10px] font-bold text-rose-600 hover:text-rose-800 transition-colors ml-1">Reset Filter Gaji</a>
                            </div>
                        @endif
                    </div>

                    <!-- Row Filter Options Grid (Dedicated Filter Dropdowns for EVERY Column in the Uploaded Data Table) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 col-span-full">
                        <!-- 1. Text Search (Nama Subjek) -->
                        <div>
                            <label for="index_salary_search" class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">Cari Nama Subjek</label>
                            <input type="text" id="index_salary_search" name="salary_search" aria-label="Cari Nama Subjek" value="{{ request('salary_search') }}" placeholder="Ketik nama subjek..." class="w-full text-xs font-medium bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- 2. Dedicated Dynamic Data-Driven Filter Dropdowns for EVERY Column in the Data Table -->
                        @foreach($importFilterableColumns as $colKey => $colTitle)
                            @if(isset($importColumnDistinctValues[$colKey]) && count($importColumnDistinctValues[$colKey]) > 0)
                                <div>
                                    <label for="index_salary_{{ $colKey }}" class="block text-[11px] font-extrabold text-blue-900 uppercase tracking-wider mb-1">{{ $colTitle }}</label>
                                    <select id="index_salary_{{ $colKey }}" name="salary_{{ $colKey }}" aria-label="Filter {{ $colTitle }}" class="w-full text-xs font-bold bg-blue-50 border border-blue-300 text-blue-900 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                        <option value="semua">Semua {{ $colTitle }}</option>
                                        @foreach($importColumnDistinctValues[$colKey] as $vItem)
                                            <option value="{{ $vItem }}" {{ (request('salary_' . $colKey) == $vItem) ? 'selected' : '' }}>{{ $vItem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ request()->fullUrlWithQuery(array_fill_keys(array_merge(['salary_search'], array_map(fn($k) => 'salary_' . $k, array_keys($importFilterableColumns))), null)) }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                            <i class="fi fi-rr-refresh"></i> Reset Filter Gaji
                        </a>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1.5">
                            <i class="fi fi-rr-dollar"></i> <span>Hitung Metrik Gaji Terfilter</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. KARTU METRIK GAJI & PENDAPATAN SUBJEK (KPI CARDS HIGH-CONTRAST & HIGH-VISIBILITY) -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-emerald-100 text-emerald-800 rounded-xl text-lg font-bold flex items-center justify-center"><i class="fi fi-rr-dollar"></i></span>
                            <h3 class="font-extrabold text-slate-900 text-base">Analisis Metrik Gaji & Pendapatan Subjek</h3>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Metrik kalkulasi otomatis Gaji Tertinggi (MAX), Terendah (MIN), Rata-Rata (AVG), dan Kumulatif (SUM) bereaksi terhadap filter yang Anda tentukan di atas.</p>
                    </div>
                </div>

                @if(!empty($isSalaryFallback))
                    <div class="p-3.5 rounded-xl border text-xs font-bold flex items-center gap-2 shadow-sm" style="background-color: #fffbeb; border-color: #fde68a; color: #78350f;">
                        <span class="text-base">ℹ️</span>
                        <span>Kombinasi filter spesifik tidak ditemukan pada dataset. Menampilkan kalkulasi metrik gaji keseluruhan dataset.</span>
                    </div>
                @endif

                <!-- 4 Kartu KPI Metrik Utama Gaji (RESPONSIVE FLEX & OVERFLOW SAFE) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                    <!-- Rata-Rata Gaji -->
                    <div class="kpi-stat-card p-5 rounded-2xl border shadow-sm space-y-2 min-w-0" style="background-color: #eff6ff; border-color: #bfdbfe;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #1e40af;">
                            <span class="truncate flex items-center gap-1.5"><i class="fi fi-rr-stats text-blue-600"></i> Gaji Rata-Rata (AVG)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black shrink-0" style="background-color: #dbeafe; color: #1e3a8a;">AVG</span>
                        </div>
                        <div class="kpi-stat-value text-slate-900 font-mono" title="Rp {{ number_format($gajiAvg, 0, ',', '.') }}">
                            <span class="text-sm font-bold text-slate-500 mr-1">Rp</span>
                            <span class="truncate">{{ number_format($gajiAvg, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-xs font-medium truncate" style="color: #2563eb;">Dari {{ number_format($gajiCount) }} subjek terfilter</p>
                    </div>

                    <!-- Gaji Tertinggi -->
                    <div class="kpi-stat-card p-5 rounded-2xl border shadow-sm space-y-2 min-w-0" style="background-color: #ecfdf5; border-color: #a7f3d0;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #065f46;">
                            <span class="truncate flex items-center gap-1.5"><i class="fi fi-rr-chart-line-up text-emerald-600"></i> Gaji Tertinggi (MAX)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black shrink-0" style="background-color: #d1fae5; color: #064e3b;">MAX</span>
                        </div>
                        <div class="kpi-stat-value text-slate-900 font-mono" title="Rp {{ number_format($gajiMax, 0, ',', '.') }}">
                            <span class="text-sm font-bold text-slate-500 mr-1">Rp</span>
                            <span class="truncate">{{ number_format($gajiMax, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs font-medium truncate" style="color: #047857;">
                            @if($gajiMaxSubjek)
                                Subjek: <strong class="font-extrabold" style="color: #064e3b;"><span x-show="isMasked">{{ $gajiMaxSubjek->masked_nama ?? ($gajiMaxSubjek->nama ?? 'Subjek') }}</span><span x-show="!isMasked" style="display:none;">{{ $gajiMaxSubjek->nama ?? 'Subjek' }}</span></strong>
                            @else
                                Subjek: <em class="text-slate-400">Belum ada data terfilter</em>
                            @endif
                        </div>
                    </div>

                    <!-- Gaji Terendah -->
                    <div class="kpi-stat-card p-5 rounded-2xl border shadow-sm space-y-2 min-w-0" style="background-color: #fffbeb; border-color: #fde68a;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #92400e;">
                            <span class="truncate flex items-center gap-1.5"><i class="fi fi-rr-chart-line-down text-rose-600"></i> Gaji Terendah (MIN)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black shrink-0" style="background-color: #fef3c7; color: #78350f;">MIN</span>
                        </div>
                        <div class="kpi-stat-value text-slate-900 font-mono" title="Rp {{ number_format($gajiMin, 0, ',', '.') }}">
                            <span class="text-sm font-bold text-slate-500 mr-1">Rp</span>
                            <span class="truncate">{{ number_format($gajiMin, 0, ',', '.') }}</span>
                        </div>
                        <div class="text-xs font-medium truncate" style="color: #b45309;">
                            @if($gajiMinSubjek)
                                Subjek: <strong class="font-extrabold" style="color: #78350f;"><span x-show="isMasked">{{ $gajiMinSubjek->masked_nama ?? ($gajiMinSubjek->nama ?? 'Subjek') }}</span><span x-show="!isMasked" style="display:none;">{{ $gajiMinSubjek->nama ?? 'Subjek' }}</span></strong>
                            @else
                                Subjek: <em class="text-slate-400">Belum ada data terfilter</em>
                            @endif
                        </div>
                    </div>

                    <!-- Total Kumulatif Gaji -->
                    <div class="kpi-stat-card p-5 rounded-2xl border shadow-sm space-y-2 min-w-0" style="background-color: #faf5ff; border-color: #e9d5ff;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #6b21a8;">
                            <span class="truncate flex items-center gap-1.5"><i class="fi fi-rr-dollar text-purple-600"></i> Total Kumulatif (SUM)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black shrink-0" style="background-color: #f3e8ff; color: #581c87;">SUM</span>
                        </div>
                        <div class="kpi-stat-value text-slate-900 font-mono" title="Rp {{ number_format($gajiSum, 0, ',', '.') }}">
                            <span class="text-sm font-bold text-slate-500 mr-1">Rp</span>
                            <span class="truncate">{{ number_format($gajiSum, 0, ',', '.') }}</span>
                        </div>
                        <p class="text-xs font-medium truncate" style="color: #7e22ce;">Total seluruh pendapatan terfilter</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                if (window.location.hash === '#salary-analytics-section') {
                    const el = document.getElementById('salary-analytics-section');
                    if (el) {
                        setTimeout(function() {
                            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }, 150);
                    }
                }
            });
        </script>

        <!-- SEKSI DINAMIS 2: KPI METRIK DINAMIS & DIAGRAM TOP 5 / BOTTOM 5 WIDGET -->
        <div id="kpi-analytics-section" class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <i class="fi fi-rr-chart-pie-alt"></i> <span>Analitik Metrik & Diagram Top 5 / Bottom 5 Dinamis</span>
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Pilih variabel target dari file unggahan untuk menghitung statistik Nilai Maksimum, Minimum, Rata-Rata, & Peringkat.</p>
                </div>

                <!-- Form Dropdown Pilihan Variabel Target KPI oleh User -->
                <form method="GET" action="{{ route('dtsen.index') }}#kpi-analytics-section" id="kpiVarForm" class="flex items-center gap-2 shrink-0">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="desil" value="{{ $desil }}">
                    <input type="hidden" name="jenis_kelamin" value="{{ $jenisKelamin }}">
                    <input type="hidden" name="pendidikan" value="{{ $pendidikan }}">
                    <input type="hidden" name="status_bekerja" value="{{ $statusBekerja }}">
                    <input type="hidden" name="status_kawin" value="{{ $statusKawin }}">
                    <input type="hidden" name="filter_col" value="{{ $filterCol }}">
                    <input type="hidden" name="filter_val" value="{{ $filterVal }}">
                    
                    <label for="index_kpi_var" class="text-xs font-bold text-slate-700 whitespace-nowrap">Variabel Target:</label>
                    <select id="index_kpi_var" name="kpi_var" aria-label="Variabel Target" class="text-xs font-bold bg-blue-50 border border-blue-300 rounded-xl px-3 py-2 text-blue-900 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm">
                        @foreach($activeColumnsMap as $key => $title)
                            <option value="{{ $key }}" {{ $kpiTargetVar === $key ? 'selected' : '' }}>{{ $title }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- Cards Metrik Kalkulasi Dinamis (Max, Min, Avg, Sum) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-sm space-y-1">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-100">Nilai Maksimum (MAKSIMUM)</span>
                    <div class="text-2xl font-black text-white">{{ is_numeric($kpiMax) ? number_format($kpiMax, (floor($kpiMax) == $kpiMax ? 0 : 2), ',', '.') : $kpiMax }}</div>
                    <p class="text-[11px] font-bold text-emerald-100">Nilai tertinggi dari {{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }}</p>
                </div>

                <div class="p-4 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-700 text-white shadow-sm space-y-1">
                    <span class="text-xs font-black uppercase tracking-wider text-blue-100">Rata-Rata (RATA-RATA)</span>
                    <div class="text-2xl font-black text-white">{{ number_format($kpiAvg, 2, ',', '.') }}</div>
                    <p class="text-[11px] font-bold text-blue-100">Nilai rata-rata seluruh record</p>
                </div>

                <div class="p-4 rounded-xl bg-gradient-to-br from-amber-600 to-orange-700 text-white shadow-sm space-y-1">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-100">Nilai Minimum (MINIMUM)</span>
                    <div class="text-2xl font-black text-white">{{ is_numeric($kpiMin) ? number_format($kpiMin, (floor($kpiMin) == $kpiMin ? 0 : 2), ',', '.') : $kpiMin }}</div>
                    <p class="text-[11px] font-bold text-amber-100">Nilai terendah dari dataset</p>
                </div>

                <div class="p-4 rounded-xl bg-gradient-to-br from-purple-600 to-slate-800 text-white shadow-sm space-y-1">
                    <span class="text-xs font-black uppercase tracking-wider text-purple-100">Total Akumulasi (TOTAL)</span>
                    <div class="text-2xl font-black text-white">{{ is_numeric($kpiSum) ? number_format($kpiSum, (floor($kpiSum) == $kpiSum ? 0 : 2), ',', '.') : $kpiSum }}</div>
                    <p class="text-[11px] font-bold text-purple-100">Jumlah total seluruh baris</p>
                </div>
            </div>

            <!-- Diagram Top 5 & Bottom 5 Widget Side-by-Side -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <!-- Top 5 Chart -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="font-bold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                            <i class="fi fi-rr-trophy"></i> <span>Top 5 Peringkat Teratas</span> ({{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }})
                        </h4>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">5 TERATAS</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($top5Records as $index => $rec)
                            @php
                                $rawDisplayVal = null;
                                $targetClean = strtolower(str_replace(' ', '_', trim($kpiTargetVar)));

                                if (isset($rec->val) && $rec->val !== '' && $rec->val !== null) {
                                    $rawDisplayVal = $rec->val;
                                } elseif ($targetClean === 'desil_nasional' || $targetClean === 'desil') {
                                    $rawDisplayVal = $rec->keluarga ? 'Desil ' . $rec->keluarga->desil_nasional : ($rec->extra_attributes['desil_nasional'] ?? ($rec->extra_attributes['desil'] ?? null));
                                    if (is_numeric($rawDisplayVal)) {
                                        $rawDisplayVal = 'Desil ' . $rawDisplayVal;
                                    }
                                } elseif (isset($rec->$kpiTargetVar) && $rec->$kpiTargetVar !== '' && $rec->$kpiTargetVar !== null) {
                                    $rawDisplayVal = $rec->$kpiTargetVar;
                                } elseif (!empty($rec->extra_attributes[$kpiTargetVar])) {
                                    $rawDisplayVal = $rec->extra_attributes[$kpiTargetVar];
                                } elseif (!empty($rec->extra_attributes) && is_array($rec->extra_attributes)) {
                                    foreach ($rec->extra_attributes as $ekey => $eval) {
                                        $ekeyClean = strtolower(str_replace(' ', '_', trim($ekey)));
                                        if ($ekeyClean === $targetClean || str_contains($ekeyClean, $targetClean) || str_contains($targetClean, $ekeyClean)) {
                                            $rawDisplayVal = $eval;
                                            break;
                                        }
                                    }
                                }

                                if (empty($rawDisplayVal)) {
                                    if (in_array($targetClean, ['pendidikan', 'ijazah_tertinggi_yang_dimiliki', 'jenjang_tertinggi_yang_diduduki', 'pendidikan_terakhir'])) {
                                        $rawDisplayVal = $rec->ijazah_tertinggi_yang_dimiliki 
                                            ?? ($rec->jenjang_tertinggi_yang_diduduki 
                                            ?? ($rec->extra_attributes['ijazah_tertinggi_yang_dimiliki'] 
                                            ?? ($rec->extra_attributes['pendidikan'] 
                                            ?? ($rec->extra_attributes['pendidikan_terakhir'] 
                                            ?? ($rec->extra_attributes['tingkat_pendidikan'] ?? '-')))));
                                    } elseif (in_array($targetClean, ['status_bekerja', 'pekerjaan', 'status_kerja'])) {
                                        $rawDisplayVal = $rec->status_bekerja ?? ($rec->extra_attributes['status_bekerja'] ?? ($rec->extra_attributes['pekerjaan'] ?? '-'));
                                    } elseif (in_array($targetClean, ['jenis_kelamin', 'gender', 'jk'])) {
                                        $rawDisplayVal = $rec->jenis_kelamin ?? ($rec->extra_attributes['jenis_kelamin'] ?? ($rec->extra_attributes['gender'] ?? ($rec->extra_attributes['jk'] ?? '-')));
                                    } else {
                                        $rawDisplayVal = '-';
                                    }
                                }

                                if (is_numeric($rawDisplayVal)) {
                                    if (preg_match('/gaji|pendapatan|take_home_pay|upah|penghasilan|omzet|salary|income/i', $kpiTargetVar)) {
                                        $displayTopVal = 'Rp ' . number_format((float)$rawDisplayVal, 0, ',', '.');
                                    } elseif (preg_match('/usia|umur|age/i', $kpiTargetVar)) {
                                        $displayTopVal = number_format((float)$rawDisplayVal, 0, ',', '.') . ' Tahun';
                                    } else {
                                        $displayTopVal = number_format((float)$rawDisplayVal, (floor((float)$rawDisplayVal) == (float)$rawDisplayVal ? 0 : 2), ',', '.');
                                    }
                                } else {
                                    $displayTopVal = (string)$rawDisplayVal;
                                }

                                $percentage = 100 - ($index * 15);
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-medium text-slate-700">
                                    <span class="truncate font-bold text-slate-900">
                                        <span x-show="isMasked">{{ $index + 1 }}. {{ ($rec->masked_nama ?? $rec->nama ?? null) ?: ('Subjek #' . ($rec->id ?? $index + 1)) }} (NIK: {{ $rec->masked_nik ?? '-' }})</span>
                                        <span x-show="!isMasked" style="display:none;">{{ $index + 1 }}. {{ ($rec->nama ?? null) ?: ('Subjek #' . ($rec->id ?? $index + 1)) }} (NIK: {{ $rec->nomor_induk_kependudukan ?? '-' }})</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-100 text-emerald-900 font-extrabold text-[11px] shadow-sm">{{ $displayTopVal }}</span>
                                </div>
                                <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ max(20, $percentage) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada data untuk ditampilkan.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Bottom 5 Chart -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="font-bold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                            <i class="fi fi-rr-caret-down"></i> <span>5 Peringkat Terbawah</span> ({{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }})
                        </h4>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded">5 TERBAWAH</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($bottom5Records as $index => $rec)
                            @php
                                $rawDisplayVal = null;
                                $targetClean = strtolower(str_replace(' ', '_', trim($kpiTargetVar)));

                                if (isset($rec->val) && $rec->val !== '' && $rec->val !== null) {
                                    $rawDisplayVal = $rec->val;
                                } elseif ($targetClean === 'desil_nasional' || $targetClean === 'desil') {
                                    $rawDisplayVal = $rec->keluarga ? 'Desil ' . $rec->keluarga->desil_nasional : ($rec->extra_attributes['desil_nasional'] ?? ($rec->extra_attributes['desil'] ?? null));
                                    if (is_numeric($rawDisplayVal)) {
                                        $rawDisplayVal = 'Desil ' . $rawDisplayVal;
                                    }
                                } elseif (isset($rec->$kpiTargetVar) && $rec->$kpiTargetVar !== '' && $rec->$kpiTargetVar !== null) {
                                    $rawDisplayVal = $rec->$kpiTargetVar;
                                } elseif (!empty($rec->extra_attributes[$kpiTargetVar])) {
                                    $rawDisplayVal = $rec->extra_attributes[$kpiTargetVar];
                                } elseif (!empty($rec->extra_attributes) && is_array($rec->extra_attributes)) {
                                    foreach ($rec->extra_attributes as $ekey => $eval) {
                                        $ekeyClean = strtolower(str_replace(' ', '_', trim($ekey)));
                                        if ($ekeyClean === $targetClean || str_contains($ekeyClean, $targetClean) || str_contains($targetClean, $ekeyClean)) {
                                            $rawDisplayVal = $eval;
                                            break;
                                        }
                                    }
                                }

                                if (empty($rawDisplayVal)) {
                                    if (in_array($targetClean, ['pendidikan', 'ijazah_tertinggi_yang_dimiliki', 'jenjang_tertinggi_yang_diduduki', 'pendidikan_terakhir'])) {
                                        $rawDisplayVal = $rec->ijazah_tertinggi_yang_dimiliki 
                                            ?? ($rec->jenjang_tertinggi_yang_diduduki 
                                            ?? ($rec->extra_attributes['ijazah_tertinggi_yang_dimiliki'] 
                                            ?? ($rec->extra_attributes['pendidikan'] 
                                            ?? ($rec->extra_attributes['pendidikan_terakhir'] 
                                            ?? ($rec->extra_attributes['tingkat_pendidikan'] ?? '-')))));
                                    } elseif (in_array($targetClean, ['status_bekerja', 'pekerjaan', 'status_kerja'])) {
                                        $rawDisplayVal = $rec->status_bekerja ?? ($rec->extra_attributes['status_bekerja'] ?? ($rec->extra_attributes['pekerjaan'] ?? '-'));
                                    } elseif (in_array($targetClean, ['jenis_kelamin', 'gender', 'jk'])) {
                                        $rawDisplayVal = $rec->jenis_kelamin ?? ($rec->extra_attributes['jenis_kelamin'] ?? ($rec->extra_attributes['gender'] ?? ($rec->extra_attributes['jk'] ?? '-')));
                                    } else {
                                        $rawDisplayVal = '-';
                                    }
                                }

                                if (is_numeric($rawDisplayVal)) {
                                    if (preg_match('/gaji|pendapatan|take_home_pay|upah|penghasilan|omzet|salary|income/i', $kpiTargetVar)) {
                                        $displayBotVal = 'Rp ' . number_format((float)$rawDisplayVal, 0, ',', '.');
                                    } elseif (preg_match('/usia|umur|age/i', $kpiTargetVar)) {
                                        $displayBotVal = number_format((float)$rawDisplayVal, 0, ',', '.') . ' Tahun';
                                    } else {
                                        $displayBotVal = number_format((float)$rawDisplayVal, (floor((float)$rawDisplayVal) == (float)$rawDisplayVal ? 0 : 2), ',', '.');
                                    }
                                } else {
                                    $displayBotVal = (string)$rawDisplayVal;
                                }

                                $percentage = 20 + ($index * 15);
                            @endphp
                            <div class="space-y-1">
                                <div class="flex items-center justify-between text-xs font-medium text-slate-700">
                                    <span class="truncate font-bold text-slate-900">
                                        <span x-show="isMasked">{{ $index + 1 }}. {{ ($rec->masked_nama ?? $rec->nama ?? null) ?: ('Subjek #' . ($rec->id ?? $index + 1)) }} (NIK: {{ $rec->masked_nik ?? '-' }})</span>
                                        <span x-show="!isMasked" style="display:none;">{{ $index + 1 }}. {{ ($rec->nama ?? null) ?: ('Subjek #' . ($rec->id ?? $index + 1)) }} (NIK: {{ $rec->nomor_induk_kependudukan ?? '-' }})</span>
                                    </span>
                                    <span class="px-2 py-0.5 rounded-lg bg-amber-100 text-amber-900 font-extrabold text-[11px] shadow-sm">{{ $displayBotVal }}</span>
                                </div>
                                <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full transition-all" style="width: {{ min(100, $percentage) }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 italic">Belum ada data untuk ditampilkan.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD AUDIT DATA QUALITY & DETAIL LOKASI ERROR -->
        @if($issueRecords->count() > 0)
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold"><i class="fi fi-rr-triangle-warning"></i></span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm">Audit Temuan Data Bermasalah / Error</h3>
                            <p class="text-xs text-slate-500">Temuan kesalahan baris data yang memerlukan perbaikan (NIK/KK Cacat, Missing Value, dll.)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dtsen.export-errors') }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                            <i class="fi fi-rr-download"></i> <span>Unduh Laporan Error (.csv)</span>
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    @foreach($issueRecords as $issueRec)
                        <div class="p-4 rounded-xl border {{ $issueRec->quality_status === 'Critical' ? 'bg-rose-50/40 border-rose-200' : 'bg-amber-50/40 border-amber-200' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($issueRec->quality_status === 'Critical')
                                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-bold text-[10px] flex items-center gap-1"><i class="fi fi-rr-cross-circle"></i> CRITICAL</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-amber-500 text-white font-bold text-[10px] flex items-center gap-1"><i class="fi fi-rr-triangle-warning"></i> WARNING</span>
                                    @endif
                                    <strong class="text-slate-900 text-xs">
                                        <span x-show="isMasked">{{ $issueRec->masked_nama ?? ($issueRec->nama ?? 'Subjek') }}</span>
                                        <span x-show="!isMasked" style="display:none;">{{ $issueRec->nama ?? 'Subjek' }}</span>
                                    </strong>
                                </div>
                                <button @click="openPreview({{ $issueRec->id ?? 0 }})" class="px-2.5 py-1 bg-white hover:bg-slate-100 border text-slate-700 font-bold text-[11px] rounded-lg shadow-sm">
                                    <i class="fi fi-rr-eye"></i> Inspeksi Baris Data
                                </button>
                            </div>

                            <div class="text-xs space-y-1">
                                <div class="text-slate-600 flex items-center gap-3 font-mono text-[11px]">
                                    <span>NIK: <strong class="text-slate-900">
                                        <span x-show="isMasked">{{ $issueRec->masked_nik ?? '****************' }}</span>
                                        <span x-show="!isMasked" style="display:none;">{{ $issueRec->nomor_induk_kependudukan ?? '-' }}</span>
                                    </strong></span>
                                    <span>KK: <strong class="text-slate-900">{{ $issueRec->nomor_kartu_keluarga ?? '-' }}</strong></span>
                                </div>
                                
                                <div class="bg-white p-2.5 rounded-lg border border-slate-200 space-y-0.5">
                                    <p class="font-bold text-slate-700 text-[11px]">Rincian Temuan Masalah:</p>
                                    <ul class="list-disc pl-4 text-[11px] {{ $issueRec->quality_status === 'Critical' ? 'text-rose-700' : 'text-amber-800' }} space-y-0.5">
                                        @if(!empty($issueRec->quality_issues))
                                            @foreach($issueRec->quality_issues as $iss)
                                                <li>{{ $iss }}</li>
                                            @endforeach
                                        @else
                                            <li>Memerlukan verifikasi kelengkapan data.</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- DYNAMIC ADAPTIVE DATA TABLE WITH PIVOT AUTOFILTERS & COLUMN VISIBILITY MANAGER -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5" id="tabel-data">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fi fi-rr-table"></i> <span>Tabel Data Terfilter</span> ({{ number_format($records->total()) }} Baris Data)
                    </h3>
                    <p class="text-xs text-slate-500">Gunakan Search Engine di bawah ini atau filter pivot header pada kolom tabel.</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Tombol Atur Kolom Tabel (Add & Delete Column Visibilities) -->
                    <button type="button" @click="showColumnModal = true" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fi fi-rr-settings-sliders"></i> <span>Atur Kolom Tabel</span> (<span x-text="visibleCols.length"></span>/{{ count($activeColumnsMap) }})
                    </button>
                    <!-- Tombol Reset Filter Data -->
                    <a href="{{ route('dtsen.index') }}#tabel-data" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <i class="fi fi-rr-refresh"></i> <span>Reset Filter</span>
                    </a>
                    <span class="text-xs font-bold text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200">Halaman {{ $records->currentPage() }} dari {{ $records->lastPage() }}</span>
                </div>
            </div>

            <!-- SEARCH ENGINE DATA TABLE (Pencarian Cepat NIK, KK, Nama, Alamat, Wilayah) -->
            <form method="GET" action="{{ route('dtsen.index') }}#tabel-data" id="dataTableSearchForm" class="flex items-center gap-2">
                <div class="relative w-full">
                    <label for="dtsen_table_search" class="sr-only">Cari data tabel</label>
                    <input type="text" id="dtsen_table_search" name="search" value="{{ $search }}" autocomplete="off" aria-label="Cari data NIK, KK, Nama, Alamat, Wilayah" placeholder="Cari data?..." class="w-full pl-4 pr-16 py-2.5 bg-slate-50 border border-slate-300 text-slate-900 font-bold text-xs rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all shadow-xs">
                    @if(!empty($search))
                        <a href="{{ route('dtsen.index') }}#tabel-data" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-700 font-extrabold text-xs"><i class="fi fi-rr-cross-small"></i> Clear</a>
                    @endif
                </div>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <i class="fi fi-rr-search"></i> <span>Cari Data</span>
                </button>
            </form>

            <!-- MODAL POPUP ATUR KOLOM TABEL (COLUMN VISIBILITY MANAGER) -->
            <div x-show="showColumnModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs p-3 sm:p-6 flex items-center justify-center">
                <div @click.away="showColumnModal = false" class="bg-white rounded-2xl max-w-4xl w-full p-5 sm:p-6 space-y-4 shadow-2xl border border-slate-200 my-auto flex flex-col" style="max-height: 88vh;">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <i class="fi fi-rr-settings-sliders"></i> <span>Pengatur Tampilan Kolom Tabel (Terdeteksi Berkas Dataset CSV)</span>
                            </h3>
                            <p class="text-xs text-slate-500">Centang atau hilangkan centang untuk memilih dari {{ count($activeColumnsMap) }} variabel terdeteksi yang tampil pada tabel data.</p>
                        </div>
                        <button type="button" @click="showColumnModal = false" class="text-slate-400 hover:text-slate-700 font-extrabold text-lg p-1 cursor-pointer">&times;</button>
                    </div>

                    <div class="space-y-4 overflow-y-auto pr-2 flex-1" style="max-height: 60vh;">
                        <!-- Group 1: Set Data Anggota Keluarga (Individu) -->
                        @if(!empty($officialIndividuVars))
                            <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-blue-900 flex items-center gap-1.5 border-b border-blue-200 pb-1.5">
                                    <i class="fi fi-rr-user"></i> Set Data Anggota Keluarga / Individu ({{ count($officialIndividuVars) }} Variabel Terdeteksi)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($officialIndividuVars as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_idx_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
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
                                    <i class="fi fi-rr-home"></i> Set Data Keluarga ({{ count($officialKeluargaVars) }} Variabel Terdeteksi)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($officialKeluargaVars as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-emerald-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_idx_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Group 3: Variabel Berkas CSV / Custom -->
                        @php
                            $extraCols = array_diff_key($activeColumnsMap, $officialIndividuVars, $officialKeluargaVars);
                        @endphp
                        @if(!empty($extraCols))
                            <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-purple-900 flex items-center gap-1.5 border-b border-purple-200 pb-1.5">
                                    <i class="fi fi-rr-thumbtack"></i> Variabel Berkas CSV / Custom ({{ count($extraCols) }} Variabel)
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($extraCols as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-purple-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" id="toggle_idx_col_{{ $cKey }}" name="toggle_col_{{ $cKey }}" aria-label="Tampilkan kolom {{ $cTitle }}" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 flex items-center justify-between border-t border-slate-100 shrink-0">
                        <button type="button" @click="resetCols()" class="text-xs font-black hover:opacity-80 transition-opacity cursor-pointer" style="color: #2563eb !important;">
                            <i class="fi fi-rr-refresh"></i> Tampilkan Semua Kolom
                        </button>
                        <button type="button" @click="showColumnModal = false" class="px-6 py-2.5 rounded-xl font-black text-xs shadow-md transition-all cursor-pointer" style="background-color: #0f172a; color: #ffffff !important;">
                            Selesai & Simpan
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dynamic Data Table Adaptif dengan Filter Pivot AutoFilter Excel (Sticky Header & Min-Height Fix) -->
            <div class="overflow-x-auto overflow-y-auto max-h-[70vh] min-h-[420px] border border-slate-200 rounded-xl relative shadow-xs">
                <table class="w-full text-left border-collapse min-w-max">
                    <thead class="sticky top-0 z-20 bg-slate-100 shadow-xs">
                        <tr class="bg-slate-100 border-b border-slate-200">
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100">No</th>
                            <!-- QC Status Header dengan Trigger Filter Modal Terpusat -->
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100" style="min-width: 140px; border-left: 1px solid #cbd5e1;">
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

                            <!-- Header Kolom Dinamis dengan Trigger Filter Modal Pivot Excel Terpusat -->
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
                                <th x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs font-bold text-slate-700 tracking-wider whitespace-nowrap relative group bg-slate-100" style="min-width: 175px; padding: 12px 16px; text-align: left; border-left: 1px solid #cbd5e1;">
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

                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider text-center bg-slate-100" style="min-width: 90px; border-left: 1px solid #cbd5e1;">Aksi</th>
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

                                <!-- Dynamic Cell Output dengan Format Pemisah Ribuan Titik (.) Otomatis -->
                                @foreach($activeColumnsMap as $colKey => $colTitle)
                                    <td x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs text-slate-800 whitespace-nowrap">
                                        @if($colKey === 'nomor_induk_kependudukan' || $colKey === 'nik')
                                            <span class="font-mono font-bold text-slate-900">
                                                <span x-show="isMasked">{{ $row->masked_nik ?? '****************' }}</span>
                                                <span x-show="!isMasked" style="display:none;">{{ $row->$colKey ?? '-' }}</span>
                                            </span>
                                        @elseif($colKey === 'nomor_kartu_keluarga' || $colKey === 'no_kk')
                                            <span class="font-mono text-slate-800">
                                                <span x-show="isMasked">{{ $row->masked_kk ?? '****************' }}</span>
                                                <span x-show="!isMasked" style="display:none;">{{ $row->$colKey ?? '-' }}</span>
                                            </span>
                                        @elseif($colKey === 'nama' || $colKey === 'nama_lengkap')
                                            <span class="font-bold text-slate-900">
                                                <span x-show="isMasked">{{ $row->masked_nama ?? '***' }}</span>
                                                <span x-show="!isMasked" style="display:none;">{{ $row->$colKey ?? '-' }}</span>
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
                                    <button @click="openPreview({{ $row->id }})" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold text-[11px] rounded-lg shadow-sm">
                                        <i class="fi fi-rr-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeColumnsMap) + 3 }}" class="px-4 py-16 text-center">
                                    <div class="space-y-3 max-w-md mx-auto py-6">
                                        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-bold mx-auto shadow-xs"><i class="fi fi-rr-search"></i></div>
                                        <div class="space-y-1">
                                            <h4 class="font-extrabold text-sm text-slate-800">Tidak ada data yang sesuai dengan kriteria filter</h4>
                                            <p class="text-xs text-slate-500 font-medium">Coba sesuaikan kata kunci pencarian atau bersihkan filter pivot header kolom.</p>
                                        </div>
                                        <div class="pt-2">
                                            <a href="{{ request()->fullUrlWithQuery(array_merge(['search' => null, 'quality_status' => null, 'filter_col' => null, 'filter_val' => null, 'page' => null], array_fill_keys(array_keys($activeColumnsMap), null))) }}#tabel-data" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
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

            <!-- Pagination Bar -->
            <div class="flex items-center justify-between pt-2">
                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan <strong>{{ $records->firstItem() ?: 0 }}</strong> s.d. <strong>{{ $records->lastItem() ?: 0 }}</strong> dari <strong>{{ $records->total() }}</strong> total data
                </div>
                <div>
                    {{ $records->links() }}
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL PREVIEW DETAIL ROW RECORD -->
    <div x-show="showPreviewModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" 
         style="display: none;">
        <div @click.away="showPreviewModal = false" class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold"><i class="fi fi-rr-document"></i></span>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Inspeksi Rincian Baris Data Subjek</h3>
                        <p class="text-[11px] text-slate-500">Detail data individu & relasi aset rumah tangga pengampu</p>
                    </div>
                </div>
                <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base flex items-center"><i class="fi fi-rr-cross"></i></button>
            </div>

            <template x-if="previewData">
                <div class="space-y-4 text-xs">
                    <!-- Status QC Banner -->
                    <div class="p-3 rounded-xl border flex items-center justify-between"
                         :class="previewData.quality_status === 'Valid' ? 'bg-emerald-50 border-emerald-200 text-emerald-900' : (previewData.quality_status === 'Warning' ? 'bg-amber-50 border-amber-200 text-amber-900' : 'bg-rose-50 border-rose-200 text-rose-900')">
                        <div class="flex items-center gap-2">
                            <span class="font-bold">Hasil Data Quality Test:</span>
                            <span class="px-2.5 py-0.5 rounded-full font-extrabold text-[11px]"
                                  :class="previewData.quality_status === 'Valid' ? 'bg-emerald-600 text-white' : (previewData.quality_status === 'Warning' ? 'bg-amber-500 text-white' : 'bg-rose-600 text-white')"
                                  x-text="previewData.quality_status"></span>
                        </div>
                    </div>

                    <!-- Issues List -->
                    <template x-if="previewData.quality_issues && previewData.quality_issues.length > 0">
                        <div class="p-4 bg-rose-50 rounded-xl border border-rose-200 space-y-2">
                            <div class="flex items-center justify-between">
                                <strong class="text-rose-900 font-extrabold text-xs flex items-center gap-1.5">
                                    <i class="fi fi-rr-triangle-warning text-rose-600"></i> <span>Rincian Temuan Error Audit Kualitas Data</span>
                                </strong>
                                <span class="px-2.5 py-0.5 rounded-full bg-rose-600 text-white font-extrabold text-[10px]"
                                      x-text="previewData.quality_issues.length + ' EROR TERDETEKSI'"></span>
                            </div>
                            <ol class="list-decimal pl-5 text-rose-900 space-y-1 font-medium text-xs">
                                <template x-for="(iss, idx) in previewData.quality_issues" :key="idx">
                                    <li x-text="iss"></li>
                                </template>
                            </ol>
                        </div>
                    </template>

                    <!-- Profil Subjek -->
                    <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3 rounded-xl border border-slate-200 font-medium">
                        <div>
                            <span class="text-slate-500 block text-[11px]">Nama Lengkap:</span>
                            <strong class="text-slate-900 text-sm" x-text="isMasked ? previewData.masked_nama : previewData.nama"></strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">NIK:</span>
                            <strong class="text-slate-900 font-mono text-sm" x-text="isMasked ? previewData.masked_nik : previewData.nik"></strong>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Nomor KK:</span>
                            <span class="text-slate-900 font-mono" x-text="previewData.nomor_kartu_keluarga"></span>
                        </div>
                        <div>
                            <span class="text-slate-500 block text-[11px]">Jenis Kelamin:</span>
                            <span class="text-slate-900" x-text="previewData.jenis_kelamin"></span>
                        </div>
                    </div>

                    <!-- Relasi KK Aset -->
                    <template x-if="previewData.keluarga">
                        <div class="space-y-2 pt-2 border-t border-slate-200">
                            <h4 class="font-extrabold text-slate-800 text-xs flex items-center gap-1.5">
                                <i class="fi fi-rr-home text-blue-600"></i> <span>Variabel Pengampu Rumah Tangga (KK)</span>
                            </h4>
                            <div class="grid grid-cols-2 gap-2 bg-blue-50/50 p-3 rounded-xl border border-blue-200">
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Desil Kesejahteraan:</span>
                                    <strong class="text-blue-900 font-bold" x-text="'Desil ' + previewData.keluarga.desil_nasional"></strong>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Jenis Lantai Terluas:</span>
                                    <span class="text-slate-800" x-text="previewData.keluarga.jenis_lantai"></span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Jenis Atap Terluas:</span>
                                    <span class="text-slate-800" x-text="previewData.keluarga.jenis_atap"></span>
                                </div>
                                <div>
                                    <span class="text-slate-500 block text-[11px]">Wilayah Domisili:</span>
                                    <span class="text-slate-800" x-text="previewData.keluarga.provinsi + ' / ' + previewData.keluarga.kabupaten_kota"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

            <div class="pt-3 flex justify-end border-t border-slate-100">
                <button type="button" @click="showPreviewModal = false" class="px-6 py-2.5 rounded-xl font-black text-xs shadow-md transition-all cursor-pointer" style="background-color: #0f172a; color: #ffffff !important;">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL POPUP FILTER PIVOT EXCEL (Terpusat, Anti-Offscreen, & Pinned Footer) -->
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

                <!-- Distinct Values Checkbox List (Strictly Contained with 220px max-height scroll) -->
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

    <!-- Modals are globally handled in layouts/modals.blade.php -->
</div>

<script>
function dtsenApp() {
    const allColKeys = @json(array_keys($activeColumnsMap));
    let savedCols = null;
    try {
        savedCols = JSON.parse(localStorage.getItem('padu_visible_columns'));
    } catch(e) {}

    let validSavedCols = (savedCols && Array.isArray(savedCols)) 
        ? savedCols.filter(c => allColKeys.includes(c)) 
        : [];

    return {
        isMasked: false,
        showPreviewModal: false,
        showColumnModal: false,
        previewData: null,
        visibleCols: validSavedCols.length > 0 ? validSavedCols : [...allColKeys],

        // State Filter Kolom Pivot Modal (Terpusat, Anti-Offscreen, & Pinned Footer)
        showFilterModal: false,
        activeFilterCol: null,
        activeFilterTitle: '',
        activeFilterType: 'general',
        filterSearchVal: '',
        filterMinVal: '',
        filterMaxVal: '',
        filterSelectedVals: [],
        filterDistinctValues: [],

        openColumnFilter(colKey, colTitle, filterType = 'general', distinctVals = [], currentVals = []) {
            this.activeFilterCol = colKey;
            this.activeFilterTitle = colTitle;
            this.activeFilterType = filterType;
            this.filterDistinctValues = Array.isArray(distinctVals) ? distinctVals : [];
            this.filterSelectedVals = Array.isArray(currentVals) ? [...currentVals] : [];
            this.filterSearchVal = (this.filterSelectedVals.length === 1) ? this.filterSelectedVals[0] : '';
            this.filterMinVal = '';
            this.filterMaxVal = '';
            this.showFilterModal = true;
        },

        closeColumnFilter() {
            this.showFilterModal = false;
            this.activeFilterCol = null;
        },

        hasFilterVal(val) {
            if (val === null || val === undefined) return false;
            const target = String(val).trim().toLowerCase();
            return this.filterSelectedVals.some(v => String(v).trim().toLowerCase() === target);
        },

        toggleFilterVal(val) {
            if (this.hasFilterVal(val)) {
                const target = String(val).trim().toLowerCase();
                this.filterSelectedVals = this.filterSelectedVals.filter(v => String(v).trim().toLowerCase() !== target);
            } else {
                this.filterSelectedVals.push(String(val).trim());
            }
        },

        selectAllFilterVals() {
            if (this.filterSelectedVals.length >= this.filterDistinctValues.length) {
                this.filterSelectedVals = [];
            } else {
                this.filterSelectedVals = this.filterDistinctValues.map(v => String(v).trim());
            }
        },

        setSingleFilterVal(val) {
            this.filterSelectedVals = [String(val).trim()];
            this.applyActiveFilter();
        },

        applyFilterRange() {
            let min = parseInt(this.filterMinVal);
            let max = parseInt(this.filterMaxVal);
            let rangeStr = '';
            if (!isNaN(min) && !isNaN(max)) {
                rangeStr = min + ' - ' + max;
            } else if (!isNaN(min)) {
                rangeStr = '> ' + (min - 1);
            } else if (!isNaN(max)) {
                rangeStr = '< ' + (max + 1);
            }
            if (rangeStr) {
                this.filterSelectedVals = [rangeStr];
                this.applyActiveFilter();
            }
        },

        applyActiveFilter() {
            if (!this.activeFilterCol) return;
            const textQuery = this.filterSearchVal.trim();
            if (textQuery !== '') {
                if (this.filterSelectedVals.length === 0) {
                    this.filterSelectedVals = [textQuery];
                } else if (!this.filterSelectedVals.some(v => String(v).toLowerCase() === textQuery.toLowerCase())) {
                    this.filterSelectedVals.push(textQuery);
                }
            }
            const url = new URL(window.location.href);
            if (this.filterSelectedVals.length > 0) {
                url.searchParams.set(this.activeFilterCol, this.filterSelectedVals.join(','));
            } else {
                url.searchParams.delete(this.activeFilterCol);
            }
            url.searchParams.set('page', '1');
            url.hash = 'tabel-data';
            this.showFilterModal = false;
            window.location.href = url.toString();
        },

        clearActiveFilter() {
            if (!this.activeFilterCol) return;
            const url = new URL(window.location.href);
            url.searchParams.delete(this.activeFilterCol);
            
            const synonyms = {
                'jenis_kelamin': ['gender', 'jk', 'sex'],
                'usia': ['umur', 'age'],
                'gaji_bulanan': ['gaji', 'pendapatan', 'income', 'salary', 'penghasilan'],
                'desil_nasional': ['desil', 'desil_kesejahteraan'],
                'status_bekerja': ['pekerjaan', 'status_kerja'],
                'nomor_induk_kependudukan': ['nik', 'no_nik'],
                'nomor_kartu_keluarga': ['kk', 'no_kk'],
                'pendidikan': ['pendidikan_terakhir', 'ijazah_tertinggi_yang_dimiliki'],
                'status_kawin': ['status_pernikahan'],
                'quality_status': ['quality_status']
            };
            if (synonyms[this.activeFilterCol]) {
                synonyms[this.activeFilterCol].forEach(s => url.searchParams.delete(s));
            }

            url.searchParams.set('page', '1');
            url.hash = 'tabel-data';
            this.showFilterModal = false;
            window.location.href = url.toString();
        },

        formatEta(sec) {
            if (!sec || sec <= 0) return '5 detik';
            if (sec >= 60) {
                const m = Math.floor(sec / 60);
                const s = sec % 60;
                return m + ' m ' + (s < 10 ? '0' : '') + s + ' s';
            }
            return sec + ' detik';
        },

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
            localStorage.setItem('padu_visible_columns', JSON.stringify(this.visibleCols));
        },

        resetCols() {
            this.visibleCols = [...allColKeys];
            localStorage.setItem('padu_visible_columns', JSON.stringify(this.visibleCols));
        },

        isUploading: false,
        uploadProgress: 0,
        uploadMessage: '',
        localPathInput: 'sample_1_juta_data.csv',

        showImportProgressModal: false,
        importProgressPercent: 0,
        importProgressMessage: 'Menyiapkan pemrosesan berkas data...',
        importEtaSeconds: 0,
        progressPollTimer: null,

        async startLocalImportSubmit(e) {
            if (e) e.preventDefault();
            this.isProcessing = true;
            this.showImportProgressModal = true;
            this.importProgressPercent = 5;
            this.importProgressMessage = 'Membaca data dari berkas CSV...';
            this.importEtaSeconds = 25;

            if (this.progressPollTimer) clearInterval(this.progressPollTimer);

            let startTime = Date.now();
            let totalEstSec = 25;
            let isPollingActive = false;
            let hasServerProgress = false;

            this.progressPollTimer = setInterval(() => {
                let elapsedSec = (Date.now() - startTime) / 1000;

                if (!hasServerProgress) {
                    let ratio = Math.min(elapsedSec / totalEstSec, 0.95);
                    let calcPct = Math.min(95, Math.round(5 + (ratio * 90)));
                    if (calcPct > this.importProgressPercent) {
                        this.importProgressPercent = calcPct;
                    }
                    let calcEta = Math.max(1, Math.round(totalEstSec - elapsedSec));
                    this.importEtaSeconds = calcEta;

                    if (this.importProgressPercent < 25) {
                        this.importProgressMessage = 'Membaca data dari berkas CSV...';
                    } else if (this.importProgressPercent < 50) {
                        this.importProgressMessage = 'Memvalidasi NIK, KK, & kualitas data...';
                    } else if (this.importProgressPercent < 75) {
                        this.importProgressMessage = 'Menyiapkan & mengurutkan data...';
                    } else if (this.importProgressPercent < 92) {
                        this.importProgressMessage = 'Memasukkan data ke dalam database...';
                    } else {
                        this.importProgressMessage = 'Membangun indeks B-Tree pencarian database...';
                    }
                }

                if (!isPollingActive) {
                    isPollingActive = true;
                    fetch('/dtsen/import-progress', { headers: { 'Accept': 'application/json' } })
                        .then(res => res.ok ? res.json() : null)
                        .then(data => {
                            if (data && data.percent > 0) {
                                hasServerProgress = true;
                                if (data.percent >= this.importProgressPercent || data.percent === 100) {
                                    this.importProgressPercent = data.percent;
                                }
                                if (data.message) {
                                    this.importProgressMessage = data.message;
                                }
                                if (typeof data.eta_seconds !== 'undefined') {
                                    this.importEtaSeconds = data.eta_seconds;
                                }
                            }
                        })
                        .catch(() => {})
                        .finally(() => { isPollingActive = false; });
                }
            }, 300);

            try {
                const formData = new FormData(e.target);
                const response = await fetch('/dtsen/import-local', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const resText = await response.text();
                let resData;
                try {
                    resData = JSON.parse(resText);
                } catch (e) {
                    const cleanErrText = resText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
                    throw new Error('Respon server (Status ' + response.status + '): ' + cleanErrText.substring(0, 200));
                }
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);

                if (response.ok && resData.success) {
                    this.importProgressPercent = 100;
                    this.importProgressMessage = resData.message || 'Pemrosesan data selesai!';
                    this.importEtaSeconds = 0;
                    setTimeout(() => {
                        window.location.reload();
                    }, 600);
                } else {
                    alert('Gagal impor: ' + (resData.message || 'Error tidak diketahui'));
                    this.showImportProgressModal = false;
                    this.isProcessing = false;
                }
            } catch (err) {
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                alert('Gagal memproses impor: ' + err.message);
                this.showImportProgressModal = false;
                this.isProcessing = false;
            }
        },

        async handleChunkedUpload(e) {
            const file = e.target.files[0];
            if (!file) return;

            this.isUploading = true;
            this.uploadProgress = 0;
            this.uploadMessage = 'Memulai persiapan upload berkas data (' + (file.size / 1024 / 1024).toFixed(1) + ' MB)...';

            const chunkSize = 5 * 1024 * 1024; // 5MB Chunks
            const totalChunks = Math.ceil(file.size / chunkSize);
            const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substring(2, 9);
            const csrfToken = '{{ csrf_token() }}';

            try {
                for (let chunkIndex = 0; chunkIndex < totalChunks; chunkIndex++) {
                    const start = chunkIndex * chunkSize;
                    const end = Math.min(start + chunkSize, file.size);
                    const chunkBlob = file.slice(start, end);

                    const formData = new FormData();
                    formData.append('file_id', fileId);
                    formData.append('chunk_index', chunkIndex);
                    formData.append('total_chunks', totalChunks);
                    formData.append('file_name', file.name);
                    formData.append('file_size', file.size);
                    formData.append('chunk', chunkBlob, file.name);
                    formData.append('_token', csrfToken);

                    const response = await fetch('/dtsen/import-chunk', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const resText = await response.text();
                    let resData;
                    try {
                        resData = JSON.parse(resText);
                    } catch (e) {
                        const cleanErrText = resText.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').trim();
                        throw new Error('Respon server (Status ' + response.status + '): ' + cleanErrText.substring(0, 200));
                    }

                    if (!response.ok || !resData.success) {
                        throw new Error(resData.message || 'Gagal mengunggah chunk ' + (chunkIndex + 1));
                    }

                    this.uploadProgress = Math.round(((chunkIndex + 1) / totalChunks) * 100);
                    if (resData.is_complete) {
                        this.uploadMessage = resData.message;
                        setTimeout(() => window.location.reload(), 1000);
                        return;
                    } else {
                        this.uploadMessage = 'Mengirim berkas skala besar (' + this.uploadProgress + '% | Chunk ' + (chunkIndex + 1) + '/' + totalChunks + ')...';
                    }
                }
            } catch (err) {
                alert('Gagal mengunggah berkas: ' + err.message);
                this.isUploading = false;
            }
        },

        showLogModal: false,
        logList: [],
        logFilter: 'ALL',
        logPollTimer: null,
        logCount: 0,

        get filteredLogs() {
            if (this.logFilter === 'ALL') return this.logList;
            return this.logList.filter(l => l.level === this.logFilter);
        },

        openLogConsole() {
            window.location.href = "{{ route('dtsen.activity-logs') }}";
        },

        async fetchLogs() {
            try {
                const res = await fetch('/dtsen/logs', { headers: { 'Accept': 'application/json' } });
                if (res.ok) {
                    const data = await res.json();
                    if (data && data.logs) {
                        this.logList = data.logs;
                        this.logCount = data.total || 0;
                    }
                }
            } catch (e) {}
        },

        async clearSystemLogs() {
            if (!confirm('Apakah Anda yakin ingin mengosongkan berkas log aktivitas sistem?')) return;
            try {
                const res = await fetch('/dtsen/logs/clear', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                if (res.ok) {
                    this.fetchLogs();
                }
            } catch (e) {}
        },

        copySystemLogs() {
            const text = this.filteredLogs.map(l => `[${l.timestamp}] [${l.level}] ${l.message}`).join('\n');
            navigator.clipboard.writeText(text).then(() => {
                alert('Berhasil menyalin log aktivitas ke clipboard!');
            });
        },

        async cancelCurrentImport() {
            if (!confirm('Batalkan proses injeksi dataset ke database?\n\nSemua proses impor yang sedang berjalan akan dihentikan dan memori temporary akan dibersihkan.')) {
                return;
            }

            try {
                const res = await fetch('/dtsen/cancel-import', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await res.json();
                if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                this.showImportProgressModal = false;
                this.isProcessing = false;

                alert(data.message || 'Proses injeksi data berhasil dibatalkan.');
                this.fetchLogs();
                setTimeout(() => {
                    window.location.reload();
                }, 500);
            } catch (err) {
                alert('Gagal membatalkan impor: ' + err.message);
            }
        },

        openPreview(id) {
            fetch('/dtsen/preview/' + id)
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.previewData = data.data;
                        this.showPreviewModal = true;
                    }
                });
        }
    }
}

let isFilterNavigating = false;

window.applyAjaxFilter = function(targetUrl, targetHash = 'tabel-data', shouldScroll = false) {
    if (isFilterNavigating) return;
    
    if (targetHash && !targetUrl.includes('#')) {
        targetUrl += '#' + targetHash;
    }

    const currentClean = window.location.href;
    if (currentClean === targetUrl) {
        return;
    }

    isFilterNavigating = true;
    window.location.href = targetUrl;
};

let filterDebounceTimer = null;

function submitFilterFormAjax(form, shouldScroll = false) {
    if (!form) return;
    const formData = new FormData(form);
    
    const currentUrl = new URL(window.location.href);
    const urlParams = currentUrl.searchParams;
    
    for (const [key, value] of formData.entries()) {
        if (value === 'semua' || value === '') {
            urlParams.delete(key);
        } else {
            urlParams.set(key, value);
        }
    }
    
    urlParams.set('page', '1');
    const actionUrl = new URL(form.action || window.location.href);
    actionUrl.search = urlParams.toString();
    
    let targetHash = 'tabel-data';
    if (form.id === 'salaryFilterForm') targetHash = 'salary-analytics-section';
    if (form.id === 'kpiVarForm') targetHash = 'kpi-analytics-section';
    
    window.applyAjaxFilter(actionUrl.toString(), targetHash, shouldScroll);
}

document.addEventListener('change', function(e) {
    const filterForm = e.target.closest('#salaryFilterForm, #dataTableSearchForm, #kpiVarForm');
    if (filterForm && (e.target.tagName === 'SELECT' || e.target.type === 'checkbox' || e.target.type === 'radio')) {
        submitFilterFormAjax(filterForm, false);
    }
});

document.addEventListener('input', function(e) {
    const filterForm = e.target.closest('#salaryFilterForm, #dataTableSearchForm');
    if (filterForm && (e.target.tagName === 'INPUT' && (e.target.type === 'text' || e.target.type === 'search'))) {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(() => {
            submitFilterFormAjax(filterForm, false);
        }, 300);
    }
});

document.addEventListener('click', function(e) {
    const link = e.target.closest('#tabel-data a, #salary-analytics-section a, #kpi-analytics-section a, #summary-stats-section a, .pagination a');
    if (link && link.href && !link.href.includes('javascript:') && !link.dataset.noAjax) {
        try {
            const url = new URL(link.href);
            if (url.origin === window.location.origin) {
                e.preventDefault();
                window.applyAjaxFilter(link.href, url.hash.replace('#', '') || 'tabel-data', true);
            }
        } catch (err) {}
    }
});

document.addEventListener('submit', function(e) {
    const form = e.target.closest('#dataTableSearchForm, #salaryFilterForm, #kpiVarForm');
    if (form) {
        e.preventDefault();
        submitFilterFormAjax(form, true);
    }
});
</script>
@endsection
