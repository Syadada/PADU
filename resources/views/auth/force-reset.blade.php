<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wajib Perbarui Kata Sandi — PADU</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        *, *::before, *::after {
            text-decoration: none !important;
        }
        a, button, [role="button"], u, ins {
            text-decoration: none !important;
        }
    </style>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
</head>
<body class="auth-body">

    <div class="auth-container auth-container-wide" x-data="forceResetForm()">

        <div class="force-reset-header">
            <div class="brand-logo-badge">
                <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </div>
            <h1 class="force-reset-title">Wajib Pembaruan Kata Sandi</h1>
            <p class="force-reset-sub">
                🛡️ Standar Keamanan Siber BSSN No. 4/2021 (Min. 15 Karakter)
            </p>
        </div>

        <div class="auth-card">

            <div class="force-reset-notice">
                <strong>⚠️ Inisialisasi Keamanan Akun Pertama Kali:</strong><br>
                Sesuai regulasi keamanan BSSN, Anda diwajibkan menyetel kata sandi baru berstandar enterprise (panjang minimal 15 karakter) sebelum dapat mengakses data aplikasi.
            </div>

            <div class="force-reset-usercard">
                <div>
                    <span class="text-[10px] text-slate-500 font-bold uppercase">PENGGUNA:</span>
                    <div class="text-xs font-black text-slate-900">{{ $user->name }}</div>
                    <div class="text-[11px] text-slate-500 font-mono">{{ $user->email }}</div>
                </div>
                <span class="text-[10px] font-extrabold px-2 py-1 rounded-md uppercase {{ $user->isSuperAdmin() ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-800 border border-blue-200' }}">
                    {{ $user->isSuperAdmin() ? 'Super Admin' : 'Operator Data' }}
                </span>
            </div>

            @if(isset($errors) && $errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('auth.force-reset.process') }}" method="POST" class="form-flex-col">
                @csrf

                <div>
                    <div class="input-label-row">
                        <label for="password" class="input-label-text">Kata Sandi Baru</label>
                        <button type="button" @click="showPass = !showPass" class="text-link-btn">
                            <span x-text="showPass ? 'Sembunyikan' : 'Perlihatkan'"></span>
                        </button>
                    </div>
                    <input :type="showPass ? 'text' : 'password'" 
                           id="password" 
                           name="password" 
                           autocomplete="new-password"
                           x-model="password" 
                           required 
                           autofocus
                           placeholder="Minimal 15 karakter..." 
                           class="form-input">
                </div>

                <div>
                    <label for="password_confirmation" class="block input-label-text mb-1.5">
                        Konfirmasi Kata Sandi Baru
                    </label>
                    <input :type="showPass ? 'text' : 'password'" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           autocomplete="new-password"
                           x-model="password_confirmation" 
                           required 
                           placeholder="Ketik ulang kata sandi baru..." 
                           class="form-input">
                </div>

                <!-- Checklist Kepatuhan Sandi -->
                <div class="force-reset-checklist">
                    <div class="text-[10px] font-extrabold text-slate-500 uppercase tracking-wider mb-0.5">
                        CHECKLIST KEBIJAKAN SANDI BSSN:
                    </div>

                    <div class="force-reset-checklist-grid">
                        <div class="checklist-item" :class="hasMinLen ? 'item-valid' : 'item-invalid'">
                            <span x-text="hasMinLen ? '✓' : '○'"></span>
                            <span>Min. 15 Karakter (<span x-text="password.length"></span>/15)</span>
                        </div>
                        <div class="checklist-item" :class="hasUpper ? 'item-valid' : 'item-invalid'">
                            <span x-text="hasUpper ? '✓' : '○'"></span>
                            <span>Huruf Besar (A-Z)</span>
                        </div>
                        <div class="checklist-item" :class="hasLower ? 'item-valid' : 'item-invalid'">
                            <span x-text="hasLower ? '✓' : '○'"></span>
                            <span>Huruf Kecil (a-z)</span>
                        </div>
                        <div class="checklist-item" :class="hasNumber ? 'item-valid' : 'item-invalid'">
                            <span x-text="hasNumber ? '✓' : '○'"></span>
                            <span>Angka (0-9)</span>
                        </div>
                        <div class="checklist-item" :class="hasSymbol ? 'item-valid' : 'item-invalid'">
                            <span x-text="hasSymbol ? '✓' : '○'"></span>
                            <span>Simbol (@$!%*#?&...)</span>
                        </div>
                        <div class="checklist-item" :class="isMatch && password ? 'item-valid' : 'item-invalid'">
                            <span x-text="isMatch && password ? '✓' : '○'"></span>
                            <span>Sandi Cocok</span>
                        </div>
                    </div>
                </div>

                <button type="submit" :disabled="!isValidAll" class="btn-primary">
                    <span>Simpan Kata Sandi & Lanjutkan</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </form>

            <div class="mt-4 text-center">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="text-link-cancel">
                        Keluar & Batalkan Sesi
                    </button>
                </form>
            </div>

        </div>

    </div>

</body>
</html>
