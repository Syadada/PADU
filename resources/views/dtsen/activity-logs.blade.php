@extends('layouts.app')

@section('title', 'Log Aktivitas Sistem Realtime — PADU DTSEN 2026')

@section('styles')
<style>
    /* Styling Data Table Mengikuti Standar Folder Formulir App */
    .wp-table-row:hover {
        background-color: #f8fafc !important;
    }
    .wp-row-actions {
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.12s ease-in-out, visibility 0.12s ease-in-out;
        font-size: 11px;
        margin-top: 4px;
        line-height: 1.2;
    }
    .wp-table-row:hover .wp-row-actions {
        visibility: visible;
        opacity: 1;
    }
    .wp-row-actions a:hover,
    .wp-row-actions button:hover {
        text-decoration: underline !important;
    }
</style>
@endsection

@section('content')
<div class="space-y-6" style="max-width: 100%; margin: 0 auto; color: #0f172a;">

    <!-- Banner Notifikasi Sukses / Pesan Sistem -->
    @if(session('success'))
        <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 14px 18px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <i class="fi fi-rr-check-circle" style="font-size: 18px; color: #16a34a;"></i>
                <span style="font-size: 13px; font-weight: 600; color: #166534;">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 14px; color: #16a34a; cursor: pointer; display: flex; align-items: center;"><i class="fi fi-rr-cross"></i></button>
        </div>
    @endif

    <!-- Card Utama Log Aktivitas (Tema Formulir: Background Putih, Teks Hitam, Border Lembut) -->
    <div class="card" style="background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
        
        <!-- Header Judul & Tombol-tombol Aksi -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 14px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 22px; border: 1px solid #dbeafe;">
                    <i class="fi fi-rr-list-check"></i>
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <h1 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0; letter-spacing: -0.3px;">
                            Log Aktivitas Sistem Realtime
                        </h1>
                        <span style="background: #eff6ff; color: #2563eb; font-size: 11px; font-weight: 700; padding: 2px 10px; border-radius: 9999px; border: 1px solid #bfdbfe;">
                            LIVE MONITOR
                        </span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin: 3px 0 0 0;">
                        Rekam jejak pemrosesan ingesti data, agregasi DuckDB, validasi kualitas (Quality Check), dan operasional sistem PADU DTSEN 2026.
                    </p>
                </div>
            </div>

            <!-- Tombol Aksi Toolbar -->
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <!-- Auto Refresh Switch -->
                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; color: #475569; background: #f8fafc; border: 1px solid #e2e8f0; padding: 7px 12px; border-radius: 8px; cursor: pointer;">
                    <input type="checkbox" id="autoRefreshToggle" onchange="toggleAutoRefresh(this.checked)" style="accent-color: #2563eb; cursor: pointer;">
                    <span>Auto-Refresh (5s)</span>
                </label>

                <!-- Tombol Segarkan -->
                <a href="{{ route('dtsen.activity-logs', request()->query()) }}" 
                   style="background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                    <i class="fi fi-rr-refresh"></i>
                    <span>Segarkan Data</span>
                </a>

                <!-- Tombol Salin Semua Log -->
                <button type="button" id="btnCopyAllLogs" onclick="copyAllLogsToClipboard()" 
                        style="background: #ffffff; color: #1e293b; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#ffffff'">
                    <i class="fi fi-rr-copy"></i>
                    <span>Salin Semua Log</span>
                </button>

                <!-- Tombol Ekspor TXT -->
                <a href="{{ route('dtsen.logs.download') }}" 
                   style="background: #0f172a; color: #ffffff; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                   onmouseover="this.style.background='#1e293b'" onmouseout="this.style.background='#0f172a'">
                    <i class="fi fi-rr-download"></i>
                    <span>Ekspor Berkas (.txt)</span>
                </a>

                <!-- Tombol Bersihkan Log -->
                <form action="{{ route('dtsen.logs.clear') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengosongkan seluruh berkas log aktivitas sistem? Tindakan ini tidak dapat dibatalkan.');" style="margin: 0;">
                    @csrf
                    <button type="submit" 
                            style="background: #fff5f5; color: #b91c1c; border: 1px solid #fecaca; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 12px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: background 0.15s ease;"
                            onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fff5f5'">
                        <i class="fi fi-rr-trash"></i>
                        <span>Bersihkan Log</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- 5 Kotak KPI / Statistik Ringkas (Format Sesuai Folder Formulir) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 14px; margin-bottom: 24px;">
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Total Entri Log</div>
                <div style="font-size: 22px; font-weight: 800; color: #0f172a;">{{ number_format($totalLogs) }}</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Log Hari Ini</div>
                <div style="font-size: 22px; font-weight: 800; color: #2563eb;">{{ number_format($todayCount) }}</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Operasi Sukses (SUCCESS)</div>
                <div style="font-size: 22px; font-weight: 800; color: #16a34a;">{{ number_format($successCount) }}</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Peringatan / Batal (WARN)</div>
                <div style="font-size: 22px; font-weight: 800; color: #d97706;">{{ number_format($warningCount) }}</div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px;">
                <div style="font-size: 12px; font-weight: 600; color: #64748b; margin-bottom: 4px;">Kendala / Error (FAIL)</div>
                <div style="font-size: 22px; font-weight: 800; color: #dc2626;">{{ number_format($errorCount) }}</div>
            </div>
        </div>

        <!-- Tab Navigasi Subsubsub WordPress Style (Mengikuti Standar Folder Formulir) -->
        <div class="wp-subsubsub" style="margin-bottom: 16px; font-size: 13px; color: #64748b; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
            <a href="{{ route('dtsen.activity-logs', array_merge(request()->except('level', 'page'), ['level' => 'ALL'])) }}" 
               style="text-decoration: none; color: {{ ($levelFilter === 'ALL' || empty($levelFilter)) ? '#0f172a' : '#2563eb' }}; font-weight: {{ ($levelFilter === 'ALL' || empty($levelFilter)) ? '700' : '500' }};">
                Semua <span style="color: #64748b; font-weight: normal;">({{ number_format($totalLogs) }})</span>
            </a>
            <span style="color: #cbd5e1;">|</span>
            <a href="{{ route('dtsen.activity-logs', array_merge(request()->except('level', 'page'), ['level' => 'INFO'])) }}" 
               style="text-decoration: none; color: {{ $levelFilter === 'INFO' ? '#0f172a' : '#2563eb' }}; font-weight: {{ $levelFilter === 'INFO' ? '700' : '500' }};">
                INFO <span style="color: #64748b; font-weight: normal;">({{ number_format($infoCount) }})</span>
            </a>
            <span style="color: #cbd5e1;">|</span>
            <a href="{{ route('dtsen.activity-logs', array_merge(request()->except('level', 'page'), ['level' => 'SUCCESS'])) }}" 
               style="text-decoration: none; color: {{ $levelFilter === 'SUCCESS' ? '#0f172a' : '#2563eb' }}; font-weight: {{ $levelFilter === 'SUCCESS' ? '700' : '500' }};">
                SUCCESS <span style="color: #64748b; font-weight: normal;">({{ number_format($successCount) }})</span>
            </a>
            <span style="color: #cbd5e1;">|</span>
            <a href="{{ route('dtsen.activity-logs', array_merge(request()->except('level', 'page'), ['level' => 'WARNING'])) }}" 
               style="text-decoration: none; color: {{ $levelFilter === 'WARNING' ? '#0f172a' : '#2563eb' }}; font-weight: {{ $levelFilter === 'WARNING' ? '700' : '500' }};">
                WARNING / BATAL <span style="color: #64748b; font-weight: normal;">({{ number_format($warningCount) }})</span>
            </a>
            <span style="color: #cbd5e1;">|</span>
            <a href="{{ route('dtsen.activity-logs', array_merge(request()->except('level', 'page'), ['level' => 'ERROR'])) }}" 
               style="text-decoration: none; color: {{ $levelFilter === 'ERROR' ? '#0f172a' : '#2563eb' }}; font-weight: {{ $levelFilter === 'ERROR' ? '700' : '500' }};">
                ERROR <span style="color: #64748b; font-weight: normal;">({{ number_format($errorCount) }})</span>
            </a>
        </div>

        <!-- Filter & Search Bar Row -->
        <div style="margin-bottom: 20px;">
            <form action="{{ route('dtsen.activity-logs') }}" method="GET" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <!-- Input Pencarian -->
                <div style="position: relative; width: 320px; max-width: 100%;">
                    <i class="fi fi-rr-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; pointer-events: none;"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kata kunci, modul, atau pesan..." 
                           style="width: 100%; height: 38px; padding-left: 36px; padding-right: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #1e293b; outline: none; background: #ffffff; box-sizing: border-box;">
                </div>

                <!-- Dropdown Level -->
                <select name="level" onchange="this.form.submit()" 
                        style="height: 38px; padding: 0 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #1e293b; background: #f8fafc; cursor: pointer; outline: none;">
                    <option value="ALL" {{ ($levelFilter === 'ALL' || empty($levelFilter)) ? 'selected' : '' }}>Semua Level</option>
                    <option value="INFO" {{ $levelFilter === 'INFO' ? 'selected' : '' }}>INFO</option>
                    <option value="SUCCESS" {{ $levelFilter === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                    <option value="WARNING" {{ $levelFilter === 'WARNING' ? 'selected' : '' }}>WARNING / BATAL</option>
                    <option value="ERROR" {{ $levelFilter === 'ERROR' ? 'selected' : '' }}>ERROR</option>
                </select>

                <button type="submit" 
                        style="height: 38px; padding: 0 16px; background: #0f172a; color: #ffffff; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fi fi-rr-search"></i>
                    <span>Cari Log</span>
                </button>

                @if(request('search') || (request('level') && request('level') !== 'ALL'))
                    <a href="{{ route('dtsen.activity-logs') }}" 
                       style="height: 38px; padding: 0 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; color: #475569; background: #ffffff; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fi fi-rr-cross-small"></i> Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- ============================================================== -->
        <!-- DATA TABLE AKTIVITAS SISTEM (MENGIKUTI STANDAR FORMULIR APP)  -->
        <!-- ============================================================== -->
        <div class="table-responsive" style="overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 10px; background: #ffffff;">
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; background: #ffffff;">
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 70px;">
                            NO
                        </th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 170px;">
                            WAKTU & TANGGAL
                        </th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 140px; text-align: center;">
                            STATUS LEVEL
                        </th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px;">
                            RINCIAN AKTIVITAS / PESAN SISTEM
                        </th>
                        <th style="padding: 12px 16px; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; width: 100px; text-align: center;">
                            AKSI
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $idx => $item)
                        @php
                            $rowNum = method_exists($logs, 'firstItem') ? ($logs->firstItem() + $idx) : ($idx + 1);
                            $lvl = $item['level'];
                        @endphp
                        <tr class="wp-table-row" style="border-bottom: 1px solid #f1f5f9; transition: background-color 0.15s ease;">
                            <!-- 1. Nomor Baris -->
                            <td style="padding: 14px 16px; vertical-align: top; font-size: 12px; font-weight: 700; color: #64748b; font-family: var(--font-mono, monospace);">
                                {{ $rowNum }}
                            </td>

                            <!-- 2. Waktu & Tanggal -->
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-size: 12px; color: #0f172a; font-weight: 600; font-family: var(--font-mono, monospace);">
                                    {{ \Carbon\Carbon::parse($item['timestamp'])->format('d/m/Y') }}
                                </div>
                                <div style="font-size: 11px; color: #64748b; margin-top: 2px; font-family: var(--font-mono, monospace);">
                                    {{ \Carbon\Carbon::parse($item['timestamp'])->format('H:i:s') }} WIB
                                </div>
                            </td>

                            <!-- 3. Status Level Badge Pill (Sesuai Gaya Formulir) -->
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center;">
                                @if($lvl === 'SUCCESS')
                                    <span style="background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fi fi-rr-check"></i> <span>SUCCESS</span>
                                    </span>
                                @elseif($lvl === 'INFO')
                                    <span style="background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fi fi-rr-info"></i> <span>INFO</span>
                                    </span>
                                @elseif($lvl === 'WARNING' || $lvl === 'CANCEL')
                                    <span style="background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fi fi-rr-triangle-warning"></i> <span>{{ $lvl }}</span>
                                    </span>
                                @elseif($lvl === 'ERROR')
                                    <span style="background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fi fi-rr-cross-circle"></i> <span>ERROR</span>
                                    </span>
                                @else
                                    <span style="background: #f1f5f9; color: #334155; border: 1px solid #e2e8f0; font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; display: inline-flex; align-items: center; gap: 4px;">
                                        <i class="fi fi-rr-bullet"></i> <span>{{ $lvl }}</span>
                                    </span>
                                @endif
                            </td>

                            <!-- 4. Rincian Pesan Log (Teks Hitam Pekat, Kontras Tinggi) -->
                            <td style="padding: 14px 16px; vertical-align: top;">
                                <div style="font-size: 13px; font-weight: 600; color: #0f172a; line-height: 1.5; word-break: break-word;">
                                    {{ $item['message'] }}
                                </div>
                                
                                <!-- Baris Aksi WordPress Style Saat Mouse Hover -->
                                <div class="wp-row-actions">
                                    <a href="javascript:void(0)" onclick="copyLogMessage(this, '{{ addslashes($item['message']) }}')" 
                                       style="color: #2563eb; text-decoration: none; font-weight: 500;">
                                        Salin Pesan
                                    </a>
                                    <span style="color: #cbd5e1; margin: 0 4px;">|</span>
                                    <a href="javascript:void(0)" onclick="copyLogFullLine(this, '[{{ $item['timestamp'] }}] [{{ $lvl }}] {{ addslashes($item['message']) }}')" 
                                       style="color: #64748b; text-decoration: none; font-weight: 500;">
                                        Salin Format Lengkap
                                    </a>
                                </div>
                            </td>

                            <!-- 5. Tombol Aksi Cepat Salin -->
                            <td style="padding: 14px 16px; vertical-align: top; text-align: center;">
                                <button type="button" 
                                        onclick="copyLogFullLine(this, '[{{ $item['timestamp'] }}] [{{ $lvl }}] {{ addslashes($item['message']) }}')" 
                                        style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 600; color: #475569; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;"
                                        title="Salin baris log ini ke clipboard">
                                    <i class="fi fi-rr-copy"></i> <span>Salin</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                                <div style="font-size: 32px; margin-bottom: 8px;"><i class="fi fi-rr-document"></i></div>
                                <div style="font-weight: 700; font-size: 14px; color: #334155;">Belum Ada Catatan Log Aktivitas</div>
                                <div style="font-size: 12px; color: #64748b; margin-top: 3px;">
                                    Tidak ada log yang sesuai dengan kata kunci pencarian atau filter yang dipilih.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination (Menggunakan Compact Pagination Bawaan) -->
        @if(method_exists($logs, 'hasPages') && $logs->hasPages())
            <div style="padding: 16px 0 0 0; margin-top: 16px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                <div style="font-size: 12px; color: #64748b;">
                    Menampilkan <strong>{{ $logs->firstItem() }}</strong> sampai <strong>{{ $logs->lastItem() }}</strong> dari <strong>{{ number_format($logs->total()) }}</strong> entri log
                </div>
                <div>
                    {{ $logs->links('vendor.pagination.compact') }}
                </div>
            </div>
        @else
            <div style="padding: 14px 0 0 0; margin-top: 14px; font-size: 12px; color: #64748b;">
                Menampilkan total <strong>{{ count($logs) }}</strong> entri log
            </div>
        @endif

    </div>
</div>

<script>
let autoRefreshTimer = null;

function toggleAutoRefresh(enabled) {
    if (enabled) {
        autoRefreshTimer = setInterval(() => {
            window.location.reload();
        }, 5000);
    } else {
        if (autoRefreshTimer) {
            clearInterval(autoRefreshTimer);
            autoRefreshTimer = null;
        }
    }
}

function copyLogMessage(el, text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            const originalText = el.innerText;
            el.innerHTML = '<i class="fi fi-rr-check"></i> Tersalin!';
            setTimeout(() => { el.innerText = originalText; }, 1500);
        });
    } else {
        fallbackCopy(text);
    }
}

function copyLogFullLine(btn, text) {
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(() => {
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<i class="fi fi-rr-check"></i> <span>Tersalin</span>';
            btn.style.color = '#16a34a';
            btn.style.borderColor = '#bbf7d0';
            btn.style.background = '#f0fdf4';
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.style.color = '';
                btn.style.borderColor = '';
                btn.style.background = '';
            }, 1500);
        });
    } else {
        fallbackCopy(text);
    }
}

async function copyAllLogsToClipboard() {
    const btn = document.getElementById('btnCopyAllLogs');
    try {
        const res = await fetch('{{ route("dtsen.logs") }}', {
            headers: { 'Accept': 'application/json' }
        });
        if (res.ok) {
            const data = await res.json();
            if (data && data.logs && data.logs.length > 0) {
                const text = data.logs.map(l => `[${l.timestamp}] [${l.level}] ${l.message}`).join('\n');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(text);
                } else {
                    fallbackCopy(text);
                }
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fi fi-rr-check"></i> Berhasil Disalin!';
                btn.style.color = '#16a34a';
                setTimeout(() => {
                    btn.innerHTML = orig;
                    btn.style.color = '';
                }, 2000);
                return;
            }
        }
        alert('Tidak ada log untuk disalin.');
    } catch (e) {
        alert('Gagal mengambil data log.');
    }
}

function fallbackCopy(text) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
        document.execCommand('copy');
        alert('Berhasil menyalin log!');
    } catch (e) {
        alert('Gagal menyalin log.');
    }
    document.body.removeChild(ta);
}
</script>
@endsection

