@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6" x-data="twoFactorSetup()">

    <!-- Script Pure Offline QR Code Generator -->
    <script src="{{ asset('js/qrcode.min.js') }}"></script>

    <!-- Header Section (Glassmorphism) -->
    <div class="glass-card p-6 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md bg-amber-500/15 text-amber-800 border border-amber-400/30">
                    RFC 6238 TOTP
                </span>
                <span class="px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider rounded-md {{ $user->two_factor_enabled ? 'bg-emerald-500/15 text-emerald-800 border border-emerald-400/30' : 'bg-slate-200 text-slate-700' }}">
                    {{ $user->two_factor_enabled ? '✓ Status: 2FA HP AKTIF' : '○ Status: 2FA Belum Aktif' }}
                </span>
            </div>
            <h1 class="text-2xl font-black text-slate-900 mt-2 flex items-center gap-2">
                Autentikasi Dua Faktor (2FA Ponsel Cerdas Offline)
            </h1>
            <p class="text-xs text-slate-500 font-medium mt-0.5">
                Pengamanan tingkat tinggi akun Pimpinan menggunakan Google Authenticator, Aegis, atau FreeOTP 100% luring (tanpa internet / pulsa).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.users') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white/70 hover:bg-white border border-slate-200 transition-all shadow-sm">
                <span>👥</span>
                <span>Direktori Staf</span>
            </a>
            <a href="{{ route('profile') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-white/70 hover:bg-white border border-slate-200 transition-all shadow-sm">
                <span>👤</span>
                <span>Profil Akun</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi Flash Message -->
    @if(session('success'))
        <div class="p-4 rounded-xl border border-emerald-300 text-emerald-900 text-xs font-semibold flex items-center gap-2 shadow-sm"
             style="background: rgba(209, 250, 229, 0.9);">
            <span class="text-base">✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('warning'))
        <div class="p-4 rounded-xl border border-amber-300 text-amber-900 text-xs font-semibold flex items-center gap-2 shadow-sm"
             style="background: rgba(254, 243, 199, 0.9);">
            <span class="text-base">⚠️</span>
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-xl border border-rose-300 text-rose-900 text-xs font-semibold space-y-1 shadow-sm"
             style="background: rgba(254, 226, 226, 0.9);">
            @foreach($errors->all() as $error)
                <div class="flex items-start gap-1.5">
                    <span class="font-bold">✕</span>
                    <span>{{ $error }}</span>
                </div>
            @endforeach
        </div>
    @endif

    @if(!$user->two_factor_enabled)
        <!-- ================= KARTU AKTIVASI 2FA BARU ================= -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Kolom Kiri: QR Code & Secret Key Manual -->
            <div class="lg:col-span-6 glass-card p-6 space-y-5">
                <div class="flex items-center gap-2.5 border-b border-slate-200/80 pb-3">
                    <span class="step-badge bg-blue-600">1</span>
                    <div>
                        <h2 class="font-extrabold text-sm text-slate-900">Pindai QR Code di Ponsel Cerdas</h2>
                        <p class="text-[11px] text-slate-500 font-medium">Buka Google Authenticator / FreeOTP &rarr; Scan QR Code</p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed font-medium">
                    Pindai kode respons cepat di bawah ini menggunakan kamera aplikasi autentikator di ponsel cerdas Anda:
                </p>

                <!-- Box QR Code Offline Murni -->
                <div class="p-5 rounded-2xl border border-slate-200 flex flex-col items-center justify-center space-y-3 shadow-inner"
                     style="background: rgba(248, 250, 252, 0.85);">
                    <div id="qrcode-container" class="p-2.5 bg-white rounded-xl shadow-md border border-slate-200/80"></div>
                    <span class="text-[10px] text-slate-500 font-mono font-bold tracking-wider">🔒 100% Pure Offline QR Rendering (No Cloud)</span>
                </div>

                <!-- Manual Secret Key -->
                <div class="pt-2">
                    <label class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-600 mb-1.5">
                        Atau Masukkan Kunci Rahasia Manual:
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 p-2.5 rounded-xl font-mono text-xs text-slate-900 font-black tracking-widest text-center select-all border border-slate-200 bg-white/90 shadow-sm" x-text="secret"></div>
                        <button type="button" 
                                @click="copySecret()" 
                                class="px-3 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 border border-slate-300 shrink-0 transition-all cursor-pointer">
                            <span x-text="secretCopied ? '✅ Tersalin' : '📋 Salin'"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Master Recovery Key & Verifikasi Uji Coba -->
            <div class="lg:col-span-6 space-y-6">

                <!-- Kartu Master Recovery Key Brankas -->
                <div class="recovery-vault-box space-y-4">
                    <div class="flex items-center justify-between border-b border-amber-300/80 pb-3">
                        <div class="flex items-center gap-2.5">
                            <span class="step-badge bg-amber-600">2</span>
                            <div>
                                <h2 class="font-black text-sm text-slate-900">Kunci Pemulihan Fisik (Brankas)</h2>
                                <p class="text-[11px] text-amber-800 font-medium">Standar Kontingensi Darurat BSSN</p>
                            </div>
                        </div>
                        <button type="button" 
                                @click="printSheet()" 
                                class="px-3 py-1.5 bg-white hover:bg-slate-50 text-slate-900 rounded-xl text-[11px] font-bold border border-amber-300 shadow-sm transition-all flex items-center gap-1.5 cursor-pointer hover:scale-105 active:scale-95">
                            <span>🖨️</span>
                            <span>Cetak Amplop</span>
                        </button>
                    </div>

                    <p class="text-xs text-slate-700 leading-relaxed font-medium">
                        Jika ponsel Anda hilang, rusak, atau ter-reset, gunakan <strong>Master Recovery Key</strong> ini untuk memulihkan akses Super Admin:
                    </p>

                    <div class="space-y-2">
                        <div class="recovery-key-display select-all" x-text="recoveryKey"></div>
                        <div class="flex justify-end">
                            <button type="button" 
                                    @click="copyRecoveryKey()" 
                                    class="text-[11px] font-bold text-amber-900 hover:text-amber-950 flex items-center gap-1 cursor-pointer">
                                <span x-text="keyCopied ? '✅ Kunci Berhasil Disalin!' : '📋 Salin Teks Kunci'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-amber-100/70 border border-amber-300/70 text-[11px] text-amber-900 leading-relaxed font-medium">
                        *Catat atau cetak kunci ini, lalu masukkan ke dalam amplop bersegel untuk disimpan di <strong>brankas fisik Pimpinan</strong>.
                    </div>
                </div>

                <!-- Formulir Uji Coba Token untuk Aktivasi -->
                <div class="glass-card p-6 space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-200/80 pb-3">
                        <span class="step-badge bg-emerald-600">3</span>
                        <div>
                            <h2 class="font-extrabold text-sm text-slate-900">Uji Coba & Aktifkan 2FA</h2>
                            <p class="text-[11px] text-slate-500 font-medium">Masukkan 6 digit angka dari aplikasi ponsel</p>
                        </div>
                    </div>

                    <form action="{{ route('admin.two-factor.enable') }}" method="POST" class="space-y-4" @submit="submitting = true">
                        @csrf
                        <div>
                            <label for="code" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Kode 6 Digit yang Tampil di Aplikasi HP:
                            </label>
                            <input type="text" 
                                   id="code" 
                                   name="code" 
                                   maxlength="6" 
                                   x-model="code"
                                   @input="formatCode()"
                                   pattern="[0-9]*" 
                                   inputmode="numeric" 
                                   required 
                                   placeholder="000000" 
                                   class="twofa-code-input">
                            <div class="mt-1 flex justify-between items-center text-[10px] text-slate-400 font-mono">
                                <span>Ketik 6 digit token</span>
                                <span x-text="code.length + ' / 6'"></span>
                            </div>
                        </div>

                        <button type="submit" 
                                :disabled="submitting || code.length !== 6"
                                class="btn-emerald-mint w-full py-3 px-4 rounded-xl text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2 cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                            <span x-show="!submitting">Aktifkan 2FA Super Admin Sekarang</span>
                            <span x-show="submitting">Menguji Kode Token...</span>
                            <span x-show="!submitting">✓</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>
    @else
        <!-- ================= KARTU JIKA 2FA SUDAH AKTIF ================= -->
        <div class="glass-card p-8 space-y-6">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-emerald-100 border border-emerald-300 flex items-center justify-center text-2xl text-emerald-700 shadow-sm">
                    📱
                </div>
                <div>
                    <h2 class="text-lg font-black text-slate-900">Perlindungan 2FA HP Cerdas Sedang Aktif</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        Setiap kali masuk ke akun Super Administrator, sistem akan meminta token dinamis 6 digit dari aplikasi autentikator di ponsel Anda.
                    </p>
                </div>
            </div>

            <!-- Tombol Nonaktifkan 2FA -->
            <div class="pt-4 border-t border-slate-200" x-data="{ showDisableForm: false }">
                <div x-show="!showDisableForm">
                    <button type="button" @click="showDisableForm = true" class="px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold border border-rose-200 transition-all cursor-pointer">
                        Nonaktifkan 2FA (Memerlukan Konfirmasi Kata Sandi)
                    </button>
                </div>

                <form x-show="showDisableForm" x-cloak action="{{ route('admin.two-factor.disable') }}" method="POST" class="max-w-md space-y-3 p-4 rounded-xl border border-rose-200 bg-rose-50/70">
                    @csrf
                    <p class="text-xs font-bold text-rose-950">Konfirmasi Kata Sandi Akun untuk Menonaktifkan 2FA:</p>
                    <input type="password" name="current_password" required placeholder="Masukkan kata sandi saat ini..." class="glass-input w-full px-3 py-2 text-xs font-semibold">
                    <div class="flex items-center gap-2 pt-1">
                        <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all cursor-pointer shadow-sm">
                            Konfirmasi Nonaktifkan
                        </button>
                        <button type="button" @click="showDisableForm = false" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-xl text-xs font-bold transition-all cursor-pointer">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- ================= DOKUMEN CETAK AMPLOP BRANKAS FISIK (PRINTABLE ONLY) ================= -->
    <div id="printable-recovery-sheet" style="display: none;">
        <div style="border: 3px double #000; padding: 25px; font-family: 'Times New Roman', serif;">
            <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 12px; margin-bottom: 18px;">
                <h2 style="font-size: 18pt; margin: 0; text-transform: uppercase; font-weight: bold;">LEMBAR KUNCI PEMULIHAN DARURAT (MASTER RECOVERY KEY)</h2>
                <h3 style="font-size: 12pt; margin: 4px 0 0; font-weight: normal;">Sistem Informasi PADU Enterprise v2.0 &bull; Standar Regulasi BSSN No. 4/2021</h3>
                <p style="font-size: 10pt; margin: 2px 0 0; font-style: italic;">DOKUMEN RAHASIA &bull; WAJIB DISIMPAN DALAM AMPLOP TERSEGEL DI BRANKAS PIMPINAN</p>
            </div>

            <table style="width: 100%; font-size: 11pt; margin-bottom: 20px;">
                <tr>
                    <td style="width: 35%; padding: 4px 0; font-weight: bold;">Nama Pemegang Akun:</td>
                    <td style="padding: 4px 0;">{{ $user->name }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold;">Alamat Surel Terdaftar:</td>
                    <td style="padding: 4px 0;">{{ $user->email }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold;">Peran / Hak Akses:</td>
                    <td style="padding: 4px 0;">Super Administrator (Tingkat Pimpinan)</td>
                </tr>
                <tr>
                    <td style="padding: 4px 0; font-weight: bold;">Tanggal Diterbitkan:</td>
                    <td style="padding: 4px 0;">{{ now()->translatedFormat('d F Y, H:i:s') }} WIB</td>
                </tr>
            </table>

            <div style="background: #f4f4f4; border: 2px dashed #000; padding: 15px; text-align: center; margin: 20px 0;">
                <div style="font-size: 10pt; font-weight: bold; margin-bottom: 6px; text-transform: uppercase;">KUNCI PEMULIHAN FISIK SEKALI PAKAI (24 KARAKTER):</div>
                <div style="font-size: 18pt; font-family: monospace; font-weight: bold; letter-spacing: 2px;">{{ $recoveryKey }}</div>
            </div>

            <div style="font-size: 10pt; line-height: 1.6; margin-top: 15px;">
                <p><strong>PETUNJUK OPERASIONAL STANDAR (SOP BSSN):</strong></p>
                <ol style="margin-left: 20px; padding-left: 0;">
                    <li>Kunci ini digunakan secara eksklusif apabila perangkat ponsel cerdas Super Admin hilang, rusak, atau ter-reset.</li>
                    <li>Memasukkan kunci ini pada halaman login akan langsung <strong>menghanguskan (auto-revoke)</strong> konfigurasi 2FA lama.</li>
                    <li>Setelah masuk, Super Admin diwajibkan segera mendaftarkan perangkat ponsel baru pada menu Keamanan 2FA.</li>
                    <li>Dokumen ini wajib dimusnahkan dengan mesin penghancur kertas segera setelah kunci digunakan.</li>
                </ol>
            </div>

            <div style="margin-top: 40px; display: flex; justify-content: space-between;">
                <div style="text-align: center; width: 45%;">
                    <p style="font-size: 10pt; margin-bottom: 50px;">Petugas Administrator,</p>
                    <p style="font-size: 10pt; font-weight: bold; border-top: 1px solid #000; padding-top: 4px;">({{ $user->name }})</p>
                </div>
                <div style="text-align: center; width: 45%;">
                    <p style="font-size: 10pt; margin-bottom: 50px;">Saksi Pimpinan Satker / Auditor,</p>
                    <p style="font-size: 10pt; font-weight: bold; border-top: 1px solid #000; padding-top: 4px;">(....................................................)</p>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Clean JavaScript Component -->
<script>
function twoFactorSetup() {
    return {
        otpUri: @json($otpUri),
        secret: @json($secret),
        recoveryKey: @json($recoveryKey),
        code: '',
        keyCopied: false,
        secretCopied: false,
        submitting: false,

        init() {
            this.$nextTick(() => {
                this.renderQRCode();
            });
        },

        renderQRCode() {
            const container = document.getElementById('qrcode-container');
            if (container && typeof QRCode !== 'undefined') {
                container.innerHTML = '';
                new QRCode(container, {
                    text: this.otpUri,
                    width: 170,
                    height: 170,
                    colorDark : '#0f172a',
                    colorLight : '#ffffff',
                    correctLevel : QRCode.CorrectLevel.M
                });
            }
        },

        formatCode() {
            this.code = this.code.replace(/[^0-9]/g, '').slice(0, 6);
        },

        copyRecoveryKey() {
            navigator.clipboard.writeText(this.recoveryKey);
            this.keyCopied = true;
            setTimeout(() => { this.keyCopied = false; }, 2500);
        },

        copySecret() {
            navigator.clipboard.writeText(this.secret);
            this.secretCopied = true;
            setTimeout(() => { this.secretCopied = false; }, 2500);
        },

        printSheet() {
            window.print();
        }
    };
}
</script>
@endsection
