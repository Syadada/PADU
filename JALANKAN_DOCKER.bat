@echo off
title PADU v1.02 - Peluncur Docker Container (Offline Analytics)
color 0B

echo =======================================================================
echo         PADU v1.02 - PELUNCUR DOCKER CONTAINER (PONTIS & PORTABLE)
echo =======================================================================
echo.

where docker >nul 2>&1
if %errorlevel% neq 0 (
    echo [ERROR] Docker belum terdeteksi di laptop ini!
    echo Silakan install Docker Desktop dari: https://www.docker.com/products/docker-desktop/
    echo.
    pause
    exit /b 1
)

echo [STARTING] Membangun & Menjalankan Docker Container PADU...
docker-compose up -d --build

echo.
echo [SUKSES] Aplikasi PADU berjalan dalam Docker Container di http://localhost:8000/
echo Membuka browser otomatis...
powershell -Command "Start-Process 'http://localhost:8000/'"
pause
