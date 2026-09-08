@echo off
title PADU v1.02 - Peluncur Docker Container (Offline Analytics)
color 0B

echo =======================================================================
echo         PADU v1.02 - PELUNCUR DOCKER CONTAINER (PONTIS & PORTABLE)
echo =======================================================================
echo.

rem 1. Tambahkan path Docker default jika belum ada di PATH system
if exist "C:\Program Files\Docker\Docker\resources\bin\docker.exe" (
    set "PATH=%PATH%;C:\Program Files\Docker\Docker\resources\bin"
)

rem 2. Cek apakah perintah docker tersedia
where docker >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Perintah Docker belum terdaftar di PATH system!
    echo Jika Docker Desktop baru dipasang, silakan buka Docker Desktop dari Desktop/Start Menu.
    echo.
    pause
    exit /b 1
)

rem 3. Cek apakah Docker Engine / Desktop Daemon sedang aktif
docker info >nul 2>&1
if %errorlevel% neq 0 (
    echo [INFO] Docker Desktop terpasang tetapi belum berjalan.
    echo [LAUNCH] Membuka Docker Desktop otomatis...
    if exist "C:\Program Files\Docker\Docker\Docker Desktop.exe" (
        start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
    )
    echo [WAIT] Menunggu Docker Engine aktif (15 detik)...
    timeout /t 15 /nobreak >nul
)

echo [STARTING] Membangun & Menjalankan Docker Container PADU...
docker-compose up -d --build

echo.
echo =======================================================================
echo [SUKSES] Aplikasi PADU berjalan dalam Docker Container di http://localhost:8000/
echo =======================================================================
echo Membuka browser otomatis...
powershell -Command "Start-Process 'http://localhost:8000/'"
pause
