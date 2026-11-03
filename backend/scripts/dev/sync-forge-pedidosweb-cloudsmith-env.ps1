# Sincroniza CLOUDSMITH_READ_TOKEN en Environment de sitios Forge PedidosWeb (requiere MCP Forge o API token).
# Uso local: exportar envs desde Forge, guardar como forge-env-<siteId>.txt, luego:
#   .\sync-forge-pedidosweb-cloudsmith-env.ps1 -EnvDir C:\ruta\a\carpeta

param(
    [Parameter(Mandatory = $true)]
    [string] $EnvDir
)

$ErrorActionPreference = 'Stop'
$injectScript = Join-Path $PSScriptRoot 'inject-cloudsmith-env-block.php'

if (-not (Test-Path $injectScript)) {
    throw "No existe $injectScript"
}

if (-not $env:CLOUDSMITH_READ_TOKEN) {
    throw 'Definí CLOUDSMITH_READ_TOKEN antes de ejecutar.'
}

Get-ChildItem -Path $EnvDir -Filter 'forge-env-*.txt' | Where-Object { $_.Name -notmatch '-patched' } | ForEach-Object {
    $src = $_.FullName
    $dst = $src -replace '\.txt$', '-patched.txt'
    cmd /c "php `"$injectScript`" < `"$src`" > `"$dst`""
    Write-Host "OK $($_.Name) -> $(Split-Path $dst -Leaf)"
}

Write-Host 'Subí cada *-patched.txt al Environment del sitio en Forge (o usá el MCP update-site-environment).'
