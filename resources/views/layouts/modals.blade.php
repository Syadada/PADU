<!-- ================= SHARED GLOBAL MODALS PADU ================= -->

<!-- 1. Modal Konfirmasi Kosongkan Data -->
<div x-show="showClearModal" 
     x-transition 
     x-cloak
     @keydown.escape.window="showClearModal = false"
     class="padu-modal-backdrop bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto" 
     style="display: none; z-index: 999999 !important;">
    <div @click.away="showClearModal = false" class="padu-modal-card bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md p-6 space-y-4 flex flex-col my-auto overflow-y-auto" style="z-index: 1000000 !important; max-height: 88vh !important;">
        <div class="flex items-center gap-3 border-b border-slate-100 pb-3 shrink-0">
            <span class="w-10 h-10 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold text-lg">🗑️</span>
            <div>
                <h3 class="font-extrabold text-slate-900 text-sm">Konfirmasi Kosongkan Data</h3>
                <p class="text-[11px] text-slate-500">Hapus seluruh data di sistem incase salah upload file</p>
            </div>
        </div>

        <div class="p-3.5 bg-rose-50 rounded-xl border border-rose-200 text-xs text-rose-900 space-y-1 shrink-0">
            <p><strong>⚠️ Perhatian:</strong> Tindakan ini akan menghapus <strong>{{ number_format($totalRows ?? 0) }} baris data</strong> yang saat ini ada di sistem. Sistem akan dikosongkan secara total agar Anda bisa mengunggah file yang benar.</p>
        </div>

        <form action="{{ route('dtsen.clear') }}" method="POST" class="pt-2 flex items-center justify-end gap-2 border-t border-slate-100 shrink-0">
            @csrf
            <button type="button" @click="showClearModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 cursor-pointer">
                Batal
            </button>
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                <span>🗑️</span> Ya, Kosongkan Sekarang
            </button>
        </form>
    </div>
</div>

<!-- 2. Modal Opsi Ekspor Data -->
<div x-show="showExportModal" 
     x-transition 
     x-cloak
     @keydown.escape.window="showExportModal = false"
     class="padu-modal-backdrop bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto" 
     style="display: none; z-index: 999999 !important;">
    <div @click.away="showExportModal = false" class="padu-modal-card bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-lg p-6 space-y-4 flex flex-col my-auto overflow-y-auto" style="z-index: 1000000 !important; max-height: 88vh !important;">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 shrink-0">
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">📤</span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Opsi Ekspor Data DTSEN 2026</h3>
                    <p class="text-[11px] text-slate-500">Pilih mode ekspor dan format berkas yang diinginkan</p>
                </div>
            </div>
            <button type="button" @click="showExportModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">✕</button>
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
                            <strong class="block text-xs text-slate-900">📄 Mode A: Ekspor As-Is (+ Catatan Error Audit)</strong>
                            <p class="text-[11px] text-slate-500">Mengekspor seluruh baris data apa adanya ditambah 1 kolom rincian temuan error. Urutan & indeks baris tetap presisi 100%.</p>
                        </div>
                    </label>
                    <label for="export_mode_cleaned_only" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-emerald-50 cursor-pointer flex items-start gap-3 transition-colors">
                        <input type="radio" id="export_mode_cleaned_only" name="mode" value="cleaned_only" aria-label="Mode B: Ekspor Cleaned Only (Data Cacat Dibuang)" class="mt-0.5 accent-emerald-600">
                        <div>
                            <strong class="block text-xs text-slate-900">🟢 Mode B: Ekspor Cleaned Only (Data Cacat Dibuang)</strong>
                            <p class="text-[11px] text-slate-500">Mengekspor hanya baris data yang 🟢 Valid. Disertai kolom <code>Original_Row_Index</code> untuk rekonsiliasi ke master file.</p>
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
                        <span class="text-xs font-bold text-slate-900">🟢 Excel Terbaru (.xlsx)</span>
                    </label>
                    <label for="export_format_csv" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-blue-50 cursor-pointer flex flex-col items-center justify-center text-center">
                        <input type="radio" id="export_format_csv" name="format" value="csv" aria-label="Format CSV File (.csv)" class="mb-1 accent-blue-600">
                        <span class="text-xs font-bold text-slate-900">📄 CSV File (.csv)</span>
                    </label>
                </div>
            </div>

            <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-[11px] text-amber-900 space-y-1">
                <p><strong>📦 Lokasi Hasil Ekspor:</strong> Paket berkas (.ZIP) akan diekspor & disimpan langsung di folder <code class="text-blue-900 font-mono font-bold">src-export/</code> di root project.</p>
                <p><strong>🧹 Auto-Purge Reset:</strong> Setelah ekspor selesai, data sistem dikosongkan agar bersih untuk pemrosesan file berikutnya.</p>
            </div>

            <div class="pt-3 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" @click="showExportModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-700 font-bold text-xs hover:bg-slate-200 cursor-pointer">
                    Batal
                </button>
                <button type="submit" @click="showExportModal = false" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                    <span>📥</span> Ekspor Langsung ke Folder src-export/
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
                <span class="w-8 h-8 rounded-xl bg-blue-100 text-blue-800 flex items-center justify-center font-bold">📋</span>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Inspeksi Rincian Baris Data Subjek</h3>
                    <p class="text-[11px] text-slate-500">Detail data individu & relasi aset rumah tangga pengampu</p>
                </div>
            </div>
            <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-600 font-bold text-base cursor-pointer">✕</button>
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
                        <span class="text-slate-900 font-mono" x-text="isMasked ? previewData.masked_nomor_kartu_keluarga : previewData.nomor_kartu_keluarga"></span>
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
            <button type="button" @click="openLogConsole()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1.5 border border-slate-300 cursor-pointer">
                <span>📋</span> Buka Log Monitor
            </button>
            <button type="button" @click="cancelCurrentImport()" class="px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                <span>🚫</span> Batalkan Injeksi Data
            </button>
        </div>
    </div>
</div>

<!-- 5. Modal Console Log System Realtime & Activity Monitor -->
<div x-show="showLogModal" 
     x-transition 
     x-cloak
     id="padu_log_activity_modal"
     @keydown.escape.window="closeLogConsole()"
     @click.self="closeLogConsole()"
     onclick="if(event.target === this) window.closeLogConsole()"
     class="padu-modal-backdrop p-3 sm:p-6 overflow-y-auto" 
     style="background-color: rgba(2, 6, 23, 0.88) !important; backdrop-filter: blur(8px); display: none; z-index: 999999 !important;">
    <div @click.away="closeLogConsole()" 
         class="padu-modal-card rounded-2xl shadow-2xl border w-full max-w-4xl p-5 sm:p-6 font-sans text-white my-auto flex flex-col" 
         style="background-color: #0b1329 !important; border: 1px solid #334155 !important; max-height: 88vh !important; z-index: 1000000 !important;">
        <div class="flex items-center justify-between border-b pb-3 shrink-0" style="border-color: #1e293b !important;">
            <div class="flex items-center gap-3">
                <span class="w-9 h-9 rounded-xl text-blue-400 flex items-center justify-center font-bold text-base border" style="background-color: rgba(59, 130, 246, 0.15) !important; border-color: rgba(59, 130, 246, 0.3) !important;">📋</span>
                <div>
                    <h3 class="font-extrabold text-white text-sm flex items-center gap-2">
                        Console Log Aktivitas & Monitor System (100% Realtime)
                    </h3>
                    <p class="text-[11px] font-medium" style="color: #94a3b8 !important;">Pantau proses injeksi data, agregasi DuckDB, ekspor berkas, dan log pembatalan.</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button" @click="fetchLogs()" class="px-3.5 py-1.5 hover:bg-blue-600 font-bold text-xs rounded-xl border flex items-center gap-1.5 transition-colors cursor-pointer" style="background-color: rgba(59, 130, 246, 0.2) !important; color: #93c5fd !important; border-color: rgba(59, 130, 246, 0.4) !important;">
                    <span>🔄</span> Refresh
                </button>
                <button type="button" 
                        @click="closeLogConsole()" 
                        onclick="window.closeLogConsole()"
                        title="Tutup Log Console" 
                        class="px-3 py-1.5 rounded-xl font-extrabold text-xs flex items-center gap-1.5 border transition-all cursor-pointer hover:bg-slate-700 bg-slate-800 text-slate-200 border-slate-600">
                    ✕ Tutup
                </button>
            </div>
        </div>

        <!-- Content Container Flex-1 -->
        <div class="py-3 flex-1 flex flex-col space-y-3 overflow-hidden min-h-0">
            <!-- Toolbar Control Log Console -->
            <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-xl border text-xs shrink-0" style="background-color: #020617 !important; border-color: #1e293b !important;">
                <div class="flex items-center gap-2">
                    <label for="modal_log_filter_level" class="font-bold" style="color: #cbd5e1 !important;">Filter Level:</label>
                    <select id="modal_log_filter_level" name="modal_log_filter_level" aria-label="Filter Level Log Konsol" x-model="logFilter" class="border rounded-xl px-3 py-1.5 text-xs font-bold outline-none cursor-pointer" style="background-color: #0f172a !important; color: #ffffff !important; border-color: #334155 !important;">
                        <option value="ALL">Semua Level</option>
                        <option value="INFO">INFO</option>
                        <option value="SUCCESS">SUCCESS (Sukses)</option>
                        <option value="WARNING">WARNING (Peringatan)</option>
                        <option value="CANCEL">CANCEL (Batal)</option>
                        <option value="ERROR">ERROR (Gagal)</option>
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="cancelCurrentImport()" class="px-3.5 py-1.5 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 shadow-md cursor-pointer border hover:bg-rose-700" style="background-color: #dc2626 !important; color: #ffffff !important; border-color: #ef4444 !important;">
                        <span>🚫</span> Batalkan Injeksi Aktif
                    </button>
                    <button type="button" @click="copySystemLogs()" class="px-3.5 py-1.5 font-bold text-xs rounded-xl border flex items-center gap-1.5 transition-all cursor-pointer hover:bg-slate-700" style="background-color: #1e293b !important; color: #f1f5f9 !important; border-color: #475569 !important;">
                        <span>📋</span> Salin Log
                    </button>
                    <button type="button" @click="downloadSystemLogsTxt()" class="px-3.5 py-1.5 font-bold text-xs rounded-xl border flex items-center gap-1.5 transition-all cursor-pointer hover:bg-emerald-700" style="background-color: #065f46 !important; color: #a7f3d0 !important; border-color: #047857 !important;">
                        <span>📥</span> Ekstrak Log (.txt)
                    </button>
                    <button type="button" @click="clearSystemLogs()" class="px-3.5 py-1.5 font-bold text-xs rounded-xl border flex items-center gap-1.5 transition-all cursor-pointer hover:bg-rose-900" style="background-color: #7f1d1d !important; color: #fecdd3 !important; border-color: #991b1b !important;">
                        <span>🗑️</span> Bersihkan Log
                    </button>
                </div>
            </div>

            <!-- Box Console Display Log Lines -->
            <div class="rounded-xl border p-4 font-mono text-xs overflow-y-auto space-y-1.5 flex-1 min-h-[260px] max-h-[55vh]" style="background-color: #020617 !important; border-color: #1e293b !important;">
                <template x-for="item in filteredLogs" :key="item.id">
                    <div class="flex items-start gap-2 leading-relaxed border-b pb-1" style="border-color: #0f172a !important;">
                        <span class="text-[11px] shrink-0 font-bold" style="color: #94a3b8 !important;" x-text="'[' + item.timestamp + ']'"></span>
                        
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold tracking-wide shrink-0" 
                              :class="{
                                  'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': item.level === 'SUCCESS',
                                  'bg-blue-500/20 text-blue-400 border border-blue-500/30': item.level === 'INFO',
                                  'bg-amber-500/20 text-amber-400 border border-amber-500/30': item.level === 'WARNING' || item.level === 'CANCEL',
                                  'bg-rose-500/20 text-rose-400 border border-rose-500/30': item.level === 'ERROR'
                              }" 
                              x-text="item.level"></span>
                              
                        <span class="break-words overflow-hidden" 
                              :class="{
                                  'text-emerald-300': item.level === 'SUCCESS',
                                  'text-blue-200': item.level === 'INFO',
                                  'text-amber-300': item.level === 'WARNING' || item.level === 'CANCEL',
                                  'text-rose-300': item.level === 'ERROR'
                              }" 
                              x-text="item.message"></span>
                    </div>
                </template>

                <div x-show="filteredLogs.length === 0" class="py-8 text-center font-sans text-xs font-bold" style="color: #64748b !important;">
                    Belum ada log aktivitas yang tercatat.
                </div>
            </div>
        </div>

        <!-- Footer Stats & Tombol Tutup -->
        <div class="flex flex-wrap items-center justify-between gap-3 text-xs border-t pt-3 font-bold shrink-0" style="border-color: #1e293b !important; color: #94a3b8 !important;">
            <div class="flex items-center gap-3">
                <span x-text="'Total ' + (logList ? logList.length : 0) + ' entri log tercatat'"></span>
                <span class="text-emerald-400 font-bold flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> Monitor Log Online
                </span>
            </div>
            <button type="button" 
                    @click="closeLogConsole()" 
                    onclick="window.closeLogConsole()"
                    class="px-5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-white border border-slate-600 font-extrabold text-xs transition-all cursor-pointer shadow-sm flex items-center gap-1.5">
                <span>✕</span> Tutup Jendela Log
            </button>
        </div>
    </div>
</div>
