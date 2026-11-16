# GEN-13 — sync SQL/catálogo tareas + smoke CLI (CC PQ #20)
# Requiere conectividad a DB_HOST del backend/.env (RDS: VPN/firewall).
# Uso (desde raíz repo):
#   .\scripts\dev\sync-gen13-tasks.ps1
#   $env:PAQSUITE_CLIENTE='quento'; .\scripts\dev\sync-gen13-tasks.ps1

$ErrorActionPreference = 'Stop'
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$backend = Join-Path $repoRoot 'backend'
. (Join-Path $PSScriptRoot 'Start-LaravelDevQueueWorker.ps1')
$php = Get-PhpExecutable

Push-Location $backend
try {
    $cliente = $env:PAQSUITE_CLIENTE
    if ([string]::IsNullOrWhiteSpace($cliente)) {
        $cliente = $env:TENANT_DEFAULT_CLIENT
    }
    if ([string]::IsNullOrWhiteSpace($cliente)) {
        $cliente = 'demo'
    }
    $proyecto = if ($env:PAQSUITE_PROYECTO) { $env:PAQSUITE_PROYECTO } else { 'pedidosweb' }

    Write-Host "[GEN-13] GEN-34 previo (migrate + installation:update + sql sync)..."
    Pop-Location
    & (Join-Path $PSScriptRoot 'sync-installation-local.ps1')
    $gen34Exit = $LASTEXITCODE
    Push-Location $backend

    if ($gen34Exit -ne 0) {
        Write-Warning "[GEN-13] GEN-34 terminó con código $gen34Exit. Si fue timeout TCP, verificá VPN/firewall hacia DB_HOST."
    }

    Write-Host "[GEN-13] tasks:validate-catalog..."
    & $php artisan tasks:validate-catalog
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    Write-Host "[GEN-13] pedidosweb:tasks-run-due --dry-run..."
    & $php artisan pedidosweb:tasks-run-due --dry-run
    if ($LASTEXITCODE -ne 0) { exit $LASTEXITCODE }

    Write-Host "[GEN-13] installation:status ($cliente / $proyecto)..."
    & $php artisan paqsuite:installation:status --cliente=$cliente --proyecto=$proyecto

    Write-Host '[GEN-13] Smoke CLI OK. Alta definición BACKUP_DATABASE = UI / smoke manual.'
    exit 0
}
finally {
    Pop-Location
}
