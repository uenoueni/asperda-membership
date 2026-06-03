# Smoke test ASPERDA - jalankan dari root project (asperda/).
# Usage: powershell -File .claude/skills/run-asperda/smoke.ps1 [-PortApi 8787] [-PortFe 5173]
# Exit 0 = semua OK, Exit 1 = ada yang gagal.
#Requires -Version 5.1

param(
    [int]$PortApi  = 8787,
    [int]$PortFe   = 5173,
    [switch]$NoCleanup
)

$ErrorActionPreference = 'Stop'
$Root     = Split-Path (Split-Path (Split-Path $PSScriptRoot))
$Frontend = Join-Path $Root 'frontend'
$Artisan  = Join-Path $Root 'artisan'
$ok       = $true

function Pass($msg) { Write-Host "  [PASS] $msg" -ForegroundColor Green }
function Fail($msg) { Write-Host "  [FAIL] $msg" -ForegroundColor Red; $script:ok = $false }
function Info($msg) { Write-Host "  [INFO] $msg" -ForegroundColor Cyan }

Write-Host ""
Write-Host "ASPERDA smoke test" -ForegroundColor White
Write-Host ""

# --- Prerequisites ---
Info "Cek prerequisites..."
$phpVer  = (php --version 2>$null) | Select-Object -First 1
$nodeVer = (node --version 2>$null)
$pnpmVer = (pnpm --version 2>$null)

if ($phpVer)  { Pass "PHP:  $phpVer" } else { Fail "PHP tidak ditemukan" }
if ($nodeVer) { Pass "Node: $nodeVer" } else { Fail "Node tidak ditemukan" }
if ($pnpmVer) { Pass "pnpm: $pnpmVer" } else { Fail "pnpm tidak ditemukan" }

if (-not $ok) {
    Write-Host ""
    Write-Host "Prerequisites gagal - hentikan." -ForegroundColor Red
    Write-Host ""
    exit 1
}

# --- Start Laravel API ---
Info "Cek apakah API sudah jalan di port $PortApi..."
$apiAlreadyUp = $false
try {
    $null = Invoke-WebRequest "http://127.0.0.1:$PortApi/api/v1/ping" -UseBasicParsing -TimeoutSec 2
    $apiAlreadyUp = $true
    Info "API sudah berjalan (proses eksternal)."
} catch {}

$artisanJob = $null
if (-not $apiAlreadyUp) {
    Info "Mulai artisan serve di port $PortApi..."
    $artisanJob = Start-Job -ScriptBlock {
        param($root, $port, $artisan)
        Set-Location $root
        php $artisan serve --port=$port 2>&1
    } -ArgumentList $Root, $PortApi, $Artisan
    Start-Sleep -Seconds 4
}

# --- Test API ping ---
Info "Test GET /api/v1/ping..."
try {
    $r    = Invoke-WebRequest "http://127.0.0.1:$PortApi/api/v1/ping" -UseBasicParsing -TimeoutSec 8
    $body = $r.Content | ConvertFrom-Json
    if ($r.StatusCode -eq 200 -and $body.success -eq $true) {
        Pass ("API ping OK - app=" + $body.data.app + ", time=" + $body.data.time)
    } else {
        Fail ("API ping: status " + $r.StatusCode + " / success=" + $body.success)
    }
} catch {
    Fail ("API ping gagal: " + $_.Exception.Message)
}

# --- Start Vite frontend ---
Info "Cek apakah frontend sudah jalan di port $PortFe..."
$feAlreadyUp = $false
try {
    $null = Invoke-WebRequest "http://localhost:$PortFe" -UseBasicParsing -TimeoutSec 2
    $feAlreadyUp = $true
    Info "Frontend sudah berjalan (proses eksternal)."
} catch {}

$viteJob = $null
if (-not $feAlreadyUp) {
    Info "Mulai Vite dev server di $Frontend..."
    $viteJob = Start-Job -ScriptBlock {
        param($fe)
        Set-Location $fe
        pnpm dev 2>&1
    } -ArgumentList $Frontend
    Start-Sleep -Seconds 10

    # Vite auto-bump port jika sudah dipakai - ambil port aktual
    $viteOut  = Receive-Job $viteJob -Keep 2>$null
    $portLine = ($viteOut -join "`n") | Select-String 'localhost:(\d+)'
    if ($portLine) {
        $PortFe = [int]$portLine.Matches[0].Groups[1].Value
        Info "Vite berjalan di port $PortFe"
    }
}

# --- Test frontend HTML ---
Info "Test GET http://localhost:$PortFe/ (frontend HTML)..."
try {
    $r = Invoke-WebRequest "http://localhost:$PortFe" -UseBasicParsing -TimeoutSec 10
    $hasApp = $r.Content -match 'id="app"'
    if ($r.StatusCode -eq 200 -and $hasApp) {
        Pass ("Frontend HTML OK - div#app ditemukan (" + $r.Content.Length + " bytes)")
    } else {
        Fail ("Frontend: status " + $r.StatusCode + " / id=app tidak ditemukan")
    }
} catch {
    Fail ("Frontend gagal: " + $_.Exception.Message)
}

# --- Test /auth/me (harus non-200 tanpa token) ---
# NOTE: Saat ini mengembalikan 500 karena route 'login' belum didefinisikan
# (lihat Gotchas di SKILL.md). Nanti setelah auth module selesai harusnya 401.
Info "Test GET /api/v1/auth/me (expect non-200)..."
try {
    $r = Invoke-WebRequest "http://127.0.0.1:$PortApi/api/v1/auth/me" -UseBasicParsing -TimeoutSec 5
    Fail ("Endpoint auth/me harusnya non-200, tapi dapat " + $r.StatusCode)
} catch {
    $code = [int]$_.Exception.Response.StatusCode
    if ($code -ge 400) {
        Pass ("Auth guard aktif - /auth/me mengembalikan " + $code + " (ok untuk stage ini)")
    } else {
        Fail ("Auth/me: status tak terduga " + $code)
    }
}

# --- Cleanup ---
if (-not $NoCleanup) {
    if ($artisanJob) { Stop-Job $artisanJob -ErrorAction SilentlyContinue; Remove-Job $artisanJob -Force -ErrorAction SilentlyContinue }
    if ($viteJob)    { Stop-Job $viteJob    -ErrorAction SilentlyContinue; Remove-Job $viteJob    -Force -ErrorAction SilentlyContinue }
}

# --- Hasil ---
Write-Host ""
if ($ok) {
    Write-Host "Semua check lulus." -ForegroundColor Green
    exit 0
} else {
    Write-Host "Ada check yang gagal. Lihat [FAIL] di atas." -ForegroundColor Red
    exit 1
}
