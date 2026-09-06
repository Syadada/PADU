@echo off
title PADU v1.02 - Pengolah dan Analisis Data Terpadu (DTSEN 2026)
color 0A

echo =======================================================================
echo          PADU v1.02 - PENGOLAH DAN ANALISIS DATA TERPADU (OFFLINE)
echo =======================================================================
echo.
echo Memeriksa lingkungan server dan peluncuran aplikasi...

set "PROJECT_DIR=%~dp0"
if not exist "%PROJECT_DIR%artisan" (
    if exist "%~dp0aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%~dp0aplikasi-cepat-analytics\"
    if exist "%USERPROFILE%\Desktop\aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%USERPROFILE%\Desktop\aplikasi-cepat-analytics\"
    if exist "%USERPROFILE%\Downloads\aplikasi-cepat-analytics\artisan" set "PROJECT_DIR=%USERPROFILE%\Downloads\aplikasi-cepat-analytics\"
)
cd /d "%PROJECT_DIR%"

rem 1. Cek Portable PHP lokal dari folder aplikasi
if exist "%PROJECT_DIR%php\php.exe" (
    set "PHP_BIN=%PROJECT_DIR%php\php.exe"
    echo [OK] Menggunakan Portable PHP lokal.
    goto LAUNCH_SERVER
)

rem 2. Cek PHP di Environment System PATH
where php >nul 2>nul
if %errorlevel% equ 0 (
    set "PHP_BIN=php"
    echo [OK] Menggunakan PHP dari System PATH.
    goto LAUNCH_SERVER
)

rem 3. Cek Lokasi Umum (C:\xampp\php\php.exe, C:\php\php.exe)
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

rem 4. Cek Laragon jika ada
if exist "C:\laragon\bin\php" (
    for /d %%d in ("C:\laragon\bin\php\php-*") do (
        if exist "%%d\php.exe" (
            set "PHP_BIN=%%d\php.exe"
            echo [OK] Menggunakan PHP dari Laragon.
            goto LAUNCH_SERVER
        )
    )
)

rem 5. Jika PHP Belum Ada, Jalankan setup_php.ps1
echo.
echo [INFO] PHP belum terdeteksi di laptop ini.
echo [DOWNLOAD] Menyiapkan Portable PHP Server otomatis...

powershell -ExecutionPolicy Bypass -File "%PROJECT_DIR%setup_php.ps1"

if exist "%PROJECT_DIR%php\php.exe" (
    set "PHP_BIN=%PROJECT_DIR%php\php.exe"
    echo [SUKSES] Portable PHP berhasil disiapkan!
    goto LAUNCH_SERVER
)

echo.
echo [ERROR] PHP belum terpasang dan unduhan otomatis tidak tersedia.
echo Silakan pasang PHP 8.x atau ekstrak PHP ke folder php.
echo.
pause
goto END

:LAUNCH_SERVER
rem Cek & buat file .env jika belum ada
if not exist "%PROJECT_DIR%.env" (
    if exist "%PROJECT_DIR%.env.example" (
        copy "%PROJECT_DIR%.env.example" "%PROJECT_DIR%.env" >nul
    )
)

rem Generate APP_KEY jika belum terisi
findstr /C:"APP_KEY=base64:" "%PROJECT_DIR%.env" >nul 2>&1
if %errorlevel% neq 0 (
    echo [CONFIG] Menggenerasi Application Key...
    "%PHP_BIN%" artisan key:generate --force >nul 2>&1
)

rem Cek & siapkan Portable Python otomatis jika belum ada
if not exist "%PROJECT_DIR%python\python.exe" (
    where python >nul 2>&1
    if %errorlevel% neq 0 (
        echo [INFO] Python belum terdeteksi di laptop ini.
        echo [DOWNLOAD] Menyiapkan Portable Python otomatis (10MB)...
        powershell -ExecutionPolicy Bypass -File "%PROJECT_DIR%setup_python.ps1"
    )
)

echo.
echo [STARTING] Menjalankan server lokal PADU di http://127.0.0.1:8000/ ...
start "" /b "%PHP_BIN%" artisan serve --host=127.0.0.1 --port=8000



echo [BROWSER] Membuka browser otomatis ke http://127.0.0.1:8000/ ...
powershell -Command "Start-Process 'http://127.0.0.1:8000/'"

:END
echo.
echo [SUKSES] Aplikasi PADU aktif di browser Anda (http://127.0.0.1:8000/).
echo =======================================================================
