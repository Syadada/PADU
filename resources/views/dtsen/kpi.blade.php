@extends('layouts.app')

@section('content')
<div class="space-y-6">
    
    <!-- Header Banner -->
    <div class="p-5 sm:p-6 bg-white rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="p-2 rounded-xl bg-indigo-50 text-indigo-600 text-lg font-bold">📊</span>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Dashboard Indikator Kunci (KPI) & Demografi DTSEN 2026
                </h2>
                <span class="px-2.5 py-0.5 rounded-full bg-indigo-100 text-indigo-800 text-xs font-bold font-mono">
                    Macro Demographics
                </span>
            </div>
            <p class="text-xs text-slate-500 font-medium">
                Ringkasan makro indikator kependudukan, rasio gender, piramida kelompok usia, distribusi kesejahteraan (desil), status ketenagakerjaan, dan pemeringkatan variabel.
            </p>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('dtsen.salary') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>💵</span> Buka Analisis Finansial Gaji
            </a>
            <a href="{{ route('dtsen.data') }}" class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-xs transition-all flex items-center gap-1.5 cursor-pointer">
                <span>📋</span> Master Data
            </a>
        </div>
    </div>

    @if(($totalSystemRows ?? $totalRows) === 0)
        <!-- Keadaan Kosong -->
        <div class="p-12 bg-white rounded-2xl border-2 border-dashed border-slate-200 text-center space-y-4">
            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center text-3xl mx-auto font-bold shadow-xs">📊</div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-900 text-base">Belum Ada Dataset yang Dimuat</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto">
                    Impor berkas data terlebih dahulu melalui menu Manajemen Berkas untuk menghitung statistik indikator KPI kependudukan.
                </p>
            </div>
            <div class="pt-2">
                <a href="{{ route('dtsen.files') }}" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md transition-all inline-flex items-center gap-2 cursor-pointer">
                    <span>⚡</span> Buka Manajemen Berkas
                </a>
            </div>
        </div>
    @else

        <!-- ================= BAGIAN 1: 4 KARTU KPI MAKRO KEPENDUDUKAN ================= -->
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-700 flex items-center gap-1.5">
                    <span>🏛️</span> Indikator Kunci Makro Kependudukan (Nasional / Satker)
                </h3>
                <span class="text-[11px] font-bold text-slate-400 font-mono">{{ number_format($totalRows) }} Jiwa Terdata</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <!-- Kartu 1: Total Populasi -->
                <div class="kpi-stat-card p-5 rounded-2xl text-white shadow-sm space-y-2 min-w-0" style="background: linear-gradient(135deg, #1e3a8a 0%, #1e1b4b 100%) !important;">
                    <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-blue-200">
                        <span class="truncate">👥 Total Populasi</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-blue-500/30 border border-blue-400/40 text-blue-100">DATASET</span>
                    </div>
                    <div class="kpi-stat-value text-white font-mono" title="{{ number_format($totalRows) }} Jiwa">
                        <span class="truncate">{{ number_format($totalRows) }}</span>
                        <span class="text-sm font-semibold text-blue-200 ml-1">Jiwa</span>
                    </div>
                    <p class="text-[11px] font-semibold text-blue-200 truncate">
                        Tercatat dalam {{ number_format($totalKk ?? 0) }} Kepala Keluarga (KK)
                    </p>
                </div>

                <!-- Kartu 2: Rasio Gender -->
                <div class="kpi-stat-card p-5 rounded-2xl text-white shadow-sm space-y-2 min-w-0" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;">
                    <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-sky-200">
                        <span class="truncate">⚖️ Rasio Jenis Kelamin</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-sky-500/30 border border-sky-400/40 text-sky-100">GENDER</span>
                    </div>
                    <div class="kpi-stat-value text-white font-mono" title="{{ $demographics['gender_ratio'] ?? '50% : 50%' }}">
                        <span class="truncate">{{ $demographics['gender_ratio'] ?? '50% : 50%' }}</span>
                    </div>
                    <p class="text-[11px] font-semibold text-sky-200 truncate">
                        {{ number_format($demographics['gender_laki'] ?? 0) }} Laki-laki &bull; {{ number_format($demographics['gender_perempuan'] ?? 0) }} Perempuan
                    </p>
                </div>

                <!-- Kartu 3: Rata-Rata Usia -->
                <div class="kpi-stat-card p-5 rounded-2xl text-white shadow-sm space-y-2 min-w-0" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%) !important;">
                    <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-amber-200">
                        <span class="truncate">🎂 Rata-Rata Usia</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-500/30 border border-amber-400/40 text-amber-100">DEMOGRAFI</span>
                    </div>
                    <div class="kpi-stat-value text-white font-mono" title="{{ $demographics['age']['avg_age'] ?? 50.0 }} Tahun">
                        <span class="truncate">{{ $demographics['age']['avg_age'] ?? 50.0 }}</span>
                        <span class="text-sm font-semibold text-amber-200 ml-1">Tahun</span>
                    </div>
                    <p class="text-[11px] font-semibold text-amber-200 truncate">
                        Produktif: {{ number_format($demographics['age']['produktif'] ?? 0) }} &bull; Lansia: {{ number_format($demographics['age']['lansia'] ?? 0) }}
                    </p>
                </div>

                <!-- Kartu 4: Partisipasi Kerja -->
                <div class="kpi-stat-card p-5 rounded-2xl text-white shadow-sm space-y-2 min-w-0" style="background: linear-gradient(135deg, #059669 0%, #065f46 100%) !important;">
                    <div class="flex items-center justify-between text-xs font-extrabold uppercase tracking-wider text-emerald-200">
                        <span class="truncate">💼 Partisipasi Kerja</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-500/30 border border-emerald-400/40 text-emerald-100">KETENAGAKERJAAN</span>
                    </div>
                    <div class="kpi-stat-value text-white font-mono" title="{{ $demographics['employment_rate'] ?? '33.3%' }}">
                        <span class="truncate">{{ $demographics['employment_rate'] ?? '33.3%' }}</span>
                        <span class="text-sm font-semibold text-emerald-200 ml-1">Bekerja</span>
                    </div>
                    <p class="text-[11px] font-semibold text-emerald-200 truncate">
                        {{ number_format($demographics['employment_bekerja'] ?? 0) }} Subjek Berstatus Bekerja
                    </p>
                </div>
            </div>

            <!-- Smart Response & Executive Summary Makro Kependudukan -->
            @php
                $popJiwa = (int)$totalRows;
                $popKk = (int)($totalKk ?? 0);
                $avgPerKk = ($popKk > 0) ? round($popJiwa / $popKk, 1) : 0;
                $empCount = (int)($demographics['employment_bekerja'] ?? 0);
                $gLaki = (int)($demographics['gender_laki'] ?? 0);
                $gPerem = (int)($demographics['gender_perempuan'] ?? 0);
                $gTotal = max(1, $gLaki + $gPerem);
                $sexRatio = round(($gLaki / max(1, $gPerem)) * 100, 1);
            @endphp
            <div class="p-4 rounded-xl border border-blue-100 bg-gradient-to-r from-blue-50/70 via-slate-50 to-indigo-50/70 space-y-2.5">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-blue-600 text-white text-xs font-black shadow-xs">📝</span>
                        <span class="text-xs font-black uppercase tracking-wider text-blue-950">
                            Keterangan Ringkasan Data Penduduk
                        </span>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] font-mono font-bold text-slate-600">
                        <span class="px-2.5 py-0.5 rounded bg-white border border-slate-200">🏠 1 Keluarga: Rata-rata ~{{ $avgPerKk }} Orang</span>
                        <span class="px-2.5 py-0.5 rounded bg-white border border-slate-200">⚖️ Warga: {{ round(($gLaki/$gTotal)*100) }}% Pria &bull; {{ round(($gPerem/$gTotal)*100) }}% Wanita</span>
                    </div>
                </div>
                <div class="p-3 bg-white/95 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-2 leading-relaxed">
                    <p class="flex items-start gap-1.5">
                        <span class="text-blue-600 font-bold shrink-0">👥</span>
                        <span>
                            <strong>Kondisi Saat Ini:</strong> Ada sebanyak <strong>{{ number_format($popJiwa) }} warga</strong> yang terdata dalam <strong>{{ number_format($popKk) }} keluarga (KK)</strong>. Perbandingan jumlah pria dan wanita tergolong seimbang. Dari seluruh warga, sebanyak <strong>{{ number_format($empCount) }} orang ({{ $demographics['employment_rate'] ?? '33.3%' }})</strong> sudah aktif bekerja dan punya penghasilan, sedangkan sisanya merupakan anak sekolah, ibu rumah tangga, lansia, atau yang sedang mencari kerja.
                        </span>
                    </p>
                    <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs space-y-1">
                        <div class="font-extrabold flex items-center gap-1.5 text-emerald-900">
                            <span>💡</span> Solusi & Tindakan yang Disarankan:
                        </div>
                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-emerald-900 font-medium pl-1">
                            <li><strong>Bantuan Sembako & PKH:</strong> Utamakan keluarga yang memiliki banyak tanggungan (di atas rata-rata {{ ceil($avgPerKk) }} orang per KK) agar kebutuhan pokok dapur selalu tercukupi.</li>
                            <li><strong>Buka Peluang Kerja:</strong> Warga usia muda yang belum bekerja perlu didaftarkan ke pelatihan kerja atau bantuan modal usaha mikro (UMKM) agar mandiri berpenghasilan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= BAGIAN 2: SEBARAN 3 PILAR SOSIAL-DEMOGRAFI ================= -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Pilar 1: Distribusi Desil Kesejahteraan (Bansos & Kemiskinan) -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4 lg:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                            <span>🏷️</span> Sebaran 10 Desil Kesejahteraan Sosial (Regsosek / DTSEN)
                        </h4>
                        <p class="text-xs text-slate-500">
                            Distribusi tingkat desil kemiskinan ekstrem (Desil 1) hingga kelompok sejahtera (Desil 10).
                        </p>
                    </div>
                    <span class="px-2.5 py-1 bg-amber-50 text-amber-900 border border-amber-200 rounded-lg text-[10px] font-extrabold">
                        Prioritas Bansos: Desil 1-2
                    </span>
                </div>

                <div class="space-y-3">
                    @php
                        $desilList = $demographics['desil'] ?? [];
                        $maxDesilCount = 1;
                        foreach($desilList as $dItem) {
                            if($dItem['count'] > $maxDesilCount) $maxDesilCount = $dItem['count'];
                        }
                    @endphp

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @forelse($desilList as $dRow)
                            @php
                                $dName = $dRow['name'] ?? '';
                                $dCnt = (int)($dRow['count'] ?? 0);
                                $dPct = $totalRows > 0 ? round(($dCnt / $totalRows) * 100, 1) : 0;
                                $dBar = min(100, max(6, round(($dCnt / $maxDesilCount) * 100)));
                                
                                // Color coding by poverty tier
                                $isExtreme = str_contains($dName, 'Desil 1') || str_contains($dName, 'Desil 2');
                                $isMiddle = str_contains($dName, 'Desil 3') || str_contains($dName, 'Desil 4') || str_contains($dName, 'Desil 5') || str_contains($dName, 'Desil 6');
                                
                                $badgeBg = $isExtreme ? 'bg-rose-100 text-rose-800 border-rose-300' : ($isMiddle ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300');
                                $barBg = $isExtreme ? 'bg-rose-500' : ($isMiddle ? 'bg-amber-500' : 'bg-emerald-500');
                            @endphp
                            <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-extrabold px-2 py-0.5 rounded border text-[11px] {{ $badgeBg }}">
                                        {{ $dName }}
                                    </span>
                                    <div class="text-right">
                                        <span class="font-mono font-bold text-slate-800">{{ number_format($dCnt) }}</span>
                                        <span class="text-[10px] text-slate-400 font-semibold ml-0.5">({{ $dPct }}%)</span>
                                    </div>
                                </div>
                                <div class="w-full h-2 bg-slate-200/80 rounded-full overflow-hidden">
                                    <div class="{{ $barBg }} h-full rounded-full transition-all" style="width: {{ $dBar }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 col-span-2 text-center py-4">Data desil tidak terdeteksi pada dataset ini.</p>
                        @endforelse
                    </div>
                </div>

                <!-- ================= SMART DATA-DRIVEN INSIGHT & RESPONSE ================= -->
                @if(!empty($desilList))
                    @php
                        $dTotalSum = 0;
                        $dBansosPrioritas = 0; // Desil 1 & 2
                        $dBufferRentan = 0;    // Desil 3 & 4
                        $dMenengahAtas = 0;    // Desil 5 - 10
                        $maxDesilItem = null;
                        $minDesilItem = null;

                        foreach($desilList as $dItem) {
                            $cnt = (int)($dItem['count'] ?? 0);
                            $name = $dItem['name'] ?? '';
                            $dTotalSum += $cnt;

                            if (str_contains($name, 'Desil 1') || str_contains($name, 'Desil 2')) {
                                $dBansosPrioritas += $cnt;
                            } elseif (str_contains($name, 'Desil 3') || str_contains($name, 'Desil 4')) {
                                $dBufferRentan += $cnt;
                            } else {
                                $dMenengahAtas += $cnt;
                            }

                            if (!$maxDesilItem || $cnt > $maxDesilItem['count']) {
                                $maxDesilItem = ['name' => $name, 'count' => $cnt];
                            }
                            if (!$minDesilItem || $cnt < $minDesilItem['count']) {
                                $minDesilItem = ['name' => $name, 'count' => $cnt];
                            }
                        }

                        $pctPrioritas = $dTotalSum > 0 ? round(($dBansosPrioritas / $dTotalSum) * 100, 1) : 0;
                        $pctBuffer = $dTotalSum > 0 ? round(($dBufferRentan / $dTotalSum) * 100, 1) : 0;
                        $pctJaringPengaman = $dTotalSum > 0 ? round((($dBansosPrioritas + $dBufferRentan) / $dTotalSum) * 100, 1) : 0;
                        $pctMenengahAtas = $dTotalSum > 0 ? round(($dMenengahAtas / $dTotalSum) * 100, 1) : 0;

                        // Evaluasi pola sebaran (Uniform 10% vs Asymmetric / Skewed)
                        $isUniform = false;
                        if (count($desilList) >= 9 && $dTotalSum > 0) {
                            $expectedShare = 100.0 / count($desilList);
                            $maxDeviation = 0;
                            foreach($desilList as $dItem) {
                                $share = ((int)$dItem['count'] / $dTotalSum) * 100;
                                $dev = abs($share - $expectedShare);
                                if ($dev > $maxDeviation) $maxDeviation = $dev;
                            }
                            // Jika deviasi antar desil <= 1.5%, artinya data mengikuti standar kuantil nasional murni
                            $isUniform = ($maxDeviation <= 1.5);
                        }
                    @endphp

                    <div class="pt-4 border-t border-slate-100 space-y-3">
                        <!-- Smart Response Container -->
                        <div class="p-4 rounded-xl border border-indigo-100 bg-gradient-to-r from-indigo-50/70 via-purple-50/40 to-blue-50/70 space-y-3">
                            <div class="flex items-center justify-between flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-indigo-600 text-white text-xs font-black shadow-xs">
                                        ✨
                                    </span>
                                    <span class="text-xs font-black uppercase tracking-wider text-indigo-950">
                                        Keterangan: Kebijakan & Sebaran Kesejahteraan
                                    </span>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold font-mono {{ $isUniform ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                    {{ $isUniform ? '🎯 Kuantil Nasional Seimbang (~10% per desil)' : '📊 Distribusi Asimetris / Terfilter' }}
                                </span>
                            </div>

                            <!-- 3 Mini Data-Driven Summary Cards -->
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1">
                                <div class="p-2.5 rounded-lg bg-white/95 border border-rose-200 shadow-2xs space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-extrabold text-rose-800">Target Bansos Ekstrem</span>
                                        <span class="font-bold text-rose-600 text-[10px] bg-rose-50 px-1 rounded">Desil 1–2</span>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-sm font-black font-mono text-slate-900">{{ number_format($dBansosPrioritas) }}</span>
                                        <span class="text-[10px] font-bold text-rose-700">({{ $pctPrioritas }}%)</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 leading-tight">Sasaran mutlak PKH, BPNT & bantuan pangan darurat.</p>
                                </div>

                                <div class="p-2.5 rounded-lg bg-white/95 border border-amber-200 shadow-2xs space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-extrabold text-amber-800">Batas Jaring Pengaman</span>
                                        <span class="font-bold text-amber-600 text-[10px] bg-amber-50 px-1 rounded">Desil 1–4</span>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-sm font-black font-mono text-slate-900">{{ number_format($dBansosPrioritas + $dBufferRentan) }}</span>
                                        <span class="text-[10px] font-bold text-amber-700">({{ $pctJaringPengaman }}%)</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 leading-tight">Ambang batas PBI-JK (BPJS Gratis) & subsidi energi terarah.</p>
                                </div>

                                <div class="p-2.5 rounded-lg bg-white/95 border border-emerald-200 shadow-2xs space-y-1">
                                    <div class="flex items-center justify-between text-[11px]">
                                        <span class="font-extrabold text-emerald-800">Mandiri & Berdaya</span>
                                        <span class="font-bold text-emerald-600 text-[10px] bg-emerald-50 px-1 rounded">Desil 5–10</span>
                                    </div>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-sm font-black font-mono text-slate-900">{{ number_format($dMenengahAtas) }}</span>
                                        <span class="text-[10px] font-bold text-emerald-700">({{ $pctMenengahAtas }}%)</span>
                                    </div>
                                    <p class="text-[10px] text-slate-500 leading-tight">Kelompok non-bansos, diarahkan ke program KUR & pemberdayaan usaha.</p>
                                </div>
                            </div>

                            <!-- Penjelasan Mudah & Solusi Bantuan -->
                            <div class="p-3 bg-white/95 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-2 leading-relaxed">
                                @if($isUniform)
                                    <div class="flex items-start gap-2">
                                        <span class="text-indigo-600 font-bold shrink-0 mt-0.5">ℹ️</span>
                                        <div class="space-y-1">
                                            <p>
                                                <strong>Kenapa jumlah tiap desil persis sama ({{ number_format($dTotalSum > 0 ? $dTotalSum / count($desilList) : 0) }} orang / 10%)?</strong>
                                                Di aturan resmi BPS & Pemerintah, seluruh warga diurutkan dari yang paling tidak mampu hingga yang paling kaya, lalu dibagi rata ke dalam <strong>10 tingkatan tangga ekonomi (Desil 1 s/d 10)</strong>. Jadi yang membedakan bukan jumlah orangnya, melainkan <u>tingkat penghasilan dan kondisi rumah/asetnya</u>.
                                            </p>
                                        </div>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-950 text-xs space-y-1">
                                        <div class="font-extrabold flex items-center gap-1.5 text-emerald-900">
                                            <span>💡</span> Solusi Pembagian Bantuan Tepat Sasaran:
                                        </div>
                                        <ul class="list-disc list-inside space-y-0.5 text-[11px] text-emerald-900 font-medium pl-1">
                                            <li><strong>Bansos Rutin (PKH & Sembako):</strong> Wajib difokuskan untuk <strong>Desil 1 & 2 ({{ number_format($dBansosPrioritas) }} orang)</strong> agar bebas dari kemiskinan ekstrem.</li>
                                            <li><strong>Jaminan Kesehatan & Subsidi:</strong> Berikan kartu BPJS Kesehatan Gratis (PBI) dan subsidi listrik/gas untuk warga di <strong>Desil 3 & 4 ({{ number_format($dBufferRentan) }} orang)</strong> agar tidak jatuh miskin saat sakit.</li>
                                            <li><strong>Pemberdayaan Usaha:</strong> Warga <strong>Desil 5–10</strong> tidak perlu diberi beras/bansos tunai, melainkan difasilitasi modal usaha (KUR) dan kemudahan usaha.</li>
                                        </ul>
                                    </div>
                                @else
                                    <div class="flex items-start gap-2">
                                        <span class="text-indigo-600 font-bold shrink-0 mt-0.5">📊</span>
                                        <div class="space-y-1">
                                            <p>
                                                <strong>Hasil Penyaringan Data:</strong> Warga paling banyak berada di <strong>{{ $maxDesilItem['name'] ?? '-' }}</strong> yaitu sebanyak <strong>{{ number_format($maxDesilItem['count'] ?? 0) }} orang</strong>.
                                                @if($pctPrioritas >= 30)
                                                    Wilayah/kelompok ini termasuk <strong class="text-rose-700">wilayah yang sangat membutuhkan bantuan</strong> karena {{ $pctPrioritas }}% warganya tergolong miskin.
                                                @elseif($pctMenengahAtas >= 65)
                                                    Wilayah/kelompok ini didominasi oleh <strong class="text-emerald-700">keluarga mampu dan mandiri</strong> ({{ $pctMenengahAtas }}% di Desil 5–10).
                                                @else
                                                    Tingkat kemampuan ekonomi warga tersebar cukup merata.
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-blue-50 border border-blue-200 text-blue-950 text-xs space-y-1">
                                        <div class="font-extrabold flex items-center gap-1.5 text-blue-900">
                                            <span>💡</span> Solusi untuk Wilayah Ini:
                                        </div>
                                        <p class="text-[11px] text-blue-900 font-medium">
                                            Gunakan data ini untuk mengajukan kuota bantuan sosial tambahan langsung ke dinas sosial terkait jika wilayah ini terbukti padat warga pra-sejahtera.
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Pilar 2: Demografi Usia & Status -->
            <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <span>👶</span> Komposisi Kelompok Usia
                    </h4>
                    <p class="text-xs text-slate-500">Proporsi piramida usia produktif vs non-produktif.</p>
                </div>

                @php
                    $ageData = $demographics['age'] ?? [];
                    $uBalita = $ageData['balita'] ?? 0;
                    $uAnak = $ageData['anak'] ?? 0;
                    $uProduktif = $ageData['produktif'] ?? 0;
                    $uLansia = $ageData['lansia'] ?? 0;
                    $uTotal = max(1, $uBalita + $uAnak + $uProduktif + $uLansia);
                @endphp

                <div class="space-y-3">
                    <!-- Balita -->
                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Balita (< 6 thn)</span>
                            <span class="font-mono font-bold text-slate-900">{{ number_format($uBalita) }} ({{ round(($uBalita / $uTotal) * 100, 1) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="bg-cyan-500 h-full rounded-full" style="width: {{ round(($uBalita / $uTotal) * 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Anak & Pelajar -->
                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-slate-700">Anak & Pelajar (6 - 17 thn)</span>
                            <span class="font-mono font-bold text-slate-900">{{ number_format($uAnak) }} ({{ round(($uAnak / $uTotal) * 100, 1) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="bg-blue-500 h-full rounded-full" style="width: {{ round(($uAnak / $uTotal) * 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Usia Produktif -->
                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-800">Usia Produktif (18 - 59 thn)</span>
                            <span class="font-mono font-bold text-emerald-700">{{ number_format($uProduktif) }} ({{ round(($uProduktif / $uTotal) * 100, 1) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="bg-emerald-500 h-full rounded-full" style="width: {{ round(($uProduktif / $uTotal) * 100) }}%"></div>
                        </div>
                    </div>

                    <!-- Lansia -->
                    <div class="p-2.5 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-amber-800">Lansia (≥ 60 thn)</span>
                            <span class="font-mono font-bold text-amber-700">{{ number_format($uLansia) }} ({{ round(($uLansia / $uTotal) * 100, 1) }}%)</span>
                        </div>
                        <div class="w-full h-2 bg-slate-200 rounded-full overflow-hidden">
                            <div class="bg-amber-500 h-full rounded-full" style="width: {{ round(($uLansia / $uTotal) * 100) }}%"></div>
                        </div>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                    <span>Rentang Usia Tercatat:</span>
                    <strong class="font-mono text-slate-800">{{ $kpiMin ?? 18 }} - {{ $kpiMax ?? 82 }} Tahun</strong>
                </div>

                <!-- Smart Response Komposisi Usia & Bonus Demografi -->
                @php
                    $depRatio = $uProduktif > 0 ? round((($uBalita + $uAnak + $uLansia) / $uProduktif) * 100, 1) : 0;
                    $produktifPct = round(($uProduktif / $uTotal) * 100, 1);
                    $hasBonusDemografi = ($produktifPct >= 60.0);
                @endphp
                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                    <div class="p-3.5 rounded-xl border border-cyan-100 bg-gradient-to-r from-cyan-50/70 via-sky-50/40 to-blue-50/70 space-y-2.5">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center w-5 h-5 rounded-md bg-cyan-600 text-white text-[11px] font-black shadow-2xs">✨</span>
                                <span class="text-xs font-black uppercase tracking-wider text-cyan-950">Keterangan: Analisis Komposisi Usia</span>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[10px] font-extrabold font-mono {{ $hasBonusDemografi ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200' }}">
                                {{ $hasBonusDemografi ? '⚡ Bonus Demografi' : '⚖️ Beban Ketergantungan' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center text-xs">
                            <div class="p-2 rounded-lg bg-white/95 border border-slate-200 shadow-2xs">
                                <span class="text-[10px] text-slate-500 font-bold block">Tanggungan per 100 Pekerja</span>
                                <strong class="font-mono text-slate-800 text-sm">{{ round($depRatio) }} Orang</strong>
                                <span class="text-[9px] text-slate-400 block">anak & lansia yang ditanggung</span>
                            </div>
                            <div class="p-2 rounded-lg bg-white/95 border border-slate-200 shadow-2xs">
                                <span class="text-[10px] text-slate-500 font-bold block">Warga Usia Kerja</span>
                                <strong class="font-mono text-emerald-700 text-sm">{{ $produktifPct }}%</strong>
                                <span class="text-[9px] text-slate-400 block">kelompok usia 18–59 tahun</span>
                            </div>
                        </div>

                        <div class="p-2.5 bg-white/95 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-2 leading-relaxed">
                            <p class="text-[11px]">
                                @if($hasBonusDemografi)
                                    <strong>Usia Kerja Mendominasi:</strong> Sebanyak <strong>{{ $produktifPct }}%</strong> warga berada di usia siap kerja. Setiap 100 orang usia kerja menanggung sekitar {{ round($depRatio) }} orang (anak atau lansia).
                                @else
                                    <strong>Jumlah Tanggungan Cukup Banyak:</strong> Setiap 100 orang usia kerja menanggung sekitar {{ round($depRatio) }} orang usia anak dan lansia.
                                @endif
                            </p>
                            <div class="p-2 rounded-lg bg-cyan-50 border border-cyan-200 text-cyan-950 text-[11px] space-y-0.5">
                                <div class="font-bold flex items-center gap-1 text-cyan-900">
                                    <span>💡</span> Solusi & Tindakan yang Disarankan:
                                </div>
                                <div class="text-cyan-900 font-medium">
                                    @if($hasBonusDemografi)
                                        Sediakan pelatihan kerja dan modal usaha mandiri agar potensi usia muda dapat menghasilkan pendapatan keluarga yang maksimal.
                                    @else
                                        Perkuat posyandu dan bantuan gizi balita agar bebas stunting, serta sediakan layanan jemput bola berobat gratis bagi lansia.
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ================= BAGIAN 3: ANALISIS VARIABEL & PEMERINGKATAN BERJENJANG ================= -->
        <div class="p-6 bg-white rounded-2xl border border-slate-200 shadow-sm space-y-6">
            
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                    <h3 class="font-extrabold text-slate-900 text-sm flex items-center gap-2">
                        <span>🎯</span> Eksplorasi Metrik & Peringkat Bebas per Variabel: 
                        <span class="text-blue-700 font-black">{{ $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar }}</span>
                    </h3>
                    <p class="text-xs text-slate-500">
                        Pilih variabel target untuk mengkalkulasi nilai ekstrem dan 5 tingkatan peringkat tertinggi & terendah.
                    </p>
                </div>

                <!-- Form Pemilih Variabel Target KPI -->
                <form method="GET" action="{{ route('dtsen.kpi') }}" id="kpiVarForm" class="flex items-center gap-2 shrink-0">
                    <label for="kpi_var" class="text-xs font-bold text-slate-700 whitespace-nowrap">Ganti Variabel:</label>
                    <select name="kpi_var" id="kpi_var" aria-label="Ganti Variabel Target KPI" onchange="this.form.submit()" class="text-xs font-extrabold bg-blue-50 border border-blue-300 rounded-xl px-3 py-2 text-blue-900 outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer shadow-sm">
                        @foreach($activeColumnsMap as $key => $title)
                            <option value="{{ $key }}" {{ $kpiTargetVar === $key ? 'selected' : '' }}>📌 {{ $title }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <!-- CARDS METRIK 4 STATISTIK UTAMA (RESPONSIVE FLEX & OVERFLOW SAFE) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="kpi-stat-card p-4 rounded-xl text-white shadow-sm space-y-1.5 min-w-0" style="background: linear-gradient(135deg, #059669 0%, #0f766e 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-emerald-100 truncate block">Nilai Maksimum (MAX)</span>
                    <div class="kpi-stat-value text-white font-mono" title="{{ is_numeric($kpiMax) ? number_format($kpiMax, (floor($kpiMax) == $kpiMax ? 0 : 2), ',', '.') : $kpiMax }}">
                        <span class="truncate">{{ is_numeric($kpiMax) ? number_format($kpiMax, (floor($kpiMax) == $kpiMax ? 0 : 2), ',', '.') : $kpiMax }}</span>
                    </div>
                    <p class="text-[11px] font-bold text-emerald-100 truncate">Nilai puncak tertinggi dari dataset</p>
                </div>

                <div class="kpi-stat-card p-4 rounded-xl text-white shadow-sm space-y-1.5 min-w-0" style="background: linear-gradient(135deg, #2563eb 0%, #4338ca 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-blue-100 truncate block">Rata-Rata (AVG)</span>
                    <div class="kpi-stat-value text-white font-mono" title="{{ number_format($kpiAvg, 2, ',', '.') }}">
                        <span class="truncate">{{ number_format($kpiAvg, 2, ',', '.') }}</span>
                    </div>
                    <p class="text-[11px] font-bold text-blue-100 truncate">Nilai rata-rata dari seluruh baris</p>
                </div>

                <div class="kpi-stat-card p-4 rounded-xl text-white shadow-sm space-y-1.5 min-w-0" style="background: linear-gradient(135deg, #d97706 0%, #c2410c 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-amber-100 truncate block">Nilai Minimum (MIN)</span>
                    <div class="kpi-stat-value text-white font-mono" title="{{ is_numeric($kpiMin) ? number_format($kpiMin, (floor($kpiMin) == $kpiMin ? 0 : 2), ',', '.') : $kpiMin }}">
                        <span class="truncate">{{ is_numeric($kpiMin) ? number_format($kpiMin, (floor($kpiMin) == $kpiMin ? 0 : 2), ',', '.') : $kpiMin }}</span>
                    </div>
                    <p class="text-[11px] font-bold text-amber-100 truncate">Nilai dasar terendah dari dataset</p>
                </div>

                <div class="kpi-stat-card p-4 rounded-xl text-white shadow-sm space-y-1.5 min-w-0" style="background: linear-gradient(135deg, #7c3aed 0%, #1e293b 100%) !important;">
                    <span class="text-xs font-black uppercase tracking-wider text-purple-100 truncate block">Total Akumulasi (SUM)</span>
                    <div class="kpi-stat-value text-white font-mono" title="{{ is_numeric($kpiSum) ? number_format($kpiSum, (floor($kpiSum) == $kpiSum ? 0 : 2), ',', '.') : $kpiSum }}">
                        <span class="truncate">{{ is_numeric($kpiSum) ? number_format($kpiSum, (floor($kpiSum) == $kpiSum ? 0 : 2), ',', '.') : $kpiSum }}</span>
                    </div>
                    <p class="text-[11px] font-bold text-purple-100 truncate">Akumulasi total keseluruhan nilai</p>
                </div>
            </div>

            <!-- TOP 5 & BOTTOM 5 SIDE-BY-SIDE WIDGETS DENGAN TINGKATAN NILAI BERBEDA -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                
                <!-- TOP 5 WIDGET -->
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50 space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                        <div>
                            <h4 class="font-extrabold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                                <span>🏆</span> Top 5 Peringkat Teratas (Maksimum)
                            </h4>
                            <p class="text-[10px] text-slate-500 font-medium">5 tingkatan nilai tertinggi berjenjang dengan subjek representatif.</p>
                        </div>
                        <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 text-[10px] font-extrabold rounded">5 TINGKAT TERATAS</span>
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
                                        <span class="w-6 h-6 rounded-md bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            #{{ $index + 1 }}
                                        </span>
                                        <span class="font-bold text-slate-800 truncate">
                                            <span x-show="isMasked">{{ $rec->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nama }}</span>
                                        </span>
                                        <span class="text-slate-400 font-mono text-[10px] shrink-0">
                                            <span x-show="isMasked">{{ $rec->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nomor_induk_kependudukan }}</span>
                                        </span>
                                    </div>
                                    <strong class="font-mono text-emerald-700 font-black shrink-0 text-sm">
                                        {{ is_numeric($displayVal) ? number_format((float)$displayVal, (floor((float)$displayVal) == (float)$displayVal ? 0 : 2), ',', '.') : $displayVal }}
                                    </strong>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
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
                        <div>
                            <h4 class="font-extrabold text-xs text-slate-800 uppercase flex items-center gap-1.5">
                                <span>📉</span> 5 Peringkat Terbawah (Minimum)
                            </h4>
                            <p class="text-[10px] text-slate-500 font-medium">5 tingkatan nilai terendah berjenjang dengan subjek representatif.</p>
                        </div>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded">5 TINGKAT TERBAWAH</span>
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
                                        <span class="w-6 h-6 rounded-md bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            #{{ $index + 1 }}
                                        </span>
                                        <span class="font-bold text-slate-800 truncate">
                                            <span x-show="isMasked">{{ $rec->masked_nama }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nama }}</span>
                                        </span>
                                        <span class="text-slate-400 font-mono text-[10px] shrink-0">
                                            <span x-show="isMasked">{{ $rec->masked_nik }}</span>
                                            <span x-show="!isMasked" style="display:none;">{{ $rec->nomor_induk_kependudukan }}</span>
                                        </span>
                                    </div>
                                    <strong class="font-mono text-amber-700 font-black shrink-0 text-sm">
                                        {{ is_numeric($displayVal) ? number_format((float)$displayVal, (floor((float)$displayVal) == (float)$displayVal ? 0 : 2), ',', '.') : $displayVal }}
                                    </strong>
                                </div>
                                <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="bg-amber-500 h-full rounded-full transition-all" style="width: {{ $barWidth }}%"></div>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-slate-400 text-center py-4">Belum ada data untuk diperingkatkan.</p>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- Smart Response Variabel Disparitas & Ranking -->
            @php
                $numMax = is_numeric($kpiMax) ? (float)$kpiMax : 0;
                $numMin = is_numeric($kpiMin) ? (float)$kpiMin : 0;
                $numAvg = is_numeric($kpiAvg) ? (float)$kpiAvg : 0;
                $disparity = max(0, $numMax - $numMin);
                $ratioMaxAvg = $numAvg > 0 ? round($numMax / $numAvg, 1) : 1;
                $varLabel = $activeColumnsMap[$kpiTargetVar] ?? $kpiTargetVar;
            @endphp
            <div class="pt-3 border-t border-slate-100">
                <div class="p-4 rounded-xl border border-purple-100 bg-gradient-to-r from-purple-50/70 via-indigo-50/40 to-slate-50 space-y-3">
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-lg bg-purple-600 text-white text-xs font-black shadow-xs">✨</span>
                            <span class="text-xs font-black uppercase tracking-wider text-purple-950">
                                Keterangan: Analisis Disparitas & Peringkat {{ $varLabel }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-[10px] font-mono font-bold text-purple-900">
                            <span class="px-2.5 py-0.5 rounded bg-white border border-purple-200">Selisih Teratas vs Terbawah: {{ number_format($disparity) }}</span>
                            <span class="px-2.5 py-0.5 rounded bg-white border border-purple-200">Teratas vs Rata-rata: {{ $ratioMaxAvg }}x lipat</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                        <div class="p-2.5 rounded-lg bg-white/95 border border-slate-200 shadow-2xs space-y-0.5">
                            <span class="text-[10px] text-slate-500 font-bold block">Nilai Paling Tinggi</span>
                            <strong class="font-mono text-emerald-700 text-sm block">
                                {{ is_numeric($kpiMax) ? number_format((float)$kpiMax, (floor((float)$kpiMax) == (float)$kpiMax ? 0 : 2), ',', '.') : $kpiMax }}
                            </strong>
                            <p class="text-[10px] text-slate-500 truncate">{{ $gajiMaxSubjek->nama ?? '-' }} (NIK: {{ $gajiMaxSubjek->masked_nik ?? '-' }})</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-white/95 border border-slate-200 shadow-2xs space-y-0.5">
                            <span class="text-[10px] text-slate-500 font-bold block">Rata-Rata Seluruh Warga</span>
                            <strong class="font-mono text-blue-700 text-sm block">
                                {{ number_format($kpiAvg, 2, ',', '.') }}
                            </strong>
                            <p class="text-[10px] text-slate-500">Nilai tengah dari total {{ number_format($totalRows) }} warga</p>
                        </div>
                        <div class="p-2.5 rounded-lg bg-white/95 border border-slate-200 shadow-2xs space-y-0.5">
                            <span class="text-[10px] text-slate-500 font-bold block">Nilai Paling Rendah</span>
                            <strong class="font-mono text-amber-700 text-sm block">
                                {{ is_numeric($kpiMin) ? number_format((float)$kpiMin, (floor((float)$kpiMin) == (float)$kpiMin ? 0 : 2), ',', '.') : $kpiMin }}
                            </strong>
                            <p class="text-[10px] text-slate-500 truncate">{{ $gajiMinSubjek->nama ?? '-' }} (NIK: {{ $gajiMinSubjek->masked_nik ?? '-' }})</p>
                        </div>
                    </div>

                    <div class="p-3 bg-white/95 rounded-lg border border-slate-200 text-xs text-slate-700 space-y-2 leading-relaxed">
                        <p class="flex items-start gap-1.5">
                            <span class="text-purple-600 font-bold shrink-0">📊</span>
                            <span>
                                <strong>Penjelasan Sederhana:</strong> Nilai paling tinggi berada di angka <strong>{{ number_format($numMax) }}</strong>, atau sekitar <strong>{{ $ratioMaxAvg }} kali lipat</strong> di atas rata-rata warga umum ({{ number_format($numAvg) }}). 
                                @if($ratioMaxAvg > 5)
                                    Perbedaan ini tergolong sangat jauh (jomplang) antara yang tertinggi dengan warga kebanyakan.
                                @else
                                    Perbedaan nilai pada kelompok ini tergolong wajar dan seimbang di sekitar angka rata-rata.
                                @endif
                            </span>
                        </p>
                        <div class="p-2.5 rounded-lg bg-purple-50 border border-purple-200 text-purple-950 text-xs space-y-1">
                            <div class="font-extrabold flex items-center gap-1.5 text-purple-900">
                                <span>💡</span> Solusi & Tindakan Lapangan:
                            </div>
                            <p class="text-[11px] text-purple-900 font-medium">
                                @if($ratioMaxAvg > 5)
                                    Lakukan pemeriksaan ulang (verifikasi lapangan) untuk memastikan data orang dengan nilai paling ekstrem ini tidak salah ketik angka (human error) sebelum dijadikan acuan resmi.
                                @else
                                    Data sudah cukup konsisten dan aman digunakan sebagai dasar pengambilan keputusan di tingkat satker/daerah.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

</div>
@endsection
