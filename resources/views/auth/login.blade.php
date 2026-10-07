<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem — PADU</title>
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- 100% Offline Standalone Assets -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ file_exists(public_path('css/auth.css')) ? filemtime(public_path('css/auth.css')) : time() }}">
    <style>
        *, *::before, *::after {
            text-decoration: none !important;
        }
        a, button, [role="button"], u, ins {
            text-decoration: none !important;
        }
    </style>
    
    <script>
        window.LOGIN_CONFIG = {
            step: {{ (old('email') && ($errors->any() || session('warning') || session('error'))) ? 2 : 1 }},
            email: @json(old('email', '')),
            checkEmailUrl: '{{ route('auth.check-email') }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    <script src="{{ asset('js/auth.js') }}?v={{ file_exists(public_path('js/auth.js')) ? filemtime(public_path('js/auth.js')) : time() }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}?v=3.13.5"></script>
</head>
<body>

    <div class="auth-container" x-data="loginForm(window.LOGIN_CONFIG)">

        <!-- Brand Monogram Header -->
        <div class="brand-header">
            <div class="brand-logo-badge">
                <span class="brand-logo-text">P</span>
            </div>
            <div class="brand-title">
                <span>PADU</span>
                <span class="version-pill">v2.0</span>
            </div>
            <p class="brand-subtitle">Pengolah & Analisis Data Terpadu (DTSEN 2026)</p>
        </div>

        <!-- Luxury Card -->
        <div class="auth-card">
            
            <!-- Top Security Badges -->
            <div class="security-strip">
                <div class="offline-status">
                    <span class="pulse-dot"></span>
                    <span>100% Offline Lokal</span>
                </div>
                <div class="bssn-status">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    <span>Standar BSSN & UU PDP</span>
                </div>
            </div>

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert-box alert-success">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert-box alert-warning">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Dynamic Error Box -->
            <div x-show="errorMessage" x-cloak class="alert-box alert-error">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                <span x-text="errorMessage"></span>
            </div>

            @if($errors->any())
                <div class="alert-box alert-error">
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lockout Countdown Alert -->
            <div x-show="isLocked" x-cloak class="lockout-box">
                <div class="lockout-title">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <span>AKUN TERKUNCI SEMENTARA</span>
                </div>
                <p style="font-size: 11px; color: #475569; margin-top: 4px;">
                    Terjadi 5 kali kegagalan kata sandi berturut-turut. Sesuai Standar BSSN No. 4/2021, akun Anda terkunci selama:
                </p>
                <div class="lockout-timer" x-text="formatSeconds(lockCountdown)"></div>
            </div>

            <!-- Form -->
            <form action="{{ route('login.process') }}" method="POST" @submit="submitting = true">
                @csrf
                <!-- Hidden input ensuring email is ALWAYS submitted in POST request -->
                <input type="hidden" name="email" :value="email">

                <!-- ================= TAHAP 1: INPUT SUREL (EMAIL-FIRST) ================= -->
                <div x-show="step === 1">
                    <div class="form-group">
                        <label for="email" class="form-label">
                            <span>Alamat Surel Dinas</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                                    <polyline points="22,6 12,13 2,6"/>
                                </svg>
                            </span>
                            <input type="email" 
                                   id="email" 
                                   name="email"
                                   autocomplete="username"
                                   x-model="email" 
                                   @keydown.enter.prevent="submitEmail()"
                                   :disabled="isLocked || loading"
                                   placeholder="nama@padu.local" 
                                   required 
                                   autofocus
                                   class="form-input">
                        </div>
                        <p class="form-help">
                            Gunakan surel resmi yang telah didaftarkan dalam sistem.
                        </p>
                    </div>

                    <button type="button" 
                            @click="submitEmail()" 
                            :disabled="isLocked || loading || !email"
                            class="btn-primary">
                        <span x-show="!loading">Lanjutkan Verifikasi</span>
                        <span x-show="loading">Memeriksa Direktori...</span>
                        <svg x-show="!loading" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>

                    <!-- Quick Demo / Testing Accounts Helper -->
                    <div class="demo-accounts-box">
                        <div class="demo-accounts-title">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            <span>Pilih Cepat Akun Demo (Klik untuk Isi):</span>
                        </div>
                        <div class="demo-account-buttons">
                            <button type="button" 
                                    @click="fillAccount('superadmin@padu.local', 'PasswordSuperAdmin2026!')" 
                                    class="demo-chip">
                                <span class="chip-role">👑 Super Administrator</span>
                                <span class="chip-email">superadmin@padu.local</span>
                            </button>
                            <button type="button" 
                                    @click="fillAccount('operator@padu.local', 'PasswordOperator2026!')" 
                                    class="demo-chip">
                                <span class="chip-role">👤 Operator Data</span>
                                <span class="chip-email">operator@padu.local</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ================= TAHAP 2: INPUT KATA SANDI (ROLE-AWARE) ================= -->
                <div x-show="step === 2" x-cloak>
                    
                    <!-- Box Pengguna Terdeteksi -->
                    <div class="user-detect-box">
                        <div class="user-detect-info">
                            <div class="user-avatar" :class="userInfo && userInfo.role === 'superadmin' ? 'avatar-superadmin' : (email === 'superadmin@padu.local' ? 'avatar-superadmin' : 'avatar-operator')">
                                <span x-text="userInfo && userInfo.role === 'superadmin' ? '👑' : (email === 'superadmin@padu.local' ? '👑' : '👤')"></span>
                            </div>
                            <div>
                                <div class="user-name" x-text="userInfo ? userInfo.name : (email === 'superadmin@padu.local' ? 'Super Administrator (Pimpinan)' : 'Operator Data DTSEN')"></div>
                                <div class="user-email-text" x-text="email"></div>
                            </div>
                        </div>
                        <button type="button" @click="backToEmail()" class="btn-change-email">
                            Ganti
                        </button>
                    </div>

                    <!-- Role Badge -->
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                        <span style="font-size: 11px; font-weight: 700; color: #64748b;">Hak Akses:</span>
                        <span class="role-tag" 
                              :class="userInfo && userInfo.role === 'superadmin' ? 'role-superadmin' : (email === 'superadmin@padu.local' ? 'role-superadmin' : 'role-operator')"
                              x-text="userInfo ? userInfo.role_label : (email === 'superadmin@padu.local' ? 'Super Administrator (Pimpinan)' : 'Operator Data Staf')"></span>
                    </div>

                    <div class="form-group">
                        <div class="form-label">
                            <span>Kata Sandi</span>
                            <span class="label-hint">Standar Min. 15 Karakter</span>
                        </div>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                </svg>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   autocomplete="current-password"
                                   x-ref="passwordInput"
                                   x-model="password" 
                                   required 
                                   placeholder="Masukkan kata sandi..." 
                                   class="form-input">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="toggle-password-btn" 
                                    tabindex="-1">
                                <svg x-show="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                <svg x-show="showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                            </button>
                        </div>

                        <!-- Auto-fill hint for standard credential -->
                        <div style="margin-top: 8px; font-size: 11px; color: #64748b; background: #f8fafc; padding: 7px 10px; border-radius: 8px; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: space-between;">
                            <span>Sandi Standar: <code style="font-weight: 700; color: #1e40af;" x-text="email === 'superadmin@padu.local' ? 'PasswordSuperAdmin2026!' : 'PasswordOperator2026!'"></code></span>
                            <button type="button" 
                                    @click="password = (email === 'superadmin@padu.local' ? 'PasswordSuperAdmin2026!' : 'PasswordOperator2026!')"
                                    style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; font-size: 11px; font-weight: 700; cursor: pointer; text-decoration: none; padding: 3px 8px; border-radius: 6px;">
                                Isi Otomatis
                            </button>
                        </div>
                    </div>

                    <!-- Peringatan Kepatuhan BSSN Lockout -->
                    <div class="policy-notice">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                        <span>Akun akan otomatis terkunci selama 15 menit jika kata sandi salah 5 kali berturut-turut.</span>
                    </div>

                    <button type="submit" 
                            :disabled="submitting || !password"
                            class="btn-primary">
                        <span x-show="!submitting">Masuk ke Sistem</span>
                        <span x-show="submitting">Mengautentikasi...</span>
                        <svg x-show="!submitting" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </button>

                    <!-- SOP Lupa Sandi -->
                    <div style="margin-top: 18px; padding-top: 14px; border-top: 1px solid #f1f5f9; text-align: center;">
                        <template x-if="userInfo && userInfo.role === 'operator'">
                            <p style="font-size: 11px; color: #64748b; line-height: 1.5;">
                                Lupa kata sandi? Hubungi <strong style="color: #0f172a;">Super Administrator</strong> untuk melakukan <em>Admin-Assisted Offline Reset</em> sesuai SOP keamanan PADU v2.0.
                            </p>
                        </template>
                        <template x-if="userInfo && userInfo.role === 'superadmin'">
                            <p style="font-size: 11px; color: #64748b; line-height: 1.5;">
                                Akun Pimpinan dilindungi oleh <strong style="color: #0f172a;">2FA HP Offline</strong> & Lembar Kunci Pemulihan Darurat fisik di brankas.
                            </p>
                        </template>
                    </div>

                </div>

            </form>

        </div>

        <!-- Footer Akreditasi -->
        <div class="auth-footer">
            <div>Sistem Informasi PADU &bull; Kedaulatan Mandiri Lepas Kunci</div>
            <div class="auth-footer-sub">Selaras Regulasi BSSN No. 4/2021, No. 11/2024, & UU No. 27/2022 (UU PDP)</div>
        </div>

    </div>

</body>
</html>
