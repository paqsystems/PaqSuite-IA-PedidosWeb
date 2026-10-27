# Workers de cola para todos los hosts PaqSuite IA en esta PC (Windows)
# Escanea c:\Programacion\PaqSuite-IA-* con backend/artisan.
# Omite repos con QUEUE_CONNECTION=sync.

param(
    [string]$SearchRoot = 'C:\Programacion'
)

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'Start-LaravelDevQueueWorker.ps1')

if (-not (Test-Path $SearchRoot)) {
    Write-Error "No existe SearchRoot: $SearchRoot"
}

$repos = Get-ChildItem -Path $SearchRoot -Directory -Filter 'PaqSuite-IA-*' | Sort-Object Name
if ($repos.Count -eq 0) {
    Write-Warning "No se encontraron carpetas PaqSuite-IA-* en $SearchRoot"
    exit 0
}

foreach ($repo in $repos) {
    $backend = Join-Path $repo.FullName 'backend'
    Start-LaravelDevQueueWorker -BackendPath $backend -ProjectLabel $repo.Name
}

Write-Host ''
Write-Host 'Listo. Un worker por host con cola real (database/redis/etc.).'
Write-Host 'Stack de un solo producto: .\scripts\dev\start-dev.ps1 -All en ese repo.'
