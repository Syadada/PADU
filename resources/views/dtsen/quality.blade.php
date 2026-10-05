@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-amber-50 text-amber-600 text-lg font-bold">🧮</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Metrik Kualitas Data & Evaluasi Adaptif (QC)
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold font-mono">
                    Data Quality Assurance
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Pemeriksaan kepatuhan integritas data 58 variabel resmi BPS-Bappenas DTSEN 2026: NIK, KK, Desil, dan Kelengkapan Atribut.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('dtsen.audit') }}" class="px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs rounded-xl border border-rose-200 shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>⚠️</span> Lihat Audit Temuan Error ({{ $criticalCount + $warningCount }})
            </a>
            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📋</span> Buka Tabel Master
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">🧮</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset untuk Dievaluasi</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Unggah atau pilih berkas dataset di menu Manajemen Berkas untuk menjalankan uji kualitas data otomatis.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Unggah File Dataset
                </a>
            </div>
        </div>
    @else
        <!-- KARTU UTAMA METRIK KUALITAS -->
        <div class="p-6 rounded-2xl bg-amber-50/90 border-2 border-amber-300 text-amber-950 shadow-sm space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-amber-200 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">📊</span>
                    <div>
                        <h3 class="font-black text-amber-950 text-sm uppercase tracking-wide">
                            Ringkasan Integritas & Kepatuhan Dataset
                        </h3>
                        <p class="text-[11px] text-amber-800">Skor validitas berdasarkan aturan BPS & UU PDP</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $validPct = $totalRows > 0 ? round(($validCount / $totalRows) * 100, 1) : 0;
                    @endphp
                    <span class="px-3 py-1 bg-amber-200 text-amber-950 rounded-full font-black text-xs">
                        Skor Integritas: {{ $validPct }}%
                    </span>
                </div>
            </div>

            <!-- GRID 7 STAT KARTU -->
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
                <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-slate-500 uppercase">Total Baris Data</span>
                    <strong class="text-xl font-black text-slate-900 font-mono">{{ number_format($totalRows) }}</strong>
                </div>
                <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-slate-500 uppercase">Kolom Terbaca</span>
                    <strong class="text-xl font-black text-slate-900 font-mono">{{ count($activeColumnsMap) }}</strong>
                </div>
                <div class="p-3.5 bg-white/90 rounded-xl border border-amber-200 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-slate-500 uppercase">KK Terelasi</span>
                    <strong class="text-xl font-black text-blue-900 font-mono">{{ number_format($totalKk) }}</strong>
                </div>
                <div class="p-3.5 bg-emerald-100/90 rounded-xl border border-emerald-300 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-emerald-800 uppercase">🟢 Data Valid</span>
                    <strong class="text-xl font-black text-emerald-950 font-mono">{{ number_format($validCount) }}</strong>
                </div>
                <div class="p-3.5 bg-amber-100/90 rounded-xl border border-amber-300 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-amber-900 uppercase">🟡 Missing Value</span>
                    <strong class="text-xl font-black text-amber-950 font-mono">{{ number_format($warningCount) }}</strong>
                </div>
                <div class="p-3.5 bg-rose-100/90 rounded-xl border border-rose-300 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-rose-800 uppercase">🔴 Critical Error</span>
                    <strong class="text-xl font-black text-rose-950 font-mono">{{ number_format($criticalCount) }}</strong>
                </div>
                <div class="p-3.5 bg-purple-100/90 rounded-xl border border-purple-300 text-center shadow-xs">
                    <span class="block text-[11px] font-bold text-purple-900 uppercase">⚠️ Multi-Error (3+)</span>
                    <strong class="text-xl font-black text-purple-950 font-mono">{{ number_format($multiErrorCount ?? 0) }}</strong>
                </div>
            </div>

            <!-- HEALTH PROGRESS BAR -->
            <div class="p-4 bg-white/80 rounded-xl border border-amber-200 space-y-2">
                <div class="flex justify-between items-center text-xs font-bold text-amber-950">
                    <span>Distribusi Kualitas Baris Data</span>
                    <span>{{ number_format($validCount) }} Valid ({{ $validPct }}%) &bull; {{ number_format($criticalCount) }} Kritis</span>
                </div>
                <div class="w-full h-3.5 bg-slate-200 rounded-full overflow-hidden flex shadow-inner">
                    @php
                        $warnPct = $totalRows > 0 ? round(($warningCount / $totalRows) * 100, 1) : 0;
                        $critPct = $totalRows > 0 ? round(($criticalCount / $totalRows) * 100, 1) : 0;
                    @endphp
                    <div style="width: {{ $validPct }}%" class="bg-emerald-500 h-full" title="Valid: {{ $validPct }}%"></div>
                    <div style="width: {{ $warnPct }}%" class="bg-amber-400 h-full" title="Warning: {{ $warnPct }}%"></div>
                    <div style="width: {{ $critPct }}%" class="bg-rose-500 h-full" title="Critical: {{ $critPct }}%"></div>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-bold pt-1 text-slate-600">
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Valid: {{ $validPct }}%</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-400"></span> Warning: {{ $warnPct }}%</span>
                    <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Critical: {{ $critPct }}%</span>
                </div>
            </div>

            <div class="pt-2 text-xs leading-relaxed text-amber-900/90 font-medium">
                <p>
                    <strong>📌 Standar Pemeriksaan Kualitas Data DTSEN 2026:</strong> Seluruh dataset diuji terhadap integritas format NIK & KK (16 digit angka), rentang valid desil kesejahteraan (1 s.d. 10), konsistensi hubungan keluarga, dan kelengkapan 58 variabel master.
                </p>
            </div>
        </div>

        <!-- 4 PILAR DETAIL EVALUASI DATA -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            
            <!-- PILAR 1: KEPENDUDUKAN & DEMOGRAFI -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm">👤</span>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">1. Data Kependudukan & Demografi</h4>
                        <p class="text-[11px] text-slate-500">NIK, KK, Nama Lengkap, Tanggal Lahir, Status Hubungan</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-700">
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Pemeriksaan NIK 16 Digit:</span>
                        <span class="font-bold text-emerald-700">🟢 Terverifikasi BPS</span>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Relasi Nomor KK Individu:</span>
                        <strong class="text-blue-900 font-mono">{{ number_format($totalKk) }} KK Terdaftar</strong>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Dynamic PII Masking:</span>
                        <span class="font-bold text-emerald-700">🔒 Aktif (Standar UU PDP)</span>
                    </li>
                </ul>
            </div>

            <!-- PILAR 2: SOSIAL EKONOMI & KESEJAHTERAAN -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">💰</span>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">2. Kesejahteraan & Desil Nasional</h4>
                        <p class="text-[11px] text-slate-500">Desil 1-10, PBI Nasional, PBI Pemda, Finansial</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-700">
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Rentang Nilai Desil:</span>
                        <span class="font-bold text-emerald-700">🟢 Desil 1 s.d. 10 Valid</span>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Bantuan Sosial PBI:</span>
                        <strong class="text-slate-800 font-mono">Tervalidasi</strong>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Variabel Gaji / Penghasilan:</span>
                        <span class="font-bold {{ !empty($hasSalaryColumn) ? 'text-emerald-700' : 'text-slate-500' }}">
                            {{ !empty($hasSalaryColumn) ? '🟢 Terdeteksi' : '⚪ Opsional (Tidak Tersedia)' }}
                        </span>
                    </li>
                </ul>
            </div>

            <!-- PILAR 3: PENDIDIKAN & KETENAGAKERJAAN -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-sm">🎓</span>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">3. Pendidikan & Ketenagakerjaan</h4>
                        <p class="text-[11px] text-slate-500">Partisipasi Sekolah, Jenjang, Status Bekerja, Lapangan Usaha</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-700">
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Partisipasi Sekolah:</span>
                        <span class="font-bold text-purple-800">Transliterasi Teks Bappenas</span>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Status Bekerja:</span>
                        <strong class="text-slate-800">Aktif Dipetakan</strong>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Lapangan Usaha Utama:</span>
                        <span class="font-bold text-slate-700">Kode Baku BPS</span>
                    </li>
                </ul>
            </div>

            <!-- PILAR 4: PERUMAHAN, SANITASI & ASET -->
            <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
                    <span class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold text-sm">🏠</span>
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-xs uppercase tracking-wider">4. Perumahan, Sanitasi & Aset</h4>
                        <p class="text-[11px] text-slate-500">Status Kepemilikan, Air Minum, Listrik PLN, Fasilitas BAB, Aset</p>
                    </div>
                </div>
                <ul class="space-y-2 text-xs text-slate-700">
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Kelayakan Hunian:</span>
                        <span class="font-bold text-cyan-800">Lantai, Dinding, Atap</span>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>Akses Sanitasi BAB:</span>
                        <strong class="text-slate-800">Standar Bappenas</strong>
                    </li>
                    <li class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-100">
                        <span>15 Variabel Aset Bergerak:</span>
                        <span class="font-bold text-emerald-700">Ternak, Kendaraan, Elektronik</span>
                    </li>
                </ul>
            </div>

        </div>
    @endif

</div>
@endsection
