# Obtiene Environment de sitios Forge, inyecta CLOUDSMITH_READ_TOKEN si falta, y hace PUT.
# Requiere: CLOUDSMITH_READ_TOKEN, FORGE_API_TOKEN (o -McpJsonPath apuntando a mcp.json con laravel-forge-mcp).

param(
    [int[]] $SiteIds = @(3205335, 3239589, 3252397, 3273238, 3273239, 3323578),
    [int] $ServerId = 485400,
    [string] $Organization = 'paqsystems',
    [string] $McpJsonPath = ''
)

$ErrorActionPreference = 'Stop'
$injectScript = Join-Path $PSScriptRoot 'inject-cloudsmith-env-block.php'

if (-not $env:CLOUDSMITH_READ_TOKEN) {
    throw 'Definí CLOUDSMITH_READ_TOKEN.'
}

$forgeToken = $env:FORGE_API_TOKEN
if (-not $forgeToken -and $McpJsonPath -and (Test-Path $McpJsonPath)) {
    $mcp = Get-Content -Raw $McpJsonPath | ConvertFrom-Json
    $forgeToken = $mcp.mcpServers.'laravel-forge-mcp'.env.FORGE_API_TOKEN
}

if (-not $forgeToken) {
    throw 'Definí FORGE_API_TOKEN o pasá -McpJsonPath con laravel-forge-mcp.'
}

$headers = @{
    Authorization = "Bearer $forgeToken"
    Accept        = 'application/json'
    'Content-Type' = 'application/json'
}

foreach ($siteId in $SiteIds) {
    $getUrl = "https://forge.laravel.com/api/orgs/$Organization/servers/$ServerId/sites/$siteId/environment"
    $resp = Invoke-RestMethod -Uri $getUrl -Headers $headers -Method Get
    $raw = $resp.data.attributes.content

    if ($raw -match '(?m)^CLOUDSMITH_READ_TOKEN=') {
        Write-Host "SKIP site $siteId (ya tiene CLOUDSMITH)"
        continue
    }

    $tmpIn = [System.IO.Path]::GetTempFileName()
    $tmpOut = [System.IO.Path]::GetTempFileName()
    try {
        [System.IO.File]::WriteAllText($tmpIn, $raw)
        cmd /c "php `"$injectScript`" < `"$tmpIn`" > `"$tmpOut`""
        $patched = [System.IO.File]::ReadAllText($tmpOut)
        $body = @{ environment = $patched } | ConvertTo-Json -Compress
        Invoke-RestMethod -Uri $getUrl -Headers $headers -Method Put -Body $body | Out-Null
        Write-Host "OK site $siteId Environment actualizado"
    }
    finally {
        Remove-Item $tmpIn, $tmpOut -ErrorAction SilentlyContinue
    }
}
