# Alias PedidosWeb → start-dev.ps1 (compatibilidad)
param(
    [switch]$All
)

$startDev = Join-Path $PSScriptRoot 'start-dev.ps1'
if ($All) {
    & $startDev -All
}
else {
    & $startDev
}
