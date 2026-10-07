@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="metadataTrainingPage()">

    <!-- Header Section (Glassmorphism) -->
    <div class="glass-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-purple-500/15 text-purple-800 border border-purple-400/30">
                    👑 Khusus Super Administrator
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-blue-500/15 text-blue-800 border border-blue-400/30">
                    📋 BAST DTSEN Versi 3/2026
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-emerald-500/15 text-emerald-800 border border-emerald-400/30">
                    ⚡ 100 Standar Variabel Terverifikasi
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                <span>Kamus Metadata & Engine Quality Check DTSEN</span>
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Pusat standarisasi variabel BAST Kemensos & Bappenas, audit konsistensi data otomatis, dan portal pelatihan skema baru jika diterbitkan revisi metadata.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="#simulator-section" class="px-4 py-2.5 rounded-xl bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs border border-purple-200 transition-all flex items-center gap-1.5 shadow-xs">
                <span>🔬</span> Uji Simulator QC
            </a>
            <a href="#training-section" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <span>📥</span> Latih Versi Baru
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl border border-emerald-300 text-emerald-900 text-xs font-semibold flex items-center gap-2 shadow-sm"
             style="background: rgba(209, 250, 229, 0.9);">
            <span class="text-base">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-xl border border-rose-300 text-rose-900 text-xs font-semibold flex items-center gap-2 shadow-sm"
             style="background: rgba(254, 226, 226, 0.9);">
            <span class="text-base">✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <!-- 1. KARTU STATISTIK & STATUS METADATA BAST AKTIF -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Versi Terdaftar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Versi Metadata Aktif</span>
                <span class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-xs">📑</span>
            </div>
            <div class="text-lg font-black text-slate-900 tracking-tight">{{ $versionInfo['version'] }}</div>
            <div class="text-[11px] text-slate-500 font-medium truncate" title="{{ $versionInfo['title'] }}">
                {{ $versionInfo['title'] }}
            </div>
        </div>

        <!-- Total Variabel -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Total Variabel Standar</span>
                <span class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-xs">📊</span>
            </div>
            <div class="text-lg font-black text-slate-900 tracking-tight">{{ $versionInfo['total_variables'] }} Variabel</div>
            <div class="text-[11px] text-blue-700 font-semibold flex items-center gap-1.5">
                <span>{{ $versionInfo['total_keluarga'] }} Keluarga</span>
                <span>•</span>
                <span>{{ $versionInfo['total_anggota'] }} Anggota</span>
            </div>
        </div>

        <!-- Status Engine Kompilasi -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Engine Quality Check</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-xs">⚡</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-lg font-black text-emerald-700 tracking-tight">Aktif & Siap</span>
            </div>
            <div class="text-[11px] text-slate-500 font-medium">
                Kamus Aturan: {{ $versionInfo['rules_file_size'] }}
            </div>
        </div>

        <!-- Terakhir Dilatih -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Waktu Terakhir Dilatih</span>
                <span class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs">🕒</span>
            </div>
            <div class="text-sm font-black text-slate-900 truncate tracking-tight">{{ $versionInfo['last_trained_at'] }}</div>
            <div class="text-[11px] text-amber-700 font-medium">
                Pembaruan otomatis real-time
            </div>
        </div>
    </div>

    <!-- 2. SECTION PELATIHAN & UNGGAH VERSI METADATA TERBARU (SUPER ADMIN ONLY) -->
    <div id="training-section" class="bg-white rounded-2xl border border-purple-200 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-purple-100 pb-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-black text-lg shrink-0">
                    🚀
                </span>
                <div>
                    <h2 class="text-base font-black text-slate-900">
                        Input & Pelatihan Metadata BAST Versi Baru
                    </h2>
                    <p class="text-xs text-slate-500 font-medium">
                        Jika Bappenas atau Kemensos menerbitkan BAST versi 4 atau addendum baru, unggah file Excel resminya di sini untuk melatih mesin secara instan.
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-purple-50 text-purple-700 border border-purple-200 self-start sm:self-auto uppercase tracking-wider">
                Otomatisasi Parser Python
            </span>
        </div>

        <form action="{{ route('admin.metadata.train') }}" method="POST" enctype="multipart/form-data" class="space-y-4" onsubmit="return confirm('⚠️ Anda akan melatih ulang kamus aturan BAST sistem dengan berkas baru ini. Lanjutkan?');">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- File Input -->
                <div class="space-y-1.5">
                    <label for="metadata_file" class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Pilih Berkas Lampiran BAST Metadata (.xlsx / .xls) <span class="text-rose-500">*</span>
                    </label>
                    <input type="file" id="metadata_file" name="metadata_file" aria-label="Pilih Berkas Lampiran BAST Metadata" required accept=".xlsx,.xls"
                           class="w-full text-xs text-slate-700 file:mr-3 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-600 file:text-white hover:file:bg-purple-700 file:cursor-pointer p-1.5 border border-slate-300 rounded-xl bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <p class="text-[11px] text-slate-500">
                        Pastikan berkas memiliki lembar <code class="font-mono font-bold text-slate-700">Set Data Keluarga</code> dan <code class="font-mono font-bold text-slate-700">Set Data Anggota Keluarga</code>.
                    </p>
                </div>

                <!-- Version Note Input -->
                <div class="space-y-1.5">
                    <label for="metadata_version_note" class="block text-xs font-black uppercase tracking-wider text-slate-700">
                        Catatan Label Versi (Opsional)
                    </label>
                    <input type="text" id="metadata_version_note" name="version_note" aria-label="Catatan Label Versi" placeholder="Contoh: Versi 3.1 Revisi Kemensos Oktober 2026"
                           class="w-full text-xs px-3.5 py-2.5 rounded-xl border border-slate-300 bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                    <p class="text-[11px] text-slate-500">
                        Jika dikosongkan, versi akan diekstrak otomatis dari judul dokumen di lembar Excel Rekap.
                    </p>
                </div>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-3 bg-purple-50/50 p-3.5 rounded-xl border border-purple-100">
                <div class="text-[11px] text-purple-900 font-medium flex items-center gap-2">
                    <span>💡</span>
                    <span>Proses pelatihan memakan waktu ~1 detik dan akan mengkompilasi batasan tipe data, panjang string, dan opsi kategori.</span>
                </div>
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer shrink-0">
                    <span>⚡</span> Latih & Terapkan Skema Baru
                </button>
            </div>
        </form>
    </div>

    <!-- 3. KAMUS DATA & DAFTAR 100 VARIABEL BAST -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden space-y-4 p-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
            <div>
                <h3 class="text-sm font-black uppercase tracking-wider text-slate-900 flex items-center gap-2">
                    <span>📖</span> Kamus Data Resmi BAST DTSEN ({{ count($displayedVars) }} Variabel)
                </h3>
                <p class="text-xs text-slate-500 font-medium">
                    Daftar seluruh variabel standar, tipe data acuan BPS, serta ketentuan kode klasifikasi.
                </p>
            </div>

            <!-- Filter Tabs & Pencarian -->
            <div class="flex flex-wrap items-center gap-2">
                <div class="inline-flex p-1 bg-slate-100 rounded-xl border border-slate-200 text-xs font-bold">
                    <a href="{{ route('admin.metadata', ['dataset' => 'all', 'search' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $filterDataset === 'all' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Semua (100)
                    </a>
                    <a href="{{ route('admin.metadata', ['dataset' => 'keluarga', 'search' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $filterDataset === 'keluarga' ? 'bg-white text-purple-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Keluarga ({{ count($keluargaVars) }})
                    </a>
                    <a href="{{ route('admin.metadata', ['dataset' => 'anggota', 'search' => $search]) }}"
                       class="px-3 py-1.5 rounded-lg transition-all {{ $filterDataset === 'anggota' ? 'bg-white text-blue-700 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        Anggota ({{ count($anggotaVars) }})
                    </a>
                </div>

                <!-- Search form -->
                <form action="{{ route('admin.metadata') }}" method="GET" class="flex items-center gap-1.5">
                    <input type="hidden" name="dataset" value="{{ $filterDataset }}">
                    <input type="text" name="search" value="{{ $search }}" autocomplete="off" placeholder="Cari variabel / kolom..."
                           class="text-xs px-3 py-1.5 rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-purple-500 w-44">
                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-300 cursor-pointer">
                        🔍
                    </button>
                    @if(!empty($search))
                        <a href="{{ route('admin.metadata', ['dataset' => $filterDataset]) }}" class="px-2 py-1.5 text-xs text-rose-600 font-bold hover:underline">
                            Reset
                        </a>
                    @endif
                </form>
            </div>
        </div>

        <!-- Tabel Variabel -->
        <div class="overflow-x-auto rounded-xl border border-slate-200 max-h-[500px] overflow-y-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 sticky top-0 border-b border-slate-200 text-slate-700 font-black uppercase text-[10px] tracking-wider z-10">
                    <tr>
                        <th class="py-3 px-3 w-12 text-center">No</th>
                        <th class="py-3 px-3">Kolom (Key)</th>
                        <th class="py-3 px-3">Label Resmi</th>
                        <th class="py-3 px-3 w-28">Tipe Data</th>
                        <th class="py-3 px-3">Definisi Operasional</th>
                        <th class="py-3 px-3 w-72">Ketentuan / Kode Isian</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($displayedVars as $index => $var)
                        <tr class="hover:bg-purple-50/30 transition-all">
                            <td class="py-2.5 px-3 text-center font-bold text-slate-400">
                                {{ $var['no'] ?? ($index + 1) }}
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="font-mono font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                                    {{ $var['key'] }}
                                </span>
                                @if(isset($var['group']))
                                    <div class="text-[9px] text-slate-400 font-semibold mt-0.5">
                                        {{ $var['group'] }}
                                    </div>
                                @endif
                            </td>
                            <td class="py-2.5 px-3 font-bold text-slate-900">
                                {{ $var['label'] }}
                            </td>
                            <td class="py-2.5 px-3">
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 text-slate-800 border border-slate-200">
                                    {{ $var['datatype'] }}
                                </span>
                            </td>
                            <td class="py-2.5 px-3 text-slate-600 max-w-xs text-[11px] leading-relaxed">
                                {{ $var['definition'] ?: '-' }}
                            </td>
                            <td class="py-2.5 px-3 text-[11px] text-slate-600 max-w-sm">
                                @if(!empty($var['code_list']))
                                    <div class="space-y-1">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach(array_slice($var['code_list'], 0, 4) as $code => $label)
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-emerald-50 text-emerald-800 border border-emerald-200 text-[10px] font-semibold">
                                                    <strong>{{ $code }}:</strong> {{ Str::limit($label, 18) }}
                                                </span>
                                            @endforeach
                                            @if(count($var['code_list']) > 4)
                                                <span class="text-[10px] text-slate-400 font-bold self-center">
                                                    +{{ count($var['code_list']) - 4 }} opsi lainnya
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @elseif(!empty($var['keterangan']))
                                    <span class="text-slate-500 font-mono text-[10px]">{{ Str::limit($var['keterangan'], 60) }}</span>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-500">
                                Tidak ada variabel yang cocok dengan kriteria pencarian "{{ $search }}".
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- 4. QUALITY CHECK PLAYGROUND / SIMULATOR REALTIME -->
    <div id="simulator-section" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-black text-lg shrink-0">
                    🔬
                </span>
                <div>
                    <h3 class="text-base font-black text-slate-900">
                        Simulator Pengecekan Kualitas Data (Quality Check Playground)
                    </h3>
                    <p class="text-xs text-slate-500 font-medium">
                        Uji validitas nilai baris data berdasarkan kamus BAST 100 variabel sebelum diinjeksi ke dataset master.
                    </p>
                </div>
            </div>
            <button type="button" @click="loadSampleData()" class="px-3.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs border border-slate-300 cursor-pointer">
                🧪 Muat Sampel Uji Coba
            </button>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Form Uji Coba Kolom Kunci -->
            <div class="space-y-4">
                <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                    <span>📝</span> Parameter Masukan Baris Data:
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label for="sample_id_keluarga" class="block font-bold text-slate-700 mb-1">id_keluarga (String 16)</label>
                        <input type="text" id="sample_id_keluarga" name="sample_id_keluarga" aria-label="id_keluarga String 16" x-model="sample.id_keluarga" placeholder="Contoh: 3201010001000001"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="sample_nik" class="block font-bold text-slate-700 mb-1">nik (String 16 Angka)</label>
                        <input type="text" id="sample_nik" name="sample_nik" aria-label="nik String 16 Angka" x-model="sample.nik" placeholder="Contoh: 3201012304950001"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="sample_desil_nasional" class="block font-bold text-slate-700 mb-1">desil_nasional (Integer 1-10)</label>
                        <input type="number" id="sample_desil_nasional" name="sample_desil_nasional" aria-label="desil_nasional Integer 1-10" x-model="sample.desil_nasional" min="1" max="10" placeholder="1 s/d 10"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="sample_jenis_kelamin" class="block font-bold text-slate-700 mb-1">jenis_kelamin (1=L, 2=P)</label>
                        <input type="text" id="sample_jenis_kelamin" name="sample_jenis_kelamin" aria-label="jenis_kelamin 1=L, 2=P" x-model="sample.jenis_kelamin" placeholder="1 atau 2 / Laki-laki"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="sample_status_bekerja" class="block font-bold text-slate-700 mb-1">status_bekerja (1=Ya, 2=Tidak)</label>
                        <input type="text" id="sample_status_bekerja" name="sample_status_bekerja" aria-label="status_bekerja 1=Ya, 2=Tidak" x-model="sample.status_bekerja" placeholder="1 atau 2"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                    <div>
                        <label for="sample_gaji" class="block font-bold text-slate-700 mb-1">gaji / gaji_bulanan (Rupiah)</label>
                        <input type="number" id="sample_gaji" name="sample_gaji" aria-label="gaji atau gaji bulanan Rupiah" x-model="sample.gaji" placeholder="Contoh: 3500000"
                               class="w-full px-3 py-2 rounded-xl border border-slate-300 font-mono text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" @click="runSimulation()" :disabled="simulating"
                            class="w-full py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <span x-show="!simulating">⚡ Jalankan Evaluasi Quality Check BAST</span>
                        <span x-show="simulating">Memeriksa terhadap 100 aturan...</span>
                    </button>
                </div>
            </div>

            <!-- Panel Hasil Evaluasi Realtime -->
            <div class="space-y-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                    <span>📊 Hasil Evaluasi Engine BAST:</span>
                    <span x-show="result" class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase"
                          :class="result?.valid ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-rose-100 text-rose-800 border border-rose-300'"
                          x-text="result?.status_label"></span>
                </div>

                <!-- Template Placeholder -->
                <div x-show="!result" class="text-center py-12 text-slate-400 space-y-2">
                    <div class="text-3xl">🔍</div>
                    <div class="text-xs font-semibold">Klik "Jalankan Evaluasi" untuk melihat diagnosa integritas baris.</div>
                </div>

                <!-- Hasil Diagnosis Nyata -->
                <div x-show="result" class="space-y-3" style="display: none;">
                    <div class="grid grid-cols-3 gap-2 text-center">
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Variabel Dicek</div>
                            <div class="text-base font-black text-slate-900" x-text="result?.checked_count"></div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Temuan Kritis</div>
                            <div class="text-base font-black text-rose-600" x-text="result?.critical_count"></div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-white border border-slate-200 shadow-xs">
                            <div class="text-[10px] font-bold text-slate-400 uppercase">Peringatan</div>
                            <div class="text-base font-black text-amber-600" x-text="result?.warning_count"></div>
                        </div>
                    </div>

                    <!-- Issues List -->
                    <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                        <template x-if="result?.issues?.length === 0">
                            <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                                <span>✅</span> Seluruh nilai variabel 100% patuh terhadap standar BAST Metadata DTSEN!
                            </div>
                        </template>

                        <template x-for="(issue, idx) in result?.issues" :key="idx">
                            <div class="p-3 rounded-xl border text-xs space-y-1"
                                 :class="issue.level === 'CRITICAL' ? 'bg-rose-50 border-rose-200 text-rose-900' : 'bg-amber-50 border-amber-200 text-amber-900'">
                                <div class="flex items-center justify-between font-bold">
                                    <span class="font-mono text-[11px]" x-text="issue.field"></span>
                                    <span class="text-[9px] uppercase px-1.5 py-0.5 rounded font-black"
                                          :class="issue.level === 'CRITICAL' ? 'bg-rose-200 text-rose-800' : 'bg-amber-200 text-amber-800'"
                                          x-text="issue.level"></span>
                                </div>
                                <div class="text-[11px]" x-text="issue.message"></div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function metadataTrainingPage() {
    return {
        sample: {
            id_keluarga: '3201010001000001',
            nik: '3201012304950001',
            desil_nasional: '3',
            jenis_kelamin: '1',
            status_bekerja: '1',
            gaji: '4500000'
        },
        simulating: false,
        result: null,

        loadSampleData() {
            this.sample = {
                id_keluarga: '3201010001000001',
                nik: '3201012304950001',
                desil_nasional: '3',
                jenis_kelamin: '1',
                status_bekerja: '1',
                gaji: '4500000'
            };
            this.runSimulation();
        },

        async runSimulation() {
            this.simulating = true;
            try {
                const response = await fetch("{{ route('admin.metadata.simulate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.sample)
                });
                const data = await response.json();
                if (data.success) {
                    this.result = data.evaluation;
                }
            } catch (err) {
                alert('Gagal mengeksekusi simulator: ' + err.message);
            } finally {
                this.simulating = false;
            }
        }
    }
}
</script>
@endsection
