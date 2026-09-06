# Script Penyiapan Portable PHP Otomatis (TLS 1.2 & Multi-Mirror)
[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
$ProgressPreference = 'SilentlyContinue'

$baseDir = Split-Path -Parent $MyInvocation.MyCommand.Definition
$zipPath = Join-Path $baseDir "php_portable.zip"
$phpDir = Join-Path $baseDir "php"

if (Test-Path "$phpDir\php.exe") {
    Write-Host "[OK] Portable PHP sudah ada."
    exit 0
}

if (-not (Test-Path $phpDir)) {
    New-Item -ItemType Directory -Path $phpDir | Out-Null
}

$urls = @(
    "https://windows.php.net/downloads/releases/archives/php-8.2.12-nts-Win32-vs16-x64.zip",
    "https://windows.php.net/downloads/releases/php-8.2.12-nts-Win32-vs16-x64.zip",
    "https://windows.php.net/downloads/releases/archives/php-8.2.0-nts-Win32-vs16-x64.zip"
)

$downloaded = $false
foreach ($u in $urls) {
    try {
        Write-Host "[DOWNLOAD] Mengunduh dari mirror: $u"
        Invoke-WebRequest -Uri $u -OutFile $zipPath -UserAgent 'Mozilla/5.0' -UseBasicParsing -TimeoutSec 45
        if ((Test-Path $zipPath) -and ((Get-Item $zipPath).Length -gt 1000000)) {
            $downloaded = $true
            break
        }
    } catch {
        Write-Host "[MIRROR FAILED] Mencoba mirror berikutnya..."
    }
}

if ($downloaded) {
    Write-Host "[EXTRACT] Mengurai berkas Portable PHP..."
    Expand-Archive -Path $zipPath -DestinationPath $phpDir -Force
    Remove-Item $zipPath -Force -ErrorAction SilentlyContinue

    if (Test-Path "$phpDir\php.ini-development") {
        Copy-Item "$phpDir\php.ini-development" "$phpDir\php.ini" -Force
        Add-Content -Path "$phpDir\php.ini" -Value "`nextension_dir = `"ext`"`nextension=pdo_sqlite`nextension=mbstring`nextension=fileinfo`nextension=openssl`nextension=sqlite3"
    }
    Write-Host "[SUKSES] Portable PHP berhasil disiapkan di: $phpDir"
} else {
    Write-Host "[ERROR] Gagal mengunduh Portable PHP dari seluruh mirror."
    exit 1
}
