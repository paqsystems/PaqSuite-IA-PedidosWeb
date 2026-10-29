# Host PaqSuite — desarrollo local (Windows)
# Uso en la raíz del repo:
#   .\scripts\dev\start-dev.ps1              → solo worker de cola
#   .\scripts\dev\start-dev.ps1 -All         → worker + backend + frontend (PedidosWeb)

param(
    [switch]$All,
    [int]$BackendPort = 8088,
    [int]$FrontendPort = 3010
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'Start-LaravelDevQueueWorker.ps1')

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$backend = Join-Path $repoRoot 'backend'
$frontend = Join-Path $repoRoot 'frontend'
$projectLabel = Split-Path $repoRoot -Leaf
$php = Get-PhpExecutable

function Test-ProcessCommandLineMatch {
    param([string]$Pattern)
    Get-CimInstance Win32_Process -Filter "name='php.exe'" -ErrorAction SilentlyContinue |
        Where-Object { $_.CommandLine -match $Pattern } |
        Select-Object -First 1
}

Start-LaravelDevQueueWorker -BackendPath $backend -ProjectLabel $projectLabel

if (-not $All) {
    Write-Host ''
    Write-Host "Solo cola para $projectLabel. Todos los hosts: .\scripts\dev\start-all-hosts-workers.ps1"
    Write-Host "Stack completo: .\scripts\dev\start-dev.ps1 -All"
    return
}

if (-not (Test-Path $frontend)) {
    Write-Host "[$projectLabel] Sin carpeta frontend; omitiendo Vite."
}
else {
    $viteListening = Get-NetTCPConnection -LocalPort $FrontendPort -State Listen -ErrorAction SilentlyContinue
    if ($null -ne $viteListening) {
        Write-Host "[$projectLabel] Frontend ya en :$FrontendPort."
    }
    else {
        Start-Process -FilePath 'cmd.exe' -ArgumentList @('/c', 'npm run dev') -WorkingDirectory $frontend -WindowStyle Minimized
        Write-Host "[$projectLabel] Frontend http://localhost:$FrontendPort"
    }
}

$servePattern = "artisan serve.*$BackendPort"
if ($null -ne (Test-ProcessCommandLineMatch -Pattern $servePattern)) {
    Write-Host "[$projectLabel] Backend ya en :$BackendPort."
}
else {
    Start-Process -FilePath $php -ArgumentList @(
        (Join-Path $backend 'artisan'), 'serve', '--host=127.0.0.1', "--port=$BackendPort"
    ) -WorkingDirectory $backend -WindowStyle Minimized
    Write-Host "[$projectLabel] Backend http://127.0.0.1:$BackendPort"
}

Write-Host ''
Write-Host "Entorno ${projectLabel}:"
Write-Host "  Frontend  http://localhost:$FrontendPort"
Write-Host "  Backend   http://127.0.0.1:$BackendPort"
Write-Host "  OpenAPI   http://127.0.0.1:$BackendPort/api/documentation"
