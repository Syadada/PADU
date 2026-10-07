@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8"
     x-data="{
        newPassword: '',
        confirmPassword: '',
        showPass: false,
        get checks() {
            return {
                length: this.newPassword.length >= 15,
                upper: /[A-Z]/.test(this.newPassword),
                lower: /[a-z]/.test(this.newPassword),
                number: /[0-9]/.test(this.newPassword),
                symbol: /[\W_]/.test(this.newPassword)
            };
        },
        get allValid() {
            return this.checks.length && this.checks.upper && this.checks.lower && this.checks.number && this.checks.symbol;
        },
        get passwordsMatch() {
            return this.newPassword && this.newPassword === this.confirmPassword;
        }
     }">

    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                <a href="{{ route('dtsen.index') }}" class="hover:text-blue-600 transition-colors">Beranda</a>
                <span>/</span>
                <span class="text-slate-800 font-bold">Akun & Profil Pengguna</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-black text-slate-900 flex items-center gap-2.5">
                <span>Pengaturan Profil & Keamanan</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 font-medium mt-1">
                Pengelolaan kredensial mandiri terotentikasi dan verifikasi kepatuhan standar keamanan siber BSSN.
            </p>
        </div>

        @if(auth()->user()->isSuperAdmin())
            <!-- Jembatan Navigasi ke Modul Manajemen Staf / Akun -->
            <a href="{{ route('admin.users') }}" 
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all shrink-0">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Direktori Staf & Operator &rarr;</span>
            </a>
        @endif
    </div>

    <!-- Banner Kepatuhan Regulasi BSSN & UU PDP -->
    <div class="p-4 sm:p-5 rounded-2xl text-white shadow-lg border border-blue-800/40 relative overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%) !important;">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-blue-500/30 text-blue-200 border border-blue-400/30">
                        Standar Keamanan Siber BSSN No. 4/2021
                    </span>
                    <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider rounded-md bg-emerald-500/30 text-emerald-200 border border-emerald-400/30">
                        UU PDP No. 27/2022
                    </span>
                </div>
                <h3 class="text-base font-extrabold text-white">Prinsip Self-Service & Akuntabilitas Identitas</h3>
                <p class="text-xs text-blue-200 max-w-2xl leading-relaxed">
                    Setiap staf berhak memperbarui nama tampilan serta kata sandi mandiri. Perubahan kata sandi mewajibkan konfirmasi sandi saat ini dan pemenuhan kompleksitas minimal 15 karakter dengan pencatatan jejak audit forensik.
                </p>
            </div>
            <div class="flex items-center gap-2 bg-white/10 p-3 rounded-xl border border-white/15 backdrop-blur shrink-0">
                <div class="text-center px-2">
                    <span class="block text-lg font-black text-amber-300">15+</span>
                    <span class="text-[9px] uppercase font-bold tracking-wider text-blue-100">Karakter Min.</span>
                </div>
                <div class="h-6 w-px bg-white/20"></div>
                <div class="text-center px-2">
                    <span class="block text-lg font-black text-emerald-300">100%</span>
                    <span class="text-[9px] uppercase font-bold tracking-wider text-blue-100">Audit Trail</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid 2 Kolom Profil & Keamanan -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Kolom Kiri: Kartu Identitas Akun & Navigasi Terkait (1 Kolom) -->
        <div class="space-y-6">
            
            <!-- Kartu Identitas Profil -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-black text-2xl shadow-inner {{ $user->isSuperAdmin() ? 'bg-amber-100 text-amber-800 border-2 border-amber-300' : 'bg-blue-100 text-blue-800 border-2 border-blue-300' }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h2 class="text-lg font-extrabold text-slate-900 leading-tight">{{ $user->name }}</h2>
                        <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider border {{ $user->isSuperAdmin() ? 'bg-amber-50 text-amber-700 border-amber-300' : 'bg-blue-50 text-blue-700 border-blue-300' }}">
                            {{ $user->isSuperAdmin() ? '👑 Super Administrator' : '👤 Operator Data' }}
                        </span>
                    </div>
                </div>

                <div class="space-y-3 pt-3 border-t border-slate-100 text-xs">
                    <div>
                        <span class="text-slate-400 font-semibold block text-[11px]">ALAMAT SUREL UTAMA</span>
                        <span class="font-mono font-bold text-slate-800">{{ $user->email }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block text-[11px]">STATUS KEAMANAN 2FA</span>
                        <div class="mt-1 flex items-center gap-2">
                            @if($user->two_factor_enabled)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                    📱 2FA TOTP Aktif
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-300">
                                    Belum Diaktifkan
                                </span>
                            @endif
                            @if($user->isSuperAdmin())
                                <a href="{{ route('admin.two-factor') }}" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                    Konfigurasi &rarr;
                                </a>
                            @endif
                        </div>
                    </div>
                    <div>
                        <span class="text-slate-400 font-semibold block text-[11px]">LOGIN TERAKHIR</span>
                        <span class="font-mono text-slate-600">
                            {{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M Y, H:i:s') : 'Baru Saja' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Kartu Jalan Pintas / Quick Links (Akses Terhubung) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Jalan Pintas Administrasi</h3>
                
                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.users') }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-purple-50 hover:text-purple-900 border border-slate-100 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">👥</span>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-purple-900">Kelola Direktori Staf</span>
                                <span class="text-[10px] text-slate-500">Tambah akun, edit, reset kata sandi staf</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-purple-600 font-bold">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.two-factor') }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-blue-50 hover:text-blue-900 border border-slate-100 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">📱</span>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-blue-900">Pengaturan 2FA TOTP</span>
                                <span class="text-[10px] text-slate-500">Aplikasi Authenticator ponsel pintar</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-blue-600 font-bold">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.audit-logs') }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-slate-100 hover:text-slate-900 border border-slate-100 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">📜</span>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-slate-900">Jejak Audit Forensik</span>
                                <span class="text-[10px] text-slate-500">Audit trail kepatuhan BSSN</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-slate-900 font-bold">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.backup') }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-emerald-50 hover:text-emerald-900 border border-slate-100 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">🗜️</span>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-emerald-900">Pencadangan Sistem AES-256</span>
                                <span class="text-[10px] text-slate-500">Arsip Cadangan & Pemulihan Bencana DRP</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-emerald-600 font-bold">&rarr;</span>
                    </a>

                    <a href="{{ route('admin.metadata') }}" class="flex items-center justify-between p-3 rounded-xl hover:bg-amber-50 hover:text-amber-900 border border-slate-100 transition-colors group">
                        <div class="flex items-center gap-2.5">
                            <span class="text-lg">📋</span>
                            <div>
                                <span class="block text-xs font-bold text-slate-800 group-hover:text-amber-900">Metadata & Quality Check (BAST)</span>
                                <span class="text-[10px] text-slate-500">Kamus Aturan BAST & Training Versi</span>
                            </div>
                        </div>
                        <span class="text-slate-400 group-hover:text-amber-600 font-bold">&rarr;</span>
                    </a>
                @else
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600">
                        <p class="font-bold text-slate-800 mb-1">ℹ️ Hak Akses Operator Data</p>
                        <p>Anda memiliki hak akses untuk memproses data kependudukan DTSEN. Hubungi Super Administrator apabila membutuhkan perubahan kewenangan peran.</p>
                    </div>
                @endif
            </div>

            <!-- Log Aktivitas Terakhir Pengguna Ini -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-3">
                <h3 class="text-xs font-black uppercase tracking-wider text-slate-400">Riwayat Aktivitas Terkini</h3>
                @if(count($recentLogs) > 0)
                    <div class="space-y-2">
                        @foreach($recentLogs as $log)
                            <div class="p-2.5 rounded-lg bg-slate-50 border border-slate-100 text-xs">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold text-slate-800">{{ $log->action }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $log->details }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-400 italic">Belum ada catatan aktivitas.</p>
                @endif
            </div>

        </div>

        <!-- Kolom Kanan: Form Edit Profil & Form Ganti Kata Sandi (2 Kolom) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- FORM 1: Perbarui Profil Dasar (Nama Lengkap) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                        <span>✏️ Ubah Informasi Profil Mandiri</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Perbarui nama tampilan resmi Anda di sistem. Sesuai aturan kepatuhan, surel dan peran hanya dapat dimodifikasi oleh Super Administrator.
                    </p>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label for="profile_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Nama Lengkap Resmi <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               id="profile_name"
                               name="name" 
                               aria-label="Nama Lengkap Resmi"
                               autocomplete="name"
                               value="{{ old('name', $user->name) }}" 
                               required 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-semibold focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div>
                        <label for="profile_email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Alamat Surel (Permanen / Terikat)
                        </label>
                        <input type="email" 
                               id="profile_email"
                               name="email"
                               aria-label="Alamat Surel Permanen"
                               autocomplete="email"
                               value="{{ $user->email }}" 
                               disabled 
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-slate-100 text-slate-500 text-xs font-mono font-medium cursor-not-allowed">
                        <span class="text-[10px] text-slate-400 mt-1 block">
                            🔒 Surel digunakan untuk identifikasi digital forensik dan tidak dapat diubah secara mandiri.
                        </span>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" 
                                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold shadow-sm transition-colors flex items-center gap-2">
                            <span>Simpan Perubahan Profil</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- FORM 2: Ubah Kata Sandi Mandiri (Standar BSSN 15+ Karakter) -->
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 space-y-5">
                <div class="border-b border-slate-100 pb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-wider bg-rose-100 text-rose-800 border border-rose-300">
                            Wajib BSSN
                        </span>
                        <h2 class="text-lg font-black text-slate-900">
                            🔑 Ubah Kata Sandi Akun
                        </h2>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Wajib menyertakan kata sandi saat ini untuk mencegah pembajakan sesi. Kata sandi baru harus memenuhi minimal 15 karakter kombinasi.
                    </p>
                </div>

                <form action="{{ route('profile.password') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label for="profile_current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Kata Sandi Saat Ini <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" 
                               id="profile_current_password"
                               name="current_password" 
                               aria-label="Kata Sandi Saat Ini"
                               autocomplete="current-password"
                               required 
                               placeholder="Masukkan kata sandi aktif Anda"
                               class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="profile_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input :type="showPass ? 'text' : 'password'" 
                                       id="profile_password"
                                       name="password" 
                                       aria-label="Kata Sandi Baru"
                                       autocomplete="new-password"
                                       x-model="newPassword" 
                                       required 
                                       placeholder="Minimal 15 karakter kompleks"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors pr-10">
                                <button type="button" 
                                        @click="showPass = !showPass" 
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-700 text-xs font-bold">
                                    <span x-text="showPass ? 'Sembunyi' : 'Lihat'"></span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label for="profile_password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
                            </label>
                            <input :type="showPass ? 'text' : 'password'" 
                                   id="profile_password_confirmation"
                                   name="password_confirmation" 
                                   aria-label="Konfirmasi Kata Sandi Baru"
                                   autocomplete="new-password"
                                   x-model="confirmPassword" 
                                   required 
                                   placeholder="Ulangi kata sandi baru"
                                   class="w-full px-4 py-2.5 rounded-xl border border-slate-300 text-xs font-medium focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        </div>
                    </div>

                    <!-- Checklist Interaktif Kepatuhan BSSN (Peraturan No. 4/2021) -->
                    <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600">
                                Evaluator Otomatis Kebijakan BSSN:
                            </span>
                            <span class="text-[11px] font-mono font-bold" :class="allValid ? 'text-emerald-600' : 'text-slate-400'">
                                Panjang: <span x-text="newPassword.length"></span> / 15
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                            <div class="flex items-center gap-2" :class="checks.length ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                                <span x-text="checks.length ? '✓' : '○'"></span>
                                <span>Minimal 15 karakter</span>
                            </div>
                            <div class="flex items-center gap-2" :class="checks.upper ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                                <span x-text="checks.upper ? '✓' : '○'"></span>
                                <span>Huruf Besar (A-Z)</span>
                            </div>
                            <div class="flex items-center gap-2" :class="checks.lower ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                                <span x-text="checks.lower ? '✓' : '○'"></span>
                                <span>Huruf Kecil (a-z)</span>
                            </div>
                            <div class="flex items-center gap-2" :class="checks.number ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                                <span x-text="checks.number ? '✓' : '○'"></span>
                                <span>Angka Numerik (0-9)</span>
                            </div>
                            <div class="flex items-center gap-2" :class="checks.symbol ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                                <span x-text="checks.symbol ? '✓' : '○'"></span>
                                <span>Simbol Khusus (!@#$%^&*)</span>
                            </div>
                            <div class="flex items-center gap-2" :class="passwordsMatch ? 'text-emerald-600 font-bold' : 'text-slate-400'">
                                <span x-text="passwordsMatch ? '✓' : '○'"></span>
                                <span>Konfirmasi Sandi Cocok</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end pt-2">
                        <button type="submit" 
                                :disabled="!allValid || !passwordsMatch"
                                :class="allValid && passwordsMatch ? 'bg-slate-900 hover:bg-slate-800 text-white cursor-pointer' : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                                class="px-6 py-2.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-2">
                            <span>Perbarui Kata Sandi</span>
                        </button>
                    </div>
                </form>
            </div>

        </div>

    </div>

</div>
@endsection
