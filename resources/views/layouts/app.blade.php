<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PADU - Pengolah & Analisis Data Terpadu (DTSEN 2026)</title>
    
    <!-- 100% Pure Offline Assets (Murni Lokal, Tanpa Request Jaringan Luar / Google Fonts) -->
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        /* Anti-Underline Enterprise Reset: Hilangkan semua garis bawah pada teks, link, dan tombol */
        *, *::before, *::after {
            text-decoration: none !important;
        }
        a, a *, a:hover, a:focus, a:active, a:visited,
        button, button *, .btn, [role="button"],
        .nav-link, .sidebar-nav-item, .profile-dropdown-link,
        th, td, span, p, h1, h2, h3, h4, h5, h6, label {
            text-decoration: none !important;
        }
    </style>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body class="min-h-screen flex antialiased text-slate-800 bg-slate-50 font-sans" 
      x-data="paduLayoutApp()"
      x-init="init()">

    <!-- ================= MOBILE SIDEBAR BACKDROP ================= -->
    <div x-show="sidebarOpen && isMobile" 
         x-cloak
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeSidebar()" 
         class="padu-sidebar-backdrop" 
         style="display: none;">
    </div>

    <!-- ================= ENTERPRISE PERSISTENT LEFT SIDEBAR ================= -->
    <aside class="padu-sidebar-aside"
           :class="sidebarOpen ? 'open' : 'closed'"
           aria-label="Sidebar Navigasi">
        
        <!-- 1. BRANDING & LOGO PADU -->
        <div class="p-4 border-b border-slate-200 bg-white shrink-0">
            <div class="flex items-center justify-between gap-2">
                <a href="{{ route('dtsen.data') }}" class="flex items-center gap-2.5 group min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-500 flex items-center justify-center text-white font-black text-lg shadow-md shadow-blue-600/30 group-hover:scale-105 transition-all shrink-0">
                        P
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="font-extrabold text-slate-900 text-sm tracking-tight leading-none truncate">PADU</span>
                            <span class="px-1.5 py-0.5 text-[9px] uppercase font-black bg-blue-50 text-blue-700 border border-blue-200 rounded-md shrink-0">v2.0</span>
                        </div>
                        <p class="text-[11px] text-slate-500 font-medium truncate mt-1">DTSEN 2026 &bull; Standar BSSN</p>
                    </div>
                </a>
                
                <!-- Close Button Inside Sidebar Header -->
                <button type="button" 
                        @click="closeSidebar()" 
                        class="padu-sidebar-close-btn shrink-0"
                        title="Tutup Sidebar"
                        aria-label="Tutup Sidebar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <div class="mt-3 flex items-center justify-between px-2.5 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[10px] font-semibold text-emerald-800">
                <span class="flex items-center gap-1.5 text-emerald-700 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    100% Offline Lokal
                </span>
                <span>UU PDP No. 27/2022</span>
            </div>
        </div>

        <!-- 2. SCROLLABLE SIDEBAR NAVIGATION -->
        <div class="flex-1 overflow-y-auto p-3.5 space-y-5 scrollbar-thin">
            
            <!-- GROUP: MODUL ANALISIS PADU -->
            <div class="space-y-1">
                <div class="px-2 pb-1 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                    <span>Modul Analisis Data</span>
                    <span class="text-[9px] text-blue-600 font-mono font-bold bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">DTSEN 2026</span>
                </div>

                <!-- 1. Tabel Master Data (58 Variabel) -->
                <a href="{{ route('dtsen.data') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ (request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index')) ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">📋</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Tabel Master Data</div>
                        <div class="text-[10px] {{ (request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index')) ? 'text-white/80' : 'text-slate-400' }} font-normal">58 Variabel Resmi DTSEN</div>
                    </div>
                    @if(isset($totalRows) && $totalRows > 0)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold {{ (request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index')) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            {{ number_format($totalRows) }}
                        </span>
                    @endif
                </a>

                <!-- 2. Metrik Kualitas & Ringkasan Data (QC) -->
                <a href="{{ route('dtsen.quality') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.quality') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">🧮</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Metrik Kualitas (QC)</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.quality') ? 'text-white/80' : 'text-slate-400' }} font-normal">Validasi Integritas Data</div>
                    </div>
                    @if(isset($validCount))
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold {{ request()->routeIs('dtsen.quality') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                            {{ number_format($validCount) }}
                        </span>
                    @endif
                </a>

                <!-- 3. Diagram KPI & Peringkat Variabel -->
                <a href="{{ route('dtsen.kpi') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.kpi') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">📈</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Diagram KPI & Ranking</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.kpi') ? 'text-white/80' : 'text-slate-400' }} font-normal">Peringkat Top / Bottom</div>
                    </div>
                </a>

                <!-- 4. Analisis Finansial & Gaji Subjek -->
                <a href="{{ route('dtsen.salary') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.salary') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">💵</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Analisis Finansial Gaji</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.salary') ? 'text-white/80' : 'text-slate-400' }} font-normal">Sebaran Pendapatan Subjek</div>
                    </div>
                </a>

                <!-- 5. Audit Temuan Error & Anomali -->
                <a href="{{ route('dtsen.audit') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.audit') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">⚠️</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Audit Temuan Error</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.audit') ? 'text-white/80' : 'text-slate-400' }} font-normal">Deteksi Kritis & Anomali</div>
                    </div>
                    @if(isset($criticalCount) && $criticalCount > 0)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-black bg-rose-500 text-white animate-pulse">
                            {{ number_format($criticalCount) }}
                        </span>
                    @endif
                </a>

                <!-- 6. Manajemen Berkas & Ingesti / Ekspor -->
                <a href="{{ route('dtsen.files') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.files') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">📁</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Manajemen Berkas</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.files') ? 'text-white/80' : 'text-slate-400' }} font-normal">Folder <code>src-dtsen/</code> & Ingesti</div>
                    </div>
                    @if(isset($srcDtsenFiles) && count($srcDtsenFiles) > 0)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold {{ request()->routeIs('dtsen.files') ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ count($srcDtsenFiles) }} file
                        </span>
                    @endif
                </a>

            </div>

            <!-- GROUP: PUSAT AKUN & ADMINISTRASI -->
            <div class="space-y-1.5 pt-3 border-t border-slate-200">
                <div class="px-2 pb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">
                    Akun & Administrasi
                </div>

                <!-- Profil & Akun Pengguna -->
                <a href="{{ route('profile') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('profile') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <span class="text-base shrink-0">👤</span>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate">Profil & Akun Saya</div>
                        <div class="text-[10px] {{ request()->routeIs('profile') ? 'text-white/80' : 'text-slate-400' }} font-normal">Kredensial & Pengaturan</div>
                    </div>
                </a>

                @if(auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                    <!-- Modul Khusus Super Administrator -->
                    <a href="{{ route('admin.users') }}" 
                       class="sidebar-nav-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.users') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                        <span class="text-base shrink-0">👥</span>
                        <div class="min-w-0 flex-1">
                            <div class="leading-tight truncate">Direktori Staf / Akun</div>
                            <div class="text-[10px] {{ request()->routeIs('admin.users') ? 'text-white/80' : 'text-slate-400' }} font-normal">Manajemen Operator</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.audit-logs') }}" 
                       class="sidebar-nav-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.audit-logs') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                        <span class="text-base shrink-0">🛡️</span>
                        <div class="min-w-0 flex-1">
                            <div class="leading-tight truncate">Audit Forensik BSSN</div>
                            <div class="text-[10px] {{ request()->routeIs('admin.audit-logs') ? 'text-white/80' : 'text-slate-400' }} font-normal">Rekaman Log Siber</div>
                        </div>
                    </a>

                    <a href="{{ route('admin.backup') }}" 
                       class="sidebar-nav-item flex items-center gap-3 px-3 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('admin.backup') ? 'bg-purple-600 text-white shadow-md shadow-purple-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                        <span class="text-base shrink-0">🗜️</span>
                        <div class="min-w-0 flex-1">
                            <div class="leading-tight truncate">Pencadangan AES-256</div>
                            <div class="text-[10px] {{ request()->routeIs('admin.backup') ? 'text-white/80' : 'text-slate-400' }} font-normal">Arsip Cadangan DRP</div>
                        </div>
                    </a>
                @endif
            </div>

            <!-- GROUP: AKSI CEPAT & ALAT DATA OPERASIONAL -->
            <div class="space-y-1.5 pt-4 border-t border-slate-200">
                <div class="px-2 pb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">
                    Alat Cepat & Operasional
                </div>

                <!-- Sakelar Masking Privasi -->
                <button type="button" 
                        @click="toggleMasking()" 
                        class="w-full sidebar-nav-item flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-50 text-slate-700 cursor-pointer border border-slate-200 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm" x-text="isMasked ? '🔒' : '🔓'"></span>
                        <span>Sensor Privasi</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[9px] font-black tracking-wider uppercase"
                          :class="isMasked ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                          x-text="isMasked ? 'MASKED' : 'UNMASKED'"></span>
                </button>

                <!-- Ekspor Paket (.ZIP) -->
                <button type="button" 
                        @click="showExportModal = true" 
                        class="w-full sidebar-nav-item flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold transition-all bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 cursor-pointer shadow-xs">
                    <span class="text-sm">📤</span>
                    <span>Ekspor Paket (.ZIP)</span>
                </button>

                <!-- Log Konsol Realtime -->
                <button type="button" 
                        @click="openLogConsole()" 
                        class="w-full sidebar-nav-item flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 cursor-pointer shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <span class="text-sm">📋</span>
                        <span>Log Aktivitas Sistem</span>
                    </div>
                    <span x-show="logList && logList.length > 0" class="px-1.5 py-0.2 rounded-full bg-blue-600 text-white text-[9px] font-mono font-bold" x-text="logList.length"></span>
                </button>

                <!-- Unduh Format Master Template -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Unduh Format Master</span>
                    <div class="grid grid-cols-2 gap-1.5 text-center">
                        <a href="{{ route('dtsen.template', ['format' => 'xlsx']) }}" class="px-2 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold transition-all block">
                            🟢 Excel
                        </a>
                        <a href="{{ route('dtsen.template', ['format' => 'csv']) }}" class="px-2 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-[11px] font-bold transition-all block">
                            📄 CSV
                        </a>
                    </div>
                </div>

                @if(isset($totalRows) && $totalRows > 0)
                    <!-- Kosongkan Dataset -->
                    <button type="button" 
                            @click="showClearModal = true" 
                            class="w-full sidebar-nav-item flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-all bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 cursor-pointer">
                        <span class="text-sm">🗑️</span>
                        <span>Kosongkan Dataset</span>
                    </button>
                @endif
            </div>

            <!-- SNAPSHOT DATASET MINI STATS -->
            @if(isset($totalRows) && $totalRows > 0)
                <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 text-xs space-y-2 font-mono">
                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-200">
                        <span class="text-[10px] text-slate-500 font-bold uppercase tracking-wider font-sans">Snapshot Data</span>
                        <span class="text-[9px] text-emerald-700 font-bold bg-emerald-100 px-1.5 py-0.2 rounded border border-emerald-200">LIVE</span>
                    </div>
                    <div class="flex justify-between items-center text-slate-700">
                        <span>Total Baris:</span>
                        <strong class="text-slate-900 font-bold">{{ number_format($totalRows) }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-emerald-700">
                        <span>🟢 Valid:</span>
                        <strong>{{ number_format($validCount ?? 0) }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-rose-700">
                        <span>🔴 Critical:</span>
                        <strong>{{ number_format($criticalCount ?? 0) }}</strong>
                    </div>
                </div>
            @endif

        </div>

        <!-- 3. SIDEBAR FOOTER (ENGINE & KEDAULATAN SISTEM) -->
        <div class="p-3.5 border-t border-slate-200 bg-slate-50 shrink-0 text-slate-500 text-[11px] space-y-1">
            <div class="flex items-center justify-between text-slate-800 font-bold">
                <span class="flex items-center gap-1.5">
                    <span class="text-amber-500">⚡</span> DuckDB OLAP Engine
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 font-mono border border-slate-300">Ultra-Fast</span>
            </div>
            <p class="text-[10px] text-slate-400">Arsitektur Kedaulatan Lepas Kunci</p>
        </div>

    </aside>

    <!-- ================= MAIN CONTENT WRAPPER ================= -->
    <div class="padu-main-wrapper"
         :class="sidebarOpen ? 'sidebar-open' : 'sidebar-closed'">
        
        <!-- HEADER TOP BAR SOLID STICKY -->
        <header class="sticky top-0 z-50 bg-white"
                style="background: #ffffff !important; opacity: 1 !important; border-bottom: 1px solid #e2e8f0; box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.06);">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">
                
                <!-- Left: Sidebar Toggle Button & Active Module Breadcrumb -->
                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="toggleSidebar()" 
                            class="padu-navbar-toggle-btn"
                            :title="sidebarOpen ? 'Sembunyikan Sidebar' : 'Tampilkan Sidebar'"
                            aria-label="Toggle Sidebar">
                        <!-- Icon Toggle (Hamburger / Panel) -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <line x1="3" y1="12" x2="21" y2="12"></line>
                            <line x1="3" y1="18" x2="21" y2="18"></line>
                        </svg>
                        <span class="text-xs font-bold" x-text="sidebarOpen ? 'Tutup' : 'Menu'"></span>
                    </button>

                    <!-- Breadcrumb Trail -->
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <a href="{{ route('dtsen.data') }}" class="hover:text-blue-600 transition-colors font-bold text-slate-700 flex items-center gap-1.5">
                            <span>📊</span>
                            <span class="hidden sm:inline">PADU</span>
                        </a>
                        <span>/</span>
                        <span class="font-extrabold text-blue-700">
                            @if(request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index'))
                                📋 Tabel Master Data
                            @elseif(request()->routeIs('dtsen.quality'))
                                🧮 Metrik Kualitas (QC)
                            @elseif(request()->routeIs('dtsen.kpi'))
                                📈 Diagram KPI & Peringkat
                            @elseif(request()->routeIs('dtsen.salary'))
                                💵 Analisis Finansial Gaji
                            @elseif(request()->routeIs('dtsen.audit'))
                                ⚠️ Audit Temuan Error
                            @elseif(request()->routeIs('dtsen.files'))
                                📁 Manajemen Berkas Data
                            @else
                                Modul Analisis
                            @endif
                        </span>
                    </div>
                </div>

                <!-- Right Controls: Status Data, Quick Masking Pill, and User Menu -->
                <div class="flex items-center gap-2.5 shrink-0">
                    
                    @if(isset($totalRows) && $totalRows > 0)
                        <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-emerald-50 text-emerald-800 rounded-xl border border-emerald-200 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span>{{ number_format($totalRows) }} Data Dimuat</span>
                        </div>
                    @endif

                    <!-- Quick Privacy Toggle -->
                    <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                        <button @click="setMasked(true)" 
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                                :class="isMasked ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                            <span>🔒</span>
                            <span class="hidden md:inline">Masked</span>
                        </button>
                        <button @click="setMasked(false)" 
                                class="px-2.5 py-1 rounded-lg text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                                :class="!isMasked ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                            <span>🔓</span>
                            <span class="hidden md:inline">Unmasked</span>
                        </button>
                    </div>

                    @auth
                        <!-- Dropdown Menu Profil & Pusat Administrasi Sistem -->
                        <div class="relative pl-1 border-l border-slate-200" x-data="{ openProfileMenu: false }" @click.outside="openProfileMenu = false">
                            <button type="button" 
                                    @click="openProfileMenu = !openProfileMenu" 
                                    class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl hover:bg-slate-100 border border-transparent hover:border-slate-200 transition-all text-left cursor-pointer group focus:outline-none"
                                    :class="openProfileMenu ? 'bg-slate-100 border-slate-200 shadow-xs' : ''"
                                    title="Buka Menu Profil & Administrasi">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs {{ method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin() ? 'bg-amber-100 text-amber-800 border border-amber-300' : 'bg-blue-100 text-blue-800 border border-blue-300' }}">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="hidden sm:block text-left">
                                    <span class="block text-xs font-extrabold text-slate-800 group-hover:text-blue-600 transition-colors leading-tight">{{ auth()->user()->name }}</span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold uppercase tracking-wider {{ method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin() ? 'text-amber-600' : 'text-blue-600' }}">
                                        {{ method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin() ? '👑 Super Admin' : '👤 Operator' }}
                                    </span>
                                </div>
                                <svg class="chevron-arrow text-slate-400 ml-1" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" :style="openProfileMenu ? 'transform: rotate(180deg);' : ''">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>

                            <!-- Dropdown Menu Opaque -->
                            <div x-show="openProfileMenu" 
                                 x-cloak
                                 x-transition
                                 class="profile-dropdown-card"
                                 style="background: #ffffff !important; opacity: 1 !important; z-index: 99999 !important;">
                                
                                <div style="padding: 12px 16px; border-bottom: 1px solid #f1f5f9; background: #f8fafc;">
                                    <span style="font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; display: block;">Akun Terautentikasi</span>
                                    <span style="font-weight: 800; font-size: 13px; color: #0f172a; display: block; margin-top: 2px;">{{ auth()->user()->name }}</span>
                                    <span style="font-family: monospace; font-size: 11px; color: #64748b; display: block;">{{ auth()->user()->email }}</span>
                                </div>

                                <div style="padding: 6px 0;">
                                    <a href="{{ route('profile') }}" class="profile-dropdown-link">
                                        <span style="font-size: 14px;">👤</span>
                                        <span style="font-size: 12px;">Profil & Akun Saya</span>
                                    </a>

                                    @if(auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                        <div class="profile-dropdown-divider"></div>
                                        <div style="padding: 4px 16px 2px 16px; font-size: 9px; font-weight: 800; text-transform: uppercase; color: #94a3b8;">
                                            Menu Super Administrator
                                        </div>
                                        <a href="{{ route('admin.users') }}" class="profile-dropdown-link">
                                            <span style="font-size: 14px;">👥</span>
                                            <span style="font-size: 12px;">Direktori Staf / Akun</span>
                                        </a>
                                        <a href="{{ route('admin.audit-logs') }}" class="profile-dropdown-link">
                                            <span style="font-size: 14px;">🛡️</span>
                                            <span style="font-size: 12px;">Audit Log Forensik</span>
                                        </a>
                                        <a href="{{ route('admin.backup') }}" class="profile-dropdown-link">
                                            <span style="font-size: 14px;">🗜️</span>
                                            <span style="font-size: 12px;">Pencadangan Sistem AES</span>
                                        </a>
                                        <a href="{{ route('admin.two-factor') }}" class="profile-dropdown-link">
                                            <span style="font-size: 14px;">📱</span>
                                            <span style="font-size: 12px;">Keamanan 2FA TOTP</span>
                                        </a>
                                    @endif
                                </div>

                                @if(Route::has('logout'))
                                    <div style="border-top: 1px solid #f1f5f9; padding: 6px 0;">
                                        <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Keluar dari sesi sistem PADU sekarang?');">
                                            @csrf
                                            <button type="submit" class="profile-dropdown-item" style="width: 100%; text-align: left; background: transparent; border: none; color: #e11d48; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 8px 16px;">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="width: 15px; height: 15px; min-width: 15px;">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                                </svg>
                                                <span style="font-size: 12px;">Keluar Sesi Aman</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endauth

                </div>

            </div>
        </header>

        <!-- Alert Notifikasi Flash Messages Global -->
        @if(session('success'))
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <span class="text-base">✅</span>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-700 font-bold hover:text-emerald-900 text-sm cursor-pointer">✕</button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚠️</span>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button @click="show = false" class="text-amber-700 font-bold hover:text-amber-900 text-sm cursor-pointer">✕</button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <span class="text-base">❌</span>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-700 font-bold hover:text-rose-900 text-sm cursor-pointer">✕</button>
                </div>
            </div>
        @endif

        <!-- MAIN PAGE CONTENT AREA -->
        <main class="flex-1 max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>

        <!-- FOOTER STANDAR BSSN -->
        <footer class="bg-white border-t border-slate-200 py-6 mt-auto">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <p><strong>PADU</strong> &copy; {{ date('Y') }} — Pengolah & Analisis Data Terpadu (Kedaulatan Mandiri Lepas Kunci).</p>
                <div class="flex items-center gap-4 text-[11px]">
                    <span class="text-emerald-600 font-bold flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        100% Offline Standalone
                    </span>
                    <span>Standar BSSN & UU PDP No. 27/2022</span>
                </div>
            </div>
        </footer>

    </div>

    <!-- SHARED GLOBAL MODALS -->
    @include('layouts.modals')

    @stack('scripts')

    <!-- GLOBAL ALPINE LAYOUT APP STATE -->
    <script>
    function paduLayoutApp() {
        return {
            sidebarOpen: window.innerWidth >= 1024 
                ? (localStorage.getItem('padu_sidebar_open') !== 'false') 
                : false,
            isMobile: window.innerWidth < 1024,
            isMasked: localStorage.getItem('padu_is_masked') !== 'false',
            showExportModal: false,
            showClearModal: false,
            showPreviewModal: false,
            showLogModal: false,
            showImportProgressModal: false,
            previewData: null,
            
            // Console Logs State
            logList: [],
            logFilter: 'ALL',
            logPollingInterval: null,

            // Import Progress State
            importProgressPercent: 0,
            importProgressMessage: 'Menyiapkan pemrosesan data...',
            importEtaSeconds: 0,
            progressPollTimer: null,
            isProcessing: false,

            get filteredLogs() {
                if (this.logFilter === 'ALL') return this.logList;
                return this.logList.filter(l => l.level === this.logFilter);
            },

            init() {
                window.addEventListener('resize', () => {
                    const wasMobile = this.isMobile;
                    this.isMobile = window.innerWidth < 1024;
                    if (wasMobile !== this.isMobile && !this.isMobile) {
                        this.sidebarOpen = localStorage.getItem('padu_sidebar_open') !== 'false';
                    }
                });

                window.addEventListener('padu-masking-changed', (e) => {
                    this.isMasked = !!e.detail;
                });
                this.fetchLogs();
            },

            toggleSidebar() {
                this.sidebarOpen = !this.sidebarOpen;
                if (!this.isMobile) {
                    localStorage.setItem('padu_sidebar_open', this.sidebarOpen ? 'true' : 'false');
                }
            },

            closeSidebar() {
                this.sidebarOpen = false;
                if (!this.isMobile) {
                    localStorage.setItem('padu_sidebar_open', 'false');
                }
            },

            openSidebar() {
                this.sidebarOpen = true;
                if (!this.isMobile) {
                    localStorage.setItem('padu_sidebar_open', 'true');
                }
            },

            setMasked(val) {
                this.isMasked = val;
                localStorage.setItem('padu_is_masked', val ? 'true' : 'false');
                window.dispatchEvent(new CustomEvent('padu-masking-changed', { detail: val }));
            },

            toggleMasking() {
                this.setMasked(!this.isMasked);
            },

            formatEta(sec) {
                if (!sec || sec <= 0) return '5 detik';
                if (sec >= 60) {
                    const m = Math.floor(sec / 60);
                    const s = sec % 60;
                    return m + ' m ' + (s < 10 ? '0' : '') + s + ' s';
                }
                return sec + ' detik';
            },

            openLogConsole() {
                this.showLogModal = true;
                this.fetchLogs();
                if (this.logPollingInterval) clearInterval(this.logPollingInterval);
                this.logPollingInterval = setInterval(() => {
                    if (this.showLogModal) {
                        this.fetchLogs();
                    } else {
                        clearInterval(this.logPollingInterval);
                    }
                }, 2000);
            },

            async fetchLogs() {
                try {
                    const res = await fetch('/dtsen/logs', {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (res.ok) {
                        const data = await res.json();
                        if (data && data.success && Array.isArray(data.logs)) {
                            this.logList = data.logs;
                        }
                    }
                } catch (e) {}
            },

            async clearSystemLogs() {
                if (!confirm('Bersihkan seluruh catatan log aktivitas sistem?')) return;
                try {
                    const res = await fetch('/dtsen/logs/clear', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    });
                    if (res.ok) {
                        this.fetchLogs();
                    }
                } catch (e) {}
            },

            downloadSystemLogsTxt() {
                if (!this.filteredLogs || this.filteredLogs.length === 0) {
                    alert('Belum ada log aktivitas untuk diekstrak.');
                    return;
                }
                const text = this.filteredLogs.map(l => `[${l.timestamp}] [${l.level}] ${l.message}`).join('\r\n');
                const blob = new Blob([text], { type: 'text/plain;charset=utf-8' });
                const url = URL.createObjectURL(blob);
                const link = document.createElement('a');
                const d = new Date();
                link.href = url;
                link.download = `padu_activity_log_${d.toISOString().slice(0, 10)}.txt`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(url);
            },

            async cancelCurrentImport() {
                if (!confirm('⚠️ Batalkan proses injeksi dataset ke database?\n\nSemua proses impor yang sedang berjalan akan dihentikan.')) {
                    return;
                }
                try {
                    const res = await fetch('/dtsen/cancel-import', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await res.json();
                    if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                    this.showImportProgressModal = false;
                    this.isProcessing = false;
                    alert(data.message || 'Proses injeksi data berhasil dibatalkan.');
                    this.fetchLogs();
                    setTimeout(() => { window.location.reload(); }, 500);
                } catch (err) {
                    alert('Gagal membatalkan impor: ' + err.message);
                }
            },

            openPreview(id) {
                fetch('/dtsen/preview/' + id)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.previewData = data.data;
                            this.showPreviewModal = true;
                        }
                    });
            },

            async startLocalImportSubmit(e) {
                if (e) e.preventDefault();
                this.isProcessing = true;
                this.showImportProgressModal = true;
                this.importProgressPercent = 5;
                this.importProgressMessage = 'Membaca data dari berkas CSV...';
                this.importEtaSeconds = 5;

                if (this.progressPollTimer) clearInterval(this.progressPollTimer);

                let startTime = Date.now();
                let totalEstSec = 6;
                let isPollingActive = false;
                let hasServerProgress = false;

                this.progressPollTimer = setInterval(() => {
                    let elapsedSec = (Date.now() - startTime) / 1000;

                    if (!hasServerProgress) {
                        let ratio = Math.min(elapsedSec / totalEstSec, 0.95);
                        let calcPct = Math.min(95, Math.round(5 + (ratio * 90)));
                        if (calcPct > this.importProgressPercent) {
                            this.importProgressPercent = calcPct;
                        }
                        let calcEta = Math.max(1, Math.round(totalEstSec - elapsedSec));
                        this.importEtaSeconds = calcEta;

                        if (this.importProgressPercent < 25) {
                            this.importProgressMessage = 'Membaca data dari berkas CSV...';
                        } else if (this.importProgressPercent < 50) {
                            this.importProgressMessage = 'Memvalidasi NIK, KK, & kualitas data...';
                        } else if (this.importProgressPercent < 75) {
                            this.importProgressMessage = 'Menyiapkan & mengurutkan data...';
                        } else if (this.importProgressPercent < 92) {
                            this.importProgressMessage = 'Memasukkan data ke dalam database...';
                        } else {
                            this.importProgressMessage = 'Membangun indeks B-Tree pencarian database...';
                        }
                    }

                    if (!isPollingActive) {
                        isPollingActive = true;
                        fetch('/dtsen/import-progress', { headers: { 'Accept': 'application/json' } })
                            .then(res => res.ok ? res.json() : null)
                            .then(data => {
                                if (data && data.percent > 0) {
                                    hasServerProgress = true;
                                    if (data.percent >= this.importProgressPercent || data.percent === 100) {
                                        this.importProgressPercent = data.percent;
                                    }
                                    if (data.message) {
                                        this.importProgressMessage = data.message;
                                    }
                                    if (typeof data.eta_seconds !== 'undefined') {
                                        this.importEtaSeconds = data.eta_seconds;
                                    }
                                }
                            })
                            .catch(() => {})
                            .finally(() => { isPollingActive = false; });
                    }
                }, 400);

                const form = e.target;
                const formData = new FormData(form);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });

                    const result = await response.json();
                    if (this.progressPollTimer) clearInterval(this.progressPollTimer);

                    if (result.success) {
                        this.importProgressPercent = 100;
                        this.importProgressMessage = 'Proses Berhasil!';
                        setTimeout(() => {
                            window.location.href = "{{ route('dtsen.data') }}";
                        }, 600);
                    } else {
                        this.showImportProgressModal = false;
                        this.isProcessing = false;
                        alert('Gagal memproses data: ' + (result.message || 'Terjadi kesalahan sistem.'));
                    }
                } catch (err) {
                    if (this.progressPollTimer) clearInterval(this.progressPollTimer);
                    this.showImportProgressModal = false;
                    this.isProcessing = false;
                    alert('Terjadi kesalahan koneksi saat mengunggah berkas: ' + err.message);
                }
            }
        };
    }
    </script>
</body>
</html>
