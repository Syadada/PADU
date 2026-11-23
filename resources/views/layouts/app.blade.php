<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PADU - Pengolah & Analisis Data Terpadu (DTSEN 2026)</title>
    
    <!-- Flat Icon UIcons CDN & Master Stylesheet -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@3.3.1/css/all/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ @filemtime(public_path('css/app.css')) ?: '2.0' }}">
    <script src="{{ asset('js/app.js') }}?v={{ @filemtime(public_path('js/app.js')) ?: '2.5' }}"></script>
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
                        <p class="text-[11px] text-slate-500 font-medium truncate mt-1">Pengolah & Analisis Data Terpadu</p>
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
        </div>

        <!-- 2. SCROLLABLE SIDEBAR NAVIGATION -->
        <div class="flex-1 overflow-y-auto p-3.5 space-y-5 scrollbar-thin">
            
            <!-- GROUP: MODUL UTAMA PADU -->
            <div class="space-y-1">
                <div class="px-2 pb-1 text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                    <span>Menu Utama</span>
                    <span class="text-[9px] text-blue-600 font-mono font-bold bg-blue-50 px-1.5 py-0.2 rounded border border-blue-200">DTSEN 2026</span>
                </div>

                <!-- 1. DATA (Load Dataset) -->
                <a href="{{ route('dtsen.files') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.files') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <i class="fi fi-rr-folder-download text-base shrink-0"></i>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate font-black">1. DATA</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.files') ? 'text-white/80' : 'text-slate-400' }} font-normal">Load & Ingesti Dataset</div>
                    </div>
                    @if(isset($srcDtsenFiles) && count($srcDtsenFiles) > 0)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold {{ request()->routeIs('dtsen.files') ? 'bg-white/20 text-white' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ count($srcDtsenFiles) }} file
                        </span>
                    @endif
                </a>

                <!-- 2. Pengolahan (Tabel Master Data Terpadu) -->
                <a href="{{ route('dtsen.data') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ (request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index')) ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <i class="fi fi-rr-table text-base shrink-0"></i>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate font-black">2. Pengolahan</div>
                        <div class="text-[10px] {{ (request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index')) ? 'text-white/80' : 'text-slate-400' }} font-normal">Tabel Master Data</div>
                    </div>
                    @if(isset($totalRows) && $totalRows > 0)
                        <span class="px-1.5 py-0.5 rounded text-[9px] font-mono font-bold {{ (request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index')) ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                            {{ number_format($totalRows) }}
                        </span>
                    @endif
                </a>

                <!-- 3. KPI metrix -->
                <a href="{{ route('dtsen.kpi') }}" 
                   class="sidebar-nav-item flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.kpi') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900 border border-transparent hover:border-slate-200' }}">
                    <i class="fi fi-rr-chart-pie-alt text-base shrink-0"></i>
                    <div class="min-w-0 flex-1">
                        <div class="leading-tight truncate font-black">3. KPI metrix</div>
                        <div class="text-[10px] {{ request()->routeIs('dtsen.kpi') ? 'text-white/80' : 'text-slate-400' }} font-normal">Metrik & Demografi</div>
                    </div>
                </a>
            </div>

            <!-- GROUP: MODUL ANALISIS LANJUTAN (Kualitas, Gaji, Audit) -->
            <div class="space-y-1 pt-3 border-t border-slate-200/80">
                <div class="px-2 pb-1 text-[9px] font-black uppercase tracking-wider text-slate-400 flex items-center justify-between">
                    <span>Analisis Spesifik</span>
                </div>
                <a href="{{ route('dtsen.quality') }}" class="sidebar-nav-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.quality') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-2.5">
                        <i class="fi fi-rr-shield-check text-sm shrink-0"></i>
                        <span class="truncate">Kualitas Data (QC)</span>
                    </div>
                    @if(isset($validCount))
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-bold {{ request()->routeIs('dtsen.quality') ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                            {{ number_format($validCount) }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('dtsen.salary') }}" class="sidebar-nav-item flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.salary') ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30 font-black' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <i class="fi fi-rr-dollar text-sm shrink-0"></i>
                    <span class="truncate">Analisis Gaji</span>
                </a>
                <a href="{{ route('dtsen.audit') }}" class="sidebar-nav-item flex items-center justify-between px-3 py-2 rounded-xl text-xs font-bold transition-all {{ request()->routeIs('dtsen.audit') ? 'bg-rose-600 text-white shadow-md shadow-rose-600/30 font-black' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
                    <div class="flex items-center gap-2.5">
                        <i class="fi fi-rr-triangle-warning text-sm shrink-0 {{ request()->routeIs('dtsen.audit') ? 'text-white' : 'text-rose-500' }}"></i>
                        <span class="truncate">Audit Anomali</span>
                    </div>
                    @if(isset($criticalCount) && $criticalCount > 0)
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-mono font-black {{ request()->routeIs('dtsen.audit') ? 'bg-white text-rose-600' : 'bg-rose-500 text-white animate-pulse' }}">
                            {{ number_format($criticalCount) }}
                        </span>
                    @endif
                </a>
            </div>

            <!-- GROUP: AKSI CEPAT & ALAT DATA OPERASIONAL -->
            <div class="space-y-1.5 pt-4 border-t border-slate-200">
                <div class="px-2 pb-1 text-[10px] font-black uppercase tracking-wider text-slate-400">
                    Operasional
                </div>

                <!-- Sensor -->
                <button type="button" 
                        @click="toggleMasking()" 
                        class="w-full sidebar-nav-item flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-bold transition-all bg-white hover:bg-slate-50 text-slate-700 cursor-pointer border border-slate-200 shadow-xs">
                    <div class="flex items-center gap-2.5">
                        <i class="fi text-sm" :class="isMasked ? 'fi-rr-lock' : 'fi-rr-unlock'"></i>
                        <span>Sensor</span>
                    </div>
                    <span class="px-2 py-0.5 rounded text-[9px] font-black tracking-wider uppercase"
                          :class="isMasked ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                          x-text="isMasked ? 'MASKED' : 'UNMASKED'"></span>
                </button>

                <!-- Ekspor -->
                <button type="button" 
                        @click="showExportModal = true" 
                        class="w-full sidebar-nav-item flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold transition-all bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 cursor-pointer shadow-xs">
                    <i class="fi fi-rr-upload text-sm"></i>
                    <span>Ekspor</span>
                </button>

                <!-- Unduh Format Master Template -->
                <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Template Master</span>
                    <div class="grid grid-cols-2 gap-1.5 text-center">
                        <a href="{{ route('dtsen.template', ['format' => 'xlsx']) }}" class="px-2 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-800 border border-emerald-200 text-[11px] font-bold transition-all block">
                            <i class="fi fi-rr-file-excel text-emerald-600"></i> Excel
                        </a>
                        <a href="{{ route('dtsen.template', ['format' => 'csv']) }}" class="px-2 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-800 border border-blue-200 text-[11px] font-bold transition-all block">
                            <i class="fi fi-rr-file-spreadsheet text-blue-600"></i> CSV
                        </a>
                    </div>
                </div>
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
                        <span class="flex items-center gap-1"><i class="fi fi-rr-check text-emerald-600 text-[10px]"></i> Valid:</span>
                        <strong>{{ number_format($validCount ?? 0) }}</strong>
                    </div>
                    <div class="flex justify-between items-center text-rose-700">
                        <span class="flex items-center gap-1"><i class="fi fi-rr-cross-circle text-rose-600 text-[10px]"></i> Critical:</span>
                        <strong>{{ number_format($criticalCount ?? 0) }}</strong>
                    </div>
                </div>
            @endif

        </div>

        <!-- 3. SIDEBAR FOOTER (ENGINE STATUS) -->
        <div class="p-3.5 border-t border-slate-200 bg-slate-50 shrink-0 text-slate-500 text-[11px]">
            <div class="flex items-center justify-between text-slate-800 font-bold">
                <span class="flex items-center gap-1.5">
                    <i class="fi fi-rr-bolt text-amber-500"></i> DuckDB OLAP Engine
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-700 font-mono border border-slate-300">Ultra-Fast</span>
            </div>
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
                        <!-- Flat Icon Menu Burger -->
                        <i class="fi fi-rr-menu-burger text-sm"></i>
                        <span class="text-xs font-bold" x-text="sidebarOpen ? 'Tutup' : 'Menu'"></span>
                    </button>

                    <!-- Breadcrumb Trail -->
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <a href="{{ route('dtsen.data') }}" class="hover:text-blue-600 transition-colors font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fi fi-rr-apps text-blue-600 text-sm"></i>
                            <span>PADU</span>
                        </a>
                        <span class="text-slate-400">/</span>
                        <span class="font-extrabold text-blue-700 inline-flex items-center gap-1.5">
                            @if(request()->routeIs('dtsen.data') || request()->routeIs('dtsen.index'))
                                <i class="fi fi-rr-table text-sm"></i> <span>Data</span>
                            @elseif(request()->routeIs('dtsen.quality'))
                                <i class="fi fi-rr-shield-check text-sm"></i> <span>Kualitas</span>
                            @elseif(request()->routeIs('dtsen.kpi'))
                                <i class="fi fi-rr-chart-pie-alt text-sm"></i> <span>KPI</span>
                            @elseif(request()->routeIs('dtsen.salary'))
                                <i class="fi fi-rr-dollar text-sm"></i> <span>Gaji</span>
                            @elseif(request()->routeIs('dtsen.audit'))
                                <i class="fi fi-rr-triangle-warning text-sm"></i> <span>Audit</span>
                            @elseif(request()->routeIs('dtsen.files'))
                                <i class="fi fi-rr-folder text-sm"></i> <span>Berkas</span>
                            @elseif(request()->routeIs('dtsen.activity-logs'))
                                <i class="fi fi-rr-list-check text-sm"></i> <span>Log</span>
                            @else
                                <i class="fi fi-rr-apps text-sm"></i> <span>Modul</span>
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
                    <button type="button" 
                            @click="toggleMasking()" 
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border transition-all text-xs font-bold cursor-pointer shadow-2xs whitespace-nowrap"
                            :class="isMasked ? 'bg-rose-50 text-rose-700 border-rose-300 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-300 hover:bg-emerald-100'"
                            :title="isMasked ? 'Sensor Aktif: Klik untuk Membuka Masking NIK/KK' : 'Sensor Terbuka: Klik untuk Menutup / Sensor NIK/KK'">
                        <i class="fi text-xs" :class="isMasked ? 'fi-rr-lock' : 'fi-rr-unlock'"></i>
                        <span>Sensor:</span>
                        <span class="font-black uppercase text-[10px] tracking-wider" x-text="isMasked ? 'Masked' : 'Unmasked'"></span>
                    </button>

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
                                        @if(method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                            <i class="fi fi-rr-crown"></i> Super Admin
                                        @else
                                            <i class="fi fi-rr-user"></i> Operator
                                        @endif
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
                                        <i class="fi fi-rr-user" style="font-size: 14px;"></i>
                                        <span style="font-size: 12px;">Profil & Akun Saya</span>
                                    </a>

                                    @if(auth()->check() && method_exists(auth()->user(), 'isSuperAdmin') && auth()->user()->isSuperAdmin())
                                        <div class="profile-dropdown-divider"></div>
                                        <div style="padding: 4px 16px 2px 16px; font-size: 9px; font-weight: 800; text-transform: uppercase; color: #94a3b8;">
                                            Menu Super Administrator
                                        </div>
                                        <a href="{{ route('admin.users') }}" class="profile-dropdown-link">
                                            <i class="fi fi-rr-users" style="font-size: 14px;"></i>
                                            <span style="font-size: 12px;">Direktori Staf / Akun</span>
                                        </a>
                                        <a href="{{ route('admin.audit-logs') }}" class="profile-dropdown-link">
                                            <i class="fi fi-rr-shield-check" style="font-size: 14px;"></i>
                                            <span style="font-size: 12px;">Audit Log Forensik</span>
                                        </a>
                                        <a href="{{ route('admin.backup') }}" class="profile-dropdown-link">
                                            <i class="fi fi-rr-database" style="font-size: 14px;"></i>
                                            <span style="font-size: 12px;">Pencadangan Sistem AES</span>
                                        </a>
                                        <a href="{{ route('admin.two-factor') }}" class="profile-dropdown-link">
                                            <i class="fi fi-rr-smartphone" style="font-size: 14px;"></i>
                                            <span style="font-size: 12px;">Keamanan 2FA TOTP</span>
                                        </a>
                                        <a href="{{ route('admin.metadata') }}" class="profile-dropdown-link">
                                            <i class="fi fi-rr-document-signed" style="font-size: 14px;"></i>
                                            <span style="font-size: 12px;">Metadata & Quality Check (BAST)</span>
                                        </a>
                                    @endif
                                </div>

                                @if(Route::has('logout'))
                                    <div style="border-top: 1px solid #f1f5f9; padding: 6px 0;">
                                        <form action="{{ route('logout') }}" method="POST" onsubmit="return confirm('Keluar dari sesi sistem PADU sekarang?');">
                                            @csrf
                                            <button type="submit" class="profile-dropdown-item" style="width: 100%; text-align: left; background: transparent; border: none; color: #e11d48; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 10px; padding: 8px 16px;">
                                                <i class="fi fi-rr-sign-out-alt" style="font-size: 14px;"></i>
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
                        <i class="fi fi-rr-check-circle text-base text-emerald-600"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-700 font-bold hover:text-emerald-900 text-sm cursor-pointer flex items-center"><i class="fi fi-rr-cross"></i></button>
                </div>
            </div>
        @endif

        @if(session('warning'))
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-triangle-warning text-base text-amber-600"></i>
                        <span>{{ session('warning') }}</span>
                    </div>
                    <button @click="show = false" class="text-amber-700 font-bold hover:text-amber-900 text-sm cursor-pointer flex items-center"><i class="fi fi-rr-cross"></i></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
                <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-xs font-semibold flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-2">
                        <i class="fi fi-rr-cross-circle text-base text-rose-600"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-700 font-bold hover:text-rose-900 text-sm cursor-pointer flex items-center"><i class="fi fi-rr-cross"></i></button>
                </div>
            </div>
        @endif

        <!-- MAIN PAGE CONTENT AREA -->
        <main class="flex-1 max-w-[1600px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </main>

        <!-- FOOTER -->
        <footer class="bg-white border-t border-slate-200 py-4 mt-auto">
            <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
                <p><strong>PADU</strong> &copy; {{ date('Y') }} — Pengolah & Analisis Data Terpadu.</p>
                <div class="text-[11px] text-slate-400">
                    Engine Analisis Data Cepat &bull; DTSEN 2026
                </div>
            </div>
        </footer>

    </div>

    <!-- SHARED GLOBAL MODALS -->
    @include('layouts.modals')

    @stack('scripts')
</body>
</html>
