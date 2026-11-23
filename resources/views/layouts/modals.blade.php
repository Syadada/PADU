<!-- ================= SHARED GLOBAL MODALS PADU ================= -->

<!-- 1. Modal Opsi Ekspor Data -->
<div x-show="showExportModal" 
     x-transition 
     x-cloak
     @keydown.escape.window="showExportModal = false"
     class="padu-modal-backdrop bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto" 
     style="display: none; z-index: 999999 !important;">
    <div @click.away="showExportModal = false" class="padu-modal-card bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg p-6 space-y-4 flex flex-col my-auto overflow-y-auto" style="z-index: 1000000 !important; max-height: 88vh !important;">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold"><i class="fi fi-rr-upload"></i></span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Opsi Ekspor Data DTSEN 2026</h3>
                    <p class="text-[11px] text-slate-500">Pilih mode ekspor dan format berkas yang diinginkan</p>
                </div>
            </div>
            <button type="button" @click="showExportModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer flex items-center"><i class="fi fi-rr-cross"></i></button>
        </div>

        <form action="{{ route('dtsen.export') }}" method="GET" class="space-y-4">
            <input type="hidden" name="search" value="{{ $search ?? '' }}">
            <input type="hidden" name="desil" value="{{ $desil ?? 'semua' }}">
            <input type="hidden" name="jenis_kelamin" value="{{ $jenisKelamin ?? 'semua' }}">
            <input type="hidden" name="pendidikan" value="{{ $pendidikan ?? 'semua' }}">
            <input type="hidden" name="status_bekerja" value="{{ $statusBekerja ?? 'semua' }}">
            <input type="hidden" name="status_kawin" value="{{ $statusKawin ?? 'semua' }}">

            <!-- 1. Mode Ekspor Data -->
            <div class="space-y-2">
                <div class="text-xs font-bold text-slate-800 uppercase">1. Mode Ekspor Data</div>
                <div class="space-y-2">
                    <label for="export_mode_as_is" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex items-start gap-3 transition-colors">
                        <input type="radio" id="export_mode_as_is" name="mode" value="as_is" aria-label="Mode A: Ekspor As-Is (+ Catatan Error Audit)" checked class="mt-0.5 accent-blue-600">
                        <div>
                            <strong class="block text-xs text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-document text-blue-600"></i> Mode A: Ekspor As-Is (+ Catatan Error Audit)</strong>
                            <p class="text-[11px] text-slate-500">Mengekspor seluruh baris data apa adanya ditambah 1 kolom rincian temuan error. Urutan & indeks baris tetap presisi 100%.</p>
                        </div>
                    </label>
                    <label for="export_mode_cleaned_only" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex items-start gap-3 transition-colors">
                        <input type="radio" id="export_mode_cleaned_only" name="mode" value="cleaned_only" aria-label="Mode B: Ekspor Cleaned Only (Data Cacat Dibuang)" class="mt-0.5 accent-emerald-600">
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
                    <label for="export_format_xlsx" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex flex-col items-center justify-center text-center">
                        <input type="radio" id="export_format_xlsx" name="format" value="xlsx" aria-label="Format Excel Terbaru (.xlsx)" checked class="mb-1 accent-emerald-600">
                        <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-file-excel text-emerald-600"></i> Excel Terbaru (.xlsx)</span>
                    </label>
                    <label for="export_format_csv" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex flex-col items-center justify-center text-center">
                        <input type="radio" id="export_format_csv" name="format" value="csv" aria-label="Format CSV File (.csv)" class="mb-1 accent-blue-600">
                        <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5"><i class="fi fi-rr-file-spreadsheet text-blue-600"></i> CSV File (.csv)</span>
                    </label>
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                <p><i class="fi fi-rr-folder"></i> <strong>Lokasi Hasil Ekspor:</strong> Paket berkas (.ZIP) akan diekspor & disimpan langsung di folder <code class="text-blue-900 font-mono font-bold">src-export/</code> di root project.</p>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" @click="showExportModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 cursor-pointer">
                    Batal
                </button>
                <button type="submit" @click="showExportModal = false" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fi fi-rr-download"></i> <span>Ekspor Langsung ke Folder src-export/</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Modal Inspeksi Rincian Baris Data Subjek -->
<div x-show="showPreviewModal" 
     x-transition 
     x-cloak
     @keydown.escape.window="showPreviewModal = false"
     class="padu-modal-backdrop bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto" 
     style="display: none; z-index: 999999 !important;">
    <div @click.away="showPreviewModal = false" class="padu-modal-card bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-2xl p-6 space-y-4 flex flex-col my-auto" style="z-index: 1000000 !important; max-height: 88vh !important;">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold"><i class="fi fi-rr-document"></i></span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Inspeksi Rincian Baris Data Subjek</h3>
                    <p class="text-[11px] text-slate-500">Detail data individu & relasi aset rumah tangga pengampu</p>
                </div>
            </div>
            <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer flex items-center"><i class="fi fi-rr-cross"></i></button>
        </div>

        <template x-if="previewData">
            <div class="space-y-4 text-xs flex-1 overflow-y-auto pr-1">
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
                                <i class="fi fi-rr-triangle-warning text-rose-600"></i> Rincian Temuan Error Audit Kualitas Data
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
                        <span class="text-slate-900 font-mono" x-text="isMasked ? previewData.masked_nomor_kartu_keluarga : previewData.nomor_kartu_keluarga"></span>
                    </div>
                    <div>
                        <span class="text-slate-500 block text-[11px]">Jenis Kelamin:</span>
                        <span class="text-slate-900 font-bold" x-text="previewData.jenis_kelamin"></span>
                    </div>
                    <div x-show="previewData.status_hubungan && previewData.status_hubungan !== '-'">
                        <span class="text-slate-500 block text-[11px]">Status Hubungan:</span>
                        <span class="text-slate-900 font-bold" x-text="previewData.status_hubungan"></span>
                    </div>
                    <div x-show="previewData.status_bekerja && previewData.status_bekerja !== '-'">
                        <span class="text-slate-500 block text-[11px]">Status Bekerja:</span>
                        <span class="text-slate-900 font-bold" x-text="previewData.status_bekerja"></span>
                    </div>
                </div>

                <!-- Relasi KK Aset -->
                <template x-if="previewData.keluarga">
                    <div class="space-y-2 pt-2 border-t border-slate-200">
                        <h4 class="font-extrabold text-slate-800 text-xs flex items-center gap-1.5">
                            <i class="fi fi-rr-home text-blue-600"></i> Variabel Pengampu Rumah Tangga (KK)
                        </h4>
                        <div class="grid grid-cols-2 gap-2 bg-blue-50/50 p-3 rounded-xl border border-blue-200">
                            <div>
                                <span class="text-slate-500 block text-[11px]">Desil Kesejahteraan:</span>
                                <strong class="text-blue-900 font-bold" x-text="'Desil ' + previewData.keluarga.desil_nasional"></strong>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[11px]">Jenis Lantai Terluas:</span>
                                <span class="text-slate-800 font-bold" x-text="previewData.keluarga.jenis_lantai"></span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[11px]">Jenis Atap Terluas:</span>
                                <span class="text-slate-800 font-bold" x-text="previewData.keluarga.jenis_atap"></span>
                            </div>
                            <div>
                                <span class="text-slate-500 block text-[11px]">Wilayah Domisili:</span>
                                <span class="text-slate-800 font-bold" x-text="previewData.keluarga.provinsi + ' / ' + previewData.keluarga.kabupaten_kota"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Seluruh Variabel & Terjemahan Metadata Kamus BPS/DTSEN -->
                <template x-if="previewData.all_fields && previewData.all_fields.length > 0">
                    <div class="space-y-2 pt-2 border-t border-slate-200" x-data="{ varSearch: '', showAllVars: false }">
                        <div class="flex items-center justify-between">
                            <h4 class="font-extrabold text-slate-800 text-xs flex items-center gap-1.5">
                                <i class="fi fi-rr-apps text-indigo-600"></i>
                                <span>Seluruh Variabel & Terjemahan Metadata (<span x-text="previewData.all_fields.length"></span> Variabel)</span>
                            </h4>
                            <button type="button" @click="showAllVars = !showAllVars" class="text-xs text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1 cursor-pointer">
                                <span x-text="showAllVars ? 'Tutup Rincian' : 'Buka Rincian Lengkap'"></span>
                                <i class="fi" :class="showAllVars ? 'fi-rr-angle-small-up' : 'fi-rr-angle-small-down'"></i>
                            </button>
                        </div>

                        <div x-show="showAllVars" x-transition class="space-y-2">
                            <!-- Input Pencarian Variabel dalam Modal -->
                            <div class="relative">
                                <i class="fi fi-rr-search text-slate-400 text-xs pointer-events-none" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%);"></i>
                                <input type="text" x-model="varSearch" placeholder="Cari nama variabel atau kode (misal: r101, jenis_kelamin, lantai)..." 
                                       class="w-full text-xs bg-white border border-slate-300 rounded-lg pl-8 pr-3 py-1.5 outline-none focus:ring-2 focus:ring-indigo-500 font-medium">
                            </div>

                            <!-- List / Tabel Grid Variabel -->
                            <div class="max-h-60 overflow-y-auto border border-slate-200 rounded-xl divide-y divide-slate-100 bg-white scrollbar-thin">
                                <template x-for="(f, fIdx) in previewData.all_fields.filter(item => !varSearch || item.key.toLowerCase().includes(varSearch.toLowerCase()) || item.label.toLowerCase().includes(varSearch.toLowerCase()) || String(item.raw_value).toLowerCase().includes(varSearch.toLowerCase()) || (item.translated_value && item.translated_value.toLowerCase().includes(varSearch.toLowerCase())))" :key="fIdx">
                                    <div class="p-2.5 flex items-center justify-between gap-3 text-xs hover:bg-slate-50 transition-colors">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-slate-800 truncate" x-text="f.label"></div>
                                            <div class="text-[10px] font-mono text-slate-400 truncate" x-text="f.key"></div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <template x-if="f.has_translation">
                                                <div class="space-y-0.5">
                                                    <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-800 font-bold text-[11px] border border-blue-200 inline-block" x-text="f.translated_value"></span>
                                                    <div class="text-[10px] font-mono text-slate-500" x-text="'Kode Asli: ' + f.raw_value"></div>
                                                </div>
                                            </template>
                                            <template x-if="!f.has_translation">
                                                <span class="font-semibold text-slate-900" x-text="f.raw_value"></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </template>

        <div class="pt-3 flex justify-end border-t border-slate-100 shrink-0">
            <button type="button" @click="showPreviewModal = false" class="px-6 py-2.5 rounded-xl font-black text-xs shadow-md transition-all cursor-pointer" style="background-color: #0f172a; color: #ffffff !important;">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- 4. Modal Realtime Progress Bar & Estimasi Waktu (ETA) -->
<div x-show="showImportProgressModal" 
     x-transition 
     x-cloak
     @keydown.escape.window="showImportProgressModal = false"
     class="padu-modal-backdrop bg-slate-900/70 backdrop-blur-md p-4" 
     style="display: none; z-index: 999999 !important;">
    <div class="padu-modal-card bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md p-6 space-y-5" style="z-index: 1000000 !important;">
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
                <span class="text-blue-600 font-mono" x-show="importProgressPercent < 100" x-text="'Estimasi sisa waktu: ~' + (importEtaSeconds > 0 ? formatEta(importEtaSeconds) : '5 detik')"></span>
                <span class="text-emerald-600 font-mono" x-show="importProgressPercent === 100">Selesai!</span>
            </div>
            <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden p-0.5 border border-slate-200">
                <div class="h-full bg-blue-600 rounded-full transition-all duration-300 shadow-sm" :style="{ width: importProgressPercent + '%' }"></div>
            </div>
        </div>

        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 flex items-center justify-between font-mono">
            <span>Status Pemrosesan:</span>
            <span class="text-slate-900 font-bold" x-text="importProgressMessage"></span>
        </div>

        <!-- Tombol Batalkan Injeksi jika Hang / Stuck -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
            <a href="{{ route('dtsen.activity-logs') }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 border border-slate-300 cursor-pointer text-decoration-none">
                <i class="fi fi-rr-list-check"></i> <span>Buka Halaman Log Monitor</span>
            </a>
            <button type="button" @click="cancelCurrentImport()" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                <i class="fi fi-rr-ban"></i> <span>Batalkan Injeksi Data</span>
            </button>
        </div>
    </div>
</div>

<!-- 5. Modal Konfirmasi Kosongkan Dataset Aktif -->
<div x-show="showClearModal || (typeof showClearDataModal !== 'undefined' && showClearDataModal)" 
     x-transition 
     x-cloak
     @keydown.escape.window="showClearModal = false; if (typeof showClearDataModal !== 'undefined') showClearDataModal = false;"
     class="padu-modal-backdrop bg-slate-900/70 backdrop-blur-md p-4 flex items-center justify-center" 
     style="display: none; z-index: 999999 !important;">
    <div class="padu-modal-card bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md p-6 space-y-5" 
         @click.away="showClearModal = false; if (typeof showClearDataModal !== 'undefined') showClearDataModal = false;"
         style="z-index: 1000000 !important;">
        <div class="flex items-center gap-3.5">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0 font-bold shadow-xs">
                <i class="fi fi-rr-trash"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-900 text-base">Kosongkan Data yang Tampil?</h3>
                <p class="text-xs text-slate-500 font-medium">Reset tabel data dan bersihkan memori analitik aktif.</p>
            </div>
        </div>

        <div class="p-3.5 bg-rose-50/70 border border-rose-200 rounded-xl space-y-1.5 text-xs text-rose-900">
            <div class="font-bold flex items-center gap-1.5">
                <i class="fi fi-rr-info text-rose-600"></i>
                <span>Perhatian Keamanan Data:</span>
            </div>
            <p class="text-[11px] text-rose-800 leading-relaxed">
                Seluruh data yang sedang dimuat di database tabel analitik (DuckDB/SQLite) akan dikosongkan. <strong>Berkas fisik asli Anda di folder <code>src-dtsen/</code> tetap aman tersimpan</strong> dan dapat dimuat ulang kapan saja.
            </p>
        </div>

        <div class="pt-2 flex items-center justify-end gap-2.5">
            <button type="button" 
                    @click="showClearModal = false; if (typeof showClearDataModal !== 'undefined') showClearDataModal = false;" 
                    class="px-4 py-2.5 rounded-xl border border-slate-300 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs transition-all cursor-pointer">
                Batal
            </button>
            <form action="{{ route('dtsen.clear') }}" method="POST" class="inline">
                @csrf
                <button type="submit" 
                        class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-black text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <i class="fi fi-rr-trash"></i> <span>Ya, Kosongkan Data</span>
                </button>
            </form>
        </div>
    </div>
</div>


