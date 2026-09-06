# Script Penyiapan Portable Python Otomatis (TLS 1.2 & Multi-Mirror)
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$ProgressPreference = 'SilentlyContinue'

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$zipPath = Join-Path $baseDir "python_portable.zip"
$pyDir = Join-Path $baseDir "python"

if (Test-Path "$pyDir\python.exe") {
    Write-Host "[OK] Portable Python sudah terpasang."
    exit 0
}

# Cek apakah python sudah terdeteksi di sistem
try {
    $out = & python --version 2>&1
    if ($out -like "*Python 3*") {
        Write-Host "[OK] Python terdeteksi di System PATH."
        exit 0
    }
} catch {}

if (-not (Test-Path $pyDir)) {
    New-Item -ItemType Directory -Path $pyDir | Out-Null
}

$urls = @(
    "https://www.python.org/ftp/python/3.11.9/python-3.11.9-embed-amd64.zip",
    "https://www.python.org/ftp/python/3.10.11/python-3.10.11-embed-amd64.zip"
)

$downloaded = $false
foreach ($u in $urls) {
    try {
        Write-Host "[DOWNLOAD] Mengunduh Portable Python (10MB)..."
        Invoke-WebRequest -Uri $u -OutFile $zipPath -UserAgent 'Mozilla/5.0' -UseBasicParsing -TimeoutSec 60
        if ((Test-Path $zipPath) -and ((Get-Item $zipPath).Length -gt 5000000)) {
            $downloaded = $true
            break
        }
    } catch {
        Write-Host "[MIRROR FAILED] Mencoba mirror berikutnya..."
    }
}

if ($downloaded) {
    Write-Host "[EXTRACT] Mengurai Portable Python..."
    Expand-Archive -Path $zipPath -DestinationPath $pyDir -Force
    Remove-Item $zipPath -Force -ErrorAction SilentlyContinue

    # Enable import site & current directory in .pth file
    $pthFiles = Get-ChildItem -Path $pyDir -Filter "python*._pth"
    foreach ($f in $pthFiles) {
        $content = Get-Content $f.FullName
        $content = $content -replace '#import site', 'import site'
        $content += "`n."
        Set-Content -Path $f.FullName -Value $content
    }

    Write-Host "[SUKSES] Portable Python berhasil disiapkan di: $pyDir"
} else {
    Write-Host "[WARN] Gagal mengunduh Portable Python. Menggunakan Engine Native PHP."
}
