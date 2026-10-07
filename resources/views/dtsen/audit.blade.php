@extends('layouts.app')

@section('content')
@php
    $currStatus = request('quality_status', $currentQualityStatus ?? 'error');
    $totalErrors = $criticalCount + $warningCount;
@endphp
<div class="space-y-6" x-data="{ isMasked: true }">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600 text-lg font-bold">⚠️</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Tabel Data Temuan Error & Anomali
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold font-mono">
                    {{ number_format($totalErrors) }} Data Bermasalah
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Daftar baris data kependudukan yang mengandung anomali (NIK/KK tidak valid, desil cacat, tanggal lahir salah). Data ini tidak boleh diajukan bansos sebelum diverifikasi.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Unduh Laporan Error CSV -->
            <a href="{{ route('dtsen.export-errors') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📥</span> Unduh Laporan Error (.CSV)
            </a>
            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📋</span> Buka Master Data Lengkap
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong (Belum Ada Data) -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-rose-50 text-rose-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">⚠️</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Impor berkas dataset CSV/Excel terlebih dahulu untuk memindai temuan anomali dan error data.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Buka Manajemen Berkas
                </a>
            </div>
        </div>
    @elseif($totalErrors === 0)
        <!-- Keadaan Sempurna (0 Eror) -->
        <div class="p-12 bg-white rounded-2xl border border-emerald-200 text-center space-y-4 shadow-xs">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">🎉</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-emerald-950 text-base">Luar Biasa! Tidak Ditemukan Error Data</h3>
                <p class="text-xs text-emerald-700 max-w-md mx-auto">
                    Seluruh <strong>{{ number_format($totalRows) }} baris data</strong> dinyatakan 🟢 <strong>100% Valid</strong> dan memenuhi standar integritas BPS-Bappenas DTSEN 2026.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.data') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>📋</span> Jelajahi Tabel Master Data
                </a>
            </div>
        </div>
    @else
        <!-- CONTAINER DATA TABLE TEMUAN ERROR -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
            
            <!-- TOOLBAR FILTER & PENCARIAN -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                
                <!-- Filter Status Pill Buttons -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tingkat Masalah:</span>
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                        <a href="{{ route('dtsen.audit', array_merge(request()->except(['page', 'quality_status']), ['quality_status' => 'error'])) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition-all {{ in_array($currStatus, ['error', 'semua', 'issues']) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            Semua Error ({{ number_format($totalErrors) }})
                        </a>
                        <a href="{{ route('dtsen.audit', array_merge(request()->except(['page', 'quality_status']), ['quality_status' => 'Critical'])) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition-all {{ $currStatus === 'Critical' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            🔴 Critical ({{ number_format($criticalCount) }})
                        </a>
                        <a href="{{ route('dtsen.audit', array_merge(request()->except(['page', 'quality_status']), ['quality_status' => 'Warning'])) }}" 
                           class="px-3 py-1.5 rounded-lg text-xs font-extrabold transition-all {{ $currStatus === 'Warning' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                            🟡 Warning ({{ number_format($warningCount) }})
                        </a>
                    </div>
                </div>

                <!-- Form Pencarian & Opsi Tampilan -->
                <div class="flex flex-wrap items-center gap-2.5">
                    <!-- Form Search -->
                    <form method="GET" action="{{ route('dtsen.audit') }}" class="flex items-center gap-1.5">
                        <input type="hidden" name="quality_status" value="{{ $currStatus }}">
                        <div class="flex items-center bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 focus-within:ring-2 focus-within:ring-blue-500 focus-within:bg-white transition-all shadow-sm">
                            <span class="text-slate-400 text-xs mr-2 shrink-0 select-none">🔍</span>
                            <input type="text" 
                                   name="search" 
                                   id="audit_search_input"
                                   aria-label="Cari NIK, KK, atau Nama"
                                   value="{{ request('search') }}" 
                                   placeholder="Cari NIK, KK, atau Nama..." 
                                   class="text-xs bg-transparent border-0 p-0 outline-none w-44 sm:w-60 text-slate-800 placeholder-slate-400">
                        </div>
                        <button type="submit" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-xl shadow-sm transition-all cursor-pointer">
                            Cari
                        </button>
                        @if(request('search'))
                            <a href="{{ route('dtsen.audit', ['quality_status' => $currStatus]) }}" class="px-2.5 py-1.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-bold text-xs rounded-xl transition-all cursor-pointer">
                                Reset
                            </a>
                        @endif
                    </form>

                    <!-- Sensor / Masking Toggle -->
                    <button type="button" 
                            @click="isMasked = !isMasked" 
                            class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                        <span x-text="isMasked ? '👁️ Buka Sensor' : '🔒 Sensor NIK/Nama'">🔒 Sensor NIK/Nama</span>
                    </button>
                </div>
            </div>

            <!-- DATA TABLE TEMUAN ERROR -->
            <div class="overflow-x-auto rounded-xl border border-slate-200 shadow-sm">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-100/80 border-b border-slate-200 text-slate-700 font-extrabold text-[11px] uppercase tracking-wider">
                            <th class="py-3 px-3 text-center w-12">No</th>
                            <th class="py-3 px-3 text-center w-28">Status QC</th>
                            <th class="py-3 px-4 min-w-[160px]">Nama Subjek</th>
                            <th class="py-3 px-4 min-w-[150px]">NIK</th>
                            <th class="py-3 px-4 min-w-[150px]">Nomor KK</th>
                            <th class="py-3 px-3 text-center w-20">Desil</th>
                            <th class="py-3 px-4 min-w-[280px]">Rincian Temuan Masalah</th>
                            <th class="py-3 px-3 text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($records as $index => $row)
                            @php
                                $isCrit = ($row->quality_status === 'Critical');
                                $rowBg = $isCrit ? 'hover:bg-rose-50/40' : 'hover:bg-amber-50/40';
                                
                                $issues = $row->quality_issues ?? [];
                                if (is_string($issues)) {
                                    $issues = json_decode($issues, true) ?: [$issues];
                                }
                                $issues = is_array($issues) ? $issues : [];

                                $desilVal = $row->desil ?? $row->desil_nasional ?? '-';
                            @endphp
                            <tr class="{{ $rowBg }} transition-colors">
                                <!-- Nomor Urut -->
                                <td class="py-3 px-3 text-center font-mono text-slate-400 font-bold">
                                    {{ ($records->currentPage() - 1) * $records->perPage() + $loop->iteration }}
                                </td>

                                <!-- Status QC Badge -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    @if($isCrit)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-100 text-rose-800 border border-rose-300">
                                            🔴 CRITICAL
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-300">
                                            🟡 WARNING
                                        </span>
                                    @endif
                                </td>

                                <!-- Nama Lengkap -->
                                <td class="py-3 px-4 font-bold text-slate-900">
                                    <span x-show="isMasked">{{ $row->masked_nama }}</span>
                                    <span x-show="!isMasked" style="display:none;">{{ $row->nama ?? $row->nama_lengkap ?? '-' }}</span>
                                </td>

                                <!-- NIK -->
                                <td class="py-3 px-4 font-mono font-medium text-slate-700 whitespace-nowrap">
                                    <span x-show="isMasked">{{ $row->masked_nik }}</span>
                                    <span x-show="!isMasked" style="display:none;">{{ $row->nomor_induk_kependudukan ?? $row->nik ?? '-' }}</span>
                                </td>

                                <!-- Nomor KK -->
                                <td class="py-3 px-4 font-mono font-medium text-slate-700 whitespace-nowrap">
                                    <span x-show="isMasked">{{ $row->masked_kk }}</span>
                                    <span x-show="!isMasked" style="display:none;">{{ $row->nomor_kartu_keluarga ?? $row->no_kk ?? '-' }}</span>
                                </td>

                                <!-- Desil -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 border border-slate-200 text-slate-800 font-bold text-[11px]">
                                        {{ is_numeric($desilVal) ? 'Desil ' . $desilVal : $desilVal }}
                                    </span>
                                </td>

                                <!-- Rincian Temuan Masalah -->
                                <td class="py-3 px-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse($issues as $iss)
                                            @if(!empty($iss))
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium {{ $isCrit ? 'bg-rose-50 text-rose-800 border border-rose-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                                                    <span>⚠️</span> {{ $iss }}
                                                </span>
                                            @endif
                                        @empty
                                            <span class="text-slate-400 italic text-[11px]">Pemeriksaan integritas data baris.</span>
                                        @endforelse
                                    </div>
                                </td>

                                <!-- Aksi Inspeksi Detail -->
                                <td class="py-3 px-3 text-center whitespace-nowrap">
                                    <button type="button" 
                                            @click="openPreview({{ $row->id ?? 0 }})" 
                                            class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold text-[11px] rounded-lg shadow-2xs cursor-pointer inline-flex items-center gap-1 transition-all">
                                        <span>👁️</span> Inspeksi
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="text-3xl mb-2">🔍</div>
                                    <p class="font-bold text-slate-700 text-sm">Tidak Ada Baris Error yang Sesuai dengan Kriteria</p>
                                    <p class="text-xs text-slate-500 mt-1">Coba sesuaikan kata kunci pencarian atau ubah filter tingkat masalah di atas.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION FOOTER -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-600">
                <div>
                    Menampilkan <strong>{{ $records->firstItem() ?? 0 }}</strong> s/d <strong>{{ $records->lastItem() ?? 0 }}</strong> dari <strong>{{ number_format($records->total()) }}</strong> temuan error
                </div>
                <div>
                    {{ $records->appends(request()->query())->onEachSide(1)->links() }}
                </div>
            </div>

            <!-- KETERANGAN & PANDUAN PENANGANAN DATA ERROR (RAMAH ORANG GAPTEK) -->
            <div class="mt-4 pt-4 border-t border-slate-100 space-y-3">
                <div class="p-4 rounded-xl border border-rose-100 bg-gradient-to-r from-rose-50/70 via-slate-50 to-amber-50/70 space-y-2.5">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-rose-600 text-white text-xs font-black shadow-xs">📝</span>
                            <span class="text-xs font-black uppercase tracking-wider text-rose-950">
                                Keterangan & Panduan Penanganan Data Error
                            </span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-[10px] font-extrabold font-mono border border-rose-200">
                            ⚠️ Wajib Ditindaklanjuti
                        </span>
                    </div>

                    <div class="p-3 bg-white/95 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-2 leading-relaxed">
                        <p class="flex items-start gap-1.5">
                            <span class="text-rose-600 font-bold shrink-0">📌</span>
                            <span>
                                <strong>Apa Arti Data di Tabel Ini?</strong> Tabel ini khusus menyaring data warga yang memiliki cacat administrasi, seperti <u>nomor NIK/KK kurang dari 16 angka, nomor KK memuat desimal/titik, desil di luar rentang 1–10, atau nama memuat simbol</u>. Data berstatus 🔴 <strong>Critical</strong> <strong>wajib ditahan dan tidak boleh diajukan untuk pencairan bansos</strong> agar bantuan tidak gagal transfer atau menyalahi audit BPK.
                            </span>
                        </p>
                        <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs space-y-1">
                            <div class="font-extrabold flex items-center gap-1.5 text-emerald-900">
                                <span>💡</span> Solusi & Langkah Perbaikan Mudah:
                            </div>
                            <ul class="list-disc list-inside space-y-0.5 text-[11px] text-emerald-900 font-medium pl-1">
                                <li><strong>Klik Tombol "👁️ Inspeksi":</strong> Tekan tombol inspeksi pada baris bersangkutan untuk melihat seluruh isi kolom dan menemukan letak cacat datanya.</li>
                                <li><strong>Cocokkan Dokumen Fisik:</strong> Jika nomor KK memuat desimal (contoh: <code>.5</code> akibat salah format Excel), buka kartu keluarga fisik warga untuk mengoreksi angka aslinya.</li>
                                <li><strong>Unduh Laporan Error (.CSV):</strong> Klik tombol hitam di kanan atas untuk mencetak atau mendownload daftar warga error ini agar petugas verifikator lapangan dapat melakukan cek pintu-ke-pintu (verval).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
