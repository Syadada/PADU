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
        Write-Host "[OK] Python 3 terdeteksi di System PATH."
        $duckOut = & python -c "import duckdb" 2>&1
        if ($duckOut -like "*ModuleNotFoundError*" -or $LASTEXITCODE -ne 0) {
            Write-Host "[DUCKDB] Memasang modul akselerasi DuckDB di Python Sistem..."
            & python -m pip install duckdb --quiet 2>$null
        }
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

    # Unduh & Pasang get-pip.py untuk Portable Embedded Python
    $getPipPath = Join-Path $pyDir "get-pip.py"
    try {
        Write-Host "[PIP] Mengunduh & Menyiapkan Pip Installer..."
        Invoke-WebRequest -Uri "https://bootstrap.pypa.io/get-pip.py" -OutFile $getPipPath -UserAgent 'Mozilla/5.0' -UseBasicParsing -TimeoutSec 30
        if (Test-Path $getPipPath) {
            & "$pyDir\python.exe" "$getPipPath" --no-warn-script-location --quiet 2>$null
            Remove-Item $getPipPath -Force -ErrorAction SilentlyContinue
        }
    } catch {}

    try {
        Write-Host "[DUCKDB] Memasang modul akselerasi DuckDB..."
        & "$pyDir\python.exe" -m pip install duckdb --quiet 2>$null
    } catch {}

    Write-Host "[SUKSES] Portable Python berhasil disiapkan di: $pyDir"
} else {
    Write-Host "[WARN] Gagal mengunduh Portable Python. Menggunakan Engine Native PHP."
}
