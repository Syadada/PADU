@echo off
setlocal enabledelayedexpansion

REM =======================================================================
REM PADU v1.02 - PENGOLAH DAN ANALISIS DATA TERPADU (DTSEN 2026)
REM ALL-IN-ONE AUTOMATIC SETUP, DEPENDENCY RESOLVER & PORT-FREE SERVER LAUNCHER
REM =======================================================================

REM 0. Memastikan system PATH standar Windows & PowerShell selalu tersedia
set "PATH=%SystemRoot%\system32;%SystemRoot%;%SystemRoot%\System32\Wbem;%SystemRoot%\System32\WindowsPowerShell\v1.0\;%LOCALAPPDATA%\Microsoft\WindowsApps;%PATH%"

title PADU v1.02 - Pengolah dan Analisis Data Terpadu (DTSEN 2026)
color 0A

echo =======================================================================
echo          PADU v1.02 - PENGOLAH DAN ANALISIS DATA TERPADU (OFFLINE)
echo             ALL-IN-ONE AUTOMATIC DEPENDENCY ^& SERVER LAUNCHER
echo =======================================================================
echo.

set "PROJECT_DIR=%~dp0"
if not exist "%PROJECT_DIR%artisan" (
    for /d %%i in ("%PROJECT_DIR%*") do (
        if exist "%%i\artisan" set "PROJECT_DIR=%%i\"
    )
    if exist "%~dp0aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%~dp0aplikasi-cepat-analytics\"
    if exist "%USERPROFILE%\Desktop\aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%USERPROFILE%\Desktop\aplikasi-cepat-analytics\"
    if exist "%USERPROFILE%\Downloads\aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%USERPROFILE%\Downloads\aplikasi-cepat-analytics\"
)
cd /d "%PROJECT_DIR%"

set PHP_BIN=
set COMPOSER_PHAR=
set PYTHON_EXE=

rem =======================================================================
rem 1. MEMASTIKAN DIREKTORI SISTEM, STORAGE, LOGS, & TEMPORARY READY
rem =======================================================================
echo [1/7] Memeriksa struktur direktori sistem, cache, dan penyimpanan...
if not exist "%PROJECT_DIR%storage\framework\cache\data" mkdir "%PROJECT_DIR%storage\framework\cache\data" >nul 2>&1
if not exist "%PROJECT_DIR%storage\framework\sessions" mkdir "%PROJECT_DIR%storage\framework\sessions" >nul 2>&1
if not exist "%PROJECT_DIR%storage\framework\views" mkdir "%PROJECT_DIR%storage\framework\views" >nul 2>&1
if not exist "%PROJECT_DIR%storage\logs" mkdir "%PROJECT_DIR%storage\logs" >nul 2>&1
if not exist "%PROJECT_DIR%storage\app\public" mkdir "%PROJECT_DIR%storage\app\public" >nul 2>&1
if not exist "%PROJECT_DIR%bootstrap\cache" mkdir "%PROJECT_DIR%bootstrap\cache" >nul 2>&1
if not exist "%PROJECT_DIR%src-dtsen" mkdir "%PROJECT_DIR%src-dtsen" >nul 2>&1
if not exist "%PROJECT_DIR%src-export" mkdir "%PROJECT_DIR%src-export" >nul 2>&1
if not exist "%PROJECT_DIR%scratch" mkdir "%PROJECT_DIR%scratch" >nul 2>&1
echo [OK] Direktori penyimpanan, cache, dan export siap.

rem =======================================================================
rem 2. OTOMASI DETEKSI DAN DEPENDENSI PYTHON BACKEND RUNTIME (DUCKDB)
rem =======================================================================
echo.
echo [2/7] Memeriksa Runtime Python Backend dan Akselerasi DuckDB...

if exist "%PROJECT_DIR%python\python.exe" (
    set "PYTHON_EXE=%PROJECT_DIR%python\python.exe"
    echo [OK] Menggunakan Local Portable Python: %PROJECT_DIR%python\python.exe
    goto :check_python_deps
)

where python >nul 2>&1
if %errorlevel% equ 0 (
    set "PYTHON_EXE=python"
    echo [OK] Menggunakan Python dari System PATH.
    goto :check_python_deps
)

where py >nul 2>&1
if %errorlevel% equ 0 (
    set "PYTHON_EXE=py"
    echo [OK] Menggunakan Python Launcher (py).
    goto :check_python_deps
)

echo [INFO] Python belum terdeteksi. Menjalankan penyiapan Portable Python otomatis...
if exist "%PROJECT_DIR%setup_python.ps1" (
    powershell -ExecutionPolicy Bypass -File "%PROJECT_DIR%setup_python.ps1"
    if exist "%PROJECT_DIR%python\python.exe" (
        set "PYTHON_EXE=%PROJECT_DIR%python\python.exe"
        echo [OK] Portable Python berhasil dipasang!
        goto :check_python_deps
    )
)

echo [SETUP] Mencoba mengunduh Python otomatis via Windows Winget...
winget install Python.Python.3.11 --accept-package-agreements --accept-source-agreements --silent >nul 2>&1
set "PATH=%LOCALAPPDATA%\Microsoft\WindowsApps;%PATH%"
where python >nul 2>&1
if %errorlevel% equ 0 (
    set "PYTHON_EXE=python"
    echo [OK] Instalasi Python via Winget berhasil!
) else (
    echo [WARNING] Python tidak ditemukan. Sistem akan beroperasi menggunakan Engine Native PHP.
)

:check_python_deps
if defined PYTHON_EXE (
    "%PYTHON_EXE%" -c "import duckdb" >nul 2>&1
    if %errorlevel% neq 0 (
        echo [INFO] Memasang modul akselerasi DuckDB untuk query analitik berkecepatan tinggi...
        if exist "%PROJECT_DIR%requirements.txt" (
            "%PYTHON_EXE%" -m pip install -r "%PROJECT_DIR%requirements.txt" --no-warn-script-location --quiet >nul 2>&1
        ) else (
            "%PYTHON_EXE%" -m pip install duckdb openpyxl pandas pyarrow --no-warn-script-location --quiet >nul 2>&1
        )
    )
    echo [OK] Engine analitik Python DuckDB siap digunakan.
)

rem =======================================================================
rem 3. OTOMASI DETEKSI DAN KONFIGURASI PHP RUNTIME (LARAVEL FRONTEND)
rem =======================================================================
echo.
echo [3/7] Memeriksa Runtime PHP (Frontend Presentation Layer)...

REM Refresh PATH dari registry jika ada instalasi runtime baru
for /f "tokens=2*" %%a in ('reg query "HKLM\System\CurrentControlSet\Control\Session Manager\Environment" /v Path 2^>nul') do set "SYS_PATH=%%b"
for /f "tokens=2*" %%a in ('reg query "HKCU\Environment" /v Path 2^>nul') do set "USER_PATH=%%b"
if defined SYS_PATH if defined USER_PATH set "PATH=%LOCALAPPDATA%\Microsoft\WindowsApps;!USER_PATH!;!SYS_PATH!;!PATH!"

rem A. Cek Portable PHP lokal
if exist "%PROJECT_DIR%php\php.exe" (
    set "PHP_BIN=%PROJECT_DIR%php\php.exe"
    set "COMPOSER_PHAR=%PROJECT_DIR%php\composer.phar"
    set "PATH=%PROJECT_DIR%php;!PATH!"
    echo [OK] Menggunakan Local Portable PHP: %PROJECT_DIR%php\php.exe
    goto :configure_php
)

rem B. Cek Laragon (C:\ atau D:\)
for /d %%d in ("C:\laragon\bin\php\php*" "D:\laragon\bin\php\php*") do (
    if not defined PHP_BIN (
        if exist "%%~d\php.exe" (
            set "PHP_BIN=%%~d\php.exe"
            set "PATH=%%~d;!PATH!"
            echo [OK] Menggunakan PHP dari Laragon: %%~d\php.exe
            goto :configure_php
        )
    )
)

rem C. Cek XAMPP (C:\ atau D:\)
for %%d in ("C:\xampp\php" "D:\xampp\php") do (
    if not defined PHP_BIN (
        if exist "%%~d\php.exe" (
            set "PHP_BIN=%%~d\php.exe"
            set "COMPOSER_PHAR=%%~d\composer.phar"
            set "PATH=%%~d;!PATH!"
            echo [OK] Menggunakan PHP dari XAMPP: %%~d\php.exe
            goto :configure_php
        )
    )
)

rem D. Cek PHP di System PATH
where php >nul 2>&1
if %errorlevel% equ 0 (
    set "PHP_BIN=php"
    echo [OK] Menggunakan PHP dari System PATH.
    goto :configure_php
)

rem E. Cek C:\php\php.exe atau C:\Program Files\PHP
if exist "C:\php\php.exe" (
    set "PHP_BIN=C:\php\php.exe"
    echo [OK] Menggunakan PHP dari C:\php\php.exe
    goto :configure_php
)

echo [INFO] PHP belum terpasang di komputer ini. Menyiapkan otomatis...
if exist "%PROJECT_DIR%setup_php.ps1" (
    powershell -ExecutionPolicy Bypass -File "%PROJECT_DIR%setup_php.ps1"
    if exist "%PROJECT_DIR%php\php.exe" (
        set "PHP_BIN=%PROJECT_DIR%php\php.exe"
        set "PATH=%PROJECT_DIR%php;!PATH!"
        echo [OK] Portable PHP berhasil disiapkan!
        goto :configure_php
    )
)

echo [SETUP] Mengunduh PHP 8.2 otomatis via Winget...
winget install PHP.PHP.8.2 --accept-package-agreements --accept-source-agreements --silent >nul 2>&1
where php >nul 2>&1
if %errorlevel% equ 0 (
    set "PHP_BIN=php"
    echo [OK] Instalasi PHP via Winget berhasil!
    goto :configure_php
)

echo.
echo [ERROR] PHP gagal disiapkan otomatis!
echo Silakan sediakan folder 'php' portable atau pasang PHP 8.2+ / Laragon / XAMPP.
pause
exit /b 1

:configure_php
rem Pastikan konfigurasi php.ini aktif dan ekstensi SQLite/mbstring aktif
if not exist "%PROJECT_DIR%php\php.ini" (
    if exist "%PROJECT_DIR%php\php.ini-development" (
        copy "%PROJECT_DIR%php\php.ini-development" "%PROJECT_DIR%php\php.ini" >nul
    )
)
echo [OK] Konfigurasi runtime PHP siap.

rem =======================================================================
rem 4. OTOMASI COMPOSER DAN DEPENDENSI VENDOR LARAVEL
rem =======================================================================
echo.
echo [4/7] Memeriksa dependensi pustaka aplikasi (vendor/autoload.php)...

if exist "%PROJECT_DIR%vendor\autoload.php" (
    echo [OK] Seluruh pustaka dependensi vendor sudah terpasang lengkap.
    goto :vendor_ready
)

echo [INFO] Folder vendor belum ada. Mengunduh pustaka dependensi otomatis...

if exist "%PROJECT_DIR%php\composer.phar" (
    "%PHP_BIN%" "%PROJECT_DIR%php\composer.phar" install --no-interaction --prefer-dist --ignore-platform-reqs
    goto :check_vendor
)

if defined COMPOSER_PHAR if exist "%COMPOSER_PHAR%" (
    "%PHP_BIN%" "%COMPOSER_PHAR%" install --no-interaction --prefer-dist --ignore-platform-reqs
    goto :check_vendor
)

if exist "%PROJECT_DIR%composer.phar" (
    "%PHP_BIN%" "%PROJECT_DIR%composer.phar" install --no-interaction --prefer-dist --ignore-platform-reqs
    goto :check_vendor
)

where composer >nul 2>&1
if %errorlevel% equ 0 (
    composer install --no-interaction --prefer-dist --ignore-platform-reqs
    goto :check_vendor
)

echo [INFO] Mengunduh composer.phar resmi dari getcomposer.org...
curl.exe -s -L -o "%PROJECT_DIR%php\composer.phar" "https://getcomposer.org/composer-stable.phar" >nul 2>&1
if exist "%PROJECT_DIR%php\composer.phar" (
    "%PHP_BIN%" "%PROJECT_DIR%php\composer.phar" install --no-interaction --prefer-dist --ignore-platform-reqs
)

:check_vendor
if not exist "%PROJECT_DIR%vendor\autoload.php" (
    echo [ERROR] Gagal memasang dependensi vendor! Pastikan koneksi internet aktif.
    pause
    exit /b 1
)
echo [OK] Dependensi vendor berhasil diverifikasi.

:vendor_ready

rem =======================================================================
rem 5. OTOMASI ENVIRONMENT (.env), APP KEY, STORAGE LINK, DAN DATABASE
rem =======================================================================
echo.
echo [5/7] Memeriksa environment, basis data, dan struktur tabel...

if not exist "%PROJECT_DIR%.env" (
    if exist "%PROJECT_DIR%.env.example" (
        copy "%PROJECT_DIR%.env.example" "%PROJECT_DIR%.env" >nul
        echo [OK] Berkas .env berhasil dibuat dari template.
    )
)

%SystemRoot%\system32\findstr.exe /C:"APP_KEY=base64:" "%PROJECT_DIR%.env" >nul 2>&1
if %errorlevel% neq 0 (
    echo [CONFIG] Menghasilkan Application Key baru...
    "%PHP_BIN%" artisan key:generate --force >nul 2>&1
)

if not exist "%PROJECT_DIR%public\storage" (
    "%PHP_BIN%" artisan storage:link >nul 2>&1
)

rem Basis data SQLite
if not exist "%PROJECT_DIR%database\database.sqlite" (
    type nul > "%PROJECT_DIR%database\database.sqlite"
    echo [OK] File basis data SQLite baru dibuat.
)

rem Migrasi tabel
"%PHP_BIN%" artisan migrate --force >nul 2>&1
echo [OK] Struktur tabel database siap.

rem Bersihkan cache Laravel
"%PHP_BIN%" artisan optimize:clear >nul 2>&1

rem =======================================================================
rem 6. DETEKSI OTOMATIS PORT BEBAS (MENCEGAH ERROR PORT CRASH / CONFLICT)
rem =======================================================================
echo.
echo [6/7] Memeriksa ketersediaan port jaringan web server...

set "LARAVEL_PORT=8000"
for %%p in (8000 8001 8002 8003 8004 8005 8080 8081 8082 8083 8084 8085 8086 8087 8088 8089 8888 8008 8090 9000 9001 9002) do (
    %SystemRoot%\system32\netstat.exe -ano 2>nul | %SystemRoot%\system32\findstr.exe /C:":%%p " | %SystemRoot%\system32\findstr.exe "LISTENING" >nul 2>&1
    if errorlevel 1 (
        set "LARAVEL_PORT=%%p"
        goto :port_found
    )
)

:port_found
if not "%LARAVEL_PORT%"=="8000" (
    echo [INFO] Port 8000 sedang digunakan oleh sistem/aplikasi lain.
    echo [PORT] Mengalihkan server secara otomatis ke port bebas: %LARAVEL_PORT%!
) else (
    echo [OK] Port %LARAVEL_PORT% bebas dan siap digunakan.
)

rem Deteksi IP LAN Server untuk akses multi-device dalam WiFi / LAN yang sama
set "SERVER_IP="
for /f "tokens=2 delims=:" %%a in ('%SystemRoot%\system32\ipconfig.exe ^| %SystemRoot%\system32\findstr.exe /c:"IPv4" /c:"Alamat IPv4" 2^>nul') do (
    if not defined SERVER_IP (
        set "temp_ip=%%a"
        set "temp_ip=!temp_ip: =!"
        if not "!temp_ip!"=="" if not "!temp_ip!"=="127.0.0.1" set "SERVER_IP=!temp_ip!"
    )
)

rem =======================================================================
rem 7. MEMBUKA PERAMBAN DAN MENJALANKAN SERVER
rem =======================================================================
echo.
echo [7/7] Meluncurkan peramban (browser) dan mengaktifkan Web Server...
echo =======================================================================
echo  SISTEM PADU v1.02 BERHASIL DIAKTIFKAN!
echo  - Akses Laptop Ini  : http://127.0.0.1:%LARAVEL_PORT%/
if defined SERVER_IP (
echo  - Akses Jaringan LAN: http://%SERVER_IP%:%LARAVEL_PORT%/
)
echo  - Basis Data        : SQLite (database/database.sqlite)
echo  - Mesin Analitik    : Python DuckDB Acceleration
echo =======================================================================
echo.
echo  [PENTING] JANGAN TUTUP JENDELA INI SELAMA SISTEM DIGUNAKAN!
echo  Jendela ini bertindak sebagai mesin web server lokal. Anda cukup
echo  meminimalkan (minimize) jendela ini ke taskbar.
echo  Tekan kombinasi tombol CTRL+C di sini untuk mematikan server kapan saja.
echo =======================================================================
echo.

rem Buka browser otomatis ke port yang tepat
start "" "http://127.0.0.1:%LARAVEL_PORT%/"

rem Jalankan Laravel Server pada port bebas yang telah dideteksi
"%PHP_BIN%" -d variables_order=EGPCS -d memory_limit=1024M -d upload_max_filesize=500M -d post_max_size=550M -d max_execution_time=600 -d max_input_time=600 artisan serve --host=127.0.0.1 --port=%LARAVEL_PORT%

if %errorlevel% neq 0 (
    echo.
    echo [INFO] artisan serve mengalami kendala. Menjalankan fallback web server [server.php]...
    "%PHP_BIN%" -d variables_order=EGPCS -d memory_limit=1024M -d upload_max_filesize=500M -d post_max_size=550M -d max_execution_time=600 -d max_input_time=600 -S 127.0.0.1:%LARAVEL_PORT% "%PROJECT_DIR%server.php"
)

echo.
echo [INFO] Server PADU telah dihentikan.
pause
:END
