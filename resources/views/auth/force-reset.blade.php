<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Kata Sandi Baru &bull; PADU DTSEN 2026</title>
    
    <!-- Cache Control -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Flat Icon UIcons CDN & Master Stylesheets -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons@3.3.1/css/all/all.min.css">
    
    <style>
        [x-cloak] {
            display: none !important;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            text-decoration: none !important;
        }
        body.force-reset-body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 0%, #e0f2fe 0%, #f1f5f9 45%, #f8fafc 100%) !important;
            margin: 0;
            padding: 32px 16px;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            color: #0f172a;
        }
        .force-reset-container {
            width: 100%;
            max-width: 480px;
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

        .welcome-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 800;
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .human-notice-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }
        .human-notice-title {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 4px;
        }
        .human-notice-desc {
            font-size: 12px;
            color: #475569;
            line-height: 1.5;
            margin: 0;
        }

        .user-profile-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 14px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            margin-top: 10px;
        }
        .user-profile-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar-circle {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: #ffffff;
            font-weight: 900;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.25);
        }
        .user-profile-name {
            font-size: 13px;
            font-weight: 800;
            color: #0f172a;
        }
        .user-profile-email {
            font-size: 11px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #64748b;
        }
        .user-role-chip {
            font-size: 10px;
            font-weight: 800;
            padding: 4px 9px;
            border-radius: 9999px;
            background: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            text-transform: uppercase;
        }

        .form-group {
            margin-bottom: 18px;
        }
        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }
        .label-hint {
            font-size: 11px;
            font-weight: 500;
            color: #64748b;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .input-icon {
            position: absolute;
            left: 14px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
            font-size: 16px;
        }
        .form-input {
            width: 100%;
            height: 48px;
            padding: 0 44px 0 42px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 12px;
            font-size: 13px;
            color: #0f172a;
            font-weight: 600;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }
        .form-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
        }
        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            transition: color 0.15s;
        }
        .toggle-password-btn:hover {
            color: #1e293b;
        }

        /* Strength Meter Bar */
        .strength-container {
            margin: 12px 0 18px;
            background: #f1f5f9;
            border-radius: 9999px;
            height: 6px;
            overflow: hidden;
            position: relative;
        }
        .strength-bar {
            height: 100%;
            transition: width 0.3s ease, background-color 0.3s ease;
            border-radius: 9999px;
        }
        .strength-status-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 700;
            margin-top: 6px;
            margin-bottom: 16px;
        }

        /* Checklist Cards */
        .checklist-title {
            font-size: 11px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .checklist-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 8px;
            margin-bottom: 24px;
        }
        @media (min-width: 480px) {
            .checklist-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        .check-chip {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 700;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #64748b;
            transition: all 0.25s ease;
        }
        .check-chip.valid {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
        }
        .check-chip.valid .check-icon {
            color: #10b981;
        }
        .check-chip .check-icon {
            font-size: 13px;
            display: flex;
            align-items: center;
        }

        /* Primary Submit Button */
        .btn-primary {
            width: 100%;
            height: 48px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
        }
        .btn-primary:hover:not(:disabled) {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -5px rgba(37, 99, 235, 0.45);
        }
        .btn-primary:disabled {
            background: #cbd5e1;
            color: #64748b;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Clean Cancel Button */
        .btn-cancel-link {
            background: none;
            border: none;
            color: #64748b;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 8px;
            transition: all 0.15s;
            margin-top: 12px;
        }
        .btn-cancel-link:hover {
            color: #dc2626;
            background: #fee2e2;
        }

        .alert-error-box {
            padding: 12px 14px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 12px;
            color: #b91c1c;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .auth-footer {
            margin-top: 24px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
        }
    </style>
    
    <script src="{{ asset('js/auth.js') }}?v={{ file_exists(public_path('js/auth.js')) ? filemtime(public_path('js/auth.js')) : time() }}"></script>
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body class="force-reset-body">

    <div class="force-reset-container" x-data="forceResetForm()">

        <!-- Brand Monogram Header -->
        <div class="brand-header">
            <div class="brand-logo-badge">
                <span class="brand-logo-text">P</span>
            </div>
            <div class="brand-title">
                <span>PADU</span>
            </div>
            <p class="brand-subtitle">Pengolah & Analisis Data Terpadu (DTSEN 2026)</p>
        </div>

        <!-- Luxury Card -->
        <div class="auth-card">

            <div style="text-align: center; margin-bottom: 16px;">
                <div class="welcome-badge">
                    <span>👋 Inisialisasi Keamanan Akun</span>
                </div>
                <h1 style="font-size: 20px; font-weight: 900; color: #0f172a; margin: 0 0 6px 0; letter-spacing: -0.3px;">
                    Buat Kata Sandi Baru Anda
                </h1>
                <p style="font-size: 12px; color: #475569; margin: 0; line-height: 1.5;">
                    Untuk melindungi data kependudukan, mari buat kata sandi pribadi Anda sebelum mulai bekerja.
                </p>
            </div>

            <!-- User Info Card -->
            <div class="human-notice-box">
                <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">
                    Akun yang sedang aktif:
                </div>
                <div class="user-profile-bar">
                    <div class="user-profile-left">
                        <div class="user-avatar-circle">
                            <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                        </div>
                        <div>
                            <div class="user-profile-name">{{ $user->name }}</div>
                            <div class="user-profile-email">{{ $user->email }}</div>
                        </div>
                    </div>
                    <span class="user-role-chip">
                        {{ $user->isSuperAdmin() ? 'Super Admin' : 'Operator Data' }}
                    </span>
                </div>
            </div>

            <!-- Error Alerts -->
            @if(isset($errors) && $errors->any())
                <div class="alert-error-box">
                    @foreach($errors->all() as $error)
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <i class="fi fi-rr-cross-circle"></i>
                            <span>{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <!-- Form Reset -->
            <form action="{{ route('auth.force-reset.process') }}" method="POST">
                @csrf

                <!-- Kata Sandi Baru -->
                <div class="form-group">
                    <div class="form-label">
                        <span>Kata Sandi Baru</span>
                        <span class="label-hint">Diawali kapital & min. 15 karakter</span>
                    </div>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fi fi-rr-lock"></i>
                        </span>
                        <input :type="showPass ? 'text' : 'password'" 
                               id="password" 
                               name="password" 
                               autocomplete="new-password"
                               x-model="password" 
                               required 
                               autofocus
                               placeholder="Contoh: PaduAnalisis2026!" 
                               class="form-input">
                        <button type="button" 
                                @click="showPass = !showPass" 
                                class="toggle-password-btn" 
                                tabindex="-1"
                                :title="showPass ? 'Sembunyikan sandi' : 'Perlihatkan sandi'">
                            <i class="fi" :class="showPass ? 'fi-rr-eye-crossed' : 'fi-rr-eye'" style="font-size: 16px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Konfirmasi Kata Sandi Baru -->
                <div class="form-group">
                    <div class="form-label">
                        <span>Konfirmasi Kata Sandi Baru</span>
                        <span class="label-hint">Ketik ulang untuk memastikan cocok</span>
                    </div>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <i class="fi fi-rr-shield-check"></i>
                        </span>
                        <input :type="showPass ? 'text' : 'password'" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               autocomplete="new-password"
                               x-model="password_confirmation" 
                               required 
                               placeholder="Ketik ulang kata sandi baru Anda..." 
                               class="form-input">
                    </div>
                </div>

                <!-- Strength Progress Bar -->
                <div>
                    <div class="strength-container">
                        <div class="strength-bar" 
                             :style="'width: ' + score + '%; background-color: ' + scoreColor"></div>
                    </div>
                    <div class="strength-status-row">
                        <span style="color: #64748b;">Kekuatan Sandi:</span>
                        <span :style="'color: ' + scoreColor" x-text="scoreLabel"></span>
                    </div>
                </div>

                <!-- Checklist Syarat Kata Sandi -->
                <div>
                    <div class="checklist-title">
                        <i class="fi fi-rr-checkbox"></i>
                        <span>Checklist Syarat Keamanan Sandi:</span>
                    </div>

                    <div class="checklist-grid">
                        <div class="check-chip" :class="{ 'valid': hasMinLen }">
                            <span class="check-icon">
                                <i class="fi" :class="hasMinLen ? 'fi-rr-check-circle' : 'fi-rr-circle'"></i>
                            </span>
                            <span>Min. 15 Karakter (<span x-text="password.length"></span>/15)</span>
                        </div>

                        <div class="check-chip" :class="{ 'valid': hasCapitalStart }">
                            <span class="check-icon">
                                <i class="fi" :class="hasCapitalStart ? 'fi-rr-check-circle' : 'fi-rr-circle'"></i>
                            </span>
                            <span>Capital di Awal (A-Z)</span>
                        </div>

                        <div class="check-chip" :class="{ 'valid': hasNumber }">
                            <span class="check-icon">
                                <i class="fi" :class="hasNumber ? 'fi-rr-check-circle' : 'fi-rr-circle'"></i>
                            </span>
                            <span>Mengandung Angka (0-9)</span>
                        </div>

                        <div class="check-chip" :class="{ 'valid': hasSymbol }">
                            <span class="check-icon">
                                <i class="fi" :class="hasSymbol ? 'fi-rr-check-circle' : 'fi-rr-circle'"></i>
                            </span>
                            <span>Simbol Khusus (!@#$...)</span>
                        </div>

                        <div class="check-chip" :class="{ 'valid': isMatch && password.length > 0 }" style="grid-column: 1 / -1;">
                            <span class="check-icon">
                                <i class="fi" :class="(isMatch && password.length > 0) ? 'fi-rr-check-circle' : 'fi-rr-circle'"></i>
                            </span>
                            <span>Konfirmasi Sandi Cocok</span>
                        </div>
                    </div>
                </div>

                <!-- Tombol Submit -->
                <button type="submit" 
                        :disabled="!isValidAll" 
                        class="btn-primary">
                    <span>Simpan Kata Sandi & Masuk ke Sistem</span>
                    <i class="fi fi-rr-arrow-right" style="font-size: 13px;"></i>
                </button>
            </form>

            <!-- Tombol Batal / Keluar -->
            <div style="text-align: center; margin-top: 14px;">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-cancel-link">
                        <i class="fi fi-rr-sign-out-alt"></i>
                        <span>Keluar & Batalkan Sesi</span>
                    </button>
                </form>
            </div>

        </div>

        <!-- Footer -->
        <div class="auth-footer">
            <div>PADU &bull; Pengolah & Analisis Data Terpadu &copy; {{ date('Y') }}</div>
        </div>

    </div>

</body>
</html>
