<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk Sistem &bull; PADU DTSEN 2026</title>
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Flat Icon UIcons CDN & Master Stylesheets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@3.3.1/css/all/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ file_exists(public_path('css/auth.css')) ? filemtime(public_path('css/auth.css')) : time() }}">
    
    <!-- Critical Inline Styles (Anti-FOUC & 100% Robust Layout Fallback) -->
    <style>
        [x-cloak] {
            display: none !important;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            text-decoration: none !important;
        }
        svg {
            max-width: 24px !important;
            max-height: 24px !important;
        }
        body.auth-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 0%, #e0f2fe 0%, #f1f5f9 45%, #f8fafc 100%) !important;
            margin: 0;
            padding: 24px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
        }
        .auth-container {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            position: relative;
            z-index: 10;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 24px;
        }
        .brand-logo-badge {
            width: 68px;
            height: 68px;
            margin: 0 auto 16px;
            border-radius: 20px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 12px 28px -6px rgba(37, 99, 235, 0.35);
        }
        .brand-logo-text {
            color: #ffffff;
            font-size: 34px;
            font-weight: 900;
            line-height: 1;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .version-pill {
            font-size: 11px;
            font-weight: 800;
            background: #dbeafe;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 3px 9px;
            border-radius: 9999px;
            text-transform: uppercase;
        }
        .brand-subtitle {
            font-size: 13px;
            color: #475569;
            margin-top: 6px;
            font-weight: 500;
        }
        .auth-card {
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 20px 45px -10px rgba(15, 23, 42, 0.08), 0 0 0 1px #e2e8f0;
            border: 1px solid #e2e8f0;
            padding: 32px;
            position: relative;
        }
        .security-strip {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-bottom: 16px;
            margin-bottom: 22px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 11px;
            font-weight: 700;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 800;
            color: #1e293b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            pointer-events: none;
            z-index: 2;
        }
        .input-icon i {
            font-size: 16px;
            color: #94a3b8;
        }
        .form-input {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #f8fafc;
            padding: 0 16px 0 44px;
            font-size: 14px;
            font-weight: 600;
            color: #0f172a;
            outline: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .form-input:focus {
            background: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .form-help {
            font-size: 11px;
            color: #64748b;
            margin-top: 6px;
            font-weight: 500;
        }
        .btn-primary {
            width: 100%;
            height: 48px;
            border-radius: 14px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            font-size: 14px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 6px 18px -2px rgba(37, 99, 235, 0.4);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 8px 22px -2px rgba(37, 99, 235, 0.5);
            transform: translateY(-1px);
        }
        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }
        .demo-accounts-box {
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px dashed #cbd5e1;
        }
        .demo-accounts-title {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .demo-account-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .demo-chip {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            cursor: pointer;
            text-align: left;
            transition: all 0.2s ease;
        }
        .demo-chip:hover {
            background: #eff6ff;
            border-color: #3b82f6;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
        }
        .chip-role {
            font-size: 11px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .chip-email {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
        }
        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }
    </style>
    
    <script>
        window.LOGIN_CONFIG = {
            step: {{ (old('email') && ($errors->any() || session('warning') || session('error'))) ? 2 : 1 }},
            email: @json(old('email', '')),
            checkEmailUrl: '{{ url('/auth/check-email', [], false) }}',
            csrfToken: '{{ csrf_token() }}'
        };
    </script>
    <script src="{{ asset('js/auth.js') }}?v={{ file_exists(public_path('js/auth.js')) ? filemtime(public_path('js/auth.js')) : time() }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body class="auth-body">

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
            
            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="alert-box alert-success">
                    <i class="fi fi-rr-check-circle" style="font-size: 15px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert-box alert-warning">
                    <i class="fi fi-rr-triangle-warning" style="font-size: 15px;"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Dynamic Error Box -->
            <div x-show="errorMessage" x-cloak class="alert-box alert-error">
                <i class="fi fi-rr-cross-circle" style="font-size: 15px;"></i>
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
                    <i class="fi fi-rr-lock" style="font-size: 16px;"></i>
                    <span>AKUN TERKUNCI SEMENTARA</span>
                </div>
                <p style="font-size: 11px; color: #475569; margin-top: 4px;">
                    Terjadi 5 kali kegagalan kata sandi berturut-turut. Akun Anda terkunci sementara selama:
                </p>
                <div class="lockout-timer" x-text="formatSeconds(lockCountdown)"></div>
            </div>

            <!-- Form -->
            <form action="{{ url('/login', [], false) }}" method="POST" id="loginFormElement" @submit="submitting = true">
                @csrf
                <!-- Hidden input ensuring email is ALWAYS submitted in POST request without duplicate name conflicts -->
                <input type="hidden" name="email" :value="email" x-model="email">

                <!-- ================= TAHAP 1: INPUT SUREL (EMAIL-FIRST) ================= -->
                <div x-show="step === 1">
                    <div class="form-group">
                        <label for="emailInput" class="form-label">
                            <span>Alamat Surel Dinas</span>
                        </label>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fi fi-rr-envelope"></i>
                            </span>
                            <input type="email" 
                                   id="emailInput" 
                                   autocomplete="username"
                                   x-model="email" 
                                   @keydown.enter.prevent="submitEmail()"
                                   :disabled="isLocked || loading"
                                   placeholder="nama@padu.local" 
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
                        <span x-show="loading" x-cloak>Memeriksa Direktori...</span>
                        <i x-show="!loading" class="fi fi-rr-arrow-right" style="font-size: 13px;"></i>
                    </button>

                    <!-- Quick Demo / Testing Accounts Helper (1-Click Instant Login) -->
                    <div class="demo-accounts-box">
                        <div class="demo-accounts-title">
                            <i class="fi fi-rr-bolt" style="font-size: 14px; color: #2563eb;"></i>
                            <span>Pilih Cepat Akun (Klik untuk Masuk Langsung):</span>
                        </div>
                        <div class="demo-account-buttons">
                            <button type="button" 
                                    @click="quickLogin('admin@padu.local', 'PasswordAdmin2026!')" 
                                    class="demo-chip"
                                    style="width: 100%; justify-content: center;"
                                    title="Klik untuk langsung masuk sebagai Administrator">
                                <span class="chip-role"><i class="fi fi-rr-shield-check text-blue-600"></i> Administrator</span>
                                <span class="chip-email">admin@padu.local &bull; <strong>Masuk Otomatis</strong></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ================= TAHAP 2: INPUT KATA SANDI (ROLE-AWARE) ================= -->
                <div x-show="step === 2" x-cloak>
                    
                    <!-- Box Pengguna Terdeteksi -->
                    <div class="user-detect-box">
                        <div class="user-detect-info">
                            <div class="user-avatar avatar-superadmin">
                                <span>Admin</span>
                            </div>
                            <div>
                                <div class="user-name" x-text="userInfo ? userInfo.name : 'Administrator'"></div>
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
                        <span class="role-tag role-superadmin" 
                              x-text="userInfo ? userInfo.role_label : 'Administrator'"></span>
                    </div>

                    <div class="form-group">
                        <div class="form-label">
                            <span>Kata Sandi</span>
                            <span class="label-hint">Standar Min. 15 Karakter</span>
                        </div>
                        <div class="input-wrapper">
                            <span class="input-icon">
                                <i class="fi fi-rr-lock"></i>
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
                                <i class="fi" :class="showPassword ? 'fi-rr-eye-crossed' : 'fi-rr-eye'" style="font-size: 16px;"></i>
                            </button>
                        </div>

                        <!-- Auto-fill hint for standard credential -->
                        <div style="margin-top: 8px; font-size: 11px; color: #64748b; background: #f8fafc; padding: 7px 10px; border-radius: 8px; border: 1px dashed #cbd5e1; display: flex; align-items: center; justify-content: space-between;">
                            <span>Sandi Standar: <code style="font-weight: 700; color: #1e40af;">PasswordAdmin2026!</code></span>
                            <button type="button" 
                                    @click="password = 'PasswordAdmin2026!'"
                                    style="background: #e0f2fe; border: 1px solid #bae6fd; color: #0369a1; font-size: 11px; font-weight: 700; cursor: pointer; text-decoration: none; padding: 3px 8px; border-radius: 6px;">
                                Isi Otomatis
                            </button>
                        </div>
                    </div>

                    <button type="submit" 
                            :disabled="submitting || !password"
                            class="btn-primary"
                            style="margin-top: 14px;">
                        <span x-show="!submitting">Masuk ke Sistem</span>
                        <span x-show="submitting" x-cloak>Mengautentikasi...</span>
                        <i x-show="!submitting" class="fi fi-rr-sign-in-alt" style="font-size: 13px;"></i>
                    </button>

                </div>

            </form>

        </div>

        <!-- Footer -->
        <div class="auth-footer">
            <div>PADU &bull; Pengolah & Analisis Data Terpadu &copy; {{ date('Y') }}</div>
        </div>

    </div>

</body>
</html>
