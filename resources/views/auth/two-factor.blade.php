<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi 2FA HP Cerdas — PADU</title>
    
    <!-- 100% Offline Standalone Assets -->
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        *, *::before, *::after {
            text-decoration: none !important;
        }
        a, button, [role="button"], u, ins {
            text-decoration: none !important;
        }
    </style>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body class="twofa-page-body">

    <div class="twofa-container" x-data="twoFactorVerify()">

        <!-- Brand Monogram / Header -->
        <div class="text-center mb-6">
            <div class="twofa-logo-badge">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                    <line x1="12" y1="18" x2="12.01" y2="18"/>
                </svg>
            </div>
            <h1 class="text-2xl font-black text-white tracking-tight">Verifikasi 2FA HP Offline</h1>
            <p class="text-xs text-slate-400 mt-1 font-medium">Autentikasi Dua Faktor Tingkat Pimpinan (Standar RFC 6238)</p>
        </div>

        <!-- Main Glass Card -->
        <div class="twofa-card">

            <!-- Super Admin User Pill -->
            <div class="twofa-user-pill">
                <div class="flex items-center gap-2.5">
                    <span class="twofa-badge-superadmin">
                        Super Admin
                    </span>
                    <span class="text-xs font-black text-slate-900">{{ $user->name }}</span>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-[11px] font-bold text-rose-600 hover:text-rose-800 transition-colors cursor-pointer">
                        Batal
                    </button>
                </form>
            </div>

            <!-- Flash Info Alert -->
            @if(session('info'))
                <div class="twofa-alert-info flex items-center gap-2">
                    <span class="text-base">ℹ️</span>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <!-- Error Validation Alert -->
            @if(isset($errors) && $errors->any())
                <div class="twofa-alert-error space-y-1">
                    @foreach($errors->all() as $error)
                        <div class="flex items-start gap-1.5">
                            <span class="font-bold">✕</span>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- ================= MODE 1: TOTP AUTHENTICATOR INPUT ================= -->
            <div x-show="mode === 'totp'">
                <p class="text-xs text-slate-600 mb-4 leading-relaxed font-medium">
                    Buka aplikasi <strong>Google Authenticator, Aegis, atau FreeOTP</strong> di ponsel cerdas Anda, lalu masukkan 6 digit token yang tampil:
                </p>

                <form action="{{ route('auth.two-factor.verify') }}" method="POST" class="space-y-4" @submit="submitting = true">
                    @csrf
                    <div>
                        <label for="code" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-500 mb-1.5">
                            Kode Token 6 Digit:
                        </label>
                        <input type="text" 
                               id="code" 
                               name="code" 
                               x-ref="codeInput"
                               x-model="code"
                               @input="formatCode()"
                               maxlength="6" 
                               pattern="[0-9]*" 
                               inputmode="numeric" 
                               autocomplete="one-time-code"
                               required 
                               autofocus
                               placeholder="000000" 
                               class="twofa-code-input">
                        <div class="mt-1 flex justify-between items-center text-[10px] text-slate-400 font-mono">
                            <span>Format: 6 Angka Numerik</span>
                            <span x-text="code.length + ' / 6'"></span>
                        </div>
                    </div>

                    <button type="submit" 
                            :disabled="submitting || code.length !== 6" 
                            class="btn-twofa-primary">
                        <span x-show="!submitting">Konfirmasi Masuk</span>
                        <span x-show="submitting">Memverifikasi Token...</span>
                        <svg x-show="!submitting" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-5 pt-3.5 border-t border-slate-200 text-center">
                    <button type="button" @click="switchToRecovery()" class="text-[11px] font-bold text-amber-700 hover:text-amber-900 transition-colors cursor-pointer">
                        🔑 HP Hilang / Rusak? Gunakan Master Recovery Key Brankas
                    </button>
                </div>
            </div>

            <!-- ================= MODE 2: EMERGENCY RECOVERY KEY ================= -->
            <div x-show="mode === 'recovery'" x-cloak>
                <div class="twofa-alert-warning text-xs leading-relaxed space-y-1">
                    <p class="font-extrabold flex items-center gap-1.5 text-amber-900">
                        <span>🛑</span> <span>PROSEDUR PEMULIHAN DARURAT BSSN:</span>
                    </p>
                    <p class="text-[11px] text-amber-800">
                        Masukkan <strong>Master Recovery Key</strong> dari lembar fisik bersegel di brankas Pimpinan. Jika diverifikasi benar, <strong>perangkat 2FA lama langsung dihanguskan (auto-revoke)</strong> demi keamanan.
                    </p>
                </div>

                <form action="{{ route('auth.two-factor.recovery') }}" method="POST" class="space-y-4" @submit="submitting = true">
                    @csrf
                    <div>
                        <label for="recovery_key" class="block text-[11px] font-extrabold uppercase tracking-wider text-slate-700 mb-1.5">
                            Master Recovery Key Fisik
                        </label>
                        <input type="text" 
                               id="recovery_key" 
                               name="recovery_key" 
                               autocomplete="off"
                               x-ref="recoveryInput"
                               x-model="recoveryKey"
                               @input="formatRecoveryKey()"
                               required 
                               placeholder="PADU-XXXX-XXXX-XXXX-XXXX" 
                               class="twofa-recovery-input">
                        <p class="mt-1 text-[10px] text-slate-400">
                            Format standar: <code>PADU-XXXX-XXXX-XXXX-XXXX</code>
                        </p>
                    </div>

                    <button type="submit" 
                            :disabled="submitting || recoveryKey.trim().length < 10" 
                            class="btn-twofa-danger">
                        <span x-show="!submitting">Verifikasi Kunci Darurat</span>
                        <span x-show="submitting">Memeriksa Kunci Brankas...</span>
                        <svg x-show="!submitting" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 9.9-1"/>
                        </svg>
                    </button>
                </form>

                <div class="mt-5 pt-3.5 border-t border-slate-200 text-center">
                    <button type="button" @click="switchToTotp()" class="text-[11px] font-bold text-slate-600 hover:text-slate-900 transition-colors cursor-pointer">
                        &larr; Kembali ke Verifikasi Aplikasi HP
                    </button>
                </div>
            </div>

        </div>

        <!-- Footer Akreditasi -->
        <div class="mt-6 text-center text-[11px] text-slate-400 space-y-0.5">
            <div>100% Offline Standalone &bull; Tanpa Internet & Pulsa &bull; Standar RFC 6238</div>
            <div class="text-[10px] text-slate-500">Direktori Forensik Keamanan Tingkat Tinggi PADU v2.0</div>
        </div>

    </div>

    <!-- Clean JavaScript Controller (No Inline Spills) -->
    <script>
    function twoFactorVerify() {
        return {
            mode: 'totp',
            code: '',
            recoveryKey: '',
            submitting: false,

            init() {
                this.$nextTick(() => {
                    if (this.$refs.codeInput) {
                        this.$refs.codeInput.focus();
                    }
                });
            },

            formatCode() {
                // Hanya terima angka numerik, maksimal 6 digit
                this.code = this.code.replace(/[^0-9]/g, '').slice(0, 6);
            },

            formatRecoveryKey() {
                // Otomatis ubah ke huruf kapital dan rapikan spasi
                this.recoveryKey = this.recoveryKey.toUpperCase().replace(/\s+/g, '');
            },

            switchToRecovery() {
                this.mode = 'recovery';
                this.$nextTick(() => {
                    if (this.$refs.recoveryInput) {
                        this.$refs.recoveryInput.focus();
                    }
                });
            },

            switchToTotp() {
                this.mode = 'totp';
                this.$nextTick(() => {
                    if (this.$refs.codeInput) {
                        this.$refs.codeInput.focus();
                    }
                });
            }
        };
    }
    </script>
</body>
</html>