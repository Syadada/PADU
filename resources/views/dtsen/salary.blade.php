@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-emerald-50 text-emerald-600 text-lg font-bold">💵</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Analisis Finansial & Distribusi Gaji Subjek
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold font-mono">
                    Financial KPI Analytics
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Kalkulasi metrik Gaji Tertinggi (MAX), Terendah (MIN), Rata-rata (AVG), dan Total Agregat (SUM) dengan filter kelompok subjek.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📋</span> Buka Tabel Master Data
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">💵</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Impor berkas dataset CSV/Excel melalui menu Manajemen Berkas untuk menganalisis statistik finansial gaji.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Buka Manajemen Berkas
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
                    Berkas dataset yang dimuat tidak menyertakan kolom <code>gaji</code> atau <code>gaji_bulanan</code>. Anda dapat mengunggah berkas yang memuat variabel finansial atau menggunakan berkas sampel.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.kpi') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs inline-flex items-center gap-1.5 shadow-sm">
                    <span>📈</span> Buka Diagram KPI Variabel Lainnya
                </a>
            </div>
        </div>
    @else
        <!-- MODUL UTAMA ANALISIS FINANSIAL GAJI -->
        <div class="space-y-6">
            
            <!-- 1. FORM FILTER KONTROL METRIK GAJI KELOMPOK -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <form method="GET" action="{{ route('dtsen.salary') }}" id="salaryFilterForm" class="space-y-4">
                    @php
                        $activeSalaryFilters = [];
                        if (request('salary_search')) {
                            $activeSalaryFilters['search'] = '🔍 "' . request('salary_search') . '"';
                        }
                        foreach ($importFilterableColumns as $cK => $cT) {
                            if (request('salary_' . $cK) && request('salary_' . $cK) !== 'semua') {
                                $activeSalaryFilters[$cK] = '📌 ' . $cT . ': ' . request('salary_' . $cK);
                            }
                        }
                    @endphp

                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <div>
                            <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                                <span>🔍</span> Filter Khusus Analisis Metrik Gaji Kelompok
                            </h3>
                            <p class="text-xs text-slate-500">Tentukan kriteria kelompok subjek untuk menghitung statistik pendapatan kelompok tertentu secara presisi.</p>
                        </div>

                        @if(!empty($activeSalaryFilters))
                            <div class="flex flex-wrap items-center gap-1.5 bg-blue-50/80 p-2 rounded-xl border border-blue-200 shrink-0">
                                <span class="text-[11px] font-bold text-blue-900">Filter Aktif:</span>
                                @foreach($activeSalaryFilters as $sFKey => $sFBadge)
                                    <span class="px-2 py-0.5 bg-blue-600 text-white rounded-md text-[10px] font-bold">{{ $sFBadge }}</span>
                                @endforeach
                                <a href="{{ route('dtsen.salary') }}" class="text-[10px] font-bold text-rose-600 hover:text-rose-800 transition-colors ml-1">Reset Filter Gaji</a>
                            </div>
                        @endif
                    </div>

                    <!-- Row Filter Options Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3.5">
                        <!-- Cari Nama -->
                        <div>
                            <label class="block text-[11px] font-extrabold text-slate-700 uppercase tracking-wider mb-1">Cari Nama Subjek</label>
                            <input type="text" name="salary_search" value="{{ request('salary_search') }}" placeholder="Ketik nama subjek..." class="w-full text-xs font-medium bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <!-- Dropdowns Dinamis -->
                        @foreach($importFilterableColumns as $colKey => $colTitle)
                            @if(isset($importColumnDistinctValues[$colKey]) && count($importColumnDistinctValues[$colKey]) > 0)
                                <div>
                                    <label class="block text-[11px] font-extrabold text-blue-900 uppercase tracking-wider mb-1">{{ $colTitle }}</label>
                                    <select name="salary_{{ $colKey }}" onchange="this.form.submit()" class="w-full text-xs font-bold bg-blue-50 border border-blue-300 text-blue-900 rounded-xl px-3 py-2 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                        <option value="semua">Semua {{ $colTitle }}</option>
                                        @foreach($importColumnDistinctValues[$colKey] as $vItem)
                                            <option value="{{ $vItem }}" {{ (request('salary_' . $colKey) == $vItem) ? 'selected' : '' }}>📌 {{ $vItem }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <a href="{{ route('dtsen.salary') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all">
                            🔄 Reset Filter Gaji
                        </a>
                        <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1.5 cursor-pointer">
                            <span>💵</span> Hitung Metrik Gaji
                        </button>
                    </div>
                </form>
            </div>

            <!-- 2. KARTU METRIK 4 STATISTIK GAJI UTAMA -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="p-2 bg-emerald-100 text-emerald-800 rounded-xl text-lg font-bold">💵</span>
                            <h3 class="font-extrabold text-slate-900 text-base">Hasil Kalkulasi Statistik Pendapatan</h3>
                        </div>
                        <p class="text-xs text-slate-500 font-medium">Metrik bereaksi langsung terhadap filter kriteria kelompok yang Anda tentukan di atas.</p>
                    </div>
                </div>

                @if(!empty($isSalaryFallback))
                    <div class="p-3.5 rounded-xl border text-xs font-bold flex items-center gap-2 shadow-sm bg-amber-50 border-amber-200 text-amber-900">
                        <span class="text-base">ℹ️</span>
                        <span>Kombinasi filter spesifik tidak ditemukan pada dataset. Menampilkan kalkulasi metrik gaji keseluruhan dataset.</span>
                    </div>
                @endif

                <!-- 4 KARTU KPI GAJI HIGH-CONTRAST -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Rata-Rata Gaji -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2 bg-blue-50 border-blue-200">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-blue-900">
                            <span>📊 Gaji Rata-Rata (AVG)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-200 text-blue-950">AVG</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">Rp {{ number_format($gajiAvg, 0, ',', '.') }}</div>
                        <p class="text-xs font-medium text-blue-700">Dari {{ number_format($gajiCount) }} subjek terfilter</p>
                    </div>

                    <!-- Gaji Tertinggi -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2 bg-emerald-50 border-emerald-200">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-emerald-900">
                            <span>📈 Gaji Tertinggi (MAX)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-200 text-emerald-950">MAX</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">Rp {{ number_format($gajiMax, 0, ',', '.') }}</div>
                        <div class="text-xs font-medium truncate text-emerald-800">
                            @if($gajiMaxSubjek)
                                Subjek: <strong class="font-extrabold"><span x-show="isMasked">{{ $gajiMaxSubjek->masked_nama }}</span><span x-show="!isMasked" style="display:none;">{{ $gajiMaxSubjek->nama }}</span></strong>
                            @else
                                Subjek: <em class="text-slate-400">Belum ada data</em>
                            @endif
                        </div>
                    </div>

                    <!-- Gaji Terendah -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2 bg-amber-50 border-amber-200">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-amber-900">
                            <span>📉 Gaji Terendah (MIN)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-200 text-amber-950">MIN</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">Rp {{ number_format($gajiMin, 0, ',', '.') }}</div>
                        <div class="text-xs font-medium truncate text-amber-800">
                            @if($gajiMinSubjek)
                                Subjek: <strong class="font-extrabold"><span x-show="isMasked">{{ $gajiMinSubjek->masked_nama }}</span><span x-show="!isMasked" style="display:none;">{{ $gajiMinSubjek->nama }}</span></strong>
                            @else
                                Subjek: <em class="text-slate-400">Belum ada data</em>
                            @endif
                        </div>
                    </div>

                    <!-- Total Kumulatif Gaji -->
                    <div class="p-5 rounded-2xl border shadow-sm space-y-2 bg-purple-50 border-purple-200">
                        <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-purple-900">
                            <span>💰 Total Kumulatif (SUM)</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-purple-200 text-purple-950">SUM</span>
                        </div>
                        <div class="text-2xl sm:text-3xl font-black text-slate-900 font-mono">Rp {{ number_format($gajiSum, 0, ',', '.') }}</div>
                        <p class="text-xs font-medium text-purple-700">Total seluruh pendapatan terfilter</p>
                    </div>
                </div>
            </div>

            <!-- 3. TOP 5 & BOTTOM 5 PENERIMA PENGHASILAN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- TOP 5 GAJI -->
                <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <h4 class="font-extrabold text-xs text-slate-900 uppercase flex items-center gap-2">
                            <span>🏆</span> 5 Subjek Penghasilan Tertinggi
                        </h4>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">TOP EARNERS</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($top5Records as $idx => $r)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            <span x-show="isMasked">{{ $r->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $r->nama }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            <span x-show="isMasked">{{ $r->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $r->nomor_induk_kependudukan }}</span>
                                        </div>
                                    </div>
                                </div>
                                <strong class="font-mono text-emerald-700 font-bold">
                                    Rp {{ number_format((float)($r->val ?? 0), 0, ',', '.') }}
                                </strong>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada data gaji.</p>
                        @endforelse
                    </div>
                </div>

                <!-- BOTTOM 5 GAJI -->
                <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                        <h4 class="font-extrabold text-xs text-slate-900 uppercase flex items-center gap-2">
                            <span>📉</span> 5 Subjek Penghasilan Terendah
                        </h4>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded">LOW EARNERS</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($bottom5Records as $idx => $r)
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between text-xs">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-slate-900">
                                            <span x-show="isMasked">{{ $r->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $r->nama }}</span>
                                        </div>
                                        <div class="text-[10px] text-slate-400 font-mono">
                                            <span x-show="isMasked">{{ $r->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $r->nomor_induk_kependudukan }}</span>
                                        </div>
                                    </div>
                                </div>
                                <strong class="font-mono text-amber-700 font-bold">
                                    Rp {{ number_format((float)($r->val ?? 0), 0, ',', '.') }}
                                </strong>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada data gaji.</p>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
