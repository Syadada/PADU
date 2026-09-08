@echo off
title PADU v1.02 - Peluncur Docker Container (Offline Analytics)
color 0B

echo =======================================================================
echo         PADU v1.02 - PELUNCUR DOCKER CONTAINER (PONTIS DAN PORTABLE)
echo =======================================================================
echo.

rem 1. Deteksi dan tambahkan path Docker serta System32 ke PATH
set "PATH=C:\Users\rasyaad\AppData\Local\Programs\DockerDesktop\resources\bin;%LOCALAPPDATA%\Programs\DockerDesktop\resources\bin;%USERPROFILE%\AppData\Local\Programs\DockerDesktop\resources\bin;C:\Program Files\Docker\Docker\resources\bin;C:\Windows\System32;%PATH%"

rem 2. Cek ketersediaan perintah docker
where docker >nul 2>&1
if %errorlevel% neq 0 goto DOCKER_NOT_FOUND
goto CHECK_DAEMON

:DOCKER_NOT_FOUND
echo [ERROR] Perintah Docker belum terdaftar di PATH system!
echo Jika Docker Desktop baru dipasang, silakan buka Docker Desktop dari Desktop/Start Menu.
echo.
echo [SOLUSI] Jika laptop tidak terpasang Docker Desktop, gunakan file peluncur tanpa Docker:
echo           Klik 2x pada file: JALANKAN_PADU.bat
echo.
pause
exit /b 1

:CHECK_DAEMON
rem 3. Cek apakah Docker Engine / Desktop Daemon sedang aktif
docker info >nul 2>&1
if %errorlevel% equ 0 goto START_CONTAINER

echo [INFO] Docker Desktop terpasang tetapi belum berjalan.
echo [LAUNCH] Membuka Docker Desktop otomatis...
if exist "C:\Program Files\Docker\Docker\Docker Desktop.exe" start "" "C:\Program Files\Docker\Docker\Docker Desktop.exe"
if exist "%LOCALAPPDATA%\Programs\DockerDesktop\Docker Desktop.exe" start "" "%LOCALAPPDATA%\Programs\DockerDesktop\Docker Desktop.exe"
if exist "%USERPROFILE%\AppData\Local\Programs\DockerDesktop\Docker Desktop.exe" start "" "%USERPROFILE%\AppData\Local\Programs\DockerDesktop\Docker Desktop.exe"
echo [WAIT] Menunggu Docker Engine aktif (15 detik)...
timeout /t 15 /nobreak >nul

:START_CONTAINER
echo [STARTING] Membangun dan Menjalankan Docker Container PADU...
docker-compose up -d --build

echo.
echo =======================================================================
echo [SUKSES] Aplikasi PADU berjalan dalam Docker Container di http://localhost:8000/
echo =======================================================================
echo Membuka browser otomatis...
powershell -Command "Start-Process 'http://localhost:8000/'"
pause


