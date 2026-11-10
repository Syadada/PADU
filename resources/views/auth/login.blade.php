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
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ file_exists(public_path('css/app.css')) ? filemtime(public_path('css/app.css')) : time() }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v={{ file_exists(public_path('css/auth.css')) ? filemtime(public_path('css/auth.css')) : time() }}">
    
    <!-- Critical Inline Styles (Anti-FOUC & Anti-Vector Blowup) -->
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            text-decoration: none !important;
        }
        [x-cloak] {
            display: none !important;
        }
        svg {
            max-width: 24px !important;
            max-height: 24px !important;
            display: inline-block;
        }
        body {
            margin: 0;
            padding: 1.5rem;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 0%, #e0f2fe 0%, #f1f5f9 45%, #f8fafc 100%) !important;
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
        .auth-card {
            background: #ffffff;
            border-radius: 1.25rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04);
            padding: 2rem;
        }
        .brand-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .brand-logo-badge {
            width: 64px;
            height: 64px;
            margin: 0 auto 0.75rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #1e40af 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 2rem;
            font-weight: 900;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.35);
        }
        .form-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #0f172a;
            outline: none;
            transition: all 0.2s;
        }
        .form-input:focus {
            background: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .btn-primary {
            width: 100%;
            padding: 0.75rem 1rem;
            background: #2563eb;
            color: #ffffff;
            border: none;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 800;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
        }
        .btn-primary:hover:not(:disabled) {
            background: #1d4ed8;
            box-shadow: 0 6px 12px -2px rgba(37, 99, 235, 0.3);
        }
        .btn-primary:disabled {
            opacity: 0.55;
            cursor: not-allowed;
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
                <span>P</span>
            </div>
            <div class="flex items-center justify-center gap-1.5">
                <span class="text-xl font-black text-slate-900 tracking-tight">PADU</span>
                <span class="px-2 py-0.5 text-[10px] font-black uppercase tracking-wider bg-blue-100 text-blue-700 rounded-full border border-blue-200">v2.0</span>
            </div>
            <p class="text-xs text-slate-500 font-medium mt-1">Pengolah & Analisis Data Terpadu (DTSEN 2026)</p>
        </div>

        <!-- Authentication Card -->
        <div class="auth-card">
            
            <!-- Top Security Badges -->
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100 text-[11px] font-bold text-slate-500">
                <div class="flex items-center gap-1.5 text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>100% Offline Lokal</span>
                </div>
                <div class="flex items-center gap-1.5 text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-200">
                    <i class="fi fi-rr-shield-check text-xs"></i>
                    <span>Standar BSSN & UU PDP</span>
                </div>
            </div>

            <!-- Flash Alerts -->
            @if(session('success'))
                <div class="p-3 mb-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-2">
                    <i class="fi fi-rr-check-circle text-emerald-600 shrink-0"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('warning'))
                <div class="p-3 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-2">
                    <i class="fi fi-rr-triangle-warning text-amber-600 shrink-0"></i>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            <!-- Dynamic Error Box -->
            <div x-show="errorMessage" x-cloak class="p-3 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-center gap-2">
                <i class="fi fi-rr-cross-circle text-rose-600 shrink-0"></i>
                <span x-text="errorMessage"></span>
            </div>

            @if($errors->any())
                <div class="p-3 mb-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold flex items-start gap-2">
                    <i class="fi fi-rr-cross-circle text-rose-600 shrink-0 mt-0.5"></i>
                    <div class="space-y-0.5">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Lockout Countdown Alert -->
            <div x-show="isLocked" x-cloak class="p-4 mb-4 rounded-xl bg-rose-50 border-2 border-rose-300 text-rose-900 space-y-2">
                <div class="flex items-center gap-2 font-black text-xs uppercase tracking-wider text-rose-700">
                    <i class="fi fi-rr-lock"></i>
                    <span>AKUN TERKUNCI SEMENTARA</span>
                </div>
                <p class="text-[11px] text-slate-600">
                    Terjadi 5 kali kegagalan kata sandi berturut-turut. Sesuai Standar BSSN No. 4/2021, akun Anda terkunci selama:
                </p>
                <div class="font-mono text-xl font-black text-rose-600" x-text="formatSeconds(lockCountdown)"></div>
            </div>

            <!-- Form -->
            <form action="{{ route('login.process') }}" method="POST" @submit="submitting = true" class="space-y-4">
                @csrf
                <!-- Hidden input ensuring email is ALWAYS submitted in POST request -->
                <input type="hidden" name="email" :value="email">

                <!-- ================= TAHAP 1: INPUT SUREL (EMAIL-FIRST) ================= -->
                <div x-show="step === 1" class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-black uppercase tracking-wider text-slate-700">
                            Alamat Surel Dinas
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-slate-400 flex items-center pointer-events-none">
                                <i class="fi fi-rr-envelope text-sm"></i>
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
                        <p class="text-[11px] text-slate-400 font-medium">
                            Gunakan surel resmi yang telah didaftarkan dalam sistem.
                        </p>
                    </div>

                    <button type="button" 
                            @click="submitEmail()" 
                            :disabled="isLocked || loading || !email"
                            class="btn-primary">
                        <span x-show="!loading">Lanjutkan Verifikasi</span>
                        <span x-show="loading">Memeriksa Direktori...</span>
                        <i x-show="!loading" class="fi fi-rr-arrow-right text-xs"></i>
                    </button>

                    <!-- Quick Demo / Testing Accounts Helper -->
                    <div class="pt-4 border-t border-slate-100 space-y-2">
                        <div class="flex items-center gap-1.5 text-[11px] font-black uppercase tracking-wider text-slate-500">
                            <i class="fi fi-rr-info text-blue-600"></i>
                            <span>Pilih Cepat Akun Demo (Klik untuk Isi):</span>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            <button type="button" 
                                    @click="fillAccount('superadmin@padu.local', 'PasswordSuperAdmin2026!')" 
                                    class="p-2.5 rounded-xl border border-blue-200 bg-blue-50/70 hover:bg-blue-100 text-left transition-all cursor-pointer flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-xs font-black text-blue-900">
                                    <i class="fi fi-rr-crown text-amber-500"></i> Super Administrator
                                </span>
                                <span class="text-[11px] font-mono text-blue-700 font-bold">superadmin@padu.local</span>
                            </button>
                            <button type="button" 
                                    @click="fillAccount('operator@padu.local', 'PasswordOperator2026!')" 
                                    class="p-2.5 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-left transition-all cursor-pointer flex items-center justify-between">
                                <span class="flex items-center gap-1.5 text-xs font-black text-slate-800">
                                    <i class="fi fi-rr-user text-slate-500"></i> Operator Data
                                </span>
                                <span class="text-[11px] font-mono text-slate-600 font-bold">operator@padu.local</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ================= TAHAP 2: INPUT KATA SANDI (ROLE-AWARE) ================= -->
                <div x-show="step === 2" x-cloak class="space-y-4">
                    
                    <!-- Box Pengguna Terdeteksi -->
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center font-black text-xs shrink-0" 
                                 :class="userInfo && userInfo.role === 'superadmin' ? 'bg-amber-100 text-amber-800' : (email === 'superadmin@padu.local' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800')">
                                <i class="fi" :class="userInfo && userInfo.role === 'superadmin' ? 'fi-rr-crown' : (email === 'superadmin@padu.local' ? 'fi-rr-crown' : 'fi-rr-user')"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="text-xs font-black text-slate-900 truncate" x-text="userInfo ? userInfo.name : (email === 'superadmin@padu.local' ? 'Super Administrator (Pimpinan)' : 'Operator Data DTSEN')"></div>
                                <div class="text-[11px] font-mono text-slate-500 truncate" x-text="email"></div>
                            </div>
                        </div>
                        <button type="button" @click="backToEmail()" class="px-2.5 py-1 rounded-lg bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 font-extrabold text-[11px] shrink-0 cursor-pointer transition-all">
                            Ganti
                        </button>
                    </div>

                    <!-- Role Badge -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="font-bold text-slate-500 text-[11px] uppercase tracking-wider">Hak Akses:</span>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-black border" 
                              :class="userInfo && userInfo.role === 'superadmin' ? 'bg-amber-50 text-amber-800 border-amber-200' : (email === 'superadmin@padu.local' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-blue-50 text-blue-800 border-blue-200')"
                              x-text="userInfo ? userInfo.role_label : (email === 'superadmin@padu.local' ? 'Super Administrator (Pimpinan)' : 'Operator Data Staf')"></span>
                    </div>

                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <label for="password" class="font-black uppercase tracking-wider text-slate-700">Kata Sandi</label>
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Min. 15 Karakter</span>
                        </div>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-slate-400 flex items-center pointer-events-none">
                                <i class="fi fi-rr-lock text-sm"></i>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   id="password" 
                                   name="password" 
                                   autocomplete="current-password"
                                   x-ref="passwordInput"
                                   x-model="password" 
                                   required 
                                   placeholder="Masukkan kata sandi..." 
                                   class="form-input pr-10">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute right-3 text-slate-400 hover:text-slate-600 flex items-center cursor-pointer" 
                                    tabindex="-1">
                                <i class="fi text-sm" :class="showPassword ? 'fi-rr-eye-crossed' : 'fi-rr-eye'"></i>
                            </button>
                        </div>

                        <!-- Auto-fill hint for standard credential -->
                        <div class="mt-2 p-2.5 rounded-xl bg-slate-50 border border-dashed border-slate-300 flex items-center justify-between text-xs">
                            <span class="text-slate-600">Sandi Standar: <code class="font-mono font-bold text-blue-700" x-text="email === 'superadmin@padu.local' ? 'PasswordSuperAdmin2026!' : 'PasswordOperator2026!'"></code></span>
                            <button type="button" 
                                    @click="password = (email === 'superadmin@padu.local' ? 'PasswordSuperAdmin2026!' : 'PasswordOperator2026!')"
                                    class="px-2.5 py-1 rounded-lg bg-blue-100 hover:bg-blue-200 text-blue-800 font-extrabold text-[11px] cursor-pointer transition-all">
                                Isi Otomatis
                            </button>
                        </div>
                    </div>

                    <!-- Peringatan Kepatuhan BSSN Lockout -->
                    <div class="p-2.5 rounded-xl bg-blue-50 border border-blue-200 text-[11px] font-bold text-blue-900 flex items-center gap-2">
                        <i class="fi fi-rr-shield-exclamation text-blue-600 shrink-0"></i>
                        <span>Akun otomatis terkunci selama 15 menit jika kata sandi salah 5 kali berturut-turut.</span>
                    </div>

                    <button type="submit" 
                            :disabled="submitting || !password"
                            class="btn-primary">
                        <span x-show="!submitting">Masuk ke Sistem</span>
                        <span x-show="submitting">Mengautentikasi...</span>
                        <i x-show="!submitting" class="fi fi-rr-sign-in-alt text-xs"></i>
                    </button>

                    <!-- SOP Lupa Sandi -->
                    <div class="pt-3 border-t border-slate-100 text-center">
                        <template x-if="userInfo && userInfo.role === 'operator'">
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Lupa kata sandi? Hubungi <strong class="text-slate-800">Super Administrator</strong> untuk melakukan <em>Admin-Assisted Offline Reset</em> sesuai SOP keamanan PADU v2.0.
                            </p>
                        </template>
                        <template x-if="userInfo && userInfo.role === 'superadmin'">
                            <p class="text-[11px] text-slate-500 leading-relaxed">
                                Akun Pimpinan dilindungi oleh <strong class="text-slate-800">2FA HP Offline</strong> & Lembar Kunci Pemulihan Darurat fisik di brankas.
                            </p>
                        </template>
                    </div>

                </div>

            </form>

        </div>

        <!-- Footer Akreditasi -->
        <div class="mt-6 text-center text-xs text-slate-400 space-y-1">
            <div class="font-bold text-slate-500">Sistem Informasi PADU &bull; Kedaulatan Mandiri Lepas Kunci</div>
            <div class="text-[11px]">Selaras Regulasi BSSN No. 4/2021, No. 11/2024, & UU No. 27/2022 (UU PDP)</div>
        </div>

    </div>

</body>
</html>
