@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ auditFilter: 'ALL' }">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-rose-50 text-rose-600 text-lg font-bold">⚠️</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Audit Temuan Data Bermasalah & Anomali
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-rose-100 text-rose-800 text-xs font-bold font-mono">
                    {{ count($issueRecords) }} Temuan Tercatat
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Pemeriksaan mendalam anomali NIK/KK cacat, tanggal lahir tidak valid, desil di luar rentang, dan inkonsistensi hubungan keluarga.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <!-- Unduh Laporan Error CSV -->
            <a href="{{ route('dtsen.export-errors') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📥</span> Unduh Laporan Error (.CSV)
            </a>
            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📋</span> Buka Tabel Master
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
                    Impor berkas dataset CSV/Excel untuk memindai temuan anomali dan error data.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Buka Manajemen Berkas
                </a>
            </div>
        </div>
    @elseif(count($issueRecords) === 0)
        <!-- Keadaan Sempurna (0 Eror) -->
        <div class="p-12 bg-white rounded-2xl border border-emerald-200 text-center space-y-4 shadow-xs">
            <div class="w-16 h-16 bg-emerald-50 text-emerald-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">🎉</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-emerald-950 text-base">Luar Biasa! Tidak Ditemukan Anomali Data</h3>
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
        <!-- CONTAINER AUDIT TEMUAN DATA -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-5">
            
            <!-- Summary Counters & Filter Level -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="flex items-center gap-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Filter Masalah:</span>
                    <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl">
                        <button type="button" 
                                @click="auditFilter = 'ALL'" 
                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                :class="auditFilter === 'ALL' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                            Semua ({{ count($issueRecords) }})
                        </button>
                        <button type="button" 
                                @click="auditFilter = 'Critical'" 
                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                :class="auditFilter === 'Critical' ? 'bg-rose-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                            🔴 Critical ({{ $criticalCount }})
                        </button>
                        <button type="button" 
                                @click="auditFilter = 'Warning'" 
                                class="px-3 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer"
                                :class="auditFilter === 'Warning' ? 'bg-amber-500 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                            🟡 Warning ({{ $warningCount }})
                        </button>
                    </div>
                </div>

                <div class="text-xs text-slate-500 font-medium">
                    Menampilkan temuan anomali yang membutuhkan verifikasi operator
                </div>
            </div>

            <!-- GRID DAFTAR TEMUAN ERROR -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($issueRecords as $issueRec)
                    <div x-show="auditFilter === 'ALL' || auditFilter === '{{ $issueRec->quality_status }}'"
                         class="p-4 rounded-xl border {{ $issueRec->quality_status === 'Critical' ? 'bg-rose-50/50 border-rose-200' : 'bg-amber-50/50 border-amber-200' }} space-y-2.5 transition-all">
                        
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                @if($issueRec->quality_status === 'Critical')
                                    <span class="px-2 py-0.5 rounded-md bg-rose-600 text-white font-extrabold text-[10px] uppercase">🔴 CRITICAL</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md bg-amber-500 text-white font-extrabold text-[10px] uppercase">🟡 WARNING</span>
                                @endif
                                <strong class="text-slate-900 text-xs font-extrabold">
                                    <span x-show="isMasked">{{ $issueRec->masked_nama ?? ($issueRec->nama ?? 'Subjek') }}</span>
                                    <span x-show="!isMasked" style="display:none;">{{ $issueRec->nama ?? 'Subjek' }}</span>
                                </strong>
                            </div>
                            <button type="button" @click="openPreview({{ $issueRec->id ?? 0 }})" class="px-2.5 py-1 bg-white hover:bg-slate-100 border border-slate-300 text-slate-700 font-bold text-[11px] rounded-lg shadow-2xs cursor-pointer">
                                👁 Inspeksi Baris
                            </button>
                        </div>

                        <div class="text-xs space-y-1.5">
                            <div class="text-slate-600 flex flex-wrap items-center gap-3 font-mono text-[11px] pt-1">
                                <span>NIK: <strong class="text-slate-900">
                                    <span x-show="isMasked">{{ $issueRec->masked_nik ?? '****************' }}</span>
                                    <span x-show="!isMasked" style="display:none;">{{ $issueRec->nomor_induk_kependudukan ?? '-' }}</span>
                                </strong></span>
                                <span>KK: <strong class="text-slate-900">
                                    <span x-show="isMasked">{{ $issueRec->masked_kk ?? '****************' }}</span>
                                    <span x-show="!isMasked" style="display:none;">{{ $issueRec->nomor_kartu_keluarga ?? '-' }}</span>
                                </strong></span>
                            </div>
                            
                            <div class="bg-white p-3 rounded-xl border border-slate-200/80 space-y-1 shadow-2xs">
                                <p class="font-extrabold text-slate-800 text-[11px] uppercase tracking-wider">Rincian Temuan Masalah:</p>
                                <ul class="list-disc pl-4 text-[11px] {{ $issueRec->quality_status === 'Critical' ? 'text-rose-700' : 'text-amber-800' }} space-y-0.5 font-medium">
                                    @if(!empty($issueRec->quality_issues))
                                        @foreach($issueRec->quality_issues as $iss)
                                            <li>{{ $iss }}</li>
                                        @endforeach
                                    @else
                                        <li>Pengecekan integritas nilai variabel master.</li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

        </div>
    @endif

</div>
@endsection
