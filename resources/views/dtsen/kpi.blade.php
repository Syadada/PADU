@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 text-lg font-bold">📈</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Diagram KPI & Peringkat Variabel DTSEN
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold font-mono">
                    Top / Bottom Rankings
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Peringkat ekstrem nilai variabel numerik & kategorikal: analisis outlier, sebaran distribusi, dan pemeringkatan subjek.
            </p>
        </div>

        <!-- Form Pemilih Variabel Target KPI -->
        <form method="GET" action="{{ route('dtsen.kpi') }}" id="kpiVarForm" class="flex items-center gap-2 shrink-0">
            <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Variabel Target:</label>
            <select name="kpi_var" onchange="this.form.submit()" class="text-xs font-extrabold bg-blue-50 border border-blue-300 rounded-xl px-3 py-2 text-blue-900 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm">
                @foreach($activeColumnsMap as $key => $title)
                    <option value="{{ $key }}" {{ $kpiTargetVar === $key ? 'selected' : '' }}>{{ $title }}</option>
                @endforeach
            </select>
        </form>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">📈</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Impor berkas data terlebih dahulu untuk menghitung statistik metrik KPI dan diagram peringkat.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Buka Manajemen Berkas
                </a>
            </div>
        </div>
    @else
        <!-- CONTAINER UTAMA DIAGRAM KPI -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-6">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <span>🎯</span> Target Analisis: <span class="text-blue-700 font-extrabold">{{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }}</span>
                    </h3>
                    <p class="text-xs text-slate-500">Kalkulasi matematis agregat cepat melalui mesin komputasi DuckDB.</p>
                </div>
                <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 text-[11px] font-bold font-mono">
                    {{ number_format($totalRows) }} Baris Dianalisis
                </span>
            </div>

            <!-- CARDS METRIK 4 STATISTIK UTAMA -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl text-white shadow-sm space-y-1" style="background: linear-gradient(135deg, #059669 0%, #0f766e 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-100">Nilai Maksimum (MAX)</span>
                    <div class="text-2xl font-black text-white font-mono">{{ is_numeric($kpiMax) ? number_format($kpiMax, (floor($kpiMax) == $kpiMax ? 0 : 2), ',', '.') : $kpiMax }}</div>
                    <p class="text-[11px] font-bold text-emerald-100">Nilai puncak tertinggi dari dataset</p>
                </div>

                <div class="p-4 rounded-xl text-white shadow-sm space-y-1" style="background: linear-gradient(135deg, #2563eb 0%, #4338ca 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-blue-100">Rata-Rata (AVG)</span>
                    <div class="text-2xl font-black text-white font-mono">{{ number_format($kpiAvg, 2, ',', '.') }}</div>
                    <p class="text-[11px] font-bold text-blue-100">Nilai rata-rata dari seluruh baris</p>
                </div>

                <div class="p-4 rounded-xl text-white shadow-sm space-y-1" style="background: linear-gradient(135deg, #d97706 0%, #c2410c 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-100">Nilai Minimum (MIN)</span>
                    <div class="text-2xl font-black text-white font-mono">{{ is_numeric($kpiMin) ? number_format($kpiMin, (floor($kpiMin) == $kpiMin ? 0 : 2), ',', '.') : $kpiMin }}</div>
                    <p class="text-[11px] font-bold text-amber-100">Nilai dasar terendah dari dataset</p>
                </div>

                <div class="p-4 rounded-xl text-white shadow-sm space-y-1" style="background: linear-gradient(135deg, #7c3aed 0%, #1e293b 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-purple-100">Total Akumulasi (SUM)</span>
                    <div class="text-2xl font-black text-white font-mono">{{ is_numeric($kpiSum) ? number_format($kpiSum, (floor($kpiSum) == $kpiSum ? 0 : 2), ',', '.') : $kpiSum }}</div>
                    <p class="text-[11px] font-bold text-purple-100">Akumulasi total keseluruhan nilai</p>
                </div>
            </div>

            <!-- TOP 5 & BOTTOM 5 SIDE-BY-SIDE WIDGETS -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                
                <!-- TOP 5 WIDGET -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="font-extrabold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                            <span>🏆</span> Top 5 Peringkat Teratas (Maksimum)
                        </h4>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-bold rounded">5 TERATAS</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($top5Records as $index => $rec)
                            @php
                                $displayVal = $rec->val ?? $rec->$kpiTargetVar ?? 0;
                                $numVal = is_numeric($displayVal) ? (float)$displayVal : 0;
                                $maxRef = max(1, is_numeric($kpiMax) ? (float)$kpiMax : 1);
                                $barWidth = min(100, max(5, round(($numVal / $maxRef) * 100)));
                            @endphp
                            <div class="p-2.5 rounded-lg bg-white border border-slate-200 shadow-2xs space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-[10px]">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="font-bold text-slate-800">
                                            <span x-show="isMasked">{{ $rec->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nama }}</span>
                                        </span>
                                        <span class="text-slate-400 font-mono text-[10px]">
                                            <span x-show="isMasked">{{ $rec->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nomor_induk_kependudukan }}</span>
                                        </span>
                                    </div>
                                    <strong class="font-mono text-emerald-700 font-bold">
                                        {{ is_numeric($displayVal) ? number_format((float)$displayVal, (floor((float)$displayVal) == (float)$displayVal ? 0 : 2), ',', '.') : $displayVal }}
                                    </strong>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada data untuk diperingkatkan.</p>
                        @endforelse
                    </div>
                </div>

                <!-- BOTTOM 5 WIDGET -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <h4 class="font-extrabold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                            <span>📉</span> 5 Peringkat Terbawah (Minimum)
                        </h4>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-bold rounded">5 TERBAWAH</span>
                    </div>

                    <div class="space-y-2.5">
                        @forelse($bottom5Records as $index => $rec)
                            @php
                                $displayVal = $rec->val ?? $rec->$kpiTargetVar ?? 0;
                                $numVal = is_numeric($displayVal) ? (float)$displayVal : 0;
                                $maxRef = max(1, is_numeric($kpiMax) ? (float)$kpiMax : 1);
                                $barWidth = min(100, max(5, round(($numVal / $maxRef) * 100)));
                            @endphp
                            <div class="p-2.5 rounded-lg bg-white border border-slate-200 shadow-2xs space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-[10px]">
                                            {{ $index + 1 }}
                                        </span>
                                        <span class="font-bold text-slate-800">
                                            <span x-show="isMasked">{{ $rec->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nama }}</span>
                                        </span>
                                        <span class="text-slate-400 font-mono text-[10px]">
                                            <span x-show="isMasked">{{ $rec->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nomor_induk_kependudukan }}</span>
                                        </span>
                                    </div>
                                    <strong class="font-mono text-amber-700 font-bold">
                                        {{ is_numeric($displayVal) ? number_format((float)$displayVal, (floor((float)$displayVal) == (float)$displayVal ? 0 : 2), ',', '.') : $displayVal }}
                                    </strong>
                                </div>
                                <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada data untuk diperingkatkan.</p>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>
    @endif

</div>
@endsection
