@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" 
     x-data="{
        showResetModal: {{ session('temp_password') ? 'true' : 'false' }},
        tempUserName: '{{ session('temp_user_name', '') }}',
        tempUserEmail: '{{ session('temp_user_email', '') }}',
        tempPassword: '{{ session('temp_password', '') }}',
        copied: false,
        copyTempPassword() {
            navigator.clipboard.writeText(this.tempPassword);
            this.copied = true;
            setTimeout(() => this.copied = false, 2500);
        }
     }">

    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-1 text-xs font-bold bg-purple-100 text-purple-900 border border-purple-300 rounded-lg uppercase tracking-wider">
                    Tata Kelola Staf (RBAC)
                </span>
                <span class="px-2.5 py-1 text-xs font-bold bg-blue-100 text-blue-800 border border-blue-300 rounded-lg">
                    🛡️ Admin-Assisted Reset
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                Manajemen Direktori Staf & Operator
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Pengelolaan hak akses pengguna, reset kata sandi luring aman bagi staf, dan pemulihan penguncian akun otomatis (*Lockout Release*).
            </p>
        </div>

        <div class="flex items-center gap-2 text-xs font-bold text-slate-500 bg-slate-50 px-4 py-2 rounded-xl border border-slate-200">
            <span>👥 Total Pengguna: {{ count($users) }}</span>
        </div>
    </div>

    <!-- Alert Notifikasi Reset Berhasil -->
    @if(session('success') && !session('temp_password'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-semibold flex items-center gap-2">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tabel Pengguna -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider">
                    <tr>
                        <th class="py-3 px-4">Nama Pengguna</th>
                        <th class="py-3 px-4">Alamat Surel</th>
                        <th class="py-3 px-4">Tingkat Hak Akses</th>
                        <th class="py-3 px-4">Status Akun</th>
                        <th class="py-3 px-4">Keamanan 2FA</th>
                        <th class="py-3 px-4">Percobaan Gagal</th>
                        <th class="py-3 px-4 text-center">Tindakan Otoritas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($users as $u)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-slate-900 flex items-center gap-2">
                                <span class="text-base">{{ $u->isSuperAdmin() ? '👑' : '👤' }}</span>
                                <span>{{ $u->name }}</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ $u->email }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $u->isSuperAdmin() ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-blue-100 text-blue-800 border-blue-300' }}">
                                    {{ $u->isSuperAdmin() ? 'Super Administrator' : 'Operator Data' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->isLocked())
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 border border-rose-300 animate-pulse">
                                        🔒 TERKUNCI ({{ ceil($u->remainingLockoutSeconds() / 60) }} mnt)
                                    </span>
                                @elseif($u->first_login)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 border border-amber-300">
                                        ⚠️ Wajib Reset Sandi
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        ✓ Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($u->two_factor_enabled)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        📱 2FA Aktif
                                    </span>
                                @else
                                    <span class="text-slate-400 text-xs">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-mono">
                                @if($u->failed_login_attempts > 0)
                                    <span class="text-rose-600 font-bold">{{ $u->failed_login_attempts }} / 5</span>
                                @else
                                    <span class="text-slate-400">0</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    
                                    <!-- Buka Kunci Akun jika Terkunci -->
                                    @if($u->isLocked())
                                        <form action="{{ route('admin.users.unlock', $u->id) }}" method="POST" onsubmit="return confirm('Buka kunci akun ini sekarang?');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition-colors">
                                                🔓 Buka Kunci
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Reset Sandi Khusus Operator -->
                                    @if($u->isOperator())
                                        <form action="{{ route('admin.users.reset-password', $u->id) }}" method="POST" onsubmit="return confirm('⚠️ Reset kata sandi staf ini? Sistem akan membuat sandi sementara baru minimal 15 karakter.');">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold border border-slate-300 transition-colors">
                                                🔑 Reset Sandi
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-[11px] text-slate-400 italic">Akun Pimpinan</span>
                                    @endif

                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL PENAMPIL KATA SANDI SEMENTARA HASIL RESET ADMIN -->
    <div x-show="showResetModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md">
        <div class="rounded-2xl max-w-lg w-full p-6 space-y-5 border border-slate-700 shadow-2xl text-white" style="background-color: #0f172a !important;" @click.outside="showResetModal = false">
            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                <div class="flex items-center gap-2 text-emerald-400 font-bold text-base">
                    <span class="text-xl">✅</span>
                    <span>Kata Sandi Sementara Berhasil Dibuat</span>
                </div>
                <button @click="showResetModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
            </div>

            <div class="p-3.5 rounded-xl bg-blue-500/10 border border-blue-500/30 text-blue-300 text-xs leading-relaxed space-y-1">
                <p class="font-bold text-blue-200">ℹ️ SOP Admin-Assisted Offline Reset:</p>
                <p>
                    Serahkan kata sandi sementara ini kepada staf yang bersangkutan secara luring (offline). Staf <strong>wajib mengganti kata sandi</strong> ini dengan kata sandi baru saat pertama kali login.
                </p>
            </div>

            <div class="space-y-3">
                <div>
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">PENGGUNA / SUREL:</span>
                    <div class="bg-black/60 p-2.5 rounded-lg text-xs font-bold text-slate-200 border border-slate-800">
                        <span x-text="tempUserName"></span> &bull; <span class="font-mono text-slate-400" x-text="tempUserEmail"></span>
                    </div>
                </div>

                <div>
                    <span class="text-[11px] font-bold text-slate-400 block mb-1">KATA SANDI SEMENTARA (MIN. 15 KARAKTER):</span>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-black/80 p-3 rounded-xl font-mono text-base font-extrabold text-amber-400 border border-amber-500/30 tracking-wider text-center select-all" x-text="tempPassword"></div>
                        <button type="button" @click="copyTempPassword()" class="px-4 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs shrink-0 transition-colors">
                            <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <div class="pt-2 text-right">
                <button type="button" @click="showResetModal = false" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold transition-colors">
                    Selesai & Tutup
                </button>
            </div>
        </div>
    </div>

</div>
@endsection
