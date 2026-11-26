# Genera .env merged GEN-13 para sitios Forge PedidosWeb (requiere exports previos en forge-env-raw/)
param(
    [string]$RawDir = (Join-Path $PSScriptRoot 'forge-env-raw')
)

. (Join-Path $PSScriptRoot 'merge-gen13-forge-env.ps1')

$sites = @(
    @{ Name = 'demo'; SiteId = 3273242; Cliente = 'DEMO' }
    @{ Name = 'ankas'; SiteId = 3205335; Cliente = 'ANKAS' }
    @{ Name = 'quento'; SiteId = 3252397; Cliente = 'QUENTO' }
    @{ Name = 'expcolor'; SiteId = 3273239; Cliente = 'EXPCOLOR' }
    @{ Name = 'lacapol'; SiteId = 3273238; Cliente = 'LACAPOL' }
)

$outDir = Join-Path $PSScriptRoot 'forge-env-merged'
New-Item -ItemType Directory -Force -Path $outDir | Out-Null

foreach ($s in $sites) {
    $rawPath = Join-Path $RawDir ($s.Name + '.env')
    if (-not (Test-Path $rawPath)) {
        Write-Warning "Falta $rawPath"
        continue
    }
    $raw = Get-Content -Raw -Path $rawPath
    $merged = Merge-Gen13Env -Content $raw -Cliente $s.Cliente
  $mergedPath = Join-Path $outDir ($s.Name + '.env')
    Set-Content -Path $mergedPath -Value $merged -NoNewline -Encoding utf8
    Write-Host "OK $($s.Name) -> $mergedPath (site $($s.SiteId))"
}
