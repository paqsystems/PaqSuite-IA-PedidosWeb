function Get-PhpExecutable {
    $phpCmd = Get-Command php -ErrorAction SilentlyContinue
    if ($phpCmd) {
        return $phpCmd.Source
    }

    return 'php'
}

function Get-DevQueueWorkerName {
    param([Parameter(Mandatory = $true)] [string]$BackendPath)
    $leaf = (Split-Path $BackendPath -Leaf) -replace '[^a-zA-Z0-9_-]', '-'
    return "paqsuite-dev-$leaf"
}

function Test-QueueWorkerRunningForBackend {
    param(
        [Parameter(Mandatory = $true)]
        [string]$BackendPath
    )

    $workerName = Get-DevQueueWorkerName -BackendPath $BackendPath
    Get-CimInstance Win32_Process -Filter "name='php.exe'" -ErrorAction SilentlyContinue |
        Where-Object {
            $_.CommandLine -match 'queue:work' -and
            (
                $_.CommandLine -match [regex]::Escape($workerName) -or
                $_.CommandLine -match [regex]::Escape($BackendPath)
            )
        } |
        Select-Object -First 1
}

function Start-LaravelDevQueueWorker {
    param(
        [Parameter(Mandatory = $true)]
        [string]$BackendPath,
        [Parameter(Mandatory = $true)]
        [string]$ProjectLabel,
        [string]$QueueConnection = 'database',
        [string]$QueueName = 'default',
        [int]$Tries = 3,
        [int]$TimeoutSeconds = 120
    )

    $backendPath = (Resolve-Path $BackendPath).Path
    $workerName = Get-DevQueueWorkerName -BackendPath $backendPath
    $artisan = Join-Path $backendPath 'artisan'
    if (-not (Test-Path $artisan)) {
        Write-Warning "[$ProjectLabel] No hay artisan en $backendPath"
        return
    }

    $existing = Test-QueueWorkerRunningForBackend -BackendPath $backendPath
    if ($null -ne $existing) {
        Write-Host "[$ProjectLabel] Worker ya activo (PID $($existing.ProcessId))."
        return
    }

    $php = Get-PhpExecutable
    Push-Location $backendPath
    try {
        $queueDriver = (php artisan config:show queue.default --no-ansi 2>$null).Trim()
        if ($queueDriver -eq 'sync') {
            Write-Host ('[{0}] QUEUE_CONNECTION=sync — worker omitido (cola en línea).' -f $ProjectLabel)
            return
        }

        # Argumentos separados: en Windows, --queue=default en un solo token a veces
        # se parte y Laravel interpreta default como segundo argumento posicional.
        $argumentList = @(
            'artisan',
            'queue:work',
            $QueueConnection,
            '--queue',
            $QueueName,
            '--tries',
            [string] $Tries,
            '--timeout',
            [string] $TimeoutSeconds,
            '--sleep',
            '1',
            '--name',
            $workerName
        )
        Start-Process -FilePath $php -ArgumentList $argumentList -WorkingDirectory $backendPath -WindowStyle Minimized
        $workerStartedMsg = '[{0}] Worker iniciado ({1}, cola {2}).' -f $ProjectLabel, $QueueConnection, $QueueName
        Write-Host $workerStartedMsg
    }
    finally {
        Pop-Location
    }
}
