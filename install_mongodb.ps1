# MediCare — MongoDB PHP Extension Auto-Installer
# Run this script as: powershell -ExecutionPolicy Bypass -File install_mongodb.ps1

Write-Host "=== MediCare MongoDB Extension Installer ===" -ForegroundColor Cyan

$phpDir  = "C:\php"
$extDir  = "$phpDir\ext"
$phpIni  = "$phpDir\php.ini"
$zipFile = "$phpDir\php_mongodb.zip"
$dllUrl  = "https://downloads.php.net/~windows/pecl/releases/mongodb/2.3.1/php_mongodb-2.3.1-8.5-nts-vs17-x64.zip"

# Step 1: Download
Write-Host "`n[1/4] Downloading php_mongodb.dll..." -ForegroundColor Yellow
if (Test-Path $zipFile) { Remove-Item $zipFile -Force }

$wc = New-Object System.Net.WebClient
$wc.DownloadFile($dllUrl, $zipFile)
Write-Host "     Downloaded: $((Get-Item $zipFile).Length) bytes" -ForegroundColor Green

# Step 2: Extract
Write-Host "`n[2/4] Extracting DLL..." -ForegroundColor Yellow
$tmpDir = "$phpDir\mongodb_tmp"
if (Test-Path $tmpDir) { Remove-Item $tmpDir -Recurse -Force }
Expand-Archive $zipFile -DestinationPath $tmpDir -Force
$dll = Get-ChildItem $tmpDir -Filter "php_mongodb.dll" -Recurse | Select-Object -First 1
if (!$dll) { Write-Host "ERROR: DLL not found in ZIP!" -ForegroundColor Red; exit 1 }
Copy-Item $dll.FullName "$extDir\php_mongodb.dll" -Force
Write-Host "     DLL copied to $extDir\php_mongodb.dll" -ForegroundColor Green

# Step 3: Update php.ini
Write-Host "`n[3/4] Updating php.ini..." -ForegroundColor Yellow
$ini = Get-Content $phpIni -Raw
if ($ini -notmatch "php_mongodb") {
    Add-Content $phpIni "`nextension=php_mongodb.dll"
    Write-Host "     extension=php_mongodb.dll added" -ForegroundColor Green
} else {
    Write-Host "     Already configured" -ForegroundColor Green
}

# Step 4: Verify
Write-Host "`n[4/4] Verifying..." -ForegroundColor Yellow
$result = & "$phpDir\php.exe" -m 2>&1 | Select-String "mongodb"
if ($result) {
    Write-Host "     SUCCESS! MongoDB extension loaded: $result" -ForegroundColor Green
} else {
    Write-Host "     WARNING: Extension may not be loaded. Check php.ini" -ForegroundColor Red
}

# Cleanup
Remove-Item $tmpDir -Recurse -Force -ErrorAction SilentlyContinue
Remove-Item $zipFile -Force -ErrorAction SilentlyContinue

Write-Host "`n=== Done! Now restart your PHP server ===" -ForegroundColor Cyan
Write-Host "Run: C:\php\php.exe -S localhost:8000 router.php" -ForegroundColor White
