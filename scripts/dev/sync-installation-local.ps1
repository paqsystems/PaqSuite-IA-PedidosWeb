# GEN-34 — sync local antes de levantar dev (regla 16-entorno-desarrollo-gen34)
# Uso (desde raíz repo): .\scripts\dev\sync-installation-local.ps1

$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'Start-LaravelDevQueueWorker.ps1')
$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..\..')).Path
$backend = Join-Path $repoRoot 'backend'
$php = Get-PhpExecutable

Push-Location $backend
try {
    $cliente = $env:PAQSUITE_CLIENTE
    if ([string]::IsNullOrWhiteSpace($cliente)) {
        $cliente = $env:TENANT_DEFAULT_CLIENT
    }
    if ([string]::IsNullOrWhiteSpace($cliente)) {
        $cliente = 'desarrollo'
    }
    $proyecto = $env:PAQSUITE_PROYECTO
    if ([string]::IsNullOrWhiteSpace($proyecto)) {
        $proyecto = 'pedidosweb'
    }

    $alignMigrationScripts = @(
        '2026_11_04_100000_align_pq_empresa_gen06_for_sp.php',
        '2026_11_04_100001_add_pq_parametros_gral_sp_compat_columns.php',
        '2026_11_04_100002_create_pq_emission_tables.php',
        '2026_11_04_100003_align_pq_emission_jobs_for_framework_gen15.php',
        '2026_11_04_100004_backfill_pq_menus_label_key_columns.php',
        '2026_11_04_100005_rebuild_pq_empresa_unified_gen06.php',
        '2026_11_04_100006_add_pq_menus_sp_compat_columns.php',
        '2026_11_04_100007_add_pq_empresa_legacy_sp_aliases.php',
        '2026_11_04_100008_add_users_gen06_sp_compat_columns.php',
        '2026_11_04_100009_users_gen06_physical_login_columns.php',
        '2026_11_04_100010_align_pq_grid_layouts_gen11_sp.php',
        '2026_11_04_100011_add_pq_grid_layout_last_used_created_at.php',
        '2026_11_04_100012_pq_grid_layouts_physical_user_id.php',
        '2026_11_04_100013_gen10_pq_parametros_gral_bridge.php',
        '2026_11_04_100014_add_pq_menus_codigo_alias.php'
    )
    Write-Host '[GEN-34] migraciones alineación GEN-06/15 (pre-update)...'
    foreach ($migrationScript in $alignMigrationScripts) {
        $migrationPath = Join-Path $backend ("database/migrations/$migrationScript")
        if (-not (Test-Path $migrationPath)) {
            Write-Warning "Falta migración $migrationScript"
            continue
        }
        & $php artisan migrate --force "--path=database/migrations/$migrationScript"
        if ($LASTEXITCODE -ne 0) {
            Write-Warning "migrate falló en $migrationScript (exit $LASTEXITCODE)"
        }
    }

    $hostManifest = $env:PAQSUITE_HOST_MANIFEST
    if ([string]::IsNullOrWhiteSpace($hostManifest)) {
        $semver = (Get-Content (Join-Path $backend 'composer.json') -Raw | ConvertFrom-Json).require.'paqsuite/laravel-core'
        if ([string]::IsNullOrWhiteSpace($semver)) { $semver = '1.3.18-beta.24' }
        $hostManifest = (Resolve-Path (Join-Path $backend "database/manifest/pedidosweb-$semver.json") -ErrorAction SilentlyContinue).Path
    }
    if ($hostManifest -and -not [System.IO.Path]::IsPathRooted($hostManifest)) {
        $hostManifest = (Resolve-Path (Join-Path $backend $hostManifest)).Path
    }

    Write-Host "[GEN-34] installation:update ($cliente / $proyecto)..."
    if ($hostManifest -and (Test-Path $hostManifest)) {
        & $php artisan paqsuite:installation:update --cliente=$cliente --proyecto=$proyecto --host-manifest=$hostManifest --non-interactive
    } else {
        & $php artisan paqsuite:installation:update --cliente=$cliente --proyecto=$proyecto --non-interactive
    }
    $updateExit = $LASTEXITCODE
    if ($updateExit -ne 0) {
        Write-Warning "installation:update terminó con código $updateExit (p. ej. esquema legacy pq_empresa / parámetros). Revisá: php artisan paqsuite:installation:status --cliente=$cliente --proyecto=$proyecto"
    }

    Write-Host "[GEN-34] sql:sync-dev (SP Framework, solo local)..."
    & $php artisan paqsuite:sql:sync-dev
    $sqlExit = $LASTEXITCODE
    if ($sqlExit -ne 0) {
        Write-Warning "paqsuite:sql:sync-dev terminó con código $sqlExit (puede quedar bloqueado en pasos GEN-06 antes de emisiones)."
    }

    if ($updateExit -eq 0 -and $sqlExit -eq 0) {
        Write-Host '[GEN-34] Sync completado.'
        exit 0
    }
    exit 1
}
finally {
    Pop-Location
}
