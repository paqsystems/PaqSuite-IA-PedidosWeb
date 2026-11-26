# Merge bloque GEN-13 en contenido .env (sin duplicar claves). Uso interno ops.
param(
    [Parameter(Mandatory)][string]$Content,
    [Parameter(Mandatory)][string]$Cliente
)

$stripKeys = @(
    'PAQSUITE_PROYECTO', 'PAQSUITE_CLIENTE',
    'PAQSUITE_TASKS_ROUTES_ENABLED', 'PAQSUITE_TASKS_SCHEDULE_TIMEZONE',
    'PAQSUITE_TASKS_BACKUP_BACKEND', 'PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING',
    'PAQSUITE_TASKS_BACKUP_RDS_GATEWAY', 'PAQSUITE_TASKS_BACKUP_WINDOWS_GATEWAY',
    'TASKS_BACKUP_S3_ARN_PREFIX', 'TASKS_BACKUP_WINDOWS_DEFAULT_FOLDER',
    'TASKS_BACKUP_POLL_SECONDS', 'TASKS_BACKUP_LOCK_LEASE_SECONDS', 'TASKS_BACKUP_RDS_TIMEOUT_MINUTES',
    'TASKS_BACKUP_RDS_POLLING_INTERVAL_SECONDS'
)

$lines = $Content -split "`r?`n"
$out = New-Object System.Collections.Generic.List[string]
$inGen13Block = $false

foreach ($line in $lines) {
    $trim = $line.Trim()
    if ($trim -match '^#\s*GEN-13') {
        $inGen13Block = $true
        continue
    }
    if ($inGen13Block) {
        $matchedKey = $false
        foreach ($k in $stripKeys) {
            if ($trim -match "^$k=") { $matchedKey = $true; break }
        }
        if ($matchedKey) { continue }
        if ($trim -eq '' -or $trim.StartsWith('#')) {
            $inGen13Block = $false
        } else {
            continue
        }
    }
    foreach ($k in $stripKeys) {
        if ($trim -match "^$k=") { continue 2 }
    }
    $out.Add($line)
}

while ($out.Count -gt 0 -and [string]::IsNullOrWhiteSpace($out[$out.Count - 1])) {
    $out.RemoveAt($out.Count - 1)
}

$block = @(
    '',
    '# GEN-13 tareas programadas (CC #19-#20)',
    'PAQSUITE_PROYECTO=pedidosweb',
    "PAQSUITE_CLIENTE=$Cliente",
    'PAQSUITE_TASKS_ROUTES_ENABLED=true',
    'PAQSUITE_TASKS_SCHEDULE_TIMEZONE=America/Argentina/Buenos_Aires',
    'PAQSUITE_TASKS_BACKUP_BACKEND=rds',
    'PAQSUITE_TASKS_BACKUP_DEFER_MANUAL_POLLING=true',
    'PAQSUITE_TASKS_BACKUP_RDS_GATEWAY=rds',
    'PAQSUITE_TASKS_BACKUP_WINDOWS_GATEWAY=sqlserver',
    'TASKS_BACKUP_S3_ARN_PREFIX=arn:aws:s3:::sql-amazon-basico/backups',
    'TASKS_BACKUP_WINDOWS_DEFAULT_FOLDER=',
    'TASKS_BACKUP_POLL_SECONDS=5',
    'TASKS_BACKUP_LOCK_LEASE_SECONDS=900',
    'TASKS_BACKUP_RDS_TIMEOUT_MINUTES=120'
)

$out.AddRange($block)
return ($out -join "`n")
