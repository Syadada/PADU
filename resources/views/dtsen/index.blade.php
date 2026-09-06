@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="dtsenApp()">

    <!-- Header Banner Dashboard -->
    <div class="p-6 bg-white rounded-2xl border border-slate-400 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <h2 class="text-xl font-extrabold text-slate-900 flex items-center gap-2">
                <span class="p-2 rounded-xl bg-blue-50 text-blue-600 text-lg">📊</span> Dashboard Analitik & Quality Test (DTSEN 2026)
            </h2>
            <p class="text-xs text-slate-500 font-medium">
                Pengolahan Data Adaptif File CSV/XLSX, Relasi Otomatis KK-Individu, & Pengecekan Kualitas Standar Validasi Data.
            </p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0" x-data="{ showExportModal: false, showTemplateModal: false, showClearModal: false }">
            @if($totalRows > 0)
                <button @click="showExportModal = true" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>📤</span> Ekspor Data Paket (.ZIP)
                </button>
                <button @click="showClearModal = true" class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>🗑️</span> Kosongkan Data
                </button>
            @endif

            <!-- Dropdown Format Template Data -->
            <div class="relative">
                <button @click="showTemplateModal = !showTemplateModal" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs transition-all flex items-center gap-1.5 border border-slate-200 cursor-pointer">
                    <span>⬇️</span> Template Data ▾
                </button>
                <div x-show="showTemplateModal" 
                     @click.away="showTemplateModal = false" 
                     x-transition 
                     class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-200 z-50 py-1.5 text-xs font-bold text-slate-700 space-y-0.5" 
                     style="display: none;">
                    <a href="{{ route('dtsen.template', ['format' => 'xlsx']) }}" class="block px-4 py-2.5 hover:bg-emerald-50 text-slate-900 flex items-center gap-2 transition-colors">
                        <span>🟢</span> Template Excel (.xlsx)
                    </a>
                    <a href="{{ route('dtsen.template', ['format' => 'csv']) }}" class="block px-4 py-2.5 hover:bg-blue-50 text-slate-900 flex items-center gap-2 transition-colors">
                        <span>📄</span> Template CSV (.csv)
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
                        <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg">🗑️</span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm">Konfirmasi Kosongkan Data</h3>
                            <p class="text-[11px] text-slate-500">Hapus seluruh data di sistem incase salah upload file</p>
                        </div>
                    </div>

                    <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-1">
                        <p><strong>⚠️ Perhatian:</strong> Tindakan ini akan menghapus <strong>{{ number_format($totalRows) }} baris data</strong> yang saat ini ada di sistem. Sistem akan dikosongkan secara total agar Anda bisa mengunggah file yang benar.</p>
                    </div>

                    <form action="{{ route('dtsen.clear') }}" method="POST" class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                        @csrf
                        <button type="button" @click="showClearModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>🗑️</span> Ya, Kosongkan Sekarang
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
                            <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">📤</span>
                            <div>
                                <h3 class="font-extrabold text-slate-900 text-sm">Opsi Ekspor Data DTSEN 2026</h3>
                                <p class="text-[11px] text-slate-500">Pilih mode ekspor dan format berkas yang diinginkan</p>
                            </div>
                        </div>
                        <button type="button" @click="showExportModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base">✕</button>
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
                            <label class="block text-xs font-bold text-slate-800 uppercase">1. Mode Ekspor Data</label>
                            <div class="space-y-2">
                                <label class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" name="mode" value="as_is" checked class="mt-0.5 accent-blue-600">
                                    <div>
                                        <strong class="block text-xs text-slate-900">📄 Mode A: Ekspor As-Is (+ Catatan Error Audit)</strong>
                                        <p class="text-[11px] text-slate-500">Mengekspor seluruh baris data apa adanya ditambah 1 kolom rincian temuan error. Urutan & indeks baris tetap presisi 100%.</p>
                                    </div>
                                </label>
                                <label class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex items-start gap-3 transition-colors">
                                    <input type="radio" name="mode" value="cleaned_only" class="mt-0.5 accent-emerald-600">
                                    <div>
                                        <strong class="block text-xs text-slate-900">🟢 Mode B: Ekspor Cleaned Only (Data Cacat Dibuang)</strong>
                                        <p class="text-[11px] text-slate-500">Mengekspor hanya baris data yang 🟢 Valid. Disertai kolom <code>Original_Row_Index</code> untuk rekonsiliasi ke master file.</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <!-- 2. Format File -->
                        <div class="space-y-1.5 pt-2 border-t border-slate-100">
                            <label class="block text-xs font-bold text-slate-800 uppercase">2. Format Berkas Paket (.ZIP)</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex flex-col items-center justify-center text-center">
                                    <input type="radio" name="format" value="xlsx" checked class="mb-1 accent-emerald-600">
                                    <span class="text-xs font-bold text-slate-900">🟢 Excel Terbaru (.xlsx)</span>
                                </label>
                                <label class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex flex-col items-center justify-center text-center">
                                    <input type="radio" name="format" value="csv" class="mb-1 accent-blue-600">
                                    <span class="text-xs font-bold text-slate-900">📄 CSV File (.csv)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Catatan Penting Paket Dual File & Auto Reset -->
                        <div class="p-3 bg-amber-500 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                            <p><strong>📦 Lokasi Hasil Ekspor:</strong> Paket berkas (.ZIP) akan diekspor & disimpan langsung di folder <code class="text-blue-900 font-mono font-bold">src-export/</code> di root project (bebas memori & anti crash browser).</p>
                            <p><strong>🧹 Auto-Purge Reset:</strong> Setelah ekspor selesai, data sistem dikosongkan agar bersih untuk pemrosesan file berikutnya.</p>
                        </div>

                        <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                            <button type="button" @click="showExportModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200">
                                Batal
                            </button>
                            <button type="submit" @click="showExportModal = false" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                                <span>📥</span> Ekspor Langsung ke Folder src-export/
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
                    <span class="p-1.5 rounded-xl text-emerald-400 font-bold text-sm" style="background-color: rgba(16, 185, 129, 0.2) !important;">📦</span>
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
                                📦 {{ $exFile['name'] }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-[11px] font-mono pt-1 border-t" style="border-color: #334155 !important; color: #94a3b8 !important;">
                            <span class="px-2 py-0.5 rounded font-bold" style="background-color: #020617 !important; color: #f59e0b !important; border: 1px solid #334155 !important;">
                                {{ $exFile['size_formatted'] }}
                            </span>
                            <span style="color: #cbd5e1 !important;">📅 {{ $exFile['modified_at'] }}</span>
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
                    <span class="p-2 rounded-xl text-blue-400 text-lg" style="background-color: rgba(59, 130, 246, 0.2);">⚡</span>
                    <h3 class="text-sm font-black uppercase tracking-wider" style="color: #ffffff !important;">
                        PILIH & PROSES DATA DARI FOLDER <code class="px-2 py-0.5 rounded font-mono text-xs" style="background-color: #1e3a8a !important; color: #93c5fd !important; border: 1px solid #1d4ed8 !important;">src-dtsen/</code>
                    </h3>
                </div>
                <p class="text-xs font-medium" style="color: #cbd5e1 !important;">
                    Pindahkan berkas dataset (CSV / XLSX) ke folder <code class="font-mono" style="color: #fde047 !important;">src-dtsen/</code> di root project. Pilih berkas di bawah ini untuk diproses.
                </p>
            </div>
            
            <a href="{{ route('dtsen.index') }}" class="btn-refresh-folder px-4 py-2 rounded-xl text-white text-xs font-bold transition-all shadow-md flex items-center gap-1.5 shrink-0 self-start md:self-auto cursor-pointer" style="background-color: #0284c7 !important; color: #ffffff !important; border: 1px solid #0369a1 !important;">
                <span>🔄</span> Refresh Folder src-dtsen
            </a>
        </div>

        @if(!empty($srcDtsenFiles) && count($srcDtsenFiles) > 0)
            <form action="{{ route('dtsen.import-local') }}" method="POST" class="space-y-4" x-data="{ selectedFile: '{{ $srcDtsenFiles[0]['name'] }}', isProcessing: false }" @submit="isProcessing = true; startLocalImportSubmit($event)">
                @csrf
                <div class="space-y-2.5">
                    <label class="block text-xs font-bold uppercase tracking-wider" style="color: #cbd5e1 !important;">
                        📁 Berkas Tersedia di Folder <code class="font-mono" style="color: #93c5fd !important;">src-dtsen/</code> ({{ count($srcDtsenFiles) }} Berkas Terdeteksi):
                    </label>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($srcDtsenFiles as $f)
                            <label @click="selectedFile = '{{ $f['name'] }}'" 
                                   :class="selectedFile === '{{ $f['name'] }}' ? 'selected-card' : ''"
                                   class="file-option-card flex items-start gap-3 relative overflow-hidden group"
                                   style="background-color: #1e293b !important; color: #ffffff !important; border: 1px solid #334155 !important;">
                                
                                <input type="radio" name="selected_file" value="{{ $f['name'] }}" x-model="selectedFile" class="mt-1 accent-blue-500">
                                
                                <div class="space-y-1 min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-base">
                                            @if($f['extension'] === 'csv' || $f['extension'] === 'txt') 📄 @elseif($f['extension'] === 'xlsx' || $f['extension'] === 'xls') 🟢 @else 📁 @endif
                                        </span>
                                        <strong class="text-xs font-bold truncate block group-hover:text-blue-300 transition-colors" style="color: #ffffff !important;" title="{{ $f['name'] }}">
                                            {{ $f['name'] }}
                                        </strong>
                                    </div>
                                    <div class="flex items-center gap-3 text-[11px] font-mono pt-1" style="color: #94a3b8 !important;">
                                        <span class="file-size-badge" style="background-color: #020617 !important; color: #f59e0b !important; border: 1px solid #334155 !important;">
                                            {{ $f['size_formatted'] }}
                                        </span>
                                        <span>📅 {{ $f['modified_at'] }}</span>
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="pt-3 flex flex-col sm:flex-row items-center justify-between gap-3 border-t" style="border-color: #334155 !important;">
                    <div class="text-xs flex items-center gap-2" style="color: #cbd5e1 !important;">
                        <span>📍 Berkas Terpilih:</span>
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
                    📁
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
                        <span>🔄</span> Refresh Folder src-dtsen
                    </a>
                </div>
            </div>
        @endif
    </div>

    <!-- Alert Notifikasi Flash Session -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-900 font-bold text-xs flex items-center gap-2">
            <span>✅</span> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-rose-900 font-bold text-xs flex items-center gap-2">
            <span>❌</span> {{ session('error') }}
        </div>
    @endif

    @if($totalRows === 0 || empty($activeColumnsMap))
        <!-- KETIKA SISTEM KOSONG (0 BARIS DATA) -> BERSIH TOTAL TANPA MENGHASILKAN HEADER KOSONG APA PUN -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-3">
            <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center text-2xl mx-auto font-bold">📂</div>
            <h3 class="font-extrabold text-slate-900 text-base">Sistem Dalam Keadaan Bersih (Belum Ada File Data)</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">Silakan unggah berkas berformat <strong>.csv</strong> atau <strong>.xlsx</strong> menggunakan form di atas, atau klik tombol <strong>⚡ Demo Data (100 Baris / 30 Error)</strong> untuk memulai pengujian.</p>
        </div>
    @else
        <!-- KARTU WARNA KUNING: METRIK STATISTIK & STANDAR VALIDASI DATA -->
        <div class="p-6 rounded-2xl bg-amber-50/90 border-2 border-amber-300 text-amber-950 shadow-sm space-y-4">
            <div>
                <div class="flex items-center justify-between border-b border-amber-200 pb-3 mb-3">
                    <h3 class="font-black text-amber-900 text-sm flex items-center gap-2 uppercase tracking-wide">
                        <span>🧮</span> Ringkasan Metrik Kualitas Data & Evaluasi Adaptif
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
                        <span class="block text-[11px] font-bold text-emerald-800 uppercase">🟢 Data Valid</span>
                        <strong class="text-xl font-black text-emerald-950">{{ number_format($validCount) }}</strong>
                    </div>
                    <div class="p-3 bg-amber-100/90 rounded-xl border border-amber-300 text-center">
                        <span class="block text-[11px] font-bold text-amber-900 uppercase">🟡 Missing Value</span>
                        <strong class="text-xl font-black text-amber-950">{{ number_format($warningCount) }}</strong>
                    </div>
                    <div class="p-3 bg-rose-100/90 rounded-xl border border-rose-300 text-center">
                        <span class="block text-[11px] font-bold text-rose-800 uppercase">🔴 Critical Error</span>
                        <strong class="text-xl font-black text-rose-950">{{ number_format($criticalCount) }}</strong>
                    </div>
                    <div class="p-3 bg-purple-100/90 rounded-xl border border-purple-300 text-center">
                        <span class="block text-[11px] font-bold text-purple-900 uppercase">⚠️ Multi-Error (3+)</span>
                        <strong class="text-xl font-black text-purple-950">{{ number_format($multiErrorCount ?? 0) }}</strong>
                    </div>
                </div>
            </div>

            <div class="pt-3 border-t border-amber-200/80 text-xs leading-relaxed text-amber-900/90 space-y-1.5 font-medium">
                <p>
                    <strong>📌 Catatan Validasi & Relasi Standar Data:</strong> Berdasarkan standar pemeriksaan kualitas data DTSEN 2026, data diolah melalui pengecekan format (NIK & KK 16 digit), keabsahan nilai desil (1 s.d. 10), serta kelengkapan isian.
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

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <span>🔍</span> Filter Khusus Analisis Metrik Gaji Kelompok Subjek
                            </h3>
                            <p class="text-xs text-slate-500">Tentukan kriteria kelompok (misal: Perempuan, SMA, Desil Kesejahteraan, Status Pekerjaan) untuk menghitung statistik gaji kelompok tersebut.</p>
                        </div>

                        @if($jenisKelamin !== 'semua' || $pendidikan !== 'semua' || $statusBekerja !== 'semua' || $statusKawin !== 'semua' || $desil !== 'semua' || !empty($search))
                            <div class="flex flex-wrap items-center gap-1.5 bg-blue-50/80 p-2 rounded-xl border border-blue-200 shrink-0">
                                <span class="text-[11px] font-bold text-blue-900">Kelompok Terfilter:</span>
                                @if(!empty($search))
                                    <span class="px-2 py-0.5 bg-blue-600 text-white rounded-md text-[10px] font-bold">🔍 "{{ $search }}"</span>
                                @endif
                                @if($jenisKelamin !== 'semua')
                                    <span class="px-2 py-0.5 bg-indigo-600 text-white rounded-md text-[10px] font-bold">👤 {{ $jenisKelamin }}</span>
                                @endif
                                @if($pendidikan !== 'semua')
                                    <span class="px-2 py-0.5 bg-purple-600 text-white rounded-md text-[10px] font-bold">🎓 {{ $pendidikan }}</span>
                                @endif
                                @if($desil !== 'semua')
                                    <span class="px-2 py-0.5 bg-emerald-600 text-white rounded-md text-[10px] font-bold">📊 Desil {{ $desil }}</span>
                                @endif
                                @if($statusBekerja !== 'semua')
                                    <span class="px-2 py-0.5 bg-slate-700 text-white rounded-md text-[10px] font-bold">💼 {{ $statusBekerja }}</span>
                                @endif
                                <a href="{{ route('dtsen.index') }}#salary-analytics-section" class="text-[10px] font-bold text-rose-600 hover:underline ml-1">Reset Filter Gaji</a>
                            </div>
                        @endif
                    </div>

                    <!-- Row Filter Options Grid (Dedicated Filter Dropdowns for EVERY Column in the Uploaded Data Table) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5 col-span-full">
                        <!-- 1. Text Search (Nama Subjek) -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">Cari Nama Subjek</label>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Ketik nama subjek..." class="w-full text-xs font-medium bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- 2. Dedicated Dynamic Filter Dropdowns for EVERY Column in the Data Table -->
                        @foreach($importFilterableColumns as $colKey => $colTitle)
                            @if(isset($importColumnDistinctValues[$colKey]) && count($importColumnDistinctValues[$colKey]) > 0)
                                <div>
                                    <label class="block text-[11px] font-extrabold text-blue-900 uppercase tracking-wider mb-1">{{ $colTitle }}</label>
                                    <select name="{{ $colKey }}" class="w-full text-xs font-bold bg-blue-50 border border-blue-300 text-blue-900 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="semua">Semua {{ $colTitle }}</option>
                                        @foreach($importColumnDistinctValues[$colKey] as $vItem)
                                            <option value="{{ $vItem }}" {{ (request($colKey) == $vItem) ? 'selected' : '' }}>📌 {{ $vItem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('dtsen.index') }}#salary-analytics-section" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                            🔄 Reset Filter
                        </a>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1.5">
                            <span>💵</span> Hitung Metrik Gaji Terfilter
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. KARTU METRIK GAJI & PENDAPATAN SUBJEK (KPI CARDS HIGH-CONTRAST & HIGH-VISIBILITY) -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-emerald-100 text-emerald-800 rounded-xl text-lg font-bold">💵</span>
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

                <!-- 4 Kartu KPI Metrik Utama Gaji (Explicit Background & High-Contrast Colors) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Rata-Rata Gaji -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2" style="background-color: #eff6ff; border-color: #bfdbfe;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #1e40af;">
                            <span>📊 Gaji Rata-Rata (AVG)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black" style="background-color: #dbeafe; color: #1e3a8a;">AVG</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black" style="color: #0f172a;">Rp {{ number_format($gajiAvg, 0, ',', '.') }}</div>
                        <p class="text-xs font-medium" style="color: #2563eb;">Dari {{ number_format($gajiCount) }} subjek terfilter</p>
                    </div>

                    <!-- Gaji Tertinggi -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2" style="background-color: #ecfdf5; border-color: #a7f3d0;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #065f46;">
                            <span>📈 Gaji Tertinggi (MAX)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black" style="background-color: #d1fae5; color: #064e3b;">MAX</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black" style="color: #0f172a;">Rp {{ number_format($gajiMax, 0, ',', '.') }}</div>
                        <div class="text-xs font-medium truncate" style="color: #047857;">
                            @if($gajiMaxSubjek)
                                Subjek: <strong class="font-extrabold" style="color: #064e3b;" x-text="isMasked ? @json($gajiMaxSubjek->masked_nama) : @json($gajiMaxSubjek->nama)"></strong>
                            @else
                                Subjek: <em class="text-slate-400">Belum ada data terfilter</em>
                            @endif
                        </div>
                    </div>

                    <!-- Gaji Terendah -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2" style="background-color: #fffbeb; border-color: #fde68a;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #92400e;">
                            <span>📉 Gaji Terendah (MIN)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black" style="background-color: #fef3c7; color: #78350f;">MIN</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black" style="color: #0f172a;">Rp {{ number_format($gajiMin, 0, ',', '.') }}</div>
                        <div class="text-xs font-medium truncate" style="color: #b45309;">
                            @if($gajiMinSubjek)
                                Subjek: <strong class="font-extrabold" style="color: #78350f;" x-text="isMasked ? @json($gajiMinSubjek->masked_nama) : @json($gajiMinSubjek->nama)"></strong>
                            @else
                                Subjek: <em class="text-slate-400">Belum ada data terfilter</em>
                            @endif
                        </div>
                    </div>

                    <!-- Total Kumulatif Gaji -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2" style="background-color: #faf5ff; border-color: #e9d5ff;">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider" style="color: #6b21a8;">
                            <span>💰 Total Kumulatif (SUM)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black" style="background-color: #f3e8ff; color: #581c87;">SUM</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black" style="color: #0f172a;">Rp {{ number_format($gajiSum, 0, ',', '.') }}</div>
                        <p class="text-xs font-medium" style="color: #7e22ce;">Total seluruh pendapatan terfilter</p>
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
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                        <span>📈</span> Analitik Metrik & Diagram Top 5 / Bottom 5 Dinamis
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">Pilih variabel target dari file unggahan untuk menghitung statistik Nilai Maksimum, Minimum, Rata-Rata, & Peringkat.</p>
                </div>

                <!-- Form Dropdown Pilihan Variabel Target KPI oleh User -->
                <form method="GET" action="{{ route('dtsen.index') }}" class="flex items-center gap-2 shrink-0">
                    <input type="hidden" name="search" value="{{ $search }}">
                    <input type="hidden" name="desil" value="{{ $desil }}">
                    <input type="hidden" name="jenis_kelamin" value="{{ $jenisKelamin }}">
                    <input type="hidden" name="pendidikan" value="{{ $pendidikan }}">
                    <input type="hidden" name="status_bekerja" value="{{ $statusBekerja }}">
                    <input type="hidden" name="status_kawin" value="{{ $statusKawin }}">
                    <input type="hidden" name="filter_col" value="{{ $filterCol }}">
                    <input type="hidden" name="filter_val" value="{{ $filterVal }}">
                    
                    <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Variabel Target:</label>
                    <select name="kpi_var" onchange="this.form.submit()" class="text-xs font-bold bg-blue-50 border border-blue-300 rounded-xl px-3 py-2 text-blue-900 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm">
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
                            <span>🏆</span> Top 5 Peringkat Teratas ({{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }})
                        </h4>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">5 TERATAS</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($top5Records as $index => $rec)
                            @php
                                $rawDisplayVal = null;
                                $targetClean = strtolower(str_replace(' ', '_', trim($kpiTargetVar)));

                                if ($targetClean === 'desil_nasional' || $targetClean === 'desil') {
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
                                    <span class="truncate font-bold text-slate-900">{{ $index + 1 }}. {{ $rec->nama }} (NIK: {{ $rec->masked_nik }})</span>
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
                            <span>🔻</span> 5 Peringkat Terbawah ({{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }})
                        </h4>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded">5 TERBAWAH</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($bottom5Records as $index => $rec)
                            @php
                                $rawDisplayVal = null;
                                $targetClean = strtolower(str_replace(' ', '_', trim($kpiTargetVar)));

                                if ($targetClean === 'desil_nasional' || $targetClean === 'desil') {
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
                                    <span class="truncate font-bold text-slate-900">{{ $index + 1 }}. {{ $rec->nama }} (NIK: {{ $rec->masked_nik }})</span>
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
                        <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold">⚠️</span>
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm">Audit Temuan Data Bermasalah / Error</h3>
                            <p class="text-xs text-slate-500">Temuan kesalahan baris data yang memerlukan perbaikan (NIK/KK Cacat, Missing Value, dll.)</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('dtsen.export-errors') }}" class="px-3.5 py-1.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5">
                            <span>📥</span> Unduh Laporan Error (.csv)
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
                    @foreach($issueRecords as $issueRec)
                        <div class="p-4 rounded-xl border {{ $issueRec->quality_status === 'Critical' ? 'bg-rose-50/40 border-rose-200' : 'bg-amber-50/40 border-amber-200' }} space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    @if($issueRec->quality_status === 'Critical')
                                        <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-bold text-[10px]">🔴 CRITICAL</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-md bg-amber-500 text-white font-bold text-[10px]">🟡 WARNING</span>
                                    @endif
                                    <strong class="text-slate-900 text-xs" x-text="isMasked ? '{{ $issueRec->masked_nama }}' : '{{ $issueRec->nama }}'"></strong>
                                </div>
                                <button @click="openPreview({{ $issueRec->id }})" class="px-2.5 py-1 bg-white hover:bg-slate-100 border text-slate-700 font-bold text-[11px] rounded-lg shadow-sm">
                                    👁 Inspeksi Baris Data
                                </button>
                            </div>

                            <div class="text-xs space-y-1">
                                <div class="text-slate-600 flex items-center gap-3 font-mono text-[11px]">
                                    <span>NIK: <strong class="text-slate-900" x-text="isMasked ? '{{ $issueRec->masked_nik }}' : '{{ $issueRec->nomor_induk_kependudukan }}'"></strong></span>
                                    <span>KK: <strong class="text-slate-900">{{ $issueRec->nomor_kartu_keluarga }}</strong></span>
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
                        <span>📋</span> Tabel Data Terfilter ({{ number_format($records->total()) }} Baris Data)
                    </h3>
                    <p class="text-xs text-slate-500">Gunakan Search Engine di bawah ini atau filter pivot header (🔽) pada kolom tabel.</p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <!-- Tombol Atur Kolom Tabel (Add & Delete Column Visibilities) -->
                    <button type="button" @click="showColumnModal = true" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold text-xs rounded-xl border border-slate-300 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                        <span>⚙️</span> Atur Kolom Tabel (<span x-text="visibleCols.length"></span>/{{ count($activeColumnsMap) }})
                    </button>
                    <span class="text-xs font-bold text-slate-500 bg-slate-50 px-3 py-2 rounded-xl border border-slate-200">Halaman {{ $records->currentPage() }} dari {{ $records->lastPage() }}</span>
                </div>
            </div>

            <!-- SEARCH ENGINE DATA TABLE (Pencarian Cepat NIK, KK, Nama, Alamat, Wilayah) -->
            <form method="GET" action="{{ route('dtsen.index') }}#tabel-data" class="flex items-center gap-2">
                <div class="relative w-full">
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search Engine Data Table: Ketik NIK, Nomor KK, Nama Lengkap, Alamat, Kecamatan, atau Provinsi..." class="w-full pl-4 pr-16 py-2.5 bg-slate-50 border border-slate-300 text-slate-900 font-bold text-xs rounded-xl outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all shadow-xs">
                    @if(!empty($search))
                        <a href="{{ route('dtsen.index') }}#tabel-data" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-700 font-extrabold text-xs">✕ Clear</a>
                    @endif
                </div>
                <button type="submit" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all flex items-center gap-1.5 shrink-0 cursor-pointer">
                    <span>⚡</span> Cari Data
                </button>
            </form>

            <!-- MODAL POPUP ATUR KOLOM TABEL (COLUMN VISIBILITY MANAGER) -->
            <div x-show="showColumnModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs p-4">
                <div @click.away="showColumnModal = false" class="bg-white rounded-2xl max-w-4xl w-full p-6 space-y-4 shadow-2xl border border-slate-200">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <span>⚙️</span> Pengatur Tampilan Kolom Tabel (Kamus Resmi DTSEN 2026 BPS-Bappenas)
                            </h3>
                            <p class="text-xs text-slate-500">Centang atau hilangkan centang untuk memilih dari 100 variabel resmi DTSEN 2026 yang tampil pada tabel data.</p>
                        </div>
                        <button type="button" @click="showColumnModal = false" class="text-slate-400 hover:text-slate-700 font-extrabold text-lg">&times;</button>
                    </div>

                    <div class="space-y-4 max-h-[65vh] overflow-y-auto pr-1">
                        <!-- Group 1: Set Data Anggota Keluarga (Individu - 48) -->
                        <div class="p-3 bg-blue-50/60 rounded-xl border border-blue-200 space-y-2">
                            <h4 class="text-xs font-black uppercase text-blue-900 flex items-center gap-1.5 border-b border-blue-200 pb-1.5">
                                👤 Set Data Anggota Keluarga (Individu - 48 Variabel Resmi)
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($officialIndividuVars as $cKey => $cTitle)
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                        <input type="checkbox" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500">
                                        <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Group 2: Set Data Keluarga (52) -->
                        <div class="p-3 bg-emerald-50/60 rounded-xl border border-emerald-200 space-y-2">
                            <h4 class="text-xs font-black uppercase text-emerald-900 flex items-center gap-1.5 border-b border-emerald-200 pb-1.5">
                                🏠 Set Data Keluarga (52 Variabel Resmi)
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                @foreach($officialKeluargaVars as $cKey => $cTitle)
                                    <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-emerald-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                        <input type="checkbox" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-emerald-600 rounded border-slate-300 focus:ring-emerald-500">
                                        <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Group 3: Variabel Tambahan / QC -->
                        @php
                            $extraCols = array_diff_key($activeColumnsMap, $officialIndividuVars, $officialKeluargaVars);
                        @endphp
                        @if(!empty($extraCols))
                            <div class="p-3 bg-purple-50/60 rounded-xl border border-purple-200 space-y-2">
                                <h4 class="text-xs font-black uppercase text-purple-900 flex items-center gap-1.5 border-b border-purple-200 pb-1.5">
                                    📌 Variabel Khusus & Indikator QC
                                </h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2">
                                    @foreach($extraCols as $cKey => $cTitle)
                                        <label class="flex items-center gap-2 p-2 rounded-lg border border-slate-200 bg-white hover:bg-purple-100/60 cursor-pointer text-xs font-bold text-slate-800 transition-colors">
                                            <input type="checkbox" :checked="isColVisible('{{ $cKey }}')" @change="toggleCol('{{ $cKey }}')" class="w-4 h-4 text-purple-600 rounded border-slate-300 focus:ring-purple-500">
                                            <span class="truncate" title="{{ $cTitle }}">{{ $cTitle }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="pt-3 flex items-center justify-between border-t border-slate-100">
                        <button type="button" @click="resetCols()" class="text-xs font-black hover:underline cursor-pointer" style="color: #2563eb !important;">
                            🔄 Tampilkan Semua Kolom
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
                            <th class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider bg-slate-100">QC Status</th>

                            <!-- Header Kolom Dinamis dengan Popover Filter Pivot Excel (Tersedia untuk Seluruh Variabel) -->
                            @foreach($activeColumnsMap as $colKey => $colTitle)
                                <th x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs font-bold text-slate-700 uppercase tracking-wider whitespace-nowrap relative group bg-slate-100">
                                    <div class="flex items-center justify-between gap-2">
                                        <span>{{ $colTitle }}</span>

                                        <!-- Tombol Filter Pivot AutoFilter Excel Multi-Select untuk Seluruh Kolom -->
                                        <div class="relative" x-data="{ 
                                            open: false, 
                                            searchVal: '',
                                            selectedVals: {{ json_encode(array_values(array_filter(is_array(request($colKey)) ? request($colKey) : explode(',', (string)request($colKey)), fn($v) => $v !== '' && $v !== 'semua'))) }},
                                            toggleVal(val) {
                                                if (this.selectedVals.includes(val)) {
                                                    this.selectedVals = this.selectedVals.filter(v => v !== val);
                                                } else {
                                                    this.selectedVals.push(val);
                                                }
                                            },
                                            selectAll(items) {
                                                if (this.selectedVals.length === items.length) {
                                                    this.selectedVals = [];
                                                } else {
                                                    this.selectedVals = [...items];
                                                }
                                            },
                                            applyFilter() {
                                                const url = new URL(window.location.href);
                                                if (this.selectedVals.length > 0) {
                                                    url.searchParams.set('{{ $colKey }}', this.selectedVals.join(','));
                                                } else {
                                                    url.searchParams.delete('{{ $colKey }}');
                                                }
                                                url.searchParams.set('page', '1');
                                                url.hash = 'tabel-data';
                                                window.location.href = url.toString();
                                            },
                                            clearFilter() {
                                                this.selectedVals = [];
                                                this.applyFilter();
                                            }
                                        }">
                                            <button type="button" @click.stop="open = !open" 
                                                    class="px-1.5 py-0.5 rounded transition-colors flex items-center gap-1 cursor-pointer"
                                                    :class="selectedVals.length > 0 ? 'bg-blue-600 text-white font-extrabold text-[10px] shadow-xs' : 'text-slate-400 hover:bg-slate-200 hover:text-slate-700'">
                                                <span class="text-[10px]">🔽</span>
                                                <span x-show="selectedVals.length > 0" class="text-[10px] font-black" x-text="selectedVals.length"></span>
                                            </button>

                                            <!-- Popover Dropdown Filter Pivot Excel Multi-Select -->
                                            <div x-show="open" @click.away="open = false" x-cloak 
                                                 class="absolute right-0 mt-1 w-72 bg-white rounded-xl shadow-2xl border border-slate-200 p-3 z-30 space-y-2.5 text-slate-800 normal-case tracking-normal">
                                                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                                                    <span class="font-extrabold text-[11px] text-blue-900 uppercase">Filter: {{ $colTitle }}</span>
                                                    <button type="button" @click="open = false" class="text-slate-400 hover:text-slate-600 font-bold text-base">&times;</button>
                                                </div>

                                                <input type="text" x-model="searchVal" placeholder="Cari nilai..." class="w-full text-xs bg-slate-50 border border-slate-300 rounded-lg px-2.5 py-1.5 outline-none focus:ring-2 focus:ring-blue-500">

                                                <div class="max-h-48 overflow-y-auto space-y-1 text-xs pr-1">
                                                    @if(isset($importColumnDistinctValues[$colKey]) && count($importColumnDistinctValues[$colKey]) > 0)
                                                        @php $allItemsJson = json_encode($importColumnDistinctValues[$colKey]); @endphp
                                                        <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-slate-100 cursor-pointer font-bold text-blue-600 border-b border-slate-100 pb-1.5 mb-1">
                                                            <input type="checkbox" 
                                                                   :checked="selectedVals.length === {{ count($importColumnDistinctValues[$colKey]) }}" 
                                                                   @change="selectAll({{ $allItemsJson }})"
                                                                   class="accent-blue-600 rounded cursor-pointer">
                                                            <span>(Pilih / Hapus Semua)</span>
                                                        </label>

                                                        @foreach($importColumnDistinctValues[$colKey] as $dItem)
                                                            <label x-show="!searchVal || @json(strtolower($dItem)).includes(searchVal.toLowerCase())"
                                                                   class="flex items-center gap-2 px-2 py-1 rounded hover:bg-blue-50 cursor-pointer font-medium text-slate-700 transition-colors"
                                                                   :class="selectedVals.includes(@json($dItem)) ? 'bg-blue-50 font-bold text-blue-900' : ''">
                                                                <input type="checkbox" 
                                                                       :checked="selectedVals.includes(@json($dItem))" 
                                                                       @change="toggleVal(@json($dItem))"
                                                                       class="accent-blue-600 rounded cursor-pointer">
                                                                <span class="truncate text-xs">{{ $dItem }}</span>
                                                            </label>
                                                        @endforeach
                                                    @else
                                                        <p class="text-xs text-slate-400 italic px-2 py-1">Tidak ada data variasi.</p>
                                                    @endif
                                                </div>

                                                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                                                    <button type="button" @click="clearFilter()" class="px-2.5 py-1.5 text-[11px] font-bold text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                                                        Reset
                                                    </button>
                                                    <button type="button" @click="applyFilter()" class="px-3.5 py-1.5 text-xs font-black bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-sm transition-all flex items-center gap-1 cursor-pointer">
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

                                <!-- Dynamic Cell Output dengan Format Pemisah Ribuan Titik (.) Otomatis -->
                                @foreach($activeColumnsMap as $colKey => $colTitle)
                                    <td x-show="isColVisible('{{ $colKey }}')" class="px-4 py-3 text-xs text-slate-800 whitespace-nowrap">
                                        @if($colKey === 'nomor_induk_kependudukan' || $colKey === 'nik')
                                            <span class="font-mono font-bold text-slate-900" x-text="isMasked ? '{{ $row->masked_nik }}' : '{{ $row->$colKey }}'"></span>
                                        @elseif($colKey === 'nomor_kartu_keluarga' || $colKey === 'no_kk')
                                            <span class="font-mono text-slate-800">{{ $row->$colKey }}</span>
                                        @elseif($colKey === 'nama' || $colKey === 'nama_lengkap')
                                            <span class="font-bold text-slate-900" x-text="isMasked ? '{{ $row->masked_nama }}' : '{{ $row->$colKey }}'"></span>
                                        @elseif($colKey === 'desil_nasional' || $colKey === 'desil')
                                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 font-bold text-[11px]">Desil {{ $row->$colKey ?? '-' }}</span>
                                        @elseif($colKey === 'tanggal_lahir')
                                            <span>{{ $row->tanggal_lahir ? $row->tanggal_lahir->format('d/m/Y') : '-' }}</span>
                                        @else
                                            @php
                                                $rawVal = $row->$colKey;
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
                                        👁 Detail
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ count($activeColumnsMap) + 3 }}" class="px-4 py-16 text-center">
                                    <div class="space-y-3 max-w-md mx-auto py-6">
                                        <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl font-bold mx-auto shadow-xs">🔍</div>
                                        <div class="space-y-1">
                                            <h4 class="font-extrabold text-sm text-slate-800">Tidak ada data yang sesuai dengan kriteria filter</h4>
                                            <p class="text-xs text-slate-500 font-medium">Coba sesuaikan kata kunci pencarian atau bersihkan filter pivot header kolom (🔽).</p>
                                        </div>
                                        <div class="pt-2">
                                            <a href="{{ route('dtsen.index') }}#tabel-data" class="px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-sm transition-all inline-flex items-center gap-1.5 cursor-pointer">
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
                    <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold">📋</span>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">Inspeksi Rincian Baris Data Subjek</h3>
                        <p class="text-[11px] text-slate-500">Detail data individu & relasi aset rumah tangga pengampu</p>
                    </div>
                </div>
                <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base">✕</button>
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
                                    <span>🔴</span> Rincian Temuan Error Audit Kualitas Data
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
                                <span>🏠</span> Variabel Pengampu Rumah Tangga (KK)
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

    <!-- Modal Realtime Progress Bar & Estimasi Waktu (ETA) -->
    <div x-show="showImportProgressModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-md p-4" 
         style="display: none;">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md p-6 space-y-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
                    <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Memproses Impor Data</h3>
                    <p class="text-xs text-slate-500 font-medium" x-text="importProgressMessage"></p>
                </div>
            </div>

            <!-- Realtime Progress Bar -->
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs font-bold">
                    <span class="text-slate-700" x-text="importProgressPercent + '% Selesai'"></span>
                    <span class="text-blue-600 font-mono" x-show="importEtaSeconds > 0" x-text="'Estimasi sisa waktu: ~' + importEtaSeconds + ' detik'"></span>
                    <span class="text-emerald-600 font-mono" x-show="importEtaSeconds === 0 && importProgressPercent === 100">Selesai!</span>
                </div>
                <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200">
                    <div class="h-full bg-blue-600 rounded-full transition-all duration-300 shadow-sm" :style="{ width: importProgressPercent + '%' }"></div>
                </div>
            </div>

            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 flex items-center justify-between font-mono">
                <span>Status Pemrosesan:</span>
                <span class="text-slate-900 font-bold" x-text="importProgressMessage"></span>
            </div>
        </div>
    </div>

</div>

<script>
function dtsenApp() {
    const allColKeys = @json(array_keys($activeColumnsMap));
    let savedCols = null;
    try {
        savedCols = JSON.parse(localStorage.getItem('padu_visible_columns'));
    } catch(e) {}

    return {
        isMasked: false,
        showPreviewModal: false,
        showColumnModal: false,
        previewData: null,
        visibleCols: (savedCols && Array.isArray(savedCols) && savedCols.length > 0) ? savedCols : allColKeys,

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
            this.importEtaSeconds = 9;

            if (this.progressPollTimer) clearInterval(this.progressPollTimer);

            // Smooth Client Progress Animation (Ticks every 400ms for realistic ETA countdown)
            let elapsedSteps = 0;
            const totalEstimatedSteps = 22; // ~9 seconds total
            this.progressPollTimer = setInterval(() => {
                elapsedSteps++;
                const progressRatio = Math.min(elapsedSteps / totalEstimatedSteps, 0.95);
                this.importProgressPercent = Math.min(95, Math.round(5 + (progressRatio * 90)));
                this.importEtaSeconds = Math.max(1, Math.round(9 * (1 - progressRatio)));

                if (this.importProgressPercent < 25) {
                    this.importProgressMessage = 'Membaca data dari berkas CSV...';
                } else if (this.importProgressPercent < 50) {
                    this.importProgressMessage = 'Memvalidasi NIK, KK, & kualitas data...';
                } else if (this.importProgressPercent < 75) {
                    this.importProgressMessage = 'Menyiapkan & mengurutkan data...';
                } else if (this.importProgressPercent < 92) {
                    this.importProgressMessage = 'Memasukkan data ke dalam database...';
                } else {
                    this.importProgressMessage = 'Membangun indeks pencarian database...';
                }

                // Polling attempt in case backend server is multi-threaded
                fetch('/dtsen/import-progress')
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.percent > 0 && data.percent > this.importProgressPercent) {
                            this.importProgressPercent = data.percent;
                            this.importProgressMessage = data.message;
                            this.importEtaSeconds = data.eta_seconds;
                        }
                    })
                    .catch(() => {});
            }, 400);

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
</script>
@endsection
