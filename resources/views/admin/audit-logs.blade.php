@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="{ selectedMeta: null, showMetaModal: false }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-900 border border-amber-300 rounded-lg uppercase tracking-wider">
                    BSSN No. 4/2021 & UU PDP
                </span>
                <span class="px-2.5 py-1 text-xs font-bold bg-slate-100 text-slate-700 border border-slate-300 rounded-lg">
                    🔒 Immutable Append-Only
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                Audit Trail Forensik Digital
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Pencatatan rekam jejak aktivitas, autentikasi, lockout, dan perubahan data kependudukan secara permanen dan kebal manipulasi.
            </p>
        </div>

        <!-- Tombol Ekspor Laporan Forensik -->
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'csv'])) }}" 
               class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <span>📥</span>
                <span>Ekspor CSV</span>
            </a>
            <a href="{{ route('admin.audit-logs.export', array_merge(request()->query(), ['format' => 'txt'])) }}" 
               class="px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-sm transition-all flex items-center gap-1.5">
                <span>📄</span>
                <span>Ekspor TXT BSSN</span>
            </a>
        </div>
    </div>

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs text-slate-500 font-medium">Aktivitas Hari Ini</span>
            <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($totalLogsToday) }}</div>
            <span class="text-[10px] text-slate-400">Total entri tercatat</span>
        </div>
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs text-rose-600 font-medium">Percobaan Gagal</span>
            <div class="text-2xl font-black text-rose-600 mt-1">{{ number_format($totalFailedAttempts) }}</div>
            <span class="text-[10px] text-slate-400">Login / 2FA gagal</span>
        </div>
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs text-amber-600 font-medium">Insiden Lockout</span>
            <div class="text-2xl font-black text-amber-600 mt-1">{{ number_format($totalLockouts) }}</div>
            <span class="text-[10px] text-slate-400">Akun terkunci otomatis</span>
        </div>
        <div class="p-4 rounded-xl bg-white border border-slate-200 shadow-sm">
            <span class="text-xs text-emerald-600 font-medium">Total Backup Sistem</span>
            <div class="text-2xl font-black text-emerald-600 mt-1">{{ number_format($totalBackups) }}</div>
            <span class="text-[10px] text-slate-400">Arsip AES-256 dibuat</span>
        </div>
    </div>

    <!-- Filter & Pencarian Bar -->
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('admin.audit-logs') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
            <div class="sm:col-span-3">
                <label class="block font-bold text-slate-700 mb-1">Cari Kata Kunci / IP / Aktor:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, IP, deskripsi..." class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Jenis Aksi:</label>
                <select name="action" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">-- Semua Aksi --</option>
                    @foreach($actionTypes as $act)
                        <option value="{{ $act }}" {{ request('action') === $act ? 'selected' : '' }}>{{ $act }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Status Forensik:</label>
                <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                    <option value="">-- Semua Status --</option>
                    <option value="SUCCESS" {{ request('status') === 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                    <option value="WARNING" {{ request('status') === 'WARNING' ? 'selected' : '' }}>WARNING</option>
                    <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>FAILED</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Dari Tanggal:</label>
                <input type="date" name="date_start" value="{{ request('date_start') }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-2">
                <label class="block font-bold text-slate-700 mb-1">Sampai Tanggal:</label>
                <input type="date" name="date_end" value="{{ request('date_end') }}" class="w-full bg-slate-50 border border-slate-300 rounded-lg px-3 py-2 text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
            </div>
            <div class="sm:col-span-1 flex items-end gap-1">
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Audit Trail Forensik -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Waktu (WIB)</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Aksi Forensik</th>
                        <th class="py-3 px-4">Aktor Pengguna</th>
                        <th class="py-3 px-4">Alamat IP & Klien</th>
                        <th class="py-3 px-4">Uraian Aktivitas</th>
                        <th class="py-3 px-4 text-center">Metadata</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3 px-4 font-mono text-[11px] text-slate-600 whitespace-nowrap">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                @if($log->status === 'SUCCESS')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        SUCCESS
                                    </span>
                                @elseif($log->status === 'WARNING')
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        WARNING
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300">
                                        FAILED
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap font-mono text-slate-800 font-bold">
                                {{ $log->action }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <div class="font-bold text-slate-900">{{ $log->user_name ?? 'Sistem' }}</div>
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider {{ $log->user_role === 'superadmin' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $log->user_role ?? 'guest' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                <div>{{ $log->ip_address ?? '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-slate-700 max-w-md break-words">
                                {{ $log->description }}
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                @if(!empty($log->metadata))
                                    <button @click="selectedMeta = {{ json_encode($log->metadata) }}; showMetaModal = true;"
                                            class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded text-[11px] font-bold transition-colors">
                                        🔍 JSON
                                    </button>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-12 text-center text-slate-400 font-medium">
                                Belum ada rekaman audit trail yang sesuai dengan kriteria filter.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="p-4 border-t border-slate-200 bg-slate-50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    <!-- Modal Metadata JSON Inspector -->
    <div x-show="showMetaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm">
        <div class="rounded-2xl max-w-xl w-full p-6 space-y-4 border border-slate-700 shadow-2xl text-white" style="background-color: #0f172a !important;" @click.outside="showMetaModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <h3 class="font-bold text-sm text-slate-200 flex items-center gap-2">
                    <span>🔬</span>
                    <span>Detail Metadata Forensik</span>
                </h3>
                <button @click="showMetaModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
            </div>
            <pre class="bg-black/60 p-4 rounded-xl font-mono text-xs text-emerald-400 overflow-x-auto max-h-80 scrollbar-thin border border-slate-800" x-text="JSON.stringify(selectedMeta, null, 2)"></pre>
            <div class="text-right">
                <button @click="showMetaModal = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold">
                    Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
