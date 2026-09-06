@echo off
title PADU v1.02 - Pengolah dan Analisis Data Terpadu (DTSEN 2026)
color 0A

echo =======================================================================
echo          PADU v1.02 - PENGOLAH DAN ANALISIS DATA TERPADU (OFFLINE)
echo =======================================================================
echo.
echo Memeriksa lingkungan server dan peluncuran aplikasi...

:: Resolusi lokasi folder proyek jika file .bat dipindah keluar dari folder proyek
set "PROJECT_DIR=%~dp0"
if not exist "%PROJECT_DIR%artisan" (
    if exist "%~dp0aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%~dp0aplikasi-cepat-analytics\"
    if exist "%USERPROFILE%\Desktop\aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%USERPROFILE%\Desktop\aplikasi-cepat-analytics\"
    if exist "%USERPROFILE%\Downloads\aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%USERPROFILE%\Downloads\aplikasi-cepat-analytics\"
)
cd /d "%PROJECT_DIR%"

:: 1. Cek Portable PHP lokal dari folder aplikasi (%PROJECT_DIR%php\php.exe)
if exist "%PROJECT_DIR%php\php.exe" (
    set "PHP_BIN=%PROJECT_DIR%php\php.exe"
    echo [OK] Menggunakan Portable PHP lokal dari folder aplikasi (%PROJECT_DIR%php\php.exe).
    goto LAUNCH_SERVER
)

:: 2. Cek PHP di Environment System PATH
where php >nul 2>nul
if not errorlevel 1 (
    set "PHP_BIN=php"
    echo [OK] Menggunakan PHP dari System PATH.
    goto LAUNCH_SERVER
)

:: 3. Cek Lokasi Umum (C:\xampp\php\php.exe, C:\php\php.exe, C:\laragon\bin\php\...)
if exist "C:\xampp\php\php.exe" (
    set "PHP_BIN=C:\xampp\php\php.exe"
    echo [OK] Menggunakan PHP dari C:\xampp\php\php.exe
    goto LAUNCH_SERVER
)

if exist "C:\php\php.exe" (
    set "PHP_BIN=C:\php\php.exe"
    echo [OK] Menggunakan PHP dari C:\php\php.exe
    goto LAUNCH_SERVER
)

:: Cek Laragon
for /d %%d in ("C:\laragon\bin\php\php-*") do (
    if exist "%%d\php.exe" (
        set "PHP_BIN=%%d\php.exe"
        echo [OK] Menggunakan PHP dari Laragon: %%d\php.exe
        goto LAUNCH_SERVER
    )
)

:: 4. Jika PHP Belum Ada, Jalankan setup_php.ps1
echo.
echo [INFO] PHP belum terdeteksi di laptop ini.
echo [DOWNLOAD] Menyiapkan Portable PHP Server otomatis ke folder aplikasi (%PROJECT_DIR%php)...

powershell -ExecutionPolicy Bypass -File "%PROJECT_DIR%setup_php.ps1"

if exist "%PROJECT_DIR%php\php.exe" (
    set "PHP_BIN=%PROJECT_DIR%php\php.exe"
    echo [SUKSES] Portable PHP berhasil disiapkan di folder lokal!
    goto LAUNCH_SERVER
)

echo.
echo [ERROR] PHP belum terpasang dan unduhan otomatis tidak tersedia (Offline).
echo Silakan pasang PHP 8.x (XAMPP / Laragon) atau ekstrak PHP ke folder '%PROJECT_DIR%php'.
echo.
pause
goto END

:LAUNCH_SERVER
echo.
echo [STARTING] Menjalankan server lokal PADU di http://127.0.0.1:8000/ ...
start "" /b "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000

timeout /t 2 >nul

echo [BROWSER] Membuka browser otomatis ke http://127.0.0.1:8000/ ...
powershell -Command "Start-Process 'http://127.0.0.1:8000/'"

:END
echo.
echo [SUKSES] Aplikasi PADU aktif di browser Anda (http://127.0.0.1:8000/).
echo =======================================================================

