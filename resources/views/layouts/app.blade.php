<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PADU - Pengolah & Analisis Data Terpadu (DTSEN 2026)</title>
    
    <!-- 100% Pure Offline Assets (Murni Lokal, Tanpa Request Jaringan Luar / Google Fonts) -->
    <link rel="stylesheet" href="{{ asset('css/tailwind.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</head>
<body class="min-h-screen flex flex-col antialiased bg-slate-50" 
      x-data="{ 
          isMasked: localStorage.getItem('padu_is_masked') !== 'false',
          setMasked(val) {
              this.isMasked = val;
              localStorage.setItem('padu_is_masked', val ? 'true' : 'false');
          }
      }">

    <!-- Header Navigation Bar Sederhana -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            
            <!-- Branding App -->
            <div class="flex items-center gap-3">
                <a href="{{ route('dtsen.index') }}" class="flex items-center gap-2 group brand-logo-link">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-extrabold text-lg shadow-sm group-hover:bg-blue-700 transition-colors">
                        P
                    </div>
                    <div>
                        <h1 class="font-extrabold text-slate-900 text-lg leading-none flex items-center gap-1.5 brand-title">
                            PADU
                            <span class="px-2 py-0.5 text-[10px] uppercase tracking-wider font-bold bg-blue-100 text-blue-800 rounded-full">v1.02</span>
                        </h1>
                        <p class="text-xs text-slate-500 font-medium brand-subtitle">Pengolah & Analisis Data Terpadu (DTSEN 2026)</p>
                    </div>
                </a>
            </div>

            <!-- Global Privacy Switch Toggle & Menu Link -->
            <div class="flex items-center gap-4">
                
                <!-- Sakelar Masking Privasi (Persisten Tersimpan di Browser) -->
                <div class="flex items-center bg-slate-100 p-1 rounded-xl border border-slate-200">
                    <button @click="setMasked(true)" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                            :class="isMasked ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                        <span>🔒</span>
                        <span>Masked</span>
                    </button>
                    <button @click="setMasked(false)" 
                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5"
                            :class="!isMasked ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                        <span>🔓</span>
                        <span>Unmasked</span>
                    </button>
                </div>

                <!-- Navigation Links: Beranda DTSEN Desil -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('dtsen.index') }}" 
                       class="text-xs font-bold transition-colors flex items-center gap-1 nav-link-clean {{ request()->routeIs('dtsen.index') ? 'text-blue-700 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-200' : 'text-slate-700 hover:text-blue-600' }}">
                        <span>🏠</span>
                        <span>Beranda Utama (DTSEN 2026)</span>
                    </a>
                </div>
            </div>

        </div>
    </header>

    <!-- Alert Notifikasi Flash Message -->
    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-sm font-medium flex items-center justify-between shadow-sm" x-data="{ show: true }" x-show="show">
                <div class="flex items-center gap-2">
                    <span class="text-lg">✅</span>
                    <span>{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="text-emerald-700 font-bold hover:text-emerald-900 text-base">✕</button>
            </div>
        </div>
    @endif

    @if(isset($errors) && $errors && method_exists($errors, 'any') && $errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm font-medium space-y-1 shadow-sm" x-data="{ show: true }" x-show="show">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2 font-bold text-rose-800">
                        <span class="text-lg">⚠️</span>
                        <span>Terdapat kesalahan pada input data:</span>
                    </div>
                    <button @click="show = false" class="text-rose-700 font-bold hover:text-rose-900 text-base">✕</button>
                </div>
                <ul class="list-disc pl-8 text-xs text-rose-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <!-- Content Area Utama -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <!-- Footer Simple -->
    <footer class="bg-white border-t border-slate-200 py-6 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-2">
            <p><strong>PADU v1.02</strong> &copy; {{ date('Y') }} — Pengolah & Analisis Data Terpadu (Satu Data Indonesia & UU PDP).</p>
            <div class="flex items-center gap-4">
                <span>Mode Offline 100% Aman</span>
            </div>
        </div>
    </footer>

    @stack('scripts')

    <!-- Skrip Pengunci Posisi Scroll Presisi (Presisi 100% di Atas Tabel Data) -->
    <script>
        function scrollToTableTop() {
            let el = document.getElementById('tabel-data');
            if (el) {
                let rect = el.getBoundingClientRect();
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                let targetPos = scrollTop + rect.top - 75;
                window.scrollTo({ top: targetPos, behavior: 'instant' });
            }
        }

        document.addEventListener('click', function(e) {
            let link = e.target.closest('a[href*="#tabel-data"]');
            if (link && document.getElementById('tabel-data')) {
                let el = document.getElementById('tabel-data');
                let rect = el.getBoundingClientRect();
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                sessionStorage.setItem('padu_table_top_pos', (scrollTop + rect.top - 75).toString());
            }
        });

        document.addEventListener('submit', function(e) {
            let form = e.target.closest('form[action*="#tabel-data"]');
            if (form && document.getElementById('tabel-data')) {
                let el = document.getElementById('tabel-data');
                let rect = el.getBoundingClientRect();
                let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                sessionStorage.setItem('padu_table_top_pos', (scrollTop + rect.top - 75).toString());
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash.includes('tabel-data')) {
                let savedPos = sessionStorage.getItem('padu_table_top_pos');
                if (savedPos !== null) {
                    window.scrollTo({ top: parseInt(savedPos), behavior: 'instant' });
                } else {
                    scrollToTableTop();
                }
            }
        });
    </script>
</body>
</html>
